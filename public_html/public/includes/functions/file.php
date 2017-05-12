<?php
/**
 * 
 * Saves an image
 * @param String $fl full path of the file
 * @param String $name name of file
 * @param String $response In case of success exact name of file will be returned in this else error message
 * @return Boolean
 */


function saveImage($fl, $name, &$response, $pathSuffix='',$replace=false){
	$pathSuffix=Utilities::getCurrUploadDirPath($pathSuffix);
    $fname = preg_replace('/[^a-zA-Z0-9\/\-\_\.]/', '', $name);
    if($replace!==true) {
    	while (file_exists(CONF_INSTALLATION_PATH . 'user-uploads/' . $pathSuffix . $fname)){
        	$fname = rand(10, 999999).'_'.$fname;
	    }
	}

    if (!copy($fl, CONF_INSTALLATION_PATH . 'user-uploads/' . $pathSuffix . $fname)){
        $response = 'Could not save file.';
        return false;
    }

    $response = Utilities::getUploadedFilePath($fname);
    return true;
}

function saveFile($fl, $name, &$response, $pathSuffix=''){
	$pathSuffix=Utilities::getCurrUploadDirPath($pathSuffix);
    $fname = preg_replace('/[^a-zA-Z0-9\/\-\_\.]/', '', $name);
    while (file_exists(CONF_INSTALLATION_PATH . 'user-uploads/' . $pathSuffix . $fname)){
        $fname = rand(10, 999999).'_'.$fname;
    }

    if (!copy($fl, CONF_INSTALLATION_PATH . 'user-uploads/' . $pathSuffix . $fname)){
        $response = 'Could not save file.';
        return false;
    }

    $response = Utilities::getUploadedFilePath($fname);
    return true;
}

function isUploadedFileValidImage($files) {
	$valid_mime_types = preg_replace('~\r?\n~', "\n", Settings::getSetting("CONF_IMAGE_MIME_ALLOWED"));
	$valid_arr = explode("\n", $valid_mime_types);
    return (isset($files['name'])
            && $files['error']==0
            && in_array($files['type'], $valid_arr)
            && $files['size']>0);
}

function isUploadedFileValidFile($files) {
	$valid_mime_types = preg_replace('~\r?\n~', "\n", Settings::getSetting("CONF_FILE_MIME_ALLOWED"));
	$valid_arr = explode("\n", $valid_mime_types);
    return (isset($files['name'])
            && $files['error']==0
            && in_array($files['type'], $valid_arr)
            && $files['size']>0);
}

function isValidFileUploaded($files) {
    return (isset($files['name'])
            && $files['error']==0
            && $files['size']>0);
}

/**
 *
 * Write contents to a file. Overwrites existing contents.
 * @param String $name Name of file
 * @param String $data Contents to write on file
 * @param String $response In case of success exact name of file will be returned in this else error message
 * @return Boolean
 */
