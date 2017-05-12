<?php
class Cashondelivery_payController extends PaymentController{
	private $key_name="CASH_ON_DELIVERY";
	private function getPaymentForm($order_id){
		$frm=new Form('frmPaymentForm','frmPaymentForm');
		$frm->setRequiredStarWith('x');
		$frm->setValidatorJsObjectName('system_validator');
		$frm->setExtra('class="siteForm" validator="system_validator" ');
		$frm->setAction(Utilities::generateUrl('cashondelivery_pay','send',array($order_id)));
		$frm->captionInSameCell(true);
		$frm->setFieldsPerRow(1);
		$pmObj=new Paymentsettings($this->key_name);
		$payment_settings=$pmObj->getPaymentSettings();
		$fld=$frm->addHtml('', 'htmlNote',$payment_settings['cod_note']);
		$fld->merge_caption=true;
		if (!empty(CONF_RECAPTACHA_SITEKEY)){
			$frm->addHtml('', 'htmlNote','<div class="g-recaptcha" data-sitekey="'.CONF_RECAPTACHA_SITEKEY.'"></div>');
		}
		$fld->merge_caption=true;
		$frm->addSubmitButton('','btn_submit',"Confirm Order",'button-confirm');
		$frm->setJsErrorDisplay('afterfield');
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
		$this->_template->render(true,false);	
	}
	public function send($order_id) {
		$pmObj=new Paymentsettings($this->key_name);
		$payment_settings=$pmObj->getPaymentSettings();
		$post = Syspage::getPostedVar();
		$orderPaymentObj=new OrderPayment($order_id);
		$order_info=$orderPaymentObj->getOrderPrimaryinfo();
		if ($order_info){
			
			if(!Utilities::verifyCaptcha()) {
				$json['error']=Utilities::getLabel('M_ERROR_PLEASE_VERIFY_YOURSELF');
			}else{
				$cartObj=new Cart();
				$cartObj->clear();
				$cartObj->updateUserCart();
				
				if ($orderPaymentObj->confirm_COD_Order($order_id)){
					$json['redirect'] = Utilities::generateUrl('custom','payment_success');
				}
				/*$orderPaymentObj->addOrderPaymentComments($comment);
				$orderPaymentObj->addOrderPayment($payment_settings["pmethod_name"],time(),0,Utilities::getLabel("L_Payment_to_be_received_on_cash_on_delivery"),'NA');
				$orderPaymentObj->addOrderPaymentHistory($order_id,2,Utilities::getLabel('M_Cash_on_delivery'),1);*/
				
				$json['redirect'] = Utilities::generateUrl('custom','payment_success');
			}
		}else{
			$json['error'] = 'Invalid Request.';
		}
		curl_close($curl);
		echo json_encode($json);
	}
}
