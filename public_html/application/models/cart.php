<?php
class Cart extends Model {
	private $data = array();
	function __construct($user_id){
		$this->db = Syspage::getdb();
		$this->Products=new Products();
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
        if (isset($this->cart_user_id)) {
            $rs = $this->db->query("SELECT * FROM `tbl_user_cart` WHERE cart_user_id = '" . $this->cart_user_id . "'");
			if ($row=$this->db->fetch($rs)){
				$this->SYSTEM_ARR['cart']=unserialize($row["cart_details"]);
				$this->SYSTEM_ARR['shopping_cart']=$this->SYSTEM_ARR['cart']['shopping_cart'];
				unset($this->SYSTEM_ARR['cart']['shopping_cart']);
			}
        }
		
		
		if (!isset($this->SYSTEM_ARR['cart']) || !is_array($this->SYSTEM_ARR['cart'])) {
			$this->SYSTEM_ARR['cart'] = array();
		}
    }
	
	public function updateUserCart() {
			if (isset($this->cart_user_id)) {
				$user_id=$this->cart_user_id;
				$record = new TableRecord('tbl_user_cart');
				$cart_arr=$this->SYSTEM_ARR['cart'];
				if (is_array($this->SYSTEM_ARR['shopping_cart']) && (!empty($this->SYSTEM_ARR['shopping_cart']))){
					$cart_arr["shopping_cart"]=$this->SYSTEM_ARR['shopping_cart'];
					//$cart_arr=array_combine($cart_arr,$this->SYSTEM_ARR['shopping_cart']);
				}
				$record->assignValues(array("cart_user_id"=>$user_id,"cart_details"=>serialize($cart_arr)));
				$sqlquery=$record->getinsertquery();
				$sqlquery = $sqlquery." on duplicate KEY UPDATE cart_details='".addslashes(serialize($cart_arr))."'";
				if (!$this->db->query($sqlquery)) {
					Message::addErrorMessage($db->getError());
					throw new Exception('');
				}
			}
	}
	
	function getError() {
        return $this->error;
    }
	
