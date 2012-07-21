<?php
class openid_ui{
	public function homeScreen(){
		// Get the template
		$template['path'] = core::getCurrentThemeLocation();
		$template['container_p'] = $template['path'].'container.html';
		$template['container'] = file_get_contents($template['container_p']);
		// Set the login form...
		$form = '
			<tbody><tr><td>
			<!-- Simple OpenID Selector -->
			<form action="index.php?act=openid&do=verify" method="get" id="openid_form">
				<input type="hidden" name="do" value="verify" />
				<input type="hidden" name="act" value="openid" />
				<input type="hidden" name="login" value="true" />
				<fieldset>
					<legend>Sign-in or Create New Account</legend>
					<div id="openid_choice">
						<p>Please click your account provider:</p>
						<div id="openid_btns"></div>
					</div>
					<div id="openid_input_area">
						<input id="openid_identifier" name="openid_identifier" type="text" value="http://" />
						<input id="openid_submit" type="submit" value="Sign-In"/>
					</div>
					<noscript>
						<p>OpenID is service that allows you to log-on to many different websites using a single indentity.
						Find out <a href="http://openid.net/what/">more about OpenID</a> and <a href="http://openid.net/get/">how to get an OpenID enabled account</a>.</p>
					</noscript>
				</fieldset>
			</form>
			<!-- /Simple OpenID Selector -->
			</td></tr></tbody>';
		// Do replacements
		$content = $template['container'];
		$content = str_replace('{header_title}','Login',$content);
		$content = str_replace('{content}',$form,$content);
		// Return
		return $content;
	}
	public function googleLogin(){
		// Do the stuff
		$content = $this->doLogin('https://www.google.com/accounts/o8/id',array('namePerson/first', 'namePerson/last', 'contact/email'));
		// Send it back
		return $content;
	}
	public function yahooLogin(){
		// Do the stuff
		$content = $this->doLogin('http://me.yahoo.com',array('namePerson/friendly', 'schema/gender', 'contact/email', 'namePerson/friendly'));
		// Send it back
		return $content;
	}
	public function normalLogin(){
		// Do the stuff
		$content = $this->doLogin(urldecode($_GET['openid_identifier']),array('namePerson/friendly', 'schema/gender', 'contact/email'));
		// Send it back
		return $content;
	}
	public function doLogin($url,$fields){
		require 'openid_auth.class.php';
		try{
			$openid = new LightOpenID;
			if(!$openid->mode){
				if(isset($_GET['login'])){
					$openid->identity = $url;
					$openid->required = $fields;
					header('Location: ' . $openid->authUrl());
				}
			}elseif($openid->mode == 'cancel'){
				exit('User has canceled authentication!');
			}else{
				if($openid->validate()){
					// DB DB DB!
					$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
					// Setup the session
					$_SESSION[db::$config['session_prefix'].'_oid_p'] = '1';
					// Secure the variables
					if($_COOKIE['openid_provider'] == 'google'){
						$attributes = $openid->getAttributes();
						$email = $sql->real_escape_string($attributes['contact/email']);
						$first_name = $sql->real_escape_string($attributes['namePerson/first']);
						$last_name = $sql->real_escape_string($attributes['namePerson/last']);
						$nickname = str_replace(array('@gmail.com','@googlemail.com'),'',$email);
					}elseif($_COOKIE['openid_provider'] == 'yahoo'){
						$attributes = $openid->getAttributes();
						$email = $sql->real_escape_string($attributes['contact/email']);
						$nickname = $sql->real_escape_string($attributes['namePerson/friendly']);
					}else{
						$gender = $sql->real_escape_string($_GET['openid_sreg_gender']);
						$nickname = $sql->real_escape_string($_GET['openid_sreg_nickname']);
						$fullname = $sql->real_escape_string($_GET['openid_sreg_fullname']);
						$email = $sql->real_escape_string($_GET['openid_sreg_email']);
					}
					$identity = $openid->identity;
					$identity_www = str_replace('http://','',$identity);
					// Does an account already exist with this information?
					$q = "SELECT count(id) AS n_exists FROM `users` WHERE openid_url='$identity' AND email='$email'
						OR openid_url='$identity_www' AND email='$email'";
					$q = $sql->query($q);
					$r = $q->fetch_assoc();
					// Result
					if($r['n_exists'] == 0){
						// We create a new account (well, we should a form to do so...)
						// Get the template
						$template['path'] = core::getCurrentThemeLocation();
						$template['container_p'] = $template['path'].'container.html';
						$content = '<form action="index.php?act=openid&do=add" method="post">'.file_get_contents($template['container_p']).'</form>';
						// Form
						$form = '
	<thead>
		<tr>
			<th colspan="2">You&#39;re almost done. In order to create a new account, simply review the information below.
			Once you&#39;ve checked everything&#39;s OK, simply click &quot;New Account&quot; and a new account will be created,
			and you&#39;ll be logged in automatically.
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td width="50%" style="text-align: right;">
				<label for="username">Username:</label>
			</td>
			<td>
				<input class="input_text" id="username" name="username" type="text" value="'.$nickname.'" /><br />
				<small class="hint">This will be the name you&#39;ll login with, and the name that appears when you post</small>
			</td>
		</tr>
		<tr>
			<td width="50%" style="text-align: right;">
				<label for="email">Email Address:</label>
			</td>
			<td>
				<input class="input_text" id="email" name="email" type="text" value="'.$email.'" readonly="readonly" /><br />
				<small class="hint">You cannot change this field.</small>
			</td>
		</tr>
		<!-- Hidden Fields -->
		<input type="hidden" name="openid_url" value="'.$identity.'" />
		<input type="hidden" name="fullname" value="'.$first_name.' '.$last_name.'" />
		<tr>
			<td></td>
			<td><input type="submit" name="s" value="New Account" class="submitEnabled" /></td>
		</tr>
	</tbody>';
						// Replacements
						$content = str_replace('{header_title}','OpenID - Create New Account',$content);
						$content = str_replace('{content}',$form,$content);
					}else{
						// Get userid
						$q = "SELECT id,username FROM `users` WHERE openid_url='$identity' AND email='$email'
							OR openid_url='$identity_www' AND email='$email' LIMIT 1;";
						$q = $sql->query($q);
						$r = $q->fetch_assoc();
						// Login
						$_SESSION[db::$config['session_prefix'].'_username'] = $r['username'];
						$_SESSION[db::$config['session_prefix'].'_userid'] = $r['id'];
						$_SESSION[db::$config['session_prefix'].'_loggedin'] = 'yes';
						$_SESSION[db::$config['session_prefix'].'_domain'] = $_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'];
						// Show success message :-)
						$content = core::errorMessage('blank_info');
						$content = str_replace('{e}','Thank you, you&#39;ve been logged in to the account &quot;'.$r['username'].'&quot;. <a href="index.php?">Go to the homepage</a>.',$content);			
					}
				}else{
					exit('User ' . $openid->identity . 'has not logged in.');
				}
			}
		}catch(ErrorException $e){
			exit($e->getMessage());
		}
		return $content;
	}
	public function addAccount(){
		if($_SESSION[db::$config['session_prefix'].'_oid_p'] != '1'){
			$content = core::errorMessage('blank_err');
			$content = str_replace('{e}','You do not appear to have properly authenticated via OpenID. Please <a href="index.php?act=openid&amp;do=try_again">try again</a>.',$content);
		}else{
			// Connect to Database
			$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
			// Secure Data
			$gender = $sql->real_escape_string($_POST['gender']);
			$openid_url = $sql->real_escape_string($_POST['openid_url']);
			$email = $sql->real_escape_string($_POST['email']);
			$fullname = $sql->real_escape_string($_POST['fullname']);
			$username = $sql->real_escape_string($_POST['username']);
			// Strip NASTY Characters From Username
			$username = strip_tags($username);
			$username = preg_replace('/[^a-z0-9 ]/', '', $username); 
			// Check for dupes...
			$q = "SELECT count(id) AS n_exists FROM `users` WHERE username='$username'";
			$q = $sql->query($q);
			$r = $q->fetch_assoc();
			// Well?
			if($r['n_exists'] != 0){
				$content = core::errorMessage('blank_err');
				$content = str_replace('{e}','Sorry, that username is already in use. Please <a href="index.php?act=openid&amp;do=try_again">try again</a>.',$content);
			}else{
				// IP address
				$ip = $_SERVER['REMOTE_ADDR'];
				// Date
				$date = time();
				// Insert to DB
				$q = "INSERT INTO `users`(`username`,`email`,`joined`,`gender`,`group`,`openid_url`,`ip_address`)
				 VALUES('$username','$email','$date','$gender','1','$openid_url','$ip')";
				$sql->query($q);
				// Show success...
				$content = core::errorMessage('blank_info');
				$content = str_replace('{e}',''.$fullname.', a new account has been created with the details below.<br />
				&bull; Email: '.$email.'<br />
				&bull; Username: '.$username.'<br />
				&bull; Gender: '.$gender.'<br /><br />
				You have now been logged in. <strong>You can only login to this account via the same OpenID in future.</strong>
				If you&#39;d rather login using the standard username &amp; password, you will need to request a password via the reset password
				option.',$content);
				// Login
				$_SESSION[db::$config['session_prefix'].'_username'] = $username;
				$_SESSION[db::$config['session_prefix'].'_userid'] = $sql->insert_id;
				$_SESSION[db::$config['session_prefix'].'_loggedin'] = 'yes';
				$_SESSION[db::$config['session_prefix'].'_domain'] = ''.$_SERVER['SERVER_NAME'].''.$_SERVER['PHP_SELF'].'';
			}
		}
		return $content;
	}
}
?>