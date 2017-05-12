<?php
class OptionsController extends BackendController {
	
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,OPTIONS);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
    }
	
	 function default_action() {
		Utilities::redirectUser(Utilities::generateUrl('options','admin'));
	 }
    
	protected function getSearchForm() {
        $frm=new Form('frmSearchOption','frmSearchOption');
		$frm->setFieldsPerRow(2);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('GET');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'owner');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addTextBox('Keyword', 'keyword','','',' class="small"');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchOptions(this); return false;');
        return $frm;
    }
	
	function admin() {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $frm = $this->getSearchForm();
		$frm->fill(array('owner'=>'A'));
        $this->set('frmPost', $frm);
		$this->b_crumb->add("Admin Options Management");
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listOptions($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
			$opObj=new Options();
            $post = Syspage::getPostedVar();
            $page = 1;
            if (isset($post['page']) && intval($post['page']) > 0) {
                $page = intval($post['page']);
            } else {
                $post['page'] = $page;
            }
            if (!empty($post['keyword'])) {
                $this->set('srch', $post);
            }
           	$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
            $post['pagesize'] = $pagesize;
            $this->set('records', $opObj->getOptions($post));
            $this->set('pages', $opObj->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $opObj->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false,'options/listOptions.php');
        }
    }
	
	
	function suppliers() {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $frm = $this->getSearchForm();
		$frm->fill(array('owner'=>'U'));
        $this->set('frmPost', $frm);
		$this->b_crumb->add("Seller Options Management");
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	
	protected function getSupplierSearchForm() {
        $frm=new Form('frmSearchOption','frmSearchOption');
		$frm->setFieldsPerRow(2);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('GET');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addHiddenField('', 'created_by');
		$frm->addTextBox('Keyword', 'keyword','','',' class="small"');
		$fld=$frm->addTextBox('Added By', 'created_by_name','','created_by_name',' class="small"');
		$frm->addSubmitButton('','btn_submit','Search');
		$frm->getField("btn_submit")->html_after_field='&nbsp;&nbsp;<a href="'.Utilities::generateUrl('options','suppliers').'"class="clear_btn">Clear Search</a>';
		$frm->setJsErrorDisplay('afterfield');
        $frm->setAction(Utilities::generateUrl('options','suppliers'));
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
        return $frm;
    }
	
    function form($option_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		
		$opObj=new Options();
        $option_id = intval($option_id);
        if ($option_id > 0) {
            $data = $opObj->getData($option_id);
			$option_values = $opObj->getOptionValues($option_id);			
			$data['option_values'] = array();
			foreach ($option_values as $key=>$val) {
				$data['option_values'][]=$val;
			}
        }
		if ($data['option_owner']=="U"){
			$this->b_crumb->add("Seller Options Management", Utilities::generateUrl("options","suppliers"));
		}else{
			$this->b_crumb->add("Admin Options Management", Utilities::generateUrl("options"));
		}
		$this->b_crumb->add("Options Setup",'');
		$this->set('breadcrumb', $this->b_crumb->output());
		$frm = $opObj->getForm($data,true);
		$frm->fill($data);
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
					if($post['option_id'] != $option_id){
						Message::addErrorMessage('Error: Invalid Request!!');
						Utilities::redirectUser(Utilities::generateUrl('options'));
					}else{
						$arr = array_merge($post,array("option_owner"=>"A","option_created_by"=>$this->getLoggedAdminId()));
						if($opObj->addUpdate($arr)){
							Message::addMessage('Success: Option details added/updated successfully.');
							if (($post['option_id']==0) || ($data['option_owner']=="A")){
								Utilities::redirectUser(Utilities::generateUrl('options'));
							}else{
								Utilities::redirectUser(Utilities::generateUrl('options','suppliers'));
							}
								
						}else{
							Message::addErrorMessage($opObj->getError());
						}
					}
			}
			$frm->fill($post);
		}
		$this->set('option_value_row',count($data['option_values']));
        $this->set('frm', $frm);
		$this->set('owner',isset($data['option_owner'])?$data['option_owner']:'A');
        $this->_template->render(true,true);
    }
	
	function delete() {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $post = Syspage::getPostedVar();
		$opObj=new Options();
        $option_id = intval($post['id']);
        $option = $opObj->getData($option_id);
		if($option==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		if($opObj->delete($option_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($opObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
    
	
	
	function autocomplete(){
		$optionObj=new Options();
	  	$post = Syspage::getPostedVar();
		$json = array();
        $options=$optionObj->getOptions(array("keyword"=>urldecode($post["keyword"]),"pagesize"=>10));
		foreach($options as $okey=>$row){
				$option_value_data = array();
				if ($row['option_type'] == 'select' || $row['option_type'] == 'radio' || $row['option_type'] == 'checkbox' || $row['option_type'] == 'image') {
					$option_values = $optionObj->getOptionValues($row['option_id']);
					foreach ($option_values as $option_value) {
						$option_value_data[] = array(
							'option_value_id' => $option_value['option_value_id'],
							'name'            => strip_tags(htmlentities($option_value['option_value_name'], ENT_QUOTES, 'UTF-8')),
							'image'           => $option_value['option_value_image']
						);
					}
					$sort_order = array();
					foreach ($option_value_data as $key => $value) {
						$sort_order[$key] = $value['name'];
					}
					//array_multisort($sort_order, SORT_ASC, $option_value_data);
				}
				$type = '';
				if ($row['option_type'] == 'select' || $row['option_type'] == 'radio' || $row['option_type'] == 'checkbox' || $row['option_type'] == 'image') {
					$type = "Choose";
				}
				if ($row['option_type'] == 'text' || $row['option_type'] == 'textarea') {
					$type = "Input";
				}
				if ($row['option_type'] == 'file') {
					$type = "File";
				}
				if ($row['option_type'] == 'date' || $row['option_type'] == 'datetime' || $row['option_type'] == 'time') {
					$type = "Date";
				}
				$json[] = array(
					'option_id'    => $row['option_id'],
					'name'         => strip_tags(htmlentities($row['option_name'], ENT_QUOTES, 'UTF-8')),
					'category'     => $type,
					'type'         => $row['option_type'],
					'option_value' => $option_value_data
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