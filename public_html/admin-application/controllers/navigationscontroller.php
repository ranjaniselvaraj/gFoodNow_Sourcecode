<?php
class NavigationsController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,NAVIGATION);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();
		$this->b_crumb->add("Navigations Management", Utilities::generateUrl("navigations"));		
        
    }
	
	protected function getSearchForm() {
        $frm=new Form('frmNavigationSearch','frmNavigationSearch');
		$frm->setFieldsPerRow(3);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('GET');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addHiddenField('', 'navigation_id');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchNavigations(this); return false;');
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
	
	function display($navigation_id) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $frm = $this->getSearchForm();
		$frm->fill(array("navigation_id"=>$navigation_id));
        $this->set('frmPost', $frm);
		$navigation = $this->Navigations->getNavigationById($navigation_id);
		$this->set('navigation', $navigation);
		$this->b_crumb->add($navigation['nav_name'],'');	
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listNavigations($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mode'])) {
			$bObj=new Brands();
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
            $this->set('records', $this->Navigations->getnavigations($post));
            $this->set('pages', $this->Navigations->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $this->Navigations->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
	
	 protected function getNavForm() {
        $frm = new Form('frmNav','frmNav');
		$frm->setExtra(' validator="navfrmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('navfrmValidator');
        $frm->addHiddenField('', 'nav_id');
		$frm->addRequiredField('Title', 'nav_name','', '', ' class="input-xlarge"');
		$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="20%"');
        return $frm;
    }
	
	function form($id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("Navigation Setup",'');
		$this->set('breadcrumb', $this->b_crumb->output());
        $id = intval($id);
        $frm = $this->getNavForm();
        if ($id > 0) {
            $data = $this->Navigations->getNavigationById($id);
            $frm->fill($data);
        }
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
					if($this->Navigations->addUpdateNavigation($post)){
						Message::addMessage('Success: Navigation added/updated successfully.');
						Utilities::redirectUser(Utilities::generateUrl('navigations'));
					}else{
						Message::addErrorMessage($this->Navigations->getError());
					}
				}
			$frm->fill($post);
		}
        $this->set('frm', $frm);
        $this->_template->render(true,true);
    }
    function pages() {
		if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
		$post = Syspage::getPostedVar();
        $navigation_id = intval($post['id']);
        $this->set('records', $this->Navigations->getNavigationPagesById($navigation_id));
		$this->set('navigation',$navigation = $this->Navigations->getNavigationById($navigation_id));
		$this->_template->render(false,false);
    }
	
	function update_navigation_status() {
		if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
		$post = Syspage::getPostedVar();
        $navigation_id = intval($post['id']);
        $navigation = $this->Navigations->getNavigationById($navigation_id);
        if($navigation==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		$data_to_update = array('nav_status'=>!$navigation['nav_status']);
		if($this->Navigations->updateNavigationStatus($navigation_id,$data_to_update)){
			Message::addMessage('Success: Status has been modified successfully.');
			$arr = array('status'=>1, 'msg'=>Message::getHtml(), 'linktext'=>(($navigation['nav_status'] == 1)?'Active':'Inactive'));
			die(convertToJson($arr));
		}else{
			Message::addErrorMessage($this->Navigations->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	function delete_page() {
        if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
        $post = Syspage::getPostedVar();
        $nav_page_id = intval($post['id']);
		if($this->Navigations->deletePage($nav_page_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($this->Navigations->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	
	
	function addEditNavigationPage($navigation_id,$nav_page_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		
		$navigation = $this->Navigations->getNavigationById($navigation_id);
        if($navigation==false) {
            Message::addErrorMessage('Error: Please perform this action on valid record.');
            Utilities::redirectUser(Utilities::generateUrl('navigations'));
        }
		$this->b_crumb->add($navigation['nav_name'], Utilities::generateUrl("navigations","display",array($navigation['nav_id'])));	
		$this->b_crumb->add("Navigation Page Setup",'');
		$this->set('breadcrumb', $this->b_crumb->output());
        $nav_page_id = intval($nav_page_id);
        $frm = $this->getForm($navigation_id,$nav_page_id);
        $navigationPage = $this->Navigations->getNavigationPageById($nav_page_id);
		$navigationPage["navigation_id"]=$navigation_id;
		$navigationPage["nav_page_id"]=$navigationPage["nl_id"];
		$navigationPage["external_page"]=$navigationPage["nl_type"]==2?$navigationPage["nl_html"]:"";
		$navigationPage["custom_html"]=$navigationPage["nl_type"]==1?$navigationPage["nl_html"]:"";
		
		$frm->fill($navigationPage);
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				if($post['navigation_id'] != $navigation_id){
					Message::addErrorMessage('Error: Invalid Request!!');
					Utilities::redirectUser(Utilities::generateUrl('navigations','display',array($navigation_id)));
				}else{
					
					if($this->Navigations->addUpdatePage($post)){
						Message::addMessage('Success: Navigation page details added/updated successfully.');
						Utilities::redirectUser(Utilities::generateUrl('navigations','display',array($navigation_id)));
					}else{
						Message::addErrorMessage($this->Navigations->getError());
					}
				}
			}
			$frm->fill($post);
		}
        
		$this->set('nav_id', $navigation_id);
        $this->set('frm', $frm);
        $this->_template->render(true, true);
    }
	
	protected function getForm($nav_id,$nl_id){
			global $db;
			global $nav_page_type;
			$frm=new Form('frmNavigations');
			$frm->setExtra('class="web_form" validator="tplValidator"');
			$frm->setValidatorJsObjectName('tplValidator');
			$frm->addHiddenField('', 'nav_page_id', '0', '', '');
			$frm->addHiddenField('', 'navigation_id','', '', '');
			$frm->addTextBox('Caption Name', 'nl_caption','','nl_caption','class="input-xlarge"')->requirements()->setRequired();
			$fld=$frm->addSelectBox('Type:', 'nl_type', $nav_page_type,'','class="input-xlarge" onChange="CallPageTypePopulate();"','','nl_type')->requirements()->setRequired();
			$frm->addSelectBox('Link Target', 'nl_target',array('_self'=>'Current Window','_blank'=>'New Window'),'','class="input-xlarge"','');
			$fld=$frm->addRadioButtons('Login Protected', 'nl_login_protected',array('0'=>'Both','1'=>'Yes','2'=>'No'),0, $table_cols=3, $tableproperties='width="25%"', $extra='');
			
			$cmsObj=new Cms();
			$frm->addSelectBox('Link to CMS Page', 'nl_cms_page_id',$cmsObj->getAssociativeArray(),'','class="input-xlarge"','--Select Page-- ','cms_page');
			$frm->addTextBox('External Page', 'external_page', '', 'external_page', ' class="input-xlarge"');
			$frm->addTextArea('Custom HTML', 'custom_html', '', 'custom_html', ' class="input-xlarge"');
			$frm->addTextBox('Display Order', 'nl_display_order','','nl_display_order','class="input-xlarge"')->requirements()->setIntPositive();
			$frm->setJsErrorDisplay('afterfield');
			$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
			$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
			$frm->setLeftColumnProperties('width="20%"');
			return $frm;
		
    }
	
	
	function getNavCode($nav_id,$parent_id){
		global $db;
		$code=str_pad($nav_id, 5, '0', STR_PAD_LEFT);
		if($parent_id>0){
			$rs=$db->query("select nl_code from tbl_nav_links where nl_id=" . $parent_id);
			if($row=$db->fetch($rs)){
					$code=$row['nl_code'].$code;
				}
			}
		return $code;
	}
}