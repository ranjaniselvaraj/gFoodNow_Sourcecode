<?php
class SubscriptionOrderPayment extends SubscriptionOrders {
	function __construct($payment_mporder_id){
		$this->db = Syspage::getdb();
        if (!is_numeric($payment_mporder_id))
            $payment_mporder_id = 0;
        $this->payment_mporder_id = intval($payment_mporder_id);
        if (!($this->payment_mporder_id > 0)) {
           return;
        }
        $this->loadData();
    }
	function getError() {
        return $this->error;
    }
	function getData() {
        return $this->attributes;
    }
	protected function loadData() {
        $this->attributes = $this->getSubscriptionOrderById($this->payment_mporder_id);
    }
	function getOrderPaymentGatewayAmount(){
		$mporder_info=$this->attributes;
		$user=new User();
		$user_balance=$user->getUserBalance($mporder_info["mporder_user_id"]);
		$mporder_credits_charge=$mporder_info["mporder_wallet_selected"]?min($mporder_info["mporder_net_charged"],$user_balance):0;
		$mporder_payment_gateway_charge=$mporder_info["mporder_net_charged"]-(float)$mporder_credits_charge;
		return round($mporder_payment_gateway_charge,2);
	}
	
	function getRecurringOrderPaymentGatewayAmount(){
		$mporder_info=$this->attributes;
		return round($mporder_info["mporder_recurring_chargeble_amount"],2);
	}
	function getOrderPrimaryinfo(){
		 $arr_order=array();
		 $mporder_info=$this->attributes;
		 if ($mporder_info && is_array($mporder_info)){
			 $arr_order=array(
			 				"id"=>$mporder_info["mporder_id"],
							"old_mporder_id"=>$mporder_info["mporder_old_mporder_id"],
							"invoice"=>$mporder_info["mporder_invoice_number"],
							"customer_id"=>$mporder_info["mporder_user_id"],
							"customer_name"=>$mporder_info["mporder_user_name"],
							"customer_email"=>$mporder_info["mporder_user_email"],
							"mporder_currency_code"=>$mporder_info["mporder_currency_code"],
							"mporder_payment_status"=>$mporder_info["mporder_payment_status"],
							"mporder_language"=>$mporder_info["mporder_language"],
							
							"mporder_merchantpack_desc"=>$mporder_info["mporder_merchantpack_desc"],
							
							"mporder_recurring_billing_cycle"=>$mporder_info["mporder_recurring_billing_cycle"],
							"mporder_recurring_billing_cycle_frequency"=>$mporder_info["mporder_recurring_billing_cycle_frequency"],
							"mporder_recurring_billing_cycle_period"=>$mporder_info["mporder_recurring_billing_cycle_period"],
							/*"mporder_recurring_billing_cycle"=>$mporder_info["mporder_recurring_billing_cycle"],
							"mporder_recurring_billing_cycle_frequency"=>$mporder_info["mporder_recurring_billing_cycle_frequency"],*/
							
							"mporder_date_added"=>$mporder_info["mporder_date_added"],
							"mporder_merchantsubpack_subs_frequency"=>$mporder_info["mporder_merchantsubpack_subs_frequency"],
							"site_system_name"=>Settings::getSetting("CONF_WEBSITE_NAME"),
							"site_system_admin_email"=>Settings::getSetting("CONF_ADMIN_EMAIL"),
							"paypal_bn"=>"FATbit_SP",
						 );
		 }
		 return $arr_order;
	}
	