	public function getProducts() {
		
		if (!$this->data) {
			foreach ($this->SYSTEM_ARR['cart'] as $key => $quantity) {
				$product = unserialize(base64_decode($key));
				$product_id = $product['product_id'];
				$stock = true;
				$is_shipping_selected=false;
				// Options
				if (!empty($product['option'])) {
					$options = $product['option'];
				} else {
					$options = array();
				}
								
				// Products Shipping
				if (!empty($product['shipping_id'])) {
					$shipping_id = $product['shipping_id'];
				} else {
					$shipping_id = 0;
				}
				
				
				$pObj= new Products();
				$pObj->joinWithDetailTable();
				$pObj->joinWithCategoryTable();
				$pObj->joinWithBrandsTable();
				$pObj->joinWithPromotionsTable();
				$pObj->addSpecialPrice();
				$pObj->selectFields(array('tp.*','tpd.*','ts.shop_id','ts.shop_user_id','ts.shop_name','ts.shop_title','ts.shop_enable_cod_orders','tu.user_id','tu.user_name','tu.user_username','tu.user_email','IF(prod_stock >0, "1", "0" ) as available','tpb.brand_name'));
				$product_info = $pObj->getData($product_id,array("available_date"=>1));
				
				/*print_r($product_info);
				die();*/
				if ($product_info) {
					
					
					$option_price = 0;
					$option_points = 0;
					$option_weight = 0;
					$product_stock = $product_info['prod_stock'];
					$option_data = array();
					foreach ($options as $product_option_id => $value) {
						$option_query = $this->db->query("SELECT po.product_option_id, po.option_id, o.option_name as name, o.option_type as type FROM tbl_product_option po LEFT JOIN tbl_options o ON (po.option_id = o.option_id) WHERE po.product_option_id = '" . (int)$product_option_id . "' AND po.product_id = '" . (int)$product_id . "'");
						if($option_query_row = $this->db->fetch($option_query)) {	
							if ($option_query_row['type'] == 'select' || $option_query_row['type'] == 'radio' || $option_query_row['type'] == 'image') {
								
								
								$option_value_query = $this->db->query("SELECT pov.option_value_id, ov.option_value_name as name, pov.quantity, pov.subtract, pov.price, pov.price_prefix,pov.weight, pov.weight_prefix FROM tbl_product_option_value pov LEFT JOIN tbl_option_values ov ON (pov.option_value_id = ov.option_value_id) WHERE pov.product_option_value_id = '" . (int)$value . "' AND pov.product_option_id = '" . (int)$product_option_id . "'");
								if($option_value_row = $this->db->fetch($option_value_query)) {	
									if ($option_value_row['price_prefix'] == '+') {
										$option_price += $option_value_row['price'];
									} elseif ($option_value_row['price_prefix'] == '-') {
										$option_price -= $option_value_row['price'];
									}
									
									if ($option_value_row['weight_prefix'] == '+') {
										$option_weight += $option_value_row['weight'];
									} elseif ($option_value_row['weight_prefix'] == '-') {
										$option_weight -= $option_value_row['weight'];
									}
									if ($option_value_row['subtract'] && (!$option_value_row['quantity'] || ($option_value_row['quantity'] < $quantity))) {
										$stock = false;
									}
									
									if ($option_value_row['quantity']<$product_stock){
										$product_stock = $option_value_row['quantity'];
									}
									
									
									$option_data[] = array(
										'product_option_id'       => $product_option_id,
										'product_option_value_id' => $value,
										'option_id'               => $option_query_row['option_id'],
										'option_value_id'         => $option_value_row['option_value_id'],
										'name'                    => $option_query_row['name'],
										'value'                   => $option_value_row['name'],
										'type'                    => $option_query_row['type'],
										'quantity'                => $option_value_row['quantity'],
										'subtract'                => $option_value_row['subtract'],
										'price'                   => $option_value_row['price'],
										'price_prefix'            => $option_value_row['price_prefix'],
										//'points'                  => $option_value_row['points'],
										//'points_prefix'           => $option_value_row['points_prefix'],
										'weight'                  => $option_value_row['weight'],
										'weight_prefix'           => $option_value_row['weight_prefix']
									);
								}
								
							} elseif ($option_query_row['type'] == 'checkbox' && is_array($value)) {
								foreach ($value as $product_option_value_id) {
									$option_value_query = $this->db->query("SELECT pov.option_value_id, ov.option_value_name as name, pov.quantity, pov.subtract, pov.price, pov.price_prefix,  pov.weight, pov.weight_prefix FROM tbl_product_option_value pov LEFT JOIN tbl_option_values ov ON (pov.option_value_id = ov.option_value_id) WHERE pov.product_option_value_id = '" . (int)$product_option_value_id . "' AND pov.product_option_id = '" . (int)$product_option_id . "'");
									if($option_value_row = $this->db->fetch($option_value_query)) {	
										if ($option_value_row['price_prefix'] == '+') {
											$option_price += $option_value_row['price'];
										} elseif ($option_value_row['price_prefix'] == '-') {
											$option_price -= $option_value_row['price'];
										}
										if ($option_value_row['weight_prefix'] == '+') {
											$option_weight += $option_value_row['weight'];
										} elseif ($option_value_row['weight_prefix'] == '-') {
											$option_weight -= $option_value_row['weight'];
										}
										if ($option_value_row['subtract'] && (!$option_value_row['quantity'] || ($option_value_row['quantity'] < $quantity))) {
											$stock = false;
										}
										
										if ($option_value_row['quantity']<$product_stock){
											$product_stock = $option_value_row['quantity'];
										}
										$option_data[] = array(
											'product_option_id'       => $product_option_id,
											'product_option_value_id' => $product_option_value_id,
											'option_id'               => $option_query_row['option_id'],
											'option_value_id'         => $option_value_row['option_value_id'],
											'name'                    => $option_query_row['name'],
											'value'                   => $option_value_row['name'],
											'type'                    => $option_query_row['type'],
											'quantity'                => $option_value_row['quantity'],
											'subtract'                => $option_value_row['subtract'],
											'price'                   => $option_value_row['price'],
											'price_prefix'            => $option_value_row['price_prefix'],
											//'points'                  => $option_value_row['points'],
											//'points_prefix'           => $option_value_row['points_prefix'],
											'weight'                  => $option_value_row['weight'],
											'weight_prefix'           => $option_value_row['weight_prefix']
										);
									}
								}
							} elseif ($option_query_row['type'] == 'text' || $option_query_row['type'] == 'textarea' || $option_query_row['type'] == 'file' || $option_query_row['type'] == 'date' || $option_query_row['type'] == 'datetime' || $option_query_row['type'] == 'time') {
								$option_data[] = array(
									'product_option_id'       => $product_option_id,
									'product_option_value_id' => '',
									'option_id'               => $option_query_row['option_id'],
									'option_value_id'         => '',
									'name'                    => $option_query_row['name'],
									'value'                   => $value,
									'type'                    => $option_query_row['type'],
									'quantity'                => '',
									'subtract'                => '',
									'price'                   => '',
									'price_prefix'            => '',
									//'points'                  => '',
									//'points_prefix'           => '',
									'weight'                  => '',
									'weight_prefix'           => ''
								);
							}
						}
					}

					$price = $product_info['prod_sale_price'];
					// Product Discounts
					$discount_quantity = 0;
					foreach ($this->SYSTEM_ARR['cart'] as $key_2 => $quantity_2) {
						$product_2 = (array)unserialize(base64_decode($key_2));
						if ($product_2['product_id'] == $product_id) {
							$discount_quantity += $quantity_2;
						}
					}
					
					// Product Specials
					$product_special_query = $this->db->query("SELECT pspecial_price as price FROM tbl_product_specials WHERE pspecial_product_id = '" . (int)$product_id . "' AND ((pspecial_start_date = '0000-00-00' OR pspecial_start_date <= '".date('Y-m-d')."') AND (pspecial_end_date = '0000-00-00' OR pspecial_end_date >= '".date('Y-m-d')."')) ORDER BY pspecial_priority ASC, pspecial_price ASC LIMIT 1");
					if($product_special = $this->db->fetch($product_special_query)) {	
						$price = $product_special['price'];
					}
					
					$product_discount_query = $this->db->query("SELECT pdiscount_price as price FROM tbl_product_discounts WHERE pdiscount_product_id = '" . (int)$product_id . "' AND pdiscount_qty <= '" . (int)$discount_quantity . "' AND ((pdiscount_start_date = '0000-00-00' OR pdiscount_start_date <= '".date('Y-m-d')."') AND (pdiscount_end_date = '0000-00-00' OR pdiscount_end_date >= '".date('Y-m-d')."')) ORDER BY pdiscount_qty DESC, pdiscount_priority ASC, pdiscount_price ASC LIMIT 1");
					if($product_discount = $this->db->fetch($product_discount_query)) {	
						$price = $product_discount['price'];
					}
										
					
					
					// Stock
					if (!$product_info['prod_stock'] || ($product_info['prod_stock'] < $quantity)) {
						$stock = false;
					}
					
					// Self Product
					$self_product = false;
					if ($product_info['shop_user_id']==$this->cart_user_id) {
						$self_product = true;
					}
					
					// Shipping Option
					if (isset($product['shipping_id']) && (intval($product['shipping_id'])>0)) {
						$is_shipping_selected = true;
					}
					
										
					
					//$shipping_address = $this->User->getUserAddress(self::getCartShippingAddress(), $this->getLoggedUserId());
					$shipping_address = $this->User->getAddress(self::getCartShippingAddress());
					$billing_address = $this->User->getAddress(self::getCartBillingAddress());
					
					$this->Shop = new Shops();
                    $seller_address = $this->Shop->getShopAddress($product_info['prod_shop']);
					
					$pObj= new Products();
					$shipping_options = $pObj->getProductShippingRates($product_info['prod_id'],array("country"=>$shipping_address["ua_country"]));
					if (!empty($shipping_options)){
						$shipping_options=array_combine(array_column($shipping_options, 'pship_id'),$shipping_options);
					}
					
					$selected_shipping_option=$shipping_options[$shipping_id];
					
					$shipping_cost=$product_info["prod_ship_free"]==0?$selected_shipping_option["pship_charges"]*1+($selected_shipping_option["pship_additional_charges"]*($quantity-1)):0;
					
					
					$is_ship_station_selected = false;
                    if (isset($product['selected_shipping_option']) && count($product['selected_shipping_option']) == 3) {
                        $selected_shipping_option["pship_charges"] = $product['selected_shipping_option']['shipping_cost'];
                        $selected_shipping_option["pship_additional_charges"] = 0;
                        $is_shipping_selected = true;
						//die($product['selected_shipping_option']["shipping_services"]);
                        $shippingLabel = str_replace("_", " ", $product['selected_shipping_option']["shipping_services"]);
                        $selected_shipping_option['sduration_label'] = ucwords($shippingLabel);
                        $is_ship_station_selected = true;
                        $shipping_cost = $product_info["prod_ship_free"] == 0 ? $product['selected_shipping_option']['shipping_cost'] : 0;
                    }
					
					if(($price+$option_price)<0){
						$price=$option_price=0;
					}
					$tax=round(($price + $option_price)*Settings::getSetting("CONF_SITE_TAX")/100,2);
					
					$pObj= new Products();
					$commission_percentage= $pObj->getProductCommission($product_id);
					//$commission=min(round(($price + $option_price+$shipping_cost)*$commission_percentage/100,2),Settings::getSetting("CONF_MAX_COMMISSION"));
					//$commission=min((($price + $option_price+$shipping_cost)*$commission_percentage/100),Settings::getSetting("CONF_MAX_COMMISSION"));
					$commission=min((((($price + $option_price) * $quantity)+$shipping_cost)*$commission_percentage/100),Settings::getSetting("CONF_MAX_COMMISSION"));
					
					$affiliate_id = 0;
					$affiliate_commission_perc= 0;
					if (isset($this->cart_user_id)) {
						$user=new User();
						$user_details=$user->getUser(array('user_id'=>$this->cart_user_id, 'get_flds'=>array('user_id', 'user_affiliate_id')));
						
						if ($user_details['user_affiliate_id']>0) {
							$pObj = new Products();	
							$affiliate_id = $user_details['user_affiliate_id'];
							
							$affiliate_commission_perc = $pObj->getProductAffiliateCommission($product_id,$affiliate_id);
						}
					}
					$affiliate_commission=round(($price + $option_price)*$affiliate_commission_perc/100,2);
					$user_balance = $this->User->getUserBalance($product_info['user_id']);
					
					$this->data[$key] = array(
						'key'             => $key,
						'product_id'      => $product_info['prod_id'],
						'name'            => $product_info['prod_name'],
						'sku'             => $product_info['prod_sku'],
						'model'           => $product_info['prod_model'],
						'brand'           => $product_info['prod_brand'],
						'brand_name'      => $product_info['brand_name'],
						'shop_id'         => $product_info['prod_shop'],
						'shop_name'       => $product_info['shop_name'],
						'shipping'        => $product_info['prod_requires_shipping'],
						'image'           => $product_info['image_file'],
						'option'          => $option_data,
						'shipping_options'=> $shipping_options,
						'shipping_id' 	 => $shipping_id,
						'selected_shipping_option' => $selected_shipping_option,
						'shipping_address'=> $shipping_address,
						'seller_address' => $seller_address,
						'billing_address'=> $billing_address,
						'quantity'        => $quantity,
						'minimum'         => ($product_info['prod_min_order_qty']>0)?$product_info['prod_min_order_qty']:1,
						'subtract'        => $product_info['prod_track_inventory'],
						'stock'           => $stock,
						'self_product'    => $self_product,
						'product_stock'   => $product_stock,
						'is_shipping_selected' => $is_shipping_selected,
						'product_price'   => $price,
						'option_price'    => $option_price,
						'shipping_price'  => $shipping_cost,
						'shipping_free'  => $product_info["prod_ship_free"],
						'quantity_tax'  => $tax,
						
						'tax'  			 => $tax * $quantity,
						'price'           => ($price + $option_price),
						'total'           => ($price + $option_price) * $quantity,
						'net_total'  	   => ($price + $option_price + $tax) * $quantity + $shipping_cost,
						'net_total_wth_tax' => ($price + $option_price) * $quantity + $shipping_cost,
						'commission_percentage' => $commission_percentage,
						'commission'  	   => round($commission,2),
						'affiliate_id' => $affiliate_id,
						'affiliate_commission_percentage' => $affiliate_commission_perc,
						'affiliate_commission' => round($affiliate_commission*$quantity,2),
						'weight'          => ($product_info['prod_weight'] + $option_weight) * $quantity,
						'weight_class_id' => $product_info['prod_weight_class'],
						'length'          => $product_info['prod_length'],
						'width'           => $product_info['prod_width'],
						'height'          => $product_info['prod_height'],
						'length_class_id' => $product_info['prod_length_class'],
						'is_ship_station_selected' => $is_ship_station_selected,
						'cod'             => ($product_info['prod_enable_cod_orders'] && $product_info['shop_enable_cod_orders'] && ($product_info['prod_type']==1) && Settings::getSetting("CONF_ENABLE_COD_PAYMENTS") && ($user_balance>=Settings::getSetting("CONF_COD_MIN_WALLET_BALANCE")))?1:0,
					);
					
				} else {
					$this->removeCartKey($key);
				}
			}
		}
		return $this->data;
	}
	
