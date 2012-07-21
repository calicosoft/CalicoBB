<?php
// Session Initialization
session_start();
error_reporting(0);
// Required Files
require_once('./classes/calicobb.class.php');
require_once('./classes/db.class.php');
require_once('./classes/core.class.php');
require_once('./classes/admin.class.php');
$db = new db();
# Initiate super-class
$calicobb = new calicobb();
$calicobb->config = db::$config;
$calicobb->startup();
// Class Initialization
$core = new core();
$forumAdmin = new forumAdmin();
// Load Theme
$html = file_get_contents("acp_theme/theme.html");
// If we're not logged in, get us to log in
if(!isset($_SESSION[''.db::$config['session_prefix'].'_ADMINLOGIN']) AND $_GET['act'] != 'login'){
	header("Location: admin.php?act=login");
	exit();
}
// Content switching
switch($_GET['act']){
	/* Main functions - eg debug & login */
	case 'login':
		// Get Content
		$content = forumAdmin::adminLogin();
		exit($content);
	break;
	case 'phpinfo':
		// Get Content
		$content = forumAdmin::phpInfo();
		exit($content);
	break;
	case 'debug':
		// Get Content
		$content = forumAdmin::debug();
		// Set Title
		$title = 'Debug & Server Information';
	break;
	case 'rebuild':
		// Get Content
		$content = forumAdmin::rebuild();
		// Set Title
		$title = 'Rebuild & Reset Counters';
	break;
	
	/* Forum Related Functions */
	case 'forums':
		// Get Content
		$content = forumAdmin::manageForums();
		// Set Title
		$title = 'Manage Forums';
	break;
	case 'editforum':
		// Get Content
		$content = forumAdmin::editForum($_GET['fid']);
		// Set Title
		$title = 'Edit Forum ('.$_GET['fid'].')';
	break;
	case 'newforum':
		// Get Content
		$content = forumAdmin::newForum();
		// Set Title
		$title = 'Create New Forum';
	break;
	case 'editforumpermissions':
		// Get Content
		$content = forumAdmin::forumPermissions($_GET['fid']);
		// Set Title
		$title = 'Edit Forum Permissions ('.$_GET['fid'].')';
	break;
	case 'deleteforum':
		// Get Content
		$content = forumAdmin::deleteForum($_GET['fid']);
		// Set Title
		$title = 'Delete Forum ('.$_GET['fid'].')';
	break;
	case 'viewdeletedtopics':
		// Get Content
		$content = forumAdmin::viewDeletedTopics();
		// Set Title
		$title = 'View Deleted Topics';
	break;
	case 'searchposts':
		// Get Content
		$content = forumAdmin::searchPosts();
		// Set Title
		$title = 'Search Posts';
	break;
	
	/* User Related Functions - eg edit user */
	case 'users':
		// Get Content
		$content = forumAdmin::manageUsers();
		// Set Title
		$title = 'User Management';
	break;
	case 'approveuser':
		// Get Content
		$content = forumAdmin::approveUser($_GET['uid']);
		// Set Title
		$title = 'Approve User '.$_GET['uid'].'';
	break;
	case 'edituser':
		// Get Content
		$content = forumAdmin::editUser($_GET['uid']);
		// Set Title
		$title = 'Edit User '.$_GET['uid'].'';
	break;
	case 'ipaddresses':
		// Get Content
		$content = forumAdmin::ipAddresses();
		// Set Title
		$title = 'Search IP Addresses';
	break;
	
	/* Mass Mail */
	case 'massmail':
		// Get Content
		$content = forumAdmin::massMail();
		// Set Title
		$title = 'Send a Mass Email';
	break;
	
	/* Group Management */
	case 'groups':
		// Get Content
		$content = forumAdmin::manageGroups();
		// Set Title
		$title = 'Group Management';
	break;
	case 'newgroup':
		// Get Content
		$content = forumAdmin::newGroup();
		// Set Title
		$title = 'Create New Member Group';
	break;
	case 'deletegroup':
		// Get Content
		$content = forumAdmin::deleteGroup($_GET['gid']);
		// Set Title
		$title = 'Delete Group '.$_GET['gid'];
	break;
	case 'editgroup':
		// Get Content
		$content = forumAdmin::editGroup($_GET['gid']);
		// Set Title
		$title = 'Edit Group '.$_GET['gid'];
	break;
	
	/* Warning Type stuff */
	case 'warningtypes':
		// Get Content
		$content = forumAdmin::warningTypes();
		// Set Title
		$title = 'Edit Warning Types';
	break;
	case 'addwarningtype':
		// Get Content
		$content = forumAdmin::addWarningType();
		// Set Title
		$title = 'Add Warning Types';
	break;
	
	/* Warning Type stuff */
	case 'subscription_packages':
		// Get Content
		$content = forumAdmin::subscriptionPackages();
		// Set Title
		$title = 'Subscription Packages';
	break;
	case 'addsubscriptionpackage':
		// Get Content
		$content = forumAdmin::addSubscriptionPackage();
		// Set Title
		$title = 'Add Subscription Package';
	break;
	case 'editsubscriptionpackage':
		// Get Content
		$content = forumAdmin::editSubscriptionPackage();
		// Set Title
		$title = 'Edit Subscription Package';
	break;
}
// Replace
$html = str_replace('{content}', $content, $html);
$html = str_replace('{title}', $title, $html);
// Send
echo $html;
?>