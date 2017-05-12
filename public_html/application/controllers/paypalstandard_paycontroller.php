<?php
class PaypalStandard_payController extends PaymentController{
	private $key_name="PaypalStandard";
	public function recurringPayments() {
		/** Used by the checkout to state the module * supports recurring. */
	return true;
	}
private function getPaymentForm($order_id){
	$pmObj=new Paymentsettings($this->key_name);
	$payment_settings=$pmObj->getPaymentSettings();
	$orderPaymentObj=new OrderPayment($order_id);
	$payment_gateway_charge=$orderPaymentObj->getOrderPaymentGatewayAmount();
	$order_info=$orderPaymentObj->getOrderPrimaryinfo();
	if ($payment_settings["transaction_mode"]==1) {
		$action_url = 'https://www.paypal.com/cgi-bin/webscr';
	} elseif ($payment_settings["transaction_mode"]==0) {
		$action_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
	}
	$frm=new Form('frmPayPalStandard','frmPayPalStandard');
	$frm->setRequiredStarWith('x');
	$frm->captionInSameCell(true);
	$frm->setExtra('class="siteForm" validator="system_validator" ');
	$frm->setAction($action_url);
	$frm->setFieldsPerRow(1);
	$frm->addHiddenField('', 'cmd', "_cart");
	$frm->addHiddenField('', 'upload', "1");
	$frm->addHiddenField('', 'business', $payment_settings["merchant_email"]);
	$order_payment_gateway_description=sprintf(Utilities::getLabel('M_Order_Payment_Gateway_Description'),$order_info["site_system_name"],$order_info['invoice']);
	$frm->addHiddenField('', 'item_name_1', $order_payment_gateway_description);
	$frm->addHiddenField('', 'item_number_1', $order_info['invoice']);
	$frm->addHiddenField('', 'amount_1', $payment_gateway_charge);
	$frm->addHiddenField('', 'quantity_1', 1);
	$frm->addHiddenField('', 'currency_code', $order_info["order_currency_code"]);
	$frm->addHiddenField('', 'first_name', $order_info["customer_name"]);
	$frm->addHiddenField('', 'address1', $order_info["customer_billing_address_1"]);
	$frm->addHiddenField('', 'address2', $order_info["customer_billing_address_2"]);
	$frm->addHiddenField('', 'city', $order_info["customer_billing_city"]);
	$frm->addHiddenField('', 'zip', $order_info["customer_billing_postcode"]);
	$frm->addHiddenField('', 'country', $order_info["customer_billing_country"]);
	$frm->addHiddenField('', 'address_override', 0);
	$frm->addHiddenField('', 'email', $order_info['customer_email']);
	$frm->addHiddenField('', 'invoice', $order_info['invoice']);
	$frm->addHiddenField('', 'lc', $order_info['order_language']);
	$frm->addHiddenField('', 'rm', 2);
	$frm->addHiddenField('', 'no_note', 1);
	$frm->addHiddenField('', 'no_shipping', 1);
	$frm->addHiddenField('', 'charset', "utf-8");
	$frm->addHiddenField('', 'return', Utilities::generateAbsoluteUrl('custom','payment_success'));
	$frm->addHiddenField('', 'notify_url', Utilities::generateAbsoluteUrl('paypalstandard_pay','callback'));
	$frm->addHiddenField('', 'cancel_return', Utilities::generateAbsoluteUrl('custom','payment_failed'));
	$frm->addHiddenField('', 'paymentaction', 'sale');  // authorization or sale
	$frm->addHiddenField('', 'custom', $order_id);
	$frm->addHiddenField('', 'bn', $order_info["paypal_bn"]);
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
	$order_id = (isset($post['custom']))?$post['custom']:0;
	$orderPaymentObj=new OrderPayment($order_id);
	$payment_gateway_charge=$orderPaymentObj->getOrderPaymentGatewayAmount();
	if ($payment_gateway_charge>0){
		$request = 'cmd=_notify-validate';
		foreach ($post as $key => $value) {
			$request .= '&' . $key . '=' . urlencode(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
		}
		if ($payment_settings["transaction_mode"]==1) {
			$curl = curl_init('https://www.paypal.com/cgi-bin/webscr');
		} elseif ($payment_settings["transaction_mode"]==0) {
			$curl = curl_init('https://www.sandbox.paypal.com/cgi-bin/webscr');
		}
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($curl);
		if ((strcmp($response, 'VERIFIED') == 0 || strcmp($response, 'UNVERIFIED') == 0) && isset($post['payment_status'])) {
			$order_payment_status = $payment_settings['order_status_initial'];
			switch($post['payment_status']) {
				case 'Pending':
				$order_payment_status = $payment_settings['order_status_pending'];
				break;
				case 'Processed':
				$order_payment_status = $payment_settings['order_status_processed'];
				break;
				case 'Completed':
				$order_payment_status = $payment_settings['order_status_completed'];
				break;
				default:
				$order_payment_status = $payment_settings['order_status_others'];
				break;
			}
			$receiver_match = (strtolower($post['receiver_email']) == strtolower($payment_settings['merchant_email']));
			$total_paid_match = ((float)$post['mc_gross'] == $payment_gateway_charge);
			if (!$receiver_match) {
				$request .= "\n\n PP_STANDARD :: RECEIVER EMAIL MISMATCH! " . strtolower($post['receiver_email']) . "\n\n";
			}
			if (!$total_paid_match) {
				$request .= "\n\n PP_STANDARD :: TOTAL PAID MISMATCH! " . strtolower($post['mc_gross']) . "\n\n";
			}
			if ($order_payment_status==1 && $receiver_match && $total_paid_match){
				$orderPaymentObj->addOrderPayment($payment_settings["pmethod_name"],$post["txn_id"],$payment_gateway_charge,Utilities::getLabel("L_Received_Payment"),$request."#".$response);
			}else{
				$orderPaymentObj->addOrderPaymentComments($request);
			}
		}
		curl_close($curl);
	}
}
private function getWalletPaymentForm($recharge_txn_id){
	$pmObj=new PPCPaymentsettings($this->key_name);
	$payment_settings=$pmObj->getPaymentSettings();
	$wrObj=new WalletRecharge($recharge_txn_id);
	$payment_gateway_charge=$wrObj->getPaymentGatewayAmount();
	$recharge_txn_info=$wrObj->getWalletRechargePrimaryinfo();
	if ($payment_settings["transaction_mode"]==1) {
		$action_url = 'https://www.paypal.com/cgi-bin/webscr';
	} elseif ($payment_settings["transaction_mode"]==0) {
		$action_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
	}
	$frm=new Form('frmPayPalStandard','frmPayPalStandard');
	$frm->setRequiredStarWith('x');
	$frm->captionInSameCell(true);
	$frm->setExtra('class="siteForm" validator="system_validator" ');
	$frm->setAction($action_url);
	$frm->setFieldsPerRow(1);
	$frm->addHiddenField('', 'cmd', "_xclick");
//$frm->addHiddenField('', 'upload', "1");
	$frm->addHiddenField('', 'business', $payment_settings["merchant_email"]);
	$order_payment_gateway_description=sprintf(Utilities::getLabel('M_Wallet_Recharge_Payment_Gateway_Description'),$recharge_txn_info["system_name"],$recharge_txn_info['invoice']);
	$frm->addHiddenField('', 'item_name', $order_payment_gateway_description);
// $frm->addHiddenField('', 'item_name_1', $order_payment_gateway_description);
	$frm->addHiddenField('', 'item_number', $recharge_txn_info['invoice']);
	$frm->addHiddenField('', 'amount', $payment_gateway_charge);
//$frm->addHiddenField('', 'quantity_1', 0);
	$frm->addHiddenField('', 'currency_code', $recharge_txn_info["currency"]);
	$frm->addHiddenField('', 'first_name', $recharge_txn_info["name"]);
	$frm->addHiddenField('', 'email', $recharge_txn_info['email']);
	$frm->addHiddenField('', 'invoice', $recharge_txn_info['invoice']);
	$frm->addHiddenField('', 'lc', 'en');
	$frm->addHiddenField('', 'rm', 2);
	$frm->addHiddenField('', 'no_note', 1);
	$frm->addHiddenField('', 'no_shipping', 1);
	$frm->addHiddenField('', 'charset', "utf-8");
	$frm->addHiddenField('', 'return', Utilities::generateAbsoluteUrl('account','payment_success'));
	$frm->addHiddenField('', 'notify_url', Utilities::generateAbsoluteUrl('paypalstandard_pay','wallet_callback'));
	$frm->addHiddenField('', 'cancel_return', Utilities::generateAbsoluteUrl('account','payment_failed'));
$frm->addHiddenField('', 'paymentaction', 'sale');  // authorization or sale
$frm->addHiddenField('', 'custom', $recharge_txn_id);
$frm->addHiddenField('', 'bn', $recharge_txn_info["paypal_bn"]);
$frm->setJsErrorDisplay('afterfield');
return $frm;
}
function charge_for_wallet($recharge_txn_id){
	$pmObj=new PPCPaymentsettings($this->key_name);
	if (!$payment_settings=$pmObj->getPaymentSettings()){
		Message::addErrorMessage($pmObj->getError());
		Utilities::redirectUserReferer();
	}
	$wrObj=new WalletRecharge($recharge_txn_id);
	$payment_amount=$wrObj->getPaymentGatewayAmount();
	$recharge_txn_info=$wrObj->getWalletRechargePrimaryinfo();
	if ($recharge_txn_info && $recharge_txn_info["payment_status"]==0){
		$frm=$this->getWalletPaymentForm($recharge_txn_id);
		$this->set('frm', $frm);
		$this->set('payment_amount', $payment_amount);
	}else{
		$this->set('error', Utilities::getLabel('M_INVALID_ORDER_PAID_CANCELLED'));
	}
	$this->set('recharge_txn_info', $recharge_txn_info);
	$this->_template->render(true,false);	
}
public function wallet_callback() {
	$pmObj=new PPCPaymentsettings($this->key_name);
	$payment_settings=$pmObj->getPaymentSettings();
	$post = Syspage::getPostedVar();
	$recharge_txn_id = (isset($post['custom']))?$post['custom']:0;
	$wrObj=new WalletRecharge($recharge_txn_id);
	$payment_gateway_charge=$wrObj->getPaymentGatewayAmount();
	if ($payment_gateway_charge>0){
		$request = 'cmd=_notify-validate';
		foreach ($post as $key => $value) {
			$request .= '&' . $key . '=' . urlencode(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
		}
		if ($payment_settings["transaction_mode"]==1) {
			$curl = curl_init('https://www.paypal.com/cgi-bin/webscr');
		} elseif ($payment_settings["transaction_mode"]==0) {
			$curl = curl_init('https://www.sandbox.paypal.com/cgi-bin/webscr');
		}
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($curl);
		if ((strcmp($response, 'VERIFIED') == 0 || strcmp($response, 'UNVERIFIED') == 0) && isset($post['payment_status'])) {
			$order_payment_status = $payment_settings['order_status_initial'];
			switch($post['payment_status']) {
				case 'Pending':
				$order_payment_status = $payment_settings['txn_status_pending'];
				break;
				case 'Processed':
				$order_payment_status = $payment_settings['txn_status_processed'];
				break;
				case 'Completed':
				$order_payment_status = $payment_settings['txn_status_completed'];
				break;
				default:
				$order_payment_status = $payment_settings['txn_status_others'];
				break;
			}
			$receiver_match = (strtolower($post['receiver_email']) == strtolower($payment_settings['merchant_email']));
			$total_paid_match = ((float)$post['mc_gross'] == $payment_gateway_charge);
			if (!$receiver_match) {
				$request .= "\n\n PP_STANDARD :: RECEIVER EMAIL MISMATCH! " . strtolower($post['receiver_email']) . "\n\n";
			}
			if (!$total_paid_match) {
				$request .= "\n\n PP_STANDARD :: TOTAL PAID MISMATCH! " . strtolower($post['mc_gross']) . "\n\n";
			}
			if ($order_payment_status==1 && $receiver_match && $total_paid_match){
				$wrObj->markWalletRechargeRequestPaid($payment_settings["ppcpmethod_name"],$post["txn_id"],$request."#".$response);
			}			
		}
		curl_close($curl);
	}
}
private function getSubscriptionPaymentForm($order_id){
	$pmObj=new SubscriptionPaymentsettings($this->key_name);
	$payment_settings=$pmObj->getPaymentSettings();
	$orderPaymentObj=new SubscriptionOrderPayment($order_id);
	$payment_gateway_charge=$orderPaymentObj->getOrderPaymentGatewayAmount();
	$recurring_payment_gateway_charge=$orderPaymentObj->getRecurringOrderPaymentGatewayAmount();
	$order_info=$orderPaymentObj->getOrderPrimaryinfo();
	if ($payment_settings["transaction_mode"]==1) {
		$action_url = 'https://www.paypal.com/cgi-bin/webscr';
	} elseif ($payment_settings["transaction_mode"]==0) {
		$action_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
	}
	$frm=new Form('frmPayPalStandard','frmPayPalStandard');
	$frm->setRequiredStarWith('x');
	$frm->captionInSameCell(true);
	$frm->setExtra('class="siteForm" validator="system_validator" ');
	$frm->setAction($action_url);
	$frm->setFieldsPerRow(1);
	$frm->addHiddenField('', 'cmd', "_xclick-subscriptions");
	$frm->addHiddenField('', 'upload', "1");
	$frm->addHiddenField('', 'business', $payment_settings["merchant_email"]);
	$order_payment_gateway_description = substr(strip_tags($order_info['mporder_merchantpack_desc']), 0, 126);
	$frm->addHiddenField('', 'item_name', $order_payment_gateway_description);
	if ($payment_gateway_charge!=$recurring_payment_gateway_charge){
		$frm->addHiddenField('', 'a1', $payment_gateway_charge);
		$frm->addHiddenField('', 'p1', $order_info['mporder_recurring_billing_cycle_frequency']);
		$frm->addHiddenField('', 't1', $order_info['mporder_recurring_billing_cycle_period']);
	}
	/* Start Free Trial Period */
	/*$frm->addHiddenField('', 'a1', 0);
	$frm->addHiddenField('', 'p1', 5);
	$frm->addHiddenField('', 't1', 'D');*/
	/* End Free Trial Period */
	$frm->addHiddenField('', 'a3', $recurring_payment_gateway_charge);
	$frm->addHiddenField('', 'p3', $order_info['mporder_recurring_billing_cycle_frequency']);
	$frm->addHiddenField('', 't3', $order_info['mporder_recurring_billing_cycle_period']);
	$frm->addHiddenField('', 'src', 1);
	if ($order_info['mporder_recurring_billing_cycle']!='9999')
	$frm->addHiddenField('', 'srt', min(array($order_info['mporder_recurring_billing_cycle'],52)));
	$frm->addHiddenField('', 'no_note', 1);
	$frm->addHiddenField('', 'invoice', $order_info['invoice']);
	$frm->addHiddenField('', 'modify', 0);
	$frm->addHiddenField('', 'currency_code', $order_info["mporder_currency_code"]);
	$frm->addHiddenField('', 'first_name', $order_info["customer_name"]);
	$frm->addHiddenField('', 'email', $order_info['customer_email']);
	$frm->addHiddenField('', 'lc', $order_info['order_language']);
	$frm->addHiddenField('', 'rm', 2);
	$frm->addHiddenField('', 'sra', 1);
	$frm->addHiddenField('', 'charset', "utf-8");
	$frm->addHiddenField('', 'return', Utilities::generateAbsoluteUrl('custom','package_payment_success'));
	$frm->addHiddenField('', 'notify_url', Utilities::generateAbsoluteUrl('paypalstandard_pay','package_callback'));
	$frm->addHiddenField('', 'cancel_return', Utilities::generateAbsoluteUrl('custom','package_payment_failed'));
	$frm->addHiddenField('', 'custom', $order_id);
	$frm->addHiddenField('', 'bn', $order_info["paypal_bn"]);
	$frm->setJsErrorDisplay('afterfield');
	return $frm;
	}
function package_charge($order_id){
	$pmObj=new SubscriptionPaymentsettings($this->key_name);
	if (!$payment_settings=$pmObj->getPaymentSettings()){
		Message::addErrorMessage($pmObj->getError());
		Utilities::redirectUserReferer();
	}
	$orderPaymentObj=new SubscriptionOrderPayment($order_id);
	$payment_amount=$orderPaymentObj->getOrderPaymentGatewayAmount();
	$order_info=$orderPaymentObj->getOrderPrimaryinfo();
	if ($order_info && $order_info["order_payment_status"]==0){
		$frm=$this->getSubscriptionPaymentForm($order_id);
		$this->set('frm', $frm);
		$this->set('payment_amount', $payment_amount);
	}else{
		$this->set('error', Utilities::getLabel('M_INVALID_ORDER_PAID_CANCELLED'));
	}
	$this->set('order_info', $order_info);
	$this->_template->render(true,false);	
}
public function package_callback() {
	$post = Syspage::getPostedVar();
	foreach ($post as $key => $value) {
		$request .= '&' . $key . '=' . urlencode(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
	}	
	mail('ravibhalla@dummyid.com','PayPal Subscription IPN',$request);
		
	$pmObj=new SubscriptionPaymentsettings($this->key_name);
	$payment_settings=$pmObj->getPaymentSettings();
	$post = Syspage::getPostedVar();
	$order_id = (isset($post['custom']))?$post['custom']:0;
	$orderPaymentObj=new SubscriptionOrderPayment($order_id);
	$order_info=$orderPaymentObj->getOrderPrimaryinfo();
	$payment_gateway_charge=$orderPaymentObj->getOrderPaymentGatewayAmount();
	$recurring_payment_gateway_charge=$orderPaymentObj->getRecurringOrderPaymentGatewayAmount();
	if ($order_info){
		$request = 'cmd=_notify-validate';
		foreach ($post as $key => $value) {
			$request .= '&' . $key . '=' . urlencode(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
		}
		if ($payment_settings["transaction_mode"]==1) {
			$curl = curl_init('https://www.paypal.com/cgi-bin/webscr');
		} elseif ($payment_settings["transaction_mode"]==0) {
			$curl = curl_init('https://www.sandbox.paypal.com/cgi-bin/webscr');
		}
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($curl);
		if ((strcmp($response, 'VERIFIED') == 0)) {
			
			$subscription_payment_status = 0;
			switch(strtoupper($post['txn_type'])) {
				case 'SUBSCR_CANCEL':
					$subscription_status_id = $payment_settings['order_status_cancelled'];
				break;
				case 'SUBSCR_SIGNUP':
					$subscription_status_id = $payment_settings['order_status_signup'];
					$orderPaymentObj->addOrderPayment($payment_settings["subscriptionpmethod_name"],$post['txn_id'],$post['mc_gross'],Utilities::getLabel("L_Subscription_Signed_Up"),$request,false);
					
					/* Cancel old previous active subscription[  */
					$subscription_order_info=$orderPaymentObj->getSubscriptionOrderById($order_id);
					if( $subscription_order_info['mporder_old_mporder_id'] > 0 ){
						$old_order_info = $orderPaymentObj->getSubscriptionOrderById( $subscription_order_info['mporder_old_mporder_id'] );
						if( $old_order_info && $old_order_info['mporder_gateway_subscription_id'] != '' ){
							if( strpos($old_order_info['mporder_gateway_subscription_id'],'FREE') === FALSE ){
								$ppExpObj = new PaypalStandard();
								$subscription_cancelation_result = $ppExpObj->recurringCancel( $old_order_info['mporder_gateway_subscription_id'] );
								if( isset($subscription_cancelation_result['PROFILEID']) ) {
								$order_update_arr = array( 'mporder_mpo_status_id' => Settings::getSetting("CONF_CANCELLED_SUBSCRIPTION_STATUS") );
								$orderPaymentObj->updateOrderInfo( $old_order_info['mporder_id'], $order_update_arr);
							}
						} else {
							$order_update_arr = array( 'mporder_mpo_status_id' => Settings::getSetting("CONF_CANCELLED_SUBSCRIPTION_STATUS") );
							$orderPaymentObj->updateOrderInfo( $old_order_info['mporder_id'], $order_update_arr);
							
						}
						$orderPaymentObj->addOrderHistory( $old_order_info['mporder_id'], Settings::getSetting("CONF_CANCELLED_SUBSCRIPTION_STATUS"),'',true );
					}
					
				}
		
					
				break;
				case 'SUBSCR_PAYMENT':
						$mptran_active_from = date("Y-m-d H:i:s");
						$days =  $order_info['mporder_merchantsubpack_subs_frequency'];
						$mptran_active_till = date('Y-m-d H:i:s', strtotime("+".$days." Day", strtotime(date("Y-m-d H:i:s"))));
						$paypal_transaction_data = array(
							'mptran_mporder_id'		=>	$order_id,
							'mptran_mode'			=>	$this->key_name,
							'mptran_gateway_transaction_id'	=>	$post['txn_id'],
							'mptran_payment_type'			=>	$post['payment_type'],
							'mptran_gateway_parent_transaction_id'	=>	$post['parent_txn_id'],
							'mptran_gateway_response'	=>	serialize($post),
							'mptran_amount'			=>	$post['mc_gross'],
							'mptran_gateway_charges'	=>	$post['payment_fee'],
							'mptran_payment_status'	=>	$post['payment_status'],
							'mptran_pending_reason'	=>	$post['pending_reason'],
							'mptran_recurring_type'	=>	'-1', //NA
							'mptran_active_from' 			=>	$mptran_active_from,
							'mptran_active_till'			=>	$mptran_active_till,
							);
						$packageTxnObj = new PackageTransactions();
						if( !$packageTxnObj->addTransaction($paypal_transaction_data) ){
							$this->log($paypal_transaction_data, "Could Not Save Package Transaction Data");
						}
			
					$orderPaymentObj->addOrderPayment($payment_settings["subscriptionpmethod_name"],$post['txn_id'],$post['mc_gross'],Utilities::getLabel("L_Payment_Received"),$request,1);
					$subscription_status_id = $payment_settings['order_status_payment'];
				break;
				case 'SUBSCR_FAILED':
					$subscription_status_id = $payment_settings['order_status_failed'];
				break;
				case 'SUBSCR_EOT':
					$subscription_status_id = $payment_settings['order_status_eot'];
				break;
				default:
					$subscription_status_id = $payment_settings['order_status_others'];
				break;
			}
			$order_update_arr = array(
				'mporder_mpo_status_id'	=>	$subscription_status_id, 
				'mporder_gateway_subscription_id' =>	$post['subscr_id'],
				); 
			if( !$orderPaymentObj->updateOrderInfo( $order_id, $order_update_arr) ){
				$this->log($order_update_arr, "Could Not Save Package Order Data");
			}
			$orderPaymentObj->addOrderHistory( $order_id, $subscription_status_id,'',true );
			
						
			$payment_gateway_response_data = array(
				'subgatewaytxn_mporder_id'		=>	$order_id,
				'subgatewaytxn_mode'			=>	$this->key_name,
				'subgatewaytxn_transaction_id'	=>	$post['txn_id'],
				'subgatewaytxn_response'	=>	serialize($post),
				'subgatewaytxn_amount'			=>	$post['mc_gross'],
				'subgatewaytxn_status'	=>	$post['payment_status'],
				);
			
			if( !$orderPaymentObj->addPaymentGatewayResponse($payment_gateway_response_data) ){
				$this->log($payment_gateway_response_data, "Could Not Save Package Transaction Data");
			}
			
			
		}else{
			mail('ravibhalla@dummyid.com','IPN Not Verified','IPN Not Verified');
		}
		curl_close($curl);
	}
	
	}
	
	private function log($data, $title = null) {
		Paymentmethods::writeLog('PayPal Express debug ('.$title.'): '.json_encode($data));
	}
}
