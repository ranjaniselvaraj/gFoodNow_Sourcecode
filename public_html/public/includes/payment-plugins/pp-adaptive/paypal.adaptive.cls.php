<?php
require_once dirname(__FILE__) . '/pp.user.agent.cls.php';

final class PaypalAdaptiveAPIClient{
	const API_URL_LIVE = 'https://svcs.paypal.com/';
	const REDIRECT_URL_LIVE = 'https://www.paypal.com/cgi-bin/webscr';
	
	const API_URL_SANDBOX = 'https://svcs.sandbox.paypal.com/';
	const REDIRECT_URL_SANDBOX = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
	
	
	private $sdk_name = 'adaptivepayments-php';
	private $sdk_version = '1.0';
	
	protected $api_app_id;
	protected $api_username;
	protected $api_password;
	protected $api_signature;
	protected $api_sandbox;
	
	public function __construct($app_id, $username, $password, $signature, $sandbox = false){
		if(!is_string($app_id) || strlen($app_id) < 5){
			throw new Exception('Invalid Application ID!');
		}
		
		if(!is_string($username) || strlen($username) < 1){
			throw new Exception('Invalid API Username!');
		}
		
		if(!is_string($password) || strlen($password) < 1){
			throw new Exception('Invalid API Password!');
		}
		
		if(!is_string($signature) || strlen($signature) < 5){
			throw new Exception('Invalid API Signature!');
		}
		
		$this->api_app_id = $app_id;
		$this->api_username = $username;
		$this->api_password = $password;
		$this->api_signature = $signature;
		$this->api_sandbox = $sandbox;
	}
	
	public function execute($data_arr, $method){
		if(0 == sizeof($data_arr)){
			throw new Exception('No Data To Post.');
		}
		
		$data_arr_processed = array();
		foreach($data_arr as $k => $v){
			$data_arr_processed[] = $k . '=' . urlencode($v);
		}
		$nvp_req_data = implode('&', $data_arr_processed);
		
		return $this->ppHttpPost($nvp_req_data, $method);
	}
	
	public function getRedirectUrl($pay_key){
		$url = self::REDIRECT_URL_SANDBOX;
		if (!$this->api_sandbox) {
    		$url = self::REDIRECT_URL_LIVE;
  		}
		
		$params = array(
			'cmd' => '_ap-payment',
			'paykey' => $pay_key
		);
		
		return $url . '?' . http_build_query($params);
	}
	
	/**
     * Get the local IP address. The client address is a required
     * request parameter for some API calls
     */
    public static function getLocalIPAddress(){
        if (array_key_exists("SERVER_ADDR", $_SERVER) && self::isIPv4($_SERVER['SERVER_ADDR'])) {
            // SERVER_ADDR is available only if we are running the CGI SAPI
            return $_SERVER['SERVER_ADDR'];
        } else if(function_exists("gethostname") && self::isIPv4(gethostbyname(gethostname()))) {
            return gethostbyname(gethostname());
        } else {
            // fallback if nothing works
            return "127.0.0.1";
        }
    }

    /**
     * Determines if valid IPv4 or not
     *
     * @param $ip
     * @return bool
     */
    public static function isIPv4($ip) {
        return filter_var($ip, FILTER_VALIDATE_IP , FILTER_FLAG_IPV4);
    }
	
	private function validateRequestMethod($method){
		return in_array($method, array(
			'Pay',
			'PaymentDetails',
			'ExecutePayment',
			/* 'Refund', */
		));
	}
	
	private function getDeviceIPAddress(){
		return self::getLocalIPAddress();
	}
	
	private function getDeviceUserAgent(){
		return PPUserAgent::getValue($this->sdk_name, $this->sdk_version);
	}
	
	private function getRequestHeaders(){
		return array(
			"X-PAYPAL-REQUEST-DATA-FORMAT: NV",
			"X-PAYPAL-RESPONSE-DATA-FORMAT: NV",
			"X-PAYPAL-APPLICATION-ID: " . $this->api_app_id,
			"X-PAYPAL-SECURITY-USERID: " . $this->api_username,
			"X-PAYPAL-SECURITY-PASSWORD: " . $this->api_password,
			"X-PAYPAL-SECURITY-SIGNATURE: " . $this->api_signature,
			"X-PAYPAL-DEVICE-IPADDRESS: " . $this->getDeviceIPAddress(),
			"X-PAYPAL-REQUEST-SOURCE: adaptivepayments-php",
			"User-Agent: " . $this->getDeviceUserAgent(),
		);
	}
	