	function chargeSubscriptionUserWallet($amount,$mporder_id){
		$mporder_info=$this->attributes;
		$user=new User();
		$user_balance=$user->getUserBalance($mporder_info["mporder_user_id"]);
		//die($user_balance."#".$amount);
		if ($user_balance>=$amount){
				$formatted_mporder_value="#".$mporder_info["mporder_invoice_number"];
				$txnArray["utxn_user_id"]=$mporder_info["mporder_user_id"];
				$txnArray["utxn_debit"]=$amount;
				$txnArray["utxn_status"]=1;
				$txnArray["utxn_mporder_id"]=$mporder_id;
				$txnArray["utxn_comments"]=sprintf(Utilities::getLabel('L_mporder_PLACED_NUMBER'),$formatted_mporder_value);
				$transObj=new Transactions();
				if($txn_id=$transObj->addTransaction($txnArray)){
					$mptran_active_from = date("Y-m-d H:i:s");
						$days =  $mporder_info['mporder_merchantsubpack_subs_frequency'];
						$mptran_active_till = date('Y-m-d H:i:s', strtotime("+".$days." Day", strtotime(date("Y-m-d H:i:s"))));
						$paypal_transaction_data = array(
							'mptran_mporder_id'		=>	$mporder_id,
							'mptran_mode'			=>	Utilities::getLabel("L_Wallet"),
							'mptran_gateway_transaction_id'	=>	'',
							'mptran_payment_type'			=>	'instant',
							'mptran_gateway_parent_transaction_id'	=>	'',
							'mptran_gateway_response'	=>	Utilities::getLabel("L_Received_Payment"),
							'mptran_amount'			=>	$amount,
							'mptran_gateway_charges'	=>	'',
							'mptran_payment_status'	=>	Utilities::getLabel("L_Completed"),
							'mptran_pending_reason'	=>	'',
							'mptran_recurring_type'	=>	'-1', //NA
							'mptran_active_from' 			=>	$mptran_active_from,
							'mptran_active_till'			=>	$mptran_active_till,
							);
						$packageTxnObj = new PackageTransactions();
						if( $packageTxnObj->addTransaction($paypal_transaction_data) ){
							//$this->log($paypal_transaction_data, "Could Not Save Package Transaction Data");
							$this->addOrderPayment(Utilities::getLabel("L_Wallet"),time(),$amount,Utilities::getLabel("L_Received_Payment"),"Payment From Wallet",true);
							//$payment_method_name,$txn_id,$amount, $comments = '', $response = '', $is_recurring=false
						}
					
				}
		}
	}
	function updateOrderInfo($mporder_id, $data){
		if( empty($data) || sizeof($data) <= 0 ){ $this->error = 'Error, in updating the order, no parameters passed to update record.'; return false; }
		$record = new TableRecord('tbl_subscription_merchant_package_orders');
		$record->assignValues($data);
		if(!$record->update(array('smt'=>'mporder_id=?', 'vals'=>array($mporder_id)))){
			$this->error = $this->db->getError();
			return false;
		}
		return true;
	}
	
	function updateOrderPaymentStatus($mporder_id, $payment_status){
		$order_info = $this->getSubscriptionOrderById($mporder_id);
		$subObj=new Subscription($order_info['mporder_user_id']);
		$subObj->SubscriptionClear();
		/****************   Update Subscription Payment Status and Initate Validity Period  ****************/
		$mporder_id = intval($mporder_id);
		if (($mporder_id < 1) || ($payment_status < 1)) return false;
		
		$record = new TableRecord('tbl_subscription_merchant_package_orders');
		$record->assignValues(array('mporder_payment_status'=>$payment_status));
		$record->assignValues(array('mporder_mpo_status_id'=>Settings::getSetting("CONF_ACTIVE_SUBSCRIPTION_STATUS")));
		if(!$record->update(array('smt'=>'mporder_id=?', 'vals'=>array($mporder_id)))){
			$this->error = $this->db->getError();
			return false;
		}
		$subcouponObj = new subscriptioncoupon();
		$coupon_info=$subcouponObj->getCouponByCode($order_info['mporder_discount_coupon']);
		if ($coupon_info){
				if (!$this->db->insert_from_array('tbl_subscription_coupons_history',  array('subscouponhistory_subscoupon_id' => (int)$coupon_info['subscoupon_id'], 'subscouponhistory_order_id' => (int)$order_info['mporder_id'], 'subscouponhistory_customer_id' => (int)$order_info['mporder_user_id'],'subscouponhistory_amount' => (float)$order_info['mporder_discount_total'],'subscouponhistory_date_added' => date('Y-m-d H:i:s')),true)){
					$this->error = $this->db->getError();
					return false;
				}
		}
		
		if ($order_info['mporder_credits_charged']==$order_info['mporder_net_charged']){
			$this->updateOrderInfo($mporder_id,array(
				'mporder_gateway_subscription_id' => "FREE - ".$order_info['mporder_id'].'-'.Utilities::getRandomPassword(11)
			)); 
		}
		
		// If order Payment status is 0 then becomes greater than 0 send main html email
		if (!$order_info['mporder_payment_status'] && $payment_status) {
			
			$cmObj=new Commissions();
			$arr['commission_setting'][] = array('vendor_id'=>(int)$order_info['mporder_user_id'],'fees'=>$order_info['mporder_merchantpack_commission']);
			$cmObj->addUpdateCommissionSettings($arr);
			
			
			
			if ($order_info['mporder_old_mporder_id']!=$order_info['mporder_id']){
				$this->updateOrderInfo( $order_info['mporder_old_mporder_id'],array('mporder_mpo_status_id' => Settings::getSetting("CONF_CANCELLED_SUBSCRIPTION_STATUS")));
			}
			
			$newProductsLimit = $order_info['mporder_merchantpack_max_products'] ;
			$newProductImageLimit = $order_info['mporder_merchantpack_max_pimages'] ;
			$products = new Products();
			$userId = $order_info['mporder_user_id'];
			$productsAddedByUser  =  $products->getTotalProductsAddedByUser($userId);
			if($productsAddedByUser>$newProductsLimit && $productsAddedByUser>0){
				$products->deleteExtraLatestProducts($userId,$newProductsLimit,$productsAddedByUser,$newProductImageLimit);
			}
				
			$emailNotificationObj=new Emailnotifications();
			$emailNotificationObj->Subscription_Details_Buyer_Admin($mporder_id);
		}
		return true;
	}
	
	
	
