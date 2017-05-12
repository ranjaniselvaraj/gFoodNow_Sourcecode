<?php
class SubscriptionCouponsController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,SUBSRIPTIONDISCOUNTCOUPONS);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Subscription Coupons Management", Utilities::generateUrl("subscriptioncoupons"));
    }
    
	protected function getSearchForm() {
       $frm=new Form('frmSubscriptionCouponSearch','frmSubscriptionCouponSearch');
		$frm->setFieldsPerRow(2);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('GET');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addTextBox('Keyword', 'keyword','','',' class="small"');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchSubscriptionCoupons(this); return false;');
        return $frm;
    }
	
    function default_action() {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $frm = $this->getSearchForm();
        $this->set('frmPost', $frm);
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listsubscriptioncoupons($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
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
            $this->set('records', $this->Subscriptioncoupons->getCoupons($post));
            $this->set('pages', $this->Subscriptioncoupons->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $this->Subscriptioncoupons->getTotalRecords();
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
    function form($coupon_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("Subscription Coupon Setup", Utilities::generateUrl("brands"));
		$this->set('breadcrumb', $this->b_crumb->output());
        $coupon_id = intval($coupon_id);
        if ($coupon_id > 0) {
            $data = $this->Subscriptioncoupons->getData($coupon_id);
			$data['subscoupon_categories'] = array();
			$data['subscoupon_history']=$this->Subscriptioncoupons->getCouponHistories($coupon_id);
			$this->set('data', $data);
        }
		$frm = $this->getForm($data);
		$frm->fill($data);
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				if($post['subscoupon_id'] != $coupon_id){
					Message::addErrorMessage('Error: Invalid Request!!');
					Utilities::redirectUser(generateUrl('subscriptioncoupons'));
				}else{
						$coupon = $this->Subscriptioncoupons->getCouponByCode($post['subscoupon_code']);
						if (($coupon==true) && ($coupon["coupon_id"]!=$post["coupon_id"]) ){
							Message::addErrorMessage('Coupon with this code already exists.');
						}else{
							if($this->Subscriptioncoupons->addUpdate($post)){
								Message::addMessage('Success: Coupon details added/updated successfully.');
								Utilities::redirectUser(Utilities::generateUrl('subscriptioncoupons'));
							}else{
								Message::addErrorMessage($this->Subscriptioncoupons->getError());
							}
						}
					}
			}
			$frm->fill($post);
		}
        $this->set('frm', $frm);
		$this->_template->render();
    }
	
	
    protected function getForm($info) {
		
		
		$packObj = new SubscriptionPackages();
		$packages = $packObj->getAssociativeArray();
		$subPackObj = new SubPackages();
		
		$subPackages = $subPackObj->getAssocSubPackages(array('exclude_free_package' => true,'merchantsubpack_merchantpack_id'=>$info['package']));
        $frm = new Form('frmCoupons');
		$frm->setExtra(' validator="CouponsfrmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('CouponsfrmValidator');
        $frm->addHiddenField('', 'subscoupon_id');
		$frm->addRequiredField('Name', 'subscoupon_name','', '', ' class="medium"');
		$fld=$frm->addTextArea('Description', 'subscoupon_description', '', 'subscoupon_description', 'rows="3" class="medium"');
		$fld->html_after_field = '<br/><small>Please enter complete coupon description along with terms & conditions.</small>';
		$fld=$frm->addRequiredField('Code', 'subscoupon_code','', '', ' class="small"');
		$fld->requirements()->setLength(6,12);
		$frm->addSelectBox('Plan', 'package', $packages,'',' class="small" onchange="getSubPackages(this.value)"','--Select Plan--' , 'ddlPackage');
		$fld =  $frm->addSelectBox('Subscription Billing', 'subscoupon_merchantsubpack',$subPackages,'',' class="small"','' , 'ddlSubPackage');
		$fld->requirements()->setRequired();
	
		$frm->addSelectBox('Discount Type', 'subscoupon_discount_type', array('P'=>'Percentage (%)','F'=>'Fixed'),array("P"),' class="small"','');
		$frm->addRequiredField('Discount Value', 'subscoupon_discount_value','', '', ' class="small"');
		$frm->addRequiredField('Max Discount Value', 'subscoupon_max_discount_value','', '', ' class="small"');
		
		$fld=$frm->addDateField('Start Date', 'subscoupon_start_date', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld->requirements()->setRequired();
		$fld=$frm->addDateField('End Date', 'subscoupon_end_date', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld->requirements()->setRequired();
		//$fld->requirements()->setCompareWith('subscoupon_start_date','ge','');
		$frm->addSelectBox('Discount Valid For', 'subscoupon_discount_valid_for', Applicationconstants::$discount_valid_for,'',' class="small"','');
		$fld=$frm->addTextBox('Uses Per Coupon', 'subscoupon_uses_per_coupon','1', '', ' class="small"');
		$fld->html_after_field = '<br/><small>Maximum number of times a coupon can be used by any customer.Leave blank for unlimited uses.</small>';
		$fld=$frm->addTextBox('Uses Per Customer', 'subscoupon_uses_per_customer','1', '', ' class="small"');
		$fld->html_after_field = '<br/><small>Maximum number of times a coupon can be used by a single customer.Leave blank for unlimited uses.</small>';
		$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="20%"');
        return $frm;
    }
	
	function update_coupon_status() {
		if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
		$post = Syspage::getPostedVar();
		$coupon_id = intval($post['id']);
        $coupon = $this->Subscriptioncoupons->getData($coupon_id);
		if($coupon==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		$data_to_update = array('subscoupon_active'=>!$coupon['subscoupon_active']);
		if($this->Subscriptioncoupons->updateCouponStatus($coupon_id,$data_to_update)){
			Message::addMessage('Success: Status has been modified successfully.');
			$arr = array('status'=>1, 'msg'=>Message::getHtml(), 'linktext'=>(($coupon['subscoupon_active'] == 1)?'Active':'Inactive'));
			die(convertToJson($arr));
		}else{
			Message::addErrorMessage($this->Subscriptioncoupons->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	function delete() {
        if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
        $post = Syspage::getPostedVar();
        $coupon_id = intval($post['id']);
        $coupon = $this->Subscriptioncoupons->getData($coupon_id);
		if($coupon==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		if($this->Subscriptioncoupons->delete($coupon_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($this->Subscriptioncoupons->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	
}