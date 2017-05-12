<?php
class Product {
	function __construct(){
		$this->db = Syspage::getdb();
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
	
	function getProducts($criteria) {
		foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
		$srch->addOrder('tp.prod_status', 'desc');
		$srch->addOrder('prod_id','desc');
		//echo($srch->getquery());
		//die();
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return false;
		}
		//return $this->db->fetch_all($rs);
        $arr_products=$this->db->fetch_all($rs);
		$pObj = new Products();
		foreach($arr_products as $sn=>$val){
			$special = false;
			$product_specials=$this->getProductSpecials($val["prod_id"]);
			foreach ($product_specials  as $product_special) {
				if (($product_special['pspecial_start_date'] == '0000-00-00' || strtotime($product_special['pspecial_start_date']) < time()) && ($product_special['pspecial_end_date'] == '0000-00-00' || strtotime($product_special['pspecial_end_date']) > time())) {
					$special = $product_special['pspecial_price'];
					break;
				}
			}
			$commission_percentage = $pObj->getProductCommission($val["prod_id"]);
			$product_price=$special>0?$special:$val['prod_sale_price'];
			$commission=min(round(($product_price)*$commission_percentage/100,2),Settings::getSetting("CONF_MAX_COMMISSION"));
			$val["commission"]=$commission;
			$val["special"]=$special;
			$products[] = $val;
		}
		return $products;
    }
	
	
	
	
   
    
    function search($criteria) {
		$srch = new SearchBase('tbl_prod_reviews', 'tpr');
		$srch->joinTable('tbl_users', 'INNER JOIN', 'tpr.review_user_id=tu.user_id and tu.user_is_deleted=0', 'tu');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tpr.review_prod_id');
		$srch->addCondition('tpr.review_is_deleted', '=', 0);
		$srch->addMultipleFields(array('tpr.review_prod_id',"ROUND(AVG(review_rating),1) as prod_rating","count(review_id) as totReviews"));
		$qry_prod_reviews = $srch->getQuery();
		
		$srch=new SearchBase('tbl_products','tp');
		$srch->joinTable('tbl_prod_details', 'LEFT JOIN', 'tp.prod_id=tpd.prod_id', 'tpd');
		$srch->joinTable('tbl_categories', 'LEFT JOIN', 'tp.prod_category=tc.category_id', 'tc');
		$srch->joinTable('tbl_shops', 'LEFT JOIN', 'tp.prod_shop=ts.shop_id and ts.shop_is_deleted=0', 'ts');
		$srch->joinTable('tbl_users', 'LEFT JOIN', 'ts.shop_user_id=tu.user_id', 'tu');
		$srch->joinTable('tbl_product_brands', 'LEFT JOIN', 'tp.prod_brand=tpb.brand_id and tpb.brand_is_deleted=0', 'tpb');
		$srch->joinTable('tbl_countries', 'LEFT JOIN', 'tp.prod_shipping_country=tco.country_id', 'tco');
		$srch->joinTable('(' . $qry_prod_reviews . ')', 'LEFT OUTER JOIN', 'tp.prod_id = tqpr.review_prod_id', 'tqpr');
		$srch->addCondition('tp.prod_is_deleted','=',0);
		$srch->addCondition('ts.shop_is_deleted','=',0);
		$srch->addCondition('tu.user_is_deleted','=',0);
		
		if (!isset($criteria["pagesize"]))
			$srch->doNotLimitRecords(true);
		
		$srch->addMultipleFields(array('DISTINCT tp.*','tpb.brand_id','tpb.brand_name','tpb.brand_status','ts.shop_id','ts.shop_name',
		'ts.shop_description','ts.shop_title','tu.user_id','tu.user_username','tu.user_name','tu.user_email','tu.user_phone','tco.country_name as prod_ship_from','prod_length','prod_length_class', 'prod_width', 'prod_height','prod_weight','prod_weight_class','prod_long_desc', 'prod_youtube_video','prod_meta_title', 'prod_meta_keywords', 'prod_meta_description', 'prod_featuered','prod_ship_free', 'prod_tax_free','prod_available_date','tqpr.*','COALESCE(totReviews,0) as totReviews',"(SELECT pspecial_price  FROM tbl_product_specials tps WHERE tps.pspecial_product_id = tp.prod_id AND ((tps.pspecial_start_date = '0000-00-00' OR tps.pspecial_start_date < NOW()) AND (tps.pspecial_end_date = '0000-00-00' OR tps.pspecial_end_date > NOW())) ORDER BY tps.pspecial_priority ASC, tps.pspecial_price ASC LIMIT 1) AS special"));
		
        foreach($criteria as $key=>$val) {
			if(strval($val)=='') continue;
            switch($key) {
            case 'id':
                $srch->addCondition('tp.prod_id', '=', intval($val));
                break;
			case 'type':
                $srch->addCondition('tp.prod_type', '=', intval($val));
                break;
			case 'name':
                $srch->addCondition('tp.prod_name', 'LIKE', '%'.$val.'%');
                break;		
			case 'shop':
                $cnd=$srch->addCondition('tp.prod_shop', '=', intval($val));
                break;
			case 'added_by':
				$srch->addCondition('tp.prod_added_by', '=',$val);
                break;		
		     case 'brand':
			 	if (is_array($val))
	                $srch->addCondition('tp.prod_brand', 'IN', $val);
					else	
					$srch->addCondition('tp.prod_brand', '=', intval($val));
            break;	
			case 'sku':
                $srch->addCondition('tp.prod_sku', '=',$val);
                break;
			case 'property':
                $srch->addCondition('tpd.'.$val, '=', 1);
                break;
			case 'category':
				if ($val!=""){
					$cats=array_merge(array($val),array_keys(Categories::getProdClassifiedCategoriesAssocArray($val)));
					$srch->addCondition('tp.prod_category', 'IN',$cats);
				}	
			break;
			case 'active':
				if ($val!="")
	                $cnd=$srch->addCondition('tp.prod_status', '=', $val);
             break;
			case 'minprice':
                $cnd=$srch->addHaving("IFNULL(`special`,`prod_sale_price`)",">=",$val);
                break;
			case 'maxprice':
				$cnd=$srch->addHaving("IFNULL(`special`,`prod_sale_price`)"," <= ",$val);
                break;
			case 'date_from':
                $srch->addCondition('tp.prod_added_on', '>=', $val. ' 00:00:00');
                break;
			case 'date_to':
                $srch->addCondition('tp.prod_added_on', '<=', $val. ' 23:59:59');
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
			case 'sort_by':
					switch($val) {
		            	case 'display':
        	    	    	$srch->addOrder('tp.prod_display_order',$criteria['sort_order']);
	            	    break;
						case 'newproduct':
        	    	    	$srch->addOrder('tp.prod_id',$criteria['sort_order']);
	            	    break;
						case 'product':
        	    	    	$srch->addOrder('tp.prod_name',$criteria['sort_order']);
	            	    break;
						case 'price':
							$srch->addOrder('IFNULL(special,prod_sale_price)',$criteria['sort_order']);
	            	    break;
						case 'bestsellers':
        	    	    	$srch->addOrder('tp.prod_sold_count',$criteria['sort_order']);
	            	    break;
						case 'featured':
        	    	    	$srch->addOrder('tpd.prod_featuered',$criteria['sort_order']);
	            	    break;
						case 'newarrivals':
							$srch->addOrder('tp.prod_added_on',$criteria['sort_order']);
						break;
						case 'rating':
        	    	    	$srch->addOrder('tqpr.prod_rating',$criteria['sort_order']);
	            	    break;
					}
			break;
																
            }
        }
		//$srch->addGroupBy('tp.prod_id');
        return $srch;
    }
	
	function getProductSpecials($product_id) {
		$product_special_data = array();
		$rs = $this->db->query("SELECT * FROM tbl_product_specials WHERE pspecial_product_id = '" . (int)$product_id . "' order by pspecial_priority,pspecial_price");
		$product_special_data=$this->db->fetch_all($rs);
		return $product_special_data;
	}
	
	
	
}