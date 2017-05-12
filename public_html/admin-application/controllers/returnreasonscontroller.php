<?php
class ReturnreasonsController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,RETURNREASONS);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Return Reasons Management",Utilities::generateUrl('returnreasons'));
    }
	
    function default_action() {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$rrObj=new Returnreasons();
        $this->set('arr_listing', $rrObj->getAllReturnReasons());
		$this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
    function form($returnreason_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("Return Reason Setup",'');
		$this->set('breadcrumb', $this->b_crumb->output());
		$rrObj=new Returnreasons();
        $returnreason_id = intval($returnreason_id);
        $frm = $this->getForm($returnreason_id);
        if ($returnreason_id > 0) {
            $data = $rrObj->getData($returnreason_id);
            $frm->fill($data);
        }
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
					if($post['returnreason_id'] != $returnreason_id){
						Message::addErrorMessage('Error: Invalid Request!!');
						Utilities::redirectUser(Utilities::generateUrl('returnreasons'));
					}else{
						if($rrObj->addUpdate($post)){
							Message::addMessage('Success: Return reason details added/updated successfully.');
							Utilities::redirectUser(Utilities::generateUrl('returnreasons'));
						}else{
							Message::addErrorMessage($rrObj->getError());
						}
					}
			}
			$frm->fill($post);
		}
        $this->set('frm', $frm);
        $this->_template->render(true,true);
    }
    protected function getForm($id) {
        $frm = new Form('frmReturnReasons','frmReturnReasons');
		$frm->setExtra(' validator="ReturnReasonsfrmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('ReturnReasonsfrmValidator');
        $frm->addHiddenField('', 'returnreason_id');
		$frm->addRequiredField('Reason Title', 'returnreason_title','', '', '');
		$frm->addTextArea('Reason Description', 'returnreason_description', '', '', '');
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
		$rrObj=new Returnreasons();
        $returnreason_id = intval($post['id']);
        $returnReason = $rrObj->getData($returnreason_id);
		if($returnReason==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		if($rrObj->delete($returnreason_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($rrObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
}