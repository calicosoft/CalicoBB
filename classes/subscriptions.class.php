<?php
class subscriptions extends calicobb{
	public function subsHomepage(){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Show initial content
		$content = '
<form action="index.php?act=subscriptions&do=pay" method="post">
	<table width="100%" class="forumIndex">
		<caption>
			<span>
				<b class="sControl"></b>
				Your Available Subscriptions
			</span>
		</caption>
		<thead>
			<tr>
				<th style="width:5%;"></th>
				<th style="width:55%;">Package Details</th>
				<th style="width:20%;">Duration</th>
				<th style="width:20%;">Cost</th>
			</tr>
		<tbody>
			';
		// Get all subscriptions
		$sq = "SELECT * FROM `subscriptions` ORDER BY cost";
		$sq = $sql->query($sq);
		while($sr = $sq->fetch_assoc()){
			// Duration
			$duration = $sr['duration'] . ' days';
			// Show sub information
			$content .= '
			<tr>
				<td style="vertical-align:middle;"><input type="radio" name="package" value="'.$sr['id'].'" /></td>
				<td><strong>'.$sr['title'].'</strong><br />'.$sr['description'].'</td>
				<td style="vertical-align:middle;text-align:center">'.$duration.'</td>
				<td style="vertical-align:middle;text-align:center">'.db::$config['currency_sign'].''.$sr['cost'].'</td>
			</tr>';
		}
		$content .= '
			<tr>
				<td colspan="4">
					<input type="submit" value="Choose Package" name="sub" class="submitEnabled" />
				</td>
			</tr>
		</tbody>
	</table>
	<div class="clippers"><div></div><span></span></div>
</form>';
		// Close off mysqli
		$sql->close();
		// Send back
		return $content;
	}
	public function paySubscription(){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// Safe variable
		$id = $sql->real_escape_string($_POST['package']);
		// Check ye old package exists, me matey
		$sq = "SELECT count(id) AS d_exists FROM `subscriptions` WHERE id='$id'";
		$sq = $sql->query($sq);
		$sr = $sq->fetch_assoc();
		// Does she exist?
		if($sr['d_exists'] == 0){
			$content = core::errorMessage('subs_not_found');
			return $content;
		}
		// Now, we get package details
		$sq = "SELECT * FROM `subscriptions` WHERE id='$id'";
		$sq = $sql->query($sq);
		$sr = $sq->fetch_assoc();
		// Create papal, er PayPal, button...
		$url = 'http://'.$_SERVER['SERVER_NAME'].''.$_SERVER['SCRIPT_NAME'].'';
		$pp = '<form action="https://www.paypal.com/cgi-bin/webscr" id="paypal" method="post">
	<input type="hidden" name="cmd" value="_xclick" />
	<input type="hidden" name="business" value="'.db::$config['paypal'].'" />
	<input name="item_name" type="hidden" value="'.$sr['title'].'" />
	<input type="hidden" name="item_number" value="'.$sr['id'].'|'.$_SESSION[''.db::$config['session_prefix'].'_userid'].'" />
	<input type="hidden" name="amount" value="'.$sr['cost'].'" />
	<input type="hidden" name="currency_code" value="'.db::$config['currency_code'].'" />
	<input type="hidden" name="rm" value="2" />
	<input type="hidden" name="return" value="'.$url.'?act=subscriptions&do=return" />
	<input type="hidden" name="cancel_return" value="'.$url.'?act=subscriptions" /> 
	<input type="hidden" name="notify_url" value="'.$url.'?act=subscriptions&do=ipn" />
	<input type="button" class="buttonEnabled" name="sub" value="Pay via PayPal..." />
	<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
</form>';
		// Show message...
		$content = '
<table width="100%" class="forumIndex">
	<caption>
		<span>
			<b class="sControl"></b>
			Pay Subscription
		</span>
	</caption>
	<thead>
		<tr>
			<th>You should automatically be redirected to PayPal. If you are not, click the button below.<br /><br />'.$pp.'</th>
		</tr>
	</thead>
</table>
<div class="clippers"><div></div><span></span></div>';
		// Close them off
		$sql->close();
		// Send er back
		return $content;
	}
	public function returnFromProcessor(){
		// Show message...
		$content = core::errorMessage('subs_return');
		// Send back
		return $content;
	}
	public function ipn(){
		// Connect To Database
		$sql = new mysqli(db::$config['host'], db::$config['user'], db::$config['pass'], db::$config['db']);
		// read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';
		foreach ($_POST as $key => $value) {
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
		}
		// post back to PayPal system to validate
		$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
		$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);
		// assign posted variables to local variables
		$item_name = $_POST['item_name'];
		$item_number = $_POST['item_number'];
		$payment_status = $_POST['payment_status'];
		$payment_amount = $_POST['mc_gross'];
		$payment_currency = $_POST['mc_currency'];
		$txn_id = $_POST['txn_id'];
		$receiver_email = $_POST['business'];
		$payer_email = $_POST['payer_email'];
		$country = $_POST['residence_country'];
		if(!isset($_POST['item_number'])){
			$item_number = $_POST['item_number1'];
		}
		if(!isset($_POST['mc_gross'])){
			$payment_amount = $_POST['mc_gross1'];
		}
		if(!$fp){
			// could not open the connection.  If loggin is on, the error message
			// will be in the log.
			$last_error = "fsockopen error no. $errnum: $errstr";
			//exit($last_error);
		}else{
			fputs($fp, $header . $req);
			while(!feof($fp)) {
				$res = fgets ($fp, 1024);
				if($res != "VERIFIED"){
					// check the payment_status is Completed
					if($payment_status != 'Completed'){
						$last_error = "Payment was not completed.";
						//exit($last_error);
					}
					// Set UID and SID
					$info = explode("|",$item_number);
					$uid = $sql->real_escape_string($info[1]);
					$sid = $sql->real_escape_string($info[0]);
					// Get subscription details :)
					$sq = "SELECT * FROM `subscriptions` WHERE id='$sid'";
					$sq = $sql->query($sq);
					$sr = $sq->fetch_assoc();
					// Check our subscription exists
					if($sq->num_rows != 1){
						$last_error = "Invalid order ID: $item_number";
						//exit($last_error);
					}
					// check that receiver_email is your Primary PayPal email
					if($receiver_email != db::$config['paypal']){
						$last_error = "$receiver_email is not the correct PayPal email";
						//exit($last_error);
					}
					// check that payment_amount is correct
					if($sr['cost'] != $payment_amount){
						$cost = $sr['cost'];
						$last_error = "The total paid was incorrect. We wanted $cost, but we received $payment_amount.";
						//exit($last_error);
					}
					// check that payment_amount is correct
					/*if($currency_c != $payment_currency){
						$last_error = "The the currency was WRONG. Customer paid in $payment_currency, we want $currency_c";
						//exit($last_error);
					}*/
				}else{
					$last_error = 'Payment was not verified';
				}
			}
			fclose ($fp);
		}
		if(isset($last_error)){
			mail('papublishers@gmail.com','err',$last_error,'From: papublishers@gmail.com');
			exit();
		}
		// Now, we can get user details :)
		$uq = "SELECT * FROM `users` WHERE id='$uid'";
		$uq = $sql->query($uq);
		$ur = $uq->fetch_assoc();
		// Move user group
		$c_gid = $ur['group'];
		$n_gid = $sr['new_group'];
		// Move 'em
		$q = "UPDATE `users` SET `group`='$n_gid' WHERE id='$uid'";
		$sql->query($q);
		// Assorted values
		$c_time = time();
		$username = $ur['username'];
		$sub_name = $sr['title'];
		$f_name = db::$config['site_name'];
		// Expires
		$duration = explode("|",$sr['duration']);
		$e_val = 'Error generating expiry - please contact an admin';
		if($duration[0] == 'Never'){
			$e_time = 0;
			$e_val = 'Never (Unlimited)';
		}elseif($duration[1] == 'months' OR $duration[1] == 'month'){
			$e_time = time() * 2592000;
			$e_val = str_replace('|',' ',$sr['duration']);
		}elseif($duration[1] == 'Weeks' OR $duration[1] == 'Week'){
			$e_time = time() * 604800;
			$e_val = str_replace('|',' ',$sr['duration']);
		}elseif($duration[1] == 'Days' OR $duration[1] == 'Day'){
			$e_time = time() * 86400;
			$e_val = str_replace('|',' ',$sr['duration']);
		}elseif($duration[1] == 'Hours' OR $duration[1] == 'Hours'){
			$e_time = time() * 3600;
			$e_val = str_replace('|',' ',$sr['duration']);
		}
		// Insert into subscriptions payment
		$q = "INSERT INTO `subscription_payments`(mid,sid,old_group,start_date,end_date,paid,trans_id)
			VALUES('$uid','$sid','$c_gid','$c_time','$e_time','1','$txn_id')";
		$sql->query($q);
		// Send 'em a PM
		$pm['msg'] = "$username,".
					"\n\nThank you for purchasing a new subscription! Your subscription is now active.".
					"\n\n----------------------".
					"\nSubscription Details".
					"\n----------------------".
					"\nPackage: $sub_name".
					"\nExpires: $e_val".
					"\nCost: $payment_amount".
					"\nStatus: PAID".
					"\n\nThank you for your continuing support,".
					"\n$f_name";
		$pm['subject'] = 'New Subscription Purchase';
		$_SESSION[''.db::$config['session_prefix'].'_userid'] = $uid;
		// Send it
		user::insertNewPrivateMessage($uid,$pm['subject'],$pm['msg']);
		user::insertNewPrivateMessage($uid,$pm['subject'],'0: '.$duration[0].' 1:'.$duration[1].'');
		// we're done
		$sql->close();
		return;
	}
}
?>