<?php
/**
 * General Front End Configurations
 */
define('CONF_INSTALLATION_PATH', $_SERVER['DOCUMENT_ROOT'] . CONF_WEBROOT_URL);
define('CONF_USER_UPLOADS_PATH', CONF_INSTALLATION_PATH . 'user-uploads/');
define('CONF_APPLICATION_PATH', CONF_INSTALLATION_PATH . 'admin-application/');
define('CONF_DB_BACKUP_DIRECTORY', 'database-backups');
define('CONF_DB_BACKUP_DIRECTORY_FULL_PATH', CONF_USER_UPLOADS_PATH . CONF_DB_BACKUP_DIRECTORY.'/');
if (CONF_URL_REWRITING_ENABLED){
    define('CONF_USER_ROOT_URL', CONF_WEBROOT_URL . 'manager/');
}
else {
    define('CONF_USER_ROOT_URL', CONF_WEBROOT_URL . 'public/manager/');
}
define('CONF_THEME_PATH', CONF_APPLICATION_PATH . 'views/');
define('CONF_DATE_FIELD_TRIGGER_IMG', CONF_WEBROOT_URL . 'images/iocn_clender.gif');
//define('CONF_FCKEDITOR_PATH', CONF_WEBROOT_URL . 'fckeditor/');
define('CONF_HTML_EDITOR', 'innova');

?>