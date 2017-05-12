<?php
class Reports extends Model {
	protected $db;
	function __construct() {
		$this->db = Syspage::getdb();
    }
	
	function getTotalPages(){
		return $this->total_pages;
	}
	
	function getTotalRecords(){
		return $this->total_records;
	}
	
	function getSalesReportData($criteria){
        $srch = new SearchBase('tbl_orders', 'tord');
		$srch->joinTable('tbl_order_products', 'LEFT JOIN', 'tord.order_id =torp.opr_order_id', 'torp');
		$srch->addCondition('tord.order_payment_status', 'IN',array(1,2));
        foreach($criteria as $key=>$val) {
			if (is_array($val)){
				if (empty($val)) continue;
			}else{
				if (strval($val)=='') continue;
			}
            switch($key) {
				case 'status':
					if (is_array($val))
	    	            $srch->addCondition('torp.opr_status', 'IN', $val);
					else
						$srch->addCondition('torp.opr_status', '=', intval($val));	
	                break;
				case 'date_from':
        	        $srch->addCondition('tord.order_date_added', '>=', $val. ' 00:00:00');
            	break;
				case 'date_to':
    	            $srch->addCondition('tord.order_date_added', '<=', $val. ' 23:59:59');
                break;
				case 'date':
                	$srch->addCondition('DATE(`order_date_added`)', '=', $val);
                break;
				case 'pagesize':
					$srch->setPageSize($val);
				break;
				case 'page':
					$srch->setPageNumber($val);
				break;
				case 'nolimit':
					$srch->doNotLimitRecords(true);
	        		$srch->doNotCalculateRecords(true);
				break;	
            }
        }
		$srch->addGroupBy('DATE(order_date_added)');
		$srch->addGroupBy('order_currency_code');
		$srch->addMultipleFields(array('DATE(order_date_added) as order_date,count(opr_id) as totOrders','SUM(opr_qty) as totQtys','SUM(opr_refund_qty) as totRefundedQtys','SUM(opr_qty-opr_refund_qty) as netSoldQty','sum((opr_commission_charged-opr_refund_commission)) as tot_sales_earnings','sum(opr_shipping_charges) as tot_shipping','sum(opr_net_charged) as tot_net_charged','SUM(opr_tax) as tot_tax_charged','(SUM(((opr_customer_buying_price+opr_customization_price))*(opr_qty))) as tot_cart_total','sum(opr_refund_amount) as tot_refunded_amount','sum(opr_refund_tax) as tot_refunded_tax','SUM(opr_refund_amount+opr_refund_tax) as opr_total_refund_amount','order_currency_code','order_currency_value'));
        $rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		return $this->db->fetch_all($rs);
	}
	
	function getSalesDetailReportData($criteria){
        $srch = new SearchBase('tbl_orders', 'tord');
		$srch->joinTable('tbl_order_products', 'LEFT JOIN', 'tord.order_id =torp.opr_order_id', 'torp');
		$srch->addCondition('tord.order_payment_status', 'IN',array(1,2));
        foreach($criteria as $key=>$val) {
			if (is_array($val)){
				if (empty($val)) continue;
			}else{
				if (strval($val)=='') continue;
			}
            switch($key) {
				case 'status':
					if (is_array($val))
	    	            $srch->addCondition('torp.opr_status', 'IN', $val);
					else
						$srch->addCondition('torp.opr_status', '=', intval($val));	
	                break;
				case 'date':
                	$srch->addCondition('DATE(`order_date_added`)', '=', $val);
                break;
				case 'code':
                	$srch->addCondition('order_currency_code', '=', $val);
                break;	
            }
        }
		$srch->addMultipleFields(array('opr_commission_charged-opr_refund_commission as net_sales_earnings','opr_tax as tax_charged','(opr_refund_amount-opr_refund_amount) as tot_refunded_tax','((opr_customer_buying_price+opr_customer_customization_price))*(opr_qty) as cart_total','opr_order_invoice_number','order_user_name','opr_qty','opr_refund_qty','opr_refund_amount','opr_refund_tax','opr_shipping_charges','opr_net_charged','order_currency_code','order_currency_value'));
		$srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
		return $this->db->fetch_all($rs);
	}
	
