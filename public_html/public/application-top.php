<?php
set_time_limit(0);
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_DEPRECATED);
if ((substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip' ))){
    ob_start("ob_gzhandler");
}
else {
    ob_start();
}
require_once dirname(__FILE__) . '/includes/conf-common.php';
require_once dirname(__FILE__) . '/includes/conf.php';

ini_set('session.cookie_path', CONF_WEBROOT_URL);

session_start();
define('SYSTEM_INIT', true);
$_SESSION['WYSIWYGFileManagerRequirements'] = realpath(dirname(__FILE__) . '/includes/WYSIWYGFileManagerRequirements.php');
$path = str_replace('/home/maxlib', '', get_include_path());
$path = trim($path, PATH_SEPARATOR);
set_include_path($path);

function add_include_path($path) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $path);
}

add_include_path(CONF_INSTALLATION_PATH . 'library');
add_include_path(CONF_INSTALLATION_PATH . 'public/includes');
//die(get_include_path());
require_once 'includes/functions.php';
//require_once 'site-functions.php';
require_once 'utilities.php';
require_once 'APIs/mailer/PHPMailerAutoload.php';
require_once 'framework-functions.php';
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
/* define configuration variables */
date_default_timezone_set(Settings::getSetting("CONF_TIMEZONE"));
define('CONF_MESSAGE_ERROR_HEADING',Utilities::getLabel('L_Error_Heading'));
/* end configuration variables */
if ((!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on')  && (Settings::getSetting("CONF_USE_SSL")==1)) {
	$redirect = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	Utilities::redirectUser($redirect);
}
$protocol=Settings::getSetting("CONF_USE_SSL")==1?"https":"http";
define('CONF_SERVER_PATH', $protocol."://".$_SERVER['SERVER_NAME'].CONF_WEBROOT_URL);


/*$arr_sort_products_options=array("newproduct_desc"=>Utilities::getLabel('L_Most_Recent'),"featured_desc"=>Utilities::getLabel('L_Featuered'),"bestsellers_desc"=>Utilities::getLabel('L_Bestselling'),"price_asc"=>Utilities::getLabel('L_Price_low_high'),"price_desc"=>Utilities::getLabel('L_Price_high_low'));*/

$arr_sort_products_options=array("rece"=>Utilities::getLabel('L_Most_Recent'),"feat"=>Utilities::getLabel('L_Featuered'),"best"=>Utilities::getLabel('L_Bestselling'),"plth"=>Utilities::getLabel('L_Price_low_high'),"phtl"=>Utilities::getLabel('L_Price_high_low'));


$prod_condition=array("N"=>Utilities::getLabel('L_New'),"U"=>Utilities::getLabel('L_Used'),"R"=>Utilities::getLabel('L_Refurbished'));

$review_status=array("0"=>Utilities::getLabel('L_New_Pending'),"1"=>Utilities::getLabel('L_Approved'),"2"=>Utilities::getLabel('L_Cancelled'));

$status_arr=array(0=>Utilities::getLabel('L_Pending'), '2'=>Utilities::getLabel('L_Cancelled'),'3'=>Utilities::getLabel('L_Approved'));

$return_status_arr=array(0=>Utilities::getLabel('L_Pending'), 1=>Utilities::getLabel('L_Escalated'),'2'=>Utilities::getLabel('L_Refunded'),'3'=>Utilities::getLabel('L_Withdrawn'),'4'=>Utilities::getLabel('L_Cancelled'));

$txn_status_arr=array(0=>Utilities::getLabel('L_Pending'), 1=>Utilities::getLabel('L_Completed'));
$payment_status_arr=array(0=>Utilities::getLabel('L_Pending'), 1=>Utilities::getLabel('L_Paid'), 2=>Utilities::getLabel('L_Cash_on_delivery'), -1=>Utilities::getLabel('L_Cancelled'));
$conf_length_class=array("CM"=>Utilities::getLabel("M_Centimeter"),"MM"=>Utilities::getLabel("M_Millimeters"),"IN"=>Utilities::getLabel("M_Inch"));
$conf_weight_class=array("KG"=>Utilities::getLabel("M_Kilogram"),"GM"=>Utilities::getLabel("M_Grams"),"PN"=>Utilities::getLabel("M_Pounds"),"OU"=>Utilities::getLabel("M_Ounce"),"Ltr"=>Utilities::getLabel("M_Litres"),"Ml"=>Utilities::getLabel("M_Mili_Litres"));

$duration_freq_arr=array('D'=>Utilities::getLabel('L_Daily'), 'W'=>Utilities::getLabel('L_Weekly'), 'M'=>Utilities::getLabel('L_Monthly'));

$duration_subscription_freq_arr=array('D'=>Utilities::getLabel('L_Days'), 'W'=>Utilities::getLabel('L_Weeks'), 'M'=>Utilities::getLabel('L_Months'), 'Y'=>Utilities::getLabel('L_Years'));
$duration_subscription_freq_arr_val=array('D'=>'days', 'W'=>'weeks', 'M'=>'months', 'Y'=>'years');

$supplier_request_status=array(0=>Utilities::getLabel('L_Pending'),1=>Utilities::getLabel('L_Approved'),2=>Utilities::getLabel('L_Cancelled'));

$seo_friendly_url_models=array("brands","products","shops","cms","category");

$binary_status=array(0=>Utilities::getLabel('L_No'), 1=>Utilities::getLabel('L_Yes'));
$active_inactive_status=array(0=>Utilities::getLabel('L_In-Active'), 1=>Utilities::getLabel('L_Active'));
$product_types=array(1=>Utilities::getLabel('L_Physical'), 2=>Utilities::getLabel('L_Digital'));


$arr_page_js=array();
$arr_page_css=array();

$arr_page_js_common = array();
$arr_page_css_common = array();

Syspage::addJs(array(
	'js/respond.min.js',
    'js/jquery-latest.js',
	'js/jquery-ui.js',
	'form-validation.js.php',
    'functions.js.php',
	'js/jQueryRotate.js',
	'js/site-functions.js',
	'js/facebox.js',
	'js/jquery_common.js',
	'js/modernizr-1.7.min.js',
	'js/common.js',
	'js/nav.js',
	'js/mustache.js',
	'js/strength.js',
	
	), true);


	
Syspage::addCss(array(
	'css/bootstrap.min.css',
	'css/skeleton.css',
	'css/nav.css',
	'css/common.css',
	'css/ionicons.css',
	'css/phone.css',
	'css/tablet.css',
	'css/slider.css',
	'css/jquery-ui.css',
	'css/facebox.css',
	'css/easy-responsive-tabs.css',
	'reCaptcha/style.css',
	), true);




$url = $_GET['url']!=""?$_GET['url']:$_SERVER['REQUEST_URI'];
$url=str_replace(substr(CONF_WEBROOT_URL,1,strlen(CONF_WEBROOT_URL)),"",$url);
$url=ltrim($url,"/");
list($controller, $action,$record_id) = explode('/', $url);

$innova_settings = array('width'=>'800', 'height'=>'400', 'groups'=>' [
        ["group1", "", ["FontName", "FontSize", "Superscript", "ForeColor", "BackColor", "FontDialog", "BRK", "Bold", "Italic", "Underline", "Strikethrough", "TextDialog", "Styles", "RemoveFormat"]],
        ["group2", "", ["JustifyLeft", "JustifyCenter", "JustifyRight", "Paragraph", "BRK", "Bullets", "Numbering", "Indent", "Outdent"]],
        ["group4", "", ["CharsDialog", "Line", "BRK", "ImageDialog", "CustomTag", "MyCustomButton"]],
        ["group5", "", ["SearchDialog", "SourceDialog", "BRK", "Undo", "Redo"]]]',
		'fileBrowser'=> '"'.CONF_WEBROOT_URL.'js/LiveEditor/assetmanager/asset.php"'); 

$tpl_for_js_css = ''; // used to include page js and page css

$system_alerts=array();

if(CONF_DEVELOPMENT_MODE) $system_alerts[]='System is in development mode.';

$post = getPostedData();
if ($_SERVER['REQUEST_URI']!=CONF_WEBROOT_URL)
$get = Utilities::getUrlData();


