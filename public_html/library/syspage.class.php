<?php
class Syspage{
	static function &getdb(){
		global $db;
		return $db;
	}
	
	static function addJs($file, $common = false){
	    if (is_array($file)){
	        foreach ($file as $fl){
	            self::addJs($fl, $common);
	        }
	        return ;
	    }
	    if ($common){
	        global $arr_page_js_common;
	        if (!in_array($file, $arr_page_js_common)) $arr_page_js_common[] = $file;
	    }
	    else {
	        global $arr_page_js;
	        if (!in_array($file, $arr_page_js)) $arr_page_js[] = $file;
	    }
	}
	
	static function addCss($file, $common = false){
	    if (is_array($file)){
	        foreach ($file as $fl){
	            self::addCss($fl, $common);
	        }
	        return ;
	    }
	    if ($common){
	        global $arr_page_css_common;
	        if (!in_array($file, $arr_page_css_common)) $arr_page_css_common[] = $file;
	    }
	    else {
	        global $arr_page_css;
	        if (!in_array($file, $arr_page_css)) $arr_page_css[] = $file;
	    }
	}
	
	static function getPostedVar($key=null){
	    global $post;
	    if ($key == null) return $post;
	    return $post[$key];
	}
	
	static function getJsCssIncludeHtml($merge_files = true){
	    global $arr_page_css_common;
	    global $arr_page_css;
	    global $arr_page_js_common;
	    global $arr_page_js;
	    
	    global $tpl_for_js_css;
	    
	    $str = '';
	    
	    $use_root_url = '';
	    if (CONF_URL_REWRITING_ENABLED){
	        $use_root_url = CONF_WEBROOT_URL . 'public/' . substr(CONF_USER_ROOT_URL, strlen(CONF_WEBROOT_URL));
	    }
	    else {
	        $use_root_url = CONF_USER_ROOT_URL;
	    }
	    
	    if (count($arr_page_css_common) > 0){
	        $last_updated = 0;
	        foreach ($arr_page_css_common as $val){
	            $temp_pth=(substr($val, 0, 1)=='/')?$_SERVER['DOCUMENT_ROOT'] . $val:realpath($val);
	            $time = filemtime($temp_pth);
	            if ($time > $last_updated) $last_updated = $time;
	            
	            if (!$merge_files){
	                $str .= '<link  rel="stylesheet" type="text/css" href="' . Utilities::generateUrl('jscss', 'css', array(), $use_root_url, false) . '&f=' . rawurlencode($val) . '&min=0&sid=' . $last_updated . '" />' . "\n";
	            }
	        }
	        
	        if ($merge_files) $str .= '<link  rel="stylesheet" type="text/css" href="' . Utilities::generateUrl('jscss', 'css', array(), $use_root_url, false) . '&f=' . rawurlencode(implode(',', $arr_page_css_common)) . '&min=1&sid=' . $last_updated . '" />' . "\n";
	    }
	    
	    if (count($arr_page_css) > 0){
	        $last_updated = 0;
	        foreach ($arr_page_css as $val){
	            $temp_pth=(substr($val, 0, 1)=='/')?$_SERVER['DOCUMENT_ROOT'] . $val:realpath($val);
	            $time = filemtime($temp_pth);
	            if ($time > $last_updated) $last_updated = $time;
	            
	            if (!$merge_files) $str .= '<link  rel="stylesheet" type="text/css" href="' . Utilities::generateUrl('jscss', 'css', array(), $use_root_url, false) . '&f=' . rawurlencode($val) . '&min=0&sid=' . $last_updated . '" />' . "\n";
	        }
	        if ($merge_files) $str .= '<link  rel="stylesheet" type="text/css" href="' . Utilities::generateUrl('jscss', 'css', array(), $use_root_url, false) . '&f=' . rawurlencode(implode(',', $arr_page_css)) . '&min=1&sid=' . $last_updated . '" />' . "\n";
	    }
	    
	    if ($tpl_for_js_css != ''){
	        $pathinfo = pathinfo($tpl_for_js_css);
	         
	        $temp_pth = realpath(CONF_THEME_PATH . '/' . $pathinfo['dirname'] . '/page-css/' . $pathinfo['filename'] . '.css');
	        if (file_exists($temp_pth)){
	            $str .= '<link  rel="stylesheet" type="text/css" href="' . Utilities::generateUrl('pagejsandcss', 'css', array($pathinfo['dirname'], $pathinfo['filename']), $use_root_url, false) . '&sid=' . filemtime($temp_pth) . '" />' . "\n";
	        }
	    }
	    
	    $str .= '<script  type="text/javascript">
	    	var webroot="' . CONF_WEBROOT_URL . '";
	    	var userwebroot = "' . CONF_USER_ROOT_URL . '";
	    	var url_rewriting_enabled = ' . (CONF_URL_REWRITING_ENABLED?'1':'0') . ';
			var js_error_file_size="' . Utilities::getLabel('M_JS_ERROR_FILE_SIZE') . '";
			var js_error_file_extensions="' . Utilities::getLabel('M_JS_ERROR_FILE_EXTENSION') . '";
	    	</script>' . "\r\n";
	    
	    if (count($arr_page_js_common) > 0){
	        $last_updated = 0;
	        foreach ($arr_page_js_common as $val){
	            $temp_pth=(substr($val, 0, 1)=='/')?$_SERVER['DOCUMENT_ROOT'] . $val:realpath($val);
	            $time = filemtime($temp_pth);
	            if ($time > $last_updated) $last_updated = $time;
	            
	            if (!$merge_files) $str .= '<script  type="text/javascript" language="javascript" src="' . Utilities::generateUrl('jscss', 'js', array(), $use_root_url, false) . '&f=' . rawurlencode($val) . '&min=0&sid=' . $last_updated . '"></script>' . "\n"; 
	        }
	        if ($merge_files) $str .= '<script  type="text/javascript" language="javascript" src="' . Utilities::generateUrl('jscss', 'js', array(), $use_root_url, false) . '&f=' . rawurlencode(implode(',', $arr_page_js_common)) . '&min=1&sid=' . $last_updated . '"></script>' . "\n";
	    }
	     
	    if (count($arr_page_js) > 0){
	        $last_updated = 0;
	        foreach ($arr_page_js as $val){
	            $temp_pth=(substr($val, 0, 1)=='/')?$_SERVER['DOCUMENT_ROOT'] . $val:realpath($val);
	            $time = filemtime($temp_pth);
	            if ($time > $last_updated) $last_updated = $time;
	            
	            if (!$merge_files) $str .= '<script  type="text/javascript" language="javascript" src="' . Utilities::generateUrl('jscss', 'js', array(), $use_root_url, false) . '&f=' . rawurlencode($val) . '&min=0&sid=' . $last_updated . '"></script>' . "\n";
	        }
	        if ($merge_files) $str .= '<script  type="text/javascript" language="javascript" src="' . Utilities::generateUrl('jscss', 'js', array(), $use_root_url, false) . '&f=' . rawurlencode(implode(',', $arr_page_js)) . '&min=1&sid=' . $last_updated . '"></script>' . "\n";
	    }
	    
	    if ($tpl_for_js_css != ''){
	        $temp_pth = realpath(CONF_THEME_PATH . '/' . $pathinfo['dirname'] . '/page-js/' . $pathinfo['filename'] . '.js');
	        if (file_exists($temp_pth)){
	            $str .= '<script  type="text/javascript" language="javascript" src="' . Utilities::generateUrl('pagejsandcss', 'js', array($pathinfo['dirname'], $pathinfo['filename']), $use_root_url, false) . '&sid=' . filemtime($temp_pth) . '"></script>' . "\n";
	        }
	    }
	    
	    return $str;
	}
	
	
	static function getCssIncludeHtml($merge_files = true){
	    global $arr_page_css_common;
	    global $arr_page_css;
	    
	    global $tpl_for_js_css;
	    
	    $str = '';
	    
	    $use_root_url = '';
	    if (CONF_URL_REWRITING_ENABLED){
	        $use_root_url = CONF_WEBROOT_URL . 'public/' . substr(CONF_USER_ROOT_URL, strlen(CONF_WEBROOT_URL));
	    }
	    else {
	        $use_root_url = CONF_USER_ROOT_URL;
	    }
	    
	    if (count($arr_page_css_common) > 0){
	        $last_updated = 0;
	        foreach ($arr_page_css_common as $val){
	            $temp_pth=(substr($val, 0, 1)=='/')?$_SERVER['DOCUMENT_ROOT'] . $val:realpath($val);
	            $time = filemtime($temp_pth);
	            if ($time > $last_updated) $last_updated = $time;
	            
	            if (!$merge_files){
	                $str .= '<link  rel="stylesheet" type="text/css" href="' . Utilities::generateUrl('jscss', 'css', array(), $use_root_url, false) . '&f=' . rawurlencode($val) . '&min=0&sid=' . $last_updated . '" />' . "\n";
					
	            }
	        }
	        
	        if ($merge_files) $str .= '<link  rel="stylesheet" type="text/css" href="' . Utilities::generateUrl('jscss', 'css', array(), $use_root_url, false) . '&f=' . rawurlencode(implode(',', $arr_page_css_common)) . '&min=1&sid=' . $last_updated . '" />' . "\n";
			
	    }
	    
	    if (count($arr_page_css) > 0){
	        $last_updated = 0;
	        foreach ($arr_page_css as $val){
	            $temp_pth=(substr($val, 0, 1)=='/')?$_SERVER['DOCUMENT_ROOT'] . $val:realpath($val);
	            $time = filemtime($temp_pth);
	            if ($time > $last_updated) $last_updated = $time;
	            
	            if (!$merge_files) $str .= '<link  rel="stylesheet" type="text/css" href="' . Utilities::generateUrl('jscss', 'css', array(), $use_root_url, false) . '&f=' . rawurlencode($val) . '&min=0&sid=' . $last_updated . '" />' . "\n";
				
	        }
	        if ($merge_files) $str .= '<link  rel="stylesheet" type="text/css" href="' . Utilities::generateUrl('jscss', 'css', array(), $use_root_url, false) . '&f=' . rawurlencode(implode(',', $arr_page_css)) . '&min=1&sid=' . $last_updated . '" />' . "\n";
			
	    }
	    
	    if ($tpl_for_js_css != ''){
	        $pathinfo = pathinfo($tpl_for_js_css);
	         
	        $temp_pth = realpath(CONF_THEME_PATH . '/' . $pathinfo['dirname'] . '/page-css/' . $pathinfo['filename'] . '.css');
	        if (file_exists($temp_pth)){
	            $str .= '<link  rel="stylesheet" type="text/css" href="' . Utilities::generateUrl('pagejsandcss', 'css', array($pathinfo['dirname'], $pathinfo['filename']), $use_root_url, false) . '&sid=' . filemtime($temp_pth) . '" />' . "\n";
	        }
	    }
	    return $str;
	}
	
