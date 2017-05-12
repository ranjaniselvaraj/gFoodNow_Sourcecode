<?php
class SmartRecommendations extends Model {
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
	
	function getProducts($criteria) {
		foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::searchProducts($add_criteria);
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return false;
		}
        $arr_products=$this->db->fetch_all($rs);
		return $arr_products;
    }
    function searchProducts($criteria) {
		$markUp=0;
		$srch=new SearchBase('tbl_smart_products_weightage','tspw');
		$srch->joinTable('tbl_products', 'LEFT JOIN', 'tspw.spw_product_id=tp.prod_id', 'tp');
		$srch->joinTable('tbl_prod_details', 'LEFT JOIN', 'tp.prod_id=tpd.prod_id', 'tpd');
		$srch->joinTable('tbl_shops', 'LEFT JOIN', 'tp.prod_shop=ts.shop_id and ts.shop_is_deleted=0', 'ts');
		$srch->joinTable('tbl_categories', 'LEFT JOIN', 'tp.prod_category=tc.category_id', 'tc');
		$srch->joinTable('tbl_users', 'INNER JOIN', 'ts.shop_user_id=tu.user_id', 'tu');
		$srch->joinTable('tbl_product_brands', 'LEFT JOIN', 'tp.prod_brand=tpb.brand_id and tpb.brand_is_deleted=0', 'tpb');
		$srch->addCondition('tp.prod_is_deleted','=',0);
        $srch->addMultipleFields(array('DISTINCT tp.*','tpb.brand_id','tpb.brand_name','tpb.brand_status','ts.shop_id','ts.shop_name',
		'ts.shop_title','tspw.*','tu.user_id','tu.user_username','tu.user_name','tu.user_email','tu.user_phone'));
        foreach($criteria as $key=>$val) {
			if(strval($val)=='') continue;
            switch($key) {
            case 'id':
                $srch->addCondition('tp.prod_id', '=', intval($val));
                break;
			case 'type':
                $srch->addCondition('tp.prod_type', '=', intval($val));
                break;	
			case 'shop':
                $cnd=$srch->addCondition('tp.prod_shop', '=', intval($val));
                break;
			case 'added_by':
				$srch->addCondition('tp.prod_added_by', '=',$val);
                break;		
			case 'status':
                $cnd=$srch->addCondition('tp.prod_status', '=', intval($val));
				//$cnd->attachcondition('tpb.brand_status', '=', $val,'AND');
				$cnd->attachcondition('tp.prod_is_expired', '=', 0,'AND');
				$cnd->attachcondition('ts.shop_status', '=', $val,'AND');
				$cnd->attachcondition('ts.shop_vendor_display_status', '=', $val,'AND');
				$cnd->attachcondition('tu.user_status', '=', 1,'AND');
				$cnd->attachcondition('tu.user_is_deleted', '=', 0,'AND');
				$cnd->attachcondition('tu.user_email_verified', '=', 1,'AND');
                break;
			 case 'brand':
			 	if (is_numeric($val))
	                $srch->addCondition('tp.prod_brand', '=', intval($val));
				else	
					$srch->addCondition('tp.prod_brand', 'IN', explode(",",$val));
                break;	
			case 'condition':
                $srch->addCondition('tp.prod_condition', 'IN', (explode(",",$val)));
                break;	
			case 'sku':
                $srch->addCondition('tp.prod_sku', '=',$val);
                break;
			case 'property':
                $srch->addCondition('tpd.'.$val, '=', 1);
                break;
			case 'category':
				if ((int) $val > 0){
					$cats=array_merge(array($val),array_keys(Categories::getProdClassifiedCategoriesAssocArray($val)));
					$srch->addCondition('tp.prod_category', 'IN',$cats);
				}	
			break;
		
			case 'active':
	                $cnd=$srch->addCondition('tp.prod_status', '=',1);
					$cnd->attachCondition('tp.prod_is_expired', '=',0,'AND');
            break;
			case 'excluded':
	                $cnd=$srch->addCondition('tspw.spw_is_excluded', '=',1);
            break;
			case 'minprice':
                $cnd=$srch->addDirectCondition("(`prod_sale_price`*(1+($markUp/100))>$val)");
                break;
			case 'maxprice':
                $cnd=$srch->addDirectCondition("(`prod_sale_price`*(1+($markUp/100)) <= $val)");
                break;
			case 'date_from':
                $srch->addCondition('tp.prod_published_on', '>=', $val. ' 00:00:00');
                break;
			case 'date_to':
                $srch->addCondition('tp.prod_published_on', '<=', $val. ' 23:59:59');
                break;
			case 'keyword':
				if ($val!=""){
						$srchTag = new SearchBase('tbl_product_to_tags', 'tptags');
						$srchTag->joinTable('tbl_product_tags', 'LEFT JOIN', 'tptags.pt_tag_id=ptags.ptag_id', 'ptags');
						$srchTag->doNotCalculateRecords();
						$srchTag->doNotLimitRecords();
						$srchTag->addGroupBy('tptags.pt_product_id');
				        $srchTag->addMultipleFields(array('tptags.pt_product_id','GROUP_CONCAT(ptags.`ptag_name`) as product_tags'));
						$qry_tag_products = $srchTag->getQuery();
						$srch->joinTable('(' . $qry_tag_products . ')', 'LEFT OUTER JOIN', 'tp.prod_id = tqtp.pt_product_id', 'tqtp');
						$val=urldecode($val);
						$arr_keywords = explode(" ",$val);
						$arr_columns = array(
									'tp.prod_sku',
									'tp.prod_name',
									'tp.prod_model',
									'tp.prod_slug',
									'tpd.prod_meta_title',
									'tqtp.product_tags',
									'tpb.brand_name',
									'tc.category_name'
									);
						$strKeys = "";
						foreach($arr_columns as $column){
							foreach($arr_keywords as $keyword){
								$strKeys.=(($strKeys=="")?"":" + ")." (case when INSTR(".$column.",".$this->db->quoteVariable(trim($keyword)).")>0 then 1 else 0 END) ";
							}
						}
						$srch->addDirectCondition($strKeys);
						$srch->addFld($strKeys.' as Rank');
						$srch->addOrder('Rank','desc');
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
		$srch->addOrder('if (spw_custom_weightage_valid_till>now(),spw_weightage+spw_custom_weightage,spw_weightage)','desc');
        return $srch;
    }
		
	function getWeightSettings() {
		$srch = new SearchBase('tbl_smart_weightage_settings');
        $rs = $srch->getResultSet();
        $row = $this->db->fetch_all($rs);
       	$srch->addMultipleFields(array('event_key', 'event_weightage'));
		$rs = $srch->getResultSet();
		return $this->db->fetch_all_assoc($rs);
	}
	
	function addUpdateWeightageSettings($data){
		if (isset($data['weightage_settings'])) {
			
			if (!$this->db->deleteRecords('tbl_smart_weightage_settings', array('smt' => '1 = ?', 'vals' => array(1)))){
				$this->error = $this->db->getError();
				return false;
			}
			foreach ($data['weightage_settings'] as $wkey=>$wsetting) {
					if (!$this->db->query("INSERT INTO tbl_smart_weightage_settings SET event_key = '" . $wkey . "', event_weightage = '" .$wsetting  . "'"))	{
					$this->error = $this->db->getError();
					return false;
					}
				}
			
		}
		return true;
	}
	
	function getWeightageEvents(){
		$arr= array(
						"products" => array(    
									'view' => array(
							    	      'caption' => 'Product Viewed',
							        	  'extra' => ''
								        ),
									'time_spent' => array(
							    	      'caption' => 'Time Spent',
							        	  'extra' => 'Here weightage will be incremented after every 30 seconds.'
								        ),	
									'cart' => array(
								          'caption' => 'Added to Cart',
								          'extra' => ''
								        ),
									'cart_remove' => array(
								          'caption' => 'Removed from Cart',
								          'extra' => ''
								        ),	
									'favorite' => array(
								          'caption' => 'Marked Favorite',
								          'extra' => ''
								        ),	
									'unfavorite' => array(
								          'caption' => 'Marked Un-favorite',
								          'extra' => ''
								        ),
									'wishlist' => array(
								          'caption' => 'Added to Wishlist',
								          'extra' => ''
								        ),
									'unwishlist' => array(
								          'caption' => 'Removed from Wishlist',
								          'extra' => ''
								        ),
									'rating_multiply_factor' => array(
								          'caption' => 'Rated',
								          'extra' => 'Here weightage will be multiplied with user\'s rating.'
								        ),				
									'order_paid' => array(
								          'caption' => 'Payment Received',
								          'extra' => ''
								        ),
									'order_cancelled' => array(
								          'caption' => 'Order Cancelled',
								          'extra' => ''
								        ),
									'order_completed' => array(
								          'caption' => 'Order Completed',
								          'extra' => ''
								        ),
							),
						
					);
		$weightages = $this->getWeightSettings();			
		foreach ($arr as $pkey=>$pval) {
					foreach ($pval as $skey=>$sval) {
							$arr[$pkey][$skey]['value'] = $weightages[$pkey."#".$skey] ;
					}
		}
		return $arr;			
	}
	
	
	function updateProductRecommendation($data){
		$this->db->update_from_array('tbl_smart_products_weightage',array($data['field']=>$data['value']), array('smt'=>'spw_product_id = ?', 'vals'=>array($data['id'])));
		return true;
	}
	
	function clear(){
		if (!$this->db->deleteRecords('tbl_smart_products_weightage', array('smt' => '1 = ?', 'vals' => array(1)))){
				$this->error = $this->db->getError();
				return false;
		}
		if (!$this->db->deleteRecords('tbl_smart_user_activity_browsing', array('smt' => '1 = ?', 'vals' => array(1)))){
				$this->error = $this->db->getError();
				return false;
		}
		
		if (!$this->db->deleteRecords('tbl_smart_log_actions', array('smt' => '1 = ?', 'vals' => array(1)))){
				$this->error = $this->db->getError();
				return false;
		}
		if (!$this->db->deleteRecords('tbl_smart_related_products', array('smt' => '1 = ?', 'vals' => array(1)))){
				$this->error = $this->db->getError();
				return false;
		}
		if (!$this->db->deleteRecords('tbl_products_browsing_history', array('smt' => '1 = ?', 'vals' => array(1)))){
				$this->error = $this->db->getError();
				return false;
		}
		return true;
	}
	
	
	
	function getCategories($criteria) {
		foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::searchCategories($add_criteria);
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return false;
		}
        $arr_categories=$this->db->fetch_all($rs);
		return $arr_categories;
    }
    function searchCategories($criteria) {
		$markUp=0;
		$srch=new SearchBase('tbl_smart_category_weightage','tscw');
		$srch->joinTable('tbl_categories', 'LEFT JOIN', 'tscw.scw_category_id=tc.category_id', 'tc');
		$srch->addCondition('tc.category_is_deleted','=',0);
        $srch->addMultipleFields(array('DISTINCT tc.*','tscw.*'));
        foreach($criteria as $key=>$val) {
			if(strval($val)=='') continue;
            switch($key) {
            case 'id':
			case 'category':	
                $srch->addCondition('tc.category_id', '=', intval($val));
                break;
			case 'keyword':
                $srch->addCondition('tc.category_name', 'LIKE','%'.$val.'%');
                break;	
			case 'pagesize':
				$srch->setPageSize($val);
				break;
			case 'page':
				$srch->setPageNumber($val);
				break;	
            }
        }
		$srch->addOrder('if (scw_custom_weightage_valid_till>now(),scw_weightage+scw_custom_weightage,scw_weightage)','desc');
        return $srch;
    }
	
		
	function getBrowsingHistoryProducts($criteria) {
		foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
		$srch = self::searchBrowsingHistoryProducts(array_diff_key($add_criteria,array_flip(array("pagesize","page"))),true);
		$rs = $srch->getResultSet();
		$records=$this->db->fetch($rs);
		//die($records['total_rows']."#");
				
		$srch = self::searchBrowsingHistoryProducts($add_criteria);
		$srch->doNotCalculateRecords(true);
		$rs = $srch->getResultSet();
		$this->total_records = $records['total_rows'];
		$this->total_pages = ceil($this->total_records/$add_criteria['pagesize']);
		$arr_products=$this->db->fetch_all($rs);
		return $arr_products;
    }
    function searchBrowsingHistoryProducts($criteria, $count='') {
		$markUp=0;
		$srch=new SearchBase('tbl_products_browsing_history','tpbh');
		$srch->joinTable('tbl_products', 'LEFT JOIN', 'tpbh.pbhistory_prod_id=tp.prod_id', 'tp');
		$srch->joinTable('tbl_shops', 'LEFT JOIN', 'tp.prod_shop=ts.shop_id and ts.shop_is_deleted=0', 'ts');
		$srch->joinTable('tbl_categories', 'LEFT JOIN', 'tp.prod_category=tc.category_id', 'tc');
		$srch->joinTable('tbl_users', 'INNER JOIN', 'ts.shop_user_id=tu.user_id', 'tu');
		$srch->joinTable('tbl_product_brands', 'LEFT JOIN', 'tp.prod_brand=tpb.brand_id and tpb.brand_is_deleted=0', 'tpb');
		$srch->joinTable('tbl_users', 'LEFT JOIN', 'tpbh.pbhistory_user_id=tubh.user_id', 'tubh');
		$srch->addCondition('tp.prod_is_deleted','=',0);
		if($count==true) {
			$srch->doNotCalculateRecords(true);
            $srch->addFld('COUNT(DISTINCT tp.prod_id) AS total_rows');
        }else{
        $srch->addMultipleFields(array('DISTINCT tp.*','tpb.brand_id','tpb.brand_name','tpb.brand_status','ts.shop_id','ts.shop_name',
		'ts.shop_title','tpbh.*','tu.user_id','tu.user_username','tu.user_name','tu.user_email','tu.user_phone','tubh.user_name as visitor_name'));
		}
        foreach($criteria as $key=>$val) {
			if(strval($val)=='') continue;
            switch($key) {
            case 'id':
                $srch->addCondition('tp.prod_id', '=', intval($val));
                break;
			case 'type':
                $srch->addCondition('tp.prod_type', '=', intval($val));
                break;	
			case 'shop':
                $cnd=$srch->addCondition('tp.prod_shop', '=', intval($val));
                break;
			case 'added_by':
				$srch->addCondition('tp.prod_added_by', '=',$val);
                break;
			case 'user':
                $cnd=$srch->addCondition('tpbh.pbhistory_user_id', '=', intval($val));
                break;			
			case 'status':
                $cnd=$srch->addCondition('tp.prod_status', '=', intval($val));
				$cnd->attachcondition('tp.prod_is_expired', '=', 0,'AND');
				$cnd->attachcondition('ts.shop_status', '=', $val,'AND');
				$cnd->attachcondition('ts.shop_vendor_display_status', '=', $val,'AND');
				$cnd->attachcondition('tu.user_status', '=', 1,'AND');
				$cnd->attachcondition('tu.user_is_deleted', '=', 0,'AND');
				$cnd->attachcondition('tu.user_email_verified', '=', 1,'AND');
                break;
			 case 'brand':
			 	if (is_numeric($val))
	                $srch->addCondition('tp.prod_brand', '=', intval($val));
				else	
					$srch->addCondition('tp.prod_brand', 'IN', explode(",",$val));
                break;	
			case 'condition':
                $srch->addCondition('tp.prod_condition', 'IN', (explode(",",$val)));
                break;	
			case 'sku':
                $srch->addCondition('tp.prod_sku', '=',$val);
                break;
			
			case 'category':
				if ((int) $val > 0){
					$cats=array_merge(array($val),array_keys(Categories::getProdClassifiedCategoriesAssocArray($val)));
					$srch->addCondition('tp.prod_category', 'IN',$cats);
				}	
			break;
		
			case 'active':
	                $cnd=$srch->addCondition('tp.prod_status', '=',1);
					$cnd->attachCondition('tp.prod_is_expired', '=',0,'AND');
            break;
			case 'minprice':
                $cnd=$srch->addDirectCondition("(`prod_sale_price`*(1+($markUp/100))>$val)");
                break;
			case 'maxprice':
                $cnd=$srch->addDirectCondition("(`prod_sale_price`*(1+($markUp/100)) <= $val)");
                break;
			case 'date_from':
                $srch->addCondition('tp.prod_published_on', '>=', $val. ' 00:00:00');
                break;
			case 'date_to':
                $srch->addCondition('tp.prod_published_on', '<=', $val. ' 23:59:59');
                break;
			case 'keyword':
				if ($val!=""){
						$srchTag = new SearchBase('tbl_product_to_tags', 'tptags');
						$srchTag->joinTable('tbl_product_tags', 'LEFT JOIN', 'tptags.pt_tag_id=ptags.ptag_id', 'ptags');
						$srchTag->doNotCalculateRecords();
						$srchTag->doNotLimitRecords();
						$srchTag->addGroupBy('tptags.pt_product_id');
				        $srchTag->addMultipleFields(array('tptags.pt_product_id','GROUP_CONCAT(ptags.`ptag_name`) as product_tags'));
						$qry_tag_products = $srchTag->getQuery();
						$srch->joinTable('(' . $qry_tag_products . ')', 'LEFT OUTER JOIN', 'tp.prod_id = tqtp.pt_product_id', 'tqtp');
						$val=urldecode($val);
						$arr_keywords = explode(" ",$val);
						$arr_columns = array(
									'tp.prod_sku',
									'tp.prod_name',
									'tp.prod_model',
									'tp.prod_slug',
									//'tpd.prod_meta_title',
									'tqtp.product_tags',
									'tpb.brand_name',
									'tc.category_name'
									);
						$strKeys = "";
						foreach($arr_columns as $column){
							foreach($arr_keywords as $keyword){
								$strKeys.=(($strKeys=="")?"":" + ")." (case when INSTR(".$column.",".$this->db->quoteVariable(trim($keyword)).")>0 then 1 else 0 END) ";
							}
						}
						$srch->addDirectCondition($strKeys);
						$srch->addFld($strKeys.' as Rank');
						$srch->addOrder('Rank','desc');
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
		//$srch->addOrder('if (spw_custom_weightage_valid_till>now(),spw_weightage+spw_custom_weightage,spw_weightage)','desc');
        return $srch;
    }
	
	
	function addUpdateUserBrowsingActivity($record_id,$record_type){
			$record_id = intval($record_id);
			$record_type = intval($record_type);
        	if (($record_id>0!=true) || ($record_type>0!=true)) return array();
			$uObj= new User();
			$user_id =  $uObj->isUserLogged()?$uObj->getLoggedUserId():$uObj->getUserIdFromCookies();
			$record = new TableRecord('tbl_smart_user_activity_browsing');
			$assign_fields['uab_session_id'] = session_id();
			$assign_fields['uab_user_id'] = $user_id;
			$assign_fields['uab_record_id'] = $record_id;
			$assign_fields['uab_record_type'] = $record_type;
			$assign_fields['uab_last_action_datetime'] = date('Y-m-d H:i:s');
			$record->assignValues($assign_fields);
			//die($record->getinsertquery());
			if ($record->addNew(array('IGNORE'),array('uab_last_action_datetime'=>date('Y-m-d H:i:s')))){
				return true;
			}else{
				return false;
			}
			
			
	}
	
	
   
}