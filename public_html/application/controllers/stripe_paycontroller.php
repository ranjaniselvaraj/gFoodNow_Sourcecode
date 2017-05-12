<?php
require_once(CONF_INSTALLATION_PATH . 'public/includes/payment-plugins/stripe/init.php');
class Stripe_payController extends PaymentController{
	private 
	$key_name="Stripe",
	$error = false,
	$payment_settings = false,
	$currency_code = 'usd';
	public function default_action(){
		echo "Unauthorized Action";
		Utilities::redirectUser(Utilities::generateUrl('/'));
	}
	private function getPaymentSettings(){
		$pmObj=new Paymentsettings($this->key_name);
		return $pmObj->getPaymentSettings();
	}
	private function getPaymentForm($order_id){
		$frm=new Form('frmPaymentForm','frmPaymentForm');
		$frm->setRequiredStarWith("x");
		$frm->setValidatorJsObjectName('system_validator');
		$frm->setExtra('class="siteForm" validator="system_validator" ');
		$frm->setAction(Utilities::generateUrl('stripe_pay','charge',array($order_id)));
		$frm->captionInSameCell(true);
		$frm->setFieldsPerRow(1);
		$frm->addRequiredField('<label>'.Utilities::getLabel('M_ENTER_CREDIT_CARD_NUMBER').'</label>', 'cc_number','','cc_number','class="type-bg" autocomplete="off"');
		$frm->addRequiredField('<label>'.Utilities::getLabel('M_CARD_HOLDER_NAME').'</label>', 'cc_owner', '', 'cc_owner', ' autocomplete="off"');
		$data['months'] = array();
		for ($i = 1; $i <= 12; $i++) {
			$data['months'][sprintf('%02d', $i)] = strftime('%B', mktime(0, 0, 0, $i, 1, 2000));
		}
		$today = getdate();
		$data['year_expire'] = array();
		for ($i = $today['year']; $i < $today['year'] + 11; $i++) {
			$data['year_expire'][strftime('%Y', mktime(0, 0, 0, 1, 1, $i))] = strftime('%Y', mktime(0, 0, 0, 1, 1, $i));
		}
		$fldMon=$frm->addSelectBox('<label>'.Utilities::getLabel('M_EXPIRY_DATE').'</label><div class="clear"></div>', 'cc_expire_date_month',$data['months'],'0' , 'class="width49"','', 'cc_expire_date_month');
		$fldMon->html_after_field = ' ';
		$fldYear=$frm->addSelectBox('', 'cc_expire_date_year',$data['year_expire'],'0' , 'class="width49 marginLeft"','', 'cc_expire_date_year');
		$fldMon->attachField($fldYear);
		$fld=$frm->addRequiredField('<label>'.Utilities::getLabel('M_CVV_SECURITY_CODE').'</label>', 'cc_cvv','','cc_cvv','class="ccCvvBox"  autocomplete="off"');
		$fld->html_after_field='<img src="'.CONF_WEBROOT_URL.'images/cvv.png"  alt=""/>';
		$frm->addSubmitButton('','btn_submit',Utilities::getLabel('M_CONFIRM_PAYMENT'),'button-confirm');
		$frm->setJsErrorDisplay('afterfield');
		return $frm;
	}
	function charge($order_id){
		$this->payment_settings = $this->getPaymentSettings();
		$stripe = array(
			'secret_key'      => $this->payment_settings['privateKey'],
			'publishable_key' => $this->payment_settings['publishableKey']
			);
		$this->set('stripe', $stripe);
		if( strlen(trim($this->payment_settings['privateKey'])) > 0 && strlen(trim($this->payment_settings['publishableKey'])) > 0 ){
			if( strpos($this->payment_settings['privateKey'], 'test') !== false || strpos($this->payment_settings['publishableKey'], 'test') !== false ){
//$this->error = Utilities::getLabel('M_TEST_MODE_ENABLED');
			}
			\Stripe\Stripe::setApiKey($stripe['secret_key']);
		} else {
			$this->error = Utilities::getLabel('STRIPE_INVALID_PAYMENT_GATEWAY_SETUP_ERROR');
		}
/*$orderObj = new Orders();
$payment_amount = $orderObj->getOrderPaymentGatewayAmount($order_id);
$payableAmount = $this->formatPayableAmount($payment_amount);
$order_info = $orderObj->getOrderPrimaryinfo($order_id);*/
$orderPaymentObj=new OrderPayment($order_id);
$payment_amount=$orderPaymentObj->getOrderPaymentGatewayAmount();
$payableAmount = $this->formatPayableAmount($payment_amount);
$order_info=$orderPaymentObj->getOrderPrimaryinfo();
if ( $order_info && $order_info["order_payment_status"] == 0 ) {
	$this->currency_code = strtolower($order_info["order_currency_code"]);
	$checkPayment = $this->doPayment($payableAmount, $order_id);
	$frm=$this->getPaymentForm($order_id);
	$this->set('frm', $frm);
	$this->set('payment_amount',  $payment_amount);
	if($checkPayment)
		$this->set('success', true);
} else {
	$this->error = Utilities::getLabel('M_INVALID_ORDER_PAID_CANCELLED');
}
$this->set('order_info', $order_info);
if($this->error)
	$this->set('error', $this->error);
$this->_template->render(true,false);
}
private function doPayment($payment_amount = null, $order_id = null){
	$pmObj=new Paymentsettings($this->key_name);
	$payment_settings=$pmObj->getPaymentSettings();
	if( $payment_amount == null || !$this->payment_settings || $order_id == null || intval($order_id) <= 0 ) return false;
	$checkPayment = false;
	if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
		try {
			if (!isset($_POST['stripeToken'])) throw new Exception("The Stripe Token was not generated correctly");
			else{
				$stripe = array(
					'secret_key'      => $this->payment_settings['privateKey'],
					'publishable_key' => $this->payment_settings['publishableKey']
					);
				if( strlen(trim($this->payment_settings['privateKey'])) > 0 && strlen(trim($this->payment_settings['publishableKey'])) > 0 ) \Stripe\Stripe::setApiKey($stripe['secret_key']);
				$charge = \Stripe\Charge::create(array(
					'source'     => $_POST['stripeToken'],
					'amount'   => $payment_amount,
					'currency' => $this->currency_code
					));
				$charge = $charge->__toArray();
				if(isset($charge['status'])){
					if(strtolower($charge['status']) == 'succeeded'){
						$message .= 'Id: '.(string)$charge['id']. "&";
						$message .= 'Object: '.(string)$charge['object']. "&";
						$message .= 'Amount: '.(string)$charge['amount']. "&";
						$message .= 'Amount Refunded: '.(string)$charge['amount_refunded']. "&";
						$message .= 'Application Fee: '.(string)$charge['application_fee']. "&";
						$message .= 'Balance Transaction: '.(string)$charge['balance_transaction']. "&";
						$message .= 'Captured: '.(string)$charge['captured']. "&";
						$message .= 'Created: '.(string)$charge['created']. "&";
						$message .= 'Currency: '.(string)$charge['currency']. "&";
						$message .= 'Customer: '.(string)$charge['customer']. "&";
						$message .= 'Description: '.(string)$charge['description']. "&";
						$message .= 'Destination: '.(string)$charge['destination']. "&";
						$message .= 'Dispute: '.(string)$charge['dispute']. "&";
						$message .= 'Failure Code: '.(string)$charge['failure_code']. "&";
						$message .= 'Failure Message: '.(string)$charge['failure_message']. "&";
						$message .= 'Invoice: '.(string)$charge['invoice']. "&";
						$message .= 'Livemode: '.(string)$charge['livemode']. "&";
						$message .= 'Paid: '.(string)$charge['paid']. "&";
						$message .= 'Receipt Email: '.(string)$charge['receipt_email']. "&";
						$message .= 'Receipt Number: '.(string)$charge['receipt_number']. "&";
						$message .= 'Refunded: '.(string)$charge['refunded']. "&";
						$message .= 'Shipping: '.(string)$charge['shipping']. "&";
						$message .= 'Statement Descriptor: '.(string)$charge['statement_descriptor']. "&";
						$message .= 'Status: '.(string)$charge['status']. "&";
						/* Recording Payment in DB */
						$orderPaymentObj=new OrderPayment($order_id);
						$orderPaymentObj->addOrderPayment($payment_settings["pmethod_name"],$charge['id'],($payment_amount/100),Utilities::getLabel("L_Received_Payment"),$message);
						/* End Recording Payment in DB */
						$checkPayment = true;
						Utilities::redirectUser(Utilities::generateUrl('custom', 'payment_success'));
					}else{
						$orderPaymentObj->addOrderPaymentComments($message);
						Utilities::redirectUser(Utilities::generateUrl('custom','payment_failed'));
					}
				}
			}
		} catch (Exception $e) {
			$this->error = $e->getMessage();
		}
	}
	return $checkPayment;
}
private function formatPayableAmount($amount = null){
	if($amount == null) return false;
	$amount = number_format($amount, 2, '.', '');
	return $amount*100;
}
}
