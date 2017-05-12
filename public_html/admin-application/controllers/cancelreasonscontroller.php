<?php
class CancelreasonsController extends BackendController {
   	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,CANCELREASONS);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Cancel Reasons Management",Utilities::generateUrl('cancelreasons'));
    }
	
    function default_action() {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$crObj=new Cancelreasons();
        $this->set('arr_listing', $crObj->getAllCancelReasons());
		$this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
    function form($cancelreason_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("Cancel Reason Setup",'');
		$this->set('breadcrumb', $this->b_crumb->output());
		$crObj=new Cancelreasons();
        $cancelreason_id = intval($cancelreason_id);
        $frm = $this->getForm($cancelreason_id);
        if ($cancelreason_id > 0) {
            $data = $crObj->getData($cancelreason_id);
            $frm->fill($data);
        }
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
					if($post['cancelreason_id'] != $cancelreason_id){
						Message::addErrorMessage('Error: Invalid Request!!');
						Utilities::redirectUser(Utilities::generateUrl('cancelreasons'));
					}else{
						if($crObj->addUpdate($post)){
							Message::addMessage('Success: Cancel reason details added/updated successfully.');
							Utilities::redirectUser(Utilities::generateUrl('cancelreasons'));
						}else{
							Message::addErrorMessage($crObj->getError());
						}
					}
			}
			$frm->fill($post);
		}
        $this->set('frm', $frm);
        $this->_template->render(true,true);
    }
    protected function getForm($id) {
        $frm = new Form('frmCancelReasons','frmCancelReasons');
		$frm->setExtra(' validator="CancelReasonsfrmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('CancelReasonsfrmValidator');
        $frm->addHiddenField('', 'cancelreason_id');
		$frm->addRequiredField('Reason Title', 'cancelreason_title','', '', '');
		$frm->addTextArea('Reason Description', 'cancelreason_description', '', '', '');
		$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="20%"');
        return $frm;
    }
	function delete() {
        if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
        $post = Syspage::getPostedVar();
		$crObj=new Cancelreasons();
        $cancelreason_id = intval($post['id']);
        $cancelReson = $crObj->getData($cancelreason_id);
		if($cancelReson==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		if($crObj->delete($cancelreason_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($crObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	/* function delete($cancelreason_id) {
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),CANCELREASONS)) {
             die(Admin::getUnauthorizedMsg());
        }
		$crObj=new Cancelreasons();
        if($crObj->delete($cancelreason_id)){
			Message::addMessage('Success: Record has been deleted.');
		}else{
			Message::addErrorMessage($crObj->getError());
		}
		
		Utilities::redirectUserReferer();
    }*/
    
}