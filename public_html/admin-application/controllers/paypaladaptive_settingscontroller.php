<?php
class PaypalAdaptive_settingsController extends PaymentmethodsController {
	
	private $key_name=CONF_PAPYAL_ADAPTIVE_KEY; 
	
	
	 protected function getSettingsForm() {
		global $payment_status_arr;
        $frm = new Form('frmPaymentMethods','frmPaymentMethods');
		$frm->setExtra(' validator="PaymentMethodfrmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('PaymentMethodfrmValidator');
		$memail_fld = $frm->addEmailField('Merchant Paypal Account Email', 'pp_adaptive_merchant_email', '', 'pp_adaptive_merchant_email');
		$memail_fld->requirements()->setRequired(true);
		$memail_fld->html_after_field = '<em style="font-size:.9em">All website payments should go to this account after making payment using Paypal Adaptive.</em>';
		
		$fld=$frm->addSelectBox('Transaction Mode', 'pp_adaptive_transaction_mode', array(0=>"Test/Sandbox","1"=>"Live"), '', '', '', 'pp_adaptive_transaction_mode');
		$fld->requirements()->setRequired();
		$fld->html_after_field='<small>Transaction modes allow admin to switch between live and testing account. Testing account is used to test the payment flow through PayPal.</small>';
		
		$fld=$frm->addRequiredField('App ID', 'pp_adaptive_api_app_id', '', 'pp_adaptive_api_app_id');
		$fld->html_after_field='<small>Please enter your Paypal application\'s APP ID here.</small>';
		$fld=$frm->addRequiredField('Username', 'pp_adaptive_api_username', '', 'pp_adaptive_api_username');
		$fld->html_after_field='<small>Please enter your Paypal application\'s Username here.</small>';
		$fld=$frm->addRequiredField('Password', 'pp_adaptive_api_password', '', 'pp_adaptive_api_password');
		$fld->html_after_field='<small>Please enter your Paypal application\'s Password here.</small>';
		$fld=$frm->addRequiredField('Signature', 'pp_adaptive_api_signature', '', 'pp_adaptive_api_signature');
		$fld->html_after_field='<small>Please enter your Paypal application\'s Signature here.</small>';
		$paypal_fee_pay_opts = array(
			'EACHRECEIVER' => 'Each Receiver',
			'PRIMARYRECEIVER' => 'Primary Receiver',
			'SENDER' => 'Sender',
			'SECONDARYONLY' => 'Secondary Only'
		);
		$fld=$frm->addSelectBox('Pay Paypal Fee', 'pp_adaptive_pay_fee', $paypal_fee_pay_opts, '', '', '', 'pp_adaptive_order_execution');
		$fld->requirements()->setRequired();
		$fld->html_after_field='<small>Please select your preferred to collect/charge PayPal Fees. All supported options are defined here.</small>';
		$delay_days_fld = $frm->addIntegerField('Delay Secondary Payment By Days', 'pp_adaptive_chained_delay_by_days', '', 'pp_adaptive_chained_delay_by_days');
		$delay_days_fld->requirements()->setRequired(true);
		$delay_days_fld->requirements()->setRange(1, 90);
		$delay_days_fld->html_after_field = '<em style="font-size:.9em">Delay Secondary Payment By Days = 0, means secondary payment to vendors will be executed immidiately. You can delay it by maximum 90 days. Recommended atleast 30 days delayed payment.</em>';
		
		/*$frm->addSelectBox('Order Status (Initial)', 'pp_adaptive_order_status_initial', $payment_status_arr, '', '', '', 'pp_adaptive_order_status_initial')->requirements()->setRequired();
		$frm->addSelectBox('Order Status (Pending)', 'pp_adaptive_order_status_pending', $payment_status_arr, '', '', '', 'pp_adaptive_order_status_pending')->requirements()->setRequired();
		$frm->addSelectBox('Order Status (Processed)','pp_adaptive_order_status_processed', $payment_status_arr, '', '', '', 'pp_adaptive_order_status_processed')->requirements()->setRequired();
		$frm->addSelectBox('Order Status (Completed)','pp_adaptive_order_status_completed', $payment_status_arr, '', '', '', 'pp_adaptive_order_status_completed')->requirements()->setRequired();
		$frm->addSelectBox('Order Status (Others)', 'pp_adaptive_order_status_others', $payment_status_arr, '', '', '', 'pp_adaptive_order_status_others')->requirements()->setRequired();*/
		
		
		$frm->addSubmitButton('&nbsp;', 'btn_submit', 'Save changes', 'btn_submit');
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
	
}