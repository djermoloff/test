<?php 
try {
	include_once(__ROOT__."/classes/Account.php");
	$user = new Account;
	if ($user->IsAuth() === false) throw new Exception ("NOT_AUTHORIZATION");
	$res = $user->GetPartners(array("id","date","name","family","email"));
	if ($res !== 0 && $res !== false) {
		$result['status'] = "ok";
		$result['partners'] = $res;
	} else {
		$result['status'] = "not_found";
	}
} catch (Exception $err) {
	$lg_text = $lg->GetText("error",$err->getMessage());
	$result = array("status"=>"error", "error"=>$lg_text, "error_code"=>$err->getMessage());
}

echo json_encode($result);

?>