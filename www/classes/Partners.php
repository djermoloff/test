<?php
class Partners {
	public $err;
	public $error_code;
	public $db;
	public $sql;
	public $payment;
	public $personal_amount = 30; //Вознаграждение спонсору за активацию аккаунта
	public $trio_compinsation = 30; //Вознаграждение трио
	
	public $c_level_1 = 35; // Вознаграждения по уровням
	public $c_level_2 = 20;
	public $c_level_3 = 20;
	public $c_level_4 = 15;
	public $c_level_5 = 10;
	public $c_level_6 = 10;
	public $c_level_7 = 10;
	public $c_level_8 = 12;
	public $c_level_9 = 15;
	public $c_level_10 = 17;
	public $c_level_11 = 5;
	
	public $t_status_1 = 200;
	public $a_status_1 = 400;
	public $t_status_2 = 800;
	public $a_status_2 = 2000;
	public $t_status_3 = 1600;
	public $a_status_3 = 4000;
	public $t_status_4 = 2800;
	public $a_status_4 = 7000;
	public $t_status_5 = 6200;
	public $a_status_5 = 20000;
	public $t_status_6 = 20000;
	public $a_status_6 = 40000;
	
	public $cell_refer;
	public $cell_user;
	public $id;
	
	function __construct () {
		require_once("MySQL.php");
		$this->sql = new MySQL(DB_HOST,DB_USER,DB_PASS,DB_NAME);
		require_once(__ROOT__."/classes/Payment.php");
		$this->payment = new Payment;
	}
	
	function CreateCell($id_user,$id_refer,$pay = 1) {
		$arr = array("id_user"=>$id_refer);
		$this->cell_refer = $this->sql->Select("cells",$arr);
		if ($this->cell_refer === false) throw new Exception ("REFER_CELL_NOT_FOUND");
		
		$myrow = $this->SearchFreeCell($this->cell_refer);
		if ($myrow === false) throw new Exception ("ERROR_SEARCH_FREE_CELL");
		
		$arr = array("id_user"=>$id_user,
					 "date"=>date("Y-m-d H:i:s"),
					 "cell_up"=>$myrow['id'],
					 "date_payment"=>date("Y-m-d H:i:s",time()+86400*30));
		$id = $this->sql->Insert("cells",$arr);
		if ($id === false) { $this->err = "ERROR_DB"; return false; }
		$this->id = $id;
		
		$arr = array("t_partners"=>$myrow['t_partners']+1);
		switch ($myrow['t_partners']) {
			case 0 : { $arr['branch_1'] = $id; break; }
			case 1 : { $arr['branch_2'] = $id; break; }
			case 2 : { $arr['branch_3'] = $id; break; }
		}
		$this->sql->UpdateID("cells",$arr,$myrow['id']);
		$this->UpdateTotalStructure($myrow['id']);
		
		if ($pay) {
			$this->PersonalCompinsation($id_user);
			$this->StructureCompinsation($id_user,$myrow['id']);
			$this->TrioCompinsation($id_user,$myrow['id'],$id);
		}
		
		return true;
	}
	
	function SearchFreeCell($cell) {
		if ($cell['t_partners'] < 3) return $cell;
		$c = array($cell['id']);
		$i = 10;
		while($i > 0) {
			$query = "SELECT id,t_partners FROM cells WHERE";
			foreach($c as $value) {
				$query .= " cell_up='".$value."'";
				if($value != end($c)) $query .= " OR";
			}
			$query .= " ORDER BY t_partners,date";

			$c = array();
			$res = mysqli_query($this->sql->db,$query);
			if (mysqli_num_rows($res) == 0) {
				return false;
			}
			while($myrow = mysqli_fetch_array($res)) {
				if ($myrow['t_partners'] < 3) return $myrow;
				array_push($c,$myrow['id']);
			}
			$i--;
		}
		return false;
	}
	
	function UpdateTotalStructure($up) {
		$i = 1;
		$id = $up;
		while($i <= 11) {
			$arr = array("id"=>$id);
			$cell_up = $this->sql->Select("cells",$arr);
			if ($cell_up === false) break;
			
			$arr = array("total"=>$cell_up['total']+1);
			$this->sql->UpdateID("cells",$arr,$cell_up['id']);
				
			$id = $cell_up['cell_up'];
			$i++;
		}
	}
	
	function PersonalCompinsation($id_user) {
		if(!isset($this->cell_refer)) return false;
		
		$this->payment->id_user = $this->cell_refer['id_user'];
		$this->payment->amount = $this->personal_amount;
		$this->payment->type = "binar-refer";
		$this->payment->payer = $id_user;
		$this->payment->confirm = true;
		$frozen = 0;
		if (strtotime($this->cell_refer['date_payment']) < time()) $frozen = 1;

		if ($this->payment->CreateCompinsation($frozen) === false) {
			$this->err = $this->payment->err;
			return false;
		}
		return true;
	}
	
