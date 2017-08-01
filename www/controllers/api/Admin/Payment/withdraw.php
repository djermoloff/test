<?php
try {
	if ($user->data['role'] != "admin" && $user->data['role'] != "operator") throw new Exception ("NOT_ACCESS");
	if (!isset($_GET['id'])) throw new Exception ("ID_NOT_FOUND");
	
	require_once(__ROOT__."/classes/Payment.php");
	$payment = new Payment;
	$payment->Init($_GET['id']);
	if (isset($_GET['payer'])) $payment->payer = $_GET['payer'];
	if (isset($_GET['fee'])) $payment->fee = $_GET['fee'];
	if (isset($_GET['batch'])) $payment->batch = $_GET['batch'];
	if (isset($_GET['comment'])) $payment->comment = $_GET['comment'];
	if ($payment->ConfirmWithdraw() === false) throw new Exception ($payment->err);
	$result['status'] = "ok";
	$result['message'] = $lg->GetText("message","executed");
	
} catch (Exception $err) {
	$lg_text = $lg->GetText("error",$err->getMessage());
	$result = array("status"=>"error", "error"=>$lg_text, "error_code"=>$err->getMessage());
	
}

echo json_encode($result);
?>