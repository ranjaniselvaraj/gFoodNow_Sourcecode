<?php
require_once(CONF_INSTALLATION_PATH . 'public/includes/payment-plugins/omise/lib/Omise.php');
/* define('OMISE_PUBLIC_KEY', 'pkey_test_5414mv04f4xeaf2sp09');
define('OMISE_SECRET_KEY', 'skey_test_5414mv04fqwvzmfsqno'); */
class omise_payController extends PaymentController{
	private $key_name="omise";
	public function __construct($model, $controller, $action){
		parent::__construct($model, $controller, $action);
		$pmObj=new Paymentsettings($this->key_name);
		if (!$payment_settings=$pmObj->getPaymentSettings()){
			Message::addErrorMessage($pmObj->getError());
			Utilities::redirectUserReferer();
		}
		if(!defined('OMISE_PUBLIC_KEY'))
			define('OMISE_PUBLIC_KEY', $payment_settings['public_key']);
		if(!defined('OMISE_SECRET_KEY'))
			define('OMISE_SECRET_KEY', $payment_settings['secret_key']);
	}
	private function getPaymentForm($order_id){
		$frm=new Form('frmPaymentForm','frmPaymentForm');
		$frm->setRequiredStarWith("x");
		$frm->setValidatorJsObjectName('system_validator');
		$frm->setExtra('class="siteForm" validator="system_validator" ');
		$frm->setAction(Utilities::generateUrl('omise_pay','send',array($order_id)));
		$frm->captionInSameCell(true);
		$frm->setFieldsPerRow(1);
		$frm->addRequiredField('<label>'.Utilities::getLabel('M_ENTER_CREDIT_CARD_NUMBER').'</label>', 'cc_number','','','class="type-bg"');
		$frm->addRequiredField('<label>'.Utilities::getLabel('M_CARD_HOLDER_NAME').'</label>', 'cc_owner');
		$data['months'] = array();
		for ($i = 1; $i <= 12; $i++) {
			$data['months'][sprintf('%02d', $i)] = strftime('%B', mktime(0, 0, 0, $i, 1, 2000));
		}
		$today = getdate();
		$data['year_expire'] = array();
		for ($i = $today['year']; $i < $today['year'] + 11; $i++) {
			$data['year_expire'][strftime('%Y', mktime(0, 0, 0, 1, 1, $i))] = strftime('%Y', mktime(0, 0, 0, 1, 1, $i));
		}
		$fldMon=$frm->addSelectBox('<label>'.Utilities::getLabel('M_EXPIRY_DATE').'</label><div class="clear"></div>', 'cc_expire_date_month',$data['months'],'0' , 'class="width49"','');
		$fldMon->html_after_field = ' ';
		$fldYear=$frm->addSelectBox('', 'cc_expire_date_year',$data['year_expire'],'0' , 'class="width49 marginLeft"','');
		$fldMon->attachField($fldYear);
		$fld=$frm->addRequiredField('<label>'.Utilities::getLabel('M_CVV_SECURITY_CODE').'</label>', 'cc_cvv','','cvv','class="ccCvvBox"');
		$fld->html_after_field='<img src="'.CONF_WEBROOT_URL.'images/cvv.png"  alt=""/>';
		$frm->addSubmitButton('','btn_submit',Utilities::getLabel('M_CONFIRM_PAYMENT'),'button-confirm');
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
	public function send($order_id) {
		$pmObj=new Paymentsettings($this->key_name);
		$payment_settings=$pmObj->getPaymentSettings();
		$post = Syspage::getPostedVar();
		$orderPaymentObj=new OrderPayment($order_id);
		/* Retrieve Payment to charge corresponding to your order */
		$order_payment_amount=$orderPaymentObj->getOrderPaymentGatewayAmount();
		if ($order_payment_amount>0){
			/* Retrieve Primary Info corresponding to your order */
			$order_info=$orderPaymentObj->getOrderPrimaryinfo();
			$order_actual_paid = ceil($order_payment_amount);
			$livemode = true;
			if ($payment_settings["transaction_mode"]=='0') {
				$livemode = false;
			}
			$json = array();
			try{
				$token = OmiseToken::create(array(
					'card' => array('name'              => html_entity_decode($order_info['customer_name'], ENT_QUOTES, 'UTF-8'),
						'number'            => str_replace(' ', '', $post['cc_number']),
						'expiration_month'  => $post['cc_expire_date_month'],
						'expiration_year'   => $post['cc_expire_date_year'],
						'city'              => html_entity_decode($order_info['customer_billing_city'], ENT_QUOTES, 'UTF-8'),
						'postal_code'       => html_entity_decode($order_info['customer_billing_postcode'], ENT_QUOTES, 'UTF-8'),
						'security_code'     => $post['cc_cvv'],
						'livemode'			=> $livemode
						)));
				$token_ref = $token->offsetGet('id');
				$customer = OmiseCustomer::create(array(
					'email' 		=> $order_info['customer_email'],
					'description' => $order_info['customer_name']. ' (id: '.$order_info['customer_id'].')',
					'card' 		=> $token_ref,
					'livemode'	=> $livemode
					));
				$response = OmiseCharge::create(array(
					'amount'      => $order_actual_paid,
'currency'    => 'thb',//$order_info["order_currency_code"],
//'currency'    => $order_info["order_currency_code"],
'description' => 'Order-'.$order_id,
'ip'          => $this->request->server['REMOTE_ADDR'],
'customer'    => $customer->offsetGet('id'),
// 'card'        => $token_ref,
'livemode'	  => $livemode
)); 
				if(!$response){
					throw new Exception(Utilities::getLabel('M_EMPTY_GATEWAY_RESPONSE'));
				}
				if (strtolower($response->offsetGet('status'))!='successful' || strtolower($response->offsetGet('paid')) != true) {
					throw new Excetpion($response->offsetGet('failure_message'));
				}
				$trans = OmiseTransaction::retrieve($response->offsetGet('transaction'));
				$omise_fee = round($order_actual_paid*('.0365'),0);
				$vat = round($omise_fee*('.07'),0);
				$trans_fee = intval($omise_fee + $vat);
				if ($trans->offsetGet('amount') != $order_actual_paid-$trans_fee) {
					throw new Exception(Utilities::getLabel('M_INVALID_TRANSACTION_AMOUNT'));
				}
				/* Recording Payment in DB */
				if (!$orderPaymentObj->addOrderPayment($payment_settings["pmethod_name"],$response->offsetGet('transaction'),$order_payment_amount,Utilities::getLabel("L_Received_Payment"),  json_encode((array)$response)))
					$json['error'] = "Invalid Action";
				/* End Recording Payment in DB */						
				$json['redirect'] = Utilities::generateUrl('custom','payment_success');				
			}
			catch(OmiseNotFoundException $e){
				$json['error'] = 'ERROR: ' . $e->getMessage();
			}
			catch(exception $e){
				$json['error'] = 'ERROR: ' . $e->getMessage();
			}
		}	
		else{
			$json['error'] = Utilities::getLabel('M_Invalid_Request');
		}
		echo json_encode($json);
	}
}
