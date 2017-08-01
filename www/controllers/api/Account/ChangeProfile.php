<?php
try {
	include_once(__ROOT__."/classes/Account.php");
	$user = new Account;
	if ($user->IsAuth() === false) throw new Exception ("NOT_AUTHORIZATION");
	$arr = array();
	if(isset($_GET['name'])) {
		$user->data['name'] = $_GET['name'];
		array_push($arr,"name");
	}
	if(isset($_GET['family'])) {
		$user->data['family'] = $_GET['family'];
		array_push($arr,"family");
	}
	if(isset($_GET['country'])) {
		$user->data['country'] = $_GET['country'];
		array_push($arr,"country");
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