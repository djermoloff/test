<?php
class Language {
	public $err;
	public $lang;
	public $dir;
	public $replace;
	
	function __construct () {
		try {
			if (isset($_GET['lang'])) {
				$dir = __ROOT__."/lang/".$_GET['lang'];
				if (is_dir($dir)) {
					$this->SetCookie($_GET['lang']);
					throw new Exception ($_GET['lang']);
				} else throw new Exception (DEFAULT_LANG);

			} else {
				if (!isset($_COOKIE['lang'])) throw new Exception (DEFAULT_LANG);
				$dir = __ROOT__."/lang/".$_COOKIE['lang'];
				if (is_dir($dir)) throw new Exception ($_COOKIE['lang']);
					else throw new Exception (DEFAULT_LANG);
			}
		} 
		catch (Exception $lg) {
			$this->dir = __ROOT__."/lang/".$lg->getMessage()."/";
			$this->lang = $lg->getMessage();
			$this->replace['lang'] = strtoupper($lg->getMessage());
		}
	}
	
	function SetCookie($lg) {
		setcookie("lang", $lg, time()+86400*91, "/", ".".$_SERVER['SERVER_NAME']);
	}
	
	function GetText($file,$index) {
		try {
			$file = $this->dir.$file.".php";
			if (!file_exists($file)) throw new Exception ("LANG_FILE_MISSING");
			require_once($file);
			if (!isset($msg[$index])) throw new Exception ("LANG_INDEX_MISSING");
			return $msg[$index];
		}
		catch (Exception $err) {
			$this->err = $err->getMessage();
			return false;
		}
	}
	
	function AddReplaceFile($file) {
		try {
			$file = $this->dir.$file.".php";
			if (!file_exists($file)) throw new Exception ("LANG_FILE_MISSING");
			require_once($file);
			foreach ($_lg as $key=>$value) {
				$this->replace[$key] = $value;
			}
			return true;
		}
		catch (Exception $err) {
			$this->err = $err->getMessage();
			return false;
		}
	}
	
	function ReplaceView($html) {
		foreach ($this->replace as $key=>$value) {
			$find = "[".$key."]";
			$html = str_replace($find,$value,$html);
		}
		return($html);
	}
	
	function CountryList($get = "") {
		try {
			$file = $this->dir."country.json";
			if (!file_exists($file)) throw new Exception ("COUNTRY_FILE_MISSING");
			$res = file_get_contents($file);
			$arr = json_decode($res);
			$result = "";
			if ($get == "") {
				$result = array();
				foreach ($arr as $key=>$value) {
					array_push($result,array("index"=>$key,"value"=>$value));
				}
			} else if (isset($arr->$get)) $result = $arr->$get;
			return ($result);
		}
		catch (Exception $err) {
			$this->err = $err->getMessage();
			return false;
		}
	}
}
?>