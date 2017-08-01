<?php
include_once(__ROOT__."/classes/Account.php");

$user = new Account;
if ($user->Activation()) header("Location:../../cabinet/authorization/?message=activated");
	else header("Location:../../cabinet/authorization/?error=".$user->err."&error_code=".$user->error_code);
?>