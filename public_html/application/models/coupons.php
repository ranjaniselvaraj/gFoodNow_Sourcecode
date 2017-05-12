<?php
class Coupons extends Model {
	
	function __construct($user_id){
		$this->db = Syspage::getdb();
		$this->logged_user_id = intval($user_id);
    }
	
	function getCouponByCode($val) {
        $srch = new SearchBase('tbl_coupons', 'tc');
		$srch->addCondition('tc.coupon_code', '=',$val);
		$srch->addCondition('tc.coupon_status', '=',1);
		$srch->addCondition('tc.coupon_is_deleted', '=',0);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	public function getCoupon($code) {
		$status = true;
		$cartObj=new Cart($this->logged_user_id);
		$cart_sub_total=$cartObj->getSubTotal();
		
		$row_coupon_query = $this->db->query("SELECT * FROM `tbl_coupons` WHERE coupon_code = " . $this->db->quoteVariable($code) . " AND ((coupon_start_date = '0000-00-00' OR coupon_start_date <= CURRENT_DATE()) AND (coupon_end_date = '0000-00-00' OR coupon_end_date >= CURRENT_DATE())) AND coupon_status = '1' and coupon_is_deleted=0");
		$coupon_query = $this->db->fetch($row_coupon_query);
		if ($coupon_query){
			if ($coupon_query['coupon_min_order_value'] > $cart_sub_total) {
				$status = false;
			}
			$row_coupon_history_query = $this->db->query("SELECT COUNT(*) AS total FROM tbl_coupons_history ch WHERE ch.coupon_id = '" . (int)$coupon_query['coupon_id'] . "'");
			$coupon_history_query = $this->db->fetch($row_coupon_history_query);
			if ($coupon_query['coupon_uses'] > 0 && ($coupon_history_query['total'] >= $coupon_query['coupon_uses'])) {
				$status = false;
			}
			if (User::getLoggedUserId()) {
				$customer_id=User::getLoggedUserId();
				$row_coupon_history_query = $this->db->query("SELECT COUNT(*) AS total FROM tbl_coupons_history ch WHERE ch.coupon_id = '" . (int)$coupon_query['coupon_id'] . "' AND ch.customer_id = '" . (int)$customer_id . "'");
				$coupon_history_query = $this->db->fetch($row_coupon_history_query);
				if ($coupon_query['coupon_uses_customer'] > 0 && ($coupon_history_query['total'] >= $coupon_query['coupon_uses_customer'])) {
					$status = false;
				}
			}
			
			// Products
			$coupon_product_data = array();
			$rs_coupon_products = $this->db->query("SELECT * FROM `tbl_coupon_products` WHERE dctc_coupon_id = '" . (int)$coupon_query['coupon_id'] . "'");
			while ($coupon_products=$this->db->fetch($rs_coupon_products)){	
				$coupon_product_data[] = $coupon_products['dctc_product_id'];
			}
			// Categories
			$coupon_category_data = array();
			$rs_coupon_category = $this->db->query("SELECT * FROM `tbl_coupon_categories` WHERE dctc_coupon_id = '" . (int)$coupon_query['coupon_id'] . "'");
			while ($coupon_categories=$this->db->fetch($rs_coupon_category)){	
				$coupon_category_data[] = $coupon_categories['dctc_category_id'];
			}
			$product_data = array();
			if ($coupon_product_data || $coupon_category_data) {
				foreach ($cartObj->getProducts() as $product) {
					if (in_array($product['product_id'], $coupon_product_data)) {
						$product_data[] = $product['product_id'];
						continue;
					}
					foreach ($coupon_category_data as $category_id) {
						$pObj= new Products();
						$coupon_category_query = $pObj->getData((int)$product['product_id'],array("category"=>(int)$category_id));
						if ($coupon_category_query) {
							$product_data[] = $product['product_id'];
							continue;
						}
					}
				}
				if (!$product_data) {
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
	
	function getCoupons() {
		$srch = new SearchBase('tbl_coupons', 'tc');
		$srch->addCondition('tc.coupon_is_deleted', '=', 0);
		$srch->addCondition('tc.coupon_status', '=', 1);
		$srch->addOrder('tc.coupon_end_date', 'DESC');
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return false;
		}
        return $this->db->fetch_all($rs);
    }
	
	
   
}