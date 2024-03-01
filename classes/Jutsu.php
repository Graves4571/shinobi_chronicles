<?php

use JetBrains\PhpStorm\Pure;

require_once __DIR__ . "/Effect.php";

class Jutsu {
    const ELEMENT_NONE = 'None';
    const ELEMENT_FIRE = 'Fire';
    const ELEMENT_EARTH = 'Earth';
    const ELEMENT_WIND = 'Wind';
    const ELEMENT_WATER = 'Water';
    const ELEMENT_LIGHTNING = 'Lightning';

    const PURCHASE_TYPE_DEFAULT = 1;
    const PURCHASE_TYPE_PURCHASABLE = 2;
    const PURCHASE_TYPE_NON_PURCHASABLE = 3;
    const PURCHASE_TYPE_BLOODLINE = 4;
    const PURCHASE_TYPE_EVENT_SHOP = 5;
    const PURCHASE_TYPE_LINKED = 6;

    const MAX_LEVEL = 100;
    const REFUND_AMOUNT = 0.1;

    const TYPE_NINJUTSU = 'ninjutsu';
    const TYPE_TAIJUTSU = 'taijutsu';
    const TYPE_GENJUTSU = 'genjutsu';

    const USE_TYPE_MELEE = 'physical';
    const USE_TYPE_PROJECTILE = 'projectile';
    const USE_TYPE_PROJECTILE_AOE = 'projectile_aoe';
    const USE_TYPE_REMOTE_SPAWN = 'spawn';
    const USE_TYPE_BUFF = 'buff';
    const USE_TYPE_BARRIER = 'barrier';
    const USE_TYPE_INDIRECT = 'indirect';

    const TARGET_TYPE_FIGHTER_ID = 'fighter_id';
    const TARGET_TYPE_TILE = 'tile';
    const TARGET_TYPE_DIRECTION = 'direction';

    const POWER_PER_LEVEL_PERCENT = 0.3;
    const EFFECT_PER_LEVEL_PERCENT = 0.2;

    const CHUUNIN_SCALE_MULTIPLIER = 1.4; // 2.9 => 3.9 = +34.4%
    const JONIN_SCALE_MULTIPLIER = 1.75; // 2.9 => 4.9 = +69%

    const BALANCE_BASELINE_POWER = 4.4;
    const BALANCE_EFFECT_RATIOS = [
        'offense_boost' => 1.85,
        'elemental_boost' => 1.15,
        'evasion_nerf' => 2.1,
        'offense_nerf' => 2,
        'erosion' => 0.35,
        'vulnerability' => 2,
        'elemental_vulnerability' => 1.25,
        'hybrid_elemental_vulnerability' => 1.375,
        'resist_boost' => 1.85,
        'evasion_boost' => 2.1,
        'speed_boost' => 1,
        'piercing' => 0.75,
        'counter' => 3.25,
        'reflect' => 3.25,
        'substitution' => 2.5,
        'immolate' => 2.5,
        'recoil' => 0,
    ];

    /* Genjutsu gets declared with full power and effect instead of a tradeoff between them, we balance in code
    const GENJUTSU_ATTACK_POWER_MODIFIER = 0.55;*/

    public static array $elements = [
        self::ELEMENT_NONE,
        self::ELEMENT_FIRE,
        self::ELEMENT_EARTH,
        self::ELEMENT_WIND,
        self::ELEMENT_WATER,
        self::ELEMENT_LIGHTNING,
    ];
    public static array $use_types = [
        self::USE_TYPE_MELEE,
        self::USE_TYPE_PROJECTILE,
        self::USE_TYPE_PROJECTILE_AOE,
        self::USE_TYPE_REMOTE_SPAWN,
        self::USE_TYPE_BUFF,
        self::USE_TYPE_BARRIER,
        self::USE_TYPE_INDIRECT,
    ];

    public static array $attacking_use_types = [
        self::USE_TYPE_MELEE,
        self::USE_TYPE_PROJECTILE,
    ];

    public int $id;
    public string $name;
    public int $rank;
    public string $jutsu_type;

    public float $base_power;
    public float $power;

    public int $range;

    public ?string $effect;

    private float $base_effect_amount;
    public float $effect_amount;

    public int $effect_length;

    /** @var Effect[] */
    public array $effects;

    public string $description;
    public string $battle_text;

    public int $cooldown;

    public string $use_type;
    public string $target_type;

    public int $use_cost;
    public int $purchase_cost;
    public int $purchase_type;

    public ?int $parent_jutsu;

    // TODO: Upgrade to enum when PHP 8.1 releases
    public string $element;

    public string $hand_seals;

    public int $travel_speed = 1;

    // Dynamic vars
    public bool $is_bloodline = false;
    public bool $is_weapon = false;

    public int $level = 0;
    public int $exp = 0;

