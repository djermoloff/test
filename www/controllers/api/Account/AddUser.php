<?php
try {
	if (!isset($_GET['email'])) throw new Exception ("REQUEST_ERROR");
	$_GET['lang'] = $lg->lang;
	include_once(__ROOT__."/classes/Account.php");
	$user = new Account;
	if ($user->UserInitEmail($_GET['email']) === true) $result = array("status"=>"ok", "id"=>$user->data['id']);
		else {
		$_GET['password'] = substr(md5(time()),0,8);
		$res = $user->UserRegistration($_GET);
		if ($res === false) throw new Exception ($user->err);
		$lg_text = $lg->GetText("message","password_chenged");
		$result = array("status"=>"ok", "id"=>$res);
	}
} catch (Exception $err) {
	$lg_text = $lg->GetText("error",$err->getMessage());
	$result = array("status"=>"error", "error"=>$lg_text, "error_code"=>$err->getMessage());
}
echo json_encode($result);
?>