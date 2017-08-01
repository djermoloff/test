<?php 
class SMSC {
	public $err;
	public $sql;
	public $user;
	public $password;
	public $sender;
	public $id;
	public $pin;
	public $failed;
	public $id_user;
	public $phone;
	
	function __construct () {
		try {
			require_once(__ROOT__."/classes/MySQL.php");
			$this->sql = new MySQL(DB_HOST,DB_USER,DB_PASS,DB_NAME);
			
			$this->user = $this->sql->GetSetting("smsc_user");
			$this->password = $this->sql->GetSetting("smsc_password");
			$this->sender = $this->sql->GetSetting("smsc_sender");
			
			if ($this->user === false || $this->password === false) throw new Exception("ERROR_SEND_SMS");
			
		} catch (Exception $err) {
			$this->err = $err->GetMessage(); 
			return false;
		}
	}
	
	function GetActivePIN() {
		$query = "SELECT * FROM sms WHERE id_user='".(int)$this->id_user."' AND date>'".date("Y-m-d H:i:s",time()-300)."' AND used=FALSE AND failed<5 ORDER BY date LIMIT 1";
		$result = mysqli_query($this->sql->db,$query);
		if (mysqli_num_rows($result) == 0) return false;
		$myrow = mysqli_fetch_assoc($result);
		$this->pin = $myrow['pin'];
		$this->id = $myrow['id'];
		$this->failed = $myrow['failed'];
		return true;
	}
	
	function CreatePIN() {
		$pin = Rand(1000,9999);
		$mes = "You PIN: ".$pin;
		if ($this->SendSMS($this->phone,$mes)) {
			$arr = array();
			$arr['id_user'] = $this->id_user;
			$arr['phone'] = $this->phone;
			$arr['date'] = date("Y-m-d H:i:s");
			$arr['pin'] = $pin;
			if ($this->sql->Insert("sms",$arr) === false) return false;
			return true;
		}
		return false;
	}
	
	function IsPIN($pin) {
		if ($this->pin == $pin) {
			$this->sql->query = "UPDATE sms SET used=TRUE WHERE id='".(int)$this->id."'";
			$this->sql->Query();
			return true;
		}
		
		$this->sql->query = "UPDATE sms SET failed=failed+1 WHERE id='".(int)$this->id."'";
		if ($this->sql->Query()) {
			$this->failed++;
		}
		return false;
	}
	
	function SendSMS($phone,$text) {
		$arr = array();
		$arr['login'] = $this->user;
		$arr['psw'] = $this->password;
		$arr['sender'] = $this->sender;
		$arr['phones'] = $phone;
		$arr['mes'] = $text;
		$arr['fmt'] = 3;
		$url = "http://smsc.ru/sys/send.php";
		$res = $this->cURL($url,$arr);
		$json = json_decode($res,true);
		if (isset($json['error'])) {
			$this->err = $json['error_code'];
			return false;
		}
		if ($json['cnt'] > 0) return true;
	}
	
	function cURL ($url,$data) {
		$d = "";
		foreach($data as $index=>$value) {
			$d .= $index."=".$value;
			if($value != end($data)) $d .= "&";
		}

		$p = curl_init($url);
		curl_setopt($p,CURLOPT_URL,$url);
		curl_setopt($p,CURLOPT_HEADER,0);
		curl_setopt($p,CURLOPT_RETURNTRANSFER,1);
		//curl_setopt($p,CURLOPT_POST,1);
		curl_setopt($p,CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded; charset=UTF-8','Content-Length: '.strlen($d)));
		curl_setopt($p,CURLOPT_POSTFIELDS,$d);
		
		$result = curl_exec($p);
		curl_close($p);
		
		return $result;
	}
}
?>