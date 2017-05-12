<?php
class Statistics extends Model {
	function __construct(){
		$this->db = Syspage::getdb();
    }
	
	function getDashboardSummary($type) {
        $type = strtolower($type);
        switch($type) {
				case 'sales':
					$srch = new SearchBase('tbl_order_products', 'torp');
					$srch->joinTable('tbl_orders', 'LEFT JOIN', 'tord.order_id =torp.opr_order_id', 'tord');
					$srch->addCondition('opr_status', 'IN', (array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS"));
					$srch->addMultipleFields(array('SUM((opr_customer_buying_price+opr_customization_price)*opr_qty - opr_refund_amount) AS lifetime_sales,avg(opr_net_charged) AS avg_order,count(*) as total_orders'));
					$srch->addCondition('order_payment_status', 'IN',array(1,2));
			        $rs = $srch->getResultSet();
					return $this->db->fetch($rs);
		        break;
				case 'orders':
					$srch = new SearchBase('tbl_orders', 'tord');
					$srch->addMultipleFields(array('avg(order_net_charged) AS avg_order,count(*) as total_orders'));
					$srch->addCondition('order_payment_status', 'IN',array(1,2));
			        $rs = $srch->getResultSet();
					return $this->db->fetch($rs);
		        break;
				case 'signups':
					$srch = new SearchBase('tbl_users', 'tu');
					$srch->addMultipleFields(array('count(*) as total_users'));
					$srch->addCondition('user_is_deleted', '=',0);
					$rs = $srch->getResultSet();
					return $this->db->fetch($rs);
				break;
				case 'shops':
					$srch = new SearchBase('tbl_shops', 'ts');
					$srch->addMultipleFields(array('count(*) as total_shops'));
					$srch->addCondition('shop_is_deleted', '=',0);
			        $rs = $srch->getResultSet();
					return $this->db->fetch($rs);
				break;
				case 'products':
					$srch = new SearchBase('tbl_products', 'tp');
					$srch->addMultipleFields(array('count(*) as total_products'));
					$srch->addCondition('prod_is_deleted', '=',0);
					$rs = $srch->getResultSet();
					return $this->db->fetch($rs);
				break;	
		}
	}
	
	function getDashboardLast12MonthsSummary($type) {
		$last12Months=Utilities::getLast12MonthsDetails();
        $type = strtolower($type);
        switch($type) {
				 case 'sales':
				 	foreach($last12Months as $key=>$val ){
						$rsSales=$this->db->query("SELECT SUM((opr_customer_buying_price+opr_customization_price)*opr_qty - opr_refund_amount) AS Sales FROM `tbl_order_products` t1 INNER JOIN tbl_orders t2 on t1.opr_order_id=t2.order_id WHERE t2.order_payment_status IN (1,2) and t1.opr_status in (".implode(",",(array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS")).") and month( t2.`order_date_added` )=$val[monthCount] and year( t2.`order_date_added` )=$val[year]");
						$row=$this->db->fetch($rsSales);
						$sales_data[]=array("duration"=>$val[monthShort]."-".$val[year],"value"=>round($row["Sales"],2));
					}
					return $sales_data;
		        break;
				
				case 'earnings':
				 	foreach($last12Months as $key=>$val ){
						$rsEarnings=$this->db->query("SELECT sum( opr_commission_charged - opr_refund_commission) AS Earning FROM `tbl_order_products` t1 INNER JOIN tbl_orders t2 on t1.opr_order_id=t2.order_id WHERE t2.order_payment_status in (1,2) and t1.opr_status in (".implode(",",(array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS")).") and month( t1.`opr_completion_date` )=$val[monthCount] and year( t1.`opr_completion_date` )=$val[year]");						
						$row=$this->db->fetch($rsEarnings);
						$earnings_data[]=array("duration"=>$val[monthShort]."-".$val[year],"value"=>round($row["Earning"],2));
					}
					return $earnings_data;
		        break;
				case 'signups':
				 	foreach($last12Months as $key=>$val ){
						$rsEarnings=$this->db->query("SELECT count(*) AS Registrations FROM `tbl_users` WHERE `user_is_deleted` =0  AND user_type IN ('3','4','5') and  month(`user_added_on` )=$val[monthCount] and  year(`user_added_on` )=$val[year] ");
						$row=$this->db->fetch($rsEarnings);
						$signups_data[]=array("duration"=>$val[monthShort]."-".$val[year],"value"=>round($row["Registrations"],2));
					}
					return $signups_data;
		        break;
				case 'affiliate_signups':
				 	foreach($last12Months as $key=>$val ){
						$rsEarnings=$this->db->query("SELECT count(*) AS Registrations FROM `tbl_affiliates` WHERE `affiliate_is_deleted` =0 and affiliate_status=1  and  month(`affiliate_added_on` )=$val[monthCount] and  year(`affiliate_added_on` )=$val[year] ");
						$row=$this->db->fetch($rsEarnings);
						$affiliate_signups_data[]=array("duration"=>$val[monthShort]."-".$val[year],"value"=>round($row["Registrations"],2));
					}
					return $affiliate_signups_data;
		        break;
				case 'products':
				 	foreach($last12Months as $key=>$val ){
						$rsEarnings=$this->db->query("SELECT count(*) AS Products FROM `tbl_products` WHERE `prod_is_deleted` =0 and prod_status=1  and  month(`prod_added_on` )=$val[monthCount] and  year(`prod_added_on` )=$val[year] ");
						$row=$this->db->fetch($rsEarnings);
						$products_data[]=array("duration"=>$val[monthShort]."-".$val[year],"value"=>round($row["Products"],2));
					}
					return $products_data;
		        break;
				
				
		}
	}
	
	function getStats($type) {
        $type = strtolower($type);
        switch($type) {
        case 'total_members':
			$sql = "SELECT 1 AS num_days, count(user_id) FROM `tbl_users` WHERE DATE(user_added_on)=DATE(NOW()) AND user_is_deleted=0 AND user_type in (3,4,5)
            UNION ALL
            SELECT 7 AS num_days, count(user_id) FROM `tbl_users` WHERE  YEARWEEK(user_added_on) = YEARWEEK(NOW()) AND user_is_deleted=0  AND user_type in (3,4,5)
            UNION ALL
            SELECT 30 AS num_days, count(user_id) FROM `tbl_users` WHERE MONTH(user_added_on)=MONTH(NOW()) AND user_is_deleted=0 AND user_type in (3,4,5)
            UNION ALL
            SELECT 90 AS num_days, count(user_id) FROM `tbl_users` WHERE user_added_on>date_sub(date_add(date_add(LAST_DAY(now()),interval 1 DAY),interval -1 MONTH), INTERVAL 3 MONTH) AND user_is_deleted=0 AND user_type in (3,4,5)
            UNION ALL
            SELECT -1 AS num_days, count(user_id) FROM `tbl_users` WHERE user_is_deleted=0 AND user_type in (3,4,5)";
            $rs = $this->db->query($sql);
            return $this->db->fetch_all_assoc($rs);
            break;
		case 'total_advertisers':
			$sql = "SELECT 1 AS num_days, count(user_id) FROM `tbl_users` WHERE DATE(user_added_on)=DATE(NOW()) AND user_is_deleted=0 AND user_type in (1)
            UNION ALL
            SELECT 7 AS num_days, count(user_id) FROM `tbl_users` WHERE  YEARWEEK(user_added_on) = YEARWEEK(NOW()) AND user_is_deleted=0  AND user_type in (1)
            UNION ALL
            SELECT 30 AS num_days, count(user_id) FROM `tbl_users` WHERE MONTH(user_added_on)=MONTH(NOW()) AND user_is_deleted=0 AND user_type in (1)
            UNION ALL
            SELECT 90 AS num_days, count(user_id) FROM `tbl_users` WHERE user_added_on>date_sub(date_add(date_add(LAST_DAY(now()),interval 1 DAY),interval -1 MONTH), INTERVAL 3 MONTH) AND user_is_deleted=0 AND user_type in (1)
            UNION ALL
            SELECT -1 AS num_days, count(user_id) FROM `tbl_users` WHERE user_is_deleted=0 AND user_type in (1)";
            $rs = $this->db->query($sql);
            return $this->db->fetch_all_assoc($rs);
            break;	
		case 'total_affiliates':
			$sql = "SELECT 1 AS num_days, count(affiliate_id) FROM `tbl_affiliates` WHERE DATE(affiliate_added_on)=DATE(NOW()) AND affiliate_is_deleted=0  
            UNION ALL
            SELECT 7 AS num_days, count(affiliate_id) FROM `tbl_affiliates` WHERE  YEARWEEK(affiliate_added_on) = YEARWEEK(NOW()) AND affiliate_is_deleted=0  
            UNION ALL
            SELECT 30 AS num_days, count(affiliate_id) FROM `tbl_affiliates` WHERE MONTH(affiliate_added_on)=MONTH(NOW()) AND affiliate_is_deleted=0  
            UNION ALL
            SELECT 90 AS num_days, count(affiliate_id) FROM `tbl_affiliates` WHERE affiliate_added_on>date_sub(date_add(date_add(LAST_DAY(now()),interval 1 DAY),interval -1 MONTH), INTERVAL 3 MONTH) AND affiliate_is_deleted=0 
            UNION ALL
            SELECT -1 AS num_days, count(affiliate_id) FROM `tbl_affiliates` WHERE affiliate_is_deleted=0 ";
            $rs = $this->db->query($sql);
            return $this->db->fetch_all_assoc($rs);
            break;	
        
        case 'total_shops':
            $sql = "SELECT 1 AS num_days, count(shop_id) FROM `tbl_shops` WHERE DATE(shop_date)=DATE(NOW()) AND shop_is_deleted=0 
            UNION ALL
            SELECT 7 AS num_days, count(shop_id) FROM `tbl_shops` WHERE YEARWEEK(shop_date) = YEARWEEK(NOW()) AND shop_is_deleted=0 
            UNION ALL
            SELECT 30 AS num_days, count(shop_id) FROM `tbl_shops` WHERE MONTH(shop_date)=MONTH(NOW()) AND shop_is_deleted=0            UNION ALL
            SELECT 90 AS num_days, count(shop_id) FROM `tbl_shops` WHERE shop_date>date_sub(date_add(date_add(LAST_DAY(now()),interval 1 DAY),interval -1 MONTH), INTERVAL 3 MONTH) AND shop_is_deleted=0 
            UNION ALL
            SELECT -1 AS num_days, count(shop_id) FROM `tbl_shops` WHERE shop_is_deleted=0";
            $rs = $this->db->query($sql);
            return $this->db->fetch_all_assoc($rs);
            break;
		case 'total_orders':
			$sql = "SELECT 1 AS num_days,count(distinct order_id) as totalorders, SUM(order_net_charged) as totalsales,AVG(order_net_charged) avgorder FROM tbl_orders WHERE order_payment_status IN (1,2) AND DATE(order_date_added)=DATE(NOW())  
            UNION ALL
			SELECT 7 AS num_days,count( distinct order_id) as totalorders, SUM(order_net_charged) as totalsales,AVG(order_net_charged) avgorder FROM tbl_orders WHERE order_payment_status IN (1,2) AND YEARWEEK(order_date_added) = YEARWEEK(NOW())
            UNION ALL
            SELECT 30 AS num_days,count(distinct order_id) as totalorders, SUM(order_net_charged) as totalsales,AVG(order_net_charged) avgorder FROM  tbl_orders WHERE order_payment_status IN (1,2) AND MONTH(order_date_added)=MONTH(NOW())
			UNION ALL
            SELECT 90 AS num_days,count(distinct order_id) as totalorders, SUM(order_net_charged) as totalsales,AVG(order_net_charged) avgorder FROM  tbl_orders WHERE order_payment_status IN (1,2) AND order_date_added>date_sub(date_add(date_add(LAST_DAY(now()),interval 1 DAY),interval -1 MONTH), INTERVAL 3 MONTH)
            UNION ALL
            SELECT -1 AS num_days,count(distinct order_id) as totalorders, SUM(order_net_charged) as totalsales,AVG(order_net_charged) avgorder FROM tbl_orders WHERE order_payment_status IN  (1,2)  ";
            $rs = $this->db->query($sql);
            return $this->db->fetch_all($rs);
            break;
		case 'total_sales':
			$sql = "SELECT 1 AS num_days,SUM((opr_customer_buying_price+opr_customization_price)*opr_qty - opr_refund_amount) AS totalsales,SUM(opr_commission_charged-opr_refund_commission) totalcommission FROM `tbl_order_products` t1 INNER JOIN tbl_orders t2 on t1.opr_order_id=t2.order_id WHERE t2.order_payment_status IN (1,2) and t1.opr_status in (".implode(",",(array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS")).") AND DATE(opr_completion_date)=DATE(NOW())  
            UNION ALL
			SELECT 7 AS num_days,SUM((opr_customer_buying_price+opr_customization_price)*opr_qty - opr_refund_amount) AS totalsales,SUM(opr_commission_charged-opr_refund_commission) totalcommission FROM `tbl_order_products` t1 INNER JOIN tbl_orders t2 on t1.opr_order_id=t2.order_id WHERE t2.order_payment_status IN (1,2) and t1.opr_status in (".implode(",",(array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS")).") AND YEARWEEK(opr_completion_date) = YEARWEEK(NOW())
            UNION ALL
            SELECT 30 AS num_days,SUM((opr_customer_buying_price+opr_customization_price)*opr_qty - opr_refund_amount) AS totalsales,SUM(opr_commission_charged-opr_refund_commission) totalcommission FROM `tbl_order_products` t1 INNER JOIN tbl_orders t2 on t1.opr_order_id=t2.order_id WHERE t2.order_payment_status IN (1,2) and t1.opr_status in (".implode(",",(array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS")).") AND MONTH(opr_completion_date)=MONTH(NOW())
			UNION ALL
            SELECT 90 AS num_days,SUM((opr_customer_buying_price+opr_customization_price)*opr_qty - opr_refund_amount) AS totalsales,SUM(opr_commission_charged-opr_refund_commission) totalcommission FROM `tbl_order_products` t1 INNER JOIN tbl_orders t2 on t1.opr_order_id=t2.order_id WHERE t2.order_payment_status IN (1,2) and t1.opr_status in (".implode(",",(array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS")).") AND opr_completion_date>date_sub(date_add(date_add(LAST_DAY(now()),interval 1 DAY),interval -1 MONTH), INTERVAL 3 MONTH)
            UNION ALL
            SELECT -1 AS num_days,SUM((opr_customer_buying_price+opr_customization_price)*opr_qty - opr_refund_amount) AS totalsales,SUM(opr_commission_charged-opr_refund_commission) totalcommission FROM `tbl_order_products` t1 INNER JOIN tbl_orders t2 on t1.opr_order_id=t2.order_id WHERE t2.order_payment_status IN (1,2) and t1.opr_status in (".implode(",",(array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS")).") ";
            $rs = $this->db->query($sql);
            return $this->db->fetch_all($rs);
            break;
		case 'total_subscription':
			$sql = "SELECT 1 AS num_days,SUM(mptran_amount) AS earnings FROM `tbl_subscription_merchant_package_transactions` tsmpt WHERE DATE(mptran_date)=DATE(NOW())  
            UNION ALL
			SELECT 7 AS num_days,SUM(mptran_amount) AS earnings FROM `tbl_subscription_merchant_package_transactions` tsmpt  WHERE YEARWEEK(mptran_date) = YEARWEEK(NOW())
            UNION ALL
            SELECT 30 AS num_days,SUM(mptran_amount) AS earnings FROM `tbl_subscription_merchant_package_transactions` tsmpt WHERE MONTH(mptran_date)=MONTH(NOW())
			UNION ALL
            SELECT 90 AS num_days,SUM(mptran_amount) AS earnings FROM `tbl_subscription_merchant_package_transactions` tsmpt WHERE mptran_date>date_sub(date_add(date_add(LAST_DAY(now()),interval 1 DAY),interval -1 MONTH), INTERVAL 3 MONTH)
            UNION ALL
            SELECT -1 AS num_days,SUM(mptran_amount) AS earnings FROM `tbl_subscription_merchant_package_transactions` tsmpt ";
            $rs = $this->db->query($sql);
            return $this->db->fetch_all($rs);
            break;
				
		case 'total_ppc':
			$sql = "SELECT 1 AS num_days,SUM(pcharge_charged_amount) AS totalppcearnings FROM `tbl_promotions_charges` tpc  WHERE  DATE(pcharge_date)=DATE(NOW())  
            UNION ALL
			SELECT 7 AS num_days,SUM(pcharge_charged_amount) AS totalppcearnings FROM `tbl_promotions_charges` tpc  WHERE   YEARWEEK(pcharge_date) = YEARWEEK(NOW())
            UNION ALL
            SELECT 30 AS num_days,SUM(pcharge_charged_amount) AS totalppcearnings FROM `tbl_promotions_charges` tpc  WHERE  MONTH(pcharge_date)=MONTH(NOW())
			UNION ALL
            SELECT 90 AS num_days,SUM(pcharge_charged_amount) AS totalppcearnings FROM `tbl_promotions_charges` tpc  WHERE  pcharge_date>date_sub(date_add(date_add(LAST_DAY(now()),interval 1 DAY),interval -1 MONTH), INTERVAL 3 MONTH)
            UNION ALL
            SELECT -1 AS num_days,SUM(pcharge_charged_amount) AS totalppcearnings FROM `tbl_promotions_charges` tpc";
			
            $rs = $this->db->query($sql);
            return $this->db->fetch_all($rs);
            break;		
		case 'total_products':
            $sql = "SELECT 1 AS num_days, count(prod_id) FROM `tbl_products` WHERE DATE(prod_published_on)=DATE(NOW())  AND prod_status=1 and prod_is_deleted=0 
            UNION ALL
            SELECT 7 AS num_days, count(prod_id) FROM `tbl_products` WHERE YEARWEEK(prod_published_on) = YEARWEEK(NOW()) AND prod_status=1 and prod_is_deleted=0 
            UNION ALL
            SELECT 30 AS num_days, count(prod_id) FROM `tbl_products` WHERE MONTH(prod_published_on)=MONTH(NOW()) AND prod_status=1 and prod_is_deleted=0
			UNION ALL
            SELECT 90 AS num_days, count(prod_id) FROM `tbl_products` WHERE prod_published_on>date_sub(date_add(date_add(LAST_DAY(now()),interval 1 DAY),interval -1 MONTH), INTERVAL 3 MONTH) AND prod_status=1 and prod_is_deleted=0 
            UNION ALL
            SELECT -1 AS num_days, count(prod_id) FROM `tbl_products` WHERE prod_status=1 and prod_is_deleted=0";
            $rs = $this->db->query($sql);
            return $this->db->fetch_all_assoc($rs);
            break;
		 case 'total_withdrawal_requests':
            $sql = "SELECT 1 AS num_days, count(withdrawal_id) FROM `tbl_user_withdrawal_requests` WHERE DATE(withdrawal_request_date)=DATE(NOW()) 
            UNION ALL
            SELECT 7 AS num_days, count(withdrawal_id) FROM `tbl_user_withdrawal_requests` WHERE YEARWEEK(withdrawal_request_date) = YEARWEEK(NOW())
            UNION ALL
            SELECT 30 AS num_days, count(withdrawal_id) FROM `tbl_user_withdrawal_requests` WHERE MONTH(withdrawal_request_date)=MONTH(NOW())
		    UNION ALL
            SELECT 90 AS num_days, count(withdrawal_id) FROM `tbl_user_withdrawal_requests` WHERE withdrawal_request_date>date_sub(date_add(date_add(LAST_DAY(now()),interval 1 DAY),interval -1 MONTH), INTERVAL 3 MONTH)
            UNION ALL
            SELECT -1 AS num_days, count(withdrawal_id) FROM `tbl_user_withdrawal_requests`";
            $rs = $this->db->query($sql);
            return $this->db->fetch_all_assoc($rs);
            break;
		case 'total_affiliate_withdrawal_requests':
            $sql = "SELECT 1 AS num_days, count(afwithdrawal_id) FROM `tbl_affiliate_withdrawal_requests` WHERE DATE(afwithdrawal_request_date)=DATE(NOW()) 
            UNION ALL
            SELECT 7 AS num_days, count(afwithdrawal_id) FROM `tbl_affiliate_withdrawal_requests` WHERE YEARWEEK(afwithdrawal_request_date) = YEARWEEK(NOW())
            UNION ALL
            SELECT 30 AS num_days, count(afwithdrawal_id) FROM `tbl_affiliate_withdrawal_requests` WHERE MONTH(afwithdrawal_request_date)=MONTH(NOW())
		    UNION ALL
            SELECT 90 AS num_days, count(afwithdrawal_id) FROM `tbl_affiliate_withdrawal_requests` WHERE afwithdrawal_request_date>date_sub(date_add(date_add(LAST_DAY(now()),interval 1 DAY),interval -1 MONTH), INTERVAL 3 MONTH)
            UNION ALL
            SELECT -1 AS num_days, count(afwithdrawal_id) FROM `tbl_affiliate_withdrawal_requests`";
            $rs = $this->db->query($sql);
            return $this->db->fetch_all_assoc($rs);
            break;	
		case 'product_reviews':
            $sql = "SELECT 1 AS num_days, count(review_id) FROM `tbl_prod_reviews` WHERE DATE(reviewed_on)=DATE(NOW())  
            UNION ALL
            SELECT 7 AS num_days, count(review_id) FROM `tbl_prod_reviews` WHERE YEARWEEK(reviewed_on) = YEARWEEK(NOW())
            UNION ALL
            SELECT 30 AS num_days, count(review_id) FROM `tbl_prod_reviews` WHERE MONTH(reviewed_on)=MONTH(NOW()) 
		    UNION ALL
            SELECT 90 AS num_days, count(review_id) FROM `tbl_prod_reviews` WHERE reviewed_on>date_sub(date_add(date_add(LAST_DAY(now()),interval 1 DAY),interval -1 MONTH), INTERVAL 3 MONTH) 
            UNION ALL
            SELECT -1 AS num_days, count(review_id) FROM `tbl_prod_reviews`";
            $rs = $this->db->query($sql);
            return $this->db->fetch_all_assoc($rs);
            break;	
						
        }
    }
	
	function getTopProducts($type){			
		$srch = new SearchBase('tbl_order_products', 'torp');
		$srch->joinTable('tbl_orders', 'LEFT JOIN', 'tord.order_id =torp.opr_order_id', 'tord');
		$srch->addCondition('opr_status', 'IN', (array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS"));
		$srch->addMultipleFields(array('opr_name','count(*) as sold'));
		$srch->addCondition('order_payment_status', 'IN',array(1,2));
		switch(strtoupper($type)){
			case 'TODAY':
				$srch->addDirectCondition('DATE(tord.order_date_added)=DATE(NOW())');				
			break;
			case 'WEEKLY':
				$srch->addDirectCondition('YEARWEEK(tord.order_date_added)=YEARWEEK(NOW())');	
			break;
			case 'MONTHLY':
				$srch->addDirectCondition('MONTH(tord.order_date_added)=MONTH(NOW())');				
			break;
			case 'YEARLY':
				$srch->addDirectCondition('YEAR(tord.order_date_added)=YEAR(NOW())');				
			break;
		}		
		$srch->addGroupBy('opr_name');
		$srch->addOrder ('sold','desc');		
		$rs = $srch->getResultSet();					
		return $this->db->fetch_all($rs);	
	}	
	function getTopSearchKeywords($type){
		$srch = new SearchBase('tbl_search_items', 'tsi');
			switch(strtoupper($type)){
				case 'TODAY':
					$srch->addDirectCondition('DATE(tsi.searchitem_date)=DATE(NOW())');				
				break;
				case 'WEEKLY':
					$srch->addDirectCondition('YEARWEEK(tsi.searchitem_date)=YEARWEEK(NOW())');	
				break;
				case 'MONTHLY':
					$srch->addDirectCondition('MONTH(tsi.searchitem_date)=MONTH(NOW())');				
				break;
				case 'YEARLY':
					$srch->addDirectCondition('YEAR(tsi.searchitem_date)=YEAR(NOW())');				
				break;
		}	
		$srch->addMultipleFields(array('tsi.*','sum(tsi.searchitem_count) as search_count'));
		$srch->addOrder ('searchitem_count','desc');
		$srch->addOrder ('search_count','desc');
		$srch->addGroupBy('tsi.searchitem_keyword');
		$rs = $srch->getResultSet();					
		return $this->db->fetch_all($rs);		
	}
	
	function getAddedToCartCount(){		
		$sql = "Select COUNT(DISTINCT user_id) as cart_count from (SELECT cart_user_id as user_id,count(cart_user_id) as count FROM `tbl_user_cart` group by cart_user_id 
				UNION ALL
				SELECT order_user_id as user_id,count(order_user_id) as count FROM `tbl_orders` group by order_user_id) tbl ";
		$rs = $this->db->query($sql);
		return $result=$this->db->fetch($rs);				
	}	
	function getUserOrderStatsCount($type=''){
		$cancelAndRefundedStatusArr=(array)Settings::getSetting("CONF_DEFAULT_CANCEL_ORDER_STATUS");		
		$srch = new SearchBase('tbl_order_products', 'torp');
		$srch->joinTable('tbl_orders', 'LEFT JOIN', 'tord.order_id =torp.opr_order_id', 'tord');
		switch(strtoupper($type)){
			case 'CANCEL_AND_REFUNDED':
				$srch->addCondition('opr_status', 'IN', $cancelAndRefundedStatusArr);	
			break;
			case 'REACHED_CHECKOUT':
				$srch->addCondition('order_payment_status', '=',0);				
			break;
			case 'purchased':
				$srch->addCondition('order_payment_status', 'IN',array(1,2));				
			break;
		}
		$srch->addMultipleFields(array('opr_id'));
		$srch->addGroupBy ('order_user_id');	
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();					
		return $srch->recordCount(); 
	}
	
	function getConversionStats(){		
		$srch = new SearchBase('tbl_users', 'torp');
		$srch->addMultipleFields(array('count(user_id) as total_users'));
		$srch->addCondition('user_is_deleted', '=',0);
		//$srch->addCondition('user_status', '=',1);
		$rs = $srch->getResultSet();	
		$res=$this->db->fetch($rs);
		$totalUser=$res['total_users'];
		$cartRes=$this->getAddedToCartCount();	
		$addedToCartCount=$cartRes["cart_count"];
		$purchasedCount=$this->getUserOrderStatsCount('purchased');
		$reachedToChecoutCount=$purchasedCount+$this->getUserOrderStatsCount('REACHED_CHECKOUT');
		$cancelAndRefundedUserCount=$this->getUserOrderStatsCount('CANCEL_AND_REFUNDED');
		$data=array(
			'added_to_cart'=>array('count'=>$addedToCartCount,'%age'=>round(($addedToCartCount*100)/$totalUser,2)),
			'reached_checkout'=>array('count'=>$reachedToChecoutCount,'%age'=>round(($reachedToChecoutCount*100)/$totalUser),2),
			'purchased'=>array('count'=>$purchasedCount,'%age'=>round(($purchasedCount*100)/$totalUser),2),
			'cancelled'=>array('count'=>$cancelAndRefundedUserCount,'%age'=>round(($cancelAndRefundedUserCount*100)/$totalUser),2),
		);
		return $data;		
	}	
}