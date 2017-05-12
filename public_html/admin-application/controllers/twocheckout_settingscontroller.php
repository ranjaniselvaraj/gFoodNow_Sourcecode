<?php
class Twocheckout_settingsController extends PaymentmethodsController {
	
	private $key_name="Twocheckout";
	
    protected function getSettingsForm() {
		global $payment_status_arr;
        $frm = new Form('frmPaymentMethods','frmPaymentMethods');
		$frm->setExtra(' validator="PaymentMethodfrmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('PaymentMethodfrmValidator');
		$payment_type = array(
			'HOSTED' => 'Hosted Checkout',
			'API' => 'Payment API'
		);
		$frm->addRadioButtons('Payment Type', 'payment_type', $payment_type, 'HOSTED', 2);
		$frm->addRequiredField('Seller ID', 'sellerId');
		$frm->addRequiredField('Publishable Key', 'publishableKey');
		$frm->addRequiredField('Private Key', 'privateKey');
		$frm->addRequiredField('Secret Word', 'hashSecretWord');
		$frm->addSelectBox('Transaction Mode','transaction_mode',array(0=>"Test/Sandbox","1"=>"Live"))->requirements()->setRequired();
		$hint = 'In case of <strong>Hosted Checkout</strong>, Admin must set <strong>Direct Return (URL)</strong> to <strong>Header Redirect</strong> and 
		<strong>Approved URL</strong> to <strong>'.Utilities::generateAbsoluteUrl('twocheckout_pay', 'callback', array(), CONF_WEBROOT_URL).'</strong> under <strong>2Checkout Accounts</strong> Section.';
		$frm->addHTML('Remember', '', $hint);
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