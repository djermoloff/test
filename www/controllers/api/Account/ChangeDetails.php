<?php
try {
	include_once(__ROOT__."/classes/Account.php");
	$user = new Account;
	if ($user->IsAuth() === false) throw new Exception ("NOT_AUTHORIZATION");
	$arr = array();
	if(isset($_GET['pm'])) {
		$user->data['pm'] = $_GET['pm'];
		array_push($arr,"pm");
	}
	
	if(isset($_GET['adv'])) {
		$user->data['adv'] = $_GET['adv'];
		array_push($arr,"adv");
	}
	
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