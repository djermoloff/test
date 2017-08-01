<?php
try {
	include_once(__ROOT__."/classes/Account.php");
	$user = new Account;
	if ($user->IsAuth() === false) throw new Exception ("NOT_AUTHORIZATION");
	$arr = array();
	
	$pinSent = false;
	if(isset($_GET['phone'])) {
		if ($user->data['phone'] != $_GET['phone']) {
			$pinSent = getPin($user->data['id'],$_GET['phone']);
			$user->AddPhone($_GET['phone']);
		}
	} else throw new Exception("PHONE_REQUIRED"); 
	
	if(isset($_GET['skype'])) {
		$user->data['skype'] = $_GET['skype'];
		array_push($arr,"skype");
	}
	
	if(isset($_GET['fb'])) {
		$user->data['fb'] = $_GET['fb'];
		array_push($arr,"fb");
	}
	if(isset($_GET['vk'])) {
		$user->data['vk'] = $_GET['vk'];
		array_push($arr,"vk");
	}
	if(isset($_GET['linkedin'])) {
		$user->data['linkedin'] = $_GET['linkedin'];
		array_push($arr,"linkedin");
	}

	
	$res = "";
	if (count($arr) > 0) $res = $user->UserUpdate($arr);
	if ($res !== false) {
		if ($pinSent) {
			$lg_text = $lg->GetText("message","confirmPhone");
			$result = array("status"=>"smsSent", "message"=>$lg_text);
		} else { 
			$lg_text = $lg->GetText("message","change_ok");
			$result = array("status"=>"ok", "message"=>$lg_text);
		}
	} else {
		$lg_text = $lg->GetText("error","ERROR_DB");
		$result = array("status"=>"error", "error"=>$lg_text);
	}
} catch (Exception $err) {
	$lg_text = $lg->GetText("error",$err->getMessage());
	$result = array("status"=>"error", "error"=>$lg_text, "error_code"=>$err->getMessage());
}

echo json_encode($result);

function getPin($id_user, $phone) {
	require_once(__ROOT__."/classes/smsc.php");
	$sms = new SMSC;
	
	$sms->id_user = $id_user;
	$sms->phone = $phone;
	
	if ($sms->GetActivePIN() === false) $sms->CreatePIN();
	
	return true;
}
?>