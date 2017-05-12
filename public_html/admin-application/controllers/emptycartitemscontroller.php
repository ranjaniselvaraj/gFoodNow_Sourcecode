<?php
class EmptyCartItemsController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,EMPTYCARTITEMS);
    }
	function default_action() {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
        $this->set('arr_listing', $this->Emptycartitems->getAllEmptyCartItems());
        $this->_template->render();
    }
    
    function form($emptycartitem_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		if ($emptycartitem_id>0)
        	$banner = $this->Emptycartitems->getData($emptycartitem_id);
			
        $frm = $this->getForm($emptycartitem_id,$banner);
		
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if($_FILES['emptycartitem_file']['error'] === 0){
			  $post['emptycartitem_file']= $_FILES['emptycartitem_file']['name'];
			}
					
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				if($post['emptycartitem_id'] != $emptycartitem_id){
					Message::addErrorMessage('Error: Invalid Request!!');
					Utilities::redirectUser(Utilities::generateUrl('emptycartitems'));
				}else{
					
					if($this->Emptycartitems->addUpdate($post)){
						Message::addMessage('Success: Empty Cart Section added/updated successfully.');
						Utilities::redirectUser(Utilities::generateUrl('emptycartitems'));
					}else{
						Message::addErrorMessage($this->Emptycartitems->getError());
					}
				}
			}
			$frm->fill($post);
		}
		
        $this->set('frm', $frm);
        $this->_template->render();
    }
    
	
    protected function getForm($emptycartitem_id,$data=array()) {
        $frm = new Form('frmEmptyCartItem','frmEmptyCartItem');
		$frm->captionInSameCell(false);
        $frm->addHiddenField('', 'emptycartitem_id', $emptycartitem_id, 'emptycartitem_id');
        $frm->addRequiredField('Empty Cart Section Title', 'emptycartitem_title', '', '', ' class="medium"');
       	$fld=$frm->addRequiredField('Empty Cart Section URL', 'emptycartitem_url', '','','class="medium"');
		$fld->html_after_field='<small>Please use {SITEROOT} in URL to define path with reference to your domain name.</small>';
        $frm->addSelectBox('Open Link in New Tab', 'emptycartitem_link_newtab', array(0=>'No', 1=>'Yes'), '0','class="medium"');
		$frm->addTextBox('Priority', 'emptycartitem_priority', '','','class="medium"')->requirements()->setIntPositive();
        $frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
        $frm->setExtra(' validator="emptycartitemfrmValidator" class="web_form" rel="upload"');
        $frm->setValidatorJsObjectName('bannerfrmValidator');
		$frm->setJsErrorDisplay('afterfield');
        $frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="20%"');
        $frm->fill($data);
        return $frm;
    }
	
	
	function delete() {
        if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
        $post = Syspage::getPostedVar();
        $slide_id = intval($post['id']);
        $emptycartitem = $this->Emptycartitems->getData($slide_id);
		if($emptycartitem==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		if($emptycartitem['emptycartitem_is_mandatory']) {
            Message::addErrorMessage('Error: You are not allowed to delete this record, this is pre-configured record, please feel free to disable if not required.');
			dieJsonError(Message::getHtml());
		}
		if($this->Emptycartitems->delete($slide_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($this->Emptycartitems->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	function update_status() {
		if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
		$post = Syspage::getPostedVar();
		$emptycartitem_id = intval($post['id']);
        $emptycartitem = $this->Emptycartitems->getData($emptycartitem_id);
		if($emptycartitem==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
        $data_to_update = array('emptycartitem_status'=>!$emptycartitem['emptycartitem_status']);
		if($this->Emptycartitems->updateStatus($emptycartitem_id,$data_to_update)){
			Message::addMessage('Success: Status has been modified successfully.');
			$arr = array('status'=>1, 'msg'=>Message::getHtml(), 'linktext'=>(($emptycartitem['emptycartitem_status'] == 1)?'Active':'Inactive'));
			die(convertToJson($arr));
		}else{
			Message::addErrorMessage($this->Emptycartitems->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
}
