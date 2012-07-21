<?php
class user extends calicobb{
	public function login(){
		if(isset($_SESSION[db::$config['session_prefix'].'_loggedin'])){
			header('Location: index.php?from=LOGGED_IN');
		}else{
			// Get the template
			$template['path'] = core::getCurrentThemeLocation();
			$template['container_p'] = $template['path'].'container.html';
			$template['container'] = file_get_contents($template['container_p']);
			// Get Loginform template
			$template['form_p'] = $template['path'].'user_login_form.html';
			$template['form'] = file_get_contents($template['form_p']);
			// Setup content
			$content = '<form action="index.php?act=dologin" method="post">'.$template['container'].'</form>';
			$form = $template['form'];
			// Replace
			$form = str_replace('{r}',$_GET['r'],$form);
			$content = str_replace('{header_title}','Welcome Back - Please Login',$content);
			$content = str_replace('{content}',$form,$content);
		}
		return $content;
	}
	public function doLogin($data = array()){
		if($data['username'] == '' OR $data['password'] == ''){
			$content = core::errorMessage('empty_fields');
			return $content;
		}
		// Connect to database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Convert password to md5 hash 
		$password = $data['password'];
		$password = sha1(md5($password));
		// Secure Data
		$username = $sql->real_escape_string($data['username']);
		$password = $sql->real_escape_string($password);
		// Get user login data
		$lq = "SELECT * from `users` WHERE username='$username'";
		$lq = $sql->query($lq);
		$lr = $lq->fetch_assoc();
		// check open id
		if($lr['openid'] == 1){
			// No User found
			$content = core::errorMessage('incorrect_login');
			return $content;
		}
		// Check password matches...
		if($password != $lr['password']) {
			// Incorrect Password
			$content = core::errorMessage('incorrect_login');
			return $content;
		}elseif($lq->num_rows != 1){
			// No User found
			$content = core::errorMessage('incorrect_login');
			return $content;
		// Check we've activated
		}elseif($lr['group'] == 3){
			// No User found
			$content = core::errorMessage('login_not_activated');
			return $content;
		}else{
			// Update Last Login
			$uid = $lr['id'];
			$lo = date('Ymd');
			$q = "UPDATE `users` SET lastonline='$lo' WHERE id='$uid'";
			$sql->query($q);
			// Correct Password
			$_SESSION[db::$config['session_prefix'].'_username'] = $lr['username'];
			$_SESSION[db::$config['session_prefix'].'_userid'] = $lr['id'];
			$_SESSION[db::$config['session_prefix'].'_loggedin'] = 'yes';
			$_SESSION[db::$config['session_prefix'].'_domain'] = ''.$_SERVER['SERVER_NAME'].''.$_SERVER['PHP_SELF'].'';
			$_SESSION[db::$config['session_prefix'].'_forumadmin'] = $row['admin'];
			if(isset($data['bounce'])){
				$bounce = urldecode($data['bounce']);
				header('Location: index.php?'.$bounce.'');
			}
			// Well done :P We can redirect them.
			$content = core::errorMessage('login_success');
			header('Refresh: 10; Location: index.php?'.$bounce.'');
		}
		// Close all exits
		$sql->close();
		// Diversion...
		return $content;
	}
	public function resetPassword(){
		// Connect to database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Have we entered our data
		if(isset($_POST['username']) AND isset($_POST['email'])){
			// Make sure we're safe...
			$username = $sql->real_escape_string($_POST['username']);
			$email = $sql->real_escape_string($_POST['email']);
			// Check oor user exists
			$uq = "SELECT * FROM `users` WHERE email='$email' AND username='$username'";
			$uq = $sql->query($uq);
			if($uq->num_rows == 0){
				// show err
				$content = core::errorMessage('reset_no_user_found');
				return $content;
			}
			// Get user info
			$ur = $uq->fetch_assoc();
			$uid = $ur['id'];
			$username = $ur['username'];
			// Get new password
			$newpass = core::createPassword(9);
			$md5pass = sha1(md5($newpass));
			// Change password
			$pq = "UPDATE `users` SET password='$md5pass' WHERE id='$uid' LIMIT 1;";
			$sql->query($pq);
			// Generate Email
			$email_add = db::$config['email'];
			$url = 'http://'.$_SERVER['SERVER_NAME'].''.$_SERVER['SCRIPT_NAME'].'';
			$site = db::$config['site_name'];
			// Get the template
			$template['path'] = core::getCurrentThemeLocation();
			$template['container_p'] = $template['path'].'user_reset_pass_email.html';
			$template['container'] = file_get_contents($template['container_p']);
			// Set message
			$msg = $template['container'];
			// Replace
			$msg = str_replace('{username}',$username,$msg);
			$msg = str_replace('{newpass}',$newpass,$msg);
			$msg = str_replace('{url}',$url,$msg);
			$msg = str_replace('{site}',$site,$msg);
			// Headers
			$headers = "From: $site <$email_add>";
			$subject = 'Your New Password';
			mail($ur['email'], $subject, $msg, $headers);
			// Show confirmation message...
			$content = core::errorMessage('reset_password');
		}else{
			// Are we using RECAPTCHA?
			if(db::$config['use_recaptcha'] == true){
				// get it
				require_once('recaptcha.class.php');
				$recaptcha_form = recaptcha_get_html(db::$config['recaptcha_public_key']);
				// Get the template
				$template['path'] = core::getCurrentThemeLocation();
				$template['container_p'] = $template['path'].'user_register_recaptcha.html';
				$template['container'] = file_get_contents($template['container_p']);
				// replacements
				$recaptcha = $template['container'];
				$recaptcha = str_replace('{recaptcha_form}',$recaptcha_form,$recaptcha);
			}
			// Get the template
			$template['path'] = core::getCurrentThemeLocation();
			$template['container_p'] = $template['path'].'container.html';
			$template['container'] = file_get_contents($template['container_p']);
			// Get Loginform template
			$template['form_p'] = $template['path'].'user_reset_pass_form.html';
			$template['form'] = file_get_contents($template['form_p']);
			// Setup content
			$content = '<form action="" method="post">'.$template['container'].'</form>';
			$form = $template['form'];
			// Replace
			$form = str_replace('{recaptcha}',$recaptcha,$form);
			$content = str_replace('{header_title}','Reset Your Password',$content);
			$content = str_replace('{content}',$form,$content);
		}
		// Close
		$sql->close();
		// Return
		return $content;
	}
	public function registrationForm(){
		// Make sure we're not already logged in!
		if(isset($_SESSION[db::$config['session_prefix'].'_loggedin'])){
			header('Location: index.php?from=REGISTRATION');
		}else{
			// Are we using RECAPTCHA?
			if(db::$config['use_recaptcha'] == true){
				// get it
				require_once('recaptcha.class.php');
				$recaptcha_form = recaptcha_get_html(db::$config['recaptcha_public_key']);
				// Get the template
				$template['path'] = core::getCurrentThemeLocation();
				$template['container_p'] = $template['path'].'user_register_recaptcha.html';
				$template['container'] = file_get_contents($template['container_p']);
				// replacements
				$recaptcha = $template['container'];
				$recaptcha = str_replace('{recaptcha_form}',$recaptcha_form,$recaptcha);
			}
			// Are we using SolveMedia?
			if(db::$config['use_solvemedia'] == true){
				// get it
				require_once("solvemedia.class.php");
				$solvemedia_form = solvemedia_get_html(db::$config['solvemedia_public_key']);
				// Get the template
				$template['path'] = core::getCurrentThemeLocation();
				$template['container_p'] = $template['path'].'user_register_recaptcha.html'; // uses recaptcha form
				$template['container'] = file_get_contents($template['container_p']);
				// replacements
				$solvemedia = $template['container'];
				$solvemedia = str_replace('{recaptcha_form}',$solvemedia_form,$solvemedia);
			}
			// Bot check setup
			$_SESSION[db::$config['session_prefix'].'_botcheck_no'] = md5(rand(100,1000000000));
			$_SESSION[db::$config['session_prefix'].'_botcheck_yes'] = md5(rand(100,1000000000));
			// Get the template
			$template['path'] = core::getCurrentThemeLocation();
			$template['container_p'] = $template['path'].'container.html';
			$template['container'] = file_get_contents($template['container_p']);
			// registration form
			$template['container_p1'] = $template['path'].'user_register.html';
			$template['container1'] = file_get_contents($template['container_p1']);
			// Do the replacements
			$content = $template['container1'];
			$content = str_replace('{botcheck_yes}',$_SESSION[db::$config['session_prefix'].'_botcheck_yes'],$content);
			$content = str_replace('{botcheck_no}',$_SESSION[db::$config['session_prefix'].'_botcheck_no'],$content);
			$content = str_replace('{recaptcha}',$recaptcha,$content);
			$content = str_replace('{solvemedia}',$solvemedia,$content);
			// the final replacements
			$content = str_replace('{content}',$content,$template['container']);
			$content = str_replace('{header_title}','Register',$content);
		}
		return $content;
	}
	public function doRegister($data = array()){
		// Connect to database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Secure the data
		$username = strip_tags(trim($sql->real_escape_string($data['username'])));
		$email = strip_tags(trim($sql->real_escape_string($data['email'])));
		$password = strip_tags(trim($sql->real_escape_string($data['password'])));
		// Check no data is missing
		if(empty($username) OR empty($password) OR empty($email) OR empty($data['botcheck'])) {
			$content = core::errorMessage('empty_fields');
			return $content;
		}
		// Check the passwords match
		if($data['password'] != $data['password2']){
			$content = str_replace('{field}','passwords',core::errorMessage('register_fields_dont_match'));
			return $content;
		}
		// Check the emails match
		if($data['email'] != $data['email2']){
			$content = str_replace('{field}','email addresses',core::errorMessage('register_fields_dont_match'));
			return $content;
		}
		// Validate the email address
		require_once('./classes/validation.php');
		if(!validEmail($email)) {
			$content = core::errorMessage('register_invalid_email');
			return $content;
		}
		// StopForumSpam
		require_once('stopforumspam.class.php');
		$stopforumspam = new stopForumSpam();
		// Check
		$sfs = stopForumSpam::spamBotCheck($email,$_SERVER['REMOTE_ADDR'],$username);
		if($sfs['spambot'] == 1){
			$content = core::errorMessage('stop_forum_spam_match');
			$content = str_replace('{field}',$sfs['reason'],$content);
			return $content;
		}
		// ReCaptcha Check
		if(db::$config['use_recaptcha'] == true){
			require_once('recaptcha.class.php');
			$resp = recaptcha_check_answer(db::$config['recaptcha_private_key'],$_SERVER["REMOTE_ADDR"],$data["recaptcha_challenge_field"],$data["recaptcha_response_field"]);
			if(!$resp->is_valid) {
				// What happens when the CAPTCHA was entered incorrectly
				$content = core::errorMessage('register_recaptcha_incorrect');
				return $content;
			}
		}
		// Solve Media Check
		if(db::$config['use_solvemedia'] == true){
			// get file
			require_once("solvemedia.class.php");
			// set up keys
			$privkey = db::$config['solvemedia_private_key'];
			$hashkey = db::$config['solvemedia_hash'];
			// check it
			$solvemedia_response = solvemedia_check_answer($privkey,$_SERVER["REMOTE_ADDR"],$_POST["adcopy_challenge"],$_POST["adcopy_response"],$hashkey);
			if(!$solvemedia_response->is_valid){
				//handle incorrect answer
				$content = core::errorMessage('register_recaptcha_incorrect');
				return $content;
			}
		}
		// Anti-Bot Check
		if($data['botcheck'] != $_SESSION[db::$config['session_prefix'].'_botcheck_no']){
			$content = core::errorMessage('register_botcheck_incorrect');
			return $content;
		}
		// Strip NASTY Characters From Username
		$username = strip_tags($username);
		$username = preg_replace('/[^A-Za-z0-9 \s]/', '', $username); 
		$date = date("j F Y");
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		# Error Checking
		if(mysqli_connect_errno()){
			printf("Connect failed: %s\n", mysqli_connect_error());
			exit();
		}
		// Does this username exist?
		$q = "SELECT COUNT(id) AS userExists FROM `users` WHERE username='$username' LIMIT 1;";
		$q = $sql->query($q);
		$r = $q->fetch_assoc();
		// Does it exist?
		if($r['userExists'] != 0){
			$content = core::errorMessage('register_user_exists');
			return $content;
		}
		// Does the email address exist?
		$q = "SELECT COUNT(id) AS userExists FROM `users` WHERE email='$email' LIMIT 1;";
		$q = $sql->query($q);
		$r = $q->fetch_assoc();
		// Does it exist?
		if($r['userExists'] != 0){
			$content = core::errorMessage('register_email_exists');
			return $content;
		}
		// Register Account
		$password_do = $data['password'];
		$password_do = $sql->real_escape_string($password_do);
		$password = sha1(md5($password_do)); 
		$group = 1;
		// Do we require user email verification?
		if(db::$config['user_verification'] == true){
			$verified = 0;
			// Setup verification code - activation_code
			$verification_code = md5(sha1(md5($username)));
			// Send verification email
			$e_username = urlencode($username);
			$email_add = db::$config['email'];
			$url = 'http://'.$_SERVER['SERVER_NAME'].''.$_SERVER['SCRIPT_NAME'].'';
			$site = db::$config['site_name'];
			// Get the template
			$template['path'] = core::getCurrentThemeLocation();
			$template['container_p'] = $template['path'].'user_register_email.html';
			$template['container'] = file_get_contents($template['container_p']);
			// replacements
			$msg = $template['container'];
			$msg = str_replace('{username}',$username,$msg);
			$msg = str_replace('{url}',$url,$msg);
			$msg = str_replace('{e_username}',$e_username,$msg);
			$msg = str_replace('{verification_code}',$verification_code,$msg);
			$msg = str_replace('{site}',$site,$msg);
			// headers
			$headers = "From: $site <$email_add>";
			$subject = 'Activate your account';
			mail($email, $subject, $msg, $headers);
			$content = core::errorMessage('register_activate_account');
			$group = 3;
		}
		// IP address
		$ip = $_SERVER['REMOTE_ADDR'];
		// Date
		$date = time();
		// Insert to DB
		$q = "INSERT INTO `users`(`username`,`email`,`joined`,`password`,`group`,`activation_code`,`ip_address`)
			VALUES('$username','$email','$date','$password','$group','$verification_code','$ip')";
		$sql->query($q);
		// Success
		if(!isset($content)){
			$content = core::errorMessage('register_complete');
		}
		// Close conn
		$sql->close();
		// Show message on index.php?
		return $content;
	}
	public function activateAccount() {
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// ID and code
		$user = $sql->real_escape_string(urldecode($_GET['username']));
		$code = $sql->real_escape_string($_GET['code']);
		// Get the username
		$aq = "SELECT * FROM `users` WHERE username='$user' AND activation_code='$code' AND `group` = 3";
		$aq = $sql->query($aq);
		if($aq->num_rows == 0){
			$content = core::errorMessage('activate_account_not_found');
			return $content;
		}else{
			$ar = $aq->fetch_assoc();
			// log user in
			$_SESSION[db::$config['session_prefix'].'_username'] = $ar['username'];
			$_SESSION[db::$config['session_prefix'].'_userid'] = $ar['id'];
			$_SESSION[db::$config['session_prefix'].'_loggedin'] = 'yes';
			$_SESSION[db::$config['session_prefix'].'_domain'] = ''.$_SERVER['SERVER_NAME'].''.$_SERVER['PHP_SELF'].'';
			// Move user to registered
			$uid = $ar['id'];
			$q = "UPDATE `users` SET `group`='1' WHERE username='$user' AND id='$uid'";
			$sql->query($q);
			// Last online :)
			$lo = date('Ymd');
			$q = "UPDATE `users` SET lastonline='$lo' WHERE id='$uid'";
			// Show confirmation....
			$content = core::errorMessage('activation_success');
			// Redirect
			echo '<meta http-equiv="refresh" content="5;url=index.php" />';
		}
		// Close
		$sql->close();
		// Send to index.php
		return $content;
	}
	public function canUseMessaging($uid){
		// Connect to Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Set member ID
		$uid = $sql->real_escape_string($uid);
		// Get User info
		$uq = "SELECT `group` FROM `users` WHERE id='$uid' LIMIT 1";
		$uq = $sql->query($uq);
		$ur = $uq->fetch_assoc();
		// Get group info
		$gid = $ur['group'];
		$gq = "SELECT use_pm FROM `groups` WHERE id='$gid'";
		$gq = $sql->query($gq);
		$gr = $gq->fetch_assoc();
		// Can we use PM?
		if($gr['use_pm'] == 0){
			return false;
		}else{
			return true;
		}
		$sql->close();
	}
	public function privateMessages(){
		// Connect to Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Set member ID
		$uid = $sql->real_escape_string($_SESSION[db::$config['session_prefix'].'_userid']);
		// Can we use PM?
		if(user::canUseMessaging($uid) == false){
			$content = core::errorMessage('pm_disabled');
			return $content;
		}
		// Sent
		if(intval($_GET['f']) == 1){
			$s = ' (Sent Messages)';
		}
		// Are we sending, or viewing?
		if(intval($_GET['f']) == 1){
			$pqi = "`from`='$uid'";
			$f = 1;
		}else{
			$pqi = "`to`='$uid'";
		}
		// Build Query
		$pq = "SELECT * FROM private_messages WHERE $pqi ORDER BY id DESC LIMIT 25;";
		$pq = $sql->query($pq);
		// Are there results?
		if($pq->num_rows == 0){
			$messages = 'You do not have any private messages. <a href="index.php?act=sendmessage">Compose a Message</a>.';
		}
		// Get the template
		$template['path'] = core::getCurrentThemeLocation();
		$template['container_p'] = $template['path'].'user_pm_row.html';
		$template['container'] = file_get_contents($template['container_p']);
		// Loopy loopy
		while($pr = $pq->fetch_assoc()){
			// unread?
			if($pr['read'] == 0 AND $f != 1){
				$unread['pre'] = '<strong>';
				$unread['aft'] = '</strong>';
			}
			// to / from
			if($f == 1){
				$to = core::getUsername($pr['to']);
			}else{
				$to = core::getUsername($pr['from']);
			}
			// replacements
			$message = $template['container'];
			$message = str_replace('{pmid}',$pr['id'],$message);
			$message = str_replace('{pre_unread}',$unread['pre'],$message);
			$message = str_replace('{aft_unread}',$unread['aft'],$message);
			$message = str_replace('{subject}',htmlspecialchars($pr['subject']),$message);
			$message = str_replace('{username}',$to,$message);
			// row
			$messages .= $message;
			// last PM
			$last = $pr['id'];
			// unread
			unset($unread);
		}
		// delete pms not shown
		if($f != 1){
			$q = "DELETE FROM `private_messages` WHERE id > $last AND to='$uid'";
			$sql->query($q);
		}
		// Get the template
		$template['path'] = core::getCurrentThemeLocation();
		$template['container_p'] = $template['path'].'container.html';
		$template['container'] = file_get_contents($template['container_p']);
		// do the replacements
		$content = $template['container'];
		$content = str_replace('{s}',$s,$content);
		$content = str_replace('{content}',$messages,$content);
		$content = str_replace('{header_title}','Private Messages '. $s,$content);
		// Close conn, and show final destination
		$sql->close();
		// Send 'em to hell, or index.php. Whatever you prefer
		return $content;
	}
	public function viewPrivateMessage($mid){
		// Connect to database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Set member ID
		$uid = $sql->real_escape_string($_SESSION[db::$config['session_prefix'].'_userid']);
		// Can we use PM?
		if(user::canUseMessaging($uid) == false){
			$content = core::errorMessage('pm_disabled');
			return $content;
		}
		// Setup the PM id
		$mid = $sql->real_escape_string($mid);
		// Get the PM
		$mq = "SELECT * FROM `private_messages` WHERE id='$mid' LIMIT 1";
		$mq = $sql->query($mq);
		$mr = $mq->fetch_assoc();
		// Can we view this PM?
		if($mr['to'] != $_SESSION[db::$config['session_prefix'].'_userid'] AND $mr['from'] != $_SESSION[db::$config['session_prefix'].'_userid']){
			$content = core::errorMessage('pm_not_yours');
			return $content;
		}
		// DID WE FIND THE PM?
		if($mq->num_rows == 0){
			$content = core::errorMessage('pm_not_found');
			return $content;
		}
		// Set the PM as read
		$q = "UPDATE `private_messages` SET `read`='1' WHERE id='$mid'";
		$sql->query($q);
		// Show our content
		$msg = $mr['message'];
		// Get the template
		$template['path'] = core::getCurrentThemeLocation();
		$template['container_p'] = $template['path'].'container.html';
		$template['container'] = file_get_contents($template['container_p']);
		$template['container_p1'] = $template['path'].'user_pm_message.html';
		$template['container1'] = file_get_contents($template['container_p1']);
		// do the replacements
		$content = $template['container1'];
		$content = str_replace('{from_id}',$mr['from'],$content);
		$content = str_replace('{mid}',$mid,$content);
		$content = str_replace('{subject}',htmlspecialchars($mr['subject']),$content);
		$content = str_replace('{profile_url}',core::generateUrl('index.php?profile='.$mr['from'],'user',$mr['from'],strip_tags(core::getUsername($mr['from'])),NULL),$content);
		$content = str_replace('{from}',core::getUsername($mr['from']),$content);
		$content = str_replace('{message}',str_replace(array("\r\n", "\r", "\n"), "<br />",core::parsePost($mr['message'])),$content);
		// container replacements
		$content = str_replace('{content}',$content,$template['container']);
		$content = str_replace('{header_title}','Viewing Private Message - '.$mr['subject'],$content);
		// Close conn
		$sql->close();
		// Send 'er back
		return $content;
	}
	public function sendPrivateMessage(){
		// Connect to Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Set member ID
		$uid = $sql->real_escape_string($_SESSION[db::$config['session_prefix'].'_userid']);
		// Can we use PM?
		if(user::canUseMessaging($uid) == false){
			$content = core::errorMessage('pm_disabled');
			return $content;
		}
		// To & Reply
		$to = $sql->real_escape_string($_GET['to']);
		$reply = $sql->real_escape_string($_GET['reply']);
		// If "To" is set, get the username
		if(isset($_GET['to'])){
			$uq = "SELECT username FROM `users` WHERE id='$to' LIMIT 1;";
			$uq = $sql->query($uq);
			$ur = $uq->fetch_assoc();
		}
		// If we're replying, get the original PM
		if(isset($_GET['reply'])){
			$rq = "SELECT `message`,`subject`,`from` FROM `private_messages` WHERE (`id`='$reply' AND `to`='$uid') OR (`id`='$reply' AND `from`='$uid')";
			$rq = $sql->query($rq);
			$rr = $rq->fetch_assoc();
			// Set some vars...
			$message = '[quote author='.core::getUsername($rr['from']).']'.htmlspecialchars($rr['message']).'[/quote]';
		}
		// Get the template
		$template['path'] = core::getCurrentThemeLocation();
		$template['container_p'] = $template['path'].'container.html';
		$template['container'] = file_get_contents($template['container_p']);
		$template['container_p1'] = $template['path'].'user_pm_send.html';
		$template['container1'] = file_get_contents($template['container_p1']);
		// Replacements
		$content = $template['container1'];
		$content = str_replace('{username}',$ur['username'],$content);
		$content = str_replace('{subject}',htmlspecialchars($rr['subject']),$content);
		$content = str_replace('{message}',core::generateEditor('message',$message),$content);
		// container
		$content = str_replace('{content}',$content,$template['container']);
		$content = str_replace('{header_title}','Send Private Message',$content);
		// Close conn
		$sql->close();
		// Return
		return $content;
	}
	public function insertPrivateMessage(){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Set member ID
		$uid = $sql->real_escape_string($_SESSION[db::$config['session_prefix'].'_userid']);
		// Can we use PM?
		if(user::canUseMessaging($uid) == false){
			$content = core::errorMessage('pm_disabled');
			return $content;
		}
		// Safe sex is good for you...er, I mean variables. Yes, variables.
		$message = $_POST['message'];
		$to = $sql->real_escape_string($_POST['to']);
		$subject = str_replace('\'','',$sql->real_escape_string($_POST['subject']));
		// Get the user ID for the "to" person
		$tq = "SELECT id FROM `users` WHERE username='$to' LIMIT 1";
		$tq = $sql->query($tq);
		$tr = $tq->fetch_assoc();
		// Does he exist? Or she, to be politically correct...
		if($tq->num_rows == 0){
			$content = core::errorMessage('pm_user_not_found');
			return $content;
		}
		// Now we can send the PM...
		$sendpm = user::insertNewPrivateMessage($tr['id'],$subject,$message);
		// Did it send?
		if($sendpm == 1){
			$content = core::errorMessage('pm_sent');
		}else{
			$content = core::errorMessage('pm_failed');
		}
		// Close
		$sql->close();
		// Return
		return $content;
	}
	public function insertNewPrivateMessage($to,$subject,$message){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Escape strings
		$from = $sql->real_escape_string($_SESSION[db::$config['session_prefix'].'_userid']);
		$to = intval($sql->real_escape_string($to));
		$subject = $sql->real_escape_string($subject);
		$message = $sql->real_escape_string($message);
		// Set the date
		$date = time();
		// Add to db
		$q = "INSERT INTO `private_messages`(`to`,`from`,`subject`,`message`,`dateline`,`read`) 
		 VALUES('$to','$from','$subject','$message','$date','0')";
		if(!$sql->query($q)){
			return 0;
		}else{
			// Send er back
			return 1;
		}
	}
	public function emptyPrivateMessages(){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Set the user
		$uid = $_SESSION[db::$config['session_prefix'].'_userid'];
		// Delete
		$q = "DELETE FROM private_messages WHERE `to`='$uid'";
		$sql->query($q);
		// Show well done :)
		$content = core::errorMessage('pm_deleted');
		// Close
		$sql->close();
		// Return
		return $content;
	}
	public function viewProfile($uid){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Secure the uid
		$uid = $sql->real_escape_string(intval($uid));
		// Can we view user profiles?
		$cuid = $_SESSION[db::$config['session_prefix'].'_userid'];
		// get current user's group
		$cuq = "SELECT `group` FROM `users` WHERE id='$cuid' LIMIT 1;";
		$cuq = $sql->query($cuq);
		$cur = $cuq->fetch_assoc();
		$gid = $cur['group'];
		// no group
		if($cuq->num_rows == 0){
			$gid = 5;
		}
		// get group perm
		$gq = "SELECT view_profile FROM `groups` WHERE id='$gid' LIMIT 1";
		$gq = $sql->query($gq);
		$gr = $gq->fetch_assoc();
		// Can we view profiles?
		if($gr['view_profile'] == 0){
			$content = core::errorMessage('profile_disabled');
			return $content;
		}
		// Now get the profile we're trying to view..
		$uq = "SELECT * FROM `users` WHERE id='$uid' LIMIT 1;";
		$uq = $sql->query($uq);
		$ur = $uq->fetch_assoc();
		// Is the user found?
		if($uq->num_rows == 0){
			$content = core::errorMessage('profile_not_found');
			header("HTTP/1.0 404 Not Found");
			return $content;
		}
		// Friendly URL Redirect
		if(db::$config['seo_urls'] == true){
			if($_GET['friendly_url_title'] != core::friendlyTitle($ur['username']) OR $_GET['friendly_url_used'] != 1){
				// Put it together
				$new_url = core::generateUrl('index.php?profile='.$uid,'user',$uid,$ur['username'],NULL);
				// Redirect
				header("HTTP/1.1 301 Moved Permanently");
				header("Location: $new_url");
				exit();
			}
		}
		// Avatar
		$size = '60';
		$grav_url = 'http://www.gravatar.com/avatar/'.md5(strtolower($ur['email'])).'?s='.$size;
		// Gender
		switch($ur['gender']){
			case 'm':
				$gender = 'Male';
			break;
			case 'f':
				$gender = 'Female';
			break;
			default:
				$gender = 'Unknown';
			break;
		}
		// About Me
		if(!empty($ur['aboutme'])){
			$aboutme = htmlspecialchars($ur['aboutme']);
		}else{
			$aboutme = '<em>This user has not set an About Me yet</em>';
		}
		// Location
		if(!empty($ur['location'])){
			$location = $ur['location'];
		}else{
			$location = 'Unknown';
		}
		// Website
		if(!empty($ur['website'])){
			$website = '<a href="'.$ur['website'].'" target="_blank">'.htmlspecialchars($ur['website']).'</a>';
		}else{
			$website = '<em>This user has not set a website yet.</em>';
		}
		// Get last active
		$laq = "SELECT time,location FROM `user_session` WHERE uid='$uid' LIMIT 1;";
		$laq = $sql->query($laq);
		$la = $laq->fetch_assoc();
		// Last active
		if(intval($la['time']) == 0){
			$lat = '<em>Never</em>';
		}else{
			$lat = $la['time'];
			$lat = date("j F Y, G:i",$lat);
			$where = '(<em><a rel="nofollow" href="index.php?'.str_replace('friendly_url_title','f_u_t',str_replace('friendly_url_used','f_u_u',$la['location'])).'">Where?</a></em>)';
		}
		// Can we moderate this user?
		if(!empty($_SESSION[db::$config['session_prefix'].'_userid'])){
			// set our UID
			$muid = $_SESSION[db::$config['session_prefix'].'_userid'];
			// get group
			$muq = "SELECT `group` FROM `users` WHERE id='$muid' LIMIT 1;";
			$muq = $sql->query($muq);
			$mur = $muq->fetch_assoc();
			// set gid
			$mgid = $mur['group'];
			// get group
			$mgq = "SELECT supermod,administrator,access_modcp FROM `groups` WHERE id='$gid' LIMIT 1";
			$mgq = $sql->query($mgq);
			$mgr = $mgq->fetch_assoc();
			// can we moderate?
			if($mgr['supermod'] == 1 OR $mgr['administrator'] == 1 OR $mgr['access_modcp'] == 1){
				// we can moderate - show some stuff
				$moderation = '<strong>Moderation:</strong>
					<a href="index.php?act=moderate&amp;do=banuser&amp;uid='.$uid.'">Ban User</a> ::
					<a href="index.php?act=moderate&amp;do=warn&amp;uid='.$uid.'&amp;profile_warning=1">Warn User</a>';
			}
		}
		// Get the template
		$template['path'] = core::getCurrentThemeLocation();
		$template['container_p'] = $template['path'].'container.html';
		$template['container'] = file_get_contents($template['container_p']);
		$template['container_p1'] = $template['path'].'user_profile.html';
		$template['container1'] = file_get_contents($template['container_p1']);
		// Replacements
		$content .= $template['container1'];
		$content = str_replace('{username}',htmlspecialchars($ur['username']),$content);
		$content = str_replace('{grav_url}',$grav_url,$content);
		$content = str_replace('{size}',$size,$content);
		$content = str_replace('{username_html}',core::getUsername($ur['id']),$content);
		$content = str_replace('{group}',core::getGroup($ur['group']),$content);
		$content = str_replace('{gender}',htmlspecialchars($gender),$content);
		$content = str_replace('{location}',htmlspecialchars($location),$content);
		$content = str_replace('{active}',$lat,$content);
		$content = str_replace('{where}',$where,$content);
		$content = str_replace('{joined}',date("j F Y",$ur['joined']),$content);
		$content = str_replace('{postcount}',$ur['postcount'],$content);
		$content = str_replace('{uid}',$ur['id'],$content);
		$content = str_replace('{website}',$website,$content);
		$content = str_replace('{aboutme}',$aboutme,$content);
		$content = str_replace('{moderation}',$moderation,$content);
		$content = str_replace('{signature}',nl2br(core::parsePost($ur['signature'])),$content);
		// Replace on the container.
		$content = str_replace('{content}',$content,$template['container']);
		$content = str_replace('{header_title}',$ur['username'] . '&#39;s profile',$content);
		// Can we see warnings?
		$y = 1;
		if($gr['administrator'] == 1 OR $gr['supermod'] == 1 OR $gr['access_modcp'] == 1){
			// Get all warnings
			$uid = $ur['id'];
			$wq = "SELECT * FROM `warnings` WHERE uid='$uid' ORDER BY id DESC";
			$wq = $sql->query($wq);
			// Set up points
			$points = 0;
			// Get the template
			$template['container_p_w'] = $template['path'].'user_profile_warnings_row.html';
			$template['container_w'] = file_get_contents($template['container_p_w']);
			// Loop 'em, boys
			while($wr = $wq->fetch_assoc()){
				// Check post exists
				if($wr['pid'] == 0){
					$post = 'Profile';
				}else{
					$pid = $wr['pid'];
					// check the post hasn't been deleted
					$pq = "SELECT count(*) AS postcount FROM `posts` WHERE id='$pid' AND deleted=0";
					$pq = $sql->query($pq);
					$pr = $pq->fetch_assoc();
					// has it?
					if($pr['postcount'] == 0){
						$post = 'Post Deleted';
					}else{
						$post = '<a href="index.php?post='.$pid.'&amp;tid='.$wr['tid'].'">View Post</a>';
					}
				}
				// Check status
				$time = time();
				if($wr['revoked'] == 1 OR $wr['expires'] < $time){
					$status = 'Expired';
					$do_points = 0;
				}else{
					$status = 'Active (Expires: '.date("j/m/y, G:i",$wr['expires']).')<!--<br />-->
					<a href="index.php?act=moderate&amp;do=expirewarning&wid='.$wr['id'].'&amp;uid='.$uid.'">Revoke</a>';
					$do_points = $wr['points'];
				}
				// Replacements
				$warning = $template['container_w'];
				$warning = str_replace('{title}',$wr['title'],$warning);
				$warning = str_replace('{points}',$wr['points'],$warning);
				$warning = str_replace('{post}',$post,$warning);
				$warning = str_replace('{status}',$status,$warning);
				// Add row
				$warnings .= $warning;
				// Increase points
				$points = $points + $do_points;
			}
			// No results
			if($wq->num_rows == 0){
				$warnings = 'This user does not have any active warnings';
			}
			// Get the template
			$template['container_p'] = $template['path'].'container.html';
			$template['container'] = file_get_contents($template['container_p']);
			$template['container_p1'] = $template['path'].'user_profile_warnings_container.html';
			$template['container1'] = file_get_contents($template['container_p1']);
			// Replacements
			$wcontent = $template['container1'];
			$wcontent = str_replace('{warnings}',$warnings,$wcontent);
			// Into container
			$content .= $template['container'];
			$content = str_replace('{content}',$wcontent,$content);
			$content = str_replace('{header_title}',$ur['username'] .'&#39;s Warnings',$content);
			$content = str_replace('{uid}',$ur['id'],$content);
		}
		// Close conn
		$sql->close();
		// Return
		return $content;
	}
	public function editProfile(){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Current user ID
		$uid = $_SESSION[db::$config['session_prefix'].'_userid'];
		// Get profile...
		$uq = "SELECT * FROM `users` WHERE id='$uid'";
		$uq = $sql->query($uq);
		$ur = $uq->fetch_assoc();
		// Is the user found?
		if($uq->num_rows == 0){
			$content = core::errorMessage('profile_not_found');
			header("HTTP/1.0 404 Not Found");
			return $content;
		}
		// Gender
		if($ur['gender'] == 'm'){
			$gender['m'] = 'selected="selected" ';
		}elseif($ur['gender'] == 'f'){
			$gender['f'] = 'selected="selected" ';
		}else{
			$gender['u'] = 'selected="selected" ';
		}
		$gender = '<select id="gender" name="gender">
			<option value="m" '.$gender['m'].'>Male</option>
			<option value="f" '.$gender['f'].'>Female</option>
			<option value="u" '.$gender['u'].'>Unknown</option>
		</select>';
		// Get the template
		$template['path'] = core::getCurrentThemeLocation();
		$template['container_p'] = $template['path'].'container.html';
		$template['container'] = file_get_contents($template['container_p']);
		$template['container_p1'] = $template['path'].'user_profile_edit.html';
		$template['container1'] = file_get_contents($template['container_p1']);
		// Do replacements
		$content = $template['container1'];
		$content = str_replace('{username}',$ur['username'],$content);
		$content = str_replace('{location}',$ur['location'],$content);
		$content = str_replace('{gender}',$gender,$content);
		$content = str_replace('{website}',$ur['website'],$content);
		$content = str_replace('{aboutme}',$ur['aboutme'],$content);
		$content = str_replace('{signature}',core::generateEditor('signature',$ur['signature']),$content);
		// container
		$content = str_replace('{content}',$content,$template['container']);
		$content = str_replace('{header_title}','Edit Profile',$content);
		// Close
		$sql->close();
		// Send er back
		return $content;
	}
	public function doEditProfile($data = array()){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Encrypt stuff first...
		$uid = $sql->real_escape_string($_SESSION[db::$config['session_prefix'].'_userid']);
		$location = strip_tags($sql->real_escape_string($data['location']));
		$gender = strip_tags($sql->real_escape_string($data['gender']));
		$website = strip_tags($sql->real_escape_string($data['website']));
		$signature = strip_tags($sql->real_escape_string($data['signature']));
		$aboutme = strip_tags($sql->real_escape_string($data['aboutme']));
		//$signature = substr($signature,0,150);
		// Query
		$q = "UPDATE `users` SET location='$location', gender='$gender', website='$website', signature='$signature', aboutme='$aboutme' WHERE id='$uid'";
		$sql->query($q);
		// Content
		$content = core::errorMessage('profile_updated');
		// Return
		return $content;
	}
	public function viewWarnings(){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Secure UID
		$uid = $_SESSION[db::$config['session_prefix'].'_userid'];
		// Get all warnings
		$wq = "SELECT * FROM `warnings` WHERE uid='$uid' ORDER BY id DESC";
		$wq = $sql->query($wq);
		// Set up points
		$points = 0;
		// Get the template
		$template['path'] = core::getCurrentThemeLocation();
		$template['container_p'] = $template['path'].'container.html';
		$template['container'] = file_get_contents($template['container_p']);
		$template['container_p1'] = $template['path'].'user_profile_warnings_row.html';
		$template['container1'] = file_get_contents($template['container_p1']);
		// Loop 'em, boys
		while($wr = $wq->fetch_assoc()){
			// Check post exists
			if($wr['pid'] == 0){
				$post = 'Profile';
			}else{
				$pid = $wr['pid'];
				// check the post hasn't been deleted
				$pq = "SELECT count(*) AS postcount FROM `posts` WHERE id='$pid' AND deleted=0";
				$pq = $sql->query($pq);
				$pr = $pq->fetch_assoc();
				// has it?
				if($pr['postcount'] == 0){
					$post = 'Post Deleted';
				}else{
					$post = '<a href="index.php?post='.$pid.'&amp;tid='.$wr['tid'].'">View Post</a>';
				}
			}
			// Check status
			$time = time();
			if($wr['revoked'] == 1 OR $wr['expires'] < $time){
				$status = 'Expired';
				$do_points = 0;
			}else{
				$status = 'Active (Expires: '.date("j/m/y, G:i",$wr['expires']).')<!--<br />-->
				<a href="index.php?act=moderate&amp;do=expirewarning&wid='.$wr['id'].'&amp;uid='.$uid.'">Revoke</a>';
				$do_points = $wr['points'];
			}
			// Replacements
			$warning = $template['container1'];
			$warning = str_replace('{title}',$wr['title'],$warning);
			$warning = str_replace('{points}',$wr['points'],$warning);
			$warning = str_replace('{post}',$post,$warning);
			$warning = str_replace('{status}',$status,$warning);
			// Add row
			$warnings .= $warning;
			// Increase points
			$points = $points + $do_points;
		}
		// No results
		if($wq->num_rows == 0){
			$warnings = 'Woohoo! You do not have any active warnings.';
		}
		// Get the template
		$template['path'] = core::getCurrentThemeLocation();
		$template['container_p2'] = $template['path'].'user_profile_warnings_container.html';
		$template['container2'] = file_get_contents($template['container_p2']);
		// Replacements
		$content .= $template['container2'];
		$content = str_replace('{warnings}',$warnings,$content);
		// remove warn user button
		$f = '<br /><br />
			<a class="smallButton" href="index.php?act=moderate&amp;do=warn&uid={uid}&amp;profile_warning=1">Warn User</a>';
		$content = str_replace($f,'<div style="clear:both;"></div>',$content);
		// container
		$content = str_replace('{content}',$content,$template['container']);
		$content = str_replace('{header_title}','View Moderation History',$content);
		// Close conn
		$sql->close();
		// Return
		return $content;
	}
	public function editAvatar(){
		// Get the template
		$template['path'] = core::getCurrentThemeLocation();
		$template['container_p'] = $template['path'].'container.html';
		$template['container'] = file_get_contents($template['container_p']);
		// Secondary content
		// Get the template
		$template['container_p2'] = $template['path'].'user_profile_avatar_edit.html';
		$template['container2'] = file_get_contents($template['container_p2']);
		// replace
		$content = $template['container'];
		$content = str_replace('{content}',$template['container2'],$content);
		$content = str_replace('{header_title}','Edit Avatar',$content);
		// send er back
		return $content;
	}
	public function boardSettings(){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Secure UID
		$uid = $_SESSION[db::$config['session_prefix'].'_userid'];
		// have we submitted?
		if(isset($_POST['submit'])){
			// secure it
			$posts_per_page = $sql->real_escape_string($_POST['posts_per_page']);
			$topics_per_page = $sql->real_escape_string($_POST['topics_per_page']);
			$view_signatures = $sql->real_escape_string($_POST['view_signatures']);
			$view_avatars = $sql->real_escape_string($_POST['view_avatars']);
			$allow_pms = $sql->real_escape_string($_POST['allow_pms']);
			$allow_emails = $sql->real_escape_string($_POST['allow_emails']);
			$advanced_editor = $sql->real_escape_string($_POST['advanced_editor']);
			// 1s and 0s
			if($view_signatures != 1){
				$view_signatures = 0;
			}
			if($view_avatars != 1){
				$view_avatars = 0;
			}
			if($allow_pms != 1){
				$allow_pms = 0;
			}
			if($allow_emails != 1){
				$allow_emails = 0;
			}
			if($advanced_editor != 1){
				$advanced_editor = 0;
			}
			// check its not greater than 50
			if($posts_per_page > 51 OR $topics_per_page > 51){
				$content = core::errorMessage('usercp_too_large_pp');
				return $content;
			}
			// We can save it :)
			$sq = "UPDATE `users` SET posts_per_page='$posts_per_page', topics_per_page='$topics_per_page', view_signatures='$view_signatures',
			 view_avatars='$view_avatars', allow_pms='$allow_pms', allow_emails='$allow_emails', advanced_editor='$advanced_editor' WHERE id='$uid'";
			$sql->query($sq);
			// Show success
			$content = core::errorMessage('usercp_updated');
		}else{
			// get current settings
			$uq = "SELECT * FROM `users` WHERE id='$uid' LIMIT 1;";
			$uq = $sql->query($uq);
			$ur = $uq->fetch_assoc();
			// Setup the settings
			// posts per page
			$pp = 5;
			while($pp <= 50){
				$option['posts_per_page'] .= '<option value="'.$pp.'"';
				if($pp == $ur['posts_per_page']){
					$option['posts_per_page'] .= ' selected="selected"';
				}
				$option['posts_per_page'] .= '>'.$pp.'</option>';
				$pp = $pp + 5;
			}
			// topics per page
			$tp = 5;
			while($tp <= 50){
				$option['topics_per_page'] .= '<option value="'.$tp.'"';
				if($tp == $ur['topics_per_page']){
					$option['topics_per_page'] .= ' selected="selected"';
				}
				$option['topics_per_page'] .= '>'.$tp.'</option>';
				$tp = $tp + 5;
			}
			// show sig
			if($ur['view_signatures'] == 1){
				$option['view_signatures'] = 'checked="checked"';
			}
			// show avatar
			if($ur['view_avatars'] == 1){
				$option['view_avatars'] = 'checked="checked"';
			}
			// allow pms
			if($ur['allow_pms'] == 1){
				$option['allow_pms'] = 'checked="checked"';
			}
			// allow emails
			if($ur['allow_emails'] == 1){
				$option['allow_emails'] = 'checked="checked"';
			}
			// advanced editor
			if($ur['advanced_editor'] == 1){
				$option['advanced_editor'] = 'checked="checked"';
			}
			// Get the template
			$template['path'] = core::getCurrentThemeLocation();
			$template['container_p'] = $template['path'].'container.html';
			$template['container'] = file_get_contents($template['container_p']);
			$template['container_p1'] = $template['path'].'user_board_settings.html';
			$template['container1'] = file_get_contents($template['container_p1']);
			// Replacements
			$content = $template['container1'];
			$content = str_replace('{posts_per_page}',$option['posts_per_page'],$content);
			$content = str_replace('{topics_per_page}',$option['topics_per_page'],$content);
			$content = str_replace('{view_signatures}',$option['view_signatures'],$content);
			$content = str_replace('{view_avatars}',$option['view_avatars'],$content);
			$content = str_replace('{allow_pms}',$option['allow_pms'],$content);
			$content = str_replace('{allow_emails}',$option['allow_emails'],$content);
			$content = str_replace('{advanced_editor}',$option['advanced_editor'],$content);
			// container
			$content = str_replace('{content}',$content,$template['container']);
			$content = str_replace('{header_title}','Edit Board Settings',$content);
		}
		// close
		$sql->close();
		// back
		return $content;
	}
	public function changePassword(){
		// have we submitted?
		if(isset($_POST['submit'])){
			// Connect To Database
			$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
			// Secure UID
			$uid = $_SESSION[db::$config['session_prefix'].'_userid'];
			// Get current pass
			$uq = "SELECT password FROM `users` WHERE id='$uid'";
			$uq = $sql->query($uq);
			$ur = $uq->fetch_assoc();
			// Does the current pass match?
			$current_password = $_POST['current_password'];
			if(sha1(md5($current_password)) != $ur['password']){
				$content = core::errorMessage('current_password_mismatch');
				return $content;
			}
			// Do the two password match?
			if($_POST['password1'] != $_POST['password2']){
				$content = core::errorMessage('passwords_mismatch');
				return $content;
			}
			// Secure
			$pass = sha1(md5($sql->real_escape_string($_POST['password2'])));
			// update
			$q = "UPDATE `users` SET password='$pass' WHERE id='$uid'";
			$sql->query($q);
			// success
			$content = core::errorMessage('password_changed');
			//session_unset();
			//session_destroy();
			// Close
			$sql->close();
		}else{
			// Get the template
			$template['path'] = core::getCurrentThemeLocation();
			$template['container_p'] = $template['path'].'container.html';
			$template['container'] = file_get_contents($template['container_p']);
			// Secondary content
			// Get the template
			$template['container_p2'] = $template['path'].'user_password.html';
			$template['container2'] = file_get_contents($template['container_p2']);
			// replace
			$content = $template['container'];
			$content = str_replace('{content}',$template['container2'],$content);
			$content = str_replace('{header_title}','Change Your Password',$content);
		}
		return $content;
	}
	public function changeEmail(){
		// have we submitted?
		if(isset($_POST['submit'])){
			// Connect To Database
			$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
			// Secure UID
			$uid = $_SESSION[db::$config['session_prefix'].'_userid'];
			// Get current pass
			$uq = "SELECT password FROM `users` WHERE id='$uid'";
			$uq = $sql->query($uq);
			$ur = $uq->fetch_assoc();
			// Does the current pass match?
			$current_password = $_POST['current_password'];
			if(sha1(md5($current_password)) != $ur['password']){
				$content = core::errorMessage('current_password_mismatch');
				return $content;
			}
			// Do the two password match?
			if($_POST['email1'] != $_POST['email2']){
				$content = core::errorMessage('email_mismatch');
				return $content;
			}
			// Secure
			$email = $sql->real_escape_string($_POST['email1']);
			// Setup verification code - activation_code
			$verification_code = sha1(md5(sha1(md5($username))));
			// update
			$q = "UPDATE `users` SET `email`='$email', `activation_code`='$verification_code', `group`='3' WHERE id='$uid'";
			$sql->query($q);
			// get username :)
			$uq = "SELECT username FROM `users` WHERE id='$uid' LIMIT 1";
			$uq = $sql->query($uq);
			$ur = $uq->fetch_assoc();
			$username = $ur['username'];
			$e_username = urlencode($username);
			// success
			$content = core::errorMessage('email_changed');
			//session_unset();
			//session_destroy();
			// Setup verification code - activation_code
			$verification_code = md5(sha1(md5($username)));
			// Send verification email
			$e_username = urlencode($username);
			$email_add = db::$config['email'];
			$url = 'http://'.$_SERVER['SERVER_NAME'].''.$_SERVER['SCRIPT_NAME'].'';
			$site = db::$config['site_name'];
			// Get the template
			$template['path'] = core::getCurrentThemeLocation();
			$template['container_p'] = $template['path'].'user_register_email.html';
			$template['container'] = file_get_contents($template['container_p']);
			// replacements
			$msg = $template['container'];
			$msg = str_replace('{username}',$username,$msg);
			$msg = str_replace('{url}',$url,$msg);
			$msg = str_replace('{e_username}',$e_username,$msg);
			$msg = str_replace('{verification_code}',$verification_code,$msg);
			$msg = str_replace('{site}',$site,$msg);
			// headers
			$headers = "From: $site <$email_add>";
			$subject = 'Activate your account';
			mail($email, $subject, $msg, $headers);
			// close
			$sql->close();
		}else{
			// Get the template
			$template['path'] = core::getCurrentThemeLocation();
			$template['container_p'] = $template['path'].'container.html';
			$template['container'] = file_get_contents($template['container_p']);
			// Secondary content
			// Get the template
			$template['container_p2'] = $template['path'].'user_email.html';
			$template['container2'] = file_get_contents($template['container_p2']);
			// replace
			$content = $template['container'];
			$content = str_replace('{content}',$template['container2'],$content);
			$content = str_replace('{header_title}','Change Your Email Address',$content);
		}
		return $content;
	}
	public function userControlPanel(){
		// Get the template
		$template['path'] = core::getCurrentThemeLocation();
		$template['container_p'] = $template['path'].'container.html';
		$template['container'] = file_get_contents($template['container_p']);
		// Secondary content
		// Get the template
		$template['container_p2'] = $template['path'].'user_cp_home.html';
		$template['container2'] = file_get_contents($template['container_p2']);
		// replace
		$content = $template['container'];
		$content = str_replace('{content}',$template['container2'],$content);
		$content = str_replace('{header_title}','User Control Panel',$content);
		// return
		return $content;
	}
}
?>