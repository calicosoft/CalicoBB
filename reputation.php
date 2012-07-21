<?php
# Session Initialization
session_start();
# Required Files
require_once('./classes/db.class.php');
require_once('./classes/core.class.php');
require_once('./classes/reputation.class.php');
# Class Initialization
$db = new db();
$core = new core();
$reputation = new reputation();
# Load Theme
$html = file_get_contents(core::getCurrentThemeLocation().'global.html');
# Start up action
$act = $_GET['do'];
$id = $_GET['id'];
# Content switching
switch($act){
	case 'vote':
		# Retrieve Content
		$content = reputation::votePost($_GET['pid'],$_GET['rating']);
		# Retrieve Title
		$title = 'Reputation';
	break;
	case 'whoRatedThisPost':
		# Retrieve Content
		$content = reputation::whoRatedThisPost($id);
		# Retrieve Title
		$title = 'Who Rated This Post?';
	break;
	default:
		exit('You cannot access reputation via this location, yet.');
	break;
}
# Check we're not banned, if we're logged in, that is...
if(isset($_SESSION[''.db::$config['session_prefix'].'_userid'])){
	if(core::banCheck($_SESSION[''.db::$config['session_prefix'].'_userid']) == true){
		$content = core::errorMessage('banned');
		$title = 'Board Message';
	}
}
# Is this a members only function
if($member_only == true AND !isset($_SESSION[''.db::$config['session_prefix'].'_userid'])){
	$content = core::errorMessage('not_logged_in');
}
# Replace Tag
$html = str_replace('{content}', $content, $html);
$html = str_replace('{title}', $title, $html);
$html = str_replace('{userbar}', core::userBar(), $html);
$html = str_replace('{current_time}', date("G:i"), $html);
$html = str_replace('\\', '', $html);
# Output Content
echo $html;
?>