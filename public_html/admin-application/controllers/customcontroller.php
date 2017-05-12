<?php
class CustomController extends BackendController {
	
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canviewwithdrawalrequests = Admin::getAdminAccess($admin_id,WITHDRAWAL_REQUESTS);
        $this->set('canviewwithdrawalrequests', $this->canviewwithdrawalrequests);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Withdrawal Requests Management", Utilities::generateUrl("custom","withdrawal_requests"));
    }
	
	
	protected function getSearchForm() {
		global $status_arr;
        $frm=new Form('frmWithdrawalRequestSearch','frmWithdrawalRequestSearch');
		$frm->setFieldsPerRow(3);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('GET');		
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$fld=$frm->addTextBox('Keyword', 'keyword','','',' class="medium" placeholder="Name, Username"');
		//$fld->html_after_field='<small>Name, Username</small>';
		$frm->addTextBox('From ['.CONF_CURRENCY_SYMBOL.']', 'minprice','','',' class="small"')->requirements()->setFloatPositive($val=true);
		$frm->addTextBox('To ['.CONF_CURRENCY_SYMBOL.']', 'maxprice','','',' class="small"')->requirements()->setFloatPositive($val=true);
		$frm->addSelectBox('Status', 'status',$status_arr,'' , 'class="small"','All');
		$fld=$frm->addDateField('Date From', 'date_from', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld=$frm->addDateField('Date To', 'date_to', '', '', 'readonly="true" class="small dateTimeFld"');
		
		
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchWithdrawalRequests(this); return false;');
        return $frm;
    }
	
	
    function withdrawal_requests() {
        if ($this->canviewwithdrawalrequests != true) {
            $this->notAuthorized();
        }
        $frm = $this->getSearchForm();
		$frm->fill(getQueryStringData());
        $this->set('frmPost', $frm);
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listWithdrawalRequests($page = 1) {
        if ($this->canviewwithdrawalrequests != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
			$wrObj=new WithdrawalRequests();
            $post = Syspage::getPostedVar();
            $page = 1;
            if (isset($post['page']) && intval($post['page']) > 0) {
                $page = intval($post['page']);
            } else {
                $post['page'] = $page;
            }
            if (!empty($post)) {
                $this->set('srch', $post);
            }
           	$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
            $post['pagesize'] = $pagesize;
            $this->set('records', $wrObj->getWithdrawRequests($post));
            $this->set('pages', $wrObj->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $wrObj->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
	function update_withdrawal_request_status(){
		if ($this->canviewwithdrawalrequests != true) {
            $this->notAuthorized();
        }
		$post = Syspage::getPostedVar();
		$mod = $post['mod'];
        $id = intval($post['id']);
        $withdrawalRequestObj=new WithdrawalRequests();
		$request=$withdrawalRequestObj->getWithdrawRequestData($id);
        if($request==false) {
            Message::addErrorMessage('Please perform this action on valid request.');
            dieJsonError(Message::getHtml());
        }
		
        switch($mod) {
            case 'approve':
            	$data_to_update = array(
					'withdrawal_status'=>3,
	            );
				Message::addMessage('Withdrawal request has been approved successfully.');
            break;
           /* case 'decline':
    	        $data_to_update = array(
					'withdrawal_status'=>2,
            	);
				Message::addMessage('Withdrawal request has been declined successfully.');
            break;*/
           
        }
		$db = &Syspage::getdb();
		if($withdrawalRequestObj->updateWithdrawalRequestStatus($id,$data_to_update)){
			$emailNotificationObj=new Emailnotifications();
			$emailNotificationObj->SendWithdrawRequestNotification($id,"U");
			$rs = $db->update_from_array('tbl_user_transactions',array("utxn_status"=>1), array('smt'=>'utxn_withdrawal_id=?','vals'=>array($id)));	
		
			$arr = array('status'=>1, 'msg'=>Message::getHtml());
			die(convertToJson($arr));
		}else{
			Message::addErrorMessage($withdrawalRequestObj->getError());
			dieJsonError(Message::getHtml());
		}
	}
	
	
	protected function getWithdrawalRequestForm(){
		$frm=new Form('withdrawalRequestForm','withdrawalRequestForm');
		$frm->setValidatorJsObjectName('withdrawalRequestFormValidator');
		$frm->setExtra('class="web_form"');
		$frm->setLeftColumnProperties('valign="top" align="left"');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="tblBorderTop small"');
		$frm->captionInSameCell(true);
		$frm->setFieldsPerRow(1);
		$frm->setJsErrorDisplay('afterfield');
		$fld=$frm->addTextArea('Comments', 'comments', '', 'comments');
		$fld->requirements()->setRequired(true);
		$fld->requirements()->setCustomErrorMessage('Please enter your reason for cancellation');
		$frm->addSubmitButton('&nbsp;', 'btn_submit', 'Update');
		return $frm;
	}
	
	
	function cancel_withdrawal_request($request_id) {
		$db = &Syspage::getdb();
		if ($this->canviewwithdrawalrequests != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("Cancel Withdrawal Request", '');
		$this->set('breadcrumb', $this->b_crumb->output());
		
		$withdrawalRequestObj=new WithdrawalRequests();
		$withdrawal_request=$withdrawalRequestObj->getWithdrawRequestData($request_id,array('status'=>0));
		if (!$withdrawal_request){
			Message::addErrorMessage('Error: Invalid Request!!');
			Utilities::redirectUser(Utilities::generateUrl('custom','withdrawal_requests'));
		}
		$this->set('withdrawal_request', $withdrawal_request);
		$frm=$this->getWithdrawalRequestForm();
		$this->set('frm',$frm);
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
					$data_to_update = array(
										'withdrawal_status'=>2,
										'withdrawal_cancel_comments'=>$post['comments'],
	    			        		);
						if($withdrawalRequestObj->updateWithdrawalRequestStatus($request_id,$data_to_update)){
						$emailNotificationObj=new Emailnotifications();
						$emailNotificationObj->SendWithdrawRequestNotification($request_id,"U");
						$rs = $db->update_from_array('tbl_user_transactions',array("utxn_status"=>1), array('smt'=>'utxn_withdrawal_id=?','vals'=>array($request_id)));	
						
						$transObj=new Transactions();
						$txn_detail=$transObj->getTransactionById('',array('withdrawal_request'=>$request_id));
						$formatted_request_value="#".str_pad($request_id,6,'0',STR_PAD_LEFT);
						$txnArray["utxn_user_id"]=$txn_detail["utxn_user_id"];
						$txnArray["utxn_credit"]=$txn_detail["utxn_debit"];
						$txnArray["utxn_status"]=1;
						$txnArray["utxn_withdrawal_id"]=$txn_detail["utxn_withdrawal_id"];
						$txnArray["utxn_comments"]=sprintf(Utilities::getLabel('M_Withdrawal_Request_Declined_Amount_Refunded'),$formatted_request_value);
						
						if($txn_id=$transObj->addTransaction($txnArray)){
								$emailNotificationObj=new Emailnotifications();
								$emailNotificationObj->sendTxnNotification($txn_id);
							}
							Message::addMessage('Withdrawal request has been declined successfully.');
						}else{
						Message::addErrorMessage($withdrawalRequestObj->getError());
					
				}
				Utilities::redirectUser(Utilities::generateUrl('custom','withdrawal_requests'));
			}
			$frm->fill($post);
		}
        $this->_template->render();
    }
	
	function download_attachment($id) {
			$request_message=Products::getReturnRequestMessage($id);
			if ($request_message){
				Utilities::outputFile($request_message["retmsg_attachment"],false,false,'',false);
			}else{
				Message::addErrorMessage(Utilities::getLabel('M_ERROR_NOT_AUTHORIZED'));
				Utilities::redirectUser(Utilities::generateUrl('custom', 'return_requests'));
			}
    }
	
	
	function test_smtp_email(){
		$settingsObj=new Settings();
		if($settingsObj->send_smtp_test_email($smtp_arr))
			echo ("Mail sent to - ".Settings::getSetting("CONF_ADMIN_EMAIL"));
		else	
			echo ($settingsObj->getError());
	}
	
	function test_email(){
		$settingsObj=new Settings();
		if($settingsObj->send_test_email())
			echo ("Mail sent to - ".Settings::getSetting("CONF_ADMIN_EMAIL"));
		else	
			echo ($settingsObj->getError());
	}

	
	
	
}