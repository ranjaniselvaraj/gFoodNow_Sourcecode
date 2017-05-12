<?php
class Emailnotifications extends Model {
	
	function __construct(){
		$this->db = Syspage::getdb();
    }
	function getError() {
		return $this->error;
	}
	
	function sendVerificationEmail($user_id,$email) {
		$userObj=new User();
		$user_details=$userObj->getUser(array(
												'user_id'=>$user_id, 
												'get_flds'=>array(
																'user_id', 
																'user_username',
																'user_name',
																'user_email',
																)));
		if ($user_details){
			$verification_code = Utilities::getRandomPassword(15);
			$this->db->deleteRecords('tbl_user_email_verification_codes', array('smt' => 'uevc_user_id = ?', 'vals' => array($user_id)));
			
			if (!$this->db->insert_from_array('tbl_user_email_verification_codes', array('uevc_user_id' => $user_id, 'uevc_code' => $verification_code, 'uevc_email' => $email))) {
				$this->error = $this->db->getError();
				return false;
			}
			$verification_url = Utilities::generateAbsoluteUrl('user', 'confirm_email', array($user_id . '.' . $verification_code),CONF_WEBROOT_URL);
			$website_url = Utilities::getUrlScheme();
			$arr_replacements = array(
				'{site_domain}' => CONF_SERVER_PATH,
				'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
				'{website_url}' => $website_url,
				'{verification_url}' => $verification_url,
				'{user_full_name}' => htmlentities($user_details['user_name']),
			);
			if ($email==""){
				$tpl_name = 'signup_verification';
				$email=$user_details["user_email"];
			}
			else	
				$tpl_name = 'email_verification';
				
			Utilities::sendMailTpl($email, $tpl_name, $arr_replacements);
	        return true;
		}else{
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
		}
    }
	
	function sendNotifyAdminRegistration($user_id) {
		$userObj=new User();
		$user_details=$userObj->getUser(array('user_id'=>$user_id, 'get_flds'=>array('user_id','user_username','user_name','user_email')));
		if ($user_details){
			$arr_replacements = array(
				'{site_domain}' => CONF_SERVER_PATH,
				'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
				'{username}' => $user_details['user_username'],
				'{email}' => $user_details['user_email'],
				'{name}' => htmlentities($user_details['user_name']),
			);
			Utilities::sendMailTpl(Settings::getSetting("CONF_ADMIN_EMAIL"), "new_registration_admin", $arr_replacements);
			$emails = explode(',', Settings::getSetting("CONF_ADDITIONAL_ALERT_EMAILS"));
			foreach ($emails as $email) {
				if (Utilities::utf8_strlen($email) > 0 && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
					Utilities::sendMailTpl($email, "new_registration_admin", $arr_replacements);
				}
			}
			
	        return true;
		}else{
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
		}
    }
	
	function sendWelcomeRegistrationMail($user_id) {
		$userObj=new User();
		$user_details=$userObj->getUser(array('user_id'=>$user_id, 'get_flds'=>array('user_id','user_username','user_name','user_email')));
		if ($user_details){
			$arr_replacements = array(
				'{site_domain}' => CONF_SERVER_PATH,
				'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
				'{username}' => $user_details['user_username'],
				'{email}' => $user_details['user_email'],
				'{name}' => htmlentities($user_details['user_name']),
				'{contact_us_email}' =>Settings::getSetting("CONF_CONTACT_EMAIL"),
			);
			Utilities::sendMailTpl($user_details['user_email'], "welcome_registration", $arr_replacements);
	        return true;
		}else{
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
		}
    }
	
	function BuyerOrderNotification($comment_id){
		$orderObj=new Orders();
		$order_comment=$orderObj->getOrderComments(array("id"=>$comment_id),1);
		if ($order_comment && $order_comment["customer_notified"]){
			$shipment_information="";
			if ($order_comment["append_comments"]==1){
				$msg_comments=Utilities::getLabel('M_Comments_for_your_order').":<em>".$order_comment['comments'].".</em><br/><br/>";
			}
			if ($order_comment["opr_status"]==Settings::getSetting("CONF_DEFAULT_SHIPPING_ORDER_STATUS")){
				$shipping_method = !empty($order_comment["scompany_name"])?$order_comment["scompany_name"]:$order_comment["opr_shipping_label"];
				$shipment_information=Utilities::getLabel('M_Shipment_Information').": ".Utilities::getLabel('M_Tracking_Number')." ".$order_comment['tracking_number']."</a> VIA ".$shipping_method."<br/>";
			}
			
			$order_items_table_format=$orderObj->getSubOrderDetail($order_comment["opr_id"]);	
			$arr_replacements=array(
					'{site_domain}' => CONF_SERVER_PATH,
					'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
					'{user_full_name}' => htmlentities(trim($data["order_user_name"])),
					'{new_order_status}' => $data["orders_status_name"],
					'{invoice_number}' => $data["opr_order_invoice_number"],
					'{order_items_table_format}' => $order_items_table_format,
					'{order_admin_comments}' => $msg_comments,
					'{shipment_information}' => "<br/><br/>".$shipment_information,
				);
			Utilities::sendMailTpl($order_comment["order_user_email"], "child_order_status_change", $arr_replacements);	
			return true;
		}else{
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
		}
			
	}
	
	function SendProductFeedbackNotification($review){
		$productFeedbackObj=new Productfeedbacks();
		$productfeedback=$productFeedbackObj->getProductFeedback($review);
		if ($productfeedback){
			$url =  Utilities::generateAbsoluteUrl('products', 'view',array($productfeedback["review_prod_id"]),CONF_WEBROOT_URL);
			$prod_title_anchor="<a href='".$url."'>".$productfeedback["prod_name"]."</a>";
			$arr_replacements = array(
				'{site_domain}' => CONF_SERVER_PATH,
				'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
				'{user_name}' => htmlentities($productfeedback['opr_shop_owner_name']),
				'{username}' => $productfeedback['user_username'],
				'{review_rating}' => $productfeedback['review_rating'],
				'{review_text}' => nl2br($productfeedback['review_text']),
				'{prod_title}' => $prod_title_anchor,
			);
			Utilities::sendMailTpl($productfeedback["opr_shop_owner_email"], "product_review", $arr_replacements);
			$arr_replacements["{user_name}"]="Admin";
			Utilities::sendMailTpl(Settings::getSetting("CONF_ADMIN_EMAIL"), "product_review", $arr_replacements);
			$emails = explode(',', Settings::getSetting("CONF_ADDITIONAL_ALERT_EMAILS"));
			foreach ($emails as $email) {
				if (Utilities::utf8_strlen($email) > 0 && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
					Utilities::sendMailTpl($email, "product_review", $arr_replacements);
				}
			}
			return true;
		}else{
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
		}
			
	}
	
