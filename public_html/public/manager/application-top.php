<?php
set_time_limit(0);
ini_set('display_errors', 0);
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip' && $donotcompress !== true)) {
    ob_start("ob_gzhandler");
} else {
    ob_start();
}
require_once dirname(dirname(__FILE__)) . '/includes/conf-common.php';
require_once dirname(__FILE__) . '/includes/conf.php';
require_once dirname(__FILE__) . '/includes/conf-permissions.php';
ini_set('session.cookie_path', CONF_WEBROOT_URL);
session_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_DEPRECATED);
//error_reporting(E_ALL);
define('SYSTEM_INIT', true);
$_SESSION['WYSIWYGFileManagerRequirements'] = realpath(dirname(__FILE__) . '/../includes/WYSIWYGFileManagerRequirements.php');
$path = str_replace('/home/maxlib', '', get_include_path());
$path = trim($path, PATH_SEPARATOR);
set_include_path($path);
set_include_path(get_include_path() . PATH_SEPARATOR . CONF_INSTALLATION_PATH . 'library');
require_once 'includes/functions.php';
require_once dirname(dirname(__FILE__)) . '/includes/utilities.php';
//require_once dirname(dirname(__FILE__)) . '/includes/site-functions.php';
require_once dirname(dirname(__FILE__)) . '/includes/APIs/mailer/PHPMailerAutoload.php';
require_once dirname(dirname(__FILE__)) . '/includes/framework-functions.php';
require_once dirname(dirname(__FILE__)) . '/includes/breadcrumbs.php';
require_once '_classes/message.cls.php';
$db_config=array(
    'server'=>CONF_DB_SERVER,
    'user'=>CONF_DB_USER,
    'pass'=>CONF_DB_PASS,
    'db'=>CONF_DB_NAME);
$db=new Database(CONF_DB_SERVER, CONF_DB_USER, CONF_DB_PASS, CONF_DB_NAME);
$db->query("SET SESSION sql_mode = 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");
define('CONF_DATE_FORMAT_PHP',Settings::getSetting("CONF_DATE_FORMAT_PHP"));
define('CONF_TIMEZONE',Settings::getSetting("CONF_TIMEZONE"));
define('CONF_RECAPTACHA_SECRETKEY',Settings::getSetting("CONF_RECAPTCHA_SECRET_KEY"));
define('CONF_RECAPTACHA_SITEKEY',Settings::getSetting("CONF_RECAPTCHA_SITE_KEY"));
define('CONF_FORM_REQUIRED_STAR_POSITION','after');
define('CONF_FORM_REQUIRED_STAR_WITH','caption');
$crObj=new Currency();
$currency_info = $crObj->getCurrencyByCode(Settings::getSetting("CONF_CURRENCY"));
define('CONF_CURRENCY_SYMBOL_LEFT',$currency_info['currency_symbol_left']);
define('CONF_CURRENCY_SYMBOL_RIGHT',$currency_info['currency_symbol_right']);
define('CONF_CURRENCY_DECIMAL_PLACES',$currency_info['currency_decimal']);
define('CONF_CURRENCY_SYMBOL',empty($currency_info['currency_symbol_right'])?$currency_info['currency_symbol_left']:$currency_info['currency_symbol_right']);
$currency=new Currencies(Settings::getSetting("CONF_CURRENCY"));

$protocol=Settings::getSetting("CONF_USE_SSL")==1?"https":"http";
define('CONF_SERVER_PATH', $protocol."://".$_SERVER['SERVER_NAME'].CONF_WEBROOT_URL);

