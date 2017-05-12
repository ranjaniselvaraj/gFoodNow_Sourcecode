<?php
class Subscriptioncoupon extends Model {
	
		function __construct($user_id){
		$this->db = Syspage::getdb();
		$this->logged_user_id = intval($user_id);
    }
	
	
	public function getCoupon($code) {
	
		$status = true;
		$cartObj=new Subscription($this->logged_user_id);		
		$cart_sub_total=$cartObj->getSubscriptionSubTotal();
		$cartSubscription = $cartObj->getSubscription();
		$row_coupon_query = $this->db->query("SELECT * FROM `tbl_subscription_coupons` WHERE subscoupon_code = " . $this->db->quoteVariable($code) . " AND ((subscoupon_start_date = '0000-00-00' OR subscoupon_start_date <= CURRENT_DATE()) AND (subscoupon_end_date = '0000-00-00' OR subscoupon_end_date >= CURRENT_DATE())) AND subscoupon_active = '1' and subscoupon_is_deleted=0");
		$coupon_query = $this->db->fetch($row_coupon_query);
		if ($coupon_query){
			
			$row_coupon_history_query = $this->db->query("SELECT COUNT(*) AS total FROM tbl_subscription_coupons_history ch WHERE ch.subscouponhistory_subscoupon_id = '" . (int)$coupon_query['subscoupon_id'] . "'");
			$coupon_history_query = $this->db->fetch($row_coupon_history_query);
			if ($coupon_query['subscoupon_uses_per_coupon'] > 0 && ($coupon_history_query['total'] >= $coupon_query['subscoupon_uses_per_coupon'])) {
				$status = false;
			}
			if (User::getLoggedUserId()) {
				$customer_id=User::getLoggedUserId();
				$row_coupon_history_query = $this->db->query("SELECT COUNT(*) AS total FROM tbl_subscription_coupons_history ch WHERE ch.subscouponhistory_subscoupon_id = '" . (int)$coupon_query['subscoupon_id'] . "' AND ch.subscouponhistory_customer_id = '" . (int)$customer_id . "'");
				$coupon_history_query = $this->db->fetch($row_coupon_history_query);
				if ($coupon_query['subscoupon_uses_per_customer'] > 0 && ($coupon_history_query['total'] >= $coupon_query['subscoupon_uses_per_customer'])) {
					$status = false;
				}
			}
			$product_data = array();
		
			
				foreach ($cartObj->getSubscription() as $product) {
				
					if ($product['merchantsubpack_id'] == $coupon_query['subscoupon_merchantsubpack']) {
							
						// $status=true;
						$product_data[] = $product['merchantsubpack_id'];
						continue;
					}
					else{
						 $status = false;
					}
				}
	
			
		}else {
			$status = false;
		}
	
		if ($status)
			return array_merge($coupon_query,array("products"=>$product_data));
		
		//return $coupon_query;
		
	}
	function getCouponByCode($val) {
        $srch = new SearchBase('tbl_subscription_coupons', 'tc');
		$srch->addCondition('tc.subscoupon_code', '=',$val);
		$srch->addCondition('tc.subscoupon_active', '=',1);
		$srch->addCondition('tc.subscoupon_is_deleted', '=',0);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
}