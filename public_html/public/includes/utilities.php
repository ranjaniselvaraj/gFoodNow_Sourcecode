<?php 
	class Utilities {
		
		public static function generateUrl($model='', $action='', $queryData=array(), $use_root_url='', $url_rewriting = null){
   			 if ($url_rewriting === null) $url_rewriting = CONF_URL_REWRITING_ENABLED;
			 if ($use_root_url == '') $use_root_url = CONF_USER_ROOT_URL;
		     foreach ($queryData as $key=>$val) $queryData[$key] = rawurlencode($val);
		     if ($url_rewriting){
			    $url = rtrim($use_root_url . strtolower($model) . '/' . strtolower($action) . '/' . implode('/', $queryData), '/ ');
        		if ($url == '') $url = '/';
				if ((strpos( $use_root_url, 'manager' ) === false)) {
					$rewriteUrl=self::rewrite($model, $action,$queryData,$use_root_url);
				}
        		return empty($rewriteUrl)?$url:$rewriteUrl;
		    }
		    else {
        		$url = rtrim($use_root_url . 'index.php?url=' . strtolower($model) . '/' . strtolower($action) . '/' . implode('/', $queryData), '/');
	        return $url;
    		}
		}
		
		
		public static function generateNoAuthUrl($model = '', $action = '', $queryData = array(), $use_root_url = '') {
    		$url = self::generateUrl($model, $action, $queryData, $use_root_url, false);
		    $url = str_replace('index.php?', 'index_noauth.php?', $url);
    		return 'http://' . $_SERVER['SERVER_NAME'] . $url;
		}

	public static function rewrite($model='', $action='', $queryData=array(),$use_root_url=''){
			$url="";
			$route=$model."/".$action;
				$record_id=isset($queryData[0])?$queryData[0]:'';
				if (($model=="brands") || ($model=="products") || ($model=="cms") || ($model=="shops") || ($model=="category")){
					$urlAliasObj=new Url_alias();
					$url_alias=$urlAliasObj->getUrlAliasByQuery($model."_id=".$record_id);
					if ($url_alias && !empty($url_alias['url_alias_keyword'])){
						$url .= $use_root_url.strtolower(urlencode($url_alias['url_alias_keyword'])).'/'.strtolower($model) . '/' . strtolower($action) . '/' . implode('/', $queryData);
					}else{
						$url .= $use_root_url.'-'.'/'.strtolower($model) . '/' . strtolower($action) ;
						if (!empty($queryData)){
							$url .= '/' . implode('/', $queryData);
						}
				}
			}
			return $url;
		}

		public static function generateAbsoluteUrl($model='', $action='', $queryData=array(), $use_root_url='', $url_rewriting=null) {
		    return self::getUrlScheme() . self::generateUrl($model, $action, $queryData, $use_root_url, $url_rewriting);
		}
		
		public static function encryptPassword($pass){
			return md5(PASSWORD_SALT . $pass . PASSWORD_SALT);
		}
		
		public static function getRandomPassword($n){
    		$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
		    $pass='';
		    for($i=0; $i<$n; $i++){
        		$pass .= substr($chars,rand(0, strlen($chars)-1), 1);
		    }
		    return $pass;
		}
		
		public static function curl_post($url, $data=array(), $options=array()) {
    		$ch = curl_init();
		    curl_setopt($ch, CURLOPT_URL, $url);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($ch, CURLOPT_POSTFIELDS, (array) $data);
		    curl_setopt($ch, CURLOPT_POST, true);
		    $rs = curl_exec($ch);
		    curl_close($ch);
		    return $rs;
		}

		public static function crop($data,$src) {
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
			  $dst_img_w = $size_w;
			  $dst_img_h = $size_w;
		
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
			 
			  //echo $dst_img."#".$src_img."#".$dst_x."#".$dst_y."#".$src_x."#".$src_y."#".$dst_w."#".$dst_h."#".$src_w."#".$src_h;	
			  //die();
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

		public static function getPercentValue($percentage ,$total){	
			$percent = $percentage/$total;
			return $percent_friendly = number_format( $percent * 100, 2 ) . '%';
		}
		
		public static function addhttp($url) {
			if (strpos($url,'http://') === false){
				$url='http://'.$url;
			}
			return $url;
		}
		
		public static function multipleExplode($delimiters = array(), $string = ''){ 
			$mainDelim=$delimiters[count($delimiters)-1]; // dernier 
			array_pop($delimiters); 
			foreach($delimiters as $delimiter){ 
				 $string= str_replace($delimiter, $mainDelim, $string); 
			} 
			$result= explode($mainDelim, $string); 
			return self::array_trim($result); 
		}
		
		public static function array_trim($ar){
			  foreach($ar as $key=>$val){
					$val=trim($val);
					if (!empty($val))
						$reArray[]=$val;
			  }
			  return $reArray;
		}

		public static function writeBlogMetaTags($content = array()) {
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
		
		public static function convert_url($str){
			return "xx".$str;
		}
		// Email functions
		
		public static function sendMail($to, $subject, $body, $extra_headers=''){
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		
			$headers .= 'From: ' . Settings::getSetting("CONF_FROM_NAME") ."<".Settings::getSetting("CONF_FROM_EMAIL").">" . "\r\nReply-to: ".Settings::getSetting("CONF_REPLY_TO_EMAIL")."\r\n";
		
			if ($extra_headers != '') $headers .= $extra_headers;
			return mail($to, $subject, $body, $headers);
		}
		
		public static function sendMailTpl($to, $tpl, $vars=array(), $extra_headers='',$smtp=0,$smtp_arr=array()) {
			
			//$db = &Syspage::getdb();
			global $db;
			$rs = $db->query("SELECT tpl_subject, tpl_body FROM tbl_email_templates WHERE tpl_code=".$db->quoteVariable($tpl)." and tpl_status=1");
			$row = $db->fetch($rs);
		
			if (!isset($row['tpl_body']) || empty($row['tpl_body'])) {
				return false;
			}
		
			$subject = $row['tpl_subject'];
			$body = $row['tpl_body'];
			$vars['{current_date}']=date('M d, Y');
			foreach ($vars as $key => $val) {
				$subject = str_replace($key, $val, $subject);
				$body = str_replace($key, $val, $body);
			}
			$company_logo="<img src='".self::generateAbsoluteUrl('image', 'site_email_logo',array(Settings::getSetting("CONF_EMAIL_LOGO")),CONF_WEBROOT_URL)."' alt=''  />";
			$body=str_replace('{Company_Logo}',$company_logo,$body);
			//die($body."=".$to."=".$subject);
			//$db = &Syspage::getdb();
		
			$db->insert_from_array('tbl_email_archives', array(
			'mailarchive_from_email'=>'',
			'mailarchive_to_email'=>$to,
			'mailarchive_tpl_name'=>$tpl,
			'mailarchive_subject'=>$subject,
			'mailarchive_message'=>$body,
			'mailarchive_sent_on'=>date('Y-m-d H:i:s'),
			), $exec_mysql_func=true);
			
			if (empty($smtp_arr)){
					$smtp_arr=array("host"=>Settings::getSetting("CONF_SMTP_HOST"),"port"=>Settings::getSetting("CONF_SMTP_PORT"),"username"=>Settings::getSetting("CONF_SMTP_USERNAME"),"password"=>Settings::getSetting("CONF_SMTP_PASSWORD"));
			}
			
			if (Settings::getSetting("CONF_SEND_EMAIL")){
				if (((Settings::getSetting("CONF_SEND_SMTP_EMAIL")) || $smtp) && ($smtp!='TEST')){
					return self::sendSmtpEmail($to, $subject, $body,'',$smtp_arr);
				}
				else
					return self::sendMail($to, $subject, $body);
			}else{
				return true;
			}
			//return self::sendSmtpEmail($to, $subject, $body);
		}
		
		
		
		public static function mail_attachment($filename, $path, $mailto, $from_mail, $from_name, $replyto, $subject, $message) {
			$file = $path.$filename;
			$file_size = filesize($file);
			$handle = fopen($file, "r");
			$content = fread($handle, $file_size);
			fclose($handle);
			$content = chunk_split(base64_encode($content));
			$uid = md5(uniqid(time()));
			$name = basename($file);
			$header = "From: ".$from_name." <".$from_mail.">\r\n";
			$header .= "Reply-To: ".$replyto."\r\n";
			$header .= "MIME-Version: 1.0\r\n";
			$header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
			$header .= "This is a multi-part message in MIME format.\r\n";
			$header .= "--".$uid."\r\n";
			$header .= "Content-type:text/html; charset=iso-8859-1\r\n";
			$header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
			$header .= $message."\r\n\r\n";
			$header .= "--".$uid."\r\n";
			$header .= "Content-Type: application/octet-stream; name=\"".$filename."\"\r\n"; // use different content types here
			$header .= "Content-Transfer-Encoding: base64\r\n";
			$header .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
			$header .= $content."\r\n\r\n";
			$header .= "--".$uid."--";
			if (@mail($mailto, $subject, "", $header)) {
				return true;
			} else {
				return false;
			}
		}
		
		public static function sendSmtpEmail($toAdress,$Subject,$body,$attachment="",$smtp_arr=array())
		{
			
			$host = $smtp_arr["host"]!=""?$smtp_arr["host"]:Settings::getSetting("CONF_SMTP_HOST"); // or "mail.example.com" is using without ssl
			$port = $smtp_arr["port"]!=""?$smtp_arr["port"]:Settings::getSetting("CONF_SMTP_PORT"); // only is using ssl
			$username = $smtp_arr["username"]!=""?$smtp_arr["username"]:Settings::getSetting("CONF_SMTP_USERNAME"); // only is using ssl
			$password = $smtp_arr["password"]!=""?$smtp_arr["password"]:Settings::getSetting("CONF_SMTP_PASSWORD"); // only is using ssl
			$secure = $smtp_arr["secure"]!=""?$smtp_arr["secure"]:Settings::getSetting("CONF_SMTP_SECURE"); // only is using ssl
			//Create a new PHPMailer instance
			$mail = new PHPMailer;
			$mail->CharSet = 'UTF-8';
			//Tell PHPMailer to use SMTP
			$mail->isSMTP();
			//Enable SMTP debugging
			// 0 = off (for production use)
			// 1 = client messages
			// 2 = client and server messages
			$mail->SMTPDebug = 0;
			$mail->SMTPSecure = $secure;
			//Ask for HTML-friendly debug output
			$mail->Debugoutput = 'html';
			//Set the hostname of the mail server
			$mail->Host = $host;
			//Set the SMTP port number - likely to be 25, 465 or 587
			$mail->Port = $port;
			//Whether to use SMTP authentication
			$mail->SMTPAuth = true;
			//Username to use for SMTP authentication
			$mail->Username = $username;
			//Password to use for SMTP authentication
			$mail->Password = $password;
			//Set who the message is to be sent from
			$mail->setFrom(Settings::getSetting("CONF_FROM_EMAIL"), Settings::getSetting("CONF_FROM_NAME"));
			//Set an alternative reply-to address
			$mail->addReplyTo(Settings::getSetting("CONF_REPLY_TO_EMAIL"), Settings::getSetting("CONF_FROM_NAME"));
			//$mail->addReplyTo('info@dummyid.com', 'First Last');
			//Set who the message is to be sent to
			$mail->addAddress($toAdress);
			//Set the subject line
			$mail->Subject = $Subject;
			//Read an HTML message body from an external file, convert referenced images to embedded,
			//convert HTML into a basic plain-text alternative body
			$mail->msgHTML($body);
			//Replace the plain text body with one created manually
			$mail->AltBody = 'This is a plain-text message body';
			//Attach an image file
			//$mail->addAttachment('images/phpmailer_mini.png');
			
			//send the message, check for errors
			if (!$mail->send()) {
				//echo 'Message could not be sent.';
				//echo 'Mailer Error: ' . $mail->ErrorInfo;
				throw new Exception('Mailer Error: ' . $mail->ErrorInfo);
				exit;
				//return $mail->ErrorInfo;
			} else {
				//echo 'Message sent.';
				return true;
			}
		}
		
		
		/**
		* 
		* Saves an image
		* @param String $fl full path of the file
		* @param String $name name of file
		* @param String $response In case of success exact name of file will be returned in this else error message
		* @return Boolean
		*/
		
		
		public static function saveImage($fl, $name, &$response, $pathSuffix='',$replace=false){
			$pathSuffix=self::getCurrUploadDirPath($pathSuffix);
			$dir = CONF_INSTALLATION_PATH . 'user-uploads/' . $pathSuffix;
			if(!is_writable($dir)){
				$response = "Directory $dir is not writable, or does not exist. Please check";
				return false;
			}
			$fname = preg_replace('/[^a-zA-Z0-9\/\-\_\.]/', '', $name);
			if($replace!==true) {
				while (file_exists($dir . $fname)){
					$fname = rand(10, 999999).'_'.$fname;
				}
			}
			if (!copy($fl, $dir . $fname)){
				$response = 'Could not save file.';
				return false;
			}
			$response = self::getUploadedFilePath($fname);
			return true;
		}
		
		public static function saveFile($fl, $name, &$response, $pathSuffix=''){
			$pathSuffix=self::getCurrUploadDirPath($pathSuffix);
			$dir = CONF_INSTALLATION_PATH . 'user-uploads/' . $pathSuffix;
			if(!is_writable($dir)){
				$response = "Directory $dir is not writable, or does not exist. Please check";
				return false;
			}
			$fname = preg_replace('/[^a-zA-Z0-9\/\-\_\.]/', '', $name);
			while (file_exists($dir.$fname)){
				$fname = rand(10, 999999).'_'.$fname;
			}
			if (!copy($fl,$dir.$fname)){
				$response = 'Could not save file.';
				return false;
			}
			$response = self::getUploadedFilePath($fname);
			return true;
		}
		
		public static function isUploadedFileValidImage($files) {
			$valid_mime_types = preg_replace('~\r?\n~', "\n", Settings::getSetting("CONF_IMAGE_MIME_ALLOWED"));
			$valid_arr = explode("\n", $valid_mime_types);
			return (isset($files['name']) && $files['error']==0 && in_array($files['type'], $valid_arr) && $files['size']>0);
		}
		
		public static function isUploadedFileValidFile($files) {
			$valid_mime_types = preg_replace('~\r?\n~', "\n", Settings::getSetting("CONF_FILE_MIME_ALLOWED"));
			$valid_arr = explode("\n", $valid_mime_types);
			return (isset($files['name']) && $files['error']==0 && in_array($files['type'], $valid_arr) && $files['size']>0);
		}
		
		
		/**
		*
		* Write contents to a file. Overwrites existing contents.
		* @param String $name Name of file
		* @param String $data Contents to write on file
		* @param String $response In case of success exact name of file will be returned in this else error message
		* @return Boolean
		*/
		public static function writeFile($name, $data, &$response) {
			$fname = CONF_USER_UPLOADS_PATH . preg_replace('/[^a-zA-Z0-9\/\-\_\.]/', '', $name);
			$dest = dirname($fname);
			
			if (!file_exists($dest)) mkdir($dest, 0777, true);
			
			$f = fopen($fname, 'w');
			$rs = fwrite($f, $data);
			fclose($f);
			
			if (!$rs) {
			$response = 'Could not save file.';
			return false;
			}
			
			$response = $fname;
			return true;
		}
		
		public static function getFilepathOnDirectory($rs) {
			return CONF_USER_UPLOADS_PATH . $rs;
		}
		
		public static function outputFile($filepath, $mask='') {	
			$filepath=self::getRealFilePath($filepath);
			$filepath = self::getFilepathOnDirectory($filepath);
			if (empty($filepath) || !is_file($filepath)) {
				return ;
			}
			$ext = pathinfo($filepath, PATHINFO_EXTENSION);
			$mask = basename($mask);
			$file_download_name = ($mask ? $mask.".".$ext : basename($filepath));
			$file_download_name = str_replace(" ","-",$file_download_name);
			ob_end_clean();
			ob_start();
			header("Content-type: application/octet-stream");
			header('Content-Disposition: attachement; filename="'.basename($file_download_name).'"');
			header('Content-Length: ' . filesize($filepath));
			readfile($filepath);			
		
			/*header("Content-type: application/octet-stream");
			header('Content-Disposition: attachement; filename="'.$file_download_name.'"');
			header('Content-Length: ' . filesize($filepath));
			readfile($filepath);*/
		}
		
		public static function outputImage($fname, $width, $height, $default='', $enable_cache=true) {
			if ($default=='') {
			//$default = CONF_INSTALLATION_PATH . 'public/images/126pic.jpg';
			$default = CONF_USER_UPLOADS_PATH . 'no-image.jpg';
			}
			$fname=self::getRealFilePath($fname);
			$fname = self::getFilepathOnDirectory($fname);
			if (!file_exists($fname) || !is_file($fname)) {
			$fname = $default;
			}
			header("Content-type: image/jpeg");
			header('Content-Disposition: inline; filename="'.basename($fname).'"');
			
			if($width===false || $height===false) {
			echo file_get_contents($fname);
			exit;
			}
			
			$cache = 'cache/' . md5($fname). '-' . $width . 'x' . $height . '.jpg';
			$cache_fname = CONF_USER_UPLOADS_PATH . $cache;
			if ($enable_cache==true && file_exists($cache_fname) && is_file($cache_fname)) {
			echo file_get_contents($cache_fname);exit;
			}
			
			$image = new ImageResize($fname);
			if (($width==0) || ($height==0))
			list($width, $height, $type, $attr) = getimagesize($fname);
			$image->setMaxDimensions($width, $height);
			$image->setExtraSpaceColor(255,255,255);
			$image->setResizeMethod(2);
			ob_start();
			$image->displayImage();
			$contents = ob_get_clean();
			self::writeFile($cache, $contents);
			echo file_get_contents($cache_fname);exit;
		}
			
		public static function unlinkFile($name) {
			@unlink(CONF_USER_UPLOADS_PATH . $name);
		}
		
		public static function deleteFile($file, $path_suffix) {
			if ($file === '' || !is_string($file) || strlen(trim($file)) < 1)
			return false;
			$file_fullpath = CONF_INSTALLATION_PATH . 'user-uploads/' . $path_suffix . $file;
			if (file_exists($file_fullpath)) {
			unlink($file_fullpath);
			return true;
			}
			return false;
		}
		
		public static function showImage($file='', $w=0, $h=0, $path_suffix='', $no_img = 'default.jpg',$apply_watermark=false){
			if($file === '' || !is_string($file) || strlen(trim($file)) < 1) $file = 'no-img.jpg';
			$file=self::getRealFilePath($file);
			$img = CONF_INSTALLATION_PATH . 'user-uploads/'. $path_suffix . $file; 
			if(!file_exists($img)) $img = CONF_INSTALLATION_PATH . 'public/images/' . $no_img;
			
			if (!is_numeric($w)) $w = 0;
			if (!is_numeric($h)) $h = 0;
			if($w == 0 || $h == 0){
			$image_size = getimagesize($img);
			}	
			if ($w == 0) $w = (isset($image_size[0])?$image_size[0]:100);
			if ($h == 0) $h = (isset($image_size[1])?$image_size[1]:100);
			
			
			$image = new ImageResize($img);
			if (!$image) die('');
			$image->setMaxDimensions($w, $h);
			$image->setResizeMethod(2);
			$ext = pathinfo($file, PATHINFO_EXTENSION);
			$file_name = basename($file, ".".$ext); // $file is set to "home"
			$resized_image=CONF_INSTALLATION_PATH . 'user-uploads/resized/'.$file_name.".jpg";
			/*header("content-type: image/jpeg");
			header('Cache-Control: public');
			header("Pragma: public");*/
			if (function_exists('apache_request_headers')) {
			$headers = apache_request_headers();
			}else {
			$headers = array();
			}
			if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == filemtime($imgpath))) {
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($imgpath)).' GMT', true, 304);
			exit;
			}
			header('Cache-Control: public');
			header("Pragma: public");
			//header("Expires: " . date('r', strtotime("+30 Minute")));
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($imgpath)).' GMT', true, 200);
			header("Content-Type: image/jpg");
			
			if ($apply_watermark){
			$image->saveImage($resized_image);
			$newcopy=CONF_INSTALLATION_PATH . 'user-uploads/resized/watermarked_'.$file_name.".jpg";
			$wtrmrk_file=CONF_INSTALLATION_PATH . 'user-uploads/'.Settings::getSetting("CONF_WATERMARK_IMAGE");
			$ext_watermark = pathinfo($wtrmrk_file, PATHINFO_EXTENSION);
			switch($ext_watermark) {
				case 'png':
					$watermark = imagecreatefrompng($wtrmrk_file);
				break;
				case 'jpg':
					$watermark = imagecreatefromjpeg($wtrmrk_file);
				break;
				case 'gif':
					$watermark = imagecreatefromgif($wtrmrk_file);
				break;
			}
			imagealphablending($watermark, false); 
			imagesavealpha($watermark, true); 
			$ext = pathinfo($resized_image, PATHINFO_EXTENSION);
			switch($ext) {
			case 'png':
			$img = imagecreatefrompng($resized_image);
			break;
			case 'jpg':
			$img = imagecreatefromjpeg($resized_image);
			break;
			}
			$img_w = imagesx($img); 
			$img_h = imagesy($img); 
			$wtrmrk_w = imagesx($watermark); 
			$wtrmrk_h = imagesy($watermark); 
			imagecopy($img, $watermark, 20, $img_h - $wtrmrk_h - 20, 0, 0, $wtrmrk_w, $wtrmrk_h); 
			imagejpeg($img, $newcopy, 100); 
			$imageObj = new ImageResize($newcopy);
			$imageObj->setMaxDimensions($w, $h);
			$imageObj->displayImage();
			}else{
			$image->displayImage();
			}
			
			imagedestroy($img); 
			imagedestroy($watermark); 
			return true;
		}
		
		
		public static function showOriginalImage($file='',$path_suffix){
			if($file === '' || !is_string($file) || strlen(trim($file)) < 1) $file = 'no-img.jpg';
			$file=self::getRealFilePath($file);
			$img = CONF_INSTALLATION_PATH . 'user-uploads/'. $path_suffix . $file;
			if(!file_exists($img)) $img = CONF_INSTALLATION_PATH . 'user-uploads/' . $path_suffix . $no_img;
			if(!file_exists($img)) $img = CONF_INSTALLATION_PATH . 'user-uploads/'  . $no_img;
			if(!file_exists($img)) $img = CONF_INSTALLATION_PATH . 'public/images/' . $no_img;
			
			//header("content-type: image/jpeg");
			$filename = basename($file);
			$file_extension = strtolower(substr(strrchr($filename,"."),1));
			if ($file_extension!="svg"){
			header("content-type: image/jpeg");
			}else{
			header("Content-type: image/svg+xml");
			}
			header('Cache-Control: public');
			header("Pragma: public");
			echo file_get_contents($img);
			return true;
			}
			
		
		public static function recursiveDelete($str) {
			if (is_file($str)) {
				return @unlink($str);
			}
			elseif (is_dir($str)) {
				$scan = glob(rtrim($str,'/').'/*');
				foreach($scan as $index=>$path) {
					self::recursiveDelete($path);
				}
			return @rmdir($str);
			}
		}
		
		public static function getCurrUploadDirPath($pathSuffix=''){ 
			$settingsObj=new Settings();
			if(trim($pathSuffix)!=''){
				$pathSuffix=rtrim($pathSuffix,'/').'/';
			}
			$uploadPath=self::getFilepathOnDirectory().$pathSuffix;
			
			// get current directory use to upload file
			$confPathSuffix=Settings::getSetting("CONF_CURR_PROD_UPLOAD_DIR"); 
			$confPathSuffix=(trim($confPathSuffix)!='')?$confPathSuffix.'/':'';
			$currUploadPath=$uploadPath.$confPathSuffix;
			
			// Create Directory if not exist
			if (!file_exists($currUploadPath)){ 
				mkdir($currUploadPath, 0777, true);
			}
			
			try{
				$fi = new FilesystemIterator($currUploadPath, FilesystemIterator::SKIP_DOTS);			
				$fileCount= iterator_count($fi); 			
				if(isset($fileCount) && $fileCount>=CONF_UPLOAD_MAX_FILE_COUNT){
					$dest=Settings::getSetting("CONF_CURR_PROD_UPLOAD_DIR")+1;
					// Create Directory if not exist	
					if (!file_exists($currUploadPath.$dest)){ 
						mkdir($currUploadPath.$dest, 0777, true);
					}
					$arr=array('CONF_CURR_PROD_UPLOAD_DIR'=>$dest);	
					if($settingsObj->update($arr)){
						$currUploadPath=$uploadPath.Settings::getSetting("CONF_CURR_PROD_UPLOAD_DIR").'/';
					}				
				}
			}catch(exception $e){	}
			
			$currUploadPath=Settings::getSetting("CONF_CURR_PROD_UPLOAD_DIR");
			$currUploadPath=(trim($currUploadPath)!='')?$currUploadPath.'/':'';
			return $pathSuffix.$currUploadPath;
		}
		
		public static function getUploadedFilePath($fname){
			if(trim($fname)=='') return;
			$currUploadPath=Settings::getSetting("CONF_CURR_PROD_UPLOAD_DIR");
			$currUploadPath=(trim($currUploadPath)!='')?$currUploadPath.'~':'';
			return $currUploadPath.$fname;
		}
		
		public static function getRealFilePath($fname=''){
			if($fname=='') return;
			return $fname=str_replace('~','/',$fname);
		}
		
		public static function isValidEmail($email){
			return filter_var($email, FILTER_VALIDATE_EMAIL);
		}
		
		public static function displayHtmlForm($frm,$seprator='</br>'){
			$str='';
			$str .= $frm->getFormTag();
			$total_fields=$frm->getFieldCount();
			for($i=0; $i<$total_fields; $i++){
			$fld=$frm->getFieldByNumber($i);
			if($fld->getAttached()) continue;
			if($fld->fldType=='hidden'){
				$str .= $fld->getHTML();
				continue;
			}
			if($fld->merge_cells<2 && !$fld->merge_caption){
				$str .= '<fieldset><label>' . $fld->field_caption . '</label>';
				$str .= $fld->getHTML();
				$str .= '</fieldset>';
			}else{
				$str .= '<fieldset>'.$fld->field_caption.'</fieldset>';;
			}
			
			}
			$str .= '</form>';
			$str .= $frm->getExternalJS();
			if (($frm->getRequiredStarWith()=="not-required"))
				$str = preg_replace('#<span class="spn_must_field">(.*?)</span>#is', '', $str);
			return $str;
		}
		
		
		
		public static function stripRequiredStar($str){
			return preg_replace('#<span class="spn_must_field">(.*?)</span>#is', '', $str);
		}
		

		public static function writeMetaTags(){
			$db = &Syspage::getDb();
			global $seo_friendly_url_models;
			$url_alias_controllers=$seo_friendly_url_models;
			$doNotShiftAliasArr=array('image','faqs','blog');
			$urlArray = array();
			/*$url=Utilities::currentPageURL();
			$url=str_replace(':'.$_SERVER["SERVER_PORT"],"",$url);	
			$url=str_replace(CONF_SERVER_PATH,"",$url);*/ 
			
			$get = getQueryStringData();
			$url = $get['url'];
			$urlArray = explode("/",$url);
			$urlParam=$urlArray[1];
			if ((!in_array($urlArray[0],$doNotShiftAliasArr)) && (in_array($urlParam,$url_alias_controllers)) && (count($urlArray)>2) ){
				array_shift($urlArray);
			}
			$controller = $urlArray[0];
			$action = $urlArray[1];
			$record_id = $urlArray[2];
					
			$url_alias=new Url_alias();
			$url_alias_info = $url_alias->getUrlAliasByKeyword(urldecode($record_id));
			if ($url_alias_info){
				$record_id=str_replace($controller."_id=","",$url_alias_info["url_alias_query"]);
			}
			
			switch($controller){
				case "home":
					$page_url = Utilities::getSiteUrl();
				break;
				case "cms":
					$cmsPageObj=new Cms();
					$page=$cmsPageObj->getData($record_id);
					if ($page):
						$page_title=$page["page_meta_title"];
						$page_meta_keywords=$page["page_meta_keywords"];
						$page_meta_description=$page["page_meta_desc"];
						$page_url = Utilities::generateAbsoluteUrl('cms', 'view',array($record_id));	
					endif;	
				break;
				case "brands":
						$brandObj=new Brands();
						$brand=$brandObj->getData($record_id);
						if ($brand):
							$page_title=$brand["brand_meta_title"];
							$page_meta_keywords=$brand["brand_meta_keywords"];
							$page_meta_description=$brand["brand_meta_desc"];
							$page_url = Utilities::generateAbsoluteUrl('brands', 'view',array($record_id));	
						endif;	
				break;
				case "category":
					$categoryObj=new Categories();
					$category=$categoryObj->getData($record_id);
					if ($category):
						$page_title=$category["category_meta_title"];
						$page_meta_keywords=$category["category_meta_keywords"];
						$page_meta_description=$category["category_meta_description"];	
						$page_url = Utilities::generateAbsoluteUrl('category', 'view',array($record_id));	
					endif;	
				break;
				case "shops":
					$shopObj=new Shops();
					$shop=$shopObj->getData($record_id);
					if ($shop):
						$page_title=$shop["shop_page_title"];
						$page_meta_keywords=$shop["shop_meta_keywords"];
						$page_meta_description=$shop["shop_meta_description"];
						$page_url = Utilities::generateAbsoluteUrl('shops', 'view',array($record_id));		
					endif;	
				break;
				case "products":
					$prodObj=new Products();
					$prodObj->joinWithDetailTable();
					$prodObj->selectFields(array('tp.*','ts.shop_id','ts.shop_user_id','ts.shop_name','ts.shop_title','tu.user_id','tu.user_name','tu.user_username','tu.user_email','IF(prod_stock >0, "1", "0" ) as available','tpd.*'));
					$product=$prodObj->getData($record_id);
					if ($product):
						$page_title=$product["prod_meta_title"];
						$page_meta_keywords=$product["prod_meta_keywords"];
						$page_meta_description=$product["prod_meta_description"];
						$page_url = Utilities::generateAbsoluteUrl('products', 'view',array($record_id));		
					endif;	
				break;
				
			}
			if ($action!='view'){
				$page_url = Utilities::generateAbsoluteUrl($controller, $action,array($record_id));
			}
			$display_page_title=$page_title!=""?$page_title:Settings::getSetting("CONF_PAGE_TITLE");
			$display_meta_keywords=$page_meta_keywords!=""?$page_meta_keywords:Settings::getSetting("CONF_META_KEYWORD");
			$display_meta_desc=$page_meta_description!=""?$page_meta_description:Settings::getSetting("CONF_META_DESCRIPTION");
			
			echo ("<title>".htmlentities($display_page_title)."</title>\n");
			echo ("<meta name='description' content='".htmlentities($display_meta_desc)."' />\n");
			echo ("<meta name='keywords' content='".htmlentities($display_meta_keywords)."' />\n");
			echo ("<link href='".$page_url."' rel='canonical' />\n");
		}
		
		
		public static function verifyCaptcha($fld_name='g-recaptcha-response') {
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
		
		
		public static function printArray() {
			if (func_num_args()) {
				echo '<pre>';
				array_map('print_r' , func_get_args());
				echo '</pre>';
			}
		}
		
		public static function printArrayToString() {
			ob_start();
			array_map('self::printArray' , func_get_args());
			return ob_get_clean();
		}
		
		public static function getLabel($key, $namespace=''){
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
			return $lang_array[$key] = self::strip_javascript($val);
		}
		
		
		
		public static function renderHtml($content='',$stripJs=true) {
			$str=html_entity_decode(htmlspecialchars_decode($content));
			$str=($stripJs==true)?self::strip_unsafe($str):$str;
			return $str;
		}
		
		function getBaseUrl() {
		    $currentPath = $_SERVER['PHP_SELF']; 
    	    $hostName = $_SERVER['HTTP_HOST']; 
    	    $protocol ="http://";
			if (isset($_SERVER['HTTPS']) && $_SERVER["HTTPS"] == "on") { 
				$protocol = "https://";
			}
		    return $protocol.$hostName."/";
		}
		
		public static function strip_javascript($content='') {
			$javascript = '/<script[^>]*?>.*?<\/script>/si';
			$noscript = '';
			return preg_replace($javascript, $noscript, $content);
		}
		
		public static function myTruncateCharacters($string, $limit, $break = " ", $pad = "...") {
			if (strlen($string) <= $limit)
				return $string;
		
			$string = substr($string, 0, $limit);
			if (false !== ($breakpoint = strrpos($string, $break))) {
				$string = substr($string, 0, $breakpoint);
			}
			return $string . $pad;
		}
		
		public static function  redirectUser($url=''){
			if ($url == '') $url = $_SERVER['REQUEST_URI'];
			header("Location: " . $url);
			exit;
		}
		
		public static function  isAjaxRequest() {
			$post = Syspage::getPostedVar();
			if (isset($post['is_ajax_request']) && strtolower($post['is_ajax_request'])=='yes') {
				return true;
			} elseif(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])=='xmlhttprequest') {
				return true;
			}
			return false;
		}
		
		public static function getCurrUrl() {
		 return self::getUrlScheme() . $_SERVER["REQUEST_URI"];
		}
		
		public static function  getUrlScheme() {
		 $pageURL = 'http';
		 if (isset($_SERVER['HTTPS']) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		 $pageURL .= "://";
		 //if ($_SERVER["SERVER_PORT"] != "80") {
		  //$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
		 //} else {
		  $pageURL .= $_SERVER["SERVER_NAME"];
		 //}
		 return $pageURL;
		}
		
		public static function redirectUserReferer() {
			if (!defined(REFERER)) {
				if (self::getCurrUrl()==$_SERVER['HTTP_REFERER'] || empty($_SERVER['HTTP_REFERER']))
					define('REFERER', CONF_WEBROOT_URL);
				else
					define('REFERER', $_SERVER['HTTP_REFERER']);
			}
			self::redirectUser(REFERER);
		}
		
		public static function  redirectUser404() {
			self::redirectUser(self::generateUrl('index', 'error', array(404)));
		}
		
		public static function  getUrlData() {
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
		
		public static function getUrlQuery() {
			$get=getQueryStringData();
			return $get;
		}
		
		public static function show404() {
			$c = new IndexController('index', 'index', 'error');
			$c->error(404);
		}
		
		
		// User Interface public static function s
		public static function reloadPage() {
			header('Location: '.$_SERVER['REQUEST_URI']);
			exit;
		}
		// User Interface public static function s Ends
		
		
		public static function getSiteUrl() {
			return self::generateUrl();
		}
		
		
		public static function setSessionRedirectUrl($url) {
			$_SESSION['sess_return_url'] = $url;
		}
		
		public static function getSessionRedirectUrl() {
			return $_SESSION['sess_return_url'];
		}
		
		public static function unsetSessionRedirectUrl() {
			$_SESSION['sess_return_url'] = null;
		}
		
		
		public static function setSessionValue($key, $value='') {
			$_SESSION[$key]=$value;
		}
		
		public static function getSessionValue($key) {
			return $_SESSION[$key];
		}
		
		public static function unsetSessionValue($key) {
			unset($_SESSION[$key]);
		}
		
		public static function checkLogin($redirect=true) {
			if (!User::isUserLogged()) {
				$_SESSION['go_to_referer_page'] = self::getCurrUrl();
				if($redirect==true) {
					self::redirectUser(self::generateUrl('user', 'account'));
				} else {
					return false;
				}
			}
			return true;
		}
		
		public static function checkBuyerLogin($redirect=true) {
			if (!User::isBuyerLogged()) {
				$_SESSION['go_to_referer_page'] = self::getCurrUrl();
				if($redirect==true) {
					self::redirectUser(self::generateUrl('user', 'account'));
				} else {
					return false;
				}
			}
			return true;
		}
		
		public static function checkIsAlreadyLoggedIn($redirect=true){
			if (User::isUserLogged()) {
				if($redirect==true) {
					self::redirectUser(self::generateUrl('account'));
				} else {
					return true;
				}
			}
			return false;
		}
		
		
		public static function getViewsPartialPath() {
			return CONF_APPLICATION_PATH . 'views/_partial/';
		}
		
		public static function renderView($fname, $vars=array(), $return=true) {
			ob_start();
			extract($vars);
			include $fname;
			$contents = ob_get_clean();
			if ($return==true) {
				return $contents;
			} else {
				echo $contents;
			}
		}
		
		public static function slugify($text){ 
		  // replace non letter or digits by -
		  $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
		  // trim
		  $text = trim($text, '-');
		  // transliterate
		  if (function_exists('iconv')) {
		  	$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
		  }
		  // lowercase
		  $text = strtolower($text);
		  // remove unwanted characters
		  $text = preg_replace('~[^-\w]+~', '', $text);
		  if (empty($text)){
			return 'n-a';
		  }
		  return $text;
		}
		
		
		
		public static function displayMoneyFormat($val,$format=true,$currency=true,$inc_tax=false){
			/*if ($inc_tax){ 
				$val = $val + round(($val)*Settings::getSetting("CONF_SITE_TAX")/100,2);
			}*/
			if ($format){ 
				$val=number_format($val,CONF_CURRENCY_DECIMAL_PLACES);
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
		
		
		
		public static function dateFormat($format, $date) {
			return date("$format", strtotime($date));
		}
		
		public static function formatdate($dt,$displayTime=false){
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
					//if ($_SERVER["SERVER_PORT"] != "80") {
					  //$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
					 //} else {
					  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
					//}
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
		
		
		public static function aasort(&$array, $key) {
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
				self::recursiveDelete($target);
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
						self::full_copy( $Entry, $target . '/' . $entry );
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
			//fprintf($temp_memory, chr(0xEF).chr(0xBB).chr(0xBF));
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
		
		function run_curl( $endpoint = '' , $post_data = false, $post_json = array() ){
			$ch = @curl_init();
			
			if( $post_data ){
				@curl_setopt($ch, CURLOPT_POST, $post_data);
				@curl_setopt($ch, CURLOPT_POSTFIELDS, $post_json);
			}
			
			@curl_setopt($ch, CURLOPT_URL, $endpoint);
			@curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$response = @curl_exec($ch);
			$status_code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$curl_errors = curl_error($ch);
			@curl_close($ch);
			return json_decode($response);			
		}

		
		function createHiddenFormFromArray($frmName,$action = '',$add = array(),$extras = ''){
			$str = '<form name="'.$frmName.'" id="'.$frmName.'" action="'.$action.'" '.$extras.'>';
			foreach ($add as $key => $value) {
					if (is_array($value)){
						foreach ($value as $skey => $svalue) {
							$str .= '<input type="hidden" name="'.$key.'[]" value="'.$svalue.'">';
						}
					}else{
						$str .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
					}
			}
			$str .= '</form>';
			return $str;
		}
		
		public static function getFileSize($file_path) {
			$file_path = CONF_USER_UPLOADS_PATH . preg_replace('/[^a-zA-Z0-9\/\-\_\.]/', '', $file_path);
			if (file_exists($file_path)) {
				$size = filesize($file_path);
				$i = 0;
				$suffix = array(
					'B',
					'KB',
					'MB',
					'GB',
					'TB',
					'PB',
					'EB',
					'ZB',
					'YB'
				);
				while (($size / 1024) > 1) {
					$size = $size / 1024;
					$i++;
				}
				
				$size = round(substr($size, 0, strpos($size, '.') + 4), 2) . $suffix[$i];
			}
			return $size;
		}
		
		static function strip_unsafe($string, $img=true){
				// Unsafe HTML tags that members may abuse
				$unsafe=array(
				'/<iframe(.*?)<\/iframe>/is',
				'/<title(.*?)<\/title>/is',
				'/<pre(.*?)<\/pre>/is',
				'/<frame(.*?)<\/frame>/is',
				'/<frameset(.*?)<\/frameset>/is',
				'/<object(.*?)<\/object>/is',
				'/<script(.*?)<\/script>/is',
				'/<embed(.*?)<\/embed>/is',
				'/<applet(.*?)<\/applet>/is',
				'/<meta(.*?)>/is',
				'/<!doctype(.*?)>/is',
				'/<link(.*?)>/is',
				'/<body(.*?)>/is',
				'/<\/body>/is',
				'/<head(.*?)>/is',
				'/<\/head>/is',
				'/onload="(.*?)"/is',
				'/onunload="(.*?)"/is',
				'/<html(.*?)>/is',
				'/<\/html>/is');
			
				// Remove graphic too if the user wants
				if ($img==true)
				{
					$unsafe[]='/<img(.*?)>/is';
				}
			
				// Remove these tags and all parameters within them
				$string=preg_replace($unsafe, "", $string);
			
				return $string;
		}
		
		function rotateimage($degrees,$filename){
             $new_file=basename($filename);
             $rotang = $degrees;
             list($width, $height, $type, $attr) = getimagesize($filename);
              $size = getimagesize($filename);
              switch($size['mime'])
              {
                 case 'image/jpeg':
                            $source = imagecreatefromjpeg($filename);
                            $bgColor=imageColorAllocateAlpha($source, 0, 0,0, 0);
                            $rotation = imagerotate($source,$rotang,$bgColor);
							imagealphablending($rotation, false);
							imagesavealpha($rotation, true);
							imagecreate($width,$height);
							imagejpeg($rotation,$filename);
							chmod($filename, 0777);
                 break;
                 case 'image/png':
									 
                                     $source =
         imagecreatefrompng($filename);
                                     $bgColor=imageColorAllocateAlpha($source, 0, 0,
         0, 0);
                                     $rotation = imagerotate($source,
         $rotang,$bgColor);
                                     imagealphablending($rotation, false);
                                     imagesavealpha($rotation, true);
                                     imagecreate($width,$height);
                                     imagepng($rotation,$filename);
                                     chmod($filename, 0777);
                 break;
                 case 'image/gif':

                                     $source =
         imagecreatefromgif($filename);
                                     $bgColor=imageColorAllocateAlpha($source, 0, 0,
         0, 0);
                                     $rotation = imagerotate($source,
         $rotang,$bgColor);
                                     imagealphablending($rotation, false);
                                     imagesavealpha($rotation, true);
                                     imagecreate($width,$height);
                                     imagegif($rotation,$filename);
                                     chmod($filename, 0777);
                 break;
                 case 'image/vnd.wap.wbmp':
                                     $source =
         imagecreatefromwbmp($filename);
                                     $bgColor=imageColorAllocateAlpha($source, 0, 0,
         0, 0);
                                     $rotation = imagerotate($source,
         $rotang,$bgColor);
                                     imagealphablending($rotation, false);
                                     imagesavealpha($rotation, true);
                                     imagecreate($width,$height);
                                     imagewbmp($rotation,$filename);
                                     chmod($filename, 0777);
                 break;
              }
		}
		
				
		
	}
?>