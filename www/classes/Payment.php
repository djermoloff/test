<?php
class Payment {
	public $err;
	public $sql;
	public $payment_account;
	
	public $id;
	public $id_user;
	public $ps_type;
	public $date;
	public $type;
	public $payer;
	public $payee;
	public $amount;
	public $currency;
	public $fee;
	public $batch;
	public $date_confirm;
	public $confirm;
	public $comment;
		
	public $form;
	
	
	
	function __construct () {
		require_once("MySQL.php");
		$this->sql = new MySQL(DB_HOST,DB_USER,DB_PASS,DB_NAME);
		$this->currency = "USD";
	}
	
	function Init ($id) {
		try {
			$this->id = (int)$id;
			if ($this->id <= 0) throw new Exception ("ID_PAYMENT_NOT_CORRECT");
			
			$arr = array("id"=>$this->id);
			$myrow = $this->sql->Select("payments",$arr);
			if ($myrow === false) throw new Exception ("PAYMENT_NOT_FOUND");
			
			foreach($myrow as $index=>$value) {
				if (property_exists($this,$index) == true) $this->$index = $myrow[$index];
			}
			
			return $myrow;
			
		} catch (Exception $err) {
			$this->err = $err->GetMessage(); 
			return false;
		}
	}
	
	function GreateDeposit() {
		try {
			$this->amount = (int)$this->amount;
			if ($this->amount <= 0) throw new Exception ("AMOUNT_NOT_CORRECT");
			
			if ($this->ps_type == "adv") $res = $this->CreateADVCash();
			
			if ($res === false) return false;
			return true;
			
		} catch (Exception $err) {
			$this->err = $err->GetMessage(); 
			return false;
		}
	}
	
	function CreateADVCash() {
		if ($this->GetPayeeAccount("adv",strtoupper($this->currency)) == false) return false;
		if ($this->CreatePayment() === false) return false;
			
		$this->form = "<form id='SendDeposit' method='POST' action='https://wallet.advcash.com/sci/'>
			<input type='hidden' name='ac_account_email' value='".$this->payment_account['account']."'>
			<input type='hidden' name='ac_sci_name' value='".$this->payment_account['adv_sci']."'>
			<input type='hidden' name='ac_amount' value='".$this->amount."'>
			<input type='hidden' name='ac_currency' value='".strtoupper($this->currency)."'>
			<input type='hidden' name='ac_comments' value='Payment #".$this->id."'>
			<input type='hidden' name='operation_id' value='".$this->id."'>
			<input type='hidden' name='ac_order_id' value='".$this->id."'>
		</form>";
		return true;
	}
	
	function CreatePayment() {
		$arr = array();
		$arr['id_user'] = $this->id_user;
		$arr['date'] = date("Y-m-d H:i:s");
		$arr['type'] = $this->type;
		$arr['ps_type'] = $this->ps_type;
		$arr['amount'] = $this->amount;
		$arr['currency'] = $this->currency;
		
		$id = $this->sql->Insert("payments",$arr);
		if ($id === false) { $this->err = "ERROR_DB"; return false; }
		$this->id = $id;
		return $id;
	}
	
	function GetPayeeAccount($ps,$currency = "") {
		$query = "SELECT * FROM payee_account WHERE ps_type='".$ps."'";
		if ($ps == "pm") $query .= " AND currency='".$currency."'";

		$result = mysqli_query($this->sql->db, $query);
		if (mysqli_num_rows($result) == 0) { $this->err = "PAYEE_ACCOUNT_NOT_FOUND"; return false; }
		$myrow = mysqli_fetch_array($result);
		$this->payment_account = $myrow;
		return $myrow;
	}
	
