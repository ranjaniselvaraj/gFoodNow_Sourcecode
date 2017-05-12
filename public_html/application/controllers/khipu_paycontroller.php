<?php
require_once(CONF_INSTALLATION_PATH . 'public/includes/payment-plugins/khipu/init.php');
class khipu_payController extends PaymentController{
	private $key_name="khipu";
	private function getPaymentForm($order_id){
		$frm=new Form('frmPaymentForm','frmPaymentForm');
		$frm->setRequiredStarWith("x");
		$frm->setValidatorJsObjectName('system_validator');
		$frm->setExtra('class="siteForm" validator="system_validator" ');
// $frm->setAction(Utilities::generateUrl('khipu_pay','send',array($order_id)));		
		$frm->setAction('https://khipu.com/api/createPaymentPage');
		$frm->captionInSameCell(true);
		$frm->setFieldsPerRow(1);
		$frm->addHiddenField('', 'receiver_id');
		$frm->addHiddenField('', 'subject');
		$frm->addHiddenField('', 'body');
		$frm->addHiddenField('', 'amount');
		$frm->addHiddenField('', 'return_url');
		$frm->addHiddenField('', 'cancel_url');
		$frm->addHiddenField('', 'notify_url');
		$frm->addHiddenField('', 'custom');
		$frm->addHiddenField('', 'transaction_id');
		$frm->addHiddenField('', 'payer_email');
		$frm->addHiddenField('', 'picture_url');
		$frm->addHiddenField('', 'hash');
		$frm->addSubmitButton('','btn_submit',Utilities::getLabel('M_Loading...'),'button-confirm');
		$frm->setJsErrorDisplay('afterfield');
		return $frm;
	}
	function charge($order_id){
		$pmObj=new Paymentsettings($this->key_name);
		if (!$payment_settings=$pmObj->getPaymentSettings()){
			Message::addErrorMessage($pmObj->getError());
			Utilities::redirectUserReferer();
		}
		$orderPaymentObj = new OrderPayment($order_id);
		$payment_amount=$orderPaymentObj->getOrderPaymentGatewayAmount();
		$order_info=$orderPaymentObj->getOrderPrimaryinfo();
// print_r($payment_settings);die;
		$receiver_id = $payment_settings['receiver_id'];
		$subject = Utilities::getLabel('M_YoKart_Payment');
		$body = '';
		$return_url = Utilities::generateAbsoluteUrl('custom', 'payment_success');
		$notify_url = Utilities::generateAbsoluteUrl('khipu_pay', 'send');
$cancel_url = '';//Utilities::generateAbsoluteUrl();
$custom = $order_id;
$transaction_id = 'Order-'.$order_id;
$picture_url = '';
$payer_email = $order_info['customer_email'];
$secret = $payment_settings['secret_key'];
$concatenated = "receiver_id=$receiver_id&subject=$subject&body=$body&amount=$payment_amount&return_url=$return_url&cancel_url=$cancel_url&custom=$custom&transaction_id=$transaction_id&picture_url=$picture_url&payer_email=$payer_email&secret=$secret";
$hash = sha1($concatenated);
$configuration = new Configuration();
$configuration->setReceiverId ($payment_settings['receiver_id']);
$configuration-> setSecret ($payment_settings['secret_key']);
//$configuration-> setDebug (true);
$client = new ApiClient ($configuration);
$payments = new PaymentsApi ($client);
try {
$response = $payments->paymentsPost ( 'YoKart Payment' // Reason for purchase
,"CLP" // Currency
, ceil($payment_amount) // Amount
,$transaction_id // transaction ID in trade
, $custom // optional field greater long to send information to the URL notification
, Null // Payment Description
, Null // ID of the bank to pay
,$return_url // return URL
,$cancel_url // URL rejection
,$picture_url // URL Product Image
,$notify_url // URL notification
,"1.3"  // notification version of the API
, Null // Expiry Date
, Null // Send the payment by email
, Null // Name of payer
, Null // Email payer
, Null // Send email reminders
, Null // E-mail of responsible payment
, Null // Personal identifier of the payer, if used only you are paid with this
, Null // Commission for the integrator
);
// print_r ($response);
} catch(exception $e) {
//die($e->getMessage());
	echo $e->getMessage();
}
Utilities::redirectUser($response->getPaymentUrl());
}
public function send() {
	$pmObj=new Paymentsettings($this->key_name);
	$payment_settings=$pmObj->getPaymentSettings();
	$post = Syspage::getPostedVar();
	$api_version = $post['api_version'];
	$notification_token = $post['notification_token'];
	try{
		if ($api_version == '1.3') {
			$configuration = new Configuration ();
			$configuration-> setSecret ($payment_settings['secret_key']);
			$configuration-> setReceiverId ($payment_settings['receiver_id']);
// $ Configuration-> setDebug (true);
			$client = new ApiClient ($configuration);
			$payments = new PaymentsApi ($client);
			$response = $payments->paymentsGet($notification_token);
			$order_id = $response->getCustom();
			$orderPaymentObj=new OrderPayment($order_id);
			/* Retrieve Payment to charge corresponding to your order */
			$order_payment_amount=$orderPaymentObj->getOrderPaymentGatewayAmount();
			if ($order_payment_amount>0){
				/* Retrieve Primary Info corresponding to your order */
				$order_info=$orderPaymentObj->getOrderPrimaryinfo();
				$order_actual_paid = ceil($order_payment_amount);
				$json = array();
				if(!$response){
					throw new Exception(Utilities::getLabel('M_EMPTY_GATEWAY_RESPONSE'));
				}
				if ($response-> getReceiverId () == $payment_settings['receiver_id']) {
					if (strtolower($response-> getStatus ()) == 'done'){
						if ($response->getAmount () == $order_actual_paid) {
// Make payment as complete and deliver the good or service
							if (!$orderPaymentObj->addOrderPayment($payment_settings["pmethod_name"],$response->getTransactionId(),$response->getAmount(),Utilities::getLabel("L_Received_Payment"),$response->__toString())){
							}
						}else{
							$request = $response->__toString()."\n\n KHIPU :: TOTAL PAID MISMATCH! " . $response-> getAmount() . "\n\n";
							$orderPaymentObj->addOrderPaymentComments($request);
						}
					}
/*if (strtolower($response-> getStatus ()) == 'done' && $response->getAmount () == $order_actual_paid) {
if (!$orderPaymentObj->addOrderPayment($payment_settings["pmethod_name"],$response->getTransactionId(),$response->getAmount(),Utilities::getLabel("L_Received_Payment"),$response->__toString())){
//mail to admin/user
}
// Make payment as complete and deliver the good or service
}*/
} else {
	$request = $response->__toString()."\n\n KHIPU :: RECEIVER MISMATCH! " . $response-> getReceiverId () . "\n\n";
	$orderPaymentObj->addOrderPaymentComments($request);
// mail();
// Not match receiver_id
}
}
else{
	$json['error'] = Utilities::getLabel('M_Invalid_Request');
}
}
else {
// Use previous version of Notification API
}				
}
catch(OmiseNotFoundException $e){
	$json['error'] = 'ERROR: ' . $e->getMessage();
}
catch(exception $e){
	$json['error'] = 'ERROR: ' . $e->getMessage();
}		
echo json_encode($json);
}
}
