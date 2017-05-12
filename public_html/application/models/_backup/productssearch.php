<?php
class Productssearch {
	function __construct(){
		$this->db = Syspage::getdb();
		$criteria = array();
		if (!Settings::getSetting("CONF_ALLOW_USED_PRODUCTS_LISTING") && ($criteria['is_admin_call']!=1)){
			$criteria['condition'] = "N";
		}
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
	
	function getData($id,$criteria=array()) {
        $id = intval($id);
        if($id>0!=true) return array();
	       	$criteria['id'] = $id;
			
		$criteria = array_merge($criteria,(array)$this->criteria);
        $srch = self::search($criteria);
		$srch->joinTable('tbl_url_alias', 'LEFT OUTER JOIN', 'tp.prod_id = REPLACE(tua.url_alias_query,"products_id=","")', 'tua');
		$srch->addFld('tua.url_alias_keyword as seo_url_keyword');
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $sql = $srch->getQuery();
		//print($sql."<br/><br/>");
        $rs = $this->db->query($sql);
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function getProductsSearchedBrands($criteria,$extended_criteria) {
		$criteria = array_merge($criteria,(array)$this->criteria);
		$criteria = array_diff_key($criteria,array_flip(array("pagesize","sort")));
        $srch_a = self::search($criteria,array('tpb.brand_id','tpb.brand_name','count(*) as productsCount','tp.prod_sale_price'));
		$srch_a->doNotCalculateRecords();
		$srch_a->addCondition('tpb.brand_status', '=',1);
		$srch_a->addGroupBy('prod_brand');
		$srch_a->addOrder('brand_name');
		$sql_query_a = $srch_a->getquery();
		
		
		
		$srch_b = self::search(array_merge($criteria,$extended_criteria),array('tpb.brand_id','tpb.brand_name','count(*) as productsCount','tp.prod_sale_price'));
		$srch_b->doNotCalculateRecords();
		$srch_b->addCondition('tpb.brand_status', '=',1);
		$srch_b->addGroupBy('prod_brand');
		$srch_b->addOrder('brand_name');
		$sql_query_b = $srch_b->getquery();
		$sql_combined_query="SELECT A.*,If(B.productsCount is not null, '', 1) as is_disabled from ($sql_query_a) A LEFT JOIN ($sql_query_b) B on A.brand_id=B.brand_id";
		
		//die($sql_combined_query);
		$rs = $this->db->query($sql_combined_query);
		$arr_brands=$this->db->fetch_all($rs);
		return $arr_brands;
	}
	
	function getProductsSearchedCategories($criteria) {
		$criteria = array_merge($criteria,(array)$this->criteria);
		$criteria = array_diff_key($criteria,array_flip(array("pagesize","sort")));
        $srch = self::search($criteria,array('distinct tc.category_id','tc.category_name','count(*) as productsCount','tp.prod_sale_price'));
		$srch->addCondition('tc.category_status', '=',1);
		$srch->addGroupBy('tc.category_id');
		$srch->addOrder('category_name');
		$rs = $srch->getResultSet();
		$arr_categories=$this->db->fetch_all($rs);
		return $arr_categories;
	}
		
	function getProductsCount($criteria) {
		$criteria = array_merge($criteria,(array)$this->criteria);
		$criteria = array_diff_key($criteria,array_flip(array("pagesize","sort")));
        $srch = self::search($criteria);
		/*if (!empty($criteria['brand']))
			die($srch->getQuery());*/
		//die($srch->getquery());
		$rs = $srch->getResultSet();
		return $srch->recordCount();
	}
	
	function getProducts($criteria,$do_not_count=false) {
		$criteria = array_merge($criteria,(array)$this->criteria);
        $srch = self::search($criteria);
		if ($do_not_count)
		$srch->doNotCalculateRecords(true);
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return false;
		}
		//die($srch->getquery());
        $arr_products=$this->db->fetch_all($rs);
		if ($criteria["donotspecial"] ){
			return $arr_products;
		}
			
		foreach($arr_products as $sn=>$val){
			$special = false; $discount = false;
			$product_special=$this->getProductSpecials($val["prod_id"]);
			if ($product_special) {
				$special = $product_special['pspecial_price'];
			}
			$product_discount=$this->getProductSpecials($val["prod_id"]);
			if ($product_discount) {
				$discount = $product_discount['pdiscount_price'];
			}
			$val["special"]=$special;
			$val["discount"]=$discount;
			
			$commission_percentage = $this->getProductCommission($val["prod_id"]);
			$product_price=$special>0?$special:$val['prod_sale_price'];
			$commission=min(round(($product_price)*$commission_percentage/100,2),Settings::getSetting("CONF_MAX_COMMISSION"));
			$val["commission"]=$commission;
			$products[] = $val;
		}
		return $products;
    }
	
	function getProductCommission($product_id){
		$product = $this->getData($product_id);
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
    
    function search($criteria,$select_fields) {
		
		$srch = new SearchBase('tbl_user_transactions', 'txn');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('txn.utxn_user_id');
		$srch->addMultipleFields(array('txn.utxn_user_id',"SUM(utxn_credit-utxn_debit) as userBalance"));
		$qry_user_balance = $srch->getQuery();
		
		
		$srch=new SearchBase('tbl_promotions','tpr');
		$srch->joinTable('tbl_users', 'LEFT JOIN', 'tpr.promotion_user_id=tu.user_id', 'tu');
		$srch->joinTable('(' . $qry_user_balance . ')', 'LEFT OUTER JOIN', 'tu.user_id = tqub.utxn_user_id', 'tqub');
		$srch->addCondition('tpr.promotion_type','=',1);
		$srch->addCondition('tpr.promotion_is_deleted','=',0);
		$srch->addCondition('tpr.promotion_status','=',1);
		$srch->addCondition('tpr.promotion_is_approved','=',1);
		$srch->addCondition('userBalance','>=',Settings::getSetting("CONF_MIN_WALLET_BALANCE"));
		$srch->addDirectCondition('(tpr.promotion_start_date = "0000-00-00" OR tpr.promotion_start_date < NOW()) AND (tpr.promotion_end_date = "0000-00-00" OR tpr.promotion_end_date > NOW()) AND CAST("'.date('H:i:s').'" AS time) BETWEEN `promotion_start_time` AND `promotion_end_time`'); 
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tpr.promotion_product_id');
		$srch->addMultipleFields(array('tpr.promotion_id',"tpr.promotion_product_id",'tpr.promotion_cost','tpr.promotion_resumption_date'));
		$qry_promotion_products = $srch->getQuery();
		//die($qry_promotion_products);
		
		$markUp=0;
		$srch=new SearchBase('tbl_products','tp');
		$srch->joinTable('tbl_prod_details', 'LEFT JOIN', 'tp.prod_id=tpd.prod_id', 'tpd');
		$srch->joinTable('tbl_product_brands', 'LEFT JOIN', 'tp.prod_brand=tpb.brand_id and tpb.brand_is_deleted=0', 'tpb');
		$srch->joinTable('tbl_shops', 'LEFT JOIN', 'tp.prod_shop=ts.shop_id and ts.shop_is_deleted=0', 'ts');
		$srch->joinTable('tbl_categories', 'LEFT JOIN', 'tp.prod_category=tc.category_id', 'tc');
		$srch->joinTable('tbl_users', 'INNER JOIN', 'ts.shop_user_id=tu.user_id', 'tu');
		$srch->joinTable('(' . $qry_promotion_products . ')', 'LEFT JOIN', 'tp.prod_id = tpr.promotion_product_id', 'tpr');
		$srch->addCondition('tp.prod_is_deleted','=',0);
		if (!isset($criteria["pagesize"]))
			$srch->doNotLimitRecords(true);
		
			
		if (count($select_fields)>0){
			foreach ($select_fields as $sfield){
				$srch->addMultipleFields(array($sfield));
			}
		}else{
			$srch->addMultipleFields(array('DISTINCT tp.*','tpb.brand_id','tpb.brand_name','ts.shop_id','ts.shop_name','ts.shop_title','IF(prod_stock >0, "1", "0" ) as available','promotion_id'));
		}
		
		$srch->addFld("(SELECT pspecial_price  FROM tbl_product_specials tps WHERE tps.pspecial_product_id = tp.prod_id AND ((tps.pspecial_start_date = '0000-00-00' OR tps.pspecial_start_date < NOW()) AND (tps.pspecial_end_date = '0000-00-00' OR tps.pspecial_end_date > NOW())) ORDER BY tps.pspecial_priority ASC, tps.pspecial_price ASC LIMIT 1) AS special");
		
        foreach($criteria as $key=>$val) {
			if(strval($val)=='') continue;
			
			//if (in_array($key,array("price","special","min","max","price_range")) || (($key=="sort") && (in_array($val,array('plth','phtl'))))){
			
			//}
			
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
			case 'available_date':
				$srch->addDirectCondition('tpd.prod_available_date <= NOW()');
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
			case 'filters':
				$srch->joinTable('tbl_product_filter', 'LEFT JOIN', 'tp.prod_id=tpf.product_id', 'tpf');
			 	if (is_array($val))
	                $srch->addCondition('tpf.filter_id', 'IN', $val);
				else	
					$srch->addCondition('tpf.filter_id', '=', intval($val));
                break;			
			 case 'brand':
			 	if (is_array($val))
	                $srch->addCondition('tp.prod_brand', 'IN', $val);
					else	
					$srch->addCondition('tp.prod_brand', '=', intval($val));
				
                break;
			 case 'tags':
				$srch->joinTable('tbl_product_to_tags', 'LEFT JOIN', 'tp.prod_id=tpt.pt_product_id', 'tpt');
			 	if (is_array($val) && !empty($val))
	                $srch->addCondition('tpt.pt_tag_id', 'IN', (array)$val);
				elseif (is_numeric($val))	
					$srch->addCondition('tpt.pt_tag_id', '=', intval($val));
                break;		
			case 'condition':
                $srch->addCondition('tp.prod_condition', 'IN', (array)$val);
                break;	
			case 'sku':
                $srch->addCondition('tp.prod_sku', '=',$val);
                break;
			case 'paypal_verified':
                $srch->addCondition('ts.shop_paypal_account_verified', '=','1');
                break;	
			case 'property':
				if (!is_array($val)){
	                if (in_array($val,array('prod_featuered','prod_ship_free'))){
		                $srch->addCondition('tpd.'.$val, '=', 1);
					}
				}else{
					foreach($val as $skey=>$sval){
						$srch->addCondition('tpd.'.$sval, '=', 1);
					}
				}
                break;
			case 'category':
				if ((int) $val > 0){
					$category_id = (int)$val;
					$cats=array_merge(array($category_id),array_keys(Categories::getProdClassifiedCategoriesAssocArray($category_id)));
					$srch->addCondition('tp.prod_category', 'IN',$cats);
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
						$str .= " Having ((IFNULL(`special`,`prod_sale_price`)) >= $min AND (IFNULL(`special`,`prod_sale_price`)) <= $max) $condition ";
					endforeach;
				$str .= ")";
					$srch->addDirectCondition($str);	
                break;
			case 'out_of_stock':
				if ($val==1)
					$srch->addCondition('tp.prod_stock', '>',0);
				break;	
			case 'active':
	                $cnd=$srch->addCondition('tp.prod_status', '=',1);
					$cnd->attachCondition('tp.prod_is_expired', '=',0,'AND');
            break;
			case 'expired':
	                $cnd=$srch->addCondition('tp.prod_is_expired', '=',1);
            break;
			case 'pending':
	                $cnd=$srch->addCondition('tp.prod_status', '=',0);
					$cnd->attachCondition('tp.prod_is_expired', '=',0,'AND');
            break;
			case 'minprice':
			case 'min':
                $cnd=$srch->addHaving("IFNULL(`special`,`prod_sale_price`)",">=",$val);
                break;
			case 'maxprice':
			case 'max':
                //$cnd=$srch->addDirectCondition("HAVING (IFNULL(`special`,`prod_sale_price`) <= $val)");
				$cnd=$srch->addHaving("IFNULL(`special`,`prod_sale_price`)"," <= ",$val);
                break;
			case 'date_from':
                $srch->addCondition('tp.prod_published_on', '>=', $val. ' 00:00:00');
                break;
			case 'date_to':
                $srch->addCondition('tp.prod_published_on', '<=', $val. ' 23:59:59');
                break;
			case 'favorite':
				$srch->joinTable('tbl_user_favourite_products', 'LEFT JOIN', 'tp.prod_id=tf.ufp_prod_id and tf.ufp_user_id='.(int)$val, 'tf');
				$srch->addFld("IF(tf.ufp_prod_id>0,'1','0') as favorite");
			break;
			case 'name':
                $srch->addCondition('tp.prod_name', 'LIKE', '%'.$val.'%');
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
									'tc.category_name',
									);
						$strKeys = "";
						foreach($arr_columns as $column){
							foreach($arr_keywords as $keyword){
								$strKeys.=(($strKeys=="")?"":" + ")." (case when INSTR(".$column.",".$this->db->quoteVariable(trim($keyword)).")>0 then 1 else 0 END) ";
							}
						}
						$srch->addDirectCondition($strKeys);
						$srch->addFld($strKeys.' as Rank');
						//$srch->addOrder('Rank','desc');
				}	
                break;
				
			case 'pagesize':
				$srch->setPageSize($val);
				break;
			case 'page':
				$srch->setPageNumber($val);
				break;
			case 'sort':
					$val = in_array($criteria['sort'],array('dlth','dhtl','rece','feat','best','plth','phtl','rate','relv','rand'))?$criteria['sort']:"best";
					$srch->addOrder('available','desc');
					$srch->addOrder('promotion_resumption_date','desc');
					switch($val) {
						case 'dlth':
        	    	    	$srch->addOrder('tp.prod_display_order','asc');
							$srch->addOrder('prod_id','desc');
	            	    break;
						case 'dhtl':
        	    	    	$srch->addOrder('tp.prod_display_order','desc');
							$srch->addOrder('prod_id','desc');
	            	    break;
		            	case 'rece':
        	    	    	$srch->addOrder('tp.prod_id','desc');
	            	    break;
						case 'feat':
        	    	    	$srch->addOrder('tpd.prod_featuered','desc');
	            	    break;
						case 'best':
        	    	    	$srch->addOrder('tp.prod_sold_count','desc');
	            	    break;
						case 'plth':
        	    	    	$srch->addOrder('IFNULL(special,prod_sale_price)','asc');
	            	    break;
						case 'phtl':
        	    	    	$srch->addOrder('IFNULL(special,prod_sale_price)','desc');
	            	    break;
						case 'rate':
        	    	    	$srch->addOrder('tqpr.prod_rating','desc');
	            	    break;
						case 'relv':
							if (!empty($criteria['keyword'])){
        	    	    		$srch->addOrder('Rank','desc');
							}
	            	    break;
						case 'rand':
							$srch->addOrder('rand()');
						break;	
					}
																
            }
        }
        return $srch;
    }
	
	
	public function getFeaturedProducts() {
		$featured_products = array();
		$limit = Settings::getSetting("CONF_FEATURED_ITEMS_HOME_PAGE");
		if(isset($limit) && $limit>0){
			$uObj= new User();
			$user_id = $uObj->getLoggedUserId();
				$featured_products = $this->getProducts(array("status"=>1,"property"=>"prod_featuered","sort"=>"rand","pagesize"=>$limit,"favorite"=>$user_id),true);
		}
		return $featured_products;
	}
	
