<?php
class Orders extends Model {
	function __construct(){
		$this->db = Syspage::getdb();
		$this->currency = &Syspage::getCurrency();
    }
	
	
	function getOrderId() {
        return $this->order_id;
    }
	
	function getTotalPages(){
		return $this->total_pages;
	}
	
	function getTotalRecords(){
		return $this->total_records;
	}
	
	protected function loadData() {
        $this->attributes = self::getOrderById($this->order_id);
		$this->attributes["products"] = self::getOrderProductsById($this->order_id);
    }
	
	function getData() {
        return $this->attributes;
    }
	
	function getError() {
        return $this->error;
    }
	
	function getAttribute($attr) {
        return isset($this->attributes[$attr])?$this->attributes[$attr]:'Y';
    }
	
	function getOrderById($id,$add_criteria=array()) {
        $id = intval($id);
        if($id>0!=true) return array();
       	$add_criteria['id'] = $id;
        $srch = self::search($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function getOrders($criteria) {
        foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
		$srch->addOrder('order_id','DESC');
        $rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return false;
		}
        $row = $this->db->fetch_all($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function search($criteria, $count='') {
		
		$srch = new SearchBase('tbl_order_products', 'torp');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('torp.opr_order_id');
		$srch->addMultipleFields(array('torp.opr_order_id',"SUM(opr_qty) as totQtys","count(*) as totChildOrders"));
		$qry_order_qty = $srch->getQuery();
		
		$srch = new SearchBase('tbl_order_payments', 'torp');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('torp.op_order_id');
		$srch->addMultipleFields(array('torp.op_order_id',"COALESCE(SUM(op_amount)) as totPayments","GROUP_CONCAT(DISTINCT(op_payment_method) SEPARATOR ',') payment_methods"));
		$qry_order_payments = $srch->getQuery();
		
        $srch = new SearchBase('tbl_orders', 'tord');
		$srch->joinTable('(' . $qry_order_qty . ')', 'LEFT OUTER JOIN', 'tord.order_id = tqoq.opr_order_id', 'tqoq');
		$srch->joinTable('(' . $qry_order_payments . ')', 'LEFT OUTER JOIN', 'tord.order_id = tqop.op_order_id', 'tqop');
		$srch->joinTable('tbl_users', 'LEFT OUTER JOIN', 'tord.order_user_id = tu.user_id', 'tu');
		$srch->joinTable('tbl_affiliates', 'LEFT OUTER JOIN', 'tord.order_affiliate_id = ta.affiliate_id', 'ta');
		$srch->joinTable('tbl_users', 'LEFT OUTER JOIN', 'tord.order_referrer_id = tur.user_id', 'tur');
		if($count==true) {
            $srch->addFld('COUNT(tord.order_id) AS total_rows');
        } else {
            $srch->addMultipleFields(array('*','GREATEST((order_net_charged - IFNULL(totPayments,0)),0) as order_balance','UNIX_TIMESTAMP(order_date_added) as order_date_timestamp','tur.user_name as referrer_name','COALESCE(totChildOrders) as totChildOrders'));
        }
		
        foreach($criteria as $key=>$val) {
			//if(strval($val)=='') continue;
            switch($key) {
            case 'id':
                $srch->addCondition('tord.order_id', '=', intval($val));
                break;
			case 'invoice':
                $srch->addCondition('tord.order_invoice_number', '=',$val);
                break;	
			case 'shop':
	              //  $srch->addCondition('tord.order_shop', '=', intval($val));
                break;
			case 'affiliate':
                $srch->addCondition('tord.order_affiliate_id', '=', intval($val));
                break;
			case 'user':
                $srch->addCondition('tord.order_user_id', '=', intval($val));
                break;			
			case 'payment_status':
                $srch->addCondition('tord.order_payment_status', '=', intval($val));
                break;
			case 'status':
                $srch->addCondition('tord.order_status', '=', intval($val));
                break;
			case 'minprice':
                $srch->addCondition('tord.order_net_charged', '>=', $val);
                break;
			case 'maxprice':
                $srch->addCondition('tord.order_net_charged', '<=', $val);
                break;
			case 'date':
                $srch->addCondition('DATE(`order_date_added`)', '=', $val);
                break;
			case 'date_from':
                $srch->addCondition('tord.order_date_added', '>=', $val. ' 00:00:00');
                break;
			case 'date_to':
                $srch->addCondition('tord.order_date_added', '<=', $val. ' 23:59:59');
                break;
			case 'public':
				$srch->addcondition('tors.public_flag','=',intval($val));
			break;
			case 'customer':
                $srch->addCondition('tord.order_user_name', '=',$val);
            break;
			case 'currency':
				$srch->addcondition('tord.order_currency','=',intval($val));
			break;				
			case 'keyword':
					$cndCondition=$srch->addCondition('tord.order_user_name', 'like', '%' . $val . '%');
					$cndCondition->attachCondition('tu.user_username', 'like', '%' . $val . '%','OR');
					$cndCondition->attachCondition('tord.order_user_email', 'like', '%' . $val . '%','OR');
					$cndCondition->attachCondition('tord.order_invoice_number', 'like', '%' . $val . '%','OR');
					$cndCondition->attachCondition('tord.order_reference', 'like', '%' . $val . '%','OR');
                break;
			case 'pagesize':
				$srch->setPageSize($val);
				break;
			case 'page':
				$srch->setPageNumber($val);
				break;		
            
            }
        }
       return $srch;
    }
	
	function getOrderProductsByOprId($id,$add_criteria=array()) {
        $id = intval($id);
        if($id>0!=true) return array();
       	$add_criteria['id'] = $id;
        $srch = self::search_order_products($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else {
			//return $row;
			$option_data = array();
			$options = $this->getOrderOptions($row['order_id'], $row['opr_id']);
			foreach ($options as $option) {
					if ($option['order_option_type'] != 'file') {
						$option_data[] = array(
							'name'  => $option['order_option_name'],
							'value' => $option['order_option_value'],
							'type'  => $option['order_option_type']
						);
					} else {
						if (!empty($option['order_option_value'])) {
							$option_data[] = array(
								'name'  => $option['order_option_name'],
								'value' => $option['order_option_value'],
								'type'  => $option['order_option_type'],
								'href'  => Utilities::generateAbsoluteUrl('file', 'download_attachment',array($option['order_option_value']),CONF_WEBROOT_URL),
							);
						}
					}
				
			}
			$row['order_options']=$option_data;
			return $row;
		}
	}
	
	
	function addUpdateCustomerOrder($data) {
		$order_id = intval($data['order_id']);
		$order_user_id = intval($data['order_user_id']);
		$order_invoice_number = $data['order_invoice_number'];
		$values = array(
					'order_reference'=>$data['order_reference'],
					'order_invoice_number'=>$order_invoice_number,
					'order_user_id'=>$order_user_id,
					'order_customer_group'=>$data['order_customer_group'],
					'order_markup_used'=>$data['order_markup_used'],
					'order_user_name'=>$data['order_user_name'],
					'order_user_email'=>$data['order_user_email'],
					'order_user_phone'=>$data['order_user_phone'],
					'order_user_fax'=>$data['order_user_fax'],
					'order_shipping_name'=>$data['order_shipping_name'],
					'order_shipping_address1'=>$data['order_shipping_address1'],
					'order_shipping_address2'=>$data['order_shipping_address2'],
					'order_shipping_city'=>$data['order_shipping_city'],
					'order_shipping_postcode'=>$data['order_shipping_postcode'],
					'order_shipping_state'=>$data['order_shipping_state'],
					'order_shipping_country'=>$data['order_shipping_country'],
					'order_shipping_country_id'=>$data['order_shipping_country_id'],
					'order_shipping_phone'=>$data['order_shipping_phone'],
					'order_shipping_method'=>$data['order_shipping_method'],
					'order_shipping_required'=>$data['order_shipping_required'],
					'order_billing_name'=>$data['order_billing_name'],
					'order_billing_address1'=>$data['order_billing_address1'],
					'order_billing_address2'=>$data['order_billing_address2'],
					'order_billing_city'=>$data['order_billing_city'],
					'order_billing_postcode'=>$data['order_billing_postcode'],
					'order_billing_state'=>$data['order_billing_state'],
					'order_billing_country'=>$data['order_billing_country'],
					'order_billing_country_id'=>$data['order_billing_country_id'],
					'order_billing_phone'=>$data['order_billing_phone'],
					'order_payment_method'=>$data['order_payment_method'],
					'order_payment_method_id'=>$data['order_payment_method_id'],
					//'order_payment_status'=>0, /* Pending */
					'order_status'=>0,
					'order_date_added'=>date('Y-m-d H:i:s'),
					'order_ip_address'=>$data['order_ip_address'],
					'order_forwarded_ip'=>$data['order_forwarded_ip'],
					'order_user_agent'=>$data['order_user_agent'],
					'order_accept_language'=>$data['order_accept_language'],
					'order_payment_gateway_charges'=>$data['order_payment_gateway_charges'],
					'order_discount_coupon'=>$data['order_discount_coupon'],
					'order_discount_total'=>$data['order_discount_total'],
					'order_cart_total'=>$data['order_cart_total'],
					'order_cart_total_without_tax'=>$data['order_cart_total_without_tax'],
					'order_shipping_charged'=>$data['order_shipping_charged'],
					'order_tax_charged'=>$data['order_tax_charged'],
					'order_value_discount'=>$data['order_value_discount'],
					'order_value_discount_label'=>$data['order_value_discount_label'],
					'order_sub_total'=>$data['order_sub_total'],
					'order_net_charged'=>$data['order_net_charged'],
					//'order_credits_charged'=>$data['order_credits_charged'],
					'order_actual_paid'=>$data['order_actual_paid'],
					'order_site_commission'=>$data['order_site_commission'],
					'order_language'=>$data['order_language'],
					'order_currency'=>$data['order_currency'],
					'order_currency_code'=>$data['order_currency_code'],
					/*'order_currency_symbol_left'=>$data['order_currency_symbol_left'],
					'order_currency_symbol_right'=>$data['order_currency_symbol_right'],*/
					'order_currency_value'=>$data['order_currency_value'],
					'order_customer_comments'=>$data['order_customer_comments'],
					'order_wallet_selected'=>$data['order_wallet_selected'],
					'order_vat_perc'=>Settings::getSetting("CONF_SITE_TAX"),
					'order_affiliate_id'=>$data['order_affiliate_id'],
					'order_affiliate_commission'=>$data['order_affiliate_commission'],
					'order_referrer_id'=>$data['order_referrer_id'],
					'order_referrer_reward_points'=>$data['order_referrer_reward_points'],
					'order_referee_reward_points'=>$data['order_referee_reward_points'],
					'order_reward_points'=>$data['order_reward_points']
				);
		$broken = false;
		$record = new TableRecord('tbl_orders');
		$record->assignValues($values);
		if($order_id === 0 && $record->addNew()){
			$this->order_id=$record->getId();
		}elseif($order_id > 0 && $record->update(array('smt'=>'order_id=?', 'vals'=>array($order_id)))){
			$this->order_id=$order_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		$_SESSION['shopping_cart']["order"]=$this->getOrderId();
		
		$this->db->deleteRecords('tbl_order_products', array('smt' => 'opr_order_id = ?', 'vals' => array($this->getOrderId())));
		$this->db->deleteRecords('tbl_order_product_options', array('smt' => 'order_option_order_id = ?', 'vals' => array($this->getOrderId())));
		
		if(isset($data['products']) && is_array($data['products']) && sizeof($data['products']) > 0){
				foreach($data['products'] as $product_id => $product){
					$cnt++;
					$new_value=str_pad($cnt,4,'0',STR_PAD_LEFT);
					$sub_order_inv_number_prefix= "-S".$new_value;
				    $sub_order_inv_number=$order_invoice_number.$sub_order_inv_number_prefix;
					$values = array(
							'opr_order_id'=>$this->getOrderId(),
							'opr_order_invoice_number'=>$sub_order_inv_number,
							'opr_product_id'=>$product["product_id"],
							'opr_product_type'=>$product["product_type"],
							'opr_qty'=>$product['quantity'],
							'opr_name'=>$product['name'],
							'opr_sku'=>$product['sku'],
							'opr_brand'=>$product['brand'],
							'opr_model'=>$product['model'],
							//'opr_vendor'=>$product['vendor'],
							'opr_shop_owner_name'=>$product['vendor_name'],
							'opr_shop_owner_username'=>$product['vendor_username'],
							'opr_shop_owner_email'=>$product['vendor_email'],
							'opr_shop_owner_phone'=>$product['vendor_phone'],
							'opr_product_shop'=>$product['shop'],
							'opr_product_shop_name'=>$product['shop_name'],
							'opr_retail_price'=>$product["retail_price"],
					 		'opr_sale_price'=>$product["sale_price"],
					 		'opr_discount_total'=>$product['discount'],
					 		'opr_customer_buying_price'=>$product["product_price"],
					 		'opr_customization_price'=>$product["option_price"],
					 		'opr_customer_customization_price'=>$product["option_price"],
					 		'opr_commission_charged'=>$product["commission"],
							'opr_commission_percentage'=>$product["commission_percentage"],
					 		'opr_ship_free'=>$product["ship_free"],
					 		'opr_tax_free'=>$product["tax_free"],
					 		'opr_string'=>$product["opr_string"],
					 		'opr_customization_string'=>$product["customization"],
					 		'opr_shipping_required'=>$product["shipping_required"],
							'opr_shipping'=>$product["shipping_mode"],
					 		'opr_shipping_days'=>$product["shipping_days"],
					 		'opr_shipping_company'=>$product["shipping_company"],
					 		'opr_shipping_charges'=>$data["order_shipping_charged"]>0?$product["shipping_charges"]:0,
					 		'opr_shipping_label'=>$product["shipping_label"],
					 		'opr_tax'=>$product["tax_charged"],
							
							'opr_qty_tax'=>$product["qty_tax_charged"],
							'opr_net_charged'=>$product["net_charged"],
							'opr_status'=>0,
							'opr_affiliate_commission_percentage'=>$product["affiliate_commission_percentage"],
							'opr_affiliate_commission'=>$product["affiliate_commission"],
							
						);
					if($this->db->insert_from_array('tbl_order_products', $values)){
						$inserted_item_id = $this->db->insert_id();
						if(isset($product['options']) && is_array($product['options']) && sizeof($product['options']) > 0){
							foreach($product['options'] as $option_id=>$option){
									$values = array(
												'order_option_order_id'=>$this->getOrderId(),
												'order_option_sub_order_id'=>$inserted_item_id,
												'order_option_product_id'=>$product["product_id"],
												'order_option_product_option_id'=>$option["product_option_id"],
												'order_option_product_option_value_id'=>$option['product_option_value_id'],
												'order_option_name'=>$option['name'],
												'order_option_value'=>$option['value'],
												'order_option_type'=>$option['type'],
											);
									if(!$this->db->insert_from_array('tbl_order_product_options', $values)){
										return false;
									}
								
							}
						}
					}else{
						return false;
					}
					
					$pObj= new Products();
					$digital_files = $pObj->getProductDigitalFiles($product["product_id"]);
					if (is_array($digital_files) & count($digital_files)>0){
						$this->db->deleteRecords('tbl_order_product_files', array('smt' => 'opf_opr_id = ?', 'vals' => array($inserted_item_id)));
						foreach($digital_files as $dgkey=>$dgval){
									if ($dgval["pfile_max_download_times"]!="-1")
										$downloadCount=$product["quantity"]*$dgval["pfile_max_download_times"];
									else
										$downloadCount=$dgval["pfile_max_download_times"];
									
									$values = array(
													'opf_opr_id'=>$inserted_item_id,
													'opf_file_download_name'=>$dgval['pfile_download_name'],
													'opf_file_name'=>$dgval["pfile_name"],
													'opf_file_max_download_times'=>$downloadCount,
													'opf_file_can_be_downloaded_within_days'=>$dgval['pfile_can_be_downloaded_within_days'],
													'opf_remaining_downloaded_times'=>$downloadCount,
												);
										if(!$this->db->insert_from_array('tbl_order_product_files', $values)){
											return false;
										}
							
						}
					}
				}
			
			
			}else{
				return false;
			}
			
			return $this->getOrderId();
		
		
	}
	
	function search_order_products($criteria, $count='') {
		
		$srch=new SearchBase('tbl_order_products','torp');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('torp.opr_order_id');
		$srch->addMultipleFields(array('torp.opr_order_id',"count(*) as totCombinedOrders"));
		$qry_combined_orders = $srch->getQuery();
		
		$srch = new SearchBase('tbl_order_payments', 'torp');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('torp.op_order_id');
		$srch->addMultipleFields(array('torp.op_order_id',"COALESCE(SUM(op_amount)) as totPayments","GROUP_CONCAT(DISTINCT(op_payment_method) SEPARATOR ', ') payment_methods"));
		$qry_order_payments = $srch->getQuery();
		
		
        $srch = new SearchBase('tbl_order_products', 'torp');
		$srch->joinTable('tbl_order_pp_adaptive_payments', 'LEFT JOIN', 'topap.ppadappay_order_id =torp.opr_order_id', 'topap');
		$srch->joinTable('tbl_orders', 'LEFT JOIN', 'tord.order_id =torp.opr_order_id', 'tord');
		$srch->joinTable('(' . $qry_combined_orders . ')', 'LEFT OUTER JOIN', 'tord.order_id = tqco.opr_order_id', 'tqco');
		$srch->joinTable('(' . $qry_order_payments . ')', 'LEFT OUTER JOIN', 'tord.order_id = tqop.op_order_id', 'tqop');
		$srch->joinTable('tbl_orders_status', 'LEFT JOIN', 'torp.opr_status = tors.orders_status_id', 'tors');
		$srch->joinTable('tbl_users', 'LEFT OUTER JOIN', 'tord.order_user_id = tcu.user_id', 'tcu');
		$srch->joinTable('tbl_shops', 'LEFT OUTER JOIN', 'torp.opr_product_shop = ts.shop_id', 'ts');
		
		$srch->joinTable('tbl_states', 'LEFT OUTER JOIN', 'ts.shop_state = tst.state_id', 'tst');
		$srch->joinTable('tbl_countries', 'LEFT OUTER JOIN', 'ts.shop_country = tco.country_id', 'tco');
		
		$srch->joinTable('tbl_prod_refund_requests', 'LEFT OUTER JOIN', 'torp.opr_id = tprr.refund_order and tprr.refund_prod_id=torp.opr_product_id', 'tprr');
		$srch->joinTable('tbl_shipping_companies', 'LEFT JOIN', 'torp.opr_shipping_company =tsc.scompany_id', 'tsc');
		$srch->joinTable('tbl_affiliates', 'LEFT OUTER JOIN', 'tord.order_affiliate_id = ta.affiliate_id', 'ta');
		$srch->joinTable('tbl_users', 'LEFT OUTER JOIN', 'tord.order_referrer_id = tur.user_id', 'tur');
		$srch->addMultipleFields(array('tord.*,torp.*,tors.*,tcu.*,ts.*,tsc.*,tqop.*,ts.shop_phone as vendor_phone','tqco.totCombinedOrders as totOrders','(opr_refund_amount + opr_refund_tax) as opr_total_refund_amount','tprr.refund_id','UNIX_TIMESTAMP(order_date_added) as order_date_timestamp','ta.affiliate_id,ta.affiliate_name','tur.user_name as referrer_name','topap.ppadappay_chained_payments_status as PA_TXN_STS','tst.state_name as shop_state_name','tco.country_name as shop_country_name','tsc.scompany_name as shipping_company_name'));
        foreach($criteria as $key=>$val) {
			if(strval($val)=='') continue;
            switch($key) {
            case 'id':
                $srch->addCondition('torp.opr_id', '=', intval($val));
                break;
			case 'inv':
                $srch->addCondition('torp.opr_order_invoice_number', '=',$val);
                break;	
			case 'order':
                $srch->addCondition('tord.order_id', '=', intval($val));
                break;
			case 'affiliate':
                $srch->addCondition('tord.order_affiliate_id', '=', intval($val));
                break;		
			case 'shop':
                $srch->addCondition('tord.order_shop', '=', intval($val));
                break;
			case 'shop_name':
                $srch->addCondition('torp.opr_product_shop_name', '=', ($val));
                break;
			case 'vendor_name':
                $srch->addCondition('torp.opr_shop_owner_name', '=', ($val));
                break;	
			case 'customer_name':
                $srch->addCondition('tord.order_user_name', '=', ($val));
                break;
			case 'vendor':
                $srch->addCondition('ts.shop_user_id', '=', intval($val));
                break;	
			case 'customer':
                $srch->addCondition('tord.order_user_id', '=', intval($val));
                break;
			case 'payment_status':
                $srch->addCondition('tord.order_payment_status', '=', intval($val));
                break;				
			case 'status':
				if (is_array($val))
	                $srch->addCondition('torp.opr_status', 'IN', $val);
				else
					$srch->addCondition('torp.opr_status', '=', intval($val));	
                break;
			case 'status_not':
				if (is_array($val))
	                $srch->addCondition('torp.opr_status', 'NOT IN', $val);
				else
					$srch->addCondition('torp.opr_status', '!=', intval($val));	
                break;	
			case 'public':
				$srch->addcondition('tors.public_flag','=',intval($val));
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
			case 'minprice':
                $srch->addCondition('(`opr_net_charged`-`order_discount_total`)', '>=', $val);
				//$srch->addDirectCondition("opr_net_charged-order_discount_total >= $val");
                break;
			case 'maxprice':
                $srch->addCondition('(`opr_net_charged`-`order_discount_total`)', '<=', $val);
				//$srch->addDirectCondition("opr_net_charged-order_discount_total <= $val");
                break;
			case 'minprice_vendor':
                $srch->addCondition('opr_net_charged', '>=', $val);
                break;
			case 'maxprice_vendor':
                $srch->addCondition('opr_net_charged', '<=', $val);
                break;
			case 'pagesize':
				$srch->setPageSize($val);
				break;
			case 'page':
				$srch->setPageNumber($val);
				break;
			case 'keyword':
					$cndCondition=$srch->addCondition('tord.order_user_name', 'like', '%' . $val . '%');
					$cndCondition->attachCondition('tord.order_user_email', 'like', '%' . $val . '%','OR');
					$cndCondition->attachCondition('torp.opr_product_shop_name', 'like', '%' . $val . '%','OR');	
					$cndCondition->attachCondition('torp.opr_name', 'like', '%' . $val . '%','OR');	
					$cndCondition->attachCondition('torp.opr_order_invoice_number', 'like', '%' . $val . '%','OR');
             break;				
            
            }
        }
        return $srch;
    }
	
	function getChildOrders($criterias){
		$srch = self::search_order_products($criterias);
		//die($srch->getquery());
		$srch->addOrder("opr_id","desc");
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return false;
		}
		$orders = array();
		$inc=0;
		while($row = $this->db->fetch($rs)){
			$inc++;
			$orders[$inc] = $row;
			$orders[$inc]["address"] = $this->getOrderBillingShippingAddress($row["order_id"]);
			$orders[$inc]["comments"] = $this->getOrderComments(array("opr"=>$row["opr_id"]));
		}
		return ($criterias["pagesize"]==1)?$orders[1]:$orders;
	}
	
	
	function getOrderBillingShippingAddress($order_id=0){
		$row=($order_id>0)?self::getOrderById($order_id):self::getData($this->order_id);
		$address2=$row["order_billing_address2"]!=""?"<br/>".$row["order_billing_address2"]:"";
		$phone=$row["order_billing_phone"]!=""?"<br/><strong>T</strong>: ".$row["order_billing_phone"]:"";
		$address["billing"]="<strong>".$row["order_billing_name"]."</strong><br />".$row["order_billing_address1"].$address2."<br/>".$row["order_billing_city"].", ".$row["order_billing_state"]." - ".$row["order_billing_postcode"]."<br />".$row["order_billing_country"].$phone;
		$address2=$row["order_shipping_address2"]!=""?"<br/>".$row["order_shipping_address2"]:"";
		$phone=$row["order_shipping_phone"]!=""?"<br/><strong>T</strong>: ".$row["order_shipping_phone"]:"";
		$address["shipping"]="<strong>".$row["order_shipping_name"]."</strong>".$row["order_shipping_address1"].$address2."<br/>".$row["order_shipping_city"].", ".$row["order_shipping_state"]." - ".$row["order_shipping_postcode"]."<br />".$row["order_shipping_country"].$phone;
		return $address;
	}
	
	
	
	function getOrderComments($criteria=array(),$pagesize=0) {
        if(count($criteria)==0) return array();
	    $srch = new SearchBase('tbl_orders_status_history', 'tosh');
		$srch->joinTable('tbl_orders_status', 'LEFT OUTER JOIN', 'tosh.orders_status_id = tos.orders_status_id', 'tos');
		$srch->joinTable('tbl_order_products', 'LEFT OUTER JOIN', 'tosh.opr_id = torp.opr_id', 'torp');
		$srch->joinTable('tbl_orders', 'LEFT OUTER JOIN', 'torp.opr_order_id = tor.order_id', 'tor');
		$srch->joinTable('tbl_shipping_companies', 'LEFT JOIN', 'torp.opr_shipping_company =tsc.scompany_id', 'tsc');
        foreach($criteria as $key=>$val) {
			if(strval($val)=='') continue;
            switch($key) {
            case 'id':
                $srch->addCondition('tosh.orders_status_history_id', '=', intval($val));
                break;
			case 'order':
                $srch->addCondition('tosh.orders_id', '=', intval($val));
                break;
			case 'opr':
                $srch->addCondition('tosh.opr_id', '=', intval($val));
                break;	
            }
        }
		//die($srch->getQuery());
		if(intval($pagesize)>0){
			$srch->setPageSize($pagesize);
		}else{
			$srch->doNotLimitRecords();
		}
        $srch->doNotCalculateRecords(true);
		$srch->addOrder('orders_status_history_id');
        $rs = $srch->getResultSet();
		return ($pagesize==1)?$this->db->fetch($rs):$this->db->fetch_all($rs);
	}
	
	
	
	function addVendorOrderComments($data){
		$opr_id = intval($data['opr_id']);
		$orders_id = intval($data['orders_id']);
		unset($data['opr_id']);
		unset($data['orders_id']);
		if (($opr_id < 1) && ($orders_id < 1)){
			$this->error = 'Invalid request!!';
			return false;
		}
		$record = new TableRecord('tbl_orders_status_history');
		$data["date_added"]=date('Y-m-d H:i:s');
		$data["opr_id"]=$opr_id;
		$data["orders_id"]=$orders_id;
		$record->assignValues($data);
		if($record->addNew()){
				$emailNotObj=new Emailnotifications();
				if ($emailNotObj->BuyerOrderNotification()){
					return intval($record->getId());
				}else{
					$this->error=$emailNotObj->getError();
				}
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return false;
	}
	
	// This is to get order products in table format to send in email
	function getSubOrderDetail($oid){
		
		$str='<table cellspacing="0" cellpadding="0" border="0" width="100%" style="border:1px solid #ddd; border-collapse:collapse;"><tr><td style="padding:10px;background:#eee;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;" width="45%">'.Utilities::getLabel('L_Product').'</td><td style="padding:10px;background:#eee;font-size:13px; border:1px solid #ddd;color:#333; font-weight:bold;" width="5%">'.Utilities::getLabel('L_Qty').'</td><td style="padding:10px;background:#eee;font-size:13px; border:1px solid #ddd;color:#333; font-weight:bold;" width="10%">'.Utilities::getLabel('L_Price').'</td><td style="padding:10px;background:#eee;font-size:13px; border:1px solid #ddd;color:#333; font-weight:bold;" width="10%">'.Utilities::getLabel('L_Total').'</td></tr>';
		//$vendor_order_detail=self::getOrderProductsByOprId($oid);
		$vendor_order_detail=$this->getOrderProductsByOprId($oid);
		$vendor_order_detail["address"]=$this->getOrderBillingShippingAddress($vendor_order_detail["opr_order_id"]);
		$inc++;
		$opr_customer_buying_price=$vendor_order_detail["opr_customer_buying_price"];
		$sku_codes=$vendor_order_detail['opr_code']!=""?$vendor_order_detail['opr_code']:$vendor_order_detail['opr_sku'];
		$options=$vendor_order_detail["opr_customization_string"];
		$customization_price=$vendor_order_detail["opr_customer_customization_price"];
		//$adds=" (+".$this->currency->format($customization_price,$vendor_order_detail['order_currency_code'],$vendor_order_detail['order_currency_value']).")";
		$options=(($options=="")?"":"<strong>".Utilities::getLabel('L_COMBINATION_SELECTED')."</strong>$adds").$options;
		$opr_customer_buying_price+=$customization_price;
		$ind_price_total=$vendor_order_detail["opr_qty"]*$opr_customer_buying_price+$vendor_order_detail["opr_shipping_charges"]+$vendor_order_detail['opr_tax']; 
		//$shipping_charges="<br/>".Utilities::getLabel("M_Shipping_Charges")." (+".Utilities::displayMoneyFormat($vendor_order_detail["opr_shipping_charges"]).")";
		
		$shipping_charges="<br/><strong>".Utilities::getLabel('M_Shipping_Charges')."</strong> (+".$this->currency->format($vendor_order_detail["opr_shipping_charges"],$vendor_order_detail['order_currency_code'],$vendor_order_detail['order_currency_value']).")";
		
		
		$str.='<tr>
				<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;"><a href="'.Utilities::generateAbsoluteUrl('products','view',array($vendor_order_detail["opr_product_id"]),"/").'" style="font-size:13px; color:#333;">'.$vendor_order_detail["opr_name"].'</a><br/>'.$options.'
				</td>
				<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;">'.$vendor_order_detail['opr_qty'].'</td>
				<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" nowrap="nowrap">'.$this->currency->format($vendor_order_detail["opr_customer_buying_price"] + $vendor_order_detail["opr_customer_customization_price"]  ,$vendor_order_detail["order_currency_code"],$vendor_order_detail["order_currency_value"]).'</td>
				
				
			<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" nowrap="nowrap">'.$this->currency->format( $vendor_order_detail["opr_qty"] * ($vendor_order_detail["opr_customer_buying_price"] + $vendor_order_detail["opr_customer_customization_price"] ) ,$vendor_order_detail["order_currency_code"],$vendor_order_detail["order_currency_value"]).'</td>
		  </tr>';
       
	   $str.='<tr><td colspan="3" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.Utilities::getLabel('L_CART_TOTAL').'</td><td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.$this->currency->format( $vendor_order_detail['opr_qty'] * ($vendor_order_detail['opr_customer_buying_price'] + $vendor_order_detail['opr_customer_customization_price']),$vendor_order_detail['order_currency_code'],$vendor_order_detail['order_currency_value']).'</td></tr>';
	  
	  if ($vendor_order_detail['opr_shipping_charges']>0){
	  $str.='<tr><td colspan="3" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.Utilities::getLabel('L_SHIPPING').'</td><td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.$this->currency->format($vendor_order_detail['opr_shipping_charges'],$vendor_order_detail['order_currency_code'],$vendor_order_detail['order_currency_value']).'</td></tr>';
	  }
	  
	  if ($vendor_order_detail['opr_tax']>0){
	  $str.='<tr><td colspan="3" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.Utilities::getLabel('L_VAT').'</td><td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.$this->currency->format($vendor_order_detail['opr_tax'],$order_details['order_currency_code'],$order_details['order_currency_value']).'</td></tr>';
	   }
	
	   $str.='<tr><td colspan="3" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right"><strong>'.Utilities::getLabel('L_ORDER_TOTAL').'</strong></td><td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right"><strong>'.$this->currency->format($vendor_order_detail['opr_net_charged'],$vendor_order_detail['order_currency_code'],$vendor_order_detail['order_currency_value']).'</strong></td></tr>';
	
	   $shipping_address = $vendor_order_detail['opr_shipping_required']?$vendor_order_detail["address"]["shipping"]:'-NA-';	
	   $str.='</table><br/><br/><table cellspacing="0" cellpadding="0" border="0" width="100%" style="border:1px solid #ddd; border-collapse:collapse;"><tr><td style="padding:10px;background:#eee;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;"  bgcolor="#f0f0f0"><strong>'.Utilities::getLabel('L_Order_Billing_Details').'</strong></td><td style="padding:10px;background:#eee;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;" bgcolor="#f0f0f0"><strong>'.Utilities::getLabel('L_Order_Shipping_Details').'</strong></td></tr><tr><td valign="top" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" >'.$vendor_order_detail["address"]["billing"].'</td>';
	   $str.='<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;">'.$shipping_address.'</td></tr></table>';
		return $str;
		
	}
	
	
	public function addOrderHistory($order_id, $order_status_id, $comment = '', $notify = false) {
		$order_info = $this->getOrderById($order_id);
		if ($order_info) {
			if (!$this->db->update_from_array('tbl_orders', array('order_status' => (int)$order_status_id,'order_date_updated' => date('Y-m-d H:i:s')),
					array('smt' => 'order_id = ? ', 'vals' => array((int)$order_info["order_id"])),true)){
					$this->error = $this->db->getError();
					return false;
			}
			/*if (!$this->db->insert_from_array('tbl_orders_status_history', array('orders_id' => $order_id, 'orders_status_id' => $order_status_id, 'date_added' => date('Y-m-d H:i:s'),'customer_notified' => (int)$notify,'comments' => $comment),true)) {
				$this->error = $this->db->getError();
				return false;
			}*/
			// If order status is 0 then becomes greater than 0 send main html email
			if (!$order_info['order_status'] && $order_status_id) {
				$emailNotificationObj=new Emailnotifications();
				$emailNotificationObj->New_Order_Buyer_Admin($order_info["order_id"]);
			}
		}
		return true;
	}
	
	
	
	public function addOrderPaymentHistory($order_id, $order_payment_status, $comment = '', $notify = false) {
		$order_info = $this->getOrderById($order_id);
		if ($order_info) {
			if (!$this->db->update_from_array('tbl_orders', array('order_payment_status' => (int)$order_payment_status,'order_date_updated' => "now()"),
					array('smt' => 'order_id = ? ', 'vals' => array((int)$order_id)))){
					$this->error = $this->db->getError();
					return false;
			}
			if (!$this->db->insert_from_array('tbl_orders_status_history', array('orders_id' => $order_id, 'orders_payment_status' => $order_payment_status, 'date_added' => date('Y-m-d H:i:s'),'customer_notified' => (int)$notify,'comments' => $comment),true)) {
				$this->error = $this->db->getError();
				return false;
			}
			
			// If order Payment status is 0 then becomes greater than 0 send main html email
			if (!$order_info['order_payment_status'] && $order_payment_status) {
				$emailNotificationObj=new Emailnotifications();
				$emailNotificationObj->Order_Payment_Update_Buyer_Admin($order_info["order_id"]);
			}
			
			// If order Payment status is 0 then becomes greater than 0 mail to Vendors and Update Child Order Status to Paid & Give Referral Reward Points 
			if (!$order_info['order_payment_status'] && ($order_payment_status>0)) {
				$emailNotificationObj=new Emailnotifications();
				$emailNotificationObj->New_Order_Vendor($order_info["order_id"]);
				$sub_orders=$this->getChildOrders(array("order"=>$order_id));
	 			foreach ($sub_orders as $subkey=>$subval){
					$child_order_payment_status = $order_payment_status==1?Settings::getSetting("CONF_DEFAULT_PAID_ORDER_STATUS"):Settings::getSetting("CONF_DEFAULT_COD_ORDER_STATUS");
					$this->addChildOrderHistory($subval["opr_id"],$child_order_payment_status,'',true);
				}
				
				
				
				/**************** Start Reward Points to Referrer *********************/
				if ($order_info['order_referrer_id'] && $order_info['order_referrer_reward_points']){
					if ((int)Settings::getSetting("CONF_SALE_REFERRER_REWARD_POINTS_VALIDITY")>0){
						$reward_expiry_date = date('Y-m-d', strtotime('+'.(int)Settings::getSetting("CONF_SALE_REFERRER_REWARD_POINTS_VALIDITY").' days'));
					}
					$rewardObj=new Rewards();
					$rewardArray=array(
						"urp_user_id"=>$order_info['order_referrer_id'],
						"urp_referrer_id"=>$order_info['order_user_id'],
						"urp_points"=>$order_info['order_referrer_reward_points'],
						"urp_date_expiry"=>$reward_expiry_date,
						"urp_description"=>sprintf(Utilities::getLabel('L_REFEREE_SALE_REWARD_POINTS_RECEIVED'),'<i>'.$order_info['order_user_name'].'</i>'),
					);
					if($reward_point_id = $rewardObj->addRewardPoints($rewardArray)){
						$emailNotificationObj=new Emailnotifications();
						$emailNotificationObj->sendRewardPointsNotification($reward_point_id);
					}else{
						$this->error=$rewardObj->getError();
						return false;
					}
				}
				/**************** End Reward Points to Referrer *********************/
				
				/**************** Start Reward Points to Referral *********************/
				if ($order_info['order_referrer_id'] && $order_info['order_referee_reward_points']){
					$uObj = new User();
					$referrer_info = $uObj->getUser(array('id'=>$order_info['order_referrer_id'], 'get_flds'=>array('user_name,user_id')));
					
					if ((int)Settings::getSetting("CONF_SALE_REFEREE_REWARD_POINTS_VALIDITY")>0){
						$reward_referee_expiry_date = date('Y-m-d', strtotime('+'.(int)Settings::getSetting("CONF_SALE_REFEREE_REWARD_POINTS_VALIDITY").' days'));
					}
					$rewardObj=new Rewards();
					$rewardArray=array(
						"urp_user_id"=>$order_info['order_user_id'],
						"urp_referrer_id"=>$order_info['order_referrer_id'],
						"urp_points"=>$order_info['order_referee_reward_points'],
						"urp_date_expiry"=>$reward_referee_expiry_date,
						"urp_description"=>sprintf(Utilities::getLabel('L_REFERRAL_SALE_REWARD_POINTS_RECEIVED'),'<i>'.$referrer_info['user_name'].'</i>'),
					);
					if($reward_point_id = $rewardObj->addRewardPoints($rewardArray)){
						$emailNotificationObj=new Emailnotifications();
						$emailNotificationObj->sendRewardPointsNotification($reward_point_id);
					}else{
						$this->error=$rewardObj->getError();
						return false;
					}
				}
				/**************** End Reward Points to Referral *********************/
				
			}
			
			// If order Payment status is 0 then becomes less than 0 send mail to Vendors and Update Child Order Status to Cancelled
			if (!$order_info['order_payment_status'] && ($order_payment_status<0)) {
				$emailNotificationObj=new Emailnotifications();
				$sub_orders=$this->getChildOrders(array("order"=>$order_id));
	 			foreach ($sub_orders as $subkey=>$subval){
					$this->addChildOrderHistory($subval["opr_id"],Settings::getSetting("CONF_DEFAULT_CANCEL_ORDER_STATUS"),'',true);
				}
			}
			
			
			
		}
		return true;
	}
	
	public function addChildOrderHistory($child_order_id, $opr_order_status_id, $comment = '', $notify = false,$tracking_number,$release_payments=0) {
		$p=new Products();
		$child_order_info=$this->getOrderProductsByOprId($child_order_id);
		if ($child_order_info) {
			if (!$this->db->update_from_array('tbl_order_products', array('opr_status' => (int)$opr_order_status_id),
					array('smt' => 'opr_id = ? ', 'vals' => array((int)$child_order_id)))){
					$this->error = $this->db->getError();
					return false;
			}
			if (!$this->db->insert_from_array('tbl_orders_status_history', array('opr_id' => $child_order_id, 'orders_status_id' => $opr_order_status_id, 'date_added' => date('Y-m-d H:i:s'),'customer_notified' => (int)$notify,'comments' => $comment,'tracking_number'=>$tracking_number),true)) {
				$this->error = $this->db->getError();
				return false;
			}else{
				$comment_id = $this->db->insert_id();
			}
			
			// If current order status is not paid up but new status is paid then commence updating the product's weightage	
			if (!in_array($child_order_info['opr_status'],(array)Settings::getSetting("CONF_DEFAULT_PAID_ORDER_STATUS")) && in_array($opr_order_status_id,(array)Settings::getSetting("CONF_DEFAULT_PAID_ORDER_STATUS")) && (!$child_order_info['opr_cod_order'])){
				$p->recordProductWeightage($child_order_info['opr_product_id'],'products#order_paid');
				$p->addUpdateProductBrowsingHistory($child_order_info['opr_product_id'],array("ordered"=>1));
				
			}
			
			// If current order status is not processing or complete but new status is processing or complete then commence completing the order
			if (!in_array($child_order_info['opr_status'], array_merge((array)Settings::getSetting("CONF_PROCESSING_ORDER_STATUS"),(array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS"))) && in_array($opr_order_status_id, array_merge((array)Settings::getSetting("CONF_PROCESSING_ORDER_STATUS"), (array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS")))) {
				// Stock subtraction
				
				$this->db->query("UPDATE tbl_products SET prod_stock = (prod_stock - " . (int)$child_order_info['opr_qty'] . "),prod_sold_count = (prod_sold_count + " . (int)$child_order_info['opr_qty'] . ") WHERE prod_id = '" . (int)$child_order_info['opr_product_id'] . "' AND prod_subtract_stock = '1'");
				
				
				$product_info=$p->getProductPrimaryInfo($child_order_info['opr_product_id']);
				if (($product_info["prod_threshold_stock_level"]>=$product_info["prod_stock"]) && ($product_info["prod_track_inventory"]==1)){
					$emailNotificationObj=new Emailnotifications();
					$emailNotificationObj->sendProductStockAlert($child_order_info['opr_product_id']);
				}
				$rs_order_option_query = $this->db->query("SELECT * FROM tbl_order_product_options WHERE order_option_sub_order_id = '" . (int)$child_order_info["opr_id"] . "' AND order_option_product_id = '" . (int)$child_order_info['opr_product_id'] . "'");
				while ($order_option=$this->db->fetch($rs_order_option_query)){
					$this->db->query("UPDATE tbl_product_option_value SET quantity = (quantity - " . (int)$child_order_info['opr_qty'] . ") WHERE product_option_value_id = '" . (int)$order_option['order_option_product_option_value_id'] . "' AND subtract = '1'");
					}
				
				
			}
			// If old order status is the processing or complete status but new status is not then commence restock, and remove coupon, voucher and reward history
			if (in_array($child_order_info['opr_status'], array_merge((array)Settings::getSetting("CONF_PROCESSING_ORDER_STATUS"),(array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS"))) && !in_array($opr_order_status_id, array_merge((array)Settings::getSetting("CONF_PROCESSING_ORDER_STATUS"),(array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS")))) {
				
				// ReStock subtraction
				$this->db->query("UPDATE tbl_products SET prod_stock = (prod_stock + " . (int)$child_order_info['opr_qty'] . "),prod_sold_count = (prod_sold_count - " . (int)$child_order_info['opr_qty'] . ") WHERE prod_id = '" . (int)$child_order_info['opr_product_id'] . "' AND prod_subtract_stock = '1'");
				$rs_order_option_query = $this->db->query("SELECT * FROM tbl_order_product_options WHERE order_option_sub_order_id = '" . (int)$child_order_info["opr_id"] . "' AND order_option_product_id = '" . (int)$child_order_info['opr_product_id'] . "'");
				while ($order_option=$this->db->fetch($rs_order_option_query)){
					$this->db->query("UPDATE tbl_product_option_value SET quantity = (quantity + " . (int)$child_order_info['opr_qty'] . ") WHERE product_option_value_id = '" . (int)$order_option['order_option_product_option_value_id'] . "' AND subtract = '1'");
				}
				
				//$this->db->deleteRecords('tbl_coupons_history', array('smt' => 'order_id = ?', 'vals' => array((int)$child_order_info["order_id"])));
				
			}
			
			// If current order status is not cancelled but new status is cancelled then commence cancelling the order
			if ( ($child_order_info['opr_status']!=Settings::getSetting("CONF_DEFAULT_CANCEL_ORDER_STATUS")) && ($opr_order_status_id == Settings::getSetting("CONF_DEFAULT_CANCEL_ORDER_STATUS")) && ($child_order_info["order_payment_status"]==1) ) {
				$formatted_request_value="#".$child_order_info["opr_order_invoice_number"];
				$comments=sprintf(Utilities::getLabel('L_Order_Number_Comments'),$formatted_request_value);
				$txn_amount=$child_order_info['opr_net_charged'];
				if ($txn_amount>0){
					
					if (Settings::getSetting("CONF_PROCESS_ORDER_REFUND_CANCELLATION")=="A_C"){
						$txnArray["utxn_user_id"]=$child_order_info['order_user_id'];
						$txnArray["utxn_credit"]=$txn_amount;
						$txnArray["utxn_status"]=1;
						$txnArray["utxn_order_id"]=$child_order_info['opr_id'];
						$txnArray["utxn_comments"]=$comments;
						$transObj=new Transactions();
						if($txn_id=$transObj->addTransaction($txnArray)){
							$emailNotificationObj=new Emailnotifications();
							$emailNotificationObj->sendTxnNotification($txn_id);
						}
					}elseif (Settings::getSetting("CONF_PROCESS_ORDER_REFUND_CANCELLATION")=="R_P"){
							$comments=sprintf(Utilities::getLabel('L_ORDER_CANCELLED_REWARDS_POINTS_ADDED'),$formatted_request_value);
							$rewardObj=new Rewards();
							$rewardArray["urp_user_id"]=$child_order_info['order_user_id'];
							$rewardArray["urp_points"]=$txn_amount;
							$rewardArray["urp_description"]=$comments;
							if ($reward_point_id = $rewardObj->addRewardPoints($rewardArray)){
								$emailNotificationObj=new Emailnotifications();
								$emailNotificationObj->sendRewardPointsNotification($reward_point_id);
							}
					}
					
					
				}
				
				if (!$child_order_info['opr_cod_order']){
					$p->recordProductWeightage($child_order_info['opr_product_id'],'products#order_cancelled');
					$p->addUpdateProductBrowsingHistory($child_order_info['opr_product_id'],array("cancelled"=>1));
				}
				
			}
			
			// If current order status is not return request approved but new status is return request approved then commence the order operation
			if (!in_array($child_order_info['opr_status'],(array)Settings::getSetting("CONF_RETURN_REQUEST_APPROVED_ORDER_STATUS")) && in_array($opr_order_status_id,(array)Settings::getSetting("CONF_RETURN_REQUEST_APPROVED_ORDER_STATUS")) && ($child_order_info["order_payment_status"]==1)) {
				
				$formatted_request_value="#".$child_order_info["opr_order_invoice_number"];
				$comments = sprintf(Utilities::getLabel('L_Return_Request_Approved'),$formatted_request_value);
				$txn_amount=$child_order_info['opr_refund_amount']+$child_order_info['opr_refund_tax'];
				if ($txn_amount>0){
					if (Settings::getSetting("CONF_PROCESS_ORDER_REFUND_CANCELLATION")=="A_C"){
						$txnArray["utxn_user_id"]=$child_order_info['order_user_id'];
						$txnArray["utxn_credit"]=$txn_amount;
						$txnArray["utxn_status"]=1;
						$txnArray["utxn_order_id"]=$child_order_info['opr_id'];
						$txnArray["utxn_comments"]=$comments;
						$transObj=new Transactions();
						if($txn_id=$transObj->addTransaction($txnArray)){
							$emailNotificationObj=new Emailnotifications();
							$emailNotificationObj->sendTxnNotification($txn_id);
						}
					}elseif (Settings::getSetting("CONF_PROCESS_ORDER_REFUND_CANCELLATION")=="R_P"){
							$comments=sprintf(Utilities::getLabel('L_RETURN_REQUEST_APPROVED_REWARDS_POINTS_ADDED'),$formatted_request_value);
							$rewardObj=new Rewards();
							$rewardArray["urp_user_id"]=$child_order_info['order_user_id'];
							$rewardArray["urp_points"]=$txn_amount;
							$rewardArray["urp_description"]=$comments;
							$rewardObj->addRewardPoints($rewardArray);
					}
				}
				
			}
			
			// If current order status is not shipped but new status is shipped then commence shipping the order
			if (!in_array($child_order_info['opr_status'],(array)Settings::getSetting("CONF_DEFAULT_SHIPPING_ORDER_STATUS")) && in_array($opr_order_status_id,(array)Settings::getSetting("CONF_DEFAULT_SHIPPING_ORDER_STATUS")) && ($child_order_info["order_payment_status"]==1)) {
				
				$this->db->update_from_array('tbl_order_products', array('opr_shipped_date' => date('Y-m-d H:i:s')),
					array('smt' => 'opr_id = ? ', 'vals' => array((int)$child_order_id)),true);
			}
			
			
			// If current order status is not completed but new status is completed then commence completing the order
			if (!in_array($child_order_info['opr_status'],(array)$this->getVendorOrderPaymentCreditedStatuses()) && in_array($opr_order_status_id,(array)$this->getVendorOrderPaymentCreditedStatuses()) && (in_array($child_order_info["order_payment_status"],array(1,2))) && ($child_order_info['PA_TXN_STS']!=0 || is_null($order_info['PA_TXN_STS']))
			
			|| (in_array($opr_order_status_id,(array)$this->getVendorOrderPaymentCreditedStatuses()) && ($child_order_info["order_payment_status"]==1) && $release_payments)
			) {
						$formatted_invoice_number="#".$child_order_info["opr_order_invoice_number"];	
						if ($child_order_info["order_payment_status"]==1){	
							/******************* Start Order Payment to Vendor ***********************/	
							
							$comments = sprintf(Utilities::getLabel('M_Received_credits_for_order'),$formatted_invoice_number);
							$txn_amount= $child_order_info['opr_qty'] * ($child_order_info['opr_customer_buying_price']+$child_order_info['opr_customer_customization_price'])+$child_order_info['opr_shipping_charges']-$child_order_info['opr_refund_amount'];
							if ($txn_amount>0){
								$txnArray["utxn_user_id"]=$child_order_info['shop_user_id'];
								$txnArray["utxn_credit"]=$txn_amount;
								$txnArray["utxn_debit"]=0;
								$txnArray["utxn_status"]=1;
								$txnArray["utxn_order_id"]=$child_order_info['opr_id'];
								$txnArray["utxn_comments"]=$comments;
								$transObj=new Transactions();
									if($txn_id=$transObj->addTransaction($txnArray)){
										$emailNotificationObj=new Emailnotifications();
										$emailNotificationObj->sendTxnNotification($txn_id);
									}
								}
								/******************* End Order Payment to Vendor ***********************/	
							}
					
					if ($child_order_info['opr_cod_order']){
						/******************* Start Charge Tax to Vendor if COD order ***********************/
						$comments = sprintf(Utilities::getLabel('M_Charged_Tax_collected_for_order'),$formatted_invoice_number);
						$tax=$child_order_info['opr_tax']-$child_order_info['opr_refund_tax'];
						if ($tax>0){
							$txnArray["utxn_user_id"]=$child_order_info['shop_user_id'];
							$txnArray["utxn_debit"]=$tax;
							$txnArray["utxn_credit"]=0;
							$txnArray["utxn_status"]=1;
							$txnArray["utxn_order_id"]=$child_order_info['opr_id'];
							$txnArray["utxn_comments"]=$comments;
							$transObj=new Transactions();
							if($txn_id=$transObj->addTransaction($txnArray)){
								$emailNotificationObj=new Emailnotifications();
								$emailNotificationObj->sendTxnNotification($txn_id);
							}
						}
						/******************* End Charge Tax to Vendor if COD order ***********************/
					}
					
					/******************* Start Charge Commission/fees to Vendor ***********************/
					$comments = sprintf(Utilities::getLabel('M_Charged_Commission_for_order'),$formatted_invoice_number);
					$commission_fees=$child_order_info['opr_commission_charged']-$child_order_info['opr_refund_commission'];
					if ($commission_fees>0){
						$txnArray["utxn_user_id"]=$child_order_info['shop_user_id'];
						$txnArray["utxn_debit"]=$commission_fees;
						$txnArray["utxn_credit"]=0;
						$txnArray["utxn_status"]=1;
						$txnArray["utxn_order_id"]=$child_order_info['opr_id'];
						$txnArray["utxn_comments"]=$comments;
						$transObj=new Transactions();
						if($txn_id=$transObj->addTransaction($txnArray)){
							$emailNotificationObj=new Emailnotifications();
							$emailNotificationObj->sendTxnNotification($txn_id);
						}
					}
					/******************* End Charge Commission/fees to Vendor ***********************/
					
					
				
				/***** Start Add commission if sale is linked to affiliate referral. *****************/
				if ($child_order_info['opr_affiliate_commission']>0) {
					$afftxnOBj=new Affiliatetransactions();
					$txnArray=array(
									"atxn_affiliate_id"=>$child_order_info["order_affiliate_id"],
									"atxn_credit"=>$child_order_info["opr_affiliate_commission"],
									"atxn_debit"=>0,
									"atxn_status"=>1,
									"atxn_description"=>sprintf(Utilities::getLabel('L_AFFILIATE_COMMISSION_RECEIVED'),'<i>'.$child_order_info['opr_order_invoice_number'].'</i>'),
									);
					if($aftxn_id=$afftxnOBj->addAffiliateTransaction($txnArray)){
						$emailNotificationObj=new Emailnotifications();
						$emailNotificationObj->sendAffiliateTxnNotification($aftxn_id);
					}
				}
				/***** End Add commission if sale is linked to affiliate referral. *****************/
				$this->db->update_from_array('tbl_order_products', array('opr_completion_date' => date('Y-m-d H:i:s')),
					array('smt' => 'opr_id = ? ', 'vals' => array((int)$child_order_id)),true);
					
				if ($child_order_info["order_payment_status"]==1){
					$p->recordProductWeightage($child_order_info['opr_product_id'],'products#order_completed');
				}	
				
			}
			
			// If order status is in buyer order statuses then send update email
			if (in_array($opr_order_status_id, (array)Settings::getSetting("CONF_BUYER_ORDER_STATUS")) && $notify ) {
				$emailNotificationObj=new Emailnotifications();
				$emailNotificationObj->Order_Status_Update_Buyer($comment_id);
			}
			
		}
		return true;
	}
	
	
	function getOrderDetail($order_id){
		$order_details=$this->getOrderById($order_id);
		$order_details["addresses"]=$this->getOrderBillingShippingAddress($order_id);
	$str='<table cellspacing="0" cellpadding="0" border="0" width="100%" style="border:1px solid #ddd; border-collapse:collapse;">
	<tr>
	<td width="50%" style="padding:10px;background:#eee;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;">'.Utilities::getLabel('L_Product').'</td>
	<td width="10%" style="padding:10px;background:#eee;font-size:13px; border:1px solid #ddd;color:#333; font-weight:bold;">'.Utilities::getLabel('L_Qty').'</td>
	<td width="20%" style="padding:10px;background:#eee;font-size:13px; border:1px solid #ddd;color:#333; font-weight:bold;" align="right">'.Utilities::getLabel('L_Price').'</td>
	<td width="20%" style="padding:10px;background:#eee;font-size:13px; border:1px solid #ddd;color:#333; font-weight:bold;" align="right">'.Utilities::getLabel('L_Total').'</td>
	</tr>';
	$order_products=$this->getChildOrders(array("order"=>$order_id));
	 	foreach ($order_products as $key=>$val){
			$inc++;
			$opr_customer_buying_price=$val["opr_customer_buying_price"];
			$sku_codes=$val['opr_code']!=""?$val['opr_code']:$val['opr_sku'];
			$options=$val["opr_customization_string"];
			$customization_price=$val["opr_customer_customization_price"];
			//$adds=" (+".$this->currency->format($customization_price,$val["order_currency_code"],$val["order_currency_value"]).")";
			//$options=(($options=="")?"":"<strong>".Utilities::getLabel('L_COMBINATION_SELECTED')."</strong>$adds").$options;
			$opr_customer_buying_price+=$customization_price;
			$ind_price_total=$val["opr_qty"]*$opr_customer_buying_price;
			$str.='<tr>
				<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;">
				<a href="'.Utilities::generateAbsoluteUrl('products','view',array($val["opr_product_id"]),"/").'" style="font-size:13px; color:#333;">'.$val["opr_name"].'</a>'.$options.'
				</td>
			<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;">'.$val['opr_qty'].'</td>
				<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.$this->currency->format($val["opr_customer_buying_price"] + $val["opr_customer_customization_price"] ,$val["order_currency_code"],$val["order_currency_value"]).'</td><td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.$this->currency->format( $val['opr_qty']*($val["opr_customer_buying_price"] + $val["opr_customer_customization_price"]),$val["order_currency_code"],$val["order_currency_value"]).'</td>
			</tr>';
	  }
	  $str.='<tr><td colspan="3" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.Utilities::getLabel('L_CART_TOTAL').'</td><td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.$this->currency->format($order_details['order_cart_total'],$order_details['order_currency_code'],$order_details['order_currency_value']).'</td></tr>';
	  
	  if ($order_details['order_shipping_charged']>0){
	  $str.='<tr><td colspan="3" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.Utilities::getLabel('L_SHIPPING').'</td><td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.$this->currency->format($order_details['order_shipping_charged'],$order_details['order_currency_code'],$order_details['order_currency_value']).'</td></tr>';
	  }
	  
	   if ($order_details['order_tax_charged']>0){
	  $str.='<tr><td colspan="3" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.Utilities::getLabel('L_VAT').'</td><td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.$this->currency->format($order_details['order_tax_charged'],$order_details['order_currency_code'],$order_details['order_currency_value']).'</td></tr>';
	   }
	 
	  if ($order_details['order_discount_total']>0){
	  	$str.='<tr><td colspan="3" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.Utilities::getLabel('L_DISCOUNT').' ('.$order_details['order_discount_coupon'].')</td><td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.$this->currency->format($order_details['order_discount_total'],$order_details['order_currency_code'],$order_details['order_currency_value']).'</td></tr>';
	  }
	  
	  if ($order_details['order_reward_points']>0){
	  	$str.='<tr><td colspan="3" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.Utilities::getLabel('L_REWARD_POINTS').' ('.$order_details['order_reward_points'].')</td><td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right">'.$this->currency->format($order_details['order_reward_points'],$order_details['order_currency_code'],$order_details['order_currency_value']).'</td></tr>';
	  }
	  
	  $str.='<tr><td colspan="3" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right"><strong>'.Utilities::getLabel('L_ORDER_TOTAL').'</strong></td><td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" align="right"><strong>'.$this->currency->format($order_details['order_net_charged'],$order_details['order_currency_code'],$order_details['order_currency_value']).'</strong></td></tr>';
	  
	  
	 $shipping_address = $order_details['order_shipping_required']?$order_details["address"]["shipping"]:'-NA-'; 
     $str.='</table><br/><br/><table cellspacing="0" cellpadding="0" border="0" width="100%" style="border:1px solid #ddd; border-collapse:collapse;"><tr><td style="padding:10px;background:#eee;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;"  bgcolor="#f0f0f0"><strong>'.Utilities::getLabel('L_Order_Billing_Details').'</strong></td><td style="padding:10px;background:#eee;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;" bgcolor="#f0f0f0"><strong>'.Utilities::getLabel('L_Order_Shipping_Details').'</strong></td></tr><tr><td valign="top" style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;" >'.$order_details["addresses"]["billing"].'</td>';
	 $str.='<td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;">'.$shipping_address.'</td></tr></table>';
		return $str;
	 }
	 
	 function getOrderProductsById($id) {
		$id = intval($id);
        if($id>0!=true) return array();
       	$add_criteria['order'] = $id;
        $srch = self::search_order_products($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $rs = $srch->getResultSet();
		//return $this->db->fetch_all($rs);
		$arr_order_products=$this->db->fetch_all($rs);
		foreach($arr_order_products as $sn=>$val){
			$option_data = array();
			$options = $this->getOrderOptions($val['order_id'], $val['opr_id']);
			foreach ($options as $option) {
					if ($option['order_option_type'] != 'file') {
						$option_data[] = array(
							'name'  => $option['order_option_name'],
							'value' => $option['order_option_value'],
							'type'  => $option['order_option_type']
						);
					} else {
						if (!empty($option['order_option_value'])) {
							$option_data[] = array(
								'name'  => $option['order_option_name'],
								'value' => $option['order_option_value'],
								'type'  => $option['order_option_type'],
								'href'  => Utilities::generateAbsoluteUrl('file', 'download_attachment',array($option['order_option_value']),CONF_WEBROOT_URL),
							);
						}
					}
				
			}
			$val['order_options']=$option_data;
			$order_products[] = $val;
		}
		return $order_products;
	}
	
	function getOrderPayments($criteria=array(),$pagesize=0) {
		$db = &Syspage::getdb();
        if(count($criteria)==0) return array();
	    $srch = new SearchBase('tbl_order_payments', 'torp');
        foreach($criteria as $key=>$val) {
			if(strval($val)=='') continue;
            switch($key) {
            case 'id':
                $srch->addCondition('torp.op_payment_id', '=', intval($val));
                break;
			case 'order':
                $srch->addCondition('torp.op_order_id', '=', intval($val));
                break;
			
            }
        }
		$srch->doNotLimitRecords();
        $srch->doNotCalculateRecords(true);
		$srch->addOrder('op_payment_id');
        $rs = $srch->getResultSet();
		return $db->fetch_all($rs);
	}
	
	function refundOrderPaidAmount($order_id){
		$order = $this->getOrderById($order_id);
		$formatted_request_value="#".$order["order_invoice_number"];
		$comments=sprintf(Utilities::getLabel('L_Order_Number_Comments'),$formatted_request_value);
		$txn_amount = $order['totPayments'];
		if ($txn_amount>0 && $order['order_payment_status']==1){
					if (Settings::getSetting("CONF_PROCESS_ORDER_REFUND_CANCELLATION")=="A_C"){
							$txnArray["utxn_user_id"]=(int)$order['order_user_id'];
							$txnArray["utxn_credit"]=$txn_amount;
							$txnArray["utxn_status"]=1;
							$txnArray["utxn_order_id"]=(int)$order['order_id'];
							$txnArray["utxn_comments"]=$comments;
							$transObj=new Transactions();
							if($txn_id=$transObj->addTransaction($txnArray)){
								$emailNotificationObj=new Emailnotifications();
								$emailNotificationObj->sendTxnNotification($txn_id);
							}
					}elseif (Settings::getSetting("CONF_PROCESS_ORDER_REFUND_CANCELLATION")=="R_P"){
							$comments=sprintf(Utilities::getLabel('L_ORDER_CANCELLED_REWARDS_POINTS_ADDED'),$formatted_request_value);
							$rewardObj=new Rewards();
							$rewardArray["urp_user_id"]=(int)$order['order_user_id'];
							$rewardArray["urp_points"]=$txn_amount;
							$rewardArray["urp_description"]=$comments;
							if ($reward_point_id = $rewardObj->addRewardPoints($rewardArray)){
								$emailNotificationObj=new Emailnotifications();
								$emailNotificationObj->sendRewardPointsNotification($reward_point_id);
							}
					}
			return true;
			}else{
			return true;
		}
	}
	
	function refundOrderRewardPoints($order_id){
		$order = $this->getOrderById($order_id);
		$formatted_request_value="#".$order["order_invoice_number"];
		$reward_points = $order['order_reward_points'];
		if ($reward_points>0){
				$comments=sprintf(Utilities::getLabel('L_ORDER_CANCELLED_REWARDS_POINTS_REVERSED'),$formatted_request_value);
				$rewardObj=new Rewards();
				$rewardArray["urp_user_id"]=(int)$order['order_user_id'];
				$rewardArray["urp_points"]=$reward_points;
				$rewardArray["urp_description"]=$comments;
				if ($reward_point_id = $rewardObj->addRewardPoints($rewardArray)){
					$emailNotificationObj=new Emailnotifications();
					$emailNotificationObj->sendRewardPointsNotification($reward_point_id);
				}
		}else{
			return true;
		}
	}
	 
	
	function getOrderPaymentFinancials($order_id){
		$order_info = $this->getOrderById($order_id);
		$user=new User();
		$user_balance=$user->getUserBalance($order_info["order_user_id"]);
		$order_credits_charge=$order_info["order_wallet_selected"]?min($order_info["order_net_charged"],$user_balance):0;
		$order_payment_gateway_charge=$order_info["order_net_charged"]-$order_credits_charge;
		$order_payment_summary=array("net_payable"=>$order_info["order_net_charged"],
									 "order_user_balance"=>$user_balance,
									 "order_credits_charge"=>$order_credits_charge,
									 "order_reward_points_charge"=>$order_info['order_reward_points_charge'],
									 "order_payment_gateway_charge"=>$order_payment_gateway_charge,
									 );
		return $order_payment_summary;							 
		
	}
	
	
	function getAssociativeArray($not_to_include=null){
		$srch = new SearchBase('tbl_orders', 'tord');
		if (!empty($not_to_include)) 
			$srch->addCondition('order_id', 'NOT IN',$not_to_include);
		$srch->addMultipleFields(array('order_id', 'order_invoice_number'));
		$srch->addOrder('order_id', 'DESC');
		$rs = $srch->getResultSet();
		return $this->db->fetch_all_assoc($rs);
	}
	
	function getBuyerAllowedOrderCancellationStatuses(){
		$buyer_allow_cancel_statuses=(array)Settings::getSetting("CONF_ALLOW_CANCELLATION_ORDER_STATUS");
		$buyer_allow_cancel_statuses=array_diff($buyer_allow_cancel_statuses,(array)Settings::getSetting("CONF_DEFAULT_ORDER_STATUS"));
		$buyer_allow_cancel_statuses=array_diff($buyer_allow_cancel_statuses,(array)Settings::getSetting("CONF_DEFAULT_CANCEL_ORDER_STATUS"));
		$buyer_allow_cancel_statuses=array_diff($buyer_allow_cancel_statuses,(array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS"));
		return $buyer_allow_cancel_statuses;
	}
	
	function getBuyerAllowedOrderReturnStatuses(){
		$buyer_allow_return_statuses=(array)Settings::getSetting("CONF_RETURN_EXCHANGE_READY_ORDER_STATUS");
		$buyer_allow_return_statuses=array_diff($buyer_allow_return_statuses,(array)Settings::getSetting("CONF_DEFAULT_ORDER_STATUS"));
		$buyer_allow_return_statuses=array_diff($buyer_allow_return_statuses,(array)Settings::getSetting("CONF_DEFAULT_CANCEL_ORDER_STATUS"));
		$buyer_allow_return_statuses=array_diff($buyer_allow_return_statuses,(array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS"));
		return $buyer_allow_return_statuses;
	}
	
	function getAffiliatePendingOrderStatuses(){
		$processing_statuses=array_merge((array)Settings::getSetting("CONF_PROCESSING_ORDER_STATUS"),(array)Settings::getSetting("CONF_DEFAULT_PAID_ORDER_STATUS"));
		$processing_statuses=array_diff($processing_statuses,(array)Settings::getSetting("CONF_DEFAULT_ORDER_STATUS"));
		$processing_statuses=array_diff($processing_statuses,(array)Settings::getSetting("CONF_DEFAULT_CANCEL_ORDER_STATUS"));
		$processing_statuses=array_diff($processing_statuses,(array)Settings::getSetting("CONF_RETURN_REQUEST_ORDER_STATUS"));
		$processing_statuses=array_diff($processing_statuses,(array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS"));
		$processing_statuses=array_diff($processing_statuses,(array)Settings::getSetting("CONF_RETURN_REQUEST_APPROVED_ORDER_STATUS"));
		return $processing_statuses;
	}
	
	function getVendorAllowedUpdateOrderStatuses(){
		$processing_statuses=array_merge((array)Settings::getSetting("CONF_VENDOR_ORDER_STATUS"),(array)Settings::getSetting("CONF_DEFAULT_PAID_ORDER_STATUS"));
		$processing_statuses=array_diff($processing_statuses,(array)Settings::getSetting("CONF_DEFAULT_ORDER_STATUS"));
		$processing_statuses=array_diff($processing_statuses,(array)Settings::getSetting("CONF_DEFAULT_COD_ORDER_STATUS"));
		$processing_statuses=array_diff($processing_statuses,(array)Settings::getSetting("CONF_DEFAULT_CANCEL_ORDER_STATUS"));
		$processing_statuses=array_diff($processing_statuses,(array)Settings::getSetting("CONF_RETURN_REQUEST_ORDER_STATUS"));
		$processing_statuses=array_diff($processing_statuses,(array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS"));
		$processing_statuses=array_diff($processing_statuses,(array)Settings::getSetting("CONF_RETURN_REQUEST_APPROVED_ORDER_STATUS"));
		return $processing_statuses;
	}
	
	function getAdminAllowedUpdateOrderStatuses(){
		$processing_statuses=array_merge((array)Settings::getSetting("CONF_PROCESSING_ORDER_STATUS"),(array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS"));
		$processing_statuses=array_merge((array)$processing_statuses,(array)Settings::getSetting("CONF_DEFAULT_PAID_ORDER_STATUS"));
		$processing_statuses=array_diff($processing_statuses,(array)Settings::getSetting("CONF_DEFAULT_COD_ORDER_STATUS"));
		$processing_statuses=array_diff($processing_statuses,(array)Settings::getSetting("CONF_DEFAULT_ORDER_STATUS"));
		$processing_statuses=array_diff($processing_statuses,(array)Settings::getSetting("CONF_DEFAULT_CANCEL_ORDER_STATUS"));
		$processing_statuses=array_diff($processing_statuses,(array)Settings::getSetting("CONF_RETURN_REQUEST_ORDER_STATUS"));
		$processing_statuses=array_diff($processing_statuses,(array)Settings::getSetting("CONF_RETURN_REQUEST_APPROVED_ORDER_STATUS"));
		return $processing_statuses;
	}
	
	function getNotAllowedOrderCancellationStatuses(){
		$cancellation_statuses=array_merge((array)Settings::getSetting("CONF_DEFAULT_ORDER_STATUS"),(array)Settings::getSetting("CONF_DEFAULT_CANCEL_ORDER_STATUS"),(array)Settings::getSetting("CONF_RETURN_REQUEST_ORDER_STATUS"),(array)Settings::getSetting("CONF_RETURN_REQUEST_WITHDRAWN_ORDER_STATUS"),(array)Settings::getSetting("CONF_RETURN_REQUEST_APPROVED_ORDER_STATUS"),(array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS"));
		return $cancellation_statuses;
	}
	
	function getVendorOrderPaymentCreditedStatuses(){
		$vendor_payment_statuses=(array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS");
		$vendor_payment_statuses=array_diff($vendor_payment_statuses,(array)Settings::getSetting("CONF_DEFAULT_ORDER_STATUS"));
		$vendor_payment_statuses=array_diff($vendor_payment_statuses,(array)Settings::getSetting("CONF_DEFAULT_CANCEL_ORDER_STATUS"));
		$vendor_payment_statuses=array_diff($vendor_payment_statuses,(array)Settings::getSetting("CONF_RETURN_REQUEST_ORDER_STATUS"));
		//$vendor_payment_statuses=array_diff($vendor_payment_statuses,(array)Settings::getSetting("CONF_RETURN_REQUEST_APPROVED_ORDER_STATUS"));
		return $vendor_payment_statuses;
	}
	
	function getOrderOptions($order_id, $child_order_id){
		$srch = new SearchBase('tbl_order_product_options', 'tord');
		$srch->addCondition('order_option_order_id', '=',$order_id);
		$srch->addCondition('order_option_sub_order_id', '=',$child_order_id);
		$srch->addOrder('order_option_id', 'ASC');
		$rs = $srch->getResultSet();
		return $this->db->fetch_all($rs);
	}
	
	
	function getDistinctOrderCustomers($name) {
        $srch = new SearchBase('tbl_orders', 'tord');
		$srch->joinTable('tbl_users', 'LEFT OUTER JOIN', 'tord.order_user_id = tu.user_id', 'tu');
		$srch->addMultipleFields(array("tu.user_id","CONCAT(tu.user_name,' (',tu.user_username,')') as name"));
		$cndCondition=$srch->addCondition('tu.user_name', 'like', '%' . $name . '%');
		$cndCondition->attachCondition('tu.user_username', 'like', '%' . $name . '%','OR');
		$srch->setPageSize(10);
		$srch->addGroupBY('order_user_id');
        $rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		if($this->total_records < 1){
			return false;
		}
        return $this->db->fetch_all($rs);
		
	}
	
	function getDistinctOrderShops($name) {
        $srch = new SearchBase('tbl_order_products', 'tp');
		$srch->joinTable('tbl_shops', 'LEFT OUTER JOIN', 'tp.opr_product_shop = ts.shop_id', 'ts');
		$srch->addMultipleFields(array("ts.shop_id","ts.shop_name as name"));
		$srch->addCondition('ts.shop_name', 'like', '%' . $name . '%');
		$srch->setPageSize(10);
		$srch->addGroupBY('shop_id');
        $rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		if($this->total_records < 1){
			return false;
		}
        return $this->db->fetch_all($rs);
		
	}
	
	
	
	
}