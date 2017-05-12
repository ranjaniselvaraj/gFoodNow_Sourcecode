<?php 
trait MerchantPackages {
	
	function packages(){
		if (!Settings::getSetting("CONF_ENABLE_SELLER_SUBSCRIPTION")) {
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
			Utilities::redirectUserReferer();
		}
		$packObj=new SubscriptionPackages();
		$criteria['pagesize'] = 30;
		$criteria['status'] = 1;
		$packages = $packObj->getSubscriptionPackages($criteria);
		$isUpdatingSubscription = false;
		
		$subscriptionOrderObj = new SubscriptionOrders();
		$order_filters = array(
				'user'					=>	$this->getLoggedUserId(),
				'subscription_status'	=>	Settings::getSetting("CONF_ACTIVE_SUBSCRIPTION_STATUS"),
				'pagesize'				=>	1
			);
		$orders = $subscriptionOrderObj->getSubscriptionOrders( $order_filters );
			
			
		if($orders){
			$mporder_id = $orders[0]['mporder_id'];
			$isUpdatingSubscription = true;
			$criteria['exclude_free_package'] = true;
			$order_row = $subscriptionOrderObj->getSubscriptionOrderById($mporder_id);
			$this->set( 'includeFreePackage', false );
			$criteria['exclude_free_package'] = true;
			if( $order_row ){
				$subObj = new Subscription();
				 $subObj->updateSubscription(  $orders[0]['mporder_id']);
				// not to show, current active subscription if any.
				$criteria['id_not_equal_to'] = $orders[0]['mporder_merchantsubpack_id'];
			}
		} else {
			$this->set( 'includeFreePackage', true );
			$order_filters = array(
				'user'					=>	$this->getLoggedUserId(),
				'subscription_status'	=>	Settings::getSetting("CONF_ACTIVE_SUBSCRIPTION_STATUS"),
				'pagesize'				=>	1
			);
			$orders = $subscriptionOrderObj->getSubscriptionOrders( $order_filters );
			if( $orders ){
				$criteria['exclude_free_package'] = true;
			}
		}
		
		if( $packages ){
			$criteria = array();
			$subPackObj = new SubPackages();
			foreach($packages as $key=>&$package){
				$criteria['merchantsubpack_merchantpack_id'] = $package['merchantpack_id'];
				$criteria['exclude_free_package'] = true;
				$package['sub_packages'] = $subPackObj->getAssocSubPackages($criteria);
				
				if( empty($package['sub_packages']) && sizeof($package['sub_packages'])==0 ){
					unset($packages[$key]);
				}
				$package['startsAt'] = $subPackObj->getCheapestPack($criteria);
				
			}
		}
		$order_filters = array(
			'user'	=>	$this->getLoggedUserId(),
			'payment_status' => 1,
		);
		$orders = $subscriptionOrderObj->getSubscriptionOrders( $order_filters );
		$total_records = $subscriptionOrderObj->getTotalRecords();
		if( $total_records ){
			$this->set( 'includeFreePackage', false );
		}
		
		$extraPageObj=new Extrapage();
		$arr_contact_content=$extraPageObj->getExtraBlockData(array('identifier'=>'PACKAGES_HEAD_CONTENT_BLOCK'));
		$extra_content=$arr_contact_content["epage_content"];
		$this->set('extra_content', $extra_content);
		
		Syspage::addCss(array('css/accountpackages.css','css/facebox.css'), false);
		
		
		$this->set( 'isSupplier', ($this->getLoggedUserType() == CONF_BUYER_SELLER_USER_TYPE) );
		$this->set( 'packages', $packages );
		$chosenPlan = Utilities::getSessionValue('cart_subscription');
		
		$this->set( 'chosenPlan', reset($chosenPlan) );
		$subscriptionObj = new subscriptionorders();
		$active_subscription = $subscriptionObj->getActiveSubscriptionDetails($this->getLoggedUserId());
		//Utilities::printarray($active_subscription);
		//die();
		$this->set('active_subscription', $active_subscription);
		$this->_template->render(true,true);
	}
	
	function subscriptions(){
		if (!Settings::getSetting("CONF_ENABLE_SELLER_SUBSCRIPTION")) {
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
			Utilities::redirectUserReferer();
		}
		$subscriptionOrderObj = new SubscriptionOrders();
		$subscription_user_status = array_keys(SubscriptionOrders::subscription_user_status_arr());
		$post = Syspage::getPostedVar();
		$page = intval($post["page"]);
		if ($page < 1){	$page = 1; }
		$pagesize = 10;
		$frmSearchForm=$this->getSubscriptionSearchForm();
		//die($frmSearchForm->getFormHtml());
		$frmSearchForm->fill($post);
		$this->set('frmSearchForm', $frmSearchForm);
		$default_status = ( !empty($post['status']) ) ? $post['status'] : $subscription_user_status;
		//$status_fld = $frmSearchForm->getField('status');
		//$status_fld->value = $default_status; //mob_logo.svg
		$order_filters = array(
			'user'					=>	$this->getLoggedUserId(),
			'subscription_status'	=>	$default_status,
			'page'					=>	$page,
			'pagesize'				=>	$pagesize,
		);
		
		if( isset($post['keyword']) && $post['keyword'] != '' ){
			$order_filters['keyword'] = $post['keyword'];
		}
		$orders = $subscriptionOrderObj->getSubscriptionOrders( $order_filters ); /*orders are itself are subscriptions. */
		$this->set( 'orders', $orders );
		$this->set('pages', $subscriptionOrderObj->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page-1)*$pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $subscriptionOrderObj->getTotalRecords();
		if ($total_records < $end_record) $end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->_template->render(TRUE , TRUE);
	}
	