	public function removeCartKey($key) {
		unset($this->SYSTEM_ARR['cart'][$key]);
		$this->updateUserCart();
		return true;
	}
	
	public function getCartFinancialSummary() {
		$cart_product_price_total = 0;
		$cart_product_option_total = 0;
		$cart_shipping_total = 0;
		$cart_tax_total = 0;
		$cart_total = 0;
		$net_total_without_discount = 0;
		$cart_wallet_credits=0;
		$products = $this->getProducts();
		foreach ($products as $product) {
			$cart_product_price_total += $product['product_price'];
			$cart_product_option_total += $product['option_price'];
			$cart_shipping_total += $product['shipping_price'];
			$cart_tax_total += $product['tax'];
			$cart_total += $product['total'];
			$net_total_without_discount += $product['net_total'];
			$total_commission += $product['commission'];
		}
		$net_total_after_discount = $net_total_without_discount;
		$cart_discount=self::getCouponDiscounts();
		$cart_max_rewards_points = $cart_total;
		if (is_array($cart_discount) && (!empty($cart_discount))){
			$net_total_after_discount= $net_total_without_discount-$cart_discount["value"];
			$cart_max_rewards_points = $cart_max_rewards_points - $cart_discount["value"];
			if ($cart_discount["shipping"]){
				$net_total_after_discount= $net_total_without_discount-$cart_discount["value"]-$cart_shipping_total;
				$cart_shipping_total=0;
			}
		}
		$cart_reward_points = $this->getCartRewardPoints();
		if (isset($this->cart_user_id)) {
			$rewardObj=new Rewards();
			$user_total_reward_points = $rewardObj->getUserRewardsPointsBalance($this->cart_user_id);
			$cart_sub_total=$this->getSubTotal();
			if ($cart_reward_points>$user_total_reward_points){
				$this->updateCartRewardPoints(abs($user_total_reward_points));
				
			}
			elseif ($cart_reward_points>$cart_max_rewards_points){
				$this->updateCartRewardPoints($cart_max_rewards_points);
			}
			$cart_reward_points = $this->getCartRewardPoints();
		}
		$net_total_after_discount = $net_total_after_discount - $cart_reward_points;
		$userObj=new User();
		$user_balance=$userObj->getUserBalance($this->cart_user_id);
		$order_credits_charge=$this->isCartUserWalletSelected()?min($net_total_after_discount,$user_balance):0;
		$order_payment_gateway_charge=$net_total_after_discount-$order_credits_charge;
		$order_cod_enabled = false;
		if (($order_payment_gateway_charge>=Settings::getSetting("CONF_MIN_COD_ORDER_LIMIT")) && ($order_payment_gateway_charge<=Settings::getSetting("CONF_MAX_COD_ORDER_LIMIT")) && $this->hasCODEligible()){
			$order_cod_enabled = true;
		}
		$cart_summary=array(
					  
					  'cart_product_price_total'  => $cart_product_price_total,
					  'cart_product_option_total' => $cart_product_option_total,
					  'cart_total' => $cart_total,
					  'cart_shipping_total' => $cart_shipping_total,
					  'cart_tax_total' => $cart_tax_total,
					  'cart_discounts' => $cart_discount,
					  'net_total_without_discount' => $net_total_without_discount,
					  'net_total_after_discount' => $net_total_after_discount,
					  'cart_wallet_enabled' => $this->isCartUserWalletSelected(),
					  'cart_actual_paid' => max(round($net_total_after_discount,2),0),
					  'site_commission' => $total_commission,
					  'cart_max_rewards_points' => floor($cart_max_rewards_points),
					  'reward_points' => $cart_reward_points,
					  'items'  => $this->countProducts(),
					  'order_payment_gateway_charge' => $order_payment_gateway_charge,
					  'order_cod_enabled' => $order_cod_enabled,
					  
					);
		
		return $cart_summary;
	}
	