	function StructureCompinsation($id_user,$up) {
		$i = 1;
		$id = $up;
		while($i <= 11) {
			$arr = array("id"=>$id);
			$cell_up = $this->sql->Select("cells",$arr);
			if ($cell_up === false) break;
			
			switch($i) {
				case 1 : { $this->payment->amount = $this->c_level_1; break; }
				case 2 : { $this->payment->amount = $this->c_level_2; break; }
				case 3 : { $this->payment->amount = $this->c_level_3; break; }
				case 4 : { $this->payment->amount = $this->c_level_4; break; }
				case 5 : { $this->payment->amount = $this->c_level_5; break; }
				case 6 : { $this->payment->amount = $this->c_level_6; break; }
				case 7 : { $this->payment->amount = $this->c_level_7; break; }
				case 8 : { $this->payment->amount = $this->c_level_8; break; }
				case 9 : { $this->payment->amount = $this->c_level_9; break; }
				case 10 : { $this->payment->amount = $this->c_level_10; break; }
				case 11 : { $this->payment->amount = $this->c_level_11; break; }
			}
			$this->payment->id_user = $cell_up['id_user'];
			$this->payment->type = "binar-structure";
			$this->payment->payer = $id_user;
			$this->payment->confirm = true;
			$frozen = 0;
			if (strtotime($cell_up['date_payment']) < time()) $frozen = 1;
			$this->payment->CreateCompinsation($frozen);
			
			$id = $cell_up['cell_up'];
			$i++;
		}
	}
	
	function TrioCompinsation($id_user,$up,$new_id) {
		$i = 1;
		$id = $up;
		$down_id = $new_id;
		
		while($i <= 7) {
			$arr = array("id"=>$id);
			$cell_up = $this->sql->Select("cells",$arr);
			if ($cell_up === false) break;
			echo $cell_up['branch_1']." ".$cell_up['branch_2']." ".$cell_up['branch_3']." = ".$down_id." ".$i."<br>";
			$branch = 0;
			if ($down_id == $cell_up['branch_1']) $branch = 1;
			if ($down_id == $cell_up['branch_2']) $branch = 2;
			if ($down_id == $cell_up['branch_3']) $branch = 3;
			if ($branch == 0) break;
			
			
			$min = $cell_up['t_branch_1'.$i];
			$max = $cell_up['t_branch_1'.$i];
			$min_branch = 1;
			if ($cell_up['t_branch_2'.$i] < $min) {
				$min = $cell_up['t_branch_2'.$i];
				$min_branch = 2;
			}
			if ($cell_up['t_branch_2'.$i] > $max) {
				$max = $cell_up['t_branch_2'.$i];
			}
			if ($cell_up['t_branch_3'.$i] < $min) {
				$min = $cell_up['t_branch_3'.$i];
				$min_branch = 3;
			}
			if ($cell_up['t_branch_3'.$i] > $max) {
				$max = $cell_up['t_branch_3'.$i];
			}
			
			$index = "t_branch_".$branch.$i;
			$value = $cell_up[$index]+1;
			echo $index."=>".$value."<br>";
			$arr = array($index=>$value);
			$this->sql->UpdateID("cells",$arr,$cell_up['id']);
			
			
			$down_id = $id;
			
			if (($branch == 1 && $cell_up['t_branch_1'.$i] < $cell_up['t_branch_2'.$i] && $cell_up['t_branch_1'.$i] < $cell_up['t_branch_3'.$i]) ||
				($branch == 2 && $cell_up['t_branch_2'.$i] < $cell_up['t_branch_1'.$i] && $cell_up['t_branch_2'.$i] < $cell_up['t_branch_3'.$i]) ||
				($branch == 3 && $cell_up['t_branch_3'.$i] < $cell_up['t_branch_2'.$i] && $cell_up['t_branch_3'.$i] < $cell_up['t_branch_1'.$i])) {
				
				$this->payment->id_user = $cell_up['id_user'];
				$this->payment->amount = $this->trio_compinsation;
				$this->payment->type = "binar-trio";
				$this->payment->payer = $id_user;
				$this->payment->confirm = true;
				$frozen = 0;
				if (strtotime($cell_up['date_payment']) < time()) $frozen = 1;
				$this->payment->CreateCompinsation($frozen);
				
			}
			$id = $cell_up['cell_up'];
			$i++;
		}
	}
	
	function ReloadStatus($cell) {
		if($cell['status'] == 0 && $cell['total'] >= $this->t_status_1) $cell['status'] = $this->ChangeStatus($cell);
		if($cell['status'] == 1 && $cell['total'] >= $this->t_status_2) $cell['status'] = $this->ChangeStatus($cell);
		if($cell['status'] == 2 && $cell['total'] >= $this->t_status_3) $cell['status'] = $this->ChangeStatus($cell);
		if($cell['status'] == 3 && $cell['total'] >= $this->t_status_4) $cell['status'] = $this->ChangeStatus($cell);
		if($cell['status'] == 4 && $cell['total'] >= $this->t_status_5) $cell['status'] = $this->ChangeStatus($cell);
		if($cell['status'] == 5 && $cell['total'] >= $this->t_status_6) $cell['status'] = $this->ChangeStatus($cell);
	}
	
	function ChangeStatus($cell) {
		switch($cell['status']) {
			case 0 : { $this->payment->amount = $this->a_status_1; break; }
			case 1 : { $this->payment->amount = $this->a_status_2; break; }
			case 2 : { $this->payment->amount = $this->a_status_3; break; }
			case 3 : { $this->payment->amount = $this->a_status_4; break; }
			case 4 : { $this->payment->amount = $this->a_status_5; break; }
			case 5 : { $this->payment->amount = $this->a_status_6; break; }
		}
		$this->payment->id_user = $cell['id_user'];
		$this->payment->type = "binar-status";
		$this->payment->confirm = true;
		$frozen = 0;
		if (strtotime($cell['date_payment']) < time()) $frozen = 1;
		$this->payment->CreateCompinsation($frozen);
		
		$arr = array("status"=>$cell['status']+1);
		$this->sql->UpdateID("cells",$arr,$cell['id']);
		
		return ($cell['status']+1);
	}
}
?>