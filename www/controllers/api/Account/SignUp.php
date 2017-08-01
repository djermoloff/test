<?php
try {
	include_once(__ROOT__."/classes/Account.php");
	$user = new Account;
	if ($user->IsAuth()) throw new Exception ("AUTH_ALREADY");
	if (!isset($_GET['email'])) throw new Exception ("REQUEST_ERROR");
	if (!isset($_GET['password'])) throw new Exception ("REQUEST_ERROR");
	if (strlen($_GET['password']) < 6) throw new Exception ("SMALL_PASSWORD");
	if (!isset($_GET['lang'])) $_GET['lang'] = $lg->lang;
	if (isset($_COOKIE['partner'])) $_GET['refer'] = $_COOKIE['partner']; else $_GET['refer'] = 0;
	
	$request = $_GET;
	$id_user = $user->UserRegistration($request);
	if ($id_user !== false) {
		$lg_text = $lg->GetText("message","reg_ok");
		$arr = array("status"=>"ok", "message"=>$lg_text);
		echo json_encode($arr);
	} else throw new Exception ($user->err);
	exit;
}
catch (Exception $err) {
	$lg_text = $lg->GetText("error",$err->getMessage());
	$arr = array("status"=>"error", "error"=>$lg_text, "error_code"=>$err->getMessage());
	echo json_encode($arr);
	exit;
}
?>