<?php
class ShippingdurationController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,SHIPPINGDURATION);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Shipping Durations Management",Utilities::generateUrl('shippingduration'));
    }
	
    function default_action() {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->set('breadcrumb', $this->b_crumb->output());
       	$sdObj=new Shippingduration();
        $this->set('arr_listing', $sdObj->getShippingdurations());
        $this->_template->render();
    }
    function form($sduration_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("Shipping Company Setup",'');
		$this->set('breadcrumb', $this->b_crumb->output());
		$sdObj=new Shippingduration();
        $sduration_id = intval($sduration_id);
        $frm = $this->getForm();
        if ($sduration_id > 0) {
            $data = $sdObj->getData($sduration_id);
            $frm->fill($data);
        }
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				if($post['sduration_id'] != $sduration_id){
					Message::addErrorMessage('Error: Invalid Request!!');
					Utilities::redirectUser(Utilities::generateUrl('shippingduration'));
				}else{
					
					if($sdObj->addUpdate($post)){
						Message::addMessage('Success: Shipping duration details added/updated successfully.');
						Utilities::redirectUser(Utilities::generateUrl('shippingduration'));
					}else{
						Message::addErrorMessage($sdObj->getError());
					}
				}
			}
			$frm->fill($post);
		}
        $this->set('frm', $frm);
        $this->_template->render(true,true);
    }
    protected function getForm() {
		global $one_to_ten_array;
        $frm = new Form('frmShippingduration');
		$frm->setExtra(' validator="ShippingdurationfrmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('ShippingdurationfrmValidator');
        $frm->addHiddenField('', 'sduration_id');
		$frm->addRequiredField('Label', 'sduration_label','', '', ' class="medium"');
		$fldFrom=$frm->addSelectBox('From', 'sduration_from',$one_to_ten_array,'' , 'class="auto"','From..');
		$fldFrom->html_after_field='<span class="spacer">&nbsp; -- &nbsp;</span>';
		$fldFrom->requirements()->setRequired(true);
		$fldTo=$frm->addSelectBox('To', 'sduration_to',$one_to_ten_array,'' , 'class="auto"','To..');
		$fldTo->requirements()->setRequired(true);
		$fldTo->html_after_field='<span class="spacer">&nbsp; -- &nbsp;</span>';
		$fldFrom->attachField($fldTo);
		
		$fldDW=$frm->addSelectBox('Y', 'sduration_days_or_weeks',array("D"=>"Business Days","W"=>"Weeks"),'D' , 'class="auto"','');
		$fldTo->attachField($fldDW);
		$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
        //$frm->setAction(Utilities::generateUrl('Shippingduration', 'setup'));
		$frm->setJsErrorDisplay('afterfield');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="15%"');
        return $frm;
    }
    function setup() {
	if (!Admin::getAdminAccess($this->getLoggedAdminId(),SHIPPINGDURATION)) {
             die(Admin::getUnauthorizedMsg());
        }	
    try {
        $post = Syspage::getPostedVar();
        $id = intval($post['sduration_id']);
        $db = &Syspage::getdb();
		$frm = $this->getForm();
		if (!$frm->validate($post)) {
				Message::addErrorMessage($frm->getValidationErrors());
				throw new Exception('');
		}
		$record = new TableRecord('tbl_shipping_durations');
		if ($id > 0){
			$record->assignValues($post);
				if (!$record->update(array('smt'=>'sduration_id = ?', 'vals'=>array($id)))){
					Message::addErrorMessage($record->getError());
					throw new Exception('');
				}
			$this->id = $id;
   		 }else {
			$record->assignValues($post);	
	            if (!$record->addNew()){
	               Message::addErrorMessage($record->getError());
            		throw new Exception('');
        	    }
            	$this->id = $record->getId();
	        }
			Message::addMessage('Shipping duration added/updated successfully.');
	    } catch (Exception $e) {
        Utilities::redirectUserReferer();
   	  }
	  Utilities::redirectUser(Utilities::generateUrl('shippingduration'));
    }
	
	function delete() {
        if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
        $post = Syspage::getPostedVar();
		$sdObj=new Shippingduration();
        $shippingduration_id = intval($post['id']);
        $shippingDuration = $sdObj->getData($shippingduration_id);
		if($shippingDuration==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		if($sdObj->delete($shippingduration_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($sdObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
    
	
	function autocomplete(){
	    $post = Syspage::getPostedVar();
		$json = array();
		$sdObj=new Shippingduration();
        $shipping_durations=$sdObj->getShippingdurations(array("keyword"=>urldecode($post["keyword"]),"pagesize"=>10));
		foreach($shipping_durations as $skey=>$sval){
			$json[] = array(
					'id' => $sval['sduration_id'],
					'name'      => strip_tags(htmlentities($sval['sduration_label'], ENT_QUOTES, 'UTF-8'))
				);
		}
		$sort_order = array();
		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}
		array_multisort($sort_order, SORT_ASC, $json);
		echo json_encode($json);
	}
	
}