	private function getSubscriptionSearchForm(){
		$frm=new Form('frmSearchSellerSubscriptions','frmSearchSellerSubscriptions');
		$frm->setFieldsPerRow(4);
		$frm->setExtra('class="siteForm ondark" rel="search"');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->setAction('?');
		$frm->setJsErrorDisplay('afterfield');
		$frm->addTextBox('', 'keyword','','','placeholder="'.Utilities::getLabel('M_Keyword').'"');
		$frm->addSelectBox('', 'status',SubscriptionOrders::subscription_user_status_arr(),'','',Utilities::getLabel('M_All_Status') );
		$fld=$frm->addSubmitButton('','btn_submit',Utilities::getLabel('M_Search'));
		$frm->getField("btn_submit")->html_after_field='&nbsp;&nbsp;<a href="?" class="btn secondary-btn">'.Utilities::getLabel('M_Clear').'</a>';
		$frm->addHiddenField('', 'page', '1');
		//$frm->addHtml('','html','<a class="buttonNormal theme-btn clear-btn" href="?">'.Utilities::getLabel('M_Clear').'</a>');
		return $frm;
	}
	
	function view_subscription($mporder_id){
		$subscriptionOrderObj = new SubscriptionOrders();
		$packageTxnObj = new PackageTransactions();
		
		$subscription_user_status = array_keys(SubscriptionOrders::subscription_user_status_arr());
			
		$order_filters = array(
			'user'	=>	$this->getLoggedUserId(),
			'subscription_status' => $subscription_user_status,
			'id'	=>	$mporder_id	
		
		);
		Syspage::addCss('css/facebox.css');
		$order = $subscriptionOrderObj->getSubscriptionOrders( $order_filters ); //orders are itself are subscriptions.
		if( !$order ){
			Message::addErrorMessage(Utilities::getLabel('M_Subscription_record_not_found'));
			Utilities::redirectUser( Utilities::generateUrl('account','subscriptions') );
		}
		
		$subscription_period = '';
		if( $order['mporder_subscription_start_date']!= '0000-00-00 00:00:00' && $order['mporder_subscription_end_date']!='0000-00-00 00:00:00' ){
			$subscription_period = Utilities::formatdate($order['mporder_subscription_start_date']).' to '.Utilities::formatdate($order['mporder_subscription_end_date']);
		}
		$order['subscription_period'] = $subscription_period;
		$this->set( 'order',$order );
		
		$valid_for_renewal_deactivate	= false;
		$can_update_plan = false;
		if( $order['mporder_mpo_status_id'] == Settings::getSetting("CONF_ACTIVE_SUBSCRIPTION_STATUS") ){
			$valid_for_renewal_deactivate	=	true;
			$can_update_plan	= true;
			$can_cancel_plan	= true;
			
		}
		
		
		$this->set( 'valid_for_renewal_deactivate', $valid_for_renewal_deactivate );
		$this->set( 'can_update_plan', $can_update_plan );
		$this->set( 'can_cancel_plan', $can_cancel_plan );
		
		$frmTxnSearchForm=$this->getSubscriptionTxnSearchForm();
		$post = Syspage::getPostedVar();
		$page = intval($post["page"]);
		if ($page < 1){	$page = 1; }
		$pagesize = 10;
		$frmTxnSearchForm->fill($post);
		$this->set('frmTxnSearchForm', $frmTxnSearchForm);
		
		$txn_filters = array(
			'mptran_mporder_id'	=>	$order['mporder_id'],
			'page'				=>	$page,
			'pagesize'			=>	$pagesize
		);
		$transactions = $packageTxnObj->getTransactions( $txn_filters );
		foreach($transactions as &$transaction){
			$transaction['mptran_gateway_response'] = unserialize($transaction['mptran_gateway_response']);
		}
		$this->set('transactions',$transactions);
		$this->set('pages', $packageTxnObj->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page-1)*$pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $packageTxnObj->getTotalRecords();
		if ($total_records < $end_record) $end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->_template->render();
	}
	
