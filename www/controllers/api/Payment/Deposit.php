<?php
try {
	if (!isset($_GET['amount'])) throw new Exception ("AMOUNT_NOT_FOUND");
	if (!isset($_GET['type'])) throw new Exception ("TYPE_NOT_FOUND");
	
	require_once(__ROOT__."/classes/Account.php");
	$user = new Account;
	if ($user->IsAuth() === false) throw new Exception ("NOT_AUTHORIZATION");
	
	require_once(__ROOT__."/classes/Payment.php");
	$payment = new Payment;
	
	$payment->id_user = $user->data['id'];
	$payment->ps_type = $_GET['type'];
	$payment->type = "deposit";
	$payment->amount = $_GET['amount'];
	$payment->currency = "USD";
	if ($payment->GreateDeposit() === false)  throw new Exception ($payment->err);

	$result['status'] = "ok";
	$result['form'] = $payment->form;
	
} catch (Exception $err) {
	$lg_text = $lg->GetText("error",$err->getMessage());
	$result = array("status"=>"error", "error"=>$lg_text, "error_code"=>$err->getMessage());
}

echo json_encode($result);

?>