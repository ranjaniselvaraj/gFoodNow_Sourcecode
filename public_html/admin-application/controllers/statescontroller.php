<?php
class StatesController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,STATES);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("States Management", Utilities::generateUrl("states"));
    }
	
	protected function getSearchForm() {
		$cnObj=new Countries();
        $frm=new Form('frmStateSearch','frmStateSearch');
		$frm->setFieldsPerRow(3);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('GET');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addTextBox('Keyword', 'keyword','','',' class="small"');
		$frm->addSelectBox('Country', 'country',$cnObj->getAssociativeArray(),'', 'class="small"');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchStates(this); return false;');
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
	
	function listStates($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
			$stObj=new States();
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
            $this->set('records', $stObj->getStates($post));
            $this->set('pages', $stObj->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $stObj->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
    
	
    function form($state_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("State Setup", Utilities::generateUrl("states"));
		$this->set('breadcrumb', $this->b_crumb->output());
		$stObj=new States();
        $state_id = intval($state_id);
        $frm = $this->getForm();
        if ($state_id > 0) {
            $data = $stObj->getData($state_id);
            $frm->fill($data);
        }
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
					if($post['state_id'] != $state_id){
						Message::addErrorMessage('Error: Invalid Request!!');
						Utilities::redirectUser(Utilities::generateUrl('states'));
					}else{
						
						$state = $stObj->getStateByName($post['state_name']);
						if (($state==true) && ($state["state_id"]!=$post["state_id"]) && ($state["country_id"]==$post["country_id"]) ){
							Message::addErrorMessage('State with this name already exists in selected country');
						}else{
							if($stObj->addUpdate($post)){
								Message::addMessage('Success: State details added/updated successfully.');
								Utilities::redirectUser(Utilities::generateUrl('states'));
							}else{
								Message::addErrorMessage($stObj->getError());
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
		$znObj=new Zones();
		$cnObj=new Countries();
        $frm = new Form('frmStates');
		$frm->setExtra(' validator="StatesfrmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('StatesfrmValidator');
        $frm->addHiddenField('', 'state_id');
		$frm->addSelectBox('Country', 'country_id',$cnObj->getAssociativeArray(),'', 'class="medium"')->requirements()->setRequired();
		$frm->addSelectBox('Zone', 'zone_id',$znObj->getAssociativeArray(),'', 'class="medium"')->requirements()->setRequired();
		$frm->addRequiredField('Name', 'state_name','', '', ' class="medium"');
		$frm->addTextBox('Code', 'state_code','', '', ' class="medium"');
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
		$stObj=new States();
        $state_id = intval($post['id']);
        $state = $stObj->getData($state_id);
		if($state==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		if($stObj->delete($state_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($stObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	
	function autocomplete(){
	    $post = Syspage::getPostedVar();
		$json = array();
		$stObj=new States();
        $states=$stObj->getStates(array("keyword"=>urldecode($post["keyword"]),"pagesie"=>10));
		foreach($states as $skey=>$sval){
			$json[] = array(
					'id' => $cval['state_id'],
					'name' => strip_tags(htmlentities($cval['state_name'], ENT_QUOTES, 'UTF-8'))
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