<?php
$arr = array();
if (isset($_POST['ac_order_id'])) $arr = $_POST;
if (isset($_GET['ac_order_id'])) $arr = $_GET;

try {
	require_once(__ROOT__."/classes/Payment.php");
	$pay = new Payment;
	$m_pay = $pay->Init($arr['ac_order_id']);

	if ($m_pay === false) throw new Exception ("PAYMENT_NOT_FOUND");
	if ($m_pay['confirm']) throw new Exception ("PAYMENT_ALREADY_CONFIRM");
	
	$payee_account = $pay->GetPayeeAccount("adv",$m_pay['currency']);

	define('ADV_PASSWORD',  $payee_account['secret']);
	$string=$arr['ac_transfer'].':'.$arr['ac_start_date'].':'.$arr['ac_sci_name'].':'.$arr['ac_src_wallet'].':'.$arr['ac_dest_wallet'].':'.$arr['ac_order_id'].':'.$arr['ac_amount'].':'.$arr['ac_merchant_currency'].':'.ADV_PASSWORD;
	 
	$hash=hash('sha256',$string);

	if($hash!=$arr['ac_hash'])  throw new Exception ("HASH_WRONG");

	if ($arr['ac_transaction_status'] != "COMPLETED")  throw new Exception ("STATUS_WRONG");
	if ($m_pay['amount'] != $arr['ac_merchant_amount'])  throw new Exception ("AMOUNT_WRONG");
	if ($m_pay['currency'] != $arr['ac_merchant_currency'])  throw new Exception ("CURRENCY_WRONG");
	if ($arr['ac_sci_name'] != $payee_account['adv_sci'])  throw new Exception ("SCI_WRONG");
	
	$pay->date_confirm = $arr['ac_start_date'];
	$pay->payer = $arr['ac_src_wallet'];
	$pay->payee = $arr['ac_dest_wallet'];
	$pay->fee = $arr['ac_fee'];
	$pay->batch = $arr['ac_transfer'];
	$pay->ConfirmPayment();
	
	echo "OK";
	
} catch (Exception $err) {
	echo $err->getMessage();
	exit;
}
?>