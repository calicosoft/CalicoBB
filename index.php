<?php
# Session Initialization
session_start();
error_reporting(E_ALL);
# Load Configuration
require_once('./classes/db.class.php');
$db = new db();
# Load Modifications System
require_once('./classes/vqmod.class.php');
$vqmod = new VQMod();
$vqmod->useCache = true; // uses the cache
# Load super-class
require_once($vqmod->modCheck('./classes/calicobb.class.php'));
# Initiate super-class
$calicobb = new calicobb();
//$calicobb->config = db::$config;
$calicobb->startup();
# Load the other files.
require_once($vqmod->modCheck('./classes/forum.class.php'));
require_once($vqmod->modCheck('./classes/core.class.php'));
require_once($vqmod->modCheck('./classes/user.class.php'));
# Class Initialization
$forum = new forum();
$core = new core();
$user = new user();
# Set up the theme.
$_SESSION[db::$config['session_prefix'].'_theme'] = 'calico';
$html = file_get_contents($core->getCurrentThemeLocation().'global.html');
# Error Handling
set_error_handler(array("core", "errorHandler"));
register_shutdown_function(array("core", "shutDownFunction")); 
# Theme handling
$_SESSION[db::$config['session_prefix'].'_theme'] = 'calico';
# Set the timezone
date_default_timezone_set('Europe/London');
# Start up action
$act = $_GET['act'];
$id = $_GET['id'];
# Short tags
if(isset($_GET['topic'])){
	$act = 'topic';
	$id = intval($_GET['topic']);
}elseif(isset($_GET['forum'])){
	$act = 'forum';
	$id = intval($_GET['forum']);
}elseif(isset($_GET['post'])){
	$act = 'post';
	$id = intval($_GET['post']);
}elseif(isset($_GET['profile'])){
	$act = 'profile';
	$id = intval($_GET['profile']);
}elseif($act == 'index'){
	$act = '';
	unset($_SERVER['QUERY_STRING']);
}
# Addon System
if(file_exists($act.'.php')){
	require_once($act.'.php');
	exit();
}
# Safe Var
$id = intval($id);
# Proceed
$p = true;
# Check we're not banned, if we're logged in, that is...
if(isset($_SESSION[db::$config['session_prefix'].'_userid'])){
	if($core->banCheck($_SESSION[db::$config['session_prefix'].'_userid']) == true){
		$content = $core->errorMessage('banned');
		$title = 'Board Message';
		// do not proceed
		$p = false;
	}
}
# Do we have unread warnings?
if(db::$config['require_warnings_read'] == true AND isset($_SESSION[db::$config['session_prefix'].'_userid'])){
	// check for warnings
	$w = $core->checkWarnings($_SESSION[db::$config['session_prefix'].'_userid']);
	// do we have any?
	if($w['check'] == true){
		// yes, show content
		$content = $w['data'];
		// do not proceed
		$p = false;
	}
}
# Content Switching - but only if we can proceed
if($p == true){
switch($act) {
	/* Topic Related Stuff - show the topic, create new topic, reply, etc... */
	case 'topic':
		# Retrieve Content
		$data = $forum->viewTopic($id);
		$content = $data['html'];
		# Use Pretty Text
		$pretty = true;
		# Retrieve Title
		$title = $data['title'];
		# Set the Meta description, Up Url & Canon Url
		$desc = $data['desc'];
		$meta = '	<meta name="description" content="'.$desc.'" />
	<link rel="canonical" href="'.$data['canon'].'" />
	<link rel="up" href="'.$data['up'].'" />
	</head>';
		$html = str_replace('</head>',$meta,$html);
	break;
	case "post":
		# Retrieve Content
		$content .= $forum->showIndivPost($id,$_GET['tid']);
		# Set Title
		$title = 'Show Post ('.$id.')';
		# Use Pretty Text
		$pretty = true;
	break;
	case 'newtopic':
		# Retrieve Content
		$content .= $forum->newTopic($_GET['fid']);
		# Set Title
		$title = 'Post New Topic';
		# We must be logged in...
		$member_only = true;
		# We require the editor
		$use_editor = true;
	break;
	case 'reply':
		# Retrieve Content
		$content .= $forum->reply($_GET['tid']);
		# Set Title
		$title = 'Reply to Topic';
		# We must be logged in...
		$member_only = true;
		# We require the editor
		$use_editor = true;
	break;
	case "do_post":
		switch($_GET['type']){
			case "newtopic":
				# Retrieve Content
				$content .= $forum->newTopicSubmit($_GET['fid'],$_POST);
				# Set Title
				$title = 'Post New Topic';
			break;
			case "reply":
				# Retrieve Content
				$content .= $forum->replySubmit($_GET['tid'],$_POST);
				# Set Title
				$title = 'Reply to Topic';
			break;
			default:
				exit();
			break;
		# We must be logged in...
		$member_only = true;
		}
	break;
	case "report":
		# Retrieve Content
		$content .= $forum->reportPost($_GET['pid'],$_GET['tid']);
		# Set Title
		$title = 'Report Post ('.$_GET['pid'].')';
		# We must be logged in...
		$member_only = true;
	break;

	/* Forum Related Stuff - eg show forum */
	case 'forum':
		# Retrieve Content
		$data = $forum->viewForum($id);
		$content .= $data['html'];
		# Set Title
		$title = $data['title'];
		# Set the Meta description
		$desc = $data['desc'];
		$meta = '	<meta name="description" content="'.$desc.'" />
	<link rel="up" href="'.$data['up'].'" />
	</head>';
		$html = str_replace('</head>',$meta,$html);
	break;

	/* Private Messaging - eg view PMs, new PM, etc */
	case 'messages':
		# Get the user CP Menu
		$content .= $core->userControlMenu();
		# Retrieve Content
		$content .= $user->privateMessages();
		# Set Title
		$title = 'Private Messages';
		# We must be logged in...
		$member_only = true;
		# Replacement
		$content = str_replace('id="container" class="','id="container" class="userMenuRight ',$content);
		$content .= '<div style="clear:both;></div>';
	break;
	case 'viewmessage':
		# Get the user CP Menu
		$content .= $core->userControlMenu();
		# Retrieve Content
		$content .= $user->viewPrivateMessage($_GET['pm']);
		# Set Title
		$title = 'View Private Message';
		# We must be logged in...
		$member_only = true;
		# Use Pretty Text
		$pretty = true;
		# Replacement
		$content = str_replace('id="container" class="','id="container" class="userMenuRight ',$content);
		$content .= '<div style="clear:both;></div>';
	break;
	case 'insertpm':
		# Get the user CP Menu
		$content .= $core->userControlMenu();
		# Retrieve Content
		$content .= $user->insertPrivateMessage();
		# Set Title
		$title = 'Send Private Message';
		# We must be logged in...
		$member_only = true;
		# Replacement
		$content = str_replace('id="container" class="','id="container" class="userMenuRight ',$content);
		$content .= '<div style="clear:both;></div>';
	break;
	case 'deletemessages':
		# Retrieve Content
		$content .= $user->emptyPrivateMessages();
		# Set Title
		$title = 'Empty Inbox';
		# We must be logged in...
		$member_only = true;
	break;
	case 'sendmessage':
		# Get the user CP Menu
		$content .= $core->userControlMenu();
		# Retrieve Content
		$content .= $user->sendPrivateMessage();
		# Set Title
		$title = 'Send Private Message';
		# We must be logged in...
		$member_only = true;
		# We require the editor
		$use_editor = true;
		# Replacement
		$content = str_replace('id="container" class="','id="container" class="userMenuRight ',$content);
		$content .= '<div style="clear:both;></div>';
	break;

	/* Search Functions - eg search form, find users posts, results etc */
	case 'search':
		# Load the Search Class
		require_once($vqmod->modCheck('./classes/search.class.php'));
		$search = new search();
		# Content Type
		if($_GET['content_type'] == 'files' AND file_exists('downloads.php')){
			//exit('Sorry, downloads search is currently unavailable.');
			# Load the Downloads Class
			require_once($vqmod->modCheck('./classes/downloads.class.php'));
			$downloads = new downloads();
			# Switch between what we're doing
			switch($_GET['do']){
				case 'searchresults':
					# Retrieve Content
					$content .= $downloads->searchResults();
					# Set Title
					$title = 'Search Results';
				break;
				case 'findcontent':
					# Retrieve Content
					$content .= $downloads->findUsersFiles($_GET['uid']);
					# Set Title
					$title = 'Search Results';
				break;
				case 'new_content':
					# Retrieve Content
					$content .= $downloads->getNewFiles();
					# Set Title
					$title = 'Search Results';
				break;
			}
		}else{
			# Switch between what we're doing
			switch($_GET['do']){
				case 'searchresults':
					# Retrieve Content
					$content .= $search->searchResults();
					# Set Title
					$title = 'Search Results';
				break;
				case 'findcontent':
					# Retrieve Content
					$content .= $search->findUsersPosts($_GET['uid']);
					# Set Title
					$title = 'Search Results';
				break;
				case 'search_google':
					# Retrieve Content
					$content .= $search->googleSearch($_GET['q']);
					# Set Title
					$title = 'Search by Google';
				break;
				case 'new_content':
					# Retrieve Content
					$content .= $search->getNewPosts();
					# Set Title
					$title = 'Search Results';
				break;
				default:
					# Retrieve Content
					$content .= $search->search();
					# Set Title
					$title = 'Search Forums';
				break;
			}
		}
	break;

	/* Session Based Functions - eg register, login */
	case 'login':
		# Retrieve Content
		$content .= $user->login();
		# Set Title
		$title = 'Login to the Messageboard';
	break;
	case 'dologin':
		# Retrieve Content
		$content .= $user->doLogin($_POST);
		# Set Title
		$title = 'Login to the Messageboard';
	break;
	case 'logout':
		session_unset();
		session_destroy();
		# Retrieve Content
		$content .= $core->errorMessage("logged_out");
		header('Refresh:5 ; URL=index.php?from=LOGGED_OUT');
		# Set Title
		$title = 'Logout';
	break;
	case 'resetpassword':
		# Retrieve Content
		$content .= $user->resetPassword();
		# Set Title
		$title = 'Reset your password';
	break;
	case 'register':
		# Retrieve Content
		$content .= $user->registrationForm();
		# Set Title
		$title = 'Create New Account';
	break;
	case 'doregister':
		# Retrieve Content
		$content .= $user->doRegister($_POST);
		# Set Title
		$title = 'Create New Account';
	break;
	case "activate":
		# Retrieve Content
		$content .= $user->activateAccount();
		# Set Title
		$title = 'Activate Your Account';
	break;

	/* Profile & User CP Functions - eg view profile, edit profile, user cp */
	case 'user': /* Only for the SEO Urls */
	case 'profile':
		# Retrieve Content
		$content .= $user->viewProfile($id);
		# Set Title
		$title = strip_tags($core->getUsername($id));
		# Use Pretty Text
		$pretty = true;
	break;
	case 'myprofile':
		# Redirect
		$u = 'index.php?profile='.intval($_SESSION[db::$config['session_prefix'].'_userid']);
		header('Location: '.$u);
		exit();
		# We must be logged in...
		$member_only = true;
	break;
	case 'usercp':
		// Get the user CP Menu
		$content .= $core->userControlMenu();
		switch($_GET['do']){
			case 'editprofile':
				# Retrieve Content
				$content .= $user->editProfile();
				# Set Title
				$title = 'Edit Your Profile';
				# We must be logged in...
				$member_only = true;
				# We require the editor
				$use_editor = true;
			break;
			case 'do_editprofile':
				# Retrieve Content
				$content .= $user->doEditProfile($_POST);
				# Set Title
				$title = 'Edit Your Profile';
				# We must be logged in...
				$member_only = true;
			break;
			case 'avatar':
				# Retrieve Content
				$content .= $user->editAvatar();
				# Set Title
				$title = 'Change your display avatar';
				# We must be logged in...
				$member_only = true;
			break;
			case 'viewwarnings':
				# Retrieve Content
				$content .= $user->viewWarnings();
				# Set Title
				$title = 'View your warnings';
				# We must be logged in...
				$member_only = true;
			break;
			case 'board_settings':
				# Retrieve Content
				$content .= $user->boardSettings();
				# Set Title
				$title = 'Board Settings';
				# We must be logged in...
				$member_only = true;
			break;
			case 'password':
				# Retrieve Content
				$content .= $user->changePassword();
				# Set Title
				$title = 'Change your Password';
				# We must be logged in...
				$member_only = true;
			break;
			case 'email':
				# Retrieve Content
				$content .= $user->changeEmail();
				# Set Title
				$title = 'Change your email address';
				# We must be logged in...
				$member_only = true;
			break;
			default:
				# Retrieve Content
				$content .= $user->userControlPanel();
				# Set Title
				$title = 'User Control Panel';
				# We must be logged in...
				$member_only = true;
			break;
		}
		// Replacement
		$content = str_replace('id="container" class="','id="container" class="userMenuRight ',$content);
		$content .= '<div style="clear:both;></div>';
	break;
	
	/* Subscriptions */
	case 'subscriptions':
		# Init the subscriptions class
		require_once($vqmod->modCheck('./classes/subscriptions.class.php'));
		$subscriptions = new subscriptions();
		# Get the user CP Menu
		$content .= $core->userControlMenu();
		# We must be logged in...
		$member_only = true;
		# Get the action
		switch($_GET['do']){
			case 'pay':
				# Retrieve Content
				$content .= $subscriptions->paySubscription();
				# Set Title
				$title = 'Pay for Subscription';
				# Automatic Submission...
				$html = str_replace('<body>','<body onload="javascript:document.forms.paypal.submit();">',$html);
			break;
			case 'return':
				# Retrieve Content
				$content .= $subscriptions->returnFromProcessor();
				# Set Title
				$title = 'Pay for Subscription';
			break;
			case 'ipn':
				$subscriptions->ipn();
				exit();
			break;
			default:
				# Retrieve Content
				$content .= $subscriptions->subsHomepage();
				# Set Title
				$title = 'Pay for Subscription';
			break;
		}
		# Enable subs?
		if(db::$config['use_subscriptions'] == false){
			$content = $core->errorMessage('subs_disabled');
		}
	break;

	/* Moderation Functions - lock topic, warn user, edit post, etc */
	case "moderate":
		# Load Class File
		require_once($vqmod->modCheck('./classes/moderate.class.php'));
		# Init Class
		$moderate = new moderate();
		switch($_GET['do']){
			case 'move':
				# Retrieve Content
				$content .= $moderate->moveTopic($_GET['tid']);
				# Set Title
				$title = 'Move Topic';
			break;
			case 'merge':
				# Retrieve Content
				$content .= $moderate->mergeTopic($_GET['tid']);
				# Set Title
				$title = 'Merge Topics';
			break;
			case 'sticky':
				# Retrieve Content
				$content .= $moderate->sticky($_GET['tid']);
				# Set Title
				$title = 'Sticky Topic';
			break;
			case 'unsticky':
				# Retrieve Content
				$content .= $moderate->unsticky($_GET['tid']);
				# Set Title
				$title = 'Unsticky Topic';
			break;
			case 'lock':
				# Retrieve Content
				$content .= $moderate->lock($_GET['tid']);
				# Set Title
				$title = 'Lock Topic';
			break;
			case 'unlock':
				# Retrieve Content
				$content .= $moderate->unlock($_GET['tid']);
				# Set Title
				$title = 'Unlock Topic';
			break;
			case 'deletepost':
				# Retrieve Content
				$content .= $moderate->deletePost($_GET['pid']);
				# Set Title
				$title = 'Delete Post '.$_GET['pid'].'';
			break;
			case 'hard_delete_post':
				# Retrieve Content
				$content .= $moderate->hardDeletePost($_GET['pid']);
				# Set Title
				$title = 'Hard Delete Post '.$_GET['pid'].'';
			break;
			case "restorepost":
				# Retrieve Content
				$content .= $moderate->revertPost($_GET['pid']);
				# Set Title
				$title = 'Restore Post '.$_GET['pid'].'';
			break;
			case 'editpost':
				# Retrieve Content
				$content .= $moderate->editPost($_GET['pid']);
				# Set Title
				$title = 'Edit Post '.$_GET['pid'].'';
				# We require the editor
				$use_editor = true;
			break;
			case "warn":
				# Retrieve Content
				$content .= $moderate->warnUser();
				# Set Title
				$title = 'Warn User';
				# We require the editor
				$use_editor = true;
			break;
			case "dowarn":
				# Retrieve Content
				$content .= $moderate->doWarn($_POST);
				# Set Title
				$title = 'Warn User';
				# We require the editor
				$use_editor = true;
			break;
			case 'expirewarning':
				# Retrieve Content
				$content .= $moderate->expireWarning($_GET['wid']);
				# Set Title
				$title = 'Moderator: Expire Warning';
			break;
			case 'deletetopic':
				# Retrieve Content
				$content .= $moderate->deleteTopic($_GET['tid']);
				# Set Title
				$title = 'Delete Topic';
			break;
			case 'hard_delete_topic':
				# Retrieve Content
				$content .= $moderate->hardDeleteTopic($_GET['tid']);
				# Set Title
				$title = 'Hard Delete Topic';
			break;
			case 'viewbanned':
				# Retrieve Content
				$content .= $moderate->viewBanned();
				# Set Title
				$title = 'Banned Users';
			break;
			case 'unban':
				# Retrieve Content
				$content .= $moderate->unBanUser($_GET['uid']);
				# Set Title
				$title = 'Unban '.$_GET['username'].'';
			break;
			case 'banuser':
				# Retrieve Content
				$content .= $moderate->banUser($_GET['uid']);
				# Set Title
				$title = 'Ban '.$_GET['username'].'';
			break;
			case 'topictitle':
				# Retrieve Content
				$content .= $moderate->editTopicTitle($_GET['tid']);
				# Set Title
				$title = 'Edit Topic Title';
			break;
			case '---':
				# Retrieve Content
				$content .= $core->errorMessage('moderate_invalid_selection');
				# Set Title
				$title = 'Board Message';
			break;
			default:
				# Retrieve Content
				$content .= $moderate->moderatorControls();
				# Set Title
				$title = 'Moderator Control Panel';
			break;
		}
		# Javascript moderation...
		if($_GET['inside'] == '1'){
			exit(strip_tags($content));
		}
		# We must be logged in...
		$member_only = true;
	break;

	/* Forum Rules */
	case 'rules':
		# Retrieve Content
		$content .= '<table width="100%" class="forumIndex" class="expanded"> 
	<caption> 
		<span> 
			<b class="sControl"></b> 
			Forum Rules
		</span> 
	</caption>
	<tbody>
		<tr>
			<td>'.file_get_contents('rules.txt').'
		</tr>
	</tbody>
</table>
<div class="clippers"><div></div><span></span></div>';
		# Set Title
		$title = 'Forum Rules';
	break;
	
	/* Members List Begin */
	case "membersList":
		# Load classes
		require('./classes/memberlist.class.php');
		$myMembers = new myMembers();
		# Retrieve Content
		$content .= myMembers::memberList();
		# Set Title
		$title = 'View Memberslist';
	break;
	
	/* Core Page Selection */
	case "pageselection":
		# Retrieve Content
		$content .= $core->pageSelection($_GET['go'],$_GET['last'],$_GET['page_var']);
		# Set Title
		$title = 'Go To Page';
	break;
	/* Core Page Selection */
	
	/* Homepage Function - list all the forums */
	default:
		/* Homepage Function - list all the forums */
		# Retrieve Content
		$content .= $forum->forumHomepage();
		# Set Title
		$title = db::$config['site_name'] . ' - Powered by CalicoBB';
	break;
}
}
# Is this a members only function
if($member_only == true AND !isset($_SESSION[db::$config['session_prefix'].'_userid'])){
	$content = $core->errorMessage('not_logged_in');
}
# Replace Tag
$html = str_replace('{content}', $content, $html);
$html = str_replace('{title}', $title, $html);
$html = str_replace('{userbar}', $core->userBar(), $html);
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