    public ?int $weapon_id = null;
    public ?Jutsu $weapon_effect = null;
    public bool $effect_only = false;

    public ?string $combat_id = null;

    public int $linked_jutsu_id = 0;

    private bool $rank_scaling_applied = false;

    public function __construct(int $id, string $name, int $rank, string $jutsu_type, float $base_power, int $range,
        ?string $effect_1, ?float $base_effect_amount_1, ?int $effect_length_1, ?string $effect_2, ?float $base_effect_amount_2, ?int $effect_length_2,
        string $description, string $battle_text, int $cooldown,
        string $use_type, string $target_type, int $use_cost, int $purchase_cost, int $purchase_type, ?int $parent_jutsu, string $element,
        string $hand_seals, int $linked_jutsu_id = 0
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->rank = $rank;
        $this->jutsu_type = $jutsu_type;

        $this->base_power = $base_power;
        $this->power = $this->base_power;

        $this->range = $range;
        if($this->jutsu_type == Jutsu::TYPE_TAIJUTSU) {
            $this->range = 1;
        }

        // legacy
        $this->effect = $effect_1;
        $this->base_effect_amount = $base_effect_amount_1;
        $this->effect_amount = $this->base_effect_amount;
        $this->effect_length = $effect_length_1 ?? 0;

        // new effect array
        $this->effects[] = new Effect($effect_1, $base_effect_amount_1, $effect_length_1);
        $this->effects[] = new Effect($effect_2, $base_effect_amount_2, $effect_length_2);

        $this->description = $description;
        $this->battle_text = $battle_text;

        $this->cooldown = $cooldown;

        $this->use_type = $use_type;
        $this->target_type = $target_type;
        $this->use_cost = $use_cost;
        $this->purchase_cost = $purchase_cost;
        $this->purchase_type = $purchase_type;

        $this->parent_jutsu = $parent_jutsu;
        $this->element = $element;
        $this->hand_seals = $hand_seals;

        $this->linked_jutsu_id = $linked_jutsu_id;

        if($this->purchase_type == Jutsu::PURCHASE_TYPE_EVENT_SHOP && $this->purchase_cost === 0) {
            $this->purchase_cost = 1;
        }
    }

    public static function fromArray(int $id, array $jutsu_data): Jutsu {
        return new Jutsu(
            id: $id,
            name: $jutsu_data['name'],
            rank: $jutsu_data['rank'],
            jutsu_type: $jutsu_data['jutsu_type'],
            base_power: $jutsu_data['power'],
            range: $jutsu_data['range'],
            effect_1: $jutsu_data['effect'] ?? 'none',
            base_effect_amount_1: $jutsu_data['effect_amount'] ?? 0,
            effect_length_1: $jutsu_data['effect_length'] ?? 0,
            effect_2: $jutsu_data['effect2'],
            base_effect_amount_2: $jutsu_data['effect2_amount'],
            effect_length_2: $jutsu_data['effect2_length'],
            description: $jutsu_data['description'],
            battle_text: $jutsu_data['battle_text'],
            cooldown: $jutsu_data['cooldown'],
            use_type: $jutsu_data['use_type'],
            target_type: $jutsu_data['target_type'] ?? Jutsu::TARGET_TYPE_TILE,
            use_cost: $jutsu_data['use_cost'],
            purchase_cost: $jutsu_data['purchase_cost'],
            purchase_type: $jutsu_data['purchase_type'],
            parent_jutsu: $jutsu_data['parent_jutsu'],
            element: $jutsu_data['element'],
            hand_seals: $jutsu_data['hand_seals'],
            linked_jutsu_id: $jutsu_data['linked_jutsu_id'],
        );
    }

    public function setLevel(int $level, int $exp): void {
        $this->level = $level;
        $this->exp = $exp;

        $this->recalculatePower();

        $level_effect_multiplier = self::EFFECT_PER_LEVEL_PERCENT / 100;
        foreach ($this->effects as $index => $effect) {
            if ($effect->effect && $effect->effect != 'none') {
                $this->effects[$index]->effect_amount = $effect->base_effect_amount *
                    (1 + round($this->level * $level_effect_multiplier, 3));
                $this->effects[$index]->display_effect_amount = $this->effects[$index]->effect_amount;
            }
        }
    }

    public function recalculatePower(): void {
        $level_power_multiplier = self::POWER_PER_LEVEL_PERCENT / 100;

        $this->power = $this->base_power * (1 + ($this->level * $level_power_multiplier));
        $this->power = round($this->power, 2);
    }