	public function getSubTotal() {
		$total = 0;
		foreach ($this->getProducts() as $product) {
			$total += $product['total'];
		}
		return $total;
	}
	
	public function getCouponDiscounts() {
		$couponObj=new Coupons($this->cart_user_id);
		$coupon_info = $couponObj->getCoupon(self::getCartDiscountCoupon());
		$cart_sub_total=self::getSubTotal();
		if ($coupon_info){
				$discount_total = 0;
				if (!$coupon_info['product']) {
					$sub_total = $cart_sub_total;
				} else {
					$sub_total = 0;
					foreach ($this->getProducts() as $product) {
						if (in_array($product['product_id'], $coupon_info['product'])) {
							$sub_total += $product['total'];
						}
					}
				}
				
				if ($coupon_info['coupon_discount_type'] == 'F') {
					$coupon_info['coupon_discount_value'] = min($coupon_info['coupon_discount_value'], $sub_total);
				}
				
				foreach ($this->getProducts() as $product) {
					$discount = 0;
					if (!$coupon_info['products']) {
						$status = true;
					} else {
						if (in_array($product['product_id'], $coupon_info['products'])) {
							$status = true;
						} else {
							$status = false;
						}
					}
					if ($status) {
						if ($coupon_info['coupon_discount_type'] == 'F') {
							$discount = $coupon_info['coupon_discount_value'] * ($product['total'] / $sub_total);
						} elseif ($coupon_info['coupon_discount_type'] == 'P') {
							$discount = $product['total'] / 100 * $coupon_info['coupon_discount_value'];
							//print($discount."#");
						}
					}
					$discount_total += $discount;
				}
				// If discount greater than total
				if ($discount_total > $coupon_info['coupon_max_discount_value']) {
					$discount_total = $coupon_info['coupon_max_discount_value'];
				}
				$coupon_data = array(
					'type'       => 'coupon',
					'code'      => $coupon_info["coupon_code"],
					'value'      => $discount_total,
					'shipping'   => $coupon_info["coupon_free_shipping"],
				);
		}
		return $coupon_data;
	}
	