	function getRecentlyViewedProducts($user_id=0){
		$recently_viewed_products = array();
		if(isset($_COOKIE['ProductVisitorCookie'])){
			foreach(explode("~",$_COOKIE['ProductVisitorCookie']) as $cookie_product){
       			$product = $this->getData(base64_decode($cookie_product),array("status"=>1,"favorite"=>$user_id));
				if ($product){
					$recently_viewed_products[]=$product;
				}
			}
		}
		return $recently_viewed_products;
	}
	
	public function getPPCProducts() {
		$PPC_products = array();
		$limit = Settings::getSetting("CONF_PPC_PRODUCTS_HOME_PAGE");
		if(isset($limit) && $limit>0){
			$uObj= new User();
			$user_id = $uObj->getLoggedUserId();
			$prmObj=new Promotions();
			$arr = array("front"=>1,"status"=>1,"pagesize"=>$limit+10,"order_by"=>'random');
			$promotion_products=$prmObj->getPromotions($arr);
			foreach($promotion_products as $pkey=>$pval){
				$product = $this->getData($pval["promotion_product_id"],array("status"=>1,"favorite"=>$user_id));
				if ($product)
					$PPC_products[] = $product;
			}
		}
		return $PPC_products;
	}
	
	function getProductSpecials($product_id) {
		$product_special_data = array();
		$rs = $this->db->query("SELECT pspecial_price  FROM tbl_product_specials tps WHERE tps.pspecial_product_id = '" . (int)$product_id . "' AND ((tps.pspecial_start_date = '0000-00-00' OR tps.pspecial_start_date < NOW()) AND (tps.pspecial_end_date = '0000-00-00' OR tps.pspecial_end_date > NOW())) ORDER BY tps.pspecial_priority ASC, tps.pspecial_price ASC LIMIT 1");
		$product_special_data=$this->db->fetch($rs);
		return $product_special_data;
	}
	
