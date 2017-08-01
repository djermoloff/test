<?php
require_once($_SERVER['DOCUMENT_ROOT']."/classes/Account.php");
$user = new Account;
if ($user->IsAuth() == false) { header('HTTP/1.0 404 not found'); exit; }
if ($user->data['role'] == NULL) { header('HTTP/1.0 404 not found'); exit; }
?>