	function SendMessageNotification($message_id){
		$userObj=new User();
		$message=$userObj->getMessages(array("id"=>$message_id),1);
		if ($message){
			$url = Utilities::generateAbsoluteUrl('account', 'messages',array(),CONF_WEBROOT_URL);
			$url='<a href="'.$url.'">'.Utilities::getLabel('M_click_here').'</a>';
			$arr_replacements = array(
			'{site_domain}' => CONF_SERVER_PATH,
			'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
			'{user_full_name}' => htmlentities($message['message_sent_to_name']),
			'{username}' => htmlentities($message['message_sent_by_username']),
			'{message_subject}' => $message['thread_subject']!=""?htmlentities($message['thread_subject']):"-NA-",
			'{message}' => nl2br($message['message_text']),
			'{click_here}' => $url,
			);
			Utilities::sendMailTpl($message["message_sent_to_email"], "send_message", $arr_replacements);
			return true;
		}else{
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
		}
	}
	
	function SendWithdrawRequestNotification($request_id,$admin_or_user="A"){
			global $status_arr;
			$withdrawalRequestObj=new WithdrawalRequests();
			$withdrawal_request=$withdrawalRequestObj->getWithdrawRequestData($request_id);
			if ($withdrawal_request){
				$formatted_request_value="#".str_pad($request_id,6,'0',STR_PAD_LEFT);
				$url = Utilities::generateAbsoluteUrl('account', 'messages',array(),CONF_WEBROOT_URL);
				$url='<a href="'.$url.'">'.Utilities::getLabel('M_click_here').'</a>';
				$request_reason_cancellation=empty($withdrawal_request['withdrawal_cancel_comments'])?"":"<br/><br/><b>".Utilities::getLabel('L_Reason_for_cancellation').":</b> ".nl2br($withdrawal_request['withdrawal_cancel_comments']);
				$arr_replacements = array(
				'{site_domain}' => CONF_SERVER_PATH,
				'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
				'{request_id}' => $formatted_request_value,
				'{username}' => $withdrawal_request['user_username'],
				'{request_amount}' => Utilities::displayMoneyFormat($withdrawal_request["withdrawal_amount"]),
				'{request_bank}' => $withdrawal_request['withdrawal_bank'],
				'{request_account_holder}' => $withdrawal_request['withdrawal_account_holder_name'],
				'{request_account_number}' => $withdrawal_request['withdrawal_account_number'],
				'{request_ifsc_swift_number}' => $withdrawal_request['withdrawal_ifc_swift_code'],
				'{request_bank_address}' => $withdrawal_request['withdrawal_bank_address'],
				'{request_comments}' => $withdrawal_request['withdrawal_comments'],
				'{request_status}' => $status_arr[$withdrawal_request['withdrawal_status']],
				'{request_reason_cancellation}' => $request_reason_cancellation,
				'{user_name}' => $withdrawal_request['user_name'],
				);
				if ($admin_or_user=="A"){
					Utilities::sendMailTpl(Settings::getSetting("CONF_ADMIN_EMAIL"), "withdrawal_request_admin", $arr_replacements);
					$emails = explode(',', Settings::getSetting("CONF_ADDITIONAL_ALERT_EMAILS"));
					foreach ($emails as $email) {
						if (Utilities::utf8_strlen($email) > 0 && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
							Utilities::sendMailTpl($email, "withdrawal_request_admin", $arr_replacements);
						}
					}
			
				}else{
					Utilities::sendMailTpl($withdrawal_request["user_email"], "withdrawal_request_approved_declined", $arr_replacements);	
				}
				return true;
			}else{
				$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			}
	}
	
	function SendShopReportNotification($shop_report_id){
		$shopObj=new Shops();
		$shopReportReasonsObj=new Reportreasons();
		$shop_report=$shopObj->getShopReport($shop_report_id);
		$arr_report_reasons=$shopReportReasonsObj->getAssociativeArray();
		if ($shop_report){
				$arr_replacements = array(
										'{username}' => $shop_report['user_username'],
										'{shop_name}' => $shop_report['shop_name'],
										'{report_reason}' => $arr_report_reasons[$shop_report['sreport_reason']],
										'{report_message}' => nl2br($shop_report['sreport_message']),
										'{site_domain}' => CONF_SERVER_PATH,
										'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
									);
				$rs = Utilities::sendMailTpl(Settings::getSetting("CONF_ADMIN_EMAIL"), 'report_shop',$arr_replacements);
				
				$emails = explode(',', Settings::getSetting("CONF_ADDITIONAL_ALERT_EMAILS"));
				foreach ($emails as $email) {
					if (Utilities::utf8_strlen($email) > 0 && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
						Utilities::sendMailTpl($email, "report_shop", $arr_replacements);
					}
				}
																						   
				return true;
			}else{
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
		}
			
	}
	
	function New_Order_Buyer_Admin($order_id){
		$orderObj=new Orders();
		$order_detail = $orderObj->getOrderById($order_id);
		
		if ($order_detail) {
			$order_discount_coupon=$order_detail['order_discount_coupon']!=""?$order_detail['order_discount_coupon']:"-NA-";
			$order_products_table_format=$orderObj->getOrderDetail($order_detail['order_id']);
			$arr_replacements = array(
						'{site_domain}' => CONF_SERVER_PATH,
			            '{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
						'{user_full_name}' => htmlentities(trim($order_detail['order_user_name'])),
						'{order_invoice_number}' => $order_detail['order_invoice_number'],
						'{reference_number}' => $order_detail['order_reference'],
						'{company_name}' => htmlentities($order_detail['order_company_name']),
						'{order_date}' => Utilities::formatDate($order_detail["order_date_added"]),
						'{shipping_method}' => htmlentities($order_detail['order_shipping_method']),
						'{discount_coupon}' => Utilities::displayNotApplicable($order_detail['order_discount_coupon']),
						'{coupon_discount}' => Utilities::displayMoneyFormat($order_detail['order_discount_total']),
						'{payment_method}' => $order_detail['order_payment_method'],
						'{order_cart_total}' => Utilities::displayMoneyFormat($order_detail['order_cart_total']),
						'{shipping}' => Utilities::displayMoneyFormat($order_detail['order_shipping_charged']),
						'{value_discount}' => Utilities::displayMoneyFormat($order_detail['order_value_discount']),
						'{payment_fees}' => Utilities::displayMoneyFormat($order_detail['order_payment_gateway_charges']),
						'{discount}' => Utilities::displayMoneyFormat($order_detail['order_discount_total']),
						'{sub_order_total}' => Utilities::displayMoneyFormat($order_detail['order_sub_total']),
						'{tax_vat}' => Utilities::displayMoneyFormat($order_detail['order_tax_charged']),
						'{total_paid}' => Utilities::displayMoneyFormat($order_detail['order_net_charged']),
						'{order_credits_used}' => Utilities::displayMoneyFormat($order_detail['order_credits_charged']),
						'{order_payment_made}' => Utilities::displayMoneyFormat($order_detail['order_actual_paid']),
						'{discount_code}' => $order_discount_coupon,
						'{order_products_table_format}' => $order_products_table_format,
			        );
					if (Settings::getSetting("CONF_NEW_ORDER_EMAIL")){
						Utilities::sendMailTpl(Settings::getSetting("CONF_ADMIN_EMAIL"), "admin_order_email", $arr_replacements);
						$emails = explode(',', Settings::getSetting("CONF_ADDITIONAL_ALERT_EMAILS"));
						foreach ($emails as $email) {
							if (Utilities::utf8_strlen($email) > 0 && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
								Utilities::sendMailTpl($email, "admin_order_email", $arr_replacements);
							}
						}
				
					}
					Utilities::sendMailTpl($order_detail["order_user_email"], "customer_order_email", $arr_replacements);
		}
		return true;
	}
	
