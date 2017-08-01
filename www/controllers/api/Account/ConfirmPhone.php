<?php
try {
	if(!isset($_GET['pin'])) throw new Exception("PIN_REQUIRED"); 
	
	include_once(__ROOT__."/classes/Account.php");
	$user = new Account;
	if ($user->IsAuth() === false) throw new Exception ("NOT_AUTHORIZATION");
	$arr = array();
	
	$new_phone = $user->GetChangePhone();
	if ($new_phone === false) throw new Exception("NEW_PHONE_NOT_FOUND");
	
	require_once(__ROOT__."/classes/smsc.php");
	$sms = new SMSC;
	
	$sms->id_user = $user->data['id'];
	if ($sms->GetActivePIN() === false) throw new Exception("PIN_NOT_FOUND");
	
	if ($sms->IsPIN($_GET['pin']) === false) {
		if ($sms->failed < 5) 
			$lg_text = str_replace("[attemps]",5-$sms->failed,$lg->GetText("error","PIN_FAILED"));
		else
			$lg_text = $lg->GetText("error","PIN_FAILED_0");
		$result = array("status"=>"error", "error"=>$lg_text, "attemps"=>5-$sms->failed);
		echo json_encode($result);
		exit;
	}

	$user->data['phone'] = $new_phone; 
	array_push($arr,"phone");
	$user->data['phone_ok'] = TRUE; 
	array_push($arr,"phone_ok");
	
	$res = "";
	if (count($arr) > 0) $res = $user->UserUpdate($arr);
	if ($res !== false) {
		$lg_text = $lg->GetText("message","change_ok");
		$result = array("status"=>"ok", "message"=>$lg_text);
	} else {
		$lg_text = $lg->GetText("error","ERROR_DB");
		$result = array("status"=>"error", "error"=>$lg_text);
	}
} catch (Exception $err) {
	$lg_text = $lg->GetText("error",$err->getMessage());
	$result = array("status"=>"error", "error"=>$lg_text, "error_code"=>$err->getMessage());
}

echo json_encode($result);
?>