<?php
class OrdersController extends BackendController {
	
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,ORDERS);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Orders Management", Utilities::generateUrl("orders"));
    }
	
	protected function getSearchForm() {
		global $payment_status_arr;
		$osObj=new Orderstatus();
      	$frm=new Form('frmOrderSearch','frmOrderSearch');
		$frm->setFieldsPerRow(2);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('GET');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addHiddenField('', 'user', "");
		$fld=$frm->addTextBox('Keyword', 'keyword','','',' class="medium"');
		$fld->html_after_field='<small>Name, Username, Email, Invoice Number, Reference Number</small>';
		$fld->merge_cells=2;
		$fld_from=$frm->addTextBox('From ['.CONF_CURRENCY_SYMBOL.']', 'minprice','','',' class="small"')->requirements()->setFloatPositive($val=true);
		$fld_to = $frm->addTextBox('To ['.CONF_CURRENCY_SYMBOL.']', 'maxprice','','',' class="small"');
		$fld_to->requirements()->setFloatPositive($val=true);
		$fld=$frm->addDateField('Date From', 'date_from', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld=$frm->addDateField('Date To', 'date_to', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld=$frm->addTextBox('Customer', 'customer_name','','customer_name',' class="small"');
		$frm->addSelectBox('Payment Status', 'payment_status', $payment_status_arr, '', 'class="small"', 'Select');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchOrders(this); return false;');
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
	
	function listOrders($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
			$oObj=new Orders();
            $post = Syspage::getPostedVar();
            $page = 1;
            if (isset($post['page']) && intval($post['page']) > 0) {
                $page = intval($post['page']);
            } else {
                $post['page'] = $page;
            }
            if (!empty($post)) {
                $this->set('srch', $post);
            }
           	$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
            $post['pagesize'] = $pagesize;
            $this->set('records', $oObj->getOrders($post));
            $this->set('pages', $oObj->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $oObj->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
	
	
	protected function getPaymentForm(){
			$frm=new Form('ordersDetail');
			$frm->setExtra(' validator="OrdersfrmValidator" class="web_form"');
			$frm->setLeftColumnProperties('valign="top" align="left"');
			$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
			$frm->captionInSameCell(false);
			$frm->setFieldsPerRow(1);
			$frm->setRequiredStarWith('caption');
			$frm->addRequiredField('Payment Method', 'op_payment_method','', '', ' class="small"');
			$frm->addRequiredField('Txn ID', 'op_gateway_txn_id','', '', ' class="small"');
			$frm->addRequiredField('Amount', 'op_amount','','',' class="small"')->requirements()->setFloatPositive($val=true);
			$fld=$frm->addTextArea('Comments', 'comments', '', 'comments', ' rows="3"');
			$fld->html_after_field='Please enter some comments/details about this transaction.';
			$fld->requirements()->setRequired();
			$frm->setJsErrorDisplay('afterfield');
			$frm->addSubmitButton('&nbsp;', 'btn_submit', 'Update');
			return $frm;
	}
		
	function view($order_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("View Order", '');
		$this->set('breadcrumb', $this->b_crumb->output());
		$oObj=new Orders();
		global $payment_status_arr;
        $order_id = intval($order_id);
        $data = $oObj->getOrderById($order_id);
		if (!$data){
			Message::addErrorMessage('Error: Please perform this action on valid record.');
            Utilities::redirectUser(Utilities::generateUrl('orders'));
		}
		$data["addresses"]=$oObj->getOrderBillingShippingAddress($order_id);
		$data["products"]=$oObj->getOrderProductsById($order_id);
		$data["comments"]=$oObj->getOrderComments(array("order"=>$order_id));
		$data["payments"]=$oObj->getOrderPayments(array("order"=>$order_id));
				
		$payment_status_array=$payment_status_arr;
		unset($payment_status_array[$data["order_payment_status"]]);
        $this->set('order', $data);
		$frm=$this->getPaymentForm();
		$this->set('frm',$frm);
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
					$orderPaymentObj = new OrderPayment($order_id);
					//if($orderPaymentObj->addOrderPayment($post["op_payment_method"],$post['op_gateway_txn_id'],$post["op_amount"],Utilities::getLabel("L_Received_Payment"))){
					 if($orderPaymentObj->addOrderPayment($post["op_payment_method"],$post['op_gateway_txn_id'],$post["op_amount"],($post['comments'] != '') ? $post['comments'] : Utilities::getLabel("L_Received_Payment"))){
							Message::addMessage('Success: Payment details added successfully.');
							Utilities::redirectUser(Utilities::generateUrl('orders', 'view', array($order_id)));
					}else{
							Message::addErrorMessage($orderPaymentObj->getError());
						}
					}
			$frm->fill($post);
		}
        $this->_template->render();
    }
	
	function cancel() {
        if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
        $post = Syspage::getPostedVar();
		$oObj=new Orders();
        $order_id = intval($post['id']);
        $order = $oObj->getOrderById($order_id);
		if($order==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		if (!$order["order_payment_status"]){
			if($oObj->addOrderPaymentHistory($order_id,-1,"Order Cancelled",1)){	
				Message::addMessage('Success: Order has been cancelled successfully.');
				if($oObj->refundOrderPaidAmount($order_id)){
					$oObj->refundOrderRewardPoints($order_id);
					Message::addMessage('Success: Order paid amount refunded & reward points reversed successfully.');
					dieJsonSuccess(Message::getHtml());
				}else{
					Message::addErrorMessage($oObj->getError());
					dieJsonError(Message::getHtml());
				}
			}
		}
		Message::addErrorMessage('Error: Invalid Record.');
    }
	
	
	function customers_autocomplete(){
		$post = Syspage::getPostedVar();
		$json = array();
		$oObj=new Orders();
        $orders=$oObj->getDistinctOrderCustomers(urldecode($post["keyword"]));
		foreach($orders as $okey=>$oval){
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