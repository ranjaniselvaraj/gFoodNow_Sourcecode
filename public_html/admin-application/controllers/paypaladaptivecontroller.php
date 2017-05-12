<?php
class PaypaladaptiveController extends CommonController {
    
	function default_action() {
		Utilities::redirectUser(Utilities::generateUrl('paypaladaptive','payments'));
	}
	
	protected function getSearchForm() {
		global $pp_adaptive_chained_payment_status;
        $frm=new Form('frmSearchPaypaladaptive','frmSearchPaypaladaptive');
		$frm->setFieldsPerRow(2);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('GET');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$fld=$frm->addTextBox('Keyword', 'keyword','','',' class="medium"');
		$frm->addSelectBox('Payment Status', 'status', $pp_adaptive_chained_payment_status, '', 'class="small"', 'Select');
		$fld=$frm->addDateField('Execution Date From', 'date_from', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld=$frm->addDateField('Execution Date To', 'date_to', '', '', 'readonly="true" class="small dateTimeFld"');
		$frm->addSubmitButton('','btn_submit','Search');
		$frm->getField("btn_submit")->html_after_field='&nbsp;&nbsp;<a href="'.Utilities::generateUrl('paypaladaptive').'" class="clear_btn">Clear Search</a>';
		$frm->setJsErrorDisplay('afterfield');
        $frm->setAction(Utilities::generateUrl('paypaladaptive','payments'));
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
        return $frm;
    }
	
    function payments($page=1) {
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),PAYPAL_ADAPTIVE_PAYMENTS)) {
             die(Admin::getUnauthorizedMsg());
        }
		$paObj=new Paypaladaptive_pay();
		$page = intval($page);
		if ($page < 1)
			$page = 1;
		$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
		$criteria=array();
		$criteria['pagesize'] = $pagesize;
		$criteria['page'] = $page;
		$frm=$this->getSearchForm();
		$get = (array) Utilities::getUrlQuery();
		if(isset($get['mode'])) {
			$frm->fill($get);
			$criteria=array_merge($criteria,$get);
		}
		$this->set('search_form', $frm);
		$this->set('arr_listing', $paObj->getChainedPayments($criteria));
		$this->set('pages', $paObj->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page-1)*$pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $paObj->getTotalRecords();
		if ($total_records < $end_record) $end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->set('search_parameter',$get);
        $this->_template->render();
    }
	
	function status($ppa_id, $mod='onhold') {
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),PAYPAL_ADAPTIVE_PAYMENTS)) {
             die(Admin::getUnauthorizedMsg());
        }
		$paObj=new Paypaladaptive_pay();	
        $ppa_id = intval($ppa_id);
        $ppatxn = $paObj->getData($ppa_id);
        if($ppatxn==false) {
            Message::addErrorMessage('Error: Please perform this action on valid record.');
            Utilities::redirectUserReferer();
        }
        switch($mod) {
            case 'hold':
            	$data_to_update = array(
					'ppadappay_status'=>-1,
	            );
            break;
            case 'unhold':
    	        $data_to_update = array(
					'ppadappay_status'=>0,
            	);
            break;
           
        }
		if($paObj->updatePPATxnStatus($ppa_id,$data_to_update)){
			Message::addMessage('Success: Status has been modified successfully.');
		}else{
			Message::addErrorMessage($paObj->getError());
		}
        Utilities::redirectUserReferer();
    }
}