    public function setWeapon(int $weapon_id, $effect, $effect_amount): Jutsu {
        $this->weapon_id = $weapon_id;
        $this->weapon_effect = new Jutsu(
            id: $weapon_id * -1,
            name: $this->name,
            rank: $this->rank,
            jutsu_type: Jutsu::TYPE_TAIJUTSU,
            base_power: $this->power,
            range: 0,
            effect_1: $effect,
            base_effect_amount_1: $effect_amount,
            effect_length_1: 2,
            effect_2: 'none',
            base_effect_amount_2: 0,
            effect_length_2: 0,
            description: $this->description,
            battle_text: $this->battle_text,
            cooldown: $this->cooldown,
            use_type: $this->use_type,
            target_type: $this->target_type,
            use_cost: $this->use_cost,
            purchase_cost: $this->purchase_cost,
            purchase_type: $this->purchase_type,
            parent_jutsu: $this->parent_jutsu,
            element: $this->element,
            hand_seals: $this->hand_seals
        );
        $this->weapon_effect->is_weapon = true;

        return $this->weapon_effect;
    }

    public function setCombatId(string $fighter_combat_id) {
        $prefix = $this->is_bloodline ? 'BL_J' : 'J';
        $this->combat_id = $prefix . $this->id . ':' . $fighter_combat_id;
    }

    public function hasEffect(): bool
    {
        $has_effect = false;
        foreach ($this->effects as $effect) {
            if ($effect && $effect->effect != 'none') {
                $has_effect = true;
            }
        }
        return $has_effect;
    }

    public function hasElement(): bool {
        if (isset($this->element) && $this->element != self::ELEMENT_NONE && $this->element != 'none') {
           return true;
        }
        return false;
    }

    #[Pure]
    public function isAllyTargetType(): bool {
        return in_array($this->use_type, [Jutsu::USE_TYPE_BUFF, Jutsu::USE_TYPE_BARRIER]);
    }

    // TODO: Replace public usages of level with this, privatize level
    /*public function getLevel() {
        return $this->level;
    }*/

    /**
     * @param System $system
     * @return Jutsu[]
     * @throws DatabaseDeadlockException
     */
    public static function fetchAll(System $system): array {
        $result = $system->db->query("SELECT * FROM `jutsu` ORDER BY `rank` ASC, `purchase_cost` ASC");

        $jutsu = [];
        while($jutsu_db = $system->db->fetch($result)) {
            $jutsu[$jutsu_db['jutsu_id']] = Jutsu::fromArray($jutsu_db['jutsu_id'], $jutsu_db);
        }

        return $jutsu;
    }


    public function applyRankScaling(int $player_rank_num): void {
        if($this->rank_scaling_applied) {
            // Applying this twice to the same jutsu would cause it to be super overpowered
            throw new RuntimeException("Rank scaling already applied!");
        }
        $this->rank_scaling_applied = true;

        if ($this->purchase_type == Jutsu::PURCHASE_TYPE_EVENT_SHOP) {
            if ($player_rank_num == 3) {
                $this->use_cost *= 2;
                $this->base_power *= Jutsu::CHUUNIN_SCALE_MULTIPLIER;
                $this->power *= Jutsu::CHUUNIN_SCALE_MULTIPLIER;
            }
            else if ($player_rank_num == 4) {
                $this->use_cost *= 3;
                $this->base_power *= Jutsu::JONIN_SCALE_MULTIPLIER;
                $this->power *= Jutsu::JONIN_SCALE_MULTIPLIER;
            }
        }
    }