	public function clear() {
		$this->data = array();
		$this->SYSTEM_ARR['cart'] = array();
		$this->SYSTEM_ARR['shopping_cart'] = array();
		unset($_SESSION['shopping_cart']["order"]);
	}
	
	public function update($key, $qty) {
		if ($qty>0){
			$cart_products=$this->getProducts();
			$short_key = $this->array_find_deep($cart_products,$key);
			if (!empty($short_key))
				$key=$short_key;
			if ($cart_products[$key]['product_stock']<$qty){
				$qty = $cart_products[$key]['product_stock'];
			}
			if ((int)$qty && ((int)$qty > 0) && isset($this->SYSTEM_ARR['cart'][$key])) {
				$this->SYSTEM_ARR['cart'][$key] = (int)$qty;
			} else {
				$this->remove($key);
			}
			$this->updateUserCart();
		}
	}
	
	public function remove($key) {
		$this->data = array();
		$cart_products=$this->getProducts();
		$short_key = $this->array_find_deep($cart_products,$key);
		$product_id = $cart_products[$short_key]['product_id'];
		$pObj = new Products();
		$pObj->recordProductWeightage($product_id,'products#cart_remove');
		if (!empty($short_key))
			$key=$short_key;
		unset($this->SYSTEM_ARR['cart'][$key]);
		$this->updateUserCart();
		return true;
	}
	
	public function hasProducts() {
		return count($this->SYSTEM_ARR['cart']);
	}
	
	public function hasStock() {
		$stock = true;
		foreach ($this->getProducts() as $product) {
			if (!$product['stock']) {
				$stock = false;
			}
		}
		return $stock;
	}
	
	
	public function hasOwnItems() {
		$self_product = false;
		foreach ($this->getProducts() as $product) {
			if ($product['self_product']) {
				$self_product = true;
			}
		}
		return $self_product;
	}
	
	public function hasShipping() {
		foreach ($this->getProducts() as $product) {
			if ($product['shipping']) {
				return true;
			}
		}
		return false;
	}
	
