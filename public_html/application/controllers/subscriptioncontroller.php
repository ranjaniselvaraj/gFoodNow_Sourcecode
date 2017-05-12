<?php
class SubscriptionController extends CommonController{
	function default_action(){
		Utilities::redirectUser(Utilities::generateUrl('subscription','checkout'));	
	}
	public function process_wallet_subscription() {
		$post = Syspage::getPostedVar();
		$json = array();
		$pay_from_wallet=$post['pay_from_wallet'];
		if (isset($pay_from_wallet)) {
			$this->Subscription->updateSubscriptionCartWalletOption($pay_from_wallet);
			$json["success"]=1;
		}
		echo json_encode($json);
	}
	function buy_subscription(){
		if ( !$this->isUserLogged() ) {
			Message::addErrorMessage( Utilities::getLabel('M_YOUR_SESSION_SEEMS_EXPIRED'));
			dieJsonError(Message::getHtml());
		}
		$this->Subscription->RemoveSubscriptionCartDiscountCoupon();
		$subscriptionOrderObj = new SubscriptionOrders();
		$subscription_status_assoc_arr = SubscriptionOrders::subscription_status_assoc_arr();
		$subscription_status_arr = SubscriptionOrders::subscription_status_arr();
		$order_filters = array(
			'user'					=>	$this->getLoggedUserId(),
			'subscription_status'	=>	Settings::getSetting("CONF_ACTIVE_SUBSCRIPTION_STATUS"),
			'pagesize'				=>	1
			);
		$orders = $subscriptionOrderObj->getSubscriptionOrders( $order_filters );
		if($orders){
			$max_products = $orders[0]['mporder_merchantpack_max_products'];
			$isUpdatingSubscription = $orders[0]['mporder_id'];
		}else{
			$isUpdatingSubscription ='';
		}
		//die($isUpdatingSubscription.'#');
		$json = array();
		$post = Syspage::getPostedVar();
		$is_free = intval($post['is_free']);
		$sub_package_id = intval($post['sub_package_id']);
		if($is_free)
		{
			$package_id = intval($post['package_id']);
			$this->SubPackages = new SubPackages();
			$sub_package_id = $this->SubPackages->getFreeTrialPack($package_id);
			$this->Subscription->applyFreeTrialPackage();
		}else{
			$this->Subscription->removeFreeTrialPackage();
		}
		if( $_SERVER['REQUEST_METHOD'] != 'POST' ){
			die(Utilities::getLabel('L_Invalid_Request!'));
		}
		if(empty($sub_package_id))
		{
			Message::addErrorMessage(Utilities::getLabel('M_PLEASE_SELECT_PACKAGE'));
			dieJsonError(Message::getHtml());
		}
		$this->SubPackages = new SubPackages();
		$this->Subscription = new Subscription();
		$sub_package_data = $this->SubPackages->getData( $sub_package_id, array( 'status' => 1 ) );
		if( !$sub_package_data || (intval($sub_package_data['merchantsubpack_id']) !== $sub_package_id && $is_free == false) || intval($sub_package_data['merchantpack_active']) == 0 ){
			Message::addErrorMessage(Utilities::getLabel('M_SELECTED_PACKAGE_NOT_FOUND'));
			dieJsonError(Message::getHtml());
		}
		
		/* if logged user have already one active subscription, then he/she cannot buy new subscription, but if he/she is changing current plan then he/she can buy the new subscription and upon sucessful creation of subscription, will cancel the current/old subscription[ */
		if( !$isUpdatingSubscription ){
			$order_filters = array(
				'user'					=>	$this->getLoggedUserId(),
				'subscription_status'	=>	Settings::getSetting("CONF_ACTIVE_SUBSCRIPTION_STATUS"),
				'pagesize'				=>	1
				);
			$orders = $subscriptionOrderObj->getSubscriptionOrders( $order_filters );
			if( $orders ){
				Message::addErrorMessage(sprintf(Utilities::getLabel('M_You_cannot_buy_new_subscription'),$subscription_status_arr[$subscription_status_assoc_arr['status_active']]));
				dieJsonError(Message::getHtml());
			}
		}
		/* ] */
		/* if logged user, have bought or used any subscription once in his/her account, then he/she cannot buy free plans/packages[ */
		if( $sub_package_data['merchantpack_max_products'] < 0 ){
			$order_filters = array(
				'user'	=>	$this->getLoggedUserId(),
				'payment_status'	=>	1,
				);
			$orders = $subscriptionOrderObj->getSubscriptionOrders( $order_filters );
			$total_records = $subscriptionOrderObj->getTotalRecords();
			if( $total_records ){
				Message::addErrorMessage(Utilities::getLabel('M_FREE_TRIAL_PACKAGE_ONLY_ONE_TIME'));
				dieJsonError(Message::getHtml());
			}
		}
		/* ] */
		/* if Downgrading package then give message to reduce products */
		
			$products = new Products();
			$totalProducts  =  $products->getTotalProductsAddedByUser($this->getLoggedUserId());
			if( $totalProducts > $sub_package_data['merchantpack_max_products'] ){
				Message::addErrorMessage(sprintf(Utilities::getLabel('M_YOU_ARE_DOWNGRADING_YOUR_PACKAGE'),$sub_package_data['merchantpack_max_products'],$totalProducts));
				dieJsonError(Message::getHtml());
			}
		
		/* ] */
		if( !$this->Subscription->addSubscription( $sub_package_data['merchantsubpack_id'] ) ){
			Message::addHtml(Utilities::getLabel('M_ERROR_UPDATING_RECORD'));
			dieJsonError(Message::getHtml());
		}
		unset($_SESSION['shopping_cart_subscription']["order"]);
		$json['status'] = 1;
		$json['msg'] = 'Redirecting...';
		$json['redirectUrl'] = Utilities::generateUrl('subscription','checkout');
		die(json_encode($json));
	}
	function remove_subscription(){
		$post = Syspage::getPostedVar();
		$json = array();
		if (isset($post['key'])) {
			if($this->Subscription->remove_subscription($post['key'])){
				$json['total'] = 0;
			}
		}
		$json['cart_count'] = 0;
		echo json_encode($json);
	}
	public function subscription_display(){
		Utilities::checkLogin();
		$error_warning = '';
		$this->set('error_warning',$error_warning);
		$this->set('subpackages', $this->Subscription->getSubscription());
		$user=new User();
		$this->set('user_balance',$user->getUserBalance($this->getLoggedUserId()));
		$this->set('payment_ready',$payment_ready);
		$this->set('cart_summary',$this->Subscription->getSubscriptionCartFinancialSummary());	
		$this->_template->render();	
	}
	public function checkout(){
		Syspage::addCss(array('css/slick.css','css/checkout-short.css'), false);
		Syspage::addJs(array('js/slick.min.js'), false);
		Utilities::checkLogin();
		$subpackages = $this->Subscription->getSubscription();
		if( !$subpackages ){
			Utilities::redirectUser(Utilities::generateUrl('account','packages'));
		}
		$this->set( 'subpackages', $subpackages );
		$user=new User();
		$this->set('user_balance',$user->getUserBalance($this->getLoggedUserId()));
		$this->set('payment_ready',$payment_ready);
		//die($this->Subscription->getSubscriptionCartRewardPoints()."#");
		/*Utilities::printArray($this->Subscription->getSubscriptionCartFinancialSummary());
		die();*/
		$this->set( 'cart_summary', $this->Subscription->getSubscriptionCartFinancialSummary() );
		$this->_template->render(false,false);
	}
	
public function checkout_summary(){
	Syspage::addCss(array('css/jQueryTab.css','css/checkout-short.min.css'), false);
	Syspage::addJs(array('js/jQueryTab.js'), false);
	Utilities::checkLogin();
	$subpackages = $this->Subscription->getSubscription();
	if( !$subpackages ){
		Message::addErrorMessage(Utilities::getLabel('L_PLEASE_SELECT_SUBSCRIPTION_PACKAGE_FROM_PREVIOUS_PAGE'));
		dieJsonError(Message::getHtml);
	}
	$this->set( 'subpackages', $subpackages );
	$user=new User();
	$this->set('user_balance',$user->getUserBalance($this->getLoggedUserId()));
	$this->set('payment_ready',$payment_ready);
//print_r($this->Subscription->getSubscriptionCartFinancialSummary());
	$this->set( 'cart_summary', $this->Subscription->getSubscriptionCartFinancialSummary() );
	$this->_template->render(false,false);
}
public function checkout_payment(){
	Utilities::checkLogin();
	$subpackage = $this->Subscription->getSubscription();
	$subpackage = array_shift( $subpackage );
	if( !$subpackage ){
		Utilities::redirectUser(Utilities::generateUrl('account','packages'));
	}
	$payment_ready = true ;
	$user=new User();
	$cart_summary = $this->Subscription->getSubscriptionCartFinancialSummary();
	$this->set('user_balance',$user->getUserBalance($this->getLoggedUserId()));
//Record Order Paramters
	$random = strtoupper(uniqid());
	
	if ($this->isUserLogged()) {
		$user_details=$user->getUser(array('user_id'=>$this->getLoggedUserId(), 'get_flds'=>array('user_id', 'user_customer_group', 'user_name','user_email','user_phone')));
		$order_data['mporder_user_id'] = $user_details["user_id"];
		$order_data['mporder_customer_group'] = $user_details['user_customer_group'];
		$order_data['mporder_user_name'] = $user_details['user_name'];
		$order_data['mporder_user_email'] = $user_details['user_email'];
		$order_data['mporder_user_phone'] = $user_details['user_phone'];
	} elseif ($this->isGuestUserLogged()) {
		$order_data['mporder_user_id'] = 0;
		$order_data['mporder_customer_group'] = $_SESSION['guest']['user_customer_group'];
		$order_data['mporder_user_name'] = $_SESSION['guest']['name'];
		$order_data['mporder_user_email'] = $_SESSION['guest']['email'];
		$order_data['mporder_user_phone'] = $_SESSION['guest']['phone'];
	}
	$order_data['mporder_old_mporder_id'] = Subscription::isUpdatingSubscription();
	$order_data['mporder_date_added'] = date('Y-m-d H:i:s');
	$order_data['mporder_ip_address'] = $_SERVER['REMOTE_ADDR'];
	if (isset($_SERVER['HTTP_USER_AGENT'])) {
		$order_data['mporder_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
	} else {
		$order_data['mporder_user_agent'] = '';
	}
	if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
		$order_data['mporder_accept_language'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
	} else {
		$order_data['mporder_accept_language'] = '';
	}
	$order_data['mporder_language']	= Settings::getSetting('CONF_LANGUAGE');
	$order_data['mporder_currency_code'] = Settings::getSetting('CONF_CURRENCY');
	$order_data["mporder_currency_symbol_left"]=Settings::getSetting('CONF_DISPLAY_CURRENCY_SYMBOL')=="L"?Settings::getSetting('CONF_CURRENCY_SYMBOL'):"";
	$order_data["mporder_currency_symbol_right"]=Settings::getSetting('CONF_DISPLAY_CURRENCY_SYMBOL')=="R"?Settings::getSetting('CONF_CURRENCY_SYMBOL'):"";
	$order_data["mporder_currency_value"] = 1.00;
	$order_data['mporder_merchantpack_id'] = $subpackage['merchantsubpack_merchantpack_id'];
	$order_data['mporder_merchantpack_name'] = $subpackage['merchantpack_name'];
	$order_data['mporder_merchantpack_desc'] = $subpackage['merchantpack_description'];
	$order_data['mporder_merchantpack_commission'] = $subpackage['merchantpack_commission_rate'];
	$order_data['mporder_merchantpack_max_products'] = $subpackage['merchantpack_max_products'];
	$order_data['mporder_merchantpack_max_pimages'] = $subpackage['merchantpack_images_per_product'];
	$order_data['mporder_merchantsubpack_id'] = $subpackage['merchantsubpack_id'];
	$order_data['mporder_merchantsubpack_name'] = SubscriptionHelper::displayFormattedSubPackage($subpackage['merchantsubpack_actual_price'] , $subpackage['merchantsubpack_subs_frequency'], $subpackage['merchantsubpack_subs_period']) ;
	$order_data['mporder_merchantsubpack_subs_frequency'] = $subpackage['merchantsubpack_subs_frequency'];
	$order_data['mporder_merchantsubpack_subs_period'] = $subpackage['merchantsubpack_subs_period'];
	$order_data['mporder_recurring_billing_cycle'] = $subpackage['merchantsubpack_total_occurrance'];
	$order_data['mporder_recurring_billing_cycle_frequency'] = $subpackage['merchantsubpack_subs_frequency'];
	$order_data['mporder_recurring_billing_cycle_period'] = $subpackage['merchantsubpack_subs_period'];
	$order_data['mporder_discount_coupon'] = $cart_summary["cart_discounts"]["code"];
	$order_data['mporder_discount_total'] = $cart_summary["cart_discounts"]["value"];
	$order_data['mporder_reward_points'] = $cart_summary["reward_points"];
	$order_data['mporder_recurring_discount_coupon'] = $cart_summary["cart_discounts"]["recurring_code"];
	$order_data['mporder_recurring_discount_total'] = $cart_summary["cart_discounts"]["recurring_discount"];		
	$order_data['mporder_recurring_amount'] = $cart_summary["net_recurring_amount"];
	$order_data['mporder_recurring_chargeble_amount'] = $cart_summary["net_recurring_after_discount_amount"];
	$order_data['mporder_merchantsubpack_subs_amount'] = $cart_summary["cart_total"];
//$subpackage['merchantsubpack_actual_price'] ;
	$order_data['mporder_actual_paid'] = $cart_summary["cart_actual_paid"];
	$order_data['order_payment_gateway_charge'] = $cart_summary["order_payment_gateway_charge"];
	$order_data["mporder_id"]=$_SESSION['shopping_cart_subscription']["order"];
	$order_data['mporder_wallet_selected'] = $cart_summary["cart_wallet_enabled"];
	$order_data['mporder_net_charged'] = $cart_summary["order_net_charged"];
	$order_data['mporder_credits_charged'] = $cart_summary["order_credits_charge"];
	$order_data['mporder_sub_total'] = $cart_summary["net_total_without_discount"];
	$order_data['mporder_amount_adjustable'] = $cart_summary["amount_adjustable"];
	$order_data['mporder_invoice_number'] = $this->Subscription->subscription_invoice_format();
	$order_data['mporder_is_free_trial'] = $cart_summary["is_free_trial"];
	
	$subscriptionOrderObj=new SubscriptionOrders();
	if( $subscriptionOrderObj->addUpdateSubscriptionOrder($order_data) ){
		$mporder_id = $subscriptionOrderObj->getSubscriptionOrderId();
	} else {
		Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_ACCESS'));
		dieJsonError(Message::getHtml());
	}
//End Recording Order Paramters
	//die($mporder_id."#");
	$order_info = $subscriptionOrderObj->getSubscriptionOrderById($mporder_id,array("payment_status"=>0));
	
	if ( !$order_info ){
		$this->Subscription->SubscriptionClear();
		Message::addErrorMessage(Utilities::getLabel('M_SUBSCRIPTION_ORDER_ALREADY_PROCESSED_NOT_FOUND'));
		dieJsonError(Message::getHtml());
		/* Utilities::redirectUser(Utilities::generateUrl('account','view_order',array($mporder_id))); */
	}
	$this->set('order_info',$order_info);
	$this->set('order_payment_financials', $this->Subscription->getSubscriptionCartFinancialSummary($mporder_id));
	$paymentMethodObj=new SubscriptionPaymentMethods();
	$payment_methods = $paymentMethodObj->getPaymentMethods(array("status"=>1));
	$this->set('payment_ready',$payment_ready);
	$this->set( 'cart_summary', $this->Subscription->getSubscriptionCartFinancialSummary() );
	$this->set('payment_methods',$payment_methods);
		//$this->_template->render(false,false);
	die(convertToJson(array('free_trial'=>$cart_summary['is_free_trial'], 'html'=>$this->_template->render(false,false,NULL,true))));	
	}
function package_payment_tab($mporder_id,$pmethod_id){
	$subscriptionOrderObj=new SubscriptionOrders();
	$order_info = $subscriptionOrderObj->getSubscriptionOrderById($mporder_id,array("payment_status"=>0));
	if ($order_info==false){
		$error = Utilities::getLabel('M_INVALID_ORDER_PAID_CANCELLED');
	}
	$paymentMethodObj=new SubscriptionPaymentMethods();
	$payment_method=$paymentMethodObj->getData($pmethod_id);
	if( !method_exists($payment_method['subscriptionpmethod_code'].'_payController', 'recurringPayments' ) ){
		$error = Utilities::getLabel('M_PLEASE_CONTACT_TECHNICAL_TEAM_RECURRING_PAYMENT');
	}
	$this->set('payment_method',$payment_method);
	if(!isset($error)){
		$frm=new Form('frmPaymentTabForm','frmPaymentTabForm');
		$frm->setExtra('class="siteForm"');
		$frm->setAction(Utilities::generateUrl(strtolower(str_replace("_","",$payment_method["subscriptionpmethod_code"]))."_pay",'package_charge',array($mporder_id)));
		$frm->setFieldsPerRow(1);
		$frm->addHiddenField('', 'order',$mporder_id);
		if (isset($payment_method["transaction_mode"]) && $payment_method["transaction_mode"]==0){
			$fld=$frm->addHtml('', 'htmlNote','<div class="alert alert-danger">'.Utilities::getLabel('M_Test_Mode_Enabled').'</div>');
			$fld->merge_caption=true;
		}
		$fld=$frm->addSubmitButton('','btn_submit',Utilities::getLabel('M_Confirm_Payment'),'button-confirm');
		$fld->merge_caption=true;
	}
	if(isset($error)){
		$this->set( 'error', $error );
	}
	$this->set('frm',$frm);
	$this->_template->render(false,false);
}
public function confirm_package_order() {
	$post = Syspage::getPostedVar();
	$json = array();
	if ( !$this->isUserLogged() ) {
		Message::addErrorMessage(Utilities::getLabel('M_YOUR_SESSION_SEEMS_EXPIRED'));
		$json['error'] = Message::getHtml();
		die(json_encode($json));
	}
	if (isset($post['order'])) {
		$mporder_id=$post['order'];
		$subscriptionOrderObj=new SubscriptionOrders();
		$order_info = $subscriptionOrderObj->getSubscriptionOrderById($mporder_id,array("payment_status"=>0,'user'=>$this->getLoggedUserId()));
		if ($order_info){
			
				/**************** Start Reduce Reward Points of Buyer *********************/
				if ($order_info['mporder_reward_points']>0){
				$rewardObj=new Rewards();
				$rewardArray=array(
				"urp_user_id"=>$order_info['mporder_user_id'],
				"urp_referrer_id"=>0,
				"urp_points"=>(int)-$order_info['mporder_reward_points'],
				"urp_description"=>sprintf(Utilities::getLabel('L_Used_Reward_Points_Subscription_Reference_Number'),'<i>'.$order_info['mporder_invoice_number'].'</i>'),
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
			
			
//$this->Subscription->SubscriptionClear();
			if ($subscriptionOrderObj->addOrderHistory($order_info['mporder_id'], 1, "-NA-", true)){
				$json['success'] = 1;
			}else{
				$json['error'] = Utilities::getLabel('M_TRY_AFTER_SOME_TIME');
			}
		}else{
			$json['error'] = Utilities::getLabel('M_INVALID_ORDER_PAID_CANCELLED');
		}
	}
	die(json_encode($json));
}
function checkout_sidebar(){
	$payment_ready = true ;
	$this->set('products',$this->Subscription->getSubscription());
	$rewardObj=new Rewards();
	$user_total_reward_points = $rewardObj->getUserRewardsPointsBalance($this->getLoggedUserId());
	$this->set('user_total_reward_points',$user_total_reward_points);
	$this->set('cart_summary',$this->Subscription->getSubscriptionCartFinancialSummary());	
	$user=new User();
	$this->set('user_balance',$user->getUserBalance($this->getLoggedUserId()));
	$this->set('payment_ready',$payment_ready);
	die(convertToJson(array('cart_count'=>1, 'html'=>$this->_template->render(false,false,NULL,true))));
}
public function apply_coupon() {
	$json = array();
	$post = Syspage::getPostedVar();
	$coupon = isset($post['coupon'])?$post['coupon']:"";
	$couponObj=new Subscriptioncoupon();
	$coupon_info = $couponObj->getCoupon($coupon);
	if (empty($post['coupon'])) {
		$json['message'] = Utilities::getLabel('L_Warning_Enter_Coupon');
	} elseif ($coupon_info) {
		if($this->Subscription->updateSubscriptionCartDiscountCoupon($coupon_info['subscoupon_code'])){
			$json['message'] = Utilities::getLabel('M_cart_discount_coupon_applied');
			$json['success'] = 1;
		}else{
			$json['message'] = Utilities::getLabel('L_ACTION_TRYING_PERFORM_NOT_VALID');
		}
	} else {
		$json['message'] = Utilities::getLabel('L_Warning_Coupon_Invalid');
	}
	echo(json_encode($json));
}
public function apply_reward_points() {
	$cart_summary = $this->Subscription->getSubscriptionCartFinancialSummary();
	$cart_sub_total=$cart_summary['cart_max_rewards_points'];
	$json = array();
	$post = Syspage::getPostedVar();
	$rewardObj=new Rewards();
	$customer_reward_points = $rewardObj->getUserRewardsPointsBalance($this->getLoggedUserId());
	$reward_points = $post['reward_points'];
	if (empty($post['reward_points'])) {
		$json['message'] = Utilities::getLabel('L_Warning_Enter_Reward_Points_to_use');
	}elseif ($reward_points > $customer_reward_points) {
		$json['message'] = sprintf(Utilities::getLabel('L_Warning_you_dont_have_reward_points'), $reward_points);
	}elseif ($reward_points > $cart_sub_total) {
		$json['message'] = sprintf(Utilities::getLabel('L_Warning_maximum_rewards_can_be_applied'), $cart_sub_total);
	}
	if (!$json) {
		if ($this->Subscription->updateSubscriptionCartRewardPoints(abs($reward_points))){
			$json['message'] = Utilities::getLabel('L_Success_Reward_points_applied');
			$json['success'] = 1;
		}else{
			$json['message'] = Utilities::getLabel('M_INVALID_REQUEST');
		}
	}
	echo(json_encode($json));
}
function checkout_sidebar_payment_summary(){
	$payment_ready = true ;
	$user=new User();
	$this->set('products',$cart_products=$this->Subscription->getSubscription());
	$this->set('cart_summary',$this->Subscription->getSubscriptionCartFinancialSummary());
	$this->set('user_balance',$user->getUserBalance($this->getLoggedUserId()));
	$this->set('payment_ready',$payment_ready);
	die(convertToJson(array('cart_count'=>'1', 'html'=>$this->_template->render(false,false,NULL,true))));
}
public function remove_coupon() {
	$json = array();
	$post = Syspage::getPostedVar();
	if($this->Subscription->RemoveSubscriptionCartDiscountCoupon()){
		$json['success'] = 1;
	}else{
		$json['message'] = Utilities::getLabel('L_ACTION_TRYING_PERFORM_NOT_VALID');
	}
	echo(json_encode($json));
}
	public function wallet_pay_send() {
		$post = Syspage::getPostedVar();
		$mporder_id=$_SESSION['shopping_cart_subscription']["order"];
		$subscriptionOrderObj=new SubscriptionOrders();
		$order_info = $subscriptionOrderObj->getSubscriptionOrderById($mporder_id,array("payment_status"=>0));
		if ($order_info){
				
				$this->Subscription->SubscriptionClear();
				$order_payment_financials=$subscriptionOrderObj->getOrderPaymentFinancials($mporder_id);
				
				if ($order_info['mporder_net_charged']==0){
					$subOrderPaymentObj = new SubscriptionOrderPayment($mporder_id);
					$subOrderPaymentObj->addOrderPayment(Utilities::getLabel("L_Free_Trial"),time(),0,Utilities::getLabel("L_Free_Trial"),Utilities::getLabel("L_Free_Trial"),1);
					//$SubOrdPayment->updateOrderPaymentStatus($mporder_id, 1);
				}else{
					if ($order_payment_financials["order_credits_charge"]>0){
						$subscriptionOrderObj=new SubscriptionOrderPayment($mporder_id);
						$subscriptionOrderObj->chargeSubscriptionUserWallet($order_payment_financials["order_credits_charge"],$mporder_id);	
					}
				}
			$json['redirect'] = Utilities::generateUrl('custom','package_payment_success');
		}else{
			$json['error'] = Utilities::getLabel('M_Invalid_Request');;
		}
		curl_close($curl);
		echo json_encode($json);
	}
}
