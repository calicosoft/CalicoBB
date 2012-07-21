<?php 
/** 
 * 使用文件系统来缓存数据 
 *  
 * @author pangzhihui<pangzhihui2001@163.com> 
 * @final 2010-6-10 
 * @copyright 0.9 beta 
 * @example  
 *  
 * page1 
 * $array = array("a"=>"a_val","b"=>"afaf"); 
 * $cache = new FileCache(); 
 * $cache -> set("yourname",$array); 
 *  
 * page2 
 * $cache = new FileCache(); 
 * $array = $cache -> get("yourname"); 
 * print_r($array); 
 */ 

if(!defined("CACHE_DIR")){ 
    define("CACHE_DIR",dirname(dirname(dirname(__FILE__)))."/cache"); 
}; 

class FileCache{ 
    var $_sCacheDir; 
    var $_sCache; 

    /** 
     * Enter description here... 
     * 
     * @return FileCache 
     */ 
    function __construct(){ 
        $this -> _sCacheDir = CACHE_DIR; 
        if(!is_dir($this -> _sCacheDir)){ 
            if(!mkdir($this -> _sCacheDir,0777)){ 
                echo 'Cache file is not permission!'; 
                return false; 
            } 
        } 
    } 

    /** 
     * � �据key值自动散列路径 
     * 
     * @param unknown_type $key 
     * @return unknown 
     */ 
    function getDir($key){ 
        $num = $this -> Md5Hash($key); 
        $dir = $this -> hashDir($num); 
        return $dir; 
    } 

    /** 
     * 生成路径 
     * 
     * @param unknown_type $file_num 
     */ 
    function hashDir($num,$file_num=1000,$m=3){ 
        $dir = $this -> _sCacheDir; 
        for ($i=1;$i<$m;$i++){ 
            $dir .= "/".round($num/(pow($file_num,$i))); 
            if(!is_dir($dir)){ 
                mkdir($dir); 
            } 
        } 
        return $dir; 
    } 

    /** 
     * � �据md5值来的生成一个数字，用于散列文件 
     * 
     * @param unknown_type $str 
     * @return unknown 
     */ 
    function md5Hash($str) 
    { 
        $hash = md5($str); 
        $hash = $hash[0] | ($hash[1] <<8 ) | ($hash[2] <<16) | ($hash[3] <<24) | ($hash[4] <<32) | ($hash[5] <<40) | ($hash[6] <<48) | ($hash[7] <<56);
        return $hash % 701819; 
    } 

    /** 
     * 获取缓存 
     * 
     */ 
    public function get($key,$time=3600){
        if(CALICO_USE_CACHE == true){
			$dir = $this -> getDir($key);
			if(!empty($dir)){
				$file = $dir."/".$key.'.cache';
			}
			if(!file_exists($file)){
				return false;
			}else{
				//上次修改距离现在时�
				$c_time = time() - filectime($file);
				//
				if($time > $c_time){
					$str = file_get_contents($file);
					return unserialize($str);
				}else{
					return false;
				}
			}
		}else{
			return false;
		}
    }
	
	/**
	 * Added for CalicoBB
	 * Checks whether we can load this from the cache (or not)
	 */
	public function exists($key,$time=3600){
        // Did we set up the caching?
		if(CALICO_USE_CACHE == true){
			// get the directory
			$dir = $this->getDir($key);
			if(!empty($dir)){
				// try to set up a new one
				$file = $dir."/".$key.'.cache';
			}
			if(!file_exists($file)){
				// file doesn't exist
				return false;
			}else{
				$c_time = time() - filectime($file);
				// check file time
				if($time > $c_time){
					// file is ok, so return true
					$str = file_get_contents($file);
					return true;
				}else{
					// file is not ok.
					return false;
				}
			}
		}else{
			return false;
		}
    }

    /** 
     * 一次性获取多个缓存 
     * 
     */ 

    public function getMulti($key_array){ 

        return $array; 
    } 

    /** 
     * 放缓存 
     * 
     */ 
    public function set($key,$val){ 
        if(CALICO_USE_CACHE == true){
			$dir  =  $this -> getDir($key);
			$file = $dir."/".$key.'.cache';
			$str  = serialize($val);
			
			// Added for CalicoBB
			$str = '<!-- Added to Cache: '.time().' -->' . $str;
			
			if(file_put_contents($file,$str)){
				return true;
			}else{
				//echo 'Cache file is not permission!';
				return false;
			}
		}
    } 

    /** 
     * 一次性存储多个缓存 
     * 
     */ 
    public function setMulti($array){ 

        return true; 
    } 

    /** 
     * � 除缓存 
     * 
     */ 
    public function delete($key){ 
        $dir  =  $this -> getDir($key); 
        $file = $dir."/".$key.'.cache'; 
        if(file_exists($file)){ 
            if(!unlink($file)){ 
                echo 'Cache file is not permission!'; 
                return false; 
            } 
        } 
        return true; 
    } 

} 
?>