	function Order_Payment_Update_Buyer_Admin($order_id){
		global $payment_status_arr;
		$orderObj=new Orders();
		$order_detail = $orderObj->getOrderById($order_id);
		if ($order_detail) {
			$order_discount_coupon=$order_detail['order_discount_coupon']!=""?$order_detail['order_discount_coupon']:"-NA-";
			$arr_replacements = array(
						'{site_domain}' => CONF_SERVER_PATH,
			            '{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
						'{user_full_name}' => htmlentities(trim($order_detail['order_user_name'])),
						'{invoice_number}' => $order_detail['order_invoice_number'],
						'{new_order_status}' => $payment_status_arr[$order_detail['order_payment_status']],
			        );
			
					Utilities::sendMailTpl(Settings::getSetting("CONF_ADMIN_EMAIL"), "primary_order_payment_status_change_admin", $arr_replacements);
					
					$emails = explode(',', Settings::getSetting("CONF_ADDITIONAL_ALERT_EMAILS"));
					foreach ($emails as $email) {
						if (Utilities::utf8_strlen($email) > 0 && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
							Utilities::sendMailTpl($email, "primary_order_payment_status_change_admin", $arr_replacements);
						}
					}
					
					Utilities::sendMailTpl($order_detail["order_user_email"], "primary_order_payment_status_change_buyer", $arr_replacements);
		}
		return true;
	}
	
	function New_Order_Vendor($order_id){
		$orderObj=new Orders();
		$order_detail = $orderObj->getOrderById($order_id);
		if ($order_detail) {
					$order_vendors=$orderObj->getChildOrders(array("order"=>$order_id));
					foreach($order_vendors as $key=>$val):
						$order_items_table_format=$orderObj->getSubOrderDetail($val["opr_id"]);	
						$website_url = Utilities::getUrlScheme();
						$arr_replacements = array(
						'{website_url}' => $website_url,
			            '{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
						'{vendor_name}' => htmlentities($val['opr_shop_owner_name']),
						'{order_items_table_format}' => $order_items_table_format,
						'{order_shipping_information}' => '',
			        );
					Utilities::sendMailTpl($val["opr_shop_owner_email"], "vendor_order_email", $arr_replacements);
				endforeach;	
		}
		return true;
	}
	
	function Order_Status_Update_Buyer($comment_id){
		$orderObj=new Orders();
		$order_comment=$orderObj->getOrderComments(array("id"=>$comment_id),1);
		if ($order_comment && $order_comment["customer_notified"]){
			if ($order_comment['comments']!=""){
				$msg_comments=Utilities::getLabel('M_Comments_for_your_order').":<br/><br/><em>".$order_comment['comments'].".</em><br/><br/>";
			}
			
			if ($order_comment['tracking_number']!=""){
				$shipping_method = !empty($order_comment["scompany_name"])?$order_comment["scompany_name"]:$order_comment["opr_shipping_label"];
				$shipment_information=Utilities::getLabel('M_Shipment_Information').": ".Utilities::getLabel('M_Tracking_Number')." ".$order_comment['tracking_number']." VIA ".$shipping_method."<br/>";
			}
			
			$order_items_table_format=$orderObj->getSubOrderDetail($order_comment["opr_id"]);	
			$arr_replacements=array(
					'{site_domain}' => CONF_SERVER_PATH,
					'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
					'{user_full_name}' => htmlentities($order_comment["order_user_name"]),
					'{new_order_status}' => $order_comment["orders_status_name"],
					'{invoice_number}' => $order_comment["opr_order_invoice_number"],
					'{order_items_table_format}' => $order_items_table_format,
					'{order_admin_comments}' => nl2br($msg_comments),
					'{shipment_information}' => "<br/><br/>".$shipment_information,
				);
			Utilities::sendMailTpl($order_comment["order_user_email"], "child_order_status_change", $arr_replacements);	
			return true;
		}else{
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
		}
	}
	
	function sendTxnNotification($txn_id){
		$txn=new Transactions($txn_id);
		$txn_detail=$txn->getTransactionById($txn_id);
		$txn_amount=$txn_detail["utxn_credit"]>0?$txn_detail["utxn_credit"]:$txn_detail["utxn_debit"];
		$arr_replacements=array(
			'{site_domain}' => CONF_SERVER_PATH,
			'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
			'{user_name}' => htmlentities($txn_detail["user_name"]),
			'{txn_id}' => $txn->format_transaction_number($txn_id),
			'{txn_type}' => $txn_detail["utxn_credit"]>0?Utilities::getLabel('L_credited'):Utilities::getLabel('L_debited'),
			'{txn_amount}' => Utilities::displayMoneyFormat($txn_amount),
			'{txn_comments}' => $txn->format_transaction_comments($txn_detail["utxn_comments"]),
		);
		Utilities::sendMailTpl($txn_detail["user_email"], "account_credited_debited", $arr_replacements);
		if ($txn_detail['utxn_debit']>0) {
			$user_id = $txn_detail['utxn_user_id'];
			$prmObj=new Promotions();
			$prmObj->getPromotions(array("user"=>$user_id,"status"=>1));
			$total_records= $prmObj->getTotalRecords();
			$userObj=new User();
			$balance=$userObj->getUserBalance($user_id);
			if ($balance <= Settings::getSetting("CONF_WALLET_BALANCE_ALERT") ){
				$url = Utilities::generateAbsoluteUrl('account', 'credits',array(),CONF_WEBROOT_URL);
				$url='<a href="'.$url.'">'.Utilities::getLabel('M_click_here').'</a>';
				$arr_replacements = array_merge($arr_replacements,array(
						'{current_wallet_balance}'=>Utilities::displayMoneyFormat($balance),
						'{click_here}'=>$url,
					));
				Utilities::sendMailTpl($txn_detail["user_email"], "wallet_balance_notification_vendor", $arr_replacements);
			}
		}
		
	}
	
	function sendRewardPointsNotification($record_id){
		$rewardObj=new Rewards();
		$reward_detail=$rewardObj->getRewardPointRecordById($record_id);
		$reward_points=$reward_detail["urp_points"];
		$arr_replacements=array(
			'{site_domain}' => CONF_SERVER_PATH,
			'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
			'{user_name}' => htmlentities($reward_detail["user_name"]),
			'{type}' => $reward_points>0?Utilities::getLabel('L_credited'):Utilities::getLabel('L_debited'),
			'{reward_points}' => $reward_points,
			'{comments}' => $reward_detail["urp_description"],
		);
		Utilities::sendMailTpl($reward_detail["user_email"], "reward_points_credited_debited", $arr_replacements);
		
	}
	
	function sendProductStockAlert($product_id){
		$p=new Products();
		$product_info=$p->getData($product_id);
		$url = Utilities::generateAbsoluteUrl('account', 'product_form',array($product_info["prod_id"]),CONF_WEBROOT_URL);
		$product_anchor="<a href='".$url."'>".Utilities::getLabel('M_click_here')."</a>";
		$website_url = Utilities::getUrlScheme();
		$arr_replacements = array(
			'{website_url}' => $website_url,
			'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
			'{user_name}' => htmlentities($product_info['user_name']),
			'{prod_title}' => htmlentities($product_info["prod_name"]),
			'{click_here}' => $product_anchor,
		);
		Utilities::sendMailTpl($product_info["user_email"],"threshold_notification_vendor", $arr_replacements);
	}
	
