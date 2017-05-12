<?php
class Product extends SearchBase {
	function __construct($config = ""){
		$settings = array(
				'pagesize' => '30', // Pagesize
				'page' => 1, // Pagesize
				'countRecords' => true,
		    );
			if (!empty($config)) {
				foreach ($config as $key => $val) {
					if (isset($val)) {
						$settings[$key] = $val;
					}
				}
			}
		parent::__construct('tbl_products', 'tp');
		$this->joinTable('tbl_shops', 'LEFT JOIN', 'tp.prod_shop=ts.shop_id', 'ts');
		$this->joinTable('tbl_users', 'LEFT JOIN', 'ts.shop_user_id=tu.user_id', 'tu');
		$this->addCondition('tp.prod_is_deleted','=',0);
		$this->addCondition('ts.shop_is_deleted','=',0);
		$this->addCondition('tu.user_is_deleted', '=', 0,'AND');
		$this->pagesize = $settings['pagesize'];
		$this->page = $settings['page'];
		$this->countRecords = $settings['countRecords'];
		$this->addMultipleFields(array('tp.*','ts.shop_id','ts.shop_name','ts.shop_title'));
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
	
	public function joinWithDetailTable($select_columns=array()){
		$this->joinTable('tbl_prod_details', 'LEFT JOIN', 'tp.prod_id=tpd.prod_id', 'tpd');
		$this->addMultipleFields((array)$select_columns);
	}
	
	public function joinWithBrandsTable($select_columns=array()){
		$this->joinTable('tbl_product_brands', 'LEFT JOIN', 'tp.prod_brand=tpb.brand_id', 'tpb');
		$this->addMultipleFields((array)$select_columns);
	}
	
	/*public function joinWithShopsTable($select_columns=array()){
		$this->joinTable('tbl_shops', 'LEFT JOIN', 'tp.prod_shop=ts.shop_id', 'ts');
		$this->addMultipleFields((array)$select_columns);
	}*/
	
	public function joinWithCategoryTable($select_columns=array()){
		$this->joinTable('tbl_categories', 'LEFT JOIN', 'tp.prod_category=tc.category_id', 'tc');
		$this->addMultipleFields((array)$select_columns);
	}
	
	public function joinWithUrlAliasTable(){
		$this->joinTable('tbl_url_alias', 'LEFT OUTER JOIN', 'tp.prod_id = REPLACE(tua.url_alias_query,"products_id=","")', 'tua');
		$this->addFld('tua.url_alias_keyword as seo_url_keyword');
	}
	
	public function addSpecialPrice(){
		$current_date = date('Y-m-d');
		$this->addFld("(SELECT pspecial_price  FROM tbl_product_specials tps WHERE tps.pspecial_product_id = tp.prod_id AND ((tps.pspecial_start_date = '0000-00-00' OR tps.pspecial_start_date <= '".$current_date."') AND (tps.pspecial_end_date = '0000-00-00' OR tps.pspecial_end_date >= '".$current_date."')) ORDER BY tps.pspecial_priority ASC, tps.pspecial_price ASC LIMIT 1) AS special");
		$this->addFld("(SELECT pdiscount_price FROM tbl_product_discounts tpd WHERE tpd.pdiscount_product_id = tp.prod_id AND tpd.pdiscount_qty = '1' AND ((tpd.pdiscount_start_date = '0000-00-00' OR tpd.pdiscount_start_date <= '".$current_date."') AND (tpd.pdiscount_end_date = '0000-00-00' OR tpd.pdiscount_end_date >= '".$current_date."')) ORDER BY tpd.pdiscount_priority ASC, tpd.pdiscount_price ASC LIMIT 1) AS discount");
		/*$this->addFld("(SELECT pspecial_price  FROM tbl_product_specials tps WHERE tps.pspecial_product_id = tp.prod_id AND ((tps.pspecial_start_date = '0000-00-00' OR tps.pspecial_start_date <= NOW()) AND (tps.pspecial_end_date = '0000-00-00' OR tps.pspecial_end_date >= NOW())) ORDER BY tps.pspecial_priority ASC, tps.pspecial_price ASC LIMIT 1) AS special");
		$this->addFld("(SELECT pdiscount_price FROM tbl_product_discounts tpd WHERE tpd.pdiscount_product_id = tp.prod_id AND tpd.pdiscount_qty = '1' AND ((tpd.pdiscount_start_date = '0000-00-00' OR tpd.pdiscount_start_date <= NOW()) AND (tpd.pdiscount_end_date = '0000-00-00' OR tpd.pdiscount_end_date >= NOW())) ORDER BY tpd.pdiscount_priority ASC, tpd.pdiscount_price ASC LIMIT 1) AS discount");*/
		//$this->addFld("IFNULL(IFNULL(`special`,`discount`),`prod_sale_price`) abc");
		
	}
	
	
	
	public function joinWithProductTags(){
		$srchTag = new SearchBase('tbl_product_to_tags', 'tptags');
		$srchTag->joinTable('tbl_product_tags', 'LEFT JOIN', 'tptags.pt_tag_id=ptags.ptag_id', 'ptags');
		$srchTag->doNotCalculateRecords();
		$srchTag->doNotLimitRecords();
		$srchTag->addGroupBy('tptags.pt_product_id');
		$srchTag->addMultipleFields(array('tptags.pt_product_id','GROUP_CONCAT(ptags.`ptag_name`) as product_tags'));
		$qry_tag_products = $srchTag->getQuery();
		$this->joinTable('(' . $qry_tag_products . ')', 'LEFT OUTER JOIN', 'tp.prod_id = tqtp.pt_product_id', 'tqtp');
	}
	
	
	
	public function applyConditions($conditions=array()){
		foreach($conditions as $key=>$val) {
			if(strval($val)=='') continue;
            	switch($key) {
	            case 'id':
    	            $this->addCondition('tp.prod_id', '=', intval($val));
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
			    case 'brand':
				 	if (is_array($val))
	            	    $this->addCondition('tp.prod_brand', 'IN', $val);
					else	
						$this->addCondition('tp.prod_brand', '=', intval($val));
            	break;	
				case 'sku':
                	$this->addCondition('tp.prod_sku', '=',$val);
                break;
				case 'property':
    	            $this->addCondition('tpd.'.$val, '=', 1);
                break;
				case 'category':
					if ($val!=""){
						$cats=array_merge(array($val),array_keys(Categories::getProdClassifiedCategoriesAssocArray($val)));
						$this->addCondition('tp.prod_category', 'IN',$cats);
				}	
				break;
				case 'active':
					if ($val!="")
	            	    $this->addCondition('tp.prod_status', '=', $val);
             	break;
				case 'minprice':
					$this->addSpecialPrice();
    	            $this->addHaving("COALESCE(`special`,`discount`,`prod_sale_price`)",">=",$val);
        	    break;
				case 'maxprice':
					$this->addSpecialPrice();
					$this->addHaving("COALESCE(`special`,`discount`,`prod_sale_price`)"," <= ",$val);
                break;
				case 'date_from':
                	$this->addCondition('tp.prod_added_on', '>=', $val. ' 00:00:00');
                break;
				case 'date_to':
                	$this->addCondition('tp.prod_added_on', '<=', $val. ' 23:59:59');
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
				case 'sort':
					$val = in_array($conditions['sort'],array('dlth','dhtl','rece','relv'))?$conditions['sort']:"best";
					$this->addOrder('tp.prod_status','desc');
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
						case 'relv':
        	    	    	$this->addOrder('Rank','desc');
	            	    break;
					}
				break;	
				
            }
        }
		//die($this->getquery());
	}
	
