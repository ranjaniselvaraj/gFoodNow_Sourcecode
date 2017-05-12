<?php



function generateUrl($model='', $action='', $queryData=array(), $use_root_url='', $url_rewriting = null){
    if ($url_rewriting === null) $url_rewriting = CONF_URL_REWRITING_ENABLED;
    if ($use_root_url == '') $use_root_url = CONF_USER_ROOT_URL;
    foreach ($queryData as $key=>$val) $queryData[$key] = rawurlencode($val);
    if ($url_rewriting){
	    $url = rtrim($use_root_url . strtolower($model) . '/' . strtolower($action) . '/' . implode('/', $queryData), '/ ');
        if ($url == '') $url = '/';
		if ((strpos( $use_root_url, 'manager' ) === false)) {
			$rewriteUrl=rewrite($model, $action,$queryData,$use_root_url);
		}
        return empty($rewriteUrl)?$url:$rewriteUrl;
    }
    else {
        $url = rtrim($use_root_url . 'index.php?url=' . strtolower($model) . '/' . strtolower($action) . '/' . implode('/', $queryData), '/');
        return $url;
    }
}



function generateQryUrl($url, $qry=array()) {
    $url = generateUrl($url['c'], $url['a'], $url['vars']);
    $url .= '?';
    foreach($qry as $k=>$v) {
        $url .= "{$k}={$v}&amp;";
    }
    return $url;
}

function encryptPassword($pass){
	return md5(PASSWORD_SALT . $pass . PASSWORD_SALT);
}

function getRandomPassword($n){
    $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass='';
    for($i=0; $i<$n; $i++){
        $pass .= substr($chars,rand(0, strlen($chars)-1), 1);
    }
    return $pass;
}

function round_to_half($number) {
    return round($number * 2) / 2;
}

function curl_post($url, $data=array(), $options=array()) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, (array) $data);
    curl_setopt($ch, CURLOPT_POST, true);

    $rs = curl_exec($ch);

    curl_close($ch);
    return $rs;
}

