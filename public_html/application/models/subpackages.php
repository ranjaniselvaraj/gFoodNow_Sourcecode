<?php
class SubPackages extends Model{
    function __construct(){
		$this->db = Syspage::getdb();
    }
	function getSubPackageId() {
        return $this->merchantsubpack_id;
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
	
	function getData($id,$criteria=array()) {
        $id = intval($id);
        if($id>0!=true) return array();
       	$add_criteria['id'] = $id;
		$add_criteria = array_merge($add_criteria,$criteria);
        $srch = self::search($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function getSubPackages($criteria , $fieldsArr = array()) {
        foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria , false , $fieldsArr);
        $rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return array();
		}
        $rows = $this->db->fetch_all($rs);
        if($rows==false) return array();
		$returnArr = [];
		if( isset($criteria['exclude_free_package']) && $criteria['exclude_free_package'] ){
			foreach($rows as $key=>$row){
				if( ( $row['merchantsubpack_actual_price'] == 0 ) ){
					//unset($rows[$key]); 
				}
				else{
					$returnArr[] = $rows[$key];
				}
			}
		}
		else{
			$returnArr = $rows;
		}
        return $returnArr ;
	}
	
	function getFreeTrialPack($packageId = 0) {
		$srch = $this->search(array('merchantsubpack_merchantpack_id' => $packageId , 'merchantsubpack_actual_price' => 0 ));
		$srch->addMultipleFields(array('merchantsubpack_id'));
		$rs = $srch->getResultSet();
		$result = $this->db->fetch($rs);
		return isset($result['merchantsubpack_id']) ? $result['merchantsubpack_id'] : 0;
	}
	
	function search($criteria, $count='' , $fieldsArr = array() , $addOrder = true) {
        $srch = new SearchBase('tbl_subscription_merchant_sub_packages', 'tmsp');
		$srch->joinTable('tbl_subscription_merchant_packages','LEFT JOIN','tmsp.merchantsubpack_merchantpack_id = tmp.merchantpack_id','tmp');
        if($count==true) {
            $srch->addFld('COUNT(merchantsubpack_id) AS total_rows');
        } else {
			if(!empty($fieldsArr) && is_array($fieldsArr) && count($fieldsArr))
			{
				$srch->addMultipleFields($fieldsArr);
			} else {
				$srch->addMultipleFields(array('tmsp.*','tmp.merchantpack_name','tmp.merchantpack_description','merchantpack_max_products','merchantpack_active','merchantpack_images_per_product','merchantsubpack_total_occurrance','merchantsubpack_subs_frequency','merchantpack_commission_rate'));
			}
        }
		// die($srch->getQuery());
        foreach($criteria as $key=>$val) {
            switch($key) {
            case 'id':
                $srch->addCondition('merchantsubpack_id', '=', intval($val));
                break;
			case 'id_not_equal_to':
				$srch->addCondition('merchantsubpack_id', '!=', intval($val));
			break;
			case 'merchantsubpack_merchantpack_id':
				$srch->addCondition('merchantsubpack_merchantpack_id', '=', intval($val));
			break;
			case 'merchantsubpack_actual_price':
				$srch->addCondition('merchantsubpack_actual_price', '=', intval($val));
			break;
			case 'exclude_free_package':
				$srch->addCondition('merchantsubpack_actual_price', '!=', 0 );
			break;
			case 'status':
				$srch->addCondition('merchantsubpack_active', '=', intval($val));
			break;
			case 'keyword':
                $srch->addCondition('merchantsubpack_name', 'like', '%'.$val.'%');
                break;
			case 'pagesize':
				$srch->setPageSize($val);
				break;
			case 'page':
				$srch->setPageNumber($val);
				break;			
            }
        }
		if($addOrder)
		{
			$srch->addOrder('merchantsubpack_active', 'DESC');
			$srch->addOrder('merchantsubpack_merchantpack_id', 'ASC');
			//$srch->addOrder('merchantsubpack_display_order', 'asc');
			// $srch->addOrder('merchantsubpack_name', 'asc');
			$srch->addOrder('merchantsubpack_id', 'asc');
		}
        return $srch;
    }
	
	function addUpdate($data){
		$merchantsubpack_id = intval($data['merchantsubpack_id']);
		$record = new TableRecord('tbl_subscription_merchant_sub_packages');
		$assign_fields = array();
		$assign_fields['merchantsubpack_merchantpack_id'] = $data['merchantsubpack_merchantpack_id'];
		$assign_fields['merchantsubpack_subs_frequency'] = $data['merchantsubpack_subs_frequency'];
		$assign_fields['merchantsubpack_subs_period'] = $data['merchantsubpack_subs_period'];
		$assign_fields['merchantsubpack_actual_price'] = $data['merchantsubpack_actual_price'];
		$assign_fields['merchantsubpack_recurring_price'] = $data['merchantsubpack_recurring_price'];
		$assign_fields['merchantsubpack_total_occurrance'] = $data['merchantsubpack_total_occurrance'];
		$assign_fields['merchantsubpack_display_order'] = $data['merchantsubpack_display_order'];
		$record->assignValues($assign_fields);
		if($merchantsubpack_id === 0 && $record->addNew()){
			$this->merchantsubpack_id=$record->getId();
		}elseif($merchantsubpack_id > 0 && $record->update(array('smt'=>'merchantsubpack_id=?', 'vals'=>array($merchantsubpack_id)))){
			$this->merchantsubpack_id=$merchantsubpack_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return $this->merchantsubpack_id;
	}
	
	function addUpdateFreeTrial( $sub_pack_id , $packageId , $subs_days){
		
		$merchantsubpack_id = intval($sub_pack_id);
		$record = new TableRecord('tbl_subscription_merchant_sub_packages');
		$assign_fields = array();
		$assign_fields['merchantsubpack_merchantpack_id'] = $packageId;
		$assign_fields['merchantsubpack_subs_frequency'] = $subs_days;
		$assign_fields['merchantsubpack_actual_price'] = 0;
		$assign_fields['merchantsubpack_recurring_price'] = 0;
		$assign_fields['merchantsubpack_total_occurrance'] = 0;
		
		$record->assignValues($assign_fields);
		if($merchantsubpack_id === 0 && $record->addNew()){
			$this->merchantsubpack_id=$record->getId();
		}elseif($merchantsubpack_id > 0 && $record->update(array('smt'=>'merchantsubpack_id=?', 'vals'=>array($merchantsubpack_id)))){
			$this->merchantsubpack_id=$merchantsubpack_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return $this->merchantsubpack_id;
	}
	
	function getAssociativeArray(){
		$srch = new SearchBase('tbl_subscription_merchant_packages', 'mp');
		$srch->addMultipleFields(array('merchantsubpack_id', 'merchantpack_name'));
		$srch->addOrder('merchantpack_name', 'ASC');
		$rs = $srch->getResultSet();
		return $this->db->fetch_all_assoc($rs);
	}
	
	function getAssocSubPackages($criteria = array()){
		
		foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
	
        $srch = self::search($add_criteria);
		
		$srch->addMultipleFields(array('merchantsubpack_id' , 'merchantsubpack_subs_frequency', 'merchantsubpack_subs_period' , 'merchantsubpack_actual_price'));
		$srch->addCondition('merchantsubpack_active','=','1');
		//die($srch->getquery());
		$rs = $srch->getResultSet();
		
		$result = $this->db->fetch_all($rs);
		$returnArr =[];
		
		foreach($result as $record)
		{
			if( isset($criteria['exclude_free_package']) && $criteria['exclude_free_package'] ){
				if($record['merchantsubpack_actual_price'] == 0 ){
					continue;
				}
			}
			$returnArr[$record['merchantsubpack_id']] = SubscriptionHelper::displayFormattedSubPackage( $record['merchantsubpack_actual_price']  , $record['merchantsubpack_subs_frequency'], $record['merchantsubpack_subs_period']);
		}
		return $returnArr;
	}
	
	function getCheapestPack($criteria = array()){
		
		foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria , false , array() , false);
		
		//$srch->addMultipleFields(array('ceil(merchantsubpack_actual_price) merchantsubpack_actual_price' ,'merchantsubpack_subs_frequency'));
		$srch->addMultipleFields(array('merchantsubpack_actual_price' ,'merchantsubpack_subs_frequency'));
		
		$srch->addOrder('merchantsubpack_actual_price' , 'asc');
		$rs = $srch->getResultSet();
		
		return $this->db->fetch($rs);
		
	}
	
	function deleteSubPackage($merchantsubpack_id){
		$merchantsubpack_id = intval($merchantsubpack_id);
		if($merchantsubpack_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		$this->db->deleteRecords('tbl_subscription_merchant_sub_packages' , array('smt'=>'`merchantsubpack_id`=? ', 'vals'=>array($merchantsubpack_id)));
		
	}
	
	function updateSubPackageStatus($merchantsubpack_id,$mod){
		$merchantsubpack_id = intval($merchantsubpack_id);
		if($merchantsubpack_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		switch($mod) {
            case 'block':
            	$data_to_update = array(
					'merchantsubpack_active'=>0,
	            );
            break;
            case 'unblock':
    	        $data_to_update = array(
					'merchantsubpack_active'=>1,
            	);
            break;
           
        }
		if(count($data_to_update)>0!=true) {
            $this->error =Utilities::getLabel('L_Action_Trying_Perform_Not_Valid');
	        return false;
        }
		if($this->db->update_from_array('tbl_subscription_merchant_sub_packages',$data_to_update, array('smt'=>'`merchantsubpack_id`=? ', 'vals'=>array($merchantsubpack_id)),true)){
			return true;
		}
		$this->error = $this->db->getError();
	}
	
	public function deleteFreePlan($packageId )
	{
		$this->db->deleteRecords( 'tbl_subscription_merchant_sub_packages' ,array('smt'=>'`merchantsubpack_merchantpack_id`=?  and `merchantsubpack_actual_price`=? ', 'vals'=>array($packageId , 0 )) );
	}
	
}
