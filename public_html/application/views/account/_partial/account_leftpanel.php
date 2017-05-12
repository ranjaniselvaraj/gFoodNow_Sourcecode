<?php defined('SYSTEM_INIT') or die('Invalid Usage'); global $loggedin_user; ?>
<?php 
if ($is_buyer_logged || $is_seller_logged){
	
	if ($buyer_supplier_tab=="B") { 
		 include CONF_THEME_PATH . 'account/_partial/account_buyerleftpanel.php';
	}else {
		include CONF_THEME_PATH . 'account/_partial/account_supplierleftpanel.php'; 
	}
}elseif ($is_advertiser_logged){
	include CONF_THEME_PATH . 'account/_partial/account_advertiserleftpanel.php'; 
}
?>
 