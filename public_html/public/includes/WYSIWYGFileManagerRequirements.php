<?php 
if(session_id() === ""){
	ob_start();
	session_start();
}

require_once realpath(dirname(__FILE__) . '/conf-common.php');
function checkValidUserForFileManager(){  	
	//$admin = (isset($_SESSION['admin_logged']) && is_numeric($_SESSION['admin_logged']) && intval($_SESSION['admin_logged']) > 0 && strlen(trim($_SESSION['admin_username'])) >= 4);
	$admin = (isset($_SESSION['logged_admin']['admin_logged']) && is_numeric($_SESSION['logged_admin']['admin_logged']) && intval($_SESSION['logged_admin']['admin_logged']) > 0 && strlen(trim($_SESSION['logged_admin']['admin_username'])) >= 4);
	
	$seller = /* 1 ||  */(isset($_SESSION['logged_user']['user_id']) && is_numeric($_SESSION['logged_user']['user_id']) && intval($_SESSION['logged_user']['user_id']) > 0 && (strlen(trim($_SESSION['logged_user']['username'])) >= 4) && is_numeric($_SESSION['logged_user']['type']) && ($_SESSION['logged_user']['type']==CONF_SELLER_USER_TYPE || $_SESSION['logged_user']['type']==CONF_BUYER_SELLER_USER_TYPE) );	
	
	if( !($admin || $seller) ){
		echo '<br/>You do not have access to file manager, Please contact admin!';
		exit(0);
	}
	
	global $is_admin_for_file_manager;
	global $is_seller_for_file_manager;
	if($admin){
		$is_admin_for_file_manager = 1;
	}else if($seller){
		$is_admin_for_file_manager = 1;
		$is_seller_for_file_manager = 1;
	}else{
		/*exit(0)*/
	}
}
checkValidUserForFileManager();

if($is_seller_for_file_manager){
	$path_for_images = "/user-uploads/text-editor/".$_SESSION['logged_user']['user_id'].'_'.$_SESSION['logged_user']['username']; /* Relative to URI Root - This is for Innova */
}else if($is_admin_for_file_manager){	
	$path_for_images = "/user-uploads/text-editor"; /* Relative to URI Root - This is for Innova */	
}else{
	exit(0);	
}
if(!file_exists($path_for_images)){	
	//create the folder
	//$dir_to_create = realpath(dirname(__FILE__). '/../../').$path_for_images;
	//@mkdir($dir_to_create, 0777, true);
	//create the folder
	$dir_to_create = realpath(dirname(__FILE__). '/../../').$path_for_images;
	if(!file_exists($dir_to_create)){
		mkdir($dir_to_create, 0777, true);
	}

}