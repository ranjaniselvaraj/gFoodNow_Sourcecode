<?php
require_once (CONF_INSTALLATION_PATH . 'public/includes/APIs/shipstatation/ship.class.php');
class Settings extends Model {
	
   function __construct(){
		parent::__construct();
		$this->db = &Syspage::getdb();
   }	
   
   Static function getSetting($attr) {
		   $db = &Syspage::getdb();
		   $srch = new SearchBase('tbl_configurations', 'tc');
		   $srch->addCondition('tc.conf_var', '=',$attr);
		   $rs = $srch->getResultSet();
		   $row = $db->fetch($rs);
		   if ($row){
			   return ($row['conf_serialized'])?unserialize($row['conf_val']):$row['conf_val'];
		   }
		   return false;
    }
	
	
    function update($data){
		$accept_keys = array(
							'CONF_WEBSITE_NAME',
							'CONF_SITE_OWNER',
							'CONF_ADMIN_EMAIL',
							'CONF_ADDRESS',
							'CONF_SITE_PHONE',
							'CONF_SITE_FAX',
							'CONF_FRONT_LOGO',
							'CONF_FRONT_MOBILE_LOGO_ICON',
							'CONF_FOOTER_LOGO_GRAPHIC',
							'CONF_WATERMARK_IMAGE',
							'CONF_LANGUAGE',
							'CONF_TIMEZONE',
							'CONF_COUNTRY',
							'CONF_DATE_FORMAT_PHP',
							'CONF_CURRENCY',
							'CONF_CURRENCY_SYMBOL',
							'CONF_DISPLAY_CURRENCY_SYMBOL',
							'CONF_FILE_EXT_ALLOWED',
							'CONF_FILE_MIME_ALLOWED',
							'CONF_IMAGE_MIME_ALLOWED',
							'CONF_PAGE_TITLE',
							'CONF_META_KEYWORD',
							'CONF_META_DESCRIPTION',
							'CONF_SITE_TRACKER_CODE',
							'CONF_DEF_ITEMS_PER_PAGE_CATALOG',
							'CONF_DEF_ITEMS_PER_PAGE_ADMIN',
							'CONF_DEF_LIST_DESC_LIMIT',
							'CONF_ALLOW_REVIEWS',
							'CONF_REVIEW_ALERT_EMAIL',
							'CONF_SITE_TAX',
							'CONF_MIN_PRODUCT_PRICE',
							//'CONF_SITE_COMM_PRODUCT_SOLD',
							'CONF_MAX_COMMISSION',
							'CONF_DISPLAY_PRICE_COMMISSION',
							'CONF_ADMIN_APPROVAL_REGISTRATION',
							'CONF_EMAIL_VERIFICATION_REGISTRATION',
							'CONF_AUTO_LOGIN_REGISTRATION',
							'CONF_NOTIFY_ADMIN_REGISTRATION',
							'CONF_WELCOME_EMAIL_REGISTRATION',
							'CONF_AUTO_LOGOUT_PASSWORD_CHANGE',
							'CONF_ACCOUNT_TERMS',
							'CONF_LOGIN_DISPLAY_PRICES',
							'CONF_NEW_ACCOUNT_EMAIL',
							'CONF_NEW_ORDER_EMAIL',
							'CONF_DEFAULT_REVIEW_STATUS',
							'CONF_DEFAULT_ORDER_STATUS',
							'CONF_DEFAULT_PAID_ORDER_STATUS',
							'CONF_DEFAULT_SHIPPING_ORDER_STATUS',
							'CONF_DEFAULT_CANCEL_ORDER_STATUS',
							'CONF_RETURN_REQUEST_ORDER_STATUS',
							'CONF_RETURN_REQUEST_WITHDRAWN_ORDER_STATUS',
							'CONF_RETURN_REQUEST_APPROVED_ORDER_STATUS',
							'CONF_VENDOR_ORDER_STATUS',
							'CONF_BUYER_ORDER_STATUS',
							'CONF_PROCESSING_ORDER_STATUS',
							'CONF_COMPLETED_ORDER_STATUS',
							'CONF_REVIEW_READY_ORDER_STATUS',
							'CONF_ALLOW_CANCELLATION_ORDER_STATUS',
							'CONF_RETURN_EXCHANGE_READY_ORDER_STATUS',
							'CONF_SALES_ORDER_STATUS',
							'CONF_PURCHASE_ORDER_STATUS',
							'CONF_CHECK_STOCK',
							'CONF_ALLOW_CHECKOUT',
							'CONF_SUBTRACT_STOCK',
							'CONF_MIN_WITHDRAW_LIMIT',
							'CONF_MIN_INTERVAL_WITHDRAW_REQUESTS',
							'CONF_FACEBOOK_APP_ID',
							'CONF_FACEBOOK_APP_SECRET',
							'CONF_GOOGLEPLUS_DEVELOPER_KEY',
							'CONF_GOOGLEPLUS_CLIENT_ID',
							'CONF_GOOGLEPLUS_CLIENT_SECRET',
							'CONF_MAILCHIMP_KEY',
							'CONF_MAILCHIMP_LIST_ID',
							'CONF_FROM_EMAIL',
							'CONF_FROM_NAME',
							'CONF_REPLY_TO_EMAIL',
							'CONF_CONTACT_EMAIL',
							'CONF_ADDITIONAL_ALERT_EMAILS',
							'CONF_SEND_EMAIL',
							'CONF_SEND_SMTP_EMAIL',
							'CONF_SMTP_HOST',
							'CONF_SMTP_PORT',
							'CONF_SMTP_USERNAME',
							'CONF_SMTP_PASSWORD',
							'CONF_SMTP_SECURE',
							'CONF_MAINTENANCE',
							'CONF_MAINTENANCE_TEXT',
							'CONF_ENABLE_LIVECHAT',
							'CONF_LIVE_CHAT_CODE',
							'CONF_FAVICON',
							'CONF_APPLE_TOUCH_ICON',
							'CONF_ENABLE_FACEBOOK_LOGIN',
							'CONF_ENABLE_GOOGLEPLUS_LOGIN',
							'CONF_USE_SSL',
							'CONF_ADMIN_LOGO',
							'CONF_EMAIL_LOGO',
							'CONF_MAX_LOGIN_ATTEMPTS',
							'CONF_AFFILIATES_REQUIRES_APPROVAL',
							'CONF_AFFILIATES_AUTO_COMMISSION',
							'CONF_AFFILIATES_COMMISSION',
							'CONF_AFFILIATES_TERMS',
							'CONF_AFFILIATES_ALERT_EMAIL',
							'CONF_SOCIAL_FEED_IMAGE',
							'CONF_SOCIAL_FEED_FACEBOOK_POST_TITLE',
							'CONF_SOCIAL_FEED_FACEBOOK_POST_CAPTION',
							'CONF_SOCIAL_FEED_FACEBOOK_POST_DESCRIPTION',
							'CONF_TWITTER_API_KEY',
							'CONF_TWITTER_API_SECRET',
							'CONF_SOCIAL_FEED_TWITTER_POST_TITLE',
							'CONF_PRODUCT_META_TITLE_MANDATORY',
							'CONF_PRODUCT_MODEL_MANDATORY',
							'CONF_PRODUCT_SKU_MANDATORY',
							'CONF_AFFILIATE_SIGNUP_COMMISSION',
							'CONF_RECAPTCHA_SECRET_KEY',
							'CONF_RECAPTCHA_SITE_KEY',
							'CONF_ENABLE_REFERRER_MODULE',
							'CONF_REGISTRATION_REFERRER_REWARD_POINTS',
							'CONF_REGISTRATION_REFERRER_REWARD_POINTS_VALIDITY',
							'CONF_REGISTRATION_REFEREE_REWARD_POINTS',
							'CONF_REGISTRATION_REFEREE_REWARD_POINTS_VALIDITY',
							'CONF_SALE_REFERRER_REWARD_POINTS',
							'CONF_SALE_REFERRER_REWARD_POINTS_VALIDITY',
							'CONF_SALE_REFEREE_REWARD_POINTS',
							'CONF_SALE_REFEREE_REWARD_POINTS_VALIDITY',
							'CONF_TWITTER_USERNAME',
							'CONF_ENABLE_NEWSLETTER_SUBSCRIPTION',
							'CONF_NEWSLETTER_SYSTEM',
							'CONF_AWEBER_SIGNUP_CODE',
							'CONF_FRONT_THEME',
							'CONF_PROCESS_ORDER_REFUND_CANCELLATION',
							'CONF_SELL_SITENAME_PAGE',
							'CONF_ENABLE_COD_PAYMENTS',
							'CONF_MIN_COD_ORDER_LIMIT',
							'CONF_MAX_COD_ORDER_LIMIT',
							'CONF_RECOMMENDED_ITEMS_HOME_PAGE',
							'CONF_RECOMMENDED_ITEMS_PRODUCT_PAGE',
							'CONF_CUSTOMER_BOUGHT_ITEMS_PRODUCT_PAGE',
							'CONF_CUSTOMER_BOUGHT_ITEMS_CART_PAGE',
							'CONF_ACTIVATE_SEPARATE_SIGNUP_FORM',
							'CONF_ADMIN_APPROVAL_SUPPLIER_REGISTRATION',
							'CONF_BUYER_CAN_SEE_SELLER_TAB',
							'CONF_ALLOW_USED_PRODUCTS_LISTING',
							'CONF_AUTO_RESTORE_ON',
							'CONF_MAX_NUMBER_PRODUCT_ADDONS',
							'CONF_FEATURED_ITEMS_HOME_PAGE',
							'CONF_ANALYTICS_CLIENT_ID',
							'CONF_ANALYTICS_SECRET_KEY',
							'CONF_ANALYTICS_ID',
							'CONF_ANALYTICS_ACCESS_TOKEN',
							'CONF_MIN_WALLET_BALANCE',
							'CONF_WALLET_BALANCE_ALERT',
							'CONF_ADVERTISER_REQUIRES_APPROVAL',
							'CONF_ADVERTISER_TERMS',
							'CONF_ADVERTISER_ALERT_EMAIL',
							'CONF_CPC_PRODUCT',
							'CONF_CPC_SHOP',
							'CONF_CPC_BANNER',
							'CONF_PPC_PRODUCTS_HOME_PAGE_CAPTION',
							'CONF_PPC_PRODUCTS_HOME_PAGE',
							'CONF_PPC_SHOPS_HOME_PAGE_CAPTION',
							'CONF_PPC_SHOPS_HOME_PAGE',
							'CONF_DISP_INC_TAX',
							'CONF_ENABLE_BUYING_OWN_PRODUCTS',
							'CONF_ACTIVE_SUBSCRIPTION_STATUS',
							'CONF_CANCELLED_SUBSCRIPTION_STATUS',
							'CONF_PENDING_SUBSCRIPTION_STATUS',
							'CONF_NEW_SUBSCRIPTION_EMAIL',
							'CONF_SHIPSTATION_API_STATUS',
							'CONF_SHIPSTATION_API_KEY',
							'CONF_SHIPSTATION_API_SECRET_KEY',
							'CONF_ENABLE_SELLER_SUBSCRIPTION',
							'CONF_ENABLE_DIGITAL_PRODUCTS',
							'CONF_DIGITAL_FILE_EXT_ALLOWED',
							'CONF_DIGITAL_MAX_FILE_SIZE',
							'CONF_DIGITAL_DOWNLOAD_STATUS',
							'CONF_COD_MIN_WALLET_BALANCE',
							'CONF_ENABLE_COD_SELLER_NOTIFICATION',
							'CONF_DEFAULT_COD_ORDER_STATUS',
							'CONF_COD_PAYMENT_METHOD',
							'CONF_SUBSCRIPTION_EXPIRY_EMAIL',
							'CONF_SUBSCRIPTION_EXPIRY_EMAIL_DAYS'
							
							
						);
		
		//To Validate Credentails	
						 if (isset($data['CONF_SHIPSTATION_API_STATUS']) && $data['CONF_SHIPSTATION_API_STATUS']) {
							require_once (CONF_INSTALLATION_PATH . 'public/includes/APIs/shipstatation/ship.class.php');
							$apiKey = $data['CONF_SHIPSTATION_API_KEY'];
							$apiSecret = $data['CONF_SHIPSTATION_API_SECRET_KEY'];
							$Ship = new Ship();
							if (!$Ship->validateShipstationAccount($apiKey, $apiSecret)) {
								$this->error = $Ship->getError();
								return false;
							}
						}	
							
		foreach($data as $key => $val){
			if(in_array($key, $accept_keys, true)){
				if (!is_array($val)) {
				$this->db->update_from_array('tbl_configurations',array('conf_val'=>$val,'conf_serialized'=>0), array('smt'=>'conf_var = ?', 'vals'=>array($key)));
				}else{
					$this->db->update_from_array('tbl_configurations',array('conf_val'=>serialize($val),'conf_serialized'=>1), array('smt'=>'conf_var = ?', 'vals'=>array($key)));
				}
			}
		}
		return true;
	}
	
	
	function restoreDatabase($backupFile,$concate_path=true) {
			$db_server = CONF_DB_SERVER;
			$db_user = CONF_DB_USER;
			$db_password = CONF_DB_PASS;
			$db_databasename = CONF_DB_NAME;
			$conf_db_path = CONF_DB_BACKUP_DIRECTORY_FULL_PATH;
			$varbsedir = $this->getMySQLVariable("basedir");
			if ($varbsedir == "/")
				$varbsedir = $varbsedir . "usr/";
			else
				$varbsedir = $varbsedir;
			if ($concate_path==true)	
				$backupFile = $conf_db_path . $backupFile;
				
			$sql = "SHOW TABLES FROM $db_databasename";
			if($rs = $this->db->query($sql)){
				  while($row = $this->db->fetch($rs)){
						$table_name=$row["Tables_in_".$db_databasename];
						$this->db->query("DROP TABLE $db_databasename.$table_name");
				  }
			}
			$cmd ="mysql --user=" . $db_user . " --password='" . $db_password . "' " . $db_databasename . " < " . $backupFile;
			system($cmd);
			echo $restore_backup . "<hr>" . $data_str;
	 }
	
