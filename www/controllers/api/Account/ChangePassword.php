<?php 
include_once(__ROOT__."/classes/Account.php");
$user = new Account;
if ($user->IsAuth()) {
	$res = $user->ProfileChangePassword();
	if ($res !== false) {
		$lg_text = $lg->GetText("message","password_chenged");
		$result = array("status"=>"ok", "message"=>$lg_text);
	} else {
		$lg_text = $lg->GetText("error",$user->err);
		$result = array("status"=>"error", "error"=>$lg_text);
	}
}

echo json_encode($result);

?>