	function ConfirmPayment($id = 0) {
		try{
			if ($id > 0) {
				$this->id = (int)$id;
				if ($this->id <= 0) throw new Exception ("ID_PAYMENT_NOT_CORRECT");
				if ($this->Init($id) === false) throw new Exception ("PAYMENT_NOT_FOUND");
			} else {
				if ($this->id <= 0) throw new Exception ("ID_PAYMENT_NOT_CORRECT");
			}
			
			if ($this->type != "deposit") throw new Exception ("TYPE_NOT_CORRECT");
			
			$this->sql->query = "UPDATE users SET balance=balance+'".$this->amount."' WHERE id='".$this->id_user."'";
			if ($this->sql->Query($this->sql->query)=== false) throw new Exception ($this->sql->err);
			
			$arr = array();
			$arr['date_confirm'] = $this->date_confirm;
			$arr['payee'] = $this->payee;
			$arr['payer'] = $this->payer;
			$arr['fee'] = $this->fee;
			$arr['batch'] = $this->batch;
			$arr['confirm'] = true;
			$this->sql->UpdateID("payments",$arr,$this->id);
			return true;
			
		} catch (Exception $err) {
			$this->err = $err->GetMessage(); 
			return false;
		}
	}
	
	function ConfirmWithdraw($id = 0) {
		try{
			if ($id > 0) {
				$this->id = (int)$id;
				if ($this->id <= 0) throw new Exception ("ID_PAYMENT_NOT_CORRECT");
				if ($this->Init($id) === false) throw new Exception ("PAYMENT_NOT_FOUND");
			} else {
				if ($this->id <= 0) throw new Exception ("ID_PAYMENT_NOT_CORRECT");
			}
			
			if ($this->type != "withdrawal") throw new Exception ("TYPE_NOT_CORRECT");
			
			$arr = array();
			$arr['date_confirm'] = date("Y-m-d H:i:s");
			$arr['payer'] = $this->payer;
			$arr['fee'] = (double)$this->fee;
			$arr['batch'] = $this->batch;
			$arr['comment'] = $this->comment;
			$arr['confirm'] = true;
			$this->sql->UpdateID("payments",$arr,$this->id);
			return true;
			
		} catch (Exception $err) {
			$this->err = $err->GetMessage(); 
			return false;
		}
	}
	
	function GreateWithdrawal($balance) {
		try {
			if ($this->payee == "") throw new Exception ("PAYEE_ACCOUNT_NOT_FOUND");
			$this->amount = (int)$this->amount;
			if ($this->amount <= 0) throw new Exception ("AMOUNT_NOT_CORRECT");
			if ($balance < $this->amount) throw new Exception ("AMOUNT_NOT_CORRECT");
			
			$this->sql->query = "UPDATE users SET partner_balance=partner_balance-'".$this->amount."' WHERE id='".$this->id_user."'";
			if ($this->sql->Query($this->sql->query)=== false) throw new Exception ($this->sql->err);
			
			$arr = array();
			$arr['id_user'] = $this->id_user;
			$arr['date'] = date("Y-m-d H:i:s");
			$arr['type'] = $this->type;
			$arr['ps_type'] = $this->ps_type;
			$arr['payee'] = $this->payee;
			$arr['amount'] = $this->amount;
			$arr['currency'] = $this->currency;
			
			$id = $this->sql->Insert("payments",$arr);
			if ($id === false) { $this->err = "ERROR_DB"; return false; }
			$this->id = $id;
			return true;
			
		} catch (Exception $err) {
			$this->err = $err->GetMessage(); 
			return false;
		}
	}
	
	function PartnerToBalance($balance) {
		try {
			$this->amount = (int)$this->amount;
			if ($this->amount <= 0) throw new Exception ("AMOUNT_NOT_CORRECT");
			if ($balance < $this->amount) throw new Exception ("AMOUNT_NOT_CORRECT");
			
			$sum = $this->amount + $this->fee;
			$this->sql->query = "UPDATE users SET partner_balance=partner_balance-'".$sum."',balance=balance+'".$this->amount."' WHERE id='".$this->id_user."'";
			if ($this->sql->Query($this->sql->query)=== false) throw new Exception ($this->sql->err);
			
			$arr = array();
			$arr['id_user'] = $this->id_user;
			$arr['date'] = date("Y-m-d H:i:s");
			$arr['date_confirm'] = date("Y-m-d H:i:s");
			$arr['type'] = "include";
			$arr['payee'] = "balance";
			$arr['payer'] = "partner_balance";
			$arr['amount'] = $this->amount;
			$arr['fee'] = $this->fee;
			$arr['currency'] = $this->currency;
			$arr['confirm'] = true;
			
			$id = $this->sql->Insert("payments",$arr);
			if ($id === false) throw new Exception ($this->sql->err);
			
			$arr['payer'] = "balance";
			$arr['payee'] = "partner_balance";
			
			$id = $this->sql->Insert("payments",$arr);
			if ($id === false)  throw new Exception ($this->sql->err);
			
			return true;
			
		} catch (Exception $err) {
			$this->err = $err->GetMessage(); 
			return false;
		}
	}
	