	function getData($id) {
        $id = intval($id);
        if($id>0!=true) return array();
	       	$criteria['id'] = $id;
		
		$this->applyConditions(array('id'=>$id));
        $this->doNotLimitRecords(true);
        $this->doNotCalculateRecords(true);
        $rs = $this->getResultSet();
        return $this->db->fetch($rs);
	}
	
	function getProducts($conditions=array(),$commissions=false) {
		$this->applyConditions($conditions);
		$this->setPageSize($this->pagesize);
		if (!$this->countRecords)
		$this->doNotCalculateRecords();
		$this->setPageNumber($this->page);
        $rs = $this->getResultSet();
		$this->total_records = $this->recordCount();
		$this->total_pages = $this->pages();
		$products = $this->db->fetch_all($rs);
		$arr_products = $products;
		if ($commissions==true){
			$arr_products = array();
			foreach($products as $sn=>$val){
				$commission_percentage = $this->getProductCommission($val["prod_id"]);
				$special = isset($val['special'])?$val['special']:0;
				$product_price=$special>0?$special:$val['prod_sale_price'];
				$commission=min(round(($product_price)*$commission_percentage/100,2),Settings::getSetting("CONF_MAX_COMMISSION"));
				$val["commission"]=$commission;
				$arr_products[] = $val;
			}
		}
        return $arr_products;
	}
	
	function getProductCommission($product_id){
		$pObj=new self();
		$product = $pObj->getData($product_id);
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
	
	
	
	
	
}