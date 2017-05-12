<?php
class EmailtemplatesController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,EMAIL_TEMPLATES);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Email Templates Management", Utilities::generateUrl("emailtemplates"));
    }
	
	function default_action() {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->set('breadcrumb', $this->b_crumb->output());
        $this->set('arr_listing', $this->Emailtemplates->getAllEmailTemplates());
        $this->_template->render();
    }
    
	function form($tpl_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("Payment Method Setup", '');
		$this->set('breadcrumb', $this->b_crumb->output());
        $tpl_id = intval($tpl_id);
        $frm = $this->getForm();
        if ($tpl_id > 0) {
            $data = $this->Emailtemplates->getData($tpl_id);
            $frm->fill($data);
        }
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
					if($post['tpl_id'] != $tpl_id){
						Message::addErrorMessage('Error: Invalid Request!!');
						Utilities::redirectUser(Utilities::generateUrl('emailtemplates'));
					}else{
						if($this->Emailtemplates->addUpdate($post)){
							Message::addMessage('Success: Email template details added/updated successfully.');
							Utilities::redirectUser(Utilities::generateUrl('emailtemplates'));
						}else{
							Message::addErrorMessage($this->Emailtemplates->getError());
						}
					}
			}
			$frm->fill($post);
		}
        $this->set('frm', $frm);
        $this->_template->render(true,true);
    }
    protected function getForm() {
        $frm = new Form('frmEmailTpl');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="20%"');
		$frm->setExtra('class="web_form"  validator="tplValidator"');
		$frm->setValidatorJsObjectName('tplValidator');
        $frm->addHiddenField('', 'tpl_id',0);
        $frm->addRequiredField('Template Name', 'tpl_name','','','class="input-xlarge"');
        $frm->addRequiredField('Subject', 'tpl_subject','','','class="input-xlarge"');
		$frm->addHtml('','','Contents');
		$fld=$frm->addHtmlEditor('', 'tpl_body', '', 'tpl_body', ' rows="3"');
        $fld->requirements()->setRequired(true);
        $frm->addHtml('Template Vars', 'tpl_replacements');
		//$frm->addTextArea('Template Vars', 'tpl_replacements');
		$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
	    $frm->setJsErrorDisplay('afterfield');
        return $frm;
    }
    
	function update_status() {
		if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
		$post = Syspage::getPostedVar();
		$tpl_id = intval($post['id']);
        $email_template = $this->Emailtemplates->getData($tpl_id);
		if($email_template==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
        $data_to_update = array('tpl_status'=>!$email_template['tpl_status']);
		if($this->Emailtemplates->updateEmailTemplateStatus($tpl_id,$data_to_update)){
			Message::addMessage('Success: Status has been modified successfully.');
			$arr = array('status'=>1, 'msg'=>Message::getHtml(), 'linktext'=>(($email_template['tpl_status'] == 1)?'Active':'Inactive'));
			die(convertToJson($arr));
		}else{
			Message::addErrorMessage($this->Emailtemplates->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
  
	
}