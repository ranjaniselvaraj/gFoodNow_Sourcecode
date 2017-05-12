<?php
public static function slugify($text){ 
  // replace non letter or digits by -
  $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
  // trim
  $text = trim($text, '-');
  // transliterate
  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
  // lowercase
  $text = strtolower($text);
  // remove unwanted characters
  $text = preg_replace('~[^-\w]+~', '', $text);
  if (empty($text)){
    return 'n-a';
  }
  return $text;
}
public static function displayNumberFormat($val){
    return number_format($val,0);
}


public static function displayMoneyFormat($val,$format=true,$currency=true){
	if ($format){ 
		$val=number_format($val,2);
	}
	if($currency==false){
		return $val;
	}
	$currencySymbolLeft=html_entity_decode(CONF_CURRENCY_SYMBOL_LEFT, ENT_QUOTES, 'UTF-8');	
	$currencySymbolRight=html_entity_decode(CONF_CURRENCY_SYMBOL_RIGHT, ENT_QUOTES, 'UTF-8');	
	return $currencySymbolLeft.$val.$currencySymbolRight;
}



public static function displayOrderFormattedCurrencyValue($val,$left_symbol="",$right_symbol=""){
	return $left_symbol.number_format($val,2).$right_symbol;
}

public static function handleReplaceParamters($str,$vars=array()){
	 foreach ($vars as $key => $val) {
        $str = str_replace($key, $val, $str);
    }
	return $str;
}

public static function cc_masking($number) {
    return str_repeat("X", strlen($number) - 4) . substr($number, -4);
}

public static function dateFormat($format, $date) {
    return date("$format", strtotime($date));
}

public static function formatdate($dt,$displayTime=false){
	//return date("M d, Y",strtotime($dt));
	return displayDate($dt, $displayTime, true, Settings::getSetting("CONF_TIMEZONE"));
}

public static function formatDateOnly($dt){
	return date(CONF_DATE_FORMAT_PHP,strtotime($dt));
	//return displayDate($dt, false, true, Settings::getSetting("CONF_TIMEZONE"));
}

public static function currentPageURL() {
		 $pageURL = 'http';
		 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
			 $pageURL .= "://";
			if ($_SERVER["SERVER_PORT"] != "80") {
			  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
			 } else {
			  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	 		}
		 return $pageURL;
	}
	
	public static function displayNotApplicable($val,$str="-NA-"){
		return $val!=""?$val:$str;
	}	


public static function is_multidim_array($arr) {
  			if (!is_array($arr))
			    return false;
				  foreach ($arr as $elm) {
    					if (!is_array($elm))
					      return false;
						  }
			  return true;
	}

public static function parse_yturl($url) 
{
    $pattern = '#^(?:https?://)?';    # Optional URL scheme. Either http or https.
    $pattern .= '(?:www\.)?';         #  Optional www subdomain.
    $pattern .= '(?:';                #  Group host alternatives:
    $pattern .=   'youtu\.be/';       #    Either youtu.be,
    $pattern .=   '|youtube\.com';    #    or youtube.com
    $pattern .=   '(?:';              #    Group path alternatives:
    $pattern .=     '/embed/';        #      Either /embed/,
    $pattern .=     '|/v/';           #      or /v/,
    $pattern .=     '|/watch\?v=';    #      or /watch?v=,    
    $pattern .=     '|/watch\?.+&v='; #      or /watch?other_param&v=
    $pattern .=   ')';                #    End path alternatives.
    $pattern .= ')';                  #  End host alternatives.
    $pattern .= '([\w-]{11})';        # 11 characters (Length of Youtube video ids).
    $pattern .= '(?:.+)?$#x';         # Optional other ending URL parameters.
    preg_match($pattern, $url, $matches);
    return (isset($matches[1])) ? $matches[1] : false;
}

public static function getLast12MonthsDetails(){
        $month = date('m');
        $year  = date('Y');
        $i = 1;
        $date = array();
        while($i<=12){
          $timestamp = mktime(0,0,0,$month,1,$year);
          $date[$i]['monthCount'] = date('m', $timestamp);
          $date[$i]['monthShort'] = date('M', $timestamp);
          $date[$i]['yearShort']  = date('y', $timestamp);
		  $date[$i]['year']      = date('Y', $timestamp);
          $month--;
          $i++;
        }
        return $date;
    }


