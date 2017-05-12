<?php
class VendorOrdersController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,VENDORORDERS);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Vendor Orders Management", Utilities::generateUrl("vendororders"));
    }
	
	protected function getSearchForm() {
		$oObj=new Orders();
		$osObj=new Orderstatus();
        $frm=new Form('frmVendorOrderSearch','frmVendorOrderSearch');
		$frm->setFieldsPerRow(3);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('GET');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addHiddenField('', 'customer','','customer');
		$frm->addTextBox('Keyword', 'keyword','','',' class="small"');
		$frm->addSelectBox('Status', 'status', $osObj->getAssociativeArray(), '', 'class="small"', 'Select');
		$frm->addSelectBox('Order', 'order',$oObj->getAssociativeArray(), '', 'class="small"', 'Select');
		$userObj=new User();
		$fld=$frm->addTextBox('Customer', 'order_customer_name','','order_customer_name',' class="small"');
		$fld=$frm->addTextBox('Shop', 'shop_name','','vendor',' class="small"');
		$frm->addTextBox('From ['.CONF_CURRENCY_SYMBOL.']', 'minprice','','',' class="small"')->requirements()->setFloatPositive($val=true);
		$frm->addTextBox('To ['.CONF_CURRENCY_SYMBOL.']', 'maxprice','','',' class="small"')->requirements()->setFloatPositive($val=true);
		$fld=$frm->addDateField('Date From', 'date_from', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld=$frm->addDateField('Date To', 'date_to', '', '', 'readonly="true" class="small dateTimeFld"');
		$frm->addHtml('&nbsp;', '&nbsp;','&nbsp;');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchVendorOrders(this); return false;');
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
	
	function listVendorOrders($page = 1) {
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
			$post['status_not'] = 0;
            $this->set('records', $oObj->getChildOrders($post));
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
	
	
	protected function getOrderCommentsForm($data,$processing_completed_status){
		$osObj=new Orderstatus();
		$frm=new Form('ordersDetail');
		$frm->setExtra(' validator="OrderfrmValidator" class="web_form"');
		$frm->setValidatorJsObjectName('OrderfrmValidator');
		$frm->setLeftColumnProperties('valign="top" align="left"');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="tblBorderTop"');
		$frm->captionInSameCell(true);
		$frm->setFieldsPerRow(1);
		$frm->setJsErrorDisplay('afterfield');
		$frm->addTextArea('Your Comment', 'comments', '', 'comments');
		$frm->addSelectBox(Utilities::getLabel('M_Status'), 'opr_status',$osObj->getAssociativeArray($processing_completed_status,$data["opr_status"]),$data["opr_status"], 'class="small"','','opr_status');
		
		$fldBL=$frm->addTextBox('<span id="div_tracking_number">Tracking Number', 'tracking_number','','', '','tracking_number','');
		$fldBL->html_after_field='</span>';
		$frm->addCheckBox('Notify Customer', 'customer_notified','1','notify_customer');
		$frm->addSubmitButton('&nbsp;', 'btn_submit', 'Update');
		return $frm;
	}
	
	function get_values_for_keys($mapping, $keys) {
		foreach($mapping as $key) {
			$output_arr[] = $key[$keys];
		}
		return $output_arr;
	}
	
    function view($order_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("View Vendor Order", '');
		$this->set('breadcrumb', $this->b_crumb->output());
		$oObj=new Orders();
		$processing_completed_status=$oObj->getAdminAllowedUpdateOrderStatuses();
		
        $order_id = intval($order_id);
        $order_info = $oObj->getOrderProductsByOprId($order_id);
		if (!$order_info){
			Message::addErrorMessage('Error: Please perform this action on valid record.');
            Utilities::redirectUser(Utilities::generateUrl('vendororders'));
		}
		$osObj=new Orderstatus();
		if ($order_info['opr_product_type']==2){
			$order_statuses = $this->get_values_for_keys($osObj->getOrderStatuses(array('digital'=>1)),"orders_status_id");
			$processing_completed_status = array_intersect((array)$order_statuses,$processing_completed_status);
			$processing_completed_status=array_merge((array)$processing_completed_status,(array)Settings::getSetting("CONF_DEFAULT_PAID_ORDER_STATUS"));
			$processing_completed_status=array_merge($processing_completed_status,(array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS"));
			$processing_completed_status=array_diff($processing_completed_status,(array)Settings::getSetting("CONF_RETURN_REQUEST_APPROVED_ORDER_STATUS"));
		}
		if ($order_info['opr_cod_order']==1){
			$processing_completed_status=array_merge((array)$processing_completed_status,(array)Settings::getSetting("CONF_DEFAULT_COD_ORDER_STATUS"));
		}
		/*if ($order_info['opr_product_type']==1){
			$order_statuses = $this->get_values_for_keys($osObj->getOrderStatuses(array('digital'=>0)),"orders_status_id");
			$processing_completed_status = array_intersect((array)$order_statuses,$processing_completed_status);
			$processing_completed_status=array_merge((array)$processing_completed_status,(array)Settings::getSetting("CONF_DEFAULT_PAID_ORDER_STATUS"));
		}elseif ($order_info['opr_product_type']==2){
			$order_statuses = $this->get_values_for_keys($osObj->getOrderStatuses(array('digital'=>1)),"orders_status_id");
			$processing_completed_status = array_intersect((array)$order_statuses,$processing_completed_status);
			$processing_completed_status=array_merge((array)$processing_completed_status,(array)Settings::getSetting("CONF_DEFAULT_PAID_ORDER_STATUS"));
		}*/
		$order_info["addresses"]=$oObj->getOrderBillingShippingAddress($order_info["opr_order_id"]);
		$order_info["comments"]=$oObj->getOrderComments(array("opr"=>$order_id));
        $this->set('order', $order_info);
		$frm=$this->getOrderCommentsForm($order_info,$processing_completed_status);
		$this->set('frm',$frm);
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
					if($oObj->addChildOrderHistory($order_id,$post["opr_status"],$post["comments"],$post["customer_notified"],$post["tracking_number"])){
							Message::addMessage('Success: Your order comments added successfully.');
							Utilities::redirectUser(Utilities::generateUrl('vendororders', 'view', array($order_id)));
					}else{
							Message::addErrorMessage($oObj->getError());
						}
					}
			$frm->fill($post);
		}
		/*print_r($processing_completed_status);
		die($order_info['opr_status']."#");*/
		$this->set('display_form',(in_array($order_info['opr_status'],$processing_completed_status)));
        $this->_template->render();
    }
	
	protected function getOrderCancelForm(){
			$frm=new Form('ordersDetail');
			$frm->setRequiredStarWith('x');
			$frm->setExtra(' validator="OrderfrmValidator" class="web_form"');
			$frm->setLeftColumnProperties('valign="top" align="left"');
			$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
			$frm->setValidatorJsObjectName('OrderfrmValidator');
			$frm->captionInSameCell(false);
			$frm->setFieldsPerRow(1);
			$frm->setJsErrorDisplay('afterfield');
			$fld=$frm->addTextArea('Comments', 'comments', '', 'comments');
			$fld->requirements()->setRequired(true);
			$fld->requirements()->setCustomErrorMessage('Please enter your reason for cancellation');
			$frm->addSubmitButton('&nbsp;', 'btn_submit', 'Submit');
			$this->set('frm',$frm);
			return $frm;
	}
	
	function cancel_order($order_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("Cancel Order", '');
		$this->set('breadcrumb', $this->b_crumb->output());
		$oObj=new Orders();
		$osObj=new Orderstatus();
        $order_id = intval($order_id);
        $order_info = $oObj->getOrderProductsByOprId($order_id);
		if (!$order_info){
			Message::addErrorMessage('Error: Please perform this action on valid record.');
            Utilities::redirectUser(Utilities::generateUrl('vendororders'));
		}
		
		$order_info["addresses"]=$oObj->getOrderBillingShippingAddress($order_info["opr_order_id"]);
		$order_info["comments"]=$oObj->getOrderComments(array("opr"=>$order_id));
        $this->set('order', $order_info);
		
		$arr_pro_com=$oObj->getNotAllowedOrderCancellationStatuses();
		if ($order_info["opr_status"]!=Settings::getSetting("CONF_DEFAULT_ORDER_STATUS") && $order_info['opr_cod_order']){
			Message::addErrorMessage('Error: Cash on Delivery order(s) cannot be cancelled at this stage.');
            Utilities::redirectUser(Utilities::generateUrl('vendororders'));
		}
		$not_eligible = false;
		if (in_array($order_info["opr_status"],$arr_pro_com)){	
			$not_eligible=true;
		}
		$frm=$this->getOrderCancelForm();
		$this->set('frm',$frm);
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
				if(!$frm->validate($post)){
					Message::addErrorMessage($frm->getValidationErrors());
				}else{
					if($oObj->addChildOrderHistory($order_id,Settings::getSetting("CONF_DEFAULT_CANCEL_ORDER_STATUS"),$post["comments"],true)){
							Message::addMessage('Success: Order has been updated successfully.');
							Utilities::redirectUser(Utilities::generateUrl('vendororders', 'view', array($order_id)));
					}else{
							Message::addErrorMessage($oObj->getError());
						}
					}
				$frm->fill($post);
		}
		$this->set('not_eligible', $not_eligible);
		$this->_template->render();
    }	
	
	function shops_autocomplete(){
		
		$post = Syspage::getPostedVar();
		$json = array();
		$oObj=new Orders();
        $orders=$oObj->getDistinctOrderShops(urldecode($post["keyword"]));
		foreach($orders as $okey=>$oval){
			$json[] = array(
					'data' => $oval['shop_id'],
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