<?php
class Admin {
	public $err;
	public $error_code;
	public $db;
	public $data;
	public $sql;
	
	private $t_list;
	private $start;
	
	function __construct () {
		require_once("MySQL.php");
		$this->sql = new MySQL(DB_HOST,DB_USER,DB_PASS,DB_NAME);
		$this->t_list = 10;
	}
	
	function Users($list = 1) {
		$this->GetStartList("users",(int)$list);
		$query = "SELECT id,name,family,email FROM users ORDER BY id DESC LIMIT ".$this->start.",".$this->t_list;

		$result = mysqli_query($this->sql->db,$query);
		$arr = array();
		while($myrow = mysqli_fetch_assoc($result)) {
			array_push($arr,$myrow);
		}
		return $arr;
	}
	
	function GetStartList($table,$list) {
		$t_res = mysqli_query($this->sql->db,"SELECT COUNT(*) FROM ".$table);
		$temp = mysqli_fetch_array($t_res);
		$posts = $temp[0];
		$total = (($posts - 1) / $this->t_list) + 1;
		$total =  intval($total);
		$list = intval($list);
		if(empty($list) or $list < 0) $list = 1;
		if($list > $total) $list = $total;
		$this->start = $list * $this->t_list - $this->t_list;
		if ($this->start < 0) {$this->t_list = $this->t_list + $this->start; $this->start = 0;}
	}
}
?>