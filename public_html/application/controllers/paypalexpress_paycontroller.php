<?php
class PaypalExpress_payController extends PaymentController{
	private $key_name="PaypalExpress";
	public function recurringPayments() {
/*
* Used by the checkout to state the module
* supports recurring.
*/
return true;
}
function package_charge($mporder_id){
	global $db;
	$subscription_status_assoc_arr = SubscriptionOrders::subscription_status_assoc_arr();
	$mporder_id = intval($mporder_id);
	$pmObj=new SubscriptionPaymentsettings($this->key_name);
	if (!$payment_settings=$pmObj->getPaymentSettings()){
		Message::addErrorMessage($pmObj->getError());
		Utilities::redirectUserReferer();
	}
	if( $payment_settings['api_username'] == '' || $payment_settings['api_password'] == '' || $payment_settings['api_signature'] == '' ){
		Message::addErrorMessage("Payment Gateway Settings are not configured by web master, Please contact web master.");
		Utilities::redirectUserReferer();
	}
	if( !$payment_settings['subscriptionpmethod_status'] ){
		Message::addErrorMessage($payment_settings['pmethod_name'].' is disabled by web master, Please contact web master.');
		Utilities::redirectUserReferer();
	}
	$subscriptionOrderObj = new SubscriptionOrders();
	$order_info = $subscriptionOrderObj->getSubscriptionOrderById($mporder_id);
	$payment_amount = $order_info['mporder_payment_gateway_charges'];
	/* add Order Transaction, make order/subscription "Active" as price is 0[ */
	if( $payment_amount == 0 && $order_info['mporder_merchantsubpack_subs_amount'] == 0){
		$mptran_gateway_transaction_id = "FREE - ".$order_info['mporder_id'].'-'.getRandomPassword(11);
		$sub_start_date = $order_info['mporder_date_added'];
		$days = $order_info['mporder_merchantsubpack_subs_frequency'];
		$sub_end_date = date('Y-m-d', strtotime("+" . $days . " Day", strtotime($sub_start_date)) );
		$transaction_data = array(
			'mptran_mporder_id'		=>	$order_info['mporder_id'],
			'mptran_mode'			=>	'1',
			'mptran_gateway_transaction_id'	=>	$mptran_gateway_transaction_id,
			'mptran_payment_type'			=>	'instant',
			'mptran_gateway_parent_transaction_id'	=>	'',
			'mptran_gateway_response'	=>	'',
			'mptran_amount'			=>	$payment_amount,
			'mptran_payment_status'	=>	'Completed',
			'mptran_pending_reason'	=>	'',
'mptran_recurring_type'	=>	'-1', //NA
'mptran_active_from'	=>	$sub_start_date,
'mptran_active_till'	=>	$sub_end_date
);
		$packageTxnObj = new PackageTransactions();
		if( !$packageTxnObj->addTransaction($transaction_data) ){
			$this->log($transaction_data, "Could Not Save Package Transaction Data");
		} else {
			$subcouponObj = new subscriptioncoupon();
			$coupon_info=$subcouponObj->getCouponByCode($order_info['mporder_discount_coupon']);
			if ($coupon_info){
				if (!$db->insert_from_array('tbl_subscoupons_history',  array('subscouponhistory_subscoupon_id' => (int)$coupon_info['subscoupon_id'], 'subscouponhistory_order_id' => (int)$order_info['mporder_id'], 'subscouponhistory_customer_id' => (int)$order_info['mporder_user_id'],'subscouponhistory_amount' => (float)$order_info['mporder_discount_total'],'subscouponhistory_date_added' => date('Y-m-d H:i:s')),true)){
					$this->error = $this->db->getError();
					return false;
				}
			}
			$order_update_arr = array(
				'mporder_payment_status'				=>	1,
				'mporder_gateway_subscription_status'	=>	$subscription_status_assoc_arr['status_active'], 
				'mporder_gateway_subscription_id'		=>	$mptran_gateway_transaction_id,
				'mporder_subscription_start_date'		=>	$sub_start_date,
				'mporder_subscription_end_date'			=>	$sub_end_date
				);
			$subscriptionOrderObj->updateOrderInfo( $order_info['mporder_id'], $order_update_arr);
			Cart::SubscriptionClear();
			Utilities::redirectUser(Utilities::generateUrl('custom','package_payment_success'));
		}
	}else if( $payment_amount == 0 && $order_info['mporder_merchantsubpack_subs_amount'] > 0 ){
		$mptran_gateway_transaction_id = $order_info['mporder_id'].'-'.getRandomPassword(11);
		$sub_start_date = $order_info['mporder_date_added'];
		$days = $order_info['mporder_merchantsubpack_subs_frequency'];
		$sub_end_date = date('Y-m-d', strtotime("+" . $days . " Day", strtotime($sub_start_date)) );
		$transaction_data = array(
			'mptran_mporder_id'		=>	$order_info['mporder_id'],
			'mptran_mode'			=>	'1',
			'mptran_gateway_transaction_id'	=>	$mptran_gateway_transaction_id,
			'mptran_payment_type'			=>	'instant',
			'mptran_gateway_parent_transaction_id'	=>	'',
			'mptran_gateway_response'	=>	'',
			'mptran_amount'			=>	$payment_amount,
			'mptran_payment_status'	=>	'Completed',
			'mptran_pending_reason'	=>	'',
'mptran_recurring_type'	=>	'-1', //NA
'mptran_active_from'	=>	$sub_start_date,
'mptran_active_till'	=>	$sub_end_date
);
		$packageTxnObj = new PackageTransactions();
		if( !$packageTxnObj->addTransaction($transaction_data) ){
			$this->log($transaction_data, "Could Not Save Package Transaction Data");
		} else {
			$subcouponObj = new subscriptioncoupon();
			$coupon_info=$subcouponObj->getCouponByCode($order_info['mporder_discount_coupon']);
			if ($coupon_info){
				if (!$db->insert_from_array('tbl_subscoupons_history', array('subscouponhistory_subscoupon_id' => (int)$coupon_info['subscoupon_id'], 'subscouponhistory_order_id' => (int)$order_info['mporder_id'], 'subscouponhistory_customer_id' => (int)$order_info['mporder_user_id'],'subscouponhistory_amount' => (float)$order_info['mporder_discount_total'],'subscouponhistory_date_added' => date('Y-m-d H:i:s')),true)) {
					$this->error = $this->db->getError();
					return false;
				}
			}
			$order_update_arr = array(
				'mporder_payment_status'				=>	1,
				'mporder_gateway_subscription_status'	=>	$subscription_status_assoc_arr['status_active'], 
				'mporder_gateway_subscription_id'		=>	$mptran_gateway_transaction_id,
				'mporder_subscription_start_date'		=>	$sub_start_date,
				'mporder_subscription_end_date'			=>	$sub_end_date
				);
			$subscriptionOrderObj->updateOrderInfo( $order_info['mporder_id'], $order_update_arr);				
		}
		/* Cancel old previous active subscription[  */
		if( $order_info['mporder_old_mporder_id'] > 0 ){
			$old_order_info = $subscriptionOrderObj->getSubscriptionOrderById( $order_info['mporder_old_mporder_id'] );
			if( $old_order_info && $old_order_info['mporder_gateway_subscription_id'] != '' ){
				if( strpos($old_order_info['mporder_gateway_subscription_id'],'FREE') === FALSE ){
					$ppExpObj = new PaypalExpress();
					$subscription_cancelation_result = $ppExpObj->recurringCancel( $old_order_info['mporder_gateway_subscription_id'] );
					if( isset($subscription_cancelation_result['PROFILEID']) ) {
						$order_update_arr = array( 'mporder_gateway_subscription_status' => $subscription_status_assoc_arr['status_cancelled'] );
						$subscriptionOrderObj->updateOrderInfo( $old_order_info['mporder_id'], $order_update_arr);
					}
				} else {
					$order_update_arr = array( 'mporder_gateway_subscription_status' => $subscription_status_assoc_arr['status_cancelled'] );
					$subscriptionOrderObj->updateOrderInfo( $old_order_info['mporder_id'], $order_update_arr);
				}
			}
			$newProductsLimit = $order_info['mporder_merchantpack_max_products'] ;
			$newProductImageLimit = $order_info['mporder_merchantpack_max_pimages'] ;
			$products = new Products();
			$userId = $order_info['mporder_user_id'];
			$productsAddedByUser  =  $products->getTotalProductsAddedByUser($userId);
			if($productsAddedByUser>$newProductsLimit){
				$products->deleteExtraLatestProducts($userId,$newProductsLimit,$productsAddedByUser,$newProductImageLimit);
			}
		}
	}
	/* ] */
	/* update orders payment gatewayinfo[ */
	$subscriptionOrderObj=new SubscriptionOrders();
	$order_update = array(
		'mporder_payment_method'	=>	$payment_settings['pmethod_name'],
		'mporder_payment_method_code'=>	$payment_settings['pmethod_code'],
		);
	if(!$subscriptionOrderObj->updateOrderInfo($mporder_id,$order_update)){
		Message::addErrorMessage($subscriptionOrderObj->getError());
		Utilities::redirectUser(Utilities::generateUrl('account','packages'));
	}
	/* ] */
	/* if ($order_info && $order_info["mporder_payment_status"]==0){ */
		if ($order_info && $order_info["mporder_payment_status"]==0){
			$max_amount = $payment_amount;
			$returnUrl = Utilities::generateAbsoluteUrl('paypalexpress_pay','package_charge_return');
			$cancelUrl = Utilities::generateAbsoluteUrl('account','packages');
			$data = array(
				'METHOD' => 'SetExpressCheckout',
				'MAXAMT' => $max_amount,
				'RETURNURL' => $returnUrl,
				'CANCELURL' => $cancelUrl,
//	'NotifyURL' => Utilities::generateAbsoluteUrl('paypalexpress_pay','package_ipn'),
				'REQCONFIRMSHIPPING' => 0,
				'NOSHIPPING' => 1,
				'LOCALECODE' => 'EN',
				'LANDINGPAGE' => 'Login',
				'HDRIMG' => '',
				'HDRBORDERCOLOR' => '',
				'HDRBACKCOLOR' => '',
				'PAYFLOWCOLOR' => '',
				'CHANNELTYPE' => 'Merchant',
				'ALLOWNOTE' => 1,
				);
			$data = array_merge($data, $this->paymentRequestInfo($order_info, $payment_settings));
			$result = $this->call($data, $payment_settings);
/**
* If a failed PayPal setup happens, handle it.
*/
// printArray($result);
// die;
if(!isset($result['TOKEN'])) {
	$error = 'Paypal Token Error : - ' . $result['L_LONGMESSAGE0'];
	Message::addErrorMessage($error);
	Paymentmethods::writeLog(serialize($result));
	Utilities::redirectUser(Utilities::generateUrl('account','packages'));
}
$_SESSION['shopping_cart_subscription']['paypal']['paypalexpress_token'] = $result['TOKEN'];
if ($payment_settings["transaction_mode"]==1) {
	$paypal_redirect_url = 'https://www.paypal.com/webscr?cmd=_express-checkout&token=' . $result['TOKEN'].'&useraction=commit';
} else {
	$paypal_redirect_url = 'https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=' . $result['TOKEN'].'&useraction=commit';
}
$this->set('payment_amount', $payment_amount);
}else{
	$error = Utilities::getLabel('M_INVALID_ORDER_PAID_CANCELLED');
}
if(isset($error)){
	$this->set('error', $error);
}
$this->set('order_info', $order_info);
$this->_template->render(true,false);
if(!isset($error)){
	Utilities::redirectUser($paypal_redirect_url);
}
}
function package_charge_return(){
	$subscriptionOrderObj	=	new SubscriptionOrders();
	$subscription_status_assoc_arr = SubscriptionOrders::subscription_status_assoc_arr();
	$mporder_id = $_SESSION['shopping_cart_subscription']["order"];
//$packageOrderPaymentObj	=	new PackageOrderPayment($mporder_id);
	$packageTxnObj = new PackageTransactions();
	$pmObj=new Paymentsettings($this->key_name);
	$payment_settings=$pmObj->getPaymentSettings();
	$token = $_SESSION['shopping_cart_subscription']['paypal']['paypalexpress_token'];
	$data = array(
		'METHOD' => 'GetExpressCheckoutDetails',
		'TOKEN' => $token,
		);
	$result = $this->call($data);
	$_SESSION['shopping_cart_subscription']['paypal']['payerid'] = $result['PAYERID'];
	$_SESSION['shopping_cart_subscription']['paypal']['result'] = $result;
	$order_info = $subscriptionOrderObj->getSubscriptionOrderById($mporder_id);
	$paypal_data = array(
		'TOKEN' => $token,
		'PAYERID' => $_SESSION['shopping_cart_subscription']['paypal']['payerid'],
		'METHOD' => 'DoExpressCheckoutPayment',
		'PAYMENTREQUEST_0_NOTIFYURL' => generateNoAuthUrl('paypalexpress_pay','package_ipn'),
		'RETURNFMFDETAILS' => 1,
		);
	$paypal_data = array_merge($paypal_data, $this->paymentRequestInfo($order_info, $payment_settings));
	$result = $this->call($paypal_data);
	if( strtoupper($result['ACK']) == 'SUCCESS' || ($order_info['mporder_payment_gateway_charges']==0 && strtoupper($result['ACK']) == 'FAILURE')) {
		switch(strtoupper($result['PAYMENTINFO_0_PAYMENTSTATUS'])) {
			case 'CANCELED_REVERSAL':
			$order_status_id = $payment_settings['order_status_canceled_reversal'];
			break;
			case 'COMPLETED':
			$order_status_id = $payment_settings['order_status_completed'];
			/* updating the order payment as completed[ */
			$order_update_arr = array( 'mporder_payment_status' => 1 );
			$subscriptionOrderObj->updateOrderInfo($order_info['mporder_id'], $order_update_arr);
			/* ] */
			/* Cancel old previous active subscription[  */
			if( $order_info['mporder_old_mporder_id'] > 0 ){
				$old_order_info = $subscriptionOrderObj->getSubscriptionOrderById( $order_info['mporder_old_mporder_id'] );
				if( $old_order_info && $old_order_info['mporder_gateway_subscription_id'] != '' ){
					if( strpos($old_order_info['mporder_gateway_subscription_id'],'FREE') === FALSE ){
						$ppExpObj = new PaypalExpress();
						$subscription_cancelation_result = $ppExpObj->recurringCancel( $old_order_info['mporder_gateway_subscription_id'] );
						if( isset($subscription_cancelation_result['PROFILEID']) ) {
							$order_update_arr = array( 'mporder_gateway_subscription_status' => $subscription_status_assoc_arr['status_cancelled'] );
							$subscriptionOrderObj->updateOrderInfo( $old_order_info['mporder_id'], $order_update_arr);
						}
					} else {
						$order_update_arr = array( 'mporder_gateway_subscription_status' => $subscription_status_assoc_arr['status_cancelled'] );
						$subscriptionOrderObj->updateOrderInfo( $old_order_info['mporder_id'], $order_update_arr);
					}
				}
				$newProductsLimit = $order_info['mporder_merchantpack_max_products'] ;
				$newProductImageLimit = $order_info['mporder_merchantpack_max_pimages'] ;
				$products = new Products();
				$userId = $order_info['mporder_user_id'];
				$productsAddedByUser  =  $products->getTotalProductsAddedByUser($userId);
				if($productsAddedByUser>$newProductsLimit){
					$products->deleteExtraLatestProducts($userId,$newProductsLimit,$productsAddedByUser,$newProductImageLimit);
				}
			}else{
				$sub_start_date = $order_info['mporder_date_added'];
				$days = $order_info['mporder_merchantsubpack_subs_frequency'];
				$sub_end_date = date('Y-m-d H:i:s', strtotime("+" . $days . " Day", strtotime($sub_start_date)) );	
				$order_update_arr = array(
					'mporder_payment_status'				=>	1,
					'mporder_gateway_subscription_status'	=>	$subscription_status_assoc_arr['status_active'], 
					'mporder_gateway_subscription_id'		=>	$result['PAYMENTINFO_0_TRANSACTIONID'],
					'mporder_subscription_start_date'		=>	$sub_start_date,
					'mporder_subscription_end_date'			=>	$sub_end_date
					); 
				if( !$subscriptionOrderObj->updateOrderInfo( $order_info['mporder_id'], $order_update_arr) ){
					$this->log($order_update_arr, "Could Not Save Package Order Data");
				} 
			}
			/* ] */
			break;
			case 'FAILURE':
			break;
			case 'DENIED':
			$order_status_id = $payment_settings['order_status_denied'];
			break;
			case 'EXPIRED':
			$order_status_id = $payment_settings['order_status_expired'];
			break;
			case 'FAILED':
			$order_status_id = $payment_settings['order_status_failed'];
			break;
			case 'PENDING':
			$order_status_id = $payment_settings['order_status_pending'];
			break;
			case 'PROCESSED':
			$order_status_id = $payment_settings['order_status_processed'];
			break;
			case 'REFUNDED':
			$order_status_id = $payment_settings['order_status_refunded'];
			break;
			case 'REVERSED':
			$order_status_id = $payment_settings['order_status_reversed'];
			break;
			case 'VOIDED':
			$order_status_id = $payment_settings['order_status_voided'];
			break;
		}
		/* add order history[ */
		$subscriptionOrderObj->addOrderHistory( $order_info['mporder_id'], $order_status_id );
		/* ] */
		$mptran_active_from = date("Y-m-d H:i:s");
		$days =  $order_info['mporder_merchantsubpack_subs_frequency'];
		$mptran_active_till = date('Y-m-d H:i:s', strtotime("+".$days." Day", strtotime(date("Y-m-d H:i:s"))));
		/* add Order Transaction[ */
		$paypal_transaction_data = array(
			'mptran_mporder_id'		=>	$order_info['mporder_id'],
			'mptran_mode'			=>	'1',
			'mptran_gateway_transaction_id'	=>	$result['PAYMENTINFO_0_TRANSACTIONID'],
			'mptran_payment_type'			=>	$result['PAYMENTINFO_0_PAYMENTTYPE'],
			'mptran_gateway_parent_transaction_id'	=>	'',
			'mptran_gateway_response'	=>	serialize($result),
			'mptran_amount'			=>	$result['PAYMENTINFO_0_AMT'],
			'mptran_gateway_charges'			=>	$result['PAYMENTINFO_0_FEEAMT'],
			'mptran_payment_status'	=>	$result['PAYMENTINFO_0_PAYMENTSTATUS'],
			'mptran_pending_reason'	=>	$result['PAYMENTINFO_0_PENDINGREASON'],
'mptran_recurring_type'	=>	'-1', //NA
'mptran_active_from' 			=>	$mptran_active_from,
'mptran_active_till'			=>	$mptran_active_till,
);
		if($order_info['mporder_payment_gateway_charges']>0){
			if( !$packageTxnObj->addTransaction($paypal_transaction_data) ){
				$this->log($paypal_transaction_data, "Could Not Save Package Transaction Data");
			}
		}
		if($order_info['mporder_credits_charged']>0){
			$comments = "Charged Against Order  #". $order_info['mporder_id'];
			$txnArray["utxn_user_id"]=$order_info['mporder_user_id'];
			$txnArray["utxn_debit"]=$order_info['mporder_credits_charged'];
			$txnArray["utxn_status"]=1;
			$txnArray["utxn_order_id"]=$order_info['mporder_id'];
			$txnArray["utxn_comments"]=$comments;
			$subscriptionOrderObj->chargeWallet($txnArray);		
		}
		/* Credit User wallet */
		if($_SESSION['shopping_cart_subscription']['amtToCredit'] > 0)
		{
			$txnArray["utxn_user_id"] = User::getLoggedUserId();
			$txnArray["utxn_credit"] = $_SESSION['shopping_cart_subscription']['amtToCredit'];
			$txnArray["utxn_status"] = 1;
			$txnArray["utxn_order_id"]= $_SESSION['shopping_cart_subscription']['order'];
			$txnArray["utxn_comments"]= Utilities::getLabel('L_Wallet_Credited') ;
			$subscriptionOrderObj = new SubscriptionOrders();
			$subscriptionOrderObj->chargeWallet($txnArray);
			unset($_SESSION['shopping_cart_subscription']['amtToCredit']);
		}
		/* ] */
		/* create recurring payment profile on paypal[ */
/* 'PROFILESTARTDATE'	=>	urlencode(gmdate("Y-m-d\TH:i:s\Z", mktime(gmdate("H"), gmdate("i")+5, gmdate("s"), gmdate("m"), gmdate("d"), gmdate("y")))),
$PROFILESTARTDATE = (gmdate("Y-m-d\TH:i:s\Z", mktime(gmdate("H"), gmdate("i")+5, gmdate("s"), gmdate("m"), gmdate("d"), gmdate("y")))); */
$p_date	= date('Y-m-d', strtotime("+".$order_info['mporder_merchantsubpack_subs_frequency']." Days")); 
$p_time = date('H:i:s');
$PROFILESTARTDATE = $p_date.'T'.$p_time.'Z';
$untillText = ($order_info['mporder_recurring_billing_cycle']==9999)?'untill cancelled':'till '.$order_info['mporder_recurring_billing_cycle'] .'occurence';
$data = array(
	'METHOD'			=>	'CreateRecurringPaymentsProfile',
	'TOKEN'				=>	$token,
	'PROFILESTARTDATE'	=>	$PROFILESTARTDATE,
	'BILLINGPERIOD'		=>	urlencode('Day'),
	'BILLINGFREQUENCY'	=>	$order_info['mporder_merchantsubpack_subs_frequency'],
	'AUTOBILLOUTAMT'	=>	'AddToNextBilling',
	'TOTALBILLINGCYCLES'=>	($order_info['mporder_recurring_billing_cycle']==9999)?0:$order_info['mporder_recurring_billing_cycle'],
	'AMT'				=>	$order_info['mporder_recurring_chargeble_amount'],
	'CURRENCYCODE'		=>	'USD',
	'DESC'				=>	Utilities::displayMoneyFormat($order_info['mporder_recurring_chargeble_amount']).' every '.$order_info['mporder_merchantsubpack_subs_frequency'].' Days '.$untillText,
	);

$result = $this->call($data); //response back from paypal along with created profile id
/* ] */
if( isset($result['PROFILEID']) ) {
	$order_update_arr = array( 'mporder_gateway_subscription_id' => $result['PROFILEID'] );
	$subscriptionOrderObj->updateOrderInfo($order_info['mporder_id'], $order_update_arr);
}
if( isset($result['REDIRECTREQUIRED']) && $result['REDIRECTREQUIRED'] == true) { //- handle german redirect here
//$this->redirect('https://www.paypal.com/cgi-bin/webscr?cmd=_complete-express-checkout&token='.$token);
} else {
	Cart::SubscriptionClear();
	Utilities::redirectUser(Utilities::generateUrl('custom','package_payment_success'));
}
} else {
	Message::addErrorMessage($result['L_ERRORCODE0'].' : '.$result['L_LONGMESSAGE0']);
	Utilities::redirectUser(Utilities::generateUrl());
}
}
function package_ipn(){
	global $db;
	$subscription_status_assoc_arr = SubscriptionOrders::subscription_status_assoc_arr();
	$pmObj=new Paymentsettings($this->key_name);
	$payment_settings=$pmObj->getPaymentSettings();
	$packageTxnObj = new PackageTransactions();
	$subscriptionOrderObj = new SubscriptionOrders();
	$post = Syspage::getPostedVar();
	$request = 'cmd=_notify-validate';
	foreach ($post as $key => $value) {
		$request .= '&' . $key . '=' . urlencode(stripslashes($value));
	}
	if ($payment_settings['transaction_mode'] == 0) {
		$curl = curl_init('https://www.sandbox.paypal.com/cgi-bin/webscr');
	} else {
		$curl = curl_init('https://www.paypal.com/cgi-bin/webscr');
	}
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_TIMEOUT, 30);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	$response = trim(curl_exec($curl));
	if (!$response) {
		$this->log(array('error' => curl_error($curl),'error_no' => curl_errno($curl)), 'Inside IPN Curl failed');
	}
	$this->log(array('request' => $request,'response' => $response), 'IPN data');
	if ((string)strtoupper($response) == "VERIFIED" )  {
		$transaction = false;
		$parent_transaction = false;
		if(isset($post['txn_id'])){
			$transaction = $packageTxnObj->getTransactions( array( 'gateway_transaction_id' => $post['txn_id'] ), $pagesize = 1 );
		}
		if(isset($post['parent_txn_id'])) {
			$parent_transaction = $packageTxnObj->getTransactions( array('gateway_transaction_id' => $post['parent_txn_id']), $pagesize = 1 );
		}elseif(isset($post['recurring_payment_id'])){
			$parent_transaction = $packageTxnObj->getTransactionsByProfileId( array('mporder_gateway_subscription_id' => $post['recurring_payment_id']), $pagesize = 1 );
			$parent_transaction['mptran_mporder_id']; 
		}			
		if($transaction){
			$this->log( array('Transaction exists'), 'IPN');
//if the transaction is pending but the new status is completed
			if( strtoupper($transaction['mptran_payment_status']) != strtoupper($post['payment_status']) ) {
				$data_to_update = array( 'mptran_payment_status' => $post['payment_status']);
				if( !$packageTxnObj->updateTransactionInfo($transaction['mptran_id'], $data_to_update) ){
					$this->log( array( 'error' => $packageTxnObj->getError() ), 'IPN Payment Status Updation error:: query failed');
				} else {
					$this->log( array('txn payment_status updated from '.$transaction['mptran_payment_status'].' to'.$post['payment_status'] ), 'IPN');
				}
			} elseif( strtoupper($transaction['mptran_payment_status']) == 'PENDING' && ( $transaction['mptran_pending_reason'] != $post['pending_reason'])) {
//payment is still pending but the pending reason has changed, update it.
				$data_to_update = array( 'mptran_pending_reason' => $post['pending_reason']);
				if( !$packageTxnObj->updateTransactionInfo( $transaction['mptran_id'], $data_to_update ) ){
					$this->log( array( 'error' => $packageTxnObj->getError() ), 'IPN Payment pending_reason Updation error:: query failed');
				}
			}
		} else {
			$this->log( array('Transaction does not exist'), 'IPN');
			if( $parent_transaction ){
				$this->log( array('Parent transaction exists'), 'IPN' );
				/* insert new related transaction[ */
				$paypal_transaction_data = array(
					'mptran_mporder_id'				=>	$parent_transaction['mptran_mporder_id'],
					'mptran_mode'					=>	'1',
					'mptran_gateway_transaction_id'	=>	$post['txn_id'],
					'mptran_payment_type'			=>	( isset($post['payment_type']) ? $post['payment_type'] : '' ),
					'mptran_gateway_parent_transaction_id'	=>	$post['parent_txn_id'],
					'mptran_gateway_response'		=>	serialize($post),
					'mptran_amount'					=>	$post['mc_gross'],
					'mptran_payment_status'			=>	(isset($post['payment_status']) ? $post['payment_status'] : ''),
					'mptran_pending_reason'			=>	(isset($post['pending_reason']) ? $post['pending_reason'] : ''),
'mptran_recurring_type'			=>	'-1' //NA
);
				if( !$packageTxnObj->addTransaction($paypal_transaction_data) ){
					$this->log($paypal_transaction_data, "Could Not Save Package IPN Transaction Data");
				}else{
					$this->log($paypal_transaction_data, "Package IPN Transaction Data at 1");
				}
				/* ] */
				/* If there has been a refund, log this against the parent transaction.[ */
				if( isset($post['payment_status']) && strtoupper($post['payment_status']) == 'REFUNDED') {
					if( ($post['mc_gross'] * -1) == $parent_transaction['mptran_amount'] ) {
						$data_to_update = array( 'mptran_payment_status' => 'Refunded' );
						if( !$packageTxnObj->updateTransactionInfo( $parent_transaction['mptran_id'], $data_to_update ) ){
							$this->log( array( 'error' => $packageTxnObj->getError() ), 'IPN Payment payment_status Updation error in refunding:: query failed');
						}
					} else {
						$data_to_update = array( 'mptran_payment_status' => 'Partially-Refunded' );
						if( !$packageTxnObj->updateTransactionInfo( $parent_transaction['mptran_id'], $data_to_update ) ){
							$this->log( array( 'error' => $packageTxnObj->getError() ), 'IPN Payment payment_status Updation error in partial payment refunding:: query failed');
						}
					}
				}
				/* ] */
				/* If the capture payment is now complete[ */
				if( isset($post['auth_status']) && strtoupper($post['auth_status']) == 'COMPLETED' && strtoupper($parent_transaction['mptran_payment_status']) == 'PENDING') {
				}
				/* ] */
			} else {
				$this->log(array('Parent transaction not found'), 'IPN');
			}
		}
		if(isset($post['txn_type'])){
			$txn_type= $post['txn_type'];
		}else{
			$txn_type=  'N/A';
		}if(isset($post['txn_id'])){
			$txn_id= $post['txn_id'];
		}else{
			$txn_id=  'N/A';
		}if(isset($post['txn_parent_id'])){
			$txn_parent_id= $post['txn_parent_id'];
		}else{
			$txn_parent_id=  'N/A';
		}
		/* to handle recurring transactions */
		if ( isset($post['txn_type']) ) {
			$profile =  $subscriptionOrderObj->getSubscriptionOrders(array('mporder_gateway_subscription_id' => $post['recurring_payment_id']));
			if( $profile != false ){
				$valid_txn_types_arr = array(
					'recurring_payment', 'recurring_payment_suspended', 
					'recurring_payment_suspended_due_to_max_failed_payment', 'recurring_payment_failed',
					'recurring_payment_outstanding_payment_failed', 'recurring_payment_outstanding_payment', 
					'recurring_payment_profile_created', 'recurring_payment_skipped', 'recurring_payment_expired' 
					);
				$mptran_active_from = '0000-00-00 00:00:00';
				$mptran_active_till = '0000-00-00 00:00:00';
				switch($post['txn_type']){
//payment
					case 'recurring_payment':
					$mptran_recurring_type = 1;
					/* update subscription end date[ */
					$sub_start_date = $profile['mporder_date_added'];
					$days = $profile['mporder_merchantsubpack_subs_frequency'];
					$sub_end_date = date('Y-m-d', strtotime("+".$days." Day", strtotime($profile['mporder_subscription_end_date'])));
					if( $profile['mporder_subscription_end_date'] == '0000-00-00 00:00:00' ){
						$sub_end_date = date('Y-m-d', strtotime("+" . $days . " Day", strtotime($sub_start_date)) );
					}
					$order_update_arr = array(
						'mporder_gateway_subscription_status'	=>	$subscription_status_assoc_arr['status_active'],
						'mporder_subscription_end_date'			=>	$sub_end_date
						);
					/* ] */
					$mptran_active_from = date('Y-m-d H:i:s', strtotime("+1 Day", strtotime($sub_end_date)) );
					$mptran_active_till = date('Y-m-d', strtotime("+" . $days . " Day", strtotime($sub_end_date)) );
//as there was a payment the profile is active, ensure it is set to active (may be been suspended before)
					if( $profile['mporder_gateway_subscription_status'] != 1 ){
						$order_update_arr['mporder_gateway_subscription_status'] = $subscription_status_assoc_arr['status_active'];
					}
					$subscriptionOrderObj->updateOrderInfo($profile['mporder_id'], $order_update_arr);
					break;
//suspend
					case 'recurring_payment_suspended':
					$mptran_recurring_type = 6;
					$order_update_arr = array( 'mporder_gateway_subscription_status' => 3 );
					$subscriptionOrderObj->updateOrderInfo($profile['mporder_id'], $order_update_arr);
					break;
//suspend due to max failed
					case 'recurring_payment_suspended_due_to_max_failed_payment':
					$mptran_recurring_type = 7;
					$order_update_arr = array( 'mporder_gateway_subscription_status' => 3 );
					$subscriptionOrderObj->updateOrderInfo($profile['mporder_id'], $order_update_arr);
					break;
//payment failed
					case 'recurring_payment_failed':
					$mptran_recurring_type = 4;
					break;
//outstanding payment failed
					case 'recurring_payment_outstanding_payment_failed':
					$mptran_recurring_type = 8;
					break;
//outstanding payment
					case 'recurring_payment_outstanding_payment':
					$mptran_recurring_type = 2;
//as there was a payment the profile is active, ensure it is set to active (may be been suspended before)
					if($profile['mporder_gateway_subscription_status'] != 1){
						$order_update_arr = array( 'mporder_gateway_subscription_status' => 2 );
						$subscriptionOrderObj->updateOrderInfo($profile['mporder_id'], $order_update_arr);
					}
					break;
//created 
					case 'recurring_payment_profile_created':
					$mptran_recurring_type = 0;
					if($profile['mporder_gateway_subscription_status'] != 1){
						$sub_start_date = $profile['mporder_date_added'];
						$days = $profile['mporder_merchantsubpack_subs_frequency'];
						$sub_end_date = date('Y-m-d', strtotime("+" . $days . " Day", strtotime($sub_start_date)) );
						$order_update_arr = array(
							'mporder_gateway_subscription_status'	=>	2,
							'mporder_subscription_start_date'		=>	$sub_start_date,
							'mporder_subscription_end_date'			=>	$sub_end_date
							);
						$subscriptionOrderObj->updateOrderInfo($profile['mporder_id'], $order_update_arr);
						$subcouponObj = new subscriptioncoupon();
						$coupon_info=$subcouponObj->getCouponByCode($profile['mporder_discount_coupon']);
						if ($coupon_info){
							if (!$db->insert_from_array('tbl_subscoupons_history',  array('subscouponhistory_subscoupon_id' => (int)$coupon_info['subscoupon_id'], 'subscouponhistory_order_id' => (int)$profile['mporder_id'], 'subscouponhistory_customer_id' => (int)$profile['mporder_user_id'],'subscouponhistory_amount' => (float)$profile['mporder_discount_total'],'subscouponhistory_date_added' => date('Y-m-d H:i:s')),true)) {
								$this->log( array( 'error' =>  $db->getError() ), 'Counpon Query Error');
							}
						}
					}
					$mptran_active_from = $sub_start_date;
					$mptran_active_till = $sub_end_date;
					break;
//cancelled
					case 'recurring_payment_profile_cancel':
					$mptran_recurring_type = 5;
					break;
//skipped
					case 'recurring_payment_skipped':
					$mptran_recurring_type = 3;
					break;
//expired
					case 'recurring_payment_expired':
					$mptran_recurring_type = 9;
					$order_update_arr = array( 'mporder_gateway_subscription_status' => 5 );
					$subscriptionOrderObj->updateOrderInfo($profile['mporder_id'], $order_update_arr);
					break;
				}
				if ($post['txn_type'] == 'recurring_payment_profile_cancel') {
//cancelled
					if($profile['mporder_gateway_subscription_status'] != 3){
						/* insert new related transaction[ */
						$paypal_transaction_data = array(
							'mptran_mporder_id'				=>	$profile['mporder_id'],
							'mptran_mode'					=>	'1',
							'mptran_gateway_transaction_id'	=>	$post['txn_id'],
							'mptran_payment_type'			=>	( isset($post['payment_type']) ? $post['payment_type'] : '' ),
							'mptran_gateway_parent_transaction_id'	=>	(isset($post['parent_txn_id']) ? $post['parent_txn_id'] : '' ),
							'mptran_gateway_response'		=>	serialize($post),
							'mptran_amount'					=>	$post['amount'],
							'mptran_payment_status'			=>	(isset($post['payment_status']) ? $post['payment_status'] : ''),
							'mptran_pending_reason'			=>	(isset($post['pending_reason']) ? $post['pending_reason'] : ''),
'mptran_recurring_type'			=>	'5', //defined in application-top.php
);
						if( !$packageTxnObj->addTransaction($paypal_transaction_data) ){
							$this->log($paypal_transaction_data, "Could Not Save Package IPN Transaction Data");
						}
						/* ] */
						$order_update_arr = array( 'mporder_gateway_subscription_status' => 4 );
						$subscriptionOrderObj->updateOrderInfo($profile['mporder_id'], $order_update_arr);
					}
				}elseif( in_array($post['txn_type'], $valid_txn_types_arr) ){
					$paypal_transaction_data = array(
						'mptran_mporder_id'				=>	$profile['mporder_id'],
						'mptran_mode'					=>	'1',
						'mptran_gateway_transaction_id'	=>	$post['txn_id'],
						'mptran_payment_type'			=>	( isset($post['payment_type']) ? $post['payment_type'] : '' ),
						'mptran_gateway_parent_transaction_id'	=>	(isset($post['parent_txn_id']) ? $post['parent_txn_id'] : '' ),
						'mptran_gateway_response'		=>	serialize($post),
						'mptran_amount'					=>	$post['amount'],
						'mptran_payment_status'			=>	(isset($post['payment_status']) ? $post['payment_status'] : ''),
						'mptran_pending_reason'			=>	(isset($post['pending_reason']) ? $post['pending_reason'] : ''),
'mptran_recurring_type'			=>	$mptran_recurring_type, //defined in application-top.php
'mptran_active_from' 			=>	$mptran_active_from,
'mptran_active_till'			=>	$mptran_active_till,
);
					if( !$packageTxnObj->addTransaction($paypal_transaction_data) ){
						$this->log($paypal_transaction_data, "Could Not Save Package IPN Transaction Data");
					}
				}
			}
		}
	} elseif( (string)strtoupper($response) == "INVALID" ) {
		$this->log(array('IPN was invalid'), 'IPN fail');
	} else {
		$this->log('Paypal IPN string unknown ');
	}
	curl_close($curl);
	header("HTTP/1.1 200 Ok");
}
private function paymentRequestInfo($order_info, $payment_settings ){
	$untillText = ($order_info['mporder_recurring_billing_cycle']==9999)?'untill cancelled':'till '.$order_info['mporder_recurring_billing_cycle'] .'occurence';
	$item_total = $order_info['mporder_payment_gateway_charges'];
	$data['PAYMENTREQUEST_0_SHIPPINGAMT']	=	'';
	$data['PAYMENTREQUEST_0_CURRENCYCODE']	=	$order_info['mporder_currency_code'];
	$data['PAYMENTREQUEST_0_PAYMENTACTION']	=	urlencode($payment_settings['transaction_method']);
	$data['L_PAYMENTREQUEST_0_DESC0']		=	substr(strip_tags($order_info['mporder_merchantpack_desc']), 0, 126) ;
	$data['L_PAYMENTREQUEST_0_NAME0']		=	$order_info['mporder_merchantpack_name'].' - '.$order_info['mporder_merchantsubpack_name'];
	$data['L_PAYMENTREQUEST_0_NUMBER0']		=	'';
	$data['L_PAYMENTREQUEST_0_AMT0']		=	number_format($item_total, 2, '.', '');
	$data['PAYMENTREQUEST_0_AMT']			=	number_format($item_total, 2, '.', '');
	$data['L_PAYMENTREQUEST_0_QTY0']		=	1;
	$data['PAYMENTREQUEST_0_ITEMAMT']		=	number_format($item_total, 2, '.', '');
	$data['L_BILLINGTYPE0']					=	'RecurringPayments';		
	$data['L_BILLINGAGREEMENTDESCRIPTION0'] =	Utilities::displayMoneyFormat($order_info['mporder_recurring_chargeble_amount']).' every '.$order_info['mporder_merchantsubpack_subs_frequency'].' Days '.$untillText;
	return $data;
}
private function call($data){
	$pmObj=new Paymentsettings($this->key_name);
	$payment_settings=$pmObj->getPaymentSettings();
	if ($payment_settings["transaction_mode"]==1) {
		$api_endpoint = 'https://api-3t.paypal.com/nvp';
	} else {
		$api_endpoint = 'https://api-3t.sandbox.paypal.com/nvp';
	}
	$settings = array(
		'VERSION' => urlencode('64.0'),
		'USER' => urlencode($payment_settings['api_username']),
		'PWD' => urlencode($payment_settings['api_password']),
		'SIGNATURE' => urlencode($payment_settings['api_signature']),
		'BUTTONSOURCE' => urlencode('PP-ECWizard'),
		);
	$data = array_merge($data,$settings);
	$ch = curl_init();
	$nvpreq = http_build_query($data, '', "&");
	curl_setopt($ch, CURLOPT_URL,$api_endpoint);
	curl_setopt($ch, CURLOPT_VERBOSE, 1);
//turning off the server and peer verification(TrustManager Concept).
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq );
	$this->log($nvpreq, 'Call data');
	$response = curl_exec($ch);
	if( ! $response ) {
		$this->log(array('error' => curl_error($ch), 'errno' => curl_errno($ch)), 'cURL failed');
	}
	$this->log($response, 'Result');
	curl_close($ch);
	$nvpResArray=$this->deformatNVP($response);
	return $nvpResArray;
}
private function deformatNVP($nvpstr){
	$intial=0;
	$nvpArray = array();
	while(strlen($nvpstr)){
//postion of Key
		$keypos= strpos($nvpstr,'=');
//position of value
		$valuepos = strpos($nvpstr,'&') ? strpos($nvpstr,'&'): strlen($nvpstr);
		/*getting the Key and Value values and storing in a Associative Array*/
		$keyval=substr($nvpstr,$intial,$keypos);
		$valval=substr($nvpstr,$keypos+1,$valuepos-$keypos-1);
//decoding the respose
		$nvpArray[urldecode($keyval)] =urldecode( $valval);
		$nvpstr=substr($nvpstr,$valuepos+1,strlen($nvpstr));
	}
	return $nvpArray;
}
private function log($data, $title = null) {
	Paymentmethods::writeLog('PayPal Express debug ('.$title.'): '.json_encode($data));
}
/* function update_subscription($profile_id){
$data = array(
'METHOD' 		=> 'UpdateRecurringPaymentsProfile',
'PROFILEID'		=>	$profile_id,
'ADDITIONALBILLINGCYCLES'	=>	1
);
$result = $this->call( $data );
printArray($result);
} */
}
