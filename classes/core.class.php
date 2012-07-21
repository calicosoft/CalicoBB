<?php
class core extends calicobb{
	public function errorMessage($message){
		// error message handling - also handles normal successes (if there are any :P)
		switch($message){
			case 'forum_closed':
				$err = 'You cannot post in this forum, as it is currently closed for posting';
			break;
			case 'user_posting_disabled':
				$err = 'Sorry, your account is currently disabled from posting. You cannot post until this suspension is lifted.';
			break;
			case 'topic_not_found':
				$err = 'The topic you are looking for could not be found. It may have been deleted or you may have followed a broken link.';
			break;
			case 'forum_deleted':
				$err = 'The forum you are trying to access could not be found. It may have been deleted or you may have followed a broken link.';
			break;
			case 'empty_fields':
				$err = 'One or more required fields were missing from your submission. Please go back and make sure all fields are complete.';
			break;
			case 'topic_success':
				$err = 'Thank you, your topic has been created successfuly. You are now being redirected to your new topic.
				<a href="{t_url}">Click here if you are not redirected</a>';
				$errtype = 'info';
			break;
			case 'topic_closed':
				$err = 'Sorry, this topic is closed and you cannot respond to it';
			break;
			case 'post_success':
				$err = 'Thank you, your post has been added to this topic. You are now being redirected to your new post. 
				<a href="index.php?post={pid}&amp;tid={tid}">Click here if you are not redirected</a>.';
				$errtype = 'info';
			break;
			case 'report_sent':
				$err = 'Thank you, your report has been sent to the moderating team. A moderator will review your report and take the appropriate action';
				$errtype = 'info';
			break;
			case 'forum_no_permission':
				$err = 'You do not have permission to view this topic. This topic may be off-limits or otherwise unavailable.';
			break;
			case 'own_topics_only':
				$err = 'Sorry, you do not have permission to view this topic. Topics in this forum can only be viewed by their author.';
			break;
			case 'post_deleted':
				$err = 'The post you are looking for could not be found. It may have been deleted or you may have followed a broken link.';
				$errtype = 'info';
			break;
			case 'pm_disabled':
				$err = 'Sorry, you are not allowed to use the Private Messaging system.';
			break;
			case 'pm_not_yours':
				$err = 'This private message does not belong to you. Private messages are kept secure, so you cannot view a message that does not belong to you.';
			break;
			case 'pm_not_found':
				$err = 'This private message could not be found. You may have followed an outdated link.';
			break;
			case 'pm_user_not_found':
				$err = 'The user you are trying to private message could not be found. Please check that this user exists and you have spelt their username correctly';
			break;
			case 'pm_sent':
				$err = 'Thanks, your private message has successfully been sent to the recipient. They will be notified next time they login.';
				$errtype = 'info';
			break;
			case 'pm_failed':
				$err = 'Sorry, an unspecified error occurred while sending this private message. Please try again later.';
			break;
			case 'pm_deleted':
				$err = 'All your private messages have been deleted, as required.';
				$errtype = 'info';
			break;
			case 'search_disabled':
				$err = 'Sorry, you are not allowed to use the Search system.';
			break;
			case 'search_keyword_missing':
				$err = 'You have not specified any search terms too look for. That&#39;s pretty silly of you. How can we search if we don&#39;t know what to search for? Go back and enter a search term.';
			break;
			case 'search_keyword_small':
				$err = 'Your search term must be three (3) characters or more. Go back and adjust your search term, or try an alternative search engine.';
			break;
			case 'search_no_results':
				$err = 'Sorry, no results were found with the search criteria you specified. Go back and adjust your search criteria.';
			break;
			case 'incorrect_login':
				$err = 'You could not be logged in with the username &amp; password you provided. Forgotten your password? <a href="index.php?act=resetpassword">Reset it here</a>.';
			break;
			case 'login_success':
				$err = 'Thank you, you have been logged in. Please wait while we redirect you...';
				$errtype = 'info';
			break;
			case 'logged_out':
				$err = 'You have now been logged out. Please wait while we redirect you...';
				$errtype = 'info';
			break;
			case 'login_not_activated':
				$err = 'You could not be logged in. You have not activated your account. Please check your email address for your activation email and activate.';
			break;
			case 'reset_no_user_found':
				$err = 'A user could not be found with the email address &amp; username you specified. Are you sure you have entered the correct information?';
			break;
			case 'reset_password':
				$err = 'Your password has been reset! Please check your email address for your new login details.';
				$errtype = 'info';
			break;
			case 'register_fields_dont_match':
				$err = 'Your {field} do not match. Please go back and try again.';
			break;
			case 'register_invalid_email':
				$err = 'The email address you specified is not a valid email address. Go back and enter a valid email address.';
			break;
			case 'register_user_exists':
				$err = 'Sorry, an account already exists with the username you specified. If this is your account, you can <a href="index.php?act=resetpassword">reset your login details here</a>.';
			break;
			case 'register_email_exists':
				$err = 'Sorry, an account already exists with the email address you specified. If this is your account, you can <a href="index.php?act=resetpassword">reset your login details here</a>.';
			break;
			case 'register_recaptcha_incorrect':
				$err = 'The anti-spam code you entered is incorrect. Registration cannot proceed until you have entered a valid anti-spam code. Please go back and try again.';
			break;
			case 'register_botcheck_incorrect':
				$err = 'Sorry, you did not pass the anti-bot check. You must choose the right option to whether you are a human or not. Please go back and try again.';
			break;
			case 'register_activate_account':
				$err = 'Thank you for creating your account. Before you can use board features, you must activate your account. An email has been sent to your email address. In order to activate your account, please click the link in the email.';
				$errtype = 'info';
			break;
			case 'register_complete':
				$err = 'Thank you for creating your account. Your account has been created, and you may now <a href="index.php?act=login">login</a> to the forum.';
				$errtype = 'info';
			break;
			case 'stop_forum_spam_match':
				$err = 'Sorry, registration cannot proceed. Your details match known spammers the <a href="http://www.stopforumspam.com">StopForumSpam</a> database. Your {field} appears in this database. Please try a different username or email.';
			break;
			case 'activate_account_not_found':
				$err = 'An account could not be found with the activation details you provided. Your account may already have been deleted, or you may have followed a broken link.';
			break;
			case 'activation_success':
				$err = 'Thank you, your account has now been activated. You are now logged in. Please wait while we redirect you...';
				$errtype = 'info';
			break;
			case 'profile_disabled':
				$err = 'Sorry, you are not allowed to view profiles.';
			break;
			case 'profile_not_found':
				$err = 'The profile you are trying to view could not be found.';
			break;
			case 'profile_updated':
				$err = 'Thank you, your profile has been updated as required.';
				$errtype = 'info';
			break;
			case 'usercp_too_large_pp':
				$err = 'The maximum posts &amp; topics per page is 50. Please update your selection to keep this limit in mind.';
			break;
			case 'usercp_updated':
				$err = 'Thank you, your settings have been updated and come in to effect immediately.';
				$errtype = 'info';
			break;
			case 'current_password_mismatch':
				$err = 'The password you provided as your current password does not match the password we have on file for you.';
			break;
			case 'passwords_mismatch':
				$err = 'The passwords do not match.';
			break;
			case 'password_changed':
				$err = 'Your password has been changed. For security reasons, you have been logged out. <a href="index.php?act=login">Please login again</a>.';
				$errtype = 'info';
			break;
			case 'email_mismatch':
				$err = 'The emails do not match.';
			break;
			case 'email_changed':
				$err = 'Your email address has been changed. In order to verify this email address is correct, an activation email has been sent to your email address. Please click the link contained within the email.';
				$errtype = 'info';
			break;
			case 'not_logged_in':
				$err = 'You must be logged in before you can perform this action. Please <a href="index.php?act=login&amp;r='.urlencode($_SERVER['QUERY_STRING']).'">login</a>.';
			break;
			case 'banned':
				$err = 'You are currently banned from this forum.';
			break;
			case 'moderator_only':
				$err = 'You must be a moderator to perform this action.';
			break;
			case 'moderator_not_found':
				$err = 'The post or topic you are trying to moderate could not be found. It may have been deleted, or you may not have permission to moderate this forum.';
			break;
			case 'moderate_topic_moved':
				$err = 'This topic has been moved to the new forum. <a href="index.php?topic={tid}">View Topic</a>.';
				$errtype = 'info';
			break;
			case 'moderate_topic_no_permission':
				$err = 'You cannot perform a moderator function in a topic in which you do not have moderator functions for.';
			break;
			case 'moderate_topic_merged':
				$err = 'Your topics have been merged. <a href="index.php?topic={tid}">View New Topic</a>.';
				$errtype = 'info';
			break;
			case 'moderate_stickied':
				$err = 'This topic has been stickied as requested. <a href="javascript:history.go(-1)">Return to the previous page</a>.';
				$errtype = 'info';
			break;
			case 'moderate_unstickied':
				$err = 'This topic has been unstickied as requested. <a href="javascript:history.go(-1)">Return to the previous page</a>.';
				$errtype = 'info';
			break;
			case 'moderate_closed':
				$err = 'This topic has been closed as requested. <a href="javascript:history.go(-1)">Return to the previous page</a>.';
				$errtype = 'info';
			break;
			case 'moderate_not_closed':
				$err = 'This topic has been re-opened for posting as requested. <a href="javascript:history.go(-1)">Return to the previous page</a>.';
				$errtype = 'info';
			break;
			case 'moderate_post_deleted':
				$err = 'This post has been deleted, as requested. It will only be visible to moderators &amp; other staff members. <a href="javascript:history.go(-1)">Return to the previous page</a>.';
				$errtype = 'info';
			break;
			case 'moderate_post_restored':
				$err = 'This post has been restored, as requested. It is now visible to all members with permission to view posts within this topic. <a href="javascript:history.go(-1)">Return to the previous page</a>.';
				$errtype = 'info';
			break;
			case 'moderate_post_edited':
				$err = 'This post has been edited, as requested. <a href="javascript:history.go(-2)">Return to the previous page</a>.';
				$errtype = 'info';
			break;
			case 'moderate_topic_title_edited':
				$err = 'The topic title has been edited, as requested. <a href="javascript:history.go(-2)">Return to the previous page</a>.';
				$errtype = 'info';
			break;
			case 'moderate_warning_added_banned':
				$err = 'This user has been warned, and subsequently been banned. <a href="javascript:history.go(-2)">Return to the previous page</a>.';
				$errtype = 'info';
			break;
			case 'moderate_warning_added':
				$err = 'This user has been warned. <a href="javascript:history.go(-1)">Return to the previous page</a>.';
				$errtype = 'info';
			break;
			case 'moderate_security_key':
				$err = 'The security key does not match. Please <a href="javascript:history.go(-1)">go back to the previous page</a> and try again.';
			break;
			case 'warning_revoke_confirm':
				$err = 'Are you sure you wish to revoke this warning? <a href="index.php?'.$_SERVER['QUERY_STRING'].'&amp;do_confirm=revoke">Revoke Warning</a>.';
				$errtype = 'info';
			break;
			case 'warning_revoked_unbanned':
				$err = 'This warning has been revoked, and the user has subsequently been unbanned. <a href="javascript:history.go(-1)">Return to the previous page</a>.';
				$errtype = 'info';
			break;
			case 'warning_revoked':
				$err = 'This warning has been revoked, as requested. <a href="javascript:history.go(-1)">Return to the previous page</a>.';
				$errtype = 'info';
			break;
			case 'moderate_invalid_selection':
				$err = 'Of all the options available to you, you had to pick the dashes. Go back and make a proper selection.';
			break;
			case 'subs_disabled':
				$err = 'The subscriptions system is disabled.';
			break;
			case 'subs_not_found':
				$err = 'The subscription package you are trying to purchase does not exist.';
			break;
			case 'subs_return':
				$err = 'Thank you for your payment! It may take a few minutes for your permissions to upgrade. If you require further help, <a href="index.php?act=forumteam">contact the moderating team</a>.';
				$errtype = 'info';
			break;
			case 'logout_please':
				$err = 'There was an error while trying to check your current status. Please <a href="index.php?act=logout">logout</a> and log back in. We are sorry for the inconvenience.';
				$errtype = 'info';
			break;
			case 'edit_time_expired':
				$err = 'Sorry, you cannot edit this post. Your editing time has expired. Regular users are only allowed <em>'.db::$config['edit_time'].'</em> minutes to edit their posts.';
			break;
			case 'blank_info':
				$err = '{e}';
				$errtype = 'info';
			break;
			case 'blank_err':
				$err = '{e}';
			break;
			default:
				$err = 'An error occured while trying to generate this error message.';
			break;
		}
		switch($errtype){
			case "info":
				$err = '<div class="status info">
				<p><img src="theme/icons/icon_info.png" alt="Information" /><span>Information:</span> '.$err.'</p>
			</div>';
			break;
			default:
				$err = '<div class="status error">
				<p><img src="theme/icons/icon_error.png" alt="Error" /><span>Error:</span> '.$err.'</p>
			</div>';
			break;
		}
		return $err;
	}
	public function getUsername($uid){
		// Have we already cached the username?
		if($_SESSION[db::$config['session_prefix'].'_user_'.$uid]){
			return $_SESSION[db::$config['session_prefix'].'_user_'.$uid];
		}else{
			// gets & formats the username based on the User ID. to remove formatting, use strip_tags();
			// Connect To Database
			$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
			// Secure the UID
			$uid = $sql->real_escape_string($uid);
			// Get user details
			$uq = "SELECT `username`,`group` FROM `users` WHERE id='$uid' LIMIT 1";
			$uq = $this->DB->query($uq);
			if($uq->num_rows == 0){
				return 'Guest (User Not Found)';
			}
			$ur = $uq->fetch_assoc();
			// username
			$username = htmlspecialchars($ur['username']);
			// Get group details
			$gid = $ur['group'];
			$gq = "SELECT group_format FROM `groups` WHERE id='$gid' LIMIT 1";
			$gq = $this->DB->query($gq);
			$gr = $gq->fetch_assoc();
			// Format the username
			$username = str_replace('{username}',$username,$gr['group_format']);
			// Strip tags?
			if(db::$config['strip_tags'] == true){
				$username = strip_tags($username);
			}
			// Close conn
			$sql->close();
			// Cache username
			$_SESSION[db::$config['session_prefix'].'_user_'.$uid] = $username;
			// Send it back
			return $username;
		}
	}
	public function getGroup($gid){
		// Have we already cached the group?
		if($_SESSION[db::$config['session_prefix'].'_group_'.$gid]){
			return $_SESSION[db::$config['session_prefix'].'_group_'.$gid];
		}else{
			// gets & formats the group based on the Group ID. to remove formatting, use strip_tags();
			// Connect To Database
			$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
			// Secure the UID
			$gid = $sql->real_escape_string($gid);
			// Get group details
			$gq = "SELECT title,group_format FROM `groups` WHERE id='$gid' LIMIT 1;";
			$gq = $this->DB->query($gq);
			if($gq->num_rows == 0){
				return 'Group Not Found';
			}
			$gr = $gq->fetch_assoc();
			// Format the group
			$group = str_replace('{username}',$gr['title'],$gr['group_format']);
			// Strip tags?
			if(db::$config['strip_tags'] == true){
				$group = strip_tags($group);
			}
			// Close conn
			$sql->close();
			// Cache the group
			$_SESSION[db::$config['session_prefix'].'_group_'.$gid] = $group;
			// Send it back
			return $group;
		}
	}
	public function getAvatar($e,$s,$u){
		// First, encode the email
		$e = md5(strtolower($email));
		// Now, put it altogether
		$avatar = 'http://www.gravatar.com/avatar/'.$e.'?d=404&s='.$s.'';
		// Check it exists
		$url = getimagesize($avatar);
		if(!is_array($url)){
			// it doesn't exist, so send back nothing
			return;
		}
		// Now, we actually display the avvy.
		$avatar = '<img src="'.$avatar.'" height="'.$s.'" width="'.$s.'" alt="'.$u.'" title="'.$u.'" />';
		return $avatar;
	}
	public function userControlMenu(){
		// gives the user control menu for private messages, edit profile, etc...
		// Get the template
		$template['path'] = core::getCurrentThemeLocation();
		$template['container_p'] = $template['path'].'container.html';
		$template['container'] = file_get_contents($template['container_p']);
		$template['container_p1'] = $template['path'].'user_cp_menu_left.html';
		$template['container1'] = file_get_contents($template['container_p1']);
		// container
		$content = str_replace('{content}',$template['container1'],$template['container']);
		$content = str_replace('{header_title}','User CP',$content);
		// class name
		$content = str_replace('id="container" class="','id="containerLeft" class="userMenuLeft ',$content);
		// Send it back
		return $content;
	}
	public function generateEditor($e_name,$e_value){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Secure UID
		$uid = $_SESSION[db::$config['session_prefix'].'_userid'];
		// Check if we've to use the advanced editor...
		$eq = "SELECT advanced_editor FROM `users` WHERE id='$uid' LIMIT 1";
		$eq = $this->DB->query($eq);
		$er = $eq->fetch_assoc();
		// Adanced
		if($er['advanced_editor'] == 1){
			$e['editor'] = '<div class="richeditor">
		<div class="editbar">
			<button title="bold" onclick="wswgEditor.doClick(\'bold\');" type="button"><b>B</b></button>
			<button title="italic" onclick="wswgEditor.doClick(\'italic\');" type="button"><i>I</i></button>
			<button title="underline" onclick="wswgEditor.doClick(\'underline\');" type="button"><u>U</u></button>
			<button title="hyperlink" onclick="wswgEditor.doLink();" type="button" style="background-image:url(\'editor/images/url.gif\');"></button>
			<button title="image" onclick="wswgEditor.doImage();" type="button" style="background-image:url(\'editor/images/img.gif\');"></button>
			<button title="list" onclick="wswgEditor.doClick(\'InsertUnorderedList\');" type="button" style="background-image:url(\'editor/images/icon_list.gif\');"></button>
			<button title="color" onclick="wswgEditor.showColorGrid2(\'none\')" type="button" style="background-image:url(\'editor/images/colors.gif\');"></button><span id="colorpicker201" class="colorpicker201"></span>
			<button title="quote" onclick="wswgEditor.doQuote();" type="button" style="background-image:url(\'editor/images/icon_quote.png\');"></button>
			<button title="youtube" onclick="wswgEditor.InsertYoutube();" type="button" style="background-image:url(\'editor/images/icon_youtube.gif\');"></button>
			<button title="switch to source" type="button" onclick="wswgEditor.SwitchEditor()" style="background-image:url(\'editor/images/icon_html.gif\');"></button>
			'.core::showSmile().'
		</div>
		<div class="container">
		<textarea name="'.$e_name.'" id="'.$e_name.'" style="height:150px;width:100%;">'.$e_value.'</textarea>
		</div>
	</div>
	<script type="text/javascript">
		wswgEditor.initEditor("'.$e_name.'", true);
	</script>';
		}else{
			$e['editor'] = '<textarea name="'.$e_name.'" id="'.$e_name.'" style="font-family: Segoe UI, Helvetica, Tahoma, Arial, sans-serif; font-size: 12px; height: 400px; width: 90%;">'.$e_value.'</textarea>';
		}
		$sql->close();
		return $e['editor'];
	}
	public function parsePost($m){
		// parses the post with bbCode, smilies and also checks the badwords
		// Make sure we cannot ever post HTML
		$m = htmlspecialchars($m, ENT_QUOTES);
		// first, we do smilies (word based)
		$sm_word = array(':angry:',':biggrin:',':blink:',':blush:',':bored:',':confused:',':cool:',':cry:',':curse:',':drool:',':glare:',
			':huh:',':laugh:',':lick:',':lol:',':love:',':mad:',':mellow:',':ohmy:',':razz:',':rolleyes:',':sad:',':scared:',':sleep:',':sneaky:',
			':thumbsup:',':thumbup:',':thumbdown:',':unsure:',':w00t:',':wink:',':wuv:');
		// loop through each one
		foreach($sm_word as $sm_word){
			// Get Image Size
			$sm_word2 = str_replace(':','',$sm_word);
			list($width, $height, $type, $attr) = getimagesize("smile/msp_$sm_word2.gif");
			// get Img
			$sm_image = str_replace(':','',$sm_word);
			$sm_image = '<img src="smile/msp_'.$sm_image.'.gif" height="'.$height.'" width="'.$width.'" alt="'.$sm_word.'" />';
			// replace
			$m = str_ireplace($sm_word,$sm_image,$m);
		}
		// now, we do the traditional smilies like :)
		$sm_smile = array(':D',':(',':)','<3',':P');
		$sm_smile_img = array('<img src="smile/msp_biggrin.gif" height="20" width="20" alt=":biggrin:" />',
			'<img src="smile/msp_sad.gif" height="20" width="20" alt=":sad:" />',
			'<img src="smile/msp_smile.gif" height="20" width="20" alt=":smile:" />',
			'<img src="smile/msp_wuv.gif" height="20" width="20" alt=":wuv:" />',
			'<img src="smile/msp_tounge.gif" height="20" width="20" alt=":tounge:" />');
		// replace
		$m = str_ireplace($sm_smile,$sm_smile_img,$m);
		// Youtube (to remove, comment out :))
		$yt_f = '/\[youtube\](.*?)\[\/youtube\]/is';
		$yt_r = "<iframe title=\"YouTube video player\" width=\"480\" height=\"390\" src=\"http://www.youtube.com/embed/$1?rel=0\" frameborder=\"0\" allowfullscreen>Youtube video - [url=http://youtube.com/watch?v=$1]Watch now[/url]</iframe>";
		$m = preg_replace($yt_f,$yt_r,$m);
		// Quotes
		$qu_f = array('/\[quote author=([0-9A-Za-z_-]+) pid=([0-9]+) tid=([0-9]+)\](.*?)\[\/quote\]/is',
			'/\[quote author=([0-9A-Za-z_-]+) pid=([0-9]+)\](.*?)\[\/quote\]/is',
			'/\[quote\](.*?)\[\/quote\]/is',
			'/\[quote author=([0-9A-Za-z_-]+)\](.*?)\[\/quote\]/is');
		$qu_r = array(
			'<div class="quote t1"><span class="quoteHeader">QUOTE: (Posted by: $1) <a href="index.php?post=$2&amp;tid=$3" title="View Original Post">&uarr;</a></span><br /><div class="quoteBody">$4</div></div>',
			'<div class="quote t2"><span class="quoteHeader">QUOTE: (Posted by: $1)</span><br /><div class="quoteBody">$3</div></div>',
			'<div class="quote t3"><span class="quoteHeader">QUOTE:</span><br /><div class="quoteBody">$1</div></div>',
			'<div class="quote t4"><span class="quoteHeader">QUOTE: (Posted by: $1) </span><br /><div class="quoteBody">$2</div></div>');
		// replace
		$m = preg_replace($qu_f,$qu_r,$m);
		// URLS, images and colours
		$url_f = array('/\[url\](.*?)\[\/url\]/is','/\[url=(.*?)\](.*?)\[\/url\]/is','/\[color=(.*?)\](.*?)\[\/color\]/is','/\[img\](.*?)\[\/img\]/is'); 
		$url_r = array('<a href="$1" target="_blank" title="$1">$1</a>','<a href="$1" target="_blank" title="$1">$2</a>',
			'<span style="color: $1">$2</span>','<img src="$1" alt="User posted image" title="User posted image" />');
		// replace
		$m = preg_replace($url_f,$url_r,$m);
		// PHP Highlighting
		$php_f = array('/\[php\](.*?)\[\/php\]/is','/\[php_b\](.*?)\[\/php_b\]/is','/\[code\](.*?)\[\/code\]/is'); 
		$php_r = '<code class="prettyprint">$1</code>';
		// replace
		$php_m = preg_replace($php_f,$php_r,$m);
		$m = $php_m;
		// /t replace
		$m = preg_replace('/\t(.*?)/is','<span style="white-space: pre;">	</span>',$m);
		// Remove Curly Brackets
		$m = str_replace('{rep','[][]rep',$m);
		$m = str_replace('{','&#123;',$m);
		$m = str_replace('[][]rep','{rep',$m);
		// Code <mbr>
		$php_f = array('/\[php\](.*?)<mbr>\[\/php\]/is','/\[php_b\](.*?)\[\/php_b\]/is'); 
		$php_r = '<pre>$1</pre>';
		// replace
		$php_m = preg_replace($php_f,$php_r,$m);
		// Standard bbCode
		$bb_f = array('[b]','[/b]','[u]','[/u]','[i]','[/i]','[ul]','[/ul]','[li]','[/li]');
		$bb_r = array('<strong>','</strong>','<u>','</u>','<em>','</em>','<ul>','</ul>','<li>','</li>');
		$m = str_ireplace($bb_f,$bb_r,$m);
		// now, BAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD words.
		// load the file
		$filename = 'badwords.txt';
		$fd = fopen ($filename, "r");
		$contents = fread ($fd,filesize ($filename));
		fclose($fd); 
		// set the delimiter (new line)
		$delimiter = "\n";
		$splitcontents = explode($delimiter, $contents);
		$counter = "";
		foreach($splitcontents as $badword){
			$counter = $counter + 1;
			$badcount = 1;
			unset($badword_replace);
			$badword_replace = '*';
			do{
				$badword_replace .= '*';
				$badcount = $badcount + 1;
			}while($badcount < strlen($badword));
			//$post = str_ireplace($badword,strlen($badword),$post);
			$m = str_ireplace($badword,$badword_replace,$m);
		}
		// For Security...
		$m = str_ireplace("%3C%73%63%72%69%70%74","&lt;script",$m);
		$m = str_ireplace("%64%6F%63%75%6D%65%6E%74%2E%63%6F%6F%6B%69%65","document&#46;cookie",$m);
		$m = preg_replace("#javascript\:#is","java script:",$m);
		$m = preg_replace("#vbscript\:#is","vb script:",$m);
		$m = str_ireplace("`","&#96;",$m);
		$m = preg_replace("#moz\-binding:#is","moz binding:",$m);
		$m = str_ireplace("<script","&lt;script",$m);
		$m = str_ireplace("&#8238;",'',$m);
		// now, we can send it back
		return $m;
	}
	public function banCheck($uid){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Secure UID
		$uid = intval($sql->real_escape_string($uid));
		// See if we're in the banned group
		$q = "SELECT count(id) AS isBanned FROM `banned` WHERE uid='$uid' LIMIT 1;";
		$q = $this->DB->query($q);
		$r = $q->fetch_assoc();
		// Are we banned?
		if($r['isBanned'] > 0){
			// date
			$time = time();
			// now, we need to perform a warning check
			$q2 = "SELECT sum(points) as warningPoints FROM `warnings` WHERE uid='$uid' AND expires < $time AND revoked = 0;";
			$q2 = $this->DB->query($q2);
			$r2 = $q2->fetch_assoc();
			// is it greater
			if($r2['warningPoints'] < db::$config['max_warning_points']){
				// unban
				moderate::unBanUser($uid);
				// we're not banned anymore
				return false;
			}
			// show error...
			$content = true;
		}else{
			// do Nowt
			$content = false;
		}
		return $content;
	}
	public function checkWarnings($uid){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Secure UID
		$uid = intval($uid);
		// Get warnings
		$q = "SELECT id,title,notes,points,pmid FROM `warnings` WHERE has_read = 0 AND uid='$uid' LIMIT 1;";
		$q = $this->DB->query($q);
		// is there one?
		if($q->num_rows == 1){
			// get result
			$r = $q->fetch_assoc();
			// show it
			$msg = '
				You have received a warning.<br /><br />
				Reason: <strong>'.$r['title'].'</strong><br />
				Points: <strong>'.$r['points'].'</strong><br />
				Note from Moderator: <strong>'.nl2br(htmlspecialchars($r['notes'])).'</strong><br /><br />
				A private message has been sent to you detailing this warning.
				<a href="index.php?act=viewmessage&pm='.$r['pmid'].'">Read message</a><br /><br />
				Before you can proceed, you must acknowledge this warning. Be aware that your continued misbehaviour may lead
				to your account being suspended.<br /><br />
				<a href="index.php?'.$_SERVER['QUERY_STRING'].'">Acknowledge Warning</a>';
			$msg = str_replace('{e}',$msg,core::errorMessage('blank_err'));
			$msg = str_replace('Error','Warning Received',$msg);
			// mark it as read
			$wid = $r['id'];
			$q = "UPDATE `warnings` SET has_read='1' WHERE id='$wid' LIMIT 1;";
			$this->DB->query($q);
			// say it's true
			$w['check'] = true;
		}else{
			$w['check'] = false;
		}
		// data
		$w['data'] = $msg;
		return $w;
	}
	public function userBar(){
		// Get the template
		$template['path'] = core::getCurrentThemeLocation();
		// Are we guest?
		if(!isset($_SESSION[db::$config['session_prefix'].'_userid'])){
			$template['container_p'] = $template['path'].'core_user_menu_guest.html';
			$template['container'] = file_get_contents($template['container_p']);
		}else{
			$uid = $_SESSION[db::$config['session_prefix'].'_userid'];
			// Connect To Database
			$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
			// check for unread PM's
			$q = "SELECT count(*) as `unread_pms` FROM `private_messages` WHERE `read`=0 AND `to`='$uid'";
			$q = $this->DB->query($q);
			$r = $q->fetch_assoc();
			// Show user bar
			$template['container_p'] = $template['path'].'core_user_menu_user.html';
			$template['container'] = file_get_contents($template['container_p']);
			// Replacements
			$template['container'] = str_replace('{user}',core::getUsername($uid),$template['container']);
			$template['container'] = str_replace('{unread_pms}',$r['unread_pms'],$template['container']);
			// Any new PMs?
			if($r['unread_pms'] != 0){
				if($r['unread_pms'] != 1){
					$s = 's';
				}
				$template['container'] .= '<br /><div style="userBoxNote">You have <strong>'.$r['unread_pms'].'</strong> unread message'.$s.'. <a href="index.php?act=messages">View your messages</a></div>';
			}
		}
		return $template['container'];
	}
	public function showSmile(){
		// check if folder exists
		if(is_dir('smile') == FALSE){
			mkdir ("./smile", 0755, true);
		}
		// initial...
		$smile = '<br />';
		if($handle = opendir('smile/')){
			while(false !== ($file = readdir($handle))){
				$pi = pathinfo('smile/'.$file.'');
				if($file != "." && $file != ".." && $pi['extension'] == 'gif'){
					$smile_code = str_replace('msp_','',$file);
					$smile_code = str_replace('.gif','',$smile_code);
					$smile .= '<img src="smile/'.$file.'" title=":'.$smile_code.':" alt=":'.$smile_code.':" onclick="wswgEditor.InsertSmile(\':'.$smile_code.':\');" />';
				}
			}
		}
		return $smile;
	}
	public function createPassword($lim){
		// allowed characters
		$chars = "abcdefghijkmnopqrstuvwxyz023456789";
		srand((double)microtime()*1000000);
		$i = 0;
		// set up password
		$pass = '';
		// loop through
		while($i <= $lim){
			$num = rand() % 33;
			$tmp = substr($chars, $num, 1);
			$pass = $pass . $tmp;
			$i++;
		}
		// send back the password
		return $pass; 
	} 
	public function generateUrl($url,$act,$id,$title,$var){
		///////////////////////////
		// USAGE:
		// $url = the index.php?act url
		// $act = the action (eg topic, forum)
		// $id = the ID of the file/topic/etc
		// $title = the title of the topic/forum/etc
		// $var = additional Variables (eg page)
		///////////////////////////
		// Are we doing friendly Urls?
		if(db::$config['seo_urls'] == true){
			// Generate friendly Url
			// Make the title URL safe
			$title = core::friendlyTitle($title);
			// Var
			if($var){
				$var = '?'.$var;
			}
			// Put it all together
			$furl = $act.'-'.$id.'-'.$title.'.html'.$var;
			// Return
			return $furl;
		}else{
			// Return a index.php?act=... var
			return $url.$var;
		}
	}
	public function friendlyTitle($string){
		// this will generate a friendly link, for SEO purposes.
		$string = preg_replace("`\[.*\]`U","",$string);
		$string = preg_replace('`&(amp;)?#?[a-z0-9]+;`i','-',$string);
		$string = htmlentities($string, ENT_COMPAT, 'utf-8');
		$string = preg_replace( "`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i","\\1", $string );
		$string = preg_replace( array("`[^a-z0-9]`i","`[-]+`") , "-", $string);
		// send er back
		return substr(trim($string, '-'),0,25);
	}
	public function getSoftwareVersion(){
		return '1.0';
	}
	public function paginationGenerate($records,$pp,$current,$pagevar,$current_url){
		// Conection
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// First, make sure we've secured the data (just to be sure :P)
		$records = intval($sql->real_escape_string($records)); // the number of records
		$pp = intval($sql->real_escape_string($pp)); // the number per page
		$pageno['current'] = intval($sql->real_escape_string($current)); // the current page no
		$pagevar = $sql->real_escape_string($pagevar); // the page variable
		$current_url = $sql->real_escape_string($current_url); // the current url (eg index.php?topic=54)
		$current_url_e = urlencode($current_url);
		$pagevar_e = htmlspecialchars_decode($pagevar);
		// Set current page number (if it's not already set :P)
		if($pageno['current'] < 1){
			$pageno['current'] = 1;
		}
		// Is the records per page set? If not, use 25 :)
		if($pp == 0){
			$pp = 25;
		}
		// Calculate the last page
		$pageno['last'] = ceil($records/$pp);
		// Is our page within the range?
		if($pageno['current'] > $pageno['last']){
			$pageno['current'] = $pageno['last'];
		}
		// Setup the page display
		$pages['selection'] = '<div class="pagination"><ul>
			<li>
				<a class="selection" rel="nofollow" href="index.php?act=pageselection&amp;page_var='.urlencode($pagevar).'&amp;last='.$pageno['last'].'&amp;go='.urlencode($current_url).'" 
					onClick="pageJump(\''.$current_url.'\',\''.$pageno['current'].'\',\''.$pageno['last'].'\',\''.$pagevar.'\'); return false;">Page '.$pageno['current'].' of '.$pageno['last'].'</a>
			</li>';
		// First Page and Previous Page Listings...
		if($pageno['last'] != 1 AND $pageno['current'] != 1){
			// Set Previous Page
			$pageno['previous'] = $pageno['current'] - 1;
			// Show First & Previoust
			$pages['selection'] .= '
			<li class="prevnext">
				<a title="Go to the first page" href="'.$current_url.''.$pagevar.'='.$pageno['previous'].'">&laquo; First</a>
			</li>';
			// a few more...
			if($pageno['current'] > 2 AND $pageno['previous'] != 2){
				// more selection
				$pages['twobefore'] = $pageno['current'] - 2;
				$pages['selection'] .= '
					<li>
						<a title="Go to the page '.$pages['twobefore'].'" href="'.$current_url.''.$pagevar.'='.$pages['twobefore'].'">'.$pages['twobefore'].'</a>
					</li>';
			}
			// Previous
			$pages['selection'] .= '
			<li>
				<a title="Go to the previous page ('.$pageno['previous'].')" href="'.$current_url.''.$pagevar.'='.$pageno['previous'].'">'.$pageno['previous'].'</a>
			</li>';
		}
		// Initial page display (allow the user to select a page)
		$pages['selection'] .= '
		
			<li class="currentpage">
				<a href="'.$current_url.''.$pagevar.'='.$pageno['current'].'" class="currentpage">'.$pageno['current'].'</a>
			</li>';
		// Next and Last Page
		if($pageno['last'] != 1 AND $pageno['current'] != $pageno['last']){
			// Set Next Page
			$pageno['next'] = $pageno['current'] + 1;
			// Show Next & Last
			$pages['selection'] .= '
			<li>
				<a title="Go to the next page ('.$pageno['next'].')" href="'.$current_url.''.$pagevar.'='.$pageno['next'].'">'.$pageno['next'].'</a>
			</li>';
			// a few more...
			if($pageno['current'] < $pageno['last'] AND $pageno['previous'] != $pageno['last'] - 2){
				// more selection
				$pages['twoafter'] = $pageno['current'] + 2;
				$pages['selection'] .= '
					<li>
						<a title="Go to the page '.$pages['twoafter'].'" href="'.$current_url.''.$pagevar.'='.$pages['twoafter'].'">'.$pages['twoafter'].'</a>
					</li>';
			}
			// Last
			$pages['selection'] .= '
			<li class="prevnext">
				<a title="Go to the last page" href="'.$current_url.''.$pagevar.'='.$pageno['last'].'">Last &raquo;</a>
			</li> ';
		}
		// Finish off the pagination :)
		$pages['selection'] .= '</ul></div>';
		// Wait... is there only 1 page (or 0 if there are no records)?
		if($pageno['last'] == 0 OR $pageno['last'] == 1){
			// Unset it all - we do NOT need it :)
			unset($pages['selection']);
		}
		// Set up the Limit (For the Database)
		$pages['limit'] = 'LIMIT '.$pageno['previous'] * $pp.', '.$pp;
		// If results is 0...
		if($records == 0){
			$pages['limit'] = '';
		}
		// Close the database
		$sql->close();
		// Send back the pagination
		return $pages;
	}
	public function pageSelection($go,$last,$page_var){
		// Get the template
		$template['path'] = core::getCurrentThemeLocation();
		$template['container_p'] = $template['path'].'container.html';
		$template['container'] = file_get_contents($template['container_p']);
		$template['container_p1'] = $template['path'].'core_page_selection.html';
		$template['container1'] = file_get_contents($template['container_p1']);
		// Have we submitted?
		if(isset($_POST['goToUrl'])){
			// Conection
			$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
			// Secure
			$go = str_replace('&amp;','&',urldecode($sql->real_escape_string($go)));
			$last = $sql->real_escape_string($last);
			$page_var = str_replace('&amp;','&',urldecode($sql->real_escape_string($page_var)));
			// Last page
			if($_POST['page_number'] > $last){
				$page = $last;
			}else{
				$page = intval($sql->real_escape_string($_POST['page_number']));
			}
			// Build URL
			$build = $go.$page_var.'='.$page;
			header('Location: '.$build);
			exit();
		}
		// Return
		$template['container'] = str_replace('{content}',$template['container1'],$template['container']);
		$template['container'] = str_replace('{last}',$last,$template['container']);
		$template['container'] = str_replace('{header_title}','Go To Page',$template['container']);
		return $template['container'];
	}
	public function getCurrentThemeLocation(){
		// This function gets the location of the template files.
		// The default theme is /theme/default
		// Set Path
		$root = str_replace('index.php','',$_SERVER['SCRIPT_FILENAME']).'theme';
		if(isset($_SESSION[db::$config['session_prefix'].'_theme'])){
			// Check the theme directory exists...
			if(file_exists($root.'/'.$_SESSION[db::$config['session_prefix'].'_theme'].'/theme.txt')){
				// The file exists...
				$path = $root.'/'.$_SESSION[db::$config['session_prefix'].'_theme'].'/';
				return $path;
			}else{
				unset($_SESSION[db::$config['session_prefix'].'_theme']);
				// Send back the default path
				$path = $root.'/default/';
				return $path;
			}
		}else{
			// Send back the default path
			$path = $root.'/default/';
			return $path;
		}
	}
	public function updateUserLocation($qs,$act){
		// Check we're not a guest
		if(!isset($_SESSION[db::$config['session_prefix'].'_userid'])){
			return;
		}
		// Check we're not in a private section
		if($act == 'moderate' OR $act == 'subscriptions' OR $act == 'logout' OR $act == 'viewmessage' OR $act == 'sendmessage' OR $act == 'profile'){
			return;
		}
		// Conection
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// User ID
		$uid = $sql->real_escape_string($_SESSION[db::$config['session_prefix'].'_userid']);
		// Query String
		$qs = $sql->real_escape_string($qs);
		// Remove some stuff from the QS
		$f = array('&friendly_url_title='.$_GET['friendly_url_title'],'&friendly_url_used='.$_GET['friendly_url_used']);
		$qs = str_replace($f,'',$qs);
		// Date
		$date = time();
		// Update
		$q = "UPDATE `user_session` SET time='$date', location='$qs' WHERE uid='$uid'";
		$this->DB->query($q);
		// If the session hasn't been set
		if($sql->insert_id == 0 OR empty($sql->insert_id)){
			// Add new row
			$q = "INSERT INTO `user_session`(uid,time,location)
				VALUES('$uid','$date','$qs')";
			$this->DB->query($q);
		}
		// Close and return
		$sql->close();
		return;
	}
	public function forumStats(){
		// Does it exist in the cache?
		if(!$this->cache->get('core_forumStats',$this->config['whatsgoingon_cache'])){
			// Set todays date
			$lo = strtotime("-1 day");
			// Get all users...
			$uq = "SELECT time,uid FROM `user_session` WHERE time > '$lo' ORDER BY time DESC";
			$uq = $this->DB->query($uq);
			// get template
			$template['path'] = core::getCurrentThemeLocation();
			$template['container_p'] = $template['path'].'container.html';
			$template['container'] = file_get_contents($template['container_p']);
			$template['container_p1'] = $template['path'].'core_whats_going_on.html';
			$template['container1'] = file_get_contents($template['container_p1']);
			// how many online?
			$online = $uq->num_rows;
			// users currentlyt active
			while($ur = $uq->fetch_assoc()){
				if($num != 0){
					$c = ', ';
				}else{
					$c = '';
				}
				$num ++;
				$gid = $ur['group'];
				// set username
				$username = core::getUsername($ur['uid']);
				// add
				$active .= $c.' <a title="Last active: '.date("j F Y, G:i",$ur['time']).'" style="text-decoration:none;" href="'.core::generateUrl('index.php?profile='.$ur['uid'],'user',$ur['uid'],strip_tags($username),NULL).'">'.$username.'</a>';
			}
			if($online == 0){
				$active = '<em>Nobody has been online today</em>';
			}
			// Now, we show some stats
			// Number of posts
			$pq = "SELECT count(id) AS num_posts FROM `posts` WHERE deleted='0'";
			$pq = $this->DB->query($pq);
			$pr = $pq->fetch_assoc();
			// Number of topics
			$tq = "SELECT count(id) AS num_topics FROM `topics` WHERE deleted='0'";
			$tq = $this->DB->query($tq);
			$tr = $tq->fetch_assoc();
			// Newest member
			$lq = "SELECT id,username,`group` FROM `users` ORDER BY `id` DESC LIMIT 1";
			$lq = $this->DB->query($lq);
			$lr = $lq->fetch_assoc();
			$gid = $lr['group'];
			// Number of members
			$mq = "SELECT count(id) AS num_users FROM `users`";
			$mq = $this->DB->query($mq);
			$mr = $mq->fetch_assoc();
			// Newset member
			$url = core::generateUrl('index.php?profile='.$lr['id'],'user',$lr['id'],$lr['username'],NULL);
			$user = core::getUsername($lr['id']);
			// replacements
			$content = $template['container1'];
			$content = str_replace('{online_users_count}',$online,$content);
			$content = str_replace('{currently_active}',$active,$content);
			$content = str_replace('{num_posts}',$pr['num_posts'],$content);
			$content = str_replace('{num_topics}',$tr['num_topics'],$content);
			$content = str_replace('{num_users}',$mr['num_users'],$content);
			$content = str_replace('{profile_url}',$url,$content);
			$content = str_replace('{newest_user}',$user,$content);
			// container
			$content = str_replace('{content}',$content,$template['container']);
			$content = str_replace('{header_title}','What&#39;s going on?',$content);
			// set the cache
			$this->cache->set('core_forumStats',$content);
		}else{
			$content = $this->cache->get('core_forumStats',$this->config['whatsgoingon_cache']) . '<!-- Loaded from cache -->';
		}
		return $content;
	}
	public function isModerator(){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Get user details
		$uid = $_SESSION[db::$config['session_prefix'].'_userid'];
		$uq = "SELECT `group` FROM `users` WHERE id='$uid' LIMIT 1;";
		$uq = $this->DB->query($uq);
		$ur = $uq->fetch_assoc();
		// Set group id
		$_SESSION[db::$config['session_prefix'].'_groupid'] = $ur['group'];
		// Get group
		$gid = $ur['group'];
		$gq = "SELECT supermod,administrator,access_modcp FROM `groups` WHERE id='$gid' LIMIT 1";
		$gq = $this->DB->query($gq);
		$gr = $gq->fetch_assoc();
		// Can we moderate?
		if($gr['supermod'] == 1 OR $gr['administrator'] == 1 OR $gr['access_modcp'] == 1){
			$sql->close();
			return true;
		}else{
			$sql->close();
			return false;
		}
	}
	public static function errorHandler($errno,$errstr,$errfile,$errline){
		if(!(error_reporting() & $errno)){
			// This error code is not included in error_reporting
			return;
		}
		switch ($errno) {
			case E_USER_ERROR:
				$msg = "CalicoBB Experienced an Error: [$errno] $errstr\n"
					."  Fatal error on line $errline in file $errfile"
					.", PHP " . PHP_VERSION . " (" . PHP_OS . ")\n"
					."Aborting...<br />\n";
				// now post & get
				$msg .= "\nPOST Variables:\n";
				foreach ($_POST as $key => $value) {
					$msg .= "$key = $value\n";
				}
				$msg .= "\n\nGET Variables:\n";
				foreach ($_GET as $key => $value) {
					$msg .= "$key = $value\n";
				}
				// now, session
				$msg .= "\nSESSION Variables:\n";
				foreach ($_SESSION as $key => $value) {
					$msg .= "$key = $value\n";
				}
				// mail to user
				mail(db::$config['email'],'CalicoBB Error',$msg,'From: CalicoBB <'.db::$config['email'].'>');
				// shwo error page
				echo('
					<style type="text/css">
						body{
							font-family: Arial, Tahoma, sans-serif;
						}
					</style>
					<h1>Oops... There appears to be an error</h1>
					<p>Sorry, there is an error with CalicoBB. Please wait a few moments before trying again.</p>
					<p>The web administrator has been informed, and will try to fix this as soon as possible.</p>
					<p><a href="">Try again</a></p>');
				// exit
				exit(1);
			break;
			case E_USER_WARNING:
				//echo "<b>My WARNING</b> [$errno] $errstr<br />\n";
			break;
			case E_USER_NOTICE:
				//echo "<b>My NOTICE</b> [$errno] $errstr<br />\n";
			break;
			default:
				//echo "Unknown error type: [$errno] $errstr<br />\n";
			break;
		}
		/* Don't execute PHP internal error handler */
		return true;
	}
	public static function shutDownFunction(){ 
		$error = error_get_last();
		if($error['type'] == 1){
			// set up email
			$msg = "CalicoBB has experienced an error. Details of which are below:\n\n";
			// get all details
			foreach ($error as $key => $value) {
				$msg .= "$key = $value\n";
			}
			// now post & get
			$msg .= "\nPOST Variables:\n";
			foreach ($_POST as $key => $value) {
				$msg .= "$key = $value\n";
			}
			$msg .= "\n\nGET Variables:\n";
			foreach ($_GET as $key => $value) {
				$msg .= "$key = $value\n";
			}
			// now, session
			$msg .= "\nSESSION Variables:\n";
			foreach ($_SESSION as $key => $value) {
				$msg .= "$key = $value\n";
			}
			// email msg
			// mail to user
			mail(db::$config['email'],'CalicoBB Error',$msg,'From: CalicoBB <'.db::$config['email'].'>');
			// tell end user
			echo('
				<style type="text/css">
					body{
						font-family: Arial, Tahoma, sans-serif;
						font-size: 0.9em;
					}
				</style>
				<h1>Oops... There appears to be an error</h1>
				<p>Sorry, there is an error with CalicoBB. Please wait a few moments before trying again.</p>
				<p>The web administrator has been informed, and will try to fix this as soon as possible.</p>
				<p><a href="">Try again</a></p>');
			// exit
			exit(1);
		}
	}
	public function moderationKey(){
		// do we have one?
		if(!isset($_SESSION[db::$config['session_prefix'].'_mod_key'])){
			$k = sha1(mt_rand(1,100000000));
		}else{
			$k = $_SESSION[db::$config['session_prefix'].'_mod_key'];
		}
	}
}
?>