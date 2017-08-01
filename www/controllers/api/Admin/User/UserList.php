<?php
try {
	require_once(__ROOT__."/classes/Admin.php");
	$admin = new Admin;
	
	$list = 1;
	if (isset($_GET['list'])) $list = (int)$_GET['list'];
	$users = $admin->Users($list);
	$result['status'] = "ok";
	$result['users'] = $users;

} catch (Exception $err) {
	$lg_text = $lg->GetText("error",$err->getMessage());
	$result = array("status"=>"error", "error"=>$lg_text, "error_code"=>$err->getMessage());
	
}

echo json_encode($result);
?>