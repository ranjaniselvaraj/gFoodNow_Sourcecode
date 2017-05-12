<?php
class Citrus_payController extends PaymentController{
	private $key_name="Citrus";
	private function getPaymentForm($order_id){
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
	public function callback() {
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
					$request .= "\n\n Citrus :: Invalid or forged transactiond.  \n\n";
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
}
