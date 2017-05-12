<?php
class FaqsController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,FAQMANAGEMENT);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("FAQ Management", Utilities::generateUrl("faqs"));
    }
	
	protected function getSearchForm() {
        $frm=new Form('frmFAQSearch','frmFAQSearch');
		$frm->setFieldsPerRow(2);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addTextBox('Keyword', 'keyword','','',' class="small"');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchFAQs(this); return false;');
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
	
	function listfaqs($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
			$faqObj=new Faqs();
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
            $this->set('records', $faqObj->getFaqs($post));
            $this->set('pages', $faqObj->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $faqObj->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
	
    function form($faq_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("FAQ Setup", '');
		$this->set('breadcrumb', $this->b_crumb->output());
		$faqObj=new Faqs();
        $faq_id = intval($faq_id);
        $frm = $this->getForm();
        if ($faq_id > 0) {
            $data = $faqObj->getData($faq_id);
            $frm->fill($data);
        }
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				if($post['faq_id'] != $faq_id){
					Message::addErrorMessage('Error: Invalid Request!!');
					Utilities::redirectUser(Utilities::generateUrl('faqs'));
				}else{
					$arr=array_merge($post,array("faq_slug"=>Utilities::slugify($post["faq_question_title"])));
					if($faqObj->addUpdate($arr)){
						Message::addMessage('Success: FAQ added/updated successfully.');
						Utilities::redirectUser(Utilities::generateUrl('faqs'));
					}else{
						Message::addErrorMessage($faqObj->getError());
					}
				}
			}
			$frm->fill($post);
		}
        $this->set('frm', $frm);
        $this->_template->render(true,true);
    }
    protected function getForm() {
		$catObj=new Categories();
        $frm = new Form('frmFaqs');
		$frm->setExtra(' validator="FaqsfrmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('FaqsfrmValidator');
		$frm->addHtml('<h6>Section 1: Basic Information about FAQ.</h6>', 'htmlNote','','&nbsp;');
        $frm->addHiddenField('', 'faq_id');
		$frm->addSelectBox('FAQ Category', 'faq_category_id', $catObj->getAssociativeArray(2),'', 'class="medium"','Select','faq_category_id')->requirements()->setRequired(true);
		
		$frm->addRequiredField('Question Title', 'faq_question_title','', '', ' class="input-xlarge"');
		$fld=$frm->addTextArea('Description', 'faq_answer_brief', '', 'faq_answer_brief', ' class="cleditor" rows="3"');
		
		$frm->addHtml('<h6>Section 2: SEO/Meta Data (Optional)</h6>', 'htmlNote','','&nbsp;');
		$frm->addTextBox('Page Title', 'faq_meta_title','','faq_meta_title','class="field95Width"');
		$frm->addTextArea('Meta Keywords', 'faq_meta_keywords','','faq_meta_keywords','class="fieldtextAreaBig" cols="112"');
		$frm->addTextArea('Meta Description', 'faq_meta_description','','faq_meta_description','class="fieldtextAreaBig" cols="112"');
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
		$faqObj=new Faqs();
        $faq_id = intval($post['id']);
        $faq = $faqObj->getData($faq_id);
		if($faq==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		if($faqObj->deleteFaq($faq_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($faqObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	    
}