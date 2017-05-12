<?php
class AffiliatesController extends BackendController {
	
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,AFFILIATES);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Affiliates Management", Utilities::generateUrl("affiliates"));
    }
	
	protected function getSearchForm() {
		global $user_status,$binary_status;
        $frm=new Form('frmSearchAffiliates','frmSearchAffiliates');
		$frm->setFieldsPerRow(4);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('GET');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addTextBox('Keyword', 'keyword','','',' class="small"');
		$frm->addSelectBox('Status', 'status',$user_status,'' , 'class="small"','All');
		$frm->addSelectBox('Approved', 'approved',$binary_status,'' , 'class="small"','All');
		$fld=$frm->addDateField('Date From', 'date_from', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld=$frm->addDateField('Date To', 'date_to', '', '', 'readonly="true" class="small dateTimeFld"');
		$frm->addTextBox('Balance From ['.CONF_CURRENCY_SYMBOL.']', 'minbalance','','',' class="small"')->requirements()->setFloatPositive($val=true);
		$frm->addTextBox('Balance To ['.CONF_CURRENCY_SYMBOL.']', 'maxbalance','','',' class="small"')->requirements()->setFloatPositive($val=true);
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchAffiliates(this); return false;');
        return $frm;
    }
	
	function default_action() {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $frm = $this->getSearchForm();
		$frm->fill(getQueryStringData());
        $this->set('frmPost', $frm);
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listAffiliates($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
			$aObj=new Affiliate();
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
            $this->set('records', $aObj->getAffiliates($post));
            $this->set('pages', $aObj->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $aObj->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
	
	protected function changePasswordForm(){
		$frm=new Form('frmCustomers','frmStrengthPassword');
		$frm->setAction(Utilities::generateUrl('affiliates','changepassword'));
		$frm->setExtra('class="web_form" validator="tplValidator"');
		$frm->setRequiredStarWith('caption');
		$frm->setValidatorJsObjectName('tplValidator');
		$affObj=new Affiliate();
		$frm->addSelectBox('User', 'affiliate_id',$affObj->getAssociativeArray(),'', 'class="field8" ')->requirements()->setRequired();
		$fld=$frm->addPasswordField('New Password', 'affiliate_password', '', 'check-password', ' class="field8"');
		$fld->requirements()->setRequired(true);
		$fld->requirements()->setLength(4,20);
		$fld->html_before_field='<div id="check-password-result">';
		$fld->html_after_field='</div>';
		$fld=$frm->addPasswordField('Confirm Password', 'affiliate_conpassword', '', '', ' class="field8"');
		$fld->requirements()->setRequired();
		$fld->requirements()->setCompareWith('affiliate_password','eq','');
		$frm->setJsErrorDisplay('afterfield');
		$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="15%"');
		return $frm;
	}
	
	
	protected function sendEmailForm(){
		$frm=new Form('frmCustomers');
		$frm->setAction(Utilities::generateUrl('affiliates','sendemail'));
		$frm->setExtra('class="web_form" validator="tplValidator"');
		$frm->setValidatorJsObjectName('tplValidator');
		$frm->setRequiredStarWith('caption');
		$affObj=new Affiliate();
		$frm->addSelectBox('User', 'affiliate_id',$affObj->getAssociativeArray(),'', 'class="input-xlarge" ')->requirements()->setRequired();
		$frm->addRequiredField('Mail Subject', 'mail_subject', '', '', ' class="field8"');
		$fld=$frm->addTextArea('Message', 'mail_body', '', 'mail_body', ' class="cleditor" rows="3"');
		$fld->requirements()->setRequired();
		
		$frm->setJsErrorDisplay('afterfield');
		$frm->addSubmitButton('&nbsp;','btn_submit','Send Email');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="15%"');
		return $frm;
	}
	function addUserForm($affiliate_id) {
			global $user_status;
			$frm=new Form('frmCustomers','frmCustomers');
			$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
			$frm->setAction(Utilities::generateUrl('affiliates','save_affiliate_details'));
			$frm->setExtra('class="web_form" validator="tplValidator" autocomplete="off"');
			$frm->setValidatorJsObjectName('tplValidator');
			$frm->setRequiredStarWith ('caption');
			$frm->addHiddenField('', 'affiliate_id', '0', '', '');
			$frm->addHtml('<h3>Affiliate Details</h3>', 'htmlNote','');
			$fld=$frm->addHtml('Username', 'affiliate_username', '', '', ' class="medium"');
			$fld=$frm->addHtml('Email Address', 'affiliate_email', '', '', ' class="medium"');
			$frm->addRequiredField('Name', 'affiliate_name');
			$frm->addRequiredField('Phone', 'affiliate_phone');
			
			$frm->addHtml('<h3>'.Utilities::getLabel('L_Your_Address_Details').'</h3>', 'htmlNote','');
			$fld=$frm->addTextBox('Company', 'affiliate_company');
			$fld=$frm->addTextBox('Website', 'affiliate_website');
			$frm->addRequiredField('Address Line 1', 'affiliate_address_1');
			$frm->addTextBox('Address Line 2', 'affiliate_address_2');
			$frm->addRequiredField('City', 'affiliate_city');
			$frm->addRequiredField('Postcode', 'affiliate_postcode');
			$countryObj=new Countries();
			$stateObj=new States();
			$countries = $countryObj->getAssociativeArray();
			$fld_country=$frm->addSelectBox('Country', 'affiliate_country', $countries, Settings::getSetting("CONF_COUNTRY"), 'onchange="loadStates(this);"', Utilities::getLabel('M_Country'), 'ua_country')->requirements()->setRequired(true);
			$frm->addSelectBox(Utilities::getLabel('M_State_County_Province'), 'affiliate_state', $stateObj->getStatesAssoc(Settings::getSetting("CONF_COUNTRY")), '', '', Utilities::getLabel('M_State_County_Province'), 'ua_state')->requirements()->setRequired(true);
			
			
			$frm->addHtml('<h3>'.Utilities::getLabel('L_Payment_Information').'</h3>', 'htmlNote','');
			$fld=$frm->addTextBox('<label>'.Utilities::getLabel('L_Tax_ID').'</label>', 'affiliate_tax');
			$frm->addRadioButtons('<label>'.Utilities::getLabel('L_Payment_Method').'</label>', 'affiliate_payment',array("cheque"=>"Cheque","bank"=>"Bank","paypal"=>"PayPal"),"cheque",1,'width="15%" ');
			
			
			$fldPayment=$frm->addTextBox('<div class="payment" lang="payment-cheque"><label>'.Utilities::getLabel('L_Cheque_Payee_Name').'</label>', 'affiliate_cheque');
			$fldPayment->html_after_field='</div>';
			
			$fldPaypal=$frm->addTextBox('<div class="payment" lang="payment-paypal"><label>'.Utilities::getLabel('L_PayPal_Email_Account').'</label>', 'affiliate_paypal');
			$fldPaypal->html_after_field='</div>';
			
			$frm->addTextBox('<div class="payment" lang="payment-bank"><label>'.Utilities::getLabel('L_Bank_Name').'</label>', 'affiliate_bank_name')->html_after_field='</div>';
			$frm->addTextBox('<div class="payment" lang="payment-bank"><label>'.Utilities::getLabel('L_ABA/BSB_number_Branch_Number').'</label>', 'affiliate_bank_branch_number')->html_after_field='</div>';
			$frm->addTextBox('<div class="payment" lang="payment-bank"><label>'.Utilities::getLabel('L_SWIFT_Code').'</label>', 'affiliate_bank_swift_code')->html_after_field='</div>';
			$frm->addTextBox('<div class="payment" lang="payment-bank"><label>'.Utilities::getLabel('L_Account_Name').'</label>', 'affiliate_bank_account_name')->html_after_field='</div>';
			$fldAn=$frm->addTextBox('<div class="payment" lang="payment-bank"><label>'.Utilities::getLabel('L_Account_Number').'</label>', 'affiliate_bank_account_number')->html_after_field='</div>';
			
			$frm->addRequiredField('Tracking Code', 'affiliate_code');
			$frm->getField("affiliate_code")->html_after_field='<small>The tracking code that will be used to track referrals.</small>';
			
			/*$fld=$frm->addTextBox('Commission (%)', 'affiliate_commission');
			$fld->requirements()->setFloatPositive();
			$fld->html_after_field='&nbsp;<small>Percentage the affiliate receives on each order.</small>';*/
			
			$frm->addSelectBox('Status', 'affiliate_status', $user_status,'','','')->requirements()->setRequired(true);
			
			$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
			$frm->setJsErrorDisplay('afterfield');
			$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
			$frm->setLeftColumnProperties('width="15%"');
			return $frm;
		}
		
		
		function addUserTransactionForm($affiliate_id) {
			$frm=new Form('frmAffTransaction','frmAffTransaction');
			$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
			$frm->setLeftColumnProperties('width="15%"');
			$frm->setValidatorJsObjectName('system_validator');
			$frm->setExtra('class="siteForm" validator="system_validator" ');
			$frm->setAction(Utilities::generateUrl('affiliates','add_transaction'));
			$frm->addHiddenField('', 'affiliate_id', '0', '', '');
			$frm->addSelectBox('Type', 'type', array("C"=>"Credit","D"=>"Debit"),'','','')->requirements()->setRequired(true);
			$frm->addRequiredField('Amount', 'amount')->requirements()->setFloatPositive();
			$frm->addTextArea('Description', 'description')->requirements()->setRequired();
			$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
			$frm->setJsErrorDisplay('afterfield');
			return $frm;
		}
	
	
    function form($affiliate_id,$tab="") {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("Affiliate Setup", '');
		$this->set('breadcrumb', $this->b_crumb->output());
		$affObj=new Affiliate();
        $affiliate_id = intval($affiliate_id);
        $frm = $this->addUserForm($affiliate_id);
		$changepasswordfrm=$this->changePasswordForm();
		$sendemailfrm=$this->sendEmailForm();
		$transactionfrm=$this->addUserTransactionForm($affiliate_id);
        if ($affiliate_id > 0) {
			$data = $affObj->getAffiliateById($affiliate_id);
			unset($data['affiliate_password']);
			$data["ua_state"]=isset($data["affiliate_state_county"])?$data["affiliate_state_county"]:'';
            $frm->fill($data);
			$changepasswordfrm->fill($data);
			$sendemailfrm->fill($data);
			$transactionfrm->fill(array("affiliate_id"=>$affiliate_id));
			$this->set('affiliate', $data);
        }
        $this->set('frm', $frm);
		$this->set('changepasswordfrm', $changepasswordfrm);
		$this->set('sendemailfrm', $sendemailfrm);
		$this->set('transactionfrm', $transactionfrm);
		$this->set('showTab', $tab);
        $this->_template->render();
    }
	
	function transactions($affiliate_id,$page=1) {
		$atObj=new Affiliatetransactions();
		$page = intval($page);
		if ($page < 1)
			$page = 1;
		$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
		$criteria['pagesize'] = $pagesize;
		$criteria['page'] = $page;
		$criteria['affiliate'] = $affiliate_id;
		$this->set('arr_listing', $atObj->getTransactions($criteria,$pagesize));
		$this->set('pages', $atObj->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page-1)*$pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $atObj->getTotalRecords();
		if ($total_records < $end_record) $end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->set('criteria', $criteria);
        $this->_template->render(false,false);
    }
	
	function save_affiliate_details() {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$affObj=new Affiliate();
		$post = Syspage::getPostedVar();
        $affiliate_id = intval($post['affiliate_id']);
		$user = $affObj->getAffiliateById($affiliate_id);
		if (!$user){
			Message::addErrorMessage('Error: Invalid Request!!');
			Utilities::redirectUser(Utilities::generateUrl('affiliates'));
		}
        $frm = $this->addUserForm($affiliate_id);
		if(!$frm->validate($post)){
			Message::addErrorMessage($frm->getValidationErrors());
		}else{
			if($affObj->updateAffiliate($post)){
				Message::addMessage('Success: Affiliate details added/updated successfully.');
			}else{
				Message::addErrorMessage($affObj->getError());
			}
		}
		Utilities::redirectUserReferer();
    }
	
	
	function sendemail() {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$affObj=new Affiliate();
		$post = Syspage::getPostedVar();
        $affiliate_id = intval($post['affiliate_id']);
		$affiliate = $affObj->getAffiliateById($affiliate_id);
		if (!$affiliate){
			Message::addErrorMessage('Error: Invalid Request!!');
		}
        $frm = $this->sendEmailForm($affiliate_id);
		if(!$frm->validate($post)){
			Message::addErrorMessage($frm->getValidationErrors());
		}else{
						Utilities::sendMailTpl($affiliate['affiliate_email'], 'user_send_email', array(
        											'{reset_url}' => $reset_url,
											        '{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
											        '{website_url}' => $website_url,
													'{site_domain}' => CONF_SERVER_PATH,
													'{full_name}' => trim($affiliate['affiliate_name']),
													'{admin_subject}' => trim($post['mail_subject']),
													'{admin_message}' => nl2br($post["mail_body"]),
													
				         ));
						 
						Message::addMessage('Your message sent to - '.$affiliate["affiliate_email"]);
		}
		Utilities::redirectUserReferer();
    }
	
	function changepassword() {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$affObj=new Affiliate();
		$post = Syspage::getPostedVar();
        $affiliate_id = intval($post['affiliate_id']);
		$user = $affObj->getAffiliateById($affiliate_id);
		if (!$user){
			Message::addErrorMessage('Error: Invalid Request!!');
		}
        $frm = $this->changePasswordForm($affiliate_id);
		if(!$frm->validate($post)){
			Message::addErrorMessage($frm->getValidationErrors());
		}else{
			if($affObj->changeAffiliatePassword($post)){
				Message::addMessage('Success: Password updated successfully.');
			}else{
				Message::addErrorMessage($affObj->getError());
			}
		}
		Utilities::redirectUserReferer();
    }
	
	function add_transaction() {
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),AFFILIATES)) {
			$json['error'] = Admin::getUnauthorizedMsg();
        }
		$affObj=new Affiliate();
		$post = Syspage::getPostedVar();
        $affiliate_id = intval($post['affiliate_id']);
		$user = $affObj->getAffiliateById($affiliate_id);
		if (!$user){
			$json['error'] = 'Error: Invalid Request!!';
		}
		$transObj=new Affiliatetransactions();
		$txnArray["atxn_affiliate_id"]=$affiliate_id;
		$txnArray["atxn_credit"]=$post["type"]=="C"?$post["amount"]:0;
		$txnArray["atxn_debit"]=$post["type"]=="D"?$post["amount"]:0;
		$txnArray["atxn_status"]=1;
		$txnArray["atxn_description"]=$post["description"];
		if($txn_id=$transObj->addAffiliateTransaction($txnArray)){
			$emailNotificationObj=new Emailnotifications();
			$emailNotificationObj->sendAffiliateTxnNotification($txn_id);
			$json['message'] = 'Success: Transaction added successfully.';
			$json['success'] = 1;
		}else{
			$json['error'] = $transObj->getError();
		}
		echo json_encode($json);
    }
	
	function update_affiliate_status() {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$post = Syspage::getPostedVar();
		$aObj=new Affiliate();		
        $affiliate_id = intval($post['id']);
        $affiliate = $aObj->getAffiliate(array('id'=>$affiliate_id),false);
		if($affiliate==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		$data_to_update = array('affiliate_status'=>!$affiliate['affiliate_status']);
		if($aObj->updateAffiliateStatus($affiliate_id,$data_to_update)){
			Message::addMessage('Success: Status has been modified successfully.');
			$arr = array('status'=>1, 'msg'=>Message::getHtml(), 'linktext'=>(($affiliate['affiliate_status'] == 1)?'Active':'Inactive'));
			die(convertToJson($arr));
		}else{
			Message::addErrorMessage($aObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	 
	function delete() {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $post = Syspage::getPostedVar();
		$aObj=new Affiliate();	
        $affiliate_id = intval($post['id']);
        $affiliate = $aObj->getAffiliate(array('id'=>$affiliate_id),false);
		if($affiliate==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		if($aObj->delete($affiliate_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($aObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	function approve() {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$post = Syspage::getPostedVar();
		$aObj=new Affiliate();		
        $affiliate_id = intval($post['id']);
        $affiliate = $aObj->getAffiliate(array('id'=>$affiliate_id),false);
		if($affiliate==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		$data_to_update = array('affiliate_is_approved'=>1);
		if($aObj->updateAffiliateStatus($affiliate_id,$data_to_update)){
			Message::addMessage('Success: Affiliate approved successfully.');
			$arr = array('status'=>1, 'msg'=>Message::getHtml(), 'linktext'=>(($affiliate['affiliate_status'] == 1)?'Active':'Inactive'));
			$emailNotObj=new Emailnotifications();
			$emailNotObj->affiliateAccountApproved($affiliate_id);
			die(convertToJson($arr));
		}else{
			Message::addErrorMessage($aObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	
	function login($affiliate_id) {
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),AFFILIATES)) {
             die(Admin::getUnauthorizedMsg());
        }
		$aObj=new Affiliate();	
		$affiliate = $aObj->getAffiliate(array('id'=>$affiliate_id),true);
		if (!$affiliate){
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
			Utilities::redirectUser(Utilities::generateUrl('affiliates'));
		}
		if(!$aObj->login($affiliate['affiliate_username'], $affiliate['affiliate_password'],true,true) === true){
			Message::addErrorMessage($aObj->getError());
			Utilities::redirectUser(Utilities::generateUrl('affiliates'));
		}
		Utilities::redirectUser(Utilities::generateUrl('affiliate','',array(),CONF_WEBROOT_URL));
		
	}
	
	protected function getWithdrawalRequestSearchForm() {
		global $status_arr;
        $frm=new Form('frmSearchAffiliateWithdrawalRequests','frmSearchAffiliateWithdrawalRequests');
		$frm->setFieldsPerRow(3);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('GET');		
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$fld=$frm->addTextBox('Keyword', 'keyword','','',' class="medium" placeholder="Name, Username"');
		$frm->addTextBox('From ['.CONF_CURRENCY_SYMBOL.']', 'minprice','','',' class="small"')->requirements()->setFloatPositive($val=true);
		$frm->addTextBox('To ['.CONF_CURRENCY_SYMBOL.']', 'maxprice','','',' class="small"')->requirements()->setFloatPositive($val=true);
		$frm->addSelectBox('Status', 'status',$status_arr,'' , 'class="small"','All');
		$fld=$frm->addDateField('Date From', 'date_from', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld=$frm->addDateField('Date To', 'date_to', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchAffiliateWithdrawalRequests(this); return false;');
        return $frm;
    }
	
	function withdrawal_requests() {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $frm = $this->getWithdrawalRequestSearchForm();
        $this->set('frmPost', $frm);
		$this->b_crumb->add("Withdrawal Requests Management", Utilities::generateUrl("affiliates","withdrawal_requests"));
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listWithdrawalRequests($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
			$afwrObj=new AffiliateWithdrawalRequests();
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
            $this->set('records', $afwrObj->getWithdrawRequests($post));
            $this->set('pages', $afwrObj->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $afwrObj->getTotalRecords();
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
	function update_affiliate_withdrawal_request_status(){
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$post = Syspage::getPostedVar();
		$mod = $post['mod'];
		$afwrObj=new AffiliateWithdrawalRequests();
        $id = intval($post['id']);
		$request=$afwrObj->getWithdrawRequestData($id);
        if($request==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		switch($mod) {
            case 'approve':
            	$data_to_update = array(
					'afwithdrawal_status'=>3,
	            );
				Message::addMessage('Withdrawal request has been approved successfully.');
            break;
            case 'decline':
    	        $data_to_update = array(
					'afwithdrawal_status'=>2,
            	);
				Message::addMessage('Withdrawal request has been declined successfully.');
            break;
           
        }
		if($afwrObj->updateWithdrawalRequestStatus($id,$data_to_update)){
			$db = &Syspage::getdb();
			$emailNotificationObj=new Emailnotifications();
			$emailNotificationObj->SendAffiliateWithdrawRequestNotification($id,"U");
			
			$rs = $db->update_from_array('tbl_affiliate_transactions',array("atxn_status"=>1), array('smt'=>'atxn_withdrawal_id=?','vals'=>array($id)));	
			if ($mod=='decline'){
				$aftransObj=new Affiliatetransactions();
				$txn_detail=$aftransObj->getTransactionById('',array('withdrawal_request'=>$id));
				$formatted_request_value="#".str_pad($id,6,'0',STR_PAD_LEFT);
				$txnArray["atxn_affiliate_id"]=$txn_detail["atxn_affiliate_id"];
				$txnArray["atxn_credit"]=$txn_detail["atxn_debit"];
				$txnArray["atxn_status"]=1;
				$txnArray["atxn_withdrawal_id"]=$txn_detail["atxn_withdrawal_id"];
				$txnArray["atxn_description"]=sprintf(Utilities::getLabel('M_Withdrawal_Request_Declined_Amount_Refunded'),$formatted_request_value);
				
				if($txn_id=$aftransObj->addAffiliateTransaction($txnArray)){
					$emailNotificationObj=new Emailnotifications();
					$emailNotificationObj->sendAffiliateTxnNotification($txn_id);
				}
		
			}
			$arr = array('status'=>1, 'msg'=>Message::getHtml());
			die(convertToJson($arr));
		
			
		}else{
			Message::addErrorMessage($afwrObj->getError());
			dieJsonError(Message::getHtml());
		}
	}
	
	
	
	
  
	
}