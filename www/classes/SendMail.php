<?php
class Mail {
	public $header;
	public $subject;
	public $tpl_url;
	public $lang=DEFAULT_LANG;
	public $body;
	public $recipients;
	public $SMTP;
	public $sender;
	public $host;
	public $sender_key = "config";
	public $charset;
	public $err;
	
	function __construct($root = __ROOT__) {
		$this->tpl_url = "/lang/[lang]/mail/";
		
	}
	
	function ConnectSMTP () {
		try {
			if ($this->sender_key == "config") {
				$this->sender['user'] = SMTP_USER;
				$this->sender['name'] = SMTP_NAME;
				$this->sender['password'] = SMTP_PASSWORD;
				$this->GetHost(SMTP_USER);
				$this->sender['port'] = 465;
				$this->charset = "utf-8";
			} else {
				$sender = $this->GetSender($sender_key);
				if ($sender === false) throw new Exception("SENDER_NOT_FOUND");
				$this->sender['password'] = $this->get_uncode($sender['password']);
				$this->GetHost(SMTP_USER);
				$this->sender['port'] = 465;
				$this->charset = "utf-8";
			}
			require_once "SMTP.php";
			$this->SMTP = new SendMailSmtpClass($this->sender['user'], $this->sender['password'], $this->host, $this->sender['name'], $this->sender['port'], $this->charset);
			return true;
		}
		catch (Exception $err) {
			$this->err = $err->getMessage();
			return false;
		}
	}
	
	function GetSender ($key) {
		require_once("MySQL.php");
		$sql = new MySQL(DB_HOST,DB_USER,DB_PASS,DB_NAME);
		
		$arr = array("key"=>$key);
		$myrow = $sql->Select("sender_mail",$arr);
		return $myrow;
	}
	
	function get_code($text) { 
		$key = md5("hKJtehFteuye98e6s", true);
		$user_crypt = urlencode(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $text, MCRYPT_MODE_ECB)));
		return($user_crypt);
	}
	
	function get_uncode($text) {
		$key = md5("hKJtehFteuye98e6s", true);
		$user_crypt= mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, base64_decode(urldecode($text)), MCRYPT_MODE_ECB);
		return($user_crypt);
	}
	
	function Send() {
		if ($this->ConnectSMTP() === true) {
			$this->header = "MIME-Version: 1.0\r\n";
			$this->header .= "Content-type: text/html; charset=".$this->charset."\r\n";
			$this->header .= "From: ".$this->sender['name']." <'".$this->sender['user']."'>\r\n";
			$this->header .= "To: [name] <'[email]'>\r\n";

			$i = 0;
			foreach ($this->recipients as $key => $value) {
				$message = $this->ChengeData($value);
				$result =  $this->SMTP->send($value['email'], $this->subject, $message['body'], $message['header']);
				if($result === true) $i++;
			}
			return $i;
		} else return false;
	}
	
	function Create($tpl) {
		$tpl_url = str_replace("[lang]",$this->lang,$this->tpl_url);
		$f_header = __ROOT__.$tpl_url."header.tpl";
		$f_body = __ROOT__.$tpl_url.$tpl.".tpl";
		$f_footer = __ROOT__.$tpl_url."footer.tpl";
		$f_subject = __ROOT__.$tpl_url."subject.php";

		if (file_exists($f_header)) $this->body = file_get_contents($f_header);
		if (file_exists($f_body)) $this->body .= file_get_contents($f_body);
		if (file_exists($f_footer)) $this->body .= file_get_contents($f_footer);

		$this->body = str_replace("[site_name]",SITE_NAME,$this->body);
		$this->body = str_replace("[site_url]",SITE_URL,$this->body);
		
		require_once($f_subject);
		if (isset($mail_subject[$tpl])) $this->subject = $mail_subject[$tpl];
	}
	
	function ChengeData($arr) {
		$message['header'] = $this->header;
		$message['body'] = $this->body;

		foreach ($arr as $key => $value) {
			$tpl_key = "[".$key."]";
			$message['header'] = str_replace($tpl_key,$value,$message['header']); 
			$message['body'] = str_replace($tpl_key,$value,$message['body']);
		}
		return $message;
	}
	
	function GetHost ($email) {
		if ($this->host == "") {
			$this->host = SMTP_HOST;
			if (strpos($email,"yandex") > 0) $this->host = "ssl://smtp.yandex.ru";
			if (strpos($email,"gmail") > 0) $this->host = "ssl://smtp.gmail.com";
			if (strpos($email,"mail.ru") > 0) $this->host = "ssl://smtp.mail.ru";
		}
	}
}
?>