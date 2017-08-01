<?php
require_once (__ROOT__."/classes/Account.php");
$account = new Account;
if ($account->IsAuth()) { header("Location:../../cabinet/home"); exit; }

$lg->replace['page_title'] = $lg->replace['h1_authorization'];
$view = true;
?>