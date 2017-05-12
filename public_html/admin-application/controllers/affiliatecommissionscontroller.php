<?php
class AffiliatecommissionsController extends CommonController {
	
	function default_action() {
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),AFFILIATE_COMMISSION_SETTINGS)) {
             die(Admin::getUnauthorizedMsg());
        }
		$afcmObj=new Affiliatecommissions();
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if($afcmObj->addUpdateCommissionSettings($post)){
				Message::addMessage('Success: Affiliate commission settings updated successfully.');
				Utilities::redirectUser(Utilities::generateUrl('affiliatecommissions'));
			}else{
				Message::addErrorMessage($afcmObj->getError());
			}
		}
		$commission_settings=$afcmObj->getCommissionSettings(0);
		$this->set('commission_settings',$commission_settings);
		$frm = new Form('frmCommissions','frmCommissions');
		$frm->setExtra('class="web_form" autocomplete="off"');
        $frm->setValidatorJsObjectName('CommissionsfrmValidator');
		$frm->addHiddenField('Dummy', 'dummy');
		$frm->setJsErrorDisplay('afterfield');
		$this->set('frm', $frm);
        $this->_template->render();
    }
	
	
	function trashed() {
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),AFFILIATE_COMMISSION_SETTINGS)) {
             die(Admin::getUnauthorizedMsg());
        }
		$afcmObj=new Affiliatecommissions();
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if($afcmObj->addUpdateCommissionSettings($post)){
				Message::addMessage('Success: Affiliate commission settings updated successfully.');
				Utilities::redirectUser(Utilities::generateUrl('affiliatecommissions'));
			}else{
				Message::addErrorMessage($afcmObj->getError());
			}
		}
		$commission_settings=$afcmObj->getCommissionSettings(1);
		$this->set('commission_settings',$commission_settings);
        $this->_template->render();
    }
	
	function remove(){
		$db = &Syspage::getdb();
		$post = Syspage::getPostedVar();
		$id = $post["id"];
		$rs = $db->update_from_array('tbl_affiliate_commission_settings', array('afcommsetting_is_deleted' => 1), array('smt' => 'afcommsetting_id = ? AND afcommsetting_is_mandatory = ?', 'vals' => array($id,0)));
	    if (!$rs)
            dieJsonError($db->getError());
		else
			dieJsonSuccess('Success');
	}
	
    
}