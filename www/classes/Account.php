<?php
class Account {
	public $err;
	public $error_code;
	public $db;
	public $data;
	public $sql;
	
	public $activation_url;
	public $fogotpass_url;
	
	function __construct () {
		require_once("MySQL.php");
		$this->sql = new MySQL(DB_HOST,DB_USER,DB_PASS,DB_NAME);
		$this->activation_url = SITE_URL."/cabinet/Activation/?email=[email]&lang=[lang]&hash=[hash]";
		$this->fogotpass_url = SITE_URL."/cabinet/change-password/?email=[email]&expiration=[expiration]&hash=[hash]";
	}
	
	function UserInitID($id) {
		$arr = array("id"=>(int)$id);
		$this->data = $this->sql->Select("users",$arr);
		if ($this->data === false) return false;
			else return true;
	}
	
	function UserInitEmail($email) {
		if (!$this->v_email($email)) return false;
		$arr = array("email"=>$email);
		$this->data = $this->sql->Select("users",$arr);
		if ($this->data === false) return false;
			else return true;
	}
	
	function UserRegistration($arr) { 
		if (!preg_match("/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/", $arr['email'])) { $this->err = "EMAIL_UNCORRECT"; return false; }
		if ($this->UserInitEmail($arr['email'])) { $this->err = "EMAIL_BUSY"; return false; }
		
		$request = array("email"=>$arr['email'],
					 "password"=>$arr['password']);
					 
		if (isset($arr['refer'])) {
			$request['refer'] = (int)$arr['refer'];
			$refer = new Account;
			if ($refer->UserInitID($request['refer']) === false) $request['refer'] = 0;
		}

		if (!isset($arr['name'])) $arr['name'] = "";
		$request['name'] = $arr['name'];
		if (isset($arr['lang'])) {
			$request['lang'] = $arr['lang'];
			$request['country'] = strtoupper($arr['lang']);
		}
		
		$pass = $arr['password'];
		$request['password'] = $this->mdPass($arr['password']);
		$request['date'] = date("Y-m-d H:i:s",time());
		$id = $this->sql->Insert("users",$request);
		if ($id === false) { $this->err = "ERROR_DB"; return false; }
		
		$hash = $this->Hash(array($arr['email'],$request['password']));
		include_once(__DIR__."/../classes/SendMail.php");
		$mail = new Mail;
		$rc = array();
		array_push($rc, array("activation_url"=>$this->activation_url,"name"=>$arr['name'],"email"=>$arr['email'],"password"=>$pass,"hash"=>$hash,"lang"=>$arr['lang']));
		$mail->recipients = $rc;
		if (isset($arr['lang'])) $mail->lang = $arr['lang'];
		$mail->Create("registration");
		$total_sent = $mail->Send();
		return $id;
	}
	
	function Activation() {
		if (!isset($_GET['email'])) { $this->err = "REQUEST_ERROR"; $this->error_code = 500; return false; }
		if (!isset($_GET['hash'])) { $this->err = "REQUEST_ERROR"; $this->error_code = 501; return false; }
		if ($this->UserInitEmail($_GET['email']) == false) { $this->err = "USER_NOT_FOUND"; return false; }
		$hash = $this->Hash(array($this->data['email'],$this->data['password']));
		if ($_GET['hash'] != $hash)  { $this->err = "ERROR_LINK"; return false; }
		$this->sql->query = "UPDATE users SET activation=true WHERE id='".$this->data['id']."'";
		if ($this->sql->Query()) {
			include_once(__DIR__."/../classes/SendMail.php");
			require_once(__DIR__."/../classes/Lang.php");
			$mail = new Mail;
			$lg = new Language();
			
			$arr = array();
			array_push($arr, array("name"=>$this->data['name'],"email"=>$this->data['email']));
			$mail->recipients = $arr;
			$mail->lang = $lg->lang;
			$mail->Create("activation");
			$mail->Send();
			
			return true;
		}
	}
	
	function FogotPassword($email) {
		if ($this->UserInitEmail($email) === false) { $this->err = "USER_NOT_FOUND"; return false; }
		$us['link'] = $this->fogotpass_url;
		$us['expiration'] = time()+3600;
		$us['password'] = $this->data['password'];
		$us['email'] = $this->data['email'];
		$us['hash'] = $this->Hash($us);
		$us['name'] = $this->data['name'];
		$us['email'] = $this->data['email'];
		
		include_once(__DIR__."/../classes/SendMail.php");
		$mail = new Mail;
		
		$arr = array();
		array_push($arr, $us);
		$mail->recipients = $arr;
		$mail->lang = $this->data['lang'];
		$mail->Create("fogot-password");
		$total = $mail->Send();
		if ($total > 0) return true; else { $this->err = "ERROR_SEND_MAIL"; return false; }
	}
	
