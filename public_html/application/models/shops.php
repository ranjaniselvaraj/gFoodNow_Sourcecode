<?php
class Shops extends Model {
	
	function __construct() {
		$this->db = Syspage::getdb();
		$criteria = array();
		$pmObj=new Paymentmethods();
		$payment_method=$pmObj->getPaymentMethodByCode(CONF_PAPYAL_ADAPTIVE_KEY);
		if ($payment_method && $payment_method['pmethod_status']){
			$criteria['paypal_verified'] = true;
		}
		$this->criteria = $criteria;
    }
	
	function getTotalPages(){
		return $this->total_pages;
	}
	
	function getTotalRecords(){
		return $this->total_records;
	}
	
    function getData($id,$add_criteria=array()) {
        $id = intval($id);
        if($id>0!=true) return array();
       	$add_criteria['id'] = $id;
		if (($add_criteria['is_owner']!=1)){
			$add_criteria = array_merge($add_criteria,(array)$this->criteria);
		}
		if (Settings::getSetting("CONF_ENABLE_SELLER_SUBSCRIPTION") && ($add_criteria['is_owner']!=1)){	
			$add_criteria['subscription'] = 1;
		}
        $srch = self::search($add_criteria);
		$srch->joinTable('tbl_url_alias', 'LEFT OUTER JOIN', 'ts.shop_id = REPLACE(tua.url_alias_query,"shops_id=","")', 'tua');
		$srch->addFld('tua.url_alias_keyword as seo_url_keyword');
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $sql = $srch->getQuery();
		
        $rs = $this->db->query($sql);
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function getShopsByCriteria($add_criteria,$pagesize) {
		if (($add_criteria['is_owner']!=1)){
			$add_criteria = array_merge($add_criteria,(array)$this->criteria);
		}
		if (Settings::getSetting("CONF_ENABLE_SELLER_SUBSCRIPTION") && ($add_criteria['is_owner']!=1)){	
			$add_criteria['subscription'] = 1;
		}
        $srch = self::search($add_criteria);
		//die($srch->getquery());
        if ((intval($pagesize)>0) || isset($add_criteria["pagesize"])){
			$srch->setPageSize(isset($add_criteria["pagesize"])?$add_criteria["pagesize"]:$pagesize);
		}else{
			$srch->doNotLimitRecords(true);
		}
		$srch->addOrder('promotion_resumption_date','desc');
		$srch->addOrder('shop_featured','desc');
		$srch->addOrder('shop_status','desc');
		//die($srch->getquery());
        $rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
        $row = ($pagesize==1)?$this->db->fetch($rs):$this->db->fetch_all($rs);
        if($row==false) return array();
        else return $row;
	}
	
	
	function getShopsCountByCriteria($add_criteria) {
		if (($add_criteria['is_owner']!=1)){
			$add_criteria = array_merge($add_criteria,(array)$this->criteria);
		}
		if (Settings::getSetting("CONF_ENABLE_SELLER_SUBSCRIPTION") && ($add_criteria['is_owner']!=true)){	
			$add_criteria['subscription'] = 1;
		}
        $srch = self::search($add_criteria);
        $rs = $srch->getResultSet();
		return $srch->recordCount();
	}
	
	
    
    function search($criteria, $count='') {
		//print_r($criteria);
		
		$pObj=new Products();
		$pObj->addCondition('tp.prod_status', '=', 1);
		$pObj->addCondition('tp.prod_is_expired', '=', 0);
		$pObj->addCondition('tp.prod_is_deleted', '=', 0);
		//$pObj->addCondition('tp.prod_type', '=', 1);
		
		$pObj->addMultipleFields(array('tp.prod_shop as product_shop',"count(prod_id) as totStoreProducts"));
		$pObj->addGroupBy('tp.prod_shop');
		$pObj->doNotCalculateRecords();
		$pObj->doNotLimitRecords();
		$qry_store_products = $pObj->getQuery();
		
		
		$srch = new SearchBase('tbl_prod_reviews', 'tpr');
		$srch->joinTable('tbl_products', 'INNER JOIN', 'tpr.review_prod_id=tp.prod_id and tp.prod_is_deleted=0 and tp.prod_is_expired=0 and tp.prod_status=1', 'tp');
		$srch->joinTable('tbl_users', 'INNER JOIN', 'tpr.review_user_id=tu.user_id', 'tu');
		//$srch->joinTable('tbl_order_products', 'INNER JOIN', 'tpr.review_order=`top`.opr_id', '`top`');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tp.prod_shop');
		$srch->addCondition('tpr.review_status', '=', 1);
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
		$srch = new SearchBase('tbl_promotions_clicks', 'tpc');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tpc.pclick_promotion_id');
		$srch->addMultipleFields(array(
			'tpc.pclick_promotion_id',
			"SUM(IF(`pclick_datetime`>CURRENT_DATE - INTERVAL 1 DAY,`pclick_cost`,0)) daily_cost,
   SUM(IF(`pclick_datetime`>CURRENT_DATE - INTERVAL 1 WEEK,`pclick_cost`,0)) weekly_cost,
   SUM(IF(`pclick_datetime`>CURRENT_DATE - INTERVAL 1 MONTH,`pclick_cost`,0)) monthly_cost",
			"SUM(pclick_cost) as total_cost"
		));
		$qry_promotion_clicks = $srch->getQuery();
		
		
		$srch = new SearchBase('tbl_user_transactions', 'txn');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('txn.utxn_user_id');
		$srch->addMultipleFields(array('txn.utxn_user_id',"SUM(utxn_credit-utxn_debit) as userBalance"));
		$qry_user_balance = $srch->getQuery();
		$srch=new SearchBase('tbl_promotions','tpr');
		$srch->joinTable('(' . $qry_promotion_clicks . ')', 'LEFT OUTER JOIN', 'tpr.promotion_id = tqpc.pclick_promotion_id', 'tqpc');
		$srch->joinTable('tbl_users', 'LEFT JOIN', 'tpr.promotion_user_id=tu.user_id', 'tu');
		$srch->joinTable('(' . $qry_user_balance . ')', 'LEFT OUTER JOIN', 'tu.user_id = tqub.utxn_user_id', 'tqub');
		$srch->addCondition('userBalance','>=',Settings::getSetting("CONF_MIN_WALLET_BALANCE"));
		$srch->addCondition('tpr.promotion_type','=',2);
		$srch->addCondition('tpr.promotion_is_deleted','=',0);
		$srch->addCondition('tpr.promotion_status','=',1);
		//$srch->addDirectCondition('(tpr.promotion_start_date = "0000-00-00" OR tpr.promotion_start_date < NOW()) AND (tpr.promotion_end_date = "0000-00-00" OR tpr.promotion_end_date > NOW()) AND CAST("'.date('H:i:s').'" AS time) BETWEEN `promotion_start_time` AND `promotion_end_time`'); 
		
		$srch->addDirectCondition('(tpr.promotion_start_date = "0000-00-00" OR tpr.promotion_start_date <= DATE(NOW())) AND (tpr.promotion_end_date = "0000-00-00" OR tpr.promotion_end_date >= DATE(NOW())) AND CAST("'.date('H:i:s').'" AS time) BETWEEN `promotion_start_time` AND `promotion_end_time`');
		
		$srch->addDirectCondition('CASE 
								WHEN promotion_budget_period="D" THEN promotion_budget > COALESCE(daily_cost,0)
								WHEN promotion_budget_period="W" THEN promotion_budget > COALESCE(weekly_cost,0)
								WHEN promotion_budget_period="M" THEN promotion_budget > COALESCE(monthly_cost,0)
							  END
					'); 
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tpr.promotion_shop_id');
		$srch->addMultipleFields(array('tpr.promotion_id',"tpr.promotion_shop_id",'tpr.promotion_cost','tpr.promotion_resumption_date'));
		$qry_promotion_shops = $srch->getQuery();
		//die($qry_promotion_shops);
		
        $srch = new SearchBase('tbl_shops', 'ts');
		//$srch->joinTable('tbl_url_alias', 'LEFT OUTER JOIN', 'ts.shop_id = REPLACE(tua.url_alias_query,"shops_id=","")', 'tua');
		$srch->joinTable('(' . $qry_store_products . ')', 'LEFT OUTER JOIN', 'ts.shop_id = tqsp.product_shop', 'tqsp');
		$srch->joinTable('(' . $qry_store_reviews . ')', 'LEFT OUTER JOIN', 'ts.shop_id = tqsr.prod_shop', 'tqsr');
		$srch->joinTable('(' . $qry_store_reports . ')', 'LEFT OUTER JOIN', 'ts.shop_id = tqsrp.sreport_shop', 'tqsrp');
		$srch->joinTable('tbl_users', 'INNER JOIN', 'ts.shop_user_id=tu.user_id and tu.user_is_deleted=0 and tu.user_status=1', 'tu');
		
		$srch->joinTable('tbl_states', 'LEFT JOIN', 'tu.user_state_county=tstat.state_id', 'tstat');
		$srch->joinTable('tbl_countries', 'LEFT JOIN', 'tu.user_country=tcon.country_id', 'tcon');
		
		$srch->joinTable('tbl_states', 'LEFT JOIN', 'ts.shop_state=tst.state_id and tst.state_delete=0', 'tst');
		$srch->joinTable('tbl_countries', 'LEFT JOIN', 'ts.shop_country=tco.country_id and tco.country_delete=0', 'tco');
		$srch->joinTable('(' . $qry_order_shop . ')', 'LEFT OUTER JOIN', 'ts.shop_id = tqos.opr_product_shop', 'tqos');
		$srch->joinTable('(' . $qry_promotion_shops . ')', 'LEFT JOIN', 'ts.shop_id = tqps.promotion_shop_id', 'tqps');
		$srch->addCondition('ts.shop_is_deleted', '=', 0);
        if($count==true) {
            $srch->addFld('COUNT(ts.shop_id) AS total_rows');
        } else {
            $srch->addMultipleFields(array('ts.*','tqos.*','COALESCE(tqos.totOrders,0) as totOrders','tu.user_name as shop_owner','tu.user_email as shop_owner_email','tu.user_username as shop_owner_username','tu.user_profile_image as shop_owner_profile_image','tst.state_name','tco.country_name','COALESCE(tqsp.totStoreProducts,0) as totProducts','COALESCE(round(tqsr.shop_rating,1),0) as shop_rating','COALESCE(tqsr.totReviews,0) as totReviews','COALESCE(tqsrp.totStoreReports,0) as totStoreReports','tstat.state_name as shop_owner_state_name','tcon.country_name as shop_owner_country_name','tqps.*'));
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
			case 'paypal_verified':
                $srch->addCondition('ts.shop_paypal_account_verified', '=','1');
                break;		
			case 'featured':
				if ($val==1){
	                $srch->addCondition('ts.shop_featured', '=', $val);
				}
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
                $srch->addCondition('ts.shop_name', 'like', '%'.$val.'%');
                break;
			case 'start':
                $srch->addCondition('ts.shop_name', 'like', $val.'%');
                break;	
			case 'must_products':
                $srch->addCondition('totStoreProducts', '>', 0);
                break;
			case 'subscription':
					$current_date = date('Y-m-d');
					$srchSubOrder = new SearchBase('tbl_subscription_merchant_package_orders', 'tmpo');
					$srchSubOrder->addCondition('mysql_func_date(tmpo.mporder_subscription_start_date)', '<=', $current_date,'AND', true);
					$srchSubOrder->addCondition('mysql_func_date(tmpo.mporder_subscription_end_date)', '>=', $current_date,'AND', true);
					$srchSubOrder->doNotCalculateRecords();
					$srchSubOrder->addGroupBy('mporder_user_id');
					$srchSubOrder->addMultipleFields( array('mporder_user_id', 'mporder_id', 'mporder_subscription_start_date', 'mporder_subscription_end_date','mporder_mpo_status_id') );
					$srchSubOrder->addCondition('mporder_mpo_status_id','=', Settings::getSetting("CONF_ACTIVE_SUBSCRIPTION_STATUS"));
					$qry_user_subscriptions = $srchSubOrder->getQuery();
					$srch->joinTable('(' . $qry_user_subscriptions . ')', 'INNER JOIN','ts.shop_user_id = tmpo.mporder_user_id', 'tmpo');
				break;	
			case 'favorite':
				if ($val>0){
					$srch->joinTable('tbl_user_favourite_shops', 'LEFT JOIN', 'ts.shop_id=tf.ufs_shop_id and tf.ufs_user_id='.$val, 'tf');
					$srch->addFld("IF(tf.ufs_shop_id>0,'1','0') as favorite");
				}
			break;
			case 'page':
				$srch->setPageNumber($val);
			break;	
			case 'pagesize':
				$srch->setPageSize($val);
			break;			
            
            }
        }
		/*print($srch->getquery());
		die();*/
        return $srch;
    }
	
	function addShopReport($data){
		$user_id = intval($data['user_id']);
		$shop_id = intval($data['shop_id']);
		unset($data['user_id']);
		unset($data['shop_id']);
		if(($user_id < 1) || ($shop_id < 1)){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		$record = new TableRecord('tbl_shop_reports');
		$assign_fields = array(
						'sreport_date'=>date('Y-m-d H:i:s'),
						'sreport_shop'=>$shop_id,
						'sreport_reported_by'=>$user_id,
						'sreport_reason'=>$data["sreport_reason"],
						'sreport_message'=>$data["sreport_message"],
					);
		
		$record->assignValues($assign_fields);
		if($record->addNew()){
			$shop_report_id=$record->getId();
			$emailObj=new Emailnotifications();
			if ($emailObj->SendShopReportNotification($shop_report_id)){
				return intval($shop_report_id);
			}else{
				$this->error=$emailObj->getError();
			}
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return false;
	}
	
	function getShopReport($shop_report_id){
		$shop_report_id = intval($shop_report_id);
		if($shop_report_id < 1){
			return false;
		}
		$srch = new SearchBase('tbl_shop_reports', 'tsr');
		$srch->joinTable('tbl_shops', 'INNER JOIN', 'tsr.sreport_shop = ts.shop_id', 'ts');
		$srch->joinTable('tbl_users', 'INNER JOIN', 'tsr.sreport_reported_by = tu.user_id', 'tu');
		$srch->addCondition('sreport_is_deleted', '=', 0);
		$srch->addCondition('sreport_id', '=', $shop_report_id);
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addMultipleFields(array('tsr.*', 'ts.shop_name','tu.user_username','tu.user_name'));
		$rs = $srch->getResultSet();
		return $this->db->fetch($rs);
	}
	
	
	
	
	
	function getAssociativeArray($not_to_include=null) {
        $db = &Syspage::getdb();
		$query = "SELECT shop_id, shop_name FROM tbl_shops WHERE shop_is_deleted='0' ";
        if (!empty($not_to_include)) $query .= " AND shop_id NOT IN ({$not_to_include})";
		$query .= " ORDER BY shop_is_default desc,shop_name";
        $rs = $db->query($query);
        return $db->fetch_all_assoc($rs);
    }
	
	
   	function updateUserShopInfoDetails($data){
		$shop_id = intval($data['shop_id']);
		$user_id = intval($data['shop_user_id']);
		if($user_id < 1) return false;
		$record = new TableRecord('tbl_shops');
		
		$values = array(
					'shop_user_id' => $data['shop_user_id'],
					'shop_name' => $data['shop_name'],
					'shop_title' => $data['shop_title'],
					'shop_slogan' => $data['shop_slogan'],
					'shop_description' => $data['shop_description'],
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
					'shop_vendor_display_status' => intval($data['shop_vendor_display_status']),
					'shop_status' =>intval($data['shop_status']),
					'shop_date' => date('Y-m-d H:i:s'),
					'shop_update_date' => date('Y-m-d H:i:s'),
					'shop_state' => intval($data['ua_state']),
					'shop_country' => intval($data['shop_country']),
					'shop_city' => $data['shop_city'],
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
		
		if(isset($data['shop_paypal_account_verified']) && strlen($data['shop_paypal_account_verified']) > 0){
			$values['shop_paypal_account_verified'] = $data['shop_paypal_account_verified'];
		}
		$record->assignValues($values);
		$sqlquery=$record->getinsertquery();
		$arr=$record->getFlds();
		unset($arr["shop_status"]);
		unset($arr["shop_date"]);
		foreach($arr as $field => $val) {
			  if (($field!="shop_update_date")){   	
	 			  $fields[] = "$field = ".$this->db->quoteVariable($val);
			  }else{
				   $fields[] = "$field = '".date('Y-m-d H:i:s')."'";
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
	
	function getPaypalAccountForMultipleShops($sids){
		if(!is_array($sids) || sizeof($sids) < 1){
			return array();
		}
		$sids = array_map('intval', $sids);
		$srch = new SearchBase('tbl_shops');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addCondition('shop_id', 'IN', $sids);
		$srch->addMultipleFields(array('shop_id', 'shop_payment_paypal_account'));
		$rs = $srch->getResultSet();
		$rows = $this->db->fetch_all_assoc($rs);
		if($rows){
			return $rows;
		}
		return array();
	}
	public function getPPCShops() {
		$PPC_shops = array();
		$limit = Settings::getSetting("CONF_PPC_SHOPS_HOME_PAGE");
		if(isset($limit) && $limit>0){
			$uObj= new User();
			$user_id = $uObj->getLoggedUserId();
			$prmObj=new Promotions();
			$arr = array("front"=>1,"type"=>2,"status"=>1,"pagesize"=>$limit+10,"order_by"=>'random');
			$promotion_shops=$prmObj->getPromotions($arr);
			foreach($promotion_shops as $skey=>$sval){
				$shop = $this->getData($sval["promotion_shop_id"],array("status"=>1,"favorite"=>$user_id));
				
				if ($shop && $shop["totProducts"]>0){
					$product= new Products();
					$product->joinWithPromotionsTable();
					$product->addSpecialPrice();
					$product->selectFields(array('tp.*','ts.shop_id','ts.shop_user_id','ts.shop_name','ts.shop_title','tu.user_id','tu.user_name','tu.user_username','tu.user_email','IF(prod_stock >0, "1", "0" ) as available'));
					$product->setPagesize(4);
					
					$shop_products=$product->getProducts(array("shop"=>$shop["shop_id"]));
					$arr_shop_prods=array("products"=>$shop_products);
					$PPC_shops[]=array_merge($shop,$arr_shop_prods);
				}
			}
		}
		
		return $PPC_shops;
	}
	
	
	public function getShopAddress($shop_id) {
        $srch = new SearchBase('tbl_shops', 'ts');
        $srch->joinTable('tbl_states', 'LEFT JOIN', 'ts.shop_state=tst.state_id and tst.state_delete=0', 'tst');
        $srch->joinTable('tbl_countries', 'LEFT JOIN', 'ts.shop_country=tco.country_id and tco.country_delete=0', 'tco');
       // $srch->joinTable('tbl_user_avatax_info', 'LEFT JOIN', 'ts.shop_user_id=uavatax_user_id', 'suser');
        $srch->addCondition('ts.shop_is_deleted', '=', 0);
        $srch->addCondition('ts.shop_id', '=', $shop_id);
        $rs = $srch->getResultSet();
        return $this->db->fetch($rs);
    }	
}