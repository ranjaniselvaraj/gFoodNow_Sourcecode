<?php
class Subscription extends Model {
	private $subscriptionData = array();
	function __construct($user_id){
		$this->db = Syspage::getdb();
		$this->User=new User();
		if ($this->User->isUserLogged() || ($user_id>0)){
			if ($user_id>0){
				$this->cart_user_id = intval($user_id);
			}else{
				$this->cart_user_id= $this->User->getLoggedUserId();
			}
		}else{
			$this->cart_user_id=session_id();
		}
		
		/************** Start Subscription Module ******************/	
		$rs = $this->db->query("SELECT * FROM `tbl_subscription_cart` WHERE scart_user_id = '" . $this->cart_user_id . "'");
		if ($row=$this->db->fetch($rs)){
			$this->SYSTEM_ARR['subscription_cart']=unserialize($row["scart_details"]);
			$this->SYSTEM_ARR['shopping_cart_subscription']=$this->SYSTEM_ARR['subscription_cart']['shopping_cart_subscription'];
			unset($this->SYSTEM_ARR['subscription_cart']['shopping_cart_subscription']);
		}
		
		if (!isset($this->SYSTEM_ARR['subscription_cart']) || !is_array($this->SYSTEM_ARR['subscription_cart'])) {
			$this->SYSTEM_ARR['subscription_cart'] = array();
		}
		
		/************** End Subscription Module ******************/	
    }
	/**** Start Subscription Module ***/
	public function removeSubscriptionCartKey($key) {
		unset($this->SYSTEM_ARR['cart'][$key]);
		$this->updateUserSubscriptionCart();
		return true;
	}
	public function updateSubscriptionCartWalletOption($val) {
		$this->SYSTEM_ARR['shopping_cart_subscription']['Pay_from_wallet'] = $val;
		$this->updateUserSubscriptionCart();
		return true;
	}
	
	function addSubscription($sub_package_id){
		$this->subsriptionData = array();
		$product= array();
	    $product['merchantsubpack_id'] = (int)$sub_package_id;
		$key = base64_encode(serialize($product));
		$this->SYSTEM_ARR['subscription_cart']=array();
		$this->SYSTEM_ARR['subscription_cart'][$key] = 	1;
		$this->updateUserSubscriptionCart();
		return true;
	}
	
	function remove_subscription($key){
		
		$this->subscriptionData = array();
		$cart_products=$this->getSubscription();
		
		$short_key = $this->array_find_deep($cart_products,$key);
		if (!empty($short_key))
			$key=$short_key;
			
		unset($this->SYSTEM_ARR['subscription_cart'][$key]);
		unset($this->SYSTEM_ARR['shopping_cart_subscription']);
		$this->updateUserSubscriptionCart();
		return true;
	}
	
	function array_find_deep($array, $search){
	    foreach($array as $key => $value) {
			if (strcasecmp(md5($key),$search)==0){
				return $key;
			}
    	}
	    return array();
	}
	
	function getSubscription(){
		if (!$this->subscriptionData) {
			foreach ($this->SYSTEM_ARR['subscription_cart'] as $key => $quantity) {
				$product = unserialize(base64_decode($key));
				//Utilities::printArray($product);
				$merchantsubpack_id = $product['merchantsubpack_id'];	
				$subPackObj = new SubPackages();
				$product_info = $subPackObj->getData($merchantsubpack_id,array("status"=>1));
				if ($product_info) {
					$price = $product_info['merchantsubpack_actual_price'];
					$this->subscriptionData[$key] = array(
						'key'             => $key,
						'merchantsubpack_id'      => $product_info['merchantsubpack_id'],
						'merchantsubpack_merchantpack_id'      => $product_info['merchantsubpack_merchantpack_id'],
						'merchantsubpack_recurring_price'      => $product_info['merchantsubpack_recurring_price'],
						'merchantsubpack_subs_frequency'      => $product_info['merchantsubpack_subs_frequency'],
						'merchantsubpack_subs_period'      => $product_info['merchantsubpack_subs_period'],
						'merchantsubpack_actual_price'      => $product_info['merchantsubpack_actual_price'],
						'merchantpack_commission_rate'      => $product_info['merchantpack_commission_rate'],
						'merchantpack_max_products'      => $product_info['merchantpack_max_products'],
						'merchantpack_images_per_product'      => $product_info['merchantpack_images_per_product'],
						'merchantsubpack_total_occurrance'      => $product_info['merchantsubpack_total_occurrance'],
						'merchantpack_name'            => $product_info['merchantpack_name'],
						'merchantpack_description'            => $product_info['merchantpack_description'],
						'quantity'        => $quantity,
						'price'           => ($price),
						'total'           => ($price) * $quantity,
						'net_total'  	   => ($price ) * $quantity ,
						'free_trial' => $this->SYSTEM_ARR['shopping_cart_subscription']['free_trial']
					);
				} else {
					$this->removeSubscriptionCartKey($key);
				}
			}
		}
		return $this->subscriptionData;
	}
	
	
	
