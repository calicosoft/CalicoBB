<?php
class calicobb{
	// the configuration vars
	public $config;
	// the current user variables
	public $uid = 0; // contains the user ID
	public $gid = 0; // contains the group ID
	// database variables
	public $DB; // contains the connection
	public $query_id; // contains the query ID
	public $query_count; // how many queries have there been?
	// cache variables
	public $cache;
	public $test = 'hi';
	// initiate
	public function __construct(){
		// set the config
		foreach(db::$config as $k => $v){
			$this->config[$k] = $v;
		}
		// first, set up the caching
		if(!defined('CALICO_USE_CACHE')){
			define('CALICO_USE_CACHE',$this->config['use_cache']);
		}
		// set cache directory
		if(!defined('CACHE_DIR')){
			define('CACHE_DIR',dirname(dirname(__FILE__)).'/cache');
		}
		// start up cache
		require_once('cache.class.php');
		$this->cache = new FileCache();	
		// now, set up the DB
		$this->connectToDatabase();
		// now, set up the user session
		$this->setUserSession();
		// are we logged in? If so, update our location
		if($this->uid > 0){
			// update location
			$this->updateUserSession();
		}
	}
	// start up functions
	public function startup(){
		
	}
	// connect to database
	public function connectToDatabase(){
		// connect to database
		$this->DB = new mysqli($this->config['host'],$this->config['user'],$this->config['pass'],$this->config['db']);
		// were we able to connect?
		if(!isset($this->DB)){
			// this is an error
			exit('CalicoBB could not connect to the database.');
		}
		// send back
		return $this->DB;
	}
	// perform a query
	public function query($query){
		// perform query
		$this->query_id = $this->DB->query($query);
		// did it succeed?
		// increase query count
		$this->query_count ++;
		// return
		return $this->query_id;
	}
	// get number of rows
	public function num_rows($query_id){
		// get number of rows
		return mysqli_num_rows($query_id);
	}
	// gets the results array
	public function results_array($query_id = ''){
		// check we've set a query ID
		if(!$query_id){
			$query_id = $this->query_id;
		}
		// get the results
		$results = mysqli_fetch_assoc($query_id);
		// return
		return $results;
	}
	// escape string
	public function escape_string($s){
		return mysqli_real_escape_string($s);
	}
	// set up the user session
	public function setUserSession(){
		// are we logged in?
		if(!isset($_SESSION[$this->config['session_prefix'].'_userid'])){
			// we are not logged in. set uid and gid
			$this->uid = 0;
			$this->gid = 5; // this is the guest group
		}else{
			// we are logged in. set uid
			$this->uid = $_SESSION[$this->config['session_prefix'].'_userid'];
			// now, get gid
			if(!isset($_SESSION[$this->config['session_prefix'].'_groupid'])){
				// get it from DB, and save it
				$q = "SELECT `group` FROM `users` WHERE id='".$this->uid."' LIMIT 1;";
				$q = $this->DB->query($q);
				$r = $q->fetch_assoc();
				// set gid
				$this->gid = $r['group'];
				$_SESSION[$this->config['session_prefix'].'_groupid'] = $this->gid;
			}else{
				// we have it saved already
				$this->gid = $_SESSION[$this->config['session_prefix'].'_groupid'];
			}
			$this->config['user'] = 'hello there';
		}
	}
	// updates the user session (for "what's going on?")
	public function updateUserSession(){
		// Query String
		$qs = htmlspecialchars($this->DB->real_escape_string($_SERVER['QUERY_STRING']));
		// Remove some stuff from the QS
		$f = array('&friendly_url_title='.$_GET['friendly_url_title'],'&friendly_url_used='.$_GET['friendly_url_used']);
		$qs = str_replace($f,'',$qs);
		// Date
		$date = time();
		// Update
		$q = "UPDATE `user_session` SET time='$date', location='$qs' WHERE uid='".$this->uid."' LIMIT 1;";
		$q = $this->DB->query($q);
		// If the session hasn't been set
		if(@$q->num_rows > 0){
			// Add new row
			$q = "INSERT INTO `user_session`(uid,time,location)
				VALUES('".$this->uid."','$date','$qs')";
			$this->query($q);
		}
	}
	// check if we can access a function
	public function checkPermission($action){
		// first, is the UID set?
		//if(!isset
	}
}
?>