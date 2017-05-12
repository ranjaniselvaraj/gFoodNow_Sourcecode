<?php
class PaypalExpress extends Model {
	private $key_name="PaypalExpress";
	private $payment_settings = array();
	
	function __construct(){
		$this->db = Syspage::getdb();
		$pmObj = new SubscriptionPaymentsettings($this->key_name);
		$this->payment_settings = $pmObj->getPaymentSettings();
    }
	
	function getRecurringProfileDetail($profile_id){
		$data = array(
			'METHOD' 		=> 'GetRecurringPaymentsProfileDetails',
			'PROFILEID'		=>	$profile_id,
		);
		return $this->call( $data );
	}
	
	/* function updateRecurringPaymentProfile($profile_id){
		$data = array(
			'METHOD' 		=> 'UpdateRecurringPaymentsProfile',
			'PROFILEID'		=>	$profile_id,
			'ADDITIONALBILLINGCYCLES'	=>	0
		);
		return $this->call( $data );
	} */
	
	function recurringCancel($profile_id){
		$data = array(
			'METHOD' => 'ManageRecurringPaymentsProfileStatus',
			'PROFILEID' => urlencode($profile_id),
			'ACTION' => 'Cancel'
		);
		//Utilities::printArray($data);
		//die();
		return $this->call($data);
	}
	
	private function call($data){
		if ($this->payment_settings["transaction_mode"]==1) {
			$api_endpoint = 'https://api-3t.paypal.com/nvp';
		} else {
			$api_endpoint = 'https://api-3t.sandbox.paypal.com/nvp';
		}
		$settings = array(
			'VERSION' => urlencode('64'),
			'USER' => urlencode($this->payment_settings['api_username']),
			'PWD' => urlencode($this->payment_settings['api_password']),
			'SIGNATURE' => urlencode($this->payment_settings['api_signature']),
			'BUTTONSOURCE' => urlencode('PP-ECWizard'),
		);
		/*Utilities::printArray($settings);
		die();*/
		$data = array_merge($data,$settings);
		$nvpreq = http_build_query($data, '', "&");
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$api_endpoint);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		//turning off the server and peer verification(TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
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
	
	private function log($data, $title = null) {
		Paymentmethods::writeLog('PayPal Express debug ('.$title.'): '.json_encode($data));
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
}
?>