	public function hasCODEligible() {
		foreach ($this->getProducts() as $product) {
			if (!$product['cod']) {
				return false;
			}
		}
		return true;
	}
	
	public function hasShippingOptionSet() {
		$shipping_selected = true;
		foreach ($this->getProducts() as $product) {
			if ($product['shipping']){
				$pObj = new Products();
				$shipping_address = $this->User->getAddress($this->SYSTEM_ARR['shopping_cart']['Shipping_Address']);
				$shipping_options = $pObj->getProductShippingRates($product['product_id'],array("country"=>$shipping_address["ua_country"]));
				if ((!$product['is_shipping_selected'])) {
				    $shipping_selected = false;
        	    }
			}
		}
		return $shipping_selected;
	}
	
	public function add($product_id, $qty = 1, $option = array(), $user_id = 0) {
		
		$this->data = array();
		$product['product_id'] = (int)$product_id;
		if ($option) {
			$product['option'] = $option;
		}
		foreach ($this->SYSTEM_ARR['cart'] as $key => $quantity) {
			$cart_product = unserialize(base64_decode($key));
			if (($cart_product["product_id"]==$product_id) && $cart_product["shipping_id"]>0)
			$product["shipping_id"]=$cart_product["shipping_id"];
		}
		
		$key = base64_encode(serialize($product));
		if ((int)$qty && ((int)$qty > 0)) {
			if (!isset($this->SYSTEM_ARR['cart'][$key])) {
				$this->SYSTEM_ARR['cart'][$key] = (int)$qty;
			} else {
				$this->SYSTEM_ARR['cart'][$key] += (int)$qty;
			}
		}
		$this->updateUserCart($user_id);
		return true;
	}
	
	
	
	public function countProducts() {
		return count($this->SYSTEM_ARR['cart']);
	}
	
	public function setCartBillingAddress($val) {
		$this->SYSTEM_ARR['shopping_cart']['Billing_Address'] = $val;
		$this->updateUserCart();
		return true;
	}
	
	public function getCartBillingAddress() {
		return $this->SYSTEM_ARR['shopping_cart']['Billing_Address'];
	}
	
	public function isBillingAddressSet() {
		return (isset($this->SYSTEM_ARR['shopping_cart']['Billing_Address']) && ($this->User->getAddress($this->SYSTEM_ARR['shopping_cart']['Billing_Address'])) && intval($this->SYSTEM_ARR['shopping_cart']['Billing_Address'])>0);
	}
	
	public function setCartShippingAddress($val) {
		$this->SYSTEM_ARR['shopping_cart']['Shipping_Address'] = $val;
		$this->updateUserCart();
		return true;
	}
	
	public function getCartShippingAddress() {
		return $this->SYSTEM_ARR['shopping_cart']['Shipping_Address'];
	}
	
	public function isShippingAddressSet() {
		return (isset($this->SYSTEM_ARR['shopping_cart']['Shipping_Address']) && ($this->User->getAddress($this->SYSTEM_ARR['shopping_cart']['Shipping_Address'])) && intval($this->SYSTEM_ARR['shopping_cart']['Shipping_Address'])>0);
	}
	
	public function updateCartDiscountCoupon($val) {
		$this->SYSTEM_ARR['shopping_cart']['discount_coupon'] = $val;
		$this->updateUserCart();
		return true;
	}
	
	public function RemoveCartDiscountCoupon() {
		unset($this->SYSTEM_ARR['shopping_cart']['discount_coupon']);
		$this->updateUserCart();
		return true;
	}
	
	public function getCartDiscountCoupon() {
		return $this->SYSTEM_ARR['shopping_cart']['discount_coupon'];
	}
	
	public function isDiscountCouponSet() {
		return !empty($this->SYSTEM_ARR['shopping_cart']['discount_coupon']);
	}
	
	public function updateCartWalletOption($val) {
		$this->SYSTEM_ARR['shopping_cart']['Pay_from_wallet'] = $val;
		$this->updateUserCart();
		return true;
	}
	
	public function isCartUserWalletSelected() {
		return (isset($this->SYSTEM_ARR['shopping_cart']['Pay_from_wallet']) && intval($this->SYSTEM_ARR['shopping_cart']['Pay_from_wallet'])==1)?1:0;
	}
	
	public function isRewardPointsSet() {
		return !empty($this->SYSTEM_ARR['shopping_cart']['reward_points']);
	}
	
	public function clearCODBenefits() {
		if ($this->isCartUserWalletSelected() || $this->isDiscountCouponSet() || $this->isRewardPointsSet()){
			$this->RemoveCartDiscountCoupon();
			$this->updateCartRewardPoints(0);
			$this->updateCartWalletOption(0);
			return true;
		}
		return false;
	}
	