########################
## Reputation Replace ##
########################
if(file_exists('classes/reputation.class.php')){
	if($act == 'post' OR $act == 'topic'){
		// get the class
		require_once($vqmod->modCheck('./classes/reputation.class.php'));
		// init
		$reputation = new reputation();
		// find, replace
		$rep['find'] = '/<!--{rep_([0-9]+)}-->/e';
		$rep['replace'] = "\$reputation->postRating('$1')";
		$html = preg_replace($rep['find'], $rep['replace'], $html);
		// we also need to add reputation.js & reputation.css
		$html = str_replace('</head>','<link href="reputation.css" rel="stylesheet" type="text/css" /> <script src="reputation.js" type="text/javascript"></script></head>',$html);
	}elseif($act == 'profile' OR $act == 'user'){
		// get the class
		require_once($vqmod->modCheck('./classes/reputation.class.php'));
		// init
		$reputation = new reputation();
		// find, replace
		$rep['find'] = '/<!--{rep_([0-9]+)}-->/e';
		$rep['replace'] = "\$reputation->userRating('$1')";
		$html = preg_replace($rep['find'], $rep['replace'], $html);
		// we also need to add reputation.js & reputation.css
		$html = str_replace('</head>','<link href="reputation.css" rel="stylesheet" type="text/css" /> <script src="reputation.js" type="text/javascript"></script></head>',$html);
	}
}
########################
##   Reputation END   ##
########################

