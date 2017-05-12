<?php
class Ppcfees extends Model {
	function __construct(){
		$this->db = Syspage::getdb();
    }
	
	function getPPCSettings($trashed=0) {
		$srch = new SearchBase('tbl_ppc_fees_settings', 'tpfs');
		$srch->joinTable('tbl_products', 'LEFT JOIN', 'tpfs.ppcfeessetting_product=tp.prod_id', 'tp');
		$srch->joinTable('tbl_categories', 'LEFT JOIN', 'tpfs.ppcfeessetting_category=tc.category_id', 'tc');
		$srch->joinTable('tbl_users', 'LEFT JOIN', 'tpfs.ppcfeessetting_vendor=tu.user_id', 'tu');
		$srch->addCondition('tpfs.ppcfeessetting_is_deleted', '=',$trashed);
		$srch->addMultipleFields(array('tpfs.*','tp.prod_name','tc.category_name','CONCAT(tu.user_name," [",tu.user_username,"]") as vendor'));
		//$srch->addOrder('tcs.sformfield_order', 'asc');
        $rs = $srch->getResultSet();
        $row = $this->db->fetch_all($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function addUpdateFeesSettings($data){
		if (isset($data['fees_setting'])) {
			foreach ($data['fees_setting'] as $key=>$val){
					if ($val["product_id"]>0){
						unset($val["vendor_id"]);
						unset($val["category_id"]);
					}
					$srch = new SearchBase('tbl_ppc_fees_settings');
					$srch->addCondition('ppcfeessetting_product', '=', $val["product_id"]);
					$srch->addCondition('ppcfeessetting_vendor', '=', $val["vendor_id"]);
					$srch->addCondition('ppcfeessetting_category', '=', $val["category_id"]);
					$srch->addCondition('ppcfeessetting_is_deleted', '=',0);
					$srch->doNotCalculateRecords();
					$srch->doNotLimitRecords();
					$rs = $srch->getResultSet();
					if ($row = $this->db->fetch($rs)) {
						if ($row["ppcfeessetting_is_mandatory"]==1){
							$this->db->update_from_array('tbl_ppc_fees_settings', array('ppcfeessetting_fees' => $val["fees"]), array('smt' => 'ppcfeessetting_id = ?', 'vals' => array($row['ppcfeessetting_id'])));
							continue;
						}
						else
						$this->db->update_from_array('tbl_ppc_fees_settings', array('ppcfeessetting_is_deleted' => 1), array('smt' => 'ppcfeessetting_id = ?', 'vals' => array($row['ppcfeessetting_id'])));
					}
				
					$record = new TableRecord('tbl_ppc_fees_settings');
					$arr=array("ppcfeessetting_id"=>(int)$val["id"],"ppcfeessetting_product"=>$val["product_id"],"ppcfeessetting_vendor"=>$val["vendor_id"],"ppcfeessetting_category"=>$val["category_id"],"ppcfeessetting_fees"=>$val["fees"],"ppcfeessetting_is_deleted"=>0,"ppcfeessetting_is_mandatory"=>(int)$val["mandatory"]);
					$record->assignValues($arr);
					if($record->addNew()){
						$inserted_record_id=$record->getId();
					}else{
						$this->error = $this->db->getError();
						return false;
					}
				
			}
		}
		return true;
	
		
	}
}