	function SendReturnNotification($return_request_message){
			$p=new Products();
			$return_request=$p->getReturnRequestMessage($return_request_message);
			if ($return_request){
				$url = Utilities::generateAbsoluteUrl('products', 'view',array($return_request["refund_prod_id"]),CONF_WEBROOT_URL);
				$prod_title_anchor="<a href='".$url."'>".$return_request["prod_name"]."</a>";
				$arr_replacements = array(
					'{site_domain}' => CONF_SERVER_PATH,
					'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
					'{user_name}' => htmlentities($return_request['opr_shop_owner_name']),
					'{username}' => $return_request['user_username'],
					'{return_prod_title}' => $prod_title_anchor,
					'{return_qty}' => $return_request['refund_qty'],
					'{return_request_type}' => $return_request['refund_or_replace']=="RP"?Utilities::getLabel('M_Replace'):Utilities::getLabel('M_Refund'),
					'{return_reason}' => $return_request['returnreason_title'],
					'{return_comments}' => nl2br($return_request['refmsg_text']),
				);
			 	Utilities::sendMailTpl($return_request["opr_shop_owner_email"], "product_return", $arr_replacements);
				$arr_replacements["{user_name}"]="Admin";
				Utilities::sendMailTpl(Settings::getSetting("CONF_ADMIN_EMAIL"), "product_return", $arr_replacements);
				
				$emails = explode(',', Settings::getSetting("CONF_ADDITIONAL_ALERT_EMAILS"));
				foreach ($emails as $email) {
					if (Utilities::utf8_strlen($email) > 0 && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
						Utilities::sendMailTpl($email, "product_return", $arr_replacements);
					}
				}
				
				return true;
			}else{
				$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			}
			
	}
	
	
	function SendOrderCancellationNotification($cancellation_requeest){
			$crObj=new CancelRequests();
			$cancellation_request=$crObj->getCancelRequest($cancellation_requeest);
			if ($cancellation_request){
			$url = Utilities::generateAbsoluteUrl('products', 'view',array($return_request["refund_prod_id"]),CONF_WEBROOT_URL);
			$prod_title_anchor="<a href='".$url."'>".$return_request["prod_name"]."</a>";
			$arr_replacements = array(
				'{site_domain}' => CONF_SERVER_PATH,
				'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
				'{user_name}' => htmlentities($cancellation_request['opr_shop_owner_name']),
				'{invoice_number}' => $cancellation_request["opr_order_invoice_number"],
				'{cancel_reason}' => $cancellation_request['cancelreason_title'],
				'{cancel_comments}' => nl2br($cancellation_request['cancellation_requst_message']),
				
			);
		 	Utilities::sendMailTpl($cancellation_request["opr_shop_owner_email"], "order_cancellation_notification", $arr_replacements);
			$arr_replacements["{user_name}"]="Admin";
			Utilities::sendMailTpl(Settings::getSetting("CONF_ADMIN_EMAIL"), "order_cancellation_notification", $arr_replacements);
			$emails = explode(',', Settings::getSetting("CONF_ADDITIONAL_ALERT_EMAILS"));
			foreach ($emails as $email) {
				if (Utilities::utf8_strlen($email) > 0 && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
					Utilities::sendMailTpl($email, "order_cancellation_notification", $arr_replacements);
				}
			}
			return true;
			}else{
				$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			}
			
	}
	
	function SendCancellationRequestUpdateNotification($cancel_request_id){
			global $status_arr;
			$crObj=new CancelRequests();
			$cancellation_request=$crObj->getCancelRequest($cancel_request_id);
			if ($cancellation_request){
				$formatted_cancellation_request_value="#".str_pad($cancellation_request,6,'0',STR_PAD_LEFT);
				$url = Utilities::generateAbsoluteUrl('account', 'messages',array(),CONF_WEBROOT_URL);
				$url='<a href="'.$url.'">'.Utilities::getLabel('M_click_here').'</a>';
				$arr_replacements = array(
				'{site_domain}' => CONF_SERVER_PATH,
				'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
				'{invoice_number}' => $cancellation_request["opr_order_invoice_number"],
				'{request_status}' => $status_arr[$cancellation_request['cancellation_request_status']],
				'{user_name}' => htmlentities($cancellation_request['user_name']),
				);
				Utilities::sendMailTpl($cancellation_request["user_email"], "cancellation_request_approved_declined", $arr_replacements);	
				return true;
			}else{
				$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			}
	}
	
	function SendReturnRequestStatusChangeNotification($return_request){
			global $return_status_arr;
			$p=new Products();
			$return_request=$p->getReturnRequest($return_request);
			$last_updated_by=$return_request["last_updated_by"]!=""?$return_request["last_updated_by"]:Settings::getSetting("CONF_WEBSITE_NAME");
			if ($return_request){
				$arr_replacements = array(
					'{site_domain}' => CONF_SERVER_PATH,
					'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
					'{username}' => $last_updated_by,
					'{request_number}' => Utilities::format_return_request_number($return_request['refund_id']),
					'{user_full_name}' => htmlentities($return_request["buyer_name"]),
					'{new_status_name}' => $return_status_arr[$return_request["refund_request_status"]],
				);
				if ($return_request['refmsg_from_type']=="A") {
					$arr_replacements["{username}"]=Settings::getSetting("CONF_WEBSITE_NAME");
				}
									
				if ($return_request["refund_request_updated_by"]!=$return_request["refund_user_id"]){
			 		Utilities::sendMailTpl($return_request["buyer_email"], "return_request_status_change_notification",$arr_replacements);
				}
			
				if ($return_request["refund_request_updated_by"]!=$return_request["shop_user_id"]){
					$arr_replacements["{user_full_name}"]=$return_request["vendor_name"];
					Utilities::sendMailTpl($return_request["opr_shop_owner_email"], "return_request_status_change_notification",$arr_replacements);
				}
				
				if ($return_request['refund_request_action_by']=="U") {
					$arr_replacements["{user_full_name}"]="Admin";
					Utilities::sendMailTpl(Settings::getSetting("CONF_ADMIN_EMAIL"), "return_request_status_change_notification", $arr_replacements);
					$emails = explode(',', Settings::getSetting("CONF_ADDITIONAL_ALERT_EMAILS"));
					foreach ($emails as $email) {
						if (Utilities::utf8_strlen($email) > 0 && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
							Utilities::sendMailTpl($email, "return_request_status_change_notification", $arr_replacements);
						}
					}
			
				}
				return true;
			}else{
				$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			}
			
	}
	
