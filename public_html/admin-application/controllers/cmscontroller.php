<?php
class CmsController extends BackendController {
	
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,CONTENTPAGES);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("CMS Pages Management", Utilities::generateUrl("cms"));
    }
	
	protected function getSearchForm() {
        $frm=new Form('frmCmsPagesSearch','frmCmsPagesSearch');
		$frm->setFieldsPerRow(3);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('GET');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchPages(this); return false;');
        return $frm;
    }
	
	function default_action() {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $frm = $this->getSearchForm();
        $this->set('frmPost', $frm);
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listPages($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mode'])) {
			$cmsObj=new Cms();
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
            $this->set('records', $cmsObj->getCmsPages($post));
            $this->set('pages', $cmsObj->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $cmsObj->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
    function form($page_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("CMS Page Setup",'');
		$this->set('breadcrumb', $this->b_crumb->output());
		
		$cmsObj=new Cms();
        $page_id = intval($page_id);
        $frm = $this->getForm();
        if ($page_id > 0) {
            $data = $cmsObj->getData($page_id);
            $frm->fill($data);
        }
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
					
					$oldSlug=$data['seo_url_keyword'];
					$slug=Utilities::slugify($post['seo_url_keyword']);
					if (($slug != $oldSlug) && (!empty($post['seo_url_keyword']))){  
					    $i = 1; $baseSlug = $slug;              
						$url_alias=new Url_alias();
					    while($url_alias->getUrlAliasByKeyword($slug)){                
					        $slug = $baseSlug . "-" . $i++;     
					        if($slug == $oldSlug){              
				           		break;                          
					       }
					    }
					}
					$post['seo_url_keyword']=$slug;	
					if (!$error){
						if($cmsObj->addUpdatePage($post)){
							Message::addMessage('Success: CMS Page details added/updated successfully.');
							Utilities::redirectUser(Utilities::generateUrl('cms'));
						}else{
							Message::addErrorMessage($cmsObj->getError());
						}
					}
			}
			$frm->fill($post);
		}
        $this->set('frm', $frm);
        $this->_template->render(true,true);
    }
    protected function getForm() {
        $frm = new Form('frmCms');
		$frm->setExtra(' validator="cmsfrmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('cmsfrmValidator');
		$frm->addHtml('<h6>Section 1: Basic Information about Page.</h6>', 'htmlNote','','&nbsp;');
        $frm->addHiddenField('', 'page_id',0,'page_id');
		$frm->addRequiredField('Title', 'page_title','', '', ' class="input-xlarge" onKeyUp="Slugify(this.value,\'seo_url_keyword\',\'page_id\')"');
		
		$fld=$frm->addRequiredField('URL Keywords', 'seo_url_keyword','', 'seo_url_keyword', ' class="input-xlarge"');
		$fld->html_after_field='<small>Do not use spaces, instead replace spaces with - and make sure the keyword is globally unique.</small>';
		
		$frm->addHtml('Content');
		$fld=$frm->addHtmlEditor('', 'page_content', '', 'page_content');
		$frm->addHtml('<h6>Section 2: SEO/Meta Data (Optional)</h6>', 'htmlNote','','&nbsp;');
		$frm->addTextBox('Page Title', 'page_meta_title','','page_meta_title','class="medium"');
		$frm->addTextArea('Meta Keywords', 'page_meta_keywords','','page_meta_keywords','class="medium" cols="112"');
		$frm->addTextArea('Meta Description', 'page_meta_desc','','page_meta_desc','class="medium" cols="112"');
		$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="20%"');
        return $frm;
    }
	function delete() {
        if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
        $post = Syspage::getPostedVar();
		$cmsObj=new Cms();
        $page_id = intval($post['id']);
        $cms_page = $cmsObj->getData($page_id);
		if($cms_page==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		if($cmsObj->delete($page_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($cmsObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	
	
	
}