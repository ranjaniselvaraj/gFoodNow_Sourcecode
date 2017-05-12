<?php
/** 
 * General Front End Configurations
 */
define('CONF_INSTALLATION_PATH', $_SERVER['DOCUMENT_ROOT'] . CONF_WEBROOT_URL);
define('CONF_USER_UPLOADS_PATH', CONF_INSTALLATION_PATH . 'user-uploads/');
define('CONF_APPLICATION_PATH', CONF_INSTALLATION_PATH . 'application/');
/*$conf_preview_webroot_url = CONF_WEBROOT_URL;
if (strpos($_SERVER['REQUEST_URI'],'preview') !== false) {
	$conf_preview_webroot_url=CONF_WEBROOT_URL."preview/";
}*/
if (CONF_URL_REWRITING_ENABLED){
    define('CONF_USER_ROOT_URL', CONF_WEBROOT_URL);
}else{
   	define('CONF_USER_ROOT_URL', CONF_WEBROOT_URL . 'public/');
}

define('CONF_THEME_PATH', CONF_APPLICATION_PATH . 'views/');
define('FRONTPAGESIZE', 12);
define('CONF_POST_COMMENT_STATUS',1);
?>