	function addOrderPayment($payment_method_name,$txn_id,$amount, $comments = '', $response = '', $is_recurring=false) {
		$payment_mporder_id=$this->payment_mporder_id;
		$mporder_info=$this->attributes;
		if ($mporder_info) {
			$mporder_payment_financials=$this->getOrderPaymentFinancials($payment_mporder_id);	
			$mporder_credits=$mporder_payment_financials["order_credits_charge"];
			if (($mporder_info["totPayments"]==0) && ($mporder_credits>0) && !$is_wallet && $is_recurring==false){
				$this->chargeSubscriptionUserWallet($mporder_credits,$payment_mporder_id);
			}
			$mporder_details = $this->getSubscriptionOrderById($payment_mporder_id);
			if ($amount >= $mporder_details["mporder_balance"]){
				$this->updateOrderPaymentStatus( $payment_mporder_id, 1);
			}
			if ($is_recurring){
				$this->extendSubscriptionValidity($payment_mporder_id);
			}
			return true;		
		}else{
			$this->error = "Invalid Order";
			return false;
		}
		
	}
	
	function is_date( $str ) {
			try {
				$dt = new DateTime( trim($str) );
			}
			catch( Exception $e ) {
				return false;
			}
			$month = $dt->format('m');
			$day = $dt->format('d');
			$year = $dt->format('Y');
			if( checkdate($month, $day, $year) ) {
				return true;
			}
			else {
				return false;
			}
	}

	function extendSubscriptionValidity($subscription_order_id){
		global $duration_subscription_freq_arr_val;
		$subscription_order_info = $this->getSubscriptionOrderById($subscription_order_id);
		$sub_start_date = $subscription_order_info['mporder_date_added'];
		$days = $subscription_order_info['mporder_recurring_billing_cycle_frequency'];
		$sub_last_date = $this->is_date($subscription_order_info['mporder_subscription_end_date'])?$subscription_order_info['mporder_subscription_end_date']:$sub_start_date;
		$sub_end_date = date('Y-m-d H:i:s', strtotime("+" . $days . " ".$duration_subscription_freq_arr_val[$subscription_order_info['mporder_recurring_billing_cycle_period']], strtotime($sub_last_date)) );	
		//die($days . " ".$duration_subscription_freq_arr[$subscription_order_info['mporder_recurring_billing_cycle_period']]);
		$order_update_arr = array(
						'mporder_subscription_start_date'		=>	$sub_start_date,
						'mporder_subscription_end_date'			=>	$sub_end_date,
						'mporder_subscription_expiry_email'			=>	0
				); 
				
		$this->updateOrderInfo( $subscription_order_id, $order_update_arr);
	}
	function addOrderPaymentComments($comments) {
		$payment_mporder_id=$this->payment_mporder_id;
		$mporder_info=$this->attributes;
		if ($mporder_info) {
			$this->addOrderPaymentHistory($payment_mporder_id,0,$comments,0);
		}else{
			$this->error = "Invalid Order";
			return false;
		}
	}
	
		
}