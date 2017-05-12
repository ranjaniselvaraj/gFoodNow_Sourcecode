<?php
class SupplierController extends CommonController{
	function __construct($model, $controller, $action){
		parent::__construct($model, $controller, $action);
		if (!Settings::getSetting("CONF_ACTIVATE_SEPARATE_SIGNUP_FORM"))
			Utilities::redirectUser(Utilities::generateUrl('user','account'));
	}
	function default_action(){
		Utilities::redirectUser(Utilities::generateUrl('supplier','account'));
	}
	function getSellerRegistrationForm($type){
		$frm=new Form('frmRegistration','frmStrengthPassword');
		$frm->setValidatorJsObjectName('system_validator');
		$frm->setLeftColumnProperties('width="25%"');
		$frm->setExtra('class="siteForm"');
		$frm->captionInSameCell(false);
		$frm->addHiddenField('','pref', 'S');
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
		$fld=$frm->addHtml('&nbsp;','',sprintf(Utilities::getLabel('L_By_using_agree_terms_conditions'),'<a target="_blank" href="'.Utilities::generateUrl('cms', 'view',array(Settings::getSetting("CONF_ACCOUNT_TERMS"))).'">'.Utilities::getLabel('L_Terms_Conditions').'</a>'));
//$fld->merge_caption=true;
		$fld=$frm->addSubmitButton('','btn_signup',strtoupper(Utilities::getLabel('BTN_REGISTER')),'',' class="btn primary-btn"');
		//$frm->getField("btn_signup")->html_after_field='&nbsp;&nbsp;<a class="btn secondary-btn" href="'.Utilities::generateUrl('user', 'forgot_password').'">'.Utilities::getLabel('L_FORGOT_PASSWORD').'</a>';
		$frm->setJsErrorDisplay('afterfield');
		return $frm;
	}
	function account($p){
		$uObj = new User();
		Syspage::addCss(array('css/seller-steps.css'), false);
		$referrer=parse_url(getenv("HTTP_REFERER"));
		if (isset($p) && (strtolower($p)=="api")){
			$hide_header_footer=true;
			$_SESSION['hide_header_footer']=true;
		}
		Utilities::checkIsAlreadyLoggedIn(); 
		$registrationFrm=$this->getSellerRegistrationForm(CONF_BUYER_SELLER_USER_TYPE);
		$become_seller_page=str_replace('{SITEROOT}', CONF_WEBROOT_URL, Settings::getSetting("CONF_SELL_SITENAME_PAGE"));
		if ($referrer['path']==$become_seller_page){
			$registrationFrm->fill(array("pref"=>"S"));
		}
		$this->set('RegistrationFrm', $registrationFrm);
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_signup'])){
			if(!$registrationFrm->validate($post)){
				Message::addErrorMessage($registrationFrm->getValidationErrors());
			}else{
				$arr = array_merge($post,array("user_email_verified"=>0,"user_type"=>0));
				if($uObj->addUser($arr)){
					$user_verified=$uObj->getAttribute('user_email_verified');
					if($user_verified==1) {
						Message::addMessage(Utilities::getLabel('M_SUCCESS_USER_SIGNUP_VERIFIED'));
					} else {
						Message::addMessage(Utilities::getLabel('M_SUCCESS_USER_SIGNUP'));
					}
					$_SESSION['registered_supplier']['id']=$uObj->getUserId();
					Utilities::redirectUser(Utilities::generateUrl('supplier','profile_activation'));
				}else{
					$registrationFrm->fill($post);
					Message::addErrorMessage($uObj->getError());
				}
			}
		} 
		$this->_template->render();	
	}
	private function getSupplierForm(){
		$userObj=new User();
		$frm = new Form('frmSupplierForm', 'frmSupplierForm');
		$frm->setExtra('class="siteForm"');
		$frm->setTableProperties('class="tbl-twocell"');
		$frm->setLeftColumnProperties('width="35%"');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setRequiredStarWith('caption');
		$supplier_form_fields=$userObj->getSupplierFormFields();
		foreach ($supplier_form_fields as $field) {
			switch($field['sformfield_type']) {
				case 'file':
				$fld=$frm->addButton("<label>".$field['sformfield_caption']."</label>", 'button['.$field['sformfield_id'].']',Utilities::getLabel('M_Upload_File'),'button-upload'.$field['sformfield_id'],'data-loading-text="Loading" class="btn btn-default btn-block"');
				if ($field['sformfield_required'])
					$fld->requirements()->setRequired();
				$fld->html_after_field='<br/><span</span>  <input type="hidden"  name="sformfield['.$field['sformfield_id'].']"/><span id="input-sformfield'.$field['sformfield_id'].'"></span>';
				break;
				case 'text':
				$fld=$frm->addTextBox("<label>".$field['sformfield_caption']."</label>", 'sformfield['.$field['sformfield_id'].']','','input-sformfield'.$field['sformfield_id'],'Placeholder="'.$field['sformfield_caption'].'"');
				if ($field['sformfield_required'])
					$fld->requirements()->setRequired();
				break;
				case 'textarea':
				$fld=$frm->addTextArea("<label>".$field['sformfield_caption']."</label>", 'sformfield['.$field['sformfield_id'].']','','input-sformfield'.$field['sformfield_id'],'Placeholder="'.$field['sformfield_caption'].'"');
				if ($field['sformfield_required'])
					$fld->requirements()->setRequired();
				break;
				case 'date':
				$fld=$frm->addTextBox("<label>".$field['sformfield_caption']."</label>", 'sformfield['.$field['sformfield_id'].']','','input-sformfield'.$field['sformfield_id'],'class="date calendar" Placeholder="'.$field['sformfield_caption'].'"');
				if ($field['sformfield_required'])
					$fld->requirements()->setRequired();
				break;
				case 'datetime':
				$fld=$frm->addTextBox("<label>".$field['sformfield_caption']."</label>", 'sformfield['.$field['sformfield_id'].']','','input-sformfield'.$field['sformfield_id'],'class="datetime calendar" Placeholder="'.$field['sformfield_caption'].'"');
				if ($field['sformfield_required'])
					$fld->requirements()->setRequired();
				break;
				case 'time':
				$fld=$frm->addTextBox("<label>".$field['sformfield_caption']."</label>", 'sformfield['.$field['sformfield_id'].']','','input-sformfield'.$field['sformfield_id'],'class="time calendar" Placeholder="'.$field['sformfield_caption'].'"');
				if ($field['sformfield_required'])
					$fld->requirements()->setRequired();
				break;
			}
			$fld->html_after_field.='<small>'.$field['sformfield_extra']."</small>";
		}
		$frm->setTableProperties(' width="100%" border="0" class="editformTable" cellpadding="0" cellspacing="0"');	
		$fld_submit = $frm->addSubmitButton(null, 'btn_submit', Utilities::getLabel('M_Submit'), 'btn_submit', 'class="btn primary-btn"');
		$fld_html = $frm->addHTML('','html','<p>'.Utilities::getLabel('M_Do_not_register_as_supplier').', <a href="'.Utilities::generateUrl('supplier','signup_buyer').'">'.Utilities::getLabel('M_Signup_as_Buyer').' </a><br>
			'.Utilities::getLabel('M_Do_not_register_as_supplier').', <a onclick="return(confirm(\''.Utilities::getLabel('L_Are_you_sure_delete_account').'\'));" href="'.Utilities::generateUrl('supplier','delete_account').'">'.Utilities::getLabel('M_Delete_my_account').' </a> </p>');
		return $frm;
	}
	function profile_activation($p){
		Utilities::checkIsAlreadyLoggedIn(); 
		$uObj = new User();
		if (!$uObj->isSupplierRegistered()){
			Message::addMessage(Utilities::getLabel('M_ERROR_INVALID_ACCESS'));
			Utilities::redirectUser(Utilities::generateUrl('supplier','account'));
		}
		Syspage::addCss(array('css/seller-steps.css'), false);
		$user_id = $uObj->getPartialRegUserId();
		$user_details=$uObj->getUser(array('user_id'=>$user_id, 'get_flds'=>array('user_id', 'user_type')));
		if ($user_details && $user_details['user_type']){
			Message::addMessage(Utilities::getLabel('M_ERROR_INVALID_ACCESS'));
			Utilities::redirectUser(Utilities::generateUrl('supplier'));
		}
		$supplier_request=$uObj->getUserSupplierRequests(array("user"=>$user_id,"pagesize"=>1));
		if ($supplier_request && $supplier_request["usuprequest_attempts"]>3){
			Message::addMessage(Utilities::getLabel('M_You_have_already_consumed_max_attempts'));
			Utilities::redirectUser(Utilities::generateUrl('supplier','view_request',array($supplier_request["usuprequest_id"])));
		}
		if ($supplier_request && $p!="reopen")
			Utilities::redirectUser(Utilities::generateUrl('supplier','view_request',array($supplier_request["usuprequest_id"])));
		$supplierFrm=$this->getSupplierForm();
		$supplierFrm->addHiddenField('','id', $supplier_request['usuprequest_id']);
		$this->set('supplierFrm', $supplierFrm);
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			$supplier_form_fields=$uObj->getSupplierFormFields();
			foreach ($supplier_form_fields as $field) {
				if ($field['sformfield_required'] && empty($post["sformfield"][$field['sformfield_id']])) {
					$error_messages[]=sprintf(Utilities::getLabel('M_Label_Required'), $field['sformfield_caption']);
				}
			}
			if (!$error_messages){
				$random = strtoupper(uniqid());
				$reference_number = substr($random, 0, 5) . '-' . substr($random, 5, 5) . '-' . substr($random . rand(10, 99), 10, 5);
				$data=array_merge($post,array("user"=>$user_id,"reference"=>$reference_number));
				if($uObj->addSupplierRequestData($data)){
					$uObj->setUserId($uObj->getPartialRegUserId());
					if ($uObj->getAttribute('usuprequest_status')==1){
						$user_type = Settings::getSetting("CONF_ACTIVATE_SEPARATE_SIGNUP_FORM")?CONF_SELLER_USER_TYPE:CONF_BUYER_SELLER_USER_TYPE;
						$uObj->updateAttributes(array("user_type"=>$user_type));
						Message::addMessage(Utilities::getLabel('M_Your_supplier_account_approved'));
					}else{
						Message::addMessage(Utilities::getLabel('M_Your_supplier_approval_form_request_sent'));
					}
					$uObj->updateAttributes(array("user_buyer_supp_pref"=>'S'));
					Utilities::redirectUser(Utilities::generateUrl('supplier','confirmation'));
				}else{
					Message::addErrorMessage(Utilities::getLabel('M_Error_details_not_saved'));
				}
			}else{
				Message::addErrorMessage($error_messages);
				$supplierFrm->fill($post);
			}
		} 
		$this->_template->render();	
	}
	function view_request($id){
		Utilities::checkIsAlreadyLoggedIn(); 
		$uObj = new User();
		if (!$uObj->isSupplierRegistered()){
			Utilities::redirectUser(Utilities::generateUrl('supplier','account'));
		}
		Syspage::addCss(array('css/seller-steps.css'), false);
		$user_id = $uObj->getPartialRegUserId();
		$supplier_request=$uObj->getUserSupplierRequests(array("id"=>$id,"user"=>$user_id,"pagesize"=>1));
		if (!$supplier_request){
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_ACCESS'));
			Utilities::redirectUser(Utilities::generateUrl('supplier','account'));
		}
		$this->set('supplier_request',$supplier_request);
		$this->_template->render();
	}
	function confirmation(){
		Utilities::checkIsAlreadyLoggedIn();
		$uObj = new User();
		$uObj->setUserId($uObj->getPartialRegUserId());
		$user_verified=$uObj->getAttribute('user_email_verified');
		$user_type=$uObj->getAttribute('user_type');
		if (in_array($user_type,array(CONF_SELLER_USER_TYPE))){
			$this->set('supplier_profile_activation',1);
		}
		if($user_verified==1) {
			$success_message = Utilities::getLabel('M_SUCCESS_USER_SIGNUP_VERIFIED');
		} else {
			$success_message = Utilities::getLabel('M_SUCCESS_USER_SIGNUP');
		}
		Syspage::addCss(array('css/seller-steps.css'), false);
		$this->set('success_message',$success_message);
		$this->_template->render();	
	}
	function signup_buyer(){
		Utilities::checkIsAlreadyLoggedIn(); 
		$uObj = new User();
		if (!$uObj->isSupplierRegistered()){
			Utilities::redirectUser(Utilities::generateUrl('supplier','account'));
		}
		$uObj->setUserId($uObj->getPartialRegUserId());
		$buyer_user_type = Settings::getSetting("CONF_ACTIVATE_SEPARATE_SIGNUP_FORM")?CONF_BUYER_USER_TYPE:CONF_BUYER_SELLER_USER_TYPE;
		if($uObj->updateAttributes(array("user_type"=>$buyer_user_type,"user_buyer_supp_pref"=>'B'))){
			Message::addMessage(Utilities::getLabel('M_Your_account_successfully_registered_as_buyer'));
			Utilities::redirectUser(Utilities::generateUrl('supplier','confirmation'));
		} else {
			Message::addErrorMessage($uObj->getError());
		}
		Utilities::redirectUser(Utilities::generateUrl('supplier','profile_activation'));
	}
	function delete_account(){
		Utilities::checkIsAlreadyLoggedIn(); 
		$uObj = new User();
		if (!$uObj->isSupplierRegistered()){
			Utilities::redirectUser(Utilities::generateUrl('supplier','account'));
		}
		if($uObj->delete($uObj->getPartialRegUserId())){
			Message::addMessage(Utilities::getLabel('M_Your_account_successfully_deleted'));
		} else {
			Message::addErrorMessage($uObj->getError());
		}
		Utilities::redirectUser(Utilities::generateUrl('supplier','account'));
	}
}
