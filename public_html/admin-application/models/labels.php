<?php
class Labels extends Model {
	function __construct(){
		$this->db = Syspage::getdb();
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
    function getData($id) {
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
	
	function getLabels($criteria) {
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
        return $this->db->fetch_all($rs);
    }
    
    function search($criteria) {
        $srch = new SearchBase('tbl_language_labels', 'tlang');
        foreach($criteria as $key=>$val) {
            switch($key) {
            case 'id':
                $srch->addCondition('tlang.label_id', '=', intval($val));
                break;
			case 'keyword':
				$srch->addDirectCondition('(tlang.label_key LIKE '. $this->db->quoteVariable('%' . $val . '%') .' OR tlang.label_caption_en like '. $this->db->quoteVariable('%' . $val . '%') .' OR tlang.label_caption_es like '. $this->db->quoteVariable('%' . $val . '%') .')');
                break;
			case 'pagesize':
					$srch->setPageSize($val);
					break;
			case 'page':
					$srch->setPageNumber($val);
					break;			
            
            }
        }
        $srch->addOrder('tlang.label_id', 'desc');
        return $srch;
    }
	
	function addUpdateLabel($data){
		$label_id = intval($data['label_id']);
		$record = new TableRecord('tbl_language_labels');
		$assign_fields = array();
		$assign_fields['label_key'] = $data['label_key'];
		$assign_fields['label_caption_en'] = $data['label_caption_en'];
		$assign_fields['label_caption_es'] = $data["label_caption_es"];
		$record->assignValues($assign_fields);
		if($label_id === 0 && $record->addNew()){
			return $record->getId();
		}elseif($label_id > 0 && $record->update(array('smt'=>'label_id=?', 'vals'=>array($label_id)))){
			return $label_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return false;
	}
	function deleteLabel($label_id){
		$label_id = intval($label_id);
		if($label_id < 1){
			$this->error = 'Invalid request!!';
			return false;
		}
		if($this->db->deleteRecords('tbl_language_labels', array('smt'=>'label_id=?', 'vals'=>array($label_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
    
	function updateLabelText($data){
		//$arr_param=explode("~",$data['id']);
		//$label_id = intval($arr_param[0]);
		$label_id = $data['id'];
		$record = new TableRecord('tbl_language_labels');
		$assign_fields = array();
		if(!empty($data['editval'])){
			$assign_fields[$data['column']] = $data['editval'];
			$record->assignValues($assign_fields);
		if($label_id === 0 && $record->addNew()){
				return $record->getId();
			}elseif($label_id > 0 && $record->update(array('smt'=>'label_id=?', 'vals'=>array($label_id)))){
				return $label_id;
			}else{
				$this->error = $this->db->getError();
				return false;
			}
		}else{
			$this->error="Can not be empty";
			return false;
		}
		return false;
	}
   
}