	function getMySQLVariable($varname, $scope = "session") {
		   $gv = $this->db->query("show $scope variables");
		   $counter = 0;
		   $val = false;
		   while ($grow = $this->db->fetch($gv)) {
	       if ($grow[0] == $varname) {
    	    	   $val = $grow[1];
	        	   break;
	    	   }
		   }	
		   return $val;
	}
	
	function getDatabaseDirectoryFiles(){
		$dir = dir(CONF_DB_BACKUP_DIRECTORY_FULL_PATH);
		$count = 0;
		while (($file = $dir->read()) !== false  ){ 
			if (!($file=="." || $file==".." ||  $file==".htaccess")){
				$files_arr[]=$file;
			}
		}
		return $files_arr;
	}
	
	function backupDatabase($name, $attachtime = true, $download = false,$backup_path="") {
		   $db_server = CONF_DB_SERVER;
		   $db_user = CONF_DB_USER;
		   $db_password = CONF_DB_PASS;
		   $db_databasename = CONF_DB_NAME;
		   $conf_db_path = $backup_path!=""?$backup_path:CONF_DB_BACKUP_DIRECTORY_FULL_PATH;
		   if ($attachtime) {
		       $backupFile = $conf_db_path . "/" . $name . "_" . date("Y-m-d-H-i-s") . '.sql';
		       $fileToDownload = $name . "_" . date("Y-m-d-H-i-s") . '.sql';
		   } else {
		       $backupFile = $conf_db_path . "/" . $name . '.sql';
		       $fileToDownload = $name . '.sql';
		   }
		   $data_str = "mysqldump --opt --host=" . $db_server . " --user=" . $db_user . " --password=" . $db_password . " " . $db_databasename . " > " . $backupFile;
		   $create_backup = system($data_str);
		   if ($download)
		       $this->download_file($fileToDownload);
			   return true;
		}
		
