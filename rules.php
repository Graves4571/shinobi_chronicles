<?php
session_start();

require "classes/System.php";
$system = new System();
$layout = $system->fetchLayoutByName(System::DEFAULT_LAYOUT);

if(isset($_SESSION['user_id'])) {
    require_once 'classes.php';
    $player = User::loadFromId($system, $_SESSION['user_id']);
    $player->loadData();
    $layout = $system->fetchLayoutByName($player->layout);
}

if ($layout->key == "new_geisha") {
    require($layout->headerModule);
    echo $layout->heading;
    require($layout->sidebarModule);
    require($layout->topbarModule);
	echo "<div id='content'>";
} else {
    $layout->renderStaticPageHeader('Rules');
}

?><table class='table'><tr><th>Rules</th></tr><tr><td><!--START_EDIT--><p>These rules are meant to serve as a guideline for on-site behavior. Case-by-case interpretation and enforcement is at the discretion of the moderating staff. If you have any problems with a moderator's decision, do not call them out in the chat. Follow the chain of command; any problems with a moderator go to a head moderator FIRST before going to an admin.</p><br><h3>Offensive language</h3><div>	Using offensive language is against the rules. All users are encouraged to avoid using language that would offend others in public 	or private settings. Shinobi Chronicles promotes an environment for a mixed age group; thus, inappropriate language is prohibited. 	This includes, but not limited to: 	<ul>		<li>Profanity that bypasses the explicit language filter (e.g. w0rd instead of word)</li>		<li>Racism</li>		<li>Religious discrimination</li>		<li>Explicit or excessive sexual references</li>		<li>Inappropriate references to illegal drugs and their use</li>		<li>Accounts with offensive usernames</li>	</ul></div><h3>Images</h3><div>	All user pictures are subject to moderation (i.e. avatars, signatures, or any other publicly displayed images). Inappropriate pictures 	would contain the following: 	<ul>		<li>Sexual content</li>		<li>Profanity</li>		<li>Racism</li>		<li>Harassment </li>	</ul>	The administration reserves the right to deem user-pictures inappropriate, even when not falling under any of the above categories. If 	the subjected user refuses to change the picture after the request of staff, the administration will be forced to change the picture 	and ban the user.</div><h3>Social Etiquette/Spamming</h3><div>	To promote a social and peaceful environment, a few guidelines have been set to ensure a user friendly experience. Those guidelines 	are as follows:	<ul>		<li>Within publicly accessible locations, excessive use of any language besides English is not allowed. (Other languages can be 		used in Personal Messages or other private places.)</li>		<li>Sexually excessive, and/or racist posts are not allowed.</li>		<li>Harassing other players and/or staff is not allowed</li>		<li>Excessive use of BBCode, ASCII art, or meme faces is not permissible.</li>		<li>Nonsensical posts that do not contribute to the conversation in any way are not allowed.</li>		<li>Harassment, trolling, or otherwise pestering a user is not allowed.</li>		<li>Unnecessarily breaking up chat messages into multiple short posts (e.g. "hello" "my" "name" "is" "bob") is not allowed.</li>	</ul></div><h3>Account Responsibility:</h3><div>	<ul>		<li>Account limits: 2 accounts</li>        <li>Attacking your own account is not allowed.</li>		<li>Account sharing is not allowed.</li>        <li>Impersonating staff is forbidden</li>	</ul></div><h3>Glitching/Hacking:</h3><div>	Exploiting bugs/glitches, attempting to hack/crack the site or its data, or changing site code is strictly prohibited. Any attempts 	will be met with severe punishment.	<br />	There is <i>Zero Tolerance</i> for planning attacks against other games anywhere on Shinobi-Chronicles. Any discussion of these topics 	is strictly forbidden and will be met with punishment as severe as the situation dictates.</div><h3>Manga Spoilers</h3><div>	As this is an anime/manga-themed game, it can be expected that most of the userbase follows various ongoing manga/anime series. 	Since many people for various reasons do not read the manga, but only watch the anime, posting spoilers of things that have not 	happened in the anime yet of a major ongoing series (Naruto, One Piece, My Hero Academia, Demon Slayer, etc) is not allowed as    it can significantly lessen the experience of watching the show.<br />	<br /></div><h3>Bots/macros/etc:</h3><div>	Bots, macros, or any other devices (hardware or software) that play the game for you, are prohibited. Any characters caught botting 	will receive a ban along with a stat cut.</div><h3>Links:</h3><div>	Linking to sites that violate any of these rules (e.g: sites with explicit content) is prohibited.<br>	Linking to sites that contain language unsuitable for SC is allowed provided a clear warning is provided in the post. Linking to 	sites that break any of the other rules or linking to sites that contain inappropriate language without providing a warning is 	strictly not allowed.</div><!--END_EDIT--></td></tr></table><?php
if ($layout->key == "new_geisha") {
    echo "</div></div>";
    $layout->renderFooter();
}
else {
    $layout->renderStaticPageFooter($player ?? null);
}



