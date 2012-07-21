<?php
class stopForumSpam{
	public function spamBotCheck($emailAddress,$ipAddress,$userName,$debug = false){
		// *********************************
		// Code originally written by Smurf_Minions (http://guildwarsholland.nl/)
		// Original Source: http://guildwarsholland.nl/phphulp/testspambot.php
		//
		// Modified by Brendan Erskine (http://sysadminspot.com/)
		// Last Modified: 8 May 2010
		// Revision Number: 2.0
		// *********************************
		// Initiate and declare spambot/errorDetected as false - as we're just getting started
		$spambot = false;
		$errorDetected = false;
		// -------------
		// Check email address
		// -------------
		if($emailAddress != ""){
			$xml_string = file_get_contents("http://www.stopforumspam.com/api?email=" . urlencode($emailAddress));
			$xml = new SimpleXMLElement($xml_string);
			if($xml->appears == "yes"){
				$spambot = true; // Check failed. Result indicates dangerous.
				$failed = 'email'; // let them know what caused it to fail
			}elseif($xml->appears == "no"){ // Check passed. Result returned safe.
				$spambot = false; // Check passed. Result returned safe.
			}else{
				$errorDetected = true; // Test returned neither positive or negative result. Service might be down?
			}
		}
		// -------------
		// Check IP Address
		// -------------
		if($spambot != true && $ipAddress != ""){
			$xml_string = file_get_contents("http://www.stopforumspam.com/api?ip=" . urlencode($ipAddress));
			$xml = new SimpleXMLElement($xml_string);
			if($xml->appears == "yes"){ // Was the result was registered
				$spambot = true; // Check failed. Result indicates dangerous.
				$failed = 'IP'; // let them know what caused it to fail
			}elseif($xml->appears == "no"){ // Check passed. Result returned safe.
				$spambot = false; // Check passed. Result returned safe.
			}else{
				$errorDetected = true; // Test returned neither positive or negative result. Service might be down?
			}
		}
		// -------------
		// Check Username
		// -------------
		/*if($spambot != true && $userName != ""){
			$xml_string = file_get_contents("http://www.stopforumspam.com/api?username=" . urlencode($userName));
			$xml = new SimpleXMLElement($xml_string);
			if($xml->appears == "yes"){// Was the result was registered		
				$spambot = true; // Check failed. Result indicates dangerous.
				$failed = 'username'; // let them know what caused it to fail
			}elseif($xml->appears == "no"){ // Check passed. Result returned safe.
				$spambot = false; // Check passed. Result returned safe.
			}else{
				$errorDetected = true; // Test returned neither positive or negative result. Service might be down?
			}
		}*/
		// To debug function, call it with the debug flag as true and instead the function will return whether or not an error was detected, rather than the test result.
		if($debug == true){
			return $errorDetected; // If enabled, return whether or not an error was detected
		}else{
			// make it into an array
			$return['spambot'] = $spambot;
			$return['reason'] = $failed;
			return $return; // Return test results as either true/false or 1/0
		}
	}
}
?>