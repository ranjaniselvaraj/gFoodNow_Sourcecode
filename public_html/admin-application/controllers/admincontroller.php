<?php
class AdminController extends CommonController{
	
	function login($frmType=false){
		
		$frm = $this->getLoginForm();
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				if($this->Admin->login($post['username'], $post['password']) === true){
					if(isset($post['remember'])){
						$remember_token = substr(md5(rand(1, 99999) . microtime()), 1, 25);
						$data = array('admin_id'=>$this->Admin->getAttribute("admin_id"), 'remember_token'=>$remember_token, 'token_expiry'=>date('Y-m-d H:i:s', strtotime("+10 DAYS")));
						if($this->Admin->updateRememberMeToken($data) === true){
							setcookie('remembertoken', $remember_token, time()+3600*24*10,'/');
						}
					}
					if(isset($_SESSION['go_to_referer_admin_page']) && filter_var($_SESSION['go_to_referer_admin_page'], FILTER_VALIDATE_URL)){
						$ref_url = $_SESSION['go_to_referer_admin_page'];
						unset($_SESSION['go_to_referer_admin_page']);
						Utilities::redirectUser($ref_url);
					}
					Utilities::redirectUser(Utilities::generateUrl('home'));
				}else{
					Message::addErrorMessage($this->Admin->getError());
				}
			}
			$frmType='LOGIN';				
		}
		
		$forgotfrm = $this->getForgotForm();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_forgot'])){
			if(!Utilities::verifyCaptcha()){
				//Message::addErrorMessage('Error: Incorrect Security Code. Please Try Again.');
				Message::addErrorMessage(Utilities::getLabel('M_ERROR_PLEASE_VERIFY_YOURSELF'));
			}elseif(!$forgotfrm->validate($post)){
				Message::addErrorMessage($forgotfrm->getValidationErrors());
			}else{
				$admin = $this->Admin->checkAdminEmail($post['admin_email']);
				if($admin['exists'] === true && !$this->Admin->checkAdminPwdResetRequest($admin['admin_id'])){
					$token = substr(md5(microtime()), 2, 20);
					$data = array('admin_id'=>$admin['admin_id'], 'token'=>$token);
					$reset_url = Utilities::generateAbsoluteUrl('admin','rpa',array($admin['admin_id'], $token));
					$website_url = Utilities::getUrlScheme();
					if($this->Admin->addPasswordResetRequest($data)){
						 Utilities::sendMailTpl($admin['admin_email'], 'forgot_password', array(
        											'{reset_url}' => $reset_url,
											        '{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
											        '{website_url}' => $website_url,
													'{site_domain}' => CONF_SERVER_PATH,
													'{user_full_name}' => trim($admin['admin_full_name']),
				         ));
					
						Message::addMessage('Success: Your password reset instructions has been sent to your email. Please check your spam folder if you do not find it in your inbox. Please mind that this request is valid only for next 24 hours.');	
						$frmType='FORGOT';	
						Utilities::redirectUser(Utilities::generateUrl('admin', 'login',array(strtolower($frmType))));
					}else{
						Message::addErrorMessage($this->Admin->getError());
					}
				}else{
					Message::addErrorMessage($this->Admin->getError());
				}
			}
			$frmType='FORGOT';			
		}
		
		Syspage::addCss('../css/admin/login.css');
		$this->set('frmType', $frmType);
		$this->set('frmLogin', $frm);
		$this->set('frmForgot', $forgotfrm);
		$this->_template->render(false, false);
	}
	
	private function getLoginForm(){
		$frm = new Form('frmLogin', 'frmLogin');
		$frm->setTableProperties('width="100%" border="0" class="formTable" cellpadding="0" cellspacing="0"');
		$frm->setExtra('class="web_form"');
		$frm->setRequiredStarWith('not-required');
		$frm->captionInSameCell(true);
		$frm->setJsErrorDisplay('afterfield');
		$uname=$frm->addTextBox('username', 'username','','username','class="usericon" title="Username" ');
		$uname->requirements()->setRequired();
		$uname->setRequiredStarWith('none');
		$fld=$frm->addPasswordField('password', 'password','','password','class="keyicon" title="Password"  ');
		$fld->requirements()->setRequired();
		$fld->setRequiredStarWith('none');
		$frm->addCheckBox('Remember Me', 'remember', 1);
		$frm->addSubmitButton('', 'btn_submit', 'Login', 'btn_submit');
		return $frm;
	}
	
	function logout() {
        //session_destroy();
		unset($_SESSION['logged_admin']);
		if ($this->Admin->deleteRememberMeToken($this->getLoggedAdminId())){
			setcookie('remembertoken', '', time()-3600,'/');
		}else{
			Message::addErrorMessage($this->Admin->getError());
			Utilities::redirectUserReferer();
		}
        Utilities::redirectUser(Utilities::generateUrl('admin'));
    }
	
	function forgot_password(){
		$frm = $this->getForgotForm();
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_forgot'])){
			if(!Utilities::verifyCaptcha()){
				//Message::addErrorMessage('Error: Incorrect Security Code. Please Try Again.');
				Message::addErrorMessage(Utilities::getLabel('M_ERROR_PLEASE_VERIFY_YOURSELF'));
			}elseif(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				$admin = $this->Admin->checkAdminEmail($post['admin_email']);
				if($admin['exists'] === true && !$this->Admin->checkAdminPwdResetRequest($admin['admin_id'])){
					$token = substr(md5(microtime()), 2, 20);
					$data = array('admin_id'=>$admin['admin_id'], 'token'=>$token);
					$reset_url = Utilities::generateAbsoluteUrl('admin','rpa',array($admin['admin_id'], $token));
					$website_url = Utilities::getUrlScheme();
					if($this->Admin->addPasswordResetRequest($data)){
						 Utilities::sendMailTpl($admin['admin_email'], 'forgot_password', array(
        											'{reset_url}' => $reset_url,
											        '{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
											        '{website_url}' => $website_url,
													'{site_domain}' => CONF_SERVER_PATH,
													'{user_full_name}' => trim($admin['admin_full_name']),
				         ));
					
						Message::addMessage('Success: Your password reset instructions has been sent to your email. Please check your spam folder if you do not find it in your inbox. Please mind that this request is valid only for next 24 hours.');
						$frmType='FORGOT';	
						Utilities::redirectUser(Utilities::generateUrl('admin', 'login',array($frmType)));
					}else{
						Message::addErrorMessage($this->Admin->getError());
					}
				}else{
					Message::addErrorMessage($this->Admin->getError());
				}
			}
		}
		$this->set('frmForgot', $frm);
		$this->_template->render(false, false);
	}
	
	private function getForgotForm(){
		$frm = new Form('frmForgot', 'frmForgot');
		$frm->setFieldsPerRow(1);
		$frm->setExtra('class="web_form"');
		$frm->captionInSameCell(true);
		$frm->setJsErrorDisplay('afterfield');
		$frm->setRequiredStarWith('not-required');
		$frm->setTableProperties('width="100%" border="0" class="formTable" cellpadding="0" cellspacing="0"');
        $email=$frm->addEmailField('Email Address', 'admin_email','','',' title="Email Address"');
		$email->setRequiredStarWith('none');
		if (!empty(CONF_RECAPTACHA_SITEKEY)){
			$frm->addHtml('', 'captcha_code','<div class="g-recaptcha" data-sitekey="'.CONF_RECAPTACHA_SITEKEY.'"></div>');
		}
        $fld4 = $frm->addSubmitButton('', 'btn_forgot', 'Send Reset Pasword Email');
		return $frm;
	}
	
	function rpa($admin_id = 0, $token = ''){
		if(intval($admin_id) < 1 && strlen(trim($token)) < 20){
			Utilities::redirectUser(Utilities::generateUrl('',''));
		}
		
		if($this->Admin->checkResetLink(intval($admin_id), trim($token)) === true){
			$frm = $this->getResetPwdForm(intval($admin_id), trim($token));
			$post = Syspage::getPostedVar();
			if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_reset'])){
				if(!$frm->validate($post)){
					Message::addErrorMessage($frm->getValidationErrors());
				}else{
					if(intval($admin_id) !== intval($post['apr_id']) || $token !== $post['token']){
						Message::addErrorMessage('Error: Invalid Request!');
						$frmType='FORGOT';	
						Utilities::redirectUser(Utilities::generateUrl('admin', 'login',array($frmType)));
						exit(0);
					}
					$admin = $this->Admin->getAdminById(intval($admin_id));
					$_SESSION['fat_es-auth_change'] = true;
					$pwd = Utilities::encryptPassword($post['new_pwd']);
				
					if($admin['admin_id'] == intval($admin_id) && $this->Admin->changeAdminPwd(intval($admin_id), $pwd)){
						Utilities::sendMailTpl($admin['admin_email'], 'password_changed_successfully', array(
											        '{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
													'{full_name}' => trim($admin['admin_full_name']),
				         ));
						Message::addMessage('Success: Password changed successfully.');
						Utilities::redirectUser(Utilities::generateUrl('admin'));
					}else{
						Message::addErrorMessage('Error: Invalid Request!');
					}
				}
			}
			Syspage::addCss('../css/admin/login.css');
			$this->set('frmResetPassword', $frm);
			$this->_template->render(false, false);			
		}else{
			Utilities::redirectUser(Utilities::generateUrl('',''));
		}
	}
	
	private function getResetPwdForm($a_id, $token){
			$frm = new Form('frmResetPassword', 'frmStrengthPassword');
			$frm->setRequiredStarWith('caption');
			$frm->setExtra('class="web_form"');
			$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0"');
			$frm->setFieldsPerRow(1);
			$frm->captionInSameCell(true);
			$frm->setJsErrorDisplay('afterfield');
			$fld_np = $frm->addPasswordField('New Password', 'new_pwd', '', 'check-password');
			$fld_np->requirements()->setRequired(true);
			$fld_np->requirements()->setLength(4,20);
			$fld_np->html_before_field='<div id="check-password-result">';
			$fld_np->html_after_field='</div>';
			$req_obj = new FormFieldRequirement('new_pwd');
			$req_obj->setRequired(true);
			$req_obj->fldCaption = 'New Password';
			$fld_np->requirements()->addOnChangerequirementUpdate('', 'ne', 'new_pwd', $req_obj);
			$req_obj = new FormFieldRequirement('new_pwd');
			$req_obj->setRequired(false);
			$fld_np->requirements()->addOnChangerequirementUpdate('', 'eq', 'new_pwd', $req_obj); 
			
			$fld_cp = $frm->addPasswordField('Confirm Password', 'confirm_pwd', '', 'confirm_pwd');
			$fld_cp->requirements()->setRequired(true);
			$fld_cp->requirements()->setCompareWith('new_pwd', 'eq','New Password');
			
			$frm->addHiddenField('', 'apr_id', $a_id, 'apr_id');
			$frm->addHiddenField('', 'token', $token, 'token');
			$frm->addSubmitButton('', 'btn_reset', 'Reset Password', 'btn_reset');
			return $frm;
	}
	
	/*function themesetup()
	{
		$post = Syspage::getPostedVar();
		$json = array();
		if($_SESSION['admin_logged']){
			$post['admin_id']=$_SESSION['admin_logged'];				
			if($this->Admin->layoutUpdate($post)){
				dieJsonSuccess('Setting updated successfully.');				
			}else{
				dieJsonError($this->Admin->getError());			
			}
		}else{
			dieJsonError('Invalid Request!');	
		}		
	}*/
	
	function profile(){
		$frm = $this->getAdminAccountForm();
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if($_SESSION['logged_admin']['admin_logged_id'] == intval($post['admin_id'])){
				$this->addUpdate($frm, $post);
			}else{
				Message::addErrorMessage('Error: Invalid Request!');
				Utilities::redirectUser(Utilities::generateUrl('admin',''));
			}
		}
		$admin = $this->Admin->getAdminById($_SESSION['logged_admin']['admin_logged_id']);
		$frm->fill($admin);
		$this->set('adminProfile', $admin);
		$this->set('frmProfile', $frm);
		$this->_template->render();
	}
	
	function change_password()
	{
		$frm=$this->getChangePasswordForm();
		$post=Syspage::getPostedVar();		
		if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['btn_submit'])){						
			if($_SESSION['logged_admin']['admin_logged_id']==intVal($post['admin_id'])){				
				$this->updatePassword($frm,$post);
			}else{
				Message::addErrorMessage('Error: Invalid Request!');
				Utilities::redirectUser(Utilities::generateUrl('admin',''));
			}			
		}
		$admin = $this->Admin->getAdminById($_SESSION['logged_admin']['admin_logged_id']);
		$frm->fill($admin);
		$this->set('adminProfile', $admin);
		$this->set('frmChangePass', $frm);
		$this->_template->render();
	}
	
	private function updatePassword($frm, $data)
	{		
		if(!$frm->validate($data)){
			Message::addErrorMessage($frm->getValidationErrors());
		}else{			
			if($this->Admin->updateAdminPass($data)){
				Message::addMessage('Success: Password updated successfully.');
				Utilities::redirectUser($data["page"]);
			}else{
				Message::addErrorMessage($this->Admin->getError());
			}
		}
	}
	
	private function addUpdate($frm, $data){
		if(!$frm->validate($data)){
			Message::addErrorMessage($frm->getValidationErrors());
		}else{			
			if($this->Admin->addUpdate($data)){
				Message::addMessage('Success: Details updated successfully.');
				Utilities::redirectUser($data["page"]);
			}else{
				Message::addErrorMessage($this->Admin->getError());
			}
		}
	}
	
	private function getAdminAccountForm($dispPassWordFld=false){
		$frm = new Form('frmAccount', 'frmStrengthPassword');
		$frm->setValidatorJsObjectName('frmAccount_validator');
		$frm->setRequiredStarWith('caption');
		$frm->setExtra('class="web_form"');		
		$frm->setJsErrorDisplay('afterfield');
		$frm->setTableProperties('class="table_form_horizontal" width="100%" cellspacing="0" cellpadding="0" border="0"');
		$frm->setLeftColumnProperties('width="25%"');
		$frm->addHiddenField('', 'admin_id', '', 'admin_id');
		$frm->addRequiredField('Full Name', 'admin_full_name', '', 'admin_full_name');
		$frm->addEmailField('Email', 'admin_email', '', 'admin_email')->requirements()->setRequired(true);
		$fld_username = $frm->addRequiredField('Username', 'admin_username', '', 'admin_username');
		$fld_username->requirements()->setUsername(true);		
		
		if($dispPassWordFld==true){
			$fld=$frm->addPasswordField('Password', 'a_pwd', '', 'check-password', ' class="field8"');
			$fld->requirements()->setRequired(true);
			$fld->requirements()->setLength(4,20);
			$fld->html_before_field='<div id="check-password-result">';
			$fld->html_after_field='</div>';
			$fld=$frm->addPasswordField('Confirm Password', 'a_confirm_pwd', '', '', ' class="field8"');
			$fld->requirements()->setRequired();
			$fld->requirements()->setCompareWith('a_pwd','eq','Password');			
		}
		
		$frm->addSubmitButton('', 'btn_submit', 'Save changes', 'btn_submit');
		return $frm;
	}
	
	protected function adminUserchangePasswordForm(){
		$frm=new Form('frmAdminUser');
		$frm->setExtra('class="web_form" validator="tplValidator"');
		$frm->setRequiredStarWith('caption');
		$frm->setValidatorJsObjectName('tplValidator');
		$frm->addHiddenField('', 'page', Utilities::getCurrUrl(), 'admin_id');
		$frm->addHiddenField('', 'admin_id', '', 'admin_id');
		$fld=$frm->addPasswordField('New Password', 'user_password', '', '', ' class="medium"');
		$fld->requirements()->setRequired(true);
		$fld->requirements()->setLength(4,20);
		$fld=$frm->addPasswordField('Confirm Password', 'user_conpassword', '', '', ' class="medium"');
		$fld->requirements()->setRequired();
		$fld->requirements()->setCompareWith('user_password','eq','');
		$frm->setJsErrorDisplay('afterfield');
		$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="15%"');
		return $frm;
	}
	
	function user_password($admin_id = 0)
	{
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),SUBADMINS)) {
            die(Admin::getUnauthorizedMsg());
        }
		
		if(!is_numeric($admin_id) || intval($admin_id) < 1){
			Message::addErrorMessage('Error: Invalid Request!');
			Utilities::redirectUser(Utilities::generateUrl('admin'));
		}
		
		$frm=$this->adminUserchangePasswordForm();
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['btn_submit'])){	
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{			
				if($this->Admin->updateAdminUserPassword($post)){
					Message::addMessage('Success: Password updated successfully.');
					Utilities::redirectUser($post["page"]);
				}else{
					Message::addErrorMessage($this->Admin->getError());
				}
			}			
		}
		$admin = $this->Admin->getAdminById($admin_id);		
		$frm->fill($admin);
		$this->set('adminProfile', $admin);
		$this->set('frmChangePass', $frm);
		$this->_template->render();
	}
	
	function uploadAdminImage()
	{
		$admin_id=intval($_SESSION['logged_admin']['admin_logged_id']);
		$post = Syspage::getPostedVar();		
		$arr=array();
		$arr['admin_id']=$admin_id;
		if(($_FILES['admin_image']['size']!=0) && ($_FILES['admin_image']['size'] < 1000000)) {
			if (Utilities::isUploadedFileValidImage($_FILES['admin_image'])){
				if(!Utilities::saveImage($_FILES['admin_image']['tmp_name'],$_FILES['admin_image']['name'], $admin_image, 'user-avatar/')){
		       		Message::addError($admin_image);
    			}
				$arr["admin_image"]=$admin_image;
				if($this->Admin->updateAdminImage($arr)){
					dieJsonSuccess('Your image uploaded successfully.');
				}else{
					dieJsonError($pObj->getError());
    		}
			}else{
				dieJsonError($_FILES['admin_image']['name'].' - Invalid: Image Type.');
			}
		}else{
			dieJsonError(Utilities::getLabel('M_ERROR_FILE_SIZE'));
		}
	}
	
	function getImageUploadTab()
	{
		$admin = $this->Admin->getAdminById($_SESSION['logged_admin']['admin_logged_id']);
		$admin_image=$admin['admin_image'];
		$this->set('admin_image', $admin_image );
		$this->_template->render(false,false,'admin/profile_image.php');
	}
	
	function getChangePasswordForm()
	{
		$frm = new Form('frmChangePass', 'frmStrengthPassword');
		$frm->setValidatorJsObjectName('frmAccount_validator');
		$frm->setRequiredStarWith('caption');
		$frm->setExtra('class="web_form"');		
		$frm->setJsErrorDisplay('afterfield');
		$frm->setTableProperties('class="table_form_horizontal" width="100%" cellspacing="0" cellpadding="0" border="0"');
		$frm->setLeftColumnProperties('width="25%"');
		$frm->addHiddenField('', 'page', Utilities::getCurrUrl(), 'admin_id');
		$frm->addHiddenField('', 'admin_id', '', 'admin_id');
		$fld_cur_pwd = $frm->addPasswordField('Current Password', 'a_current_pwd', '', 'a_current_pwd', 'title="Password"');
		$fld_cur_pwd->requirements()->setRequired(true);
				
		$fld_new_pwd = $frm->addPasswordField('New Password', 'a_pwd', '', 'check-password', 'title="Password"');
		$fld_new_pwd->requirements()->setRequired(true);
		$fld_new_pwd->requirements()->setLength(4,20);
		$fld_new_pwd->html_before_field='<div id="check-password-result">';
		$fld_new_pwd->html_after_field='</div>';
		$fld_con_pwd = $frm->addPasswordField('Confirm Password', 'a_confirm_pwd', '', 'a_confirm_pwd', 'title="Confirm Password"');
		$fld_con_pwd->requirements()->setCompareWith('a_pwd', 'eq','Password');
		
		$frm->addSubmitButton('', 'btn_submit', 'Update', 'btn_submit');
		return $frm;
	}
	
	function default_action(){
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),SUBADMINS)) {
             die(Admin::getUnauthorizedMsg());
        }
		$this->set('admins', $this->Admin->getAllAdmins());
		$this->_template->render();
    }
	
	function edit($admin_id = 0){
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),SUBADMINS)) {
             die(Admin::getUnauthorizedMsg());
        }
		
		if(!is_numeric($admin_id) || intval($admin_id) < 1){
			Message::addErrorMessage('Error: Invalid Request!');
			Utilities::redirectUser(Utilities::generateUrl('admin'));
		}
		$frm = $this->getAdminAccountForm();
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			$this->addUpdate($frm, $post);
		}
		$admin = $this->Admin->getAdminById(intval($admin_id));
		if ($admin["admin_is_super_admin"]){
			Message::addErrorMessage('Error: You are not authorized to view this page.');
			Utilities::redirectUser(Utilities::generateUrl('admin'));
		}
		$frm->fill($admin);
		$this->set('frmEdit', $frm);
		$this->_template->render();
	}
	
	function add(){
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),SUBADMINS)) {
             die(Admin::getUnauthorizedMsg());
        }
		
		$frm = $this->getAdminAccountForm(true);		 
		/*$fld = $frm->getField('a_pwd');
		$fld->field_caption = 'Password';
		$fld->requirements()->fldCaption = 'Password';
		$fld->requirements()->setRequired(true);
		$fld->setRequiredStarWith('caption');
		$frm->getField('a_confirm_pwd')->field_caption = 'Confirm Password';*/
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			$this->addUpdate($frm, $post);
		}
		$this->set('frmAdd', $frm);
		$this->_template->render();
	}
	
	function delete($admin_id) {
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),SUBADMINS)) {
             die(Admin::getUnauthorizedMsg());
        }
		if(!is_numeric($admin_id) || intval($admin_id) < 1){
			Message::addErrorMessage('Error: Invalid Request!');
			Utilities::redirectUser(Utilities::generateUrl('admin'));
		}
		$admin = $this->Admin->getAdminById(intval($admin_id));
		if ($admin["admin_is_super_admin"]){
			Message::addErrorMessage('Error: You are not authorized to perform this action.');
			Utilities::redirectUser(Utilities::generateUrl('admin'));
		}
		
		if($this->Admin->delete($admin_id)){
			Message::addMessage('Success: Record has been deleted.');
		}else{
			Message::addErrorMessage($this->Admin->getError());
		}
		Utilities::redirectUserReferer();
    }
	
	function permissions($admin_id = 0){
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),SUBADMINS)) {
             die(Admin::getUnauthorizedMsg());
        }
		if(!is_numeric($admin_id) || intval($admin_id) < 1){
			Message::addErrorMessage('Error: Invalid Request!');
			Utilities::redirectUser(Utilities::generateUrl('admin'));
		}
		$admin_id = intval($admin_id);
		$admin = $this->Admin->getAdminById(intval($admin_id));
		if ( (!$admin) || ($admin["admin_is_super_admin"])){
			Message::addErrorMessage('Error: You are not authorized to perform this action.');
			Utilities::redirectUser(Utilities::generateUrl('admin'));
		}
		$post = Syspage::getPostedVar();
		$frm = $this->getPermissionsForm($admin_id);
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				if($post['admin_id'] == intval($admin_id)){
					if($this->Admin->updatePermissions($post)){
						Message::addMessage('Success: Permissions updated successfully.');
						Utilities::redirectUser(Utilities::generateUrl('admin', 'permissions', array($admin_id)));
					}else{
						Message::addErrorMessage($this->Admin->getError());
					}
				}else{
					Message::addErrorMessage('Error: Invalid Request!');
				}
			}
		}
		
		$permissions = $this->Admin->getPermissions($admin_id);
		$frm_values = array('admin_id'=>$admin_id);
		if(is_array($permissions)){
			$frm_values = array_merge($permissions, $frm_values);
		}
		$frm->fill($frm_values);
		$admin = $this->Admin->getAdminById($admin_id);
		$this->set('admin_detail', $admin['admin_full_name'] . ' (' . $admin['admin_username'] . ')');
		$this->set('frmPermissions', $frm);
		$this->_template->render();
	}
	
	private function getPermissionsForm($admin_id = 0){
		$frm = new Form('frmPermissions', 'frmPermissions');
		$frm->setExtra('class="web_form"');
		$frm->setTableProperties('class="table_form_horizontal" width="100%" cellspacing="0" cellpadding="0" border="0"');
		$frm->setLeftColumnProperties('width="20%"');
		$frm->addHiddenField('', 'admin_id', '', 'admin_id');
		$adminObj=new Admin();
		$rows = $adminObj->getAdminPermissionsArr($admin_id);
		foreach($rows as $key=>$val)
				$adminPermissions[]=$val["ap_module"];
			
		$frm->addCheckBoxes("<h3>Permissions</h3>", 'user_permissions[]', $adminObj->getPermissionsAssocArr(), $adminPermissions);
		$fld=$frm->addCheckBox('<strong>Check ALL</strong>', 'check_all', '1', 'checkAll');
		$frm->addSubmitButton('', 'btn_submit', 'Update', 'btn_submit');
		return $frm;
	}
	
}