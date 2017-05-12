<?php

function writeMetaTags(){
	$db = &Syspage::getDb();
	$url=Utilities::currentPageURL();
	//$url=str_replace(CONF_SERVER_PATH,"",$url);
	$url=str_replace(':'.$_SERVER["SERVER_PORT"],"",$url);	
	$url=str_replace(CONF_SERVER_PATH,"",$url); 
	list($url_keywords,$controller, $action, $record_id) = explode('/', $url);
	$url_alias=new Url_alias();
	$url_alias_info = $url_alias->getUrlAliasByKeyword(urldecode($record_id));
	if ($url_alias_info){
		$record_id=str_replace($controller."_id=","",$url_alias_info["url_alias_query"]);
	}
	switch($controller){
		case "cms":
			$cmsPageObj=new Cms();
			$page=$cmsPageObj->getData($record_id);
			if ($page):
				$page_title=$page["page_meta_title"];
				$page_meta_keywords=$page["page_meta_keywords"];
				$page_meta_description=$page["page_meta_desc"];	
			endif;	
		break;
		case "brands":
			$brandObj=new Brands();
			$brand=$brandObj->getData($record_id);
			if ($brand):
				$page_title=$brand["brand_meta_title"];
				$page_meta_keywords=$brand["brand_meta_keywords"];
				$page_meta_description=$brand["brand_meta_desc"];	
			endif;	
		break;
		case "category":
			$categoryObj=new Categories();
			$category=$categoryObj->getData($record_id);
			if ($category):
				$page_title=$category["category_meta_title"];
				$page_meta_keywords=$category["category_meta_keywords"];
				$page_meta_description=$category["category_meta_description"];	
			endif;	
		break;
		case "shops":
			$shopObj=new Shops();
			$shop=$shopObj->getData($record_id);
			if ($shop):
				$page_title=$shop["shop_page_title"];
				$page_meta_keywords=$shop["shop_meta_keywords"];
				$page_meta_description=$shop["shop_meta_description"];	
			endif;	
		break;
		case "products":
			$prodObj=new Products();
			$product=$prodObj->getData($record_id);
			if ($product):
				$page_title=$product["prod_meta_title"];
				$page_meta_keywords=$product["prod_meta_keywords"];
				$page_meta_description=$product["prod_meta_description"];	
			endif;	
		break;
		
	}
	$display_page_title=$page_title!=""?$page_title:Settings::getSetting("CONF_PAGE_TITLE");
	$display_meta_keywords=$page_meta_keywords!=""?$page_meta_keywords:Settings::getSetting("CONF_META_KEYWORD");
	$display_meta_desc=$page_meta_description!=""?$page_meta_description:Settings::getSetting("CONF_META_DESCRIPTION");
	
	echo ('<title>'.$display_page_title.'</title>');
	echo ('<meta name="description" content="'.$display_meta_desc.'" />');
	echo ('<meta name="keywords" content="'.$display_meta_keywords.'" />');
}


function createButton($caption){
    return $caption;
}

function captchaImgUrl() {
    return CONF_WEBROOT_URL . 'securimage/securimage_show.php';
}

function recaptchaImgUrl() {
    return CONF_WEBROOT_URL . 'reCaptcha/get_captcha.php';
}

function verifyCaptcha($fld_name='g-recaptcha-response') {

  require_once (CONF_INSTALLATION_PATH . 'public/includes/APIs/ReCaptcha/src/autoload.php');
  if (!empty(CONF_RECAPTACHA_SITEKEY) && !empty(CONF_RECAPTACHA_SECRETKEY)){
  	$recaptcha = new \ReCaptcha\ReCaptcha(CONF_RECAPTACHA_SECRETKEY);
	  $post = Syspage::getPostedVar();
	  $resp = $recaptcha->verify($post[$fld_name], $_SERVER['REMOTE_ADDR']);  
	  return $resp->isSuccess()==true?true:false;
  }else{
	  return true;
  }
  //return $_SESSION['random_number']==$post[$fld_name]?true:false;
}


function printArray() {
    if (func_num_args()) {
        echo '<pre>';
        foreach (func_get_args() as $arr) {
            print_r($arr);
        }
        echo '</pre>';
    }
}

function printArrayToString() {
    ob_start();
    foreach(func_get_args() as $arr) {
        printArray($arr);
    }
    return ob_get_clean();
}

function getLabel($key, $namespace=''){
    if ($key=='') return ;
    global $lang_array;
    $key_original = $key;
    $key = strtoupper($key);
    if (isset($lang_array[$key])) return $lang_array[$key];

    $db = &Syspage::getdb();
    $val = '';
    $rs = $db->query("SELECT * FROM tbl_language_labels WHERE label_key = " . $db->quoteVariable($key) . "");
    if ($db->total_records($rs)>0 != false) {
        $row = $db->fetch($rs);
		$val = $row['label_caption_'.Settings::getSetting("CONF_LANGUAGE")];
    } else {
        $arr = explode('_', $key_original);
        array_shift($arr);
        //array_shift($arr);
        //$val = ucwords(strtolower(implode(' ', $arr) ) );
        $val = implode(' ', $arr);

        $db->insert_from_array('tbl_language_labels', array(
            'label_key' => $key,
            'label_caption_en' => $val,
			'label_caption_es' => $val,
            ));
    }
    return $lang_array[$key] = strip_javascript($val);
}

function renderText($val, $type='string', $default='N A') {
    switch($type) {
        case 'string':
            $val = trim($val);
            if ($val=='') return $default;
            else return $val;
            break;
        case 'array':
            if (!count($val)) return $default;
            else return implode(', ', $val);
            break;
    }
}

function wordSplit($str, $words = 25) {
    $arr = preg_split("/[\s]+/",  $str, $words+1);
    $arr = array_slice($arr, 0, $words);
    return join(' ', $arr);
}

function getStringOnCount($count, $text1, $text2) {
    if ($count==1) return $text1;
    else return $text2;
}

function renderHtml($content='',$stripJs=false) {
    $str=html_entity_decode(htmlspecialchars_decode($content));
	$str=($stripJs==true)?strip_javascript($str):$str;
	return $str;
}

function strip_javascript($content='') {
	$javascript = '/<script[^>]*?>.*?<\/script>/si';
    $noscript = '';
    return preg_replace($javascript, $noscript, $content);
}

function myTruncateCharacters($string, $limit, $break = " ", $pad = "...") {
    if (strlen($string) <= $limit)
        return $string;

    $string = substr($string, 0, $limit);
    if (false !== ($breakpoint = strrpos($string, $break))) {
        $string = substr($string, 0, $breakpoint);
    }
    return $string . $pad;
}