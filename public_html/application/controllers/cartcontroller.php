<?php
class CartController extends CommonController{
	function default_action(){
		Utilities::redirectUser(Utilities::generateUrl('cart','display'));	
	}
	function cart_summary(){
		$this->set('products',$this->Cart->getProducts());
		$this->set('cart_summary',$this->Cart->getCartFinancialSummary());
		$this->_template->render(false,false);	
	}
	function display(){
		
		Syspage::addJs(array('js/owl.carousel.js'), false);
		if ((!$this->Cart->hasStock()) && Settings::getSetting("CONF_CHECK_STOCK")) {
			Message::addErrorMessage(Utilities::getLabel('M_Error_Stock'));
		}
		if (($this->Cart->hasOwnItems()) && (!Settings::getSetting("CONF_ENABLE_BUYING_OWN_PRODUCTS"))) {
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_OWN_STORE'));
		}
		$products = $this->Cart->getProducts();
		
		foreach($products as $product) {
			if ($product['minimum'] > $product["quantity"]) {
				Message::addErrorMessage(sprintf(Utilities::getLabel('L_Please_add_mimimum_cart_qty'), $product['minimum'],($product['name'])));
			}
		}
		/*$r = $this->Cart->getProducts();
		Utilities::printarray($r);
		die();*/
		$emptyCartObj=new Emptycartitems();
		$empty_cart_items=$emptyCartObj->getEmptyCartItems();
		$this->set('empty_cart_items',$empty_cart_items);	
		$this->set('error_warning',$error_warning);
		$this->set('products',$this->Cart->getProducts());
		$this->set('cart_summary',$this->Cart->getCartFinancialSummary());
		$this->set('cart_also_bought_products',$this->Cart->getAlsoBoughtProductsForCart());
		$this->_template->render();	
	}
	function checkout(){
		
		Utilities::checkLogin();
		if (!Utilities::checkBuyerLogin(false)){
			Message::addErrorMessage(Utilities::getLabel('M_Please_login_with_buyer_account'));
			Utilities::redirectUser(Utilities::generateUrl('cart')); 
		}
		Syspage::addCss(array('css/slick.css','css/checkout-short.css'), false);
		Syspage::addJs(array('js/slick.min.js'), false);
		$this->is_eligible_for_next_step(array("products"=>true,"stock"=>true,"self_product"=>true,"minimum"=>true));
		$user = new User();
		$address = $user->getUserAddresses($this->getLoggedUserId(),1);
		if ($this->Cart->isShippingAddressSet()) {
			$selected_shipping_address= $this->Cart->getCartShippingAddress();
		}else{
			$selected_shipping_address = $address['ua_id'];
			$this->Cart->setCartShippingAddress($selected_shipping_address);
		}
		$this->set('shipping_address',$selected_shipping_address);
		$shipping_address = $user->getUserAddress($selected_shipping_address, $this->getLoggedUserId());
		if ($shipping_address){
			$shipping_address_formatted = $shipping_address['ua_name'].', '.((strlen($shipping_address['ua_address1']) > 0)?$shipping_address['ua_address1'].', ':'') .((strlen($shipping_address['ua_address2']) > 0)?$shipping_address['ua_address2'].', ':'') . ((strlen($shipping_address['ua_city']) > 0)?''.$shipping_address['ua_city'] . ', ':'') . $shipping_address['ua_zip'] .', '.$shipping_address['state_name'] .', '. $shipping_address['country_name'] .' T:'.$shipping_address['ua_phone'];
			$this->set('shipping_address_formatted',$shipping_address_formatted);	
		}
		if ($this->Cart->isBillingAddressSet()) {
			$selected_billing_address= $this->Cart->getCartBillingAddress();
		}else{
			$selected_billing_address = $address['ua_id'];
			$this->Cart->setCartBillingAddress($selected_billing_address);
		}
		$this->set('billing_address',$selected_billing_address);
		$billing_address = $user->getUserAddress($selected_billing_address, $this->getLoggedUserId());
		if ($billing_address){
			$billing_address_formatted = $billing_address['ua_name'].', '.((strlen($billing_address['ua_address1']) > 0)?$billing_address['ua_address1'].', ':'') .((strlen($billing_address['ua_address2']) > 0)?$billing_address['ua_address2'].', ':'') . ((strlen($billing_address['ua_city']) > 0)?''.$billing_address['ua_city'] . ', ':'') . $billing_address['ua_zip'] .', '.$billing_address['state_name'] .', '. $billing_address['country_name'] .' T:'.$billing_address['ua_phone'];
			$this->set('billing_address_formatted',$billing_address_formatted);	
		}
		if ($this->Cart->hasShippingOptionSet()) {
			$this->set('shipping_options_set',1);
		}
		$this->set('addresses_set',($selected_shipping_address && $selected_billing_address));
		$this->set('products',$this->Cart->getProducts());
		$this->set('shipping_required',$this->Cart->hasShipping());
		$this->_template->render(false,false);	
	}
	function checkout_sidebar(){
		$this->set('products',$this->Cart->getProducts());
		$rewardObj=new Rewards();
		$user_total_reward_points = $rewardObj->getUserRewardsPointsBalance($this->getLoggedUserId());
		$this->set('user_total_reward_points',$user_total_reward_points);
		$this->set('cart_summary',$this->Cart->getCartFinancialSummary());	
		die(convertToJson(array('cart_count'=>$this->Cart->countProducts(), 'html'=>$this->_template->render(false,false,NULL,true))));
	}
	function checkout_billing_addresses(){
		$user=new User();
		$this->set('selected_address', $this->Cart->getCartBillingAddress());
		$this->set('addresses', $user->getUserAddresses($this->getLoggedUserId()));
		die(convertToJson(array('cart_count'=>$this->Cart->countProducts(),'shipping_required',$this->Cart->hasShipping(),'html'=>$this->_template->render(false,false,NULL,true))));
	}
	function checkout_shipping_addresses(){
		$user=new User();
		$this->set('selected_address', $this->Cart->getCartShippingAddress());
		$this->set('addresses', $user->getUserAddresses($this->getLoggedUserId()));
		die(convertToJson(array('cart_count'=>$this->Cart->countProducts(),'shipping_required',$this->Cart->hasShipping(),'html'=>$this->_template->render(false,false,NULL,true))));
	}
	function checkout_shipping_summary(){
		$user=new User();
		$this->set('products',$this->Cart->getProducts());
		$this->set('billing_address', $billing_address = $user->getUserAddress($this->Cart->getCartBillingAddress(), $this->getLoggedUserId()));
		$this->set('delivery_address', $delivery_address = $user->getUserAddress($this->Cart->getCartShippingAddress(), $this->getLoggedUserId()));
		$this->set('shipStationCarrierList', $this->Cart->shipStationCarrierList());
		$this->set('cartObj',$this->Cart);
		//die($this->_template->render(false,false,NULL,true));
		die(convertToJson(array('cart_count'=>$this->Cart->countProducts(),'shipping_required',$this->Cart->hasShipping(), 'html'=>$this->_template->render(false,false,NULL,true))));
	}
	function checkout_cart_summary(){
		$user=new User();
		$this->set('products',$cart_products=$this->Cart->getProducts());
		$this->set('billing_address', $billing_address = $user->getUserAddress($this->Cart->getCartBillingAddress(), $this->getLoggedUserId()));
		$this->set('delivery_address', $delivery_address = $user->getUserAddress($this->Cart->getCartShippingAddress(), $this->getLoggedUserId()));
		$this->set('cart_summary',$this->Cart->getCartFinancialSummary());
		die(convertToJson(array('cart_count'=>$this->Cart->countProducts(), 'html'=>$this->_template->render(false,false,NULL,true))));
	}
	function checkout_sidebar_payment_summary(){
		$payment_ready = $this->is_eligible_for_next_step(array("products"=>true,"stock"=>true,"minimum"=>true,"billing_address"=>true,"shipping_address"=>true,"shipping_options"=>true),false);
		$user=new User();
		$this->set('products',$cart_products=$this->Cart->getProducts());
		$this->set('cart_summary',$this->Cart->getCartFinancialSummary());
		$this->set('user_balance',$user->getUserBalance($this->getLoggedUserId()));
		$this->set('payment_ready',$payment_ready);
		die(convertToJson(array('cart_count'=>$this->Cart->countProducts(), 'html'=>$this->_template->render(false,false,NULL,true))));
	}
	function checkout_payment_summary(){
		$payment_ready = $this->is_eligible_for_next_step(array("products"=>true,"stock"=>true,"minimum"=>true,"billing_address"=>true,"shipping_address"=>true,"shipping_options"=>true),false);
		$paymentMethodObj=new PaymentMethods();
		$this->set('payment_methods',$paymentMethodObj->getPaymentMethods(array("status"=>1)));
		if ($payment_ready){
			$user=new User();
			$cart_products=$this->Cart->getProducts();
			$billing_address = $user->getUserAddress($this->Cart->getCartBillingAddress(), $this->getLoggedUserId());
			$delivery_address = $user->getUserAddress($this->Cart->getCartShippingAddress(), $this->getLoggedUserId());
			$cart_summary = $this->Cart->getCartFinancialSummary();
			/*******************        Record Order Parameters         *************/
			$random = strtoupper(uniqid());
			$reference_number = substr($random, 0, 5) . '-' . substr($random, 5, 5) . '-' . substr($random . rand(10, 99), 10, 5);
			$order_data['order_reference'] = $reference_number;
			$order_data['order_invoice_number'] = $this->Cart->order_invoice_format();
			if ($this->isUserLogged()) {
				$user=new User();
				$user_details=$user->getUser(array('user_id'=>$this->getLoggedUserId(), 'get_flds'=>array('user_id', 'user_customer_group', 'user_name','user_email','user_phone','user_referrer_id')));
				$order_data['order_user_id'] = $user_details["user_id"];
				$order_data['order_customer_group'] = $user_details['user_customer_group'];
				$order_data['order_user_name'] = $user_details['user_name'];
				$order_data['order_user_email'] = $user_details['user_email'];
				$order_data['order_user_phone'] = $user_details['user_phone'];
				$order_data['order_user_fax'] = $user_details['user_fax'];
			} elseif ($this->isGuestUserLogged()) {
				$order_data['order_user_id'] = 0;
				$order_data['order_customer_group'] = $_SESSION['guest']['user_customer_group'];
				$order_data['order_user_name'] = $_SESSION['guest']['name'];
				$order_data['order_user_email'] = $_SESSION['guest']['email'];
				$order_data['order_user_phone'] = $_SESSION['guest']['phone'];
				$order_data['order_user_fax'] = $_SESSION['guest']['fax'];
			}
			if ($this->Cart->hasShipping()){
				$order_data['order_shipping_name'] = $delivery_address["ua_name"];
				$order_data['order_shipping_address1'] = $delivery_address["ua_address1"];
				$order_data['order_shipping_address2'] = $delivery_address["ua_address2"];
				$order_data['order_shipping_city'] = $delivery_address["ua_city"];
				$order_data['order_shipping_postcode'] = $delivery_address["ua_zip"];
				$order_data['order_shipping_state'] = $delivery_address["state_name"];
				$order_data['order_shipping_country'] = $delivery_address["country_name"];
				$order_data['order_shipping_country_id'] = $delivery_address["ua_country"];
				$order_data['order_shipping_phone'] = $delivery_address["ua_phone"];
				$order_data['order_shipping_method'] = "-";
				$order_data['order_shipping_required'] = "1";
			}
			$order_data['order_billing_name'] = $billing_address["ua_name"];
			$order_data['order_billing_address1'] = $billing_address["ua_address1"];
			$order_data['order_billing_address2'] = $billing_address["ua_address2"];
			$order_data['order_billing_city'] = $billing_address["ua_city"];
			$order_data['order_billing_postcode'] = $billing_address["ua_zip"];
			$order_data['order_billing_state'] = $billing_address["state_name"];
			$order_data['order_billing_country'] = $billing_address["country_name"];
			$order_data['order_billing_country_id'] = $billing_address["ua_country"];
			$order_data['order_billing_phone'] = $billing_address["ua_phone"];
			
			$order_data['products'] = array();
			foreach ($this->Cart->getProducts() as $cart_product) {
				$prodObj=new Products();
				$prodObj->joinWithBrandsTable();
				$customization='';
				$product_info = $prodObj->getData($cart_product['product_id'],array("status"=>1));
				$option_data = array();
				foreach ($cart_product['option'] as $option) {
					$option_data[] = array(
						'product_option_id'       => $option['product_option_id'],
						'product_option_value_id' => $option['product_option_value_id'],
						'option_id'               => $option['option_id'],
						'option_value_id'         => $option['option_value_id'],
						'name'                    => $option['name'],
						'value'                   => $option['value'],
						'type'                    => $option['type']
						);
					$customization.='<br />- <small>'.$option['name'].': '.$option['value'].'</small>';
				}
				$order_data['products'][] = array(
					'product_id' 		=> $cart_product['product_id'],
					'product_type' 		=> $product_info['prod_type'],
					'quantity'  		  => $cart_product['quantity'],
					'name'       		  => $product_info['prod_name'],
					'sku'        	   => $product_info['prod_sku'],
					'brand'      		 => $product_info['brand_name'],
					'model'      		 => $product_info['prod_model'],
					'vendor'    		=> $product_info['prod_added_by'],
					'vendor_name'	   => $product_info['user_name'],
					'vendor_username'   => $product_info['user_username'],
					'vendor_email' 	  => $product_info['user_email'],
					'vendor_phone' 	  => $product_info['user_phone'],
					'shop'      		  => $product_info['prod_shop'],
					'shop_name'  		 => $product_info['shop_name'],
					'retail_price'	  => $product_info['prod_retail_price'],
					'sale_price'  		=> $product_info['prod_sale_price'],
					'product_price'     => $cart_product['product_price'],
					'option_price'	  => $cart_product['option_price'],
					'price'	 		 => $cart_product['price'],
					'commission'	    => $cart_product['commission'],
					'commission_percentage'	    => $cart_product['commission_percentage'],
					'affiliate_commission'	    => $cart_product['affiliate_commission'],
					'affiliate_commission_percentage'	    => $cart_product['affiliate_commission_percentage'],
					'ship_free'     	 => $product_info['prod_ship_free'],
					'tax_free'          => $product_info['prod_tax_free'],
					'customization'     => $customization,
					'shipping_required' => $product_info['prod_requires_shipping'],
					'shipping_mode'     => $cart_product['selected_shipping_option']['pship_id'],
					'shipping_days'     => $cart_product['selected_shipping_option']['sduration_days_or_weeks']=="D"?$cart_product['selected_shipping_option']['sduration_to']:$cart_product['selected_shipping_option']['sduration_to']*7,
					'shipping_company'  => $cart_product['selected_shipping_option']['scompany_id'],
					'shipping_charges'  => $cart_product['shipping_price'],
					'shipping_label'    => $cart_product['selected_shipping_option']['sduration_label'],
					'tax_charged'       => $cart_product['tax'],
					'qty_tax_charged'   => $cart_product['quantity_tax'],
					'net_charged'       => $cart_product['net_total'],
					'options'     => $option_data,
					);
				$affiliate_id = $cart_product['affiliate_id'];
				$total_affiliate_commission += $cart_product['affiliate_commission'];
			}
			$order_data['order_status'] = 0;
			$order_data['order_date_added'] = date('Y-m-d H:i:s');
			$order_data['order_ip_address'] = $_SERVER['REMOTE_ADDR'];
			if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$order_data['order_forwarded_ip'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
				$order_data['order_forwarded_ip'] = $_SERVER['HTTP_CLIENT_IP'];
			} else {
				$order_data['order_forwarded_ip'] = '';
			}
			if (isset($_SERVER['HTTP_USER_AGENT'])) {
				$order_data['order_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
			} else {
				$order_data['order_user_agent'] = '';
			}
			if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
				$order_data['order_accept_language'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
			} else {
				$order_data['order_accept_language'] = '';
			}
			$order_data['order_discount_coupon'] = $cart_summary["cart_discounts"]["code"];
			$order_data['order_discount_total'] = $cart_summary["cart_discounts"]["value"];
			$order_data['order_cart_total'] = $cart_summary["cart_total"];
			$order_data['order_cart_total_without_tax'] = $order_cart_total_without_tax;
			$order_data['order_shipping_charged'] = $cart_summary["cart_shipping_total"];
			$order_data['order_tax_charged'] = $cart_summary["cart_tax_total"];
			$order_data['order_value_discount'] = $order_value_discount;
			$order_data['order_value_discount_label'] = $order_value_discount_label;
			$order_data['order_sub_total'] = $cart_summary["net_total_without_discount"];
			$order_data['order_net_charged'] = $cart_summary["net_total_after_discount"];
			$order_data['order_actual_paid'] = $cart_summary["cart_actual_paid"];
			$order_data['order_site_commission'] = $cart_summary["site_commission"];
			$order_data['order_language'] = Settings::getSetting('CONF_LANGUAGE');
			$crObj=new Currency();
			$currency = $crObj->getCurrencyByCode(Settings::getSetting("CONF_CURRENCY"));
			$order_data['order_currency'] = $currency['currency_id'];
			$order_data['order_currency_code'] = $currency['currency_code'];
			$order_data['order_currency_value'] = $currency['currency_value'];
			$order_data["order_id"]=$_SESSION['shopping_cart']["order"];
			$order_data['order_wallet_selected'] = $cart_summary["cart_wallet_enabled"];
			$order_data['order_affiliate_id'] = $affiliate_id;
			$order_data['order_affiliate_commission'] = $total_affiliate_commission;
			$rewardObj=new Rewards();
			if ($user_details["user_referrer_id"] && Settings::getSetting("CONF_ENABLE_REFERRER_MODULE") && (!$rewardObj->getTotalUserRewardsByReferrerId($user_details["user_referrer_id"],$user_details["user_id"]))){
				$order_data['order_referrer_id'] = $user_details["user_referrer_id"];
				$order_data['order_referrer_reward_points'] = Settings::getSetting("CONF_SALE_REFERRER_REWARD_POINTS");
			}
			if ($user_details["user_referrer_id"] && Settings::getSetting("CONF_ENABLE_REFERRER_MODULE") && (!$rewardObj->getTotalUserRewardsByReferrerId($user_details["user_id"],$user_details["user_referrer_id"]))){
				$order_data['order_referee_reward_points'] = Settings::getSetting("CONF_SALE_REFEREE_REWARD_POINTS");
			}
			$user_total_reward_points = $rewardObj->getUserRewardsPointsBalance($this->getLoggedUserId());
			$use_cart_reward_points = $cart_summary["reward_points"]>$user_total_reward_points?$user_total_reward_points:$cart_summary["reward_points"];
			$order_data['order_reward_points'] = $use_cart_reward_points;
			/*Utilities::printArray($order_data);
			die();*/
			$orderObj=new Orders();
			if($orderObj->addUpdateCustomerOrder($order_data)){
				$order_id = $orderObj->getOrderId();
			}else{
				Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_ACCESS'));
				Utilities::redirectUserReferer();
			}
			/************* End Recording Order Parameters ****************/
			$order_info = $orderObj->getOrderById($order_id,array("payment_status"=>0));
			if (!$order_info){
				$this->Cart->clear();
				if(!$hide_header_footer){				
					Utilities::redirectUser(Utilities::generateUrl('account','view_order',array($order_id)));
				}
			}
			$this->set('billing_address', $billing_address);
			$this->set('delivery_address', $delivery_address );
			$this->set('order_info',$order_info);
			$this->set('order_payment_financials',$orderObj->getOrderPaymentFinancials($order_id));
			$this->set('payment_ready',$payment_ready);
		}
		die(convertToJson(array('cart_count'=>$this->Cart->countProducts(), 'html'=>$this->_template->render(false,false,NULL,true))));
	}
	
	function payment_tab($order_id,$pmethod_id){
		unset($_SESSION['shopping_cart']["payment_method"]);
		$payment_ready = $this->is_eligible_for_next_step(array("products"=>true,"stock"=>true,"minimum"=>true,"billing_address"=>true,"shipping_address"=>true,"shipping_options"=>true),false);
		$orderObj=new Orders();
		$order_info = $orderObj->getOrderById($order_id,array("payment_status"=>0));
		if ($order_info==false){
			$this->set('error', Utilities::getLabel('M_INVALID_ORDER_PAID_CANCELLED'));
		}
		
		$cart_summary = $this->Cart->getCartFinancialSummary();	
		$paymentMethodObj=new PaymentMethods();
		$payment_method=$paymentMethodObj->getData($pmethod_id);
		if ($payment_method["pmethod_id"]==Settings::getSetting("CONF_COD_PAYMENT_METHOD") && !$cart_summary['order_cod_enabled']){
			$this->set('error', sprintf(Utilities::getLabel('M_Sorry_Cash_On_Delivery_not_available'),Utilities::displayMoneyFormat(Settings::getSetting("CONF_MIN_COD_ORDER_LIMIT")),Utilities::displayMoneyFormat(Settings::getSetting("CONF_MAX_COD_ORDER_LIMIT"))));
		}
		
		if ($payment_method["pmethod_id"]==Settings::getSetting("CONF_COD_PAYMENT_METHOD") && $cart_summary['order_cod_enabled']){
			if ($this->Cart->clearCODBenefits()){
				Message::addMessage(Utilities::getLabel('M_Discount_reward_wallet_benefits_not_applicable_cod'));
				$_SESSION['shopping_cart']["payment_method"] = $pmethod_id;
			}
			
		}

		$this->set('payment_method',$payment_method);
		$frm=new Form('frmPaymentTabForm','frmPaymentTabForm');
		$frm->setExtra('class="siteForm"');
		$frm->setAction(Utilities::generateUrl(strtolower(str_replace("_","",$payment_method["pmethod_code"]))."_pay",'charge',array($order_id)));
		$frm->setFieldsPerRow(1);
		$frm->addHiddenField('', 'order',$order_id);
		if (isset($payment_method["transaction_mode"]) && $payment_method["transaction_mode"]==0){
			$fld=$frm->addHtml('', 'htmlNote','<div class="alert alert-danger">'.Utilities::getLabel('M_Test_Mode_Enabled').'</div>');
			$fld->merge_caption=true;
		}
		if ($payment_ready){
			$fld=$frm->addSubmitButton('','btn_submit',Utilities::getLabel('M_Confirm_Payment'),'button-confirm','class="btn primary-btn"');
		}else{
			$fld=$frm->addHtml('', 'htmlNote','<div class="alert alert-danger">'.Utilities::getLabel('M_Something_wrong_with_order').'</div>');
		}
		$fld->merge_caption=true;
		$this->set('frm',$frm);
		die(convertToJson(array('msg'=>Message::getHtml(),'cart_count'=>$this->Cart->countProducts(), 'html'=>$this->_template->render(false,false,NULL,true))));
		//die(convertToJson(array('cart_count'=>$this->Cart->countProducts(), 'html'=>$this->_template->render(false,false,NULL,true))));
		//$this->_template->render(false,false);	
	}
	public function confirm_order() {
		$post = Syspage::getPostedVar();
		$json = array();
		if (isset($post['order'])) {
			$order_id=$post['order'];
			$orderObj=new Orders();
			$order_info = $orderObj->getOrderById($order_id,array("payment_status"=>0,'user'=>$this->getLoggedUserId()));
			if ($order_info){
				/**************** Start Reduce Reward Points of Buyer *********************/
				if ($order_info['order_reward_points']>0){
					$rewardObj=new Rewards();
					$rewardArray=array(
						"urp_user_id"=>$order_info['order_user_id'],
						"urp_referrer_id"=>0,
						"urp_points"=>(int)-$order_info['order_reward_points'],
						"urp_description"=>sprintf(Utilities::getLabel('L_Used_Reward_Points_Order_Invoice_Number'),'<i>'.$order_info['order_invoice_number'].'</i>'),
						);
					if($reward_point_id = $rewardObj->addRewardPoints($rewardArray)){
						$emailNotificationObj=new Emailnotifications();
						$emailNotificationObj->sendRewardPointsNotification($reward_point_id);
					}else{
						$this->error=$rewardObj->getError();
						return false;
					}
				}
				/**************** End Reduce Reward Points of Buyer *********************/
				/*$this->Cart->clear();
				$this->Cart->updateUserCart();*/
				if ($orderObj->addOrderHistory($order_id,1,"-NA-",true)){
					$sub_orders=$orderObj->getChildOrders(array("order"=>$order_id));
	 				foreach ($sub_orders as $subkey=>$subval){
						$orderObj->addChildOrderHistory($subval["opr_id"],Settings::getSetting("CONF_DEFAULT_ORDER_STATUS"),'',false);
					}

					$json['success'] = 1;
				}else{
					$json['error'] = 'Please try after some time.';
				}
			}else{
				$json['error'] = Utilities::getLabel('M_INVALID_ORDER_PAID_CANCELLED');
			}
		}
		echo json_encode($json);
	}
	
	/****************       Cart Operations           ******************/
	
	function save_shipping_address(){
		$post = Syspage::getPostedVar();
			$json = array();
			if($_SERVER['REQUEST_METHOD'] == 'POST'){
				$user=new User();
				$product=new Products();
				$cart_products=$this->Cart->getProducts();
				foreach($cart_products as $cartkey=>$cartval){
						
						$sn++;
						if ($cartval['shipping']){
							$shipping_address = $user->getUserAddress($this->Cart->getCartShippingAddress(), $this->getLoggedUserId());
							$shipping_options = $product->getProductShippingRates($cartval['product_id'],array("country"=>$shipping_address["ua_country"]));
							$productKey = md5($cartval["key"]);
							if (empty($post["shipping_locations"][md5($cartval["key"])]) || (!in_array($post["shipping_locations"][md5($cartval["key"])],array_column($shipping_options, 'pship_id')))){	
								if(!empty($post["shipping_services"][$productKey])){
										 continue;
								}
							$json['error']['product'][$sn] = sprintf(Utilities::getLabel('M_Shipping_Info_Required'), htmlentities($cartval['name']));	
						}
					}
				}
				if (!$json) {
					$json = array('cart_count'=>$this->Cart->countProducts());
					if ($this->Cart->setProductsShipping($post)){
						$json['success']=1;
					}else{
					}
				}
			}
			echo json_encode($json);	
			
	}
	
	
	function is_eligible_for_next_step($criteria=array(),$redirect=true){
		$error_set = false;
		foreach($criteria as $key=>$val) {
			switch($key) {
				case 'products':
				if (!$this->Cart->hasProducts()) {
					$error_set=true;
				}
				break;	
				case 'stock':
				if ((!$this->Cart->hasStock()) && ((!Settings::getSetting("CONF_ALLOW_CHECKOUT")))) {
					$error_set=true;
				}
				break;
				case 'self_product':
				if (($this->Cart->hasOwnItems()) && (!Settings::getSetting("CONF_ENABLE_BUYING_OWN_PRODUCTS"))) {
					$error_set=true;
				}
				break;
				case 'minimum':
				$products = $this->Cart->getProducts();
				foreach($products as $product) {
					if ($product['minimum'] > $product["quantity"]) {
						$error_set=true;
						break;
					}
				}
				break;
				case 'billing_address':
				if (!$this->Cart->isBillingAddressSet()) {
					$error_set=true;
				}
				break;
				case 'shipping_address':
				if (!$this->Cart->isShippingAddressSet() && ($this->Cart->hasShipping())) {
					$error_set=true;
				}
				break;	
				case 'shipping_options':
				if ((!$this->Cart->hasShippingOptionSet()) && ($this->Cart->hasShipping())) {
					$error_set=true;
				}
				break;
			}
		}
		if ($error_set){
			if ($redirect){
				Utilities::redirectUser(Utilities::generateUrl('cart','display'));
			}else{
				return false;
			}
		}
		return true;
	}
	function add(){
		$pObj=new Products();
		$post = Syspage::getPostedVar();
		$json = array();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$products = $post['addons'];
			$products[$post['product_id']] = $post['quantity'];
			foreach($products as $pkey => $pval){
				if (isset($pkey)) {
					$product_id = (int)$pkey;
				} else {
					$product_id = 0;
				}
				$pObj= new Products();
				$pObj->joinWithDetailTable();
				$product_info = $pObj->getData($product_id);
				if ($product_info) {
					$pObj= new Products();
					$pObj->joinWithDetailTable();
					$product = $pObj->getData($product_id,array("available_date"=>1));
					if ($product) {
						$prodObj=new Products();
						$addons = $prodObj->getProductAddons($product_id);
						if (!empty($addons) && (!isset($post['pdetail']))){
							Message::addErrorMessage(Utilities::getLabel('L_Product_contains_addons'),htmlentities($product['prod_name']),Utilities::generateUrl('products','view',array($product_id)));
							$json['error']['product'] = Message::getHtml();
						}
						$minimum_quantity = $product_info['prod_min_order_qty'] ? $product_info['prod_min_order_qty'] : 1;
						if (isset($pval) && ((int)$pval >= $minimum_quantity)) {
							$quantity = (int)$pval;
						} else {
							Message::addErrorMessage(sprintf(Utilities::getLabel('L_Please_add_mimimum_cart_qty'), "<i>".$minimum_quantity."</i>",htmlentities($product_info['prod_name'])));
							if ($product_id!=$post['product_id']){
								$json['error']['addon'][$product_id]=Message::getHtml();
							}else{
								$json['error']['product']= Message::getHtml();
							}
						}
						if (isset($post['option']) && $product_id==$post['product_id']) {
							$option = array_filter($post['option']);
						} else {
							$option = array();
						}
						$product_options = $pObj->getProductOptions($product_id);
						foreach ($product_options as $product_option) {
							if (count($product_option['product_option_value'])>0){
								if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
									if ($product_id==$post['product_id']){
										$json['error']['option'][$product_option['product_option_id']] = sprintf(Utilities::getLabel('M_Label_Required'), htmlentities($product_option['name']));
									}
									else{
										Message::addErrorMessage(sprintf(Utilities::getLabel('L_Product_contains_required_combinations'),htmlentities($product['prod_name']),Utilities::generateUrl('products','view',array($product_id))));
										$json['error']['addon'][$product_id] = Message::getHtml();
									}
								}
							}
						}
					}else{
						Message::addErrorMessage(sprintf(Utilities::getLabel('L_Warning_Product_Available_Date'),"<i>".$product_info["prod_name"]."</i>", "<i>".Utilities::formatDateOnly($product_info["prod_available_date"])."</i>"));
						if ($product_id!=$post['product_id'])
							$json['error']['addon'][$product_id] = Message::getHtml();
						else
							$json['error']['product'] = Message::getHtml();
					}
				}else{
					$json['error']['product'][$product_id]=Utilities::getLabel('M_ERROR_INVALID_REQUEST');
				}
			}
			if (!$json) {
				foreach($products as $pkey => $pval){
					$option = array();
					if (isset($post['option']) && $pkey==$post['product_id']) {
						$option = array_filter($post['option']);
					}
					if ($this->Cart->add($pkey, $pval, $option)){
						$pObj->recordProductWeightage($pkey,'products#cart');
						$pObj->addUpdateProductBrowsingHistory($pkey,array("cart"=>1));
					}
				}
				Message::addMessage(sprintf(Utilities::getLabel('M_Success_cart_add'), Utilities::generateUrl('products','view',array($product_id)), htmlentities($product_info['prod_name']), Utilities::generateUrl('cart')));
				$json['success'] = Message::getHtml();
				$json['total'] = $this->Cart->countProducts();
			}else{
				$json['redirect']=Utilities::generateUrl('products','view',array($product_id)); 
			}
		}
		echo json_encode($json);
	}
	
	public function remove() {
		$post = Syspage::getPostedVar();
		$json = array();
		if (isset($post['key'])) {
			if($this->Cart->remove($post['key'])){
				Message::addMessage(Utilities::getLabel('M_Your_action_performed_successfully'));
			}
		}
		$arr = array('cart_count'=>$this->Cart->countProducts(), 'msg'=>Message::getHtml(), 'total'=>$this->Cart->hasProducts());
		die(convertToJson($arr));
		//echo json_encode($json);
	}
	function edit(){
		$post = Syspage::getPostedVar();
		$json = array();
		if (!empty($post['quantity'])) {
			if (is_array($post['quantity'])) {
				foreach ($post['quantity'] as $key => $value) {
					$this->Cart->update($key, $value);
				}
			}else{
				$this->Cart->update($post['key'], $post['quantity']);
			}
			$json['success']=1;
		}else{
			$json['error']=Utilities::getLabel('M_INVALID_REQUEST');
		}
		$json['cart_count'] = $this->Cart->countProducts();
		$json['shipping_required'] = !$this->Cart->isShippingAddressSet() && ($this->Cart->hasShipping());
		echo json_encode($json);
	}
	public function billing_address_update() {
		$post = Syspage::getPostedVar();
		$json = array('cart_count'=>$this->Cart->countProducts());
		$address_id=$post['address'];
		if (isset($address_id)) {
			$user_id=$this->getLoggedUserId();
			$user=new User();
			$address_details = $user->getUserAddress($address_id,$user_id);
			if ($address_details){
				if($this->Cart->setCartBillingAddress($post['address'])){
//Message::addMessage(Utilities::getLabel('M_cart_billing_address_modified'));	
					$json['success'] = 1;
					$billing_address = $user->getUserAddress($this->Cart->getCartBillingAddress(), $this->getLoggedUserId());
					$billing_address_formatted = $billing_address['ua_name'].', '.((strlen($billing_address['ua_address1']) > 0)?$billing_address['ua_address1'].', ':'') .((strlen($billing_address['ua_address2']) > 0)?$billing_address['ua_address2'].', ':'') . ((strlen($billing_address['ua_city']) > 0)?''.$billing_address['ua_city'] . ', ':'') . $billing_address['ua_zip'] .', '.$billing_address['state_name'] .', '. $billing_address['country_name'] .' T:'.$billing_address['ua_phone'];
					$json['billing_address'] = htmlentities($billing_address_formatted);
					if (!$this->Cart->isShippingAddressSet()) {
						$this->Cart->setCartShippingAddress($post['address']);
					}
					$shipping_address = $user->getUserAddress($this->Cart->getCartShippingAddress(), $this->getLoggedUserId());
					$shipping_address_formatted = $shipping_address['ua_name'].', '.((strlen($shipping_address['ua_address1']) > 0)?$shipping_address['ua_address1'].', ':'') .((strlen($shipping_address['ua_address2']) > 0)?$shipping_address['ua_address2'].', ':'') . ((strlen($shipping_address['ua_city']) > 0)?''.$shipping_address['ua_city'] . ', ':'') . $shipping_address['ua_zip'] .', '.$shipping_address['state_name'] .', '. $shipping_address['country_name'] .' T:'.$shipping_address['ua_phone'];
					$json['shipping_address'] = htmlentities($shipping_address_formatted);
					$json['shipping_required'] = $this->Cart->hasShipping();
					
				}
			}else{
				Message::addErrorMessage(Utilities::getLabel('L_ACTION_TRYING_PERFORM_NOT_VALID'));	
			}
		}
		echo json_encode($json);
	}
	public function shipping_address_update() {
		$post = Syspage::getPostedVar();
		$json = array('cart_count'=>$this->Cart->countProducts());
		$address_id=$post['address'];
		if (isset($address_id)) {
			$user_id=$this->getLoggedUserId();
			$user=new User();
			$address_details = $user->getUserAddress($address_id,$user_id);
			if ($address_details){
				if($this->Cart->setCartShippingAddress($post['address'])){
					$json['success'] = 1;
					$shipping_address = $user->getUserAddress($this->Cart->getCartShippingAddress(), $this->getLoggedUserId());
					$json['shipping_address'] = htmlentities($shipping_address['ua_name'].', '.((strlen($shipping_address['ua_address1']) > 0)?$shipping_address['ua_address1'].', ':'') .((strlen($shipping_address['ua_address2']) > 0)?$shipping_address['ua_address2'].', ':'') . ((strlen($shipping_address['ua_city']) > 0)?''.$shipping_address['ua_city'] . ', ':'') . $shipping_address['ua_zip'] .', '.$shipping_address['state_name'] .', '. $shipping_address['country_name'] .' T:'.$shipping_address['ua_phone']);
					$json['shipping_required'] = $this->Cart->hasShipping();
				}
			}else{
				Message::addErrorMessage(Utilities::getLabel('L_ACTION_TRYING_PERFORM_NOT_VALID'));	
			}
		}
		echo json_encode($json);
	}
	public function apply_coupon() {
		$json = array();
		$post = Syspage::getPostedVar();
		$coupon = isset($post['coupon'])?$post['coupon']:"";
		$couponObj=new Coupons();
		$coupon_info = $couponObj->getCoupon($coupon);
		if (empty($post['coupon'])) {
			$json['message'] = Utilities::getLabel('L_Warning_Enter_Coupon');
		} elseif ($coupon_info) {
			if($this->Cart->updateCartDiscountCoupon($coupon_info['coupon_code'])){
//Message::addMessage(Utilities::getLabel('M_cart_discount_coupon_applied'));	
				$json['message'] = Utilities::getLabel('M_cart_discount_coupon_applied');
				$json['success'] = 1;
			}else{
				$json['message'] = Utilities::getLabel('L_ACTION_TRYING_PERFORM_NOT_VALID');
			}
		} else {
			$json['message'] = Utilities::getLabel('L_Warning_Coupon_Invalid');
		}
		echo(json_encode($json));
	}
	public function remove_coupon() {
		$json = array();
		$post = Syspage::getPostedVar();
		if($this->Cart->RemoveCartDiscountCoupon()){
			$json['success'] = 1;
		}else{
			$json['message'] = Utilities::getLabel('L_ACTION_TRYING_PERFORM_NOT_VALID');
		}
		echo(json_encode($json));
	}
	public function remove_reward_points() {
		$json = array();
		$post = Syspage::getPostedVar();
		if($this->Cart->updateCartRewardPoints(0)){
			$json['success'] = 1;
		}else{
			$json['message'] = Utilities::getLabel('L_ACTION_TRYING_PERFORM_NOT_VALID');
		}
		echo(json_encode($json));
	}
	public function apply_reward_points() {
		$cartObj=new Cart($this->getLoggedUserId());
		$cart_summary = $this->Cart->getCartFinancialSummary();
		$cart_sub_total=floor($cart_summary['cart_max_rewards_points']);
		$json = array();
		$post = Syspage::getPostedVar();
		$rewardObj=new Rewards();
		$customer_reward_points = $rewardObj->getUserRewardsPointsBalance($this->getLoggedUserId());
		$reward_points = round(abs($post['reward_points']));
		if (empty($post['reward_points'])) {
			$json['message'] = Utilities::getLabel('L_Warning_Enter_Reward_Points_to_use');
		}elseif ($reward_points > $customer_reward_points) {
			$json['message'] = sprintf(Utilities::getLabel('L_Warning_you_dont_have_reward_points'), $reward_points);
		}elseif ($reward_points > $cart_sub_total) {
			$json['message'] = sprintf(Utilities::getLabel('L_Warning_maximum_rewards_can_be_applied'), $cart_sub_total);
		}
		if (!$json) {
			if ($this->Cart->updateCartRewardPoints($reward_points)){
				$json['message'] = Utilities::getLabel('L_Success_Reward_points_applied');
				$json['success'] = 1;
			}else{
				$json['message'] = Utilities::getLabel('M_INVALID_REQUEST');
			}
		}
		echo(json_encode($json));
	}
	public function process_wallet() {
		$post = Syspage::getPostedVar();
		$json = array();
		$pay_from_wallet=$post['pay_from_wallet'];
		if (isset($pay_from_wallet)) {
			$this->Cart->updateCartWalletOption($pay_from_wallet);
			$json["success"]=1;
		}
		echo json_encode($json);
	}
	
	public function getCarrierServicesList($product_key,$carrier_id=0){
			$this->Cart = new Cart($this->getLoggedUserId());
			$carrierList = $this->Cart->getCarrierShipmentServicesList($product_key,$carrier_id);
			
			$this->set('options',$carrierList);
			$this->_template->render(false,false,"common/ajax/options.php");
	}
	/****************   End Cart Operations           ******************/
}
