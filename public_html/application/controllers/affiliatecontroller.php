<?php
class AffiliateController extends CommonController{
	function __construct($model, $controller, $action){
		parent::__construct($model, $controller, $action);
		if ($this->Affiliate->isAffiliateLogged()) {
			$this->affiliate_details=$this->Affiliate->getAffiliateById($this->Affiliate->getLoggedAffiliateId(),array("status"=>1,"approved"=>1));
			if (empty($this->affiliate_details) && ($action!="logout")){
				Utilities::redirectUser(Utilities::generateUrl('affiliate','logout'));
			}
			$this->set('affiliate_details',$this->affiliate_details);	
		}
	}
	function default_action(){
		Utilities::redirectUser(Utilities::generateUrl('affiliate','account'));
	}
	function account($p){
		if (isset($p) && ($p=="p_c")){
			Message::addMessage(Utilities::getLabel('M_Your_password_changed_login'));	
			Utilities::redirectUser(Utilities::generateUrl('affiliate','account'));
		}
		if ($this->Affiliate->isAffiliateLogged()) {
			Utilities::redirectUser(Utilities::generateUrl('affiliate','dashboard'));
		}
		$loginFrm=$this->getLoginForm();
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_login'])){
			if(!$loginFrm->validate($post)){
				Message::addErrorMessage($loginFrm->getValidationErrors());
			}else{
// Check how many login attempts have been made.
				$login_info = $this->Affiliate->getLoginAttempts($post['username']);
				if ($login_info && ($login_info['alogin_total'] >= Settings::getSetting("CONF_MAX_LOGIN_ATTEMPTS")) && strtotime('-1 hour') < strtotime($login_info['alogin_date_modified'])) {
					Message::addErrorMessage(Utilities::getLabel('L_Warning_Max_Login_Attempts'));
					Utilities::redirectUser(Utilities::generateUrl('affiliate','account'));
				}
				$pwd = Utilities::encryptPassword($post['password']);
				if($this->Affiliate->login($post['username'], $post['password']) === true){
					$this->Affiliate->deleteLoginAttempts($post['username']);
					if(isset($post['remember'])){
						$remember_token = substr(md5(rand(1, 99999) . microtime()), 1, 25);
						$data = array('affiliate_id'=>$this->Affiliate->getAttribute("affiliate_id"), 'remember_token'=>$remember_token, 'token_expiry'=>date('Y-m-d H:i:s', strtotime("+10 DAYS")));
						if($this->Affiliate->updateRememberMeToken($data) === true){
							setcookie('remember_affiliate_token', $remember_token, time()+3600*24*10,'/');
						}
					}
					Utilities::redirectUserReferer();
				}else{
					$this->Affiliate->addLoginAttempt($post['username']);
					$loginFrm->fill($post);
					Message::addErrorMessage($this->Affiliate->getError());
					Utilities::redirectUserReferer();
				}
			}
		}
		$this->set('loginFrm', $loginFrm);
		$this->set('affiliate_commission', $this->Affiliate->getDefaultAffiliateCommission());
		$this->_template->render();	
	}
	public function logout($p){
		unset($_SESSION['logged_affiliate']);
		if ($this->Affiliate->deleteRememberMeToken($this->Affiliate->getLoggedAffiliateId())){
			setcookie('remember_affiliate_token', '', time()-3600,'/');
			Utilities::redirectUser(Utilities::generateUrl('affiliate','account',array($p)));
		}else{
			Message::addErrorMessage($this->Affiliate->getError());
		}
		Utilities::redirectUserReferer();
	}
	private function getRegistrationForm(){
		$frm=new Form('frmRegistration','frmStrengthPassword');
		$frm->setFieldsPerRow(2);
		$frm->setAction('?');
		$frm->setValidatorJsObjectName('system_validator');
		$frm->setExtra('class="siteForm form-double-cell"');
		$frm->captionInSameCell(true);
		$frm->addHtml('<span class="panelTitleHeading">'.Utilities::getLabel('L_Your_Personal_Details').'</span>', 'htmlNote','')->merge_cells=2;
		$fld=$frm->addTextBox('<label>'.Utilities::getLabel('F_Username').'</label>', 'affiliate_username', '', '', ' class="check_affiliate_username"');
		$fld->requirements()->setUsername(true);
		$fld->requirements()->setRequired(true);
		$fld->html_after_field='<span id="ajax_availability_username"></span>';
		$fld=$frm->addEmailField('<label>'.Utilities::getLabel('F_Email').'</label>', 'affiliate_email','', '', ' class="check_affiliate_email"');
		$fld->html_after_field='<span id="ajax_availability_email"></span>';
		$frm->addRequiredField('<label>'.Utilities::getLabel('F_Name').'</label>', 'affiliate_name');
		$fld_phn = $frm->addRequiredField('<label>'.Utilities::getLabel('F_Phone').'</label>', 'affiliate_phone');
		$fld_phn->requirements()->setRegularExpressionToValidate('^[\s()+-]*([0-9][\s()+-]*){5,20}$');
		$frm->addHtml('<span class="panelTitleHeading">'.Utilities::getLabel('L_Your_Address_Details').'</span>', 'htmlNote','')->merge_cells=2;
		$fld=$frm->addTextBox('<label>'.Utilities::getLabel('L_Company').'</label>', 'affiliate_company');
		$fld=$frm->addTextBox('<label>'.Utilities::getLabel('L_Website').'</label>', 'affiliate_website');
		$frm->addRequiredField('<label>'.Utilities::getLabel('L_ADDRESS_LINE_1').'</label>', 'affiliate_address_1');
		$frm->addTextBox('<label>'.Utilities::getLabel('L_ADDRESS_LINE_2').'</label>', 'affiliate_address_2');
		
		$countryObj=new Countries();
		$stateObj=new States();
		$countries = $countryObj->getAssociativeArray();
		$fld_country=$frm->addSelectBox('<label>'.Utilities::getLabel('M_Country').'</label>', 'affiliate_country', $countries, Settings::getSetting("CONF_COUNTRY"), 'onchange="loadStates(this);"', Utilities::getLabel('M_Country'), 'ua_country')->requirements()->setRequired(true);
		$frm->addSelectBox('<label>'.Utilities::getLabel('M_State_County_Province').'</label>', 'affiliate_state', $stateObj->getStatesAssoc(Settings::getSetting("CONF_COUNTRY")), '', '', Utilities::getLabel('M_State_County_Province'), 'ua_state')->requirements()->setRequired(true);
		
		$frm->addRequiredField('<label>'.Utilities::getLabel('L_CITY').'</label>', 'affiliate_city');
		$frm->addRequiredField('<label>'.Utilities::getLabel('M_POSTCODE_ZIP').'</label>', 'affiliate_postcode');
		
		$frm->addHtml('<span class="panelTitleHeading">'.Utilities::getLabel('L_Payment_Information').'</span>', 'htmlNote','')->merge_cells=2;
		$fld=$frm->addTextBox('<label>'.Utilities::getLabel('L_Tax_ID').'</label>', 'affiliate_tax');
		$frm->addRadioButtons('<label>'.Utilities::getLabel('L_Payment_Method').'</label>', 'affiliate_payment',array("cheque"=>Utilities::getLabel('L_Cheque'),"bank"=>Utilities::getLabel('L_Bank'),"paypal"=>Utilities::getLabel('L_PayPal')),"cheque",1,'width="15%" ');
		$fldPayment=$frm->addTextBox('<div class="payment" lang="payment-cheque"><label>'.Utilities::getLabel('L_Cheque_Payee_Name').'</label>', 'affiliate_cheque');
		$fldPayment->html_after_field='</div>';
		$fldPayment->merge_cells=2;
		$fldPaypal=$frm->addTextBox('<div class="payment" lang="payment-paypal"><label>'.Utilities::getLabel('L_PayPal_Email_Account').'</label>', 'affiliate_paypal');
		$fldPaypal->html_after_field='</div>';
		$fldPaypal->merge_cells=2;
		$frm->addTextBox('<div class="payment" lang="payment-bank"><label>'.Utilities::getLabel('L_Bank_Name').'</label>', 'affiliate_bank_name')->html_after_field='</div>';
		$frm->addTextBox('<div class="payment" lang="payment-bank"><label>'.Utilities::getLabel('L_ABA/BSB_number_Branch_Number').'</label>', 'affiliate_bank_branch_number')->html_after_field='</div>';
		$frm->addTextBox('<div class="payment" lang="payment-bank"><label>'.Utilities::getLabel('L_SWIFT_Code').'</label>', 'affiliate_bank_swift_code')->html_after_field='</div>';
		$frm->addTextBox('<div class="payment" lang="payment-bank"><label>'.Utilities::getLabel('L_Account_Name').'</label>', 'affiliate_bank_account_name')->html_after_field='</div>';
		$fldAn=$frm->addTextBox('<div class="payment" lang="payment-bank"><label>'.Utilities::getLabel('L_Account_Number').'</label>', 'affiliate_bank_account_number')->html_after_field='</div>';
		$frm->addHtml('<span class="panelTitleHeading">'.Utilities::getLabel('L_Your_Password').'</span>', 'htmlNote','')->merge_cells=2;
		$fld_password = $frm->addPasswordField('<label>'.Utilities::getLabel('M_Password').'</label>', 'affiliate_password', '', 'check-password', '');
		$fld_password->requirements()->setRequired(true);
		$fld_password->requirements()->setLength(4,20);
		$fld_password->html_before_field='<div id="check-password-result">';
		$fld_password->html_after_field='</div>';
		$fld = $frm->addPasswordField('<label>'.Utilities::getLabel('M_Confirm_new_password').'</label>', 'confirm_pwd', '', 'confirm_pwd');
		$fld->requirements()->setRequired(true);
		$fld->requirements()->setCompareWith('affiliate_password', 'eq');
		$fld=$frm->addHtml('&nbsp;','',sprintf(Utilities::getLabel('L_By_using_agree_terms_conditions'),'<a target="_blank" href="'.Utilities::generateUrl('cms', 'view',array(Settings::getSetting("CONF_AFFILIATES_TERMS"))).'">'.Utilities::getLabel('L_Terms_Conditions').'</a>'))->merge_cells=2;
		$fld=$frm->addSubmitButton('','btn_signup',Utilities::getLabel('BTN_REGISTER'));
		$frm->setJsErrorDisplay('afterfield');
		$frm->setLeftColumnProperties('width="25%"');
		$frm->setTableProperties('width="100%" border="0" class="tbl-twocell" cellpadding="0" cellspacing="0"');
		return $frm;
	}
	private function getLoginForm() {
		$frm=new Form('frmLogin');
		$frm->setAction('?');
		$frm->setExtra('class="siteForm"');
		$frm->captionInSameCell(true);
		$redirect_url = Utilities::getSessionRedirectUrl();
		Utilities::unsetSessionRedirectUrl();
		$frm->addHiddenField('', 'redirect_url', $redirect_url);
//$frm->setRequiredStarWith('not-required');
		$fld = $frm->addRequiredField(Utilities::getLabel('L_Affiliate_Username_or_Email'), 'username','','username','placeholder="'.Utilities::getLabel('L_Affiliate_Username_or_Email').'"');
		$fld=$frm->addPasswordField(Utilities::getLabel('L_Affiliate_Password'), 'password','','password','placeholder="'.Utilities::getLabel('L_Affiliate_Password').'"');
		$fld->requirements()->setRequired(true);
		$frm->addHtml('', '','<div class="remember">
			<input type="checkbox" name="remember" value="1">
			'.Utilities::getLabel('L_Remember_Me').' <br>
			<p><a href="'.Utilities::generateUrl('affiliate', 'forgot_password').'">'.Utilities::getLabel('L_FORGOT_PASSWORD').'</a></p>
		</div>
		<input type="submit" name="btn_login" value="'.Utilities::getLabel('L_LOGIN_NOW').'" class="form-submit">');
		$frm->setValidatorJsObjectName('frm_validator');
		$frm->setJsErrorDisplay('afterfield');
		return $frm;
	}
	
	private function getQuickLoginForm() {
		$frm=new Form('frmLogin');
		$frm->setFieldsPerRow(3);
		$frm->setAction(Utilities::generateUrl('affiliate','account'));
		$frm->setExtra('class="siteForm seller-login form-double-cell"');
		$frm->setRequiredStarPosition('N');
		$frm->captionInSameCell(false);
		$redirect_url = Utilities::getSessionRedirectUrl();
		Utilities::unsetSessionRedirectUrl();
		$frm->addHiddenField('', 'redirect_url', $redirect_url);
		$fld = $frm->addRequiredField('', 'username','','username','placeholder="'.Utilities::getLabel('L_Username_or_Email').'" title="'.Utilities::getLabel('L_Username_or_Email').'"');
		$fld=$frm->addPasswordField('', 'password','','password','placeholder="'.Utilities::getLabel('L_Password').'" title="'.Utilities::getLabel('L_Password').'"');
		$fld->requirements()->setRequired(true);
		$fld=$frm->addSubmitButton('','btn_login',Utilities::getLabel('L_LOGIN_NOW'));
		$frm->addHtml('&nbsp;','forgot','<a href="'.Utilities::generateUrl('affiliate', 'forgot_password').'" class="forgot fl">'.Utilities::getLabel('L_FORGOT_PASSWORD').'</a>');
		$frm->setValidatorJsObjectName('frm_validator');
		$frm->setJsErrorDisplay('afterfield');
		return $frm;
	}
	
	function registration(){
		if ($this->Affiliate->isAffiliateLogged()) {
			Utilities::redirectUser(Utilities::generateUrl('affiliate','dashboard'));
		}
		$quickLoginFrm=$this->getQuickLoginForm();
		$registrationFrm=$this->getRegistrationForm();
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_signup'])){
			if(!$registrationFrm->validate($post)){
				Message::addErrorMessage($registrationFrm->getValidationErrors());
			}else{
				if($this->Affiliate->addAffiliate($post)){
					Message::addMessage(Utilities::getLabel('L_Affiliate_Registered_Successfully'));
					Utilities::redirectUser(Utilities::generateUrl('affiliate','success'));
				}else{
					Message::addErrorMessage($this->Affiliate->getError());
				}
			}
		}
		$this->set('quickLoginFrm', $quickLoginFrm);
		$this->set('RegistrationFrm', $registrationFrm);
		$this->_template->render(false,false);	
	}
	function success(){
		if (Settings::getSetting("CONF_AFFILIATES_REQUIRES_APPROVAL")) {
			$text_message = sprintf(Utilities::getLabel('L_AFFILIATE_ACCOUNT_REGISTRATION_APPROVAL_MESSAGE'), Settings::getSetting("CONF_WEBSITE_NAME"), Utilities::generateAbsoluteUrl('custom', 'contact_us'));
		} else {
			$text_message = sprintf(Utilities::getLabel('L_AFFILIATE_ACCOUNT_REGISTRATION_SUCCESS_MESSAGE'), Settings::getSetting("CONF_WEBSITE_NAME"), Utilities::generateAbsoluteUrl('custom', 'contact_us'));
		}
		$this->set('text_message', $text_message);
		$this->_template->render();	
	}
	function forgot_password(){
		if ($this->Affiliate->isAffiliateLogged()) {
			Utilities::redirectUser(Utilities::generateUrl('affiliate','dashboard'));
		}
		$frm = new Form('frmForgotPassword');
		$fld = $frm->addRequiredField('<label>'.Utilities::getLabel('F_EMAIL_OR_USERNAME').'</label>', 'affiliate_email_username', '', 'affiliate_email_username');
		if (!empty(CONF_RECAPTACHA_SITEKEY)){
			$frm->addHtml('', 'htmlNote','<div class="g-recaptcha" data-sitekey="'.CONF_RECAPTACHA_SITEKEY.'"></div>');
		}
		$fld=$frm->addSubmitButton('&nbsp;','btn_submit',Utilities::getLabel('btn_submit'),'',' title="'.Utilities::getLabel('btn_submit').'" ');
		$fld->html_before_field = '<div class="fieldrw">';
		$fld->html_after_field = ' <a href="'.Utilities::generateUrl('affiliate', 'account').'" class="btn gray">'.Utilities::getLabel('F_Back_to_Affiliate_Login').'</a></div>';
		$fld->merge_cells=2;
		$frm->setTableProperties(' width="100%" border="0" class="tableform"');
		$frm->setExtra('class="siteForm"');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setRequiredStarWith('x');
		$frm->captionInSameCell(true);
		$frm->setFieldsPerRow(1);
		$this->set('frm', $frm);
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			$frm->fill($post);
			if(!$frm->validate($post)) {
				Message::addErrorMessage($frm->getValidationErrors());
			} else if(!Utilities::verifyCaptcha()) {
				Message::addErrorMessage(Utilities::getLabel('M_ERROR_PLEASE_VERIFY_YOURSELF'));
			} else{
				$affiliate = $this->Affiliate->getAffiliate(array('affiliate_email_username'=>$post['affiliate_email_username']));
				if(!$affiliate){
					Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_EMAIL_USERNAME'));
				}elseif($affiliate['affiliate_status'] != 1){
					Message::addErrorMessage(Utilities::getLabel('M_ERROR_YOUR_ACCOUNT_NOT_ACTIVE'));
				}elseif($affiliate['affiliate_is_deleted'] == 1){
					Message::addErrorMessage(Utilities::getLabel('M_ERROR_YOUR_ACCOUNT_IS_DELETED'));
				}elseif(!$this->Affiliate->canResetPassword($affiliate['affiliate_id'])){
					Message::addErrorMessage(Utilities::getLabel('M_WARNING_FORGOT_PASSWORD_DUPLICATE_REQUEST'));
				}else{
					$reset_token = substr(md5(rand(1, 99999) . microtime()), 1, 25);
					$data = array('affiliate_id'=>$affiliate['affiliate_id'], 'reset_token'=>$reset_token, 'token_expiry'=>date('Y-m-d H:i:s', strtotime("+24 HOUR")));
					if($this->Affiliate->updateForgotRequest($data) === true){
						$reset_url = Utilities::generateAbsoluteUrl('affiliate', 'reset_password', array($affiliate["affiliate_id"].".".$reset_token));
						$website_name = Settings::getSetting("CONF_WEBSITE_NAME");
						$website_url = Utilities::getUrlScheme();
						if (Utilities::sendMailTpl($affiliate['affiliate_email'], 'forgot_password', array(
							'{reset_url}' => $reset_url,
							'{website_name}' => $website_name,
							'{website_url}' => $website_url,
							'{site_domain}' => CONF_SERVER_PATH,
							'{user_full_name}' => trim($affiliate['affiliate_name']),
							))){
							Message::addMessage(Utilities::getLabel('M_SUCCESS_FORGOT_PASSWORD_REQUEST'));
						Utilities::redirectUser(Utilities::generateUrl('affiliate','forgot_password'));
					}else{
						Message::addErrorMessage(Utilities::getLabel('M_email_not_sent_server_issue'));
					}
				}else{
					Message::addErrorMessage($this->User->getError());
				}
			}
		}
	}
	$this->_template->render();	
}
function reset_password($param) {
	if ($this->Affiliate->isAffiliateLogged()) {
		Utilities::redirectUser(Utilities::generateUrl('affiliate','dashboard'));
	}
	$attr = explode(".",$param);
	$affiliate_id = intval($attr[0]);
	$token = trim($attr[1]);
	if($affiliate_id < 1 || strlen($token) != 25){
		Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
		Utilities::redirectUser(Utilities::generateUrl('affiliate','account'));
	}
	if(!$affiliate_reset_pwd_data = $this->Affiliate->validateToken($affiliate_id, $token)){
		Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_TOCKEN'));
		Utilities::redirectUser(Utilities::generateUrl('affiliate','account'));
	}
	if(!$affiliate = $this->Affiliate->getAffiliate(array('id'=>$affiliate_reset_pwd_data["aprr_affiliate_id"]))){
		Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
		Utilities::redirectUser(Utilities::generateUrl('affiliate','account'));
	}
	if($affiliate['affiliate_status'] != 1){
		Message::addErrorMessage(Utilities::getLabel('M_ERROR_YOUR_ACCOUNT_NOT_ACTIVE'));
		Utilities::redirectUser(Utilities::generateUrl('affiliate','account'));
	}
	if($affiliate['affiliate_is_deleted'] == 1){
		Message::addErrorMessage(Utilities::getLabel('M_ERROR_YOUR_ACCOUNT_IS_DELETED'));
		Utilities::redirectUser(Utilities::generateUrl('affiliate','account'));
	}
	$frm = $this->getResetPwdForm(); 
	$post = Syspage::getPostedVar();
	if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
		if(!$frm->validate($post)){
			Message::addErrorMessage($frm->getValidationErrors());
		}else{
			if($this->Affiliate->updatePassword(intval($affiliate['affiliate_id']), $post['affiliate_password'])){
				Message::addMessage(Utilities::getLabel('M_Password_successfully_updated'));
				Utilities::redirectUser(Utilities::generateUrl('affiliate', 'account'));
			}else{
				Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
			}
		}
	}
	$frm->fill(array('affiliate_id'=>$affiliate_id));
	$this->set('frm', $frm);
	$this->_template->render();	
}
private function getResetPwdForm(){
	$frm = new Form('frmResetPassword','frmStrengthPassword');
	$frm->setTableProperties(' width="100%" border="0" class="tableform"');
	$frm->setExtra('class="siteForm"');
	$frm->captionInSameCell(true);
//$frm->setRequiredStarWith('not-required');
	$frm->addHiddenField('', 'affiliate_id', '', 'affiliate_id');
	$fld = $frm->addPasswordField("<label>".Utilities::getLabel('f_new_password')."</label>", 'affiliate_password','','check-password');
	$fld->requirements()->setRequired();
	$fld->requirements()->setLength(4, 20);
	$fld->html_before_field='<div id="check-password-result">';
	$fld->html_after_field='</div>';
	$fld1 = $frm->addPasswordField("<label>".Utilities::getLabel('f_Confirm_new_password')."</label>", 'affiliate_password1');
	$fld1->requirements()->setRequired();
	$fld1->requirements()->setCompareWith('affiliate_password');
	$fld=$frm->addSubmitButton('&nbsp;','btn_submit',Utilities::getLabel('btn_submit'));
	$fld->html_before_field = '<div class="fieldrw">';
	$fld->html_after_field = '</div>';
	$frm->setJsErrorDisplay('afterfield');	
	return $frm;
}
function cryptPwd($str){
	return crypt($str, 'NxhPwrR07zYijkhgdfg46M2fad9a5189454d05879a76f5e8b569xf2CVo6JpNxhPwr587988a76f5e');
}
function check_username_availability(){
	$post = Syspage::getPostedVar();
	$username=$post["username"];
	if (preg_match('/^[A-Za-z][A-Za-z0-9_.]{3,20}$/', $username) ){
		$affiliate = $this->Affiliate->getAffiliate(array('affiliate_username'=>$username),false,true);
		if ($affiliate){
			$userCheck=2;
			$message=sprintf(Utilities::getLabel('F_Username_not_available'),$username);
		}else{
			$userCheck=1;
			$message=sprintf(Utilities::getLabel('F_Username_available'),$username);
		}
	}
	$arr=array("check"=>$userCheck,"message"=>$message);
	echo json_encode($arr);
}
function check_email_availability(){
	$post = Syspage::getPostedVar();
	$email=$post["email"];
	if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$affiliate = $this->Affiliate->getAffiliate(array('affiliate_email'=>$email),false,true);
		if ($affiliate){
			$userCheck=2;
			$message=sprintf(Utilities::getLabel('F_Email_not_available'),$email);
		}else{
			$userCheck=1;
			$message=sprintf(Utilities::getLabel('F_Email_available'),$email);
		}
	}
	$arr=array("check"=>$userCheck,"message"=>$message);
	echo json_encode($arr);
}
function dashboard($p){
	if (!$this->Affiliate->isAffiliateLogged()) {
		Utilities::redirectUser(Utilities::generateUrl('affiliate','account'));
	}
	$orderObj=new Orders();
	$affiliate_orders=$orderObj->getChildOrders(array("affiliate"=>$this->affiliate_details["affiliate_id"],"payment_status"=>1,"pagesize"=>5));
	$this->set('affiliate_orders',$affiliate_orders);
	$this->set('short_header_footer',1);
	$this->_template->render();	
}
function orders($page){
	if (!$this->Affiliate->isAffiliateLogged()) {
		Utilities::redirectUser(Utilities::generateUrl('affiliate','account'));
	}
	$orderObj=new Orders();
	$this->set('short_header_footer',1);
	$post = Syspage::getPostedVar();
	$page = intval($page);
	if ($page < 1)
		$page = 1;
	$pagesize = 10;
	$sts=in_array($_REQUEST["sts"],array("pending","received","all"))?$_REQUEST["sts"]:"all";
	switch($sts) {
		case "received":
		$order_status=(array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS");
		break;
		case "pending":
		$order_status=$orderObj->getAffiliatePendingOrderStatuses();
		break;	
	}
	$criterias=array("affiliate"=>$this->affiliate_details["affiliate_id"],"payment_status"=>1,"pagesize"=>$pagesize,"page"=>$page,"status"=>$order_status,"status_not"=>$order_status_not);
	$affiliate_orders=$orderObj->getChildOrders($criterias);
	$this->set('affiliate_orders',$affiliate_orders);
	$this->set('pages', $orderObj->getTotalPages());
	$this->set('page', $page);
	$this->set('start_record', ($page-1)*$pagesize + 1);
	$end_record = $page * $pagesize;
	$total_records = $orderObj->getTotalRecords();
	if ($total_records < $end_record) $end_record = $total_records;
	$this->set('end_record', $end_record);
	$this->set('total_records', $total_records);
	$this->set('search_parameter',array("sts"=>$sts));
	$this->set('frm',$frmSearchForm);
	$this->set('sts', $sts);
	$this->_template->render();
}
function profile_info(){
	if (!$this->Affiliate->isAffiliateLogged()) {
		Utilities::redirectUser(Utilities::generateUrl('affiliate','account'));
	}
	$this->set('short_header_footer',1);
	Syspage::addJs(array('js/jquery.form.js'), false);
	Syspage::addJs(array('js/cropper.js'), false);
	Syspage::addCss(array('css/cropper.css'), false);
	$frmProfileImage = $this->getProfileImageForm();
	$frm = $this->getProfileInfoForm();
	$post = Syspage::getPostedVar();
	if($_SERVER['REQUEST_METHOD'] == 'POST' &&  isset($post) && !empty($post)){
		if($post['action'] == "demo_avatar"){
			if(($_FILES['affiliate_profile_image']['size']!=0) && ($_FILES['affiliate_profile_image']['size'] < 1000000)) {
				if (Utilities::isUploadedFileValidImage($_FILES['affiliate_profile_image'])){
					if(Utilities::saveImage($_FILES['affiliate_profile_image']['tmp_name'],$_FILES['affiliate_profile_image']['name'], $response, 'affiliates/')){	
						dieJsonSuccess(Utilities::generateUrl("image","affiliate_photo",array($response)));
					}
				}else{
					dieJsonError(Utilities::getLabel('M_ERROR_INVALID_FILETYPE'));
				}
			}else{
				dieJsonError(Utilities::getLabel('M_ERROR_FILE_SIZE'));
			}
/*
if(Utilities::saveImage($_FILES['affiliate_profile_image']['tmp_name'],$_FILES['affiliate_profile_image']['name'], $response, 'affiliates/')){	
dieJsonSuccess(Utilities::generateUrl("image","affiliate",array($response)));
}
*/}
if($post['action'] == "avatar"){
	if(Utilities::saveImage($_FILES['affiliate_profile_image']['tmp_name'],$_FILES['affiliate_profile_image']['name'], $response, 'affiliates/')){	  
		$data = json_decode(stripslashes($post['img_data']));
		Utilities::crop($data,CONF_INSTALLATION_PATH . 'user-uploads/affiliates/'.$response);
		$post['affiliate_profile_image'] = $response;
	}
	$arr = array_merge($post,array("affiliate_id"=>$this->affiliate_details["affiliate_id"]));
	if($this->Affiliate->updateAffiliate($arr)){
		Message::addMessage(Utilities::getLabel('M_MSG_Your_Account_Details_Updated'));
		Utilities::redirectUser();
	}else{
		Message::addErrorMessage(Utilities::getLabel('M_Error_details_not_saved'));
	}
}
}
$frmProfileImage->getField('affiliate_profile_image')->html_before_field = '<div class="uploadedphoto"><img alt="" src="'. Utilities::generateUrl('image', 'affiliate', array($this->affiliate_details['affiliate_profile_image'],'SMALL')) .'" id="dpic" />'.((strlen($this->affiliate_details['affiliate_profile_image']))?'':'').'</div><div class="choose_file"><span>'.Utilities::getLabel('M_Change_Photo').'</span>';
$afterText = (strlen($this->affiliate_details['affiliate_profile_image']))?'</div> <div class="marginLeft"><a href="#" id="picRemove">'.Utilities::getLabel('M_Remove').'</a></div>':'</div>';
$frmProfileImage->getField('affiliate_profile_image')->html_after_field = $afterText;
$frm->fill($this->affiliate_details);
$this->set('frm', $frm);
$frmProfileImage->fill($this->affiliate_details);
$this->set('frmProfileImage', $frmProfileImage);
$this->_template->render();	
}
private function getProfileImageForm(){
	$frm = new Form('frmProfile', 'frmProfile');
	$frm->setExtra('class="siteForm"');
	$frm->setTableProperties('class="tbl-twocell"');
	$frm->setLeftColumnProperties('width="30%"');
	$frm->setJsErrorDisplay('afterfield');
	$frm->setRequiredStarWith('caption');
	$fld=$frm->addFileUpload('<label>'.Utilities::getLabel('M_Profile_Picture').'</label>', 'affiliate_profile_image', 'affiliate_profile_image','onchange = "popupImage(this)" class="upload"');
	$frm->addHiddenField('', 'update_profile_img', Utilities::getLabel('M_Update_Profile_Picture'), 'update_profile_img');
	$frm->addHiddenField('', 'rotate_left', Utilities::getLabel('M_Rotate_Left'), 'rotate_left');
	$frm->addHiddenField('', 'rotate_right', Utilities::getLabel('M_Rotate_Right'), 'rotate_right');
	$frm->addHiddenField('', 'remove_profile_img', '0', 'remove_profile_img');
	$frm->addHiddenField('', 'action', 'avatar', 'avatar-action');
	$frm->addHiddenField('', 'img_data', '', 'img-data');
	return $frm;
}
private function getProfileInfoForm(){
	$frm = new Form('frmProfileInfo', 'frmProfileInfo');
	$frm->setExtra('class="siteForm"');
	$frm->setTableProperties('class="tbl-twocell"');
	$frm->setLeftColumnProperties('width="30%"');
	$frm->setJsErrorDisplay('afterfield');
	$frm->setRequiredStarWith('caption');
	$frm->addHtml('<span class="panelTitleHeading">'.Utilities::getLabel('L_Personal_Information').'</span>', 'htmlNote','')->merge_caption=true;
	$frm->addHtml('<label>'.Utilities::getLabel('L_Username').'</label>', 'affiliate_username');
	$frm->addHtml('<label>'.Utilities::getLabel('L_Email').'</label>', 'affiliate_email');
	$frm->addRequiredField('<label>'.Utilities::getLabel('F_Name').'</label>', 'affiliate_name');
	$fld_phn = $frm->addRequiredField('<label>'.Utilities::getLabel('F_Phone').'</label>', 'affiliate_phone');
	$fld_phn->requirements()->setRegularExpressionToValidate('^[\s()+-]*([0-9][\s()+-]*){5,20}$');
	$frm->addHtml('<span class="panelTitleHeading">'.Utilities::getLabel('L_Your_Address_Details').'</span>', 'htmlNote','')->merge_caption=true;
	$fld=$frm->addTextBox('<label>'.Utilities::getLabel('L_Company').'</label>', 'affiliate_company');
	$fld=$frm->addTextBox('<label>'.Utilities::getLabel('L_Website').'</label>', 'affiliate_website');
	$frm->addRequiredField('<label>'.Utilities::getLabel('L_ADDRESS_LINE_1').'</label>', 'affiliate_address_1');
	$frm->addTextBox('<label>'.Utilities::getLabel('L_ADDRESS_LINE_2').'</label>', 'affiliate_address_2');
	
	$countryObj=new Countries();
	$stateObj=new States();
	$countries = $countryObj->getAssociativeArray();
	$fld_country=$frm->addSelectBox('<label>'.Utilities::getLabel('M_Country').'</label>', 'affiliate_country', $countries, Settings::getSetting("CONF_COUNTRY"), 'onchange="loadStates(this);"', Utilities::getLabel('M_Country'), 'ua_country')->requirements()->setRequired(true);
	$frm->addSelectBox('<label>'.Utilities::getLabel('M_State_County_Province').'</label>', 'affiliate_state', $stateObj->getStatesAssoc(Settings::getSetting("CONF_COUNTRY")), '', '', Utilities::getLabel('M_State_County_Province'), 'ua_state')->requirements()->setRequired(true);
	$frm->addRequiredField('<label>'.Utilities::getLabel('L_CITY').'</label>', 'affiliate_city');
	$frm->addRequiredField('<label>'.Utilities::getLabel('M_POSTCODE_ZIP').'</label>', 'affiliate_postcode');
	$fld_submit = $frm->addSubmitButton(null, 'btn_submit', Utilities::getLabel('M_Save_Changes'), 'btn_submit', ' class="orgbutton"');
	$frm->addHiddenField('', 'action', 'avatar');
	return $frm;
}
function payment_info(){
	if (!$this->Affiliate->isAffiliateLogged()) {
		Utilities::redirectUser(Utilities::generateUrl('affiliate','account'));
	}
	$this->set('short_header_footer',1);
	$frm = $this->getPaymentInfoForm();
	$post = Syspage::getPostedVar();
	if($_SERVER['REQUEST_METHOD'] == 'POST' &&  isset($post) && !empty($post)){
		$arr = array_merge($post,array("affiliate_id"=>$this->affiliate_details["affiliate_id"]));
		if($this->Affiliate->updateAffiliate($arr)){
			Message::addMessage(Utilities::getLabel('M_Your_Payment_Details_Updated'));
			Utilities::redirectUser();
		}else{
			Message::addErrorMessage(Utilities::getLabel('M_Error_details_not_saved'));
		}
	}
	$frm->fill($this->affiliate_details);
	$this->set('frm', $frm);
	$this->_template->render();	
}
private function getPaymentInfoForm(){
	$frm = new Form('frmPaymentInfo', 'frmPaymentInfo');
	$frm->setExtra('class="siteForm"');
	$frm->setTableProperties('class="tbl-twocell"');
	$frm->setLeftColumnProperties('width="30%"');
	$frm->setJsErrorDisplay('afterfield');
	$frm->setRequiredStarWith('caption');
	$fld=$frm->addTextBox('<label>'.Utilities::getLabel('L_Tax_ID').'</label>', 'affiliate_tax');
	$frm->addRadioButtons('<label>'.Utilities::getLabel('L_Payment_Method').'</label>', 'affiliate_payment',array("cheque"=>Utilities::getLabel('L_Cheque'),"bank"=>Utilities::getLabel('L_Bank'),"paypal"=>Utilities::getLabel('L_PayPal')),"cheque",1,'width="15%" ');
	$fldPayment=$frm->addTextBox('<div class="payment" lang="payment-cheque"><label>'.Utilities::getLabel('L_Cheque_Payee_Name').'</label>', 'affiliate_cheque');
	$fldPayment->html_after_field='</div>';
	$fldPaypal=$frm->addTextBox('<div class="payment" lang="payment-paypal"><label>'.Utilities::getLabel('L_PayPal_Email_Account').'</label>', 'affiliate_paypal');
	$fldPaypal->html_after_field='</div>';
	$frm->addTextBox('<div class="payment" lang="payment-bank"><label>'.Utilities::getLabel('L_Bank_Name').'</label>', 'affiliate_bank_name')->html_after_field='</div>';
	$frm->addTextBox('<div class="payment" lang="payment-bank"><label>'.Utilities::getLabel('L_ABA/BSB_number_Branch_Number').'</label>', 'affiliate_bank_branch_number')->html_after_field='</div>';
	$frm->addTextBox('<div class="payment" lang="payment-bank"><label>'.Utilities::getLabel('L_SWIFT_Code').'</label>', 'affiliate_bank_swift_code')->html_after_field='</div>';
	$frm->addTextBox('<div class="payment" lang="payment-bank"><label>'.Utilities::getLabel('L_Account_Name').'</label>', 'affiliate_bank_account_name')->html_after_field='</div>';
	$fldAn=$frm->addTextBox('<div class="payment" lang="payment-bank"><label>'.Utilities::getLabel('L_Account_Number').'</label>', 'affiliate_bank_account_number')->html_after_field='</div>';
	$fld_submit = $frm->addSubmitButton(null, 'btn_submit', Utilities::getLabel('M_Save_Changes'), 'btn_submit', ' class="orgbutton"');
	return $frm;
}
function change_password(){
	if (!$this->Affiliate->isAffiliateLogged()) {
		Utilities::redirectUser(Utilities::generateUrl('affiliate','account'));
	}
	$this->set('short_header_footer',1);
	$frm = $this->getPasswordForm();
	$post = Syspage::getPostedVar();
	if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
		if(!$frm->validate($post)){
			Message::addErrorMessage($frm->getValidationErrors());
		}else{
			$arr=array_merge($post,array("affiliate_id"=>$this->affiliate_details["affiliate_id"]));
			if($this->Affiliate->savePassword($arr)){
				Message::addMessage(Utilities::getLabel('M_Your_password_has_been_updated'));
				if (Settings::getSetting("CONF_AUTO_LOGOUT_PASSWORD_CHANGE")){
					Utilities::redirectUser(Utilities::generateUrl('affiliate', 'logout',array('p_c')));
				}
				Utilities::redirectUser();
			}else{
				Message::addErrorMessage($this->Affiliate->getError());
			}
		}
	}
	$this->set('frmPwd', $frm);
	$this->_template->render();	
}
private function getPasswordForm(){
	$frm = new Form('frmPassword', 'frmStrengthPassword');
	$frm->setExtra('class="siteForm"');
	$frm->setTableProperties('class="tbl-twocell"');
	$frm->setLeftColumnProperties('width="30%"');
	$frm->setJsErrorDisplay('afterfield');
	$frm->setRequiredStarWith('caption');
	$frm->addPasswordField('<label>'.Utilities::getLabel('M_Current_Password').'</label>', 'current_pwd', '', 'current_pwd')->requirements()->setRequired(true);
	$fld=$frm->addPasswordField('<label>'.Utilities::getLabel('M_New_password').'</label>', 'new_pwd', '', 'check-password');
	$fld->requirements()->setRequired(true);
	$fld->requirements()->setLength(4,20);
	$fld->html_before_field='<div id="check-password-result">';
	$fld->html_after_field='</div>';
	$fld = $frm->addPasswordField('<label>'.Utilities::getLabel('M_Confirm_new_password').'</label>', 'confirm_pwd', '', 'confirm_pwd');
	$fld->requirements()->setRequired(true);
	$fld->requirements()->setCompareWith('new_pwd', 'eq');
	$frm->addSubmitButton('', 'btn_submit', Utilities::getLabel('M_Save_Changes'), 'btn_submit');
	return $frm;
}
private function getFriendsSharingForm(){
	$frm=new Form('frmCustomShare','frmCustomShare');
	$frm->setOnSubmit('return(false);');
	$frm->setRequiredStarWith('caption');
	$frm->setValidatorJsObjectName('frmValidator');
	$frm->setExtra('class="siteForm" rel="action" validator="frmValidator"');
	$frm->setAction(Utilities::generateUrl('affiliate','send_email'));
	$frm->captionInSameCell(true);
	$frm->setFieldsPerRow(1);
	$frm->setLeftColumnProperties('valign="top" width="30%"');
	$frm->setTableProperties(' width="100%" border="0" class="formtable"');
	$fld=$frm->addTextArea(Utilities::getLabel('L_Friends_Email').' <small>('.Utilities::getLabel('L_Use_commas_separate_emails').')</small>', 'email');
	$fld->requirements()->setRequired();
	$fld->requirements()->setCustomErrorMessage(Utilities::getLabel('L_Friends_Email_Mandatory'));
	$frm->addTextArea(Utilities::getLabel('L_Personal_Message'), 'message');
	$frm->addSubmitButton('','btn_submit',Utilities::getLabel('L_Invite_Your_Friends'),'');
	$frm->setJsErrorDisplay('afterfield');
	return $frm;
}
function sharing(){
	require_once (CONF_INSTALLATION_PATH . 'public/includes/APIs/twitter/twitteroauth.php');
	if (!$this->Affiliate->isAffiliateLogged()) {
		Utilities::redirectUser(Utilities::generateUrl('affiliate','account'));
	}
	$this->set('affiliate_tracking_url',$this->affiliate_tracking_url($this->affiliate_details["affiliate_code"]));
	$this->set('short_header_footer',1);
	$this->set('sharingfrm',$this->getFriendsSharingForm());
	$this->_template->render();	
}
function send_email(){
	$post=getPostedData();
	$email=Utilities::multipleExplode(array(",",";","\t","\n"),trim($post["email"],","));
	$message=$post['message']!=""?$post['message']:"-NA-";
	if (count($email) && !empty($email)){
		$Personal_Message=empty($message)?"":"<b>".Utilities::getLabel('L_Comments_from_sender').":</b> ".nl2br($message);
		foreach($email as $email_id) {
			$email_id = trim($email_id);
			if(!Utilities::isValidEmail($email_id)) continue;
			$rs = Utilities::sendMailTpl($email_id, 'invitation_email', array(
				'{Sender_Name}' => $this->affiliate_details['affiliate_name'],
				'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
				'{Tracking_URL}' => $this->affiliate_tracking_url($this->affiliate_details["affiliate_code"]),
				'{Invitation_Message}' => $Personal_Message,
				));
		}
	}
	echo json_encode(array("status"=>1,"message"=>Utilities::getLabel('L_We_sent_invitation_emails')));
}
function twitter_callback(){
	require_once (CONF_INSTALLATION_PATH . 'public/includes/APIs/twitter/twitteroauth.php');
	$get = getQueryStringData();
	if (!empty($get['oauth_verifier']) && !empty($_SESSION['oauth_token']) && !empty($_SESSION['oauth_token_secret'])) {
// We've got everything we need
		$twitteroauth = new TwitterOAuth(Settings::getSetting("CONF_TWITTER_API_KEY"), Settings::getSetting("CONF_TWITTER_API_SECRET"), $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
// Let's request the access token
		$access_token = $twitteroauth->getAccessToken($get['oauth_verifier']);
// Save it in a session var
		$_SESSION['access_token'] = $access_token;
// Let's get the user's info
		$twitter_info = $twitteroauth->get('account/verify_credentials');
//$twitter_info->id
		$anchor_tag=$this->affiliate_tracking_url($this->affiliate_details["affiliate_code"]);
		$urlapi = "http://tinyurl.com/api-create.php?url=".$anchor_tag;
		/*** activate cURL for URL shortening ***/
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $urlapi);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$shorturl = curl_exec($ch);
		curl_close($ch);
		$anchor_length=strlen($shorturl);
//$message = substr($shorturl." Twitter Message will go here ",0,(140-$anchor_length-6));
		$message = substr($shorturl." ".sprintf(Settings::getSetting("CONF_SOCIAL_FEED_TWITTER_POST_TITLE"),Settings::getSetting("CONF_WEBSITE_NAME")),0,134-$anchor_length);
		$image_path = CONF_USER_UPLOADS_PATH.Settings::getSetting("CONF_SOCIAL_FEED_IMAGE");
		$handle = fopen($image_path,'rb');
		$image = fread($handle,filesize($image_path));
		fclose($handle);
		$parameters = array('media[]' => "{$image};type=image/jpeg;filename={$image_path}",'status' => $message);
		$post = $twitteroauth->post('statuses/update_with_media', $parameters, true);
		if (isset($post->errors)) {
			?>
			<script type="text/javascript">
				opener.location.reload();
  // or opener.location.href = opener.location.href;
				window.close();
  // or self.close();
			</script>
			<?
		} else{
			?>
			<script type="text/javascript">
				close();
				window.opener.twitter_shared();
			</script>
			<?
		}
	}
}
function referral($tracking_code){
	if (isset($tracking_code)) {
		setcookie('tracking', $tracking_code, time() + 3600 * 24 * 1000, '/');
		setcookie('referrer_tracking', '', time()-3600,'/');
		Utilities::redirectUser(Utilities::getSiteUrl());
	}else{
		die("Invalid Action");
	}
}
function affiliate_tracking_url($code){
	return Utilities::generateAbsoluteUrl('affiliate','referral',array($code));
}
function credits($page){
	if (!$this->Affiliate->isAffiliateLogged()) {
		Utilities::redirectUser(Utilities::generateUrl('affiliate','account'));
	}
	$this->set('short_header_footer',1);
	$page = intval($page);
	if ($page < 1)
		$page = 1;
	$pagesize = 10;
	$criterias=array_merge(array("affiliate"=>$this->Affiliate->getLoggedAffiliateId()),array("page"=>$page));
	$aftxnObj=new Affiliatetransactions();
	$transactions=$aftxnObj->getTransactions($criterias,$pagesize);
	$this->set('my_credits',$transactions);
	$this->set('pages', $aftxnObj->getTotalPages());
	$this->set('page', $page);
	$this->set('start_record', ($page-1)*$pagesize + 1);
	$end_record = $page * $pagesize;
	$total_records = $aftxnObj->getTotalRecords();
	if ($total_records < $end_record) $end_record = $total_records;
	$this->set('end_record', $end_record);
	$this->set('total_records', $total_records);
	$this->set('search_parameter',$post);
	$this->_template->render();	
}
function view_withdrawal_request($view_request){
	$afwithdrawalRequestObj=new AffiliateWithdrawalRequests();
	$request_detail=$afwithdrawalRequestObj->getWithdrawRequestData($view_request,array("affiliate"=>$this->Affiliate->getLoggedAffiliateId()));
	if (!$request_detail){
		Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_ACCESS'));
		Utilities::redirectUserReferer();
	}
	$this->set('request_detail',$request_detail);
	$this->_template->render();
}
function request_withdrawal(){
	if (!$this->Affiliate->isAffiliateLogged()) {
		Utilities::redirectUser(Utilities::generateUrl('affiliate','account'));
	}
	$this->set('short_header_footer',1);
	$frm = $this->getWithdrawalForm();
	$frm->fill($this->affiliate_details);
	$post = Syspage::getPostedVar();
	$last_withdrawal=$this->Affiliate->getAffiliateLastWithdrawalRequest($this->affiliate_details['affiliate_id']);
	$balance=$this->affiliate_details['balance'];
	$minimum_withdraw_limit=Settings::getSetting("CONF_MIN_WITHDRAW_LIMIT");
	if (!$balance){
		Message::addErrorMessage(sprintf(Utilities::getLabel('L_Withdrawal_Request_Minimum_Balance_Less'),Utilities::displaymoneyformat($minimum_withdraw_limit)));
		Utilities::redirectUserReferer();
	}
/*if ($balance<$minimum_withdraw_limit){
Message::addErrorMessage(sprintf(Utilities::getLabel('L_Withdrawal_Request_Minimum_Balance_Less'),displaymoneyformat($minimum_withdraw_limit)));
Utilities::redirectUser(Utilities::generateUrl('affiliate', 'credits'));
}*/
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
	if(!$frm->validate($post)){
		Message::addErrorMessage($frm->getValidationErrors());
	}else{
		if (($minimum_withdraw_limit>$post["withdrawal_amount"])){
			Message::addErrorMessage(sprintf(Utilities::getLabel('L_Withdrawal_Request_Less'),Utilities::displaymoneyformat($minimum_withdraw_limit)));
			$error=true;
		}
		if (($post["withdrawal_amount"]>$balance)){
			Message::addErrorMessage(Utilities::getLabel('L_Withdrawal_Request_Greater'));
			$error=true;
		}
		if ($last_withdrawal && (strtotime($last_withdrawal["afwithdrawal_request_date"] . "+".Settings::getSetting("CONF_MIN_INTERVAL_WITHDRAW_REQUESTS")." days") - time())>0 && ((int)Settings::getSetting("CONF_MIN_INTERVAL_WITHDRAW_REQUESTS")>0)){
			$next_withdrawal_date=date('d M,Y',strtotime($last_withdrawal["afwithdrawal_request_date"] . "+".Settings::getSetting("CONF_MIN_INTERVAL_WITHDRAW_REQUESTS")." days"));
			Message::addErrorMessage(sprintf(Utilities::getLabel('L_Withdrawal_Request_Date'),Utilities::formatDate($last_withdrawal["afwithdrawal_request_date"]),Utilities::formatDate($next_withdrawal_date),Settings::getSetting("CONF_MIN_INTERVAL_WITHDRAW_REQUESTS")));
			$error=true;
		}
		if ($error==false){
			if($this->Affiliate->addAffiliateWithdrawalRequest(array_merge($post,array("affiliate_id"=>$this->affiliate_details['affiliate_id'])))){
				$emailNotificationObj=new Emailnotifications();
				if ($emailNotificationObj->SendAffiliateWithdrawRequestNotification($this->Affiliate->getWithdrawalRequestId(),"A")){
					Message::addMessage(Utilities::getLabel('L_Withdrawal_Request_Successfully'));
					Utilities::redirectUser(Utilities::generateUrl('affiliate', 'request_withdrawal'));
				}else{
					Message::addErrorMessage($emailNotificationObj->getError());
				}
			}else{
				Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
			}
		}
	}
}
$this->set('frmWithdrawalInfo', $frm);
$this->set('balance',$balance);
$this->_template->render();	
}
private function getWithdrawalForm(){
	$frm = new Form('frmWithdrawalForm', 'frmWithdrawalForm');
	$frm->setExtra('class="siteForm"');
	$frm->setTableProperties('class="tbl-twocell"');
	$frm->setLeftColumnProperties('width="30%"');
	$frm->setJsErrorDisplay('afterfield');
	$frm->setRequiredStarWith('caption');
	$fld=$frm->addHtml('', 'htmlNote','<span class="panelTitleHeading">'.Utilities::getLabel('M_Withdrawal_account_details').'</span>','&nbsp;');
	$fld->merge_caption=true;
	$fld=$frm->addRequiredField('<label>'.Utilities::getLabel('L_Amount_to_be_Withdrawn').' ['.CONF_CURRENCY_SYMBOL.']'.'</label>', 'withdrawal_amount', null, 'user_account_holder_name');
	$frm->addRadioButtons('<label>'.Utilities::getLabel('L_Payment_Method').'</label>', 'affiliate_payment',array("cheque"=>Utilities::getLabel('L_Cheque'),"bank"=>Utilities::getLabel('L_Bank'),"paypal"=>Utilities::getLabel('L_PayPal')),"cheque",1,'width="15%" ');
	$fldPayment=$frm->addTextBox('<div class="payment" lang="payment-cheque"><label>'.Utilities::getLabel('L_Cheque_Payee_Name').'</label>', 'affiliate_cheque');
	$fldPayment->html_after_field='</div>';
	$fldPaypal=$frm->addTextBox('<div class="payment" lang="payment-paypal"><label>'.Utilities::getLabel('L_PayPal_Email_Account').'</label>', 'affiliate_paypal');
	$fldPaypal->html_after_field='</div>';
	$frm->addTextBox('<div class="payment" lang="payment-bank"><label>'.Utilities::getLabel('L_Bank_Name').'</label>', 'affiliate_bank_name')->html_after_field='</div>';
	$frm->addTextBox('<div class="payment" lang="payment-bank"><label>'.Utilities::getLabel('L_ABA/BSB_number_Branch_Number').'</label>', 'affiliate_bank_branch_number')->html_after_field='</div>';
	$frm->addTextBox('<div class="payment" lang="payment-bank"><label>'.Utilities::getLabel('L_SWIFT_Code').'</label>', 'affiliate_bank_swift_code')->html_after_field='</div>';
	$frm->addTextBox('<div class="payment" lang="payment-bank"><label>'.Utilities::getLabel('L_Account_Name').'</label>', 'affiliate_bank_account_name')->html_after_field='</div>';
	$fldAn=$frm->addTextBox('<div class="payment" lang="payment-bank"><label>'.Utilities::getLabel('L_Account_Number').'</label>', 'affiliate_bank_account_number')->html_after_field='</div>';
	$fld=$frm->addTextArea('<label>'.Utilities::getLabel('M_Other_Info_Instructions').'</label>', 'withdrawal_comments');
	$frm->setTableProperties(' width="100%" border="0" class="editformTable" cellpadding="0" cellspacing="0"');	
	$fld_submit = $frm->addSubmitButton(null, 'btn_submit', Utilities::getLabel('M_Send_Request'), 'btn_submit', '');
	return $frm;
}
}