	function SendReturnRequestMessageNotification($return_request_message){
			$p=new Products();
			$return_request=$p->getReturnRequestMessage($return_request_message);
			if ($return_request){
				$return_request_id=$return_request["refund_id"];
				$url = Utilities::generateAbsoluteUrl('account', 'view_return_request',array($return_request_id),CONF_WEBROOT_URL);
				$url='<a href="'.$url.'">'.Utilities::getLabel('M_click_here').'</a>';
				/* Buyer Notification */
				$arr_replacements = array(
						'{site_domain}' => CONF_SERVER_PATH,
						'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
						'{username}' => Settings::getSetting("CONF_WEBSITE_NAME"),
						'{request_number}' => Utilities::format_return_request_number($return_request_id),
						'{message}' => nl2br($return_request["refmsg_text"]),
						'{user_full_name}' => htmlentities($return_request["buyer_name"]),
						'{click_here}' => $url,
				);
				
				if ($return_request["refund_user_id"]!=$return_request["refmsg_from"]){
					$arr_replacements["{user_full_name}"] = htmlentities($return_request["buyer_name"]);
					if ($return_request['refmsg_from_type']=="U"){
						$arr_replacements["{username}"] = $return_request["opr_shop_owner_username"];
					}
					Utilities::sendMailTpl($return_request["buyer_email"], "return_request_message_user", $arr_replacements);
				}
				/* End Buyer Notification */
								
				/* Vendor Notification */
				if ($return_request["shop_user_id"]!=$return_request["refmsg_from"]){
					$arr_replacements["{user_full_name}"] = htmlentities($return_request["opr_shop_owner_name"]);
					if ($return_request['refmsg_from_type']=="U"){
						$arr_replacements["{username}"] = $return_request["message_sent_by_username"];
					}
					Utilities::sendMailTpl($return_request["opr_shop_owner_email"], "return_request_message_user", $arr_replacements);
				}
				/* End Vendor Notification */
								
				if (($return_request["refund_request_status"]==1) && ($return_request['refmsg_from_type']=="U")){
					$url = Utilities::generateAbsoluteUrl('returnrequests', 'view_return_request',array($return_request_id),'/manager/');
					$url='<a href="'.$url.'">'.Utilities::getLabel('M_click_here').'</a>';
					$arr_replacements["{user_full_name}"]="Admin";
					$arr_replacements["{click_here}"]=$url;
					Utilities::sendMailTpl(Settings::getSetting("CONF_ADMIN_EMAIL"), "return_request_message_user", $arr_replacements);
					$emails = explode(',', Settings::getSetting("CONF_ADDITIONAL_ALERT_EMAILS"));
					foreach ($emails as $email) {
						if (Utilities::utf8_strlen($email) > 0 && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
							Utilities::sendMailTpl($email, "return_request_message_user", $arr_replacements);
						}
					}
				}
			return true;
			}else{
				$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			}
			
	}
	
	function sendNotifyAdminSupplierApproval($user_id) {
		$userObj=new User();
		$supplier_request=$userObj->getUserSupplierRequests(array("user"=>$user_id,"pagesize"=>1));
		if ($supplier_request){
			$arr_replacements = array(
				'{site_domain}' => CONF_SERVER_PATH,
				'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
				'{username}' => $supplier_request['user_username'],
				'{email}' => $supplier_request['user_email'],
				'{name}' => htmlentities($supplier_request['user_name']),
				'{reference_number}' => $supplier_request['usuprequest_reference'],
			);
			Utilities::sendMailTpl(Settings::getSetting("CONF_ADMIN_EMAIL"), "new_supplier_approval_admin", $arr_replacements);
			$emails = explode(',', Settings::getSetting("CONF_ADDITIONAL_ALERT_EMAILS"));
			foreach ($emails as $email) {
				if (Utilities::utf8_strlen($email) > 0 && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
					Utilities::sendMailTpl($email, "new_supplier_approval_admin", $arr_replacements);
				}
			}
	        return true;
		}else{
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
		}
    }
	
	function SendSupplierRequestStatusChangeNotification($srequest_id){
		global $supplier_approval_request_status;
		$userObj=new User();
		$supplier_request=$userObj->getUserSupplierRequests(array("id"=>$srequest_id,"pagesize"=>1));
		if ($supplier_request){
			$supplier_request_comments=$supplier_request['usuprequest_comments']!=""?"<br/><br/>".Utilities::getLabel('L_Admin_Comments').": <i>".nl2br($supplier_request['usuprequest_comments'])."</i>":"";
			$arr_replacements = array(
				'{site_domain}' => CONF_SERVER_PATH,
				'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
				'{user_full_name}' => htmlentities($supplier_request['user_name']),
				'{reference_number}' => $supplier_request['usuprequest_reference'],
				'{new_request_status}' => $supplier_approval_request_status[$supplier_request['usuprequest_status']],
				'{request_comments}' => $supplier_request_comments,
			);
			Utilities::sendMailTpl($supplier_request["user_email"], "supplier_request_status_change_buyer", $arr_replacements);
	        return true;
		}else{
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
		}
	}
	
	function sendAffiliateTxnNotification($txn_id){
		$atxn=new Affiliatetransactions($txn_id);
		$txn_detail=$atxn->getTransactionById($txn_id);
		$txn_amount=$txn_detail["atxn_credit"]>0?$txn_detail["atxn_credit"]:$txn_detail["atxn_debit"];
		$arr_replacements=array(
			'{site_domain}' => CONF_SERVER_PATH,
			'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
			'{user_name}' => htmlentities($txn_detail["affiliate_name"]),
			'{txn_id}' => $txn_id,
			'{txn_type}' => $txn_detail["atxn_credit"]>0?Utilities::getLabel('L_credited'):Utilities::getLabel('L_debited'),
			'{txn_amount}' => Utilities::displayMoneyFormat($txn_amount),
			'{txn_comments}' => $txn_detail["atxn_description"],
		);
		Utilities::sendMailTpl($txn_detail["affiliate_email"], "account_credited_debited", $arr_replacements);
		
	}
	
	function affiliateAccountApproved($affiliate_id) {
		$aObj=new Affiliate();
		$affiliate_details=$aObj->getAffiliate(array('affiliate_id'=>$affiliate_id, 'get_flds'=>array('affiliate_id','affiliate_username','affiliate_name','affiliate_email')));
		if ($affiliate_details){
			$arr_replacements = array(
				'{site_domain}' => CONF_SERVER_PATH,
				'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
				'{username}' => $affiliate_details['affiliate_username'],
				'{email}' => $affiliate_details['affiliate_email'],
				'{name}' => htmlentities($affiliate_details['affiliate_name']),
				'{affiliate_page_url}' =>Utilities::generateAbsoluteUrl('affiliate', 'account',array(),CONF_WEBROOT_URL),
				'{contact_us_email}' =>Settings::getSetting("CONF_CONTACT_EMAIL"),
			);
			Utilities::sendMailTpl($affiliate_details['affiliate_email'], "affiliate_account_activated", $arr_replacements);
	        return true;
		}else{
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
		}
    }
	
