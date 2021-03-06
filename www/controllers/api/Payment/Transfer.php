<?php
try {
	if (!isset($_GET['amount'])) throw new Exception ("AMOUNT_NOT_FOUND");
	if (!isset($_GET['email'])) throw new Exception ("EMAIL_NOT_FOUND");
	
	require_once(__ROOT__."/classes/Account.php");
	$user = new Account;
	if ($user->IsAuth() === false) throw new Exception ("NOT_AUTHORIZATION");
	if ($user->data['email'] == $_GET['email']) throw new Exception ("TRANSFER_YOURSELF_UNAVALABLE");
	$userPayee = new Account;
	if ($userPayee->UserInitEmail($_GET['email']) === false) throw new Exception("USER_NOT_FOUND");
	
	
	if ($user->data['phone'] == "") throw new Exception ("PHONE_NOT_FULLID");
	if ($user->data['phone_ok'] == 0) throw new Exception ("PHONE_NOT_CONFIRM");
	require_once(__ROOT__."/classes/smsc.php");
	$sms = new SMSC;
	
	$sms->id_user = $user->data['id'];
	$sms->phone = $user->data['phone'];
	
	if ($sms->GetActivePIN() === false) $sms->CreatePIN();
	
	if (!isset($_GET['pin']) || $_GET['pin'] == "") {
		$lg_text = $lg->GetText("message","sms_sent");
		$result = array("status"=>"smsSent", "message"=>$lg_text, "attemps"=>5-$sms->failed);
		echo json_encode($result);
		exit;
	} else {
		if ($sms->IsPIN($_GET['pin']) === false) {
			if ($sms->failed < 5) 
				$lg_text = str_replace("[attemps]",5-$sms->failed,$lg->GetText("error","PIN_FAILED"));
			else
				$lg_text = $lg->GetText("error","PIN_FAILED_0");
			$result = array("status"=>"error", "error"=>$lg_text, "attemps"=>5-$sms->failed);
			echo json_encode($result);
			exit;
		}
	}
	
	
	require_once(__ROOT__."/classes/Payment.php");
	$payment = new Payment;
	
	$payment->amount = $_GET['amount'];
	$payment->currency = "USD";
	$payment->amount = (int)$payment->amount;
	$payment->fee = $payment->amount*0.02;
	if ($payment->amount <= 0) throw new Exception ("AMOUNT_NOT_CORRECT");
	if ($user->data['balance'] < $payment->amount) throw new Exception ("AMOUNT_NOT_CORRECT");
	
	$payment->type = "transfer-out";
	$payment->id_user = $user->data['id'];
	if ($payment->WithdrawFromBalance(0) === false)  throw new Exception ($payment->err);
	
	$payment->type = "transfer-in";
	$payment->id_user = $userPayee->data['id'];
	if ($payment->DepositToBalance(0) === false)  throw new Exception ($payment->err);
	
	$user->UserInitID($user->data['id']);
	$result['status'] = "ok";
	$result['message'] = $lg->GetText("message","transfer_ok");
	$result['partner_balance'] = $user->data['partner_balance'];
	$result['balance'] = $user->data['balance'];
	
} catch (Exception $err) {
	$lg_text = $lg->GetText("error",$err->getMessage());
	$result = array("status"=>"error", "error"=>$lg_text, "error_code"=>$err->getMessage());
}

echo json_encode($result);
?>