<?php
class UsersController extends BackendController {
	
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canviewcustomers = Admin::getAdminAccess($admin_id,CUSTOMERS);
        $this->set('canviewcustomers', $this->canviewcustomers);
		$this->canviewcancellationrequests = Admin::getAdminAccess($admin_id,CANCELLATION_REQUESTS);
        $this->set('canviewcancellationrequests', $this->canviewcancellationrequests);
		$this->canviewsuppapprequests = Admin::getAdminAccess($admin_id,SUPPLIER_APPROVAL_REQUESTS);
        $this->set('canviewsuppapprequests', $this->canviewsuppapprequests);
		$this->canviewsuppappform = Admin::getAdminAccess($admin_id,SUPPLIER_APPROVAL_FORM);
		$this->canviewsupprequests = Admin::getAdminAccess($admin_id,SUPPLIER_REQUESTS);
        $this->set('canviewsupprequests', $this->canviewsupprequests);
		$this->canviewadvertisers = Admin::getAdminAccess($admin_id,ADVERTISERS);
        $this->set('canviewadvertisers', $this->canviewcustomers);
        $this->b_crumb = new Breadcrumb();		
    }
	
	protected function getSearchForm() {
		global $user_status;
        $frm=new Form('frmUserSearch','frmUserSearch');
		$frm->setFieldsPerRow(4);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('GET');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addHiddenField('', 'is_deleted',0);
		$frm->addTextBox('Keyword', 'keyword','','',' class="small"');
		$frm->addSelectBox('Status', 'status',$user_status,'' , 'class="small"','All');
		$fld=$frm->addDateField('Date From', 'date_from', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld=$frm->addDateField('Date To', 'date_to', '', '', 'readonly="true" class="small dateTimeFld"');
		$frm->addTextBox('Balance From ['.CONF_CURRENCY_SYMBOL.']', 'minbalance','','',' class="small"')->requirements()->setFloatPositive($val=true);
		$frm->addTextBox('Balance To ['.CONF_CURRENCY_SYMBOL.']', 'maxbalance','','',' class="small"')->requirements()->setFloatPositive($val=true);
		$frm->addSelectBox('Type', 'type',array(CONF_BUYER_USER_TYPE=>"Buyer Only",CONF_SELLER_USER_TYPE=>"Seller Only",CONF_BUYER_SELLER_USER_TYPE=>"Buyer+Seller"),'' , 'class="small"','All');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchUsers(this); return false;');
        return $frm;
    }
	
	function default_action() {
        if ($this->canviewcustomers != true) {
            $this->notAuthorized();
        }
        $frm = $this->getSearchForm();
		$frm->fill(getQueryStringData());
        $this->set('frmPost', $frm);
		$this->b_crumb->add("Buyers & Sellers Management",Utilities::generateUrl("users"));
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function deleted_records() {
        if ($this->canviewcustomers != true) {
            $this->notAuthorized();
        }
        $frm = $this->getSearchForm();
		$frm->fill(array('is_deleted'=>1));
        $this->set('frmPost', $frm);
		$this->b_crumb->add("Buyers & Sellers Management",Utilities::generateUrl("users"));
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listUsers($page = 1) {
        if ($this->canviewcustomers != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
			$uObj=new User();
            $post = Syspage::getPostedVar();
			if ($post['is_deleted']){
				$uObj->setDeletedRecords(true);
			}
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
			if (empty($post['type']))
			$post['type']=array("3","4","5");
            $post['pagesize'] = $pagesize;
            $this->set('records', $uObj->getUsers($post));
            $this->set('pages', $uObj->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $uObj->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
			$this->set('subscriptionOrderObj',new SubscriptionOrders());
            $this->_template->render(false, false);
        }
    }
	
	
	
	
	protected function getUserBalanceSearchForm() {
		global $user_status;
        $frm=new Form('frmUserBalanceSearch','frmUserBalanceSearch');
		$frm->setFieldsPerRow(4);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('GET');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'user');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchUserBalance(this); return false;');
        return $frm;
    }
	
	function balance($user) {
		Utilities::redirectUser(generateUrl('users','customer_form',array($user,'transactions')));
        /*if ($this->canviewcustomers != true) {
            $this->notAuthorized();
        }
        $frm = $this->getUserBalanceSearchForm();
		$frm->fill(array("user"=>$user));
        $this->set('frmPost', $frm);
		//$this->b_crumb->add("Buyers & Sellers Management",'#');
		$this->b_crumb->add("Account Balance",'');
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();*/
    }
	
	function listUserBalanceRecords() {
        if ($this->canviewcustomers != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user'])) {
			$tObj=new Transactions();
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
            $this->set('records', $tObj->getTransactions($post,$pagesize));
            $this->set('pages', $tObj->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $tObj->getTotalRecords();
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
	protected function changePasswordForm(){
		$frm=new Form('frmCustomers','frmStrengthPassword');
		$frm->setAction(Utilities::generateUrl('users','changepassword'));
		$frm->setExtra('class="web_form" validator="tplValidator"');
		$frm->setRequiredStarWith('caption');
		$frm->setValidatorJsObjectName('tplValidator');
		$userObj=new User();
		$frm->addHiddenField('', 'user_id');
		//$frm->addSelectBox('User', 'user_id',$userObj->getAssociativeArray(),$user_id, 'class="medium" ')->requirements()->setRequired();
		$fld=$frm->addPasswordField('New Password', 'user_password', '', 'check-password', ' class="field8"');
		$fld->requirements()->setRequired(true);
		$fld->requirements()->setLength(4,20);
		$fld->html_before_field='<div id="check-password-result">';
		$fld->html_after_field='</div>';
		$fld=$frm->addPasswordField('Confirm Password', 'user_conpassword', '', '', ' class="field8"');
		$fld->requirements()->setRequired();
		$fld->requirements()->setCompareWith('user_password','eq','');
		$frm->setJsErrorDisplay('afterfield');
		$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="15%"');
		return $frm;
	}
	
	
	protected function sendEmailForm(){
		$frm=new Form('frmCustomersSendEmail','frmCustomersSendEmail');
		$frm->setAction(Utilities::generateUrl('users','sendemail'));
		$frm->setExtra('class="web_form" validator="tplValidator"');
		$frm->setValidatorJsObjectName('tplValidator');
		$frm->setRequiredStarWith('caption');
		$userObj=new User();
		$frm->addHiddenField('', 'user_id');
		//$frm->addSelectBox('User', 'user_id',$userObj->getAssociativeArray(), $user_id, 'class="input-xlarge" ')->requirements()->setRequired();
		$frm->addRequiredField('Mail Subject', 'mail_subject', '', '', ' class="field8"');
		$fld=$frm->addTextArea('Message', 'mail_body', '', 'mail_body', ' class="cleditor" rows="3"');
		$fld->requirements()->setRequired();
		
		$frm->setJsErrorDisplay('afterfield');
		$frm->addSubmitButton('&nbsp;','btn_submit','Send Email');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="15%"');
		return $frm;
	}
	function addUserForm($user_id) {
			$frm=new Form('frmCustomersAddUser','frmCustomersAddUser');
			$frm->setAction(Utilities::generateUrl('users','save_user_details'));
			$frm->setExtra('class="web_form" validator="tplValidator" autocomplete="off"');
			$frm->setValidatorJsObjectName('tplValidator');
			$frm->setRequiredStarWith ('caption');
			$frm->addHiddenField('', 'user_id');
			$fld=$frm->addHtml('Username', 'user_username', '', '', ' class="medium"');
			$fld=$frm->addHtml('Email Address', 'user_email', '', '', ' class="medium"');
			$frm->addRequiredField('Customer Name', 'user_name', null, 'user_name');
			$fld_addr = $frm->addRequiredField('Phone Number', 'user_phone', '', 'user_phone', '');
			$frm->addTextBox(Utilities::getLabel('M_City_Town'), 'user_city_town', null, 'user_city_town');
			$countryObj=new Countries();
			$stateObj=new States();
			$fld_country=$frm->addSelectBox('Country', 'user_country', $countryObj->getAssociativeArray(), Settings::getSetting("CONF_COUNTRY"), 'onchange="loadStates(this);"', Utilities::getLabel('M_Country'), 'ua_country');
			$frm->addSelectBox(Utilities::getLabel('M_State_County_Province'), 'ua_state', array(), '', '', Utilities::getLabel('M_State_County_Province'), 'ua_state');
			
			$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
			$frm->setJsErrorDisplay('afterfield');
			$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
			$frm->setLeftColumnProperties('width="15%"');
			return $frm;
		}
		
		function addUserBankForm($user_id) {
			$bObj=new Banks();
			$frm=new Form('frmCustomersBankForm','frmCustomersBankForm');
			$frm->setAction(Utilities::generateUrl('users','save_bank_details'));
			$frm->setExtra('class="web_form" validator="tplValidator" autocomplete="off"');
			$frm->setValidatorJsObjectName('tplValidator');
			$frm->setRequiredStarWith ('caption');
			$frm->addHiddenField('', 'ub_user_id', $user_id, '', '');
			$frm->addRequiredField('<label>'.Utilities::getLabel('M_Bank_Name').'</label>', 'ub_bank_name', null, 'ub_bank_name');
			$frm->addRequiredField('<label>'.Utilities::getLabel('M_Account_Holder_Name').'</label>', 'ub_account_holder_name', null, 'user_account_holder_name');
			$frm->addRequiredField('<label>'.Utilities::getLabel('M_Account_Number').'</label>', 'ub_account_number', null, 'user_account_number');
			$frm->addTextBox('<label>'.Utilities::getLabel('M_IFSC_Swift_Code').'</label>', 'ub_ifsc_swift_code', null, 'ub_ifsc_swift_code');
			$frm->addTextArea('<label>'.Utilities::getLabel('M_Bank_Address').'</label>', 'ub_bank_address', null, 'ub_bank_address')->requirements()->setRequired();
			
			$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
			$frm->setJsErrorDisplay('afterfield');
			$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
			$frm->setLeftColumnProperties('width="15%"');
			return $frm;
		}
		
		function addUserTransactionForm($user_id=0) {
			$frm=new Form('frmCustomerTransaction','frmCustomerTransaction');
			$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
			$frm->setLeftColumnProperties('width="15%"');
			$frm->setValidatorJsObjectName('txnsystem_validator');
			$frm->setExtra('class="siteForm" validator="txnsystem_validator" ');
			$frm->setAction(Utilities::generateUrl('users','add_transaction'));
			$frm->addHiddenField('', 'user_id');
			$frm->addSelectBox('Type', 'type', array("C"=>"Credit","D"=>"Debit"),'','','')->requirements()->setRequired(true);
			$frm->addRequiredField('Amount', 'amount')->requirements()->setFloatPositive();
			$frm->addTextArea('Description', 'description')->requirements()->setRequired();
			$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
			$frm->setJsErrorDisplay('afterfield');
			return $frm;
		}
		
	function addUserRewardForm($user_id=0) {
			$frm=new Form('frmCustomerReward','frmCustomerReward');
			$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
			$frm->setLeftColumnProperties('width="15%"');
			$frm->setValidatorJsObjectName('rewardsystem_validator');
			$frm->setExtra('class="siteForm" validator="rewardsystem_validator" ');
			$frm->setAction(Utilities::generateUrl('users','add_reward_points'));
			$frm->addHiddenField('', 'user_id');
			$frm->addRequiredField('Points', 'points')->requirements()->setInt();
			$frm->addTextArea('Description', 'description')->requirements()->setRequired();
			$fld=$frm->addTextBox('Validity:', 'validity', '', '', 'class="small"');
			$fld->requirements()->setIntPositive();
			$fld->html_after_field='&nbsp;<strong>days</strong><small>Rewards points validity in days from the date of credit. Please leave it blank if you don\'t want reward points to expire.</small>';
			$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
			$frm->setJsErrorDisplay('afterfield');
			return $frm;
	}
	
	
    function customer_form($user_id,$tab="") {
		global $conf_arr_advertiser_types;
		if ($this->canviewcustomers != true) {
            $this->notAuthorized();
        }
		$userObj=new User();
        $user_id = intval($user_id);
        $frm = $this->addUserForm($user_id);
		$bankfrm = $this->addUserBankForm($user_id);
		$changepasswordfrm=$this->changePasswordForm();
		$sendemailfrm=$this->sendEmailForm();
		$transactionfrm=$this->addUserTransactionForm();
		$rewardfrm=$this->addUserRewardForm();
        if ($user_id > 0) {
			$data = $userObj->getUserById($user_id);
			if ($data['user_type']==CONF_ADVERTISER_USER_TYPE){
				$this->b_crumb->add("Advertisers Management",Utilities::generateUrl("users","advertisers"));
				$fld=$frm->addTextBox(Utilities::getLabel('L_Company'), 'user_company');
				$fld=$frm->addTextArea(Utilities::getLabel('L_Brief_Profile'), 'user_profile');
				$fld->html_after_field=Utilities::getLabel('L_Please_tell_us_something_about_yourself');
				$frm->addTextArea(Utilities::getLabel('L_What_kind_products_services_advertise'), 'user_products_services');
				$frm->changeFieldPosition($frm->getField('user_company')->getFormIndex(),5);
				$frm->changeFieldPosition($frm->getField('user_profile')->getFormIndex(),6);
				$frm->changeFieldPosition($frm->getField('user_products_services')->getFormIndex(),7);
				
			}else{
				$this->b_crumb->add("Buyers & Sellers Management",Utilities::generateUrl("users"));
			}
			$this->b_crumb->add("User Setup", '');
			unset($data['user_password']);
			$data['addresses']=$userObj->getUserAddresses($user_id);
			$data["ua_state"]=$data["user_state_county"];
            $frm->fill($data);
			$bankfrm->fill($data);
			$changepasswordfrm->fill($data);
			$sendemailfrm->fill($data);
			$transactionfrm->fill(array("user_id"=>$user_id));
			$rewardfrm->fill(array("user_id"=>$user_id));
			$this->set('user', $data);
        }
        $this->set('frm', $frm);
		$this->set('bankfrm', $bankfrm);
		$this->set('changepasswordfrm', $changepasswordfrm);
		$this->set('sendemailfrm', $sendemailfrm);
		$this->set('transactionfrm', $transactionfrm);
		$this->set('rewardfrm', $rewardfrm);
		$this->set('breadcrumb', $this->b_crumb->output());
		$this->set('showTab', $tab);
        $this->_template->render();
    }
	
	function transactions($user_id,$page=1) {
		$tObj=new Transactions();
		$page = intval($page);
		if ($page < 1)
			$page = 1;
		$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
		$criteria['pagesize'] = $pagesize;
		$criteria['page'] = $page;
		$criteria['user'] = $user_id;
		$this->set('arr_listing', $tObj->getTransactions($criteria,$pagesize));
		$this->set('pages', $tObj->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page-1)*$pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $tObj->getTotalRecords();
		if ($total_records < $end_record) $end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->set('criteria', $criteria);
        $this->_template->render(false,false);
    }
	
	function rewards($user_id,$page=1) {
		$rewObj=new Rewards();
		$page = intval($page);
		if ($page < 1)
			$page = 1;
		$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
		$criteria['pagesize'] = $pagesize;
		$criteria['page'] = $page;
		$criteria['user'] = $user_id;
		$this->set('arr_listing', $rewObj->getRewardPoints($criteria,$pagesize));
		$this->set('pages', $rewObj->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page-1)*$pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $rewObj->getTotalRecords();
		if ($total_records < $end_record) $end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->set('criteria', $criteria);
        $this->_template->render(false,false);
    }
	
	function add_reward_points() {
		if ($this->canviewcustomers != true) {
            $this->notAuthorized();
        }
		$userObj=new User();
		$post = Syspage::getPostedVar();
        $user_id = intval($post['user_id']);
		$user = $userObj->getUserById($user_id);
		if (!$user){
			$json['error'] = 'Error: Invalid Request!!';
		}
		$rewardObj=new Rewards();
		$rewardArray["urp_user_id"]=$user_id;
		$rewardArray["urp_points"]=$post["points"];
		if ((int)$post['validity']>0){
			$reward_expiry_date = date('Y-m-d', strtotime('+'.(int)$post['validity'].' days'));
			$rewardArray["urp_date_expiry"]=$reward_expiry_date;
		}
		$rewardArray["urp_description"]=$post["description"];
		if($reward_point_id=$rewardObj->addRewardPoints($rewardArray)){
			$emailNotificationObj=new Emailnotifications();
			$emailNotificationObj->sendRewardPointsNotification($reward_point_id);
			$json['message'] = 'Success: Reward points updated successfully.';
			$json['success'] = 1;
		}else{
			$json['error'] = $rewardObj->getError();
		}
		echo json_encode($json);
    }
	
	function save_user_details() {
		if ($this->canviewcustomers != true) {
            $this->notAuthorized();
        }
		$userObj=new User();
		$post = Syspage::getPostedVar();
        $user_id = intval($post['user_id']);
		$user = $userObj->getUserById($user_id);
		if (!$user){
			Message::addErrorMessage('Error: Invalid Request!!');
			Utilities::redirectUser(Utilities::generateUrl('users'));
		}
        $frm = $this->addUserForm($user_id);
		if(!$frm->validate($post)){
			Message::addErrorMessage($frm->getValidationErrors());
		}else{
			if($userObj->updateUser($post)){
				Message::addMessage('Success: User details added/updated successfully.');
			}else{
				Message::addErrorMessage($userObj->getError());
			}
		}
		Utilities::redirectUserReferer();
    }
	
	function save_bank_details() {
		if ($this->canviewcustomers != true) {
            $this->notAuthorized();
        }
		$userObj=new User();
		$post = Syspage::getPostedVar();
        $user_id = intval($post['ub_user_id']);
		$user = $userObj->getUserById($user_id);
		if (!$user){
			Message::addErrorMessage('Error: Invalid Request!!');
		}else{
        	$frm = $this->addUserBankForm($user_id);
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				if($userObj->updateUserBankDetails($post)){
					Message::addMessage('Success: Bank details added/updated successfully.');
				}else{
					Message::addErrorMessage($userObj->getError());
				}
			}
		}
		Utilities::redirectUserReferer();
    }
	
	function sendemail() {
		if ($this->canviewcustomers != true) {
            $this->notAuthorized();
        }
		$userObj=new User();
		$post = Syspage::getPostedVar();
        $user_id = intval($post['user_id']);
		$user = $userObj->getUserById($user_id);
		if (!$user){
			Message::addErrorMessage('Error: Invalid Request!!');
		}
        $frm = $this->sendEmailForm($user_id);
		if(!$frm->validate($post)){
			Message::addErrorMessage($frm->getValidationErrors());
		}else{
						Utilities::sendMailTpl($user['user_email'], 'user_send_email', array(
        											'{reset_url}' => $reset_url,
											        '{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
											        '{website_url}' => $website_url,
													'{site_domain}' => CONF_SERVER_PATH,
													'{full_name}' => trim($user['user_name']),
													'{admin_subject}' => trim($post['mail_subject']),
													'{admin_message}' => nl2br($post["mail_body"]),
													
				         ));
						 
						Message::addMessage('Your message sent to - '.$user["user_email"]);
		}
		Utilities::redirectUserReferer();
    }
	
	function changepassword() {
		if ($this->canviewcustomers != true) {
            $this->notAuthorized();
        }
		$userObj=new User();
		$post = Syspage::getPostedVar();
        $user_id = intval($post['user_id']);
		$user = $userObj->getUserById($user_id);
		if (!$user){
			Message::addErrorMessage('Error: Invalid Request!!');
		}
        $frm = $this->changePasswordForm($user_id);
		if(!$frm->validate($post)){
			Message::addErrorMessage($frm->getValidationErrors());
		}else{
			if($userObj->changeUserPassword($post)){
				Message::addMessage('Success: Password updated successfully.');
			}else{
				Message::addErrorMessage($userObj->getError());
			}
		}
		Utilities::redirectUserReferer();
    }
	
	function add_transaction() {
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),CUSTOMERS)) {
			$json['error'] = Admin::getUnauthorizedMsg();
        }
		$userObj=new User();
		$post = Syspage::getPostedVar();
        $user_id = intval($post['user_id']);
		$user = $userObj->getUserById($user_id);
		if (!$user){
			$json['error'] = 'Error: Invalid Request!!';
		}
		$transObj=new Transactions();
		$txnArray["utxn_user_id"]=$user_id;
		$txnArray["utxn_credit"]=$post["type"]=="C"?$post["amount"]:0;
		$txnArray["utxn_debit"]=$post["type"]=="D"?$post["amount"]:0;
		$txnArray["utxn_status"]=1;
		$txnArray["utxn_comments"]=$post["description"];
		if($txn_id=$transObj->addTransaction($txnArray)){
			$emailNotificationObj=new Emailnotifications();
			$emailNotificationObj->sendTxnNotification($txn_id);
			$json['message'] = 'Success: Transaction added successfully.';
			$json['success'] = 1;
		}else{
			$json['error'] = $transObj->getError();
		}
		echo json_encode($json);
    }
	
	
	
	function addressform($user_id) {
			$frm = new Form('frmCustomerAddress','frmCustomerAddress');
			$frm->setExtra(' validator="frmValidator" class="web_form"');
			$frm->setValidatorJsObjectName('frmValidator');
			$frm->addHiddenField('','user_id',0);
			$frm->addHiddenField('','ua_id',0);
			$frm->addRequiredField('Full Name', 'ua_name', null, 'ua_name');
			$fld_addr = $frm->addRequiredField('Phone Number', 'ua_phone', '', 'ua_phone', '');
			$frm->addRequiredField('Address Line 1', 'ua_address1', null, 'ua_address1');
			$frm->addTextBox('Address Line 2', 'ua_address2', null, 'ua_address2');
			$countryObj=new Countries();
			$stateObj=new States();
			$countries = $countryObj->getAssociativeArray();
			$fld_country=$frm->addSelectBox('Country', 'ua_country', $countries, Settings::getSetting("CONF_COUNTRY"), 'onchange="loadStates(this);"', Utilities::getLabel('M_Country'), 'ua_country')->requirements()->setRequired(true);
			$frm->addSelectBox(Utilities::getLabel('M_State_County_Province'), 'ua_state', $stateObj->getStatesAssoc(Settings::getSetting("CONF_COUNTRY")), '', '', Utilities::getLabel('M_State_County_Province'), 'ua_state')->requirements()->setRequired(true);
			$frm->addRequiredField(Utilities::getLabel('M_City_Town'), 'ua_city', null, 'ua_city');		
			$frm->addRequiredField(Utilities::getLabel('M_Postcode_Zip'), 'ua_zip', null, 'ua_zip');
			$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
			$frm->setJsErrorDisplay('afterfield');
			$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
			return $frm;
		}
	
	function address_form($user_id,$ua_id=0) {
		/*if ($this->canview != true) {
            $this->notAuthorized();
        }*/
		$this->b_crumb->add("User Address Setup",'');
		$this->set('breadcrumb', $this->b_crumb->output());
		$userObj=new User();
        $user_id = intval($user_id);
        $frm = $this->addressform($user_id);
        $frm->fill(array("user_id"=>$user_id));
		if($ua_id > 0 && $address = $userObj->getUserAddress($ua_id,$user_id)){
			$frm->fill($address);
		}
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
				if(!$frm->validate($post)){
					Message::addErrorMessage($frm->getValidationErrors());
				}else{
						$arr=array_merge($post,array("ua_user_id"=>$user_id));
						if($userObj->addUpdateAddress($arr)){
							Message::addMessage('Success: User Address Added/updated successfully.');	
							Utilities::redirectUser(Utilities::generateUrl('users','address_form',array($user_id,$ua_id)));
						}else{
							Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
						}
				}
				$frm->fill($post);
			}
        $this->set('frm', $frm);
        $this->_template->render(true,true);
    }
    function delete_address($user_id,$id) {
		if ($this->canviewcustomers != true) {
            $this->notAuthorized();
        }
		$userObj=new User();
		$address_details = $userObj->getUserAddress($id,$user_id);
		if ($address_details==true){
			if($userObj->deleteUserAddress(intval($id), $user_id)){
				Message::addMessage(Utilities::getLabel('M_Address_deleted_successfully'));
			}else{
				Message::addErrorMessage($userObj->getError());
			}
		}else{
			 Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
		}
		dieJsonSuccess('Success');
    }
	
	function update_user_status() {
		if ($this->canviewcustomers != true) {
            $this->notAuthorized();
        }
		$post = Syspage::getPostedVar();
		$uObj=new User();		
        $user_id = intval($post['id']);
        $user = $uObj->getUser(array('id'=>$user_id),false);
		if($user==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		$data_to_update = array('user_status'=>!$user['user_status']);
		if($uObj->updateUserStatus($user_id,$data_to_update)){
			Message::addMessage('Success: Status has been modified successfully.');
			$arr = array('status'=>1, 'msg'=>Message::getHtml(), 'linktext'=>(($user['user_status'] == 1)?'Active':'Inactive'));
			die(convertToJson($arr));
		}else{
			Message::addErrorMessage($uObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	function delete() {
        if ($this->canviewcustomers != true) {
            $this->notAuthorized();
        }
        $post = Syspage::getPostedVar();
		$uObj=new User();		
        $user_id = intval($post['id']);
        $user = $uObj->getUser(array('id'=>$user_id),false);
		if($user==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		if($uObj->delete($user_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($uObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	function restore_deleted_user() {
        if ($this->canviewcustomers != true) {
            $this->notAuthorized();
        }
        $post = Syspage::getPostedVar();
		$uObj=new User();		
        $user_id = intval($post['id']);
        $user = $uObj->getUser(array('id'=>$user_id),false,false);
		if($user==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		if($uObj->restore($user_id)){
			Message::addMessage('Success: Record has been restored.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($uObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	
	protected function getCancellationSearchForm() {
		global $status_arr;
        $frm=new Form('frmCancellationRequests','frmCancellationRequests');
		$frm->setFieldsPerRow(2);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('GET');		
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$fld=$frm->addTextBox('Keyword', 'keyword','','',' class="medium" placeholder="Name, Username"');
		$frm->addSelectBox('Status', 'status',$status_arr,'' , 'class="small"','All');
		$fld=$frm->addDateField('Date From', 'date_from', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld=$frm->addDateField('Date To', 'date_to', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchCancellationRequests(this); return false;');
        return $frm;
    }
	
	function cancellation_requests() {
        if ($this->canviewcancellationrequests != true) {
            $this->notAuthorized();
        }
        $frm = $this->getCancellationSearchForm();
        $this->set('frmPost', $frm);
		$this->b_crumb->add("Cancellation Requests",'');
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listCancellationRequests($page = 1) {
        if ($this->canviewcancellationrequests != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
			$crObj=new CancelRequests();
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
            $this->set('records', $crObj->getCancelRequests($post));
            $this->set('pages', $crObj->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $crObj->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
	
	function update_user_cancellation_request_status(){
		if ($this->canviewcancellationrequests != true) {
            $this->notAuthorized();
        }
		$post = Syspage::getPostedVar();
		$mod = $post['mod'];
        $id = intval($post['id']);
        $crObj=new CancelRequests();
		$cancellation_request=$crObj->getCancelRequest($id);
		if($cancellation_request==false) {
            Message::addErrorMessage('Please perform this action on valid request.');
            dieJsonError(Message::getHtml());
        }
		if ($mod=='approve'){
				$arr_pro_com=array_merge((array)Settings::getSetting("CONF_PROCESSING_ORDER_STATUS"),(array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS"));
				array_push($arr_pro_com,(array)Settings::getSetting("CONF_DEFAULT_CANCEL_ORDER_STATUS"));
				if (!in_array($cancellation_request["opr_status"],$arr_pro_com)){	
					$oObj=new Orders();
					$oObj->addChildOrderHistory($cancellation_request["opr_id"],Settings::getSetting("CONF_DEFAULT_CANCEL_ORDER_STATUS"),Utilities::getLabel('M_Your_Cancellation_Request_Approved'),true);
				}else{
					Message::addError('This order is not eligible for cancellation.');
		           dieJsonError(Message::getHtml());
				}
			}
        switch($mod) {
            case 'approve':
            	$data_to_update = array(
					'cancellation_request_status'=>3,
	            );
				Message::addMessage('Cancellation request has been approved successfully.');
            break;
            case 'decline':
    	        $data_to_update = array(
					'cancellation_request_status'=>2,
            	);
				Message::addMessage('Cancellation request has been declined successfully.');
            break;
        }
		if($crObj->updateCancellationRequestStatus($id,$data_to_update)){
			$emailNotificationObj=new Emailnotifications();
	  		$emailNotificationObj->SendCancellationRequestUpdateNotification($id);
			$arr = array('status'=>1, 'msg'=>Message::getHtml());
			die(convertToJson($arr));
		}else{
			Message::addErrorMessage($crObj->getError());
			dieJsonError(Message::getHtml());
		}
	}
	protected function getSearchApprovalRequestForm() {
		global $supplier_approval_request_status;
        $frm=new Form('frmSearchSupplierApprovalRequests','frmSearchSupplierApprovalRequests');
		$frm->setFieldsPerRow(2);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('GET');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addTextBox('Keyword', 'keyword','','',' class="small"');
		$frm->addSelectBox('Status', 'status',$supplier_approval_request_status,'' , 'class="small"','All');
		$fld=$frm->addDateField('Date From', 'date_from', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld=$frm->addDateField('Date To', 'date_to', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchSupplierApprovalRequests(this); return false;');
        return $frm;
    }
	
	function supplier_approval_requests() {
        if ($this->canviewsuppapprequests != true) {
            $this->notAuthorized();
        }
        $frm = $this->getSearchApprovalRequestForm();
		$frm->fill(getQueryStringData());
        $this->set('frmPost', $frm);
		$this->b_crumb->add("Supplier Approval Requests",'');
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listSupplierApprovalRequests() {
        if ($this->canviewsuppapprequests != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
			$uObj=new User();
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
            $this->set('records', $uObj->getUserSupplierRequests($post));
            $this->set('pages', $uObj->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $uObj->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
	function supplier_form() {
		if ($this->canviewsuppappform != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("Supplier Approval Form",'');
        $this->set('breadcrumb', $this->b_crumb->output());
		$uObj=new User();
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if($uObj->addUpdateSupplierFormFields($post)){
				Message::addMessage('Success: Seller Form fields updated successfully.');
				Utilities::redirectUser(Utilities::generateUrl('users','supplier_form'));
			}else{
				Message::addErrorMessage($uObj->getError());
			}
		}	
		$supplier_form_fields=$uObj->getSupplierFormFields();
		$this->set('supplier_form_fields',$supplier_form_fields);
        $this->_template->render();
    }
	
	protected function getSupplierRequestForm(){
		global $supplier_approval_request_status;
		$update_statueses=$supplier_approval_request_status;
		unset($update_statueses[0]);
		$frm=new Form('supplierRequestForm','supplierRequestForm');
		$frm->setValidatorJsObjectName('supplierRequestFormValidator');
		$frm->setExtra('class="web_form"');
		$frm->setLeftColumnProperties('valign="top" align="left"');
		//$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="tblBorderTop small"');
		$frm->captionInSameCell(true);
		$frm->setFieldsPerRow(1);
		$frm->setJsErrorDisplay('afterfield');
		$frm->addSelectBox(Utilities::getLabel('M_Status'), 'status',$update_statueses, 'class="small"','','-- Update Status -- ','status')->requirements()->setRequired();
		$fldBL=$frm->addTextArea('<span id="div_comments_box">Reason for Cancellation', 'comments', '', 'comments');
		$fldBL->html_after_field='</span>';
		$frm->addSubmitButton('&nbsp;', 'btn_submit', 'Update');
		return $frm;
	}
	
	function view_request($srequest_id) {
		if ($this->canviewsuppapprequests != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("Supplier Approval Requests",Utilities::generateUrl("users","supplier_approval_requests"));
		$this->b_crumb->add("View Supplier Requests",'');
        $this->set('breadcrumb', $this->b_crumb->output());
		$uObj=new User();
		$supplier_request=$uObj->getUserSupplierRequests(array("id"=>$srequest_id,"pagesize"=>1));
		if($supplier_request==false) {
            Message::addErrorMessage('Please perform this action on valid request.');
			Utilities::redirectUser('users','supplier_approval_requests');
            //Utilities::redirectUserReferer();
        }
		$supplier_request["field_values"]=$uObj->getSupplierRequestFieldsValues($srequest_id);
		$this->set('supplier_request',$supplier_request);
		$frm=$this->getSupplierRequestForm();
		$this->set('frm',$frm);
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
					if($uObj->updateSupplierRequest($srequest_id,$post["status"],$post["comments"])){
							Message::addMessage('Success: Seller request updated successfully.');
							Utilities::redirectUser(Utilities::generateUrl('users', 'view_request', array($srequest_id)));
					}else{
							Message::addErrorMessage($oObj->getError());
						}
					}
			$frm->fill($post);
		}
		$this->_template->render();
	}
	
	function download_attachment($file) {
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),SUPPLIER_APPROVAL_REQUESTS)) {
             die(Admin::getUnauthorizedMsg());
        }
		if ($file){
			Utilities::outputFile("front-users/".$file,false,false,'',false);
		}else{
			Message::addErrorMessage('Error: File not found.');
			Utilities::redirectUser(Utilities::generateUrl('returnrequests'));
		}
    }
	
	function supplier_form_fields_autocomplete(){
		global $conf_supplier_form_field_types;
	    $post = Syspage::getPostedVar();
		$json = array();
		foreach($conf_supplier_form_field_types as $skey=>$sval){
			$json[] = array(
					'data' => $skey,
					'value'      => strip_tags(htmlentities($sval, ENT_QUOTES, 'UTF-8'))
				);
		}
		$sort_order = array();
		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}
		array_multisort($sort_order, SORT_ASC, $json);
		$arr["suggestions"]=$json;
		echo json_encode($arr);
		//echo json_encode($json);
	}
	
	
	
	function login($user_id) {
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),CUSTOMERS)) {
             die(Admin::getUnauthorizedMsg());
        }
		$userObj=new User();
		$user = $userObj->getUser(array('id'=>$user_id),true);
		if (!$user){
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
			Utilities::redirectUser(Utilities::generateUrl('users'));
		}
		if(!$userObj->login($user['user_username'], $user['user_password'],true,true) === true){
			Message::addErrorMessage($userObj->getError());
			Utilities::redirectUser(Utilities::generateUrl('users'));
		}
		Utilities::redirectUser(Utilities::generateUrl('account','',array(),CONF_WEBROOT_URL));
	}
	
	protected function getSearchSupplierRequestForm() {
		global $supplier_request_status;
        $frm=new Form('frmSearchSupplierRequests','frmSearchSupplierRequests');
		$frm->setFieldsPerRow(3);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('GET');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addHiddenField('', 'type', "B");
		$frm->addSelectBox('Status', 'status',$supplier_request_status,'' , 'class="small"','All');
		$fld=$frm->addDateField('Date From', 'date_from', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld=$frm->addDateField('Date To', 'date_to', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchSupplierRequests(this); return false;');
        return $frm;
    }
	
	function supplier_requests() {
        if ($this->canviewsupprequests != true) {
            $this->notAuthorized();
        }
        $frm = $this->getSearchSupplierRequestForm();
		$frm->fill(getQueryStringData());
        $this->set('frmPost', $frm);
		$this->b_crumb->add("Supplier Requests",'');
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listSupplierRequests($page = 1) {
        if ($this->canviewsupprequests != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
			$uObj=new User();
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
            $this->set('records', $uObj->getUserRequests($post));
            $this->set('pages', $uObj->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $uObj->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
	function update_supplier_request_status() {
		if ($this->canviewsupprequests != true) {
            $this->notAuthorized();
        }
		$post = Syspage::getPostedVar();
		$mod = $post['mod'];
        $id = intval($post['id']);
        $uObj=new User();	
		$user_request = $uObj->getUserRequestById(array('id'=>$id),false);
        if($user_request==false) {
            Message::addErrorMessage('Error: Please perform this action on valid record.');
            dieJsonError(Message::getHtml());
        }
		
        switch($mod) {
            case 'approve':
            	$data_to_update = array(
					'urequest_status'=>1,
	            );
            break;
            case 'decline':
    	        $data_to_update = array(
					'urequest_status'=>2,
            	);
            break;
           
        }
		if($uObj->updateUserRequestStatus($id,$data_to_update)){
			$emailNotificationObj=new Emailnotifications();
			$emailNotificationObj->SendUserRequestStatusChangeNotification($id);
			Message::addMessage('Success: Status has been modified successfully.');
			$arr = array('status'=>1, 'msg'=>Message::getHtml());
			die(convertToJson($arr));
		}else{
			Message::addErrorMessage($uObj->getError());
			dieJsonError(Message::getHtml());
		}
	}
	
	protected function getAdvertiserSearchForm() {
		global $user_status;
        $frm=new Form('frmAdvertiserSearch','frmAdvertiserSearch');
		$frm->setFieldsPerRow(4);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('GET');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addTextBox('Keyword', 'keyword','','',' class="small"');
		$frm->addSelectBox('Status', 'status',$user_status,'' , 'class="small"','All');
		$fld=$frm->addDateField('Date From', 'date_from', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld=$frm->addDateField('Date To', 'date_to', '', '', 'readonly="true" class="small dateTimeFld"');
		$frm->addTextBox('Balance From ['.CONF_CURRENCY_SYMBOL.']', 'minbalance','','',' class="small"')->requirements()->setFloatPositive($val=true);
		$frm->addTextBox('Balance To ['.CONF_CURRENCY_SYMBOL.']', 'maxbalance','','',' class="small"')->requirements()->setFloatPositive($val=true);
		$frm->addSelectBox('Type', 'type',array(CONF_BUYER_USER_TYPE=>"Buyer Only",CONF_SELLER_USER_TYPE=>"Seller Only",CONF_BUYER_SELLER_USER_TYPE=>"Buyer+Seller"),'' , 'class="small"','All');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchAdvertisers(this); return false;');
        return $frm;
    }
	
	
	function advertisers() {
        if ($this->canviewadvertisers != true) {
            $this->notAuthorized();
        }
        $frm = $this->getAdvertiserSearchForm();
        $this->set('frmPost', $frm);
		$this->b_crumb->add("Advertisers Management",'');
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listAdvertisers($page = 1) {
        if ($this->canviewadvertisers != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
			$uObj=new User();
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
			$post['type']=array("1");
            $post['pagesize'] = $pagesize;
            $this->set('records', $uObj->getUsers($post));
            $this->set('pages', $uObj->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $uObj->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
}