########################
## Downloads Replace  ##
########################
if($act == 'profile' AND file_exists('downloads.php') OR $act == 'user' AND file_exists('downloads.php')){	
	// How many files?
	$uid = intval($id);
	$fq = "SELECT count(id) AS num_files FROM `dl_files` WHERE owner='$uid'";
	$fq = $calicobb->DB->query($fq);
	$fr = $fq->fetch_assoc();
	// Show
	$r = '<strong>Files Uploaded:</strong>
		'.$fr['num_files'].' &ndash; <a href="index.php?act=search&amp;do=findcontent&amp;uid='.$uid.'&amp;content_type=files">Find My Files</a><br />';
	// Replace
	$html = str_replace('<!-- DL -->',$r,$html);
}elseif(!isset($_GET['act']) AND file_exists('downloads.php')){	
	// Count how many files there are...
	$fq = "SELECT count(id) AS num_files FROM `dl_files`";
	$fq = $calicobb->DB->query($fq);
	$fr = $fq->fetch_assoc();
	// Show...
	$r = '<strong>Files: </strong>'.$fr['num_files'].'<br />';
	// Replace
	$html = str_replace('<!-- DL -->',$r,$html);
}
########################
## Downloads Replace  ##
########################

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
$_SESSION[db::$config['session_prefix'].'_theme'] = 'calico';
?>