public static function replace_array_keys($arr,$arr_replace){
	foreach($arr as $key=>$val){
		if (array_key_exists($key,$arr_replace)){
			$arr[$arr_replace[$key]]=$val;
		}
	}
	return $arr;	
}

public static function format_return_request_number($return_id){
		$new_value=str_pad($return_id,5,'0',STR_PAD_LEFT);
		$new_value="R".$new_value;
		return $new_value;
	}


public static function aasort (&$array, $key) {
    $sorter=array();
    $ret=array();
    reset($array);
    foreach ($array as $ii => $va) {
        $sorter[$ii]=$va[$key];
    }
    asort($sorter);
    foreach ($sorter as $ii => $va) {
        $ret[$ii]=$array[$ii];
    }
    $array=$ret;
}
public static function utf8_strlen($string) {
		return mb_strlen($string);
}

public static function full_copy( $source, $target,$empty_first=true) {
	if ($empty_first){
		Utilities::recursiveDelete($target);
	}
    if ( is_dir( $source ) ) {
        @mkdir( $target );
        $d = dir( $source );
        while ( FALSE !== ( $entry = $d->read() ) ) {
            if ( $entry == '.' || $entry == '..' ) {
                continue;
            }
            $Entry = $source . '/' . $entry; 
            if ( is_dir( $Entry ) ) {
                Utilities::full_copy( $Entry, $target . '/' . $entry );
                continue;
            }
            copy( $Entry, $target . '/' . $entry );
        }

        $d->close();
    }else {
        copy( $source, $target );
    }
}

public static function isHideHeaderFooter(){
	if (isset($_SESSION['hide_header_footer'])){
		return true;
	}
	return false;
}

public static function validate_cc_number($cardNumber) {
	$cardNumber = preg_replace('/\D/', '', ($cardNumber));
	$len = strlen($cardNumber);
   $result=array();	
	if ($len > 16) {
      $result['card_type']='Invalid';
	  return $result;
   }
	switch($cardNumber) {
		case 0:
			$result['card_type']='';
		break;	
		case(preg_match ('/^4/', $cardNumber) >= 1):	
			$result['card_type']='VISA';	
		break;
		case(preg_match ('/^5[1-5]/', $cardNumber) >= 1):
			$result['card_type']='MASTERCARD';	
		break;
		case(preg_match ('/^3[47]/', $cardNumber) >= 1):
			$result['card_type']='AMEX';	
		break;
		case(preg_match ('/^3(?:0[0-5]|[68])/', $cardNumber) >= 1):
			$result['card_type']='DINERS_CLUB';	
		break;
		case(preg_match ('/^6(?:011|5)/', $cardNumber) >= 1):
			$result['card_type']='DISCOVER';	
		break;
		case(preg_match ('/^(?:2131|1800|35\d{3})/', $cardNumber) >= 1):
			$result['card_type']='JCB';	
		break;
		default:
			$result['card_type']='';	
		break;
	} 
	return $result;
}

public static function convert_to_csv($input_array, $output_file_name, $delimiter){
    /** open raw memory as file, no need for temp files */
    $temp_memory = fopen('php://memory', 'w');
	fprintf($temp_memory, chr(0xEF).chr(0xBB).chr(0xBF));
    /** loop through array  */
    foreach ($input_array as $line) {
        /** default php csv handler **/
        fputcsv($temp_memory, $line, $delimiter);
    }
    /** rewrind the "file" with the csv lines **/
    fseek($temp_memory, 0);
    /** modify header to be downloadable csv file **/
	//header("content-type:application/csv;charset=UTF-8");
	header('Content-Encoding: UTF-8');
	header('Content-type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachement; filename="' . $output_file_name . '";');
    /** Send file to browser for download */
    fpassthru($temp_memory);
}

public static function isBrandingSignatureAuthenticated(){		
	if(strpos($_SERVER['SERVER_NAME'], 'yo-kart') === false && $_SERVER['SERVER_ADDR'] =='69.167.184.132' || $_SERVER['SERVER_ADDR'] =='69.167.184.134') {		
		return true;
	}
	return false;
}

public static function isOurDemoSystem(){
	if(strpos($_SERVER['SERVER_NAME'], 'yo-kart') !== true && ($_SERVER['SERVER_ADDR'] =='69.167.184.199' || $_SERVER['SERVER_ADDR'] =='69.167.184.134') ) {		
		return true;
	}
	return false;
}