	public function mapResponseToArray($raw_array){
		$return = array();
		foreach($raw_array as $k=>$v){
			$k = $k;
			$arr = explode('.', $k);
			$arr[] = $v;
			$return[] = $arr;
		}
		
		$final = array();
		foreach($return as $val){
			$str = '';
			for($i=0; $i<(sizeof($val)-1); $i++){
				if(strpos($val[$i], '(') !== false){
					$val[$i] = str_replace('(', "']['", $val[$i]);
					$val[$i] = str_replace(')', "", $val[$i]);
				}
				$str .=	"['" . $val[$i] . "']";	
			}
			eval('$final' . $str . '="' . $val[sizeof($val)-1] . '";');
		}
		return $final;
	}
	
	public static function parseHttpResponse($raw_http_response){
		$httpResponseAr = explode("&", $raw_http_response);

		$httpParsedResponseAr = array();
		foreach ($httpResponseAr as $i => $value) {
			$tmpAr = explode("=", $value);
			if(sizeof($tmpAr) > 1) {
				$httpParsedResponseAr[$tmpAr[0]] = urldecode($tmpAr[1]);
			}
		}
		return $httpParsedResponseAr;
	}
	
	private function getDefaultCurlOptions(){
		/*return array(
			CURLOPT_SSLVERSION => 1,
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_TIMEOUT        => 60,	
			CURLOPT_USERAGENT      => 'PayPal-PHP',
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_SSL_VERIFYPEER => 1,
			//CURLOPT_SSL_CIPHER_LIST => 'TLSv1',
		);*/
		
		$curlopt =  array(
				CURLOPT_SSLVERSION => 1,
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_RETURNTRANSFER => TRUE,
				CURLOPT_TIMEOUT        => 60,	
				CURLOPT_USERAGENT      => 'PayPal-PHP',
				CURLOPT_SSL_VERIFYHOST => 2,
				CURLOPT_SSL_VERIFYPEER => 1,
				CURLOPT_SSL_CIPHER_LIST => 'TLSv1'
				);
		$curl = curl_version();
		$sslVersion = isset($curl['ssl_version']) ? $curl['ssl_version'] : '';
		if (substr_compare($sslVersion, "NSS/", 0, strlen("NSS/")) === 0) {
			unset($curlopt[CURLOPT_SSL_CIPHER_LIST]);
		}
		return $curlopt;
	}
	
	private function ppHttpPost($request, $method){
		if(!function_exists("curl_init")){
			throw new Exception("Curl module is not available on this system");
		}
		
		if(!$this->validateRequestMethod($method)){
			throw new Exception("Invalid Request Methodss: $method");
		}
		
		$url = self::API_URL_SANDBOX;
		if (!$this->api_sandbox) {
    		$url = self::API_URL_LIVE;
  		}
		
		$headers = $this->getRequestHeaders();
		$curl_opts = $this->getDefaultCurlOptions();
		
		$endpoint = $url . 'AdaptivePayments/' . $method;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $endpoint);
		curl_setopt_array($ch, $curl_opts);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		$result = curl_exec($ch);
		
		if (curl_errno($ch) == 60) {
			$cert = dirname(__FILE__) . '/cacert.pem';
			if(file_exists($cert)){
				curl_setopt($ch, CURLOPT_CAINFO, $cert);
				$result = curl_exec($ch);
			}
		}
		
		if(!$result) {
			$ex = "$method Failed: ".curl_error($ch).'('.curl_errno($ch).')';
			curl_close($ch);
			throw new Exception($ex);
		}
		
		curl_close($ch);
		
		$parsedResponseAr = $this->parseHttpResponse($result);
		
		if((0 == sizeof($parsedResponseAr)) || !array_key_exists('responseEnvelope.ack', $parsedResponseAr)) {
			throw new Exception("Invalid HTTP Response for POST request to $endpoint.");
		}
		
		return $parsedResponseAr;
	}
}