<?php
class Wallet_payController extends PaymentController{
	public function recharge($recharge_id){
		$wrObj=new WalletRecharge();
		$recharge_info = $wrObj->getWalletRequests(array("id"=>$recharge_id,"status"=>0,"pagesize"=>1));
		if ($recharge_info==false){
			Message::addErrorMessage(Utilities::getLabel('M_INVALID_WALLET_REQUEST_PAID_CANCELLED'));
			Utilities::redirectUserReferer();
		}
		$ppcpaymentMethodObj=new PPCPaymentMethods();
		$this->set('payment_methods',$ppcpaymentMethodObj->getPaymentMethods(array("status"=>1)));
		$this->set('recharge_info',$recharge_info);
		$this->_template->render(true,false);	
	}
	function payment_tab($recharge_id,$ppcpmethod_id){
		$wrObj=new WalletRecharge();
		$recharge_info = $wrObj->getWalletRequests(array("id"=>$recharge_id,"status"=>0,"pagesize"=>1));
		if ($recharge_info==false)
			$this->set('error', Utilities::getLabel('M_INVALID_WALLET_REQUEST_PAID_CANCELLED'));
		$this->set('recharge_info',$recharge_info);
		$ppcpaymentMethodObj=new PPCPaymentMethods();
		$payment_method=$ppcpaymentMethodObj->getData($ppcpmethod_id);
		$this->set('payment_method',$payment_method);
		$frm=new Form('frmPaymentTabForm','frmPaymentTabForm');
		$frm->setExtra('class="siteForm"');
		$frm->setAction(Utilities::generateUrl(strtolower(str_replace("_","",$payment_method["ppcpmethod_code"]))."_pay",'charge_for_wallet',array($recharge_id)));
		$frm->setFieldsPerRow(1);
		$frm->addHiddenField('', 'recharge_id',$recharge_id);
		$fld=$frm->addSubmitButton('','btn_submit',Utilities::getLabel('M_Confirm_Payment'),'button-confirm','class="btn primary-btn"');
		$fld->merge_caption=true;
		$this->set('frm',$frm);
		$this->_template->render(false,false);	
	}
	public function confirm_recharge() {
		$post = Syspage::getPostedVar();
		$json = array();
		if (isset($post['recharge_id'])) {
			$recharge_id=$post['recharge_id'];
			$wrObj=new WalletRecharge();
			$recharge_info = $wrObj->getWalletRequests(array("id"=>$recharge_id,"user"=>User::getLoggedUserId(),"status"=>0,"pagesize"=>1));
			if ($recharge_info){
				$json['success'] = 1;
			}else{
				$json['error'] = Utilities::getLabel('M_ERROR_INVALID_REQUEST');
			}
		}
		echo json_encode($json);
	}
	public function send() {
		$post = Syspage::getPostedVar();
		$order_id=$_SESSION['shopping_cart']["order"];
		$orderObj=new Orders();
		$order_info = $orderObj->getOrderById($order_id,array("payment_status"=>0));
		if ($order_info){
			/**************** Start Reduce Reward Points of Buyer *********************/
			if ($order_info['order_reward_points']>0){
				$rewardObj=new Rewards();
				$rewardArray=array(
					"urp_user_id"=>$order_info['order_user_id'],
					"urp_referrer_id"=>0,
					"urp_points"=>(int)-$order_info['order_reward_points'],
					"urp_description"=>sprintf(Utilities::getLabel('L_Used_Reward_Points_Order_Invoice_Number'),'<i>'.$order_info['order_invoice_number'].'</i>'),
					);
				if($reward_point_id = $rewardObj->addRewardPoints($rewardArray)){
					$emailNotificationObj=new Emailnotifications();
					$emailNotificationObj->sendRewardPointsNotification($reward_point_id);
				}else{
					$this->error=$rewardObj->getError();
					return false;
				}
			}
			/**************** End Reduce Reward Points of Buyer *********************/
			$cartObj=new Cart();
			$cartObj->clear();
			$cartObj->updateUserCart();
			$order_payment_financials=$orderObj->getOrderPaymentFinancials($order_id);	
			/* Charging User Credits */
			if ($order_payment_financials["order_credits_charge"]>0){
				$orderPaymentObj=new OrderPayment($order_id);
				$orderPaymentObj->chargeUserWallet($order_payment_financials["order_credits_charge"],$order_id);	
			}
			$json['redirect'] = Utilities::generateUrl('custom','payment_success');
		}else{
			$json['error'] = Utilities::getLabel('M_Invalid_Request');;
		}
		curl_close($curl);
		echo json_encode($json);
	}
}
