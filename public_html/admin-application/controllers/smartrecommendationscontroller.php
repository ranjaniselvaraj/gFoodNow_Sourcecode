<?php
class SmartRecommendationsController extends BackendController {
	
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canviewweightages = Admin::getAdminAccess($admin_id,SMART_RECOMENDED_WEIGHTAGES);
        $this->set('canviewweightages', $this->canviewweightages);
		$this->canviewrecommendedproducts = Admin::getAdminAccess($admin_id,SMART_RECOMENDED_PRODUCTS);
		$this->set('canviewrecommendedproducts', $this->canviewrecommendedproducts);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Smart Recommendations",'');
    }
	
	function weightages() {
		if ($this->canviewweightages != true) {
            $this->notAuthorized();
        }
		$srObj=new SmartRecommendations();
		$weightageEvents= $srObj->getWeightageEvents();
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if($srObj->addUpdateWeightageSettings($post)){
				Message::addMessage('Success: Smart recommendations weightages updated successfully.');
				Utilities::redirectUser(Utilities::generateUrl('smartrecommendations','weightages'));
			}else{
				Message::addErrorMessage($srObj->getError());
			}
		}
		$this->set('product_weightage_events',$weightageEvents);
        $this->_template->render();
    }
	
	function searchproductform(){
		$sObj=new Shops();
		$bObj=new Brands();
		$cObj=new Categories();
		$frm=new Form('frmSearchProducts','frmSearchProducts');
		$frm->setFieldsPerRow(4);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setRequiredStarWith('caption');
		$frm->setMethod('GET');
		$frm->captionInSameCell(true);
		$frm->addHiddenField('', 'mode', "search");
		$frm->addHiddenField('', 'shop');
		$frm->addHiddenField('', 'brand');
		$frm->addHiddenField('', 'user');
		$fld=$frm->addTextBox('Keyword', 'keyword','','',' class="medium"');
		//$fld->merge_cells=2;
		$fld=$frm->addTextBox('Visitor', 'visitor','','visitor',' class="small"');
		$frm->addTextBox('Product Shop', 'product_shop','', '', 'class="small"');
		$frm->addTextBox('Product Brand', 'brand_manufacturer','', '', 'class="small"');
		$frm->addSelectBox('Category', 'category', $cObj->getCategoriesAssocArray(), '', 'class="small"', 'Select');
		$fld=$frm->addSelectBox('Active', 'active',array(1=>"Yes",0=>"No"),'' , 'class="small"','All');
		$frm->addTextBox('Price From', 'minprice','','',' class="small"')->requirements()->setFloatPositive($val=true);
		$frm->addTextBox('Price To', 'maxprice','','',' class="small"')->requirements()->setFloatPositive($val=true);
		$fld=$frm->addDateField('Date From', 'date_from', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld=$frm->addDateField('Date To', 'date_to', '', '', 'readonly="true" class="small dateTimeFld"');
		$frm->addCheckBox('Is Excluded', 'excluded','1');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
        return $frm;
    }
		
    
	
	function products() {
        if ($this->canviewrecommendedproducts != true) {
            $this->notAuthorized();
        }
        $frm = $this->searchproductform();
		$frm->removeField($frm->getField('visitor'));
		$frm->setOnSubmit('searchRecommendedProducts(this); return false;');
        $this->set('frmPost', $frm);
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listRecommendedProducts($page = 1) {
        if ($this->canviewrecommendedproducts != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
			$srObj=new SmartRecommendations();
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
            $this->set('records', $srObj->getProducts($post));
            $this->set('pages', $srObj->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $srObj->getTotalRecords();
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
    
	
	protected function getUserSearchForm() {
        $frm=new Form('frmSearchUserProducts','frmSearchUserProducts');
		$frm->setFieldsPerRow(3);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('GET');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addHiddenField('', 'user');
		$fld=$frm->addTextBox('User', 'user_name','','user_name',' class="small"');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchUserRecommendedProducts(this); return false;');
        return $frm;
    }
	
	function users() {
        if ($this->canviewrecommendedproducts != true) {
            $this->notAuthorized();
        }
        $frm = $this->getUserSearchForm();
        $this->set('frmPost', $frm);
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listUserRecommendedProducts($page = 1) {
        if ($this->canviewrecommendedproducts != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
			$prodObj=new Products();
            $post = Syspage::getPostedVar();
           	$this->set('records', $prodObj->getSmartRecommendedProducts($post['user'],0,false));
			$this->set('user', $post['user']);
            $this->_template->render(false, false);
        }
    }
	
	
	
	function clear_records(){
		$srObj=new SmartRecommendations();
		if($srObj->clear()){
			Message::addMessage('Success: Smart recommendations records cleared successfully.');
			}else{
			Message::addErrorMessage($srObj->getError());
		}
		Utilities::redirectUserReferer();
	}
	
	function updateProductRecommendations(){
		$srObj=new SmartRecommendations();
		$post = Syspage::getPostedVar();
		if($srObj->updateProductRecommendation($post)){
			echo $post["value"];
		}else{
			echo $msgObj->getError();
		}
	}
	
	
	
	function products_browsing_history() {
        if ($this->canviewrecommendedproducts != true) {
            $this->notAuthorized();
        }
        $frm = $this->searchproductform();
		$frm->removeField($frm->getField('excluded'));
		$frm->setOnSubmit('searchBrowsingHistoryProducts(this); return false;');
        $this->set('frmPost', $frm);
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listBrowsedHistoryProducts($page = 1) {
        if ($this->canviewrecommendedproducts != true) {
            $this->notAuthorized();
        }
		
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
			$srObj=new SmartRecommendations();
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
            $this->set('records', $srObj->getBrowsingHistoryProducts($post));
            $this->set('pages', $srObj->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $srObj->getTotalRecords();
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
	/*function products_browsing_history($page) {
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),SMART_RECOMENDED_PRODUCTS)) {
             die(Admin::getUnauthorizedMsg());
        }
		$srObj=new SmartRecommendations();
		$page = intval($page);
		if ($page < 1)
			$page = 1;
		$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
		$criteria=array();
		$criteria['pagesize'] = $pagesize;
		$criteria['page'] = $page;
		$frm=$this->searchproductform();
		$frm->removeField($frm->getField('excluded'));
		$get = (array) Utilities::getUrlQuery();
		if(count($get)>1) {
			$frm->fill($get);
			$criteria=array_merge($criteria,$get);
		}
		$this->set('search_form', $frm);
		$this->set('arr_listing', $srObj->getBrowsingHistoryProducts($criteria));
		$this->set('pages', $srObj->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page-1)*$pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $srObj->getTotalRecords();
		if ($total_records < $end_record) $end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->set('search_parameter',$get);
        $this->_template->render();
    }*/	
	
    
}