	public function getSubscriptionCartFinancialSummary() {
		$cart_total = 0;
		$subPackObj = new SubPackages();
		$net_total_without_discount = 0;
		$products = $this->getSubscription();
		foreach ($products as $product) {
			$cart_product_price_total += $product['total'];
			$cart_total += $product['total'];
			$net_total_without_discount += $product['total'];
			$net_recurring_without_discount = $product['merchantsubpack_recurring_price'];
		}	
		
		$net_total_after_discount = $net_total_without_discount;
		$net_recurring_after_discount = $net_recurring_without_discount;
	    $cart_discount=self::getSubscriptionCouponDiscounts(); 
		$cart_max_rewards_points = $cart_total;
		
		if (is_array($cart_discount) && (!empty($cart_discount))){
			$net_total_after_discount= $net_total_without_discount-$cart_discount["value"];
			$cart_max_rewards_points = $cart_max_rewards_points - $cart_discount["value"];
			if($cart_discount["validFor"]=='2'){
				 $net_recurring_after_discount = $net_recurring_after_discount- $cart_discount["recurring_discount"];
			}
			if ($cart_discount["shipping"]){
				$net_total_after_discount= $net_total_without_discount-$cart_discount["value"]-$cart_shipping_total;
				$cart_shipping_total=0;
			}
		}
		
		$cart_reward_points = $this->getSubscriptionCartRewardPoints();
		if (isset($this->cart_user_id)) {
			$rewardObj=new Rewards();
			$user_total_reward_points = $rewardObj->getUserRewardsPointsBalance($this->cart_user_id);
			if ($cart_reward_points>$user_total_reward_points){
				$this->updateSubscriptionCartRewardPoints(abs($user_total_reward_points));
			}
			elseif ($cart_reward_points>$cart_max_rewards_points){
				$this->updateSubscriptionCartRewardPoints($cart_max_rewards_points);
			}
			$cart_reward_points = $this->getSubscriptionCartRewardPoints();
		}
		$net_total_after_discount = $net_total_after_discount - $cart_reward_points;
		$userObj=new User();
		$user_balance=$userObj->getUserBalance($this->cart_user_id);
		$order_credits_charge=$this->isSubscriptionCartUserWalletSelected()?min($net_total_after_discount,$user_balance):0;
		$order_payment_gateway_charge=$net_total_after_discount-$order_credits_charge;
		$cart_summary=array(
					  'cart_total'  => $cart_total,
					  'cart_wallet_enabled' => $this->isSubscriptionCartUserWalletSelected(),
					  'cart_discounts' => $cart_discount,
					  'net_total_without_discount' => $net_total_without_discount,
					  'net_total_after_discount' => $net_total_after_discount,
					  'order_credits_charge' => $order_credits_charge,
					  'cart_actual_paid' => max(round($net_total_after_discount,2),0),
					  'cart_max_rewards_points' => floor($cart_max_rewards_points),
					  'reward_points' => $cart_reward_points,
					  'order_payment_gateway_charge' => $order_payment_gateway_charge,
					  'order_net_charged' => $net_total_after_discount,
					  'net_recurring_amount' => max(round($net_recurring_without_discount,2),0),
					  'net_recurring_after_discount_amount' => max(round($net_recurring_after_discount,2),0),
					  'is_free_trial' => $this->SYSTEM_ARR['shopping_cart_subscription']['free_trial'],
					);
		return $cart_summary;
		
		
		
			/*'order_net_charged' => $net_total_without_discount,
			'cart_actual_paid' => max(round($chargeabelAmount,2),0),
			'net_recurring_amount' => max(round($net_recurring_without_discount,2),0),
			'net_recurring_after_discount_amount' => max(round($net_recurring_after_discount,2),0),*/
			
	}
	
	
	public function isSubscriptionCartUserWalletSelected() {
		return (isset($this->SYSTEM_ARR['shopping_cart_subscription']['Pay_from_wallet']) && intval($this->SYSTEM_ARR['shopping_cart_subscription']['Pay_from_wallet'])==1)?1:0;
	}
	