	private function getSubscriptionTxnSearchForm(){
		$frm=new Form('frmSearchSubscriptionTxns','frmSearchSubscriptionTxns');
		$frm->setFieldsPerRow(8);
		$frm->setExtra('class="siteForm ondark" rel="search"');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->setAction('?');
		$frm->setJsErrorDisplay('afterfield');
		$frm->addHiddenField('', 'page', '1');
		return $frm;
	}
	
	
	
	function cancel_subscription( $mporder_id  , $deactivateUser = 1 ){
		$packagesObj = new SubscriptionPackages();
		$package_order_status_arr = $packagesObj->getPackageOrderStatusAssoc();
		$subscriptionOrderObj=new SubscriptionOrderPayment($mporder_id);
		$order_filter = array(
			'id'	=>	$mporder_id,
			'user'	=>	$this->getLoggedUserId()
		);
		$order_info = $subscriptionOrderObj->getSubscriptionOrders($order_filter);
		if( !$order_info || $order_info['mporder_gateway_subscription_id'] == '' ){
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
			Utilities::redirectUser(Utilities::generateUrl('account','subscriptions'));
		}
		
		if( $order_info['mporder_mpo_status_id'] != Settings::getSetting("CONF_ACTIVE_SUBSCRIPTION_STATUS") ){
			Message::addErrorMessage(sprintf(Utilities::getLabel('M_Subscription_cannot_be_cancelled'),$package_order_status_arr[Settings::getSetting("CONF_ACTIVE_SUBSCRIPTION_STATUS")]));
			Utilities::redirectUser( Utilities::generateUrl('account','subscriptions') );
		}
		
		if( strpos($order_info['mporder_gateway_subscription_id'], 'FREE') !== FALSE ){
			$order_update_arr = array( 'mporder_mpo_status_id' => Settings::getSetting("CONF_CANCELLED_SUBSCRIPTION_STATUS") );
			$subscriptionOrderObj->updateOrderInfo( $order_info['mporder_id'], $order_update_arr);
			$subscriptionOrderObj->addOrderHistory( $order_info['mporder_id'], Settings::getSetting("CONF_CANCELLED_SUBSCRIPTION_STATUS"),'',true );
			Utilities::redirectUser( Utilities::generateUrl('account','view_subscription',array($mporder_id)) );
		}
		
		$ppExpObj = new PaypalStandard();
		$result = $ppExpObj->recurringCancel( $order_info['mporder_gateway_subscription_id'] );
		
		if( !isset($result['PROFILEID']) ){
			Message::addErrorMessage($result['L_LONGMESSAGE0']);
			Utilities::redirectUser( Utilities::generateUrl('account','view_subscription',array($mporder_id)) );
		}
		
		if (isset($result['PROFILEID'])) {
			$order_update_arr = array( 'mporder_mpo_status_id' => Settings::getSetting("CONF_CANCELLED_SUBSCRIPTION_STATUS") );
			$subscriptionOrderObj->updateOrderInfo( $order_info['mporder_id'], $order_update_arr);
			$subscriptionOrderObj->addOrderHistory( $order_info['mporder_id'], Settings::getSetting("CONF_CANCELLED_SUBSCRIPTION_STATUS"),'',true );
			
			Message::addMessage(Utilities::getLabel('M_Subscription_cancelled_successfully'));
			Utilities::redirectUser( Utilities::generateUrl('account','view_subscription',array($mporder_id)) );
		}
		
	}
	
	
	
	
	function confirm_change_subscription(){
		if($_SERVER['REQUEST_METHOD'] != 'POST'){
			die(Utilities::getLabel('L_Invalid_Request'));
		}
		$post = Syspage::getPostedVar();
		$mporder_id = intval($post['mporder_id']);
		
		$subscriptionOrderObj = new SubscriptionOrders();
		$packageTxnObj = new PackageTransactions();
		$subscription_status_assoc_arr = SubscriptionOrders::subscription_status_assoc_arr();
		$subscription_status_arr = SubscriptionOrders::subscription_status_arr();
		$order_filters = array(
			'user'	=>	$this->getLoggedUserId(),
			'id'	=>	$mporder_id,
			'subscription_status'=>	$subscription_status_assoc_arr['status_active']
		);
		$order = $subscriptionOrderObj->getSubscriptionOrders( $order_filters ); //orders are itself are subscriptions.
		if( !$order ){
			Message::addErrorMessage(sprintf(Utilities::getLabel('M_Record_not_found_or_subscription_not'),$subscription_status_arr[$subscription_status_assoc_arr['status_active']]));
			dieJsonError(Message::getHtml());
		}
		
		$subObj = new Subscription();
		if( !$subObj->updateSubscription( $order['mporder_id']) ){
			Message::addErrorMessage(Utilities::getLabel('M_Error_updating_record'));
			dieJsonError(Message::getHtml());
		}
		$json['status'] = true;
		$json['msg'] = 'Redirecting...';
		$json['redirectUrl'] = Utilities::generateUrl('account','packages');
		die(json_encode($json));
	}
}
?>