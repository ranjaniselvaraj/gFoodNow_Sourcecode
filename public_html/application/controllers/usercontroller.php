<?php
class UserController extends CommonController{
	function default_action(){
		Utilities::redirectUser(Utilities::generateUrl('user', 'account'));
	}
	function login_popup() {
		if(!Utilities::isAjaxRequest()) {
			Utilities::redirectUser(Utilities::generateUrl('user', 'account'));
		}
		Utilities::setSessionRedirectUrl($_SERVER['HTTP_REFERER']);
		$frm = $this->getLoginForm();
		$frm->setAction(Utilities::generateUrl('user', 'account'));
		$this->set('frm', $frm);
		$this->_template->render(false,false);
	}
	function redirect() {
		$redirect_url = Utilities::getSessionRedirectUrl();
		Utilities::unsetSessionRedirectUrl();
		if (!empty($redirect_url)) 
			Utilities::redirectUser($redirect_url);
		elseif(User::isUserLogged()) {
			Utilities::redirectUser(Utilities::generateUrl('account'));
		}
		else Utilities::redirectUser(Utilities::generateUrl());
	}
	function social_media_login($oauth_provider) {
		if (isset($oauth_provider)){
			if ($oauth_provider == 'googleplus') {
				Utilities::redirectUser(Utilities::generateUrl('user', 'login_googleplus'));
			}else if ($oauth_provider == 'facebook') {
				Utilities::redirectUser(Utilities::generateUrl('user', 'login_facebook'));
			}else{
				Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
			}
		}
		Utilities::redirectUserReferer();
	}
	function login_facebook() {
		//die(Utilities::currentPageURL());
		require_once (CONF_INSTALLATION_PATH . 'public/includes/APIs/facebook/facebook.php');
		$facebook = new Facebook(array(
			'appId' => Settings::getSetting("CONF_FACEBOOK_APP_ID"),
			'secret' => Settings::getSetting("CONF_FACEBOOK_APP_SECRET"),
			));
		$user = $facebook->getUser();
		//Utilities::printArray($user);
				//die();
		if ($user) {
			try {
// Proceed knowing you have a logged in user who's authenticated.
				$user_profile = $facebook->api('/me?fields=id,name,email');
				Utilities::printArray($user_profile);
				die();
				/*Utilities::printArray($user_profile);
				die();*/
			} catch (FacebookApiException $e) {
				error_log($e);
				$user = null;
			}
			if (!empty($user_profile )) {
# User info ok? Let's print it (Here we will be adding the login and registering routines)
				$facebook_name = $user_profile['name'];
				$user_facebook_id = $user_profile['id'];
				$facebook_email = $user_profile['email'];
				if (empty($facebook_email) || $facebook_email==""){
					Message::addErrorMessage(Utilities::getLabel('M_There_was_problem_authenticating_facebook_account'));
					Utilities::redirectUser(Utilities::generateUrl('user','account'));
				}
				$user = $this->User->getUser(array('user_email'=>$facebook_email));
				if (!$user){
					$buyer_user_type = Settings::getSetting("CONF_ACTIVATE_SEPARATE_SIGNUP_FORM")?CONF_BUYER_USER_TYPE:CONF_BUYER_SELLER_USER_TYPE;
					$arr = array(
						'user_password'=>uniqid(),
						'user_email_verified'=>1,
						'user_name'=>$facebook_name,
						'user_email'=>$facebook_email,
						'user_username'=>str_replace(" ","",$facebook_name).$user_facebook_id,
						'user_facebook_id'=>$user_facebook_id,
						'user_type'=>$buyer_user_type,
						'pref'=>'B',
						);
					if(!$this->User->addUser($arr)){
						Message::addErrorMessage($this->User->getError());
					}
				}else{
//if (!$user['user_facebook_id']) {}
					$this->User->setUserId($user["user_id"]);
					if(!$this->User->updateAttributes(array('user_facebook_id' => $user_facebook_id))){
						Message::addErrorMessage($this->User->getError());
					}
				}
				$user = $this->User->getUser(array('facebook_id'=>$user_facebook_id),true);
				if($this->User->login($user['user_username'], $user['user_password'],true) === true){
					if(isset($_SESSION['go_to_referer_page']) && filter_var($_SESSION['go_to_referer_page'], FILTER_VALIDATE_URL)){
						$ref_url = $_SESSION['go_to_referer_page'];
						unset($_SESSION['go_to_referer_page']);
						Utilities::redirectUser($ref_url);
					}else{
						$r=Utilities::generateUrl('account');
						Utilities::redirectUser(Utilities::generateUrl('account'));
					}
				}else{
					Message::addErrorMessage($this->User->getError());
				}
			} else {
				Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
			}
			Utilities::redirectUserReferer();
		}else{
			
//,'redirect_uri' => Utilities::generateAbsoluteUrl('api', 'login_fb',array())
			//Utilities::currentPageURL()
			$login_url = $facebook->getLoginUrl(array('scope' => 'email'),Utilities::currentPageURL());
			/*$loginUrl = $facebook->getLoginUrl(array(
												'scope'     => 'email',
												'redirect_uri'  => Utilities::currentPageURL(),
												));*/
			//die($loginUrl);
			Utilities::redirectUser($login_url);
		}
	}
	
