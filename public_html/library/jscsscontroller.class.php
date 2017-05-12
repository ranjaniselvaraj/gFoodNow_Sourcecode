<?php
class JscssController{
    function css(){
        header('Content-Type: text/css');
        header('Cache-Control: public');
		header("Pragma: public");
        header("Expires: " . date('r', strtotime("+30 Day")));
        if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')){
            ob_start("ob_gzhandler");
        }
        else {
            ob_start();
        }
        
        
        $arr = explode(',', $_GET['f']);
        
        $str = '';
        foreach ($arr as $fl){
            
            if (substr($fl, '-4') != '.css') continue;
        
            if (file_exists($fl)) $str .= file_get_contents($fl);
        }
        
        //$str = str_replace('../', '', $str);
        
        /*if ($_GET['min'] == 1){
            $str = preg_replace('/([\n][\s]*)+/', " ", $str);
            $str = str_replace("\r", '', $str);
            $str = str_replace("\n", '', $str);
        }*/
        
        echo $str;
    }
    
    function js(){
        if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')){
            ob_start("ob_gzhandler");
        }
        else {
            ob_start();
        }
        $arr = explode(',', $_GET['f']);
        
        $time = microtime(true);
        
        $str = '';
        foreach ($arr as $fl){
            if ($fl == 'form-validation.js.php'){
                ob_start();
                include '_classes/form-validation.js.php';
                $str .= ob_get_clean();
                continue;
            }
            
            if ($fl == 'functions.js.php'){
                ob_start();
                include '_classes/functions.js.php';
                $str .= ob_get_clean();
                continue;
            }
            
            if (substr($fl, '-3') != '.js') continue;
        
            if (file_exists($fl)) $str .= file_get_contents($fl);
        }
        
        header('Content-Type: application/javascript');
        header('Cache-Control: public');
		header("Pragma: public");
        header("Expires: " . date('r', strtotime("+30 Day")));
        
        echo($str);
    }
}