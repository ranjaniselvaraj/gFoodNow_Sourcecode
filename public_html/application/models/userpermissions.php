<?php
class UserPermissions extends Model {
	
	function __construct(){
		$this->db = Syspage::getdb();
    }
	public function canAddProducts($userId = 0){
		if (!Settings::getSetting("CONF_ENABLE_SELLER_SUBSCRIPTION")) return true;
		if($userId <=0){ return false; }
		$subscriptionObj = new subscriptionorders();
		$orderDetails= $subscriptionObj->getActiveSubscriptionDetails($userId);
		$user_limit=  $orderDetails['mporder_merchantpack_max_products']; 
		$products = new Products();
		$totalProducts  =  $products->getTotalProductsAddedByUser($userId);
		//die($user_limit."#".$totalProducts);
		if($totalProducts<$user_limit){
			return true;
		}else{
			return false;
		}
		
	}
	public function canAddProductImages($userId = 0,$product_id,$session_id){
		if (!Settings::getSetting("CONF_ENABLE_SELLER_SUBSCRIPTION")) return true;
		if($userId <=0){ return false; }
		$subscriptionObj = new subscriptionorders();
		$orderDetails= $subscriptionObj->getActiveSubscriptionDetails();
		$user_limit=  $orderDetails['mporder_merchantpack_max_pimages']; 
		$products = new Products();
		
		 $totalProducts  =  $products->getTotalImagesAddedByUser($userId,$product_id,$session_id); 
		if($totalProducts<$user_limit){
			return true;
		}else{
			return false;
		}
		
	}
	static function canAccessProductsArea($user_id){
		if (!Settings::getSetting("CONF_ENABLE_SELLER_SUBSCRIPTION")) return true;
		$user_id = intval($user_id);
		if($user_id < 1) return false;
		$subscription_status_assoc_arr = subscriptionorders::subscription_status_assoc_arr();
		$subscriptionObj = new subscriptionorders();
			$order_filters = array(
					'user'						=>	$user_id,
					'subscription_with_in_date'	=>	date('Y-m-d'),
					'subscription_status'	=>	Settings::getSetting("CONF_ACTIVE_SUBSCRIPTION_STATUS"),
					'pagesize'				=>	1
				);
				$orders = $subscriptionObj->getSubscriptionOrders( $order_filters );
				if(!$orders){
					return false;
				}
				
		return true;
	}
}?>
	