<?php
class CountriesController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,COUNTRIES);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Countries Management", Utilities::generateUrl("countries"));
    }
	
	protected function getSearchForm() {
        $frm=new Form('frmCountrySearch','frmCountrySearch');
		$frm->setFieldsPerRow(3);
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
		$frm->setOnSubmit('searchCountries(this); return false;');
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
	
	function listCountries($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
			$cnObj=new Countries();
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
            $this->set('records', $cnObj->getCountries($post));
            $this->set('pages', $cnObj->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $cnObj->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
    function form($country_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("Country Setup", Utilities::generateUrl("countries"));
		$this->set('breadcrumb', $this->b_crumb->output());
		$cnObj=new Countries();
        $country_id = intval($country_id);
        $frm = $this->getForm();
        if ($country_id > 0) {
            $data = $cnObj->getData($country_id);
            $frm->fill($data);
        }
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
					if($post['country_id'] != $country_id){
						Message::addErrorMessage('Error: Invalid Request!!');
						Utilities::redirectUser(Utilities::generateUrl('countries'));
					}else{
						$country = $cnObj->getCountryByName($post['country_name']);
						if (($country==true) && ($country["country_id"]!=$post["country_id"]) ){
							Message::addErrorMessage('Country with this name already exists.');
						}else{
							if($cnObj->addUpdate($post)){
								Message::addMessage('Success: Country details added/updated successfully.');
								Utilities::redirectUser(Utilities::generateUrl('countries'));
							}else{
								Message::addErrorMessage($cnObj->getError());
							}
						}
					}
			}
			$frm->fill($post);
		}
        $this->set('frm', $frm);
        $this->_template->render(true,true);
    }
    protected function getForm() {
        $frm = new Form('frmCountries');
		$frm->setExtra(' validator="CountriesfrmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('CountriesfrmValidator');
        $frm->addHiddenField('', 'country_id');
		$frm->addRequiredField('Name', 'country_name','', '', ' class="medium"');
		$frm->addTextBox('Code', 'country_code','', '', ' class="medium"');
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
		$cnObj=new Countries();
        $country_id = intval($post['id']);
        $country = $cnObj->getData($country_id);
		if($country==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		if($cnObj->delete($country_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($cnObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
		
	function autocomplete(){
	    $post = Syspage::getPostedVar();
		$json = array();
		$cnObj=new Countries();
        $countries=$cnObj->getCountries(array("keyword"=>urldecode($post["keyword"]),"pagesize"=>10));
		foreach($countries as $ckey=>$cval){
			$json[] = array(
					'id' => $cval['country_id'],
					'name' => strip_tags(htmlentities($cval['country_name'], ENT_QUOTES, 'UTF-8'))
				);
		}
		$sort_order = array();
		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}
		array_multisort($sort_order, SORT_ASC, $json);
		echo json_encode($json);
	}
	
}