<?php
class OrderPayment extends Orders {
	function __construct($payment_order_id){
		$this->db = Syspage::getdb();
        if (!is_numeric($payment_order_id))
            $payment_order_id = 0;
        $this->payment_order_id = intval($payment_order_id);
        if (!($this->payment_order_id > 0)) {
            return;
        }
        $this->loadData();
    }
	
	function getError() {
        return $this->error;
    }
	
	function getData() {
        return $this->attributes;
    }
	
	protected function loadData() {
        $this->attributes = $this->getOrderById($this->payment_order_id);
    }
	
	function getOrderPaymentGatewayAmount(){
		$order_info=$this->attributes;
		$user=new User();
		$user_balance=$user->getUserBalance($order_info["order_user_id"]);
		$order_credits_charge=$order_info["order_wallet_selected"]?min($order_info["order_net_charged"],$user_balance):0;
		$order_payment_gateway_charge=$order_info["order_net_charged"]-(float)$order_credits_charge;
		return round($order_payment_gateway_charge,2);
	}
	
	function getOrderPrimaryinfo(){
		 $arr_order=array();
		 $order_info=$this->attributes;
		 if ($order_info && is_array($order_info)){
			 $arr_order=array(
			 				"id"=>$order_info["order_id"],
							"reference"=>$order_info["order_reference"],
							"invoice"=>$order_info["order_invoice_number"],
							"customer_id"=>$order_info["order_user_id"],
							"customer_name"=>$order_info["order_user_name"],
							"customer_email"=>$order_info["order_user_email"],
							"customer_phone"=>$order_info["order_user_phone"],
							"customer_shipping_name"=>$order_info["order_shipping_name"],
							"customer_shipping_address_1"=>$order_info["order_shipping_address1"],
							"customer_shipping_address_2"=>$order_info["order_shipping_address2"],
							"customer_shipping_city"=>$order_info["order_shipping_city"],
							"customer_shipping_postcode"=>$order_info["order_shipping_postcode"],
							"customer_shipping_state"=>$order_info["order_shipping_state"],
							"customer_shipping_country"=>$order_info["order_shipping_country"],
							"customer_shipping_phone"=>$order_info["order_shipping_phone"],
							"customer_billing_name"=>$order_info["order_billing_name"],
							"customer_billing_address_1"=>$order_info["order_billing_address1"],
							"customer_billing_address_2"=>$order_info["order_billing_address2"],
							"customer_billing_city"=>$order_info["order_billing_city"],
							"customer_billing_postcode"=>$order_info["order_billing_postcode"],
							"customer_billing_state"=>$order_info["order_billing_state"],
							"customer_billing_country"=>$order_info["order_billing_country"],
							"customer_billing_phone"=>$order_info["order_billing_phone"],
							"order_currency_code"=>$order_info["order_currency_code"],
							"order_payment_status"=>$order_info["order_payment_status"],
							"order_language"=>$order_info["order_language"],
							"site_system_name"=>Settings::getSetting("CONF_WEBSITE_NAME"),
							"site_system_admin_email"=>Settings::getSetting("CONF_ADMIN_EMAIL"),
							"paypal_bn"=>"FATbit_SP",
						 );
		 }
		 return $arr_order;
	}
	
	function chargeUserWallet($amount,$order_id){
		$order_info=$this->attributes;
		$user=new User();
		$user_balance=$user->getUserBalance($order_info["order_user_id"]);
		if ($user_balance>=$amount){
				$formatted_order_value="#".$order_info["order_invoice_number"];
				$txnArray["utxn_user_id"]=$order_info["order_user_id"];
				$txnArray["utxn_debit"]=$amount;
				$txnArray["utxn_status"]=1;
				$txnArray["utxn_order_id"]=$order_id;
				$txnArray["utxn_comments"]=sprintf(Utilities::getLabel('L_ORDER_PLACED_NUMBER'),$formatted_order_value);
				$transObj=new Transactions();
				if($txn_id=$transObj->addTransaction($txnArray)){
					$this->addOrderPayment("Wallet",time(),$amount,Utilities::getLabel("L_Received_Payment"),"Payment From Wallet",true);
				}
		}
		
	}
	
	
	function addOrderPayment($payment_method_name,$txn_id,$amount, $comments = '', $response = '', $is_wallet=false ) {
				
		$payment_order_id=$this->payment_order_id;
		$order_details = $this->getOrderById($payment_order_id);
		$cartObj=new Cart($order_details['order_user_id']);
		$cartObj->clear();
		$cartObj->updateUserCart();
		$order_info=$this->attributes;
		if ($order_info) {
			$order_payment_financials=$this->getOrderPaymentFinancials($payment_order_id);	
			$order_credits=$order_payment_financials["order_credits_charge"];
			if (($order_info["totPayments"]==0) && ($order_credits>0)&& !$is_wallet)
				$this->chargeUserWallet($order_credits,$payment_order_id);
			
			$order_details = $this->getOrderById($payment_order_id);
			
			if (!$this->db->insert_from_array('tbl_order_payments', array('op_order_id' => $payment_order_id, 'op_payment_method' => $payment_method_name,'op_gateway_txn_id'=>$txn_id,'op_amount'=>$amount,'op_comments'=>$comments,'op_gateway_response'=>$response,'op_date' => date('Y-m-d H:i:s')),true))	{
				$this->error = $this->db->getError();
				return false;
			}
			
			if ($amount >= $order_details["order_balance"]){
				$this->addOrderPaymentHistory($payment_order_id,1,"Payment Received",1);
				$couponObj=new Coupons();
				$coupon_info=$couponObj->getCouponByCode($order_details['order_discount_coupon']);
				if ($coupon_info){
					if (!$this->db->insert_from_array('tbl_coupons_history', array('coupon_id' => (int)$coupon_info['coupon_id'], 'order_id' => (int)$order_details['order_id'], 'customer_id' => (int)$order_details['order_user_id'],'amount' => (float)$order_details['order_net_charged'],'date_added' => date('Y-m-d H:i:s')),true)) {
						$this->error = $this->db->getError();
						return false;
					}
				}
				
			}
			return true;		
		}else{
			$this->error = "Invalid Order";
			return false;
		}
		
	}
	
