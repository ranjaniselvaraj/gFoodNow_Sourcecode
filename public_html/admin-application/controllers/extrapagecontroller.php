<?php
class ExtrapageController extends CommonController {
	
    function default_action($page=1) {
		$extraPageObj=new Extrapage();
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),EXTRACONTENTPAGE)) {
             die(Admin::getUnauthorizedMsg());
        }
		$this->set('arr_listing', $extraPageObj->getExtraCmsPages());
        $this->_template->render();
    }
	
    function form($epage_id) {
		$extraPageObj=new Extrapage();
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),EXTRACONTENTPAGE)) {
             die(Admin::getUnauthorizedMsg());
        }
        $epage_id = intval($epage_id);
        $frm = $this->getForm();
        if ($epage_id > 0) {
            $data = $extraPageObj->getData($epage_id);
            $frm->fill($data);
        }
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
					if($post['epage_id'] != $epage_id){
						Message::addErrorMessage('Error: Invalid Request!!');
						Utilities::redirectUser(Utilities::generateUrl('extrapage'));
					}else{
						if($extraPageObj->addUpdateExtraPage($post)){
							Message::addMessage('Success: Content Block added/updated successfully.');
							Utilities::redirectUser(Utilities::generateUrl('extrapage'));
						}else{
							Message::addErrorMessage($extraPageObj->getError());
						}
					}
			}
			$frm->fill($post);
		}
        $this->set('frm', $frm);
        $this->_template->render(true,true);
	}
    protected function getForm() {
        $frm = new Form('frmExtraPage','frmExtraPage');
		$frm->setExtra(' validator="extrapagefrmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('extrapagefrmValidator');
        $frm->addHiddenField('', 'epage_id');
		$frm->addRequiredField('Title', 'epage_label','', '', ' class="input-xlarge"');			
		$frm->addHtml('Content<span class="spn_must_field">*</span>');	
		$fld=$frm->addHtmlEditor('', 'epage_content', '', 'epage_content', ' class="cleditor" rows="3"');
        $fld->requirements()->setRequired(true);
		$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="20%"');
        return $frm;
    }
	
	/*function delete($epage_id) {
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),EXTRACONTENTPAGE)) {
             die(Admin::getUnauthorizedMsg());
        }
		$extraPageObj=new Extrapage();
        if($extraPageObj->delete($epage_id)){
			Message::addMessage('Success: Record has been deleted.');
		}else{
			Message::addErrorMessage($extraPageObj->getError());
		}
		
		Utilities::redirectUserReferer();
    }*/
}