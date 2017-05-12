<?php
class CommissionsController extends CommonController {
	
	function default_action() {
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),COMMISSIONSETTINGS)) {
             die(Admin::getUnauthorizedMsg());
        }
		$cmObj=new Commissions();
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			//die('TT');
			if($cmObj->addUpdateCommissionSettings($post)){
				Message::addMessage('Success: Commission Settings updated successfully.');
				Utilities::redirectUser(Utilities::generateUrl('commissions'));
			}else{
				Message::addErrorMessage($cmObj->getError());
			}
		}
		$commission_settings=$cmObj->getCommissionSettings(0);
		$this->set('commission_settings',$commission_settings);
		$frm = new Form('frmCommissions','frmCommissions');
		$frm->setExtra('class="web_form" autocomplete="off"');
        $frm->setValidatorJsObjectName('CommissionsfrmValidator');
		$frm->addHiddenField('Dummy', 'dummy');
		$frm->setJsErrorDisplay('afterfield');
		$this->set('frm', $frm);
        $this->_template->render();
    }
	
	function how_it_works() {
        $this->_template->render(false,false);
    }
	
	function trashed() {
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),COMMISSIONSETTINGS)) {
             die(Admin::getUnauthorizedMsg());
        }
		$cmObj=new Commissions();
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if($cmObj->addUpdateCommissionSettings($post)){
				Message::addMessage('Success: Commission Settings updated successfully.');
				Utilities::redirectUser(Utilities::generateUrl('commissions'));
			}else{
				Message::addErrorMessage($cmObj->getError());
			}
		}
		$commission_settings=$cmObj->getCommissionSettings(1);
		$this->set('commission_settings',$commission_settings);
        $this->_template->render();
    }
	
	function remove(){
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),COMMISSIONSETTINGS)) {
             die(Admin::getUnauthorizedMsg());
        }
		$db = &Syspage::getdb();
		$post = Syspage::getPostedVar();
		$id = $post["id"];
		$rs = $db->update_from_array('tbl_commission_settings', array('commsetting_is_deleted' => 1), array('smt' => 'commsetting_id = ? AND commsetting_is_mandatory = ?', 'vals' => array($id,0)));
	    if (!$rs)
            dieJsonError($db->getError());
		else
			dieJsonSuccess('Success');
	}
	
    
}