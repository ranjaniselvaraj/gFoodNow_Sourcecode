<?php
require_once(CONF_INSTALLATION_PATH . 'public/includes/payment-plugins/PayWithAmazon/Client.php');
class Amazon_payController extends PaymentController{
	private 
	$key_name="Amazon",
	$error = false,
	$payment_settings = false,
	$currency_code = 'usd';
	public function default_action(){
		echo "Unauthorized Action";
		Utilities::redirectUser(Utilities::generateUrl('/'));
	}
	private function getPaymentSettings(){
		$pmObj=new Paymentsettings($this->key_name);
		$payment_settings=$pmObj->getPaymentSettings();
		return $payment_settings;
	}
	function charge($order_id){
		$this->payment_settings = $this->getPaymentSettings();
		$amazon = array(
			'merchant_id'		=> trim($this->payment_settings['amazon_merchantId']),
			'access_key'		=> trim($this->payment_settings['amazon_accessKey']),
			'secret_key'		=> trim($this->payment_settings['amazon_secretKey']),
			'client_id'			=> trim($this->payment_settings['amazon_clientId']),
			'transaction_mode' 	=> trim($this->payment_settings['transaction_mode'])
			);
		$this->set('amazon', $amazon);
		if( !(strlen($amazon['merchant_id']) > 0 && strlen($amazon['access_key']) > 0 && strlen($amazon['secret_key']) > 0 && strlen($amazon['client_id']) > 0 && strlen($amazon['transaction_mode']) > 0) )
			$this->error = Utilities::getLabel('AMAZON_INVALID_PAYMENT_GATEWAY_SETUP_ERROR');
		$orderPaymentObj=new OrderPayment($order_id);
		$payment_amount=$orderPaymentObj->getOrderPaymentGatewayAmount();
		$payableAmount = $this->formatPayableAmount($payment_amount);
		$order_info=$orderPaymentObj->getOrderPrimaryinfo();
		if ( $order_info && $order_info["order_payment_status"] == 0 ) {
			$this->currency_code = strtolower($order_info["order_currency_code"]);
			$this->set('payment_amount',  $payment_amount);
		} else {
			$this->error = Utilities::getLabel('M_INVALID_ORDER_PAID_CANCELLED');
		}
		$this->set('order_info', $order_info);
		$this->set('order_id', $order_id);
		if($this->error)
			$this->set('error', $this->error);
		$queryString = getQueryStringData();
//printArray($queryString);
//die($queryString['access_token']."#");
		if(isset($queryString['token_type'])){
			if(strlen($queryString['token_type']) > 0){
//die("aa");
				$this->_template->render(true,false, 'amazon_pay/set_payment_details.php');
				return;
			}
		}
		$this->_template->render(true,false);
	}
	public function get_details($order_id){
		if(strtolower($_SERVER['REQUEST_METHOD']) != 'post') dieJsonError( 'Invalid post request.' );
		$postedData = getPostedData( );
		if(!isset($postedData['orderReferenceId']) && !isset($postedData['addressConsentToken'])) dieJsonError( 'Invalid post request.' );
		elseif(strlen($postedData['orderReferenceId']) <= 0) dieJsonError( 'Invalid post request.' );
		$this->payment_settings = $this->getPaymentSettings();
		$config = array(
			'merchant_id'		=> trim($this->payment_settings['amazon_merchantId']),
			'access_key'		=> trim($this->payment_settings['amazon_accessKey']),
			'secret_key'		=> trim($this->payment_settings['amazon_secretKey']),
			'client_id'			=> trim($this->payment_settings['amazon_clientId'])
			);
		if( !( strlen($config['merchant_id']) > 0 && strlen($config['access_key']) > 0 && strlen($config['secret_key']) > 0 && strlen($config['client_id']) > 0 ) )
			dieJsonError( Utilities::getLabel('AMAZON_INVALID_PAYMENT_GATEWAY_SETUP_ERROR') );
		$orderPaymentObj=new OrderPayment($order_id);
		$payment_amount=$orderPaymentObj->getOrderPaymentGatewayAmount();
		$payableAmount = $this->formatPayableAmount($payment_amount);
		$order_info=$orderPaymentObj->getOrderPrimaryinfo();
		if ( $order_info && $order_info["order_payment_status"] == 0 ) {
			$this->currency_code = strtolower($order_info["order_currency_code"]);
			$config['region']        = 'us';
			$config['currency_Code'] = strtoupper($this->currency_code);
			$config['sandbox']       = ( ($this->payment_settings['transaction_mode'] == 1)?false:true );	
			if(!class_exists('\PayWithAmazon\Client')) dieJsonError( Utilities::getLabel('AMAZON_INVALID_PAYMENT_GATEWAY_SETUP_ERROR') );
			$client = new \PayWithAmazon\Client($config);
			$requestParameters = array();
			$requestParameters['amount']            = $payment_amount;
			$requestParameters['currency_code']     = strtoupper($this->currency_code);
			$requestParameters['seller_order_id']   = 'order-'.$order_id;
			$requestParameters['seller_Id']         = null;
			$requestParameters['platform_id']       = null;
			$requestParameters['mws_auth_token']    = null;
			$requestParameters['amazon_order_reference_id'] = $postedData['orderReferenceId'];
			$response = $client->setOrderReferenceDetails($requestParameters);
			if($client->success){
				$requestParameters['address_consent_token'] = $postedData['addressConsentToken'];
				$response = $client->getOrderReferenceDetails($requestParameters);
				dieJsonSuccess($response->toJson());
			}
			dieJsonError($response->toJson());
		}else dieJsonError( Utilities::getLabel('M_INVALID_ORDER_PAID_CANCELLED') );
	}
	public function doPayment($order_id){
		if(strtolower($_SERVER['REQUEST_METHOD']) != 'post') dieJsonError( 'Invalid post request.' );
		$postedData = getPostedData( );
		if(!isset($postedData['amazon_order_reference_id'])) dieJsonError( 'Invalid post request.' );
		elseif(strlen($postedData['amazon_order_reference_id']) <= 0) dieJsonError( 'Invalid post request.' );
		$this->payment_settings = $this->getPaymentSettings();
		$config = array(
			'merchant_id'		=> trim($this->payment_settings['amazon_merchantId']),
			'access_key'		=> trim($this->payment_settings['amazon_accessKey']),
			'secret_key'		=> trim($this->payment_settings['amazon_secretKey']),
			'client_id'			=> trim($this->payment_settings['amazon_clientId'])
			);
		if( !( strlen($config['merchant_id']) > 0 && strlen($config['access_key']) > 0 && strlen($config['secret_key']) > 0 && strlen($config['client_id']) > 0 ) )
			dieJsonError( Utilities::getLabel('AMAZON_INVALID_PAYMENT_GATEWAY_SETUP_ERROR') );
		$orderPaymentObj=new OrderPayment($order_id);
		$payment_amount=$orderPaymentObj->getOrderPaymentGatewayAmount();
		$payableAmount = $this->formatPayableAmount($payment_amount);
		$order_info=$orderPaymentObj->getOrderPrimaryinfo(); 
		if ( $order_info && $order_info["order_payment_status"] == 0 ) {
			$this->currency_code = strtolower($order_info["order_currency_code"]);
			$config['region']        = 'us';
			$config['currency_Code'] = strtoupper($this->currency_code);
			$config['sandbox']       = ( ($this->payment_settings['transaction_mode'] == 1)?false:true );	
			if(!class_exists('\PayWithAmazon\Client')) dieJsonError( Utilities::getLabel('AMAZON_INVALID_PAYMENT_GATEWAY_SETUP_ERROR') );
			$client = new \PayWithAmazon\Client($config);
			$requestParameters = array();
			$requestParameters['amazon_order_reference_id'] = $postedData['amazon_order_reference_id'];
			$requestParameters['mws_auth_token'] = null;
			$response = $client->confirmOrderReference($requestParameters);
			$responsearray['confirm'] = json_decode($response->toJson());
			if($client->success) {
				$requestParameters['authorization_amount'] = $payment_amount;
				$requestParameters['authorization_reference_id'] = uniqid('A01_REF_');
				$requestParameters['transaction_timeout'] = 0;
				$requestParameters['capture_now'] = false;
				$requestParameters['soft_descriptor'] = null;
				$response = $client->authorize($requestParameters);
				$res = $responsearray['authorize'] = json_decode($response->toJson());
				if($client->success) {
					$requestParameters['amazon_reference_id'] = uniqid('P01_');
					$requestParameters['amazon_authorization_id'] = $res->AuthorizeResult->AuthorizationDetails->AmazonAuthorizationId;
					$requestParameters['capture_reference_id'] = uniqid('A01_');
					$requestParameters['capture_amount']= $payment_amount;
					$response = $client->capture($requestParameters);
					$responsearray['capture'] = json_decode($response->toJson());
					if($client->success) {
						$response = $client->closeOrderReference(
							array(	
								'amazon_order_reference_id' => $postedData['amazon_order_reference_id'],
								'cancelation_reason'		=> 'My cancel reason.'
								)
							);
						$responsearray['close'] = json_decode($response->toJson());
						if($client->success) {
							/* Recording Payment in DB */
							$orderPaymentObj->addOrderPayment($this->payment_settings["pmethod_name"],$postedData['amazon_order_reference_id'],$payment_amount,Utilities::getLabel("L_Received_Payment"),json_encode($responsearray));
							/* End Recording Payment in DB */
						}
						if($client->success) dieJsonSuccess( Utilities::getLabel('AMAZON_PAYMENT_COMPLETE') );
					}
				}
			}
			dieJsonError($responsearray);
		}else dieJsonError( Utilities::getLabel('M_INVALID_ORDER_PAID_CANCELLED') );
	}
	private function formatPayableAmount($amount = null){
		if($amount == null) return false;
		$amount = number_format($amount, 2, '.', '');
		return $amount*100;
	}
}
