<?php
include_once(__ROOT__."/classes/Account.php");

$user = new Account;
if ($user->IsAuth()) $arr = array("status"=>"ok"); else $arr = array("status"=>"not_authorized");

echo json_encode($arr);
?>