    public function getBalanceMaxUtility(): float {
        if(!$this->rank_scaling_applied) {
            $this->applyRankScaling(System::SC_MAX_RANK);
        }

        $level_power_multiplier = self::POWER_PER_LEVEL_PERCENT / 100;
        $capped_power = $this->base_power * (1 + (self::MAX_LEVEL * $level_power_multiplier));

        $cr_discount_per_turn_multiplier = 0.005; // 0.5% discount

        $residual_effect_percent = 0;
        $compound_residual_effect_percent = 0;
        $compound_residual_discount = 0;
        $recoil_effect_percent = 0;

        $total_effect_utility = 0;
        
        $level_effect_multiplier = self::EFFECT_PER_LEVEL_PERCENT / 100;

        $total_elemental_vuln = 0;
        $count_elemental_vulns = 0;

        foreach ($this->effects as $effect) {
            if(!$effect->effect || $effect->effect == 'none') continue;

            $capped_effect_amount = $effect->base_effect_amount * (1 + self::MAX_LEVEL * $level_effect_multiplier);
            $capped_effect_amount = round($capped_effect_amount / 100, 4);

            switch($effect->effect) {
                case 'none':
                case 'barrier':
                    break;
                case 'residual_damage':
                case 'delayed_residual':
                    $residual_effect_percent += $capped_effect_amount * $effect->effect_length;
                    break;
                case 'compound_residual':
                    $compound_residual_effect_percent += $capped_effect_amount * $effect->effect_length;

                    $max_damage_multiplier = pow(
                        1 + BattleEffectsManager::COMPOUND_RESIDUAL_INCREASE,
                        $effect->effect_length - 1
                    );

                    $extra_effective_percent = $compound_residual_effect_percent * ($max_damage_multiplier - 1) * 0.5;
                    $compound_residual_effect_percent += $extra_effective_percent;

                    // Discount
                    $compound_residual_discount += $compound_residual_effect_percent * ($cr_discount_per_turn_multiplier * $effect->effect_length);
                    break;
                case 'recoil':
                    $recoil_effect_percent += $capped_effect_amount * $effect->effect_length;
                    break;
                case 'immolate':
                    $total_effect_utility += self::BALANCE_EFFECT_RATIOS['immolate'] * $capped_effect_amount * $effect->effect_length;
                    break;
                case 'counter':
                case 'substitution':
                case 'reflect':
                case 'piercing':
                    $total_effect_utility += self::BALANCE_EFFECT_RATIOS[$effect->effect] * $capped_effect_amount * $effect->effect_length;
                    break;
                case 'ninjutsu_boost':
                case 'taijutsu_boost':
                case 'genjutsu_boost':
                    $total_effect_utility += self::BALANCE_EFFECT_RATIOS['offense_boost'] * $capped_effect_amount * $effect->effect_length;
                    break;
                case 'speed_boost':
                case 'cast_speed_boost':
                    $total_effect_utility += self::BALANCE_EFFECT_RATIOS['speed_boost'] * $capped_effect_amount * $effect->effect_length;
                    break;
                case 'fire_boost':
                case 'wind_boost':
                case 'lightning_boost':
                case 'earth_boost':
                case 'water_boost':
                    $total_effect_utility += self::BALANCE_EFFECT_RATIOS['elemental_boost'] * $capped_effect_amount * $effect->effect_length;
                    break;
                case 'evasion_boost':
                    $total_effect_utility += self::BALANCE_EFFECT_RATIOS['evasion_boost'] * $capped_effect_amount * $effect->effect_length;
                    break;
                case 'resist_boost':
                    $total_effect_utility += self::BALANCE_EFFECT_RATIOS['resist_boost'] * $capped_effect_amount * $effect->effect_length;
                    break;
                case 'ninjutsu_nerf':
                case 'taijutsu_nerf':
                case 'genjutsu_nerf':
                case 'offense_nerf':
                    $total_effect_utility += self::BALANCE_EFFECT_RATIOS['offense_nerf'] * $capped_effect_amount * $effect->effect_length;
                    break;

                case 'cast_speed_nerf':
                case 'speed_nerf':
                    $total_effect_utility += self::BALANCE_EFFECT_RATIOS['speed_boost'] * $capped_effect_amount * $effect->effect_length;
                    break;

                case 'vulnerability':
                    $total_effect_utility += self::BALANCE_EFFECT_RATIOS['vulnerability'] * $capped_effect_amount * $effect->effect_length;
                    break;
                case 'fire_vulnerability':
                case 'wind_vulnerability':
                case 'lightning_vulnerability':
                case 'earth_vulnerability':
                case 'water_vulnerability':
                    $total_elemental_vuln += self::BALANCE_EFFECT_RATIOS['vulnerability'] * $capped_effect_amount * $effect->effect_length;
                    $count_elemental_vulns++;
                    break;
                case 'evasion_nerf':
                    $total_effect_utility += self::BALANCE_EFFECT_RATIOS['evasion_nerf'] * $capped_effect_amount * $effect->effect_length;
                    break;
                case 'erosion':
                    $total_effect_utility += self::BALANCE_EFFECT_RATIOS['erosion'] * $capped_effect_amount * $effect->effect_length;
                    break;
            }
        }
        
        if($count_elemental_vulns >= 2) {
            $total_effect_utility += ($total_elemental_vuln / $count_elemental_vulns) * self::BALANCE_EFFECT_RATIOS['hybrid_elemental_vulnerability'];
        }
        else if($count_elemental_vulns == 1) {
            $total_effect_utility += $total_elemental_vuln * self::BALANCE_EFFECT_RATIOS['elemental_vulnerability'];
        }

        $residual_power = $capped_power * $residual_effect_percent;
        $compound_residual_power = $capped_power * $compound_residual_effect_percent;

        $recoil_power = $recoil_effect_percent * ($capped_power + $residual_power + $compound_residual_power);
        $recoil_self_damage = $recoil_effect_percent * $capped_power;
        $recoil_discount = $capped_power * $recoil_effect_percent * 0.15;

        // Final power
        $total_utility = $capped_power + $residual_power + $compound_residual_power + $recoil_power;
        $total_utility += $total_effect_utility;

        // Long effect discount (0%)

        $total_utility -= $compound_residual_discount;
        $total_utility -= $recoil_self_damage;
        $total_utility -= $recoil_discount;

        return $total_utility;

    }
}