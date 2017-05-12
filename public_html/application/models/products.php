<?php
class Products extends SearchBase {
	public $instance;
	var $page = 1;
	var $pagesize = 30;
	var $countRecords = true;
	var $limitRecords = true; 
	function __construct($check_status=true,$is_owner_call=false){
		$this->db = Syspage::getdb();
		parent::__construct('tbl_products', 'tp');
		$this->joinTable('tbl_shops', 'LEFT JOIN', 'tp.prod_shop=ts.shop_id', 'ts');
		$this->joinTable('tbl_users', 'INNER JOIN', 'ts.shop_user_id=tu.user_id', 'tu');
		$this->addCondition('tp.prod_is_deleted','=',0);
		$this->addCondition('tp.prod_is_expired', '=', 0,'AND');
		$this->addCondition('ts.shop_is_deleted', '=', 0,'AND');
		$this->addCondition('ts.shop_status', '=', 1,'AND');
		$this->addCondition('ts.shop_vendor_display_status', '=', 1,'AND');
		$this->addCondition('tu.user_status', '=', 1,'AND');
		$this->addCondition('tu.user_is_deleted', '=', 0,'AND');
		$this->addCondition('tu.user_email_verified', '=', 1,'AND');
		if ($check_status){
			$this->addCondition('tp.prod_status', '=', 1);
			
			/* subscriptions*/
				
				//die($qry_user_subscriptions);
				
				
		}
		
		$criteria = array();
		if (!Settings::getSetting("CONF_ALLOW_USED_PRODUCTS_LISTING") && ($is_owner_call!=true)){
			$criteria['condition'] = "N";
		}
		if (Settings::getSetting("CONF_ENABLE_SELLER_SUBSCRIPTION") && ($is_owner_call!=true)){	
			$criteria['subscription'] = 1;
		}
		$pmObj=new Paymentmethods();
		$payment_method=$pmObj->getPaymentMethodByCode(CONF_PAPYAL_ADAPTIVE_KEY);
		if ($payment_method && $payment_method['pmethod_status']){
			$criteria['paypal_verified'] = true;
		}
		$this->criteria = $criteria;
		
    }
	
		
	public function joinWithURLAliasTable(){
		$this->joinTable('tbl_url_alias', 'LEFT OUTER JOIN', 'tp.prod_id = REPLACE(tua.url_alias_query,"products_id=","")', 'tua');
	}
		
	public function joinWithDetailTable(){
		$this->joinTable('tbl_prod_details', 'LEFT JOIN', 'tp.prod_id=tpd.prod_id', 'tpd');
	}
	
	public function joinWithBrandsTable(){
		$this->joinTable('tbl_product_brands', 'LEFT JOIN', 'tp.prod_brand=tpb.brand_id', 'tpb');
	}
	
	public function joinWithCategoryTable(){
		$this->joinTable('tbl_categories', 'LEFT JOIN', 'tp.prod_category=tc.category_id', 'tc');
	}
	
	public function joinWithProductTags(){
		$srchTag = new parent('tbl_product_to_tags', 'tptags');
		$srchTag->joinTable('tbl_product_tags', 'LEFT JOIN', 'tptags.pt_tag_id=ptags.ptag_id', 'ptags');
		$srchTag->doNotCalculateRecords();
		$srchTag->doNotLimitRecords();
		$srchTag->addGroupBy('tptags.pt_product_id');
		$srchTag->addMultipleFields(array('tptags.pt_product_id','GROUP_CONCAT(ptags.`ptag_name`) as product_tags'));
		$qry_tag_products = $srchTag->getQuery();
							
		$this->joinTable('(' . $qry_tag_products . ')', 'LEFT OUTER JOIN', 'tp.prod_id = tqtp.pt_product_id', 'tqtp');
							
	}
	
	public function setSortBy($sort_by){
		$this->sort_by = $sort_by;
	}
	
