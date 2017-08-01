<?php
class MySQL {
	public $err;
	public $db;
	public $query;
	
	function __construct ($host,$user,$pass,$base) {
		$db = mysqli_connect($host, $user, $pass, $base);
		mysqli_query($db, 'SET CHARSET UTF8');

		mysqli_query ($db,"set character_set_results='utf8'"); 
		mysqli_query ($db,"set collation_connection='utf8_general_ci'");
		mysqli_query ($db,"SET @@global.sql_mode= ''");
		$this->db = $db;
	}
	
	function Query() {
		$result = mysqli_query($this->db, $this->query);
		if(!$result) {
			$this->err = mysqli_error($this->db);
			return false; 
		} else { 
			return true; 
		}
	}
	
	function Insert ($table,$arr) {
		$param = "";
		$values = "";
		foreach ($arr as $k => $v) {
			if ($param != "") $param .= ",";
			if ($values != "") $values .= ","; 
			$param .= "`".$k."`";
			$values .= "'".$this->mysql_text($arr[$k])."'";
		}
		
		$this->query = "INSERT INTO ".$table." (".$param.") VALUES (".$values.")";
		$result = mysqli_query($this->db, $this->query);
		if(!$result) {
			$this->err = mysqli_error($this->db);
			return false; 
		} else { 
			return mysqli_insert_id($this->db); 
		}
	}
	
	function Select($table,$arr) {
		$this->query = "SELECT * FROM ".$table." WHERE ";
		$param = "";
		foreach ($arr as $k => $v) {
			if ($param != "") $param .= " AND ";
			$param .= "`".$k."`='".$this->mysql_text($arr[$k])."'";
		}
		$this->query .= $param;

		$result = mysqli_query($this->db,$this->query);
		if ($result === false) {
			$this->err = mysqli_error($this->db); 
			return false; 
		}
		if (mysqli_num_rows($result) == 0) return false;
		$myrow = mysqli_fetch_assoc($result);
		return $myrow;
	}
	
	function GetSetting($index) {
		$this->query = "SELECT value FROM settings WHERE `index`='".$this->mysql_text($index)."'";

		$result = mysqli_query($this->db,$this->query);
		if ($result === false) {
			$this->err = mysqli_error($this->db); 
			return false; 
		}
		if (mysqli_num_rows($result) == 0) return false;
		$myrow = mysqli_fetch_assoc($result);
		return $myrow['value'];
	}
	
	function UpdateID ($table, $arr, $id) {
		$id = (int)$id;
		$this->query = "UPDATE ".$table." SET ";
		$param = "";
		foreach ($arr as $k => $v) {
			if ($param != "") $param .= ", ";
			$param .= "`".$k."`='".$this->mysql_text($arr[$k])."'";
		}
		$this->query .= $param." WHERE id='".$id."'";
		
		$result = mysqli_query($this->db, $this->query);
		if(!$result) {
			$this->err = mysqli_error($this->db);
			return false; 
		} else { 
			return true; 
		}
	}
	
	function mysql_text ($text) {
		return mysqli_real_escape_string($this->db,stripslashes(trim($text))); 
	}
}
?>