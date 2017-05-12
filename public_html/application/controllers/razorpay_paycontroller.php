<?php
class Razorpay_payController extends PaymentController{
	private $key_name="Razorpay";
	private function getPaymentForm($order_id){
		$pmObj=new Paymentsettings($this->key_name);
		$payment_settings=$pmObj->getPaymentSettings();
		$frm=new Form('razorpay-form','razorpay-form');
		$frm->setRequiredStarWith('x');
		$frm->captionInSameCell(true);
		$frm->setExtra('class="siteForm" validator="system_validator" ');
		$frm->setAction(Utilities::generateAbsoluteUrl('razorpay_pay','callback'));
		$frm->setFieldsPerRow(1);
		$frm->addHiddenField('', 'razorpay_payment_id','','razorpay_payment_id');
		$frm->addHiddenField('', 'merchant_order_id',$order_id,'merchant_order_id');
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
		$this->set('payment_settings', $payment_settings);
		$this->_template->render(true,false);	
	}
	public function callback() {
		$pmObj=new Paymentsettings($this->key_name);
		$payment_settings=$pmObj->getPaymentSettings();
		$post = Syspage::getPostedVar();
		foreach ($post as $key => $value) {
			$request .= '&' . $key . '=' . urlencode(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
		}
		$razorpay_payment_id = $post['razorpay_payment_id'];
		$merchant_order_id = (isset($post['merchant_order_id']))?$post['merchant_order_id']:0;
		$orderPaymentObj=new OrderPayment($merchant_order_id);
		$payment_gateway_charge=$orderPaymentObj->getOrderPaymentGatewayAmount();
		$payment_gateway_charge_in_paisa = $payment_gateway_charge*100;
		if ($payment_gateway_charge>0){
			$success = false;
			$error = "";
			try {
				$url = 'https://api.razorpay.com/v1/payments/'.$razorpay_payment_id.'/capture';
				$fields_string="amount=$payment_gateway_charge_in_paisa";
//cURL Request
				$ch = curl_init();
//set the url, number of POST vars, POST data
				curl_setopt($ch,CURLOPT_URL, $url);
				curl_setopt($ch,CURLOPT_USERPWD, $payment_settings['merchant_key_id'] . ":" . $payment_settings['merchant_key_secret']);
				curl_setopt($ch,CURLOPT_TIMEOUT, 60);
				curl_setopt($ch,CURLOPT_POST, 1);
				curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
				curl_setopt($ch,CURLOPT_RETURNTRANSFER, TRUE);
//execute post
				$result = curl_exec($ch);
				$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				if($result === false) {
					$success = false;
					$error = 'Curl error: ' . curl_error($ch);
				}
				else {
					$response_array = json_decode($result, true);
//Check success response
					if($http_status === 200 and isset($response_array['error']) === false){
						$success = true;    
					}
					else {
						$success = false;
						if(!empty($response_array['error']['code'])) {
							$error = $response_array['error']['code'].":".$response_array['error']['description'];
						}
						else {
							$error = "RAZORPAY_ERROR:Invalid Response <br/>".$result;
						}
					}
				}
//close connection
				curl_close($ch);
			}
			catch (Exception $e) {
				$success = false;
				$error ="ERROR:Request to Razorpay Failed";
			}
			if ($success === true) {
				$orderPaymentObj->addOrderPayment($payment_settings["pmethod_name"],$razorpay_payment_id,$payment_gateway_charge,Utilities::getLabel("L_Received_Payment"),'Payment Successful. Razorpay Payment Id:'.$razorpay_payment_id);
				Utilities::redirectUser(Utilities::generateUrl('custom','payment_success'));
			}else{
				$orderPaymentObj->addOrderPaymentComments($error.' Payment Failed! Check Razorpay dashboard for details of Payment Id:'.$razorpay_payment_id);
				Utilities::redirectUser(Utilities::generateUrl('custom','payment_failed'));
			}
		}else{
			Utilities::show404();
		}
	}
}