	function getProductQtyDiscounts($product_id) {
		$product_special_data = array();
		$rs = $this->db->query("SELECT pdiscount_price FROM tbl_product_discounts tpd WHERE tpd.pdiscount_product_id = '" . (int)$product_id . "' AND tpd.pdiscount_qty = '1' AND ((tpd.pdiscount_start_date = '0000-00-00' OR tpd.pdiscount_start_date < NOW()) AND (tpd.pdiscount_end_date = '0000-00-00' OR tpd.pdiscount_end_date > NOW())) ORDER BY tpd.pdiscount_priority ASC, tpd.pdiscount_price ASC LIMIT 1");
		$product_special_data=$this->db->fetch_all($rs);
		return $product_special_data;
	}
	
	function getProductsMinMaxPrice($criteria){
		$criteria = array_diff_key($criteria,array_flip(array("pagesize","sort")));
		$price_range=array();
		$srch = self::search($criteria,array('MIN(prod_sale_price) as min_price','MAX(prod_sale_price) as max_price'));
		$rs = $srch->getResultSet();
		$min_max_price=$this->db->fetch($rs);
		return $min_max_price;
	}
	
	
	function product_additional_info($arr){
		$arr['prod_url']=Utilities::generateUrl('products','view',array($arr["prod_id"],'MEDIUM'));
		$arr['prod_image_url']=Utilities::generateUrl('image','product_image',array($arr["prod_id"],'MEDIUM'));
		$arr['prod_shop_url']=Utilities::generateUrl('shops','view',array($arr["prod_shop"]));
		$arr['prod_list_url']=Utilities::generateUrl('common', 'view_lists',array($arr["prod_id"]));
		$arr['prod_out_of_stock']=$arr['prod_stock']>0?false:true;
		$arr['prod_promotion_id']=$arr['promotion_id'];
		$arr['prod_special']=Utilities::displayMoneyFormat($arr['special']);
		$price = $arr['discount']?$arr['discount']:$arr['prod_sale_price'];
		$arr['prod_price']=Utilities::displayMoneyFormat($price);
		$arr['prod_favorite']=$arr["favorite"]?1:"";
		$arr['prod_short_name']=subStringByWords($arr["prod_name"],66);
		
		return $arr;
		  //return($v*$v);
	}
	
	
}