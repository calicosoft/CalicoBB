<?php
# Session Initialization
session_start();
error_reporting();
# Load Configuration
require_once('./classes/db.class.php');
$db = new db();
# Load Modifications System
require_once('./classes/vqmod.class.php');
$vqmod = new VQMod();
$vqmod->useCache = true; // uses the cache
# Load the other files.;
require_once($vqmod->modCheck('./classes/core.class.php'));
require_once($vqmod->modCheck('./classes/downloads.class.php'));
# Class Initialization
$downloads = new downloads();
$core = new core();
$user = new user();
# Set up the theme.
$_SESSION[db::$config['session_prefix'].'_theme'] = 'calico';
$html = file_get_contents(core::getCurrentThemeLocation().'global.html');
# Error Handling
set_error_handler(array("core", "errorHandler"));
register_shutdown_function(array("core", "shutDownFunction")); 
# Set the timezone
date_default_timezone_set('Europe/London');
# Start up action
$do = $_GET['do'];
$id = intval($_GET['id']);
# Short tags
if(isset($_GET['file'])){
	$do = 'file';
	$id = intval($_GET['file']);
}
# DB
$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
# Safe Var
$id = $sql->real_escape_string($id);
# What GROUPS can sell products?
$GLOBALS['developers'] = array('1','2','6','7');
# Commission Percentage
$GLOBALS['commission'] = '0.85'; // what decimal should be paid out?
# Content Switching
switch($do){
	case 'file':
		# Retrieve Content
		$content = downloads::viewFile($id);
		# Retrieve Title
		$title = $_SESSION[''.db::$config['session_prefix'].'_filetitle'];
		# Use Pretty Text
		$pretty = true;
	break;
	case 'download':
		# Retrieve Content
		$content = downloads::downloadFile($id);
		# Retrieve Title
		$title = 'Download File';
		# We must be logged in...
		$member_only = true;
	break;
	case 'category':
		# Retrieve Content
		$content = downloads::viewCategory($id);
		# Retrieve Title
		$title = $_SESSION[''.db::$config['session_prefix'].'_cattitle'];
	break;
	case 'upload':
		# Retrieve Content
		$content = downloads::addFile();
		# Retrieve Title
		$title = 'Add New File';
		# We must be logged in...
		$member_only = true;
		# We require the editor
		$use_editor = true;
	break;
	
	/* Moderator Functions */
	case 'moderate':
		# Switch
		switch($_GET['mod']){
			# Edit File
			case 'editfile':
				# Retrieve Content
				$content = downloads::editFile();
				# Retrieve Title
				$title = 'Edit File';
				# We must be logged in...
				$member_only = true;
				# We require the editor
				$use_editor = true;
			break;
			# Delete File
			case 'deletefile':
				# Retrieve Content
				$content = downloads::deleteFile();
				# Retrieve Title
				$title = 'Delete File';
				# We must be logged in...
				$member_only = true;
				# We require the editor
				$use_editor = true;
			break;
			# Delete File
			case 'upload':
				# Retrieve Content
				$content = downloads::uploadNewFile();
				# Retrieve Title
				$title = 'Upload New File';
				# We must be logged in...
				$member_only = true;
				# We require the editor
				$use_editor = true;
			break;
		}
	break;
	
	/* Paid Functions */
	case 'buy':
		# Retrieve Content
		$content = downloads::buyFile($_GET['id']);
		# Retrieve Title
		$title = 'Buy';
		# We must be logged in...
		$member_only = true;
	break;
	case 'pay':
	case 'invoice':
		# Retrieve Content
		$content = downloads::viewInvoice($_GET['id'],$_GET['do']);
		# Retrieve Title
		$title = 'View Invoice #'.$_GET['id'];
		# We must be logged in...
		$member_only = true;
	break;
	case 'paynow':
		# Retrieve Content
		$content = downloads::payNow($_GET['id']);
		# Retrieve Title
		$title = 'Choose Payment Processor';
		# We must be logged in...
		$member_only = true;
	break;
	case 'payment':
		# Retrieve Content
		$content = downloads::paymentProcessorRedirect($_GET['id'],$_POST['payment']);
		# Retrieve Title
		$title = 'Choose Payment Processor';
		# We must be logged in...
		$member_only = true;
	break;
	case 'return':
		# Retrieve Content
		$content = downloads::paymentReturn($_GET['order_id']);
		# Retrieve Title
		$title = 'Thanks for your payment!';
		# We must be logged in...
		$member_only = true;
	break;
	case 'cancel_invoice':
		# Retrieve Content
		$content = downloads::cancelInvoice($_GET['id']);
		# Retrieve Title
		$title = 'Cancel Invoice';
		# We must be logged in...
		$member_only = true;
	break;
	case 'myorders':
		# Retrieve Content
		$content = downloads::myOrders();
		# Retrieve Title
		$title = 'My Orders';
		# We must be logged in...
		$member_only = true;
	break;
	case 'mycommission':
		# Retrieve Content
		$content = downloads::myCommission();
		# Retrieve Title
		$title = 'My Commission';
		# We must be logged in...
		$member_only = true;
	break;
	case 'ipn':
		downloads::payPalProcess();
		exit();
	break;
	
	default:
		# Retrieve Content
		$content = downloads::downloadHomepage($id);
		# Retrieve Title
		$title = ''.db::$config['site_name'].' Downloads Center';
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
# Do we need the editor?
if($use_editor == true){
	$head = '</title>
	<link href="editor/editor.css" rel="stylesheet" type="text/css" /> 
	<script src="editor/editor.js" type="text/javascript"></script>';
	$find = '</title>';
	$html = str_replace($find,$head,$html);
}
###### PRETTIFY ######
if($pretty = true){
	// Set what we need to replace
	$pretty_code = '<!-- Pretty Print -->
	<link href="prettyprint/prettify.css" type="text/css" rel="stylesheet" />
	<script type="text/javascript" src="prettyprint/prettify.js"></script>';
	// Replace the <body> tag
	$html = str_replace('<body>','<body onload="prettyPrint()">',$html);
}
// Replace It
$html = str_replace('<!--{prettify}-->',$pretty_code,$html);
###### PRETTIFY ######
# Output Content
echo $html;
?>