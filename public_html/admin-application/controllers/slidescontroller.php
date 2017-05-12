<?php
class SlidesController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,SLIDES);
    }
	
	function default_action() {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
        $this->set('arr_listing', $this->Slides->getAllSlides());
        $this->_template->render();
    }
    
    function form($slide_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		if ($slide_id>0)
       		$slide = $this->Slides->getData($slide_id);
        $frm = $this->getForm($slide_id,$slide);
		
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				if($post['slide_id'] != $slide_id){
					Message::addErrorMessage('Error: Invalid Request!!');
					Utilities::redirectUser(Utilities::generateUrl('slides'));
				}else{
					
					if (Utilities::isUploadedFileValidImage($_FILES['slide_file'])){
						if(!Utilities::saveImage($_FILES['slide_file']['tmp_name'],$_FILES['slide_file']['name'], $saved_image_name, 'slides/')){
		               		Message::addError($saved_image_name);
    		   			}
						$post["slide_image_path"]=$saved_image_name;
    		    	}
					if($this->Slides->addUpdate($post)){
						Message::addMessage('Success: Slide added/updated successfully.');
						Utilities::redirectUser(Utilities::generateUrl('slides'));
					}else{
						Message::addErrorMessage($this->Slides->getError());
					}
				}
			}
			$frm->fill($post);
		}
		
        $this->set('frm', $frm);
        $this->_template->render();
    }
    
	
    protected function getForm($slide_id,$data=array()) {
	
        $frm = new Form('frmSlide','frmSlide');
		$frm->captionInSameCell(false);
        $frm->addHiddenField('', 'slide_id', $slide_id, 'slide_id');
        $frm->addRequiredField('Slide Title', 'slide_title', '', '', ' class="medium"');
        $fld = $frm->addFileUpload('Slide Image', 'slide_file');
		if($slide_id>0){
        	$fld->html_before_field = '<div id="slide_image" ><div class="filefield"><span class="filename"></span>';
			$fld->html_after_field = '<label class="filelabel">Browse File</label></div><p><b>Current Image:</b><br /><img src="'.Utilities::generateUrl('image', 'slide', array($data["slide_image_path"],"THUMB"),CONF_WEBROOT_URL).'" /></p></div>';
		}else{
			$fld->html_before_field = '<div class="filefield"><span class="filename"></span>';
			$fld->html_after_field = '<label class="filelabel">Browse File</label></div>';
		}
		
		$frm->addTextBox('Slide URL', 'slide_url', '','','placeholder="http://" class="medium"');
        $frm->addSelectBox('Open Link in New Tab', 'slide_link_newtab', array(0=>'No', 1=>'Yes'), '1','class="medium"');
        $frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
        $frm->setExtra(' validator="slidefrmValidator" class="web_form" rel="upload"');
        $frm->setValidatorJsObjectName('slidefrmValidator');
		$frm->setJsErrorDisplay('afterfield');
        $frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="20%"');
		  
        $frm->fill($data);
        return $frm;
    }
	
	function update_slide_status() {
		if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
		$post = Syspage::getPostedVar();
		$slide_id = intval($post['id']);
        $slide = $this->Slides->getData($slide_id);
		if($slide==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
        $data_to_update = array('slide_status'=>!$slide['slide_status']);
		if($this->Slides->updateSlideStatus($slide_id,$data_to_update)){
			Message::addMessage('Success: Status has been modified successfully.');
			$arr = array('status'=>1, 'msg'=>Message::getHtml(), 'linktext'=>(($slide['slide_status'] == 1)?'Active':'Inactive'));
			die(convertToJson($arr));
		}else{
			Message::addErrorMessage($this->Slides->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	function delete() {
        if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
        $post = Syspage::getPostedVar();
        $slide_id = intval($post['id']);
        $slide = $this->Slides->getData($slide_id);
		if($slide==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		if($this->Slides->delete($slide_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($this->Slides->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
}
