<?php
try {
	require_once(__ROOT__."/classes/Account.php");
	$user = new Account;
	if ($user->IsAuth() === false) throw new Exception ("NOT_AUTHORIZATION");
	
	require_once(__ROOT__."/classes/Partners.php");
	$cell = new Partners;
	
	if ($cell->CreateCell($user->data['id'],$user->data['refer']) === false) throw new Exception ($cell->err);
	
	$result['status'] = "ok";
	
} catch (Exception $err) {
	$lg_text = $lg->GetText("error",$err->getMessage());
	$result = array("status"=>"error", "error"=>$lg_text, "error_code"=>$err->getMessage());
}

echo json_encode($result);

?>