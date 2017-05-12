<?php
class FiltersController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,FILTERGROUPOPTIONS);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Filters Management", Utilities::generateUrl("filters"));
    }
	
	protected function getSearchForm() {
        $frm=new Form('frmFilterSearch','frmFilterSearch');
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
		$frm->setOnSubmit('searchFilters(this); return false;');
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
	
	function listFilters($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
			$fgObj=new Filters();
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
            $this->set('records', $fgObj->getFilters($post));
            $this->set('pages', $fgObj->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $fgObj->getTotalRecords();
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
    function form($filter_group_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("Filters Setup",'');
		$this->set('breadcrumb', $this->b_crumb->output());
		$fgObj=new Filters();
        $filter_group_id = intval($filter_group_id);
        if ($filter_group_id > 0) {
            $data = $fgObj->getData($filter_group_id);
			$filter_group_values = $fgObj->getFilterValues($filter_group_id);			
			$data['filter_group_values'] = array();
			foreach ($filter_group_values as $key=>$val) {
				$data['filter_group_values'][]=$val;
			}
        }
		$frm = $this->getForm($data);
		$frm->fill($data);
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
					if($post['filter_group_id'] != $filter_group_id){
						Message::addErrorMessage('Error: Invalid Request!!');
						Utilities::redirectUser(Utilities::generateUrl('filters'));
					}else{
						if($fgObj->addUpdate($post)){
							Message::addMessage('Success: Filter details added/updated successfully.');
							Utilities::redirectUser(Utilities::generateUrl('filters'));
						}else{
							Message::addErrorMessage($fgObj->getError());
						}
					}
			}
			$frm->fill($post);
		}
		$this->set('filter_group_value_row',count($data['filter_group_values']));
        $this->set('frm', $frm);
        $this->_template->render(true,true);
    }
    protected function getForm($info) {
        $frm = new Form('frmFilter');
		$frm->setExtra(' validator="FilterfrmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('FilterfrmValidator');
	    $frm->setRequiredStarWith('caption');
        $frm->addHiddenField('', 'filter_group_id');
		$frm->addRequiredField('Filter Group Name', 'filter_group_name','', '', ' class="medium"');
		$frm->addTextBox('Display Order', 'filter_group_display_order','1', '', 'class="medium"');
		$filter_group_value_row=0;
		$filter_group_values = '';
		foreach($info["filter_group_values"] as $key=>$val):
			$filter_group_value_id = isset($val["filter_group_value_id"])?$val["filter_group_value_id"]:0;
			$filter_group_values.='<tr id="option-value-row'.$filter_group_value_row.'"><td><input type="hidden" name="filter_group_values['.$filter_group_value_row.'][id]" value="'.$filter_group_value_id.'" /><input type="text" name="filter_group_values['.$filter_group_value_row.'][name]" value="'.$val["filter_name"].'" placeholder="Filter Value Name" /></td><td><input type="text" name="filter_group_values['.$filter_group_value_row.'][sort]" value="'.$val["filter_display_order"].'" placeholder="Display Order" /></td><td><ul class="actions"><li><a class="button medium red" onclick="$(\'#option-value-row'.$filter_group_value_row.'\').remove();" title="Remove" ><i class="ion-minus icon"></i></a></li></ul></td></tr>';
			$filter_group_value_row++;	
		endforeach;
		
		$htmlField='<table id="filter_group_value" class="table_listing"><thead><tr><th width="60%">Filter Name <span class="spn_must_field">*</span></th><th width="30%">Display Order</th><th></th></tr></thead><tbody>'.$filter_group_values.'</tbody><tfoot><tr><td colspan="2"></td><td class="text-left"><ul class="actions"><li><a onclick="addFilterValue();" class="button medium blue"><i class="ion-plus-round icon"></i></a></li></ul></td></tr></tfoot></table>';
		$fld_html=$frm->addHtml('&nbsp;','&nbsp;',$htmlField)->merge_caption = true;
		//$fld_html->merge_cells=2;
		
		$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="20%"');
        return $frm;
    }
	
	
	function delete() {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $post = Syspage::getPostedVar();
		$filterObj=new Filters();
        $filter_group_id = intval($post['id']);
        $filter = $filterObj->getData($filter_group_id);
		if($filter==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		if($filterObj->delete($filter_group_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($filterObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
}