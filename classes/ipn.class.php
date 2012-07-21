<?php

// Original Author: Pineapple Technologies 
// License: Free (GPL)
//
// Modified By: ScriptDevelopers.NET (http://www.scriptdevelopers.net)
// Modified Date: September 14, 2003
// 
// Modified to use cURL as PayPal now does a redirect no matter
// whether you use http or https, or get or post. Using cURL, you
// can have PHP properly follow the redirect and have the expected
// VERIFIED or INVALID responses.
//

class paypal_ipn{
	var $paypal_post_vars;
	var $paypal_response;
	var $protocol;
	var $url_string;
	var $timeout;
	// error logging info
	var $error_log_file;
	var $error_email;
	
	function paypal_ipn($paypal_post_vars, $protocol = "s"){
		$this->paypal_post_vars = $paypal_post_vars;
		$this->protocol = $protocol;
		$this->timeout = 120;
		$this->url_string = "http" . $this->protocol . "://www.sandbox.paypal.com/cgi-bin/webscr?";
	}
	
	// sends response back to paypal
	function send_response(){
		// put all POST variables received from Paypal back into a URL encoded string
		foreach($this->paypal_post_vars AS $key => $value){
			// if magic quotes gpc is on, PHP added slashes to the values so we need
			// to strip them before we send the data back to Paypal.
			if(@get_magic_quotes_gpc()){
				$value = stripslashes($value);
			}
			// make an array of URL encoded values
			$values[] = "$key" . "=" . urlencode($value);
		}
		// join the values together into one url encoded string
		$this->url_string .= @implode("&", $values);
		// add paypal cmd variable
		$this->url_string .= "&cmd=_notify-validate";
		// CURL
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, $this->url_string);
		curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; www.ScriptDevelopers.NET; PayPal IPN Class)");
		curl_setopt ($ch, CURLOPT_HEADER, 1);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt ($ch, CURLOPT_TIMEOUT, $this->timeout);
		// Response...
		$this->paypal_response = curl_exec ($ch);
		// Close CURL
		curl_close($ch);
	}

	// returns true if paypal says the order is good, false if not
	function is_verified(){
		if(ereg("VERIFIED", $this->paypal_response)){
			return true;
		}else{
			return false;
		}
	}

	// returns the paypal payment status
	function get_payment_status(){
		return $this->paypal_post_vars['payment_status'];
	}

	// writes error to logfile, exits script
	function error_out($message){
		$date = date("D M j G:i:s T Y", time());
		// add on the data we sent:
		$message .= "\n\nThe following input was received from (and sent back to) PayPal:\n\n";
		@reset($this->paypal_post_vars);
		while(@list($key,$value) = @each($this->paypal_post_vars)){
			$message .= $key . ':' . " \t$value\n";
		}
		$message .= "\n\n" . $this->url_string . "\n\n" . $this->paypal_response;
		// log to file?
		if($this->error_log_file){
			@fopen($this->error_log_file, 'a');
			$message = "$date\n\n" . $message . "\n\n";
			@fputs($fp, $message);
			@fclose($fp);
		}
		// email errors?
		if($this->error_email){
			$additional_headers = "From: \"$fromname\" <$from>\nReply-To: $from";
			mail($this->error_email, "[$date] paypay_ipn error", $message, $additional_headers);
		}
		exit;
	}
} // end class paypal_ipn

?>