	function ChangePassword() {
		if (!isset($_GET['email'])) { $this->err = "REQUEST_ERROR"; return false; }
		if (!isset($_GET['hash'])) { $this->err = "REQUEST_ERROR"; return false; }
		if (!isset($_GET['password'])) { $this->err = "REQUEST_ERROR"; return false; }
		if (strlen($_GET['password']) < 6) { $this->err = "SMALL_PASSWORD"; return false; }
		/*if (!isset($_GET['confirm_password'])) { $this->err = "REQUEST_ERROR"; return false; }
		if ($_GET['password'] != $_GET['confirm_password']) { $this->err = "REQUEST_ERROR"; return false; }*/
		if (isset($_GET['expiration']) && $_GET['expiration'] < time()) { $this->err = "LINK_IS_OUTDATED"; return false; }
		
		if ($this->UserInitEmail($_GET['email']) == false) { $this->err = "USER_NOT_FOUND"; return false; }
		
		$us['link'] = $this->fogotpass_url;
		if (isset($_GET['expiration'])) $us['expiration'] = $_GET['expiration'];
		$us['password'] = $this->data['password'];
		$us['email'] = $this->data['email'];
		$hash = $this->Hash($us);
		
		$us['"name'] = $this->data['name'];
		$us['email'] = $this->data['email'];
		
		if ($_GET['hash'] != $hash)  { $this->err = "REQUEST_ERROR"; return false; }
		$password = $this->mdPass($_GET['password']);
		$this->sql->query = "UPDATE users SET password='$password' WHERE id='".$this->data['id']."'";
		if ($this->sql->Query()) {
			include_once(__DIR__."/../classes/SendMail.php");
			require_once(__DIR__."/../classes/Lang.php");
			$mail = new Mail;
			$lg = new Language();
			
			$arr = array();
			array_push($arr, array("name"=>$this->data['name'],"email"=>$this->data['email']));
			$mail->recipients = $arr;
			$mail->lang = $lg->lang;
			$mail->Create("password-changed");
			$mail->Send();
			
			return true;
		}
	}
	
	function Auth() {
		if ($this->IsAuth()) return true;
		if (!isset($_GET['email']) || !isset($_GET['password'])) { $this->err = "REQUEST_ERROR"; return false; }
		if ($this->UserInitEmail($_GET['email']) == false) { $this->err = "ERROR_AUTH"; return false; }
		if ($this->data['auth_err'] > 4 && $this->data['auth_err_date'] > date('Y-m-d H:i:s')) { $this->err = "ACCOUNT_TEMPORARILY_BLOCKED"; return false; }
		
		$password = $this->mdPass($_GET['password']);
		
		if ($this->data['password'] != $password) {
			$this->data['auth_err']++;
			$this->data['auth_err_date'] = date("Y-m-d H:i:s",time()+60*15);
			$this->data['auth_err_ip'] = $_SERVER['REMOTE_ADDR'];
			$this->UserUpdate(array("auth_err","auth_err_date","auth_err_ip"));
			if ($this->data['auth_err'] > 4)  { $this->err = "ACCOUNT_TEMPORARILY_BLOCKED"; return false; }
			$this->err = "ERROR_AUTH";
			return false; 
		} else {
			$change = array();
			if ($this->data['auth_err'] > 0) {
				$this->data['auth_err'] = 0;
				array_push($change,"auth_err");	
			}
			$this->data['ip'] = $_SERVER['REMOTE_ADDR'];
			$this->data['online'] = date("Y-m-d H:i:s",time());
			array_push($change,"ip","online");	
			$this->UserUpdate($change);
			$this->UserAuthorization();
			return true;
		}
	}
	
	function ProfileChangePassword() {
		if (!isset($_GET['oldpass'])) { $this->err = "REQUEST_ERROR"; return false; }
		if (!isset($_GET['pass'])) { $this->err = "REQUEST_ERROR"; return false; }
		
		$password = $this->mdPass($_GET['oldpass']);
		if ($this->data['password'] != $password) { $this->err = "ERROR_PASSWORD"; return false; }
			
		$this->data['password'] = $this->mdPass($_GET['pass']);
		if ($this->UserUpdate(array("password")) === false) { $this->err = "ERROR_DB"; return false; }
		
		$this->UserAuthorization();
		return true;
	}
	
	function UserAuthorization($id = 0) {
		if ($id > 0) if ($this->UserInitID($id) == false)  { $this->err = "ERROR_AUTHORIZATION"; return false; }
		$_SESSION['id'] = $this->data['id']; 
		$_SESSION['key'] = md5($this->data['id'].$this->data['password'].USER_AUTH_KEY.$_SERVER['REMOTE_ADDR']);
		$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
		return true;
	}
	
	function Logout() {
		session_unset();
	}
	
	function UserUpdate($arr) {
		$r = array();
		foreach ($arr as $index) {
			$r[$index] = $this->data[$index];
		}
		return $this->sql->UpdateID("users",$r,$this->data['id']);
	}
	
