<?php
class PaypalStandard_settingsController extends PaymentmethodsController {
	
	private $key_name="PaypalStandard";
	
	
	protected function getSettingsForm() {
		global $payment_status_arr;
        $frm = new Form('frmPaymentMethods','frmPaymentMethods');
		$frm->setExtra(' validator="PaymentMethodfrmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('PaymentMethodfrmValidator');
		$fld=$frm->addEmailField('Merchant Email', 'merchant_email');
		$fld->requirements()->setRequired();
		$fld->html_after_field='<small>PayPal Email ID of merchant\'s account which would be used to process the payment.</small>';
		
		
		$fld=$frm->addSelectBox('Transaction Mode','transaction_mode',array(0=>"Test/Sandbox","1"=>"Live"));
		$fld->requirements()->setRequired();
		$fld->html_after_field='<small>Transaction modes allow admin to switch between live and testing account. Testing account is used to test the payment flow through PayPal.</small>';
		
		
		$fld=$frm->addSelectBox('Order Status (Initial)','order_status_initial',$payment_status_arr);
		$fld->requirements()->setRequired();
		$fld->html_after_field='<small>Set order status when order is initiated and sent to paypal.</small>';
		
		$fld=$frm->addSelectBox('Order Status (Pending)','order_status_pending',$payment_status_arr);
		$fld->requirements()->setRequired();
		$fld->html_after_field='<small>After payment is processed on Paypal, Paypal API sends back different Payment Status e.g. Pending, Processed, Completed, etc. Setup the order status in our system when "Pending" is returned from PayPal in Response.</small>';
		
		$fld=$frm->addSelectBox('Order Status (Processed)','order_status_processed',$payment_status_arr);
		$fld->requirements()->setRequired();
		$fld->html_after_field='<small>After payment is processed on Paypal, Paypal API sends back different Payment Status e.g. Pending, Processed, Completed, etc. Setup the order status in our system when "Processed" is returned from PayPal in Response.</small>';
		
		$fld=$frm->addSelectBox('Order Status (Completed)','order_status_completed',$payment_status_arr);
		$fld->requirements()->setRequired();
		$fld->html_after_field='<small>After payment is processed on Paypal, Paypal API sends back different Payment Status e.g. Pending, Processed, Completed, etc. Setup the order status in our system when "Completed" is returned from PayPal in Response.</small>';
		
		$fld=$frm->addSelectBox('Order Status (Others)','order_status_others',$payment_status_arr);
		$fld->requirements()->setRequired();
		$fld->html_after_field='<small>After payment is processed on Paypal, Paypal API sends back different Payment Status e.g. Pending, Processed, Completed, etc. Setup the order status in our system when other than defined above is returned from PayPal in Response.</small>';
		
		$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="20%"');
        return $frm;
    }
	
	function default_action() {
		$pmObj=new Paymentsettings($this->key_name);
		if (!$payment_settings=$pmObj->getPaymentSettings()){
			Message::addErrorMessage($pmObj->getError());
			Utilities::redirectUserReferer();
		}
		$frm = $this->getSettingsForm();
		$frm->fill($payment_settings);
			$post = Syspage::getPostedVar();
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				if(!$frm->validate($post)){
					Message::addErrorMessage($frm->getValidationErrors());
				}else{
						if (!$pmObj->saveSetting($post)){
							Message::addErrorMessage($pmObj->getError());
							//break;
						}
						Message::addMessage('Success: Payment method details added/updated successfully.');
						Utilities::reloadPage();
				}
				$frm->fill($post);
			}
       	$this->set('frm', $frm);
		$this->set('payment_settings', $payment_settings);
        $this->_template->render(true,true);
    }
	