date_default_timezone_set(CONF_TIMEZONE);
/* end configuration variables */
if ((!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on')  && (Settings::getSetting("CONF_USE_SSL")==1)) {
	$redirect = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	Utilities::redirectUser($redirect);
}
/* end configuration variables */
$arr_sort_products_options=array("newproduct_desc"=>Utilities::getLabel('L_Most_Recent'),"featured_desc"=>Utilities::getLabel('L_Featuered'),"bestsellers_desc"=>Utilities::getLabel('L_Bestselling'),"price_asc"=>Utilities::getLabel('L_Price_low_high'),"price_desc"=>Utilities::getLabel('L_Price_high_low'),"product_asc"=>Utilities::getLabel('L_Name_a_z'),"product_desc"=>Utilities::getLabel('L_name_z_a'));

$prod_condition=array("N"=>Utilities::getLabel('L_New'),"U"=>Utilities::getLabel('L_Used'),"R"=>Utilities::getLabel('L_Refurbished'));
$review_status=array("0"=>Utilities::getLabel('L_New_Pending'),"1"=>Utilities::getLabel('L_Approved'),"2"=>Utilities::getLabel('L_Cancelled'));

$status_arr=array(0=>Utilities::getLabel('L_Pending'),'2'=>Utilities::getLabel('L_Cancelled'),'3'=>Utilities::getLabel('L_Approved'));

$return_status_arr=array(0=>Utilities::getLabel('L_Pending'), 1=>Utilities::getLabel('L_Escalated'),'2'=>Utilities::getLabel('L_Refunded'),'3'=>Utilities::getLabel('L_Withdrawn'),'4'=>Utilities::getLabel('L_Cancelled'));
$txn_status_arr=array(0=>Utilities::getLabel('L_Pending'), 1=>Utilities::getLabel('L_Completed'));
$payment_status_arr=array(0=>Utilities::getLabel('L_Pending'), 1=>Utilities::getLabel('L_Paid'),2=>Utilities::getLabel('L_Cash_on_delivery'), -1=>Utilities::getLabel('L_Cancelled'));
$supplier_approval_request_status=array("0"=>Utilities::getLabel('L_New_Pending'),"1"=>Utilities::getLabel('L_Approved'),"2"=>Utilities::getLabel('L_Cancelled'));

$supplier_request_status=array(0=>Utilities::getLabel('L_Pending'),1=>Utilities::getLabel('L_Approved'),2=>Utilities::getLabel('L_Cancelled'));

$button_status_arr=array(Utilities::getLabel('L_Pending')=>'label-info',Utilities::getLabel('L_Cash_on_delivery')=>'label-primary',Utilities::getLabel('L_In_Process')=>'label-primary',Utilities::getLabel('L_Completed')=>'label-success',Utilities::getLabel('L_Cancelled')=>'label-danger',Utilities::getLabel('L_Refunded')=>'label-warning',Utilities::getLabel('L_Paid')=>'label-success',Utilities::getLabel('L_Approved')=>'label-success');

$conf_length_class=array("CM"=>Utilities::getLabel("M_Centimeter"),"MM"=>Utilities::getLabel("M_Millimeters"),"IN"=>Utilities::getLabel("M_Inch"));
$conf_weight_class=array("KG"=>Utilities::getLabel("M_Kilogram"),"GM"=>Utilities::getLabel("M_Grams"),"PN"=>Utilities::getLabel("M_Pounds"),"OU"=>Utilities::getLabel("M_Ounce"),"Ltr"=>Utilities::getLabel("M_Litres"),"Ml"=>Utilities::getLabel("M_Mili_Litres"));

$duration_freq_arr=array('D'=>Utilities::getLabel('L_Daily'), 'W'=>Utilities::getLabel('L_Weekly'), 'M'=>Utilities::getLabel('L_Monthly'));

$duration_subscription_freq_arr=array('D'=>Utilities::getLabel('L_Days'), 'W'=>Utilities::getLabel('L_Weeks'), 'M'=>Utilities::getLabel('L_Months'), 'Y'=>Utilities::getLabel('L_Years'));
$duration_subscription_freq_arr_val=array('D'=>'days', 'W'=>'weeks', 'M'=>'months', 'Y'=>'years');

$binary_status=array(0=>Utilities::getLabel('L_No'), 1=>Utilities::getLabel('L_Yes'));
$active_inactive_status=array(0=>Utilities::getLabel('L_In-Active'), 1=>Utilities::getLabel('L_Active'));
$product_types=array(1=>Utilities::getLabel('L_Physical'), 2=>Utilities::getLabel('L_Digital'));

$seo_friendly_url_models = array();

$pp_adaptive_chained_payment_status = array(
				"-1"=>"No Secondary Receiver",
				"0"=>"Pending",
				"1"=>"Executed",
				"2"=>"Execution Failed",
				"3"=>"Execution Cancelled",
				"4"=>"Executed Successfully",
				) 
			;

$arr_page_js=array();
$arr_page_css=array();

$arr_page_js_common = array();
$arr_page_css_common = array();

Syspage::addJs(array(
	'../js/admin/jquery-latest.js',
	'../js/jquery-ui/jquery-ui.js',
	'../js/admin/common.js',
	'../js/admin/jquery_common.js',
	'functions.js.php', 
    'form-validation.js.php',
	'../js/jQueryRotate.js',
	'../js/facebox.js',
	'../js/site-functions.js',
	'../js/calendar.js',
	'../js/strength.js',
	'../js/jquery.datetimepicker.js',
	), true);


	
Syspage::addCss(array(
    '../css/admin/style.css',
	'../css/admin/common.css',
    '../css/facebox.css',
	'../reCaptcha/style.css',
	'../css/magnific-popup.css',
	),
    true);	

$innova_settings = array('width'=>'850', 'height'=>'400', 'groups'=>' [
        ["group1", "", ["FontName", "FontSize", "Superscript", "ForeColor", "BackColor", "FontDialog", "BRK", "Bold", "Italic", "Underline", "Strikethrough", "TextDialog", "Styles", "RemoveFormat"]],
        ["group2", "", ["JustifyLeft", "JustifyCenter", "JustifyRight", "Paragraph", "BRK", "Bullets", "Numbering", "Indent", "Outdent"]],
		["group4", "", ["CharsDialog", "Line", "BRK", "ImageDialog", "CustomTag", "MyCustomButton"]],
        ["group5", "", ["SearchDialog", "SourceDialog", "BRK", "Undo", "Redo"]]]',
		'fileBrowser'=> '"'.CONF_WEBROOT_URL.'js/LiveEditor/assetmanager/asset.php"');  
    
$tpl_for_js_css = ''; // used to include page js and page css

$system_alerts=array();