	function WithdrawFromBalance($account = 0) {
		try {
			$this->amount = (int)$this->amount;
			if ($this->amount <= 0) throw new Exception ("AMOUNT_NOT_CORRECT");
			
			switch ($account) {
				case 0 : { $a = "balance"; break; }
				case 1 : { $a = "partner_balance"; break; }
				default : $a = "balance";
			}
			
			$sum = $this->amount + $this->fee;
			$this->sql->query = "UPDATE users SET $a=$a-'".$sum."' WHERE id='".$this->id_user."'";
			if ($this->sql->Query($this->sql->query)=== false) throw new Exception ($this->sql->err);
			
			$arr = array();
			$arr['id_user'] = $this->id_user;
			$arr['date'] = date("Y-m-d H:i:s");
			$arr['date_confirm'] = date("Y-m-d H:i:s");
			$arr['type'] = $this->type;
			$arr['payer'] = $a;
			$arr['payee'] = "";
			$arr['amount'] = $this->amount;
			$arr['fee'] = $this->fee;
			$arr['currency'] = $this->currency;
			
			$id = $this->sql->Insert("payments",$arr);
			if ($id === false) { $this->err = "ERROR_DB"; return false; }
			$this->id = $id;
			return true;
			
		} catch (Exception $err) {
			$this->err = $err->GetMessage(); 
			return false;
		}
	}
	
	function DepositToBalance($account = 0) {
		try {
			$this->amount = (int)$this->amount;
			if ($this->amount <= 0) throw new Exception ("AMOUNT_NOT_CORRECT");
			
			switch ($account) {
				case 0 : { $a = "balance"; break; }
				case 1 : { $a = "partner_balance"; break; }
				default : $a = "balance";
			}
			
			$this->sql->query = "UPDATE users SET $a=$a+'".$this->amount."' WHERE id='".$this->id_user."'";
			if ($this->sql->Query($this->sql->query)=== false) throw new Exception ($this->sql->err);
			
			$arr = array();
			$arr['id_user'] = $this->id_user;
			$arr['date'] = date("Y-m-d H:i:s");
			$arr['date_confirm'] = date("Y-m-d H:i:s");
			$arr['type'] = $this->type;
			$arr['payer'] = "";
			$arr['payee'] = $a;
			$arr['amount'] = $this->amount;
			$arr['currency'] = $this->currency;
			
			$id = $this->sql->Insert("payments",$arr);
			if ($id === false) { $this->err = "ERROR_DB"; return false; }
			$this->id = $id;
			return true;
			
		} catch (Exception $err) {
			$this->err = $err->GetMessage(); 
			return false;
		}
	}
	
	function CreateCompinsation($account=0) {
		try {
			$this->amount = (int)$this->amount;
			if ($this->amount <= 0) throw new Exception ("AMOUNT_NOT_CORRECT");
			
			switch ($account) {
				case 0 : { $a = "partner_balance"; break; }
				case 1 : { $a = "frozen"; break; }
				default : $a = "partner_balance";
			}
			
			$this->sql->query = "UPDATE users SET $a=$a+'".$this->amount."' WHERE id='".$this->id_user."'";
			if ($this->sql->Query($this->sql->query)=== false) throw new Exception ($this->sql->err);
			
			$arr = array();
			$arr['id_user'] = $this->id_user;
			$arr['date'] = date("Y-m-d H:i:s");
			$arr['date_confirm'] = date("Y-m-d H:i:s");
			$arr['type'] = $this->type;
			$arr['payer'] = $this->payer;
			$arr['payee'] = $a;
			$arr['amount'] = $this->amount;
			$arr['currency'] = $this->currency;
			
			$id = $this->sql->Insert("payments",$arr);
			if ($id === false) { $this->err = "ERROR_DB"; return false; }
			$this->id = $id;
			return true;
			
		} catch (Exception $err) {
			$this->err = $err->GetMessage(); 
			return false;
		}
	}
}
?>