	static function getJsIncludeHtml($merge_files = true){
	    global $arr_page_js_common;
	    global $arr_page_js;
	    
		//printArray($arr_page_js);
		//die();
	    global $tpl_for_js_css;
		
	    
	    $str = '';
	    
	    $use_root_url = '';
	    if (CONF_URL_REWRITING_ENABLED){
	        $use_root_url = CONF_WEBROOT_URL . 'public/' . substr(CONF_USER_ROOT_URL, strlen(CONF_WEBROOT_URL));
	    }
	    else {
	        $use_root_url = CONF_USER_ROOT_URL;
	    }
	    
	    $str .= '<script  type="text/javascript">
	    	var webroot="' . CONF_WEBROOT_URL . '";
	    	var userwebroot = "' . CONF_USER_ROOT_URL . '";
	    	var url_rewriting_enabled = ' . (CONF_URL_REWRITING_ENABLED?'1':'0') . ';
	    	</script>' . "\r\n";
	    
	    if (count($arr_page_js_common) > 0){
	        $last_updated = 0;
	        foreach ($arr_page_js_common as $val){
	            $temp_pth=(substr($val, 0, 1)=='/')?$_SERVER['DOCUMENT_ROOT'] . $val:realpath($val);
	            $time = filemtime($temp_pth);
	            if ($time > $last_updated) $last_updated = $time;
	            
	            if (!$merge_files) $str .= '<script  type="text/javascript" language="javascript" src="' . Utilities::generateUrl('jscss', 'js', array(), $use_root_url, false) . '&f=' . rawurlencode($val) . '&min=0&sid=' . $last_updated . '"></script>' . "\n"; 
	        }
	        if ($merge_files) $str .= '<script  type="text/javascript" language="javascript" src="' . Utilities::generateUrl('jscss', 'js', array(), $use_root_url, false) . '&f=' . rawurlencode(implode(',', $arr_page_js_common)) . '&min=1&sid=' . $last_updated . '"></script>' . "\n";
	    }
	    if (count($arr_page_js) > 0){
	        $last_updated = 0;
	        foreach ($arr_page_js as $val){
	            $temp_pth=(substr($val, 0, 1)=='/')?$_SERVER['DOCUMENT_ROOT'] . $val:realpath($val);
	            $time = filemtime($temp_pth);
	            if ($time > $last_updated) $last_updated = $time;
	            
	            if (!$merge_files) $str .= '<script  type="text/javascript" language="javascript" src="' . Utilities::generateUrl('jscss', 'js', array(), $use_root_url, false) . '&f=' . rawurlencode($val) . '&min=0&sid=' . $last_updated . '"></script>' . "\n";
	        }
	        if ($merge_files) $str .= '<script  type="text/javascript" language="javascript" src="' . Utilities::generateUrl('jscss', 'js', array(), $use_root_url, false) . '&f=' . rawurlencode(implode(',', $arr_page_js)) . '&min=1&sid=' . $last_updated . '"></script>' . "\n";
	    }
	    if ($tpl_for_js_css != ''){
		    $pathinfo = pathinfo($tpl_for_js_css);
	        $temp_pth = realpath(CONF_THEME_PATH . '/' . $pathinfo['dirname'] . '/page-js/' . $pathinfo['filename'] . '.js');
	        if (file_exists($temp_pth)){
	            $str .= '<script  type="text/javascript" language="javascript" src="' . Utilities::generateUrl('pagejsandcss', 'js', array($pathinfo['dirname'], $pathinfo['filename']), $use_root_url, false) . '&sid=' . filemtime($temp_pth) . '"></script>' . "\n";
	        }
	    }
	    
	    return $str;
	}
	
	
	static function getPageJsCssIncludeHtmlOnly($merge_files = true){

	    global $arr_page_css_common;
	    global $arr_page_css;
	    global $arr_page_js_common;
	    global $arr_page_js;
	    
	    global $tpl_for_js_css;
	    
	    $str = '';
	    
	    $use_root_url = '';
	    if (CONF_URL_REWRITING_ENABLED){
	        $use_root_url = CONF_WEBROOT_URL . 'public/' . substr(CONF_USER_ROOT_URL, strlen(CONF_WEBROOT_URL));
	    }
	    else {
	        $use_root_url = CONF_USER_ROOT_URL;
	    }
	    
	    

	    if ($tpl_for_js_css != ''){
	        $pathinfo = pathinfo($tpl_for_js_css);
	        $temp_pth = realpath(CONF_THEME_PATH . '/' . $pathinfo['dirname'] . '/page-css/' . $pathinfo['filename'] . '.css');
	        if (file_exists($temp_pth)){
	            $str .= '<link  rel="stylesheet" type="text/css" href="' . Utilities::generateUrl('pagejsandcss', 'css', array($pathinfo['dirname'], $pathinfo['filename']), $use_root_url, false) . '&sid=' . filemtime($temp_pth) . '" />' . "\n";
	        }
	    }

	    $str .= '<script  type="text/javascript">
	    	var webroot="' . CONF_WEBROOT_URL . '";
	    	var userwebroot = "' . CONF_USER_ROOT_URL . '";
	    	var url_rewriting_enabled = ' . (CONF_URL_REWRITING_ENABLED?'1':'0') . ';
	    	</script>' . "\r\n";
	    
	     
	    
	    if ($tpl_for_js_css != ''){
	        $temp_pth = realpath(CONF_THEME_PATH . '/' . $pathinfo['dirname'] . '/page-js/' . $pathinfo['filename'] . '.js');
	        if (file_exists($temp_pth)){
	            $str .= '<script  type="text/javascript" language="javascript" src="' . Utilities::generateUrl('pagejsandcss', 'js', array($pathinfo['dirname'], $pathinfo['filename']), $use_root_url, false) . '&sid=' . filemtime($temp_pth) . '"></script>' . "\n";
	        }
	    }
	    
	    return $str;
	}

	
	static function &getCurrency(){
		global $currency;
		return $currency;
	}
	
	static function getLanguage(){
		global $language;
		return 1;
	}


}