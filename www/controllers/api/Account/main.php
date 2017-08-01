<?php 
try {
	include_once(__ROOT__."/classes/Account.php");
	$user = new Account;
	if ($user->IsAuth() === false) throw new Exception ("NOT_AUTHORIZATION");
	
	$result['user'] = $user->GetArrayAccount(array("id","name","family","email","date","country","ip","balance","partner_balance"));
	$result['user']['country'] = $lg->CountryList($result['user']['country']);
	$url_replace = array("TEMPLATE_URL"=>TEMPLATE_URL,
						 "SITE_URL"=>SITE_URL);
	$result['url'] = $url_replace;
	$result['lg'] = $lg->replace;
	
} catch (Exception $err) {
	$lg_text = $lg->GetText("error",$err->getMessage());
	$result = array("status"=>"error", "error"=>$lg_text, "error_code"=>$err->getMessage());
}

echo json_encode($result);
?>