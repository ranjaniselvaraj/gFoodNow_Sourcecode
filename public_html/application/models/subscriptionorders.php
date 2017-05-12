<?php
class SubscriptionOrders extends Model {
	
	private $mporder_id;
	
	function __construct(){
		$this->db = Syspage::getdb();
		$this->currency = &Syspage::getCurrency();
    }
	
	static function subscription_status_arr(){
		$db = Syspage::getdb();
		$query = "SELECT sorder_status_id, sorder_status_name FROM tbl_subscription_order_status WHERE sorder_status_is_deleted='0' ORDER BY sorder_status_priority";
        $rs = $db->query($query);
        return $db->fetch_all_assoc($rs);
	}
	
	/*static function subscription_admin_status_arr(){
		return array(
			4 => 'Cancelled',
			2 => 'Active',
			3 => 'Suspended',
			7 => 'Refunded'
		);
	}*/
	
	static function subscription_user_status_arr(){
		$db = Syspage::getdb();
		$query = "SELECT sorder_status_id, sorder_status_name FROM tbl_subscription_order_status WHERE sorder_status_is_deleted='0' and sorder_status_subscriber_flag=1 ORDER BY sorder_status_priority";
        $rs = $db->query($query);
        return $db->fetch_all_assoc($rs);
		/*return array(
			2 => 'Active',
			3 => 'Suspended',
			7 => 'Refunded'
		);*/
	}
	
	static function subscription_status_assoc_arr(){
		return array(
			'status_void'		=>	0,
			'status_inactive'	=>	1,
			'status_active'		=>	2,
			'status_suspended'	=>	3,
			'status_cancelled'	=>	4,
			'status_expired'	=>	5,
			'status_pending'	=>	6,
			'status_refunded'	=>	7
		);
	}
	
	static function subscription_txn_type_arr(){
		return array(
			0 => 'Created',
			1 => 'Payment',
			2 => 'Outstanding payment',
			3 => 'Payment skipped',
			4 => 'Payment failed',
			5 => 'Cancelled',
			6 => 'Suspended',
			7 => 'Suspended from failed payment',
			8 => 'Outstanding payment failed',
			9 => 'Expired',
		);
	}
	
	function getSubscriptionOrderId(){
		return $this->mporder_id;
	}
	
	function getTotalPages(){
		return $this->total_pages;
	}
	
	function getTotalRecords(){
		return $this->total_records;
	}
	
	function getError() {
        return $this->error;
    }
	
	function getSubscriptionMsg() {
        return $this->subscription_msg;
    }
	
