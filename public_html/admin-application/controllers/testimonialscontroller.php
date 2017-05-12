<?php
class TestimonialsController extends BackendController {
	
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,TESTIMONIALS);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Testimonials Management", Utilities::generateUrl("testimonials"));
    }
	
	protected function getSearchForm() {
        $frm=new Form('frmSearchTestimonials','frmSearchTestimonials');
		$frm->setFieldsPerRow(2);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('GET');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addTextBox('Keyword', 'keyword','','',' class="small"');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchTestimonials(this); return false;');
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
	
	function listTestimonials($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
			$tObj=new Testimonials();
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
            $this->set('records', $tObj->getTestimonials($post));
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
	
	
	
	
    function form($testimonial_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("Testimonial Setup",'');
		$this->set('breadcrumb', $this->b_crumb->output());
		$tObj=new Testimonials();
		if ($testimonial_id>0)
        	$testimonial = $tObj->getTestimonialById($testimonial_id);
        $frm = $this->getForm($testimonial_id,$testimonial);
		
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				if($post['testimonial_id'] != $testimonial_id){
					Message::addErrorMessage('Error: Invalid Request!!');
					Utilities::redirectUser(Utilities::generateUrl('testimonials'));
				}else{
					
					if (Utilities::isUploadedFileValidImage($_FILES['testimonial_file'])){
						if(!Utilities::saveImage($_FILES['testimonial_file']['tmp_name'],$_FILES['testimonial_file']['name'], $saved_image_name, 'testimonials/')){
		               		Message::addError($saved_image_name);
    		   			}
						$post["testimonial_image"]=$saved_image_name;
    		    	}
					
					if($tObj->addUpdate($post)){
						Message::addMessage('Success: Testimonial details added/updated successfully.');
						Utilities::redirectUser(Utilities::generateUrl('testimonials'));
					}else{
						Message::addErrorMessage($tObj->getError());
					}
				}
			}
			$frm->fill($post);
		}
		
        $this->set('frm', $frm);
        $this->_template->render(true,true);
    }
	
	
    protected function getForm($testimonial_id,$data=array()) {
        $frm = new Form('frmTestimonial','frmTestimonial');
		$frm->captionInSameCell(false);
        $frm->addHiddenField('', 'testimonial_id', $testimonial_id, 'testimonial_id');
        $fld=$frm->addRequiredField('Testimonial By', 'testimonial_name', '', '', ' class="medium"');
		$fld->html_after_field = 'Enter name here.';
		$fld=$frm->addRequiredField('Testimonial Location', 'testimonial_address', '', '', ' class="medium"');
        $fld = $frm->addFileUpload('Testimonial Image', 'testimonial_file');
		$fld->html_before_field='<div class="filefield"><span class="filename"></span>';
		$fld->html_after_field='<label class="filelabel">Browse File</label></div>';
        if ($testimonial_id>0) {
			$frm->addHTML('','','<img src="'.Utilities::generateUrl('image', 'testimonial_image', array($data["testimonial_image"],"THUMB"),CONF_WEBROOT_URL).'" />');
        } 
		$fld = $frm->addTextArea('Testimonial Text', 'testimonial_text', '', '', 'rows=6 class="medium"')->requirements()->setRequired();
        
        $frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
        $frm->setExtra(' validator="testimonialfrmValidator" class="web_form" rel="upload"');
        $frm->setValidatorJsObjectName('testimonialfrmValidator');
		$frm->setJsErrorDisplay('afterfield');
        $frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="20%"');
	    $frm->fill($data);
        return $frm;
    }
    
	function delete() {
        if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
        $post = Syspage::getPostedVar();
		$tObj=new Testimonials();
        $testimonial_id = intval($post['id']);
        $testimonial = $tObj->getTestimonialById($testimonial_id);
		if($testimonial==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		if($tObj->delete($testimonial_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($tObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
    
	
}