	public function joinWithPromotionsTable(){
		$srch = new parent('tbl_user_transactions', 'txn');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('txn.utxn_user_id');
		$srch->addMultipleFields(array('txn.utxn_user_id',"SUM(utxn_credit-utxn_debit) as userBalance"));
		$qry_user_balance = $srch->getQuery();
		
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
		
		
		$srch=new parent('tbl_promotions','tprom');
		$srch->joinTable('(' . $qry_promotion_clicks . ')', 'LEFT OUTER JOIN', 'tprom.promotion_id = tqpc.pclick_promotion_id', 'tqpc');
		
		$srch->joinTable('tbl_users', 'LEFT JOIN', 'tprom.promotion_user_id=tu.user_id', 'tu');
		$srch->joinTable('(' . $qry_user_balance . ')', 'LEFT OUTER JOIN', 'tu.user_id = tqub.utxn_user_id', 'tqub');
		$srch->addCondition('tprom.promotion_type','=',1);
		$srch->addCondition('tprom.promotion_is_deleted','=',0);
		$srch->addCondition('tprom.promotion_status','=',1);
		$srch->addCondition('tprom.promotion_is_approved','=',1);
		$srch->addCondition('userBalance','>=',Settings::getSetting("CONF_MIN_WALLET_BALANCE"));
		$srch->addDirectCondition('(tprom.promotion_start_date = "0000-00-00" OR tprom.promotion_start_date <= DATE(NOW())) AND (tprom.promotion_end_date = "0000-00-00" OR tprom.promotion_end_date >= DATE(NOW())) AND CAST("'.date('H:i:s').'" AS time) BETWEEN `promotion_start_time` AND `promotion_end_time`'); 
		$srch->addDirectCondition('CASE 
								WHEN promotion_budget_period="D" THEN promotion_budget > COALESCE(daily_cost,0)
								WHEN promotion_budget_period="W" THEN promotion_budget > COALESCE(weekly_cost,0)
								WHEN promotion_budget_period="M" THEN promotion_budget > COALESCE(monthly_cost,0)
							  END
					');
					
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tprom.promotion_product_id');
		$srch->addMultipleFields(array('tprom.promotion_id',"tprom.promotion_product_id",'tprom.promotion_cost','tprom.promotion_resumption_date'));
		$qry_promotion_products = $srch->getQuery();
		$this->joinTable('(' . $qry_promotion_products . ')', 'LEFT JOIN', 'tp.prod_id = tqprom.promotion_product_id', 'tqprom');
		$this->addFld('promotion_id');
		
		
	}
	
	public function addSpecialPrice(){
		$current_date = date('Y-m-d');
		$this->addFld("(SELECT pspecial_price  FROM tbl_product_specials tps WHERE tps.pspecial_product_id = tp.prod_id AND ((tps.pspecial_start_date = '0000-00-00' OR tps.pspecial_start_date <= '".$current_date."') AND (tps.pspecial_end_date = '0000-00-00' OR tps.pspecial_end_date >= '".$current_date."')) ORDER BY tps.pspecial_priority ASC, tps.pspecial_price ASC LIMIT 1) AS special");
		$this->addFld("(SELECT pdiscount_price FROM tbl_product_discounts tpd WHERE tpd.pdiscount_product_id = tp.prod_id AND tpd.pdiscount_qty = '1' AND ((tpd.pdiscount_start_date = '0000-00-00' OR tpd.pdiscount_start_date <= '".$current_date."') AND (tpd.pdiscount_end_date = '0000-00-00' OR tpd.pdiscount_end_date >= '".$current_date."')) ORDER BY tpd.pdiscount_priority ASC, tpd.pdiscount_price ASC LIMIT 1) AS discount");
	}
	
	public function joinWithReviewsTable(){
		$srch = new parent('tbl_prod_reviews', 'tpr');
		$srch->joinTable('tbl_users', 'INNER JOIN', 'tpr.review_user_id=tu.user_id and tu.user_is_deleted=0', 'tu');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tpr.review_prod_id');
		$srch->addCondition('tpr.review_status', '=', 1);
		$srch->addCondition('tpr.review_is_deleted', '=', 0);
		$srch->addMultipleFields(array('tpr.review_prod_id',"ROUND(AVG(review_rating),1) as prod_rating","count(review_id) as totReviews"));
		$qry_prod_reviews = $srch->getQuery();
		
		$this->joinTable('(' . $qry_prod_reviews . ')', 'LEFT OUTER JOIN', 'tp.prod_id = tqpr.review_prod_id', 'tqpr');
		$this->addFld("tqpr.prod_rating,tqpr.totReviews");
	}
	
	public function selectFields($select_columns){
		$this->addMultipleFields((array)$select_columns);
	}
	
	function setCountRecords($countRecords=true) {
        return $this->countRecords=$countRecords;
    }
	
	function setLimitRecords($limitRecords=true) {
        return $this->limitRecords=$limitRecords;
    }
	
	function setPageSize($pagesize=30) {
        return $this->pagesize=$pagesize;
    }
	
	function setPageNumber($page=1) {
        return $this->page=$page;
    }
	
	function getInstance() {
        return $this;
    }
	
	function getProdId() {
        return $this->product_id;
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
	
	public function applyConditions($conditions=array()){
		$conditions = array_merge($conditions,(array)$this->criteria);
		foreach($conditions as $key=>$val) {
			if(strval($val)=='') continue;
            	switch($key) {
	            case 'id':
    	            $this->addCondition('tp.prod_id', '=', intval($val));
        	    break;
				case 'id_arr':
					if (!empty($val)){
						$this->addCondition('tp.prod_id', 'IN', (array)$val);
					}
					break;
				case 'type':
                	$this->addCondition('tp.prod_type', '=', intval($val));
                break;
				case 'name':
    	            $this->addCondition('tp.prod_name', 'LIKE', '%'.$val.'%');
        	    break;		
				case 'shop':
                	$this->addCondition('tp.prod_shop', '=', intval($val));
                break;
				case 'added_by':
					$this->addCondition('tp.prod_added_by', '=',$val);
                break;
				case 'available_date':
					$this->addDirectCondition('tpd.prod_available_date <= NOW()');
				break;
				case 'filters':
					$this->joinTable('tbl_product_filter', 'LEFT JOIN', 'tp.prod_id=tpf.product_id', 'tpf');
					if (is_array($val))
						$this->addCondition('tpf.filter_id', 'IN', $val);
					else	
						$this->addCondition('tpf.filter_id', '=', intval($val));
					break;		
			    case 'brand':
				 	if (is_array($val))
	            	    $this->addCondition('tp.prod_brand', 'IN', $val);
					else	
						$this->addCondition('tp.prod_brand', '=', intval($val));
            	break;
				case 'tags':
					$this->joinTable('tbl_product_to_tags', 'LEFT JOIN', 'tp.prod_id=tpt.pt_product_id', 'tpt');
					if (is_array($val) && !empty($val))
						$this->addCondition('tpt.pt_tag_id', 'IN', (array)$val);
					elseif (is_numeric($val))	
						$this->addCondition('tpt.pt_tag_id', '=', intval($val));
				break;
				case 'condition':
					$this->addCondition('tp.prod_condition', 'IN', (array)$val);
				break;
				case 'paypal_verified':
					$this->addCondition('ts.shop_paypal_account_verified', '=','1');
				break;	
				case 'sku':
                	$this->addCondition('tp.prod_sku', '=',$val);
                break;
				case 'property':
					if (!is_array($val)){
						if (in_array($val,array('prod_featuered','prod_ship_free'))){
							$this->addCondition('tpd.'.$val, '=', 1);
						}
					}else{
						foreach($val as $skey=>$sval){
							$this->addCondition('tpd.'.$sval, '=', 1);
						}
					}
				break;
				case 'category':
					if ((int) $val > 0){
						$category_id = (int)$val;
						$cats=array_merge(array($category_id),array_keys(Categories::getProdClassifiedCategoriesAssocArray($category_id)));
						$this->addCondition('tp.prod_category', 'IN',$cats);
					}	
				break;
				case 'price_range':
					$arr_price_range=(array)$val;
					$str = "(";
						foreach($arr_price_range as $key=>$val):
							$cnt++;
							$arr_min_max_price=explode("-",$val);
							$min=$arr_min_max_price[0];
							$max=$arr_min_max_price[1];
							$condition=$cnt==count($arr_price_range)?"":"OR";
							//$str .= " Having ((IFNULL(`special`,`prod_sale_price`)) >= $min AND (IFNULL(`special`,`prod_sale_price`)) <= $max) $condition ";
							$str .= " Having ((((CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE prod_sale_price END))) >= $min AND (CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE prod_sale_price END) <= $max) $condition ";
						endforeach;
						$str .= ")";
						$this->addDirectCondition($str);	
				break;
				case 'active':
					if ($val!="")
	            	    $this->addCondition('tp.prod_status', '=', $val);
             	break;
				case 'out_of_stock':
					if ($val==1)
						$this->addCondition('tp.prod_stock', '>',0);
				break;
				case 'active':
						$cnd=$this->addCondition('tp.prod_status', '=',1);
						$cnd->attachCondition('tp.prod_is_expired', '=',0,'AND');
				break;
				case 'expired':
						$cnd=$this->addCondition('tp.prod_is_expired', '=',1);
				break;
				case 'pending':
						$cnd=$this->addCondition('tp.prod_status', '=',0);
						$cnd->attachCondition('tp.prod_is_expired', '=',0,'AND');
				break;
				case 'minprice':
				case 'min':
					$cnd=$this->addHaving("IFNULL(`special`,`prod_sale_price`)",">=",$val);
					break;
				case 'maxprice':
				case 'max':
					$cnd=$this->addHaving("IFNULL(`special`,`prod_sale_price`)"," <= ",$val);
					break;
				case 'date_from':
					$this->addCondition('tp.prod_published_on', '>=', $val. ' 00:00:00');
					break;
				case 'date_to':
					$this->addCondition('tp.prod_published_on', '<=', $val. ' 23:59:59');
					break;
				case 'favorite':
					$this->joinTable('tbl_user_favourite_products', 'LEFT JOIN', 'tp.prod_id=tf.ufp_prod_id and tf.ufp_user_id='.(int)$val, 'tf');
					$this->addFld("IF(tf.ufp_prod_id>0,'1','0') as favorite");
				break;
				case 'subscription':
					$current_date = date('Y-m-d');
					$srchSubOrder = new parent('tbl_subscription_merchant_package_orders', 'tmpo');
					$srchSubOrder->addCondition('mysql_func_date(tmpo.mporder_subscription_start_date)', '<=', $current_date,'AND', true);
					$srchSubOrder->addCondition('mysql_func_date(tmpo.mporder_subscription_end_date)', '>=', $current_date,'AND', true);
					$srchSubOrder->doNotCalculateRecords();
					$srchSubOrder->addGroupBy('mporder_user_id');
	
					$srchSubOrder->addMultipleFields( array('mporder_user_id', 'mporder_id', 'mporder_subscription_start_date', 'mporder_subscription_end_date','mporder_mpo_status_id') );
					$srchSubOrder->addCondition('mporder_mpo_status_id','=', Settings::getSetting("CONF_ACTIVE_SUBSCRIPTION_STATUS"));
					$qry_user_subscriptions = $srchSubOrder->getQuery();
					$this->joinTable('(' . $qry_user_subscriptions . ')', 'INNER JOIN','tp.prod_added_by = tmpo.mporder_user_id', 'tmpo');
					//$this->addCondition('tmpo.mporder_user_id','!=','NULL');
				break;
				case 'keyword':
				if ($val!=""){
						$this->joinWithProductTags();
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
									'tc.category_name',
									);
						$strKeys = "";
						foreach($arr_columns as $column){
							foreach($arr_keywords as $keyword){
								$strKeys.=(($strKeys=="")?"":" + ")." (case when INSTR(".$column.",".$this->db->quoteVariable(trim($keyword)).")>0 then 1 else 0 END) ";
							}
						}
						$this->addDirectCondition($strKeys);
						$this->addFld($strKeys.' as Rank');
				}	
                break;
				
				
            }
        }
		
	}
	
	public function addSortByOnRecords(){
			
			$sort = $this->sort_by;
			if (!empty($sort)) {
			$val = in_array($sort,array('dlth','dhtl','rece','feat','best','plth','phtl','rate','relv','rand'))?$sort:"best";
			$this->addOrder('available','desc');
			$this->addOrder('promotion_resumption_date','desc');
			switch($val) {
				case 'dlth':
					$this->addOrder('tp.prod_display_order','asc');
					$this->addOrder('prod_id','desc');
				break;
				case 'dhtl':
					$this->addOrder('tp.prod_display_order','desc');
					$this->addOrder('prod_id','desc');
				break;
				case 'rece':
					$this->addOrder('tp.prod_id','desc');
				break;
				case 'feat':
					$this->addOrder('tpd.prod_featuered','desc');
				break;
				case 'best':
					$this->addOrder('tp.prod_sold_count','desc');
				break;
				case 'plth':
					$this->addOrder('CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE prod_sale_price END','asc');
				break;
				case 'phtl':
					$this->addOrder('CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE prod_sale_price END','desc');
				break;
				case 'rate':
					$this->addOrder('tqpr.prod_rating','desc');
				break;
				case 'relv':
					if (strpos(implode(',',$this->flds), 'Rank') !== false) {
						$this->addOrder('Rank','desc');
					}
				break;
				case 'rand':
					$this->addOrder('rand()');
				break;
			}
		}
	}
	
	/*public function applyConditions($obj,$conditions=array()){
		$cloneObj = clone($obj);
		foreach($conditions as $key=>$val) {
			if(strval($val)=='') continue;
            	switch($key) {
	            case 'id':
    	            $cloneObj->addCondition('tp.prod_id', '=', intval($val));
        	    break;
				case 'brand':
				 	if (is_array($val))
	            	    $cloneObj->addCondition('tp.prod_brand', 'IN', $val);
					else	
						$cloneObj->addCondition('tp.prod_brand', '=', intval($val));
            	break;
				
            }
        }
		return $cloneObj;
	}*/
	
	
	function getData($id,$criteria=array()) {
        $id = intval($id);
        if($id>0!=true) return array();
	       	$criteria['id'] = $id;
		$this->applyConditions($criteria);
        $this->doNotLimitRecords(true);
        $this->doNotCalculateRecords(true);
		//die($this->getquery());
        $rs = $this->getResultSet();
        return $this->db->fetch($rs);
	}
	
	function getProducts($conditions=array()) {
		$this->applyConditions($conditions);
		if (!$this->countRecords)
		$this->doNotCalculateRecords();
		if (!$this->limitRecords)
		$this->doNotLimitRecords();
		$this->setPageNumber($this->page);
		$this->setPageSize($this->pagesize);
		if (!empty($conditions['sort'])){
			$this->setSortBy($conditions['sort']);
		}
		if (!empty($conditions['pagesize'])){
			$this->setPageSize($conditions['pagesize']);
		}
		$this->addSortByOnRecords();
		//echo($this->getquery()."<br/><br/>");
		/*echo($this->getquery()."<br/><br/>");
		die();*/
		$rs = $this->getResultSet();
		$this->total_records = $this->recordCount();
		$this->total_pages = $this->pages();
		return $this->db->fetch_all($rs);
	}
	
	public function getFeaturedProducts() {
		$featured_products = array();
		$limit = Settings::getSetting("CONF_FEATURED_ITEMS_HOME_PAGE");
		if(isset($limit) && $limit>0){
			$uObj= new User();
			$user_id = $uObj->getLoggedUserId();
			$this->applyConditions(array("property"=>"prod_featuered","favorite"=>$user_id));
			$this->setPageSize($limit);
			$this->doNotCalculateRecords();
			$rs = $this->getResultSet();
			$featured_products = $this->db->fetch_all($rs);
		}
		return $featured_products;
	}
	
	function getRecentlyViewedProducts($user_id=0){
		$recently_viewed_products = array();
		if(isset($_COOKIE['ProductVisitorCookie'])){
			$product_ids_arr_encrpted = explode("~",$_COOKIE['ProductVisitorCookie']);
			$product_ids = array_map("base64_decode",$product_ids_arr_encrpted);
			$this->applyConditions(array("id_arr"=>$product_ids,"favorite"=>$user_id));
			$this->setPageSize(25);
			$rs = $this->getResultSet();
			$recently_viewed_products = $this->db->fetch_all($rs);
		}
		return $recently_viewed_products;
	}
	
	function getProductsCount($conditions=array()) {
		$this->addSpecialPrice();
		$this->joinWithDetailTable();
		$this->selectFields(array('prod_sale_price'));
		$this->applyConditions((array)$conditions);
		$rs = $this->getResultSet();
		return $this->recordCount();
	}
	
	function getProductsMinMaxPrice($conditions=array()) {
		$this->addSpecialPrice();
		$this->applyConditions((array)$conditions);
		$this->selectFields(array('prod_sale_price'));
		$this->doNotCalculateRecords(true);
		$this->doNotLimitRecords();
		$srch_qry_min_max = $this->getquery();
		$sCustomObj=new SearchBase('(' . $srch_qry_min_max . ')', 'CQ');
		$sCustomObj->addMultipleFields(array('FLOOR(MIN(CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE prod_sale_price END)) as min_price','CEIL(MAX(CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE prod_sale_price END)) as max_price'));
		$sCustomObj->doNotCalculateRecords(true);
		$rs = $sCustomObj->getResultSet();
		return $this->db->fetch($rs);
	}
	
	function product_additional_info($arr){
		$arr['prod_url']=Utilities::generateUrl('products','view',array($arr["prod_id"]));
		$arr['prod_image_url']=Utilities::generateUrl('image','product_image',array($arr["prod_id"],'MEDIUM'));
		$arr['prod_shop_url']=Utilities::generateUrl('shops','view',array($arr["prod_shop"]));
		$arr['prod_list_url']=Utilities::generateUrl('common', 'view_lists',array($arr["prod_id"]));
		$arr['prod_out_of_stock']=$arr['prod_stock']>0?false:true;
		$arr['prod_promotion_id']=$arr['promotion_id'];
		$arr['prod_special']=Utilities::displayMoneyFormat($arr['special']);
		/*$price = $arr['discount']?$arr['discount']:$arr['prod_sale_price'];
		$arr['prod_price']=Utilities::displayMoneyFormat($price);*/
		$arr['prod_price']=Utilities::displayMoneyFormat($arr['prod_sale_price']);
		$arr['prod_favorite']=$arr["favorite"]?1:"";
		$arr['prod_short_name']=subStringByWords($arr["prod_name"],66);
		$arr['prod_cod']=$arr["prod_enable_cod_orders"]?1:"";
		return $arr;
		  //return($v*$v);
	}
	
	function getProductsSearchedBrands($conditions,$extended_conditions){
		$this->joinWithBrandsTable();
		$this->addSpecialPrice();
		$this->selectFields(array('tpb.brand_id','tpb.brand_name','count(*) as productsCount','tp.prod_sale_price'));
		$this->doNotCalculateRecords();
		$this->doNotLimitRecords();
		$this->addCondition('tpb.brand_status', '=',1);
		$this->addGroupBy('prod_brand');
		$exObj = clone $this;
		$this->applyConditions((array)$conditions);
		$sql_query_a = $this->getquery();
		$exObj->applyConditions((array)$extended_conditions);
		$sql_query_b = $exObj->getquery();
		
		$sCombObj=new SearchBase('(' . $sql_query_a . ')', 'SA');
		$sCombObj->joinTable('(' . $sql_query_b . ')', 'LEFT JOIN', 'SA.brand_id = SB.brand_id', 'SB');
		$sCombObj->addMultipleFields(array("SA.*,If(SB.productsCount is not null, '', 1) as is_disabled"));
		$sCombObj->addOrder('brand_name');
		//die($sCombObj->getquery());
		$rs = $sCombObj->getResultSet();
		$arr_brands=$this->db->fetch_all($rs);
		return $arr_brands;
		
	}
	
	function getProductsSearchedCategories($conditions){
		$this->joinWithCategoryTable();
		$this->applyConditions((array)$conditions);
		$this->selectFields(array('tc.category_id','tc.category_name','count(*) as productsCount','tp.prod_sale_price'));
		$this->doNotCalculateRecords();
		$this->doNotLimitRecords();
		$this->addCondition('tc.category_status', '=',1);
		$this->addGroupBy('tc.category_id');
		$this->addOrder('category_name');
		$rs = $this->getResultSet();
		$arr_brands=$this->db->fetch_all($rs);
		return $arr_brands;
		
	}
	
	public function getPPCProducts() {
		$PPC_products = array();
		$limit = Settings::getSetting("CONF_PPC_PRODUCTS_HOME_PAGE");
		if(isset($limit) && $limit>0){
			$uObj= new User();
			$user_id = $uObj->getLoggedUserId();
			$this->joinWithPromotionsTable();
			$this->addSpecialPrice();
			$this->applyConditions(array("favorite"=>$user_id));
			$this->selectFields(array('tp.*','ts.shop_id','ts.shop_user_id','ts.shop_name','ts.shop_title','tu.user_id','tu.user_name','tu.user_username','tu.user_email','IF(prod_stock >0, "1", "0" ) as available'));
			$this->doNotCalculateRecords();
			$this->doNotLimitRecords();
			$srch_prom_qry = $this->getQuery();
			
			$srch = new parent('tbl_promotions', 'tp');
			$srch->addGroupBy('tp.promotion_product_id');
			$srch->joinTable('(' . $srch_prom_qry . ')', 'INNER JOIN', 'tp.promotion_product_id = p.prod_id AND tp.promotion_id=p.promotion_id', 'p');
			
			$srch->addCondition('promotion_type', '=', 1);
			$srch->addCondition('promotion_status', '=', 1);
			$srch->addCondition('promotion_is_approved', '=', 1);
			$srch->setPagesize($limit);
			$srch->addOrder('rand()');
			//die($srch->getquery());
			$rs = $srch->getResultSet();
			$PPC_products=$this->db->fetch_all($rs);
		}
		return $PPC_products;
	}
	
	function getProductSpecials($product_id,$date_conditions=false) {
		$srch=new SearchBase('tbl_product_specials', 'tps');
		$srch->addCondition('pspecial_product_id', '=',$product_id);
		if ($date_conditions){
			$cnd = $srch->addCondition('pspecial_start_date', '=','0000-00-00');
			$cnd->attachCondition('pspecial_start_date', '<','mysql_func_now()','OR',true);
			$cnd = $srch->addCondition('pspecial_end_date', '=','0000-00-00');
			$cnd->attachCondition('pspecial_end_date', '>','mysql_func_now()','OR',true);
		}
		$srch->addOrder('pspecial_priority');
		$srch->addOrder('pspecial_price','ASC');
		$rs = $srch->getResultSet();
		return $this->db->fetch_all($rs);
		
	}
	
	function getProductQtyDiscounts($product_id) {
		$current_date = date('Y-m-d');
		$srch=new SearchBase('tbl_product_discounts', 'tpd');
		$srch->addCondition('pdiscount_product_id', '=',$product_id);
		$cnd = $srch->addCondition('pdiscount_start_date', '=','0000-00-00');
		$cnd->attachCondition('pdiscount_start_date', '<=',$current_date,'OR',true);
		$cnd = $srch->addCondition('pdiscount_end_date', '=','0000-00-00');
		$cnd->attachCondition('pdiscount_end_date', '>=',$current_date,'OR',true);
		$srch->addOrder('pdiscount_priority');
		$srch->addOrder('pdiscount_price','ASC');
		$rs = $srch->getResultSet();
		return $this->db->fetch($rs);
		
	}
	
	function addUpdateProduct($data){
		
		
			$product_id = intval($data['prod_id']);
			
			$record = new TableRecord('tbl_products');
			$assign_fields = array();
			$assign_fields['prod_added_by'] = intval($data['prod_added_by']);
			$assign_fields['prod_category'] = intval($data['prod_category']);
			$assign_fields['prod_type'] = intval($data['prod_type']);
			$assign_fields['prod_sku'] = $data['prod_sku'];
			$assign_fields['prod_name'] = $data['prod_name'];
			$assign_fields['prod_slug'] = $data['prod_slug'];
			$assign_fields['prod_brand'] = intval($data['prod_brand']);
			$assign_fields['prod_model'] = $data['prod_model'];
			$assign_fields['prod_shop'] = intval($data['prod_shop']);
			$assign_fields['prod_retail_price'] = $data['prod_retail_price'];
			$assign_fields['prod_sale_price'] = $data['prod_sale_price'];
			$assign_fields['prod_shipping'] = $data['prod_shipping'];
			$assign_fields['prod_stock'] = intval($data['prod_stock']);
			$assign_fields['prod_shipping_country'] = intval($data['prod_shipping_country']);
			$assign_fields['prod_min_order_qty'] = intval($data['prod_min_order_qty']);
			$assign_fields['prod_subtract_stock'] = intval($data['prod_subtract_stock']);
			$assign_fields['prod_requires_shipping'] = intval($data['prod_requires_shipping']);
			$assign_fields['prod_track_inventory'] = intval($data['prod_track_inventory']);
			$assign_fields['prod_threshold_stock_level'] = intval($data['prod_threshold_stock_level']);
			$assign_fields['prod_group'] = intval($data['prod_group']);
			$assign_fields['prod_markup'] = intval($data['prod_markup']);
			$assign_fields['prod_markup_type'] = $data['prod_markup_type'];
			$assign_fields['prod_condition'] = Settings::getSetting("CONF_ALLOW_USED_PRODUCTS_LISTING")?$data['prod_condition']:"N";
			$assign_fields['prod_display_order'] = intval($data['prod_display_order']);
			$assign_fields['prod_enable_cod_orders'] = intval($data['prod_enable_cod_orders']);
			if(isset($data['prod_image']) && $data['prod_image'] != ''){
				$assign_fields['prod_image'] = $data['prod_image'];
			}
			if($product_id === 0){
				$assign_fields['prod_added_on'] = date('Y-m-d H:i:s');
				$assign_fields['prod_published_on'] = date('Y-m-d H:i:s');
				$assign_fields['prod_status'] = $data['prod_status'];
			}if($product_id > 0 && isset($data['prod_status'])){
				$assign_fields['prod_status'] = intval($data['prod_status']);
			}
			$record->assignValues($assign_fields);
			if($product_id === 0 && $record->addNew()){
				$this->product_id=$record->getId();
			}elseif($product_id > 0 && $record->update(array('smt'=>'prod_id=?', 'vals'=>array($product_id)))){
				$this->product_id=$product_id;
			}else{
				$this->error = $this->db->getError();
				return false;
			}
			
			$data["prod_id"]=$this->getProdId();
			$accept_keys = array(
							'prod_id',
							'prod_length',
							'prod_width',
							'prod_height',
							'prod_weight',
							'prod_length_class',
							'prod_weight_class',
							//'prod_tags',
							'prod_youtube_video',
							'prod_long_desc',
							'prod_meta_title',
							'prod_meta_keywords',
							'prod_meta_description',
							'prod_featuered',
							'prod_ship_free',
							'prod_tax_free',
							'prod_available_date',
							);
			foreach($data as $key => $val){
				if(in_array($key, $accept_keys, true)){
					$fields[] = "$key = ".$this->db->quoteVariable($val)."";
					$info[$key]=$val;
			  }
			}
			$record = new TableRecord('tbl_prod_details');
			$record->assignValues($info);
			$sqlquery=$record->getinsertquery();
			$sqlquery = $sqlquery." on duplicate KEY UPDATE " . join(', ', $fields);
			//die($sqlquery);
			if(!$this->db->query($sqlquery)){
				$this->error = $this->db->getError();
				return false;
			}
			
			if (!$this->db->deleteRecords('tbl_url_alias', array('smt' => 'url_alias_query = ?', 'vals' => array('products_id='.$this->getProdId())))){
				$this->error = $this->db->getError();
				return false;
			}
	
			if (!empty($data['seo_url_keyword'])) {
				if(!$this->db->insert_from_array('tbl_url_alias', array('url_alias_query'=>'products_id='.$this->getProdId(),'url_alias_keyword'=>$data['seo_url_keyword']))){
					$this->error = $this->db->getError();
					return false;
				}
			}
			
			if (!$this->db->update_from_array('tbl_product_images', array('image_prod_id' => $this->getProdId()),
				array('smt' => 'image_prod_id = ? and image_session = ?', 'vals' => array(0,session_id())))){
					$this->error = $this->db->getError();
				   	return false;
			}
			
			if (!$this->db->deleteRecords('tbl_product_to_tags', array('smt' => 'pt_product_id = ?', 'vals' => array($this->getProdId())))){
				$this->error = $this->db->getError();
				return false;
			}
			if (isset($data['product_tag'])) {
				foreach ($data['product_tag'] as $tag_id) {
					if (!$this->db->query("INSERT INTO tbl_product_to_tags SET pt_product_id = '" . (int)$this->getProdId() . "', pt_tag_id = '" . (int)$tag_id . "'"))	{
					$this->error = $this->db->getError();
					return false;
					}
				}
			}
			
			if (!$this->db->deleteRecords('tbl_product_filter', array('smt' => 'product_id = ?', 'vals' => array($this->getProdId())))){
				$this->error = $this->db->getError();
				return false;
			}
			if (isset($data['product_filter'])) {
				foreach ($data['product_filter'] as $filter_id) {
					if (!$this->db->query("INSERT INTO tbl_product_filter SET product_id = '" . (int)$this->getProdId() . "', filter_id = '" . (int)$filter_id . "'"))	{
					$this->error = $this->db->getError();
					return false;
					}
				}
			}
			
			if(!$this->db->deleteRecords('tbl_product_relations', array('smt' => 'relation_source_id = ?', 'vals' => array($this->getProdId())))){
				$this->error = $this->db->getError();
				return false;
			}
			
			if (isset($data['product_related'])) {
				foreach ($data['product_related'] as $relation_product_id) {
					if(!$this->db->query("INSERT INTO tbl_product_relations SET relation_source_id = '" . (int)$this->getProdId() . "', relation_to_id = '" . (int)$relation_product_id . "'")){
						$this->error = $this->db->getError();
						return false;
					}
				}
			}
			
			if(!$this->db->deleteRecords('tbl_product_addons', array('smt' => 'addon_source_id = ?', 'vals' => array($this->getProdId())))){
				$this->error = $this->db->getError();
				return false;
			}
			
			if (isset($data['product_addon'])) {
				$addon_arr = array_slice($data['product_addon'],0,Settings::getSetting("CONF_MAX_NUMBER_PRODUCT_ADDONS"));
				foreach ($addon_arr as $addon_product_id) {
					if(!$this->db->query("INSERT INTO tbl_product_addons SET addon_source_id = '" . (int)$this->getProdId() . "', addon_to_id = '" . (int)$addon_product_id . "'")){
						$this->error = $this->db->getError();
						return false;
					}
				}
			}
			
			if(!$this->db->deleteRecords('tbl_product_attributes', array('smt' => 'product_id = ?', 'vals' => array($this->getProdId())))){
				$this->error = $this->db->getError();
				return false;
			}
			$record = new TableRecord('tbl_product_attributes');
			foreach($data["product_attribute"] as $key=>$val):
				$info["product_id"]=$this->getProdId();
				$info["attribute_id"]=(int)$val["attribute_id"];
				$info["attribute_text"]=$val["product_attribute_description"];
				$record->assignValues($info);
				$sqlquery=$record->getInsertQuery();
				$sqlquery=str_replace("INSERT","INSERT IGNORE",$sqlquery);
				if(!$this->db->query($sqlquery)){
					$this->error = $this->db->getError();
					return false;
				}
			endforeach;
			
			if(!$this->db->deleteRecords('tbl_product_shipping_rates', array('smt' => 'pship_prod_id = ?', 'vals' => array($this->getProdId())))){
				$this->error = $this->db->getError();
				return false;
			}
			
			//if ($data["prod_ship_free"]==0){
				$record = new TableRecord('tbl_product_shipping_rates');
				$cnObj=new Countries();
	            $scObj=new Shippingcompany();
				$sdObj=new Shippingduration();
				foreach($data["product_shipping"] as $key=>$val):
					$country = $cnObj->getData($val["country_id"]);
					$shipping_company = $scObj->getData($val["company_id"]);
					$shipping_duration = $sdObj->getData($val["processing_time_id"]);
					if ( ($country || $val["country_id"]==-1) && $shipping_company && $shipping_duration){
						$info["pship_prod_id"]=$this->getProdId();
						$info["pship_id"]=(int)$val["pship_id"];
						$info["pship_country"]=(int)$val["country_id"];
						$info["pship_company"]=(int)$val["company_id"];
						$info["pship_duration"]=(int)$val["processing_time_id"];
						$info["pship_charges"]=(float)$val["cost"];
						$info["pship_additional_charges"]=(float)$val["additional_cost"];
						$record->assignValues($info);
						$sqlquery=$record->getInsertQuery();
						$sqlquery=str_replace("INSERT","INSERT IGNORE",$sqlquery);
						if(!$this->db->query($sqlquery)){
							$this->error = $this->db->getError();
							return false;
						}
					}
				endforeach;
			//}
			
			if(!$this->db->deleteRecords('tbl_product_discounts', array('smt' => 'pdiscount_product_id = ?', 'vals' => array($this->getProdId())))){
				$this->error = $this->db->getError();
				return false;
			}
			$record = new TableRecord('tbl_product_discounts');
			foreach($data["product_discount"] as $key=>$val):
				$info["pdiscount_product_id"]=$this->getProdId();
				$info["pdiscount_priority"]=$val["priority"];
				$info["pdiscount_qty"]=(int)$val["quantity"];
				$info["pdiscount_price"]=(float)$val["price"];
				$info["pdiscount_start_date"]=$val["start_date"];
				$info["pdiscount_end_date"]=$val["end_date"];
				$record->assignValues($info);
				$sqlquery=$record->getInsertQuery();
				$sqlquery=str_replace("INSERT","INSERT IGNORE",$sqlquery);
				if(!$this->db->query($sqlquery)){
					$this->error = $this->db->getError();
					return false;
				}
			endforeach;
			
			if(!$this->db->deleteRecords('tbl_product_specials', array('smt' => 'pspecial_product_id = ?', 'vals' => array($this->getProdId())))){
				$this->error = $this->db->getError();
				return false;
			}
			$record = new TableRecord('tbl_product_specials');
			foreach($data["product_special"] as $key=>$val):
					$info["pspecial_product_id"]=$this->getProdId();
					$info["pspecial_priority"]=(int)$val["priority"];
					$info["pspecial_price"]=(float)$val["price"];
					$info["pspecial_start_date"]=$val["start_date"];
					$info["pspecial_end_date"]=$val["end_date"];
					$record->assignValues($info);
					$sqlquery=$record->getInsertQuery();
					$sqlquery=str_replace("INSERT","INSERT IGNORE",$sqlquery);
					if(!$this->db->query($sqlquery)){
						$this->error = $this->db->getError();
						return false;
					}
			endforeach;
			
			if(!$this->db->deleteRecords('tbl_product_option', array('smt' => 'product_id = ?', 'vals' => array($this->getProdId())))){
				$this->error = $this->db->getError();
				return false;
			}
			if (!$this->db->deleteRecords('tbl_product_option_value', array('smt' => 'product_id = ?', 'vals' => array($this->getProdId())))){
				$this->error = $this->db->getError();
				return false;
			}
			
			if (isset($data['product_option'])) {
					foreach ($data['product_option'] as $product_option) {
					if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
					if (isset($product_option['product_option_value'])) {
						$this->db->query("INSERT INTO tbl_product_option SET product_option_id = '" . (int)$product_option['product_option_id'] . "',product_id = '" . (int)$this->getProdId() . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "'");
						$product_option_id = $this->db->insert_id();
						foreach ($product_option['product_option_value'] as $product_option_value) {
							if(!$this->db->query("INSERT INTO tbl_product_option_value SET product_option_value_id = '" . (int)$product_option_value['product_option_value_id'] . "',product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$this->getProdId() . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$product_option_value['option_value_id'] . "', quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = " . $this->db->quoteVariable($product_option_value['price_prefix']) . ",weight = '" . (float)$product_option_value['weight'] . "', weight_prefix = " . $this->db->quoteVariable($product_option_value['weight_prefix']))){
								$this->error = $this->db->getError();
								return false;
							}
						}
					}
				} else {
					if(!$this->db->query("INSERT INTO tbl_product_option SET product_option_id = '" . (int)$product_option['product_option_id'] . "',product_id = '" . (int)$this->getProdId() . "', option_id = '" . (int)$product_option['option_id'] . "', value = " . $this->db->quoteVariable($product_option['value']) . ", required = '" . (int)$product_option['required'] . "'")){
						$this->error = $this->db->getError();
						return false;
					}
				}
			}
		}
		
		foreach($data["product_download"] as $key=>$val):
				$pfile_id = intval($val['pfile_id']);
				$record = new TableRecord('tbl_product_files');
				$assign_fields = array();
				$assign_fields['pfile_product_id'] = $this->getProdId();
				$assign_fields['pfile_download_name'] = $val['download_name'];
				$assign_fields['pfile_max_download_times'] = intval($val['max_download_times']);
				$assign_fields['pfile_can_be_downloaded_within_days'] = intval($val['validity']);
				if(isset($val['filename']) && $val['filename'] != ''){
					$assign_fields['pfile_name'] = $val['filename'];
				}
				$record->assignValues($assign_fields);
				if($pfile_id === 0 && $record->addNew()){
					$this->pfile_id=$record->getId();
				}elseif($pfile_id > 0 && $record->update(array('smt'=>'pfile_id=?', 'vals'=>array($pfile_id)))){
					$this->pfile_id=$pfile_id;
				}else{
					$this->error = $this->db->getError();
					return false;
				}
		endforeach;
		
			
		return $this->getProdId();
			
	}
	
	function getProductDefaultImage($product){
		$product = intval($product);
        if($product>0!=true) return array();
       	$add_criteria['product'] = $product;
        $srch = self::search_product_images($add_criteria,true);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $sql = $srch->getQuery();
        $rs = $this->db->query($sql);
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function getProductImageData($id){
		$id = intval($id);
        if($id>0!=true) return array();
       	$add_criteria['id'] = $id;
        $srch = self::search_product_images($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function getProductImages($id,$add_criteria=array()){
		$id = intval($id);
       	$add_criteria['product'] = $id;
        $srch = self::search_product_images($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch_all($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function search_product_images($criteria, $default='',$includeOrder= true) {
		$srch = new SearchBase('tbl_product_images', 'tpi');
        foreach($criteria as $key=>$val) {
            switch($key) {
         	   case 'id':
            	    $srch->addCondition('tpi.image_id', '=', intval($val));
    	            break;
			   case 'product':
			   		if (!array_key_exists("session",$criteria)){
						$srch->addCondition('tpi.image_prod_id', '=', intval($val));
					}
	                break;
			   case 'session':
					if ($criteria["product"]>0)
						$srch->addDirectCondition("(image_prod_id=".$criteria["product"].")");
					else	
						$srch->addDirectCondition("(tpi.image_session = '$val')");
	                break;		
        	   case 'not_include':
					if (is_array($val))
	                	$srch->addCondition('tpi.image_id', 'NOT IN', $val);
					else
						$srch->addCondition('tpi.image_id', '!=', $val);	
        	        break;													
            }
        }
		if ($default==true)
			$srch->setPageSize(1);
		
		if($includeOrder){
	 		$srch->addOrder('tpi.image_ordering', 'asc');
			$srch->addOrder('tpi.image_default', 'desc');
			$srch->addOrder('tpi.image_id','desc');
		}
		return $srch;
    }
	
	function getProductShippingRates($product_id,$criteria=array()){
		$product_id = intval($product_id);
        if($product_id>0!=true) return array();
		$srch = new SearchBase('tbl_product_shipping_rates', 'tpsr');
		$srch->joinTable('tbl_prod_details', 'LEFT JOIN', 'tpsr.pship_prod_id=tpd.prod_id', 'tpd');
		$srch->joinTable('tbl_countries', 'LEFT JOIN', 'tpsr.pship_country=tc.country_id and tc.country_delete=0', 'tc');
		$srch->joinTable('tbl_shipping_companies', 'INNER JOIN', 'tpsr.pship_company=tsc.scompany_id and tsc.scompany_is_deleted=0', 'tsc');
		$srch->joinTable('tbl_shipping_durations', 'INNER JOIN', 'tpsr.pship_duration=tsd.sduration_id and tsd.sduration_is_deleted=0', 'tsd');
		$srch->addCondition('tpsr.pship_prod_id', '=', intval($product_id));
		$srch->addOrder('(`pship_country` = -1),country_name');
		$srch->addMultipleFields(array('tpsr.*','tc.*','tsc.*','tsd.*','tpd.prod_ship_free'));
		
		foreach($criteria as $key=>$val) {
            switch($key) {
         	   case 'id':
            	    $srch->addCondition('tpsr.pship_id', '=', intval($val));
    	            break;
			   case 'country':
			   		$cnd=$srch->addCondition('tpsr.pship_country', '=', intval($val));
					$cnd->attachCondition('tpsr.pship_country', '=','-1','OR');
	                break;
            }
        }
		//die($srch->getquery());
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
       	$rs = $srch->getResultSet();
        $row = $this->db->fetch_all($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function getProductTags($product_id) {
		$srch = new SearchBase('tbl_product_to_tags', 'tprot');
		$srch->joinTable('tbl_product_tags', 'INNER JOIN', 'tprot.pt_tag_id=tpt.ptag_id', 'tpt');
		$srch->addCondition('pt_product_id', '=', intval($product_id));
		$srch->addOrder('ptag_name', 'asc');
		$rs = $srch->getResultSet();
		$product_tag_data = array();
       	while ($row=$this->db->fetch($rs)){
			$product_tag_data[] = $row['ptag_id'];
		}
		return $product_tag_data;
	}
	
	function getProductFilters($product_id) {
		$product_filter_data = array();
		$srch = new SearchBase('tbl_product_filter', 'tpf');
		$srch->joinTable('tbl_filters', 'INNER JOIN', 'tpf.filter_id=tf.filter_id', 'tf');
		$srch->addCondition('product_id', '=', intval($product_id));
		$srch->addOrder('filter_group', 'asc');
		$rs = $srch->getResultSet();
		$product_filter_data = array();
       	while ($row=$this->db->fetch($rs)){
			$product_filter_data[] = $row['filter_id'];
		}
		return $product_filter_data;
	}
	
	
	function getProductAttributes($product_id) {
		$product_attribute_data = array();
		$srch = new SearchBase('tbl_product_attributes', 'tpa');
		$srch->joinTable('tbl_attributes', 'INNER JOIN', 'tpa.attribute_id=ta.attribute_id', 'ta');
		$srch->addCondition('product_id', '=', intval($product_id));
		$srch->addOrder('attribute_group', 'asc');
		$rs = $srch->getResultSet();
		$product_attribute_data = array();
       	while ($row=$this->db->fetch($rs)){
			$product_attribute_data[] = array('id' => $row['attribute_id'],'text'=> $row['attribute_text']);
		}
		return $product_attribute_data;
	}
	
	function getProductDetailedAttributes($product_id) {
		$product_attribute_group_data = array();
		$srch = new SearchBase('tbl_product_attributes', 'tpa');
		$srch->joinTable('tbl_attributes', 'LEFT JOIN', 'tpa.attribute_id=ta.attribute_id', 'ta');
		$srch->joinTable('tbl_attribute_groups', 'LEFT JOIN', 'tag.attribute_group_id=ta.attribute_group', 'tag');
		$srch->addMultipleFields(array('tag.attribute_group_id, tag.attribute_group_name'));
		$srch->addCondition('tpa.product_id', '=', intval($product_id));
		$srch->addCondition('tag.attribute_group_is_deleted', '=',0);
		$srch->addCondition('ta.attribute_is_deleted', '=',0);
		$srch->addGroupBy('tag.attribute_group_id');
		$srch->addOrder('tag.attribute_group_display_order', 'asc');
		$srch->addOrder('tag.attribute_group_name', 'asc');
		$rsAttributeGroup = $srch->getResultSet();
		$product_attribute_group_data = array();
       	while ($product_attribute_group=$this->db->fetch($rsAttributeGroup)){
			$product_attribute_data = array();
			$srch = new SearchBase('tbl_product_attributes', 'tpa');
			$srch->joinTable('tbl_attributes', 'LEFT JOIN', 'tpa.attribute_id=ta.attribute_id', 'ta');
			$srch->addMultipleFields(array('ta.attribute_id, ta.attribute_name, tpa.attribute_text'));
			$srch->addCondition('tpa.product_id', '=', intval($product_id));
			$srch->addCondition('ta.attribute_group', '=',(int)$product_attribute_group['attribute_group_id']);
			$srch->addOrder('ta.attribute_display_order', 'asc');
			$srch->addOrder('ta.attribute_name', 'asc');
			$rsAttribute = $srch->getResultSet();
			$product_attribute_data = array();
			while ($product_attribute=$this->db->fetch($rsAttribute)){
				$product_attribute_data[] = array(
					'attribute_id' => $product_attribute['attribute_id'],
					'name'         => $product_attribute['attribute_name'],
					'text'         => $product_attribute['attribute_text']
				);
			}
			$product_attribute_group_data[] = array(
				'attribute_group_id' => $product_attribute_group['attribute_group_id'],
				'name'               => $product_attribute_group['attribute_group_name'],
				'attribute'          => $product_attribute_data
			);
		}
		return $product_attribute_group_data;
	}
	
	function getProductRelated($product_id) {
		$related_products = array();
		$uObj= new User();
		$user_id = $uObj->getLoggedUserId();
		$this->joinWithPromotionsTable();
		$this->addSpecialPrice();
		$this->applyConditions(array("favorite"=>$user_id));
		$this->selectFields(array('tp.*','ts.shop_id','ts.shop_user_id','ts.shop_name','ts.shop_title','tu.user_id','tu.user_name','tu.user_username','tu.user_email','IF(prod_stock >0, "1", "0" ) as available'));
		$this->doNotCalculateRecords();
		$this->doNotLimitRecords();
		$srch_related_query = $this->getQuery();
		
		$srch = new parent('tbl_product_relations', 'tpr');
		$srch->joinTable('(' . $srch_related_query  . ')', 'INNER JOIN', 'tpr.relation_to_id = p.prod_id', 'p');
		$srch->addCondition('tpr.relation_source_id', '=', intval($product_id));
		//die($srch->getquery());
		$rs = $srch->getResultSet();
		$related_products=$this->db->fetch_all($rs);
		return $related_products;
	}
	
	function getProductDiscounts($product_id,$criteria=array()) {
		$current_date = date('Y-m-d');
		$srch = new SearchBase('tbl_product_discounts', 'tpd');
		$srch->addCondition('tpd.pdiscount_product_id', '=', intval($product_id));
		foreach($criteria as $key=>$val) {
            switch($key) {
         	   case 'id':
            	    $srch->addCondition('tpd.pdiscount_id', '=', intval($val));
    	            break;
			   case 'date':
			   		$cnd=$srch->addDirectCondition('((pdiscount_start_date = \'0000-00-00\' OR pdiscount_start_date <= "'.$current_date.'") AND (pdiscount_end_date = \'0000-00-00\' OR pdiscount_end_date >= "'.$current_date.'"))');
	                break;
			   case 'qty':
            	    $srch->addCondition('tpd.pdiscount_qty', '<=', intval($val));
    	            break;	
            }
        }
		$srch->addOrder('tpd.pdiscount_priority', 'asc');
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch_all($rs);
		return $row;
	}
	
	function getProductOptions($product_id) {
		$product_option_data = array();
		$srch = new SearchBase('tbl_product_option', 'po');
		$srch->joinTable('tbl_options', 'INNER JOIN', 'po.option_id=o.option_id', 'o');
		$srch->addCondition('po.product_id', '=', intval($product_id));
		$srch->addCondition('o.option_is_deleted', '=',0);
		$srch->addOrder('o.option_display_order', 'asc');
		$rsOptGroup = $srch->getResultSet();
		$product_option_data = array();
       	while ($product_option=$this->db->fetch($rsOptGroup)){	
			$product_option_value_data = array();
			$srchOpt = new SearchBase('tbl_product_option_value', 'tpov');
			$srchOpt->joinTable('tbl_option_values', 'INNER JOIN', 'tpov.option_value_id=tov.option_value_id', 'tov');
			$srchOpt->addCondition('product_option_id', '=', (int)$product_option['product_option_id']);
			$srchOpt->addOrder('tov.option_value_display_order', 'ASC');
			$srchOpt->addOrder('tov.option_value_name', 'ASC');
			$rsOpt = $srchOpt->getResultSet();
			$product_option_value_data = array();
    	   	while ($product_option_value=$this->db->fetch($rsOpt)){	
		
				$product_option_value_data[] = array(
					'product_option_value_id' => $product_option_value['product_option_value_id'],
					'option_value_id'         => $product_option_value['option_value_id'],
					'name'                    => $product_option_value['option_value_name'],
					'quantity'                => $product_option_value['quantity'],
					'subtract'                => $product_option_value['subtract'],
					'price'                   => $product_option_value['price'],
					'price_prefix'            => $product_option_value['price_prefix'],
					'weight'                  => $product_option_value['weight'],
					'weight_prefix'           => $product_option_value['weight_prefix']
				);
			}
			$product_option_data[] = array(
				'product_option_id'    => $product_option['product_option_id'],
				'product_option_value' => $product_option_value_data,
				'option_id'            => $product_option['option_id'],
				'name'                 => $product_option['option_name'],
				'type'                 => $product_option['option_type'],
				'value'                => $product_option['value'],
				'required'             => $product_option['required']
			);
		}
		return $product_option_data;
	}
	
	function deleteProduct($prod_id){
		$prod_id = intval($prod_id);
		if($prod_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_products', array('prod_is_deleted' => 1), array('smt'=>'`prod_id`=?', 'vals'=>array($prod_id)))){	
			$this->db->deleteRecords('tbl_url_alias', array('smt'=>'url_alias_query=? ', 'vals'=>array('products_id='.$prod_id)));
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function finalizeUserProduct($prod_id, $user_id){
		$prod_id = intval($prod_id);
		$user_id = intval($user_id);
		if($prod_id < 1 || $user_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_products', array('prod_is_expired'=>1), array('smt'=>'`prod_id`=? AND `prod_added_by`=?', 'vals'=>array($prod_id, $user_id)),true)){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	function updateProductStatus($prod_id,$mod){
		$prod_id = intval($prod_id);
		if($prod_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		switch($mod) {
            case 'block':
            	$data_to_update = array(
					'prod_status'=>0,
	            );
            break;
            case 'unblock':
    	        $data_to_update = array(
					'prod_status'=>1,
            	);
            break;
           
        }
		if(count($data_to_update)>0!=true) {
            $this->error =Utilities::getLabel('L_Action_Trying_Perform_Not_Valid');
	        return false;
        }
		if($this->db->update_from_array('tbl_products',$data_to_update, array('smt'=>'`prod_id`=? ', 'vals'=>array($prod_id)),true)){
			return true;
		}
		$this->error = $this->db->getError();
		
	}
	
	function getProductPrimaryInfo($id) {
        $id = intval($id);
        if($id>0!=true) return array();
       	$srch=new SearchBase('tbl_products','tp');
		$srch->joinTable('tbl_prod_details', 'LEFT JOIN', 'tp.prod_id=tpd.prod_id', 'tpd');
		$srch->addCondition('tp.prod_id', '=', intval($id));
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function deleteProductImage($image_id){
		$image_id = intval($image_id);
		if($image_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->deleteRecords('tbl_product_images', array('smt'=>'image_id=?', 'vals'=>array($image_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function makeProductImageDefault($image_id,$product_id){
		$image_id = intval($image_id);
		if (($image_id < 1)){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_product_images', array('image_default' => 0), array('smt' => 'image_prod_id = ?', 'vals' => array($product_id)))){
			$this->db->update_from_array('tbl_product_images', array('image_default' => 1), array('smt' => 'image_id = ?', 'vals' => array($image_id)));
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function addProductImage($data){
		$record = new TableRecord('tbl_product_images');
		$record->assignValues($data);
		if($record->addNew()){
			$this->product_image_id=$record->getId();
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return $this->product_image_id;
	}
	
	function getProductReturnRequests($criteria,$pagesize=10){
		 $srch = self::search_product_refund_requests($criteria);
         if ((intval($pagesize)>0) || isset($criteria["pagesize"])){
			$srch->setPageSize(isset($criteria["pagesize"])?$criteria["pagesize"]:$pagesize);
		 }
		 $srch->addOrder('refund_id','DESC');
         $rs = $srch->getResultSet();
		 $this->total_records = $srch->recordCount();
		 $this->total_pages = $srch->pages();
         $row = (($pagesize==1) || ($criteria["pagesize"]==1))?$this->db->fetch($rs):$this->db->fetch_all($rs);
         if($row==false) return array();
         else return $row;
	
	}
	
	
	function search_product_refund_requests($criteria, $default='') {
		$srch = new SearchBase('tbl_prod_refund_requests', 'tprr');
		$srch->joinTable('tbl_order_products', 'INNER JOIN', 'tprr.refund_order=torp.opr_id', 'torp');
		$srch->joinTable('tbl_orders', 'INNER JOIN', 'torp.opr_order_id=tord.order_id', 'tord');
		$srch->joinTable('tbl_return_reasons', 'LEFT JOIN', 'tprr.refund_reason=trr.returnreason_id', 'trr');
		$srch->joinTable('tbl_users', 'INNER JOIN', 'tprr.refund_user_id=tb.user_id', 'tb');
        $srch->joinTable('tbl_shops', 'INNER JOIN', 'torp.opr_product_shop=ts.shop_id', 'ts');
		$srch->joinTable('tbl_states', 'LEFT JOIN', 'ts.shop_state=tsret.state_id', 'tsret');
		$srch->joinTable('tbl_countries', 'LEFT JOIN', 'ts.shop_country=tcret.country_id', 'tcret');
		$srch->joinTable('tbl_users', 'LEFT JOIN', 'tprr.refund_request_updated_by=tbu.user_id and tprr.refund_request_action_by="U"', 'tbu');
        $srch->addMultipleFields(array('tprr.*','tb.*','torp.*','tord.order_id','tord.order_vat_perc','tb.user_name as buyer_name','tb.user_email as buyer_email','torp.opr_shop_owner_name as vendor_name','torp.opr_shop_owner_email as vendor_email','ts.*','tsret.state_name as shop_state_name','tcret.country_name as shop_country_name','trr.returnreason_title','tbu.user_username as last_updated_by'));
        foreach($criteria as $key=>$val) {
			if(strval($val)=='') continue;
            switch($key) {
         	   case 'id':
            	    $srch->addCondition('tprr.refund_id', '=', intval($val));
    	            break;
			   case 'return':
			   		$user=$criteria["user"];
			   		if ($val=="all"){
						$cnd=$srch->addCondition('tprr.refund_user_id', '=', intval($user));
						$cnd->attachCondition('ts.shop_user_id', '=',$user,'OR');
					}elseif ($val=="sent"){
						$srch->addCondition('tprr.refund_user_id', '=', intval($user));
					}
					elseif ($val=="received"){
						$srch->addCondition('ts.shop_user_id', '=', intval($user));
					}
					break;
			   case 'order':
                	$srch->addCondition('tprr.refund_order', '=', intval($val));
	                break;
			   case 'status':	
			   			if (is_array($val))	
							$srch->addCondition('tprr.refund_request_status', 'IN', $val);
						else
							$srch->addCondition('tprr.refund_request_status', '=', intval($val));	
			   		break;
			   case 'product':
                	$srch->addCondition('tprr.refund_prod_id', '=', intval($val));
	                break;
			    case 'page':
					$srch->setPageNumber($val);
				break;	
				case 'pagesize':
					$srch->setPageSize($val);
				break;		
			   case 'keyword':
				if ($val!=""){
					$val=urldecode($val);
					$cndCondition=$srch->addCondition('tb.user_name', 'like', '%' . $val . '%');
					$cndCondition->attachCondition('torp.opr_name', 'like', '%' . $val . '%','OR');
				}	
                break;		
            }
        }
        $srch->addOrder('tprr.refund_id', 'desc');
        return $srch;
    }
	
	function getReturnRequest($id,$add_criteria=array()) {
        global $db;
        $id = intval($id);
        if($id>0) $add_criteria['id'] = $id;
        $srch = self::search_product_refund_requests($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
    }
	
	function getReturnRequestMessage($id,$add_criteria=array()) {
        $id = intval($id);
        if($id>0) $add_criteria['id'] = $id;
        $srch = self::search_product_refund_requests_messages($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
    }
	
	function getReturnRequestMessages($request,$add_criteria=array()) {
        $request = intval($request);
        if($request>0) $add_criteria['request'] = $request;
        $srch = self::search_product_refund_requests_messages($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch_all($rs);
        if($row==false) return array();
        else return $row;
    }
	
	function search_product_refund_requests_messages($criteria, $default='') {
		$srch = new SearchBase('tbl_prod_refund_request_messages', 'tprrm');
		$srch->joinTable('tbl_prod_refund_requests', 'INNER JOIN', 'tprrm.refmsg_request=tprr.refund_id', 'tprr');
		$srch->joinTable('tbl_users', 'LEFT JOIN', 'tprr.refund_user_id=tb.user_id', 'tb');
		$srch->joinTable('tbl_products', 'INNER JOIN', 'tprr.refund_prod_id=tp.prod_id', 'tp');
		$srch->joinTable('tbl_users', 'LEFT JOIN', 'tprrm.refmsg_from=tu.user_id and tprrm.refmsg_from_type="U"', 'tu');
		$srch->joinTable('tbl_admin', 'LEFT JOIN', 'tprrm.refmsg_from=ta.admin_id and tprrm.refmsg_from_type="A"', 'ta');
		$srch->joinTable('tbl_order_products', 'INNER JOIN', 'tprr.refund_order=torp.opr_id', 'torp');
		$srch->joinTable('tbl_return_reasons', 'LEFT JOIN', 'tprr.refund_reason=trr.returnreason_id', 'trr');
		$srch->addCondition('tprrm.refmsg_is_deleted', '=', 0);
		$srch->addMultipleFields(array('*','tu.user_username as message_sent_by_username','tu.user_profile_image as message_sent_by_profile','ta.admin_id','tb.user_name as buyer_name','tb.user_email as buyer_email'));
        foreach($criteria as $key=>$val) {
            switch($key) {
         	   case 'id':
            	    $srch->addCondition('tprrm.refmsg_id', '=', intval($val));
    	            break;
			  case 'request':
            	    $srch->addCondition('refmsg_request', '=', intval($val));
    	            break; 			
			  case 'user':
            	    $cnd=$srch->addCondition('tprr.refund_user_id', '=', intval($val));
					$cnd->attachCondition('tp.prod_added_by', '=',$val,'OR');
    	            break;		
			   case 'order':
                	$srch->addCondition('tprrm.return_order', '=', intval($val));
	                break;
			   case 'product':
                	$srch->addCondition('tprrm.refund_prod_id', '=', intval($val));
	                break;
            }
        }
        $srch->addOrder('tprrm.refmsg_id', 'asc');
        return $srch;
	}
	
	function getProductCommission($product_id){
		$product = $this->getProductPrimaryInfo($product_id);
		$cObj = new Categories();
		$category_id=0;
		if ($product['prod_category']>0){
			$product_category_structure=$cObj->funcGetCategoryStructure($product['prod_category']);
			if (is_array($product_category_structure) && (!empty($product_category_structure)))
				$category_id = $product_category_structure[0]['category_id'];
		}
		$rs = $this->db->query("SELECT commsetting_fees, 
			CASE
  				WHEN commsetting_product = '".$product_id."' THEN 10
				WHEN (commsetting_vendor = '".(int)$product['prod_added_by']."' AND commsetting_category = '".(int)$category_id."') THEN 9
				WHEN (commsetting_vendor = '".(int)$product['prod_added_by']."' AND commsetting_category = '0') THEN 8
				WHEN (commsetting_vendor = '".(int)$product['prod_added_by']."' AND commsetting_category != '".(int)$category_id."')  THEN 7
				WHEN (commsetting_category = '".(int)$category_id."' AND commsetting_vendor = 0)  THEN 6
				WHEN (commsetting_category = '".(int)$category_id."' AND commsetting_vendor != '".(int)$product['prod_added_by']."')  THEN 5
				WHEN (commsetting_product = '0' AND commsetting_vendor = '0' AND commsetting_category = '0') THEN 1
			END 
       		as matches FROM tbl_commission_settings WHERE commsetting_is_deleted = 0 order by matches desc  limit 0,1");	
		if($row = $this->db->fetch($rs)) {
			return $row['commsetting_fees'];
		}
		
	}
	
	function getProductAffiliateCommission($product_id,$affiliate_id){
		$product = $this->getProductPrimaryInfo($product_id);
		$cObj = new Categories();
		$category_id=0;
		if ($product['prod_category']>0){
			$product_category_structure=$cObj->funcGetCategoryStructure($product['prod_category']);
			if (is_array($product_category_structure) && (!empty($product_category_structure)))
				$category_id = $product_category_structure[0]['category_id'];
		}
		
		$rs = $this->db->query("SELECT afcommsetting_fees, 
			CASE
				WHEN (afcommsetting_affiliate = '".(int)$affiliate_id."' AND afcommsetting_category = '".(int)$category_id."') THEN 9
				WHEN (afcommsetting_affiliate = '".(int)$affiliate_id."' AND afcommsetting_category = '0') THEN 8
				WHEN (afcommsetting_category = '".(int)$category_id."' AND afcommsetting_affiliate = 0)  THEN 6
				WHEN (afcommsetting_category = '".(int)$category_id."' AND afcommsetting_affiliate != '".(int)$affiliate_id."')  THEN 5
				WHEN (afcommsetting_affiliate = '0' AND afcommsetting_category = '0') THEN 1
			END 
       		as matches FROM tbl_affiliate_commission_settings WHERE afcommsetting_is_deleted = 0 order by matches desc  limit 0,1");	
		if($row = $this->db->fetch($rs)) {
			return $row['afcommsetting_fees'];
		}
		
	}
	
	function startsWith($haystack, $needle) {
	    return $needle === "" || strripos($haystack, $needle, -strlen($haystack)) !== FALSE;
	}
	
	
	
	function setProductVisitorCookie($product_id){
		if(isset($_COOKIE['ProductVisitorCookie'])){
			$number_elements = 20;
			$cookie_product_arr=explode("~",$_COOKIE['ProductVisitorCookie']);
			array_push($cookie_product_arr,base64_encode($product_id));
			$cookie_product_arr= array_slice($cookie_product_arr,-$number_elements,$number_elements,true);
			$cookie_product_arr = array_unique($cookie_product_arr);
			setcookie('ProductVisitorCookie', implode("~",$cookie_product_arr), time()+3600*24*90,'/');
		}else{
			setcookie('ProductVisitorCookie', ($product_id), time()+3600*24*90,'/');
		}
	}
	
	public function getAlsoBoughtProducts($product_id) {
		$product_data = array();
		$limit = Settings::getSetting("CONF_CUSTOMER_BOUGHT_ITEMS_PRODUCT_PAGE");
		if(isset($limit) && $limit>0){
			if (isset($product_id) && $product_id > 0) {
				$uObj= new User();
				$user_id = $uObj->getLoggedUserId();
				
				$this->joinWithPromotionsTable();
				$this->addSpecialPrice();
				$this->applyConditions(array("favorite"=>$user_id));
				$this->selectFields(array('tp.*','ts.shop_id','ts.shop_user_id','ts.shop_name','ts.shop_title','tu.user_id','tu.user_name','tu.user_username','tu.user_email','IF(prod_stock >0, "1", "0" ) as available'));
				$this->doNotCalculateRecords();
				$this->doNotLimitRecords();
				$srch_prom_prod_qry = $this->getQuery();
				
				$srch = new parent('tbl_order_products', 'op');
				$srch->joinTable('tbl_orders', 'INNER JOIN', 'op.opr_order_id = o.order_id', 'o');
				$srch->addDirectCondition("EXISTS (SELECT 1 FROM tbl_order_products op1  WHERE op1.opr_order_id = op.opr_order_id AND op1.opr_product_id = '" .(int)$product_id . "')");
				$srch->addCondition('o.order_payment_status', 'IN',array(1,2));
				$srch->addCondition('op.opr_product_id', '!=',$product_id);
				$srch->joinTable('(' . $srch_prom_prod_qry . ')', 'INNER JOIN', 'op.opr_product_id = p.prod_id', 'p');
				$srch->addFld(array('p.*'));
				$srch->addGroupBy('opr_product_id');
				//die($srch->getquery());
				$rs = $srch->getResultSet();
				$product_data=$this->db->fetch_all($rs);
			}
		}
		return $product_data;
	}
	
	function getProductAddons($product_id) {
		$product_addons_data = array();
		$uObj= new User();
		$user_id = $uObj->getLoggedUserId();
		
		$this->joinWithPromotionsTable();
		$this->addSpecialPrice();
		$this->applyConditions(array("favorite"=>$user_id));
		$this->selectFields(array('tp.*','ts.shop_id','ts.shop_user_id','ts.shop_name','ts.shop_title','tu.user_id','tu.user_name','tu.user_username','tu.user_email','IF(prod_stock >0, "1", "0" ) as available'));
		$this->doNotCalculateRecords();
		$this->doNotLimitRecords();
		$srch_prod_addon_qry = $this->getQuery();
		
		$srch = new parent('tbl_product_addons', 'tpa');
		$srch->joinTable('(' . $srch_prod_addon_qry  . ')', 'INNER JOIN', 'tpa.addon_to_id = p.prod_id', 'p');
		$srch->addCondition('tpa.addon_source_id', '=',$product_id);
		$srch->addFld(array('p.*'));
		//die($srch->getquery());
		$rs = $srch->getResultSet();
		$product_addons_data=$this->db->fetch_all($rs);
		return $product_addons_data;
	}
	
	
	public function getSmartRecommendedProducts($user_id=0,$product_id=0,$limit=true) {
		$suggested_products_data = array();
		$limit = Settings::getSetting("CONF_RECOMMENDED_ITEMS_HOME_PAGE");
		if(isset($limit) && $limit>0){
			$uObj= new User();
			if ($user_id==0) $user_id = $uObj->isUserLogged()?$uObj->getLoggedUserId():$uObj->getUserIdFromCookies();
				
				$this->joinWithPromotionsTable();
				$this->addSpecialPrice();
				$this->applyConditions(array("favorite"=>$user_id));
				$this->selectFields(array('tp.*','ts.shop_id','ts.shop_user_id','ts.shop_name','ts.shop_title','tu.user_id','tu.user_name','tu.user_username','tu.user_email','IF(prod_stock >0, "1", "0" ) as available'));
				$this->doNotCalculateRecords();
				$this->doNotLimitRecords();
				$get_products_query = $this->getQuery();
				//die();
			
				$srch=new parent('tbl_order_products','op');
				$srch->joinTable('tbl_orders', 'LEFT JOIN', 'o.order_id=op.opr_order_id', 'o');
				$srch->addCondition('o.order_payment_status', 'IN',array(1,2));
				$srch->addCondition('o.order_user_id', '=',$user_id);
				$srch->addGroupBy('op.opr_product_id');
				$srch->addFld('op.opr_product_id');
				$srch->doNotCalculateRecords();
				$srch->doNotLimitRecords();
				$sql_purchased_query = $srch->getQuery();
				
				
				$srch=new parent('tbl_smart_user_activity_browsing','tsuab');
				$srch->joinTable('tbl_smart_related_products', 'LEFT JOIN', 'tsuab.uab_record_id=tsrp.tsrp_source_product_id AND tsuab.uab_record_type=1', 'tsrp');
				$srch->joinTable('tbl_product_to_tags', 'LEFT JOIN', 'tsuab.uab_record_id=tpt.pt_tag_id AND tsuab.uab_record_type=4', 'tpt');
				$srch->joinTable('tbl_products', 'LEFT JOIN', 'tsuab.uab_record_id=tpc.prod_category and tsuab.uab_record_type=3', 'tpc');
				$srch->joinTable('tbl_products', 'LEFT JOIN', 'tsuab.uab_record_id=tpb.prod_brand AND tsuab.uab_record_type=2', 'tpb');
				$srch->addMultipleFields(array('tsuab.uab_last_action_datetime as last_action,coalesce(tsrp.tsrp_related_product_id,tpt.pt_product_id,tpc.prod_id,tpb.prod_id,0) product_id'));
				$srch->addDirectCondition('coalesce(tsrp.tsrp_related_product_id,tpt.pt_product_id,tpc.prod_id,tpb.prod_id) NOT IN ('.$sql_purchased_query.')');
				if ($product_id>0){
					$catObj = new Categories();
					$pObj = new self();
					$product = $pObj->getData($product_id);
					$category_id = (int)$product['prod_category'];
					if ($category_id>0 && ($cat_structure = $catObj->funcGetCategoryStructure($category_id))){
							$category_id = $cat_structure[0]['category_id'];
							$srch->joinTable('tbl_products', 'LEFT JOIN', 'tsrp.tsrp_related_product_id=tp.prod_id', 'tp');
							$cats=array_merge(array($category_id),array_keys(Categories::getProdClassifiedCategoriesAssocArray($category_id)));
							$srch->addCondition('tp.prod_category', 'IN',$cats);
						}
						$srch->addCondition('tsrp.tsrp_source_product_id','=',$product_id);
						$srch->addDirectCondition('coalesce(tsrp.tsrp_related_product_id,tpt.pt_product_id,tpc.prod_id,tpb.prod_id) != '.(int)$product_id);
				}
				$srch->addCondition('tsuab.uab_user_id','=',$user_id);
				$srch->addOrder('tsuab.uab_last_action_datetime','desc');
				$srch->setPageSize(1000);
				$srch->doNotCalculateRecords(true);
				$sql_sub_activity_query = $srch->getquery();
				
				
				$srch=new parent('(' . $sql_sub_activity_query . ')','tsjq');
				$srch->joinTable('tbl_smart_products_weightage', 'LEFT OUTER JOIN', 'tspw.spw_product_id = tsjq.product_id', 'tspw');
				$srch->joinTable('(' . $get_products_query . ')', 'INNER JOIN', 'tspw.spw_product_id = p.prod_id', 'p');
				$srch->addOrder('tsjq.last_action','desc');
				$srch->addOrder('if (spw_custom_weightage_valid_till>now(),spw_weightage+spw_custom_weightage,spw_weightage)','desc');
				$srch->addMultipleFields(array('distinct(p.prod_id) as pid,p.*,tsjq.*,tspw.*','if (spw_custom_weightage_valid_till>now(),COALESCE(spw_weightage+spw_custom_weightage,0),COALESCE(spw_weightage,0)) weightage'));
				$srch->addCondition('tspw.spw_is_excluded', '=',0);
				$srch->addGroupBy('p.prod_id');
				if ($limit) $srch->setPagesize($limit);
				else $srch->doNotLimitRecords(true);
				//die($srch->getquery());
				$rs = $srch->getResultSet();
				$suggested_products_data = $this->db->fetch_all($rs);
					
		}
		return $suggested_products_data;
	}
	
	public function recordProductWeightage($product_id,$action,$event_weightage=0,$unique_action=true){
		$srObj=new SmartRecommendations();
		if ($event_weightage==0){
			$weightageEvents= $srObj->getWeightageEvents();
			$arr=explode("#",$action);
			$event_weightage = $weightageEvents[$arr[0]][$arr[1]]['value'];
			if (empty($event_weightage))
				$event_weightage = 0;
		}
		$limit = 50;
		if (isset($product_id) && $product_id > 0) {
			if(self::UniqueProductAction($product_id,$action) || $unique_action==false){
				$record = new TableRecord('tbl_smart_products_weightage');
				$assign_fields['spw_product_id'] = $product_id;
				$assign_fields['spw_weightage'] = $event_weightage;
				$record->assignValues($assign_fields);
				$sql= str_replace("INSERT","INSERT IGNORE",$record->getinsertquery()." ON DUPLICATE KEY UPDATE `spw_weightage` = `spw_weightage`+$event_weightage");
				//die($sql);
				if ($this->db->query($sql)){
					$this->db->insert_from_array('tbl_smart_log_actions', array('slog_record_id'=>$product_id,'slog_ip'=>$_SERVER['REMOTE_ADDR'],'slog_datetime'=>'mysql_func_now()','slog_action'=>$action,'slog_type'=>'P'),true);
				}
				$uObj= new User();
	
				$sql_join_query = "Select distinct product_id from (Select distinct pt_product_id as product_id from tbl_product_to_tags where pt_tag_id in (SELECT pt_tag_id FROM `tbl_product_to_tags` tptot INNER JOIN tbl_product_tags tpt on tptot.pt_tag_id=tpt.ptag_id WHERE tptot.`pt_product_id` = '".(int)$product_id."') 
				UNION ALL (Select relation_to_id from tbl_product_relations where relation_source_id = '".(int)$product_id."' ) 
				UNION ALL (SELECT op.opr_product_id FROM tbl_order_products op INNER JOIN `tbl_orders` o ON (o.order_id = op.opr_order_id) INNER JOIN `tbl_products` p ON (op.opr_product_id = p.prod_id)  WHERE EXISTS (SELECT 1 FROM tbl_order_products op1  WHERE op1.opr_order_id = op.opr_order_id AND op1.opr_product_id = '" .(int)$product_id . "' ) AND op.opr_product_id <> '" . (int)$product_id . "' AND  o.order_payment_status IN (1,2) GROUP BY op.opr_product_id order by count(op.opr_product_id) desc LIMIT 25)  
				UNION ALL (Select `pbhistory_prod_id` from tbl_products_browsing_history where pbhistory_sessionid in ( SELECT distinct pbhistory_sessionid FROM `tbl_products_browsing_history` WHERE pbhistory_prod_id='" .(int)$product_id . "') and pbhistory_prod_id!='" .(int)$product_id . "' group by `pbhistory_prod_id` order by count(`pbhistory_prod_id`) LIMIT 25)) tsp INNER JOIN tbl_products tpa ON tpa.prod_id=tsp.product_id INNER JOIN tbl_products tpb ON tpb.prod_sale_price>=tpa.prod_sale_price*(1-(25/100)) and tpb.prod_sale_price<=tpa.prod_sale_price*(1+(25/100)) and tpb.prod_id='" .(int)$product_id . "'";
				
				
				$spw_weightage = $event_weightage>0?1:-1;
				$srch=new SearchBase('(' . $sql_join_query . ')','tsjq');
				$srch->joinTable('tbl_smart_products_weightage', 'LEFT OUTER JOIN', 'tspw.spw_product_id = tsjq.product_id', 'tspw');
				$srch->addOrder('if (spw_custom_weightage_valid_till>now(),spw_weightage+spw_custom_weightage,spw_weightage)','desc');
				$srch->addMultipleFields(array('tsjq.*,tspw.*'));
				$srch->setPagesize($limit);
				$rs = $srch->getResultSet();
				while ($row=$this->db->fetch($rs)){
						$record = new TableRecord('tbl_smart_products_weightage');
						$assign_fields['spw_product_id'] = $row['product_id'];
						$assign_fields['spw_weightage'] = $spw_weightage;
						$record->assignValues($assign_fields);
						$record->addNew(array('IGNORE'),array('spw_weightage'=>'mysql_func_spw_weightage+'.$spw_weightage));
						$record = new TableRecord('tbl_smart_related_products');
						$record->assignValues(array('tsrp_source_product_id'=>$product_id,"tsrp_related_product_id"=>$row['product_id']));
						$record->addNew(array('IGNORE'));
						
				}
				$srObj=new SmartRecommendations();
				$srObj->addUpdateUserBrowsingActivity($product_id,1);
				if (strpos($action,"order_paid") !== false) {
					$pObj= new self();
					$pObj->joinWithPromotionsTable();
					$product = $pObj->getData($product_id);	
					$promObj=new Promotions();
					$arrData=array('promotion_id'=>$product['promotion_id']);	
					$promObj->addPromotionAnalysisRecord($arrData,"orders");
				}
				return true;
				
			}
		}
		return false;
	}
	
	private function UniqueProductAction($product_id,$action){
		return true;
		$ip_address = $_SERVER['REMOTE_ADDR'];
		$rs = $this->db->query("SELECT * FROM tbl_smart_log_actions where slog_ip = '" .$ip_address . "' AND (date_add(NOW(), INTERVAL -5 MINUTE) < slog_datetime) AND slog_action='$action' AND slog_record_id='$product_id' AND slog_type='P'");
		$row=$this->db->fetch($rs);
		return ($row==false)?true:false;
	}
	
	
	
	function addUpdateProductBrowsingHistory($product_id,$data){
			$product_id = intval($product_id);
			$uObj = new User();
			$record = new TableRecord('tbl_products_browsing_history');
			$assign_fields = array();
			$assign_fields['pbhistory_sessionid'] = session_id();
			$assign_fields['pbhistory_datetime'] = date('Y-m-d H:i:s');
			$assign_fields['pbhistory_prod_id'] = $product_id;
			$user_id =  $uObj->isUserLogged()?$uObj->getLoggedUserId():$uObj->getUserIdFromCookies();
			$assign_fields['pbhistory_user_id'] = $user_id;
			if (isset($data['ordered'])){
				$assign_fields['pbhistory_is_ordered'] = 1;
			}
			if (isset($data['session_id'])){
				$assign_fields['pbhistory_sessionid'] = $data['session_id'];
			}
			if (isset($data['user_id'])){
				$assign_fields['pbhistory_user_id'] = $data['user_id'];
			}
			if (isset($data['returned'])){
				$assign_fields['pbhistory_is_order_returned'] = 1;
			}
			if (isset($data['cancelled'])){
				$assign_fields['pbhistory_is_order_cancelled'] = 1;
			}
			if (isset($data['favorite'])){
				$assign_fields['pbhistory_is_favorite'] = 1;
			}
			if (isset($data['wishlist'])){
				$assign_fields['pbhistory_is_included_in_wishlist'] = 1;
			}
			if (isset($data['cart'])){
				$assign_fields['pbhistory_is_in_cart'] = 1;
			}
			if (isset($data['cart_removed'])){
				$assign_fields['pbhistory_is_removed_from_cart'] = 1;
			}
			if (isset($data['blocked'])){
				$assign_fields['pbhistory_is_blocked'] = 1;
			}
			$arrOnDuplicateKey = array();
			if (isset($data['visits'])){
				$assign_fields['pbhistory_visits_count'] = 1;
				$arrOnDuplicateKey['pbhistory_visits_count']='mysql_func_pbhistory_visits_count+1';
			}
			if (isset($data['seconds'])){
				$arrOnDuplicateKey['pbhistory_seconds_spent']='mysql_func_pbhistory_seconds_spent+'.$data['seconds'];
			}
			$onDuplicateKeyUpdate=array_merge($assign_fields,$arrOnDuplicateKey);
			
			$this->db->insert_from_array('tbl_products_browsing_history',$assign_fields,true,false,$onDuplicateKeyUpdate);
			
	}
	
	public function validateOptionQuantity($productOptions,$prod_stock){
			foreach($productOptions as $productOption){
				$optQuantity= 0;
				foreach($productOption['product_option_value'] as $key=> $optionValues){
					$optQuantity += $optionValues['quantity'];
				}
				if($optQuantity>$prod_stock){			
					return false;
				}
			}
		return true;
	}
	
	
	public function getTotalProductsAddedByUser($userId = 0){
		$pObj= new self(false,true);
		$pObj->applyConditions(array('added_by'=>$userId));
		$rs = $pObj->getResultSet();
		return $pObj->recordCount();
	}
	public function getTotalImagesAddedByUser($userId,$product_id){
		$prod_images = $this->getProductImages($product_id,array("session"=>session_id()));
		return count($prod_images);
	}
	public function deleteExtraLatestProducts($userId,$productsAllowed = 0,$productsAdded,$allowedImages=0){
		global $db;
		//$srch = self::search(array(),false,false);
		$srch=new SearchBase('tbl_products','tp');
		$srch->joinTable('tbl_product_images','Left Join','tpi.image_prod_id=tp.prod_id and tpi.image_default=1','tpi');
		$srch->addCondition('tp.prod_is_deleted','=',0);
		$srch->addCondition('tp.prod_added_by','=',$userId);
		$srch->addOrder('tp.prod_id','asc');		
		$srch ->addMultipleFields(array('tp.prod_id','tpi.image_id'));
		$srch->doNotLimitRecords();
		if($rs = $db->query($srch->getQuery().' limit '.intval($productsAllowed).",".(intval($productsAdded )))){
			foreach($db->fetch_all($rs) as $product){
				$data['prod_is_deleted']=1;
				$record = new TableRecord('tbl_products');
				$record->assignValues($data);
				if(!$record->update(array('smt'=>'prod_id=?', 'vals'=>array($product['prod_id'])))){
				 $this->error = $this->db->getError();					
				}
				
			}
	}
	if($rs = $db->query($srch->getQuery().' limit 0,'.intval($productsAllowed))){
			foreach($db->fetch_all($rs) as $product){
				$imageCount= $this->getTotalImagesAddedByUser($userId,$product['prod_id']);
				if($imageCount>$allowedImages){
					 $lowestImageId = $this->getLowestProductImageId($allowedImages,$product['prod_id'],$product['image_id']);
					 if($allowedImages ==0){
						$this->db->deleteRecords('tbl_product_images', array('smt'=>'image_id>=? and image_prod_id = ?', 'vals'=>array($lowestImageId,$product['prod_id'])));
					}else{
						$this->db->deleteRecords('tbl_product_images', array('smt'=>'image_id>=? and image_prod_id = ? and image_id != ?', 'vals'=>array($lowestImageId,$product['prod_id'],$product['image_id'])));
					} 
				}
				
			}
		}
	}
	
	public function getLowestProductImageId($allowedImages,$pId,$defaultImageId){
		 $allowedImages = intval($allowedImages);
		 $imageData = $this->getProductImagesToDelete($pId,false);
		 if($defaultImageId>=$imageData[$allowedImages]['image_id']){
			$indexId= intval($allowedImages)-1;
			return $imageData[$indexId]['image_id'];
		} else{			
		  return $imageData[$allowedImages]['image_id'];
		}
	}
	
	function getProductImagesToDelete($id){
		$id = intval($id);
		//if($id>0!=true) return array();
		$add_criteria['product'] = $id;
		$srch = self::search_product_images($add_criteria,'',false);
		$srch->addOrder('image_id','asc');
		$srch->doNotLimitRecords(true);
		$srch->doNotCalculateRecords(true);
		$sql = $srch->getQuery();
		$rs = $this->db->query($sql);
		$row = $this->db->fetch_all($rs);
		if($row==false) return array();
		else return $row;
	}
	
	function getImagesCountByProduct($products=null){
		$srch = new SearchBase('tbl_product_images', 'tpi');
		$srch->addCondition('tpi.image_prod_id', 'IN', explode(",",$products));
		//$srch->addGroupBy('tpi.image_prod_id');
		$srch->addOrder('tpi.image_prod_id','ASC');
		$srch->addMultipleFields(array('image_prod_id','image_file'));
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount(); 
		$arr_products=$this->db->fetch_all($rs);
		$productImageArray = array();
		foreach($arr_products as $sn=>$val){
			$productImageArray[$val['image_prod_id']][]= $val['image_file'];
		}
		return $productImageArray; 
	}
	
	
	function getProductDownloads($product_id) {
		$product_attribute_data = array();
		$srch = new SearchBase('tbl_product_files', 'tpf');
		$srch->addCondition('pfile_product_id', '=', intval($product_id));
		$srch->addOrder('pfile_id', 'asc');
		$rs = $srch->getResultSet();
      	$row = $this->db->fetch_all($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function addProductDownloads($data){
		$record = new TableRecord('tbl_product_files');
		$record->assignValues($data);
		if($record->addNew()){
			$this->product_file_id=$record->getId();
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return $this->product_file_id;
	}
	
	function getProductDownloadFileData($id){
		$id = intval($id);
        if($id>0!=true) return array();
       	$add_criteria['id'] = $id;
        $srch = self::search_product_files($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function getProductDigitalFiles($id,$add_criteria=array()){
		$id = intval($id);
       	$add_criteria['product'] = $id;
        $srch = self::search_product_files($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch_all($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function search_product_files($criteria, $default='',$includeOrder= true) {
		$srch = new SearchBase('tbl_product_files', 'tpf');
        foreach($criteria as $key=>$val) {
            switch($key) {
         	   case 'id':
            	    $srch->addCondition('tpf.pfile_id', '=', intval($val));
    	            break;
			   case 'product':
					$srch->addCondition('tpf.pfile_product_id', '=', intval($val));
                break;
			   												
            }
        }
		if ($default==true)
			$srch->setPageSize(1);
		
		if($includeOrder){
			$srch->addOrder('tpf.pfile_id','desc');
		}
		return $srch;
    }
	
	function deleteProductDownloadFile($pfile_id){
		$pfile_id = intval($pfile_id);
		if($pfile_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->deleteRecords('tbl_product_files', array('smt'=>'pfile_id=?', 'vals'=>array($pfile_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
}