	public function getSubscriptionCartDiscountCoupon() {
		return $this->SYSTEM_ARR['shopping_cart_subscription']['discount_coupon'];
	}
	
	public function getSubscriptionSubTotal() {
		$total = 0;
		foreach ($this->getSubscription() as $product) {
			$total += $product['total'];
		}
		return $total;
	}
	
	
	public function getSubscriptionCouponDiscounts() {
		$couponObj=new SubscriptionCoupon($this->cart_user_id);
		$coupon= self::getSubscriptionCartDiscountCoupon();
		$coupon_info = $couponObj->getCoupon($coupon);
		$cart_sub_total=self::getSubscriptionSubTotal();
		if ($coupon_info){
				$discount_total = 0;
				if (!$coupon_info['products']) {
					$sub_total = $cart_sub_total;
				} else {
					$sub_total = 0;
					foreach ($this->getSubscription() as $product) {
						if (in_array($product['merchantsubpack_id'], $coupon_info['products'])) {
							$sub_total += $product['merchantsubpack_actual_price'];
						}
					}
				}
				if ($coupon_info['subscoupon_discount_type'] == 'F') {
					$coupon_info['subscoupon_discount_value'] = min($coupon_info['subscoupon_discount_value'], $sub_total);
				}
				foreach ($this->getSubscription() as $product) {
					$discount = 0;
					$recurring_discount = 0;
					if (!$coupon_info['products']) {
						$status = true;
					} else {
						if (in_array($product['merchantsubpack_id'], $coupon_info['products'])) {
							$status = true;
						} else {
							$status = false;
						}
					}
				if ($status) {
						if ($coupon_info['subscoupon_discount_type'] == 'F') {
							$discount = $coupon_info['subscoupon_discount_value'] * ($product['total'] / $sub_total);
							if($coupon_info['subscoupon_discount_valid_for']==2){
								$recurring_discount = $coupon_info['subscoupon_discount_value'] * ($product['merchantsubpack_recurring_price'] / $sub_total);
							}
						} elseif ($coupon_info['subscoupon_discount_type'] == 'P') {
							$discount = $product['merchantsubpack_actual_price'] / 100 * $coupon_info['subscoupon_discount_value'];
							if($coupon_info['subscoupon_discount_valid_for']==2){
								$recurring_discount =$product['merchantsubpack_recurring_price'] / 100 * $coupon_info['subscoupon_discount_value'];
							}
						}
					}
					$discount_total += $discount;
				}
				if ($discount_total > $coupon_info['subscoupon_max_discount_value']) {
					$discount_total = $coupon_info['subscoupon_max_discount_value'];
				}if ($recurring_discount > $coupon_info['subscoupon_max_discount_value']) {
					$recurring_discount = $coupon_info['subscoupon_max_discount_value'];
				}
				$coupon_data = array(
					'type'       => 'coupon',
					'code'      => $coupon_info["subscoupon_code"],
					'value'      => $discount_total,
					'recurring_discount'      => $recurring_discount,
					'validFor'      =>$coupon_info["subscoupon_discount_valid_for"],
				);
				if($coupon_info["subscoupon_discount_valid_for"]==2){
					$coupon_data ['recurring_code']=$coupon_info["subscoupon_code"] ;
				}
		}
		return $coupon_data;
	}
	
	
	
