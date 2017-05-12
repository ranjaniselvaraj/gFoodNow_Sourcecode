<?php
require_once CONF_APPLICATION_PATH.'utilities/MerchantPackages.php';
class AccountController extends CommonController{
	use MerchantPackages;
	function __construct($model, $controller, $action){
		parent::__construct($model, $controller, $action);
		Utilities::checkLogin();
		$user_id = $this->getLoggedUserId();
		$userObj=new User($user_id);
		$this->user_details=$userObj->getUserById($user_id,array("status"=>1));
		if (empty($this->user_details) && ($action!="logout")){
			Utilities::redirectUser(Utilities::generateUrl('account','logout'));
		}
		$this->set('user_details',$this->user_details);	
		$this->set('controller',$controller);
		$this->set('action',$action);
		if (!$userObj->canAccessMyAccountPage($action) && ($action!="logout")){
			if (!empty($userObj->getError())){
				Message::addErrorMessage($userObj->getError());
			}
			Utilities::redirectUser($userObj->getRedirectPage());
		}
		$display_buyer_supplier_tab=$userObj->getBuyerSupplierTab();
//echo($display_buyer_supplier_tab."#".$_SESSION["buyer_supplier_tab"]);
		$_SESSION["buyer_supplier_tab"]=$display_buyer_supplier_tab;
		$this->set('buyer_supplier_tab',$display_buyer_supplier_tab);
		$this->canAccessProductsArea = UserPermissions::canAccessProductsArea($this->getLoggedUserId());
		$this->set('canAccessProductsArea',$this->canAccessProductsArea);
		$subscriptionOrderObj = new SubscriptionOrders();
		if ($subscriptionOrderObj->checkUserHasAnyActiveSubscriptionAbtToExpire($user_id) && ($display_buyer_supplier_tab=='S') && (Message::getErrorCount()==0)) { 
			Message::addErrorMessage($subscriptionOrderObj->getSubscriptionMsg()); 
		}
		
		
	}
	function default_action(){
		if ($this->user_details['user_type']==CONF_ADVERTISER_USER_TYPE){
			$dashboard_url=Utilities::generateUrl('account','dashboard_advertiser');
		}elseif ($this->user_details['user_type']==CONF_BUYER_USER_TYPE){
			$dashboard_url=Utilities::generateUrl('account','dashboard_buyer');
		}elseif ($this->user_details['user_type']==CONF_SELLER_USER_TYPE){
			$dashboard_url=Utilities::generateUrl('account','dashboard_supplier');
		}elseif ($this->user_details['user_type']==CONF_BUYER_SELLER_USER_TYPE){
			if ($this->user_details['user_buyer_supp_pref']=="B")
				$dashboard_url=Utilities::generateUrl('account','dashboard_buyer');
			else
				$dashboard_url=Utilities::generateUrl('account','dashboard_supplier');	
		}
		Utilities::redirectUser($dashboard_url);
	}
	function dashboard_buyer(){
		$orderObj=new Orders();
		$userObj=new User();
		$buyer_orders=$orderObj->getChildOrders(array("customer"=>$this->getLoggedUserId(),"status"=>(array)Settings::getSetting("CONF_BUYER_ORDER_STATUS"),"pagesize"=>5));
		$this->set('buyer_orders',$buyer_orders);
		$criterias = array("to"=>$this->getLoggedUserId(),"order"=>"message_id");
		$messages=$userObj->getMessages($criterias,10);
		$this->set('messages',$messages);
		$this->_template->render();	
	}
	function dashboard_supplier(){
		$userObj=new User();
		foreach($userObj->getUserSales($this->getLoggedUserId()) as $saleskey=>$salesval ){
			$sales_earnings_chart_data.="['".$saleskey."', ".round($salesval,2)."],";	
		}
		$dashboard_info['sales_earnings_chart_data']=rtrim($sales_earnings_chart_data,',');
		$orderObj=new Orders();
		$sales_orders=$orderObj->getChildOrders(array("vendor"=>$this->getLoggedUserId(),"status"=>(array)Settings::getSetting("CONF_VENDOR_ORDER_STATUS"),"pagesize"=>5));
		$this->set('sales_orders',$sales_orders);
		$this->set('dashboard_info',$dashboard_info);		
		$criterias = array("to"=>$this->getLoggedUserId(),"order"=>"message_id");
		$messages=$userObj->getMessages($criterias,10);
		$this->set('messages',$messages);
		$this->_template->render();	
	}
	function recent_activity(){
		$frm = new Form('frmSearch', 'frmSearch');
		$frm->addHiddenField('', 'page', 1);
		$frm->addHiddenField('', 'pagesize', 18);
		$this->set('frmSearch', $frm);
		$this->_template->render();	
	}
	public function logout($p){
		require_once (CONF_INSTALLATION_PATH . 'public/includes/APIs/facebook/facebook.php');
		unset($_SESSION['logged_user']);
		$userObj=new User();
		if ($userObj->deleteRememberMeToken($this->getLoggedUserId())){
			setcookie('remembertoken', '', time()-3600,'/');
		}else{
			Message::addErrorMessage($userObj->getError());
			Utilities::redirectUserReferer();
		}
		$facebook = new Facebook(array(
			'appId' => Settings::getSetting("CONF_FACEBOOK_APP_ID"),
			'secret' => Settings::getSetting("CONF_FACEBOOK_APP_SECRET"),
			));
		$user = $facebook->getUser();
		if ($user) {
			$app_id = Settings::getSetting("CONF_FACEBOOK_APP_ID");	
			if (isset($_SESSION['fb_' . $app_id . '_code'])) {
				unset ($_SESSION['fb_' . $app_id . '_code']);
			}
			if (isset($_SESSION['fb_' . $app_id . '_access_token'])) {
				unset ($_SESSION['fb_' . $app_id . '_access_token']);
			}
			if (isset($_SESSION['fb_' . $app_id . '_user_id'])) {
				unset ($_SESSION['fb_' . $app_id . '_user_id']);
			}
		}
		unset($_SESSION['access_token']);
		Utilities::redirectUser(Utilities::generateUrl('user','account',array($p)));
	}
	function ajax_recent_activity($page){
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$userObj=new User();
			$psObj= new Products();
			$psObj->setPageSize(4);
			$user_id=$this->getLoggedUserId();
			$post = Syspage::getPostedVar();
			$page = 1;
			if(isset($post['page']) && intval($post['page']) > 0) $page = intval($post['page']); 
			else $post['page'] = $page;
			$pagesize = isset($post['pagesize'])?$post['pagesize']:10;
			$post['pagesize'] = $pagesize;
			$activities=$userObj->getUserActivities($user_id,$post);
			foreach($activities as $key=>$val){
				$shop_products=array();
				if ($val["type"]==2){
					$shop_products=$psObj->getProducts(array("shop"=>$val["shop_id"]));
				}
				$arr_shop_prods=array("products"=>$shop_products);
				$user_activities[]=array_merge($val,$arr_shop_prods);
			}
			$this->set('arr_listing',$user_activities);
			$total_pages=$userObj->getTotalPages();
			$this->set('pages',$total_pages);
			$this->set('page', $page);
			$this->set('start_record', ($page-1)*$pagesize + 1);
			$end_record = $page * $pagesize;
			$total_records = $userObj->getTotalRecords();
			if ($total_records < $end_record) $end_record = $total_records;
			$this->set('end_record', $end_record);
			$this->set('total_records', $total_records);
			$this->set('search_parameter',$get);
			$this->set('loggedin_user',$this->user_details);
			die(convertToJson(array('total_pages'=>$total_pages, 'html'=>$this->_template->render(false,false,NULL,true))));
		}
		die(0);	
	}
	function sales(){
		$post = Syspage::getPostedVar();
		$arr=$post;
		unset($arr["page"]);
		$page = intval($post["page"]);
		$frmSearchForm=$this->getSalesSearchForm();
		$frmSearchForm->fill($arr);
		if ($page < 1)
			$page = 1;
		$pagesize = 10;
		$user_id=$this->getLoggedUserId();
		$criteria=array("vendor"=>$user_id,"pagesize"=>$pagesize);
		if (in_array($post["status"],(array)Settings::getSetting("CONF_VENDOR_ORDER_STATUS"))){
			$criteria=array_merge($criteria,array("status"=>$post["status"]));
			unset($post["status"]);
		}else{
			$criteria=array_merge($criteria,array("status"=>(array)Settings::getSetting("CONF_VENDOR_ORDER_STATUS")));
		}
		$criterias=array_merge($post,$criteria);
		$orderObj=new Orders();
		$sales_order=$orderObj->getChildOrders($criterias);
		$this->set('arr_listing',$sales_order);
		$this->set('pages', $orderObj->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page-1)*$pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $orderObj->getTotalRecords();
		if ($total_records < $end_record) $end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->set('search_parameter',$get);
		$this->set('frm',$frmSearchForm);
		$this->_template->render();
	}
	protected function getSalesSearchForm()	{
		$frm=new Form('frmSearchSalesOrder','frmSearchSalesOrder');
		$frm->setFieldsPerRow(9);
		$frm->setExtra('class="siteForm ondark" rel="search"');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addHiddenField('', 'page', '1');
		$fld=$frm->addTextBox('', 'keyword','','','placeholder="'.Utilities::getLabel('M_Keyword').'"');
		$orderStsObj=new Orderstatus();
		$frm->addSelectBox('', 'status',$orderStsObj->getAssociativeArray((array)Settings::getSetting("CONF_VENDOR_ORDER_STATUS")));
		$fld=$frm->addTextBox('', 'date_from', '', '', 'placeholder="'.Utilities::getLabel('L_Date_From').'" class="date-pick calendar" readonly ');
		$fld=$frm->addTextBox('', 'date_to', '', '', 'placeholder="'.Utilities::getLabel('L_Date_To').'" class="date-pick calendar" readonly ');
		$fldMinPrice=$frm->addTextBox('', 'minprice_vendor','','','placeholder="'.Utilities::getLabel('M_From').' ['.CONF_CURRENCY_SYMBOL.']'.'"')->requirements()->setFloatPositive($val=true);
		$fldMaxPrice=$frm->addTextBox('', 'maxprice_vendor','','','placeholder="'.Utilities::getLabel('M_To').' ['.CONF_CURRENCY_SYMBOL.']'.'"')->requirements()->setFloatPositive($val=true);
		$frm->addSubmitButton('','btn_submit',Utilities::getLabel('M_Search'),'','class="btn primary-btn"');
		$frm->addHtml('', 'htmlNote','<a class="btn secondary-btn" href="?">'.Utilities::getLabel('M_Clear').'</a>');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setAction('?');
		$this->set('frm', $frm);
		return $frm;
	}
	
	function get_values_for_keys($mapping, $keys) {
		foreach($mapping as $key) {
			$output_arr[] = $key[$keys];
		}
		return $output_arr;
	}

	function sales_view_order($opr_id){
		$orderObj=new Orders();
		$processing_statuses=$orderObj->getVendorAllowedUpdateOrderStatuses();
		$user_id=$this->getLoggedUserId();
		$order_detail = $orderObj->getOrderProductsByOprId($opr_id,array("vendor"=>$user_id,"status"=>(array)Settings::getSetting("CONF_VENDOR_ORDER_STATUS")));
		if (!$order_detail){
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_ACCESS'));
			Utilities::redirectUserReferer();
		}
		$osObj=new Orderstatus();
		if ($order_detail['opr_product_type']==1){
			$order_statuses = $this->get_values_for_keys($osObj->getOrderStatuses(array('digital'=>0)),"orders_status_id");
			$processing_statuses = array_intersect((array)$order_statuses,$processing_statuses);
			$processing_statuses=array_merge((array)$processing_statuses,(array)Settings::getSetting("CONF_DEFAULT_PAID_ORDER_STATUS"));
		}elseif ($order_detail['opr_product_type']==2){
			$order_statuses = $this->get_values_for_keys($osObj->getOrderStatuses(array('digital'=>1)),"orders_status_id");
			$processing_statuses = array_intersect((array)$order_statuses,$processing_statuses);
			$processing_statuses=array_merge((array)$processing_statuses,(array)Settings::getSetting("CONF_DEFAULT_PAID_ORDER_STATUS"));
		}
		if ($order_detail['opr_cod_order']==1){
			$processing_statuses=array_merge((array)$processing_statuses,(array)Settings::getSetting("CONF_DEFAULT_COD_ORDER_STATUS"));
		}
		$order_detail["address"]=$orderObj->getOrderBillingShippingAddress($order_detail["opr_order_id"]);
		$order_detail["comments"]=$orderObj->getOrderComments(array("opr"=>$opr_id));
		$this->set('order_detail',$order_detail);
		$frm=$this->getOrderCommentsForm($order_detail,$processing_statuses);
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				if (in_array($order_detail["opr_status"],$processing_statuses) && in_array($post["opr_status"],$processing_statuses)){
					if($orderObj->addChildOrderHistory($opr_id,$post["opr_status"],$post["comments"],$post["customer_notified"],$post["tracking_number"])){	
						Message::addMessage(Utilities::getLabel('M_Your_action_performed_successfully'));	
						Utilities::redirectUser(Utilities::generateUrl('account', 'sales_view_order',array($opr_id)));
					}else{
						Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
					}
				}else{
					Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
				}
			}
			$frm->fill($post);
		}
		$this->set('frm',$frm);
		$this->set('display_form',(in_array($order_detail['opr_status'],$processing_statuses)));
		$this->_template->render();
	}
	
	function sales_print_order($opr_id){
		$orderObj=new Orders();
		$user_id=$this->getLoggedUserId();
		$order_detail = $orderObj->getOrderProductsByOprId($opr_id,array("vendor"=>$user_id,"status"=>(array)Settings::getSetting("CONF_VENDOR_ORDER_STATUS")));
		if (!$order_detail){
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_ACCESS'));
			Utilities::redirectUserReferer();
		}
		$order_detail["address"]=$orderObj->getOrderBillingShippingAddress($order_detail["opr_order_id"]);
		//Utilities::printarray($order_detail);
		//die();
		$this->set('order_detail',$order_detail);
		$this->_template->render(false,false);
	}
	
	protected function getOrderCommentsForm($data,$processing_statuses){
		$frm=new Form('ordersDetail');
		$frm->setExtra(' validator="OrderfrmValidator" class="siteForm"');
		$frm->setValidatorJsObjectName('OrderfrmValidator');
		$frm->setLeftColumnProperties('valign="top" align="left"');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="editformTable"');
		$frm->captionInSameCell(true);
		$frm->setFieldsPerRow(1);
		$frm->setJsErrorDisplay('afterfield');
		$frm->addTextArea('<label>'.Utilities::getLabel('M_Your_Comments').'</label>', 'comments', '', 'comments');
		$orderStsObj=new Orderstatus();
		$frm->addSelectBox('<label>'.Utilities::getLabel('M_Status').'</label>', 'opr_status',$orderStsObj->getAssociativeArray($processing_statuses,$data["opr_status"]),$data["opr_status"], 'class="auto"','','opr_status');
		$fldBL=$frm->addTextBox('<span id="div_tracking_number"><label>'.Utilities::getLabel('M_Tracking_Number').'</label>', 'tracking_number','','', '','tracking_number','');
		$fldBL->html_after_field='</span>';
		$frm->addCheckBox(Utilities::getLabel('M_Notify_Customer'), 'customer_notified','1','notify_customer');
		$frm->addSubmitButton('&nbsp;', 'btn_submit', Utilities::getLabel('M_Update'));
		return $frm;
	}
	function cancel_order($opr_id){
		$orderObj=new Orders();
		$processing_statuses=$orderObj->getVendorAllowedUpdateOrderStatuses();
		$user_id=$this->getLoggedUserId();
		$order_detail = $orderObj->getOrderProductsByOprId($opr_id,array("vendor"=>$user_id,"status"=>(array)Settings::getSetting("CONF_VENDOR_ORDER_STATUS")));
		if (!$order_detail){
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_ACCESS'));
			Utilities::redirectUserReferer();
		}
		$order_detail["address"]=$orderObj->getOrderBillingShippingAddress($order_detail["opr_order_id"]);
		$order_detail["comments"]=$orderObj->getOrderComments(array("opr"=>$opr_id));
		$arr_pro_com=$orderObj->getNotAllowedOrderCancellationStatuses();
		if (in_array($order_detail["opr_status"],$arr_pro_com)){
			Message::addErrorMessage(sprintf(Utilities::getLabel('L_this_order_already'),"<i>".$order_detail["orders_status_name"]."</i>"));
			$not_eligible=true;
			$this->set('not_eligible', $not_eligible);	
		}
		$this->set('order_detail',$order_detail);
		$frm=$this->getOrderCancelForm();
		$frm->fill(array('user_id'=>$user_id,'opr_id'=>$opr_id));
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				if($orderObj->addChildOrderHistory($opr_id,Settings::getSetting("CONF_DEFAULT_CANCEL_ORDER_STATUS"),$post["comments"],true)){	
					Message::addMessage(Utilities::getLabel('M_Your_action_performed_successfully'));	
					Utilities::redirectUser(Utilities::generateUrl('account', 'sales_view_order',array($opr_id)));
				}else{
					Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
				}
			}
			$frm->fill($post);
		}
		$this->set('frm',$frm);
		$this->_template->render();
		}
	protected function getOrderCancelForm(){
		$frm=new Form('ordersDetail');
		$frm->setExtra(' validator="OrderfrmValidator" class="siteForm"');
		$frm->setValidatorJsObjectName('OrderfrmValidator');
		$frm->setLeftColumnProperties('valign="top" align="left"');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="editformTable"');
		$frm->captionInSameCell(true);
		$frm->setFieldsPerRow(1);
		$frm->setJsErrorDisplay('afterfield');
		$fld=$frm->addTextArea('', 'comments', '', 'comments');
		$fld->requirements()->setRequired(true);
		$fld->requirements()->setCustomErrorMessage(Utilities::getLabel('L_Reason_cancellation'));
		$frm->addSubmitButton('&nbsp;', 'btn_submit', Utilities::getLabel('M_Update'));
		$frm->setRequiredStarPosition('x');
		return $frm;
	}
	protected function getMessageSearchForm()	{
		$frm=new Form('frmMessage','frmMessage');
		$frm->setExtra('rel="search"');
		$frm->setMethod('POST');
		$frm->setFieldsPerRow(2);
		$frm->addHiddenField('', 'sts','inbox');
		$frm->addHiddenField('', 'page','1');
		$frm->addTextBox('', 'keyword','','keyword','Placeholder="'.Utilities::getLabel('M_Search_Here').'"');
		$frm->addSubmitButton('','btn_submit','','');
		$this->set('frm', $frm);
		return $frm;
	}
	function messages(){
		$post = Syspage::getPostedVar();
		$page = intval($post["page"]);
		if ($page < 1)
			$page = 1;
		$pagesize = 10;
		$user_id=$this->getLoggedUserId();
		$frm=$this->getMessageSearchForm();
		$criterias = array("user"=>$user_id);
		$criterias=array_merge($criterias,$post);
		$userObj=new User();
		$messages=$userObj->getMessagesThreads($criterias,$pagesize);
		$this->set('arr_listing',$messages);
		$this->set('pages', $userObj->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page-1)*$pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $userObj->getTotalRecords();
		if ($total_records < $end_record) $end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->set('search_parameter',$get);
		$this->set('sts', $post["sts"]);
		unset($post["page"]);
		$frm->fill($post);
		$this->set('frm',$frm);
		$this->_template->render();	
	}
	function view_message($thread_id,$message_id){
		$user_id=$this->getLoggedUserId();
		$userObj=new User();
		$thread_detail=$userObj->getMessageThread($thread_id,$user_id);
		if (!$thread_detail){
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_ACCESS'));
			Utilities::redirectUserReferer();
		}
		$frm=$this->send_message_form();
		if(!$userObj->markUserMessageRead(intval($thread_id), $user_id)){
			Message::addErrorMessage($userObj->getError());
		}
		$criteria=array("all"=>$user_id,"thread"=>$thread_id,"order"=>"message_id","orderby"=>"ASC");
		$messages=$userObj->getMessages($criteria);
		$post = Syspage::getPostedVar();
		$this->set('thread',$thread_detail);
		$this->set('messages',$messages);
		$this->set('message',$message_id);
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				$message_sent_to=$thread_detail["message_from"]==$user_id?$thread_detail["message_to"]:$thread_detail["message_from"];
				$arr=array_merge($post,array("message_sent_to"=>$message_sent_to,"user_id"=>$user_id,"thread_id"=>$thread_id));
				if($userObj->addThreadMessage($arr)){
					Message::addMessage(Utilities::getLabel('M_YOUR_MESSAGE_SENT_SUCCESSFULLY'));	
					Utilities::redirectUser(Utilities::generateUrl('account', 'view_message',array($thread_id,$message_id)));
				}else{
					Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
				}
			}
			$frm->fill($post);
		}
		$this->set('frm',$frm);
		$this->_template->render();
	}
	protected function send_message_form(){
		$frm = new Form('frmSendMessage','frmSendMessage');
		$fld = $frm->addTextArea("", 'message_text');
		$fld->requirements()->setCustomErrorMessage(Utilities::getLabel('L_Please_enter_your_message'));
		$fld->requirements()->setRequired(true);
		$frm->addSubmitButton('', 'btn_submit', Utilities::getLabel('M_Send'), 'btn_submit');
		$frm->setRequiredStarWith('caption');
		$frm->setRequiredStarPosition('xx');
		$frm->setExtra('class="siteForm"');
		$frm->captionInSameCell(true);
		$frm->setValidatorJsObjectName('frmValidator');
		$frm->setJsErrorDisplay('afterfield');
		return $frm;
	}
	protected function getRechargeWalletForm()	{
		$frm=new Form('frmCreditsWalletForm','frmCreditsWalletForm');
		$frm->setExtra('class="siteForm"');
		$frm->setFieldsPerRow(2);
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$fldMinPrice=$frm->addRequiredField('', 'amount','','',' class="enter-amount" placeholder="'.Utilities::getLabel('L_Enter_Amount_to_be_Added').'"');
		$fldMinPrice->requirements()->setFloatPositive($val=true);
		$fldMinPrice->requirements()->setRange(1,10000);
		$fldMinPrice->requirements()->setCustomErrorMessage(Utilities::getLabel('L_Invalid_Amount_Enter_Value_Between_1_10000'));
		$fldButton = $frm->addSubmitButton('','btn_submit',Utilities::getLabel('L_Add_Money_to_Wallet'),'','class="btn primary-btn"');
		
		//$fldMinPrice->attachField($fldButton);
		$frm->setJsErrorDisplay('afterfield');
		$frm->setAction(Utilities::generateUrl('account','credits'));
		$this->set('frm', $frm);
		$frm->setJsErrorDisplay('alertbox');
		return $frm;
	}
	function credits(){
		$wrObj=new WalletRecharge();
		$post = Syspage::getPostedVar();
		$frm = $this->getRechargeWalletForm();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				$arr=array_merge($post,array("member_id"=>$this->getLoggedUserId(),"member_type"=>"U"));
				if($wr_txn_id=$wrObj->saveRechargeRequest($arr)){
					Utilities::redirectUser(Utilities::generateUrl('wallet_pay','recharge',array($wr_txn_id)));
				}else{
					Message::addErrorMessage($wrObj->getError());
				}
			}
		}
		$page = intval($post["page"]);
		if ($page < 1)
			$page = 1;
		$pagesize = 10;
		$user_id=$this->getLoggedUserId();
		$criterias=array_merge(array("user"=>$user_id),$post);
		$transactionObj=new Transactions();
		$transactions=$transactionObj->getTransactions($criterias,$pagesize);
		$this->set('my_credits',$transactions);
		$this->set('pages', $transactionObj->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page-1)*$pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $transactionObj->getTotalRecords();
		if ($total_records < $end_record) $end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->set('search_parameter',$post);
		$this->set('points_summary',$transactionObj->getTransactionSummary($criterias));
		$this->set('walletfrm',$frm);
		$this->_template->render();	
	}
	function reward_points(){
		$post = Syspage::getPostedVar();
		$page = intval($post["page"]);
		if ($page < 1)
			$page = 1;
		$pagesize = 10;
		$user_id=$this->getLoggedUserId();
		$criterias=array_merge(array("user"=>$user_id),$post);
		$rewardPointObj=new Rewards();
		$my_rewards=$rewardPointObj->getRewardPoints($criterias,$pagesize);
		$this->set('my_rewards',$my_rewards);
		$this->set('pages', $rewardPointObj->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page-1)*$pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $rewardPointObj->getTotalRecords();
		if ($total_records < $end_record) $end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->set('search_parameter',$post);
		$this->_template->render();	
	}
	function request_withdrawal(){
		$userObj=new User();
		$frm = $this->getWithdrawalForm();
		$frm->fill($this->user_details);
		$post = Syspage::getPostedVar();
		$user_id=$this->getLoggedUserId();
		$balance=$userObj->getUserBalance($user_id);
		$last_withdrawal=$userObj->getUserLastWithdrawalRequest($user_id);
		$minimum_withdraw_limit=Settings::getSetting("CONF_MIN_WITHDRAW_LIMIT");
		$unsettled_ppc_balance = $userObj->getUserUnsettledPPCPayment($user_id);
		$final_withdrawable_balance = $balance - $unsettled_ppc_balance; 
		if ($final_withdrawable_balance < $minimum_withdraw_limit){
			Message::addErrorMessage(sprintf(Utilities::getLabel('L_Withdrawal_Request_Minimum_Balance_Less'),Utilities::displaymoneyformat($minimum_withdraw_limit)));
			Utilities::redirectUserReferer();
		}
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				$arr=array_merge($post,array("ub_user_id"=>$user_id));
				if($userObj->updateUserBankDetails($arr)){
					if (($minimum_withdraw_limit>$post["withdrawal_amount"])){
						Message::addErrorMessage(sprintf(Utilities::getLabel('L_Withdrawal_Request_Less'),Utilities::displaymoneyformat($minimum_withdraw_limit)));
						$error=true;
					}
					if (($post["withdrawal_amount"]>$final_withdrawable_balance)){
						Message::addErrorMessage(Utilities::getLabel('L_Withdrawal_Request_Greater'));
						$error=true;
					}
					if ($last_withdrawal && (strtotime($last_withdrawal["withdrawal_request_date"] . "+".Settings::getSetting("CONF_MIN_INTERVAL_WITHDRAW_REQUESTS")." days") - time())>0){
						$next_withdrawal_date=date('d M,Y',strtotime($last_withdrawal["withdrawal_request_date"] . "+".Settings::getSetting("CONF_MIN_INTERVAL_WITHDRAW_REQUESTS")." days"));
						Message::addErrorMessage(sprintf(Utilities::getLabel('L_Withdrawal_Request_Date'),Utilities::formatDate($last_withdrawal["withdrawal_request_date"]),Utilities::formatDate($next_withdrawal_date),Settings::getSetting("CONF_MIN_INTERVAL_WITHDRAW_REQUESTS")));
						$error=true;
					}
					if ($error==false){
						if($userObj->addWithdrawalRequest(array_merge($post,array("ub_user_id"=>$user_id)))){
							$emailNotificationObj=new Emailnotifications();
							if ($emailNotificationObj->SendWithdrawRequestNotification($userObj->getWithdrawalRequestId(),"A")){
								Message::addMessage(Utilities::getLabel('L_Withdrawal_Request_Successfully'));
								Utilities::redirectUser(Utilities::generateUrl('account', 'credits'));
							}else{
								Message::addErrorMessage($emailNotificationObj->getError());
							}
						}else{
							Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
						}
					}
				}else{
					Message::addErrorMessage(Utilities::getLabel('M_Error_details_not_saved'));
				}
			}
		}
		$this->set('frmWithdrawalInfo', $frm);
		$this->set('final_withdrawable_balance',$final_withdrawable_balance);
		$this->_template->render();	
	}
	private function getWithdrawalForm(){
		$frm = new Form('frmChangeEmail', 'frmChangeEmail');
		$frm->setExtra('class="siteForm"');
		$frm->setTableProperties('class="tbl-twocell"');
		$frm->setLeftColumnProperties('width="30%"');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setRequiredStarWith('caption');
		$fld=$frm->addHtml('', 'htmlNote','<span class="panelTitleHeading">'.Utilities::getLabel('M_Withdrawal_account_details').'</span>','&nbsp;');
		$fld->merge_caption=true;
		$fld=$frm->addRequiredField('<label>'.Utilities::getLabel('L_Amount_to_be_Withdrawn').' ['.CONF_CURRENCY_SYMBOL.']'.'</label>', 'withdrawal_amount', null, 'user_account_holder_name');
		$frm->addRequiredField('<label>'.Utilities::getLabel('M_Bank_Name').'</label>', 'ub_bank_name', null, 'ub_bank_name');
		$frm->addRequiredField('<label>'.Utilities::getLabel('M_Account_Holder_Name').'</label>', 'ub_account_holder_name', null, 'user_account_holder_name');
		$frm->addRequiredField('<label>'.Utilities::getLabel('M_Account_Number').'</label>', 'ub_account_number', null, 'user_account_number');
		$frm->addTextBox('<label>'.Utilities::getLabel('M_IFSC_Swift_Code').'</label>', 'ub_ifsc_swift_code', null, 'ub_ifsc_swift_code');
		$frm->addTextArea('<label>'.Utilities::getLabel('M_Bank_Address').'</label>', 'ub_bank_address', null, 'ub_bank_address')->requirements()->setRequired();
		$fld=$frm->addTextArea('<label>'.Utilities::getLabel('M_Other_Info_Instructions').'</label>', 'withdrawal_comments');
		$frm->setTableProperties(' width="100%" border="0" class="editformTable" cellpadding="0" cellspacing="0"');	
		$fld_submit = $frm->addSubmitButton(null, 'btn_submit', Utilities::getLabel('M_Send_Request'), 'btn_submit', '');
		return $frm;
	}
	function orders(){
		$post = Syspage::getPostedVar();
		$page = intval($post["page"]);
		$arr=$post;
		unset($arr["page"]);
		$frmSearchForm=$this->getBuyerSearchForm();
		$frmSearchForm->fill($arr);
		if ($page < 1)
			$page = 1;
		$pagesize = 10;
		$user_id=$this->getLoggedUserId();
		$criteria=array("customer"=>$user_id,"pagesize"=>$pagesize);
		if (in_array($post["status"],(array)Settings::getSetting("CONF_BUYER_ORDER_STATUS"))){
			$criteria=array_merge($criteria,array("status"=>$post["status"]));
			unset($post["status"]);
		}else{
			$criteria=array_merge($criteria,array("status"=>(array)Settings::getSetting("CONF_BUYER_ORDER_STATUS")));
		}
		foreach($post as $key=>$value){
			if($key=='maxprice' || $key=='minprice'){
					if(!is_numeric($post[$key])){
						$post[$key]='';
					}
			}
		}
		$criterias=array_merge($post,$criteria);
		$orderObj=new Orders();
		$sales_order=$orderObj->getChildOrders($criterias);
		$this->set('arr_listing',$sales_order);
		$this->set('pages', $orderObj->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page-1)*$pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $orderObj->getTotalRecords();
		if ($total_records < $end_record) $end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->set('search_parameter',$get);
		$orderStatusObj=new Orderstatus();
		$order_sts_arr=$orderStatusObj->getAssociativeArray();
		$this->set('order_sts_arr',$order_sts_arr);
		$this->set('frm',$frmSearchForm);
		$this->_template->render();
	}
	protected function getBuyerSearchForm()	{
		$frm=new Form('frmSearchBuyerOrder','frmSearchBuyerOrder');
		$frm->setFieldsPerRow(8);
		$frm->setExtra('class="siteForm ondark" rel="search"');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addHiddenField('', 'page', '1');
		$frm->addTextBox('', 'keyword','','','placeholder="'.Utilities::getLabel('M_Keyword').'"');
		$orderStsObj=new Orderstatus();
		$frm->addSelectBox('', 'status',$orderStsObj->getAssociativeArray((array)Settings::getSetting("CONF_BUYER_ORDER_STATUS")),'','',Utilities::getLabel('L_SELECT'));
		$fld=$frm->addTextBox('', 'date_from', '', '', ' placeholder="'.Utilities::getLabel('L_Date_From').'" readonly class="date-pick calendar"');
		$fld=$frm->addTextBox('', 'date_to', '', '', ' placeholder="'.Utilities::getLabel('L_Date_To').'" readonly class="date-pick calendar"');
		$frm->addTextBox('', 'minprice','','','placeholder="'.Utilities::getLabel('L_Order_From').' ['.CONF_CURRENCY_SYMBOL.']'.'"')->requirements()->setFloatPositive($val=true);
		$frm->addTextBox('', 'maxprice','','','placeholder="'.Utilities::getLabel('L_Order_To').' ['.CONF_CURRENCY_SYMBOL.']'.'"')->requirements()->setFloatPositive($val=true);
		$frm->addSubmitButton('','btn_submit',Utilities::getLabel('M_Search'),'','class="btn primary-btn"');
		$frm->addHtml('','html','<a class="btn secondary-btn" href="?">'.Utilities::getLabel('M_Clear').'</a>');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setAction('?');
		$this->set('frm', $frm);
		return $frm;
	}
	
	function view_order($order,$child_order){
		$primary_order_display=0;
		$user_id=$this->getLoggedUserId();
		$criteria=array("user"=>$user_id,"order"=>$order,"id"=>$child_order,"pagesize"=>1);
		$orderObj=new Orders();
		$primary_order_detail = $orderObj->getOrderById($order,array("user"=>$user_id));
		if (!(isset($primary_order_detail) && $primary_order_detail)){
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_ACCESS'));
			Utilities::redirectUserReferer();
		}
		$order_detail = $orderObj->getChildOrders($criteria);
		$order_detail["addresses"]=$orderObj->getOrderBillingShippingAddress($order);
		if (isset($child_order)){
			$order_detail["invoice_number"]=$order_detail['opr_order_invoice_number'];
			$order_detail["status_name"]=' - '.$order_detail['orders_status_name'];
			$order_detail["products"][]=$orderObj->getOrderProductsByOprId($child_order);
		}else{
			$order_detail["invoice_number"]=$primary_order_detail['order_invoice_number'];
			$order_detail["products"]=$orderObj->getOrderProductsById($order);
		}
		if ((!isset($child_order)) || ($primary_order_detail["totChildOrders"]==1)){
			$order_detail["comments"]=$orderObj->getOrderComments(array("order"=>$order));	
			$order_detail["payments"]=$orderObj->getOrderPayments(array("order"=>$order));
			$primary_order_display=1;
		}else{
			$order_detail["comments"]=$orderObj->getOrderComments(array("opr"=>$child_order));
		}
		//die($child_order."###");
		$order_detail["primary_order"]=$primary_order_display;
		$this->set('order_detail',$order_detail);
		$this->set('child_order',$child_order);
		$this->_template->render();
	}
	
	function print_order($order,$child_order){
		$primary_order_display=0;
		$user_id=$this->getLoggedUserId();
		$criteria=array("user"=>$user_id,"order"=>$order,"id"=>$child_order,"pagesize"=>1);
		$orderObj=new Orders();
		$primary_order_detail = $orderObj->getOrderById($order,array("user"=>$user_id));
		if (!(isset($primary_order_detail) && $primary_order_detail)){
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_ACCESS'));
			Utilities::redirectUserReferer();
		}
		$order_detail = $orderObj->getChildOrders($criteria);
		$order_detail["addresses"]=$orderObj->getOrderBillingShippingAddress($order);
		if (isset($child_order)){
			$order_detail["invoice_number"]=$order_detail['opr_order_invoice_number'];
			$order_detail["status_name"]=' - '.$order_detail['orders_status_name'];
			$order_detail["products"][]=$orderObj->getOrderProductsByOprId($child_order);
		}else{
			$order_detail["invoice_number"]=$primary_order_detail['order_invoice_number'];
			$order_detail["products"]=$orderObj->getOrderProductsById($order);
		}
		if ((!isset($child_order)) || ($primary_order_detail["totChildOrders"]==1)){
			$primary_order_display=1;
		}
		$order_detail["primary_order"]=$primary_order_display;
		$this->set('order_detail',$order_detail);
		$this->set('child_order',$child_order);
		$this->_template->render(false,false);
	}
	
	
	function feedback($id){
		$userObj=new User();
		$order_eligible_feedback=true;
		$user_id=$this->getLoggedUserId();
		$criteria=array("customer"=>$user_id,"id"=>$id,"status"=>(array)Settings::getSetting("CONF_BUYER_ORDER_STATUS"),"pagesize"=>1);
		$orderObj=new Orders();
		$order_detail=$orderObj->getChildOrders($criteria);
		if (!$order_detail || Utilities::is_multidim_array($order_detail) || !(Settings::getSetting("CONF_ALLOW_REVIEWS"))){
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_ACCESS') );
			Utilities::redirectUserReferer();
		}
		if (!in_array($order_detail["opr_status"],(array)Settings::getSetting("CONF_REVIEW_READY_ORDER_STATUS"))){
			Message::addErrorMessage(Utilities::getLabel('L_Feedback_can_be_placed_delivered_completed'));
			$order_eligible_feedback=false;
		}
		$productFeedbackObj=new Productfeedbacks();
		$product_feedback=$productFeedbackObj->getProductFeedback('',array("order"=>$order_detail["opr_id"]));
		if ($product_feedback==true){
			Message::addErrorMessage(Utilities::getLabel('M_You_have_already_submitted_feedback'));
			$order_eligible_feedback=false;
		}
		$this->set('order_detail',$order_detail);
		$frm=$this->getFeedbackForm();
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit']) && ($order_eligible_feedback)){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
				$frm->fill($post);
			}else{
				$post=array_merge($post,array("user_id"=>$user_id,"product_id"=>$order_detail["opr_product_id"],"opr_id"=>$order_detail["opr_id"]));
				if($userObj->addProductFeedback($post)){
					if (Settings::getSetting("CONF_REVIEW_ALERT_EMAIL")){
						$emailNotificationObj=new Emailnotifications();
						$emailNotificationObj->SendProductFeedbackNotification($userObj->getProductFeedbackId());
					}
					Message::addMessage(Utilities::getLabel('L_Your_review_submitted_successfully'));	
				}else{
					Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
				}
			}
		}
		$this->set('frmFeedbackForm',$frm);
		$this->_template->render();
	}
	private function getFeedbackForm(){
		$frm = new Form('frmSendMessage','frmSendMessage');
		$frm->setTableProperties('width="100%" border="0" cellspacing="10" cellpadding="10"');
		$frm->setLeftColumnProperties('width="30%"');
		$fld=$frm->addSelectBox('<label>'.Utilities::getLabel('L_Product_Rating').'</label>', "review_rating", array("1"=>"1","2"=>"2","3"=>"3","4"=>"4","5"=>"5"), "", "", Utilities::getLabel('L_Rate'), "star-rating");
		$fld->requirements()->setRequired(true);
		$fld->html_before_field='<div class="rating-f">';
		$fld->html_after_field='</span>';
		$fld = $frm->addTextArea('<label>'.Utilities::getLabel('L_Tell_experience_product').'</label>', 'review_text');
		$fld->requirements()->setRequired(true);
		$frm->addSubmitButton('', 'btn_submit', Utilities::getLabel('M_Send'), 'btn_submit');
		$frm->setLeftColumnProperties('width="25%" valign="top" align="left"');
		$frm->setExtra('class="siteForm" validator="frmValidator" autocomplete="off"');
		$frm->captionInSameCell(true);
		$frm->setValidatorJsObjectName('frmValidator');
		$frm->setJsErrorDisplay('afterfield');
		return $frm;
	}
	function return_request($id){
		$userObj=new User();
		$user_id=$this->getLoggedUserId();
		$criteria=array("customer"=>$user_id,"id"=>$id,"status"=>(array)Settings::getSetting("CONF_BUYER_ORDER_STATUS"),"pagesize"=>1);
		$orderObj=new Orders();
		$order_detail=$orderObj->getChildOrders($criteria);
		$order_eligible_return_request=true;
		if (!in_array($order_detail["opr_status"],(array)$orderObj->getBuyerAllowedOrderReturnStatuses())){
			Message::addErrorMessage(Utilities::getLabel('L_Return_Refund_cannot_placed'));
			Utilities::redirectUserReferer();
		}
		if (!$order_detail || Utilities::is_multidim_array($order_detail)){
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_ACCESS'));
			Utilities::redirectUserReferer();
		}
		if (($order_detail["opr_cod_order"])){
			Message::addErrorMessage(Utilities::getLabel('L_Return_Refund_cannot_cod_placed'));
			Utilities::redirectUserReferer();
		}
		$pObj=new Products();
		$return_request=$pObj->getProductReturnRequests(array("order"=>$order_detail["opr_id"],"pagesize"=>1),1);
		if ($return_request==true){
			Message::addErrorMessage(Utilities::getLabel('L_Already_submitted_request_order'));
			$order_eligible_return_request=false;
		}
		$this->set('order_detail',$order_detail);
		$frm=$this->getReturnRequestForm($order_detail);
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit']) && ($order_eligible_return_request)){	
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
				$frm->fill($post);
			}else{
				$arr=array_merge($post,array("user_id"=>$user_id,"opr_id"=>$order_detail["opr_id"]));
				if($userObj->addProductReturnRequest($arr)){
					$orderObj->addChildOrderHistory($order_detail["opr_id"],Settings::getSetting("CONF_RETURN_REQUEST_ORDER_STATUS"),Utilities::getLabel('M_Buyer_Raised_Return_Request'),1);
					$emailNotificationObj=new Emailnotifications();
					if ($emailNotificationObj->SendReturnNotification($userObj->getReturnRequestMessageId())){
						Message::addMessage(Utilities::getLabel('L_Your_request_submitted'));	
					}else{
						Message::addErrorMessage($emailNotificationObj->getError());
					}
				}else{
					Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
				}
			}
		}
		$this->set('frmReturnInfo',$frm);
		$this->_template->render();
	}
	function view_request($view_request){
		$user_id=$this->getLoggedUserId();
		$withdrawalRequestObj=new WithdrawalRequests();
		$request_detail=$withdrawalRequestObj->getWithdrawRequestData($view_request,array("user"=>$user_id));
		if (!$request_detail){
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_ACCESS'));
			Utilities::redirectUserReferer();
		}
		$this->set('request_detail',$request_detail);
		$this->_template->render();
	}
	private function getReturnRequestForm($info){
		$frm = new Form('frmReturnRequestForm', 'frmReturnRequestForm');
		$frm->setExtra('class="siteForm"');
		$frm->setLeftColumnProperties('width="30%"');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setRequiredStarWith('caption');
		$opr_qty=isset($info["opr_qty"])?$info["opr_qty"]:1;
		for($k=1;$k<=$opr_qty;$k++){
			$return_qty_array[$k]=$k;
		}
		$fld=$frm->addSelectBox('<label>'.Utilities::getLabel('L_Return_Qty').'</label>', 'refund_qty',$return_qty_array,'1',1,'');
		$returnReasonsObj=new Returnreasons();
		$arr_return_reasons=$returnReasonsObj->getAssociativeArray();
		$fld=$frm->addSelectBox('<label>'.Utilities::getLabel('L_Reason_for_request').'</label>', 'refund_reason',$arr_return_reasons,'',1,Utilities::getLabel('L_Select_Reason'));
		$fld->requirements()->setRequired(true);
		$fld->requirements()->setCustomErrorMessage(Utilities::getLabel('L_Please_select_your_reason'));
		$frm->addRadioButtons('<label>'.Utilities::getLabel('L_want_refund_replace').'</label>', 'refund_or_replace',array("RP"=>Utilities::getLabel('M_Replace'),"RE"=>Utilities::getLabel('M_Refund')),"RP",2,'class="mediumtable" ');
		$fld = $frm->addTextArea('<label>'.Utilities::getLabel('L_Comments').'</label>', 'refmsg_text');
		$fld->requirements()->setRequired(true);
		$fld_submit = $frm->addSubmitButton(null, 'btn_submit', Utilities::getLabel('M_Send_Request'), 'btn_submit', '');
		return $frm;
	}
	function publications(){
		$post = Syspage::getPostedVar();
		$page = intval($post["page"]);
		if ($page < 1)
			$page = 1;
		$pagesize = 10;
		$user_id=$this->getLoggedUserId();
		$user_shop_id = 0;
		if (!empty($this->user_details['shop_id']))
			$user_shop_id = $this->user_details['shop_id'];
		$criteria=array("shop"=>$user_shop_id,"sort"=>"rece");
		$criteria=array_merge($criteria,$post);
		$psObj= new Products(true,true);
		$psObj->joinWithDetailTable();
		$psObj->joinWithCategoryTable();
		$psObj->joinWithBrandsTable();
		$psObj->joinWithPromotionsTable();
		$psObj->addSpecialPrice();
		$psObj->selectFields(array('tp.*','ts.shop_id','ts.shop_user_id','ts.shop_name','ts.shop_title','tu.user_id','tu.user_name','tu.user_username','tu.user_email','IF(prod_stock >0, "1", "0" ) as available'));
		$psObj->setPagesize($pagesize);
		$psObj->setPageNumber($page);
		$this->set('arr_listing',$psObj->getProducts($criteria));
		$this->set('pages', $psObj->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page-1)*$pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $psObj->getTotalRecords();
		if ($total_records < $end_record) $end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->set('keyword',$post["keyword"]);
		$subscriptionOrderObj = new SubscriptionOrders();
		if (!$subscriptionOrderObj->checkUserHasAnyActiveSubscription($user_id))  
		Message::addErrorMessage(Utilities::getLabel('M_ERROR_YOU_DONOT_HAVE_ACTIVE_SUBSCRIPTION')); 
		
		$this->_template->render();	
		}
	function paused_publications(){
		$post = Syspage::getPostedVar();
		$page = intval($post["page"]);
		if ($page < 1)
			$page = 1;
		$pagesize = 10;
		$user_id=$this->getLoggedUserId();
		$user_shop_id = 0;
		if (!empty($this->user_details['shop_id']))
			$user_shop_id = $this->user_details['shop_id'];
		$criteria=array("shop"=>$user_shop_id,"pending"=>1,"pagesize"=>$pagesize,"page"=>$page,"sort"=>"rece");
		$criteria=array_merge($criteria,$post);
		$psObj= new Products(false,true);
		$psObj->joinWithDetailTable();
		$psObj->joinWithCategoryTable();
		$psObj->joinWithBrandsTable();
		$psObj->joinWithPromotionsTable();
		$psObj->addSpecialPrice();
		$psObj->selectFields(array('tp.*','ts.shop_id','ts.shop_user_id','ts.shop_name','ts.shop_title','tu.user_id','tu.user_name','tu.user_username','tu.user_email','IF(prod_stock >0, "1", "0" ) as available'));
		$psObj->setPagesize($pagesize);
		$psObj->setPageNumber($page);
		
		$this->set('arr_listing',$psObj->getProducts($criteria));
		$this->set('pages', $psObj->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page-1)*$pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $psObj->getTotalRecords();
		if ($total_records < $end_record) $end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->set('keyword',$post["keyword"]);
		$this->_template->render();	
	}
	function return_requests(){
		$post = Syspage::getPostedVar();
		$post["sts"]=in_array($_REQUEST["sts"],array("sent","received","all"))?$_REQUEST["sts"]:"all";
		$this->set('sts', $post["sts"]);
		$page = intval($post["page"]);
		if ($page < 1)
			$page = 1;
		$pagesize = 10;
		$user_id=$this->getLoggedUserId();
		$criteria=array("user"=>$user_id,"return"=>$post["sts"],"pagesize"=>$pagesize,"page"=>$page);
		$criteria=array_merge($criteria,$post);
		$prodObj=new Products();
		$this->set('arr_listing',$prodObj->getProductReturnRequests($criteria));
		$this->set('pages', $prodObj->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page-1)*$pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $prodObj->getTotalRecords();
		if ($total_records < $end_record) $end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->set('keyword',$post["keyword"]);
		$this->_template->render();	
	}
	function view_return_request($id){
		$userObj=new User();
		$user_id=$this->getLoggedUserId();
		$prodObj=new Products();
		$request_detail = $prodObj->getProductReturnRequests(array("id"=>$id,"user"=>$user_id,"return"=>"all","pagesize"=>1),1);
		if (!$request_detail){
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
			Utilities::redirectUserReferer();
		}
		$request_detail["messages"] = $prodObj->getReturnRequestMessages($id);
		$this->set('order_detail',$order_detail);
		$frm=$this->return_request_message_form();
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){	
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
				$frm->fill($post);
			}else{
				$file_error = false;
				if(is_uploaded_file($_FILES['refmsg_attachment']['tmp_name'])){
					if (Utilities::isUploadedFileValidFile($_FILES['refmsg_attachment'])){
						if(!Utilities::saveFile($_FILES['refmsg_attachment']['tmp_name'],$_FILES['refmsg_attachment']['name'], $attachment, 'messages/')){
							Message::addError($attachment);
						}
						$post["attachment"]=$attachment;
					}else{
						$valid_mime_types = preg_replace('~\r?\n~', "\n", Settings::getSetting("CONF_FILE_EXT_ALLOWED"));
						$valid_arr = explode("\n", $valid_mime_types);
						foreach($valid_arr as $vkey=>$vval){
							$valid_extensions .= "'".$vval."',";
						}
						$valid_extensions = substr($valid_extensions,0,strlen($valid_extensions)-1);
						$valid_file_extension_msg = sprintf(Utilities::getLabel('L_unsupported_file_type'),$valid_extensions);
						Message::addErrorMessage($valid_file_extension_msg);
						$file_error=true;
					}
				}
				$arr=array_merge($post,array("user_id"=>$user_id,"id"=>$id,"type"=>"U"));
				if (!$file_error){
					if($userObj->addProductReturnRequestMessage($arr)){
						$emailNotificationObj=new Emailnotifications();
						if ($emailNotificationObj->SendReturnRequestMessageNotification($userObj->getReturnRequestMessageId())){
							Message::addMessage(Utilities::getLabel('M_YOUR_MESSAGE_SENT_SUCCESSFULLY'));	
						}else{
							Message::addErrorMessage($emailNotificationObj->getError());
						}
					}else{
						Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
					}
				}
				Utilities::redirectUser(Utilities::generateUrl('account', 'view_return_request',array($id)));
			}
		}
		$this->set('frm',$frm);
		$this->set('request_detail', $request_detail);
		$this->_template->render();	
	}
	private function return_request_message_form(){
		$frm = new Form('frmSendMessage','frmSendMessage');
		$fld = $frm->addTextArea("", 'refmsg_text');
		$fld->requirements()->setCustomErrorMessage(Utilities::getLabel('L_Please_enter_your_message'));
		$fld->requirements()->setRequired(true);
		$fld=$frm->addFileUpload(Utilities::getLabel('M_Attachment'), 'refmsg_attachment', 'refmsg_attachment','onchange = "validateFileSize(this)" class="upload"');
		$frm->addSubmitButton('', 'btn_submit', Utilities::getLabel('M_Send'), 'btn_submit');
		$frm->setRequiredStarWith('caption');
		$frm->setRequiredStarPosition('xx');
		$frm->setExtra('class="siteForm"');
		$frm->captionInSameCell(true);
		$frm->setValidatorJsObjectName('frmValidator');
		$frm->setJsErrorDisplay('afterfield');
		return $frm;
	}
	function escalate_request($request_id) {
		$userObj=new User();
		$prodObj=new Products();
		$user_id=$this->getLoggedUserId();
		$request=$prodObj->getReturnRequest($request_id,array("user"=>$user_id,"return"=>"all","status"=>0));
		if ($request){
			if($userObj->escalateRequest($request_id,$user_id,'U')){
				$emailNotificationObj=new Emailnotifications();
				if ($emailNotificationObj->SendReturnRequestStatusChangeNotification($request_id)){
					Message::addMessage(Utilities::getLabel('L_Your_request_sent'));	
				}else{
					Message::addErrorMessage($emailNotificationObj->getError());
				}
			}else{
				Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
			}
			Utilities::redirectUser(Utilities::generateUrl('account', 'view_return_request',array($request_id)));
		}else{
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_ACCESS'));
			Utilities::redirectUserReferer();
		}
	}
	function withdraw_request($request_id) {
		$userObj=new User();
		$prodObj=new Products();
		$user_id=$this->getLoggedUserId();
		$request=$prodObj->getReturnRequest($request_id,array("user"=>$user_id,"status"=>array("0","1")));
		if ($request && $request["refund_user_id"]==$user_id){
			if($userObj->withdrawRequest($request_id,$user_id,'U')){
				$emailNotificationObj=new Emailnotifications();
				if ($emailNotificationObj->SendReturnRequestStatusChangeNotification($request_id)){
					Message::addMessage(Utilities::getLabel('L_Request_Withdrawn'));	
				}else{
					Message::addErrorMessage($emailNotificationObj->getError());
				}
			}else{
				Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
			}
			Utilities::redirectUser(Utilities::generateUrl('account', 'return_requests'));
		}else{
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_ACCESS'));
			Utilities::redirectUserReferer();
		}
	}
	function approve_request($request_id) {
		$userObj=new User();
		$prodObj=new Products();
		$user_id=$this->getLoggedUserId();
		$request=$prodObj->getReturnRequest($request_id,array("user"=>$user_id,"status"=>array("0","1")));
		if ($request && $request["shop_user_id"]==$user_id){
			if($userObj->approveRequest($request_id,$user_id,'U')){
				$emailNotificationObj=new Emailnotifications();
				if ($emailNotificationObj->SendReturnRequestStatusChangeNotification($request_id)){
					Message::addMessage(Utilities::getLabel('L_Request_Approved_Refund'));	
				}else{
					Message::addErrorMessage($emailNotificationObj->getError());
				}
			}else{
				Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
			}
			Utilities::redirectUser(Utilities::generateUrl('account', 'return_requests'));
		}else{
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_ACCESS'));
			Utilities::redirectUserReferer();
		}
	}
	function download_attachment($reqmsg) {
		$prodObj=new Products();
		$user_id=$this->getLoggedUserId();
		$request_message=$prodObj->getReturnRequestMessage($reqmsg,array("user"=>$user_id));
		if ($request_message){
			Utilities::outputFile("messages/".$request_message["refmsg_attachment"],false,false,'',false);
		}else{
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_NOT_AUTHORIZED'));
			Utilities::redirectUser(Utilities::generateUrl('account'));
		}
	}
	function cancellation_requests(){
		$post = Syspage::getPostedVar();
		$post["sts"]=in_array($_REQUEST["sts"],array("sent","received","all"))?$_REQUEST["sts"]:"all";
		$this->set('sts', $post["sts"]);
		$page = intval($post["page"]);
		if ($page < 1)
			$page = 1;
		$pagesize = 10;
		$user_id=$this->getLoggedUserId();
		$criteria=array("user"=>$user_id,"return"=>$post["sts"],"pagesize"=>$pagesize,"page"=>$page);
		$criteria=array_merge($criteria,$post);
		$crObj=new CancelRequests();
		$this->set('arr_listing',$crObj->getCancelRequests($criteria));
		$this->set('pages', $crObj->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page-1)*$pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $crObj->getTotalRecords();
		if ($total_records < $end_record) $end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->set('keyword',$post["keyword"]);
		$this->_template->render();	
	}
	function remove_product($id) {
		$user_id=$this->getLoggedUserId();
		$prodObj=new Products(false,true);
		$product_details = $prodObj->getProducts(array("added_by"=>$user_id,"id"=>$id));
		if ($product_details==true){
			if($prodObj->deleteProduct(intval($id),$user_id)){
				Message::addMessage(Utilities::getLabel('M_Record_deleted'));
			}else{
				Message::addErrorMessage($prodObj->getError());
			}
		}else{
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
		}
		Utilities::redirectUserReferer();
	}
	function finalize_product($id) {
		$prodObj=new Products();
		$user_id=$this->getLoggedUserId();
		$product_details = $prodObj->getProducts(array("added_by"=>$user_id,"id"=>$id));
		if ($product_details==true){
			if($prodObj->finalizeUserProduct(intval($id), $user_id)){
				Message::addMessage(Utilities::getLabel('L_Finalized_Successfully'));
			}else{
				Message::addErrorMessage($prodObj->getError());
			}
		}else{
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
		}
		Utilities::redirectUserReferer();
	}
	function product_status($id,$mod='block') {
		$prodObj=new Products(false,true);
		$user_id=$this->getLoggedUserId();
		$product_details = $prodObj->getProducts(array("added_by"=>$user_id,"id"=>$id));
		if ($product_details==true){
			if($prodObj->updateProductStatus(intval($id),$mod)){
				Message::addMessage(Utilities::getLabel('L_Status_modified_successfully'));
			}else{
				Message::addErrorMessage($prodObj->getError());
			}
		}else{
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
		}
		Utilities::redirectUserReferer();
	}
	function favorites(){
		$userObj=new User();
		$this->set('favourite_items', $userObj->getUserFavoriteItems(
			array(
				"user"=>$this->getLoggedUserId(),
				"favorite"=>$this->getLoggedUserId()
				)));
		$user_lists=$userObj->getUserLists($this->getLoggedUserId());
		foreach($user_lists as $key=>$val){
			$list_products=$userObj->getUserListProducts(array("list"=>$val["ulist_id"],"favorite"=>$this->getLoggedUserId(),
				"pagesize"=>4));
			$user_lists_items[]=array_merge($val,array("list_products"=>$list_products));
		}
	//Utilities::printArray($user_lists_items);
	//die();
		$this->set('user_lists',$user_lists_items);
		$frm=$this->getListForm();
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				$arr=array_merge($post,array("user_id"=>$this->getLoggedUserId()));
				if($userObj->addList($arr)){
					Message::addMessage(Utilities::getLabel('M_New_list_added'));	
					Utilities::redirectUser(Utilities::generateUrl('account', 'favorites'));
				}else{
					Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
				}
			}
			$frm->fill($post);
		}
		$this->set('frm',$frm);
		$this->_template->render();	
	}
	protected function getListForm()	{
		$frm=new Form('frmList','frmList');
		$frm->setRequiredStarWith('caption');
		$frm->setValidatorJsObjectName('system_validator');
		$frm->setExtra('class="siteForm"');
		$frm->setAction('?');
		$frm->captionInSameCell(true);
		$frm->setFieldsPerRow(1);
		$frm->setLeftColumnProperties('align="Left"');
		$frm->setTableProperties(' width="100%" cellpadding="0" cellspacing="0" border="0"');
		$frm->addRequiredField('<label>'.Utilities::getLabel('F_Create_New_List').'</label>', 'ulist_title','','name','Placeholder="'.Utilities::getLabel('M_List_Name').'" title="'.Utilities::getLabel('M_List_Name').'"');
		$frm->addSubmitButton('','btn_submit',Utilities::getLabel('F_Create'),'');
		$frm->setJsErrorDisplay('afterfield');
		$this->set('frm', $frm);
		return $frm;
	}
	function delete_list($id) {
		$userObj=new User();
		$user_id=$this->getLoggedUserId();
		$list_details = $userObj->getUserList($id,$user_id);
		if ($list_details==true){
			if($userObj->deleteUserList(intval($id), $user_id)){
				Message::addMessage(Utilities::getLabel('M_List_deleted'));
			}else{
				Message::addErrorMessage($userObj->getError());
			}
		}else{
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
		}
		Utilities::redirectUserReferer();
	}
	function ajax_load_favorite_products_json(){
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$post = Syspage::getPostedVar();
			$page = 1;
			if(isset($post['page']) && intval($post['page']) > 0) $page = intval($post['page']); 
			else $post['page'] = $page;
			$pagesize = isset($post['pagesize'])?$post['pagesize']:10;
			$userObj=new User();
			$psobj= new Products();
			$psobj->addSpecialPrice();
			$products=$userObj->getUserFavoriteItems(array("pagesize"=>$pagesize,"page"=>$page,"user"=>$this->getLoggedUserId(),"favorite"=>$this->getLoggedUserId()));
			foreach($products as $pkey=>$pval){
				$arr = $psobj->product_additional_info($pval);
				$arr_products[] = array_merge($pval,$arr); 
			}
			$total_records = $userObj->getTotalRecords();
			$total_pages = $userObj->getTotalPages();
			die(convertToJson(array('count'=>$total_records,'total_pages'=>$total_pages, 'products'=>$arr_products,'html'=>$this->_template->render(false,false,'common/json/product_thumb_template.php',true))));
		}
		die(0);
	}	
	function ajax_load_list_products_json($list_id){
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$user_id=$this->getLoggedUserId();
			$post = Syspage::getPostedVar();
			$page = 1;
			if(isset($post['page']) && intval($post['page']) > 0) $page = intval($post['page']); 
			else $post['page'] = $page;
			$pagesize = isset($post['pagesize'])?$post['pagesize']:10;
			$userObj=new User();
			$psobj= new Products();
			$psobj->addSpecialPrice();
			$list_details = $userObj->getUserList($list_id,$user_id);
			if ($list_details){
				$products=$userObj->getUserListProducts(array("pagesize"=>$pagesize,"page"=>$page,"list"=>$list_id,"favorite"=>$user_id));
				foreach($products as $pkey=>$pval){
					$arr = $psobj->product_additional_info($pval);
					$arr_products[] = array_merge($pval,$arr); 
				}
				$total_records = $userObj->getTotalRecords();
				$total_pages = $userObj->getTotalPages();
				die(convertToJson(array('count'=>$total_records,'total_pages'=>$total_pages, 'products'=>$arr_products,'html'=>$this->_template->render(false,false,'common/json/product_thumb_template.php',true))));
			}
		}
		die(0);
	}	
	function favorite_items(){
		$userObj=new User();
		$favorite_items=$userObj->getUserFavoriteItems(array("user"=>$this->getLoggedUserId(),"favorite"=>$this->getLoggedUserId()));
		$this->set('products', $favorite_items);
		$this->_template->render();	
	}
	function view_list($list_id){
		$userObj=new User();
		$user_id=$this->getLoggedUserId();
		$list_details = $userObj->getUserList($list_id,$user_id);
		if ($list_details==true){
			$list_items=$userObj->getUserListProducts(array("list"=>$list_id,"favorite"=>$user_id));
			$this->set('list_items_count', $userObj->getTotalRecords());
			$this->set('list_details', $list_details);
		}else{
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
		}
		$this->_template->render();	
	}
	function favorite_shops(){
		$userObj=new User();
		$favorite_shops=$userObj->getUserFavoriteShops(array("user"=>$this->getLoggedUserId()));
		foreach($favorite_shops as $key=>$val){
			$psObj= new Products();
			$psObj->joinWithPromotionsTable();
			$psObj->addSpecialPrice();
			$psObj->selectFields(array('tp.*','ts.shop_id','ts.shop_user_id','ts.shop_name','ts.shop_title','tu.user_id','tu.user_name','tu.user_username','tu.user_email','IF(prod_stock >0, "1", "0" ) as available'));
			$psObj->setPagesize(4);
			$shop_products=$psObj->getProducts(array("shop"=>$val["shop_id"],"sort"=>"dhtl"));
			$arr_shop_prods=array("products"=>$shop_products);
			$favorite_shops_items[]=array_merge($val,$arr_shop_prods);
		}
		$this->set('favourite_shops', $favorite_shops_items);
		$this->_template->render();	
	}
	function addresses(){
		$userObj=new User();
		$this->set('addresses', $userObj->getUserAddresses($this->getLoggedUserId()));
		$this->_template->render();	
	}
	function default_address($id) {
		$userObj=new User();
		$user_id=$this->getLoggedUserId();
		$address_details = $userObj->getUserAddress($id,$user_id);
		if ($address_details==true){
			if($userObj->setAddressDefault(intval($id), $user_id)){
				Message::addMessage(Utilities::getLabel('M_Address_updated_successfully'));
			}else{
				Message::addErrorMessage($userObj->getError());
			}
		}else{
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
		}
		Utilities::redirectUserReferer();
	}
	function delete_address($id, $ajax_request = 0) {
		$userObj=new User();
		$user_id=$this->getLoggedUserId();
		$address_details = $userObj->getUserAddress($id,$user_id);
		if ($address_details==true){
			if($userObj->deleteUserAddress(intval($id), $user_id)){
				if (!$ajax_request){
					Message::addMessage(Utilities::getLabel('M_Address_deleted_successfully'));
				}
			}else{
				Message::addErrorMessage($userObj->getError());
			}
		}else{
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
		}
		if (!$ajax_request)
			Utilities::redirectUserReferer();
	}
	function address_form($ua_id = 0,$ajax_request = 0){
		$userObj=new User();
		$ua_id = intval($ua_id);
		$frm = $this->getAddressForm();
		if ($ajax_request){
			$json = array();
			$frm->setOnSubmit('return validateAddressForm(this, AddressfrmValidator);');
			$frm->setValidatorJsObjectName('AddressfrmValidator');
		}
		if($ua_id > 0 && $address = $userObj->getUserAddress($ua_id, $this->getLoggedUserId())){
			$frm->fill($address);
		}
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				$arr=array_merge($post,array("ua_user_id"=>$this->getLoggedUserId()));
				if($userObj->addUpdateAddress($arr)){
					if (!$ajax_request){	
						Message::addMessage(Utilities::getLabel('M_Address_Added_Updated'));	
						Utilities::redirectUser(Utilities::generateUrl('account', 'addresses'));
					}else{
						$json['success']=1;
					}
				}else{
					Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
				}
			}
			if ($ajax_request){
				echo json_encode($json);
				die();
			}
			$frm->fill($post);
		}
		$this->set('frmAddress', $frm);
		$this->set('ajax_request', $ajax_request);
		$this->_template->render($ajax_request?false:true,$ajax_request?false:true);	
	}
	protected function getAddressForm() {
		global $loggedin_user;
		$frm = new Form('frmAddress');
		$frm->addHiddenField('','ua_id',0);
		$frm->addRequiredField('<label>'.Utilities::getLabel('M_Full_Name').'</label>', 'ua_name', null, 'ua_name');
		$fld_phn = $frm->addRequiredField('<label>'.Utilities::getLabel('M_Phone_Number').'</label>', 'ua_phone', '', 'ua_phone', '');
		$fld_phn->requirements()->setRegularExpressionToValidate('^[\s()+-]*([0-9][\s()+-]*){5,20}$');
		$frm->addRequiredField('<label>'.Utilities::getLabel('M_Address_Line_1').'</label>', 'ua_address1', null, 'ua_address1');
		$frm->addTextBox('<label>'.Utilities::getLabel('M_Address_Line_2').'</label>', 'ua_address2', null, 'ua_address2');
		$countryObj=new Countries();
		$stateObj=new States();
		$countries = $countryObj->getAssociativeArray();
		$fld_country=$frm->addSelectBox('<label>'.Utilities::getLabel('M_Country').'</label>', 'ua_country', $countries, Settings::getSetting("CONF_COUNTRY"), 'onchange="loadStates(this);"', Utilities::getLabel('M_Country'), 'ua_country')->requirements()->setRequired(true);
		$frm->addSelectBox('<label>'.Utilities::getLabel('M_State_County_Province').'</label>', 'ua_state', $stateObj->getStatesAssoc(Settings::getSetting("CONF_COUNTRY")), '', '', Utilities::getLabel('M_State_County_Province'), 'ua_state')->requirements()->setRequired(true);
		$frm->addRequiredField('<label>'.Utilities::getLabel('M_City_Town').'</label>', 'ua_city', null, 'ua_city');		
		$frm->addRequiredField('<label>'.Utilities::getLabel('M_Postcode_Zip').'</label>', 'ua_zip', null, 'ua_zip');
		$frm->setTableProperties(' width="100%" border="0" class="editformTable" cellpadding="0" cellspacing="0"');	
		$fld_submit = $frm->addSubmitButton(null, 'btn_submit', Utilities::getLabel('M_Save_Changes'), 'btn_submit', '');
		$frm->setExtra('class="siteForm"');
		$frm->setTableProperties('class="tbl-twocell"');
		$frm->setLeftColumnProperties('width="30%"');
		$frm->setValidatorJsObjectName('frmValidator');
		$frm->setJsErrorDisplay('afterfield');
		$frm->captionInSameCell(false);
		return $frm;
	}
	function change_password(){
		$userObj=new User();
		$frm = $this->getPasswordForm();
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				$arr=array_merge($post,array("user_id"=>$this->getLoggedUserId()));
				if($userObj->savePassword($arr)){
					Message::addMessage(Utilities::getLabel('M_Your_password_has_been_updated'));
					if (Settings::getSetting("CONF_AUTO_LOGOUT_PASSWORD_CHANGE")){
						Utilities::redirectUser(Utilities::generateUrl('account', 'logout',array('p_c')));
					}
					Utilities::redirectUser();
				}else{
					Message::addErrorMessage($userObj->getError());
				}
			}
		}
		$this->set('frmPwd', $frm);
		$this->_template->render();	
	}
	private function getPasswordForm(){
		$frm = new Form('frmPassword', 'frmStrengthPassword');
		$frm->setExtra('class="siteForm"');
		$frm->setTableProperties('class="tbl-twocell"');
		$frm->setLeftColumnProperties('width="30%"');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setRequiredStarWith('caption');
		$frm->addPasswordField('<label>'.Utilities::getLabel('M_Current_Password').'</label>', 'current_pwd', '', 'current_pwd')->requirements()->setRequired(true);
		$fld=$frm->addPasswordField('<label>'.Utilities::getLabel('M_New_password').'</label>', 'new_pwd', '', 'check-password');
		$fld->requirements()->setRequired(true);
		$fld->requirements()->setLength(4,20);
		$fld->html_before_field='<div id="check-password-result">';
		$fld->html_after_field='</div>';
		$fld = $frm->addPasswordField('<label>'.Utilities::getLabel('M_Confirm_new_password').'</label>', 'confirm_pwd', '', 'confirm_pwd');
		$fld->requirements()->setRequired(true);
		$fld->requirements()->setCompareWith('new_pwd', 'eq');
		$frm->addSubmitButton('', 'btn_submit', Utilities::getLabel('M_Save_Changes'), 'btn_submit');
		return $frm;
	}
	function change_email(){
		$userObj=new User();
		$frm = $this->getChangeEmailForm();
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				$arr=array_merge($post,array("user_id"=>$this->getLoggedUserId()));
				if($userObj->changeEmail($arr)){
					Message::addMessage(Utilities::getLabel('M_CHANGE_EMAIL_REQUEST'));
					Utilities::redirectUser();
				}else{
					Message::addErrorMessage($userObj->getError());
				}
			}
		}
		$this->set('frm', $frm);
		$this->_template->render();	
	}
	private function getChangeEmailForm(){
		$frm = new Form('frmChangeEmail', 'frmChangeEmail');
		$frm->setExtra('class="siteForm"');
		$frm->setTableProperties('class="tbl-twocell"');
		$frm->setLeftColumnProperties('width="30%"');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setRequiredStarWith('caption');
		$fld_email=$frm->addEmailField('<label>'.Utilities::getLabel('M_New_Email').'</label>', 'user_email', '', 'user_email', '')->requirements()->setRequired();
		$fld_email_con = $frm->addEmailField('<label>'.Utilities::getLabel('M_Confirm_New_Email').'</label>', 'user_email1', '', 'password2', '');
		$fld_email_con->requirements()->setCompareWith('user_email', 'eq', 'Email');
		$fld_email_con->requirements()->setRequired();
		$fld_password=$frm->addPasswordField('<label>'.Utilities::getLabel('M_Current_Password').'</label>', 'current_password', '', 'password', '')->requirements()->setRequired();
		$fld_submit = $frm->addSubmitButton(null, 'btn_submit', Utilities::getLabel('M_Change_Email'), 'btn_submit', 'class="orgbutton"');
		$fld_submit->html_after_field='<br/><br/>'.Utilities::getLabel('M_Your_email_not_change');
		return $frm;
	}
	function profile_info(){
		Syspage::addJs(array('js/jquery.form.js'), false);
		Syspage::addJs(array('js/cropper.js'), false);
		Syspage::addCss(array('css/cropper.css'), false);
		$userObj=new User();
		$frmProfileImage = $this->getProfileImageForm();
		$frm = $this->getProfileInfoForm();
		$post = Syspage::getPostedVar();
		$user=$this->user_details;
		$user["ua_state"]=$user["user_state_county"];
		if($_SERVER['REQUEST_METHOD'] == 'POST' &&  isset($post) && !empty($post)){
			if($post['action'] == "demo_avatar"){
				if(($_FILES['user_profile_image']['size']!=0) && ($_FILES['user_profile_image']['size'] < 1000000)) {
					if (Utilities::isUploadedFileValidImage($_FILES['user_profile_image'])){
						if(Utilities::saveImage($_FILES['user_profile_image']['tmp_name'],$_FILES['user_profile_image']['name'], $response, 'user-avatar/')){	
							dieJsonSuccess(Utilities::generateUrl("image","user_photo",array($response)));
						}
					}else{
						dieJsonError(Utilities::getLabel('M_ERROR_INVALID_FILETYPE'));
					}
				}else{
					dieJsonError(Utilities::getLabel('M_ERROR_FILE_SIZE'));
				}
			}
			if($post['action'] == "avatar"){
				if(Utilities::saveImage($_FILES['user_profile_image']['tmp_name'],$_FILES['user_profile_image']['name'], $response, 'user-avatar/')){	  
					$data = json_decode(stripslashes($post['img_data']));
					Utilities::crop($data,CONF_INSTALLATION_PATH . 'user-uploads/user-avatar/'.$response);
					$post['user_profile_image'] = $response;
				}
				$arr = array_merge($post,array("user_id"=>$user["user_id"]));
				if($userObj->updateUser($arr)){
					Message::addMessage(Utilities::getLabel('M_MSG_Your_Account_Details_Updated'));
					Utilities::redirectUser();
				}else{
					Message::addErrorMessage(Utilities::getLabel('M_Error_details_not_saved'));
				}
			}	
		}
		$frmProfileImage->getField('user_profile_image')->html_before_field = '<div class="uploadedphoto"><img alt="" src="'. Utilities::generateUrl('image', 'user', array($user['user_profile_image'],'SMALL')) .'" id="dpic" />'.((strlen($user['user_profile_image']))?'':'').'</div><div class="choose_file"><span>'.Utilities::getLabel('M_Change_Photo').'</span>';
		$afterText = (strlen($user['user_profile_image']))?'</div> <div class="marginLeft"><a href="#" class="btn mini secondary-btn" id="picRemove">'.Utilities::getLabel('M_Remove').'</a></div>':'</div>';
		$frmProfileImage->getField('user_profile_image')->html_after_field = $afterText;
		global $conf_arr_advertiser_types;
		if (in_array($user['user_type'],$conf_arr_advertiser_types)){
			$fld=$frm->addTextBox('<label>'.Utilities::getLabel('L_Company').'</label>', 'user_company');
			$fld=$frm->addTextArea('<label>'.Utilities::getLabel('L_Brief_Profile').'</label>', 'user_profile');
			$fld->html_after_field='<small>'.Utilities::getLabel('L_Please_tell_us_something_about_yourself').'</small>';
			$frm->addTextArea('<label>'.Utilities::getLabel('L_What_kind_products_services_advertise').'</label>', 'user_products_services');
			$frm->changeFieldPosition($frm->getField('btn_submit')->getFormIndex(),$frm->getFieldCount()-1);
		}
		$frm->fill($user);
		$this->set('user_profile_image', $user['user_profile_image']);
		$this->set('frmAccount', $frm);
		$frmProfileImage->fill($user);
		$this->set('frmProfileImage', $frmProfileImage);
		$this->_template->render();	
	}
	private function getProfileImageForm(){
		$frm = new Form('frmProfile', 'frmProfile');
		$frm->setExtra('class="siteForm"');
		$frm->setTableProperties('class="tbl-twocell"');
		$frm->setLeftColumnProperties('width="30%"');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setRequiredStarWith('caption');
		$fld=$frm->addFileUpload('<label>'.Utilities::getLabel('M_Profile_Picture').'</label>', 'user_profile_image', 'user_profile_image','onchange = "popupImage(this)" class="upload"');
		$frm->addHiddenField('', 'update_profile_img', Utilities::getLabel('M_Update_Profile_Picture'), 'update_profile_img');
		$frm->addHiddenField('', 'rotate_left', Utilities::getLabel('M_Rotate_Left'), 'rotate_left');
		$frm->addHiddenField('', 'rotate_right', Utilities::getLabel('M_Rotate_Right'), 'rotate_right');
		$frm->addHiddenField('', 'remove_profile_img', '0', 'remove_profile_img');
		$frm->addHiddenField('', 'action', 'avatar', 'avatar-action');
		$frm->addHiddenField('', 'img_data', '', 'img-data');
		return $frm;
	}
	private function getProfileInfoForm(){
		$frm = new Form('frmProfileInfo', 'frmProfileInfo');
		$frm->setExtra('class="siteForm"');
		$frm->setTableProperties('class="tbl-twocell"');
		$frm->setLeftColumnProperties('width="30%"');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setRequiredStarWith('caption');
		$frm->addHtml('<span class="panelTitleHeading">'.Utilities::getLabel('L_Personal_Information').'</span>', 'htmlNote','')->merge_caption=true;
		$frm->addHtml('<label><strong>'.Utilities::getLabel('L_Username').'</strong></label>', 'user_username');
		$frm->addHtml('<label><strong>'.Utilities::getLabel('L_Email').'</strong></label>', 'user_email');
		$frm->addRequiredField('<label><strong>'.Utilities::getLabel('M_Name').'</strong></label>', 'user_name', null, 'user_name');
		$fld_phn = $frm->addRequiredField('<label><strong>'.Utilities::getLabel('M_Phone_Number').'</strong></label>', 'user_phone', '', 'user_phone', '');
		$fld_phn->requirements()->setRegularExpressionToValidate('^[\s()+-]*([0-9][\s()+-]*){5,20}$');
		
		$countryObj=new Countries();
		$stateObj=new States();
		$fld_country=$frm->addSelectBox('<label><strong>'.Utilities::getLabel('M_Country').'</strong></label>', 'user_country', $countryObj->getAssociativeArray(), Settings::getSetting("CONF_COUNTRY"), 'onchange="loadStates(this);"', Utilities::getLabel('M_Country'), 'ua_country')->requirements()->setRequired(true);
		$frm->addSelectBox('<label><strong>'.Utilities::getLabel('M_State_County_Province').'</strong></label>', 'ua_state', '', '', '', Utilities::getLabel('M_State_County_Province'), 'ua_state')->requirements()->setRequired(true);
		$frm->addRequiredField('<label><strong>'.Utilities::getLabel('M_City_Town').'</strong></label>', 'user_city_town', null, 'user_city_town');
		$frm->addHiddenField('', 'action', 'avatar', 'avatar-action');
		$frm->addHiddenField('', 'img_data', '', 'img-data');
		$fld_submit = $frm->addSubmitButton(null, 'btn_submit', Utilities::getLabel('M_Save_Changes'), 'btn_submit', ' class="orgbutton"');
		return $frm;
	}
	function bank_info(){
		$userObj=new User();
		$frm = $this->getBankInfoForm();
		$post = Syspage::getPostedVar();
		$user=$this->user_details;
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				$post["ub_user_id"]=$user["user_id"];
				if($userObj->updateUserBankDetails($post)){
					Message::addMessage(Utilities::getLabel('M_MSG_Your_Bank_Details_Updated'));
					Utilities::redirectUser();
				}else{
					Message::addErrorMessage(Utilities::getLabel('M_Error_details_not_saved'));
				}
			}
		}
		$frm->fill($user);
		$this->set('frmBankInfo', $frm);
		$this->_template->render();	
	}
	private function getBankInfoForm(){
		$frm = new Form('frmChangeEmail', 'frmChangeEmail');
		$frm->setExtra('class="siteForm"');
		$frm->setTableProperties('class="tbl-twocell"');
		$frm->setLeftColumnProperties('width="30%"');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setRequiredStarWith('caption');
		$fld=$frm->addHtml('', 'htmlNote','<span class="panelTitleHeading">'.Utilities::getLabel('M_Bank_account_details').'</span>','&nbsp;');
		$fld->merge_caption=true;
		$bankObj=new Banks();
		$frm->addRequiredField('<label>'.Utilities::getLabel('M_Bank_Name').'</label>', 'ub_bank_name', null, 'ub_bank_name');
		$frm->addRequiredField('<label>'.Utilities::getLabel('M_Account_Holder_Name').'</label>', 'ub_account_holder_name', null, 'user_account_holder_name');
		$frm->addRequiredField('<label>'.Utilities::getLabel('M_Account_Number').'</label>', 'ub_account_number', null, 'user_account_number');
		$frm->addTextBox('<label>'.Utilities::getLabel('M_IFSC_Swift_Code').'</label>', 'ub_ifsc_swift_code', null, 'ub_ifsc_swift_code');
		$frm->addTextArea('<label>'.Utilities::getLabel('M_Bank_Address').'</label>', 'ub_bank_address', null, 'ub_bank_address')->requirements()->setRequired();
		$frm->setTableProperties(' width="100%" border="0" class="editformTable" cellpadding="0" cellspacing="0"');	
		$fld_submit = $frm->addSubmitButton(null, 'btn_submit', Utilities::getLabel('M_Save_Changes'), 'btn_submit', '');
		return $frm;
	}
	function shop(){
		$user_details=$this->user_details;
		if (!is_null($user_details["shop_id"]) && ($user_details["shop_is_deleted"]==1)){
			Message::addErrorMessage(Utilities::getLabel('M_Your_shop_removed_contact_admin'));
			Utilities::redirectUser(Utilities::generateUrl('account'));
		}
		if(!$this->canAccessProductsArea && is_null($user_details["shop_id"])){
			Message::addErrorMessage(Utilities::getLabel('M_Please_buy_subscription_package'));
			Utilities::redirectUser(Utilities::generateUrl('account','packages'));
		}
		$shopObj=new Shops();
		$frm = $this->getShopInfoForm();
		if($shop = $shopObj->getShopsByCriteria(array("user"=>$this->getLoggedUserId(),"is_owner"=>1),1)){
			$shop = $shopObj->getData($shop['shop_id'],array("is_owner"=>1));
			$shop["ua_state"]=$shop["shop_state"];
			if (strlen($shop['shop_logo'])){
				$frm->getField('shop_logo')->html_after_field = '<small class="small">'.Utilities::getLabel('M_SHOP_LOGO_EXTENSIONS').'</small><br/><br/><div class="uploadedphoto"><img alt="" src="'. Utilities::generateUrl('image', 'shop_logo', array($shop['shop_logo'],'thumb')) .'" id="lpic" />'.((strlen($shop['shop_logo']))?'<a href="javascript:void(0)" class="removepic" onclick="$(\'#lpic\').hide(); $(\'#remove_shop_logo\').val(1);$(this).remove();">'.Utilities::getLabel('L_Remove').'</a>':'').'</div>';
			}else{
				$frm->getField('shop_logo')->html_after_field ='<small class="small">'.Utilities::getLabel('M_SHOP_LOGO_EXTENSIONS').'</small>';
			}
			if (strlen($shop['shop_banner'])){
				$frm->getField('shop_banner')->html_after_field = '<small class="small">'.Utilities::getLabel('M_SHOP_BANNER_EXTENSIONS').'</small><br/><br/><div class="uploadedphoto"><img alt="" src="'. Utilities::generateUrl('image', 'shop_banner', array($shop['shop_banner'],'banner')) .'" id="bpic" />'.((strlen($shop['shop_banner']))?'<a href="javascript:void(0)" class="removepic" onclick="$(\'#bpic\').hide(); $(\'#remove_shop_banner\').val(1);$(this).remove();">'.Utilities::getLabel('L_Remove').'</a>':'').'</div>';
			}else{
				$frm->getField('shop_banner')->html_after_field ='<small class="small">'.Utilities::getLabel('M_SHOP_BANNER_EXTENSIONS').'</small>';
			}
			$frm->fill($shop);
		}
		$shop_id=(isset($shop) && $shop["shop_id"]>0)?$shop["shop_id"]:0;
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				$oldSlug=$shop['seo_url_keyword'];
				$slug=Utilities::slugify($post['seo_url_keyword']);
				if (($slug != $oldSlug) && (!empty($post['seo_url_keyword']))){  
					$i = 1; $baseSlug = $slug;              
					$url_alias=new Url_alias();
					while($url_alias->getUrlAliasByKeyword($slug)){  
						$slug = $baseSlug . "-" . $i++;     
						if($slug == $oldSlug){              
							break;                          
						}
					}
				}
				$post['seo_url_keyword']=$slug;	
				$shop_name = preg_replace('/\s+/', ' ',$post['shop_name']);
				$shop = $shopObj->getShopsByCriteria(array("name"=>$shop_name,"pagesize"=>1),1);
				if (($shop==true) && ($shop["shop_id"]!=$post["shop_id"])){
					Message::addErrorMessage(Utilities::getLabel('L_Shop_name_already_exists'));
					$error = true;
				}
				$pmObj=new Paymentmethods();
				$payment_method=$pmObj->getPaymentMethodByCode(CONF_PAPYAL_ADAPTIVE_KEY);
				$shop_paypal_account_verified = 0;
				if ($payment_method && $payment_method['pmethod_status']){
					$paObj = new Paypaladaptive_pay();
					if (!$paObj->verifyPayPalAccount($post['shop_payment_paypal_account'],$post['shop_payment_paypal_firstname'],$post['shop_payment_paypal_lastname'])){
						Message::addErrorMessage(Utilities::getLabel('L_Paypal_account_not_verified'));
						$error = true;
					}
					$shop_paypal = $shopObj->getShopsByCriteria(array("paypal"=>$post['shop_payment_paypal_account'],"pagesize"=>1),1);
					if (($shop_paypal==true) && ($error==false) && ($shop_paypal["shop_id"]!=$post["shop_id"])){
						Message::addErrorMessage(Utilities::getLabel('L_Paypal_account_already_exists'));
						$error = true;
					}
					$shop_paypal_account_verified = 1;
					
				}
				if (!$error){
					
					if(is_uploaded_file($_FILES['shop_logo']['tmp_name'])){
						if(Utilities::isUploadedFileValidImage($_FILES['shop_logo'])){
							$shop_logo_name = '';
							if(Utilities::saveImage($_FILES['shop_logo']['tmp_name'],$_FILES['shop_logo']['name'], $shop_logo_name, 'shops/logo/')){
								$post['shop_logo'] = $shop_logo_name;
								if(isset($shop['shop_logo']) && strlen($shop['shop_logo']) > 0){
									Utilities::unlinkFile('shops/logo/'.$shop['shop_logo']);
								}
							}else{
								Message::addErrorMessage($shop_logo_name);
							}
						}else{
							Message::addErrorMessage(Utilities::getLabel('M_Invalid_shop_logo_Image'));
						}
					}
					
					if(is_uploaded_file($_FILES['shop_banner']['tmp_name'])){
						if(Utilities::isUploadedFileValidImage($_FILES['shop_banner'])){
							$shop_banner_name = '';
							if(Utilities::saveImage($_FILES['shop_banner']['tmp_name'],$_FILES['shop_banner']['name'], $shop_banner_name, 'shops/banner/')){
								$post['shop_banner'] = $shop_banner_name;
									if(isset($shop['shop_banner']) && strlen($shop['shop_banner']) > 0){
										Utilities::unlinkFile('shops/banner/'.$shop['shop_banner']);
									}
							}else{
								Message::addErrorMessage($shop_banner_name);
							}
						}else{
							Message::addErrorMessage(Utilities::getLabel('M_Invalid_shop_banner_Image'));
						}
					}
					//$post = array_merge(array('shop_enable_cod_orders'=>0),$post);
					$arr=array_merge(array_merge(array('shop_enable_cod_orders'=>0),$post),array("shop_user_id"=>$this->getLoggedUserId(),"shop_status"=>1,"shop_paypal_account_verified"=>$shop_paypal_account_verified,"shop_name"=>$shop_name));
					if($shopObj->updateUserShopInfoDetails($arr)){
						Message::addMessage(Utilities::getLabel('M_Your_action_performed_successfully'));
						Utilities::redirectUser();
					}else{
						Message::addErrorMessage(Utilities::getLabel('M_Error_details_not_saved'));
					}
				}else{
					$frm->fill($post);
				}
			}
		}
		$this->set('frmShopInfo', $frm);
		$this->_template->render();	
	}
	private function getShopInfoForm(){
		$frm = new Form('frmChangeEmail', 'frmChangeEmail');
		$frm->setExtra('class="siteForm"');
		$frm->captionInSameCell(false);
		$frm->setFieldsPerRow(1);
		$frm->setTableProperties('class="tbl-twocell"');
		$frm->setLeftColumnProperties('width="27%"');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setRequiredStarWith('caption');
		$frm->addHtml('<span class="panelTitleHeading">'.Utilities::getLabel('M_Shop_Basic_Information').'</span>', 'htmlNoteTop','','&nbsp;');
		$fld=$frm->addRequiredField('<label>'.Utilities::getLabel('M_Shop_Name').'</label>', 'shop_name','', '', ' onKeyUp="Slugify(this.value,\'seo_url_keyword\',\'shop_id\')" ');
		$fld->requirements()->setLength(4,50);
		$fld=$frm->addRequiredField('<label>'.Utilities::getLabel('M_URL_Keywords').'</label>', 'seo_url_keyword','','seo_url_keyword');
		$fld->html_after_field='<small>'.Utilities::getLabel('M_SEO_KEYWORDS_TEXT').'</small>';
		$frm->addTextArea('<label>'.Utilities::getLabel('M_Description').'</label>', 'shop_description', '','', 'class="height120" rows="3"');
		$frm->addFileUpload('<label>'.Utilities::getLabel('M_Shop_Logo').'</label>', 'shop_logo');
		$frm->addFileUpload('<label>'.Utilities::getLabel('M_Shop_Banner').'</label>', 'shop_banner');
		
		
		
		$frm->addRadioButtons('<label>'.Utilities::getLabel('M_Display_Status').'</label>', 'shop_vendor_display_status', array(0=>Utilities::getLabel("L_OFF"),1=>Utilities::getLabel("L_ON")), 1, 2,$tableproperties = ' class="smalltable"','');
		$frm->getField("shop_vendor_display_status")->html_after_field='<small>'.Utilities::getLabel('M_Display_Status_Text').'</small>';
		
		if (Settings::getSetting("CONF_ENABLE_COD_PAYMENTS")){
			$fldALA=$frm->addCheckBox('<label>'.Utilities::getLabel('M_Enable_COD_Orders').'</label>', 'shop_enable_cod_orders','1','shop_enable_cod_orders');
			if (Settings::getSetting("CONF_COD_MIN_WALLET_BALANCE")>0){
				$frm->getField("shop_enable_cod_orders")->html_after_field='<br/><small>'.sprintf(Utilities::getLabel('M_Enable_COD_Text'),Utilities::displayMoneyFormat(Settings::getSetting("CONF_COD_MIN_WALLET_BALANCE"))).'</small>';
			}
		}
		
		$frm->addHtml('<span class="panelTitleHeading">'.Utilities::getLabel('M_Shop_Address').'</span>', 'htmlShopAddress',sprintf(Utilities::getLabel('L_Shop_Address_Text'),Settings::getSetting("CONF_WEBSITE_NAME"),Utilities::getUrlScheme()),'&nbsp;');
		$frm->addRequiredField('<label>'.Utilities::getLabel('F_Contact_Person_Name').'</label>', 'shop_contact_person');
		$fld_phn=$frm->addRequiredField('<label>'.Utilities::getLabel('M_Phone').'</label>', 'shop_phone');
		$fld_phn->requirements()->setRegularExpressionToValidate('^[\s()+-]*([0-9][\s()+-]*){5,20}$');
		$frm->addRequiredField('<label>'.Utilities::getLabel('L_Pickup_Dispatch_Address_1').'</label>', 'shop_address_line_1');
		$frm->addTextBox('<label>'.Utilities::getLabel('L_Pickup_Dispatch_Address_2').'</label>', 'shop_address_line_2');
		
		$countryObj=new Countries();
		$statesObj=new States();
		$fld_country=$frm->addSelectBox('<label>'.Utilities::getLabel('M_Country').'</label>', 'shop_country', $countryObj->getAssociativeArray(), Settings::getSetting("CONF_COUNTRY"), 'onchange="loadStates(this);"', Utilities::getLabel('M_Country'), 'ua_country')->requirements()->setRequired(true);
		$frm->addSelectBox('<label>'.Utilities::getLabel('M_State_County_Province').'</label>', 'ua_state', $statesObj->getAssociativeArray(), '', '', Utilities::getLabel('M_State_County_Province'), 'ua_state')->requirements()->setRequired(true);
		
		$frm->addRequiredField('<label>'.Utilities::getLabel('L_CITY').'</label>', 'shop_city');
		$frm->addRequiredField('<label>'.Utilities::getLabel('M_POSTCODE_ZIP').'</label>', 'shop_postcode');
		
		$frm->addHtml('<span class="panelTitleHeading">'.Utilities::getLabel('M_Shop_Policies').'</span>', 'htmlNote','','&nbsp;');
		$frm->addTextArea('<label>'.Utilities::getLabel('L_Payment_Policy').'</label>', 'shop_payment_policy', '', 'shop_payment_policy', ' class="height120" rows="3"');
		$frm->getField("shop_payment_policy")->html_after_field=Utilities::getLabel('L_Payment_Policy_Text');
		$frm->addTextArea('<label>'.Utilities::getLabel('M_Delivery_Policy').'</label>', 'shop_delivery_policy', '', 'shop_delivery_policy', ' class="height120" rows="3"');
		$frm->getField("shop_delivery_policy")->html_after_field='<small>'.Utilities::getLabel('M_Delivery_Policy_Text').'</small>';
		$frm->addTextArea('<label>'.Utilities::getLabel('L_Refund_Policy').'</label>', 'shop_refund_policy', '', 'shop_refund_policy', ' class="height120" rows="3"');
		$frm->getField("shop_refund_policy")->html_after_field=Utilities::getLabel('L_Refund_Policy_Text');
		$frm->addTextArea('<label>'.Utilities::getLabel('M_Additional_Information').'</label>', 'shop_additional_info', '', 'shop_additional_info', ' class="height120" rows="3"');
		$frm->getField("shop_additional_info")->html_after_field='<small>'.Utilities::getLabel('M_Additional_Information_Text').'</small>';
		$frm->addTextArea('<label>'.Utilities::getLabel('L_SELLER_INFORMATION').'</label>', 'shop_seller_info', '', 'shop_seller_info', ' class="height120" rows="3"');
		$frm->getField("shop_seller_info")->html_after_field='<small>'.Utilities::getLabel('M_Seller_Information_Text').'</small>';
		$frm->addHtml('<span class="panelTitleHeading">'.Utilities::getLabel('M_SHOP_SEO_INFORMATION').'</span>', 'htmlNote','','&nbsp;');
		$frm->addTextBox('<label>'.Utilities::getLabel('M_Meta_Tag_Title').'</label>', 'shop_page_title', '', 'shop_page_title', '');
		$frm->addTextArea('<label>'.Utilities::getLabel('M_Meta_Tag_Keywords').'</label>', 'shop_meta_keywords', '', 'shop_meta_keywords', ' rows="3"');
		$frm->addTextArea('<label>'.Utilities::getLabel('M_Meta_Tag_Description').'</label>', 'shop_meta_description', '', 'shop_meta_description', ' rows="3"');
		$pmObj=new Paymentmethods();
		$payment_method=$pmObj->getPaymentMethodByCode(CONF_PAPYAL_ADAPTIVE_KEY);
		if ($payment_method && $payment_method['pmethod_status']){
			$frm->addHtml('<span class="panelTitleHeading">'.Utilities::getLabel('M_Payment_Information_Text').'</span>', 'htmlNote','','&nbsp;');
			$frm->addRequiredField('<label>'.Utilities::getLabel('M_First_Name').'</label>', 'shop_payment_paypal_firstname', '', 'shop_payment_paypal_firstname', '');
			$frm->getField("shop_payment_paypal_firstname")->html_after_field='<small>'.Utilities::getLabel('M_Please_enter_your_paypal_account_first_name').'</small>';
			$frm->addRequiredField('<label>'.Utilities::getLabel('M_Last_Name').'</label>', 'shop_payment_paypal_lastname', '', 'shop_payment_paypal_lastname', '');
			$frm->getField("shop_payment_paypal_lastname")->html_after_field='<small>'.Utilities::getLabel('M_Please_enter_your_paypal_account_last_name').'</small>';
			
			$frm->addRequiredField('<label>'.Utilities::getLabel('M_Paypal_account_details').'</label>', 'shop_payment_paypal_account', '', 'shop_payment_paypal_account', '');
			$frm->getField("shop_payment_paypal_account")->html_after_field='<small>'.Utilities::getLabel('M_paypal_account_text').'</small>';
		}
		$fld_submit = $frm->addSubmitButton(null, 'btn_submit', Utilities::getLabel('M_Save_Changes'), 'btn_submit', '');
		$frm->addHiddenField('', 'shop_id', '0', 'shop_id');
		$frm->addHiddenField('', 'remove_shop_logo', '0', 'remove_shop_logo');
		$frm->addHiddenField('', 'remove_shop_banner', '0', 'remove_shop_banner');
		return $frm;
	}
	function product_form($prod_id = 0,$tab=""){
		if($prod_id == 0 && !$this->canAccessProductsArea){
			Message::addErrorMessage(Utilities::getLabel('M_Please_buy_subscription_package'));
			Utilities::redirectUser(Utilities::generateUrl('account','packages'));
		}
		$user_id=$this->getLoggedUserId();
		if($prod_id == 0  && !UserPermissions::canAddProducts($user_id)){
			$products = new Products();
			Message::addErrorMessage(sprintf(Utilities::getLabel('M_Your_subscription_package_products_limit_exceeded.'),$products->getTotalProductsAddedByUser($user_id)));
			Utilities::redirectUser(Utilities::generateUrl('account','packages'));
		}	
		Syspage::addJs(array('js/jquery.tooltipster.min.js'), false);
		Syspage::addCss(array('css/tooltipster.css'), false);		
		$user_id=$this->getLoggedUserId();
		$userObj = new User();
		$prodObj=new Products(false,true);
		$prodObj->joinWithDetailTable();
		$prodObj->joinWithURLAliasTable();
		$prodObj->joinWithBrandsTable();
		$prodObj->joinWithCategoryTable();
		$prodObj->joinWithProductTags();
		$user_details=$this->user_details;
		if ($user_details["shop_is_deleted"]==1){
			Utilities::redirectUser(Utilities::generateUrl('account', 'shop'));
		}
		if (is_null($user_details["shop_id"])){
			Message::addErrorMessage(Utilities::getLabel('M_Please_create_shop'));
			Utilities::redirectUser(Utilities::generateUrl('account', 'shop'));
		}
		$prod_id = intval($prod_id);
		$product=array('prod_added_by'=>$this->getLoggedUserId(),'prod_shop'=>$user_details["shop_id"]);
	//$product = $prodObj->getData($prod_id,array("prod_shop"=>$user_details["shop_id"]));
		if($prod_id > 0 && $product = $prodObj->getData($prod_id,array("added_by"=>$this->getLoggedUserId()))){
			$product['seo_url_keyword'] = $product['url_alias_keyword'];
			$product["embed_code"]=Utilities::parse_yturl($product["prod_youtube_video"]);
			foreach($data as $key=>$val){
				if ((array_key_exists($key,$prod_properties)) && ($val==1))
					$product["prod_properties"][]=$key;
			}
			$countryObj=new Countries();
			$country_info = $countryObj->getData($product["prod_shipping_country"]);
			$product["shipping_country"]=$country_info["country_name"];
			$product["prod_tab"]=$tab;
			$categories=Categories::getCatgeoryTreeStructure();
			$product["category"]=strip_tags(html_entity_decode($categories[$product["prod_category"]], ENT_QUOTES, 'UTF-8'));
			$product["shop"]=$product["shop_name"];
			$product["brand_manufacturer"]=$product["brand_name"];
			$prodObj=new Products();
			$tags = $prodObj->getProductTags($prod_id);
			$product['product_tags'] = array();
			$ptOBj=new Producttags();
			foreach ($tags as $tag_id) {
				$product_tag = $ptOBj->getData($tag_id);
				if ($product_tag) {
					$product['product_tags'][] = array(
						'tag_id' => $product_tag['ptag_id'],
						'name'      => $product_tag['ptag_name'] 
						);
				}
			}
			$prodObj=new Products();
			$filters = $prodObj->getProductFilters($prod_id);
			$product['product_filters'] = array();
			foreach ($filters as $filter_id) {
				$filterGroupOptionsObj=new Filtergroupoptions();
				$filter_info = $filterGroupOptionsObj->getData($filter_id);
				if ($filter_info) {
					$product['product_filters'][] = array(
						'filter_id' => $filter_info['filter_id'],
						'name'      => $filter_info['filter_group_name'] . ' &gt; ' . $filter_info['filter_name']
						);
				}
			}
			$prodObj=new Products();
			$related = $prodObj->getProductRelated($prod_id);
			$product['products_related'] = array();
			foreach ($related as $key=>$val) {
				$product['products_related'][]=$val;
			}
			$prodObj=new Products();
			$addons = $prodObj->getProductAddons($prod_id);
			$data['products_addons'] = array();
			foreach ($addons as $key=>$val) {
				$product['products_addons'][]=$val;
			}
			$prodObj=new Products();
			$attributes = $prodObj->getProductAttributes($prod_id);
			$product['product_attributes'] = array();
			foreach ($attributes as $key=>$val) {
				$attributeObj=new Attributes();
				$attribute_info = $attributeObj->getData($val["id"]);
				if ($attribute_info) {
					$product['product_attributes'][] = array(
						'id' => $attribute_info['attribute_id'],
						'name'      => $attribute_info['attribute_name'],
						'text'      => $val['text']
						);
				}
			}
			$prodObj=new Products();
			$shipping_rates = $prodObj->getProductShippingRates($prod_id);
			$product['product_shipping_rates'] = array();
			foreach ($shipping_rates as $key=>$val) {
				$product['product_shipping_rates'][]=$val;
			}
			$prodObj=new Products();
			$product_discounts = $prodObj->getProductDiscounts($prod_id);
			$product['product_discounts'] = array();
			foreach ($product_discounts as $key=>$val) {
				$product['product_discounts'][]=$val;
			}
			$prodObj=new Products();
			$product_specials = $prodObj->getProductSpecials($prod_id);
			$product['product_specials'] = array();
			foreach ($product_specials as $key=>$val) {
				$product['product_specials'][]=$val;
			}
			$prodObj=new Products();
			$product_options = $prodObj->getProductOptions($prod_id);
			$product['product_options'] = array();
			foreach ($product_options as $key=>$val) {
				$product['product_options'][]=$val;
			}
			
			$prodObj=new Products();
			$product_downloads = $prodObj->getProductDownloads($prod_id);
			$product['product_downloads'] = array();
			foreach ($product_downloads as $key=>$val) {
				$product['product_downloads'][]=$val;
			}
			$product['option_values'] = array();
			foreach ($product['product_options'] as $product_option) {
				if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
					if (!isset($data['option_values'][$product_option['option_id']])) {
						$optionObj=new Options();
						$product['option_values'][$product_option['option_id']] = $optionObj->getOptionValues($product_option['option_id']);
					}
				}
			}
			$this->set('data', $product);
		}else{
				if(!UserPermissions::canAddProducts($user_id)){
					$products = new Products();
					Message::addErrorMessage(sprintf(Utilities::getLabel('M_Your_subscription_package_products_limit_exceeded.'),$products->getTotalProductsAddedByUser($user_id)));
					Utilities::redirectUser(Utilities::generateUrl('account','packages'));
				}	
		}
		$frm = $this->getProductForm($product);
		unset($product['prod_category']);
		$frm->fill($product);
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			/*utilities::printarray($_POST);
			die();*/
			if ($post['prod_type']==2){
				$frm->getField('prod_length')->requirements()->setRequired(false);
				$frm->getField('prod_width')->requirements()->setRequired(false);
				$frm->getField('prod_height')->requirements()->setRequired(false);
				$frm->getField('prod_weight')->requirements()->setRequired(false);
			}
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}elseif(!$prodObj->validateOptionQuantity($post['product_option'],$post['prod_stock'])){
				Message::addErrorMessage(Utilities::getLabel('M_OPTION_QUANTITY_ERROR'));
			}else{
				$arr_cnt = count($post['prod_category']);
				$prod_category=empty($post['prod_category'][$arr_cnt-1])?$post['prod_category'][$arr_cnt-2]:$post['prod_category'][$arr_cnt-1];
				$post['prod_category']= $prod_category;						
				$oldSlug=$product['seo_url_keyword'];
				$slug=Utilities::slugify($post['seo_url_keyword']);
				if (($slug != $oldSlug) && (!empty($post['seo_url_keyword']))){  
					$i = 1; $baseSlug = $slug;
					$url_alias=new Url_alias();              
					while($url_alias->getUrlAliasByKeyword($slug)){                
						$slug = $baseSlug . "-" . $i++;     
						if($slug == $oldSlug){              
							break;                          
						}
					}
				}	
				$post['seo_url_keyword']=$slug;
				if (!$error){
					$post=array_merge(array('prod_enable_cod_orders'=>0,'prod_ship_free'=>0,'prod_added_by'=>$this->getLoggedUserId(),'prod_shop'=>$user_details["shop_id"]),$post);
					if($prodObj->addUpdateProduct($post)){
						$product_id =$prodObj->getProdId();
						Message::addMessage(Utilities::getLabel('M_YOUR_ACTION_PERFORMED_SUCCESSFULLY'));	
						Utilities::redirectUser(Utilities::generateUrl('account', 'product_form',array($product_id,$post['prod_tab'])));
					}else{
						Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
					}
				}
			}
			$frm->fill($post);
		}
		$this->set('frm', $frm);
		$this->set('user_id', $user_id);
		$this->set('shop', $user_details["shop_id"]);
		$this->set('tab', $tab);
		session_regenerate_id(false);
		$this->_template->render();	
	}
	private function getProductForm($info){
		global $binary_status,$active_inactive_status,$product_types;
		global $conf_length_class,$conf_weight_class,$binary_status,$prod_properties,$prod_condition,$prod_inventory_status;
		$frm = new Form('frmProducts','frmProducts');
		$frm->setOnSubmit('return validateProductForm(this, ProductfrmValidator);');
		$frm->setExtra(' validator="ProductfrmValidator" class="siteForm" autocomplete="off"');
		$frm->setValidatorJsObjectName('ProductfrmValidator');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setTableProperties('class="tbl-twocell"');
		$frm->setLeftColumnProperties('width="20%"');
		$frm->addHiddenField('', 'prod_id',0,'prod_id');
		$frm->addHiddenField('', 'prod_tab','general','prod_tab');
		$frm->addHiddenField('', 'prod_brand');
		$frm->addHiddenField('', 'prod_category');
		$frm->addHiddenField('', 'prod_shipping_country');
		/*****************    START TAB 1      *******************/
		$fld=$frm->addRequiredField(Utilities::getLabel('M_Product_Title'), 'prod_name','', 'prod_name', 'class="medium" onKeyUp="Slugify(this.value,\'seo_url_keyword\',\'prod_id\');copyValue(this.value,\'prod_meta_title\',\'prod_id\')"' );
		/*$fld->requirements()->setIntPositive();
		$fld->requirements()->setRegularExpressionToValidate('^[1-9][0-9]$');*/
		
		$fld=$frm->addRequiredField(Utilities::getLabel('M_URL_Keywords'), 'seo_url_keyword','', 'seo_url_keyword', 'class="medium"' );
		$fld->html_after_field='<br/><small>'.Utilities::getLabel('M_SEO_Keywords_Text').'</small>';
		
		$frm->addSelectBox(Utilities::getLabel('M_Type'), 'prod_type',$product_types,'1','class="medium"','','prod_type');
		
		$fld=$frm->addRequiredField(Utilities::getLabel('M_Selling_Price'), 'prod_sale_price', '', 'prod_sale_price', ' class="medium"');
		$fld->requirements()->setFloatPositive($val=true);
		$fld->html_after_field='<br/><small>'.sprintf(Utilities::getLabel('M_Minimum_Selling_Price'),Utilities::displayMoneyFormat(Settings::getSetting("CONF_MIN_PRODUCT_PRICE"))).'</small>';
	//$fld->html_after_field='&nbsp;<span id="final_price"></span>';
		$fld=$frm->addRequiredField(Utilities::getLabel('M_Quantity'), 'prod_stock', '1', '', ' class="medium"');
		$fld->requirements()->setIntPositive($val=true);
		$fld=$frm->addRequiredField(Utilities::getLabel('M_Minimum_Quantity'), 'prod_min_order_qty', '1', '', ' class="medium"');
		$fld->requirements()->setIntPositive($val=true);
		$fld->html_after_field='<br/><small>'.Utilities::getLabel('M_Force_Minimum_Quantity').'</small>';
		$frm->addTextBox(Utilities::getLabel('M_Brand_Manufacturer'), 'brand_manufacturer','', '', 'class="medium"');
	//$frm->addTextBox(Utilities::getLabel('M_Product_Category'), 'prod_category[]','', '', 'class="medium"');
		$cObj = new Categories();
		$cat_arr = $cObj->funcGetCategoryStructure($info["prod_category"]);
		$cat_id = $cat_arr[0]['category_id'];
		if ($cat_id>0){
			foreach($cat_arr as $ckey=>$cval){
				$incr++;
				${'fld_'.$incr} = $frm->addSelectBox(Utilities::getLabel('M_Product_Category'), 'prod_category[]', $cObj->getParentAssociativeArray($cval['category_parent'],1),$cval['category_id'], 'class="product_category small primary"', Utilities::getLabel('M_Select'));
				if ($incr>1){
					$j=$incr-1;
					${'fld_'.$j}->attachField(${'fld_'.$incr});
				}
				if ($incr==count($cat_arr))
					${'fld_'.$incr}->html_after_field='<span id="show_sub_categories"></span>';
			}
		}else{
			$fld = $frm->addSelectBox(Utilities::getLabel('M_Product_Category'), 'prod_category[]', $cObj->getParentAssociativeArray(0,1),$cat_id, 'class="product_category small primary"', Utilities::getLabel('M_Select'));
			$fld->html_after_field='<span id="show_sub_categories"></span>';
		}
		$fld_model = $frm->addTextBox(Utilities::getLabel('M_Model'), 'prod_model','', '', 'class="medium"' );
		if (Settings::getSetting("CONF_PRODUCT_MODEL_MANDATORY")){
			$fld_model->requirements()->setRequired();
		}
		$fld_sku=$frm->addTextBox(Utilities::getLabel('M_SKU'), 'prod_sku','', '', 'class="medium"' );
		if (Settings::getSetting("CONF_PRODUCT_MODEL_MANDATORY")){
			$fld_sku->requirements()->setRequired();
		}
		$fld_sku->html_after_field='<br/><small>'.Utilities::getLabel('M_Stock_Keeping_Unit').'</small>';
		if (Settings::getSetting("CONF_ALLOW_USED_PRODUCTS_LISTING")){
			$fldC=$frm->addSelectBox(Utilities::getLabel('M_Product_Condition'), 'prod_condition', $prod_condition,'N', 'class=""',Utilities::getLabel('M_Select_Condition'),'prod_condition');
			$fldC->requirements()->setRequired();
		}
		$frm->addSelectBox(Utilities::getLabel('M_Status'), 'prod_status',$active_inactive_status,'1','class="medium"','');
		
		if (Settings::getSetting("CONF_ENABLE_COD_PAYMENTS")){
			$fldALA=$frm->addCheckBox('<label>'.Utilities::getLabel('M_Enable_COD').'</label>', 'prod_enable_cod_orders','1','prod_enable_cod_orders');
		}
		
		$fldPI=$frm->addFileUpload(Utilities::getLabel('M_Photos'), 'prod_image', '', ' id="prod_image" '.$strDisabled.' multiple onchange="submitImageUploadImageForm(); return false;" ');
		$fldPI->html_after_field='<small>'.Utilities::getLabel('M_PLEASE_KEEP_IMAGE_DIMENSIONS').' '.Utilities::getLabel('M_YOU_CAN_UPLOAD_MULTIPLE_PHOTOS').'</small><br/><span id="imageupload_div"></span>';
		$fld=$frm->addHtmlEditor(Utilities::getLabel('M_Product_Description'), 'prod_long_desc', '', 'prod_long_desc', 'class="fieldtextAreaEditor cleditor"');
		$fld_meta_title = $frm->addTextBox(Utilities::getLabel('M_Meta_Tag_Title'), 'prod_meta_title','', 'prod_meta_title', 'class="medium"');
		if (Settings::getSetting("CONF_PRODUCT_META_TITLE_MANDATORY")){
			$fld_meta_title->requirements()->setRequired();
		}
		$frm->addTextArea(Utilities::getLabel('M_Meta_Tag_Description'), 'prod_meta_description','', '', 'class="medium"');
		$frm->addTextArea(Utilities::getLabel('M_Meta_Tag_Keywords'), 'prod_meta_keywords','', '', 'class="medium"');
	//$fld=$frm->addTextBox(Utilities::getLabel('M_Tags'), 'prod_tags','', '', 'class="medium"');
	//$fld->html_after_field='<br/><small>'.Utilities::getLabel('M_TAGS_COMMA_SEPARATED').'</small>';
		foreach ($info["product_tags"] as $product_tag) { 
			$product_tags.='<div id="product-tag'.$product_tag['tag_id'].'"><i class="remove_tag remove_param"><img src="'.CONF_WEBROOT_URL.'images/admin/closelabels.png"/></i> '.$product_tag['name'].'<input type="hidden" name="product_tag[]" value="'. $product_tag['tag_id'].'" /></div>';
		}
		$fld=$frm->addTextBox(Utilities::getLabel('M_Tags'), 'prod_tags','', '', 'class="small"');
		$fld->html_after_field = '<div class="anchor-popup"><a href="'.Utilities::generateUrl('account', 'product_tag').'" rel="fancy_popup_box" >'.Utilities::getLabel('M_Add_Your_Own_Tag').'</a></div><div id="product-tag" class="well well-sm" style="height: 150px; overflow: auto;">'.$product_tags.'</div>';
		/*****************    END TAB 1    *******************/
		/*****************    START TAB 2      *******************/
		$frm->addSelectBox(Utilities::getLabel('M_Subtract_Stock'), 'prod_subtract_stock',$binary_status,'1','class="medium"','','');
		
		$frm->addSelectBox(Utilities::getLabel('M_Requires_Shipping'), 'prod_requires_shipping',$binary_status,'1','class="medium"','','prod_requires_shipping');
		$frm->addSelectBox(Utilities::getLabel('M_Track_Inventory'), 'prod_track_inventory',$prod_inventory_status,'0' , 'class="medium"','','prod_track_inventory');
		$fld=$frm->addTextBox(Utilities::getLabel('M_Alert_Stock_Level'), 'prod_threshold_stock_level', '', '', ' class="medium" maxlength="5" ');
		$fld->requirements()->setIntPositive($val=true);
		$fld->html_after_field='<br/><small>'.Utilities::getLabel('M_Alert_Stock_Level_Text').'</small>';
		$fld=$frm->addTextBox(Utilities::getLabel('M_Youtube_Video'), 'prod_youtube_video','', '', 'class="medium"');
		$fld->html_after_field='<br/><small>'.Utilities::getLabel('M_Youtube_Video_Text').'</small>';
		$frm->addTextBox(Utilities::getLabel('M_Date_Available'), 'prod_available_date', date('Y-m-d'), '', 'readonly="true" class="date-pick medium"');
		$fldLength=$frm->addTextBox(Utilities::getLabel('M_Dimensions_LWH'), 'prod_length','', 'prod_length', 'Placeholder="'.Utilities::getLabel('M_Length').'" class="mini"');
		$fldLength->html_after_field = ' ';
		$fldWidth=$frm->addTextBox(Utilities::getLabel('M_Dimensions_LWH'), 'prod_width','', 'prod_width', 'Placeholder="'.Utilities::getLabel('M_Width').'" class="mini"');
		$fldWidth->html_after_field = ' ';
		$fldHeight=$frm->addTextBox(Utilities::getLabel('M_Dimensions_LWH'), 'prod_height','', 'prod_height', 'Placeholder="'.Utilities::getLabel('M_Height').'" class="mini"');
		$frm->addHtml('', 'dimensionNote','<small>'.Utilities::getLabel('M_Dimensions_Text').'</small>');
		$frm->addSelectBox(Utilities::getLabel('M_Length_Class'), 'prod_length_class',$conf_length_class,'','class="medium"','');
		$field_weight = $frm->addTextBox(Utilities::getLabel('M_Weight'), 'prod_weight','', 'prod_weight', 'Placeholder="'.Utilities::getLabel('M_Weight').'" class="medium"');
		if (Settings::getSetting("CONF_SHIPSTATION_API_STATUS")){
			$fldLength->requirements()->setRequired();
			$fldLength->requirements()->setFloatPositive($val=true);
			$fldWidth->requirements()->setRequired();
			$fldWidth->requirements()->setFloatPositive($val=true);
			$fldHeight->requirements()->setRequired();
			$fldHeight->requirements()->setFloatPositive($val=true);
			$field_weight->requirements()->setRequired();
			$field_weight->requirements()->setFloatPositive($val=true);
		}
		$frm->addSelectBox(Utilities::getLabel('M_Weight_Class'), 'prod_weight_class',$conf_weight_class,'','class="medium"','');
		
		$frm->addTextBox(Utilities::getLabel('M_Display_Order'), 'prod_display_order','1', '', 'class="medium"');
		foreach ($info["product_filters"] as $product_filter) { 
			$product_filters.='<div id="product-filter'.$product_filter['filter_id'].'"> <i class="remove_filter remove_param"><img src="'.CONF_WEBROOT_URL.'images/admin/closelabels.png"/></i> '.$product_filter['name'].'<input type="hidden" name="product_filter[]" value="'. $product_filter['filter_id'].'" /></div>';
		}
		$fld=$frm->addTextBox(Utilities::getLabel('M_Product_Filters'), 'filter','', '', 'class="medium"');
		$fld->html_after_field = '<div id="product-filter" class="well well-sm" style="height: 150px; overflow: auto;">'.$product_filters.'</div>';
		foreach ($info["products_addons"] as $product_addon) { 
			$product_addons.='<div id="product-addon'.$product_addon['prod_id'].'"> <i class="remove_addon remove_param"><img src="'.CONF_WEBROOT_URL.'images/admin/closelabels.png"/></i> '.$product_addon['prod_name'].'<input type="hidden" name="product_addon[]" value="'. $product_addon['prod_id'].'" /></div>';
		}
		$fld=$frm->addTextBox(Utilities::getLabel('M_Product_Addons'), 'addons','', '', 'class="medium"');
		$fld->html_after_field = '<div id="product-addon" class="well well-sm" style="height: 150px; overflow: auto;">'.$product_addons.'</div><small>'.sprintf(Utilities::getLabel('M_Max_Product_Addons_Selected'),Settings::getSetting("CONF_MAX_NUMBER_PRODUCT_ADDONS")).'</small>';
		/*****************    END TAB 2      *******************/
		/*****************    START TAB 3      *******************/		
		/*****************    ENF TAB 3      *******************/
		/*****************    START TAB 6      *******************/		
		$countryObj=new Countries();
		$stateObj=new States();
		$countries = $countryObj->getAssociativeArray();
		$frm->addTextBox(Utilities::getLabel('M_Country'), 'shipping_country','', '', 'class="medium"');
		$fld=$frm->addCheckBox(Utilities::getLabel('M_Free_Shipping'), 'prod_ship_free',1,'prod_ship_free');
	//$fld->html_after_field='<br/><small>'.Utilities::getLabel('M_Free_Shipping_Text').'</small>';	
	//$fld=$frm->addTextBox('Default Shipping ['.CONF_CURRENCY_SYMBOL.']', 'prod_shipping', '', 'prod_shipping', ' class="medium"');
	//$fld->requirements()->setFloatPositive($val=true);
		/*****************    ENF TAB 6      *******************/
		$frm->addSubmitButton('&nbsp;','btn_submit',Utilities::getLabel('M_Save_Changes'),'','class="btn-align-tabs"');
		return $frm;
	}
	function sales_graph($width=false, $height=false) {
		$user_id=$this->getLoggedUserId();
		Utilities::outputImage('graphs/monthlysales-'.$user_id.'.png', $width, $height, '', false);
	}
	function cancellation_request($id){
		$userObj=new User();
		$user_id=$this->getLoggedUserId();
		$criteria=array("customer"=>$user_id,"id"=>(int)$id,"status"=>(array)Settings::getSetting("CONF_BUYER_ORDER_STATUS"),"pagesize"=>1);
		$orderObj=new Orders();
		$order_detail=$orderObj->getChildOrders($criteria);
		$order_eligible_cancel_request=true;
		if (!$order_detail || Utilities::is_multidim_array($order_detail)){
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_ACCESS'));
			Utilities::redirectUserReferer();
		}
		/*Utilities::printarray((array)$orderObj->getBuyerAllowedOrderCancellationStatuses());
		die();*/
		if (!in_array($order_detail["opr_status"],(array)$orderObj->getBuyerAllowedOrderCancellationStatuses())){
			Message::addErrorMessage(Utilities::getLabel('L_Order_Cancellation_cannot_placed'));
			Utilities::redirectUserReferer();
		}
		$cancelRequestObj=new CancelRequests();
		$cancellation_request=$cancelRequestObj->getCancelRequestByOrder($order_detail["opr_id"]);
		if ($cancellation_request==true){
			Message::addErrorMessage(Utilities::getLabel('L_Already_submitted_cancel_request_order'));
			$order_eligible_cancel_request=false;
		}
		$this->set('order_detail',$order_detail);
		$frm=$this->getCancelRequestForm($order_detail);
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit']) && ($order_eligible_cancel_request)){	
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
				$frm->fill($post);
			}else{
				$arr=array_merge($post,array("user_id"=>$user_id,"opr_id"=>$order_detail["opr_id"]));
				if($userObj->addOrderCancellationRequest($arr)){
					$emailNotificationObj=new Emailnotifications();
					if ($emailNotificationObj->SendOrderCancellationNotification($userObj->getCancellationRequestId())){
						Message::addMessage(Utilities::getLabel('L_Your_cancellation_request_submitted'));	
					}else{
						Message::addErrorMessage($emailNotificationObj->getError());
					}
				}else{
					Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
				}
			}
		}
		$this->set('frmCancelOrder',$frm);
		$this->_template->render();
	}
	private function getCancelRequestForm($info){
		$frm = new Form('frmCancelRequestForm', 'frmCancelRequestForm');
		$frm->setExtra('class="siteForm"');
		$frm->setLeftColumnProperties('width="30%"');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setRequiredStarWith('caption');
		$cancelReasonsObj=new Cancelreasons();
		$arr_cancel_reasons=$cancelReasonsObj->getAssociativeArray();
		$fld=$frm->addSelectBox('<label>'.Utilities::getLabel('L_Reason_for_cancellation').'</label>', 'reason',$arr_cancel_reasons,'',1,Utilities::getLabel('L_Select_Reason'));
		$fld->requirements()->setRequired(true);
		$fld->requirements()->setCustomErrorMessage(Utilities::getLabel('L_Please_select_your_reason'));
		$fld = $frm->addTextArea('<label>'.Utilities::getLabel('L_Comments').'</label>', 'message');
		$fld->requirements()->setRequired(true);
		$fld_submit = $frm->addSubmitButton(null, 'btn_submit', Utilities::getLabel('M_Send_Request'), 'btn_submit', '');
		return $frm;
	}
	function supplier_approval_form($p){
		global $conf_arr_seller_types;
		$userObj=new User();
		$user=$this->user_details;
		if (!(Settings::getSetting("CONF_BUYER_CAN_SEE_SELLER_TAB")) || (!Settings::getSetting("CONF_BUYER_CAN_SEE_SELLER_TAB") && (in_array($user['user_type'],$conf_arr_seller_types)))){
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_ACCESS'));
			Utilities::redirectUserReferer();
		}
		$supplier_request=$userObj->getUserSupplierRequests(array("user"=>$this->getLoggedUserId(),"pagesize"=>1));
		if ($supplier_request && $supplier_request["usuprequest_attempts"]>3){
			Message::addMessage(Utilities::getLabel('M_You_have_already_consumed_max_attempts'));
			Utilities::redirectUser(Utilities::generateUrl('account','view_supplier_request',array($supplier_request["usuprequest_id"])));
		}
		if ($supplier_request && ($p!="reopen"))
			Utilities::redirectUser(Utilities::generateUrl('account','view_supplier_request',array($supplier_request["usuprequest_id"])));
		$frm = $this->getSupplierForm();
		$frm->addHiddenField('','id', $supplier_request['usuprequest_id']);
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			$supplier_form_fields=$userObj->getSupplierFormFields();
			foreach ($supplier_form_fields as $field) {
				if ($field['sformfield_required'] && empty($post["sformfield"][$field['sformfield_id']])) {
					$error_messages[]=sprintf(Utilities::getLabel('M_Label_Required'), $field['sformfield_caption']);
				}
			}
			if (!$error_messages){
				$random = strtoupper(uniqid());
				$reference_number = substr($random, 0, 5) . '-' . substr($random, 5, 5) . '-' . substr($random . rand(10, 99), 10, 5);
				$data=array_merge($post,array("user"=>$user["user_id"],"reference"=>$reference_number));
				if($userObj->addSupplierRequestData($data)){
					$userObj->setUserId($this->getLoggedUserId());
					if ($userObj->getAttribute('usuprequest_status')==1){
						$user_type = Settings::getSetting("CONF_ACTIVATE_SEPARATE_SIGNUP_FORM")?CONF_SELLER_USER_TYPE:CONF_BUYER_SELLER_USER_TYPE;
						$userObj->updateAttributes(array("user_type"=>$user_type));
						Message::addMessage(Utilities::getLabel('M_Your_supplier_account_approved'));
					}else{
						Message::addMessage(Utilities::getLabel('M_Your_supplier_approval_form_request_sent'));
					}
					Utilities::redirectUser(Utilities::generateUrl('account','dashboard_buyer'));
				}else{
					Message::addErrorMessage(Utilities::getLabel('M_Error_details_not_saved'));
				}
			}else{
				Message::addErrorMessage($error_messages);
				$frm->fill($post);
			}
		}
		$this->set('frmSupplierForm', $frm);
	//$this->set('buyer_supplier_tab',"S");
		$this->_template->render();	
	}
	private function getSupplierForm(){
		$userObj=new User();
		$frm = new Form('frmSupplierForm', 'frmSupplierForm');
		$frm->setExtra('class="siteForm"');
		$frm->setTableProperties('class="tbl-twocell"');
		$frm->setLeftColumnProperties('width="30%"');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setRequiredStarWith('caption');
		$supplier_form_fields=$userObj->getSupplierFormFields();
		foreach ($supplier_form_fields as $field) {
			switch($field['sformfield_type']) {
				case 'file':
				$fld=$frm->addButton("<label>".$field['sformfield_caption']."</label>", 'button['.$field['sformfield_id'].']',Utilities::getLabel('M_Upload_File'),'button-upload'.$field['sformfield_id'],'data-loading-text="Loading" class="btn btn-default btn-block"');
				if ($field['sformfield_required'])
					$fld->requirements()->setRequired();
				$fld->html_after_field='<br/><input type="hidden"  name="sformfield['.$field['sformfield_id'].']"/><span id="input-sformfield'.$field['sformfield_id'].'"></span>';
				break;
				case 'text':
				$fld=$frm->addTextBox("<label>".$field['sformfield_caption']."</label>", 'sformfield['.$field['sformfield_id'].']','','input-sformfield'.$field['sformfield_id'],'Placeholder="'.$field['sformfield_caption'].'"');
				if ($field['sformfield_required'])
					$fld->requirements()->setRequired();
				break;
				case 'textarea':
				$fld=$frm->addTextArea("<label>".$field['sformfield_caption']."</label>", 'sformfield['.$field['sformfield_id'].']','','input-sformfield'.$field['sformfield_id'],'Placeholder="'.$field['sformfield_caption'].'"');
				if ($field['sformfield_required'])
					$fld->requirements()->setRequired();
				break;
				case 'date':
				$fld=$frm->addTextBox("<label>".$field['sformfield_caption']."</label>", 'sformfield['.$field['sformfield_id'].']','','input-sformfield'.$field['sformfield_id'],'class="date calendar" Placeholder="'.$field['sformfield_caption'].'"');
				if ($field['sformfield_required'])
					$fld->requirements()->setRequired();
				break;
				case 'datetime':
				$fld=$frm->addTextBox("<label>".$field['sformfield_caption']."</label>", 'sformfield['.$field['sformfield_id'].']','','input-sformfield'.$field['sformfield_id'],'class="datetime calendar" Placeholder="'.$field['sformfield_caption'].'"');
				if ($field['sformfield_required'])
					$fld->requirements()->setRequired();
				break;
				case 'time':
				$fld=$frm->addTextBox("<label>".$field['sformfield_caption']."</label>", 'sformfield['.$field['sformfield_id'].']','','input-sformfield'.$field['sformfield_id'],'class="time calendar" Placeholder="'.$field['sformfield_caption'].'"');
				if ($field['sformfield_required'])
					$fld->requirements()->setRequired();
				break;
			}
			$fld->html_after_field.='<small>'.$field['sformfield_extra']."</small>";
		}
		$frm->setTableProperties(' width="100%" border="0" class="editformTable" cellpadding="0" cellspacing="0"');	
		$fld_submit = $frm->addSubmitButton(null, 'btn_submit', Utilities::getLabel('M_Save_Changes'), 'btn_submit', '');
		return $frm;
	}
	function view_supplier_request($id){
		$uObj=new User();
		$supplier_request=$uObj->getUserSupplierRequests(array("id"=>$id,"user"=>$this->getLoggedUserId(),"pagesize"=>1));
		if (!$supplier_request){
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_ACCESS'));
			Utilities::redirectUser(Utilities::generateUrl('account','dashboard_buyer'));
		}
		$this->set('supplier_request',$supplier_request);
		$this->set('buyer_supplier_tab',"S");
		$this->_template->render();
	}
	function referral_tracking_url($code){
		return Utilities::generateAbsoluteUrl('home','referral',array($code));
	}
	private function getFriendsSharingForm(){
		$frm=new Form('frmCustomShare','frmCustomShare');
		$frm->setOnSubmit('return(false);');
		$frm->setRequiredStarWith('caption');
		$frm->setValidatorJsObjectName('frmValidator');
		$frm->setExtra('class="siteForm" rel="action" validator="frmValidator"');
		$frm->setAction(Utilities::generateUrl('account','send_email'));
		$frm->captionInSameCell(true);
		$frm->setFieldsPerRow(1);
		$frm->setLeftColumnProperties('valign="top" width="30%"');
		$frm->setTableProperties(' width="100%" border="0" class="formtable"');
		$fld=$frm->addTextArea(Utilities::getLabel('L_Friends_Email').' <small>('.Utilities::getLabel('L_Use_commas_separate_emails').')</small>', 'email');
		$fld->requirements()->setRequired();
		$fld->requirements()->setCustomErrorMessage(Utilities::getLabel('L_Friends_Email_Mandatory'));
		$frm->addTextArea(Utilities::getLabel('L_Personal_Message'), 'message');
		$frm->addSubmitButton('','btn_submit',Utilities::getLabel('L_Invite_Your_Friends'),'');
		$frm->setJsErrorDisplay('afterfield');
		return $frm;
	}
	function share_earn(){
		if (!Settings::getSetting("CONF_ENABLE_REFERRER_MODULE")){
			Message::addErrorMessage(Utilities::getLabel('M_INVALID_REQUEST'));
			Utilities::redirectUserReferer();
		}
		require_once (CONF_INSTALLATION_PATH . 'public/includes/APIs/twitter/twitteroauth.php');
		$this->set('referral_tracking_url',$this->referral_tracking_url($this->user_details['user_referral_code']));
		$this->set('sharingfrm',$this->getFriendsSharingForm());
		$this->_template->render();	
	}
	function send_email(){
		$post=getPostedData();
		$email=Utilities::multipleExplode(array(",",";","\t","\n"),trim($post["email"],","));
		$email=array_slice(array_unique($email),0,100);
		$message=$post['message']!=""?$post['message']:"-NA-";
		if (count($email) && !empty($email)){
			$Personal_Message=empty($message)?"":"<b>".Utilities::getLabel('L_Personal_Message_From_Sender').":</b> ".nl2br($message);
			foreach($email as $email_id) {
				$email_id = trim($email_id);
				if(!Utilities::isValidEmail($email_id)) continue;
				$rs = Utilities::sendMailTpl($email_id, 'invitation_email', array(
					'{Sender_Name}' => htmlentities($this->user_details['user_name']),
					'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
					'{Tracking_URL}' => $this->referral_tracking_url($this->user_details['user_referral_code']),
					'{Invitation_Message}' => $Personal_Message,
					));
			}
		}
		
		echo json_encode(array("status"=>1,"message"=>Utilities::getLabel('L_We_sent_invitation_emails')));
		
	}
	function twitter_callback(){
		require_once (CONF_INSTALLATION_PATH . 'public/includes/APIs/twitter/twitteroauth.php');
		$get = getQueryStringData();
		if (!empty($get['oauth_verifier']) && !empty($_SESSION['oauth_token']) && !empty($_SESSION['oauth_token_secret'])) {
	// We've got everything we need
			$twitteroauth = new TwitterOAuth(Settings::getSetting("CONF_TWITTER_API_KEY"), Settings::getSetting("CONF_TWITTER_API_SECRET"), $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
	// Let's request the access token
			$access_token = $twitteroauth->getAccessToken($get['oauth_verifier']);
	// Save it in a session var
			$_SESSION['access_token'] = $access_token;
	// Let's get the user's info
			$twitter_info = $twitteroauth->get('account/verify_credentials');
	//$twitter_info->id
			$anchor_tag=$this->referral_tracking_url($this->user_details['user_referral_code']);
			$urlapi = "http://tinyurl.com/api-create.php?url=".$anchor_tag;
			/*** activate cURL for URL shortening ***/
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $urlapi);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$shorturl = curl_exec($ch);
			curl_close($ch);
			$anchor_length=strlen($shorturl);
	//$message = substr($shorturl." Twitter Message will go here ",0,(140-$anchor_length-6));
			$message = substr($shorturl." ".sprintf(Settings::getSetting("CONF_SOCIAL_FEED_TWITTER_POST_TITLE"),Settings::getSetting("CONF_WEBSITE_NAME")),0,134-$anchor_length);
			$image_path = CONF_USER_UPLOADS_PATH.Settings::getSetting("CONF_SOCIAL_FEED_IMAGE");
			$handle = fopen($image_path,'rb');
			$image = fread($handle,filesize($image_path));
			fclose($handle);
			$parameters = array('media[]' => "{$image};type=image/jpeg;filename={$image_path}",'status' => $message);
			$post = $twitteroauth->post('statuses/update_with_media', $parameters, true);
			if (isset($post->errors)) {
				?>
				<script type="text/javascript">
					opener.location.reload();
	  // or opener.location.href = opener.location.href;
					window.close();
	  // or self.close();
				</script>
				<?
			} else{
				?>
				<script type="text/javascript">
					close();
					window.opener.twitter_shared();
				</script>
				<?
			}
		}
	}
	function brand_request(){
		global $supplier_request_status;
		$userObj=new User();
		$user_id=$this->getLoggedUserId();
		$frmBrandRequest=$this->getBrandRequestForm();
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){	
			if(!$frmBrandRequest->validate($post)){
				Message::addErrorMessage($frmBrandRequest->getValidationErrors());
				$frmBrandRequest->fill($post);
			}else{
				$userRequest = $userObj->getUserRequests(array("text"=>$post["brand_name"],"pagesize"=>1));
				if ($userRequest){
					Message::addErrorMessage(sprintf(Utilities::getLabel('M_REQUEST_ALREADY_EXISTS'),$supplier_request_status[$userRequest['urequest_status']]));
				}else{
					$arr=array("type"=>"B","request"=>$post["brand_name"],"user_id"=>$user_id);
					if($userObj->addUserRequest($arr)){
						$emailNotificationObj=new Emailnotifications();
						if ($emailNotificationObj->SendAdminRequestNotification($userObj->getUserRequestId())){
							Message::addMessage(Utilities::getLabel('L_YOUR_REQUEST_SUBMITTED'));	
						}else{
							Message::addErrorMessage($emailNotificationObj->getError());
						}
					}else{
						Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
					}
				}
			}
			$arr = array('msg'=>Message::getHtml());
			die(convertToJson($arr));
		}
		$this->set('frmBrandRequest',$frmBrandRequest);
		$this->_template->render(false,false);
	}
	private function getBrandRequestForm($info){
		$frm = new Form('frmBrandRequestForm', 'frmBrandRequestForm');
		$frm->setOnSubmit('return validateRequestForm(this, RequestfrmValidator);');
		$frm->setValidatorJsObjectName('RequestfrmValidator');
		$frm->setExtra('class="siteForm"');
		$frm->setLeftColumnProperties('width="30%"');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setRequiredStarWith('caption');
		$frm->addRequiredField('<label>'.Utilities::getLabel('F_Brand_Name').'</label>', 'brand_name','','name');
		$fld_submit = $frm->addSubmitButton(null, 'btn_submit', Utilities::getLabel('M_Send_Request'), 'btn_submit', '');
		return $frm;
	}
	function options(){
		$post = Syspage::getPostedVar();
		$page = intval($post["page"]);
		if ($page < 1)
			$page = 1;
		$pagesize = 10;
		$user_id=$this->getLoggedUserId();
		$criteria=array("owner"=>"U","created_by"=>$user_id,"pagesize"=>$pagesize,"page"=>$page);
		$criteria=array_merge($criteria,$post);
		$opObj=new Options();
		$this->set('arr_listing',$opObj->getOptions($criteria));
		$this->set('pages', $opObj->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page-1)*$pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $opObj->getTotalRecords();
		if ($total_records < $end_record) $end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->_template->render();	
	}
	function option_form($option_id = 0,$ajax_request = 0){
		$user_id = $this->getLoggedUserId();
		$opObj=new Options();
		$option_id = intval($option_id);
		if ($option_id > 0) {
			$data = $opObj->getData($option_id,array("owner"=>"U","added_by"=>$user_id));
			if (!$data){
				Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
				Utilities::redirectUser(Utilities::generateUrl('account', 'options'));
			}
			$option_values = $opObj->getOptionValues($option_id);			
			$data['option_values'] = array();
			foreach ($option_values as $key=>$val) {
				$data['option_values'][]=$val;
			}
		}
		$frm = $opObj->getForm($data);
		if ($ajax_request){
			$json = array();
			$frm->setOnSubmit('return validateOptionForm(this, OptionfrmValidator);');
			$frm->setValidatorJsObjectName('OptionfrmValidator');
		}
		$frm->fill($data);
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				if($post['option_id'] != $option_id){
					Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
				}else{
					$arr = array_merge($post,array("option_owner"=>"U","option_created_by"=>$user_id));
					if($opObj->addUpdate($arr)){
						$is_success=true;
						Message::addMessage(Utilities::getLabel('M_SUCCESS_OPTION_DETAILS_UPDATED_SUCCESSFULLY'));
					}else{
						Message::addErrorMessage($opObj->getError());
					}
				}
			}
			if (!$ajax_request){
				Utilities::redirectUser(Utilities::generateUrl('account', 'options'));
			}else{
				$arr = array('msg'=>Message::getHtml());
				die(convertToJson($arr));
				/*if ($is_success)
					dieJsonSuccess(Utilities::getLabel('M_SUCCESS_OPTION_DETAILS_UPDATED_SUCCESSFULLY'));	
				else
					dieJsonError($opObj->getError())*/;	
			}
		}
		$this->set('option_value_row',count($data['option_values']));
		$this->set('frm', $frm);
		$this->set('ajax_request', $ajax_request);
		$this->_template->render($ajax_request?false:true,$ajax_request?false:true);	
	}
	function product_setup_info(){
		$extraPageObj=new Extrapage();
		$arr_product_setup_content=$extraPageObj->getExtraBlockData(array('identifier'=>'PRODUCT_SETUP_CONTENT_BLOCK'));
		$product_setup_content=$arr_product_setup_content["epage_content"];
		$this->set('product_setup_content', $product_setup_content);
		$this->_template->render(false,false);	
	}
	function product_tag(){
		$userObj=new User();
		$user_id=$this->getLoggedUserId();
		$frmProductTag=$this->getTagForm();
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){	
			if(!$frmProductTag->validate($post)){
				Message::addErrorMessage($frmProductTag->getValidationErrors());
				$frmProductTag->fill($post);
			}else{
				$ptOBj=new Producttags();
				$tag = $ptOBj->getProductTagByName($post['ptag_name']);
				if (($tag==true)){
					Message::addErrorMessage(Utilities::getLabel('L_Error_Product_tag_already_exists'));
				}else{
					$oldSlug=$data['ptag_name'];
					$slug=Utilities::slugify($post['ptag_name']);
					if (($slug != $oldSlug) && (!empty($post['ptag_name']))){  
						$i = 1; $baseSlug = $slug;              
						$url_alias=new Url_alias();
						while($url_alias->getUrlAliasByKeyword($slug)){                
							$slug = $baseSlug . "-" . $i++;     
							if($slug == $oldSlug){              
								break;                          
							}
						}
					}
					$post['seo_url_keyword']=$slug;	
					$arr = array_merge($post,array("owner"=>"U","added_by"=>$user_id));
					if($ptOBj->addUpdate($arr)){
						Message::addMessage(sprintf(Utilities::getLabel('L_Product_tag_added_successfully'),$post['ptag_name']));
					}else{
						Message::addErrorMessage($ptOBj->getError());
					}
				}
			}
			$arr = array('msg'=>Message::getHtml());
			die(convertToJson($arr));
		}
		$this->set('frmProductTag',$frmProductTag);
		$this->_template->render(false,false);
	}
	private function getTagForm($info){
		$frm = new Form('frmTagForm', 'frmTagForm');
		$frm->setOnSubmit('return validateRequestForm(this, RequestfrmValidator);');
		$frm->setValidatorJsObjectName('RequestfrmValidator');
		$frm->setExtra('class="siteForm"');
		$frm->setLeftColumnProperties('width="30%"');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setRequiredStarWith('caption');
		$frm->addRequiredField('<label>'.Utilities::getLabel('F_Tag_Name').'</label>', 'ptag_name','','ptag_name');
		$fld_submit = $frm->addSubmitButton(null, 'btn_submit', Utilities::getLabel('M_Submit'), 'btn_submit', '');
		return $frm;
	}	
	function promote(){
		$userObj=new User();
		$prmObj=new Promotions();
		$post = Syspage::getPostedVar();
		$page = intval($post["page"]);
		if ($page < 1)
			$page = 1;
		$pagesize = 10;
		$criterias=array_merge(array("user"=>$user_id=$this->getLoggedUserId(),"pagesize"=>$pagesize),$post);
		$arr_promotions = $prmObj->getPromotions($criterias);
		$balance=$userObj->getUserBalance($user_id);
		$minimum_wallet_limit=Settings::getSetting("CONF_MIN_WALLET_BALANCE");
		
		foreach($arr_promotions as $pkey=>$pval){
			if ($pval["promotion_start_date"]<=date("Y-m-d") && $pval["promotion_end_date"]>=date("Y-m-d") && ($balance<$minimum_wallet_limit)){
				$pval['promotion_min_balance']=1;
				if (!isset($error_warning)){
					Message::addErrorMessage(sprintf(Utilities::getLabel('L_Promotion_Minimum_Balance_Less'),Utilities::displaymoneyformat($minimum_wallet_limit)));
					$error_warning = true;
				}
			}
			$promotions[]=$pval;
		}
		$this->set('promotions',$promotions);
		$this->set('pages', $prmObj->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page-1)*$pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $prmObj->getTotalRecords();
		if ($total_records < $end_record) $end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->set('error_warning', $error_warning);
		$this->_template->render();	
	}
	function promotion_clicks($promotion_id) {
		$prmObj=new Promotions();
		$post = Syspage::getPostedVar();
		$page = intval($post["page"]);
		if ($page < 1)
			$page = 1;
		$pagesize = 10;
		$criteria=array("promotion"=>$promotion_id);
		$criteria['pagesize'] = $pagesize;
		$criteria['page'] = $page;
		$this->set('arr_listing', $prmObj->getPromotionClicks($criteria));
		$this->set('pages', $prmObj->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page-1)*$pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $prmObj->getTotalRecords();
		if ($total_records < $end_record) $end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->set('promotion_id',$promotion_id);
		$this->set('pagesize',$pagesize);
		$this->_template->render();
	}
	private function validatePromotionBalance(){
		$userObj=new User();
		$user_id=$this->getLoggedUserId();
		$balance=$userObj->getUserBalance($user_id);
		$minimum_wallet_limit=Settings::getSetting("CONF_MIN_WALLET_BALANCE");
		if ($balance < $minimum_wallet_limit){
			Message::addErrorMessage(sprintf(Utilities::getLabel('L_Promote_Request_Minimum_Balance_Less'),Utilities::displaymoneyformat($minimum_wallet_limit)));
			Utilities::redirectUserReferer();
		}
	}
	function promote_form($prm_id = 0){
		$prmObj=new Promotions();
		$promotion = $prmObj->getUserPromotion($prm_id, $this->getLoggedUserId());
		if ($promotion['promotion_type']==1) {
			Utilities::redirectUser(Utilities::generateUrl('account', 'promote_product',array($prm_id)));
		}else if ($promotion['promotion_type']==2) {
			Utilities::redirectUser(Utilities::generateUrl('account', 'promote_shop',array($prm_id)));
		}else if ($promotion['promotion_type']==3) {
			Utilities::redirectUser(Utilities::generateUrl('account', 'promote_banner',array($prm_id)));
		}else{
			Utilities::redirectUserReferer();
		}
	}
	function promote_product($prm_id = 0){
		if ($prm_id==0){
			$this->validatePromotionBalance();
		}
		$prmObj=new Promotions();
		$prm_id = intval($prm_id);
		$frm = $prmObj->getPromotionForm();
		$frm->fill(array("promotion_cost"=>Settings::getSetting("CONF_CPC_PRODUCT")));
		$frm->removeField($frm->getField('shop_name'));
		if($prm_id > 0 && $promotion = $prmObj->getUserPromotion($prm_id, $this->getLoggedUserId(),1)){
			$promotion['promotion_start_time']=date( 'H:i', strtotime($promotion['promotion_start_time']) );
			$promotion['promotion_end_time']=date( 'H:i', strtotime($promotion['promotion_end_time']) );
			$frm->fill($promotion);
		}
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				$psObj= new Products();
				$criteria=array("shop"=>$this->user_details['shop_id'],"active"=>1);
				$shop_owner_products=$psObj->getProducts($criteria);
				/*Utilities::printArray($shop_owner_products);
				die();*/
				foreach($shop_owner_products as $product){
					if ($product["prod_id"]==$post["promotion_product_id"]){
						$product_found = true;
						break;
					}
				}
				$promotion = $prmObj->getPromotions(array("date_interval"=>$post['promotion_start_date'].'~'.$post['promotion_end_date'],"product"=>$post["promotion_product_id"],"pagesize"=>1));
				if ($promotion && ($promotion['promotion_id']!=$post['promotion_id'])){
					Message::addErrorMessage(Utilities::getLabel('M_Promotion_already_exists'));
				}else{
					if (!empty($post["promotion_product_id"]) && is_numeric($post["promotion_product_id"]) && $product_found){
						$arr=array_merge($post,array("promotion_user_id"=>$this->getLoggedUserId(),"promotion_type"=>1,"promotion_cost"=>Settings::getSetting("CONF_CPC_PRODUCT")));					
						if($prmObj->addUpdatePromotion($arr)){
							Message::addMessage(Utilities::getLabel('M_Promotion_Added_Updated'));	
							Utilities::redirectUser(Utilities::generateUrl('account', 'promote'));
						}else{
							Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
						}
					}else{
						Message::addErrorMessage(Utilities::getLabel('M_Please_select_valid_product_promotion'));	
					}
				}
			}
			$frm->fill($post);
		}
		$this->set('frmPromote', $frm);
		$this->set('shop', $this->user_details['shop_id']);
		$this->_template->render();	
	}
	function promote_shop($prm_id = 0){
		if ($prm_id==0){
			$this->validatePromotionBalance();
		}
		$prmObj=new Promotions();
		$prm_id = intval($prm_id);
		$frm = $prmObj->getPromotionForm();
		$frm->fill(array("promotion_cost"=>Settings::getSetting("CONF_CPC_SHOP")));
		$frm->removeField($frm->getField('prod_name'));
		$frm->fill(array("shop_name"=>$this->user_details['shop_name'],"promotion_shop_id"=>$this->user_details['shop_id']));
		if($prm_id > 0 && $promotion = $prmObj->getUserPromotion($prm_id, $this->getLoggedUserId(),2)){
			$promotion['promotion_start_time']=date( 'H:i', strtotime($promotion['promotion_start_time']) );
			$promotion['promotion_end_time']=date( 'H:i', strtotime($promotion['promotion_end_time']) );
			$frm->fill($promotion);
		}
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				$promotion = $prmObj->getPromotions(array("date_interval"=>$post['promotion_start_date'].'~'.$post['promotion_end_date'],"shop"=>$this->user_details['shop_id'],"pagesize"=>1));
				if ($promotion && ($promotion['promotion_id']!=$post['promotion_id'])){
					Message::addErrorMessage(Utilities::getLabel('M_Promotion_already_exists'));
				}else{
					if (!empty($this->user_details['shop_id']) && is_numeric($this->user_details['shop_id'])){
						$arr=array_merge($post,array("promotion_user_id"=>$this->getLoggedUserId(),"promotion_shop_id"=>$this->user_details['shop_id'],"promotion_type"=>2,"promotion_cost"=>Settings::getSetting("CONF_CPC_SHOP")));
						if($prmObj->addUpdatePromotion($arr)){
							Message::addMessage(Utilities::getLabel('M_Promotion_Added_Updated'));	
							Utilities::redirectUser(Utilities::generateUrl('account', 'promote'));
						}else{
							Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
						}
					}else{
						Message::addErrorMessage(Utilities::getLabel('M_Please_select_valid_shop_promotion'));	
					}
				}
			}
			$frm->fill($post);
		}
		$this->set('frmPromote', $frm);
		$this->set('shop', $this->user_details['shop_id']);
		$this->_template->render();	
	}
	function promote_banner($prm_id = 0){
		if ($prm_id==0){
			$this->validatePromotionBalance();
		}
		$prmObj=new Promotions();
		$prm_id = intval($prm_id);
		$frm = $prmObj->getPromotionForm();
		$frm->fill(array("promotion_cost"=>Settings::getSetting("CONF_CPC_BANNER")));
		$frm->addRequiredField('<label>'.Utilities::getLabel('M_Banner_Name').'</label>', 'promotion_banner_name');
		$fld_position=$frm->addSelectBox('<label>'.Utilities::getLabel('M_Banner_Position').'</label>', 'promotion_banner_position',array('TB'=>Utilities::getLabel('M_Top'),'BB'=>Utilities::getLabel('M_Bottom')),' ',' class="small-select-box" ',Utilities::getLabel('M_Select'),'promotion_banner_position');
		$fld_position->requirements()->setRequired();
		$fld_position->html_after_field='<a href="'.CONF_WEBROOT_URL.'images/banner-positions/wireframe.jpg" id="fancy_popup_box" >'.Utilities::getLabel('M_View_Wire_Frame').'</a>';
		//$fld_position->html_after_field='<span id="banner_position" class="img-div"></span>';
		$fld_banner_file=$frm->addFileUpload('<label>'.Utilities::getLabel('M_Banner_File').'</label>', 'promotion_banner_file', 'promotion_banner_file');
		$fld_banner_file->requirements()->setRequired();
		
		$fld=$frm->addRequiredField('<label>'.Utilities::getLabel('M_Banner_URL').'</label>', 'promotion_banner_url');
		$fld->requirements()->setRegularExpressionToValidate('^(http(?:s)?\:\/\/[a-zA-Z0-9]+(?:(?:\.|\-)[a-zA-Z0-9]+)+(?:\:\d+)?(?:\/[\w\-]+)*(?:\/?|\/\w+\.[a-zA-Z]{2,4}(?:\?[\w]+\=[\w\-]+)?)?(?:\&[\w]+\=[\w\-]+)*)$');
		$fld->html_after_field='<small>'.Utilities::getLabel('M_Please_enter_URL').' - http://www.example.com</small>';
		$frm->addSelectBox('<label>'.Utilities::getLabel('M_Banner_Target').'</label>', 'promotion_banner_target',array('_self'=>Utilities::getLabel('M_Self'),'_blank'=>Utilities::getLabel('M_Blank')))->requirements()->setRequired();
		$frm->changeFieldPosition($frm->getField('promotion_banner_name')->getFormIndex(),2);
		$frm->changeFieldPosition($frm->getField('promotion_banner_position')->getFormIndex(),3);
		$frm->changeFieldPosition($frm->getField('promotion_banner_file')->getFormIndex(),4);
		$frm->changeFieldPosition($frm->getField('promotion_banner_url')->getFormIndex(),5);
		$frm->changeFieldPosition($frm->getField('promotion_banner_target')->getFormIndex(),6);
		$frm->removeField($frm->getField('prod_name'));
		$frm->removeField($frm->getField('shop_name'));
		if($prm_id > 0 && $promotion = $prmObj->getUserPromotion($prm_id, $this->getLoggedUserId(),3)){
			$fld_banner_file->requirements()->setRequired(false);
			$frm->getField('promotion_banner_file')->html_after_field = '<div class="uploadedphoto"><img alt="" src="'. Utilities::generateUrl('image', 'promotion_banner', array($promotion['promotion_banner_file'],'THUMB')) .'" id="dpic" />'.((strlen($user['user_profile_image']))?'':'').'</div>';
			$promotion['promotion_start_time']=date( 'H:i', strtotime($promotion['promotion_start_time']) );
			$promotion['promotion_end_time']=date( 'H:i', strtotime($promotion['promotion_end_time']) );
			$frm->fill($promotion);
		}
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if($_FILES['promotion_banner_file']['error'] === 0){
				$post['promotion_banner_file']= $_FILES['promotion_banner_file']['name'];
			}
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				if (Utilities::isUploadedFileValidImage($_FILES['promotion_banner_file'])){
					if(!Utilities::saveImage($_FILES['promotion_banner_file']['tmp_name'],$_FILES['promotion_banner_file']['name'], $saved_banner_file, 'promotions/')){
						Message::addErrorMessage($saved_banner_file);
						$error_found=true;
					}
					$post["promotion_banner_file"]=$saved_banner_file;
				}else{
					if (empty($post['promotion_id']))
						$error_found=true;
				}
				if (!$error_found){
					$arr=array_merge($post,array("promotion_user_id"=>$this->getLoggedUserId(),"promotion_type"=>3,"promotion_cost"=>Settings::getSetting("CONF_CPC_BANNER")));
					if($prmObj->addUpdatePromotion($arr)){
						Message::addMessage(Utilities::getLabel('M_Promotion_Added_Updated'));	
						$promotion_is_approved=$prmObj->getAttribute('promotion_is_approved');
						if (!$promotion_is_approved){
							Message::addMessage(Utilities::getLabel('M_Promotion_Banner_Published_Admin_Approval'));
							$emailNotObj=new Emailnotifications();	
							if (!$emailNotObj->sendNotifyAdminPromotion($prmObj->promotion_id)){
								$this->error=$emailNotObj->getError();
							}
						}
						Utilities::redirectUser(Utilities::generateUrl('account', 'promote'));
					}else{
						Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
					}
				}else{
					Message::addErrorMessage(Utilities::getLabel('M_Please_select_valid_banner_file_for_promotion'));	
				}
			}
			$frm->fill($post);
		}
		$this->set('frmPromote', $frm);
		$this->set('shop', $this->user_details['shop_id']);
		$this->_template->render();	
	}
	function promotion_status($id,$mod='block') {
		$prmObj=new Promotions();
		$user_id=$this->getLoggedUserId();
		$promotion = $prmObj->getUserPromotion($id,$user_id);
		if ($promotion==true){
			switch($mod) {
				case 'block':
				$data_to_update = array(
					'promotion_status'=>0,
					);
				break;
				case 'unblock':
				$data_to_update = array(
					'promotion_status'=>1,
					'promotion_resumption_date'=>date('Y-m-d H:i:s'),
					);
				break;
			}
			if($prmObj->updatePromotionStatus(intval($id),$data_to_update)){
				Message::addMessage(Utilities::getLabel('L_Status_modified_successfully'));
			}else{
				Message::addErrorMessage($prmObj->getError());
			}
		}else{
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
		}
		Utilities::redirectUserReferer();
	}
	function promotion_analytics($id){
		$post = Syspage::getPostedVar();
		$page = intval($post["page"]);
		unset($arr["page"]);
		$frmSearchForm=$this->getPromotionSearchForm();
		$frmSearchForm->fill($post);
		if ($page < 1)
			$page = 1;
		$pagesize = 10;
		$user_id=$this->getLoggedUserId();
		$criterias=array("user"=>$user_id,"id"=>$id,"pagesize"=>$pagesize);
		if (isset($post['mode'])){
			$criterias=array_merge($post,$criterias);
		}
		$prmObj=new Promotions();
		$this->set('arr_listing', $prmObj->getPromotionLogs($criterias));
		$this->set('pages', $prmObj->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page-1)*$pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $prmObj->getTotalRecords();
		if ($total_records < $end_record) $end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->set('search_parameter',$get);
		$this->set('frm',$frmSearchForm);
		$this->_template->render();
	}
	protected function getPromotionSearchForm()	{
		$frm=new Form('frmSearchPromotion','frmSearchPromotion');
		$frm->setFieldsPerRow(4);
		$frm->setExtra('class="siteForm ondark" rel="search"');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addHiddenField('', 'page', '1');
		$fld=$frm->addTextBox('', 'date_from', '', '', ' placeholder="'.Utilities::getLabel('L_Date_From').'" readonly class="date-pick calendar"');
		$fld=$frm->addTextBox('', 'date_to', '', '', ' placeholder="'.Utilities::getLabel('L_Date_To').'" readonly class="date-pick calendar"');
		$frm->addSubmitButton('','btn_submit',Utilities::getLabel('M_Search'),'','class="btn primary-btn"');
		$frm->getField("btn_submit")->html_after_field='&nbsp;&nbsp;<a href="?" class="btn secondary-btn">'.Utilities::getLabel('M_Clear').'</a>';
		$frm->setJsErrorDisplay('afterfield');
		$frm->setAction('?');
		$this->set('frm', $frm);
		return $frm;
	}
	function payment_failed(){
		Message::addErrorMessage(sprintf(Utilities::getLabel('M_customer_failure_wallet_recharge'),Utilities::generateUrl('custom','contact_us'	)));
		Utilities::redirectUser(Utilities::generateUrl('account', 'credits'));
	}
	function payment_success(){
		Message::addMessage(sprintf(Utilities::getLabel('M_customer_success_wallet_recharge'), Utilities::generateUrl('account','credits')));
		Utilities::redirectUser(Utilities::generateUrl('account', 'credits'));
	}
	function dashboard_advertiser(){
		$prmObj=new Promotions();
		$userObj = new User();
		$balance=$userObj->getUserBalance($this->getLoggedUserId());
		$minimum_wallet_limit=Settings::getSetting("CONF_MIN_WALLET_BALANCE");
		$arr_promotions = $prmObj->getPromotions(array("user"=>$this->getLoggedUserId(),"pagesize"=>5));
		foreach($arr_promotions as $pkey=>$pval){
			if ($pval["promotion_start_date"]<=date("Y-m-d") && $pval["promotion_end_date"]>=date("Y-m-d") && ($balance<$minimum_wallet_limit)){
				$pval['promotion_min_balance']=1;
				if (!isset($error_warning)){
					Message::addErrorMessage(sprintf(Utilities::getLabel('L_Promotion_Minimum_Balance_Less'),Utilities::displaymoneyformat($minimum_wallet_limit)));
					$error_warning = true;
				}
			}
			$promotions[]=$pval;
		}
		$this->set('error_warning', $error_warning);
		$this->set('promotions',$promotions);
		$frm = $this->getRechargeWalletForm();
		$this->set('walletfrm',$frm);
		$this->_template->render();	
	}
	
	function downloads(){
		$post = Syspage::getPostedVar();
		$page = intval($post["page"]);
		if ($page < 1)
			$page = 1;
		$pagesize = 10;
		$user_id=$this->getLoggedUserId();
		$criterias=array("user"=>$user_id,'status'=>(array)Settings::getSetting("CONF_DIGITAL_DOWNLOAD_STATUS"),'page'=>$page,'pagesize'=>$pagesize);
		$userObj=new User();
		$my_downloads=$userObj->getUserDownloads($criterias);
		$this->set('my_downloads',$my_downloads);
		$this->set('pages', $userObj->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page-1)*$pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $userObj->getTotalRecords();
		if ($total_records < $end_record) $end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->set('user_id', $user_id);
		$this->_template->render();	
	}
	
	function download_file($code){
		$strArr= explode(".",(base64_decode($code)));
		$user_id=$this->getLoggedUserId();
		$criterias=array("user"=>$user_id,'status'=>(array)Settings::getSetting("CONF_DIGITAL_DOWNLOAD_STATUS"),'id'=>$strArr[0],'downloads_remaining'=>1,'pagesize'=>1);
		$userObj=new User();
		$download=$userObj->getUserDownloads($criterias);
		if ($download && ($strArr[0]==$download['opf_id']) && ($strArr[1]==$download['opf_opr_id']) && ($strArr[2]==$user_id)){
			list($dt_year, $dt_month, $dt_day) = explode('-', $download['order_date_added']);
  			$download_timestamp = mktime(23, 59, 59, $dt_month, $dt_day + $download['opf_file_can_be_downloaded_within_days'], $dt_year);
			// Die if time expired (maxdays = -1 means no time limit)
			if (($download['opf_file_can_be_downloaded_within_days'] != "-1") && ($download_timestamp <= time())) {
				Message::addErrorMessage(Utilities::getLabel('M_ERROR_DOWNLOAD_EXPIRED'));
				Utilities::redirectUserReferer();
			}
  
			Utilities::outputFile("product_downloads/".$download["opf_file_name"],$download["opf_file_download_name"]);
			$userObj->incrementUserFileDownloadCount($download['opf_id']);
		}else{
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_DOWNLOAD_EXPIRED'));
			Utilities::redirectUserReferer();
		}
	}
	
	function setProductImagesOrdering(){
		$this->db = Syspage::getdb();
		$post = Syspage::getPostedVar();
		$pObj=new Products();
		if(isset($post['do_submit']))  {
				$ids = explode(',',$post['sort_order']);
				foreach($ids as $index=>$id) {
					$id = (int) $id;
					if($id != '') {
						$this->db->update_from_array('tbl_product_images', array('image_ordering' => ($index + 1)), array('smt' => 'image_id = ?', 'vals' => array($id)));
					}
				}
				if($post['byajax']) { die(); } else { $message = 'Sortation has been saved.'; }
		}
		
	}
	
	function save_image_orientation(){
		$post = Syspage::getPostedVar();
		$pObj=new Products();
		if(isset($post['do_submit']))  {
			$product_image = $pObj->getProductImageData($post['image']);
			if (isset($product_image)){
				/*require_once (CONF_INSTALLATION_PATH . 'public/includes/imagemanipulation.php');
				$img = new ImageManipulation();
				$filename= CONF_INSTALLATION_PATH . 'user-uploads/products/'.$product_image['image_file'];
				$ext = pathinfo($filename, PATHINFO_EXTENSION);
				$img->load($filename);
				$img->rotate_image(($post['rotation']), 'ffffff', 127);
				$img->save_image($filename, $ext);*/
				$filename= CONF_INSTALLATION_PATH . 'user-uploads/products/'.$product_image['image_file'];
				$size = getimagesize($filename);
				$arr = array('rotate'=>$post['rotation'],'width'=>$size[0],'height'=>$size[1],'x'=>0,'y'=>0);
				Utilities::rotateimage($post['rotation'],$filename);
				die(Utilities::getLabel('L_Image_with_orientation_saved'));
			}
		}
		
	}
	
}
