<?php
class AuthorizeAIM_payController extends PaymentController{
	private $key_name="AuthorizeAIM";
	private function getPaymentForm($order_id){
		$frm=new Form('frmPaymentForm','frmPaymentForm');
		$frm->setRequiredStarWith("x");
		$frm->setValidatorJsObjectName('system_validator');
		$frm->setExtra('class="siteForm" validator="system_validator" ');
		$frm->setAction(Utilities::generateUrl('authorizeaim_pay','send',array($order_id)));
		$frm->captionInSameCell(true);
		$frm->setFieldsPerRow(1);
		$frm->addRequiredField('<label>'.Utilities::getLabel('M_ENTER_CREDIT_CARD_NUMBER').'</label>', 'cc_number','','cc_number','class="type-bg"');
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
			$order_actual_paid = number_format(round($order_payment_amount,2),2,".","");
			if ($payment_settings["transaction_mode"]==1) {
				$url = 'https://secure.authorize.net/gateway/transact.dll';
			} elseif ($payment_settings["transaction_mode"]==0) {
				$url = 'https://test.authorize.net/gateway/transact.dll';
			}
			$data = array();
			$data['x_login'] = $payment_settings['login_id'];
			$data['x_tran_key'] = $payment_settings['transaction_key'];
			$data['x_version'] = '3.1';
			$data['x_delim_data'] = 'true';
			$data['x_delim_char'] = '|';
			$data['x_encap_char'] = '"';
			$data['x_relay_response'] = 'false';
			$data['x_first_name'] = html_entity_decode($order_info['customer_name'], ENT_QUOTES, 'UTF-8');
			$data['x_company'] = html_entity_decode($order_info['customer_name'], ENT_QUOTES, 'UTF-8');
			$data['x_address'] = html_entity_decode($order_info['customer_billing_address_1'], ENT_QUOTES, 'UTF-8').' '.html_entity_decode($order_info['customer_billing_address_2'], ENT_QUOTES, 'UTF-8');
			$data['x_city'] = html_entity_decode($order_info['customer_billing_city'], ENT_QUOTES, 'UTF-8');
			$data['x_state'] = html_entity_decode($order_info['customer_billing_state'], ENT_QUOTES, 'UTF-8');
			$data['x_zip'] = html_entity_decode($order_info['customer_billing_postcode'], ENT_QUOTES, 'UTF-8');
			$data['x_country'] = html_entity_decode($order_info['customer_billing_country'], ENT_QUOTES, 'UTF-8');
			$data['x_phone'] = $order_info['customer_phone'];
			$data['x_customer_ip'] = $this->request->server['REMOTE_ADDR'];
			$data['x_email'] = $order_info['customer_email'];
			$order_payment_gateway_description=sprintf(Utilities::getLabel('M_Order_Payment_Gateway_Description'),$order_info["site_system_name"],$order_info['invoice']);
			$data['x_description'] = html_entity_decode($order_payment_gateway_description, ENT_QUOTES, 'UTF-8');
			$data['x_amount'] = $order_actual_paid;
			$data['x_currency_code'] = $order_info["order_currency_code"];
			$data['x_method'] = 'CC';
			$data['x_type'] = 'AUTH_CAPTURE';
			$data['x_card_num'] = str_replace(' ', '', $post['cc_number']);
			$data['x_exp_date'] = $post['cc_expire_date_month'] . $post['cc_expire_date_year'];
			$data['x_card_code'] = $post['cc_cvv'];
			$data['x_invoice_num'] = $order_id;
			$data['x_solution_id'] = 'A1000015';
			/* Customer Shipping Address Fields */
			$data['x_ship_to_first_name'] = html_entity_decode($order_info['customer_shipping_name'], ENT_QUOTES, 'UTF-8');
			$data['x_ship_to_company'] = html_entity_decode($order_info['customer_shipping_name'], ENT_QUOTES, 'UTF-8');
			$data['x_ship_to_address'] = html_entity_decode($order_info['customer_shipping_address_1'], ENT_QUOTES, 'UTF-8') . ' ' . html_entity_decode($order_info['customer_shipping_address_2'], ENT_QUOTES, 'UTF-8');
			$data['x_ship_to_city'] = html_entity_decode($order_info['customer_shipping_city'], ENT_QUOTES, 'UTF-8');
			$data['x_ship_to_state'] = html_entity_decode($order_info['customer_shipping_state'], ENT_QUOTES, 'UTF-8');
			$data['x_ship_to_zip'] = html_entity_decode($order_info['customer_shipping_postcode'], ENT_QUOTES, 'UTF-8');
			$data['x_ship_to_country'] = html_entity_decode($order_info['customer_shipping_country'], ENT_QUOTES, 'UTF-8');
			if ($payment_settings["transaction_mode"]=='0') {
				$data['x_test_request'] = 'true';
			}
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_PORT, 443);
			curl_setopt($curl, CURLOPT_HEADER, 0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
			curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($curl, CURLOPT_TIMEOUT, 10);
			curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
			$response = curl_exec($curl);
			$json = array();
			if (curl_error($curl)) {
				$json['error'] = 'CURL ERROR: ' . curl_errno($curl) . '::' . curl_error($curl);
			} elseif ($response) {
				$i = 1;
				$response_info = array();
				$results = explode('|', $response);
				foreach ($results as $result) {
					$response_info[$i] = trim($result, '"');
					$i++;
				}
				if ($response_info[1] == '1') {
					$message = '';
					if (isset($response_info['5'])) {
						$message .= 'Authorization Code: ' . $response_info['5'] . "\n";
					}
					if (isset($response_info['6'])) {
						$message .= 'AVS Response: ' . $response_info['6'] . "\n";
					}
					if (isset($response_info['7'])) {
						$message .= 'Transaction ID: ' . $response_info['7'] . "\n";
					}
					if (isset($response_info['39'])) {
						$message .= 'Card Code Response: ' . $response_info['39'] . "\n";
					}
					if (isset($response_info['40'])) {
						$message .= 'Cardholder Authentication Verification Response: ' . $response_info['40'] . "\n";
					}
					if (!$payment_settings['md5_hash'] || (strtoupper($response_info[38])."#".strtoupper(md5($payment_settings['md5_hash'].$payment_settings['login_id'].$response_info[7] . $order_actual_paid)))) {
						/* Recording Payment in DB */
						if (!$orderPaymentObj->addOrderPayment($payment_settings["pmethod_name"],$response_info['7'],$order_payment_amount,Utilities::getLabel("L_Received_Payment"),$message))
							$json['error'] = "Invalid Action";
						/* End Recording Payment in DB */
					} else {
						/*  Do what ever you want to do */
					}
					$json['redirect'] = Utilities::generateUrl('custom','payment_success');
				} else {
					$json['error'] = $response_info[4];
				}
			} else {
				$json['error'] = Utilities::getLabel('M_EMPTY_GATEWAY_RESPONSE');
			}
		}	
		else{
			$json['error'] = Utilities::getLabel('M_Invalid_Request');
		}
		curl_close($curl);
		echo json_encode($json);
	}
	function check_card_type(){
		$post = Syspage::getPostedVar();		
		$res=Utilities::validate_cc_number($post['cc']);		
		echo json_encode($res); exit;
	}
}
