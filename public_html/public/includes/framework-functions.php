<?php
function unregisterGlobals() {
	if (ini_get('register_globals')) {
		$array = array('_SESSION', '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');
		foreach ($array as $value) {
			foreach ($GLOBALS[$value] as $key => $var) {
				if ($var === $GLOBALS[$key]) {
					unset($GLOBALS[$key]);
				}
			}
		}
	}
}

function callHook($installer=false) {
	global $seo_friendly_url_models;
	$url_alias_controllers=$seo_friendly_url_models;
	$doNotShiftAliasArr=array('image','faqs','blog');
	$get = getQueryStringData();
	if (!isset($get['url']) || ($get['url']=="preview")) $get['url']='';
	$url = $get['url'];
	
	//$url=str_replace(substr(CONF_WEBROOT_URL,1,strlen(CONF_WEBROOT_URL)),"",$url);
	$urlArray = array();
	$urlArray = explode("/",$url);
	$urlParam=isset($urlArray[1]) ? $urlArray[1] : '';
	if ((!in_array($urlArray[0],$doNotShiftAliasArr)) && (in_array($urlParam,$url_alias_controllers)) && (count($urlArray)>2) && (!(strpos($_SERVER['REQUEST_URI'], 'manager') !== false)) ){
		array_shift($urlArray);
	}
	
	$controller = $urlArray[0];
	array_shift($urlArray);
	$action = isset($urlArray[0]) ? $urlArray[0] : '';
	//$r1 = isset($words[0]) ? $words[0] : '';
	//$action = $urlArray[0];
	array_shift($urlArray);
	$queryString = $urlArray;
	
	if ($controller == '') $controller = 'home';
	if ($action == '') $action = 'default_action';

	$controllerName = $controller;
	$controller = ucwords($controller);
	$model = $controller;
	$controller .= 'Controller';
	if (!file_exists(CONF_APPLICATION_PATH . 'controllers/' . strtolower($controller) . '.php') && !file_exists(CONF_INSTALLATION_PATH . 'library/' . strtolower($controller) . '.class.php')){
		/* Make sure that a class from application folder is called and not any php system class for hacking. */
		
		//@todo ehance this area to send user to some 404 page.
		//die('The page you are looking for could not be found.');
        Utilities::show404();
        return;
	}

	if (!class_exists($controller)){
		die('Page you are looking for could not be found.');
	}
	$dispatch = new $controller($model,$controllerName,$action);
	if ((int)method_exists($controller, $action)) {
		if ($installer==false){
			$url_keyword=urldecode(isset($queryString[0])?$queryString[0]:'');
			$url_alias=new Url_alias();
			$url_alias_info = $url_alias->getUrlAliasByKeyword($url_keyword);
		}
		if ($url_alias_info && $url_keyword!="" && (in_array($urlParam,$url_alias_controllers))){
			$record=str_replace($controllerName."_id=","",$url_alias_info["url_alias_query"]);
			call_user_func_array(array($dispatch,$action),array($record));
		}
		else{
			call_user_func_array(array($dispatch,$action),$queryString);
		}
			
	} else {
        Utilities::show404();
        return;
	}
}

spl_autoload_register('Library_Autoload');

function Library_Autoload($clname){
    switch ($clname){
        case 'Applicationconstants':
            require_once CONF_INSTALLATION_PATH . 'public/includes/applicationconstants.php';
            break;
        case 'Message':
            require_once '_classes/message.cls.php';
            break;
        case 'Database':
            require_once '_classes/db.mysqli.php';
            break;
        case 'FormField':
            require_once '_classes/form-field.cls.php';
            break;
        case 'SearchBase':
            require_once '_classes/search-base.cls.php';
            break;
        case 'SearchCondition':
            require_once '_classes/search-condition.cls.php';
            break;
        case 'Form':
            require_once '_classes/form.cls.php';
            break;
        case 'FormFieldRequirement':
            require_once '_classes/form-field-requirement.cls.php';
            break;
        case 'TableRecord':
            require_once '_classes/table-record.cls.php';
            break;
        case 'Record':
            require_once '_classes/record-base.cls.php';
            break;
        case 'imageResize':
        case 'ImageResize':
            require_once '_classes/image-resize.cls.php';
            break;
        case 'HtmlElement':
            require_once '_classes/html-element.cls.php';
            break;
		case 'Currencies':
            require_once CONF_INSTALLATION_PATH . 'public/includes/currencies.php';
            break;
		case 'Files':
            require_once CONF_INSTALLATION_PATH . 'public/includes/files.php';
            break;	            	
            /*for framework*/
        	
        default:
            if (file_exists(CONF_INSTALLATION_PATH . 'library/' . strtolower($clname) . '.class.php')){
            	require_once CONF_INSTALLATION_PATH . 'library/' . strtolower($clname) . '.class.php';
			} else if (file_exists(CONF_APPLICATION_PATH . 'controllers/' . strtolower($clname) . '.php')){
				require_once CONF_APPLICATION_PATH . 'controllers/' . strtolower($clname) . '.php';
			} else if (file_exists(CONF_APPLICATION_PATH . 'models/' . strtolower($clname) . '.php')){
				require_once CONF_APPLICATION_PATH . 'models/' . strtolower($clname) . '.php';
			} else {
            /* if current application path is not the application folder at installtion path
             * let us try to look into application at root if that exists
            *  */
            $root_application_path = CONF_INSTALLATION_PATH . 'application/';
            if ($root_application_path != CONF_APPLICATION_PATH){
                if (file_exists($root_application_path . 'models/' . strtolower($clname) . '.php')){
                    require_once $root_application_path . 'models/' . strtolower($clname) . '.php';
                }
            }
        }
        break;
        	
    }
}