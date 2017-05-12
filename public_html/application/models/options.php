<?php
class Options extends Model {
	
	function __construct(){
		$this->db = Syspage::getdb();
    }
	
	function getOptionId() {
        return $this->option_id;
    }
	
	function getTotalPages(){
		return $this->total_pages;
	}
	
	function getTotalRecords(){
		return $this->total_records;
	}
	
	function getError() {
        return $this->error;
    }
	
    function getData($id,$add_criteria=array()) {
        $id = intval($id);
        if($id>0!=true) return array();
       	$add_criteria['id'] = $id;
        $srch = self::search($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function getOptions($criteria) {
        foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
        $rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return false;
		}
        $row = $this->db->fetch_all($rs);
        if($row==false) return array();
        else return $row;
	}
	
    function search($criteria) {
        $srch = new SearchBase('tbl_options', 'topt');
		$srch->joinTable('tbl_users', 'LEFT JOIN', 'topt.option_created_by=tu.user_id and topt.option_owner="U"', 'tu');
		$srch->addCondition('topt.option_is_deleted', '=', 0);
		$srch->addMultipleFields(array('topt.*','tu.user_name','tu.user_username'));
        foreach($criteria as $key=>$val) {
            switch($key) {
            case 'id':
                $srch->addCondition('topt.option_id', '=', intval($val));
                break;
			case 'keyword':
                $srch->addCondition('topt.option_name', 'like', '%'.$val.'%');
                break;
			case 'owner':
                $srch->addCondition('topt.option_owner', '=', $val);
                break;
			case 'created_by':
                $srch->addCondition('topt.option_created_by', '=', intval($val));
                break;
			case 'owner_check':
				if ($val=="Y"){
					$srch->addDirectCondition('((topt.option_owner = "A") OR ((topt.option_owner = "U") AND (topt.option_created_by = '.intval($criteria['owner_check_by_id']).')))');
				}
			break;				
			case 'pagesize':
				$srch->setPageSize($val);
				break;
			case 'page':
				$srch->setPageNumber($val);
				break;			
            }
        }
        $srch->addOrder('topt.option_name', 'asc');
		//die($srch->getquery());
        return $srch;
    }
	
	function getForm($info,$is_admin_call=false) {
		global $conf_option_types;
        $frm = new Form('frmOption','frmOption');
		$frm->setExtra(' validator="OptionfrmValidator" class="web_form siteForm"');
        $frm->setValidatorJsObjectName('OptionfrmValidator');
		 $frm->setRequiredStarWith('caption');
        $frm->addHiddenField('', 'option_id');
		$fld=$frm->addSelectBox(Utilities::getLabel('F_Type'), 'option_type',$conf_option_types, '', 'class="medium"',Utilities::getLabel('F_Please_Choose'))->requirements()->setRequired();
		$frm->addRequiredField(Utilities::getLabel('F_Name'), 'option_name','', '', ' class="medium"');
		$frm->addTextBox(Utilities::getLabel('F_Display_Order'),'option_display_order','1', '', 'class="medium"');
		$option_value_row=0;
		$option_values = '';
		foreach($info["option_values"] as $key=>$val):
			$option_values.='<tr id="option-value-row'.$option_value_row.'"><td><input  type="hidden" name="option_values['.$option_value_row.'][id]" value="'.$val["option_value_id"].'" /><input data-fld="name" type="text" name="option_values['.$option_value_row.'][name]" value="'.$val["option_value_name"].'" placeholder="'.Utilities::getLabel('F_Option_Value_Name').'" title="'.Utilities::getLabel('F_Option_Value_Name').'" /></td><td><input type="text" name="option_values['.$option_value_row.'][sort]" value="'.$val["option_value_display_order"].'" placeholder="'.Utilities::getLabel('F_Display_Order').'" /></td><td>
			<button type="button" class="btn red" onclick="deleteOptionValue('.$option_value_row.');" title="'.Utilities::getLabel('M_Remove').'" ><i><img src="'.CONF_WEBROOT_URL.'images/minus-white.png" alt=""/></i></button>
			</td></tr>';
			$option_value_row++;	
		endforeach;
		if ($is_admin_call){
			$htmlField='<table id="option_value" class="table_listing"><thead><tr><th width="60%">'.Utilities::getLabel('F_Option_Value_Name').' </th><th width="30%">'.Utilities::getLabel('F_Display_Order').'</th><th></th></tr></thead><tbody>'.$option_values.'</tbody><tfoot><tr><td colspan="2"></td><td class="text-left">
			<ul class="actions"><li><a onclick="addOptionValue();" class="button medium blue" title="'.Utilities::getLabel('M_Add_Option_Value').'"><i class="ion-plus-round icon"></i></a></li></ul>
			</td></tr></tfoot></table>';
		}
		else{
			$htmlField='<table id="option_value" class="table_listing"><thead><tr><th width="60%">'.Utilities::getLabel('F_Option_Value_Name').' </th><th width="30%">'.Utilities::getLabel('F_Display_Order').'</th><th></th></tr></thead><tbody>'.$option_values.'</tbody><tfoot><tr><td colspan="2"></td><td class="text-left">
			<button type="button" onclick="addOptionValue();" class="btn blue" title="'.Utilities::getLabel('M_Add_Option_Value').'"><i><img src="'.CONF_WEBROOT_URL.'images/plus-white.png" alt=""/></i></button></a></li></ul>
			</td></tr></tfoot></table>';
		}
		$fld_html=$frm->addHtml('&nbsp;','&nbsp;',$htmlField)->merge_caption = true;
		//$fld_html->merge_cells=2;
		
		$frm->addSubmitButton('&nbsp;','btn_submit',Utilities::getLabel('F_Save_Changes'));
		$frm->setJsErrorDisplay('afterfield');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="20%"');
        return $frm;
    }
	
	function addUpdate($data){
		$option_id = intval($data['option_id']);
		$record = new TableRecord('tbl_options');
		$assign_fields = array();
		$assign_fields['option_type'] = $data['option_type'];
		$assign_fields['option_name'] = $data['option_name'];
		$assign_fields['option_display_order'] = intval($data['option_display_order']);
		if($option_id === 0){
			$assign_fields['option_owner'] = $data['option_owner'];
			$assign_fields['option_created_by'] = intval($data['option_created_by']);
		}
			
		
		$record->assignValues($assign_fields);
		if($option_id === 0 && $record->addNew()){
			$this->option_id=$record->getId();
		}elseif($option_id > 0 && $record->update(array('smt'=>'option_id=?', 'vals'=>array($option_id)))){
			$this->option_id=$option_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		
		if (!$this->db->deleteRecords('tbl_option_values', array('smt' => 'option_id = ?', 'vals' => array($this->getOptionId())))){
			$this->error = $this->db->getError();
			return false;
		}
		
		if (isset($data['option_values'])) {
			foreach ($data['option_values'] as $key=>$val){
				$record = new TableRecord('tbl_option_values');
				$record->assignValues(array("option_id"=>$this->getOptionId()));
				$record->assignValues(array("option_value_name"=>$val["name"],"option_value_display_order"=>$val["sort"]));	
				if (isset($val['id'])){
					$record->setFldValue('option_value_id',$val['id']);
				}
				if($val>0){
					if (!$record->addNew()){
						$this->error = $this->db->getError();
						return false;
					}
				}
			}
		}
		
		return $this->getOptionId();
	}
	
	function delete($option_id){
		$option_id = intval($option_id);
		if($option_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_options', array('option_is_deleted' => 1), array('smt' => 'option_id = ?', 'vals' => array($option_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function getOptionValues($option_id) {
		$option_value_data = array();
		$rs = $this->db->query("SELECT * FROM tbl_option_values WHERE option_id = '" . (int)$option_id . "' order by option_value_display_order asc,option_value_name asc");
		$option_value_data=$this->db->fetch_all($rs);
		return $option_value_data;
	}
    
   
}