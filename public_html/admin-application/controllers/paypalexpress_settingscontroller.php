<?php
class PaypalExpress_settingsController extends PaymentmethodsController {
	
	private $key_name="PaypalExpress";
	
	
	
	protected function getSubscriptionSettingsForm() {
		global $package_order_status_arr;
		$packagesObj = new SubscriptionPackages();
		$package_order_status_arr = $packagesObj->getPackageOrderStatusAssoc();
		
        $frm = new Form('frmPaymentMethods','frmPaymentMethods');
		$frm->setExtra(' validator="PaymentMethodfrmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('PaymentMethodfrmValidator');
		
		$frm->addTextBox('API Username', 'api_username')->requirements()->setRequired();
		$frm->addTextBox('API Password', 'api_password')->requirements()->setRequired();
		$frm->addTextBox('API Signature', 'api_signature')->requirements()->setRequired();
		$frm->addSelectBox('Transaction Mode','transaction_mode',array(0=>"Test/Sandbox","1"=>"Live"))->requirements()->setRequired();
		$frm->addSelectBox('Transaction Method','transaction_method',array('Sale' => 'Sale', 'Authorization' => 'Authorization'));
		/* $frm->addSelectBox('Order Status (Initial)','order_status_initial',$package_order_status_arr)->requirements()->setRequired(); */
		
		$frm->addSelectBox('Order Status (Canceled Reversal)','order_status_canceled_reversal',$package_order_status_arr)->requirements()->setRequired();
		$frm->addSelectBox('Order Status (Completed)','order_status_completed',$package_order_status_arr)->requirements()->setRequired();
		$frm->addSelectBox('Order Status (Denied)','order_status_denied',$package_order_status_arr)->requirements()->setRequired();
		$frm->addSelectBox('Order Status (Expired)','order_status_expired',$package_order_status_arr)->requirements()->setRequired();
		$frm->addSelectBox('Order Status (Failed)','order_status_failed',$package_order_status_arr)->requirements()->setRequired();
		$frm->addSelectBox('Order Status (Pending)','order_status_pending',$package_order_status_arr)->requirements()->setRequired();
		$frm->addSelectBox('Order Status (Processed)','order_status_processed',$package_order_status_arr)->requirements()->setRequired();
		$frm->addSelectBox('Order Status (Refunded)','order_status_refunded',$package_order_status_arr)->requirements()->setRequired();
		$frm->addSelectBox('Order Status (Reversed)','order_status_reversed',$package_order_status_arr)->requirements()->setRequired();
		$frm->addSelectBox('Order Status (Voided)','order_status_voided',$package_order_status_arr)->requirements()->setRequired();
		
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