	function sendWelcomeAffiliateRegistrationMail($affiliate_id) {
		$aObj=new Affiliate();
		$affiliate_details=$aObj->getAffiliate(array('affiliate_id'=>$affiliate_id, 'get_flds'=>array('affiliate_id','affiliate_username','affiliate_name','affiliate_email')));
		if ($affiliate_details){
			$arr_replacements = array(
				'{site_domain}' => CONF_SERVER_PATH,
				'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
				'{username}' => $affiliate_details['affiliate_username'],
				'{email}' => $affiliate_details['affiliate_email'],
				'{name}' => htmlentities($affiliate_details['affiliate_name']),
				'{affiliate_account_login_approved_text}' => Settings::getSetting("CONF_AFFILIATES_REQUIRES_APPROVAL")?Utilities::getLabel('L_AFFILIATE_ACCOUNT_MUST_APPROVED'):Utilities::getLabel('L_AFFILIATE_ACCOUNT_CREATED_TEXT'),
				'{affiliate_page_url}' =>Utilities::generateAbsoluteUrl('affiliate', 'account',array(),CONF_WEBROOT_URL),
				'{contact_us_email}' =>Settings::getSetting("CONF_CONTACT_EMAIL"),
			);
			Utilities::sendMailTpl($affiliate_details['affiliate_email'], "welcome_affiliate_registration", $arr_replacements);
	        return true;
		}else{
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
		}
    }
	
	function sendNotifyAdminAffiliateRegistration($affiliate_id) {
		$aObj=new Affiliate();
		$affiliate_details=$aObj->getAffiliate(array('affiliate_id'=>$affiliate_id, 'get_flds'=>array('affiliate_id','affiliate_username','affiliate_name','affiliate_email')));
		if ($affiliate_details){
			$arr_replacements = array(
				'{site_domain}' => CONF_SERVER_PATH,
				'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
				'{username}' => $affiliate_details['affiliate_username'],
				'{email}' => $affiliate_details['affiliate_email'],
				'{name}' => htmlentities($affiliate_details['affiliate_name']),
			);
			Utilities::sendMailTpl(Settings::getSetting("CONF_ADMIN_EMAIL"), "affiliate_registration_admin", $arr_replacements);
			$emails = explode(',', Settings::getSetting("CONF_ADDITIONAL_ALERT_EMAILS"));
			foreach ($emails as $email) {
				if (Utilities::utf8_strlen($email) > 0 && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
					Utilities::sendMailTpl($email, "affiliate_registration_admin", $arr_replacements);
				}
			}
	        return true;
		}else{
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
		}
    }
	
	function SendAffiliateWithdrawRequestNotification($request_id,$admin_or_user="A"){
			global $status_arr;
			$afwithdrawalRequestObj=new AffiliateWithdrawalRequests();
			$affiliate_withdrawal_request=$afwithdrawalRequestObj->getWithdrawRequestData($request_id);
			if ($affiliate_withdrawal_request){
				switch($affiliate_withdrawal_request['afwithdrawal_payment_mode']) {
			        case 'bank':
            			$payment_details=Utilities::getLabel('L_Bank_Name').": ".$affiliate_withdrawal_request["afwithdrawal_bank_name"]."<br/>".Utilities::getLabel('L_ABA/BSB_number_Branch_Number').": ".$affiliate_withdrawal_request["afwithdrawal_bank_branch_number"]."<br/>".Utilities::getLabel('L_SWIFT_Code').": ".$affiliate_withdrawal_request["afwithdrawal_bank_swift_code"]."<br/>".Utilities::getLabel('L_Account_Name').": ".$affiliate_withdrawal_request["afwithdrawal_bank_account_name"]."<br/>".Utilities::getLabel('L_Account_Number').": ".$affiliate_withdrawal_request["afwithdrawal_bank_account_number"]."<br/>";
					break;
        			case 'paypal':
		        	    $payment_details=Utilities::getLabel('L_PayPal_Email_Account').": ".$affiliate_withdrawal_request["afwithdrawal_paypal"];
        		    break;
					case 'cheque':
		        	    $payment_details=Utilities::getLabel('L_Cheque_Payee_Name').": ".$affiliate_withdrawal_request["afwithdrawal_cheque"];
        		    break;
				}
				
				$formatted_request_value="#".str_pad($request_id,6,'0',STR_PAD_LEFT);
				$arr_replacements = array(
				'{site_domain}' => CONF_SERVER_PATH,
				'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
				'{request_id}' => $formatted_request_value,
				'{affiliate_username}' => $affiliate_withdrawal_request['affiliate_username'],
				'{request_amount}' => Utilities::displayMoneyFormat($affiliate_withdrawal_request["afwithdrawal_amount"]),
				'{request_payment_mode}' => ucfirst($affiliate_withdrawal_request['afwithdrawal_payment_mode']),
				'{request_payment_details}' => $payment_details,
				'{request_comments}' => $affiliate_withdrawal_request['afwithdrawal_comments'],
				'{request_status}' => $status_arr[$affiliate_withdrawal_request['afwithdrawal_status']],
				'{affiliate_name}' => $affiliate_withdrawal_request['affiliate_name'],
				);
				if ($admin_or_user=="A"){
					Utilities::sendMailTpl(Settings::getSetting("CONF_ADMIN_EMAIL"), "affiliate_withdrawal_request_admin", $arr_replacements);
					$emails = explode(',', Settings::getSetting("CONF_ADDITIONAL_ALERT_EMAILS"));
					foreach ($emails as $email) {
						if (Utilities::utf8_strlen($email) > 0 && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
							Utilities::sendMailTpl($email, "affiliate_withdrawal_request_admin", $arr_replacements);
						}
					}
			
				}else{
					Utilities::sendMailTpl($affiliate_withdrawal_request["affiliate_email"], "affiliate_withdrawal_request_approved_declined", $arr_replacements);	
				}
				return true;
			}else{
				$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			}
	}
	
	function SendAdminRequestNotification($user_request_id){
			$userObj=new User();
			$user_request=$userObj->getUserRequestById($user_request_id);
			if ($user_request){
			$user_request_type = $user_request["urequest_type"]=="B"?Utilities::getLabel('L_Brand'):Utilities::getLabel('L_NA');
			$user_request_text = $user_request["urequest_text"];
			$arr_replacements = array(
				'{site_domain}' => CONF_SERVER_PATH,
				'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
				'{username}' => htmlentities($user_request['user_name']),
				'{request_type}' => $user_request_type,
				'{request_name}' => $user_request_text,
			);
			Utilities::sendMailTpl(Settings::getSetting("CONF_ADMIN_EMAIL"), "supplier_request_submitted", $arr_replacements);
				$emails = explode(',', Settings::getSetting("CONF_ADDITIONAL_ALERT_EMAILS"));
					foreach ($emails as $email) {
						if (Utilities::utf8_strlen($email) > 0 && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
							Utilities::sendMailTpl($email, "supplier_request_submitted", $arr_replacements);
						}
				}
			return true;
			}else{
				$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			}
			
	}
	
