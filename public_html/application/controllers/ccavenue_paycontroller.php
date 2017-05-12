<?php
require_once(CONF_INSTALLATION_PATH . 'public/includes/payment-plugins/ccavenue/Crypto.php');
class CCAvenue_payController extends PaymentController{
	private $key_name="CCAvenue";
	private function getPaymentForm($order_id){
		$pmObj=new Paymentsettings($this->key_name);
		$payment_settings=$pmObj->getPaymentSettings();
		$orderPaymentObj=new OrderPayment($order_id);
		$payment_gateway_charge=$orderPaymentObj->getOrderPaymentGatewayAmount();
		$order_info=$orderPaymentObj->getOrderPrimaryinfo();
		$frm=new Form('frm-ccavenue','frm-ccavenue');
		$frm->setRequiredStarWith('x');
		$frm->captionInSameCell(true);
		$frm->setExtra('class="siteForm" validator="system_validator" ');
		$frm->setAction(Utilities::generateAbsoluteUrl('ccavenue_pay','iframe'));
		$frm->setFieldsPerRow(1);
		$frm->addHiddenField('', 'tid', "","tid");
		$frm->addHiddenField('', 'merchant_id', $payment_settings["merchant_id"]);
		$frm->addHiddenField('', 'order_id', $order_info['invoice']);
		$frm->addHiddenField('', 'amount', $payment_gateway_charge);
		$frm->addHiddenField('', 'merchant_param1', $order_id);
//$frm->addHiddenField('', 'currency', $order_info["order_currency_code"]);
		$frm->addHiddenField('', 'language', "EN");
		$frm->addHiddenField('', 'redirect_url', Utilities::generateAbsoluteUrl('ccavenue_pay','callback'));
$frm->addHiddenField('', 'cancel_url', Utilities::generateAbsoluteUrl('custom','payment_failed'));	//$order_payment_gateway_description=sprintf(Utilities::getLabel('M_Order_Payment_Gateway_Description'),$order_info["site_system_name"],$order_info['invoice']);
//$frm->addHiddenField('', 'item_name_1', $order_payment_gateway_description);
$frm->addHiddenField('', 'billing_name', $order_info["customer_billing_name"]);
$frm->addHiddenField('', 'billing_address', $order_info["customer_billing_address_1"].', '.$order_info["customer_billing_address_2"]);
$frm->addHiddenField('', 'billing_city', $order_info["customer_billing_city"]);
$frm->addHiddenField('', 'billing_state', $order_info["customer_billing_state"]);
$frm->addHiddenField('', 'billing_zip', $order_info["customer_billing_postcode"]);
$frm->addHiddenField('', 'billing_country', $order_info['customer_billing_country']);
$frm->addHiddenField('', 'billing_tel', $order_info['customer_billing_phone']);
$frm->addHiddenField('', 'billing_email', $order_info['customer_email']);
$frm->addHiddenField('', 'delivery_name', $order_info["customer_shipping_name"]);
$frm->addHiddenField('', 'delivery_address', $order_info["customer_shipping_address_1"].', '.$order_info["customer_shipping_address_2"]);
$frm->addHiddenField('', 'delivery_city', $order_info["customer_shipping_city"]);
$frm->addHiddenField('', 'delivery_state', $order_info["customer_shipping_state"]);
$frm->addHiddenField('', 'delivery_zip', $order_info["customer_shipping_postcode"]);
$frm->addHiddenField('', 'delivery_country', $order_info['customer_shipping_country']);
$frm->addHiddenField('', 'delivery_tel', $order_info['customer_shipping_phone']);
$frm->addHiddenField('', 'integration_type', 'iframe_normal');
return $frm;
}
function charge($order_id){
	$pmObj=new Paymentsettings($this->key_name);
	if (!$payment_settings=$pmObj->getPaymentSettings()){
		Message::addErrorMessage($pmObj->getError());
		Utilities::redirectUserReferer();
	}
	$orderPaymentObj=new OrderPayment($order_id);
	$payment_amount=$orderPaymentObj->getOrderPaymentGatewayAmount();
	$order_info=$orderPaymentObj->getOrderPrimaryinfo();
	if ($order_info && $order_info["order_payment_status"]==0){
		$frm=$this->getPaymentForm($order_id);
		$this->set('frm', $frm);
		$this->set('payment_amount', $payment_amount);
	}else{
		$this->set('error', Utilities::getLabel('M_INVALID_ORDER_PAID_CANCELLED'));
	}
	$this->set('order_info', $order_info);
	$this->_template->render(true,false);	
}
public function iframe(){
	$pmObj=new Paymentsettings($this->key_name);
	$payment_settings=$pmObj->getPaymentSettings();
	$orderPaymentObj=new OrderPayment($order_id);
	$payment_gateway_charge=$orderPaymentObj->getOrderPaymentGatewayAmount();
	$order_info=$orderPaymentObj->getOrderPrimaryinfo();
	$working_key=$payment_settings['working_key'];
	$access_code=$payment_settings['access_code'];
	$merchant_data='';
	$post = Syspage::getPostedVar();
	foreach ($post as $key => $value){
		$merchant_data.=$key.'='.$value.'&';
	}
//$merchant_data= str_replace("#~#","&",$merchant_data);
	$merchant_data.="currency=INR";
//die($merchant_data);		
$encrypted_data=encrypt($merchant_data,$working_key); // Method for encrypting the data.
if ($payment_settings["transaction_mode"]==1) {
	$iframe_url = 'https://secure.ccavenue.com';
} elseif ($payment_settings["transaction_mode"]==0) {
	$iframe_url = 'https://test.ccavenue.com';
}
$iframe_url.='/transaction/transaction.do?command=initiateTransaction&encRequest='.$encrypted_data.'&access_code='.$access_code;
$this->set('url', $iframe_url);
$this->_template->render(true,false);	
}
public function callback() {
	$pmObj=new Paymentsettings($this->key_name);
	$payment_settings=$pmObj->getPaymentSettings();
	$post = Syspage::getPostedVar();
	$workingKey=$payment_settings['working_key'];
$encResponse=$post["encResp"];			//This is the response sent by the CCAvenue Server
$rcvdString=decrypt($encResponse,$workingKey);		//Crypto Decryption used as per the specified working key.
$request=$rcvdString;
$order_status="";
$decryptValues=explode('&', $rcvdString);
$dataSize=sizeof($decryptValues);
for($i = 0; $i < $dataSize; $i++){
	$information=explode('=',$decryptValues[$i]);
	if($i==3)$order_status=$information[1];
	if($i==26)$order_id=$information[1];
	if($i==10)$paid_amount=$information[1];
	if($i==1)$tracking_id=$information[1];
}
$orderPaymentObj=new OrderPayment($order_id);
$payment_gateway_charge=$orderPaymentObj->getOrderPaymentGatewayAmount();
if ($payment_gateway_charge>0){
	$total_paid_match = ((float)$paid_amount == $payment_gateway_charge);
	if (!$total_paid_match) {
		$request .= "\n\n CCAvenue :: TOTAL PAID MISMATCH! " . strtolower($paid_amount) . "\n\n";
	}
	if ($order_status=="Success" && $total_paid_match){
		$orderPaymentObj->addOrderPayment($payment_settings["pmethod_name"],$tracking_id,$payment_gateway_charge,Utilities::getLabel("L_Received_Payment"),$request);
		Utilities::redirectUser(Utilities::generateUrl('custom','payment_success'));
	}else{
		$orderPaymentObj->addOrderPaymentComments($request);
		Utilities::redirectUser(Utilities::generateUrl('custom','payment_failed'));
	}
}
}
}
