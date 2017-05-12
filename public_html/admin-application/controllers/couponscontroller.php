<?php
class CouponsController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,DISCOUNTCOUPONS);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Coupons Management", Utilities::generateUrl("coupons"));
    }
    
	protected function getSearchForm() {
       $frm=new Form('frmCouponSearch','frmCouponSearch');
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
		$frm->setOnSubmit('searchCoupons(this); return false;');
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
	
	function listCoupons($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
			$bObj=new Brands();
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
            $this->set('records', $this->Coupons->getCoupons($post));
            $this->set('pages', $this->Coupons->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $this->Coupons->getTotalRecords();
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
		$this->b_crumb->add("Coupons Setup", Utilities::generateUrl("coupons"));
		$this->set('breadcrumb', $this->b_crumb->output());
        $coupon_id = intval($coupon_id);
        if ($coupon_id > 0) {
            $data = $this->Coupons->getData($coupon_id);
			$data['coupon_categories'] = array();
			$categories = $this->Coupons->getCouponCategories($coupon_id);
			foreach ($categories as $key=>$val) {
				$data['coupon_categories'][]=$val;
			}
			$products = $this->Coupons->getCouponProducts($coupon_id);
			foreach ($products as $key=>$val) {
				$data['coupon_products'][]=$val;
			}
			$data['coupon_history']=$this->Coupons->getCouponHistories($coupon_id);
			$this->set('data', $data);
        }
		$frm = $this->getForm($data);
		$frm->fill($data);
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				if($post['coupon_id'] != $coupon_id){
					Message::addErrorMessage('Error: Invalid Request!!');
					Utilities::redirectUser(Utilities::generateUrl('coupons'));
				}else{
					
					
					
						$coupon = $this->Coupons->getCouponByCode($post['coupon_code']);
						if (($coupon==true) && ($coupon["coupon_id"]!=$post["coupon_id"]) ){
							Message::addErrorMessage('Coupon with this code already exists.');
						}else{
							if (Utilities::isUploadedFileValidImage($_FILES['coupon_file'])){
								if(!Utilities::saveImage($_FILES['coupon_file']['tmp_name'],$_FILES['coupon_file']['name'], $saved_image_name, 'coupons/')){
		               				Message::addError($saved_image_name);
			    		   			}
								$post["coupon_file"]=$saved_image_name;
    		    			}
							if($this->Coupons->addUpdate($post)){
								Message::addMessage('Success: Coupon details added/updated successfully.');
								Utilities::redirectUser(Utilities::generateUrl('coupons'));
							}else{
								Message::addErrorMessage($this->Coupons->getError());
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
        $frm = new Form('frmCoupons');
		$frm->setExtra(' validator="CouponsfrmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('CouponsfrmValidator');
        $frm->addHiddenField('', 'coupon_id');
		$frm->addRequiredField('Name', 'coupon_title','', '', ' class="medium"');
		$fld=$frm->addTextArea('Description', 'coupon_description', '', 'coupon_description', 'rows="3" class="medium"');
		$fld->html_after_field = '<small>Please enter complete coupon description along with terms & conditions.</small>';
		$fld=$frm->addRequiredField('Code', 'coupon_code','', '', ' class="small"');
		$fld->requirements()->setLength(6,12);
		$fld = $frm->addFileUpload('Image', 'coupon_file');
		if(!empty($info["coupon_image"])){
        	$fld->html_before_field = '<div id="coupon_image" ><div class="filefield"><span class="filename"></span>';
			$fld->html_after_field = '<label class="filelabel">Browse File</label></div><p><b>Current Image:</b><br /><img src="'.Utilities::generateUrl('image', 'coupon', array('THUMB',$info["coupon_image"]),CONF_WEBROOT_URL).'" /></p></div>';
		}else{
			$fld->html_before_field = '<div class="filefield"><span class="filename"></span>';
			$fld->html_after_field = '<label class="filelabel">Browse File</label></div>';			
		}
		
		$fld=$frm->addRequiredField('Min Order Value ['.CONF_CURRENCY_SYMBOL.']', 'coupon_min_order_value','', '', ' class="small"');
		$fld->requirements()->setFloatPositive($val=true);
		$frm->addSelectBox('Discount Type', 'coupon_discount_type', array('P'=>'Percentage (%)','F'=>'Fixed'),array("P"),' class="small"','');
		$frm->addRequiredField('Discount Value', 'coupon_discount_value','', '', ' class="small"');
		$frm->addRequiredField('Max Discount Value', 'coupon_max_discount_value','', '', ' class="small"');
		
		$fld=$frm->addDateField('Start Date', 'coupon_start_date', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld->requirements()->setRequired();
		$fld=$frm->addDateField('End Date', 'coupon_end_date', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld->requirements()->setRequired();
		//$fld->requirements()->setCompareWith('coupon_start_date','ge','');
		$coupon_categories = '';
		if (isset($info["coupon_categories"])){
			foreach ($info["coupon_categories"] as $coupon_category) { 
					$coupon_categories.='<div id="coupon-category'.$coupon_category['id'].'"><i class="remove_filter remove_param"><img src="'.CONF_WEBROOT_URL.'images/admin/closelabels.png"/></i> '.$coupon_category['name'].'<input type="hidden" name="categories[]" value="'. $coupon_category['id'].'" /></div>';
			}
		}
		$fld = $frm->addTextBox('Categories', 'category');
		$fld->html_after_field = '<small>Choose specific categories the coupon will apply to. Select no category to apply coupon to all categories.</small><br/><div id="coupon-categories" class="well well-sm" style="height: 150px; overflow: auto;">'.$coupon_categories.'</div>';
		
		$coupon_products = '';
		if (isset($info["coupon_products"])){
			foreach ($info["coupon_products"] as $coupon_product) { 
					$coupon_products.='<div id="coupon-products'.$coupon_product['id'].'"><i class="remove_product remove_param"><img src="'.CONF_WEBROOT_URL.'images/admin/closelabels.png"/></i> '.$coupon_product['name'].'<input type="hidden" name="products[]" value="'. $coupon_product['id'].'" /></div>';
			}
		}
		$fld = $frm->addTextBox('Products', 'products');
		$fld->html_after_field = '<small>Choose specific products the coupon will apply to. Select no products to apply coupon to entire cart.</small><br/><div id="coupon-products" class="well well-sm" style="height: 150px; overflow: auto;">'.$coupon_products.'</div>';
		
		$fld=$frm->addTextBox('Uses Per Coupon', 'coupon_uses','1', '', ' class="small"');
		$fld->html_after_field = '<small>Maximum number of times a coupon can be used by any customer.Leave blank for unlimited uses.</small>';
		$fld=$frm->addTextBox('Uses Per Customer', 'coupon_uses_customer','1', '', ' class="small"');
		$fld->html_after_field = '<small>Maximum number of times a coupon can be used by a single customer.Leave blank for unlimited uses.</small>';
		
		//$frm->addCheckBox('Free Shipping', 'coupon_free_shipping', 1);
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
        $coupon = $this->Coupons->getData($coupon_id);
		if($coupon==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		$data_to_update = array('coupon_status'=>!$coupon['coupon_status']);
		if($this->Coupons->updateCouponStatus($coupon_id,$data_to_update)){
			Message::addMessage('Success: Status has been modified successfully.');
			$arr = array('status'=>1, 'msg'=>Message::getHtml(), 'linktext'=>(($coupon['coupon_status'] == 1)?'Active':'Inactive'));
			die(convertToJson($arr));
		}else{
			Message::addErrorMessage($this->Coupons->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	function delete() {
        if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
        $post = Syspage::getPostedVar();
        $coupon_id = intval($post['id']);
        $coupon = $this->Coupons->getData($coupon_id);
		if($coupon==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		if($this->Coupons->delete($coupon_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($this->Coupons->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	/*function delete($coupon_id) {
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),DISCOUNTCOUPONS)) {
             die(Admin::getUnauthorizedMsg());
        }
        if($this->Coupons->delete($coupon_id)){
			Message::addMessage('Success: Record has been deleted.');
		}else{
			Message::addErrorMessage($this->Coupons->getError());
		}
		Utilities::redirectUserReferer();
    }
	
	function status($coupon_id, $mod='block') {
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),DISCOUNTCOUPONS)) {
             die(Admin::getUnauthorizedMsg());
        }	
        $coupon_id = intval($coupon_id);
        $coupon = $this->Coupons->getData($coupon_id);
        if($coupon==false) {
            Message::addErrorMessage('Error: Please perform this action on valid record.');
            Utilities::redirectUserReferer();
        }
        switch($mod) {
            case 'block':
            	$data_to_update = array(
					'coupon_status'=>0,
	            );
            break;
            case 'unblock':
    	        $data_to_update = array(
					'coupon_status'=>1,
            	);
            break;
           
        }
		if($this->Coupons->updateCouponStatus($coupon_id,$data_to_update)){
			Message::addMessage('Success: Status has been modified successfully.');
		}else{
			Message::addErrorMessage($this->Coupons->getError());
		}
        Utilities::redirectUserReferer();
    }*/
}