	public function setProductsShipping($arr) {
			foreach ($this->SYSTEM_ARR['cart'] as $key => $quantity) {
				$product = unserialize(base64_decode($key));
				if ($arr["shipping_locations"][md5($key)] >= 0) {
					$product["shipping_id"] = $arr["shipping_locations"][md5($key)];
					if (isset($arr["shipping_locations"][md5($key)]) &&
							$arr["shipping_locations"][md5($key)] == 0 &&
							!empty($arr["shipping_services"][md5($key)])
					) {
						$product['selected_shipping_option']["shipping_carrier"] = $arr["shipping_carrier"][md5($key)];
						list($carrier_name, $carrier_price) = explode("-", $arr["shipping_services"][md5($key)]);
						$product['selected_shipping_option']["shipping_services"] = ($carrier_name);
						$product['selected_shipping_option']["shipping_cost"] = $carrier_price;
					} else {
						unset($product['selected_shipping_option']["shipping_carrier"]);
						unset($product['selected_shipping_option']["shipping_services"]);
						unset($product['selected_shipping_option']["shipping_cost"]);
					}
				}
				unset($this->SYSTEM_ARR['cart'][$key]);
				$updated_key = base64_encode(serialize($product));
				if (!isset($this->SYSTEM_ARR['cart'][$updated_key])) {
					$this->SYSTEM_ARR['cart'][$updated_key] = (int) $quantity;
				} else {
					$this->SYSTEM_ARR['cart'][$updated_key] += (int) $quantity;
				}
			}
			$this->updateUserCart();
			return true;
		}
	/*public function setProductsShipping($arr) {
		foreach ($this->SYSTEM_ARR['cart'] as $key => $quantity) {
			$product = unserialize(base64_decode($key));
			if ($arr["shipping_locations"][md5($key)]>0){
				$product["shipping_id"]=$arr["shipping_locations"][md5($key)];
			}
			unset($this->SYSTEM_ARR['cart'][$key]);
			$updated_key = base64_encode(serialize($product));
			if (!isset($this->SYSTEM_ARR['cart'][$updated_key])) {
				$this->SYSTEM_ARR['cart'][$updated_key] = (int)$quantity;
			} else {
				$this->SYSTEM_ARR['cart'][$updated_key] += (int)$quantity;
			}
		}
		$this->updateUserCart();
		return true;
	}
*/
	
	function order_invoice_format(){
		$value=Settings::getSetting("CONF_INVOICE_FORMAT");
		$new_value=(int)$value+1;
		$new_value=str_pad($new_value,7,'0',STR_PAD_LEFT);
		if (!$this->db->update_from_array('tbl_configurations', array('conf_val' => $new_value),
		array('smt' => 'conf_var = ? ', 'vals' => array('CONF_INVOICE_FORMAT')))){
			$this->error = $this->db->getError();
		}
		$new_value=date("ymd")."-".$new_value;
		return $new_value;
	}
	
	public function getTaxes() {
		foreach ($this->getProducts() as $product) {
			$tax_data+=Settings::getSetting("CONF_SITE_TAX");
		}
		return $tax_data;
	}
	
	function array_find_deep($array, $search){
	    foreach($array as $key => $value) {
			if (strcasecmp(md5($key),$search)==0){
				return $key;
			}
    	}
	    return array();
	}
	
	public function updateCartRewardPoints($val) {
		$this->SYSTEM_ARR['shopping_cart']['reward_points'] = $val;
		$this->updateUserCart();
		return true;
	}
	
	public function getCartRewardPoints() {
		return $this->SYSTEM_ARR['shopping_cart']['reward_points'];
	}
	
	public function getAlsoBoughtProductsForCart() {
		$product_data = array();
		$limit = Settings::getSetting("CONF_CUSTOMER_BOUGHT_ITEMS_CART_PAGE");
		if(isset($limit) && $limit>0){
			$productIds = array();		
			foreach ($this->getProducts() as $product) {
				$productIds[] = $product['product_id'];
				$prodIdsS = implode(",", $productIds);
				if (isset($productIds) && !empty($productIds) ) {
					sort($productIds);		
					$rs = $this->db->query("SELECT op.opr_product_id FROM tbl_order_products op INNER JOIN `tbl_orders` o ON (o.order_id = op.opr_order_id) INNER JOIN `tbl_products` p ON (op.opr_product_id = p.prod_id) INNER JOIN `tbl_prod_details` pd ON (p.prod_id = pd.prod_id) WHERE EXISTS (SELECT 1 FROM tbl_order_products op1  WHERE op1.opr_order_id = op.opr_order_id AND op1.opr_product_id IN (" . $prodIdsS . ") ) AND op.opr_product_id NOT IN (" . $prodIdsS . ") AND o.order_payment_status IN (1,2) AND  p.prod_status = '1' AND p.prod_is_expired = '0' and p.prod_is_deleted=0 AND pd.prod_available_date <= NOW() GROUP BY op.opr_product_id LIMIT " . (int)$limit);
					
					$arr_products=$this->db->fetch_all($rs);
					foreach($arr_products as $sn=>$val){
						$pObj= new Products();
						$product_data[$val['opr_product_id']] = $pObj->getData($val['opr_product_id'],array("favorite"=>User::getLoggedUserId()));
					}
					
				}
			}
		}
		return $product_data;
	}
	private function getCache($key) {
			require_once (CONF_INSTALLATION_PATH . 'public/includes/phpfastcache.php');
			phpFastCache::setup("storage", "files");
			phpFastCache::setup("path", CONF_USER_UPLOADS_PATH . "caching");
			$cache = phpFastCache();
			return $cache->get($key);
	}
	private function setCache($key, $value) {
			require_once (CONF_INSTALLATION_PATH . 'public/includes/phpfastcache.php');
			phpFastCache::setup("storage", "files");
			phpFastCache::setup("path", CONF_USER_UPLOADS_PATH . "caching");
			$cache = phpFastCache();
			return $cache->set($key, $value, 60 * 60);
	}
		
