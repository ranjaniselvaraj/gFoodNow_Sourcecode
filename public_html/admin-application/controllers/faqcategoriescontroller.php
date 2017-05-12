<?php
class FaqCategoriesController extends BackendController {
	
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,FAQCATEGORIES);
    }
	
    function default_action($parent=0,$page=1) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$catObj=new Categories();
		$page = intval($page);
		if ($page < 1)
			$page = 1;
		$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
		$post['type'] = 2;
		$post['pagesize'] = $pagesize;
		$post['page'] = $page;
		$this->set('arr_listing', $catObj->getCategories($post));
		$this->set('pages', $catObj->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page-1)*$pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $catObj->getTotalRecords();
		if ($total_records < $end_record) $end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->set('parent',$parent);
		$this->set('category_structure',$catObj->funcGetCategoryStructure($parent));
        $this->_template->render();
    }
	
    function form($category_id,$parent=0) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$catObj=new Categories();
        $category_id = intval($category_id);
        $frm = $this->getForm($parent);
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				if($post['category_id'] != $category_id){
					Message::addErrorMessage('Error: Invalid Request!!');
					Utilities::redirectUser(Utilities::generateUrl('faqcategories'));
				}else{
					$arr=array_merge($post,array("category_type"=>2));
					if($catObj->addUpdate($arr)){
						Message::addMessage('Success: Category details added/updated successfully.');
						Utilities::redirectUser(Utilities::generateUrl('faqcategories'));
					}else{
						Message::addErrorMessage($catObj->getError());
					}
				}
			}
		}
        if ($category_id > 0) {
            $data = $catObj->getData($category_id);
            $frm->fill($data);
        }
        $this->set('frm', $frm);
		$this->set('parent',$parent);
		$this->set('category_structure',$catObj->funcGetCategoryStructure($parent));
        $this->_template->render(true,true);
    }
    protected function getForm($parent_id) {
        $parent_id = intval($parent_id);
        $frm = new Form('frmCategory');
        $frm->addHtml('<h6>Section 1: Basic Information about Category.</h6>', 'htmlNote','','&nbsp;');
        $fld1 = $frm->addHiddenField('', 'category_id');
        $fld1->requirements()->setInt(true);
        $fld2 = $frm->addTextBox('Category Name', 'category_name');
        $fld2->requirements()->setRequired(true);
		
		$fld=$frm->addTextArea('Description', 'category_description', '', 'category_description', ' class="cleditor" rows="3"');
        $frm->addTextBox('Display Order', 'category_display_order','','',' class="small"');
		$frm->addHtml('<h6>Section 2: SEO/Meta Data (Optional)</h6>', 'htmlNote','','&nbsp;');
		$frm->addTextBox('Page Title', 'category_meta_title','','category_meta_title','class="medium"');
		$frm->addTextArea('Meta Keywords', 'category_meta_keywords','','category_meta_keywords','class="medium" cols="112"');
		$frm->addTextArea('Meta Description', 'category_meta_description','','category_meta_description','class="medium" cols="112"');
		
        $frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
        //$frm->setAction(Utilities::generateUrl('faqcategories', 'setup'));
        $frm->setValidatorJsObjectName('categoryValidator');
        $frm->setExtra(' validator="categoryValidator" class="web_form"');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="20%"');
		$frm->setJsErrorDisplay('afterfield');
        return $frm;
	}
	
	function update_status() {
		if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
		$post = Syspage::getPostedVar();
		$catObj=new Categories();
		$category_id = intval($post['id']);
        $category = $catObj->getData($category_id);
		if($category==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
        $data_to_update = array('category_status'=>!$category['category_status']);
		if($catObj->updateCategoryStatus($category_id,$data_to_update)){
			Message::addMessage('Success: Status has been modified successfully.');
			$arr = array('status'=>1, 'msg'=>Message::getHtml(), 'linktext'=>(($category['category_status'] == 1)?'Active':'Inactive'));
			die(convertToJson($arr));
		}else{
			Message::addErrorMessage($catObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	function delete() {
        if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
        $post = Syspage::getPostedVar();
		$catObj=new Categories();
        $category_id = intval($post['id']);
        $category = $catObj->getData($category_id);
		if($category==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		if($catObj->delete($category_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($catObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	/*function delete($category_id) {
		$catObj=new Categories();
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),FAQCATEGORIES)) {
             die(Admin::getUnauthorizedMsg());
        }
        if($catObj->delete($category_id)){
			Message::addMessage('Success: Record has been deleted.');
		}else{
			Message::addErrorMessage($catObj->getError());
		}
		
		Utilities::redirectUserReferer();
    }
	
	function status($category_id, $mod='block') {
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),FAQCATEGORIES)) {
             die(Admin::getUnauthorizedMsg());
        }	
        $category_id = intval($category_id);
		$catObj=new Categories();
        $category = $catObj->getData($category_id);
        if($category==false) {
            Message::addErrorMessage('Error: Please perform this action on valid record.');
            Utilities::redirectUserReferer();
        }
        switch($mod) {
            case 'block':
            	$data_to_update = array(
					'category_status'=>0,
	            );
            break;
            case 'unblock':
    	        $data_to_update = array(
					'category_status'=>1,
            	);
            break;
           
        }
		if($catObj->updateCategoryStatus($category_id,$data_to_update)){
			Message::addMessage('Success: Status has been modified successfully.');
		}else{
			Message::addErrorMessage($catObj->getError());
		}
        Utilities::redirectUserReferer();
    }
*/
}