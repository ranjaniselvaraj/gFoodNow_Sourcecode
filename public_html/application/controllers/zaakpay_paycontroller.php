<?php
require_once(CONF_INSTALLATION_PATH . 'public/includes/payment-plugins/zaakpay/checksum.php');
class Zaakpay_payController extends PaymentController{
	private $key_name="Zaakpay";
	private function getPaymentForm($order_id){
		$pmObj=new Paymentsettings($this->key_name);
		$payment_settings=$pmObj->getPaymentSettings();
		$orderPaymentObj=new OrderPayment($order_id);
		$payment_gateway_charge=$orderPaymentObj->getOrderPaymentGatewayAmount();
		$order_info=$orderPaymentObj->getOrderPrimaryinfo();
		$order_payment_gateway_description=sprintf(Utilities::getLabel('M_Order_Payment_Gateway_Description'),$order_info["site_system_name"],$order_info['invoice']);		
		$frm=new Form('frmZaakpay','frmZaakpay');
		$frm->setRequiredStarWith('x');
		$frm->captionInSameCell(true);
		$frm->setExtra('class="siteForm" validator="system_validator" ');
		$frm->setAction(Utilities::generateAbsoluteUrl('zaakpay_pay','checksum'));
		$frm->setFieldsPerRow(1);
		$cust_name = explode(" ",$order_info["customer_name"]);
		$cust_shipping_name = explode(" ",$order_info["customer_shipping_name"]);
		$parameters = array(
			"merchantIdentifier" => $payment_settings["merchant_identifier"],
			"orderId"  => $order_id,
			"returnUrl" => Utilities::generateAbsoluteUrl('zaakpay_pay','callback'),               
			"buyerEmail" => $order_info['customer_email'],
			"buyerFirstName" => $cust_name[0],
			"buyerLastName" => empty($cust_name[1])?$cust_name[0]:$cust_name[1],
			"buyerAddress" => $order_info["customer_billing_address_1"].', '.$order_info["customer_billing_address_2"],
			"buyerCity" => $order_info["customer_billing_city"],
			"buyerState" => $order_info["customer_billing_state"],
			"buyerCountry" => $order_info["customer_billing_country"],
			"buyerPincode" => $order_info["customer_billing_postcode"],
			"buyerPhoneNumber" => $order_info["customer_phone"],
			"txnType" => 1,
			"zpPayOption" => 1,
			"mode"=> 0,
			"currency" => "INR",
			"amount" => $payment_gateway_charge*100,
			"merchantIpAddress" => $_SERVER['REMOTE_ADDR'],
			"purpose" => 1,
			"productDescription" => $order_payment_gateway_description,
			"txnDate" => date('Y-m-d'),
			"shipToAddress" => $order_info["customer_shipping_address_1"].', '.$order_info["customer_shipping_address_2"],
			"shipToCity" => $order_info["customer_shipping_city"],
			"shipToState" => $order_info["customer_shipping_state"],
			"shipToCountry" => $order_info["customer_shipping_country"],
			"shipToPincode" => $order_info["customer_shipping_postcode"],
			"shipToPhoneNumber" => $order_info["customer_shipping_phone"],
			"shipToFirstname" => $cust_shipping_name[0],
			);
		foreach ($parameters as $paramkey=>$paramval){
			$frm->addHiddenField($paramkey, $paramkey, $paramval,paramkey);
		}
		$frm->addSubmitButton('','btn_submit',Utilities::getLabel('M_CONFIRM_PAYMENT'),'button-confirm');
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
	function checksum(){
		$pmObj=new Paymentsettings($this->key_name);
		$payment_settings=$pmObj->getPaymentSettings();
		$secret = $payment_settings['merchant_key'];
		$all = Checksum::getAllParams();
		$checksum = Checksum::calculateChecksum($secret, $all);
		$this->set('checksum', $checksum);
		$this->_template->render(true,false);
	}
	public function callback() {
		$pmObj=new Paymentsettings($this->key_name);
		$payment_settings=$pmObj->getPaymentSettings();
		$post = Syspage::getPostedVar();
		foreach ($post as $key => $value) {
			$request .= '&' . $key . '=' . urlencode(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
		}
		die($request);
	}
}