		function download_file($file) {
		   $download_dir = CONF_DB_BACKUP_DIRECTORY_FULL_PATH; // the folder where the files are stored ('.' if this script is in the same folder)
		   $path = $download_dir  . $file;
		   if (file_exists($path)) {
		       $filename = $download_dir . "/" . $file;
		       header('Content-Description: File Transfer');
		       header("Content-Type: application/force-download");
		       header("Content-Disposition: attachment; filename=\"" . basename($filename) . "\";");
		       header('Content-Length: ' . filesize($filename));
		       readfile("$filename");
		   } else {
		       echo "<center>The file [$file] is not available for download.</center>";
		   }
		}
		
		
		
		function getError() {
    	    return $this->error;
	    }
		
		function recurse_zip($src,&$zip,$path_length) {
			$dir = opendir($src);
			while(false !== ( $file = readdir($dir)) ) {
			if (( $file != '.' ) && ( $file != '..' )) {
				if ( is_dir($src . '/' . $file) ) {
					$this->recurse_zip($src . '/' . $file,$zip,$path_length);
				}else {
					$zip->addFile($src . '/' . $file,substr($src . '/' . $file,$path_length));
					}
				}
			}
			closedir($dir);
		}
		
		//Call this function with argument = absolute path of file or directory name.
		function compress($src,$destination){
				if(substr($src,-1)==='/'){$src=substr($src,0,-1);}
				$arr_src=explode('/',$src);
				$filename=end($src);
				unset($arr_src[count($arr_src)-1]);
				$path_length=strlen(implode('/',$arr_src).'/');
				$f=explode('.',$filename);
				$filename=$f[0];
				$filename=(($filename=='')? $destination.date("d-m-y H-i-s").'.zip' : $filename.'.zip');
				$zip = new ZipArchive;
				$res = $zip->open($filename, ZipArchive::CREATE);
				if($res !== TRUE){
					echo 'Error: Unable to create zip file';
					exit;
				}
				if(is_file($src)){
						$zip->addFile($src,substr($src,$path_length));}
				else{
					if(!is_dir($src)){
						$zip->close();
						@unlink($filename);
						echo 'Error: File not found';
					exit;
				}
				$this->recurse_zip($src,$zip,$path_length);}
				$zip->close();
				return true;
	}
	
