<?php
class SubscriptionOrderstatusController extends CommonController {
    	
	function default_action($page) {
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),SUBSCRIPTIONORDERSTATUS)) {
             die(Admin::getUnauthorizedMsg());
        }
		$osObj=new SubscriptionOrderstatus();
		$page = intval($page);
		if ($page < 1)
			$page = 1;
		$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
		$criteria=array();
		$criteria['pagesize'] = $pagesize;
		$criteria['page'] = $page;
		$this->set('arr_listing', $osObj->getOrderStatuses($criteria));
		$this->set('pages', $osObj->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page-1)*$pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $osObj->getTotalRecords();
		if ($total_records < $end_record) $end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->set('search_parameter',$get);
        $this->_template->render();
    }
	
    function form($sorder_status_id) {
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),SUBSCRIPTIONORDERSTATUS)) {
             die(Admin::getUnauthorizedMsg());
        }
		$osObj=new SubscriptionOrderstatus();
        $sorder_status_id = intval($sorder_status_id);
        if ($sorder_status_id > 0) {
            $data = $osObj->getData($sorder_status_id);
        }
		$frm = $this->getForm($data);
		$frm->fill($data);
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
					if($post['sorder_status_id'] != $sorder_status_id){
						Message::addErrorMessage('Error: Invalid Request!!');
						Utilities::redirectUser(Utilities::generateUrl('subscriptionorderstatus'));
					}else{
						$post = array_merge(array(
										'sorder_status_subscriber_flag'=>0,
										),$post);
										
						if($osObj->addUpdate($post)){
							Message::addMessage('Success: Order Status added/updated successfully.');
							Utilities::redirectUser(Utilities::generateUrl('subscriptionorderstatus'));
						}else{
							Message::addErrorMessage($osObj->getError());
						}
					}
			}
			$frm->fill($post);
		}
        $this->set('frm', $frm);
        $this->_template->render(true,true);
    }
    protected function getForm($data) {
        $frm = new Form('frmBrands');
		$frm->setExtra(' validator="frmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('frmValidator');
		$frm->setJsErrorDisplay('afterfield');
        $frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="20%"');
		$frm->addHiddenField('', 'sorder_status_id','0');
		$fld=$frm->addRequiredField('Order Status', 'sorder_status_name');
		$fld=$frm->addRequiredField('Order Status Priority', 'sorder_status_priority')->Requirements()->setIntPositive();
		$fld=$frm->addCheckBox('Show in Subscriber Login:', 'sorder_status_subscriber_flag','1','sorder_status_subscriber_flag', 'class="field4"',Settings::getSetting("sorder_status_subscriber_flag"));
		
		$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
        return $frm;
    }
	function delete($sorder_status_id) {
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),SUBSCRIPTIONORDERSTATUS)) {
             die(Admin::getUnauthorizedMsg());
        }
		$osObj=new SubscriptionOrderstatus();
        if($osObj->delete($sorder_status_id)){
			Message::addMessage('Success: Record has been deleted.');
		}else{
			Message::addErrorMessage($osObj->getError());
		}
		
		Utilities::redirectUserReferer();
    }
	
}