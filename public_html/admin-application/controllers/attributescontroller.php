<?php
class AttributesController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,ATTRIBUTES);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Specifications Management", Utilities::generateUrl("attributes"));
    }
	
	protected function getSearchForm() {
        $frm=new Form('frmSearchAttribute','frmSearchAttribute');
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
		$frm->setOnSubmit('searchAttributes(this); return false;');
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
	
	function listAttributes($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
			$agObj=new Attributegroups();
            $post = Syspage::getPostedVar();
            $page = 1;
            if (isset($post['page']) && intval($post['page']) > 0) {
                $page = intval($post['page']);
            } else {
                $post['page'] = $page;
            }
            if (!empty($post['Keyword'])) {
                $this->set('srch', $post);
            }
           	$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
            $post['pagesize'] = $pagesize;
            $this->set('records', $agObj->getAttributeGroups($post));
            $this->set('pages', $agObj->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $agObj->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
    function form($attribute_group_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("Specification Setup", Utilities::generateUrl("attributes"));
		$this->set('breadcrumb', $this->b_crumb->output());
		$agObj=new Attributegroups();
        $attribute_group_id = intval($attribute_group_id);
        if ($attribute_group_id > 0) {
            $data = $agObj->getData($attribute_group_id);
			$attribute_group_values = $agObj->getAttributeValues($attribute_group_id);			
			$data['attribute_group_values'] = array();
			foreach ($attribute_group_values as $key=>$val) {
				$data['attribute_group_values'][]=$val;
			}
        }
		$frm = $this->getForm($data);
		$frm->fill($data);
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
					if($post['attribute_group_id'] != $attribute_group_id){
						Message::addErrorMessage('Error: Invalid Request!!');
						Utilities::redirectUser(Utilities::generateUrl('attributes'));
					}else{
						
						if($agObj->addUpdate($post)){
							Message::addMessage('Success: Specification details added/updated successfully.');
							Utilities::redirectUser(Utilities::generateUrl('attributes'));
						}else{
							Message::addErrorMessage($agObj->getError());
						}
					}
			}
			$frm->fill($post);
		}
		$this->set('attribute_group_value_row',count($data['attribute_group_values']));
        $this->set('frm', $frm);
        $this->_template->render(true,true);
    }
    protected function getForm($info) {
        $frm = new Form('frmAttribute');
		$frm->setExtra(' validator="AttributefrmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('AttributefrmValidator');
	    $frm->setRequiredStarWith('caption');
        $frm->addHiddenField('', 'attribute_group_id');
		$frm->addRequiredField('Specification Group Name', 'attribute_group_name','', '', ' class="medium"');
		$frm->addTextBox('Display Order', 'attribute_group_display_order','1', '', 'class="medium"');
		$attribute_group_value_row=0;
		$attribute_group_values = '';
		foreach($info["attribute_group_values"] as $key=>$val):
			$attribute_group_values.='<tr id="option-value-row'.$attribute_group_value_row.'"><td><input type="hidden" name="attribute_group_values['.$attribute_group_value_row.'][id]" value="'.$val["attribute_id"].'" /><input type="text" name="attribute_group_values['.$attribute_group_value_row.'][name]" value="'.$val["attribute_name"].'" placeholder="Specification Value Name" /></td><td><input type="text" name="attribute_group_values['.$attribute_group_value_row.'][sort]" value="'.$val["attribute_display_order"].'" placeholder="Display Order" /></td><td><ul class="actions"><li><a class="button medium red" onclick="$(\'#option-value-row'.$attribute_group_value_row.'\').remove();" title="Remove" ><i class="ion-minus icon"></i></a></li></ul></td></tr>';
			$attribute_group_value_row++;	
		endforeach;
		
		$htmlField='<table id="attribute_group_value" class="table_listing"><thead><tr><th width="60%">Specification Name <span class="spn_must_field">*</span></th><th width="30%">Display Order</th><th></th></tr></thead><tbody>'.$attribute_group_values.'</tbody><tfoot><tr><td colspan="2"></td><td class="text-left"><ul class="actions"><li><a onclick="addAttributeValue();" class="button medium blue"><i class="ion-plus-round icon"></i></a></li></ul></td></tr></tfoot></table>';
		$fld_html=$frm->addHtml('&nbsp;','&nbsp;',$htmlField)->merge_caption = true;
		//$fld_html->merge_cells=2;
		
		$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="20%"');
        return $frm;
    }
	
	function autocomplete(){
		$aObj=new Attributes();
	    $post = Syspage::getPostedVar();
		$json = array();
		$criteria=array("keyword"=>urldecode($post["keyword"]),"pagesize"=>10);
		$attributes=$aObj->getAttributes($criteria);
		foreach($attributes as $akey=>$aval){
				$json[] = array(
					'attribute_id' => $aval['attribute_id'],
					'name'      => strip_tags(htmlentities($aval['attribute_name'], ENT_QUOTES, 'UTF-8')),
					'attribute_group'      => htmlentities($aval['attribute_group_name'])
				);
		}
		$sort_order = array();
		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}
		array_multisort($sort_order, SORT_ASC, $json);
		echo json_encode($json);
	}
	
	
	function delete() {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $post = Syspage::getPostedVar();
		$agObj=new Attributegroups();
        $attribute_group_id = intval($post['id']);
        $attribute_group = $agObj->getData($attribute_group_id);
		if($attribute_group==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		if($agObj->delete($attribute_group_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($agObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
}