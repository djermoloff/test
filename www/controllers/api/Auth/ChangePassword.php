<?php
include_once(__ROOT__."/classes/Account.php");

try {
	$user = new Account;
	if ($user->ChangePassword()) {
		$lg_text = $lg->GetText("message","password_chenged");
		$arr = array("status"=>"ok", "message"=>$lg_text);
		echo json_encode($arr);
	} else throw new Exception ($user->err);
} catch (Exception $err) {
	$lg_text = $lg->GetText("error",$err->getMessage());
	$arr = array("status"=>"error", "error"=>$lg_text, "error_code"=>$err->getMessage());
	echo json_encode($arr);
	exit;
}
?>