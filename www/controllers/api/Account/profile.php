<?php 
try {
	include_once(__ROOT__."/classes/Account.php");
	$user = new Account;
	if ($user->IsAuth() === false) throw new Exception ("NOT_AUTHORIZATION");
	$res = array("id"=>$user->data['id'],
				 "date"=>$user->data['date'],
				 "name"=>$user->data['name'],
				 "family"=>$user->data['family'],
				 "country"=>$user->data['country'],
				 "phone"=>$user->data['phone'],
				 "skype"=>$user->data['skype'],
				 "fb"=>$user->data['fb'],
				 "vk"=>$user->data['vk'],
				 "linkedin"=>$user->data['linkedin'],
				 "email"=>$user->data['email'],
				 "avatar"=>$user->data['avatar'],
				 "adv"=>$user->data['adv'],
				 "pm"=>$user->data['pm']);
	$result['country_list'] = $lg->CountryList();
	$result['status'] = "ok";
	$result['profile'] = $res;
} catch (Exception $err) {
	$lg_text = $lg->GetText("error",$err->getMessage());
	$result = array("status"=>"error", "error"=>$lg_text, "error_code"=>$err->getMessage());
}

echo json_encode($result);

?>