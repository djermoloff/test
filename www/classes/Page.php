<?php
class Page {
	public $err;
	public $sql;
	public $page;
	public $title;
	public $meta_k;
	public $meta_d;
	public $text;
	public $lang;
	private $lg;
	
	
	function __construct () {
		require_once("MySQL.php");
		$this->sql = new MySQL(DB_HOST,DB_USER,DB_PASS,DB_NAME);
		require_once("Lang.php");
		$this->lg = new Language;
		$this->lang = $this->lg->lang;
	}
	
	function GetPage($page) {
		
		$arr = array("page"=>$page,"lang"=>$this->lang,"public"=>true);
		$res = $this->sql->Select("pages",$arr);
		if ($res === false) { $this->err = "PAGE_NOT_FOUND"; return false; }
		foreach($res as $index=>$value) {
			if (property_exists($this,$index) == true) $this->$index = $res[$index];
		}
		return true;
	}
}
?>