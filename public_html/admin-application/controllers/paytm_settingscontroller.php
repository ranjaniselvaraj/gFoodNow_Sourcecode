<?php
class Paytm_settingsController extends PaymentmethodsController {
	
	private $key_name="Paytm";
	
    protected function getSettingsForm() {
		global $payment_status_arr;
        $frm = new Form('frmPaymentMethods','frmPaymentMethods');
		$frm->setExtra(' validator="PaymentMethodfrmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('PaymentMethodfrmValidator');
		$frm->addRequiredField('Merchant ID', 'merchant_id');
		$frm->addRequiredField('Merchant Key', 'merchant_key');
		$frm->addRequiredField('Website', 'merchant_website');
		$frm->addRequiredField('Channel ID', 'merchant_channel_id');
		$frm->addRequiredField('Industry Type ID', 'merchant_industry_type');
		$frm->addSelectBox('Transaction Mode','transaction_mode',array(0=>"Test/Sandbox","1"=>"Live"))->requirements()->setRequired();
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
	
}