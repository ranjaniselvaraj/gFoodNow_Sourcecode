<?php
/**
* Description: This class deals with both mode of 2Checkout payment types i.e. Hosted Checkout and API Checkout.
* Hosted Checkout: 
*			1. Customer is redirected to 2checkout server for the payment. 
*           2. All the details related to shipping and billing is passed to 2checkout server.
*           3. Customer enter the credit card information to make the payments and on success, redirected to thankyou page or payment failure page in other case.
* API Checkout: 
*			1. Customer is taken to payments page on our server. 
*           2. Customer enter the credit card information to make the payments and on success, redirected to thankyou page or payment failure page in other case.
*/
class Twocheckout_payController extends PaymentController{
	private $key_name		=	"TwoCheckout";
private $payment_type	= 	"";//holds two values HOSTED or API
public function default_action(){
	echo "Unauthorized Action";
	Utilities::redirectUser(Utilities::generateUrl('/'));
}
private function getPaymentForm($order_id){
	$payment_settings=$this->getPaymentSettings();
	$this->payment_type = $payment_settings['payment_type'];
if($this->payment_type=='HOSTED'){//check admin controller for confirmation
	return $this->getHostedCheckoutForm($payment_settings, $order_id);
}else{
	return $this->getAPICheckoutForm($payment_settings, $order_id);
}
}
private function getPaymentSettings(){
	$pmObj=new Paymentsettings($this->key_name);
	$payment_settings=$pmObj->getPaymentSettings();
	return $payment_settings;
}
private function getHostedCheckoutForm($payment_settings, $order_id){
	$orderPaymentObj=new OrderPayment($order_id);
	$payment_gateway_charge=$orderPaymentObj->getOrderPaymentGatewayAmount();
	/* Retrieve Primary Info corresponding to your order */
	$order_info=$orderPaymentObj->getOrderPrimaryinfo();
	if ($payment_settings["transaction_mode"]==1) {
		$action_url = 'https://www.2checkout.com/checkout/purchase';
	} elseif ($payment_settings["transaction_mode"]==0) {
		$action_url = 'https://sandbox.2checkout.com/checkout/purchase';
	}
	$frm=new Form('frmTwoCheckout','frmTwoCheckout');
	$frm->captionInSameCell(true);
	$frm->setRequiredStarWith('x');
	$frm->setExtra('class="siteForm" validator="system_validator" ');
	$frm->setAction($action_url);
	$frm->setFieldsPerRow(1);
	$frm->addHiddenField('sid', 'sid', $payment_settings["sellerId"]);
$frm->addHiddenField('mode', 'mode', '2CO');//it should always be 2CO (We're using hosted payment approach)
$txnid=$order_info["invoice"];
$frm->addHiddenField('li_0_name', 'li_0_name', 'Payment for Order - Invoice #'.$txnid);
$frm->addHiddenField('li_0_price', 'li_0_price', $payment_gateway_charge);
$frm->addHiddenField('li_0_product_id', 'li_0_product_id', $order_id);//in our case it is order id
$frm->addHiddenField('li_0_tangible', 'li_0_tangible', 'N');//no need of charging or calculating shipping as we have already handled the same at our end.
$frm->addHiddenField('currency_code', 'currency_code', $order_info["order_currency_code"]);
$frm->addHiddenField('merchant_order_id', 'merchant_order_id', $txnid);
$frm->addHiddenField('purchase_step', 'purchase_step', 'payment-method');
$frm->addHiddenField('x_receipt_link_url', 'x_receipt_link_url', Utilities::generateAbsoluteUrl('twocheckout_pay','callback'));
/**Pre-populate Billing Information**/
$frm->addHiddenField('card_holder_name', 'card_holder_name', html_entity_decode($order_info['customer_name'], ENT_QUOTES, 'UTF-8'));
$frm->addHiddenField('street_address', 'street_address', html_entity_decode($order_info['customer_billing_address_1'], ENT_QUOTES, 'UTF-8'));
$frm->addHiddenField('street_address2', 'street_address2', html_entity_decode($order_info['customer_billing_address_2'], ENT_QUOTES, 'UTF-8'));
$frm->addHiddenField('city', 'city', html_entity_decode($order_info['customer_billing_city'], ENT_QUOTES, 'UTF-8'));
$frm->addHiddenField('state', 'state', html_entity_decode($order_info['customer_billing_state'], ENT_QUOTES, 'UTF-8'));
$frm->addHiddenField('zip', 'zip', html_entity_decode($order_info['customer_billing_postcode'], ENT_QUOTES, 'UTF-8'));
$frm->addHiddenField('country', 'country', html_entity_decode($order_info['customer_billing_country'], ENT_QUOTES, 'UTF-8'));
$frm->addHiddenField('email', 'email', html_entity_decode($order_info['customer_email'], ENT_QUOTES, 'UTF-8'));
$frm->addHiddenField('phone', 'phone', html_entity_decode($order_info['customer_phone'], ENT_QUOTES, 'UTF-8'));
/**The value of customer_phone_extension is not available in the $order_info**/
#$frm->addHiddenField('phone_extension', 'phone_extension', html_entity_decode($order_info['customer_phone_extension'], ENT_QUOTES, 'UTF-8'));
/**Pre-populate Shipping Information**/
$frm->addHiddenField('ship_name', 'ship_name', html_entity_decode($order_info['customer_shipping_name'], ENT_QUOTES, 'UTF-8'));
$frm->addHiddenField('ship_street_address', 'ship_street_address', html_entity_decode($order_info['customer_shipping_address_1'], ENT_QUOTES, 'UTF-8'));
$frm->addHiddenField('ship_street_address2', 'ship_street_address2', html_entity_decode($order_info['customer_shipping_address_2'], ENT_QUOTES, 'UTF-8'));
$frm->addHiddenField('ship_city', 'ship_city', html_entity_decode($order_info['customer_shipping_city'], ENT_QUOTES, 'UTF-8'));
$frm->addHiddenField('ship_state', 'ship_state', html_entity_decode($order_info['customer_shipping_state'], ENT_QUOTES, 'UTF-8'));
$frm->addHiddenField('ship_zip', 'ship_zip', html_entity_decode($order_info['customer_shipping_postcode'], ENT_QUOTES, 'UTF-8'));
$frm->addHiddenField('ship_country', 'ship_country', html_entity_decode($order_info['customer_shipping_country'], ENT_QUOTES, 'UTF-8'));
$frm->setJsErrorDisplay('afterfield');
return $frm;
}
private function getAPICheckoutForm($payment_settings, $order_id){
	$frm=new Form('frmTwoCheckout','frmTwoCheckout');
	$frm->setRequiredStarWith("x");
	$frm->setValidatorJsObjectName('system_validator');
	$frm->setExtra('class="siteForm" validator="system_validator" ');
	$frm->setAction(Utilities::generateUrl('twocheckout_pay','send',array($order_id)));
	$frm->captionInSameCell(true);
	$frm->setFieldsPerRow(1);
	$frm->addRequiredField('<label>'.Utilities::getLabel('M_ENTER_CREDIT_CARD_NUMBER').'</label>', '','','ccNo','class="type-bg"');
#$frm->addRequiredField('<label>'.Utilities::getLabel('M_CARD_HOLDER_NAME').'</label>', 'ccOwner');//2checkout credit card processing has no use of cc owner name.
	$frm->addHiddenField( '', 'token', '', '', '');
	$data['months'] = array();
	for ($i = 1; $i <= 12; $i++) {
		$data['months'][sprintf('%02d', $i)] = strftime('%B', mktime(0, 0, 0, $i, 1, 2000));
	}
	$today = getdate();
	$data['year_expire'] = array();
	for ($i = $today['year']; $i < $today['year'] + 11; $i++) {
		$data['year_expire'][strftime('%Y', mktime(0, 0, 0, 1, 1, $i))] = strftime('%Y', mktime(0, 0, 0, 1, 1, $i));
	}
	$fldMon=$frm->addSelectBox('<label>'.Utilities::getLabel('M_EXPIRY_DATE').'</label><div class="clear"></div>', '',$data['months'],'0' , 'class="width49"','', 'expMonth');
	$fldMon->html_after_field = ' ';
	$fldYear=$frm->addSelectBox('', '',$data['year_expire'],'0' , 'class="width49 marginLeft"','', 'expYear');
	$fldMon->attachField($fldYear);
	$fld=$frm->addRequiredField('<label>'.Utilities::getLabel('M_CVV_SECURITY_CODE').'</label>', '','','cvv','class="ccCvvBox"');
	$fld->html_after_field='<img src="'.CONF_WEBROOT_URL.'images/cvv.png"  alt=""/>';
	$frm->addSubmitButton('','btn_submit',Utilities::getLabel('M_CONFIRM_PAYMENT'),'button-confirm');
	$frm->setJsErrorDisplay('afterfield');
	return $frm;
}
/*private function getPaymentForm($order_id){
$pmObj=new Paymentsettings($this->key_name);
$payment_settings=$pmObj->getPaymentSettings();
$orderPaymentObj=new OrderPayment($order_id);
$payment_gateway_charge=$orderPaymentObj->getOrderPaymentGatewayAmount();
$order_info=$orderPaymentObj->getOrderPrimaryinfo();
$vanityUrl = $payment_settings['merchant_vanity_url'];
$currency ='INR';
$merchantTxnId = $order_id;
$orderAmount = $payment_gateway_charge;
$tmpdata = "$vanityUrl$orderAmount$merchantTxnId$currency";
$secSignature = hash_hmac('sha1', $tmpdata, $payment_settings['merchant_secret_key']);
if ($payment_settings["transaction_mode"]==1) {
$action_url = 'https://production.citruspay.com/sslperf/';
} elseif ($payment_settings["transaction_mode"]==0) {
$action_url = 'https://sandbox.citruspay.com/sslperf/';
}
$action_url = $action_url."$vanityUrl"; 
$frm=new Form('frm-citrus-payment','frm-citrus-payment');
$frm->setRequiredStarWith('x');
$frm->captionInSameCell(true);
$frm->setExtra('class="siteForm" validator="system_validator" ');
$frm->setAction($action_url);
$frm->setFieldsPerRow(1);
$frm->addHiddenField('', 'merchantTxnId', $order_id);
$frm->addHiddenField('', 'orderAmount', $payment_gateway_charge);
$frm->addHiddenField('', 'currency', "INR");
$frm->addHiddenField('', 'secSignature', $secSignature);
$frm->addHiddenField('', 'returnUrl', Utilities::generateAbsoluteUrl('citrus_pay','callback'));
$frm->addHiddenField('', 'email', $order_info["customer_email"]);
$frm->addHiddenField('', 'phoneNumber', $order_info["customer_phone"]);
$frm->addHiddenField('', 'addressState', $order_info["customer_billing_state"]);
$frm->addHiddenField('', 'addressCity', $order_info["customer_billing_city"]);
$frm->addHiddenField('', 'addressStreet1', $order_info["customer_billing_address_1"]);
$frm->addHiddenField('', 'addressStreet2', $order_info["customer_billing_address_2"]);
$frm->addHiddenField('', 'addressCountry', $order_info["customer_billing_country"]);
$frm->addHiddenField('', 'addressZip', $order_info["customer_billing_postcode"]);
$cust_name = explode(" ",$order_info["customer_name"]);
$frm->addHiddenField('', 'firstName', $cust_name[0]);
$frm->addHiddenField('', 'lastName', $cust_name[1]);
$frm->setJsErrorDisplay('afterfield');
return $frm;
}*/
function charge($order_id){
	$payment_settings = $this->getPaymentSettings();
	$orderPaymentObj=new OrderPayment($order_id);
	$payment_amount=$orderPaymentObj->getOrderPaymentGatewayAmount();
	$order_info=$orderPaymentObj->getOrderPrimaryinfo();
	if ($order_info && $order_info["order_payment_status"]==0){
		$frm=$this->getPaymentForm($order_id);
		$this->set('frm', $frm);
		$this->set('payment_amount', $payment_amount);
		$this->set('payment_type', $this->payment_type);
		if($this->payment_type!='HOSTED'){
			/***Adding here because we want these values in the js script**/
			$this->set('sellerId', $payment_settings['sellerId']);
			$this->set('publishableKey', $payment_settings['publishableKey']);
			if ($payment_settings["transaction_mode"]==1) {
				$this->set('transaction_mode', 'production');
			}else{
				$this->set('transaction_mode', 'sandbox');
			}
		}
	}else{
		$this->set('error', Utilities::getLabel('M_INVALID_ORDER_PAID_CANCELLED'));
	}
	$this->set('order_info', $order_info);
	$this->_template->render(true,false);	
}
/**
* Description: This method will be called when the payment type is HOSTED CHECKOUT i.e. $payment_type has HOSTED value. 
*/
public function callback(){
	$payment_settings = $this->getPaymentSettings();
	$request= getQueryStringData();		
$order_id = $request['li_0_product_id'];//in our case it is order id (hosted checkout case)
//$order_payment_amount = $request['total'];
$orderPaymentObj=new OrderPayment($order_id);
$order_payment_amount=$orderPaymentObj->getOrderPaymentGatewayAmount();
$hashSecretWord = $payment_settings['hashSecretWord']; //2Checkout Secret Word
$hashSid = $payment_settings['sellerId']; //2Checkout account number
$hashOrder = $request['order_number']; //2Checkout Order Number
$hashTotal = $order_payment_amount; //Sale total to validate against
$StringToHash = strtoupper(md5($hashSecretWord.$hashSid.$hashOrder.$hashTotal));
if ($StringToHash == $request['key']) {
	if($request['credit_card_processed']=='Y'){
		$message .= '2Checkout Order Number: '.$request['order_number']. "\n";
		$message .= '2Checkout Invoice Id: '.$request['invoice_id']. "\n";
		$message .= 'Merchant Order Id: '.$request['merchant_order_id']. "\n";
		$message .= 'Pay Method: '.$request['pay_method']. "\n";
		$message .= 'Description: '.$request['li_0_name']. "\n";
		$message .= 'Hash Match: '.'Keys matched'. "\n";
		/* Recording Payment in DB */
		$orderPaymentObj->addOrderPayment($payment_settings["pmethod_name"],$request['invoice_id'],$order_payment_amount,Utilities::getLabel("L_Received_Payment"),$message);
		/* End Recording Payment in DB */
		Utilities::redirectUser(Utilities::generateUrl('custom', 'payment_success'));
	}		
}
Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_ACCESS'));
Utilities::redirectUser(Utilities::generateUrl('custom', 'payment_failed'));
}
/**
* Description: This function will be called in case of Payment type is API CHECKOUT i.e. $payment_type = API.
*/
public function send($order_id){
	$payment_settings = $this->getPaymentSettings();
	$post = Syspage::getPostedVar();
	$orderPaymentObj=new OrderPayment($order_id);
	/* Retrieve Payment to charge corresponding to your order */
	$order_payment_amount=$orderPaymentObj->getOrderPaymentGatewayAmount();
	if ($order_payment_amount>0){
		/* Retrieve Primary Info corresponding to your order */
		$order_info=$orderPaymentObj->getOrderPrimaryinfo();
		$order_actual_paid = number_format(round($order_payment_amount,2),2,".","");
		$params = array(
			"merchantOrderId" => $order_id,
			"token" => $post['token'],
			"currency" => $order_info["order_currency_code"],
			"total" => $order_actual_paid,
			"billingAddr" => array(
				"name" => html_entity_decode($order_info['customer_name'], ENT_QUOTES, 'UTF-8'),
				"addrLine1" => html_entity_decode($order_info['customer_billing_address_1'], ENT_QUOTES, 'UTF-8').' '.html_entity_decode($order_info['customer_billing_address_2'], ENT_QUOTES, 'UTF-8'),
				"city" => html_entity_decode($order_info['customer_billing_city'], ENT_QUOTES, 'UTF-8'),
				"state" => html_entity_decode($order_info['customer_billing_state'], ENT_QUOTES, 'UTF-8'),
				"zipCode" => html_entity_decode($order_info['customer_billing_postcode'], ENT_QUOTES, 'UTF-8'),
				"country" => html_entity_decode($order_info['customer_billing_country'], ENT_QUOTES, 'UTF-8'),
				"email" => $order_info['customer_email'],
				"phoneNumber" => $order_info['customer_phone']
				),
			"shippingAddr" => array(
				"name" => html_entity_decode($order_info['customer_shipping_name'], ENT_QUOTES, 'UTF-8'),
				"addrLine1" => html_entity_decode($order_info['customer_shipping_address_1'], ENT_QUOTES, 'UTF-8') . ' ' . html_entity_decode($order_info['customer_shipping_address_2'], ENT_QUOTES, 'UTF-8'),
				"city" => html_entity_decode($order_info['customer_shipping_city'], ENT_QUOTES, 'UTF-8'),
				"state" => html_entity_decode($order_info['customer_shipping_state'], ENT_QUOTES, 'UTF-8'),
				"zipCode" => html_entity_decode($order_info['customer_shipping_postcode'], ENT_QUOTES, 'UTF-8'),
				"country" => html_entity_decode($order_info['customer_shipping_country'], ENT_QUOTES, 'UTF-8'),
				"email" => $order_info['customer_email'],
				"phoneNumber" => $order_info['customer_phone']
				)
			);
		if ($payment_settings["transaction_mode"]==1) {
			$url = 'https://www.2checkout.com/checkout/api/1/'.$payment_settings['sellerId'].'/rs/authService';
		} elseif ($payment_settings["transaction_mode"]==0) {
			$url = 'https://sandbox.2checkout.com/checkout/api/1/'.$payment_settings['sellerId'].'/rs/authService';
		}
		$params['sellerId'] = $payment_settings['sellerId'];
		$params['privateKey'] = $payment_settings['privateKey'];
		$curl = curl_init($url);
		$params = json_encode($params);
		$header = array("content-type:application/json","content-length:".strlen($params));
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");   
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);     
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curl, CURLOPT_USERAGENT, "2Checkout PHP/0.1.0%s");
		curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
		$result = curl_exec($curl);
		$json['redirect'] = Utilities::generateUrl('custom','payment_failed');
		$json = array();
		if (curl_error($curl)) {
			$json['error'] = 'CURL ERROR: ' . curl_errno($curl) . '::' . curl_error($curl);
		} elseif ($result) {
			$object = json_decode($result, true);
			$result_array=array();
			foreach($object as $member=>$data)
			{
				$result_array[$member]=$data;
			}
/**
"validationErrors": null,
"exception": {
"errorMsg": "Payment Authorization Failed:  Please verify your Credit Card details are entered correctly and try again, or try another payment method.",
"httpStatus": "400",
"exception": false,
"errorCode": "602"
},
"response": null
**/
$exception = $result_array['exception']; //must be null in case of successful orders
$response = $result_array['response']; 
$message = '';
if(!is_null($response)){
	$errors = $response['errors'];
$validationErrors = $response['validationErrors'];// '' or null
if(is_null($errors)){
$responseCode = $response['responseCode']; //APPROVED : Code indicating the result of the authorization attempt.
$responseMsg = $response['responseMsg'];//Message indicating the result of the authorization attempt.
$orderNumber = $response['orderNumber'];//2Checkout Order Number
$merchantOrderId = $response['merchantOrderId'];//must be equal to order id sent
$transactionId = $response['transactionId'];//2Checkout Invoice ID
$message .= 'Response Code: '.$responseCode. "\n";
$message .= 'Order Number: '.$orderNumber. "\n";
$message .= 'Merchant Order Id: '.$merchantOrderId. "\n";
$message .= 'Transaction Id: '.$transactionId. "\n";
$message .= 'Payment Method: 2Checkout API'. "\n";
$message .= 'Response Message: '.$responseMsg. "\n";
if($responseCode=='APPROVED'){
	/* Recording Payment in DB */
	$orderPaymentObj->addOrderPayment($payment_settings["pmethod_name"],$transactionId,$order_payment_amount,Utilities::getLabel("L_Received_Payment"),$message);
	$json['redirect'] = Utilities::generateUrl('custom','payment_success');
	/* End Recording Payment in DB */
}
}else{
	$json['error'] = $error;
}
}else{
	$json['error'] = $exception['errorMsg'];
}
} else {
	$json['error'] = Utilities::getLabel('M_EMPTY_GATEWAY_RESPONSE');
}
}else{
	$json['error'] = Utilities::getLabel('M_Invalid_Request');
}
curl_close($curl);
echo json_encode($json);
}
/*public function callback() {
$pmObj=new Paymentsettings($this->key_name);
$payment_settings=$pmObj->getPaymentSettings();
$post = Syspage::getPostedVar();
$order_id = (isset($post['TxId']))?$post['TxId']:0;
$orderPaymentObj=new OrderPayment($order_id);
$payment_gateway_charge=$orderPaymentObj->getOrderPaymentGatewayAmount();
foreach ($post as $key => $value) {
$request .= '&' . $key . '=' . urlencode(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
}
if ($payment_gateway_charge>0){
if (strtoupper($post['TxStatus']) == 'SUCCESS'){
//resp signature validation
$str=$post['TxId'].$post['TxStatus'].$post['amount'].$post['pgTxnNo'].$post['issuerRefNo'].$post['authIdCode'].$post['firstName'].$post['lastName'].$post['pgRespCode'].$post['addressZip'];
$respSig=$post['signature'];
if(hash_hmac('sha1', $str, $payment_settings['merchant_secret_key']) == $respSig){
$orderPaymentObj->addOrderPayment($payment_settings["pmethod_name"],$post['pgTxnNo'],$payment_gateway_charge,Utilities::getLabel("L_Received_Payment"),$request);
Utilities::redirectUser(Utilities::generateUrl('custom','payment_success'));
}else{
$request .= "\n\n Twocheckout :: Invalid or forged transactiond.  \n\n";
$orderPaymentObj->addOrderPaymentComments($request);
Utilities::redirectUser(Utilities::generateUrl('custom','payment_failed'));
}
}else{
$orderPaymentObj->addOrderPaymentComments($request);
Utilities::redirectUser(Utilities::generateUrl('custom','payment_failed'));
}
}else{
Utilities::show404();
}
}
*/
}