	function getSubscriptionOrderById($id, $add_criteria=array()){
		$id = intval($id);
        if($id>0!=true) {  return array();}
       	$add_criteria['id'] = $id;
        $srch = self::search($add_criteria);
		//die($srch->getquery());
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	
	function getSubscriptionOrders($criteria) {
        foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
//		echo($srch->getquery()."<br/><br/>");
		$srch->addOrder('mporder_id','DESC');
        $rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return false;
		}
		
		if( isset($criteria['mporder_gateway_subscription_id']) || isset($criteria['id']) ){
			return $this->db->fetch($rs);
		}
        $row = $this->db->fetch_all($rs);
        if($row==false) return array();
        else return $row;
	}
	function getSubscriptionOrder($criteria) {
		
        foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
		$srch->addOrder('mporder_id','DESC');
		// die($srch->getQuery());
        $rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return false;
		}
		
		
			return $this->db->fetch($rs);
	
       
        if($row==false) return array();
        else return $row;
	}
	
	function addUpdateSubscriptionOrder($data){
		$mporder_id = intval($data['mporder_id']);
		$mporder_user_id = intval($data['mporder_user_id']);
		$values = array(
				'mporder_old_mporder_id'	=>	$data['mporder_old_mporder_id'],
				'mporder_invoice_number'	=>	$data['mporder_invoice_number'],
				'mporder_user_id'			=>	$mporder_user_id,
				'mporder_user_name'			=>	$data['mporder_user_name'],
				'mporder_user_email'		=>	$data['mporder_user_email'],
				'mporder_user_phone'		=>	$data['mporder_user_phone'],
				'mporder_mpo_status_id'		=>	Settings::getSetting("CONF_PENDING_SUBSCRIPTION_STATUS"),
				'mporder_merchantpack_id'	=>	$data['mporder_merchantpack_id'],
				'mporder_merchantpack_name'	=>	$data['mporder_merchantpack_name'],
				'mporder_merchantpack_desc'	=>	$data['mporder_merchantpack_desc'],
				'mporder_merchantpack_commission'	=>	$data['mporder_merchantpack_commission'],
				'mporder_merchantpack_max_products'	=>	$data['mporder_merchantpack_max_products'],
				'mporder_merchantpack_max_pimages'	=>	$data['mporder_merchantpack_max_pimages'],
				'mporder_merchantsubpack_id'=>	$data['mporder_merchantsubpack_id'],
				'mporder_merchantsubpack_name'		=>	$data['mporder_merchantsubpack_name'],
				'mporder_merchantsubpack_subs_frequency'	=>	$data['mporder_merchantsubpack_subs_frequency'],
				'mporder_merchantsubpack_subs_amount'=>	$data['mporder_merchantsubpack_subs_amount'],
				
				'mporder_date_added'		=>	date('Y-m-d H:i:s'),
				'mporder_ip_address'		=>	$data['mporder_ip_address'],
				'mporder_user_agent'		=>	$data['mporder_user_agent'],
				'mporder_accept_language'	=>	$data['mporder_accept_language'],
				'mporder_language'					=>	$data['mporder_language'],
				'mporder_currency_code'				=>	$data['mporder_currency_code'],
				'mporder_currency_symbol_left'		=>	$data['mporder_currency_symbol_left'],
				'mporder_currency_symbol_right'		=>	$data['mporder_currency_symbol_right'],
				'mporder_currency_value'			=>	$data['mporder_currency_value'],
				'mporder_merchantsubpack_subs_amount'	=>	$data['mporder_merchantsubpack_subs_amount'],
				'mporder_actual_paid'			=>	$data['mporder_actual_paid'],
				'mporder_wallet_selected'			=>	$data['mporder_wallet_selected'],
				'mporder_discount_coupon'			=>	$data['mporder_discount_coupon'],
				'mporder_discount_total'			=>	$data['mporder_discount_total'],
				'mporder_reward_points'			=>	$data['mporder_reward_points'],
				'mporder_net_charged'			=>	$data['mporder_net_charged'],
				'mporder_credits_charged'			=>	$data['mporder_credits_charged'],
				'mporder_sub_total'			=>	$data['mporder_sub_total'],
				'mporder_payment_gateway_charges'			=>	$data['order_payment_gateway_charge'],
				'mporder_recurring_amount'			=>	$data['mporder_recurring_amount'],
				'mporder_recurring_discount_coupon'			=>	$data['mporder_recurring_discount_coupon'],
				'mporder_recurring_discount_total'			=>	$data['mporder_recurring_discount_total'],
				'mporder_recurring_chargeble_amount'			=>	$data['mporder_recurring_chargeble_amount'],
				'mporder_recurring_billing_cycle'			=>	$data['mporder_recurring_billing_cycle'],
				'mporder_recurring_billing_cycle_frequency'			=>	$data['mporder_recurring_billing_cycle_frequency'],
				'mporder_recurring_billing_cycle_period'			=>	$data['mporder_recurring_billing_cycle_period'],
				'mporder_is_free_trial'			=>	$data['mporder_is_free_trial'],
		);
		// printArray($values);
		$record = new TableRecord('tbl_subscription_merchant_package_orders');
		$record->assignValues($values);
		// die($record->getInsertQuery());
		// var_dump($record->addNew());
		// exit;
		
		if($mporder_id === 0 && $record->addNew()){
			
			$this->mporder_id=$record->getId();
		}elseif($mporder_id > 0 && $record->update(array('smt'=>'mporder_id=?', 'vals'=>array($mporder_id)))){
			$this->mporder_id=$mporder_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		$_SESSION['shopping_cart_subscription']["order"]=$this->getSubscriptionOrderId();
		return $this->getSubscriptionOrderId();
	}
	
	function search($criteria, $count='') {
		
		//print_r($criteria);
		$srch = new SearchBase('tbl_subscription_merchant_package_transactions', 'tsmpt');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tsmpt.mptran_mporder_id');
		$srch->addMultipleFields(array('tsmpt.mptran_mporder_id',"COALESCE(SUM(mptran_amount)) as totPayments","GROUP_CONCAT(DISTINCT(mptran_mode) SEPARATOR ',') payment_methods"));
		$qry_order_payments = $srch->getQuery();
		//die($qry_order_payments);
		
		
        $srch = new SearchBase( 'tbl_subscription_merchant_package_orders', 'tmpo' );
		$srch->joinTable('tbl_subscription_order_status', 'LEFT JOIN', 'tmpo.mporder_mpo_status_id = tsos.sorder_status_id', 'tsos');
		$srch->joinTable('tbl_users', 'LEFT OUTER JOIN', 'tmpo.mporder_user_id = tu.user_id', 'tu');
		$srch->joinTable('(' . $qry_order_payments . ')', 'LEFT OUTER JOIN', 'tmpo.mporder_id = tqop.mptran_mporder_id', 'tqop');
		if($count==true) {
            $srch->addFld('COUNT(tmpo.mporder_id) AS total_rows');
        } else {
            $srch->addMultipleFields(array('tmpo.*','COALESCE(tqop.totPayments,0)','tqop.payment_methods','tsos.sorder_status_name','GREATEST((mporder_net_charged - IFNULL(totPayments,0)),0) as mporder_balance'));
        }
		
        foreach($criteria as $key=>$val) {
			//if(strval($val)=='') continue;
            switch($key) {
            case 'id':
                $srch->addCondition('tmpo.mporder_id', '=', intval($val));
                break;
			case 'user':
			case 'subscriber':
                $srch->addCondition('tmpo.mporder_user_id', '=', intval($val));
                break;
			case 'payment_status':
                $srch->addCondition('tmpo.mporder_payment_status', '=', intval($val));
                break;
			case 'status':
                $srch->addCondition('tmpo.mporder_mpo_status_id', '=', intval($val));
                break;
			case 'date':
                $srch->addCondition('DATE(`mporder_date_added`)', '=', $val);
                break;
			case 'date_from':
                $srch->addCondition('tmpo.mporder_date_added', '>=', $val. ' 00:00:00');
                break;
			case 'date_to':
                $srch->addCondition('tmpo.mporder_date_added', '<=', $val. ' 23:59:59');
                break;
			case 'customer':
                $srch->addCondition('tmpo.mporder_user_name', '=',$val);
            break;				
			case 'keyword':
					$cndCondition=$srch->addCondition('tmpo.mporder_gateway_subscription_id', 'like', '%' . $val . '%');
					$cndCondition->attachCondition('tmpo.mporder_user_name', 'like', '%' . $val . '%','OR');
					$cndCondition->attachCondition('tu.user_username', 'like', '%' . $val . '%','OR');
					$cndCondition->attachCondition('tmpo.mporder_user_email', 'like', '%' . $val . '%','OR');
					$cndCondition->attachCondition('tmpo.mporder_merchantpack_name', 'like', '%' . $val . '%');
					$cndCondition->attachCondition('tmpo.mporder_invoice_number', 'like', '%' . $val . '%');
                break;
			case 'pagesize':
				$srch->setPageSize($val);
				break;
			case 'page':
				$srch->setPageNumber($val);
				break;
			case 'mporder_gateway_subscription_id':
			case 'gateway_subscription_id':
			case 'subscription_id':
				 $srch->addCondition('tmpo.mporder_gateway_subscription_id', '=', $val);
			break;
			case 'subscription_status':
					if (is_array($val))
						$srch->addCondition('tmpo.mporder_mpo_status_id', 'IN', (array)($val));
					else	
						$srch->addCondition('tmpo.mporder_mpo_status_id', '=', intval($val));
					break;	
				//$srch->addCondition('tmpo.mporder_gateway_subscription_status', '=', intval($val));
			break;
			case 'exclude_void_subscription':
				$srch->addCondition('tmpo.mporder_mpo_status_id', '>', 0);
			break;
			case 'subscription_date_from':
				$srch->addCondition('tmpo.mporder_subscription_start_date', '>=', $val. ' 00:00:00');
			break;
			case 'subscription_date_to':
				$srch->addCondition('tmpo.mporder_subscription_end_date', '<=', $val. ' 23:59:59');
			break;
			case 'subscription_end_date':
				$srch->addCondition('tmpo.mporder_subscription_end_date', '<', $val. ' 23:59:59');
			break;
			case 'subscription_with_in_date':
				$srch->addCondition('mysql_func_date(tmpo.mporder_subscription_start_date)', '<=', $val, 'AND', true);
				$srch->addCondition('mysql_func_date(tmpo.mporder_subscription_end_date)', '>=', $val,'AND',true);
			break;
			case 'expiry_email':
				$srch->addCondition('tmpo.mporder_subscription_expiry_email', '=', $val);
			break;
			case 'trial':
				$srch->addCondition('tmpo.mporder_is_free_trial', '=', $val);
			break;
            }
        }
		//die($srch->getquery());
       return $srch;
    }
	
	function getOrderPaymentFinancials($mporder_id){
		$order_info = $this->getSubscriptionOrderById($mporder_id);
		$cart_total = $order_info["mporder_merchantsubpack_subs_amount"];
		$user=new User();
		$user_balance=$user->getUserBalance($order_info["mporder_user_id"]);
		
		$order_credits_charge=$order_info["mporder_wallet_selected"]?min($order_info["mporder_credits_charged"],$user_balance):0;
		$order_discounts=$order_info["mporder_discount_total"];
		$order_adjustable=$order_info["mporder_amount_adjustable"];
		
		
		$order_payment_gateway_charge = $cart_total - $order_credits_charge-$order_discounts-$order_adjustable;
		
		$order_payment_summary=array("net_payable" => $cart_total,
									 "order_user_balance" => $user_balance,
									 "order_credits_charge" => $order_credits_charge,
									 "order_adjustable_charge" => $order_adjustable,
									 "order_payment_gateway_charge" => $order_payment_gateway_charge,
									 );
		return $order_payment_summary;							 
	}
	
	public function addOrderHistory($mporder_id, $order_status_id, $comment = '', $notify = false) {
		$order_info = $this->getSubscriptionOrderById($mporder_id);
		
		if (($order_info) && ($order_status_id!=$order_info['mporder_mpo_status_id'])) {
			
			if (!$this->db->update_from_array('tbl_subscription_merchant_package_orders', array('mporder_mpo_status_id' => (int)$order_status_id,'mporder_date_updated' => "mysql_func_NOW()"),
					array('smt' => 'mporder_id = ? ', 'vals' => array((int)$mporder_id)),true)){
					$this->error = $this->db->getError();
					return false;
			}
			//die($order_status_id."#");
			if (!$this->db->insert_from_array('tbl_subscription_merchant_package_order_status_history', array('mpos_history_mporder_id' => $mporder_id, 'mpos_history_mpo_status_id' => $order_status_id, 'mpos_history_date_added' => date('Y-m-d H:i:s'),'mpos_history_customer_notified' => (int)$notify,'mpos_history_comments' => $comment),true)) {
				$this->error = $this->db->getError();
				return false;
			}else{
				$comment_id = $this->db->insert_id();
			}
			// If order status is in buyer order statuses then send update email
			$osObj=new SubscriptionOrderstatus();
      	   	$subscription_status = $osObj->getData($order_status_id);
			if ($subscription_status['sorder_status_subscriber_flag'] && $notify ) {
				$emailNotificationObj=new Emailnotifications();
				$emailNotificationObj->Subscription_Status_Update_Buyer($comment_id);
			}
			
		}
		return true;
	
	}
	
	
	function chargeWallet($txnArray){
		
		
		$transObj=new Transactions();
		if($txn_id=$transObj->addTransaction($txnArray)){
			$emailNotificationObj=new Emailnotifications();
			$emailNotificationObj->sendTxnNotification($txn_id);
		}
	}
	
	function isSubscriptionActive($userId){
			//Settings::getSetting("CONF_ACTIVE_SUBSCRIPTION_STATUS")
			$subscription_status_assoc_arr = SubscriptionOrders::subscription_status_assoc_arr();
			$order_filters = array(
				'user'					=>	$userId,
				'subscription_status'	=>	Settings::getSetting("CONF_ACTIVE_SUBSCRIPTION_STATUS"),
				'subscription_with_in_date'=> date("Y-m-d H:i:s"),
			);
	
			$orders = $this->getSubscriptionOrders( $order_filters );
			
			if(is_array($orders) && count($orders)>0){
				return true;
			}else{
				return false;
			}
	}
	
	function getActiveSubscriptionDetails($userId){
			$order_filters = array(
				'user'					=>	$userId,
				'subscription_status'	=>	$subscription_status_assoc_arr['status_active'],
				'subscription_with_in_date'=> date("Y-m-d H:i:s"),
			);
			$orders=array();
			$orders = $this->getSubscriptionOrder( $order_filters );
			return $orders;
			
	}
	function getTransactions($criterias,$pagesize){
		$srch = self::search($criterias);
		if(intval($pagesize)>0){
			$srch->setPageSize($pagesize);
		}
		if( intval($pagesize)<=0 && $criterias['pagesize']<=0 ){
			$srch->doNotLimitRecords();
		}
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		//print_r($criterias);
		if( isset($criterias['mporder_gateway_subscription_id'])  ){
			return $this->db->fetch($rs);
		}
		return $this->db->fetch_all($rs);
	}
	
	function getOrderStatusHistory($criteria=array(),$pagesize=0) {
        if(count($criteria)==0) return array();
	    $srch = new SearchBase('tbl_subscription_merchant_package_order_status_history', 'tsmposh');
		$srch->joinTable('tbl_subscription_order_status', 'LEFT OUTER JOIN', 'tsmposh.mpos_history_mpo_status_id = tsos.sorder_status_id', 'tsos');
		$srch->joinTable('tbl_subscription_merchant_package_orders', 'LEFT OUTER JOIN', 'tsmposh.mpos_history_mporder_id = tsmpo.mporder_id', 'tsmpo');
        foreach($criteria as $key=>$val) {
			if(strval($val)=='') continue;
            switch($key) {
            case 'id':
                $srch->addCondition('tsmposh.mpos_history_id', '=', intval($val));
                break;
			case 'order':
                $srch->addCondition('tsmposh.mpos_history_mporder_id', '=', intval($val));
                break;
            }
        }
		if(intval($pagesize)>0){
			$srch->setPageSize($pagesize);
		}else{
			$srch->doNotLimitRecords();
		}
        $srch->doNotCalculateRecords(true);
		$srch->addOrder('mpos_history_id');
        $rs = $srch->getResultSet();
		return ($pagesize==1)?$this->db->fetch($rs):$this->db->fetch_all($rs);
	}
	
	
	function getSubscriptionDetailForEmailTemplate($order_id){
		global $payment_status_arr;
		$order_details=$this->getSubscriptionOrderById($order_id);
		
		$free_trial_package = $order_details['mporder_is_free_trial']==1?' - '.Utilities::getLabel('L_Free_Trial'):'';
		
		$str='<table cellspacing="0" cellpadding="0" border="0" width="100%" style="border:1px solid #ddd; border-collapse:collapse;">';
		$str.='<tr><td width="40%" style="padding:10px;background:#eee;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;">'.Utilities::getLabel('L_Invoice_Number').'</td><td width="60%" style="padding:10px;font-size:13px; border:1px solid #ddd;color:#333; ">'.$order_details['mporder_invoice_number'].'</td></tr>';
		$str.='<tr><td width="40%" style="padding:10px;background:#eee;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;">'.Utilities::getLabel('L_Customer_Name').'</td><td width="60%" style="padding:10px;font-size:13px; border:1px solid #ddd;color:#333; ">'.$order_details['mporder_user_name'].'</td></tr>';
		$str.='<tr><td width="40%" style="padding:10px;background:#eee;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;">'.Utilities::getLabel('L_Customer_Email').'</td><td width="60%" style="padding:10px;font-size:13px; border:1px solid #ddd;color:#333; ">'.$order_details['mporder_user_email'].'</td></tr>';
		$str.='<tr><td width="40%" style="padding:10px;background:#eee;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;">'.Utilities::getLabel('L_Payment_Status').'</td><td width="60%" style="padding:10px;font-size:13px; border:1px solid #ddd;color:#333; ">'.$payment_status_arr[$order_details['mporder_payment_status']].'</td></tr>';
		$str.='<tr><td width="40%" style="padding:10px;background:#eee;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;">'.Utilities::getLabel('L_Package_Name').'</td><td width="60%" style="padding:10px;font-size:13px; border:1px solid #ddd;color:#333; ">'.$order_details['mporder_merchantpack_name'].' ('.$order_details['mporder_merchantsubpack_name'].') '.$free_trial_package.'</td></tr>';
		$str.='<tr><td width="40%" style="padding:10px;background:#eee;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;">'.Utilities::getLabel('L_Package_Charges').'</td><td width="60%" style="padding:10px;font-size:13px; border:1px solid #ddd;color:#333; ">'.$this->currency->format($order_details['mporder_merchantsubpack_subs_amount'],$order_details['mporder_currency_code'],$order_details['mporder_currency_value']).'</td></tr>';
		if ($order_details['mporder_discount_total']>0){
		$str.='<tr><td width="40%" style="padding:10px;background:#eee;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;">'.Utilities::getLabel('L_Package_Discount').'</td><td width="60%" style="padding:10px;font-size:13px; border:1px solid #ddd;color:#333; ">'.$this->currency->format($order_details['mporder_discount_total'],$order_details['mporder_currency_code'],$order_details['mporder_currency_value']).' ('.$order_details['mporder_discount_coupon'].')</td></tr>';
		}
		if ($order_details['mporder_reward_points']>0){
		$str.='<tr><td width="40%" style="padding:10px;background:#eee;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;">'.Utilities::getLabel('L_Reward_Points').'</td><td width="60%" style="padding:10px;font-size:13px; border:1px solid #ddd;color:#333; ">'.$this->currency->format($order_details['mporder_reward_points'],$order_details['mporder_currency_code'],$order_details['mporder_currency_value']).' ('.$order_details['mporder_reward_points'].')</td></tr>';
		}
		$str.='<tr><td width="40%" style="padding:10px;background:#eee;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;">'.Utilities::getLabel('L_Net_Charged').'</td><td width="60%" style="padding:10px;font-size:13px; border:1px solid #ddd;color:#333; ">'.$this->currency->format($order_details['mporder_net_charged'],$order_details['mporder_currency_code'],$order_details['mporder_currency_value']).'</td></tr>';
		if ($order_details['mporder_is_free_trial']!=1){
		$str.='<tr><td width="40%" style="padding:10px;background:#eee;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;">'.Utilities::getLabel('L_Billing_Cycle').'</td><td width="60%" style="padding:10px;font-size:13px; border:1px solid #ddd;color:#333; ">'.$order_details['mporder_recurring_billing_cycle_frequency'].' '.Utilities::getLabel('L_Days').'</td></tr>';
		$str.='<tr><td width="40%" style="padding:10px;background:#eee;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;">'.Utilities::getLabel('L_Recurring_Charges').'</td><td width="60%" style="padding:10px;font-size:13px; border:1px solid #ddd;color:#333; ">'.$this->currency->format($order_details['mporder_recurring_chargeble_amount'],$order_details['mporder_currency_code'],$order_details['mporder_currency_value']).'</td></tr>';
		}
		$str.='</table>';
		return $str;
	 }
	 
	 function checkUserHasAnyActiveSubscription($user_id){
			 if (!Settings::getSetting("CONF_ENABLE_SELLER_SUBSCRIPTION")) return true;
			 $order_filters = array(
			'user'					=>	$user_id,
			'subscription_status'	=>	Settings::getSetting("CONF_ACTIVE_SUBSCRIPTION_STATUS"),
			);
			$orders = $this->getSubscriptionOrders( $order_filters );
			return ($orders && is_array($orders))?true:false;
	 }
	 
	 function getDistinctOrderSubscribers($name) {
        $srch = new SearchBase('tbl_subscription_merchant_package_orders', 'tsmpo');
		$srch->joinTable('tbl_users', 'LEFT OUTER JOIN', 'tsmpo.mporder_user_id = tu.user_id', 'tu');
		$srch->addMultipleFields(array("tu.user_id","CONCAT(tu.user_name,' (',tu.user_username,')') as name"));
		$cndCondition=$srch->addCondition('tu.user_name', 'like', '%' . $name . '%');
		$cndCondition->attachCondition('tu.user_username', 'like', '%' . $name . '%','OR');
		$srch->setPageSize(10);
		$srch->addGroupBY('mporder_user_id');
        $rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		if($this->total_records < 1){
			return false;
		}
        return $this->db->fetch_all($rs);
		
	}
	
	function addPaymentGatewayResponse($data){
		$record = new TableRecord('tbl_subscription_payment_gateway_responses');
		$data["subgatewaytxn_date"]= date('Y-m-d H:i:s');// 'mysql_func_NOW()';
		$record->assignValues($data);
		if($record->addNew()){
			return intval($record->getId());
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return false;
	}
	
	function checkUserHasAnyActiveSubscriptionAbtToExpire($user_id){
			 if ((!Settings::getSetting("CONF_ENABLE_SELLER_SUBSCRIPTION")) || (!Settings::getSetting("CONF_SUBSCRIPTION_EXPIRY_EMAIL")) || (isset($_COOKIE['subsription_expire']))) return false;
			 if (self::checkUserHasAnyActiveSubscription($user_id)){
				 $subscription=self::getActiveSubscriptionDetails($user_id);
				 if ($subscription && ($subscription['mporder_is_free_trial']==1) && (strtotime($subscription['mporder_subscription_end_date']) < strtotime('+'.Settings::getSetting("CONF_SUBSCRIPTION_EXPIRY_EMAIL_DAYS").' day'))){
					$this->subscription_msg=sprintf(Utilities::getLabel('L_Subscription_About_Expire'),Utilities::formatDate($subscription['mporder_subscription_end_date']),Utilities::generateUrl('account','packages'));
				 	return true;
				 }
			 }
	 }
}
?>