	function SendUserRequestStatusChangeNotification($user_request_id){
			global $supplier_request_status;
			$userObj=new User();
			$user_request=$userObj->getUserRequestById($user_request_id);
			if ($user_request){
				$user_request_type = $user_request["urequest_type"]=="B"?Utilities::getLabel('L_Brand'):Utilities::getLabel('L_NA');
				$arr_replacements = array(
					'{site_domain}' => CONF_SERVER_PATH,
					'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
					'{user_name}' => htmlentities($user_request['user_name']),
					'{request_value}' => $user_request['urequest_text'],
					'{request_type}' => $user_request_type,
					'{request_status}' => $supplier_request_status[$user_request['urequest_status']],
				);
				Utilities::sendMailTpl($user_request['user_email'], "supplier_request_status_change", $arr_replacements);
			return true;
			}else{
				$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			}
			
	}
	function sendblogCommentEmail($post) {
		if (empty($post)) { return false; }
		$blogObj =  new Blog;
		$data = $blogObj->getPostById($post['comment_post_id']);
		if ($data){
			$arr_replacements = array(
				'{comment}' => $post['comment_content'],
				'{post_name}' => htmlentities($data['post_title']),
				'{user_full_name}' => htmlentities(ucfirst($post['comment_author_name'])),
			);
			Utilities::sendMailTpl(Settings::getSetting("CONF_ADMIN_EMAIL"), 'blog_comment_email', $arr_replacements);
	        return true;
		}else{
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
		}
    }
	function sendContributionEmailToAdmin($data) {
		if (empty($data)) { return false; }
		if ($data){
			$replacements = array(
				'{user_full_name}' => ucfirst($data['contribution_author_first_name']) . ' ' . ucfirst($data['contribution_author_last_name']),
				'{posted_on}' => date('Y-m-d'),
				'{user_email}' => $data['contribution_author_email'],
				'{user_phone}' => $data['contribution_author_phone'],
			);
			Utilities::sendMailTpl(Settings::getSetting("CONF_ADMIN_EMAIL"), 'contribution_email_to_admin', $replacements);
	        return true;
		}else{
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
		}
    }
	
	
	function sendWelcomeAdvertiserRegistrationMail($advertiser_id) {
		$aObj=new Advertisers();
		$advertiser_details=$aObj->getAdvertiser(array('advertiser_id'=>$advertiser_id, 'get_flds'=>array('advertiser_id','advertiser_username','advertiser_name','advertiser_email')));
		if ($advertiser_details){
			$arr_replacements = array(
				'{site_domain}' => CONF_SERVER_PATH,
				'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
				'{username}' => $advertiser_details['advertiser_username'],
				'{email}' => $advertiser_details['advertiser_email'],
				'{name}' => htmlentities($advertiser_details['advertiser_name']),
				'{advertiser_account_login_approved_text}' => Settings::getSetting("CONF_ADVERTISER_REQUIRES_APPROVAL")?Utilities::getLabel('L_ADVERTISER_ACCOUNT_MUST_APPROVED'):Utilities::getLabel('L_ADVERTISER_ACCOUNT_CREATED_TEXT'),
				'{advertiser_page_url}' =>Utilities::generateAbsoluteUrl('advertisers', 'account',array(),CONF_WEBROOT_URL),
				'{contact_us_email}' =>Settings::getSetting("CONF_CONTACT_EMAIL"),
			);
			Utilities::sendMailTpl($advertiser_details['advertiser_email'], "welcome_advertiser_registration", $arr_replacements);
	        return true;
		}else{
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
		}
    }
	
	function sendNotifyAdminAdvertiserRegistration($advertiser_id) {
		$aObj=new Advertisers();
		$advertiser_details=$aObj->getAdvertiser(array('advertiser_id'=>$advertiser_id, 'get_flds'=>array('advertiser_id','advertiser_username','advertiser_name','advertiser_email')));
		if ($advertiser_details){
			$arr_replacements = array(
				'{site_domain}' => CONF_SERVER_PATH,
				'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
				'{username}' => $advertiser_details['advertiser_username'],
				'{email}' => $advertiser_details['advertiser_email'],
				'{name}' => htmlentities($advertiser_details['advertiser_name']),
			);
			Utilities::sendMailTpl(Settings::getSetting("CONF_ADMIN_EMAIL"), "advertiser_registration_admin", $arr_replacements);
			$emails = explode(',', Settings::getSetting("CONF_ADDITIONAL_ALERT_EMAILS"));
			foreach ($emails as $email) {
				if (Utilities::utf8_strlen($email) > 0 && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
					Utilities::sendMailTpl($email, "advertiser_registration_admin", $arr_replacements);
				}
			}
	        return true;
		}else{
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
		}
    }
	
	function advertiserAccountApproved($advertiser_id) {
		$aObj=new Advertisers();
		$advertiser_details=$aObj->getAdvertiser(array('advertiser_id'=>$advertiser_id, 'get_flds'=>array('advertiser_id','advertiser_username','advertiser_name','advertiser_email')));
		if ($advertiser_details){
			$arr_replacements = array(
				'{site_domain}' => CONF_SERVER_PATH,
				'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
				'{username}' => $advertiser_details['advertiser_username'],
				'{email}' => $advertiser_details['advertiser_email'],
				'{name}' => htmlentities($advertiser_details['advertiser_name']),
				'{advertiser_page_url}' =>Utilities::generateAbsoluteUrl('advertisers', 'account',array(),CONF_WEBROOT_URL),
				'{contact_us_email}' =>Settings::getSetting("CONF_CONTACT_EMAIL"),
			);
			Utilities::sendMailTpl($advertiser_details['advertiser_email'], "advertiser_account_activated", $arr_replacements);
	        return true;
		}else{
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
		}
    }
	
	function sendAdvertiserTxnNotification($txn_id){
		$atxn=new Advertisertransactions($txn_id);
		$txn_detail=$atxn->getTransactionById($txn_id);
		$txn_amount=$txn_detail["atxn_credit"]>0?$txn_detail["atxn_credit"]:$txn_detail["atxn_debit"];
		$arr_replacements=array(
			'{site_domain}' => CONF_SERVER_PATH,
			'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
			'{user_name}' => htmlentities($txn_detail["advertiser_name"]),
			'{txn_id}' => $txn_id,
			'{txn_type}' => $txn_detail["atxn_credit"]>0?Utilities::getLabel('L_credited'):Utilities::getLabel('L_debited'),
			'{txn_amount}' => Utilities::displayMoneyFormat($txn_amount),
			'{txn_comments}' => $txn_detail["atxn_description"],
		);
		Utilities::sendMailTpl($txn_detail["advertiser_email"], "account_credited_debited", $arr_replacements);
		
	}
	
