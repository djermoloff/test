<?php
include_once(__ROOT__."/classes/Account.php");

try {
	if (!isset($_GET['email'])) throw new Exception ("REQUEST_ERROR");
	$user = new Account;
	if ($user->FogotPassword($_GET['email'])) {
		$lg_text = $lg->GetText("message","restoration_sent");
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