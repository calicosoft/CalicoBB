<?php
# Session Initialization
session_start();
# Required Files
require_once('./classes/openid_ui.class.php');
# Class
$openid_ui = new openid_ui();
# Error Handling
set_error_handler(array("core", "errorHandler"));
register_shutdown_function(array("core", "shutDownFunction")); 
# Set the timezone
date_default_timezone_set('Europe/London');
# Theme
$html = file_get_contents(core::getCurrentThemeLocation().'global.html');
# Head Switching
$head = '<link type="text/css" rel="stylesheet" href="openid/css/openid.css" />
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script>
	<script type="text/javascript" src="openid/js/openid-jquery.js"></script>
	<script type="text/javascript" src="openid/js/openid-en.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			openid.init(\'openid_identifier\');
			openid.setDemoMode(false); //Stops form submission for client javascript-only test purposes
		});
	</script>';
# Switch
switch($_GET['do']){
	/* The Verify Screen */
	case 'verify':
		/* Is there a specific page for this? */
		switch(urldecode($_GET['openid_identifier'])){
			case 'https://www.google.com/accounts/o8/id':
				# Retrieve Content
				$content = $openid_ui->googleLogin();
				# Set Title
				$title = 'Login with your Google account';
			break;
			case 'http://me.yahoo.com/':
				# Retrieve Content
				$content = $openid_ui->yahooLogin();
				# Set Title
				$title = 'Login with your Yahoo! account';
			break;
			default:
				# Retrieve Content
				$content = $openid_ui->normalLogin();
				# Set Title
				$title = 'Login with your account';
			break;
		}
	break;
	
	/* Add Account */
	case 'add':
		# Retrieve Content
		$content = $openid_ui->addAccount();
		# Set Title
		$title = 'Add new account';
	break;
	
	/* The Home Screen */
	default:
		# Retrieve Content
		$content = $openid_ui->homeScreen();
		# Set Title
		$title = 'Login with another account';
	break;
}
# Replace Tag
$html = str_replace('{content}', $content, $html);
$html = str_replace('{title}', $title, $html);
$html = str_replace('{userbar}', core::userBar(), $html);
$html = str_replace('{current_time}', date("G:i"), $html);
$html = str_replace('\\', '', $html);
$html = str_replace('</head>',$head.'</head>',$html);
# Output Content
echo $html;
?>