<?php
class BanksController extends CommonController {
    
	function default_action($page) {
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),BANKS)) {
             die(Admin::getUnauthorizedMsg());
        }
		$bankObj=new Banks();
		$page = intval($page);
		if ($page < 1)
			$page = 1;
		$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
		$criteria=array();
		$criteria['pagesize'] = $pagesize;
		$criteria['page'] = $page;
		$this->set('arr_listing', $bankObj->getBanks($criteria));
		$this->set('pages', $bankObj->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page-1)*$pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $bankObj->getTotalRecords();
		if ($total_records < $end_record) $end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->set('search_parameter',$get);
        $this->_template->render();
    }
	
	
    function form($bank_id) {
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),BANKS)) {
             die(Admin::getUnauthorizedMsg());
        }
		$bankObj=new Banks();
        $bank_id = intval($bank_id);
        $frm = $this->getForm($bank_id);
        if ($bank_id > 0) {
            $data = $bankObj->getData($bank_id);
            $frm->fill($data);
        }
		$frm = $this->getForm($data);
		$frm->fill($data);
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
					if($post['bank_id'] != $bank_id){
						Message::addErrorMessage('Error: Invalid Request!!');
						Utilities::redirectUser(Utilities::generateUrl('banks'));
					}else{
						if($bankObj->addUpdate($post)){
							Message::addMessage('Success: Bank details added/updated successfully.');
							Utilities::redirectUser(Utilities::generateUrl('banks'));
						}else{
							Message::addErrorMessage($bankObj->getError());
						}
					}
			}
			$frm->fill($post);
		}
        $this->set('frm', $frm);
        $this->_template->render(true,true);
    }
    protected function getForm($id) {
        $frm = new Form('frmBanks');
		$frm->setExtra(' validator="BanksfrmValidator" class="form-horizontal"');
        $frm->setValidatorJsObjectName('BanksfrmValidator');
        $frm->addHiddenField('', 'bank_id');
		$frm->addRequiredField('Bank Title', 'bank_name','', '', 'class="medium"');
		$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="formTable"');
		$frm->setLeftColumnProperties('width="20%"');
        return $frm;
    }
	
	function delete($bank_id) {
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),BANKS)) {
             die(Admin::getUnauthorizedMsg());
        }
		$bankObj=new Banks();
        if($bankObj->delete($bank_id)){
			Message::addMessage('Success: Record has been deleted.');
		}else{
			Message::addErrorMessage($bankObj->getError());
		}
		
		Utilities::redirectUserReferer();
    }
    
}