	protected function getPPCSettingsForm() {
		global $txn_status_arr;
        $frm = new Form('frmPaymentMethods','frmPaymentMethods');
		$frm->setExtra(' validator="PaymentMethodfrmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('PaymentMethodfrmValidator');
		$fld=$frm->addEmailField('Merchant Email', 'merchant_email');
		$fld->requirements()->setRequired();
		$fld->html_after_field='<small>PayPal Email ID of merchant\'s account which would be used to process the payment.</small>';
		
		
		$fld=$frm->addSelectBox('Transaction Mode','transaction_mode',array(0=>"Test/Sandbox","1"=>"Live"));
		$fld->requirements()->setRequired();
		$fld->html_after_field='<small>Transaction modes allow admin to switch between live and testing account. Testing account is used to test the payment flow through PayPal.</small>';
		
		
		$fld=$frm->addSelectBox('Txn Status (Initial)','txn_status_initial',$txn_status_arr);
		$fld->requirements()->setRequired();
		$fld->html_after_field='<small>Set Txn status when order is initiated and sent to paypal.</small>';
		
		$fld=$frm->addSelectBox('Txn Status (Pending)','txn_status_pending',$txn_status_arr);
		$fld->requirements()->setRequired();
		$fld->html_after_field='<small>After payment is processed on Paypal, Paypal API sends back different Payment Status e.g. Pending, Processed, Completed, etc. Setup the Txn status in our system when "Pending" is returned from PayPal in Response.</small>';
		
		$fld=$frm->addSelectBox('Txn Status (Processed)','txn_status_processed',$txn_status_arr);
		$fld->requirements()->setRequired();
		$fld->html_after_field='<small>After payment is processed on Paypal, Paypal API sends back different Payment Status e.g. Pending, Processed, Completed, etc. Setup the Txn status in our system when "Processed" is returned from PayPal in Response.</small>';
		
		$fld=$frm->addSelectBox('Txn Status (Completed)','txn_status_completed',$txn_status_arr);
		$fld->requirements()->setRequired();
		$fld->html_after_field='<small>After payment is processed on Paypal, Paypal API sends back different Payment Status e.g. Pending, Processed, Completed, etc. Setup the Txn status in our system when "Completed" is returned from PayPal in Response.</small>';
		
		$fld=$frm->addSelectBox('Txn Status (Others)','txn_status_others',$txn_status_arr);
		$fld->requirements()->setRequired();
		$fld->html_after_field='<small>After payment is processed on Paypal, Paypal API sends back different Payment Status e.g. Pending, Processed, Completed, etc. Setup the Txn status in our system when other than defined above is returned from PayPal in Response.</small>';
		
		$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="20%"');
        return $frm;
    }
	
	function ppc_payment_settings() {
		$pmObj=new PPCPaymentsettings($this->key_name);
		if (!$payment_settings=$pmObj->getPaymentSettings()){
			Message::addErrorMessage($pmObj->getError());
			Utilities::redirectUserReferer();
		}
		$frm = $this->getPPCSettingsForm();
		$frm->fill($payment_settings);
			$post = Syspage::getPostedVar();
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				if(!$frm->validate($post)){
					Message::addErrorMessage($frm->getValidationErrors());
				}else{
						if (!$pmObj->saveSetting($post)){
							Message::addErrorMessage($pmObj->getError());
							//break;
						}
						Message::addMessage('Success: Payment method details added/updated successfully.');
						Utilities::reloadPage();
				}
				$frm->fill($post);
			}
       	$this->set('frm', $frm);
		$this->set('payment_settings', $payment_settings);
        $this->_template->render(true,true);
    }
	
	
	protected function getSubscriptionSettingsForm() {
		global $package_order_status_arr;
		$packagesObj = new SubscriptionPackages();
		$package_order_status_arr = $packagesObj->getPackageOrderStatusAssoc();
        $frm = new Form('frmPaymentMethods','frmPaymentMethods');
		$frm->setExtra(' validator="PaymentMethodfrmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('PaymentMethodfrmValidator');
		$fld=$frm->addEmailField('Merchant Email', 'merchant_email');
		$fld->requirements()->setRequired();
		$fld->html_after_field='<small>PayPal Email ID of merchant\'s account which would be used to process the payment.</small>';
		
		$frm->addTextBox('API Username', 'pp_api_username')->requirements()->setRequired();
		$frm->addTextBox('API Password', 'pp_api_password')->requirements()->setRequired();
		$frm->addTextBox('API Signature', 'pp_api_signature')->requirements()->setRequired();


		$fld=$frm->addSelectBox('Transaction Mode','transaction_mode',array(0=>"Test/Sandbox","1"=>"Live"));
		$fld->requirements()->setRequired();
		
		
		$fld=$frm->addSelectBox('Subscription Status (Initial)','order_status_initial',$package_order_status_arr)->requirements()->setRequired();
		
		$fld=$frm->addSelectBox('Subscription Status (Sign Up)','order_status_signup',$package_order_status_arr)->requirements()->setRequired();
		
		$fld=$frm->addSelectBox('Subscription Status (Payment Received)','order_status_payment',$package_order_status_arr)->requirements()->setRequired();
		
		$fld=$frm->addSelectBox('Subscription Status (Failed)','order_status_failed',$package_order_status_arr)->requirements()->setRequired();
		$fld=$frm->addSelectBox('Subscription Status (Cancelled)','order_status_cancelled',$package_order_status_arr)->requirements()->setRequired();
		
		$fld=$frm->addSelectBox('Subscription Status (End of Time)','order_status_eot',$package_order_status_arr)->requirements()->setRequired();
		
		$fld=$frm->addSelectBox('Subscription Status (Others)','order_status_others',$package_order_status_arr)->requirements()->setRequired();
		
		
		$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="20%"');
        return $frm;
    }
	
	function subscription_payment_settings() {
		$pmObj=new SubscriptionPaymentsettings($this->key_name);
		if (!$payment_settings=$pmObj->getPaymentSettings()){
			Message::addErrorMessage($pmObj->getError());
			Utilities::redirectUserReferer();
		}
		$frm = $this->getSubscriptionSettingsForm();
		$frm->fill($payment_settings);
			$post = Syspage::getPostedVar();
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				if(!$frm->validate($post)){
					Message::addErrorMessage($frm->getValidationErrors());
				}else{
						if (!$pmObj->saveSetting($post)){
							Message::addErrorMessage($pmObj->getError());
							//break;
						}
						Message::addMessage('Success: Payment method details added/updated successfully.');
						Utilities::reloadPage();
				}
				$frm->fill($post);
			}
       	$this->set('frm', $frm);
		$this->set('payment_settings', $payment_settings);
        $this->_template->render(true,true);
    }
	
}