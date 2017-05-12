<?php
class BannersController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,BANNERS);
    }
	
	function default_action() {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
        $this->set('arr_listing', $this->Banners->getAllBanners());
        $this->_template->render();
    }
    
    function form($banner_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		if ($banner_id>0)
        	$banner = $this->Banners->getData($banner_id);
			
        $frm = $this->getForm($banner_id,$banner);
		
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				if($post['banner_id'] != $banner_id){
					Message::addErrorMessage('Error: Invalid Request!!');
					Utilities::redirectUser(Utilities::generateUrl('banners'));
				}else{
					
					if (Utilities::isUploadedFileValidImage($_FILES['banner_file'])){
						if(!Utilities::saveImage($_FILES['banner_file']['tmp_name'],$_FILES['banner_file']['name'], $saved_image_name, 'banners/')){
		               		Message::addError($saved_image_name);
    		   			}
						$post["banner_image_path"]=$saved_image_name;
    		    	}
					if($this->Banners->addUpdate($post)){
						Message::addMessage('Success: Banner added/updated successfully.');
						Utilities::redirectUser(Utilities::generateUrl('banners'));
					}else{
						Message::addErrorMessage($this->Banners->getError());
					}
				}
			}
			$frm->fill($post);
		}
		
        $this->set('frm', $frm);
        $this->_template->render();
    }
    
	
    protected function getForm($banner_id,$data=array()) {
        $frm = new Form('frmBanner','frmBanner');
		$frm->captionInSameCell(false);
		$fld=$frm->addHtml('<small>*Note:- Banner Dimensions - 600 X 394.</small>','');
		$fld->merge_caption=true;
        $frm->addHiddenField('', 'banner_id', $banner_id, 'banner_id');
		$frm->addHiddenField('', 'banner_position', 0, 'banner_position');
        $frm->addRequiredField('Banner Title', 'banner_title', '', '', ' class="medium"');
		$frm->addSelectBox('Banner Type', 'banner_type', array(0=>'Image',1=>'HTML'),array("0"),'onChange="CallFunc()" id="banner_type" class="medium"','');
		
        $fld = $frm->addFileUpload('Banner Image', 'banner_file');
        $fld->html_before_field = '<div id="banner_image" ><div class="filefield"><span class="filename"></span>';
        if ($data['banner_type']=='0') {
			$fld->html_after_field = '<label class="filelabel">Browse File</label></div><p><b>Current Image:</b><br /><img src="'.Utilities::generateUrl('image', 'banner', array($data["banner_image_path"],'THUMB'),CONF_WEBROOT_URL).'" /></p></div> ';
        } else {
			$fld->html_after_field = '<label class="filelabel">Browse File</label></div></div>';
        }
		
		$fld = $frm->addTextArea('Banner HTML', 'banner_html', '', '', 'rows=6 class="medium"');
        /* $fld->html_before_field = '<div id="banner_html">';
		$fld->html_after_field = '</div>'; */
       	$frm->addTextBox('Banner URL', 'banner_url', '','','placeholder="http://" class="medium"');
        $frm->addSelectBox('Open Link in New Tab', 'banner_link_newtab', array(0=>'No', 1=>'Yes'), '1','class="medium"');
		//$frm->addTextBox('Priority', 'banner_priority', '','','class="medium"')->requirements()->setIntPositive();
        $frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
        $frm->setExtra(' validator="bannerfrmValidator" class="web_form" rel="upload"');
        $frm->setValidatorJsObjectName('bannerfrmValidator');
		$frm->setJsErrorDisplay('afterfield');
        $frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="20%"');
        $frm->fill($data);
        return $frm;
    }
	
	function update_banner_status() {
		if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
		$post = Syspage::getPostedVar();
		$banner_id = intval($post['id']);
        $banner = $this->Banners->getData($banner_id);
		if($banner==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
        $data_to_update = array('banner_status'=>!$banner['banner_status']);
		if($this->Banners->updateBannerStatus($banner_id,$data_to_update)){
			Message::addMessage('Success: Status has been modified successfully.');
			$arr = array('status'=>1, 'msg'=>Message::getHtml(), 'linktext'=>(($banner['banner_status'] == 1)?'Active':'Inactive'));
			die(convertToJson($arr));
		}else{
			Message::addErrorMessage($this->Banners->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	function delete() {
        if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
        $post = Syspage::getPostedVar();
        $banner_id = intval($post['id']);
        $banner = $this->Banners->getData($banner_id);
		if($banner==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		if($this->Banners->delete($banner_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($this->Banners->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
}
