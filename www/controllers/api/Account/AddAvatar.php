<?php
try {
	$max_avatar = 280;
	
	include_once(__ROOT__."/classes/Account.php");
	$user = new Account;
	if ($user->IsAuth() === false) throw new Exception ("NOT_AUTHORIZATION");
	if (!isset($_FILES["file"]["name"]) || $_FILES["file"]["name"] == "") throw new Exception ("FILE_NOT_FOUND");
	$path_info = pathinfo($_FILES["file"]["name"]);
	$str_1 = strtolower($path_info['extension']);
	if ($str_1 != 'jpg' && $str_1 != 'jpeg' && $str_1 != 'gif' && $str_1 == 'png') throw new Exception ("FILE_FORMAT_IS_NOT_SUPPORTED");
	if($_FILES["file"]["size"] > 1024*10*1024)  throw new Exception ("EXCESS_FILE_SIZE_10");
	$name = md5(time()."-".$user->data['email']);
	$url = __ROOT__."/images/avatars";
	if (!is_dir($url)) mkdir($url, 0775);
	if ($user->upload_images($url,$name,$max_avatar) == false) throw new Exception ("ERROR_UPLOAD_IMAGES");
	
	$arr = array();
	$user->data['avatar'] = $name.".".$str_1;
	array_push($arr,"avatar");

	$url_av = SITE_URL."/images/avatars/".$name.".".$str_1;
	$res = "";
	if (count($arr) > 0) $res = $user->UserUpdate($arr);
	if ($res !== false) {
		$lg_text = $lg->GetText("message","change_ok");
		$result = array("status"=>"ok", "message"=>$lg_text, "url_avatar"=>$url_av);
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