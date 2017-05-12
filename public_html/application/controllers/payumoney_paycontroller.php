<?php
class PayuMoney_payController extends PaymentController{
	private $key_name="PayuMoney";
	private function getPaymentForm($order_id){
		$pmObj=new Paymentsettings($this->key_name);
		$payment_settings=$pmObj->getPaymentSettings();
		$orderPaymentObj=new OrderPayment($order_id);
		$payment_gateway_charge=$orderPaymentObj->getOrderPaymentGatewayAmount();
		if ($payment_settings["transaction_mode"]==1) {
			$action_url = 'https://secure.payu.in/_payment.php';
		} elseif ($payment_settings["transaction_mode"]==0) {
			$action_url = 'https://test.payu.in/_payment.php';
		}
		$frm=new Form('frmPayuMoney','frmPayuMoney');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('x');
		$frm->setExtra('class="siteForm" validator="system_validator" ');
		$frm->setAction($action_url);
		$frm->setFieldsPerRow(1);
		/* Retrieve Primary Info corresponding to your order */
		$order_info=$orderPaymentObj->getOrderPrimaryinfo();
		$firstname=$order_info["customer_name"];
		$phone_number=$order_info["customer_phone"];
		$address_line_1=$order_info["customer_billing_address_1"];
		$address_line_2=$order_info["customer_billing_address_2"];
		$zip_code=$order_info["customer_billing_postcode"];
		$email=$order_info["customer_email"];
		$order_payment_gateway_description=sprintf(Utilities::getLabel('M_Order_Payment_Gateway_Description'),$order_info["site_system_name"],$order_info['invoice']);
		$txnid=$order_info["invoice"];
		$frm->addHiddenField('key', 'key', $payment_settings["merchant_key"]);
		$frm->addHiddenField('txnid', 'txnid',$txnid);
		$frm->addHiddenField('amount', 'amount', $payment_gateway_charge);
		$frm->addHiddenField('productinfo', 'productinfo',$order_payment_gateway_description );
		$frm->addHiddenField('firstname', 'firstname', $firstname);
		$frm->addHiddenField('Lastname', 'Lastname',$lastname);
		$frm->addHiddenField('Zipcode', 'Zipcode', $zip_code);
		$frm->addHiddenField('email', 'email', $email);
		$frm->addHiddenField('phone', 'phone', $phone_number);
		$frm->addHiddenField('surl', 'surl', Utilities::generateAbsoluteUrl('payumoney_pay','callback'));
		$frm->addHiddenField('furl', 'furl', Utilities::generateAbsoluteUrl('payumoney_pay','callback'));
		$frm->addHiddenField('curl', 'curl', Utilities::generateAbsoluteUrl('cart','checkout'));
		$key          =  $payment_settings["merchant_key"];
		$amount       = $payment_gateway_charge;
		$salt         = $payment_settings["salt"];
		$udf1 		  = $order_id;
		$Hash=hash('sha512', $key.'|'.$txnid.'|'.$payment_gateway_charge.'|'.$order_payment_gateway_description.'|'.$firstname.'|'.$email.'|'.$udf1.'||||||||||'.$salt); 
		$frm->addHiddenField('hash', 'hash', $Hash);
		$frm->addHiddenField('udf1', 'udf1', $udf1);
		$frm->addHiddenField('Pg', 'Pg', 'CC');
		$frm->addHiddenField('address1', 'address1', $address_line_1);
		$frm->addHiddenField('address2', 'address2', $address_line_2);
		$frm->addHiddenField('city', 'city', $order_info["customer_billing_city"]);
		$frm->addHiddenField('country', 'country', $order_info["customer_billing_country"]);
		$frm->addHiddenField('state', 'state', $order_info["customer_billing_state"]);
		$frm->addHiddenField('custom_note', 'custom_note', Utilities::getLabel('M_ORDER_CUSTOM_NOTE'));
		$frm->addHiddenField('api_version', 'api_version', 1);
		$frm->addHiddenField('service_provider', 'service_provider', 'payu_paisa');
		$frm->setJsErrorDisplay('afterfield');
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
	public function callback(){
		$pmObj=new Paymentsettings($this->key_name);
		$payment_settings=$pmObj->getPaymentSettings();
		$post = Syspage::getPostedVar();
		foreach ($post as $key => $value) {
			$request .= '&' . $key . '=' . urlencode(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
		}
		$order_id = (isset($post['udf1']))?$post['udf1']:0;
		$orderPaymentObj=new OrderPayment($order_id);
		$payment_gateway_charge=$orderPaymentObj->getOrderPaymentGatewayAmount();
		$order_info=$orderPaymentObj->getOrderPrimaryinfo();
		if ($order_info){
			$order_payment_gateway_description=sprintf(Utilities::getLabel('M_Order_Payment_Gateway_Description'),$order_info["site_system_name"],$order_info['invoice']);
			switch($post['status']) {
				case 'success':
				$receiver_match = (strtolower($post['key']) == strtolower($payment_settings['merchant_key']));
				$total_paid_match = ((float)$post['amount'] == (float)$payment_gateway_charge);
				$hash_string = $payment_settings["salt"]."|".$post["status"]."||||||||||".$post["udf1"]."|".$post["email"]."|".$post["firstname"]."|".$post["productinfo"]."|".$post["amount"]."|".$post["txnid"]."|".$post["key"];
				$reverse_hash=strtolower(hash('sha512',$hash_string)); 
				$reverse_hash_match=($post['hash'] == $reverse_hash);
				if ($receiver_match && $total_paid_match && $reverse_hash_match) {
					$order_payment_status = 1;
				}
				if (!$receiver_match) {
					$request .= "\n\n PAYUMONEY_NOTE :: RECEIVER MERCHANT MISMATCH! " . strtolower($post['key']) . "\n\n";
				}
				if (!$total_paid_match) {
					$request .= "\n\n PAYUMONEY_NOTE :: TOTAL PAID MISMATCH! " . strtolower($post['amount']) . "\n\n";
				}
				if (!$reverse_hash_match) {
					$request .= "\n\n PAYUMONEY_NOTE :: REVERSE HASH MISMATCH! " . strtolower($post['hash']) . "\n\n";
				}
				break;
			}
			if ($order_payment_status==1){
				$orderPaymentObj->addOrderPayment($payment_settings["pmethod_name"],$post["mihpayid"],$payment_gateway_charge,Utilities::getLabel("L_Received_Payment"),$request);
				Utilities::redirectUser(Utilities::generateUrl('custom', 'payment_success'));
			}else{
				$orderPaymentObj->addOrderPaymentComments($request);
				Utilities::redirectUser(Utilities::generateUrl('custom', 'payment_failed'));
			}
		}else{
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_ACCESS'));
			Utilities::redirectUser(Utilities::generateUrl('custom', 'payment_failed'));
		}
	}
}
