<?php
class db {
	public static $config = array();
	public static $sql;
	
	function __construct() {
		# Build Config
		db::$config = array(
			// DATABASE CONFIGURATION
			'host' => 'localhost', //normally localhost
			'user' => ' ', //enter your dbusername here
			'pass' => ' ', // enter your db password here
			'db' => '   ', //enter the database table here
			
			// SITE CONFIGURATION
			'mod_emails' => '', //enter all the moderator emails, seperated by a COMMA.
			'site_name' => 'CalicoBB Forum', // enter your sitename here
			'siteurl' => 'http://'.$_SERVER['SERVER_NAME'], // enter the url to the site. leave the $_SERVER variable if you wish.
			'email' => ' ', // enter your email address for forum mailings
			'seo_urls' => false, // do you wish to enable seo tags in the url? set to true or false.
			'root' => ' ', // the root location of your forums
			'session_prefix' => 'bb', // the session prefix
			
			// SUBSCRIPTIONS CONFIGURATION
			'use_subscriptions' => true, // do you wish to enable subscriptions? true or false.
			'currency_sign' => '$', // your currency sign - use HTML value (eg £ = &pound;)
			'currency_code' => 'USD', // currency code - eg GBP, USD...
			'paypal' => ' ', // your PayPal email address...
			
			// RECAPTCHA CONFIGURATION
			'use_recaptcha' => false, // do you wish to use ReCaptcha? set to true or false.
			'recaptcha_public_key' => '6Ld_G8MSAAAAAMztvgUnRkBTXzjyVmB9hGHwYYw_', // enter your reCaptcha public key
			'recaptcha_private_key' => '6Ld_G8MSAAAAAEB0G4NFTqt6Vzuwnp_HyhwEEdDN', // enter your reCaptcha private key
			
			// SOLVEMEDIA CONFIGURATION
			'use_solvemedia' => true, // do you wish to use solvemedia? true or false
			'solvemedia_public_key' => 'vA9DYTwOFz-GdSNWolVK-9MyRI0Lyjue', // enter your SolveMedia public key
			'solvemedia_private_key' => 'GBuJO6AQmOCHcL8wom-GDWF8gO5BF7rt', // enter your SolveMedia private key
			'solvemedia_hash' => 'L8jg9bBF029CDCmGKY9PDTFB-Q39rJWb', // enter your SolveMedia private key
			
			// GENERAL SETUP
			'user_verification' => true, // enter true to require user email verification
			'search_limit' => '25', // the max number of results to be shown in the searchresults
			'edit_time' => '15', // the number of minutes a user has to edit a post...
			'strip_tags' => true, // do you want to strip tags from usernames and group names (getUsername & getGroup)
			'max_warning_points' => 5, // how many warning points can a user get?
			'require_warnings_read' => true, // will the system show the user their warnings before they can do anything else?

			// CACHE
			'use_cache' => true, // do we use caching? true/false
			'homepage_cache' => '120', // how many seconds to cache the homepage?
			'profile_cache' => '900', // how many seconds to cache profiles?
			'post_cache' => '300', // how many seconds to cache posts?
			'newposts_cache' => '900', // how many seconds to cache new posts?
			'whatsgoingon_cache' => '900' // how many seconds to cache whats going on?
		);
	}
}
?>