function writeFile($name, $data, &$response) {
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

function getFilepathOnDirectory($rs) {
    return CONF_USER_UPLOADS_PATH . $rs;
}

function outputFile($filepath, $name='') {	
	$filepath=Utilities::getRealFilePath($filepath);
    $filepath = getFilepathOnDirectory($filepath);
    if (empty($filepath) || !is_file($filepath)) {
        return ;
    }
    header("Content-type: application/octet-stream");
    header('Content-Disposition: attachement; filename="'.basename($filepath).'"');
    header('Content-Length: ' . filesize($filepath));
    readfile($filepath);
}

function outputImage($fname, $width, $height, $default='', $enable_cache=true) {
    if ($default=='') {
        //$default = CONF_INSTALLATION_PATH . 'public/images/126pic.jpg';
        $default = CONF_USER_UPLOADS_PATH . 'no-image.jpg';
    }
	$fname=Utilities::getRealFilePath($fname);
    $fname = getFilepathOnDirectory($fname);
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
    Utilities::writeFile($cache, $contents);
    echo file_get_contents($cache_fname);exit;
}

function unlinkFile($name) {
    @unlink(CONF_USER_UPLOADS_PATH . $name);
}

function deleteFile($file, $path_suffix) {
    if ($file === '' || !is_string($file) || strlen(trim($file)) < 1)
        return false;
    $file_fullpath = CONF_INSTALLATION_PATH . 'user-uploads/' . $path_suffix . $file;
    if (file_exists($file_fullpath)) {
        unlink($file_fullpath);
        return true;
    }
    return false;
}

function outputOriginalImage($fname,$default='',$enable_cache=true) {
    if ($default=='') {
        //$default = CONF_INSTALLATION_PATH . 'public/images/126pic.jpg';
        $default = CONF_USER_UPLOADS_PATH . 'noimage.jpg';
    }
	$fname=Utilities::getRealFilePath($fname);
    $fname = getFilepathOnDirectory($fname);
    if (!file_exists($fname) || !is_file($fname)) {
        $fname = $default;
    }
    header("Content-type: image/jpeg");
    header('Content-Disposition: attachement; filename="'.basename($fname).'"');
    
    $cache = 'cache/' . md5($fname). '-' . $width . 'x' . $height . '.jpg';
    $cache_fname = CONF_USER_UPLOADS_PATH . $cache;
    if ($enable_cache==true && file_exists($cache_fname) && is_file($cache_fname)) {
        echo file_get_contents($cache_fname);exit;
    }
    $image = new ImageResize($fname);
	$size = getimagesize($fname);
    $image->setMaxDimensions($size[0], $size[1]);
    $image->setExtraSpaceColor(255,255,255);
    $image->setResizeMethod(2);
    ob_start();
    $image->displayImage();
    $contents = ob_get_clean();
    Utilities::writeFile($cache, $contents);
    
    echo file_get_contents($cache_fname);exit;
}

function uploadFile($name, $data, &$response) {
	$fname=CONF_USER_UPLOADS_PATH.preg_replace('/[^a-zA-Z0-9\/\-\_\.]/', '-', $name);
    $dest = dirname($fname);
    if (!file_exists($dest)) mkdir($dest, 0777, true);
	$rs=move_uploaded_file($data,$fname);
    if (!$rs) {
        $response = 'Could not save file.';
        return false;
    }

    $response = $fname;
    return true;
}

function moveFile($srcLoc, $destLoc) {
    $srcLocName = CONF_USER_UPLOADS_PATH . preg_replace('/[^a-zA-Z0-9\/\-\_\.]/', '', $srcLoc);
	$destLocName = CONF_USER_UPLOADS_PATH . preg_replace('/[^a-zA-Z0-9\/\-\_\.]/', '', $destLoc);
    $dest = dirname($destLoc);
    if (!file_exists($dest)) mkdir($dest, 0777, true);
	$rs=rename($srcLocName,$destLocName);
    if (!$rs) {
        return false;
    }
    return true;
}

function showImage($file='', $w=0, $h=0, $path_suffix='', $no_img = 'default.jpg',$apply_watermark=false){
	if($file === '' || !is_string($file) || strlen(trim($file)) < 1) $file = 'no-img.jpg';
		$file=Utilities::getRealFilePath($file);
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
	header("Expires: " . date('r', strtotime("+30 Minute")));
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($imgpath)).' GMT', true, 200);
	header("Content-Type: image/jpg");
	
	if ($apply_watermark){
		$image->saveImage($resized_image);
		$newcopy=CONF_INSTALLATION_PATH . 'user-uploads/resized/watermarked_'.$file_name.".jpg";
		$wtrmrk_file=CONF_INSTALLATION_PATH . 'user-uploads/'.Settings::getSetting("CONF_WATERMARK_IMAGE");
		$watermark = imagecreatefrompng($wtrmrk_file); 
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


function showOriginalImage($file='',$path_suffix){
	if($file === '' || !is_string($file) || strlen(trim($file)) < 1) $file = 'no-img.jpg';
	$file=Utilities::getRealFilePath($file);
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


function getFileSize($srcFileName) {
    $srcLocName = CONF_USER_UPLOADS_PATH . $srcFileName;
	return round(filesize($srcLocName)/1024);
}

function recursiveDelete($str) {
    if (is_file($str)) {
        	return @unlink($str);
    }
    elseif (is_dir($str)) {
        $scan = glob(rtrim($str,'/').'/*');
        foreach($scan as $index=>$path) {
            Utilities::recursiveDelete($path);
        }
        return @rmdir($str);
    }
}

function getCurrUploadDirPath($pathSuffix=''){ 
	$settingsObj=new Settings();
	if(trim($pathSuffix)!=''){
		$pathSuffix=rtrim($pathSuffix,'/').'/';
	}
	$uploadPath=getFilepathOnDirectory().$pathSuffix;
	
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
		if(isset($fileCount) && $fileCount>=CONF_UPLOAD_MAX_FILE_COUNT)
		{
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

function getUploadedFilePath($fname)
{
	if(trim($fname)=='') return;
	
	$currUploadPath=Settings::getSetting("CONF_CURR_PROD_UPLOAD_DIR");
	$currUploadPath=(trim($currUploadPath)!='')?$currUploadPath.'~':'';
	return $currUploadPath.$fname;
}

function getRealFilePath($fname='')
{
	if($fname=='') return;
	return $fname=str_replace('~','/',$fname);
}