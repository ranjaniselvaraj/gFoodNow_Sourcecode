<?php
require_once(CONF_INSTALLATION_PATH . 'public/includes/payment-plugins/pp-adaptive/paypal.adaptive.cls.php');
class PaypalAdaptive_payController extends PaymentController{
	private $key_name=CONF_PAPYAL_ADAPTIVE_KEY;
	private $order_tracking_prefix;
	function test($order_id){
		$amount = 279.95;
		$orderPaymentObj = new OrderPayment($order_id);
		$orderPaymentObj->addOrderPayment($this->key_name, "AAA11111",$amount , Utilities::getLabel("L_Received_Payment"), convertToJson($response));
	}
	function __construct($model, $controller, $action){
		parent::__construct($model, $controller, $action);
		$this->order_tracking_prefix = 'PA-YOKART-'. date('Y-m-d-H:i:s');
	}
	function charge($order_id){
				$pmObj = new PaymentSettings($this->key_name);
				if (!$payment_settings = $pmObj->getPaymentSettings()){
					Message::addErrorMessage($pmObj->getError());
					Utilities::redirectUserReferer();
				}
		//die('<pre>' . print_r($payment_settings, true) . '</pre>');
				$error = '';
				if(!($payment_data = $this->preparePaymentData($order_id, $payment_settings, $error))){
		//die($error);
					Message::addErrorMessage($error);
					Utilities::redirectUserReferer();
				}
				try{
					$pp_adp = new PaypalAdaptiveAPIClient($payment_settings['pp_adaptive_api_app_id'], $payment_settings['pp_adaptive_api_username'], $payment_settings['pp_adaptive_api_password'], $payment_settings['pp_adaptive_api_signature'], !$payment_settings['pp_adaptive_transaction_mode']);
					$response = $pp_adp->execute($payment_data, 'Pay');
		/*printArray($response);
		die();*/
		if(array_key_exists('responseEnvelope.ack', $response) && 'Success' == $response['responseEnvelope.ack'] && array_key_exists('payKey', $response)){
			$chained = -1; /* For no execution of chained payments */
			$exe_date = '';
			if('PAY_PRIMARY' == $payment_data['actionType']){
				$chained = 0;
				$delay_days = intval($payment_settings['pp_adaptive_chained_delay_by_days']);
				if($delay_days < 1){
					$delay_days = 1;
				}
				$exe_date = date('Y-m-d H:i:s', strtotime("+$delay_days day"));
			}
			$data = array(
				'pay_key'=>$response['payKey'],
				'chained'=>$chained,
				'execution_date'=>$exe_date,
				);
			$this->Paypaladaptive_pay->addAdaptivePayEntry($order_id, $data);
			$this->Paypaladaptive_pay->saveTransaction($order_id, $payment_data['actionType'], $payment_data, $response);
			Utilities::redirectUser($pp_adp->getRedirectUrl($response['payKey']));
		}else{
		/* echo '<pre>' . print_r($response, true) . '</pre>';
		die(); */
		$error = '';
		if(array_key_exists('payErrorList', $response) && is_string($response['payErrorList'])){
			$error = $response['payErrorList'];
		}elseif(array_key_exists('error(0).message', $response) && is_string($response['error(0).message'])){
			$error = $response['error(0).message'];
		}else{
			$error = 'Payment execution failed due to some technical reasons!';
		}
		Message::addErrorMessage('Error: ' . $error);
		Utilities::redirectUserReferer();
		}
		}catch(Exception $e){
			die($e->getMessage());
			Message::addErrorMessage($e->getMessage());
			Utilities::redirectUserReferer();
		}
	}
	private function getIPNPostedData(){
		$postData = file_get_contents('php://input');
		return PaypalAdaptiveAPIClient::parseHttpResponse($postData);
	}
	function callback($payKey){
		$post = $this->getIPNPostedData();
		foreach ($post as $key => $value) {
			$request .= "\n" . $key . "=" . $value;
		}
		mail("ravibhalla@dummyid.com","Paypal Adaptive IPN",$request);
		if(!array_key_exists('transaction_type', $post) || !array_key_exists('pay_key', $post)){
			exit(0);
		}
		$pmObj = new Paymentsettings($this->key_name);
		if(!$payment_settings=$pmObj->getPaymentSettings()){
			exit('Payment Settings not found!');
		}
		$error = '';
		if(!$this->validateIPN($post, !$payment_settings['pp_adaptive_transaction_mode'], $error)){
			exit($error);
		}
		try{
			$pp_adp = new PaypalAdaptiveAPIClient($payment_settings['pp_adaptive_api_app_id'], $payment_settings['pp_adaptive_api_username'], $payment_settings['pp_adaptive_api_password'], $payment_settings['pp_adaptive_api_signature'], !$payment_settings['pp_adaptive_transaction_mode']);
			$payment_data = array(
				'requestEnvelope.errorLanguage'=>'en_US',
				'payKey'=>$post['pay_key'],
				);
			$response = $pp_adp->execute($payment_data, 'PaymentDetails');
			if(array_key_exists('responseEnvelope.ack', $response) && 'Success' == $response['responseEnvelope.ack'] && array_key_exists('payKey', $response)){
				$resp_arr = $pp_adp->mapResponseToArray($response);
				$trackingId = $resp_arr['trackingId'];
				if(strlen($trackingId) < strlen($this->order_tracking_prefix)){
					$trackingId = $post['tracking_id'];
				}
				$track_arr_order = explode("#",$trackingId);
				if (empty($track_arr_order[1])){
					exit('Invalid Order!');
				}
				$order_id = $track_arr_order[1];
				$this->Paypaladaptive_pay->saveTransaction($order_id, $response['actionType'], $payment_data, $response);
				/* ======== Get Order Data ======= */
				$orderPaymentObj = new OrderPayment($order_id);
				$total_chargeable_amount = $orderPaymentObj->getOrderPaymentGatewayAmount();
				$order_info = $orderPaymentObj->getOrderPrimaryinfo();
				/* ======== Order Data End ======= */
				$pay_info = $resp_arr['paymentInfoList']['paymentInfo'];
				foreach($pay_info as $pinfo){
					$incr++;
					if('COMPLETED' == $pinfo['transactionStatus']){
						if ($post['action_type']=='PAY_PRIMARY'){
							if('true' == $pinfo['receiver']['primary'] && $order_info["order_payment_status"] == 0){
								$orderPaymentObj->addOrderPayment($this->key_name, $pinfo['transactionId'], $pinfo['receiver']['amount'], Utilities::getLabel("L_Received_Payment"), convertToJson($response));
								$this->Paypaladaptive_pay->updateAdaptivePayments($order_id, $resp_arr['payKey'], array('PRIMARY_STATUS'=>1));
							}elseif('false' == $pinfo['receiver']['primary']){
								$this->Paypaladaptive_pay->doDebitTransactionForPaidAmountToSeller($pinfo, $order_id);
							}
						}elseif ($post['action_type']=='PAY'){
							if($order_info["order_payment_status"] == 0){
								$orderPaymentObj->addOrderPayment($this->key_name, $pinfo['transactionId'], $pinfo['receiver']['amount'], Utilities::getLabel("L_Received_Payment"), convertToJson($response));
								$this->Paypaladaptive_pay->updateAdaptivePayments($order_id, $resp_arr['payKey'], array('PRIMARY_STATUS'=>1));
							}
						}
					}
				}
			}
		}catch(Exception $e){
			exit($e->getMessage());
		}
	}
	private function validateIPN($post, $sandbox = false, &$error){
		$request = 'cmd=_notify-validate';
		foreach ($post as $key => $value) {
			$request .= '&' . $key . '=' . urlencode(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
		}
		$curl = curl_init('https://www.paypal.com/cgi-bin/webscr');
		if($sandbox){
			$curl = curl_init('https://www.sandbox.paypal.com/cgi-bin/webscr');
		}
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($curl);
		if (strcmp($response, 'VERIFIED') == 0) {
			return true;
		}
		$error = 'IPN is invalid!';
		return false;
	}
	function cron(){
		$output = '';
		$this->doExecuteChainedPayments($output);
		echo $output;
		exit(0);
	}
	private function doExecuteChainedPayments(&$output){
		$orders_to_be_executed = $this->Paypaladaptive_pay->getDueChainedPayments();
		if(!$orders_to_be_executed){
			$output = 'No due chained payment found!';
			return false;
		}
		set_time_limit(0);
		$pmObj = new PaymentSettings($this->key_name);
		if (!$payment_settings = $pmObj->getPaymentSettings()){
			$output = 'Payment Setting not found!';
			return false;
		}
		foreach($orders_to_be_executed as $opay){
			$output .= 'Execution of PayKey: ' . $opay['ppadappay_pay_key'] . ', for OrderId: #' . $opay['ppadappay_order_id'] . ' has started...'. PHP_EOL;
			if($this->executeSinglePay($opay, $payment_settings)){
				$output .= 'Executed Successfully.' . PHP_EOL;
			}else{
				$output .= 'Execution Failed.' . PHP_EOL;
			}
			$output .= PHP_EOL;
		}
		return true;
	}
	
	private function executeSinglePay($opay, $payment_settings){
			$order_id = $opay['ppadappay_order_id'];
			if($order_id < 1 || !is_string($opay['ppadappay_pay_key']) || strlen($opay['ppadappay_pay_key']) < 4){
				return false;
			}
			$opObj = new OrderPayment($order_id);
			$order_info = $opObj->getOrderPrimaryinfo();
		//die('<pre>' . print_r($opay, true) . print_r($order_info, true) . '</pre>');
			if($order_info['order_payment_status'] != 1 /* || $order_info['order_status'] != 7 */){ 
				return false;
			}
			$order_products = $opObj->getOrderProductsInfo($order_id);
			$this->Paypaladaptive_pay->updateAdaptivePayEntry($order_id, $opay['ppadappay_pay_key'], 1);
			foreach($order_products as $pKey=>$product){
				if (!in_array($product['product_order_status'],(array)$opObj->getVendorOrderPaymentCreditedStatuses()) || ($product['product_refund_qty'] > 0) || (0 < $product['product_refund_amount']) ) {
					$child_order_pending=true;
				}else{
					$opObj->addChildOrderHistory($pKey,$product['product_order_status'],'',false,'',true);
				}
			}
			if ($child_order_pending){
				return false;
			}
			try{
				$pp_adp = new PaypalAdaptiveAPIClient($payment_settings['pp_adaptive_api_app_id'], $payment_settings['pp_adaptive_api_username'], $payment_settings['pp_adaptive_api_password'], $payment_settings['pp_adaptive_api_signature'], !$payment_settings['pp_adaptive_transaction_mode']);
				$payment_data = array(
					'requestEnvelope.errorLanguage'=>'en_US',
					'payKey'=>$opay['ppadappay_pay_key'],
					'actionType'=>'PAY'
					);
				$response = $pp_adp->execute($payment_data, 'ExecutePayment');
		//echo '<pre>' . print_r($payment_data, true) . '</pre>';
				if(array_key_exists('responseEnvelope.ack', $response) && 'Success' == $response['responseEnvelope.ack'] && array_key_exists('paymentExecStatus', $response)){
					$this->Paypaladaptive_pay->updateAdaptivePayEntry($order_id, $payment_data['payKey'], 4);
					$this->Paypaladaptive_pay->saveTransaction($order_id, $payment_data['actionType'], $payment_data, $response);
					return true;
				}else{
					$this->Paypaladaptive_pay->updateAdaptivePayEntry($order_id, $payment_data['payKey'], 2);
					$this->Paypaladaptive_pay->saveTransaction($order_id, $payment_data['actionType'], $payment_data, $response);
		//echo '<pre>' . print_r($response, true) . '</pre>';
					$error = '';
					if(array_key_exists('payErrorList', $response) && is_string($response['payErrorList'])){
						$error = $response['payErrorList'];
					}elseif(array_key_exists('error(0).message', $response) && is_string($response['error(0).message'])){
						$error = $response['error(0).message'];
					}else{
						$error = 'Payment execution failed due to some technical reasons!';
					}
					echo $error . PHP_EOL;
					return false;
				}
			}catch(Exception $e){
		//die($e->getMessage());
				echo $e->getMessage() . PHP_EOL;
				return false;
			}
			return false;
	}
	private function preparePaymentData($order_id, $payment_settings, &$error){
		$orderPaymentObj = new OrderPayment($order_id);
		$total_chargeable_amount = $orderPaymentObj->getOrderPaymentGatewayAmount();
		$order_info = $orderPaymentObj->getOrderPrimaryinfo();
		if (!$order_info || $order_info["order_payment_status"] != 0){
			$error = Utilities::getLabel('M_INVALID_ORDER_PAID_CANCELLED');
			return false;
		}
		$order_products = $orderPaymentObj->getOrderProductsInfo($order_id);
		$receivers = array();
		$products_total = 0;
		$total_commission = 0;
		$total_payable_to_vendors = 0;
		$currency_issue = false;
		foreach($order_products as $product){
			if($order_info['order_currency_code'] != $product['product_currency_code']){
				$currency_issue = true;
				break;
			}
			$products_total += $product['product_total'];
			$total_commission += $product['product_portal_commission_amount'];
			$total_tax += $product['product_tax_amount'];
			$total_payable_to_vendors += $product['product_vendor_payable_amount'];
			$receivers[$product['product_shop_id']]['amount'] += $product['product_vendor_payable_amount'];
			$receivers[$product['product_shop_id']]['email'] = $product['product_shop_owner_email'];
			$receivers[$product['product_shop_id']]['invoice_id'] = $product['invoice_id'];
			$receivers[$product['product_shop_id']]['primary'] = 'false';
		}
		if($currency_issue === true){
			$error = 'Currency code mismatch!';
			return false;
		}
	
	$data = array(
		'trackingId'=>$this->order_tracking_prefix ."#". $order_id,
		'currencyCode'=>$order_info['order_currency_code'],
		'feesPayer'=>($this->validatePayFee($payment_settings['pp_adaptive_pay_fee'])?$payment_settings['pp_adaptive_pay_fee']:'EACHRECEIVER'),
		'requestEnvelope.errorLanguage'=>'en_US',
		'returnUrl'=>Utilities::generateAbsoluteUrl('custom', 'payment_success', array($order_id)),
		'cancelUrl'=>Utilities::generateAbsoluteUrl('cart'),
		'ipnNotificationUrl'=>Utilities::generateAbsoluteUrl('paypaladaptive_pay', 'callback'),
		);
	$data['receiverList.receiver(0).amount'] = $total_chargeable_amount;
	$data['receiverList.receiver(0).email'] = $payment_settings['pp_adaptive_merchant_email'];
	if($total_payable_to_vendors > $total_chargeable_amount){
		$data['actionType'] = 'PAY';
		$data['feesPayer']='EACHRECEIVER';
	}else{
		$data['receiverList.receiver(0).invoiceId'] = $this->order_tracking_prefix . 'INV-' . $order_id;
		$data['receiverList.receiver(0).primary'] = 'true';
		if(sizeof($receivers) > 0 && intval($payment_settings['pp_adaptive_chained_delay_by_days']) > 0){
			$data['actionType'] = 'PAY_PRIMARY';
		}else{
			$data['actionType'] = 'PAY';
		}
		$total_secondary_receivers = sizeof($receivers);
		if($total_secondary_receivers > 8){
			/* With PayPal Adaptive, we can only pay 9 receivers at a time, One primary plus 8 secondary receivers. */
			$total_secondary_receivers = 8;
		}
		$receiver_ids = array_keys($receivers);
		$receiver_paypal_accounts = array();
		if(sizeof($receiver_ids)){
			$shopObj = new Shops();
			$receiver_paypal_accounts = $shopObj->getPaypalAccountForMultipleShops($receiver_ids);
		}
		$i=1;
		foreach($receivers as $shop_id => $receiver){
			if($i > $total_secondary_receivers){
				break;
			}
			$data['receiverList.receiver(' . $i . ').amount'] = $receiver['amount'];
			if(array_key_exists($shop_id, $receiver_paypal_accounts)){
				$data['receiverList.receiver(' . $i . ').email'] = $receiver_paypal_accounts[$shop_id];
			}else{
				$data['receiverList.receiver(' . $i . ').email'] = $receiver['email'];
			}
			$data['receiverList.receiver(' . $i . ').invoiceId'] = $shop_id . '-' . $receiver['invoice_id'];
			$data['receiverList.receiver(' . $i . ').primary'] = 'false';
			$i++;
		}
	}
	return $data;
	}
	private function validatePayFee($pay_fee){
		$paypal_fee_pay_opts = array(
			'EACHRECEIVER' => 'Each Receiver',
			'PRIMARYRECEIVER' => 'Primary Receiver',
			'SENDER' => 'Sender',
			'SECONDARYONLY' => 'Secondary Only'
			);
		return array_key_exists($pay_fee, $paypal_fee_pay_opts);
	}
	private function prepareResponseStringToArray($string){
		$return = array();
		$pieces = explode('&',$string);
		foreach($pieces as $piece){
			$keyval = explode('=',$piece);
			if (count($keyval) > 1){
				$return[$keyval[0]] = $keyval[1];
			} else {
				$return[$keyval[0]] = '';
			}
		}
		return $return;
	}
}