function crop($data,$src) {
    if (!empty($data)) {
      
      $size = getimagesize($src);
      $size_w = $size[0]; // natural width
      $size_h = $size[1]; // natural height

      $src_img_w = $size_w;
      $src_img_h = $size_h;

      $degrees = $data -> rotate;
	#  test($size);exit;
	  switch($size['mime']){
	   case "image/gif":
          $src_img = imagecreatefromgif($src);
          break;

        case "image/jpeg":
          $src_img = imagecreatefromjpeg($src);
          break;

        case "image/png":
          $src_img = imagecreatefrompng($src);
          break;
	}
	
#	echo $src_img;exit;
	 //  $src_img = imagecreatefromjpeg($src);
      // Rotate the source image
      if (is_numeric($degrees) && $degrees != 0) {
        // PHP's degrees is opposite to CSS's degrees
        $new_img = imagerotate( $src_img, -$degrees, imagecolorallocatealpha($src_img, 0, 0, 0, 127) );

        imagedestroy($src_img);
        $src_img = $new_img;

        $deg = abs($degrees) % 180;
        $arc = ($deg > 90 ? (180 - $deg) : $deg) * M_PI / 180;

        $src_img_w = $size_w * cos($arc) + $size_h * sin($arc);
        $src_img_h = $size_w * sin($arc) + $size_h * cos($arc);

        // Fix rotated image miss 1px issue when degrees < 0
        $src_img_w -= 1;
        $src_img_h -= 1;
      } 

      $tmp_img_w = $data -> width;
      $tmp_img_h = $data -> height;
	  $dst_img_w = 320;
      $dst_img_h = 320;

      $src_x = $data -> x;
      $src_y = $data -> y;

      if ($src_x <= -$tmp_img_w || $src_x > $src_img_w) {
        $src_x = $src_w = $dst_x = $dst_w = 0;
      } else if ($src_x <= 0) {
        $dst_x = -$src_x;
        $src_x = 0;
        $src_w = $dst_w = min($src_img_w, $tmp_img_w + $src_x);
      } else if ($src_x <= $src_img_w) {
        $dst_x = 0;
        $src_w = $dst_w = min($tmp_img_w, $src_img_w - $src_x);
      }

      if ($src_w <= 0 || $src_y <= -$tmp_img_h || $src_y > $src_img_h) {
        $src_y = $src_h = $dst_y = $dst_h = 0;
      } else if ($src_y <= 0) {
        $dst_y = -$src_y;
        $src_y = 0;
        $src_h = $dst_h = min($src_img_h, $tmp_img_h + $src_y);
      } else if ($src_y <= $src_img_h) {
        $dst_y = 0;
        $src_h = $dst_h = min($tmp_img_h, $src_img_h - $src_y);
      }

      // Scale to destination position and size
      $ratio = $tmp_img_w / $dst_img_w;
      $dst_x /= $ratio;
      $dst_y /= $ratio;
      $dst_w /= $ratio;
      $dst_h /= $ratio;

      $dst_img = imagecreatetruecolor($dst_img_w, $dst_img_h);

      // Add transparent background to destination image
      imagefill($dst_img, 0, 0, imagecolorallocatealpha($dst_img, 0, 0, 0, 127));
      imagesavealpha($dst_img, true);
	 
	  $result = imagecopyresampled($dst_img, $src_img, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
		
	  if ($result) {
        if (!imagepng($dst_img, $src)) {
          echo "Failed to save the cropped image file";exit;
        }
      } else {
        echo $msg = "Failed to crop the image file";exit;
      }

      imagedestroy($src_img);
      imagedestroy($dst_img);
    }
}

function getPercentValue($percentage ,$total){	
	$percent = $percentage/$total;
	return $percent_friendly = number_format( $percent * 100, 2 ) . '%';
}

	
function getCityState($zip, $blnUSA = true) {
    $url = "http://maps.googleapis.com/maps/api/geocode/json?address=" . $zip . "&sensor=true";

    $address_info = file_get_contents($url);
    $json = json_decode($address_info);
    $city = "";
    $state = "";
    $country = "";
    if (count($json->results) > 0) {
        //break up the components
        $arrComponents = $json->results[0]->address_components;

        foreach($arrComponents as $index=>$component) {
            $type = $component->types[0];
			//echo($type."#");
            if ($city == "" && ($type == "sublocality_level_1" || $type == "locality") ) {
                $city = trim($component->short_name);
            }
            if ($state == "" && $type=="administrative_area_level_1") {
                $state = trim($component->short_name);
            }
            if ($country == "" && $type=="country") {
                $country = trim($component->short_name);

                if ($blnUSA && $country!="US") {
                    $city = "";
                    $state = "";
                    break;
                }
            }
            if ($city != "" && $state != "" && $country != "") {
                //we're done
                break;
            }
        }
    }
    $arrReturn = array("city"=>$city, "state"=>$state, "country"=>$country);

    die(json_encode($arrReturn));
}

function addhttp($url) {
	if (strpos($url,'http://') === false){
		$url='http://'.$url;
	}
	return $url;
}

function multipleExplode($delimiters = array(), $string = ''){ 
	$mainDelim=$delimiters[count($delimiters)-1]; // dernier 
	array_pop($delimiters); 
	foreach($delimiters as $delimiter){ 
		 $string= str_replace($delimiter, $mainDelim, $string); 
	} 
	$result= explode($mainDelim, $string); 
	return Utilities::array_trim($result); 
}

function array_trim($ar){
	  foreach($ar as $key=>$val){
			$val=trim($val);
			if (!empty($val))
				$reArray[]=$val;
	  }
	  return $reArray;
}

function writeBlogMetaTags($content = array()) {
    if (isset($content['meta_title']) && $content['meta_title'] != "" ) {
        echo '<title>' . $content['meta_title'] . '</title>' . "\n";
    } else {
        echo '<title>' . CONF_WEBSITE_NAME . '</title>' . "\n";
    }
    if (isset($content['meta_description']))
        echo '<meta name="description" content="' . $content['meta_description'] . '" />';
    if (isset($content['meta_keywords']))
        echo '<meta name="keywords" content="' . $content['meta_keywords'] . '" />';
    if (isset($content['meta_others']))
        echo html_entity_decode($content['meta_others'], ENT_QUOTES, 'UTF-8');
}


$func = dirname(__FILE__).'/functions/';
include_once $func . 'email.php';
include_once $func . 'file.php';
include_once $func . 'form.php';
include_once $func . 'html.php';
include_once $func . 'request.php';
include_once $func . 'session.php';
include_once $func . 'template.php';
include_once $func . 'misc.php';