	function fblogin() {
        if (!Utilities::isAjaxRequest()) {
            dieJsonError(Utilities::getLabel('M_ERROR_INVALID_ACCESS'));
        }
        $post = Syspage::getPostedVar();
		$userData = json_decode($post['userData']);
		if(empty($userData) || empty($userData->email)){	
            dieJsonError(Utilities::getLabel('M_There_was_problem_authenticating_facebook_account'));
        }
		# User info ok? Let's print it (Here we will be adding the login and registering routines)
				$facebook_name = trim($userData->first_name.' '.$userData->last_name);
				$user_facebook_id = $userData->id;
				$facebook_email = $userData->email;
				$user = $this->User->getUser(array('user_email'=>$facebook_email));
				if (!$user){
					$buyer_user_type = Settings::getSetting("CONF_ACTIVATE_SEPARATE_SIGNUP_FORM")?CONF_BUYER_USER_TYPE:CONF_BUYER_SELLER_USER_TYPE;
					$arr = array(
						'user_password'=>uniqid(),
						'user_email_verified'=>1,
						'user_name'=>$facebook_name,
						'user_email'=>$facebook_email,
						'user_username'=>str_replace(" ","",$facebook_name).$user_facebook_id,
						'user_facebook_id'=>$user_facebook_id,
						'user_type'=>$buyer_user_type,
						'pref'=>'B',
						);
					if(!$this->User->addUser($arr)){
						dieJsonError($this->User->getError());
					}
				}else{
//if (!$user['user_facebook_id']) {}
					$this->User->setUserId($user["user_id"]);
					if(!$this->User->updateAttributes(array('user_facebook_id' => $user_facebook_id))){
						dieJsonError($this->User->getError());
					}
				}
				$user = $this->User->getUser(array('facebook_id'=>$user_facebook_id),true);
				if($this->User->login($user['user_username'], $user['user_password'],true) === true){
					if(isset($_SESSION['go_to_referer_page']) && filter_var($_SESSION['go_to_referer_page'], FILTER_VALIDATE_URL)){
						$ref_url = $_SESSION['go_to_referer_page'];
						unset($_SESSION['go_to_referer_page']);
						//Utilities::redirectUser($ref_url);
					}
					dieJsonSuccess('FB Logged in');
				}else{
					dieJsonError($this->User->getError());
				}
    }
	
	
	function login_googleplus(){
		require_once 'APIs/googleplus/Google_Client.php'; // include the required calss files for google login
		require_once 'APIs/googleplus/contrib/Google_PlusService.php';
		require_once 'APIs/googleplus/contrib/Google_Oauth2Service.php';
		$client = new Google_Client();
		$client->setApplicationName("Yokart"); // Set your applicatio name
		$client->setScopes(array('https://www.googleapis.com/auth/userinfo.email', 'https://www.googleapis.com/auth/plus.me')); // set scope during user login
		$client->setClientId(Settings::getSetting("CONF_GOOGLEPLUS_CLIENT_ID")); // paste the client id which you get from google API Console
		$client->setClientSecret(Settings::getSetting("CONF_GOOGLEPLUS_CLIENT_SECRET")); // set the client secret
		$current_page_uri = preg_replace('/\?.*$/', '', Utilities::getCurrUrl());
		$client->setRedirectUri($current_page_uri);
		$client->setDeveloperKey(Settings::getSetting("CONF_GOOGLEPLUS_DEVELOPER_KEY")); // Developer key
		$plus       = new Google_PlusService($client);
		$oauth2     = new Google_Oauth2Service($client); // Call the OAuth2 class for get email address
		if(isset($_GET['code'])) {
		$client->authenticate(); // Authenticate
		$_SESSION['access_token'] = $client->getAccessToken(); // get the access token here	
		Utilities::redirectUser($current_page_uri);
		}
		if(isset($_SESSION['access_token'])) {
			$client->setAccessToken($_SESSION['access_token']);
		}
		if ($client->getAccessToken()) {
			$user = $oauth2->userinfo->get();
			$_SESSION['access_token'] = $client->getAccessToken();
			$user_googleplus_email = filter_var($user['email'], FILTER_SANITIZE_EMAIL); // get the USER EMAIL ADDRESS using OAuth2
			if (empty($user_googleplus_email) || $user_googleplus_email==""){
						Message::addErrorMessage(Utilities::getLabel('M_There_was_problem_authenticating_googleplus_account'));
						Utilities::redirectUser(Utilities::generateUrl('user','account'));
			}
		$user_googleplus_id = $user['id'];
		$user_googleplus_name = $user['name'];
		if (isset($user_googleplus_email) && (!empty($user_googleplus_email))){
			$user = $this->User->getUser(array('user_email'=>$user_googleplus_email));
			if (!$user){
				$buyer_user_type = Settings::getSetting("CONF_ACTIVATE_SEPARATE_SIGNUP_FORM")?CONF_BUYER_USER_TYPE:CONF_BUYER_SELLER_USER_TYPE;
				$arr = array(
					'user_password'=>uniqid(),
					'user_email_verified'=>1,
					'user_name'=>$user_googleplus_name,
					'user_email'=>$user_googleplus_email,
					'user_username'=>str_replace(" ","",$user_googleplus_name).$user_googleplus_id,
					'user_googleplus_id'=>$user_googleplus_id,
					'user_type'=>$buyer_user_type,
					'pref'=>'B',
					);
				if(!$this->User->addUser($arr)){
					Message::addErrorMessage($this->User->getError());
				}
			}else{
				if (!$user['user_googleplus_id']) {
					$this->User->setUserId($user["user_id"]);
					if(!$this->User->updateAttributes(array('user_googleplus_id' => $user_googleplus_id))){
						Message::addErrorMessage($this->User->getError());
					}
				}
			}
			$user = $this->User->getUser(array('googleplus_id'=>$user_googleplus_id),true);
			if($this->User->login($user['user_username'], $user['user_password'],true) === true){
				if(isset($_SESSION['go_to_referer_page']) && filter_var($_SESSION['go_to_referer_page'], FILTER_VALIDATE_URL)){
					$ref_url = $_SESSION['go_to_referer_page'];
					unset($_SESSION['go_to_referer_page']);
					Utilities::redirectUser($ref_url);
				}else{
					Utilities::redirectUser(Utilities::generateUrl('account'));
				}
			}else{
				Message::addErrorMessage($this->User->getError());
			}
			Utilities::redirectUser(Utilities::generateUrl('user','account'));
		}
		//print($email);
		} else {
			$authUrl = $client->createAuthUrl();
		}
		Utilities::redirectUser($authUrl);
	}
	function getLoginForm() {
		$frm=new Form('frmLogin');
		$frm->setAction('?');
		$frm->setExtra('class="siteForm"');
		$frm->captionInSameCell(true);
		$redirect_url = Utilities::getSessionRedirectUrl();
		Utilities::unsetSessionRedirectUrl();
		$frm->addHiddenField('', 'redirect_url', $redirect_url);
	//$frm->setRequiredStarWith('not-required');
		$frm->addRequiredField(Utilities::getLabel('L_Username_or_Email'), 'username', '', 'username', ' autocapitalize="none"');
		$fld=$frm->addPasswordField(Utilities::getLabel('L_Password'), 'password', '', '', ' autocapitalize="none"');
		$fld->requirements()->setRequired(true);
		$frm->addHtml('', '','<div class="remember">
			<input type="checkbox" name="remember" value="1">
			'.Utilities::getLabel('L_Remember_Me').' <br>
			<p><a href="'.Utilities::generateUrl('user', 'forgot_password').'">'.Utilities::getLabel('L_FORGOT_PASSWORD').'</a></p>
		</div>
		<input type="submit" name="btn_login" value="'.Utilities::getLabel('L_LOGIN_NOW').'" class="btn primary-btn form-submit">');
		$frm->setValidatorJsObjectName('frm_validator');
		$frm->setJsErrorDisplay('afterfield');
		return $frm;
	}
	function getBuyerSellerRegistrationForm($type){
		$frm=new Form('frmRegistration','frmStrengthPassword');
	//$frm->setRequiredStarWith('not-required');
		$frm->setValidatorJsObjectName('system_validator');
		$frm->setExtra('class="siteForm"');
		$frm->captionInSameCell(true);
		$frm->addHiddenField('','pref', 'B');
		$frm->addRequiredField(Utilities::getLabel('F_Your_Name'), 'user_name');
		$fld=$frm->addTextBox(Utilities::getLabel('F_Username'), 'user_username', '', '', ' class="check_username" autocapitalize="none"');
		$fld->requirements()->setUsername(true);
		$fld->requirements()->setRequired(true);
		$fld->html_after_field='<span id="ajax_availability_username"></span>';
		$fld=$frm->addEmailField(Utilities::getLabel('F_Email'), 'user_email','', '', ' class="check_email" autocapitalize="none"');
		$fld->html_after_field='<span id="ajax_availability_email"></span>';
		$fld_password = $frm->addPasswordField(Utilities::getLabel('M_Password'), 'user_password', '', 'check-password', ' autocapitalize="none" ');
		$fld_password->requirements()->setRequired(true);
		$fld_password->requirements()->setLength(4,20);
		$fld_password->html_before_field='<div id="check-password-result">';
		$fld_password->html_after_field='</div>';
		$fld=$frm->addHtml(sprintf(Utilities::getLabel('L_By_using_agree_terms_conditions'),'<a target="_blank" href="'.Utilities::generateUrl('cms', 'view',array(Settings::getSetting("CONF_ACCOUNT_TERMS"))).'">'.Utilities::getLabel('L_Terms_Conditions').'</a>'),'');
		$fld->merge_caption=true;
		$fld=$frm->addSubmitButton('','btn_signup',strtoupper(Utilities::getLabel('BTN_REGISTER')),'',' class="btn primary-btn"');
		if (Settings::getSetting("CONF_ACTIVATE_SEPARATE_SIGNUP_FORM")) {
			$become_seller_page=str_replace('{SITEROOT}', CONF_WEBROOT_URL, Settings::getSetting("CONF_SELL_SITENAME_PAGE"));
			$fld->html_after_field='<p class="marginLeft marginTop">'.sprintf(Utilities::getLabel('L_Please_click_here_to_signup_seller'),'<a href="'.$become_seller_page.'">'.Utilities::getLabel('L_Click_here').'</a>').'</p>';
		}
		$frm->setJsErrorDisplay('afterfield');
		return $frm;
	}
	function account($p){
		$referrer=parse_url(getenv("HTTP_REFERER"));
		if (isset($p) && (strtolower($p)=="api")){
			$hide_header_footer=true;
			$_SESSION['hide_header_footer']=true;
		}
		if (isset($p) && ($p=="p_c")){
			Message::addMessage(Utilities::getLabel('M_Your_password_changed_login'));	
			Utilities::redirectUser(Utilities::generateUrl('user','account'));
		}
		Utilities::checkIsAlreadyLoggedIn(); 
		$loginFrm=$this->getLoginForm();
		$registrationFrm=$this->getBuyerSellerRegistrationForm(CONF_BUYER_SELLER_USER_TYPE);
		$become_seller_page=str_replace('{SITEROOT}', CONF_WEBROOT_URL, Settings::getSetting("CONF_SELL_SITENAME_PAGE"));
		if ($referrer['path']==$become_seller_page){
			$registrationFrm->fill(array("pref"=>"S"));
		}
		$this->set('loginFrm',$loginFrm);
		$this->set('RegistrationFrm', $registrationFrm);
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_login'])){
			if(!$loginFrm->validate($post)){
				Message::addErrorMessage($loginFrm->getValidationErrors());
			}else{
	// Check how many login attempts have been made.
				$login_info = $this->User->getLoginAttempts($post['username']);
				if ($login_info && ($login_info['ulogin_total'] >= Settings::getSetting("CONF_MAX_LOGIN_ATTEMPTS")) && strtotime('-1 hour') < strtotime($login_info['ulogin_date_modified'])) {
					Message::addErrorMessage(Utilities::getLabel('L_Warning_Max_Login_Attempts'));
					if(Utilities::isAjaxRequest()) {
						dieJsonError(Message::getHtml());
					}
					Utilities::redirectUser(Utilities::generateUrl('user','account'));
				}
				$pwd = Utilities::encryptPassword($post['password']);
				if($this->User->login($post['username'], $post['password']) === true){
					setcookie('uc_id', $this->User->getAttribute("user_id"), time()+3600*24*30,'/');
	//$this->User->expireRewardPoints($this->User->getAttribute("user_id"));
					$this->User->deleteLoginAttempts($post['username']);
					if(isset($post['remember'])){
						$remember_token = substr(md5(rand(1, 99999) . microtime()), 1, 25);
						$data = array('user_id'=>$this->User->getAttribute("user_id"), 'remember_token'=>$remember_token, 'token_expiry'=>date('Y-m-d H:i:s', strtotime("+10 DAYS")));
						if($this->User->updateRememberMeToken($data) === true){
							setcookie('remembertoken', $remember_token, time()+3600*24*10,'/');
						}
					}
					if(Utilities::isAjaxRequest()) dieJsonSuccess('');
					if(isset($_SESSION['go_to_referer_page']) && filter_var($_SESSION['go_to_referer_page'], FILTER_VALIDATE_URL)){
						$ref_url = $_SESSION['go_to_referer_page'];
						unset($_SESSION['go_to_referer_page']);
						Utilities::redirectUser($ref_url);
					}else{
						Utilities::redirectUser(Utilities::generateUrl('account'));
					}
				}else{
					$this->User->addLoginAttempt($post['username']);
					$loginFrm->fill($post);
					Message::addErrorMessage($this->User->getError());
					if(Utilities::isAjaxRequest()) {
						dieJsonError(Message::getHtml());
					}
				}
			}
		}elseif($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_signup'])){
			if(!$registrationFrm->validate($post)){
				Message::addErrorMessage($registrationFrm->getValidationErrors());
			}else{
				$buyer_user_type = Settings::getSetting("CONF_ACTIVATE_SEPARATE_SIGNUP_FORM")?CONF_BUYER_USER_TYPE:CONF_BUYER_SELLER_USER_TYPE;
				$arr= array_merge($post,array("user_email_verified"=>0,"user_type"=>$buyer_user_type));
				if($this->User->addUser($arr)){
					if (Settings::getSetting("CONF_AUTO_LOGIN_REGISTRATION") && ($p!="api")){
						$pwd = Utilities::encryptPassword($post['user_password']);
						if($this->User->login($post['user_username'],$pwd,true) === true){
							Utilities::redirectUser(Utilities::generateUrl('account'));
						}
					}
					$user_verified=$this->User->getAttribute('user_email_verified');
					if($user_verified==1) {
						Message::addMessage(Utilities::getLabel('M_SUCCESS_USER_SIGNUP_VERIFIED'));
					} else {
						Message::addMessage(Utilities::getLabel('M_SUCCESS_USER_SIGNUP'));
					}
				}else{
					$registrationFrm->fill($post);
					Message::addErrorMessage($this->User->getError());
				}
			}
		} elseif(!isset($_SESSION['go_to_referer_page']) && isset($_SERVER['HTTP_REFERER']) && filter_var($_SERVER['HTTP_REFERER'], FILTER_VALIDATE_URL) && strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) == true){
			$_SESSION['go_to_referer_page'] = $_SERVER['HTTP_REFERER'];
		}
		if(!$hide_header_footer){
			$hide_header_footer=Utilities::isHideHeaderFooter();
		}
		if($hide_header_footer){ 
			$this->set('hide_header_footer',$hide_header_footer);
			$this->_template->render(false,false,'user/account_app.php');	
		}else{
			$this->_template->render();	
		}	
	}
	function cryptPwd($str){
		return crypt($str, 'NxhPwrR07zYijkhgdfg46M2fad9a5189454d05879a76f5e8b569xf2CVo6JpNxhPwr587988a76f5e');
	}
	function forgot_password(){
			Utilities::checkIsAlreadyLoggedIn();
			$frm = new Form('frmForgotPassword');
			$fld = $frm->addRequiredField('<label>'.Utilities::getLabel('F_EMAIL_OR_USERNAME').'</label>', 'user_email_username', '', 'user_email_username');
			if (!empty(CONF_RECAPTACHA_SITEKEY)){
				$frm->addHtml('', 'htmlNote','<div class="g-recaptcha" data-sitekey="'.CONF_RECAPTACHA_SITEKEY.'"></div>');
			}
			$fld=$frm->addSubmitButton('&nbsp;','btn_submit',Utilities::getLabel('btn_submit'),'',' title="'.Utilities::getLabel('btn_submit').'" ');
			$fld->html_before_field = '<div class="fieldrw">';
			$fld->html_after_field = ' <a href="'.Utilities::generateUrl('user', 'account').'" class="btn gray">'.Utilities::getLabel('F_Back_to_Login').'</a></div>';
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
					$user = $this->User->getUser(array('user_email_username'=>$post['user_email_username']));
					if(!$user){
						Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_EMAIL_USERNAME'));
					}elseif($user['user_status'] != 1){
						Message::addErrorMessage(Utilities::getLabel('M_ERROR_YOUR_ACCOUNT_NOT_ACTIVE'));
					}elseif($user['user_is_deleted'] == 1){
						Message::addErrorMessage(Utilities::getLabel('M_ERROR_YOUR_ACCOUNT_IS_DELETED'));
					}elseif($user['user_email_verified'] != 1){
						Message::addErrorMessage(sprintf(Utilities::getLabel('M_ERROR_YOU_HAVE_NOT_VERIFIED_EMAIL'),'<a href="'.Utilities::generateUrl('user', 'resend_verification_code').'" class="greenAnchorLink">'.Utilities::getLabel('M_Click_here').'</a>'));
					}elseif(!$this->User->canResetPassword($user['user_id'])){
						Message::addErrorMessage(Utilities::getLabel('M_WARNING_FORGOT_PASSWORD_DUPLICATE_REQUEST'));
					}else{
						$reset_token = substr(md5(rand(1, 99999) . microtime()), 1, 25);
						$data = array('user_id'=>$user['user_id'], 'reset_token'=>$reset_token, 'token_expiry'=>date('Y-m-d H:i:s', strtotime("+24 HOUR")));
						if($this->User->updateForgotRequest($data) === true){
							$reset_url = Utilities::generateAbsoluteUrl('user', 'reset_password', array($user["user_id"].".".$reset_token));
							$website_name = Settings::getSetting("CONF_WEBSITE_NAME");
							$website_url = Utilities::getUrlScheme();
							if (Utilities::sendMailTpl($user['user_email'], 'forgot_password', array(
								'{reset_url}' => $reset_url,
								'{website_name}' => $website_name,
								'{website_url}' => $website_url,
								'{site_domain}' => CONF_SERVER_PATH,
								'{user_full_name}' => htmlentities($user['user_name']),
								))){
								Message::addMessage(Utilities::getLabel('M_SUCCESS_FORGOT_PASSWORD_REQUEST'));
							Utilities::redirectUser(Utilities::generateUrl('user','forgot_password'));
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
	function resend_verification_code(){
		Utilities::checkIsAlreadyLoggedIn();
		$frm = $this->getResendVerificationEmailForm(); 
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)) {
				Message::addErrorMessage($frm->getValidationErrors());
			} else if(!Utilities::verifyCaptcha()) {
				Message::addErrorMessage(Utilities::getLabel('M_ERROR_PLEASE_VERIFY_YOURSELF'));
				$frm->fill($post);
			} else{
				$user = $this->User->getUser(array('user_email_username'=>$post['user_email_username']));
				if(!$user){
					Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_EMAIL_USERNAME'));
				}elseif($user['user_is_deleted'] == 1){
					Message::addErrorMessage(Utilities::getLabel('M_ERROR_YOUR_ACCOUNT_IS_DELETED'));
				}elseif($user['user_email_verified'] == 1){
					Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_EMAIL_OR_EMAIL_ALREADY_VERIFIED'));
				}else{
					$emailNotObj=new Emailnotifications();
					if (!$emailNotObj->sendVerificationEmail($user["user_id"])){
						Message::addErrorMessage(Utilities::getLabel('M_email_not_sent_server_issue'));
					}else{
						Message::addMessage(Utilities::getLabel('M_Verification_Email_Send_Successfully'));
					}
					Utilities::redirectUser(Utilities::getCurrUrl());
				}
			}
		}
		$this->set('frm', $frm);
		$this->_template->render();	
	}
	private function getResendVerificationEmailForm(){
		$frm = new Form('frmResendVerificationCode');
		$fld = $frm->addRequiredField("<label>".Utilities::getLabel('M_Email_or_Username')."</label>", 'user_email_username', '', 'email');
		if (!empty(CONF_RECAPTACHA_SITEKEY)){
			$frm->addHtml('', 'htmlNote','<div class="g-recaptcha" data-sitekey="'.CONF_RECAPTACHA_SITEKEY.'"></div>');
		}
		$fld=$frm->addSubmitButton('&nbsp;','btn_submit',Utilities::getLabel('btn_submit'));
		$fld->html_before_field = '<div class="fieldrw">';
		$fld->html_after_field = '&nbsp;&nbsp;<a class="btn blue" href="'.Utilities::generateUrl('user','account').'">'.Utilities::getLabel('L_Back_to_login').'</a></div>';
		$frm->setTableProperties('width="100%" border="0" cellspacing="10" cellpadding="0" class="formTable"');
		$frm->setRequiredStarWith('x');
		$frm->setValidatorJsObjectName('system_validator');
		$frm->setExtra('class="siteForm"');
		$frm->setTableProperties(' width="100%" border="0" class="tableform"');
		$frm->captionInSameCell(true);
		$frm->setFieldsPerRow(1);
		$frm->setJsErrorDisplay('afterfield');
		return $frm;
	}
	protected function validateEmail($email){
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}
	function reset_password($param) {
		Utilities::checkIsAlreadyLoggedIn();
		$attr = explode(".",$param);
		$user_id = intval($attr[0]);
		$token = trim($attr[1]);
	//$email=urldecode($email);
		if($user_id < 1 || strlen($token) != 25){
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
			Utilities::redirectUser(Utilities::generateUrl('user','account'));
		}
		if(!$user_reset_pwd_data = $this->User->validateToken($user_id, $token)){
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_TOCKEN'));
			Utilities::redirectUser(Utilities::generateUrl('user','account'));
		}
		if(!$user = $this->User->getUser(array('id'=>$user_reset_pwd_data["uprr_user_id"]))){
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
			Utilities::redirectUser(Utilities::generateUrl('user','account'));
		}
		if($user['user_status'] != 1){
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_YOUR_ACCOUNT_NOT_ACTIVE'));
			Utilities::redirectUser(Utilities::generateUrl('user','account'));
		}
		if($user['user_is_deleted'] == 1){
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_YOUR_ACCOUNT_IS_DELETED'));
			Utilities::redirectUser(Utilities::generateUrl('user','account'));
		}
		if($user['user_email_verified'] != 1){
			Message::addErrorMessage(sprintf(Utilities::getLabel('M_ERROR_YOU_HAVE_NOT_VERIFIED_EMAIL'),'<a href="'.Utilities::generateUrl('user', 'resend_verification_code').'" class="greenAnchorLink">'.Utilities::getLabel('M_Click_here').'</a>'));
			Utilities::redirectUser(Utilities::generateUrl('user','account'));
		}
		$frm = $this->getResetPwdForm(); 
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				if($this->User->updatePassword(intval($user['user_id']), $post['user_password'])){
					Message::addMessage(Utilities::getLabel('M_Password_successfully_updated'));
					Utilities::redirectUser(Utilities::generateUrl('user', 'account'));
				}else{
					Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
				}
			}
		}
		$frm->fill(array('user_id'=>$user_id));
		$this->set('frm', $frm);
		$this->_template->render();	
	}
	private function getResetPwdForm(){
		$frm = new Form('frmResetPassword','frmStrengthPassword');
		$frm->setTableProperties(' width="100%" border="0" class="tableform"');
		$frm->setExtra('class="siteForm"');
		$frm->captionInSameCell(true);
	//$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'user_id', '', 'user_id');
		$fld = $frm->addPasswordField("<label>".Utilities::getLabel('f_new_password')."</label>", 'user_password','','check-password');
		$fld->requirements()->setRequired();
		$fld->requirements()->setLength(4, 20);
		$fld->html_before_field='<div id="check-password-result">';
		$fld->html_after_field='</div>';
		$fld1 = $frm->addPasswordField("<label>".Utilities::getLabel('f_Confirm_new_password')."</label>", 'user_password1');
		$fld1->requirements()->setRequired();
		$fld1->requirements()->setCompareWith('user_password');
		$fld=$frm->addSubmitButton('&nbsp;','btn_submit',Utilities::getLabel('btn_submit'));
		$fld->html_before_field = '<div class="fieldrw">';
		$fld->html_after_field = '</div>';
		$frm->setJsErrorDisplay('afterfield');	
		return $frm;
	}
	function confirm_email($code) {
		if ($this->User->confirmEmail($code)) {
			Message::addMessage(Utilities::getLabel('M_SUCCESS_USER_ACCOUNT_VERIFIED'));
			$redirect_url = Utilities::generateUrl('account');
		} else {
			Message::addErrorMessage($this->User->getError());
			$redirect_url = Utilities::generateUrl('user', 'account');
		}
		Utilities::redirectUser($redirect_url);
	}
	function check_username_availability(){
		$post = Syspage::getPostedVar();
		$username=$post["username"];
		if (preg_match('/^[A-Za-z][A-Za-z0-9_.]{3,20}$/', $username) ){
	//$user = $this->User->getUser(array('user_username'=>$username));
			$user = $this->User->getUser(array('user_username'=>$username),false,true);
			if ($user){
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
			$user = $this->User->getUser(array('user_email'=>$email),false,true);
			if ($user){
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
	function become_seller(){
		$extraPageObj=new Extrapage();
		$arr_contact_content=$extraPageObj->getExtraBlockData(array('identifier'=>'BECOME_SELLER_CONTENT_BLOCK'));
		$become_seller_content=$arr_contact_content["epage_content"];
		$become_seller_content=str_replace('{SITEROOT}', CONF_WEBROOT_URL, $become_seller_content);
		$this->set('become_seller_content', $become_seller_content);
		$this->_template->render();	
	}
	
	private function getAdvertiserLoginForm() {
		$frm=new Form('frmLogin');
		$frm->setAction('?');
		$frm->setExtra('class="siteForm"');
		$frm->captionInSameCell(true);
		$redirect_url = Utilities::getSessionRedirectUrl();
		Utilities::unsetSessionRedirectUrl();
		$frm->addHiddenField('', 'redirect_url', $redirect_url);
		$fld = $frm->addRequiredField(Utilities::getLabel('L_Advertiser_Username_or_Email'), 'username','','username','placeholder="'.Utilities::getLabel('L_Advertiser_Username_or_Email').'"');
		$fld=$frm->addPasswordField(Utilities::getLabel('L_Advertiser_Password'), 'password','','password','placeholder="'.Utilities::getLabel('L_Advertiser_Password').'"');
		$fld->requirements()->setRequired(true);
		$frm->addHtml('', '','<div class="remember">
			<input type="checkbox" name="remember" value="1">
			'.Utilities::getLabel('L_Remember_Me').' <br>
			<p><a href="'.Utilities::generateUrl('user', 'forgot_password').'">'.Utilities::getLabel('L_FORGOT_PASSWORD').'</a></p>
		</div>
		<input type="submit" name="btn_login" value="'.Utilities::getLabel('L_LOGIN_NOW').'" class="form-submit">');
		$frm->setValidatorJsObjectName('frm_validator');
		$frm->setJsErrorDisplay('afterfield');
		return $frm;
	}
	function advertise($p){
		$loginFrm=$this->getAdvertiserLoginForm();
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_login'])){
			if(!$loginFrm->validate($post)){
				Message::addErrorMessage($loginFrm->getValidationErrors());
			}else{
	// Check how many login attempts have been made.
				$login_info = $this->User->getLoginAttempts($post['username']);
				if ($login_info && ($login_info['ulogin_total'] >= Settings::getSetting("CONF_MAX_LOGIN_ATTEMPTS")) && strtotime('-1 hour') < strtotime($login_info['ulogin_date_modified'])) {
					Message::addErrorMessage(Utilities::getLabel('L_Warning_Max_Login_Attempts'));
					Utilities::redirectUser(Utilities::generateUrl('user','advertise'));
				}
				$pwd = Utilities::encryptPassword($post['password']);
				if($this->User->login($post['username'], $post['password']) === true){
					$this->User->deleteLoginAttempts($post['username']);
					if(isset($post['remember'])){
						$remember_token = substr(md5(rand(1, 99999) . microtime()), 1, 25);
						$data = array('user_id'=>$this->User->getAttribute("user_id"), 'remember_token'=>$remember_token, 'token_expiry'=>date('Y-m-d H:i:s', strtotime("+10 DAYS")));
						if($this->User->updateRememberMeToken($data) === true){
							setcookie('remembertoken', $remember_token, time()+3600*24*10,'/');
						}
					}
					Utilities::redirectUser(Utilities::generateUrl('account'));
				}else{
					$this->User->addLoginAttempt($post['username']);
					$loginFrm->fill($post);
					Message::addErrorMessage($this->User->getError());
				}
				Utilities::redirectUserReferer();
			}
		}
		$this->set('loginFrm', $loginFrm);
		$this->_template->render(true,true,'',false,true,false);	
	}
	private function getRegistrationForm(){
		$frm=new Form('frmRegistration','frmRegistration');
		$frm->setFieldsPerRow(2);
		$frm->setAction('?');
		$frm->setValidatorJsObjectName('system_validator');
		$frm->setExtra('class="siteForm form-double-cell"');
		$frm->captionInSameCell(true);
		$frm->addHtml('<span class="panelTitleHeading">'.Utilities::getLabel('L_Your_Personal_Details').'</span>', 'htmlNote','')->merge_cells=2;
		$fld=$frm->addTextBox('<label>'.Utilities::getLabel('F_Username').'</label>', 'user_username', '', '', ' class="check_username"');
		$fld->requirements()->setUsername(true);
		$fld->requirements()->setRequired(true);
		$fld->html_after_field='<span id="ajax_availability_username"></span>';
		$fld=$frm->addEmailField('<label>'.Utilities::getLabel('F_Email').'</label>', 'user_email','', '', ' class="check_email"');
		$fld->html_after_field='<span id="ajax_availability_email"></span>';
		$frm->addRequiredField('<label>'.Utilities::getLabel('F_Name').'</label>', 'user_name');
		$fld_phn = $frm->addRequiredField('<label>'.Utilities::getLabel('F_Phone').'</label>', 'user_phone');
		$fld_phn->requirements()->setRegularExpressionToValidate('^[\s()+-]*([0-9][\s()+-]*){5,20}$');
		$frm->addHtml('<span class="panelTitleHeading">'.Utilities::getLabel('L_Your_Company_Details').'</span>', 'htmlNote','')->merge_cells=2;
		$fld=$frm->addTextBox('<label>'.Utilities::getLabel('L_Company').'</label>', 'user_company')->merge_cells=2;
		$fld=$frm->addTextArea('<label>'.Utilities::getLabel('L_Brief_Profile').'</label>', 'user_profile');
		$fld->html_after_field='<small>'.Utilities::getLabel('L_Please_tell_us_something_about_yourself').'</small>';
		$frm->addTextArea('<label>'.Utilities::getLabel('L_What_kind_products_services_advertise').'</label>', 'user_products_services');
		$frm->addHtml('<span class="panelTitleHeading">'.Utilities::getLabel('L_Your_Password').'</span>', 'htmlNote','')->merge_cells=2;
		$fld_password = $frm->addPasswordField('<label>'.Utilities::getLabel('M_Password').'</label>', 'user_password', '', 'user_password', '');
		$fld_password->requirements()->setRequired(true);
		$fld_password->requirements()->setLength(4,20);
		$fld = $frm->addPasswordField('<label>'.Utilities::getLabel('M_Confirm_new_password').'</label>', 'confirm_pwd', '', 'confirm_pwd');
		$fld->requirements()->setRequired(true);
		$fld->requirements()->setCompareWith('user_password', 'eq');
		$fld=$frm->addHtml('&nbsp;','',sprintf(Utilities::getLabel('L_By_using_agree_terms_conditions'),'<a target="_blank" href="'.Utilities::generateUrl('cms', 'view',array(Settings::getSetting("CONF_ACCOUNT_TERMS"))).'">'.Utilities::getLabel('L_Terms_Conditions').'</a>'))->merge_cells=2;
		$fld=$frm->addSubmitButton('','btn_signup',Utilities::getLabel('BTN_REGISTER'));
		$frm->setJsErrorDisplay('afterfield');
		$frm->setLeftColumnProperties('width="25%"');
		$frm->setTableProperties('width="100%" border="0" class="tbl-twocell" cellpadding="0" cellspacing="0"');
		return $frm;
	}
	
	private function getQuickLoginForm() {
		$frm=new Form('frmLogin');
		$frm->setFieldsPerRow(3);
		$frm->setRequiredStarPosition('N');
		$frm->setAction(Utilities::generateUrl('user','advertise'));
		$frm->setExtra('class="siteForm seller-login form-double-cell"');
		$frm->captionInSameCell(false);
		$redirect_url = Utilities::getSessionRedirectUrl();
		Utilities::unsetSessionRedirectUrl();
		$frm->addHiddenField('', 'redirect_url', $redirect_url);
		$fld = $frm->addRequiredField('', 'username','','username','title="'.Utilities::getLabel('L_Username_or_Email').'" placeholder="'.Utilities::getLabel('L_Username_or_Email').'"');
		$fld=$frm->addPasswordField('', 'password','','password','title="'.Utilities::getLabel('L_Password').'" placeholder="'.Utilities::getLabel('L_Password').'"');
		$fld->requirements()->setRequired(true);
		$fld=$frm->addSubmitButton('','btn_login',Utilities::getLabel('L_LOGIN_NOW'));
		$frm->addHtml('&nbsp;','forgot','<a href="'.Utilities::generateUrl('user', 'forgot_password').'" class="forgot fl">'.Utilities::getLabel('L_FORGOT_PASSWORD').'</a>');
		$frm->setValidatorJsObjectName('frm_validator');
		$frm->setJsErrorDisplay('afterfield');
		return $frm;
	}
	
	function advertiser_registration(){
		Utilities::checkIsAlreadyLoggedIn();
		$quickLoginFrm=$this->getQuickLoginForm();
		$registrationFrm=$this->getRegistrationForm();
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_signup'])){
			if(!$registrationFrm->validate($post)){
				Message::addErrorMessage($registrationFrm->getValidationErrors());
			}else{
				if(!$registrationFrm->validate($post)){
					Message::addErrorMessage($registrationFrm->getValidationErrors());
				}else{
					$arr= array_merge($post,array("user_email_verified"=>0,"user_type"=>CONF_ADVERTISER_USER_TYPE));
					if($this->User->addUser($arr)){
						if (Settings::getSetting("CONF_AUTO_LOGIN_REGISTRATION") && ($p!="api")){
							$pwd = Utilities::encryptPassword($post['user_password']);
							if($this->User->login($post['user_username'],$pwd,true) === true){
								Utilities::redirectUser(Utilities::generateUrl('account'));
							}
						}
						$user_verified=$this->User->getAttribute('user_email_verified');
						if($user_verified==1) {
							Message::addMessage(Utilities::getLabel('M_SUCCESS_USER_SIGNUP_VERIFIED'));
						} else {
							Message::addMessage(Utilities::getLabel('M_SUCCESS_USER_SIGNUP'));
						}
					}else{
						$registrationFrm->fill($post);
						Message::addErrorMessage($this->User->getError());
					}
				}
			}
		}
		$this->set('quickLoginFrm', $quickLoginFrm);
		$this->set('RegistrationFrm', $registrationFrm);
		$this->_template->render(false,false,'',false,true,false);	
	}
}