	function getUsersReportData($criteria){
		
		$srch = new SearchBase('tbl_order_products', 'tx');
		$srch->joinTable('tbl_orders', 'LEFT OUTER JOIN', 'tord.order_id = tx.opr_order_id', 'tord');
		$srch->addCondition('tord.order_payment_status', 'IN',array(1,2));
		$srch->addCondition('`tx`.opr_status', 'IN', (array)Settings::getSetting("CONF_PURCHASE_ORDER_STATUS"));
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tx.opr_order_id');
		$srch->addMultipleFields(array('tx.opr_order_id',"SUM(opr_qty-opr_refund_qty) as totQtys","SUM(opr_net_charged-opr_refund_amount) as totUserPurchase"));
		$qry_order_product_qty = $srch->getQuery();
		
		$srch = new SearchBase('tbl_orders', 'tord');
		$srch->addCondition('tord.order_payment_status', 'IN',array(1,2));
		$srch->joinTable('(' . $qry_order_product_qty . ')', 'LEFT OUTER JOIN', 'tord.order_id = top.opr_order_id', 'top');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tord.order_user_id');
		$srch->addMultipleFields(array('tord.order_user_id',"count(order_id) as totUserOrders","SUM(totQtys) as totUserOrderQtys","SUM(totUserPurchase) as totUserOrderPurchases"));
		$qry_order_qty = $srch->getQuery();
		
		$srch = new SearchBase('tbl_user_transactions', 'txn');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('txn.utxn_user_id');
		$srch->addMultipleFields(array('txn.utxn_user_id',"SUM(utxn_credit-utxn_debit) as userBalance"));
		$qry_user_balance = $srch->getQuery();
	
		$srch = new SearchBase('tbl_order_products', '`top`');
		$srch->joinTable('tbl_orders', 'INNER JOIN', '`top`.opr_order_id=`to`.order_id', '`to`');
		$srch->joinTable('tbl_shops', 'INNER JOIN', '`top`.opr_product_shop=`ts`.shop_id', '`ts`');
		$srch->addCondition('`top`.opr_status', 'IN', (array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS"));
		$srch->addCondition('`to`.order_payment_status', 'IN',array(1,2));
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('`ts`.shop_user_id');
		$srch->addMultipleFields(array('`top`.opr_product_shop','`ts`.shop_user_id shop_owner',"COUNT(distinct opr_order_id) as totVendorOrders","SUM(opr_qty-opr_refund_qty) as totSoldQty","SUM(((opr_customer_buying_price+opr_customization_price))*opr_qty - opr_refund_amount) as totalVendorSales"));
		$qry_vendor_orders = $srch->getQuery();
		
        $srch = new SearchBase('tbl_users', 'tu');
		$srch->joinTable('(' . $qry_order_qty . ')', 'LEFT OUTER JOIN', 'tu.user_id = tqoq.order_user_id', 'tqoq');
		$srch->joinTable('(' . $qry_user_balance . ')', 'LEFT OUTER JOIN', 'tu.user_id = tqub.utxn_user_id', 'tqub');
		$srch->joinTable('(' . $qry_vendor_orders . ')', 'LEFT OUTER JOIN', 'tu.user_id = tqvo.shop_owner', 'tqvo');
        $srch->addMultipleFields(array('tu.*','COALESCE(tqvo.totVendorOrders,0) as totVendorOrders','COALESCE(tqvo.totSoldQty,0) as totSoldQty','COALESCE(tqvo.totalVendorSales,0) as totalVendorSales','user_name user_full_name',
        'date_format(tu.user_added_on, \''.Settings::getSetting("CONF_DATE_FORMAT_MYSQL").'\') AS user_added_on','COALESCE(tqoq.totUserOrders,0) as totUserOrders','COALESCE(tqoq.totUserOrderQtys,0) as totUserOrderQtys','COALESCE(tqoq.totUserOrderPurchases,0) as totUserOrderPurchases','COALESCE(tqub.userBalance,0) as totUserBalance'));
		
		foreach($criteria as $key=>$val) {
				if (is_array($val)){
						if (empty($val)) continue;
				}else{
						if (strval($val)=='') continue;
				}
				switch($key) {
					case 'date_from':
						$srch->addCondition('tu.user_added_on', '>=', $val. ' 00:00:00');
					break;
					case 'date_to':
						$srch->addCondition('tu.user_added_on', '<=', $val. ' 23:59:59');
					break;
					case 'pagesize':
						$srch->setPageSize($val);
					break;
					case 'page':
						$srch->setPageNumber($val);
					break;
					case 'nolimit':
						$srch->doNotLimitRecords(true);
        				$srch->doNotCalculateRecords(true);
					break;
					case 'type':
					if (is_array($val)){
						$srch->addCondition('tu.user_type', 'IN', $val);
					}else {
						$srch->addCondition('tu.user_type', '=', $val);
					}
					break;	
				}
			}
		$rs = $srch->getResultSet();
		
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		return $this->db->fetch_all($rs);
	}
	
	function getProductsReportData($criteria){
		$srch = new SearchBase('tbl_order_products', 'top');
		$srch->joinTable('tbl_orders', 'INNER JOIN', '`top`.opr_order_id=`to`.order_id', '`to`');
		$srch->addCondition('`top`.opr_status', 'IN',(array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS"));
		$srch->addCondition('`to`.order_payment_status', 'IN',array(1,2));
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('`top`.opr_product_id');
		$srch->addMultipleFields(array('`top`.opr_product_id',"COUNT(opr_order_id) as totOrders","SUM(opr_qty-opr_refund_qty) as totSoldQty","SUM((opr_customer_buying_price+opr_customization_price)*opr_qty - opr_refund_amount) as total","SUM((opr_customization_price)*opr_qty) as customizations","SUM(opr_commission_charged - opr_refund_commission) as commission","SUM(opr_shipping_charges) as shipping","SUM(opr_net_charged-opr_refund_amount) as sub_total","SUM(opr_shipping_charges) as shipping","SUM(opr_tax) as tax","SUM(opr_net_charged) as net_charged"));
		$qry_order_product_qty = $srch->getQuery();
		
		$srch=new SearchBase('tbl_products','tp');
		$srch->joinTable('(' . $qry_order_product_qty . ')', 'LEFT OUTER JOIN', 'tp.prod_id = tqopq.opr_product_id', 'tqopq');
		$srch->addMultipleFields(array('tp.*','tqopq.*'));
		$srch->addCondition('tp.prod_is_deleted', '=', 0);
        foreach($criteria as $key=>$val) {
			if(strval($val)=='') continue;
            switch($key) {
			case 'type':
                $srch->addCondition('tp.prod_type', '=', intval($val));
                break;
			case 'keyword':
				if ($val!=""){
					$val=urldecode($val);
					$cndCondition=$srch->addCondition('tp.prod_sku', 'like', '%' . $val . '%');
					$cndCondition->attachCondition('tp.prod_name', 'like', '%' . $val . '%','OR');
					$cndCondition->attachCondition('tp.prod_model', 'like', '%' . $val . '%','OR');
					$cndCondition->attachCondition('tp.prod_slug', 'like', '%' . $val . '%','OR');
				}	
                break;
			case 'shop':
                $cnd=$srch->addCondition('tp.prod_shop', '=', intval($val));
                break;
			case 'brand':
	                $srch->addCondition('tp.prod_brand', '=', intval($val));
                break;
			case 'category':
				if ($val!=""){
					$cats=array_merge(array($val),array_keys(Categories::getProdClassifiedCategoriesAssocArray($val)));
					$srch->addCondition('tp.prod_category', 'IN',$cats);
				}	
			break;
			case 'minprice':
				$srch->addCondition('tp.prod_sale_price', '>=', $val);
                break;
			case 'maxprice':
                $srch->addCondition('tp.prod_sale_price', '<=', $val);
                break;					
			case 'date_from':
                $srch->addCondition('tp.prod_published_on', '>=', $val. ' 00:00:00');
                break;
			case 'date_to':
                $srch->addCondition('tp.prod_published_on', '<=', $val. ' 23:59:59');
                break;
			case 'pagesize':
				$srch->setPageSize($val);
				break;
			case 'page':
				$srch->setPageNumber($val);
			break;
			case 'nolimit':
				$srch->doNotLimitRecords(true);
        		$srch->doNotCalculateRecords(true);
			break;
			
            }
        }
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		return $this->db->fetch_all($rs);
	}
	
	function getShopsReportData($criteria){
		
		$pObj=new Product();
		$pObj->addMultipleFields(array('tp.prod_shop as product_shop',"count(prod_id) as totStoreProducts"));
		$pObj->addGroupBy('tp.prod_shop');
		$pObj->doNotCalculateRecords();
		$pObj->doNotLimitRecords();
		$qry_store_products = $pObj->getQuery();
		
		$srch = new SearchBase('tbl_prod_reviews', 'tpr');
		$srch->joinTable('tbl_products', 'INNER JOIN', 'tpr.review_prod_id=tp.prod_id and tp.prod_is_deleted=0 and tp.prod_is_expired=0 and tp.prod_status=1', 'tp');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tp.prod_shop');
		$srch->addCondition('tpr.review_is_deleted', '=', 0);
		$srch->addMultipleFields(array('tp.prod_shop',"AVG(review_rating) as shop_rating","count(review_id) as totReviews"));
		$qry_store_reviews = $srch->getQuery();
		
		
		$srch = new SearchBase('tbl_order_products', '`top`');
		$srch->joinTable('tbl_orders', 'INNER JOIN', '`top`.opr_order_id=`to`.order_id', '`to`');
		$srch->joinTable('tbl_orders_status', 'INNER JOIN', '`top`.opr_status = `tos`.orders_status_id', '`tos`');
		$srch->addCondition('`top`.opr_status', 'IN', (array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS"));
		$srch->addCondition('order_payment_status', 'IN',array(1,2));
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('`top`.opr_product_shop');
		$srch->addMultipleFields(array('`top`.opr_product_shop',"COUNT(distinct opr_order_id) as totOrders","SUM(opr_qty-opr_refund_qty) as totSoldQty","SUM(((opr_customer_buying_price))*opr_qty) as total","SUM(((opr_customization_price))*opr_qty) as customizations","SUM((opr_commission_charged-opr_refund_commission)) as commission","SUM(opr_shipping_charges) as shipping","(SUM(opr_tax)) as tax","(SUM((opr_customer_buying_price + opr_customization_price)*opr_qty - opr_refund_amount)) as sub_total"));
		$qry_order_shop = $srch->getQuery();
		
		
        $srch = new SearchBase('tbl_shops', 'ts');
		$srch->joinTable('(' . $qry_store_products . ')', 'LEFT OUTER JOIN', 'ts.shop_id = tqsp.product_shop', 'tqsp');
		$srch->joinTable('(' . $qry_store_reviews . ')', 'LEFT OUTER JOIN', 'ts.shop_id = tqsr.prod_shop', 'tqsr');
		$srch->joinTable('tbl_users', 'INNER JOIN', 'ts.shop_user_id=tu.user_id' , 'tu');
		$srch->joinTable('(' . $qry_order_shop . ')', 'LEFT OUTER JOIN', 'ts.shop_id = tqos.opr_product_shop', 'tqos');
            $srch->addMultipleFields(array('ts.*','tqos.*','COALESCE(tqos.totSoldQty,0) as totSoldQty','COALESCE(tqos.totOrders,0) as totOrders','tu.user_name as shop_owner','tu.user_email as shop_owner_email','tu.user_username as shop_owner_username','COALESCE(tqsp.totStoreProducts,0) as totProducts','COALESCE(round(tqsr.shop_rating,1),0) as shop_rating','COALESCE(tqsr.totReviews,0) as totReviews'));
        foreach($criteria as $key=>$val) {
			if(strval($val)=='') continue;
            switch($key) {
            case 'date_from':
                $srch->addCondition('ts.shop_date', '>=', $val. ' 00:00:00');
                break;
			case 'date_to':
                $srch->addCondition('ts.shop_date', '<=', $val. ' 23:59:59');
                break;
			case 'pagesize':
				$srch->setPageSize($val);
				break;
			case 'page':
				$srch->setPageNumber($val);
			break;
			case 'nolimit':
				$srch->doNotLimitRecords(true);
        		$srch->doNotCalculateRecords(true);
			break;	
            }
        }
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		return $this->db->fetch_all($rs);
	}
	
	function getTaxReportData($criteria){
        $srch = new SearchBase('tbl_orders', 'tord');
		$srch->joinTable('tbl_order_products', 'LEFT JOIN', 'tord.order_id =torp.opr_order_id', 'torp');
		$srch->addCondition('`tord`.order_payment_status', 'IN',array(1,2));
		$srch->addGroupBy('order_user_id');
        foreach($criteria as $key=>$val) {
			if (is_array($val)){
					if (empty($val)) continue;
			}else{
					if (strval($val)=='') continue;
			}
            switch($key) {
				case 'status':
					if (is_array($val))
	    	            $srch->addCondition('torp.opr_status', 'IN', $val);
					else
						$srch->addCondition('torp.opr_status', '=', intval($val));	
	                break;
				case 'date_from':
        	        $srch->addCondition('tord.order_date_added', '>=', $val. ' 00:00:00');
            	break;
				case 'date_to':
    	            $srch->addCondition('tord.order_date_added', '<=', $val. ' 23:59:59');
                break;
				case 'pagesize':
					$srch->setPageSize($val);
				break;
				case 'page':
					$srch->setPageNumber($val);
				break;
				case 'nolimit':
					$srch->doNotLimitRecords(true);
		       		$srch->doNotCalculateRecords(true);
				break;	
            }
        }
		$srch->addMultipleFields(array('tord.order_user_id,tord.order_user_name,count(distinct tord.order_id) as totbuyerorders','SUM(opr_tax-opr_refund_tax)  as tax'));
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		return $this->db->fetch_all($rs);
	}
	
	function getCommissionsReportData($criteria){
        $srch = new SearchBase('tbl_orders', 'tord');
		$srch->joinTable('tbl_order_products', 'LEFT JOIN', 'tord.order_id =torp.opr_order_id', 'torp');
		$srch->addCondition('tord.order_payment_status', 'IN',array(1,2));
		$srch->addGroupBy('opr_product_shop');
        foreach($criteria as $key=>$val) {
			if (is_array($val)){
					if (empty($val)) continue;
			}else{
					if (strval($val)=='') continue;
			}
            switch($key) {
				case 'status':
					if (is_array($val))
	    	            $srch->addCondition('torp.opr_status', 'IN', $val);
					else
						$srch->addCondition('torp.opr_status', '=', intval($val));	
	                break;
				case 'date_from':
        	        $srch->addCondition('tord.order_date_added', '>=', $val. ' 00:00:00');
            	break;
				case 'date_to':
    	            $srch->addCondition('tord.order_date_added', '<=', $val. ' 23:59:59');
                break;
				case 'pagesize':
					$srch->setPageSize($val);
				break;
				case 'page':
					$srch->setPageNumber($val);
				break;
				case 'nolimit':
					$srch->doNotLimitRecords(true);
	        		$srch->doNotCalculateRecords(true);
				break;	
            }
        }
		$srch->addMultipleFields(array('torp.opr_product_shop,torp.opr_product_shop_name,count(distinct torp.opr_order_id) as totvendororders','SUM(opr_commission_charged-opr_refund_commission) as commission','SUM(((opr_customer_buying_price + opr_customization_price ))*opr_qty) - SUM(((opr_customer_buying_price + opr_customization_price))*opr_refund_qty) as sales'));
        $rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		return $this->db->fetch_all($rs);
	}
	
	
	function getAffiliatesReportData($criteria){
		
		$orderObj=new Orders();
		$affiliate_pending_order_statuses=$orderObj->getAffiliatePendingOrderStatuses();
		
		$srch = new SearchBase('tbl_order_products', 'torp');
		$srch->joinTable('tbl_orders', 'INNER JOIN', 'tord.order_id =torp.opr_order_id', 'tord');
		$srch->addCondition('tord.order_payment_status', 'IN',array(1,2));
		$srch->addCondition('opr_status', 'IN', $affiliate_pending_order_statuses );
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tord.order_affiliate_id');
		$srch->addMultipleFields(array('tord.order_affiliate_id',"SUM(opr_affiliate_commission) as affPending"));
		$qry_affiliate_pending = $srch->getQuery();
		
		$srch = new SearchBase('tbl_order_products', 'torp');
		$srch->joinTable('tbl_orders', 'INNER JOIN', 'tord.order_id =torp.opr_order_id', 'tord');
		$srch->addCondition('tord.order_payment_status', 'IN',array(1,2));
		$srch->addCondition('opr_status', 'IN', (array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS"));
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tord.order_affiliate_id');
		$srch->addMultipleFields(array('tord.order_affiliate_id',"SUM(opr_affiliate_commission) as affOrdersReceived"));
		$qry_affiliate_received = $srch->getQuery();
		
		$srch = new SearchBase('tbl_affiliate_transactions', 'atxn');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('atxn.atxn_affiliate_id');
		$srch->addMultipleFields(array('atxn.atxn_affiliate_id',"SUM(atxn_credit-atxn_debit) as affBalance"));
		$qry_affiliate_balance = $srch->getQuery();
		
		$srch = new SearchBase('tbl_affiliate_transactions', 'atxn');
		$srch->addCondition('atxn_withdrawal_id', '=',0);
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('atxn.atxn_affiliate_id');
		$srch->addMultipleFields(array('atxn.atxn_affiliate_id',"SUM(atxn_credit) as affRevenue"));
		$qry_affiliate_revenue = $srch->getQuery();
		
		$srch = new SearchBase('tbl_orders', 'tord');
		$srch->addCondition('tord.order_payment_status', 'IN',array(1,2));
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tord.order_affiliate_id');
		$srch->addMultipleFields(array('tord.order_affiliate_id',"COUNT(order_id) as affOrders"));
		$qry_affiliate_orders = $srch->getQuery();
		
		$srch = new SearchBase('tbl_users', 'tu');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tu.user_affiliate_id');
		$srch->addMultipleFields(array('tu.user_affiliate_id',"COUNT(user_affiliate_id) as affSignups"));
		$qry_affiliate_signups = $srch->getQuery();
		
		
       $srch = new SearchBase('tbl_affiliates', 'ta');
		$srch->joinTable('(' . $qry_affiliate_balance . ')', 'LEFT OUTER JOIN', 'ta.affiliate_id = tqab.atxn_affiliate_id', 'tqab');
		$srch->joinTable('(' . $qry_affiliate_pending . ')', 'LEFT OUTER JOIN', 'ta.affiliate_id = tqap.order_affiliate_id', 'tqap');
		$srch->joinTable('(' . $qry_affiliate_received . ')', 'LEFT OUTER JOIN', 'ta.affiliate_id = tqar.order_affiliate_id', 'tqar');
		$srch->joinTable('(' . $qry_affiliate_revenue . ')', 'LEFT OUTER JOIN', 'ta.affiliate_id = tqarev.atxn_affiliate_id', 'tqarev');
		$srch->joinTable('(' . $qry_affiliate_signups . ')', 'LEFT OUTER JOIN', 'ta.affiliate_id = tqas.user_affiliate_id', 'tqas');
		$srch->joinTable('(' . $qry_affiliate_orders . ')', 'LEFT OUTER JOIN', 'ta.affiliate_id = tqao.order_affiliate_id', 'tqao');
		$srch->addCondition('ta.affiliate_is_deleted', '=', 0);
        $srch->addMultipleFields(array('ta.*','COALESCE(tqab.affBalance,0) as balance','COALESCE(tqarev.affRevenue,0) as revenue','COALESCE(tqar.affOrdersReceived,0) as received','COALESCE(tqap.affPending,0) as pending','COALESCE(tqas.affSignups,0) as signups'));
	
		foreach($criteria as $key=>$val) {
				if (is_array($val)){
						if (empty($val)) continue;
				}else{
						if (strval($val)=='') continue;
				}
				switch($key) {
					case 'date_from':
						$srch->addCondition('ta.affiliate_added_on', '>=', $val. ' 00:00:00');
					break;
					case 'date_to':
						$srch->addCondition('ta.affiliate_added_on', '<=', $val. ' 23:59:59');
					break;
					case 'pagesize':
						$srch->setPageSize($val);
					break;
					case 'page':
						$srch->setPageNumber($val);
					break;
					case 'nolimit':
						$srch->doNotLimitRecords(true);
		        		$srch->doNotCalculateRecords(true);
					break;	
            }
        }
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		return $this->db->fetch_all($rs);
	}
	
	function getAdvertisersReportData($criteria){
		
		$srch = new SearchBase('tbl_promotions', 'tp');
		$srch->addCondition('tp.promotion_is_deleted', '=',0);
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tp.promotion_user_id');
		$srch->addMultipleFields(array('tp.promotion_user_id',"COUNT(promotion_id) as totPromotions"));
		$qry_user_promotions = $srch->getQuery();
		
		
		
		$srch = new SearchBase('tbl_user_transactions', 'txn');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('txn.utxn_user_id');
		$srch->addMultipleFields(array('txn.utxn_user_id',"SUM(utxn_credit-utxn_debit) as userBalance"));
		$qry_user_balance = $srch->getQuery();
	
		
        $srch = new SearchBase('tbl_users', 'tu');
		$srch->joinTable('(' . $qry_user_promotions . ')', 'LEFT OUTER JOIN', 'tu.user_id = tqup.promotion_user_id', 'tqup');
		$srch->joinTable('(' . $qry_user_balance . ')', 'LEFT OUTER JOIN', 'tu.user_id = tqub.utxn_user_id', 'tqub');
		$srch->addMultipleFields(array('tu.*','user_name user_full_name',
        'date_format(tu.user_added_on, \''.Settings::getSetting("CONF_DATE_FORMAT_MYSQL").'\') AS user_added_on','COALESCE(tqub.userBalance,0) as totUserBalance','COALESCE(tqup.totPromotions,0) as totPromotions'));
		
		foreach($criteria as $key=>$val) {
				if (is_array($val)){
						if (empty($val)) continue;
				}else{
						if (strval($val)=='') continue;
				}
				switch($key) {
					case 'date_from':
						$srch->addCondition('tu.user_added_on', '>=', $val. ' 00:00:00');
					break;
					case 'date_to':
						$srch->addCondition('tu.user_added_on', '<=', $val. ' 23:59:59');
					break;
					case 'pagesize':
						$srch->setPageSize($val);
					break;
					case 'page':
						$srch->setPageNumber($val);
					break;
					case 'nolimit':
						$srch->doNotLimitRecords(true);
        				$srch->doNotCalculateRecords(true);
					break;
					case 'type':
					if (is_array($val)){
						$srch->addCondition('tu.user_type', 'IN', $val);
					}else {
						$srch->addCondition('tu.user_type', '=', $val);
					}
					break;	
				}
			}
		$rs = $srch->getResultSet();
		
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		return $this->db->fetch_all($rs);
	}
	
	
	function getPromotionsReportData($criteria){
		$srch = new SearchBase('tbl_promotions_logs', 'tpl');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tpl.lprom_id');
		$srch->addMultipleFields(array(
			'tpl.lprom_id',
			"SUM(lprom_impressions) as totImpressions",
			"SUM(lprom_clicks) as totClicks",
			"SUM(lprom_orders) as totOrders"
		));
		$qry_promotion_logs = $srch->getQuery();
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
		$srch->addMultipleFields(array(
			'txn.utxn_user_id',
			"SUM(utxn_credit-utxn_debit) as userBalance"
		));
		$qry_user_balance = $srch->getQuery();
		$srch = new SearchBase('tbl_promotions_charges', 'tpc');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tpc.pcharge_promotion_id');
		$srch->addMultipleFields(array(
			'tpc.pcharge_promotion_id',
			"SUM(pcharge_charged_amount) as totPromotionPayments"
		));
		$qry_promotion_payments = $srch->getQuery();
		$srch = new SearchBase('tbl_promotions', 'tp');
		$srch->joinTable('(' . $qry_promotion_clicks . ')', 'LEFT OUTER JOIN', 'tp.promotion_id = tqpc.pclick_promotion_id', 'tqpc');
		$srch->joinTable('(' . $qry_promotion_payments . ')', 'LEFT OUTER JOIN', 'tp.promotion_id = tqpp.pcharge_promotion_id', 'tqpp');
		$srch->joinTable('tbl_users', 'LEFT OUTER JOIN', 'tp.promotion_user_id = u.user_id', 'u');
		$srch->joinTable('(' . $qry_user_balance . ')', 'LEFT OUTER JOIN', 'u.user_id = tqub.utxn_user_id', 'tqub');
		$srch->joinTable('tbl_products', 'LEFT OUTER JOIN', 'tp.promotion_product_id = p.prod_id and tp.promotion_type=1', 'p');
		$srch->joinTable('tbl_shops', 'LEFT OUTER JOIN', 'tp.promotion_shop_id = s.shop_id and tp.promotion_type=2', 's');
		$srch->joinTable('(' . $qry_promotion_logs . ')', 'LEFT OUTER JOIN', 'tp.promotion_id = tqpl.lprom_id', 'tqpl');
		$srch->addCondition('promotion_is_deleted', '=', 0);
		$srch->addMultipleFields(array(
			'tp.*',
			'u.user_name',
			'u.user_id',
			'u.user_email',
			's.shop_name',
			's.shop_logo',
			'p.prod_name',
			'COALESCE(tqpl.totImpressions,0) as totImpressions',
			'COALESCE(tqpl.totClicks,0) as totClicks',
			'COALESCE(tqpl.totOrders,0) as totOrders',
			'COALESCE(tqpp.totPromotionPayments,0) as totPayments',
			'tqpc.*',
			'LPAD(tp.promotion_id, 6,"0") as promotion_number'
		));
        foreach($criteria as $key=>$val) {
			if(strval($val)=='') continue;
            switch($key) {
				case 'promoter':
				$srch->addCondition('user_name', 'like', '%' . $val . '%');
				break;
			case 'user':
				$srch->addCondition('promotion_user_id', '=', intval($val));
				break;
			case 'shop':
				$srch->addCondition('promotion_shop_id', '=', intval($val));
				break;
			case 'product':
				$srch->addCondition('promotion_product_id', '=', intval($val));
				break;
			case 'type':
				$srch->addCondition('promotion_type', '=', intval($val));
				break;
			case 'position':
				$srch->addCondition('promotion_banner_position', '=', $val);
				break;
			case 'date_from':
				$srch->addDirectCondition("('$val' BETWEEN promotion_start_date and promotion_end_date)");
				break;
			case 'date_to':
				$srch->addDirectCondition("('$val' BETWEEN promotion_start_date and promotion_end_date)");
				break;
			case 'date_interval':
				$arr = explode("~", $val);
				$srch->addDirectCondition("((promotion_start_date BETWEEN '$arr[0]' and '$arr[1]') OR (promotion_end_date BETWEEN '$arr[0]' and '$arr[1]'))");
				break;
			case 'impressions_from':
				$srch->addCondition('totImpressions', '>=', intval($val));
				break;
			case 'impressions_to':
				$srch->addCondition('totImpressions', '<=', intval($val));
				break;
			case 'clicks_from':
				$srch->addCondition('totClicks', '>=', intval($val));
				break;
			case 'clicks_to':
				$srch->addCondition('totClicks', '<=', intval($val));
				break;
			case 'status':
				$srch->addCondition('promotion_status', '=', $val);
				break;
			case 'approved':
				$srch->addCondition('promotion_is_approved', '=', $val);
				break;
			case 'pagesize':
					$srch->setPageSize($val);
					break;
				case 'page':
					$srch->setPageNumber($val);
				break;
				case 'nolimit':
					$srch->doNotLimitRecords(true);
					$srch->doNotCalculateRecords(true);
				break;
			
            }
        }
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		return $this->db->fetch_all($rs);
	}
	
	function getSubscriptionsReportData($criteria){
		$srch = new SearchBase('tbl_subscription_merchant_package_transactions', 'tsmpt');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tsmpt.mptran_mporder_id');
		$srch->addMultipleFields(array('tsmpt.mptran_mporder_id',"COUNT(mptran_id) as totPaymentRecords","SUM(mptran_amount) as totPayments","GROUP_CONCAT(DISTINCT(mptran_mode) SEPARATOR ',') payment_methods"));
		$qry_order_payments = $srch->getQuery();
		
        $srch = new SearchBase( 'tbl_subscription_merchant_package_orders', 'tmpo' );
		$srch->joinTable('tbl_subscription_order_status', 'LEFT JOIN', 'tmpo.mporder_mpo_status_id = tsos.sorder_status_id', 'tsos');
		$srch->joinTable('tbl_users', 'LEFT OUTER JOIN', 'tmpo.mporder_user_id = tu.user_id', 'tu');
		$srch->joinTable('(' . $qry_order_payments . ')', 'LEFT OUTER JOIN', 'tmpo.mporder_id = tqop.mptran_mporder_id', 'tqop');
        $srch->addMultipleFields(array('tmpo.*','COALESCE(tqop.totPaymentRecords,0) as totPaymentRecords','COALESCE(tqop.totPayments,0) as totPayments','tqop.payment_methods','tsos.sorder_status_name','GREATEST((mporder_net_charged - IFNULL(totPayments,0)),0) as mporder_balance'));
		$srch->addCondition('tmpo.mporder_payment_status', '=', 1);
		$srch->addOrder('mporder_id','DESC');
		foreach($criteria as $key=>$val) {
			if(strval($val)=='') continue;
				switch($key) {
						case 'id':
							$srch->addCondition('tmpo.mporder_id', '=', intval($val));
							break;
						case 'user':
						case 'subscriber':
							$srch->addCondition('tmpo.mporder_user_id', '=', intval($val));
							break;
						case 'status':
							$srch->addCondition('tmpo.mporder_mpo_status_id', '=', intval($val));
							break;
						case 'date':
							$srch->addCondition('DATE(`mporder_date_added`)', '=', $val);
							break;
						case 'date_from':
							$srch->addCondition('tmpo.mporder_date_added', '>=', $val. ' 00:00:00');
							break;
						case 'date_to':
							$srch->addCondition('tmpo.mporder_date_added', '<=', $val. ' 23:59:59');
							break;
						case 'pagesize':
							$srch->setPageSize($val);
							break;
						case 'page':
							$srch->setPageNumber($val);
							break;
						case 'subscription_status':
								if (is_array($val))
									$srch->addCondition('tmpo.mporder_mpo_status_id', 'IN', (array)($val));
								else	
									$srch->addCondition('tmpo.mporder_mpo_status_id', '=', intval($val));
								break;	
						break;
						case 'pagesize':
							$srch->setPageSize($val);
							break;
						case 'page':
							$srch->setPageNumber($val);
						break;
						case 'nolimit':
							$srch->doNotLimitRecords(true);
							$srch->doNotCalculateRecords(true);
						break;
			
            }
        }
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		return $this->db->fetch_all($rs);
	}
}