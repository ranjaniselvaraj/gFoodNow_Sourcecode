<?php
require_once(CONF_INSTALLATION_PATH . 'public/includes/payment-plugins/paytm/encdec_paytm.php');
class Paytm_payController extends PaymentController{
	private $key_name="Paytm";
	private function getPaymentForm($order_id){
		$pmObj=new Paymentsettings($this->key_name);
		$payment_settings=$pmObj->getPaymentSettings();
		$orderPaymentObj=new OrderPayment($order_id);
		$payment_gateway_charge=$orderPaymentObj->getOrderPaymentGatewayAmount();
		$order_info=$orderPaymentObj->getOrderPrimaryinfo();
		if ($payment_settings["transaction_mode"]==1) {
			$action_url = "https://secure.paytm.in/oltp-web/processTransaction";
		} elseif ($payment_settings["transaction_mode"]==0) {
			$action_url = "https://pguat.paytm.com/oltp-web/processTransaction";
		}
		$order_payment_gateway_description=sprintf(Utilities::getLabel('M_Order_Payment_Gateway_Description'),$order_info["site_system_name"],$order_info['invoice']);		
		$frm=new Form('frmPaytm','frmPaytm');
		$frm->setRequiredStarWith('x');
		$frm->captionInSameCell(true);
		$frm->setExtra('class="siteForm" validator="system_validator" ');
		$frm->setAction($action_url);
		$frm->setFieldsPerRow(1);
		$parameters = array(
			"MID" => $payment_settings["merchant_id"],
			"ORDER_ID"  => date("ymdhis")."_".$order_id,               
			"CUST_ID" => $order_info['customer_id'],
			"TXN_AMOUNT" => $payment_gateway_charge,
			"CHANNEL_ID" => $payment_settings['merchant_channel_id'],
			"INDUSTRY_TYPE_ID" => $payment_settings['merchant_industry_type'],
			"WEBSITE" => $payment_settings['merchant_website'],
			"MOBILE_NO" => $order_info['customer_phone'],
			"EMAIL" => $order_info['customer_email'],
			"CALLBACK_URL" => Utilities::generateAbsoluteUrl('paytm_pay','callback'),
			"ORDER_DETAILS" => $order_payment_gateway_description,
			);
		$checkSumHash = getChecksumFromArray($parameters, $payment_settings['merchant_key']);
		$frm->addHiddenField('', 'CHECKSUMHASH',$checkSumHash);
		foreach ($parameters as $paramkey=>$paramval){
			$frm->addHiddenField('', $paramkey, $paramval);
		}
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
		foreach ($post as $key => $value) {
			$request .= '&' . $key . '=' . urlencode(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
		}
		$isValidChecksum = FALSE;
	$paytmChecksum = isset($post["CHECKSUMHASH"]) ? $post["CHECKSUMHASH"] : ""; //Sent by Paytm pg
	$isValidChecksum = verifychecksum_e($post, $payment_settings['merchant_key'], $paytmChecksum); //will return TRUE or FALSE string.
	/*die($request."#".$isValidChecksum);*/
	$arr_order= explode("_",$post['ORDERID']);
	$order_id = (!empty($arr_order[1]))?$arr_order[1]:0;
	$orderPaymentObj=new OrderPayment($order_id);
	$payment_gateway_charge=$orderPaymentObj->getOrderPaymentGatewayAmount();
	if ($payment_gateway_charge>0){
	if ($isValidChecksum){
		$total_paid_match = ((float)$post['TXNAMOUNT'] == $payment_gateway_charge);
		if (!$total_paid_match) {
			$request .= "\n\n Paytm :: TOTAL PAID MISMATCH! " . strtolower($paid_amount) . "\n\n";
		}
		if ($post['STATUS']=="TXN_SUCCESS" && $total_paid_match){
			$orderPaymentObj->addOrderPayment($payment_settings["pmethod_name"],$post['TXNID'],$payment_gateway_charge,Utilities::getLabel("L_Received_Payment"),$request);
			Utilities::redirectUser(Utilities::generateUrl('custom','payment_success'));
		}else{
			$orderPaymentObj->addOrderPaymentComments($request);
			Utilities::redirectUser(Utilities::generateUrl('custom','payment_failed'));
		}
	}else{
		Utilities::redirectUser(Utilities::generateUrl('custom','payment_failed'));
	}
}else{
	Utilities::show404();
}
}
}
