<?php
class SocialMediaController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,SOCIALPLATFORMS);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Social Platforms Management", Utilities::generateUrl("socialmedia"));
    }
	
	protected function getSearchForm() {
        $frm=new Form('frmSocialPlatformSearch','frmSocialPlatformSearch');
		$frm->setFieldsPerRow(3);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('GET');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->setOnSubmit('seachSocialPlatforms(this); return false;');
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
		$this->_template->render(true,true);
    }
	
	function listsocialplatforms($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['mode']=='search') {
			$sObj=new Socialmedia();
            $post = Syspage::getPostedVar();
            $this->set('records', $sObj->getSocialmedias($post));
            $total_records = $sObj->getTotalRecords();
			$this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
	function form($splatform_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("Social Platform Setup", Utilities::generateUrl("socialmedia"));
		$this->set('breadcrumb', $this->b_crumb->output());
		$sObj=new Socialmedia();
		if ($splatform_id>0)
        	$socialplatform = $sObj->getSocialMediaById($splatform_id);
        $frm = $this->getForm($splatform_id,$socialplatform);
		
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				if($post['splatform_id'] != $splatform_id){
					Message::addErrorMessage('Error: Invalid Request!!');
					Utilities::redirectUser(Utilities::generateUrl('socialmedia'));
				}else{
					if (Utilities::isUploadedFileValidImage($_FILES['splatform_file'])){
						if(!Utilities::saveImage($_FILES['splatform_file']['tmp_name'],$_FILES['splatform_file']['name'], $saved_icon_file_name, 'socialplatforms/')){
		               		Message::addError($saved_icon_file_name);
    		   			}
						$post["splatform_icon_file"]=$saved_icon_file_name;
    		    	}
					if (empty($splatform_id) && (!file_exists($_FILES['splatform_file']['tmp_name']) || !is_uploaded_file($_FILES['splatform_file']['tmp_name']))){
						Message::addError('Error: Please select icon file.');
						Utilities::redirectUser(Utilities::generateUrl('socialmedia'));
					}
					if($sObj->addUpdate($post)){
						Message::addMessage('Success: Social platform details added/updated successfully.');
						Utilities::redirectUser(Utilities::generateUrl('socialmedia'));
					}else{
						Message::addErrorMessage($sObj->getError());
					}
				}
			}
			$frm->fill($post);
		}
		
        $this->set('frm', $frm);
        $this->_template->render(true,true);
    }
	
	
    protected function getForm($splatform_id,$data=array()) {
        $frm = new Form('frmSocialPlatform','frmSocialPlatform');
		$frm->captionInSameCell(false);
        $frm->addHiddenField('', 'splatform_id', $splatform_id, 'splatform_id');
        $frm->addRequiredField('Title', 'splatform_title');
		$fld = $frm->addRequiredField('URL', 'splatform_url');
		$fld->requirements()->setRegularExpressionToValidate('^(http(?:s)?\:\/\/[a-zA-Z0-9]+(?:(?:\.|\-)[a-zA-Z0-9]+)+(?:\:\d+)?(?:\/[\w\-]+)*(?:\/?|\/\w+\.[a-zA-Z]{2,4}(?:\?[\w]+\=[\w\-]+)?)?(?:\&[\w]+\=[\w\-]+)*)$');
		$fld = $frm->addFileUpload('Icon Image', 'splatform_file');
        $fld->html_before_field = '<div id="banner_image" ><div class="filefield"><span class="filename"></span>';
        if (!empty($data['splatform_icon_file'])) {
			$fld->html_after_field = '<label class="filelabel">Browse File</label></div><p><b>Current Image:</b><br /><img src="'.Utilities::generateUrl('image', 'social_platform_icon', array($data["splatform_icon_file"],'SMALL'),CONF_WEBROOT_URL).'" /></p></div> ';
        } else {
			$fld->html_after_field = '<label class="filelabel">Browse File</label></div></div>';
        }
		
        $frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
        $frm->setExtra(' validator="socialplatformfrmValidator" class="web_form" rel="upload"');
        $frm->setValidatorJsObjectName('socialplatformfrmValidator');
		$frm->setJsErrorDisplay('afterfield');
        $frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="20%"');
	    $frm->fill($data);
        return $frm;
    }
	
	function update_social_platform_status() {
		if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
		$post = Syspage::getPostedVar();
		$sObj=new Socialmedia();
        $splatform_id = intval($post['id']);
        $socialplatform = $sObj->getSocialMediaById($splatform_id);
		if($socialplatform==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		$data_to_update = array('splatform_status'=>!$socialplatform['splatform_status']);
		if($sObj->updateSocialPlatformStatus($splatform_id,$data_to_update)){
			Message::addMessage('Success: Status has been modified successfully.');
			$arr = array('status'=>1, 'msg'=>Message::getHtml(), 'linktext'=>(($socialplatform['splatform_status'] == 1)?'Active':'Inactive'));
			die(convertToJson($arr));
		}else{
			Message::addErrorMessage($sObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	function delete() {
        if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
        $post = Syspage::getPostedVar();
		$sObj=new Socialmedia();
        $splatform_id = intval($post['id']);
        $socialplatform = $sObj->getSocialMediaById($splatform_id);
		if($socialplatform==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		if($sObj->delete($splatform_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($sObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
    
	
}