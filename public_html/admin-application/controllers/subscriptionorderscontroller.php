<?php
class SubscriptionOrdersController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,SUBSCRIPTIONORDERS);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Subscription Orders Management", Utilities::generateUrl("subscriptionorders"));
    }
	
	protected function getSearchForm() {
		$frm=new Form('frmSubscriptionOrderSearch','frmSubscriptionOrderSearch');
		$frm->setFieldsPerRow(3);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('POST');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addHiddenField('', 'subscriber','','subscriber');
		$frm->addHiddenField('', 'mode', "search");
		$fld=$frm->addTextBox('Keyword', 'keyword','','',' class="medium"');
		$fld->html_after_field='<br/><small>Name, Username, Email, Invoice Number, Profile Reference</small>';
		$fld->merge_cells=2;
		
		$fld=$frm->addDateField('Subscription Created Date From', 'date_from', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld=$frm->addDateField('Subscription Created Date To', 'date_to', '', '', 'readonly="true" class="small dateTimeFld"');
		
		$frm->addSelectBox('Subscription Status', 'subscription_status', SubscriptionOrders::subscription_status_arr(), '', 'class="small"', 'Select');
		$fld=$frm->addTextBox('Subscriber', 'subscriber_name','','subscriber_name',' class="small"');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchSubscriptionOrders(this); return false;');
        return $frm;
    }
	
	function default_action() {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $frm = $this->getSearchForm();
		$frm->fill(getQueryStringData());
        $this->set('frmPost', $frm);
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listsubscriptionorders($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
		$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
			$soObj=new SubscriptionOrders();
			$criteria=array();
			$criteria['pagesize'] = $pagesize;
			$criteria['page'] = $page;
			$criteria['exclude_void_subscription'] = true;
		
            $post = Syspage::getPostedVar();
            $page = 1;
            if (isset($post['page']) && intval($post['page']) > 0) {
                $page = intval($post['page']);
            } else {
                $post['page'] = $page;
            }
            if (!empty($post['mode'])) {
				$criteria=array_merge($criteria,$post);
                $this->set('srch', $post);
            }
           	
            $post['pagesize'] = $pagesize;
            $this->set('records', $soObj->getSubscriptionOrders($criteria));
            $this->set('pages', $soObj->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $soObj->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
			$this->set('subscription_status_arr', SubscriptionOrders::subscription_status_arr() );
			$this->set('subscription_status_assoc_arr', SubscriptionOrders::subscription_status_assoc_arr());
            $this->_template->render(false, false);
        }
    }
	
	
    function view($mporder_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("View Subscription Order", '');
		$this->set('breadcrumb', $this->b_crumb->output());
		$subscriptionOrderObj = new SubscriptionOrders();
		$packageTxnObj = new PackageTransactions();
		$oObj=new Orders();
		/* global $payment_status_arr; */
        $mporder_id = intval($mporder_id);
		$order_filters = array( 'id' => $mporder_id );
		$data = $subscriptionOrderObj->getSubscriptionOrders( $order_filters );
		if ( !$data ){
			Message::addErrorMessage('Error: Please perform this action on valid record.');
            Utilities::redirectUser(Utilities::generateUrl('subscriptionorders'));
		}
		
		$subscription_period = '';
		if( $data['mporder_subscription_start_date']!= '0000-00-00 00:00:00' && $data['mporder_subscription_end_date']!='0000-00-00 00:00:00' ){
			$subscription_period = displayDate($data['mporder_subscription_start_date']).' to '.displayDate($data['mporder_subscription_end_date']);
		}
		$data['subscription_period'] = $subscription_period;
		
		$txn_filters = array( 'mptran_mporder_id'	=>	$data['mporder_id']);
		$transactions = $packageTxnObj->getTransactions( $txn_filters );
		foreach($transactions as &$transaction){
			$transaction['mptran_gateway_response'] = unserialize($transaction['mptran_gateway_response']);
		}
		$data['transactions'] = $transactions;
		$data["comments"]=$subscriptionOrderObj->getOrderStatusHistory(array("order"=>$mporder_id));
        $this->set('order', $data);
		$this->set('subscription_status_arr', SubscriptionOrders::subscription_status_arr());
		$this->set('subscription_status_assoc_arr', SubscriptionOrders::subscription_status_assoc_arr());
		$this->set('subscription_txn_type_arr', SubscriptionOrders::subscription_txn_type_arr());
		$post = Syspage::getPostedVar();
        $this->_template->render();
    }
	
	function cancel_subscription( $mporder_id ){
		$packagesObj = new SubscriptionPackages();
		$package_order_status_arr = $packagesObj->getPackageOrderStatusAssoc();
		/*$subscription_status_assoc_arr = SubscriptionOrders::subscription_status_assoc_arr();
		$subscription_status_arr = SubscriptionOrders::subscription_user_status_arr();*/
		$subscriptionOrderObj=new SubscriptionOrderPayment($mporder_id);
		$order_filter = array(
			'id'	=>	$mporder_id,
			//'user'	=>	$this->getLoggedUserId()
		);
		$order_info = $subscriptionOrderObj->getSubscriptionOrders($order_filter);
		
		$userId = $order_info['mporder_user_id'];
		if( !$order_info || $order_info['mporder_gateway_subscription_id'] == '' ){
			Message::addErrorMessage("Invalid Request!");
			Utilities::redirectUser(Utilities::generateUrl('subscriptionorders'));
		}
		if( $order_info['mporder_mpo_status_id'] != Settings::getSetting("CONF_ACTIVE_SUBSCRIPTION_STATUS") ){
			Message::addErrorMessage("Subscription cannot be cancelled as it is not ".$package_order_status_arr[Settings::getSetting("CONF_ACTIVE_SUBSCRIPTION_STATUS")].".");
			Utilities::redirectUser(Utilities::generateUrl('subscriptionorders') );
		}
		
		if( strpos($order_info['mporder_gateway_subscription_id'], 'FREE') !== FALSE ){
			$order_update_arr = array( 'mporder_mpo_status_id' => Settings::getSetting("CONF_CANCELLED_SUBSCRIPTION_STATUS") );
			$subscriptionOrderObj->updateOrderInfo( $order_info['mporder_id'], $order_update_arr);
			$subscriptionOrderObj->addOrderHistory( $order_info['mporder_id'], Settings::getSetting("CONF_CANCELLED_SUBSCRIPTION_STATUS"),'',true );
			Utilities::redirectUser( Utilities::generateUrl('subscriptionorders','view',array($mporder_id)) );
		}
		
		$ppExpObj = new PaypalStandard();
		$result = $ppExpObj->recurringCancel($order_info['mporder_gateway_subscription_id'] );
		
		if( !isset($result['PROFILEID']) ){
			Message::addErrorMessage($result['L_LONGMESSAGE0']);
			Utilities::redirectUser( Utilities::generateUrl('subscriptionorders') );
		}
		
		if (isset($result['PROFILEID'])) {
			$order_update_arr = array( 'mporder_mpo_status_id' => Settings::getSetting("CONF_CANCELLED_SUBSCRIPTION_STATUS") );
			$subscriptionOrderObj->updateOrderInfo( $order_info['mporder_id'], $order_update_arr);
			$subscriptionOrderObj->addOrderHistory( $order_info['mporder_id'], Settings::getSetting("CONF_CANCELLED_SUBSCRIPTION_STATUS"),'',true );
			Message::addMessage("Subscription cancelled successfully!");
			Utilities::redirectUser( Utilities::generateUrl('subscriptionorders') );
		}
		
	}
	
	
	function subscribers_autocomplete(){
		$post = Syspage::getPostedVar();
		$json = array();
		$soObj=new SubscriptionOrders();
        $subscription_orders=$soObj->getDistinctOrderSubscribers(urldecode($post["keyword"]));
		foreach($subscription_orders as $okey=>$oval){
			$json[] = array(
					'data' => $oval['user_id'],
					'value' => strip_tags(htmlentities($oval['name'], ENT_QUOTES, 'UTF-8'))
				);
		}
		$sort_order = array();
		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}
		array_multisort($sort_order, SORT_ASC, $json);
		$arr["suggestions"]=$json;
		echo json_encode($arr);
		//echo json_encode($aNew);
	}
	
	
	
	
	
	
}