	function findandDeleteOldestFile($directory) {
		if ($handle = opendir($directory)) {
			while (false !== ($file = readdir($handle))) {
				$files[] = $file;
			}
	
			foreach ($files as $file) {
				if (is_file($directory.'/'.$file)) {
					$file_date[$file] = filemtime($directory.'/'.$file);
				}
			}
		}
		closedir($handle);
		asort($file_date, SORT_NUMERIC);
		reset($file_date);
		$oldest = key($file_date);
		if (count($file_date)>3){
			return @unlink($directory.'/'.$oldest);
		}
		
	}
		
	
	function is_ssl_enabled() {
    	if ( isset($_SERVER['HTTPS']) ) {
        	if ( 'on' == strtolower($_SERVER['HTTPS']) )
            	return true;
	        if ( '1' == $_SERVER['HTTPS'] )
    	        return true;
	    } elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
    	    return true;
	    }
    	return false;
	}
	
	function send_smtp_test_email($smtp_arr){
		 try {
			 $email = Utilities::sendMailTpl(Settings::getSetting("CONF_ADMIN_EMAIL"), 'smtp_settings', array(
						'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
                    ),'',true,$smtp_arr);
				 return true;
			 }catch (Exception $e) {
				$this->error = $e->getMessage();
		 	}					
	} 
	
	function send_test_email(){
		 try {
			  $email = Utilities::sendMailTpl(Settings::getSetting("CONF_ADMIN_EMAIL"), 'smtp_settings', array(
						'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
                    ),'','TEST');
				 return $email;
			 }catch (Exception $e) {
				$this->error = $e->getMessage();
		 	}					
		}
	
	
}