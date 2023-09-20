<?php

class FileCache{
	public $cachepath;
	
	function __construct($cachepath){
		if(!$cachepath){
			$this->cachepath = APP_PATH."/data/cache/filecache/";
		}else{
			$this->cachepath = $cachepath;
		}
	}
	
	function get($key,$expire = 3600){
		$cachefile = $this->cachepath.md5($key).'.tmp';
		if(!file_exists($cachefile) || filemtime($cachefile) < (time()-$expire)){	//缓存时间，默认1个小时
			return false;
		}else{
			return unserialize(file_get_contents($cachefile));
		}
	}
	
	function set($key,$data){
		if (!is_dir($this->cachepath)) makedir($this->cachepath);
		$cachefile = $this->cachepath.md5($key).'.tmp';
		file_put_contents($cachefile,serialize($data));
		return true;
	}
	
}