	function Hash($arr) {
		$str = "";
		foreach($arr as $value) {
			$str .= $value;
		}
		$str .= USER_HASH_KEY;
		return md5($str);
	}
	
	function IsAuth() {
		if (!isset($_SESSION['id'])) return false;
		if ($this->UserInitID($_SESSION['id']) == false) return false;
		
		$key = md5($this->data['id'].$this->data['password'].USER_AUTH_KEY.$_SERVER['REMOTE_ADDR']);
		if ($key != $_SESSION['key']) return false; 
		return true;
	}
	
	function GetArrayAccount($arr) {
		$result = array();
		foreach ($arr as $value) {
			if (isset($this->data[$value])) $result[$value] = $this->data[$value];
		}
		return $result;
	}
	
	function upload_images($url,$name,$max_img) {
		$ftp_href = $_FILES["file"]["name"];

		if (!is_dir($url."/temp")) mkdir($url."/temp", 0775);
		
		if(copy($_FILES["file"]["tmp_name"],$url."/temp/".$ftp_href)) {
			$path_info = pathinfo($_FILES["file"]["name"]);
			$str_1 = strtolower($path_info['extension']);

			if($str_1 == "jpg" or $str_1 == "jpeg") $im = imagecreatefromjpeg($url."/temp/" . $ftp_href);
			if($str_1 == "gif") $im = imagecreatefromgif($url."/temp/" . $ftp_href);
			if($str_1 == "png") $im = imagecreatefrompng($url."/temp/" . $ftp_href);
			  
			$ox = imagesx($im);
			$oy = imagesy($im);
			
			if ($ox > $oy) {
				$top_y = 0;
				$bottom_y = $oy;
				$k = $oy/$max_img;
				$left_x = floor(($ox - $oy)/2);
				$right_x = $oy;
			} else {
				$left_x = 0;
				$right_x = $ox;
				$k = $ox/$max_img;
				$top_y = floor(($oy - $ox)/2);
				$bottom_y = $ox;
			}
			
			$nm = imagecreatetruecolor($max_img, $max_img);
			imagecopyresized($nm, $im, 0,0,$left_x,$top_y,$max_img,$max_img,$right_x,$bottom_y);
			$images = $url."/".$name.".".$str_1;
			
			imagejpeg($nm, $images);
			
			unlink($url."/temp/".$ftp_href);
			return (true);
		} else return(false);
			
		return(false);
	}
	
	function GetPayments($arr = array()) {
		$res = array();
		$this->sql->query = "SELECT * FROM payments WHERE id_user='".$this->data['id']."' ORDER BY date DESC";
		$result = mysqli_query($this->sql->db,$this->sql->query);
		if ($result !== false) {
			if (mysqli_num_rows($result) == 0) return 0;
			while ($myrow = mysqli_fetch_array($result)) {
				if (count($arr)>0) {
					$m = array();
					foreach($arr as $index) {
						if(isset($myrow[$index])) if ($index != "date") $m[$index] = $myrow[$index]; else $m[$index] = strtotime($myrow[$index])*1000;
					}
					array_push($res,$m);
				} else array_push($res,$myrow);
			}
			return $res;
		}
		else return false;
	}
	
	function GetPartners($arr = array()) {
		$res = array();
		$this->sql->query = "SELECT * FROM users WHERE refer='".$this->data['id']."' ORDER BY date DESC";
		$result = mysqli_query($this->sql->db,$this->sql->query);
		if ($result !== false) {
			if (mysqli_num_rows($result) == 0) return 0;
			while ($myrow = mysqli_fetch_array($result)) {
				if (count($arr)>0) {
					$m = array();
					foreach($arr as $index) {
						if(isset($myrow[$index])) if ($index != "date") $m[$index] = $myrow[$index]; else $m[$index] = strtotime($myrow[$index])*1000;
					}
					array_push($res,$m);
				} else array_push($res,$myrow);
			}
			return $res;
		}
		else return false;
	}
	
	function AddPhone($phone) {
		$arr = array("id_user"=>$this->data['id'],"phone"=>$phone);
		$res = $this->sql->Insert("change_phone",$arr);
		if ($res === false) return false;
		return true;
	}
	
	function GetChangePhone() {
		$query = "SELECT phone FROM change_phone WHERE id_user='".$this->sql->mysql_text($this->data['id'])."' ORDER BY id DESC LIMIT 1";
		$res = mysqli_query($this->sql->db,$query);
		if ($res === false) return false;
		$myrow = mysqli_fetch_assoc($res);
		return $myrow['phone'];
	}
	
	function mdPass ($pass) {
		global $pass_key;
		return md5($pass.$pass_key);
	}
	
	function v_email ($email) {
		if (preg_match("/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/", $email) || $email == "") return true; else return false;
	}
}
?>