<?php
ob_start();
require_once dirname(__FILE__) . '/includes/conf-common.php';
require_once dirname(__FILE__) . '/includes/conf.php';
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_DEPRECATED);
define('SYSTEM_INIT', true);
define('CRON_INIT', true);
$path = str_replace('/home/maxlib', '', get_include_path());
$path = trim($path, PATH_SEPARATOR);
set_include_path($path);
function add_include_path($path) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $path);
}
add_include_path(CONF_INSTALLATION_PATH . 'library');
add_include_path(CONF_INSTALLATION_PATH . 'public/includes');
require_once 'includes/functions.php';
require_once 'includes/functions/file.php';
require_once 'utilities.php';
require_once 'framework-functions.php';
$db_config=array('server'=>CONF_DB_SERVER,'user'=>CONF_DB_USER,'pass'=>CONF_DB_PASS,'db'=>CONF_DB_NAME);
$db=new Database(CONF_DB_SERVER, CONF_DB_USER, CONF_DB_PASS, CONF_DB_NAME);
$db->query("SET SESSION sql_mode = 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");
//if (Settings::getSetting("CONF_AUTO_RESTORE_ON")){
	$f = fopen('database-restore-progress.txt', 'w');
	$rs = fwrite($f, time());
	fclose($f);
	try{
		$db = &Syspage::getdb();
		if(!isset($_GET['passkey']) || $_GET['passkey']!=CONF_CRONSCRIPT_PASSKEY) {		
			throw new Exception('Access denied!!');
		}
		include (dirname(__FILE__).'/cron-jobs/auto-restore-db-uploads.php');
	} catch(Exception $e) {
	   echo $e->getMessage();
	}
	$rs_txt = ob_get_clean();
	echo nl2br($rs_txt);
	@unlink('database-restore-progress.txt');
/*}else{
	die("Auto restore is disabled from admin.");
}*/