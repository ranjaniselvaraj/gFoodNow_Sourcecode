<?php
function redirectUser($url=''){
	if ($url == '') $url = $_SERVER['REQUEST_URI'];
	header("Location: " . $url);
	exit;
}
function dieWithError($err){
	global $post;
	if ($post['outmode'] == 'json')	dieJsonError($err);
	die($err);
}
function isAjaxRequest() {
    $post = Syspage::getPostedVar();
    if (isset($post['is_ajax_request']) && strtolower($post['is_ajax_request'])=='yes') {
        return true;
    } elseif(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])=='xmlhttprequest') {
        return true;
    }
    return false;
}

function getCurrUrl() {
 return Utilities::getUrlScheme() . $_SERVER["REQUEST_URI"];
}

function getUrlScheme() {
 $pageURL = 'http';
 if (isset($_SERVER['HTTPS']) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"];
 }
 return $pageURL;
}

function redirectUserReferer() {
    if (!defined(REFERER)) {
        if (getCurrUrl()==$_SERVER['HTTP_REFERER'] || empty($_SERVER['HTTP_REFERER']))
            define('REFERER', CONF_WEBROOT_URL);
        else
            define('REFERER', $_SERVER['HTTP_REFERER']);
    }
    redirectUser(REFERER);
}

function redirectUser404() {
    redirectUser(generateUrl('index', 'error', array(404)));
}

function getUrlData() {
    if (isset($_GET['url'])) return $_GET;
    preg_match('/\/([a-zA-Z0-9\-\_\.\/\|\,\@]+)\??(.*)?/', $_SERVER['REQUEST_URI'], $matches);
    $return = array(
        'url' => ($matches[1]=='index.php'?'':$matches[1]),
    );
    $qry_arr = explode('&', $matches[2]);
    foreach($qry_arr as $qry) {
        $qry = explode('=', $qry);
        $return[ $qry[0] ] = $qry[1];
    }
    return $_GET = $return;
}

function getUrlQuery() {
	//global $get;
	$get=getQueryStringData();
    return $get;
}

function show404() {
    $c = new IndexController('index', 'index', 'error');
    $c->error(404);
}


// User Interface functions
function reloadPage() {
    header('Location: '.$_SERVER['REQUEST_URI']);
    exit;
}
// User Interface functions Ends


function getSiteUrl() {
    return generateUrl();
}

function encodeArray($data) {
    $d=array();
    foreach($data as $key=>$value) {
        if(is_string($key))
            $key=htmlspecialchars($key,ENT_QUOTES);
        if(is_string($value))
            $value=htmlspecialchars($value,ENT_QUOTES);
        elseif(is_array($value))
            $value=encodeArray($value);
        $d[$key]=$value;
    }
    return $d;
}


//function to send xml request via fsockopen
//It is a good idea to check the http status code.
function send_request_via_fsockopen($host,$path,$content,$content_type='text/xml')
{
    $posturl = "ssl://" . $host;
    $header = "Host: $host\r\n";
    $header .= "User-Agent: PHP Script\r\n";
    $header .= "Content-Type: {$content_type}\r\n";
    $header .= "Content-Length: ".strlen($content)."\r\n";
    $header .= "Connection: close\r\n\r\n";
    $fp = fsockopen($posturl, 443, $errno, $errstr, 30);
    if (!$fp)
    {
        $body = false;
    }
    else
    {
        error_reporting(E_ERROR);
        fputs($fp, "POST $path  HTTP/1.1\r\n");
        fputs($fp, $header.$content);
        fwrite($fp, $out);
        $response = "";
        while (!feof($fp))
        {
            $response = $response . fgets($fp, 128);
        }
        fclose($fp);
        error_reporting(E_ALL ^ E_NOTICE);
        
        $len = strlen($response);
        $bodypos = strpos($response, "\r\n\r\n");
        if ($bodypos <= 0)
        {
            $bodypos = strpos($response, "\n\n");
        }
        while ($bodypos < $len && $response[$bodypos] != '<')
        {
            $bodypos++;
        }
        $body = substr($response, $bodypos);
    }
    return $body;
}

//function to send xml request via curl
function send_request_via_curl($host,$path,$content,$content_type='text/xml')
{
    $posturl = "https://" . $host . $path;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $posturl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: ".$content_type));
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $response = curl_exec($ch);
    return $response;
}