	function addOrderPaymentComments($comments) {
		$payment_order_id=$this->payment_order_id;
		$order_info=$this->attributes;
		if ($order_info) {
			$this->addOrderPaymentHistory($payment_order_id,0,$comments,0);
		}else{
			$this->error = "Invalid Order";
			return false;
		}
	}
	
	function getOrderProductsInfo($order_id){
		if(!isset($order_id)){
			return false;
		}
		
		$order_products = $this->getOrderProductsById($order_id);
		
		$products = array();
		foreach($order_products as $product){
			$products[$product['opr_id']]['id'] = $product['opr_id']; 
			$products[$product['opr_id']]['product_name'] = $product['opr_name']; 
			$products[$product['opr_id']]['product_sku'] = $product['opr_sku']; 
			$products[$product['opr_id']]['product_model'] = $product['opr_model']; 
			$products[$product['opr_id']]['product_price'] = $product['opr_sale_price']; 
			$products[$product['opr_id']]['product_quantity'] = $product['opr_qty']; 
			$products[$product['opr_id']]['product_tax_amount'] = $product['opr_tax']; 
			$products[$product['opr_id']]['product_shipping_amount'] = $product['opr_shipping_charges']; 
			$products[$product['opr_id']]['product_total'] = $product['opr_net_charged']; 
			$products[$product['opr_id']]['product_portal_commission_amount'] = $product['opr_commission_charged']; 
			$products[$product['opr_id']]['product_vendor_payable_amount'] = ($product['opr_net_charged'] - $product['opr_tax'] - $product['opr_commission_charged']); 
			$products[$product['opr_id']]['product_shop_id'] = $product['opr_product_shop']; 
			$products[$product['opr_id']]['product_shop_name'] = $product['opr_product_shop_name']; 
			$products[$product['opr_id']]['product_shop_owner_email'] = $product['opr_shop_owner_email']; 
			$products[$product['opr_id']]['product_currency_code'] = $product['order_currency_code']; 
			$products[$product['opr_id']]['product_refund_qty'] = $product['opr_refund_qty']; 
			$products[$product['opr_id']]['product_refund_amount'] = $product['opr_refund_amount']; 
			$products[$product['opr_id']]['invoice_id'] = $product['opr_order_invoice_number']; 
			$products[$product['opr_id']]['product_order_status'] = $product['opr_status']; 
		}
		
		return $products;
	}
	
	public function confirm_COD_Order($id){
		$id = intval($id);
		if($id < 1){
			return false;
		}
		$paymentMethodObj=new PaymentMethods();
		$payment_method=$paymentMethodObj->getData(Settings::getSetting("CONF_COD_PAYMENT_METHOD"));
		if(!$this->db->update_from_array('tbl_orders', array('order_is_cod'=>1,'order_payment_method'=>$payment_method['pmethod_name'],'order_payment_method_id'=>$payment_method['pmethod_id']), array('smt' => 'order_id = ? ', 'vals' => array((int)$id)))){
			$this->error = $this->db->getError();
			return false;
		}
		if(!$this->db->update_from_array('tbl_order_products', array('opr_cod_order'=>1), array('smt' => 'opr_order_id = ? ', 'vals' => array((int)$id)))){
			$this->error = $this->db->getError();
			return false;
		}
		$orderPaymentObj=new self($id);
		/*$comment  = Utilities::getLabel('M_PAYMENT_INSTRUCTIONS') . "\n\n";
		$comment .= Utilities::getLabel('M_Buyer_Selected_Cash_on_delivery') . "\n\n";
		$orderPaymentObj->addOrderPaymentComments($comment);*/
		$orderPaymentObj->addOrderPayment($payment_method["pmethod_name"],time(),0,Utilities::getLabel("L_Payment_to_be_received_on_cash_on_delivery"),'NA');
		$orderPaymentObj->addOrderPaymentHistory($id,2,Utilities::getLabel('M_Cash_on_delivery'),1);
		
		return true;
	}
		
}