	function SendAdvertiserWithdrawRequestNotification($request_id,$admin_or_user="A"){
			global $status_arr;
			$adwithdrawalRequestObj=new AdvertiserWithdrawalRequests();
			$advertiser_withdrawal_request=$adwithdrawalRequestObj->getWithdrawRequestData($request_id);
			if ($advertiser_withdrawal_request){
				switch($advertiser_withdrawal_request['adwithdrawal_payment_mode']) {
			        case 'bank':
            			$payment_details=Utilities::getLabel('L_Bank_Name').": ".$advertiser_withdrawal_request["adwithdrawal_bank_name"]."<br/>".Utilities::getLabel('L_ABA/BSB_number_Branch_Number').": ".$advertiser_withdrawal_request["adwithdrawal_bank_branch_number"]."<br/>".Utilities::getLabel('L_SWIFT_Code').": ".$advertiser_withdrawal_request["adwithdrawal_bank_swift_code"]."<br/>".Utilities::getLabel('L_Account_Name').": ".$advertiser_withdrawal_request["adwithdrawal_bank_account_name"]."<br/>".Utilities::getLabel('L_Account_Number').": ".$advertiser_withdrawal_request["adwithdrawal_bank_account_number"]."<br/>";
					break;
        			case 'paypal':
		        	    $payment_details=Utilities::getLabel('L_PayPal_Email_Account').": ".$advertiser_withdrawal_request["adwithdrawal_paypal"];
        		    break;
					case 'cheque':
		        	    $payment_details=Utilities::getLabel('L_Cheque_Payee_Name').": ".$advertiser_withdrawal_request["adwithdrawal_cheque"];
        		    break;
				}
				
				$formatted_request_value="#".str_pad($request_id,6,'0',STR_PAD_LEFT);
				$arr_replacements = array(
				'{site_domain}' => CONF_SERVER_PATH,
				'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
				'{request_id}' => $formatted_request_value,
				'{advertiser_username}' => $advertiser_withdrawal_request['advertiser_username'],
				'{request_amount}' => Utilities::displayMoneyFormat($advertiser_withdrawal_request["adwithdrawal_amount"]),
				'{request_payment_mode}' => ucfirst($advertiser_withdrawal_request['adwithdrawal_payment_mode']),
				'{request_payment_details}' => $payment_details,
				'{request_comments}' => $advertiser_withdrawal_request['adwithdrawal_comments'],
				'{request_status}' => $status_arr[$advertiser_withdrawal_request['adwithdrawal_status']],
				'{advertiser_name}' => $advertiser_withdrawal_request['advertiser_name'],
				);
				if ($admin_or_user=="A"){
					Utilities::sendMailTpl(Settings::getSetting("CONF_ADMIN_EMAIL"), "advertiser_withdrawal_request_admin", $arr_replacements);
					$emails = explode(',', Settings::getSetting("CONF_ADDITIONAL_ALERT_EMAILS"));
					foreach ($emails as $email) {
						if (Utilities::utf8_strlen($email) > 0 && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
							Utilities::sendMailTpl($email, "advertiser_withdrawal_request_admin", $arr_replacements);
						}
					}
			
				}else{
					Utilities::sendMailTpl($advertiser_withdrawal_request["advertiser_email"], "advertiser_withdrawal_request_approved_declined", $arr_replacements);	
				}
				return true;
			}else{
				$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			}
	}
	function sendNotifyAdminPromotion($promotion_id) {
		$prmObj=new Promotions();
		$promotion_details=$prmObj->getPromotion($promotion_id);
		if ($promotion_details){
			$arr_replacements = array(
				'{site_domain}' => CONF_SERVER_PATH,
				'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
				'{promotion_number}' => '#'.$promotion_details['promotion_number'],
			);
			Utilities::sendMailTpl(Settings::getSetting("CONF_ADMIN_EMAIL"), "unapproved_promotion_admin", $arr_replacements);
			$emails = explode(',', Settings::getSetting("CONF_ADDITIONAL_ALERT_EMAILS"));
			foreach ($emails as $email) {
				if (Utilities::utf8_strlen($email) > 0 && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
					Utilities::sendMailTpl($email, "unapproved_promotion_admin", $arr_replacements);
				}
			}
	        return true;
		}else{
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
		}
    }
	
	function promotionApproved($promotion_id) {
		$prmObj=new Promotions();
		$promotion_details=$prmObj->getPromotion($promotion_id);
		if ($promotion_details){
			$arr_replacements = array(
				'{site_domain}' => CONF_SERVER_PATH,
				'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
				'{user_full_name}' => htmlentities($promotion_details['user_name']),
				'{promotion_number}' => '#'.$promotion_details['promotion_number'],
			);
			Utilities::sendMailTpl($promotion_details['user_email'], "promotion_approved", $arr_replacements);
	        return true;
		}else{
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
		}
    }
	function Subscription_Status_Update_Buyer($comment_id){
		$sorderObj=new SubscriptionOrders();
		$subscription_order_comment=$sorderObj->getOrderStatusHistory(array("id"=>$comment_id),1);
		
		
		if ($subscription_order_comment && $subscription_order_comment["mpos_history_customer_notified"]){
			if ($subscription_order_comment['mpos_history_comments']!=""){
				$msg_comments=Utilities::getLabel('M_Comments_for_your_subscription').":<br/><br/><em>".$order_comment['mpos_history_comments'].".</em><br/><br/>";
			}
			
			
			$arr_replacements=array(
					'{site_domain}' => CONF_SERVER_PATH,
					'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
					'{user_full_name}' => htmlentities($subscription_order_comment["mporder_user_name"]),
					'{new_subscription_status}' => $subscription_order_comment["sorder_status_name"],
					'{subscription_invoice_number}' => $subscription_order_comment["mporder_invoice_number"],
					
				);
			Utilities::sendMailTpl($subscription_order_comment["mporder_user_email"], "subscription_status_change", $arr_replacements);	
			return true;
		}else{
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
		}
	}
	
	function Subscription_Details_Buyer_Admin($order_id){
		global $payment_status_arr;
		$sorderObj=new SubscriptionOrders();
		$order_detail = $sorderObj->getSubscriptionOrderById($order_id);
		if ($order_detail) {
			$arr_replacements = array(
						'{site_domain}' => CONF_SERVER_PATH,
			            '{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
						'{user_full_name}' => htmlentities($order_detail['mporder_user_name']),
						'{subscription_invoice_number}' => $order_detail['mporder_invoice_number'],
						'{subscription_payment_status}' => $payment_status_arr[$order_detail['mporder_payment_status']],
						'{subscription_details}' => $sorderObj->getSubscriptionDetailForEmailTemplate($order_id),
			        );
					
					if (Settings::getSetting("CONF_NEW_SUBSCRIPTION_EMAIL")){
						Utilities::sendMailTpl(Settings::getSetting("CONF_ADMIN_EMAIL"), "subscription_details_admin", $arr_replacements);
						$emails = explode(',', Settings::getSetting("CONF_ADDITIONAL_ALERT_EMAILS"));
						foreach ($emails as $email) {
							if (Utilities::utf8_strlen($email) > 0 && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
								Utilities::sendMailTpl($email, "subscription_details_admin", $arr_replacements);
							}
						}
					}
					Utilities::sendMailTpl($order_detail["mporder_user_email"], "subscription_details_buyer", $arr_replacements);
		}
		return true;
	}
	
	function Subscription_Expiry_Notification($order_id){
		global $payment_status_arr;
		$sorderObj=new SubscriptionOrders();
		$order_detail = $sorderObj->getSubscriptionOrderById($order_id);
		if ($order_detail) {
			$url = Utilities::generateAbsoluteUrl('account', 'view_subscription',array($order_id),CONF_WEBROOT_URL);
			$subscription_anchor="<a href='".$url."'>".Utilities::getLabel('M_click_here')."</a>";
			$arr_replacements = array(
						'{site_domain}' => CONF_SERVER_PATH,
			            '{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
						'{user_full_name}' => htmlentities($order_detail['mporder_user_name']),
						'{subscription_end_date}' => Utilities::formatDate($order_detail['mporder_subscription_end_date']),
						'{click_here}' => $subscription_anchor,
			        );
					if (Settings::getSetting("CONF_SUBSCRIPTION_EXPIRY_EMAIL")){
						Utilities::sendMailTpl($order_detail["mporder_user_email"], "subscription_expiry_alert", $arr_replacements);
					}
			return true;
		}
		return false;
		
	}
}
?>