	public function shipStationCarrierList() {
			$carrierList = array();
			$carrierList[0] = Utilities::getLabel('M_Select_Provider');
			try {
				$carriers = null;
				if (!$carriers = $this->getCache("shipstationcarriers")) {
					$ship = new Ship();
					$carriers = $ship->getCarriers();
					$this->setCache("shipstationcarriers", $carriers);
				}
				foreach ($carriers as $carrier) {
					$code = $carrier->code;
					$name = $carrier->name;
					$carrierList[$code] = $name;
				}
			} catch (Exception $ex) {
					// $carriers = new stdClass();
		}
		return $carrierList;
		}
		public function getProductByKey($find_key) {
				if (!$this->hasProducts()) {
					return false;
				}
				foreach ($this->SYSTEM_ARR['cart'] as $key => $cart) {
					if ($find_key == md5($key)) {
						return $key;
					}
				}
				return false;
			}
			
		public function getCarrierShipmentServicesList($product_key, $carrier_id = 0) {
				if (!$key = $this->getProductByKey($product_key)) {
					return array();
				}
				$products = $this->getProducts();
				$product = $products[$key];
				$product_ship_free = $product['shipping_free'];
				$services = $this->getCarrierShipmentServices($product_key, $carrier_id);
				$servicesList = array();
				$servicesList[0] = Utilities::getLabel('M_Select_Service');
				if (!empty($carrier_id)) {
					foreach ($services as $key => $value) {
						$code = $value->serviceCode;
						$price = $product_ship_free?0:$value->shipmentCost + $value->otherCost;
						$name = $value->serviceName;
						$label = $name . " (" . Utilities::displayMoneyFormat($price) . " )";
						$servicesList[$code . "-" . $price] = $label;
					}
				}
				return $servicesList;
			}
			
			
			
			public function getCarrierShipmentServices($product_key, $carrier_id) {
				if (!$key = $this->getProductByKey($product_key)) {
					return array();
				}
				$products = $this->getProducts();
				$product = $products[$key];
				$productShippingAddress = $product['shipping_address'];
				$productShopAddress = $product['seller_address'];
				$sellerPinCode = $productShopAddress['shop_postcode'];
				$productWeight = $product['weight'];
				$productWeightClass = $product['weight_class_id'];
				$productLength = $product['length'];
				$productWidth = $product['width'];
				$productHeight = $product['height'];
				$productLengthUnit = $product['length_class_id'];
				$productWeightInOunce = $this->ConvertWeightInOunce($productWeight, $productWeightClass);
				$productLengthInCenti = $this->ConvertLengthInCenti($productLength, $productLengthUnit);
				$productWidthInCenti = $this->ConvertLengthInCenti($productWidth, $productLengthUnit);
				$productHeightInCenti = $this->ConvertLengthInCenti($productHeight, $productLengthUnit);
				$product_rates = array();
				try {
					$Ship = new Ship();
					$Ship->setProductDeliveryAddress($productShippingAddress['state_code'], $productShippingAddress['country_code'], $productShippingAddress['ua_city'], $productShippingAddress['ua_zip']);
					$Ship->setProductWeight($productWeightInOunce);
					if ($productLengthInCenti > 0 && $productWidthInCenti > 0 && $productHeightInCenti > 0) {
						$Ship->setProductDim($productLengthInCenti, $productWidthInCenti, $productHeightInCenti);
					}
					$product_rates = (array) $Ship->getProductShippingRates($carrier_id, $sellerPinCode, $Ship->getProductWeight(), $Ship->getProductDeliveryAddress(), $Ship->getProductDim());
					
				} catch (Exception $ex) {
					$ex->getMessage();
					return array();
				}
				return $product_rates;
			}
			function ConvertWeightInOunce($productWeight, $productWeightClass) {
				$coversionRate = 1;
				switch ($productWeightClass) {
					case "KG":
						$coversionRate = "35.274";
						break;
					case "GM":
						$coversionRate = "0.035274";
						break;
					case "PN":
						$coversionRate = "16";
						break;
					case "OU":
						$coversionRate = "1";
						break;
					case "Ltr":
						$coversionRate = "33.814";
						break;
					case "Ml":
						$coversionRate = "0.033814";
						break;
				}
				return $productWeight * $coversionRate;
			}
			function ConvertLengthInCenti($productWeight, $productWeightClass) {
				$coversionRate = 1;
				switch ($productWeightClass) {
					case "IN":
						$coversionRate = "2.54";
						break;
					case "MM":
						$coversionRate = "0.1";
						break;
					case "CM":
						$coversionRate = "1";
						break;
				}
				return $productWeight * $coversionRate;
			}
	
}