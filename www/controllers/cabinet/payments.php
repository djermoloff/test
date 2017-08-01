<?php
require_once (__ROOT__."/classes/Account.php");
$account = new Account;
if ($account->IsAuth() == false) { header("Location:../../cabinet/authorization"); exit; }

$lg->replace['page_title'] = $lg->replace['h1_payments'];
$view = true;
?>