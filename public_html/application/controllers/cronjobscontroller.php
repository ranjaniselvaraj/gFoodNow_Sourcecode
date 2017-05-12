<?php
class cronjobsController extends CommonController{
	function execute($skip_time_limit=0){
		$csObj = new Cronschedules();
		$cron_schedules = $csObj->getCronSchedules(1);
		foreach($cron_schedules as $crKey=>$crVal){
			$cron_last_activity=$csObj->getCronLastLoggedActivity($crVal['cron_id']);
			$time_interval_minutes = round(abs(time() - strtotime($cron_last_activity['cronlog_ended_at'])) / 60,2);
			if ((!$cron_last_activity || ($time_interval_minutes>$crVal['cron_interval'])) && ($cron_last_activity['cronlog_ended_at']!='0000-00-00 00:00:00')){
				if (!$cronlog_id=$csObj->addUpdateCronLog(
					array('cron_id'=>$crVal['cron_id']
						))){
					die($csObj->getError());				
			}
			$dt = date("Y-m-d",strtotime($cron_last_activity['cronlog_started_at']));
			list($controller,$action) = explode("/",$crVal['cron_command']);
			$return = Utilities::curl_post(Utilities::generateAbsoluteUrl($controller,$action,array($dt)));
			if (!$cron_log_id=$csObj->addUpdateCronLog(array(
				'cronlog_id'=>$cronlog_id,
				'ended_at'=>date("Y-m-d H:i:s"),
				'details'=>'Completed Successfully'
				))){
				die($csObj->getError());
		}
	}else{
		$time_interval_minutes_start = round(abs(time() - strtotime($cron_last_activity['cronlog_started_at'])) / 60,2);
		if ($time_interval_minutes_start>(2*$crVal['cron_interval']) && ($cron_last_activity['cronlog_ended_at']=='0000-00-00 00:00:00')){
			$cron_log_id=$csObj->addUpdateCronLog(array(
				'cronlog_id'=>$cron_last_activity['cronlog_id'],
				'ended_at'=>date("Y-m-d H:i:s"),
				'details'=>'Ended Forcefully'
				));
			}
		}
	}
}
	function cron_charge_wallet_promotions($dt){
		$prmObj=new Promotions();
		$promotions = $prmObj->getPromotionClicksSummary(array("date"=>$dt));
		foreach($promotions as $pkey=>$pval){
			$promotion_id = $pval['pclick_promotion_id'];
			$promotion_last_charged=$prmObj->getPromotionLastChargedEntry($promotion_id);
			$promotion_clicks= $prmObj->getPromotionClicksSummary(array("promotion"=>$promotion_id,"start_id"=>$promotion_last_charged['pcharge_end_click_id'],"pagesize"=>1));
			if ($promotion_clicks['total_cost']>0 && (!$promotion_payment_id=$prmObj->addUpdatePromotionCharges(
				array(
					'user_id'=>$pval['promotion_user_id'],
					'promotion_id'=>$promotion_id,
					'total_cost'=>$promotion_clicks['total_cost'],
					'total_clicks'=>$promotion_clicks['total_clicks'],
					'start_click_id'=>$promotion_clicks['start_click_id'],
					'end_click_id'=>$promotion_clicks['end_click_id'],
					'start_click_date'=>$promotion_clicks['start_click_date'],
					'end_click_date'=>$promotion_clicks['end_click_date'],
					)))){
				die($prmObj->getError());				
			}
		}
	}
	
	function cron_send_subscription_expiry_email(){
		$subscriptionOrderObj = new SubscriptionOrders();
		$expiry = strtotime('+'.Settings::getSetting("CONF_SUBSCRIPTION_EXPIRY_EMAIL_DAYS").' day');
		$order_filters = array(
			'subscription_status'	=>	Settings::getSetting("CONF_ACTIVE_SUBSCRIPTION_STATUS"),
			'subscription_end_date'=> date('Y-m-d H:i:s', $expiry),
			'trial'=>1,
			'expiry_email'=>0,
			'pagesize'=>25
		);
		$subscription_orders = $subscriptionOrderObj->getSubscriptionOrders( $order_filters ); 
		foreach($subscription_orders as $skey=>$sval){
			$emailNotificationObj=new Emailnotifications();
			if ($emailNotificationObj->Subscription_Expiry_Notification($sval['mporder_id'])){
				$orderPaymentObj=new SubscriptionOrderPayment($sval['mporder_id']);
				$order_update_arr = array( 'mporder_subscription_expiry_email' => 1 );
				$orderPaymentObj->updateOrderInfo( $sval['mporder_id'], $order_update_arr);
			}
		}
		
	}
	
	function cron_change_subscription_status(){
		$subscriptionOrderObj = new SubscriptionOrders();
		$order_filters = array(
			'subscription_status'	=>	Settings::getSetting("CONF_ACTIVE_SUBSCRIPTION_STATUS"),
			'subscription_end_date'=> date("Y-m-d"),
			'pagesize'=>25
		);
		$subscription_orders = $subscriptionOrderObj->getSubscriptionOrders( $order_filters ); 
		foreach($subscription_orders as $skey=>$sval){
			$orderPaymentObj=new SubscriptionOrderPayment($sval['mporder_id']);
			if( strpos($sval['mporder_gateway_subscription_id'],'FREE') === FALSE ){
						$ppExpObj = new PaypalStandard();
						$subscription_cancelation_result = $ppExpObj->recurringCancel( $sval['mporder_gateway_subscription_id'] );
						if( isset($subscription_cancelation_result['PROFILEID']) ) {
						$order_update_arr = array( 'mporder_mpo_status_id' => Settings::getSetting("CONF_CANCELLED_SUBSCRIPTION_STATUS") );
						$orderPaymentObj->updateOrderInfo( $sval['mporder_id'], $order_update_arr);
					}
				}else {
					$order_update_arr = array( 'mporder_mpo_status_id' => Settings::getSetting("CONF_CANCELLED_SUBSCRIPTION_STATUS") );
					$orderPaymentObj->updateOrderInfo( $sval['mporder_id'], $order_update_arr);
					
			}
			$orderPaymentObj->addOrderHistory( $sval['mporder_id'], Settings::getSetting("CONF_CANCELLED_SUBSCRIPTION_STATUS"),'',true );
		}
		
	}
}