	function SubscriptionClear() {
		$this->data = array();
		$this->SYSTEM_ARR['subscription_cart'] = array();
		$this->SYSTEM_ARR['shopping_cart_subscription'] = array();
		$this->updateUserSubscriptionCart();
	}
	
	public function updateSubscription($mporder_id){
		$this->SYSTEM_ARR['shopping_cart_subscription']['update_subscription_id']=$mporder_id;
		$this->updateUserSubscriptionCart();
		return true;
	}
	
	static function isUpdatingSubscription(){
		$scart = new Subscription();
		return $scart->SYSTEM_ARR['shopping_cart_subscription']['update_subscription_id'];
	}
	
	public function updateUserSubscriptionCart() {
			if (isset($this->cart_user_id)) {
				$user_id=$this->cart_user_id;
				$record = new TableRecord('tbl_subscription_cart');
				$cart_arr=$this->SYSTEM_ARR['subscription_cart'];
				if (is_array($this->SYSTEM_ARR['shopping_cart_subscription']) && (!empty($this->SYSTEM_ARR['shopping_cart_subscription']))){
					
					$cart_arr["shopping_cart_subscription"]=$this->SYSTEM_ARR['shopping_cart_subscription'];
				}
				
				$record->assignValues(array("scart_user_id"=>$user_id,"scart_details"=>serialize($cart_arr)));
				$sqlquery=$record->getinsertquery();
				$sqlquery = $sqlquery." on duplicate KEY UPDATE scart_details='".addslashes(serialize($cart_arr))."'";
				//die($sqlquery);
				if (!$this->db->query($sqlquery)) {
					Message::addErrorMessage($db->getError());
					throw new Exception('');
				}
			}
	}
	public function updateSubscriptionCartDiscountCoupon($val) {
		$this->SYSTEM_ARR['shopping_cart_subscription']['discount_coupon'] = $val;
		$this->updateUserSubscriptionCart();
		return true;
	}
	public function RemoveSubscriptionCartDiscountCoupon() {
		unset($this->SYSTEM_ARR['shopping_cart_subscription']['discount_coupon']);
		$this->updateUserSubscriptionCart();
		return true;
	}
	
	public function updateSubscriptionCartRewardPoints($val) {
		$this->SYSTEM_ARR['shopping_cart_subscription']['reward_points'] = $val;
		$this->updateUserSubscriptionCart();
		return true;
	}
	
	public function getSubscriptionCartRewardPoints() {
		return $this->SYSTEM_ARR['shopping_cart_subscription']['reward_points'];
	}
	
	
	public function applyFreeTrialPackage() {
		$this->SYSTEM_ARR['shopping_cart_subscription']['free_trial'] = 1;
		$this->updateUserSubscriptionCart();
		return true;
	}
	public function removeFreeTrialPackage() {
		unset($this->SYSTEM_ARR['shopping_cart_subscription']['free_trial']);
		$this->updateUserSubscriptionCart();
		return true;
	}
	
	
	function subscription_invoice_format(){
		$value=Settings::getSetting("CONF_SUBSCRIPTION_INVOICE_FORMAT");
		$new_value=(int)$value+1;
		$new_value=str_pad($new_value,7,'0',STR_PAD_LEFT);
		if (!$this->db->update_from_array('tbl_configurations', array('conf_val' => $new_value),
		array('smt' => 'conf_var = ? ', 'vals' => array('CONF_SUBSCRIPTION_INVOICE_FORMAT')))){
			$this->error = $this->db->getError();
		}
		$new_value='S'.date("ymd")."-".$new_value;
		return $new_value;
	}
	/************** End Subscription Module ******************/	
	
}