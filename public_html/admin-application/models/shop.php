<?php
class Shop extends Model {
	
	function __construct() {
		$this->db = Syspage::getdb();
    }
	
	function getTotalPages(){
		return $this->total_pages;
	}
	
	function getTotalRecords(){
		return $this->total_records;
	}
	
	function search($criteria, $count='') {
		
		$pObj=new Product();
		$pObj->addMultipleFields(array('tp.prod_shop as product_shop',"count(prod_id) as totStoreProducts"));
		$pObj->addGroupBy('tp.prod_shop');
		$pObj->doNotCalculateRecords();
		$pObj->doNotLimitRecords();
		$qry_store_products = $pObj->getQuery();	
				
		$srch = new SearchBase('tbl_prod_reviews', 'tpr');
		$srch->joinTable('tbl_products', 'INNER JOIN', 'tpr.review_prod_id=tp.prod_id', 'tp');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tp.prod_shop');
		$srch->addCondition('tpr.review_is_deleted', '=', 0);
		$srch->addMultipleFields(array('tp.prod_shop',"AVG(review_rating) as shop_rating","count(review_id) as totReviews"));
		$qry_store_reviews = $srch->getQuery();
		
		//die($qry_store_reviews);
		
		$srch = new SearchBase('tbl_shop_reports', 'tsr');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tsr.sreport_shop');
		$srch->addCondition('tsr.sreport_is_deleted', '=', 0);
		$srch->addMultipleFields(array('tsr.sreport_shop',"count(sreport_id) as totStoreReports"));
		$qry_store_reports = $srch->getQuery();
		
		
		$srch = new SearchBase('tbl_order_products', '`top`');
		$srch->joinTable('tbl_orders', 'INNER JOIN', '`top`.opr_order_id=`to`.order_id', '`to`');
		$srch->joinTable('tbl_orders_status', 'INNER JOIN', '`top`.opr_status = `tos`.orders_status_id', '`tos`');
		$srch->addCondition('`top`.opr_status', 'IN', (array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS"));
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('`top`.opr_product_shop');
		$srch->addMultipleFields(array('`top`.opr_product_shop',"COUNT(distinct opr_order_id) as totOrders","SUM(opr_qty-opr_refund_qty) as totSoldQty","SUM(((opr_customer_buying_price))*opr_qty) as total","SUM(((opr_customization_price))*opr_qty) as customizations","SUM((opr_commission_charged-opr_refund_commission)) as commission","SUM(opr_shipping_charges) as shipping","(SUM(opr_tax)) as tax","(SUM((opr_customer_buying_price + opr_customization_price)*opr_qty - opr_refund_amount)) as sub_total"));
		$qry_order_shop = $srch->getQuery();
		//die($qry_order_shop);
		
		
        $srch = new SearchBase('tbl_shops', 'ts');
		$srch->joinTable('(' . $qry_store_products . ')', 'LEFT OUTER JOIN', 'ts.shop_id = tqsp.product_shop', 'tqsp');
		$srch->joinTable('(' . $qry_store_reviews . ')', 'LEFT OUTER JOIN', 'ts.shop_id = tqsr.prod_shop', 'tqsr');
		$srch->joinTable('(' . $qry_store_reports . ')', 'LEFT OUTER JOIN', 'ts.shop_id = tqsrp.sreport_shop', 'tqsrp');
		$srch->joinTable('tbl_users', 'LEFT JOIN', 'ts.shop_user_id=tu.user_id and tu.user_is_deleted=0 and tu.user_status=1', 'tu');
		$srch->joinTable('tbl_states', 'LEFT JOIN', 'ts.shop_state=tst.state_id', 'tst');
		$srch->joinTable('(' . $qry_order_shop . ')', 'LEFT OUTER JOIN', 'ts.shop_id = tqos.opr_product_shop', 'tqos');
		$srch->addCondition('ts.shop_is_deleted', '=', 0);
        if($count==true) {
            $srch->addFld('COUNT(ts.shop_id) AS total_rows');
        } else {
			$srch->addMultipleFields(array('ts.*','tqos.*','COALESCE(tqos.totSoldQty,0) as totSoldQty','COALESCE(tqos.totOrders,0) as totOrders','tu.user_name as shop_owner','tu.user_email as shop_owner_email','tu.user_username as shop_owner_username','tst.state_name','COALESCE(tqsp.totStoreProducts,0) as totProducts','COALESCE(round(tqsr.shop_rating,1),0) as shop_rating','COALESCE(tqsr.totReviews,0) as totReviews','COALESCE(tqsrp.totStoreReports,0) as totStoreReports'));
        }
        foreach($criteria as $key=>$val) {
			//if(strval($val)=='') continue;
            switch($key) {
            case 'id':
                $srch->addCondition('ts.shop_id', '=', intval($val));
                break;
			 case 'user':
                $srch->addCondition('ts.shop_user_id', '=', intval($val));
                break;	
			case 'name':
                $srch->addCondition('ts.shop_name', '=', $val);
                break;
			case 'paypal':
                $srch->addCondition('ts.shop_payment_paypal_account', '=', $val);
                break;	
			case 'active':
				if ($val!="")
	                $cnd=$srch->addCondition('ts.shop_status', '=', $val);
             break;
			case 'display':
				if ($val!="")
	                $cnd=$srch->addCondition('ts.shop_vendor_display_status', '=', $val);
            break;
			case 'status':
                $cnd=$srch->addCondition('ts.shop_status', '=', $val);
				$cnd->attachcondition('ts.shop_vendor_display_status', '=', $val,'AND');
                break;
			case 'date_from':
                $srch->addCondition('ts.shop_date', '>=', $val. ' 00:00:00');
                break;
			case 'date_to':
                $srch->addCondition('ts.shop_date', '<=', $val. ' 23:59:59');
                break;
			case 'keyword':
               	$cnd=$srch->addCondition('ts.shop_name', 'like', '%'.$val.'%');
				$cnd->attachcondition('tu.user_name', 'like', '%'.$val.'%','OR');
				$cnd->attachcondition('tu.user_username', 'like', '%'.$val.'%','OR');
                break;
			case 'page':
				$srch->setPageNumber($val);
			break;	
			case 'pagesize':
				$srch->setPageSize($val);
			break;			
            
            }
        }
        return $srch;
    }
	
    function getData($id,$add_criteria=array()) {
        $id = intval($id);
        if($id>0!=true) return array();
       	$srch = new SearchBase('tbl_shops', 'ts');
		$srch->joinTable('tbl_url_alias', 'LEFT OUTER JOIN', 'ts.shop_id = REPLACE(tua.url_alias_query,"shops_id=","")', 'tua');
	 	$srch->addCondition('ts.shop_is_deleted', '=', 0);
		$srch->addCondition('ts.shop_id', '=', $id);
		$srch->addMultipleFields(array('ts.*','tua.url_alias_keyword as seo_url_keyword'));
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $sql = $srch->getQuery();
		//die($sql);
        $rs = $this->db->query($sql);
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function getShops($criteria,$pagesize=10) {
		foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
		if ((intval($pagesize)>0) || isset($add_criteria["pagesize"])){
			$srch->setPageSize(isset($add_criteria["pagesize"])?$add_criteria["pagesize"]:$pagesize);
		}
		
		$srch->addOrder('shop_status','DESC');
		$srch->addOrder('shop_id','DESC');
		//die($srch->getquery());
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return false;
		}
		$row = ($pagesize==1)?$this->db->fetch($rs):$this->db->fetch_all($rs);
        if($row==false) return array();
        else return $row;
        //return $this->db->fetch_all($rs);
    }
	
	function getShopReports($shop_id){
		$shop_id = intval($shop_id);
		if($shop_id < 1){
			return false;
		}
		$srch = new SearchBase('tbl_shop_reports', 'tsr');
		$srch->joinTable('tbl_report_reasons', 'LEFT JOIN', 'tsr.sreport_reason=trr.reportreason_id', 'trr');
		$srch->joinTable('tbl_shops', 'LEFT JOIN', 'tsr.sreport_shop = ts.shop_id', 'ts');
		$srch->joinTable('tbl_users', 'LEFT JOIN', 'tsr.sreport_reported_by = tu.user_id', 'tu');
		$srch->addCondition('sreport_is_deleted', '=', 0);
		$srch->addCondition('sreport_shop', '=', $shop_id);
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addMultipleFields(array('tsr.*', 'ts.shop_name','tu.user_name','tu.user_username','trr.reportreason_title'));
		$rs = $srch->getResultSet();
		return $this->db->fetch_all($rs);
	}
	
	function updateShopStatus($shop_id,$data_update=array()) {
		$shop_id = intval($shop_id);
		if($shop_id < 1 || count($data_update) < 1){
			$this->error = 'Error: Invalid request!!';
			return false;
		}
		if($this->db->update_from_array('tbl_shops', $data_update, array('smt'=>'`shop_id` = ?', 'vals'=> array($shop_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function delete($shop_id){
		$shop_id = intval($shop_id);
		if($shop_id < 1){
			$this->error = 'Error: Invalid request!!';
			return false;
		}
		if($this->db->update_from_array('tbl_shops', array('shop_is_deleted' => 1), array('smt' => 'shop_id = ? and shop_is_default=0', 'vals' => array($shop_id))) && $this->db->update_from_array('tbl_shops', array('shop_is_deleted' => 1), array('smt' => 'shop_id = ? and shop_is_default=0', 'vals' => array($shop_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function delete_report($sreport_id){
		$sreport_id = intval($sreport_id);
		if($sreport_id < 1){
			$this->error = 'Error: Invalid request!!';
			return false;
		}
		if($this->db->update_from_array('tbl_shop_reports', array('sreport_is_deleted' => 1), array('smt' => 'sreport_id = ? ', 'vals' => array($sreport_id))) ){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
    
    
	
	function getAssociativeArray($not_to_include=null) {
		$query = "SELECT shop_id, shop_name FROM tbl_shops WHERE shop_is_deleted='0' ";
        if (!empty($not_to_include)) $query .= " AND shop_id NOT IN ({$not_to_include})";
		$query .= " ORDER BY shop_is_default desc,shop_name";
        $rs = $this->db->query($query);
        return $this->db->fetch_all_assoc($rs);
    }
	
   	function addUpdateShopInfo($data){
		$shop_id = intval($data['shop_id']);
		$user_id = intval($data['shop_user_id']);
		if($user_id < 1) return false;
		$record = new TableRecord('tbl_shops');
		$values = array(
					'shop_user_id' => $data['shop_user_id'],
					'shop_name' => $data['shop_name'],
					'shop_featured' => $data['shop_featured'],
					'shop_title' => $data['shop_title'],
					'shop_slogan' => $data['shop_slogan'],
					'shop_description' => $data['shop_description'],
					'shop_city' => $data['shop_city'],
					'shop_announcement' => $data['shop_announcement'],
					'shop_general_message' => $data['shop_general_message'],
					'shop_welcome_message' => $data['shop_welcome_message'],
					'shop_payment_policy' => $data['shop_payment_policy'],
					'shop_delivery_policy' => $data['shop_delivery_policy'],
					'shop_refund_policy' => $data['shop_refund_policy'],
					'shop_additional_info' => $data['shop_additional_info'],
					'shop_seller_info' => $data['shop_seller_info'],
					'shop_page_title' => $data['shop_page_title'],
					'shop_meta_keywords' => $data['shop_meta_keywords'],
					'shop_meta_description' => $data['shop_meta_description'],
					//'shop_vendor_display_status' => $data['shop_vendor_display_status'],
					'shop_date' => date('Y-m-d H:i:s'),
					'shop_update_date' => date('Y-m-d H:i:s'),
					'shop_state' => intval($data['ua_state']),
					'shop_country' => intval($data['shop_country']),
					'shop_contact_person' => $data['shop_contact_person'],
					'shop_address_line_1' => $data['shop_address_line_1'],
					'shop_address_line_2' => $data['shop_address_line_2'],
					'shop_postcode' => $data['shop_postcode'],
					'shop_phone' => $data['shop_phone'],
					'shop_payment_paypal_account' => $data['shop_payment_paypal_account'],
					'shop_payment_paypal_firstname' => $data['shop_payment_paypal_firstname'],
					'shop_payment_paypal_lastname' => $data['shop_payment_paypal_lastname'],
					'shop_enable_cod_orders' => $data['shop_enable_cod_orders'],
				);
		if(isset($data['shop_logo']) && strlen($data['shop_logo']) > 0){
			$values['shop_logo'] = $data['shop_logo'];
		}elseif(isset($data['remove_shop_logo']) && intval($data['remove_shop_logo']) == 1){
			$values['shop_logo'] = '';
		}
		
		if(isset($data['shop_banner']) && strlen($data['shop_banner']) > 0){
			$values['shop_banner'] = $data['shop_banner'];
		}elseif(isset($data['remove_shop_banner']) && intval($data['remove_shop_banner']) == 1){
			$values['shop_banner'] = '';
		}
		
		if($shop_id === 0 && !isset($data['shop_status'])){
			$values['shop_vendor_display_status'] = 1;
			$values['shop_status'] = 1;
			$values['shop_is_deleted'] = 0;
		}if($shop_id > 0 && isset($data['shop_status'])){
			$values['shop_status'] = intval($data['shop_status']);
		}	
		$record->assignValues($values);
		$sqlquery=$record->getinsertquery();
		$arr=$record->getFlds();
		foreach($arr as $field => $val) {
			 if ($field!='shop_date'){
				 	if ($field=='shop_update_date'){
						$fields[] = "$field = '".date('Y-m-d H:i:s')."'";
					}else{
						$fields[] = "$field = ".$this->db->quoteVariable($val)."";
					}
			 }
			
		}
		$sqlquery = $sqlquery." on duplicate KEY UPDATE " . join(', ', $fields);
		//die($sqlquery);
		if(!$this->db->query($sqlquery)){
			$this->error = $this->db->getError();
			return false;
		}
		
		$last_shop_id=$this->db->insert_id();
		if ($last_shop_id>0)
			$shop_id=$last_shop_id;
		
		if (!$this->db->deleteRecords('tbl_url_alias', array('smt' => 'url_alias_query = ?', 'vals' => array('shops_id='.$shop_id)))){
			$this->error = $this->db->getError();
			return false;
		}
	
		if (!empty($data['seo_url_keyword'])) {
			if(!$this->db->insert_from_array('tbl_url_alias', array('url_alias_query'=>'shops_id='.$shop_id,'url_alias_keyword'=>$data['seo_url_keyword']))){
				$this->error = $this->db->getError();
				return false;
			}
		}
		
		return true;
	}
}