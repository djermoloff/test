<?php
class URL {
	var $err;
	
	function ArrayURL () {
		$url = array();
		$str_url = "";
		$pos = strpos($_SERVER['REQUEST_URI'],"/?");
		if ($pos === false) $pos = 1000;
		if ($pos > 0) {
			$pos--;
			$str_url = substr($_SERVER['REQUEST_URI'],1,$pos);
		}
		$url = explode("/",$str_url);
		return $url;
	}
}
?>