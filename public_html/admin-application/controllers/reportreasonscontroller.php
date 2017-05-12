<?php
class ReportreasonsController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,REPORTREASONS);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Report Reasons Management",Utilities::generateUrl('reportreasons'));
    }
	
    function default_action() {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$rrObj=new Reportreasons();
		$this->set('breadcrumb', $this->b_crumb->output());
        $this->set('arr_listing', $rrObj->getAllReportReasons());
        $this->_template->render();
    }
    function form($reportreason_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("Report Reason Setup",'');
		$this->set('breadcrumb', $this->b_crumb->output());
		$rrObj=new Reportreasons();
        $reportreason_id = intval($reportreason_id);
        $frm = $this->getForm($reportreason_id);
        if ($reportreason_id > 0) {
            $data = $rrObj->getData($reportreason_id);
            $frm->fill($data);
        }
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
					if($post['reportreason_id'] != $reportreason_id){
						Message::addErrorMessage('Error: Invalid Request!!');
						Utilities::redirectUser(Utilities::generateUrl('reportreasons'));
					}else{
						if($rrObj->addUpdate($post)){
							Message::addMessage('Success: Report reason details added/updated successfully.');
							Utilities::redirectUser(Utilities::generateUrl('reportreasons'));
						}else{
							Message::addErrorMessage($rrObj->getError());
						}
					}
			}
			$frm->fill($post);
		}
        $this->set('frm', $frm);
        $this->_template->render();
    }
    protected function getForm($id) {
        $frm = new Form('frmReportReason');
		$frm->setExtra(' validator="ReportReasonsfrmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('ReportReasonsfrmValidator');
        $frm->addHiddenField('', 'reportreason_id');
		$frm->addRequiredField('Reason Title', 'reportreason_title','', '', '');
		$frm->addTextArea('Reason Description', 'reportreason_description', '', '', '');
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
		$rrObj=new Reportreasons();
        $reportreason_id = intval($post['id']);
        $reportReson = $rrObj->getData($reportreason_id);
		if($reportReson==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		if($rrObj->delete($reportreason_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($tObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	
}