<?php
static $registry = null;
class Importexport extends Model {
	protected $db;
	
	function __construct($storeId=0) {
		$this->db = Syspage::getdb();
		$this->displayDateFormat='mm/dd/yyyy';	
		$this->displayDateTimeFormat='mm/dd/yyyy hh:mm:ss';	
		$this->convertDateFromExcelSheet='Y-m-d h:i:s';
		$this->storeId=$storeId;
		$this->exportImportSettings=$this->getSetting('export_import',$storeId);			
    }
	
	function isUploadedFileValidFile($files) {
		$valid_arr = array(
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'application/vnd.oasis.opendocument.spreadsheet',
			'application/octet-stream',
		); 
		 return (isset($files['name'])
            && $files['error']==0
            && in_array($files['type'], $valid_arr)
            && $files['size']>0);
	}
	
	function getError() {
        return $this->error;
    }	
		
	function getSetting($code, $store_id = 0) {
		$store_id = intval($store_id);
		$add_criteria=array();        
       	$add_criteria['setting_shop_id'] = $store_id;
       	$add_criteria['setting_code'] = $code;
		
        $srch = self::search($add_criteria);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
		
        $rs = $srch->getResultSet();
        $row = $this->db->fetch_all($rs);
		$setting_data=array();	
        if(!$row==false){ 
			foreach ($row as $result) {
				if (!$result['setting_serialized']) {
					$setting_data[$result['setting_key']] = $result['setting_value'];
				} else {
					$setting_data[$result['setting_key']] = json_decode($result['setting_value'], true);
				}
			}
		}
		if($store_id>0){
			$setting_data['export_import_settings_use_export_cache']=true;
			$setting_data['export_import_settings_use_import_cache']=true;
		}
		return $setting_data;
	}
	
	function editSetting($code, $data, $store_id = 0) {
		$store_id = intval($store_id);
		$this->deleteSetting($code,$store_id);	
		$record = new TableRecord('tbl_import_export_settings');
		foreach ($data as $key => $value) {
			if (substr($key, 0, strlen($code)) == $code) {								
				$assign_fields = array();
				$assign_fields['setting_shop_id']=$store_id;
				$assign_fields['setting_code']=$code;
				$assign_fields['setting_key']=$key;
				if (!is_array($value)) {
					$assign_fields['setting_value']=$value;
				}else{
					$assign_fields['setting_value']=json_encode($value);
				}	
				$record->assignValues($assign_fields);				
				$record->addNew();
			}
		}		
	}
	
	function addLog($data){
		$assign_fields = array();		
		$assign_fields['log_message']=json_encode($data);
		$assign_fields['log_url']=$_SERVER['REQUEST_URI'];
		$assign_fields['log_time']=date('Y-m-d H:i:s');
		$assign_fields['log_response_ip']=$_SERVER['REMOTE_ADDR'];
		$record = new TableRecord('tbl_log');
		$record->assignValues($assign_fields);				
		$record->addNew();
	}
	
	function deleteSetting($code, $store_id = 0) {						
		$this->db->query("delete from `tbl_import_export_settings` where setting_shop_id = '".intval($store_id)."' and setting_code = '".$code
."'"); 		
		return false;
	}
	
	function search($criteria, $count='') {
        $srch = new SearchBase('tbl_import_export_settings', 'ts');		
        foreach($criteria as $key=>$val) {
			if(strval($val)=='') continue;
            switch($key) {
            case 'setting_id':
                $srch->addCondition('ts.setting_id', '=', intval($val));
                break;
			case 'setting_shop_id':
			case 'setting_store_id':
                $srch->addCondition('ts.setting_shop_id', '=', $val);
                break;	
			case 'setting_code':
	            $srch->addCondition('ts.setting_code', '=', $val);
                break;	
			case 'limit':
                $srch->setPageSize($val);
                break;			
            }
        }
        return $srch;
    }
	
	function isUrl($url){
		$regex = "((https?|ftp)\:\/\/)?"; // SCHEME 
		$regex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?"; // User and Pass 
		$regex .= "([a-z0-9-.]*)\.([a-z]{2,3})"; // Host or IP 
		$regex .= "(\:[0-9]{2,5})?"; // Port 
		$regex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?"; // Path 
		$regex .= "(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?"; // GET Query 
		$regex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?"; // Anchor 
		if(preg_match("/^$regex$/", $url)) 
		{ 
		   return true; 
		}
		return false;		
	}
	
	function isValidImageUrl($url){					
		if(getimagesize($url)!== false){return true;}
		return false;
	}
	
	function formatDate($dt,$time=false)
	{
		if(trim($dt)=='' || $dt=='0000-00-00' || $dt=='0000-00-00 00:00:00'){return;}
		if($time==false){
			return date("m/d/Y",strtotime($dt));
		}
		return date('m/d/Y H:i:s',strtotime($dt)); 
	}
	
	function convertDateFormat($dt,$time=false)
	{
		$emptyDateArr=array('0000-00-00','0000-00-00 00:00:00','0000/00/00','0000/00/00 00:00:00','00/00/0000','00/00/0000 00:00:00','00/00/00','00/00/00 00:00:00');
		if(trim($dt)=='' || in_array($dt,$emptyDateArr)){return '0000-00-00';}
		//$dt = str_replace('/', '-', $dt);			
		$date = new DateTime($dt);
		$timeStamp=$date->getTimestamp();
		if($time==false){
			return date("Y-m-d",$timeStamp);
		}
		return date("Y-m-d H:i:s",$timeStamp); 
	}
	
	function getRemoteFileContent($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 200);
		curl_setopt($ch, CURLOPT_AUTOREFERER, false);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		$file = curl_exec($ch);
		if($file === false){return false;}
		curl_close ($ch);
		return $file;
	}
	
	function setLocalfile(&$localfile,$dirimg) {
		$imgnm = basename($localfile);
		preg_match('/^[0-9]*/i', $imgnm, $nrimg);
		if($nrimg[0] === ''){ $imgnm = '0_'. $imgnm;}
		else{ $imgnm = str_replace($nrimg[0], $nrimg[0] + 1, $imgnm);}
		$localfile = $dirimg. $imgnm;		
		if(file_exists($dirimg. $imgnm)){ return $this->setLocalfile($localfile,$dirimg);}		
		return $imgnm;
	}
	
	function getImageName($url)
	{		
		$imageName='';
		$isUrlArr=parse_url($url);
		if(is_array($isUrlArr) && isset($isUrlArr['host'])){
			if($this->isValidImageUrl($url)){			
				$imgFileContent=$this->getRemoteFileContent($url);
				if($imgFileContent){					
					$imageName=$this->uploadImage($imgFileContent,$url);				
				}
			}
		}else{
			$imageName=$url;
		}		
		return $imageName;
	}
	
	function uploadImage($imgFileContent,$url)
	{
		//$path=CONF_INSTALLATION_PATH . 'user-uploads/products/';
		$path=Utilities::getFilepathOnDirectory().Utilities::getCurrUploadDirPath('products/');
		$fname = preg_replace('/[^a-zA-Z0-9\/\-\_\.]/', '', basename($url));		
		$localfile = $path. $fname; 
		if(file_exists($localfile)){			
			$imageName=$this->setLocalfile($localfile,$path);
		}else{
			$imageName=$fname;
		}
		/* $fp = fopen($localfile,'x');
		fwrite($fp, $imgFileContent);
		fclose($fp); */
		file_put_contents($localfile, $imgFileContent);				
		return Utilities::getUploadedFilePath($imageName);
	}
			
	function upload($filename,$incremental,$shop_user_id=0)
	{			
		try{
			$_SESSION['export_import_nochange']=1;
			// we use the PHPExcel package from http://phpexcel.codeplex.com/
			$cwd = getcwd();
			chdir( CONF_INSTALLATION_PATH.'public/includes/APIs/PHPExcel' );
			require_once( 'Classes/PHPExcel.php' );			
			chdir( $cwd );
			
			// Memory Optimization
			if ($this->exportImportSettings['export_import_settings_use_import_cache']) {
				$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
				$cacheSettings = array( ' memoryCacheSize '  => '24MB'  );
				PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
			}
			
			// parse uploaded spreadsheet file
			$inputFileType = PHPExcel_IOFactory::identify($filename);
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setReadDataOnly(true);
			$reader = $objReader->load($filename);
				/* Check subscription plan */
			
			$products = $reader->getSheetByName('Products'); 
			if(intVal($shop_user_id)>0 && !empty($products)){ 
				$totalProducts = $products->getHighestRow();
				$productIds		= array(); 
				for ($i=0; $i<$totalProducts; $i+=1) {
					$j= 1;
					if ($i==0) {
						continue;
					}
					$product_id = trim($this->getCell($products,$i,$j++));
					if ($product_id=="") {
						continue;
					}
					if(!isset($productIds[$product_id])){
						$totalsProduct = $totalProducts-1;
						$productIds[$totalsProduct][] = $product_id; 
					}
				}	 
				$this->checkSubscriptionPlanForProductQty($productIds,$incremental,$shop_user_id);  
				
			}	 
			$ProductImages 		 	= $reader->getSheetByName( 'ProductImages' );  
			if(intVal($shop_user_id)>0 && !empty($ProductImages)){ 
				$totalImagesRows 		= $ProductImages->getHighestRow();
				$productImagesQty 		= array();
				for ($i=0; $i<$totalImagesRows; $i+=1) {
					$j= 1;
					if ($i==0) {
						continue;
					}
					$product_id = trim($this->getCell($ProductImages,$i,$j++));
					if ($product_id=="") {
						continue;
					}
					$image_name = trim($this->getCell($ProductImages,$i,$j++));
					if(!isset($productImagesQty[$product_id])){
						$productImagesQty[$product_id]['count'] =1;
						$productImagesQty[$product_id]['name'][] =$image_name;
					}else{
						$productImagesQty[$product_id]['count'] =$productImagesQty[$product_id]['count']+1;
						$productImagesQty[$product_id]['name'][] =$image_name;
					}
				}
				$this->checkSubscriptionPlanForProductImgs($productImagesQty,$incremental,$shop_user_id); 			
			}
			
			/* END Check subscription plan */
			
			// read the various worksheets and load them to the database			
			if (!$this->validateUpload( $reader,$shop_user_id)) { 
				/* Message::addErrorMessage(Utilities::getLabel('L_Invalid_Coloums_In_Uploaded_File')); */
				return false;
			}
			
			$_SESSION['export_import_nochange'] = 0;
			$available_product_ids = array();
			$available_seller_prod_ids = array();
			$available_category_ids = array();
			$available_tag_ids = array();
			
			//die('valid upload. pending to update data');	
			if(intVal($shop_user_id)>0){
				$this->uploadProducts( $reader, $incremental, $available_product_ids,$shop_user_id,$available_seller_prod_ids ); 
				$this->uploadSpecials( $reader, $incremental, $available_product_ids,$shop_user_id,$available_seller_prod_ids ); 
				$this->uploadDiscounts( $reader, $incremental, $available_product_ids,$shop_user_id,$available_seller_prod_ids );
				$this->uploadProductOptions( $reader, $incremental, $available_product_ids,$shop_user_id,$available_seller_prod_ids ); 
				$this->uploadProductOptionValues( $reader, $incremental, $available_product_ids,$shop_user_id,$available_seller_prod_ids );
				$this->uploadProductToTags( $reader, $incremental, $available_product_ids,$shop_user_id,$available_seller_prod_ids );
				$this->uploadProductAttributes( $reader, $incremental, $available_product_ids,$shop_user_id,$available_seller_prod_ids );
				$this->uploadProductFilters( $reader, $incremental, $available_product_ids,$shop_user_id,$available_seller_prod_ids );			
				$this->uploadProductShippingRates( $reader, $incremental, $available_product_ids,$shop_user_id,$available_seller_prod_ids );
				$this->uploadProductSkuStock( $reader, $incremental,$shop_user_id );
				$this->uploadProductImages( $reader, $incremental, $available_product_ids,$shop_user_id,$available_seller_prod_ids );
				$this->uploadTags( $reader,$incremental, $available_tag_ids,$shop_user_id );
			}else{
				
				$this->uploadCategories( $reader, $incremental, $available_category_ids );
				$this->uploadCategoryFilters( $reader, $incremental, $available_category_ids ); 
				$this->uploadCategoryCollections( $reader, $incremental, $available_category_ids );
				$this->uploadProducts( $reader, $incremental, $available_product_ids ); 
				$this->uploadSpecials( $reader, $incremental, $available_product_ids ); 
				$this->uploadDiscounts( $reader, $incremental, $available_product_ids );
				$this->uploadProductOptions( $reader, $incremental, $available_product_ids );
				$this->uploadProductOptionValues( $reader, $incremental, $available_product_ids );
				$this->uploadProductToTags( $reader, $incremental, $available_product_ids );
				$this->uploadProductAttributes( $reader, $incremental, $available_product_ids );
				$this->uploadProductFilters( $reader, $incremental, $available_product_ids );			
				$this->uploadProductShippingRates( $reader, $incremental, $available_product_ids );			
				$this->uploadProductImages( $reader, $incremental, $available_product_ids );
				$this->uploadOptions( $reader, $incremental );
				$this->uploadTags( $reader,$incremental,$available_tag_ids );
				$this->uploadOptionValues( $reader, $incremental );
				$this->uploadAttributeGroups( $reader, $incremental );
				$this->uploadAttributes( $reader, $incremental );
				$this->uploadFilterGroups( $reader, $incremental );
				$this->uploadFilters( $reader, $incremental );
				$this->uploadShippingDurations( $reader, $incremental );
				$this->uploadShippingCompanies( $reader, $incremental );
			}
			return true;			
		}catch(exception $e){
			$errstr = $e->getMessage();
			$errline = $e->getLine();
			$errfile = $e->getFile();
			$errno = $e->getCode();
			$_SESSION['export_import_error']=array( 'errstr'=>$errstr, 'errno'=>$errno, 'errfile'=>$errfile, 'errline'=>$errline );
			$this->addLog(array('message'=>$errstr.' line : '.$errline.' File : '.$errfile.' Error No: '.$errno));
			return false;
		}
	}
	
	protected function validateUpload( &$reader ,$shop_user_id=0)
	{
		$ok = true;	
		// worksheets must have correct heading rows
		if (!$this->validateCategories( $reader )) {
			$msg = str_replace( '%1', 'Categories', Utilities::getLabel( 'L_Invalid_Headers_In_Worksheet' ) );
			Message::addErrorMessage($msg);
			$this->addLog(array('message'=>$msg));
			$ok = false;
		}	
	
		if (!$this->validateCategoryFilters( $reader )) {
			$msg = str_replace( '%1', 'CategoryFilters', Utilities::getLabel( 'L_Invalid_Headers_In_Worksheet' ) );
			Message::addErrorMessage($msg);
			$this->addLog(array('message'=>$msg));			
			$ok = false;
		}
		if (!$this->validateProducts( $reader,$shop_user_id )) {
			$msg = str_replace( '%1', 'Products', Utilities::getLabel( 'L_Invalid_Headers_In_Worksheet' ) );
			Message::addErrorMessage($msg);
			$this->addLog(array('message'=>$msg));				
			$ok = false;
		}
		if (!$this->validateProductPrices( $reader,$shop_user_id )) {
			
			$ok = false;
		}
		if (!$this->validateProductImages( $reader )) {
			$msg = str_replace( '%1', 'ProductImages', Utilities::getLabel( 'L_Invalid_Headers_In_Worksheet' ) );
			Message::addErrorMessage($msg);
			$this->addLog(array('message'=>$msg));			
			$ok = false;
		}
		if (!$this->validateSpecials( $reader )) { 
			$msg = str_replace( '%1', 'SpecialDiscounts', Utilities::getLabel( 'L_Invalid_Headers_In_Worksheet' ) );
			Message::addErrorMessage($msg);
			$this->addLog(array('message'=>$msg));			
			$ok = false;
		}
		if (!$this->validateDiscounts( $reader )) {
			$msg = str_replace( '%1', 'QuantityDiscounts', Utilities::getLabel( 'L_Invalid_Headers_In_Worksheet' ) );
			Message::addErrorMessage($msg);
			$this->addLog(array('message'=>$msg));			
			$ok = false;
		}		
		if (!$this->validateProductOptions( $reader )) { 
			$msg = str_replace( '%1', 'ProductOptions', Utilities::getLabel( 'L_Invalid_Headers_In_Worksheet' ) );
			Message::addErrorMessage($msg);
			$this->addLog(array('message'=>$msg));			
			$ok = false;
		}
		if (!$this->validateProductOptionValues( $reader )) {
			$msg = str_replace( '%1', 'ProductOptionValues', Utilities::getLabel( 'L_Invalid_Headers_In_Worksheet' ) );
			Message::addErrorMessage($msg);
			$this->addLog(array('message'=>$msg));					
			$ok = false;
		}
		if (!$this->validateProductAttributes( $reader )) { 		
			$msg = str_replace( '%1', 'Specifications', Utilities::getLabel( 'L_Invalid_Headers_In_Worksheet' ) );
			Message::addErrorMessage($msg);
			$this->addLog(array('message'=>$msg));			
			$ok = false;
		}
		if (!$this->validateProductFilters( $reader )) {
			$msg = str_replace( '%1', 'ProductFilters', Utilities::getLabel( 'L_Invalid_Headers_In_Worksheet' ) );
			Message::addErrorMessage($msg);
			$this->addLog(array('message'=>$msg));			
			$ok = false;
		}
		if (!$this->validateProductShippingRates( $reader )) { 
			$msg = str_replace( '%1', 'ProductShippingRates', Utilities::getLabel( 'L_Invalid_Headers_In_Worksheet' ) );
			Message::addErrorMessage($msg);
			$this->addLog(array('message'=>$msg));			
			$ok = false;
		}
		if (!$this->validateOptions( $reader )) {
			$msg = str_replace( '%1', 'Options', Utilities::getLabel( 'L_Invalid_Headers_In_Worksheet' ) );
			Message::addErrorMessage($msg);
			$this->addLog(array('message'=>$msg));			
			$ok = false;
		}
		if (!$this->validateOptionValues( $reader )) {
			$msg = str_replace( '%1', 'OptionValues', Utilities::getLabel( 'L_Invalid_Headers_In_Worksheet' ) );
			Message::addErrorMessage($msg);
			$this->addLog(array('message'=>$msg));			
			$ok = false;
		}
		if (!$this->validateAttributeGroups( $reader )) {
			$msg = str_replace( '%1', 'AttributeGroup', Utilities::getLabel( 'L_Invalid_Headers_In_Worksheet' ) );
			Message::addErrorMessage($msg);
			$this->addLog(array('message'=>$msg));			
			$ok = false;
		}
		if (!$this->validateAttributes( $reader )) {
			$msg = str_replace( '%1', 'Attributes', Utilities::getLabel( 'L_Invalid_Headers_In_Worksheet' ) );
			Message::addErrorMessage($msg);
			$this->addLog(array('message'=>$msg));			
			$ok = false;
		}
		if (!$this->validateFilterGroups( $reader )) {
			$msg = str_replace( '%1', 'FilterGroups', Utilities::getLabel( 'L_Invalid_Headers_In_Worksheet' ) );
			Message::addErrorMessage($msg);
			$this->addLog(array('message'=>$msg));			
			$ok = false;
		}
		if (!$this->validateFilters( $reader )) {
			$msg = str_replace( '%1', 'Filters', Utilities::getLabel( 'L_Invalid_Headers_In_Worksheet' ) );
			Message::addErrorMessage($msg);
			$this->addLog(array('message'=>$msg));				
			$ok = false;
		}		
		if (!$this->validateShippingDurations( $reader )) {
			$msg = str_replace( '%1', 'ShippingDurations', Utilities::getLabel( 'L_Invalid_Headers_In_Worksheet' ) );
			Message::addErrorMessage($msg);
			$this->addLog(array('message'=>$msg));				
			$ok = false;
		}
		if (!$this->validateProductStockAndSku( $reader )) {
			$msg = str_replace( '%1', 'StockAndSku', Utilities::getLabel( 'L_Invalid_Headers_In_Worksheet' ) );
			Message::addErrorMessage($msg);
			$this->addLog(array('message'=>$msg));			
			$ok = false;
		}
		
		if (!$this->validateShippingCompanies( $reader )) {	
			$msg = str_replace( '%1', 'ShippingCompanies', Utilities::getLabel( 'L_Invalid_Headers_In_Worksheet' ) );
			Message::addErrorMessage($msg);
			$this->addLog(array('message'=>$msg));			
			$ok = false;
		}
		// certain worksheets rely on the existence of other worksheets
		$names = $reader->getSheetNames();
		$exist_categories = false;
		$exist_category_filters = false;
		$exist_category_collections = false;
		$exist_product_options = false;	
		$exist_product_option_values = false;		
		$exist_products = false;
		$exist_additional_images = false;
		$exist_specials = false;
		$exist_discounts = false;
		$exist_product_attributes = false;
		$exist_product_filters = false;
		$exist_product_groups = false;
		$exist_filters = false;
		$exist_filter_groups = false;
		$exist_attributes = false;
		$exist_attribute_groups = false;
		$exist_options = false;
		$exist_option_values = false;
		$exist_Prod_stock_sku = false;
		
		foreach ($names as $name) {
			
			if ($name=='Categories') { 
				$exist_categories = true;
				continue;
			}
			if ($name=='CategoryFilters') {
				if (!$exist_categories) {					
					// Missing Categories worksheet, or Categories worksheet not listed before CategoryFilters	
					$msg = str_replace( '%2', 'Categories', str_replace( '%1', 'CategoryFilters',Utilities::getLabel( 'L_Missing_Worksheet_Or_Not_Listed_Before' ) ) );					
					Message::addErrorMessage($msg);
					$this->addLog(array('message'=>$msg));					
					$ok = false;
				}
				$exist_category_filters = true;
				continue;
			}
			
			if($name=='CategoryCollections'){
				if (!$exist_categories) {
					$msg = str_replace( '%2', 'Categories', str_replace( '%1', 'CategoryCollections',Utilities::getLabel( 'L_Missing_Worksheet_Or_Not_Listed_Before' ) ) );					
					Message::addErrorMessage($msg);
					$this->addLog(array('message'=>$msg));					
					$ok = false;
				}
				$exist_category_collections = true;
				continue;
			}
			
			if ($name=='Products') {
				$exist_products = true;
				continue;
			}
			
			if ($name=='ProductOptions') {
				if (!$exist_products) {
					// Missing Products worksheet, or Products worksheet not listed before ProductOptions		
					$msg = str_replace( '%2', 'Products', str_replace( '%1', 'ProductOptions',Utilities::getLabel( 'L_Missing_Worksheet_Or_Not_Listed_Before' ) ) );					
					Message::addErrorMessage($msg);
					$this->addLog(array('message'=>$msg));										
					$ok = false;
				}
				$exist_product_options = true;
				continue;
			}
			if ($name=='ProductOptionValues') {
				if (!$exist_products) {
					// Missing Products worksheet, or Products worksheet not listed before ProductOptionValues		
					$msg = str_replace( '%2', 'Products', str_replace( '%1', 'ProductOptionValues',Utilities::getLabel( 'L_Missing_Worksheet_Or_Not_Listed_Before' ) ) );					
					Message::addErrorMessage($msg);
					$this->addLog(array('message'=>$msg));						
					$ok = false;
				}
				if (!$exist_product_options) {					
					$msg = str_replace( '%2', 'ProductOptions', str_replace( '%1', 'ProductOptionValues',Utilities::getLabel( 'L_Missing_Worksheet_Or_Not_Listed_Before' ) ) );					
					Message::addErrorMessage($msg);
					$this->addLog(array('message'=>$msg));					
					$ok = false;
				}
				$exist_product_option_values = true;
				continue;
			}
			if ($name=='ProductImages') {
				if (!$exist_products) {
					// Missing Products worksheet, or Products worksheet not listed before ProductImages					
					$msg = str_replace( '%2', 'Products', str_replace( '%1', 'ProductImages',Utilities::getLabel( 'L_Missing_Worksheet_Or_Not_Listed_Before' ) ) );					
					Message::addErrorMessage($msg);
					$this->addLog(array('message'=>$msg));					
					$ok = false;
				}
				$exist_additional_images = true;
				continue;
			}
			if ($name=='SpecialDiscounts') {
				if (!$exist_products) {
					// Missing Products worksheet, or Products worksheet not listed before SpecialDiscounts
					$msg = str_replace( '%2', 'Products', str_replace( '%1', 'SpecialDiscounts',Utilities::getLabel( 'L_Missing_Worksheet_Or_Not_Listed_Before' ) ) );					
					Message::addErrorMessage($msg);
					$this->addLog(array('message'=>$msg));										
					$ok = false;
				}
				$exist_specials = true;
				continue;
			}
			if ($name=='QuantityDiscounts') {
				if (!$exist_products) {
					// Missing Products worksheet, or Products worksheet not listed before QuantityDiscounts
					$msg = str_replace( '%2', 'Products', str_replace( '%1', 'QuantityDiscounts',Utilities::getLabel( 'L_Missing_Worksheet_Or_Not_Listed_Before' ) ) );					
					Message::addErrorMessage($msg);
					$this->addLog(array('message'=>$msg));
					$ok = false;
				}
				$exist_discounts = true;
				continue;
			}
			
			if ($name=='Specifications') {
				if (!$exist_products) {					
					// Missing Products worksheet, or Products worksheet not listed before Specifications
					$msg = str_replace( '%2', 'Products', str_replace( '%1', 'Specifications',Utilities::getLabel( 'L_Missing_Worksheet_Or_Not_Listed_Before' ) ) );					
					Message::addErrorMessage($msg);
					$this->addLog(array('message'=>$msg));							
					$ok = false;
				}
				$exist_product_attributes = true;
				continue;
			}
			if ($name=='AttributeGroups') {
				$exist_attribute_groups = true;
				continue;
			}
			if ($name=='Attributes') {
				if (!$exist_attribute_groups) {					
					// Missing AttributeGroups worksheet, or AttributeGroups worksheet not listed before Attributes
					$msg = str_replace( '%2', 'AttributeGroups', str_replace( '%1', 'Attributes',Utilities::getLabel( 'L_Missing_Worksheet_Or_Not_Listed_Before' ) ) );					
					Message::addErrorMessage($msg);
					$this->addLog(array('message'=>$msg));						
					$ok = false;
				}
				$exist_attributes = true;
				continue;
			}
			if ($name=='ProductFilters') {
				if (!$exist_products) {
					// Missing Products worksheet, or Products worksheet not listed before ProductFilters
					$msg = str_replace( '%2', 'Products', str_replace( '%1', 'ProductFilters',Utilities::getLabel( 'L_Missing_Worksheet_Or_Not_Listed_Before' ) ) );					
					Message::addErrorMessage($msg);
					$this->addLog(array('message'=>$msg));											
					$ok = false;
				}
				$exist_product_filters = true;
				continue;
			}
			if ($name=='FilterGroups') {
				$exist_filter_groups = true;
				continue;
			}
			
			if ($name=='Filters') {
				if (!$exist_filter_groups) {					
					// Missing FilterGroups worksheet, or FilterGroups worksheet not listed before Filters
					$msg = str_replace( '%2', 'FilterGroups', str_replace( '%1', 'Filters',Utilities::getLabel( 'L_Missing_Worksheet_Or_Not_Listed_Before' ) ) );					
					Message::addErrorMessage($msg);
					$this->addLog(array('message'=>$msg));						
					$ok = false;
				}
				$exist_filters = true;
				continue;
			}
			if ($name=='Options') {
				$exist_options = true;
				continue;
			}
			if ($name=='StockAndSKU') {
				$exist_Prod_stock_sku = true;
				continue;
			}
			if ($name=='OptionValues') {
				if (!$exist_options) {					
					// Missing Options worksheet, or Options worksheet not listed before OptionValues
					$msg = str_replace( '%2', 'Options', str_replace( '%1', 'OptionValues',Utilities::getLabel( 'L_Missing_Worksheet_Or_Not_Listed_Before' ) ) );					
					Message::addErrorMessage($msg);
					$this->addLog(array('message'=>$msg));					
					$ok = false;
				}
				$exist_option_values = true;
				continue;
			} 
		}
		
		if ($exist_product_options) {
			if (!$exist_product_option_values) {				
				// ProductOptionValues worksheet also expected after a ProductOptions worksheet	
				$msg = str_replace( '%2', 'ProductOptionValues', str_replace( '%1', 'ProductOptions',Utilities::getLabel( 'L_Missing_Worksheet_Or_Not_Listed_Before' ) ) );					
				Message::addErrorMessage($msg);
				$this->addLog(array('message'=>$msg));				
				$ok = false;
			}
		}
		if ($exist_attribute_groups) {
			if (!$exist_attributes) {
				// Attributes worksheet also expected after an AttributeGroups worksheet
				$msg = str_replace( '%2', 'Attributes', str_replace( '%1', 'AttributeGroups',Utilities::getLabel( 'L_Missing_Worksheet_Or_Not_Listed_Before' ) ) );					
				Message::addErrorMessage($msg);				
				$ok = false;
			}
		}
		if ($exist_filter_groups) {
			if (!$exist_filters) {
				// Filters worksheet also expected after an FilterGroups worksheet
				$msg = str_replace( '%2', 'Filters', str_replace( '%1', 'FilterGroups',Utilities::getLabel( 'L_Missing_Worksheet_Or_Not_Listed_Before' ) ) );					
				Message::addErrorMessage($msg);
				$this->addLog(array('message'=>$msg));					
				$ok = false;
			}
		}
		if ($exist_options) {
			if (!$exist_option_values) {
				// OptionValues worksheet also expected after an Options worksheet
				$msg = str_replace( '%2', 'OptionValues', str_replace( '%1', 'Options',Utilities::getLabel( 'L_Missing_Worksheet_Or_Not_Listed_Before' ) ) );					
				Message::addErrorMessage($msg);
				$this->addLog(array('message'=>$msg));						
				$ok = false;
			}
		}
				
		if (!$ok) {
			return false;
		}
		
		if (!$this->validateCategoryIdColumns( $reader )) {
			return false;
		}
			
		if (!$this->validateProductIdColumns( $reader )) {
			return false;
		}		
		
		if (!$this->validateOptionColumns( $reader )) {
			$ok = false;
		}
		
		if (!$this->validateAttributeColumns( $reader )) {
			$ok = false;
		}
		
		if ($this->existFilter()) {
			if (!$this->validateFilterColumns( $reader )) {
				$ok = false;
			}
		}
		
		if (!$this->validateShippingDurationColumns( $reader )) {
			$ok = false;
		}
		
		if (!$this->validateShippingCompaniesColumns( $reader )) {
			$ok = false;
		}
		
		return $ok;
	}
	
	function getCell(&$worksheet,$row,$col,$default_val='') {
		$col -= 1; // we use 1-based, PHPExcel uses 0-based column index
		$row += 1; // we use 0-based, PHPExcel uses 1-based row index		
		$val = ($worksheet->cellExistsByColumnAndRow($col,$row)) ? $worksheet->getCellByColumnAndRow($col,$row)->getValue() : $default_val;
		if ($val===null) {
			$val = $default_val;
		}
		return trim($val);
	}
	
	function validateHeading( &$data, &$expected) {
		$heading = array();
		$k = PHPExcel_Cell::columnIndexFromString( $data->getHighestColumn() );
		$i = 0;
		
		for ($j=1; $j <= $k; $j+=1) {
			$entry = $this->getCell($data,$i,$j);			
			$bracket_start = strripos( $entry, '(', 0 );
			if ($bracket_start === false) {
				$heading[] = strtolower($entry);
				//$heading[] = $entry;
			}else{
				$name = strtolower(substr( $entry, 0, $bracket_start ));
				//$name = substr( $entry, 0, $bracket_start );
				$bracket_end = strripos( $entry, ')', $bracket_start );
				if ($bracket_end <= $bracket_start) {
					return false;
				}
				if ($bracket_end+1 != strlen($entry)) {
					return false;
				}
				
				if (count($heading) <= 0) {
					return false;
				}
				if ($heading[count($heading)-1] != $name) {
					$heading[] = $name;
				}
			}
		}	
			
		for ($i=0; $i < count($expected); $i+=1) {
			if (!isset($heading[$i])) {
				return false;
			}
			if ($heading[$i] != $expected[$i]) {
				return false;
			}
		}
		return true;
	}
		
	public function download( $export_type, $offset=null, $rows=null, $min_id=null, $max_id=null,$shop_user_id=0) {	 
		
		// we use our own error handler
		global $registry;		
		$registry = $this->registry;
		
		
		set_error_handler('error_handler_for_export_import',E_ALL);
		register_shutdown_function('fatal_error_shutdown_handler_for_export_import');
						
		// we use the PHPExcel package from http://phpexcel.codeplex.com/
		$cwd = getcwd();
		chdir( CONF_INSTALLATION_PATH.'public/includes/APIs/PHPExcel' );
		require_once( 'Classes/PHPExcel.php' );
		PHPExcel_Cell::setValueBinder( new PHPExcel_Cell_ExportImportValueBinder() );		
		chdir( $cwd );
		
		// find out whether all data is to be downloaded
		$all = !isset($offset) && !isset($rows) && !isset($min_id) && !isset($max_id);
		// var_dump($export_type, $offset, $rows, $min_id, $max_id,$shop_user_id,$registry,$all,$this->exportImportSettings['export_import_settings_use_export_cache']);
		// exit;
		
			
		if($this->exportImportSettings['export_import_settings_use_export_cache']){
			$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
			$cacheSettings = array( 'memoryCacheSize'  => '24MB' );  
			PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);  
		}
		
		try {
			// set appropriate timeout limit
			set_time_limit( 1800 );
			
			// create a new workbook
			$workbook = new PHPExcel();			
			
			// set some default styles
			$workbook->getDefaultStyle()->getFont()->setName('Arial');
			$workbook->getDefaultStyle()->getFont()->setSize(10);
			//$workbook->getDefaultStyle()->getAlignment()->setIndent(0.5);
			$workbook->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$workbook->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$workbook->getDefaultStyle()->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_GENERAL);
			// pre-define some commonly used styles
			$box_format = array(
				'fill' => array(
					'type'      => PHPExcel_Style_Fill::FILL_SOLID,
					'color'     => array( 'rgb' => 'F0F0F0')
				),
				/*
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					'wrap'       => false,
					'indent'     => 0
				)
				*/
			);
			$text_format = array(
				'numberformat' => array(
					'code' => PHPExcel_Style_NumberFormat::FORMAT_TEXT
				),
				/*
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					'wrap'       => false,
					'indent'     => 0
				)
				*/
			);
			$price_format = array(
				'numberformat' => array(
					'code' => '######0.00'
				),
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
					/*
					'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					'wrap'       => false,
					'indent'     => 0
					*/
				)
			);
			$weight_format = array(
				'numberformat' => array(
					'code' => '##0.00'
				),
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
					/*
					'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					'wrap'       => false,
					'indent'     => 0
					*/
				)
			);
			
			// create the worksheets
			$worksheet_index = 0;
			
			switch ($export_type) {
				case 'c': 
					// creating the Categories worksheet
					$workbook->setActiveSheetIndex($worksheet_index++);
					$worksheet = $workbook->getActiveSheet();
					$worksheet->setTitle( 'Categories' );
					$this->populateCategoriesWorksheet( $worksheet, $box_format, $text_format, $offset, $rows, $min_id, $max_id );
					$worksheet->freezePaneByColumnAndRow( 1, 2 );
					if(intval($shop_user_id)==0){
						// Creating the CategoryFilters worksheet
						if ($this->existFilter()) {
							$workbook->createSheet();
							$workbook->setActiveSheetIndex($worksheet_index++);
							$worksheet = $workbook->getActiveSheet();
							$worksheet->setTitle( 'CategoryFilters' );
							$this->populateCategoryFiltersWorksheet( $worksheet, $box_format, $text_format, $min_id, $max_id );
							$worksheet->freezePaneByColumnAndRow( 1, 2 );
						}	
						// Creating the CategoryCollections worksheet	
						if($this->existCollection()){
							$workbook->createSheet();
							$workbook->setActiveSheetIndex($worksheet_index++);
							$worksheet = $workbook->getActiveSheet();
							$worksheet->setTitle( 'CategoryCollections' );
							$this->populateCategoryCollectionWorksheet( $worksheet, $box_format, $text_format, $min_id, $max_id );
							$worksheet->freezePaneByColumnAndRow( 1, 2 );
						} 	
					}					
					
				break;
				case 'p': 
				
					// creating the Products worksheet
					$workbook->setActiveSheetIndex($worksheet_index++);
					$worksheet = $workbook->getActiveSheet();
					$worksheet->setTitle( 'Products' );
					$this->populateProductsWorksheet( $worksheet, $price_format, $box_format, $weight_format, $text_format, $offset, $rows, $min_id, $max_id ,$shop_user_id);
					$worksheet->freezePaneByColumnAndRow( 1, 2 );
					
					// creating the ProductImages worksheet
					$workbook->createSheet();
					$workbook->setActiveSheetIndex($worksheet_index++);
					$worksheet = $workbook->getActiveSheet();
					$worksheet->setTitle( 'ProductImages' );
					$this->populateProductImagesWorksheet( $worksheet, $box_format, $text_format, $min_id, $max_id,$shop_user_id);
					$worksheet->freezePaneByColumnAndRow( 1, 2 );
					
					// creating the SpecialDiscounts worksheet
					$workbook->createSheet();
					$workbook->setActiveSheetIndex($worksheet_index++);
					$worksheet = $workbook->getActiveSheet();
					$worksheet->setTitle( 'SpecialDiscounts' );
					$this->populateSpecialsWorksheet( $worksheet, $price_format, $box_format, $text_format, $min_id, $max_id,$shop_user_id );
					$worksheet->freezePaneByColumnAndRow( 1, 2 );
					
					// creating the QuantityDiscounts worksheet
					$workbook->createSheet();
					$workbook->setActiveSheetIndex($worksheet_index++);
					$worksheet = $workbook->getActiveSheet();
					$worksheet->setTitle( 'QuantityDiscounts' );
					$this->populateDiscountsWorksheet( $worksheet, $price_format, $box_format, $text_format, $min_id, $max_id,$shop_user_id );
					$worksheet->freezePaneByColumnAndRow( 1, 2 );
					
					// creating the ProductOptions worksheet
					$workbook->createSheet();
					$workbook->setActiveSheetIndex($worksheet_index++);
					$worksheet = $workbook->getActiveSheet();
					$worksheet->setTitle( 'ProductOptions' );
					$this->populateProductOptionsWorksheet( $worksheet, $box_format, $text_format, $min_id, $max_id ,$shop_user_id);
					$worksheet->freezePaneByColumnAndRow( 1, 2 );
					
					// creating the ProductOptionValues worksheet
					$workbook->createSheet();
					$workbook->setActiveSheetIndex($worksheet_index++);
					$worksheet = $workbook->getActiveSheet();
					$worksheet->setTitle( 'ProductOptionValues' );
					$this->populateProductOptionValuesWorksheet( $worksheet, $price_format, $box_format, $weight_format, $text_format, $min_id, $max_id,$shop_user_id);
					$worksheet->freezePaneByColumnAndRow( 1, 2 );
					
					// creating the Specifications worksheet
					$workbook->createSheet();
					$workbook->setActiveSheetIndex($worksheet_index++);
					$worksheet = $workbook->getActiveSheet();
					$worksheet->setTitle( 'Specifications' );
					$this->populateProductAttributesWorksheet( $worksheet, $box_format, $text_format, $min_id, $max_id ,$shop_user_id);
					$worksheet->freezePaneByColumnAndRow( 1, 2 );
					
					// creating the ProductFilters worksheet
					if ($this->existFilter()) {
						$workbook->createSheet();
						$workbook->setActiveSheetIndex($worksheet_index++);
						$worksheet = $workbook->getActiveSheet();
						$worksheet->setTitle( 'ProductFilters' );
						$this->populateProductFiltersWorksheet( $worksheet, $box_format, $text_format, $min_id, $max_id,$shop_user_id );
						$worksheet->freezePaneByColumnAndRow( 1, 2 );
					}
					
					// creating the ProductShippingRates worksheet
					$workbook->createSheet();
					$workbook->setActiveSheetIndex($worksheet_index++);
					$worksheet = $workbook->getActiveSheet();
					$worksheet->setTitle( 'ProductShippingRates' );
					$this->populateProductShippingRatesWorksheet( $worksheet, $box_format, $text_format, $min_id, $max_id,$shop_user_id );
					$worksheet->freezePaneByColumnAndRow( 1, 2 );
					
					// creating the ProductTags worksheet
					$workbook->createSheet();
					$workbook->setActiveSheetIndex($worksheet_index++);
					$worksheet = $workbook->getActiveSheet();
					$worksheet->setTitle( 'ProductTags' );
					$this->populateProductTagsWorksheet( $worksheet, $box_format, $text_format, $min_id, $max_id,$shop_user_id );
					$worksheet->freezePaneByColumnAndRow( 1, 2 );
					
				break;
				case 'o': 
					// creating the Options worksheet
					$workbook->setActiveSheetIndex($worksheet_index++);
					$worksheet = $workbook->getActiveSheet();
					$worksheet->setTitle( 'Options' );
					$this->populateOptionsWorksheet( $worksheet, $box_format, $text_format );
					$worksheet->freezePaneByColumnAndRow( 1, 2 );
					// creating the OptionValues worksheet
					$workbook->createSheet();
					$workbook->setActiveSheetIndex($worksheet_index++);
					$worksheet = $workbook->getActiveSheet();
					$worksheet->setTitle( 'OptionValues' );
					$this->populateOptionValuesWorksheet( $worksheet, $box_format, $text_format );
					$worksheet->freezePaneByColumnAndRow( 1, 2 );
					break;
				case 'a':
					// Creating the AttributeGroups worksheet
					$workbook->setActiveSheetIndex($worksheet_index++);
					$worksheet = $workbook->getActiveSheet();
					$worksheet->setTitle( 'AttributeGroups' );
					$this->populateAttributeGroupsWorksheet( $worksheet, $box_format, $text_format );
					$worksheet->freezePaneByColumnAndRow( 1, 2 );
					// Creating the Attributes worksheet
					$workbook->createSheet();
					$workbook->setActiveSheetIndex($worksheet_index++);
					$worksheet = $workbook->getActiveSheet();
					$worksheet->setTitle( 'Attributes' );
					$this->populateAttributesWorksheet( $worksheet, $box_format, $text_format );
					$worksheet->freezePaneByColumnAndRow( 1, 2 );
				break;
				case 'f': 
					if (!$this->existFilter()) { 
						throw new Exception( Utilities::getLabel('M_Error_Filter_Not_Supported') );
						break;
					}
					
					// creating the FilterGroups worksheet
					$workbook->setActiveSheetIndex($worksheet_index++);
					$worksheet = $workbook->getActiveSheet();
					$worksheet->setTitle( 'FilterGroups' );
					$this->populateFilterGroupsWorksheet( $worksheet, $box_format, $text_format );
					$worksheet->freezePaneByColumnAndRow( 1, 2 );
					// creating the Filters worksheet
					$workbook->createSheet();
					$workbook->setActiveSheetIndex($worksheet_index++);
					$worksheet = $workbook->getActiveSheet();
					$worksheet->setTitle( 'Filters' );
					$this->populateFiltersWorksheet( $worksheet, $box_format, $text_format );
					$worksheet->freezePaneByColumnAndRow( 1, 2 );
				break;
				case 'sd': 
					// creating the ShippingDurations worksheet
					$workbook->setActiveSheetIndex($worksheet_index++);
					$worksheet = $workbook->getActiveSheet();
					$worksheet->setTitle( 'ShippingDurations' );
					$this->populateShippingDurationsWorksheet( $worksheet, $box_format, $text_format );
					$worksheet->freezePaneByColumnAndRow( 1, 2 );
				break;
				case 'sc': 
					// creating the ShippingCompanies worksheet
					$workbook->setActiveSheetIndex($worksheet_index++);
					$worksheet = $workbook->getActiveSheet();
					$worksheet->setTitle( 'ShippingCompanies' );
					$this->populateShippingCompaniesWorksheet( $worksheet, $box_format, $text_format );
					$worksheet->freezePaneByColumnAndRow( 1, 2 );
				break;
				case 'sq':
					// creating the Shops worksheet
					$workbook->setActiveSheetIndex($worksheet_index++);
					$worksheet = $workbook->getActiveSheet();
					$worksheet->setTitle( 'StockAndSKU' );
					$this->populateProductSkuStockWorksheet( $worksheet,$price_format, $box_format, $text_format,$shop_user_id );
					$worksheet->freezePaneByColumnAndRow( 1, 2 );
				break;				
				case 's': die('Under development');
					// creating the Shops worksheet
					$workbook->setActiveSheetIndex($worksheet_index++);
					$worksheet = $workbook->getActiveSheet();
					$worksheet->setTitle( 'Shops' );
					$this->populateShopsWorksheet( $worksheet, $box_format, $text_format );
					$worksheet->freezePaneByColumnAndRow( 1, 2 );
				break;
				case 'b': 
					// creating the Shops worksheet
					$workbook->setActiveSheetIndex($worksheet_index++);
					$worksheet = $workbook->getActiveSheet();
					$worksheet->setTitle( 'Brands' );
					$this->populateBrandsWorksheet( $worksheet, $box_format, $text_format );
					$worksheet->freezePaneByColumnAndRow( 1, 2 );
				break;
				case 'cn': 
					// creating the Shops worksheet
					$workbook->setActiveSheetIndex($worksheet_index++);
					$worksheet = $workbook->getActiveSheet();
					$worksheet->setTitle( 'Countries' );
					$this->populateCountriesWorksheet( $worksheet, $box_format, $text_format );
					$worksheet->freezePaneByColumnAndRow( 1, 2 );
				break;
				case 'tg': 
					// creating Tags worksheet
					$workbook->setActiveSheetIndex($worksheet_index++);
					$worksheet = $workbook->getActiveSheet();
					$worksheet->setTitle( 'Tags' );
					$this->populateTagsWorksheet( $worksheet, $box_format, $text_format );
					$worksheet->freezePaneByColumnAndRow( 1, 2 );
				break;
			}		
			$workbook->setActiveSheetIndex(0);
			
			// redirect output to client browser
			$datetime = date('Y-m-d');
			
			switch ($export_type) {
				case 'c':
					$filename = 'categories-'.$datetime;
					if (!$all) {
						if (isset($offset)) {
							$filename .= "-offset-$offset";
						} else if (isset($min_id)) {
							$filename .= "-start-$min_id";
						}
						if (isset($rows)) {
							$filename .= "-rows-$rows";
						} else if (isset($max_id)) {
							$filename .= "-end-$max_id";
						}
					}
					$filename .= '.xlsx';
				break;
				case 'p':
					$filename = 'products-'.$datetime;
					if (!$all) {
						if (isset($offset)) {
							$filename .= "-offset-$offset";
						} else if (isset($min_id)) {
							$filename .= "-start-$min_id";
						}
						if (isset($rows)) {
							$filename .= "-rows-$rows";
						} else if (isset($max_id)) {
							$filename .= "-end-$max_id";
						}
					}
					$filename .= '.xlsx';
					
					break;
				case 'o':
					$filename = 'options-'.$datetime.'.xlsx';
					break;
				case 'a':
					$filename = 'attributes-'.$datetime.'.xlsx';
					break;
				case 'f':
					if (!$this->existFilter()) {
						throw new Exception( Utilities::getLabel( 'M_Error_Filter_Not_Supported' ) );
						break;
					}
					$filename = 'filters-'.$datetime.'.xlsx';
				break;
				case 'sd':
					$filename = 'ShippingDurations-'.$datetime.'.xlsx';
					break;
				case 'sc':
					$filename = 'ShippingCompanies-'.$datetime.'.xlsx';
					break;
				case 'sq':
					$filename = 'ProductsQtyAndSku-'.$datetime.'.xlsx';
					break;					
				case 's':
					$filename = 'Shops-'.$datetime.'.xlsx';
					break;
				case 'b':
					$filename = 'Brands-'.$datetime.'.xlsx';
					break;
				case 'cn':
					$filename = 'Countries-'.$datetime.'.xlsx';
					break;
				case 'tg':
					$filename = 'Tags-'.$datetime.'.xlsx';
					break;					
			}	
			
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'.$filename.'"');
			header('Cache-Control: max-age=0');
			$objWriter = PHPExcel_IOFactory::createWriter($workbook, 'Excel2007');
			$objWriter->setPreCalculateFormulas(false);
			$objWriter->save('php://output');
			// Clear the spreadsheet caches
			$this->clearSpreadsheetCache();
			exit;
		}catch (Exception $e) {
			$errstr = $e->getMessage();
			$errline = $e->getLine();
			$errfile = $e->getFile();
			$errno = $e->getCode();
			$export_import_error = array( 'errstr'=>$errstr, 'errno'=>$errno, 'errfile'=>$errfile, 'errline'=>$errline );
			$err=array('log_message'=>$export_import_error);
			return ;
		}
	}
	
	
	
	protected function populateCategoriesWorksheet( &$worksheet, &$box_format, &$text_format, $offset=null, $rows=null, &$min_id=null, &$max_id=null ) {
		
		// Set the column widths
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('id')+5);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('parent')+5);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('name')+4,30)+1);		
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('description'),32)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('file'),12)+1);
		
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('meta_title'),20)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('meta_keywords'),32)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('meta_description'),32)+1);
		
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('display_order'),5)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('url_keyword'),5)+10);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('status'),10)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('deleted'),5)+1);
		// The heading row and column styles
		$styles = array();
		$data = array();
		$i = 1;
		$j = 0;
		
		$data[$j++] = 'id';
		$data[$j++] = 'parent';
		$styles[$j] = &$text_format;
		$data[$j++] = 'name';	
		$styles[$j] = &$text_format;		
		$data[$j++] = 'description';
		$styles[$j] = &$text_format;
		$data[$j++] = 'file';
		$styles[$j] = &$text_format;
		$data[$j++] = 'meta_title';
		$styles[$j] = &$text_format;
		$data[$j++] = 'meta_keywords';
		$styles[$j] = &$text_format;
		$data[$j++] = 'meta_description';
		$data[$j++] = 'display_order';
		$styles[$j] = &$text_format;
		$data[$j++] = 'url_keyword';
		$data[$j++] = 'status';
		$data[$j++] = 'deleted';
			
		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data, $box_format );
		// The actual categories data
		$i += 1;
		$j = 0;	
		$categories = $this->getCategories($offset, $rows, $min_id, $max_id,1);		
		$len = count($categories);
		$min_id = $categories[0]['category_id'];
		$max_id = $categories[$len-1]['category_id'];
		foreach ($categories as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(26);
			$data = array();
			$data[$j++] = $row['category_id'];
			/* $data[$j++] = $row['category_type']; */
			$data[$j++] = $row['category_parent'];
			$data[$j++] = html_entity_decode($row['category_name'],ENT_QUOTES,'UTF-8');
			$data[$j++] = html_entity_decode($row['category_description'],ENT_QUOTES,'UTF-8');
			/* $data[$j++] = ($row['category_featured']==0) ? "false" : "true"; */
			$data[$j++] = html_entity_decode($row['category_file'],ENT_QUOTES,'UTF-8');
			$data[$j++] = html_entity_decode($row['category_meta_title'],ENT_QUOTES,'UTF-8');
			$data[$j++] = html_entity_decode($row['category_meta_keywords'],ENT_QUOTES,'UTF-8');
			$data[$j++] = html_entity_decode($row['category_meta_description'],ENT_QUOTES,'UTF-8');
			$data[$j++] =  $row['category_display_order'];
			$data[$j++] =  $row['url_alias_keyword'];
			$data[$j++] =  ($row['category_status']==0 || $row['category_status']=='0') ? "Disabled" : "Enabled";
			$data[$j++] =  ($row['category_is_deleted']==0 || $row['category_is_deleted']=='0') ? "No" : "Yes";
			$this->setCellRow( $worksheet, $i, $data, $this->null_array, $styles );
			$i += 1;
			$j = 0;
		}
	}
	
	protected function populateCategoryFiltersWorksheet( &$worksheet, &$box_format, &$text_format, $min_id=null, $max_id=null ) 	{
		
		// Set the column widths
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('category_id')+1);
		if ($this->exportImportSettings['export_import_settings_use_filter_group_id']) {
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('filter_group_id')+1);
		} else {
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('filter_group_name'),30)+1);
		}
		if ($this->exportImportSettings['export_import_settings_use_filter_id']) {
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('filter_id')+1);
		} else {
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('filter_name'),30)+1);
		}
		
		// The heading row and column styles
		$styles = array();
		$data = array();
		$i = 1;
		$j = 0;
		$data[$j++] = 'category_id';
		if ($this->exportImportSettings['export_import_settings_use_filter_group_id']) {
			$data[$j++] = 'filter_group_id';
		} else {
			$styles[$j] = &$text_format;
			$data[$j++] = 'filter_group_name';
		}
		if ($this->exportImportSettings['export_import_settings_use_filter_id']) {
			$data[$j++] = 'filter_id';
		} else {
			$styles[$j] = &$text_format;
			$data[$j++] = 'filter_name';
		}
		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data, $box_format );
		// The actual category filters data		
		if (!$this->exportImportSettings['export_import_settings_use_filter_group_id']) {
			$filter_group_names = $this->getFilterGroupNames( ); 
		}
		if (!$this->exportImportSettings['export_import_settings_use_filter_id']) {
			$filter_names = $this->getFilterNames( );
		} 
		
		$i += 1;
		$j = 0;
		$category_filters = $this->getCategoryFilters( $min_id, $max_id );
		
		foreach ($category_filters as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = array();
			$data[$j++] = $row['category_id'];
			if ($this->exportImportSettings['export_import_settings_use_filter_group_id']) {
				$data[$j++] = $row['filter_group_id'];
			} else {
				$data[$j++] = html_entity_decode($filter_group_names[$row['filter_group_id']],ENT_QUOTES,'UTF-8');
			}
			if ($this->exportImportSettings['export_import_settings_use_filter_id']) {
				$data[$j++] = $row['filter_id'];
			} else {
				$data[$j++] = html_entity_decode($filter_names[$row['filter_id']],ENT_QUOTES,'UTF-8');
			}
			$this->setCellRow( $worksheet, $i, $data, $this->null_array, $styles );
			$i += 1;
			$j = 0;
		}
	}
	
	function populateCategoryCollectionWorksheet(&$worksheet, &$box_format, &$text_format, $min_id=null, $max_id=null)
	{
		// Set the column widths
		$j = 0;
		if ($this->exportImportSettings['export_import_settings_use_collection_id']) {
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('collection_id')+1);
		}else{
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('collection_name')+1);
		}
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('category_id')+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('display_order')+1);
		
		// The heading row and column styles
		$styles = array();
		$data = array();
		$i = 1;
		$j = 0;
		if ($this->exportImportSettings['export_import_settings_use_collection_id']) {
			$data[$j++] = 'collection_id';
		}else{
			$styles[$j] = &$text_format;
			$data[$j++] = 'collection_name';
		}
		$data[$j++] = 'category_id';
		$data[$j++] = 'display_order';
		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data, $box_format );
		
		// Actual collection data
		/* if (!$this->exportImportSettings['export_import_settings_use_collection_id']) {
			$collections = $this->getCollectionNames('C'); 
		} */
		
		$i += 1;
		$j = 0; 
		$category_collections = $this->getCategoryCollections( $min_id, $max_id );
		
		foreach($category_collections as $catid=>$row){
			foreach($row as $key=>$val){
				$worksheet->getRowDimension($i)->setRowHeight(13);
				$data = array();
				if ($this->exportImportSettings['export_import_settings_use_collection_id']) {				
					$data[$j++] = $key;
				}else{
					$data[$j++] =  html_entity_decode($val['collection_name'],ENT_QUOTES,'UTF-8');;
				}
				$data[$j++] = $catid;
				$data[$j++] = $val['display_order'];
				$this->setCellRow( $worksheet, $i, $data, $this->null_array, $styles );
				$i += 1;
				$j = 0;
			}	
		}		
	}
	
	function populateProductsWorksheet( &$worksheet, &$price_format, &$box_format, &$weight_format, &$text_format, $offset=null, $rows=null, &$min_id=null, &$max_id=null,$shop_user_id) {
		global $prod_condition;
		$shop_user_id=intval($shop_user_id);
		// get list of the field names, some are only available for certain Yokart versions
		$query = $this->db->query( "DESCRIBE `tbl_products`" );
		$product_fields = array();
		foreach ($query->rows as $row) {
			$product_fields[] = $row['Field'];
		}
				
		if (!$this->exportImportSettings['export_import_settings_use_added_by_id']) {
			$users = $this->getUsersName(); 
		}
		
		if (!$this->exportImportSettings['export_import_settings_use_shop_id']) {
			$shops = $this->getShopNames(); 
		}
		
		if (!$this->exportImportSettings['export_import_settings_use_brand_id']) {
			$productBrands =$this->getProductBrandsName();
		}
		
		$relatedProducts=$this->getRelatedProducts();
		$productAddons=$this->getProductAddons();
		
		// Set the column widths
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('id'),4)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('name')+4,30)+1);	
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('category_id'),12)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('sku'),10)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('model'),8)+1);
		
		if($shop_user_id==0){
			if (!$this->exportImportSettings['export_import_settings_use_added_by_id']) {
				$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('added_by'),15)+1);
			}else{
				$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('added_by_id'),4)+1);
			}	
		}
		
		if (!$this->exportImportSettings['export_import_settings_use_brand_id']) {
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('brand'),15)+1);
		}else{
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('brand_id'),8)+1);
		}		
		
		if($shop_user_id==0){
			if (!$this->exportImportSettings['export_import_settings_use_shop_id']) {
				$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('shop'),15)+1);
			}else{
				$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('shop_id'),4)+1);
			}
		}
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('sale_price'),10)+1);
		/* $worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('shipping'),10)+1); */
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('stock'),4)+1);
		if (!$this->exportImportSettings['export_import_settings_use_ship_country_id']) {
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('shipping_country'),4)+1);
		}else{
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('shipping_country_id'),4)+1);
		}
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('min_order_qty'),5)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('subtract_stock'),4)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('requires_shipping'),4)+1);	 
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('track_inventory'),4)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('notify_stock_level'),4)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('condition'),5)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('added_on'),20)+1);
		/* $worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('published_on'),25)+1); */
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('available_date'),20)+1);	
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('status'),4)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('display_order'),4)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('url_keyword'),10)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('length'),5)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('length_class'),4)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('width'),5)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('height'),5)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('weight'),5)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('weight_class'),4)+1);
		/* $worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('tags'),10)+1); */
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('youtube_video'),10)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('short_description'),10)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('long_description'),10)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('meta_title'),10)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('meta_keywords'),10)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('meta_description'),10)+1);
		/* $worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('featuered'),10)+1); */
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('ship_free'),4)+1);
		/* $worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('tax_free'),4)+1); */
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('sold_count'),4)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('view_count'),4)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('is_deleted'),4)+5);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('related_products'),15)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('product_addons'),15)+1);
			
		// The product headings row and column styles
		$styles = array();
		$data = array();
		$i = 1;
		$j = 0;
		$data[$j++] = 'id';
		$styles[$j] = &$text_format;	
		$data[$j++] = 'name';
		$data[$j++] = 'category_id';
		$styles[$j] = &$text_format;	
		$data[$j++] = 'sku';
		$styles[$j] = &$text_format;	
		$data[$j++] = 'model';	
		
		if($shop_user_id==0){
			if (!$this->exportImportSettings['export_import_settings_use_added_by_id']) {
				$data[$j++] = 'added_by';
			}else{
				$data[$j++] = 'added_by_id';
			}			
		}
		if (!$this->exportImportSettings['export_import_settings_use_brand_id']) {
			$data[$j++] = 'brand';	
		}else{
			$data[$j++] = 'brand_id';
		}		
		
		if($shop_user_id==0){
			$styles[$j] = &$text_format;
			if (!$this->exportImportSettings['export_import_settings_use_shop_id']) {
				$data[$j++] = 'shop';
			}else{
				$data[$j++] = 'shop_id';
			}
		}
		
		$styles[$j] = &$price_format;
		$data[$j++] = 'sale_price';		
		/* $styles[$j] = &$price_format;
		$data[$j++] = 'shipping'; */
		$data[$j++] = 'stock';
		
		$styles[$j] = &$text_format;
		if (!$this->exportImportSettings['export_import_settings_use_ship_country_id']) {
			$data[$j++] = 'shipping_country';
			$countryNames=$this->getCountries();
		}else{
			$data[$j++] = 'shipping_country_id';
		}
		
		$data[$j++] = 'min_order_qty'; 
		$data[$j++] = 'subtract_stock';
		$data[$j++] = 'requires_shipping';
		$data[$j++] = 'track_inventory';
		$data[$j++] = 'notify_stock_level';
		$styles[$j] = &$text_format;
		$data[$j++] = 'condition';
		$data[$j++] = 'added_on '.$this->displayDateTimeFormat;
		/* $data[$j++] = 'published_on '.$this->displayDateTimeFormat; */
		$data[$j++] = 'available_date '.$this->displayDateFormat;
		$data[$j++] = 'status';	
		$data[$j++] = 'display_order';
		$data[$j++] = 'url_keyword';
		$data[$j++] = 'length';
		$data[$j++] = 'length_class';
		$data[$j++] = 'width';
		$data[$j++] = 'height';
		$data[$j++] = 'weight';
		$data[$j++] = 'weight_class';
		/* $data[$j++] = 'tags'; */
		$data[$j++] = 'youtube_video';
		$data[$j++] = 'short_description';
		$data[$j++] = 'long_description';
		$data[$j++] = 'meta_title';
		$data[$j++] = 'meta_keywords';
		$data[$j++] = 'meta_description';
		/* $data[$j++] = 'featuered'; */
		$data[$j++] = 'ship_free';
		/* $data[$j++] = 'tax_free'; */		
		$data[$j++] = 'sold_count';		
		$data[$j++] = 'view_count';		
		$data[$j++] = 'is_deleted';		
		$data[$j++] = 'related_products';		
		$data[$j++] = 'product_addons';		
						
		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data, $box_format );
		// The actual products data
		$i += 1;
		$j = 0;
		$store_ids = $this->getStoreIdsForProducts();		
		$products = $this->getProducts($product_fields, $exist_meta_title, $offset, $rows, $min_id, $max_id ,$shop_user_id);
		
		$len = count($products);		
		$min_id = $products[0]['prod_id'];
		$max_id = $products[$len-1]['prod_id'];
		
		foreach ($products as $row) {
			$data = array();
			$worksheet->getRowDimension($i)->setRowHeight(26);
			$product_id = $row['prod_id'];
			$data[$j++] = $product_id;
			$data[$j++] = html_entity_decode($row['prod_name'],ENT_QUOTES,'UTF-8');
			$data[$j++] = $row['prod_category'];
			$data[$j++] = $row['prod_sku'];
			$data[$j++] = $row['prod_model'];
			
			if($shop_user_id==0){
				if (!$this->exportImportSettings['export_import_settings_use_added_by_id']) {
					$data[$j++] = $users[$row['prod_added_by']];
				}else{
					$data[$j++] = $row['prod_added_by'];
				}
			}
			
			if (!$this->exportImportSettings['export_import_settings_use_brand_id']) {
				$data[$j++] = $productBrands[$row['prod_brand']];
			}else{
				$data[$j++] = $row['prod_brand'];
			}
			
			if($shop_user_id==0){	
				$store_id_list = '';
				if (isset($store_ids[$product_id])) {
					foreach ($store_ids[$product_id] as $store_id) {	
						if (!$this->exportImportSettings['export_import_settings_use_shop_id']) {
							$store_id_list .= ($store_id_list=='') ? $shops[$store_id] : ','.$shops[$store_id];
						}else{
							$store_id_list .= ($store_id_list=='') ? $store_id : ','.$store_id;
						}					
					}
				}
				$data[$j++] = $store_id_list;
			}
			$data[$j++] = $row['prod_sale_price'];
			/* $data[$j++] = $row['prod_shipping']; */
			$data[$j++] = $row['prod_stock'];
			if (!$this->exportImportSettings['export_import_settings_use_ship_country_id']) {
				$data[$j++] = $countryNames[$row['prod_shipping_country']];				
			}else{
				$data[$j++] = $row['prod_shipping_country'];
			}
			
			$data[$j++] = $row['prod_min_order_qty']; 
			$data[$j++] = ($row['prod_subtract_stock']==0 || $row['prod_subtract_stock']=='0') ? 'No' : 'Yes';
			$data[$j++] = ($row['prod_requires_shipping']==0 || $row['prod_requires_shipping']=='0') ? 'false' : 'true'; 
			$data[$j++] = ($row['prod_track_inventory']==0 || $row['prod_track_inventory']=='0')? 'No' : 'Yes';
			$data[$j++] = $row['prod_threshold_stock_level'];
			$data[$j++] = $prod_condition[$row['prod_condition']];
			$data[$j++] = $this->formatDate($row['prod_added_on'],true);
			/* $data[$j++] = $this->formatDate($row['prod_published_on'],true); */
			$data[$j++] = $this->formatDate($row['prod_available_date']);
			$data[$j++] = ($row['prod_status']==0 || $row['prod_status']=='0') ? 'Disabled' : 'Enabled';
			$data[$j++] = $row['prod_display_order'];
			$data[$j++] = $row['url_alias_keyword'];
			$data[$j++] = $row['prod_length'];
			$data[$j++] = $row['prod_length_class'];
			$data[$j++] = $row['prod_width'];		
			$data[$j++] = $row['prod_height'];
			$data[$j++] = $row['prod_weight'];
			$data[$j++] = $row['prod_weight_class'];
			//$data[$j++] = html_entity_decode($row['prod_tags'],ENT_QUOTES,'UTF-8');		
			$data[$j++] = html_entity_decode($row['prod_youtube_video'],ENT_QUOTES,'UTF-8');		
			$data[$j++] = html_entity_decode($row['prod_short_desc'],ENT_QUOTES,'UTF-8');			
			$data[$j++] = html_entity_decode($row['prod_long_desc'],ENT_QUOTES,'UTF-8');					
			$data[$j++] = html_entity_decode($row['prod_meta_title'],ENT_QUOTES,'UTF-8');					
			$data[$j++] = html_entity_decode($row['prod_meta_keywords'],ENT_QUOTES,'UTF-8');								
			$data[$j++] = html_entity_decode($row['prod_meta_description'],ENT_QUOTES,'UTF-8');											
			/* $data[$j++] = ($row['prod_featuered']==0 || $row['prod_featuered']=='0') ? 'no' : 'yes'; */
			$data[$j++] = ($row['prod_ship_free']==0 || $row['prod_ship_free']=='0') ? 'No' : 'Yes';
			/* $data[$j++] = ($row['prod_tax_free']==0 || $row['prod_tax_free']=='0') ? 'no' : 'yes'; */			
			$data[$j++] = $row['prod_sold_count'];			
			$data[$j++] = $row['prod_view_count'];	
			$data[$j++] = ($row['prod_is_deleted']==1 || $row['prod_is_deleted']=='1') ? 'Yes' : 'No';	
			$related_products='';
			
			if (isset($relatedProducts[$product_id])) {
				foreach ($relatedProducts[$product_id] as $prod_id) {	
					$related_products .= ($related_products=='') ? $prod_id : ','.$prod_id;					
				}
			}
			
			$data[$j++] = $related_products;
			
			$product_addons='';
			
			if (isset($productAddons[$product_id])) {
				foreach ($productAddons[$product_id] as $prod_id) {	
					$product_addons .= ($product_addons=='') ? $prod_id : ','.$prod_id;					
				}
			}
			
			$data[$j++] = $product_addons;
			$this->setCellRow( $worksheet, $i, $data, $this->null_array, $styles );
			$i += 1;
			$j = 0;
		}	
	}
	
	protected function populateProductImagesWorksheet( &$worksheet, &$box_format, &$text_format, $min_id=null, $max_id=null,$shop_user_id=null) {
		
		// Set the column widths
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('image_prod_id'),4)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('image_file'),30)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('main_image'),4)+1);
		
		// The additional images headings row and colum styles
		$styles = array();
		$data = array();
		$i = 1;
		$j = 0;		
		$data[$j++] = 'image_prod_id';
		$styles[$j] = &$text_format;
		$data[$j++] = 'image_file';
		$data[$j++] = 'main_image';
				
		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data, $box_format );
		// The actual additional images data
		$styles = array();
		$i += 1;
		$j = 0;
		$additional_images = $this->getProductImages( $min_id, $max_id ,$shop_user_id);		
		foreach ($additional_images as $row) {
			if(trim($row['image_file'])==''){continue;}
			$data = array();
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data[$j++] = $row['image_prod_id'];
			$data[$j++] = $row['image_file'];
			$data[$j++] = ($row['image_default']==1 || $row['image_default']=='1')?'Yes':'No';
			$this->setCellRow( $worksheet, $i, $data, $this->null_array, $styles );
			$i += 1;
			$j = 0;
		}
	}
	
	protected function populateSpecialsWorksheet( &$worksheet, &$price_format, &$box_format, &$text_format, $min_id=null, $max_id=null ,$shop_user_id=null) {
		// Set the column widths
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('product_id')+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('priority')+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('price'),10)+1,$price_format);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('start_date'),20)+1,$text_format);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('end_date'),20)+1,$text_format);
		// The heading row and column styles
		$styles = array();
		$data = array();
		$i = 1;
		$j = 0;
		$data[$j++] = 'product_id';
		$data[$j++] = 'priority';
		$styles[$j] = &$price_format;
		$data[$j++] = 'price';
		$data[$j++] = 'start_date '.$this->displayDateFormat;
		$data[$j++] = 'end_date '.$this->displayDateFormat;
		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data, $box_format );
		// The actual product specials data
		$i += 1;
		$j = 0;
		$specials = $this->getSpecials( $min_id, $max_id ,$shop_user_id); 
		foreach ($specials as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = array();
			$data[$j++] = $row['pspecial_product_id'];
			$data[$j++] = $row['pspecial_priority'];
			$data[$j++] = $row['pspecial_price'];
			$data[$j++] = $this->formatDate($row['pspecial_start_date']);
			$data[$j++] = $this->formatDate($row['pspecial_end_date']);			
			$this->setCellRow( $worksheet, $i, $data, $this->null_array, $styles );
			$i += 1;
			$j = 0;
		}
	}
	
	protected function populateDiscountsWorksheet( &$worksheet, &$price_format, &$box_format, &$text_format, $min_id=null, $max_id=null,$shop_user_id=null ) {
		// Set the column widths
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('product_id')+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('qty')+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('priority')+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('price'),10)+1,$price_format);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('start_date'),20)+1,$text_format);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('end_date'),20)+1,$text_format);
		// The heading row and column styles
		$styles = array();
		$data = array();
		$i = 1;
		$j = 0;
		$data[$j++] =  'product_id';		
		$data[$j++] =  'qty';
		$data[$j++] =  'priority';
		$styles[$j] = &$price_format;
		$data[$j++] =  'price';
		$data[$j++] =  'start_date '.$this->displayDateFormat;
		$data[$j++] =  'end_date '.$this->displayDateFormat;
		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data, $box_format );
		// The actual product discounts data
		$i += 1;
		$j = 0;
		$discounts = $this->getDiscounts( $min_id, $max_id ,$shop_user_id);
		foreach ($discounts as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = array();
			$data[$j++] =$row['pdiscount_product_id'];			
			$data[$j++] =$row['pdiscount_qty'];
			$data[$j++] =$row['pdiscount_priority'];
			$data[$j++] =$row['pdiscount_price'];
			$data[$j++] =$this->formatDate($row['pdiscount_start_date']);
			$data[$j++] =$this->formatDate($row['pdiscount_end_date']);
			$this->setCellRow( $worksheet, $i, $data, $this->null_array, $styles );
			$i += 1;
			$j = 0;
		}
	}
	
	protected function populateProductOptionsWorksheet( &$worksheet, &$box_format, &$text_format, $min_id=null, $max_id=null,$shop_user_id=null ) {
		// Set the column widths
		$j = 0;
			
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('product_id')+1);
		if ($this->exportImportSettings['export_import_settings_use_option_id' ]) {
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('id')+1);
		} else {
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('option'),30)+1);
		}
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('value')+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('required'),5)+1);
		// The heading row and column styles
		$styles = array();
		$data = array();
		$i = 1;
		$j = 0;
		$data[$j++] = 'product_id';
		if ($this->exportImportSettings['export_import_settings_use_option_id']) {
			$data[$j++] = 'id';
		} else {
			$styles[$j] = &$text_format;
			$data[$j++] = 'option';
			$options=$this->getOption();
		}
		$styles[$j] = &$text_format;
		$data[$j++] = 'value';
		$data[$j++] = 'required';
		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data, $box_format );
		// The actual product options data
		$i += 1;
		$j = 0;
		$product_options = $this->getProductOptions( $min_id, $max_id, $shop_user_id );
		
		foreach ($product_options as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = array();
			$data[$j++] = $row['prod_id'];
			if ($this->exportImportSettings['export_import_settings_use_option_id']) {
				$data[$j++] = $row['option_id'];
			} else {				
				$data[$j++] = html_entity_decode($options[$row['option_id']],ENT_QUOTES,'UTF-8');
			}
			$data[$j++] = html_entity_decode($row['option_value'],ENT_QUOTES,'UTF-8');
			$data[$j++] = ($row['required']==0 || $row['required']=='0') ? 'No' : 'Yes';
			$this->setCellRow( $worksheet, $i, $data, $this->null_array, $styles );
			$i += 1;
			$j = 0;
		}
	}
	
	protected function populateProductTagsWorksheet( &$worksheet, &$box_format, &$text_format, $min_id=null, $max_id=null,$shop_user_id=null ) {
		// Set the column widths
		$j = 0;
			
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('product_id')+1);
		if ($this->exportImportSettings['export_import_settings_use_product_tag_id' ]) {
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('tag_id')+1);
		} else {
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('tag'),30)+1);
		}
		
		// The heading row and column styles
		$styles = array();
		$data = array();
		$i = 1;
		$j = 0;
		$data[$j++] = 'product_id';
		if ($this->exportImportSettings['export_import_settings_use_product_tag_id']) {
			$data[$j++] = 'tag_id';
		} else {
			$styles[$j] = &$text_format;
			$data[$j++] = 'tag';
			$tags=$this->getTagNames();
		}
		$styles[$j] = &$text_format;
		
		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data, $box_format );
		// The actual product tags data
		$i += 1;
		$j = 0;
		$product_tags = $this->getProductTags( $min_id, $max_id, $shop_user_id );
		
		foreach ($product_tags as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = array();
			$data[$j++] = $row['pt_product_id'];
			if ($this->exportImportSettings['export_import_settings_use_product_tag_id']) {
				$data[$j++] = $row['pt_tag_id'];
			} else {				
				$data[$j++] = html_entity_decode($tags[$row['pt_tag_id']],ENT_QUOTES,'UTF-8');
			}
			
			$this->setCellRow( $worksheet, $i, $data, $this->null_array, $styles );
			$i += 1;
			$j = 0;
		}
	}
	
	protected function populateProductOptionValuesWorksheet( &$worksheet, &$price_format, &$box_format, &$weight_format, &$text_format, $min_id=null, $max_id=null,$shop_user_id=null) {
		// Set the column widths
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('product_id')+1);
		if ($this->exportImportSettings['export_import_settings_use_option_id']) {
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('option_id')+1);
		} else {
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('option'),30)+1);
		}
		if ($this->exportImportSettings['export_import_settings_use_option_value_id']) {
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('option_value_id')+1);
		} else {
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('option_value'),30)+1);
		}
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('quantity'),4)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('subtract'),5)+1,$text_format);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('price'),10)+1,$price_format);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('price_prefix'),5)+1,$text_format);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('weight'),10)+1,$price_format);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('weight_prefix'),5)+1,$text_format);
		// The heading row and column styles
		$styles = array();
		$data = array();
		$i = 1;
		$j = 0;
		$data[$j++] = 'product_id';
		if ($this->exportImportSettings['export_import_settings_use_option_id' ]) {
			$data[$j++] = 'option_id';
		} else {
			$styles[$j] = &$text_format;
			$data[$j++] = 'option';
			$options=$this->getOption();
		}
		if ($this->exportImportSettings['export_import_settings_use_option_value_id']) {
			$data[$j++] = 'option_value_id';
		} else {
			$styles[$j] = &$text_format;
			$data[$j++] = 'option_value';
			$optionValues=$this->getOptionValueNames();
		}
		$data[$j++] = 'quantity';
		$data[$j++] = 'subtract';
		$styles[$j] = &$price_format;
		$data[$j++] = 'price';
		$data[$j++] = "price_prefix";		
		$styles[$j] = &$weight_format;
		$data[$j++] = 'weight';
		$data[$j++] = 'weight_prefix';
		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data, $box_format );
		// The actual product option values data
		$i += 1;
		$j = 0;
		$product_option_values = $this->getProductOptionValues( $min_id, $max_id ,$shop_user_id);
		foreach ($product_option_values as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = array();
			$data[$j++] = $row['prod_id'];
			if ($this->exportImportSettings['export_import_settings_use_option_id']) {
				$data[$j++] = $row['option_id'];
			} else {
				$data[$j++] = html_entity_decode($options[$row['option_id']],ENT_QUOTES,'UTF-8');
			}
			if ($this->exportImportSettings['export_import_settings_use_option_value_id']) {
				$data[$j++] = $row['option_value_id'];
			} else {
				$data[$j++] = html_entity_decode($optionValues[$row['option_id']][$row['option_value_id']],ENT_QUOTES,'UTF-8');
			}
			$data[$j++] = $row['quantity'];
			$data[$j++] = ($row['subtract']==0 || $row['subtract']=='0') ? 'No' : 'Yes';
			$data[$j++] = $row['price'];
			$data[$j++] = $row['price_prefix'];			
			$data[$j++] = $row['weight'];
			$data[$j++] = $row['weight_prefix'];
			$this->setCellRow( $worksheet, $i, $data, $this->null_array, $styles );
			$i += 1;
			$j = 0;
		}
	}
	
	protected function populateProductAttributesWorksheet( &$worksheet, &$box_format, &$text_format, $min_id=null, $max_id=null,$shop_user_id=null ) {
		// Set the column widths
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('product_id')+1);
		if ($this->exportImportSettings['export_import_settings_use_attribute_group_id']) {
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('group_id')+1);
		} else {
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('group_name'),30)+1);
		}
		if ($this->exportImportSettings['export_import_settings_use_attribute_id']) {
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('id')+1);
		} else {
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('name'),30)+1);
		}
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('text')+4,30)+1);
		// The heading row and column styles
		$styles = array();
		$data = array();
		$i = 1;
		$j = 0;
		$data[$j++] = 'product_id';
		if ($this->exportImportSettings['export_import_settings_use_attribute_group_id']) {
			$data[$j++] = 'group_id';
		} else {
			$styles[$j] = &$text_format;
			$data[$j++] = 'group_name';
		}
		if ($this->exportImportSettings['export_import_settings_use_attribute_id']) {
			$data[$j++] = 'id';
		} else {
			$styles[$j] = &$text_format;
			$data[$j++] = 'name';
		}
		$styles[$j] = &$text_format;
		$data[$j++] = 'text';
		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data, $box_format );
		// The actual product attributes data
		if (!$this->exportImportSettings['export_import_settings_use_attribute_group_id']) {
			$attribute_group_names = $this->getAttributeGroupNames( );
		}
		
		if (!$this->exportImportSettings['export_import_settings_use_attribute_id']) {
			$attribute_names = $this->getAttributeNames( );
		}
		
		$i += 1;
		$j = 0;
		$product_attributes = $this->getProductAttributes( $min_id, $max_id ,$shop_user_id);
		
		foreach ($product_attributes as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = array();
			$data[$j++] = $row['product_id'];
			if ($this->exportImportSettings['export_import_settings_use_attribute_group_id']) {
				$data[$j++] = $row['attribute_group_id'];
			} else {
				$data[$j++] = html_entity_decode($attribute_group_names[$row['attribute_group_id']],ENT_QUOTES,'UTF-8');
			}
			if ($this->exportImportSettings['export_import_settings_use_attribute_id']) {
				$data[$j++] = $row['attribute_id'];
			} else {
				$data[$j++] = html_entity_decode($attribute_names[$row['attribute_id']],ENT_QUOTES,'UTF-8');
			}
			$data[$j++] = html_entity_decode($row['attribute_text'],ENT_QUOTES,'UTF-8');
			$this->setCellRow( $worksheet, $i, $data, $this->null_array, $styles );
			$i += 1;
			$j = 0;
		}
	}
	
	protected function populateProductFiltersWorksheet( &$worksheet, &$box_format, &$text_format, $min_id=null, $max_id=null,$shop_user_id=null ) {
		// Set the column widths
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('product_id')+1);
		if ($this->exportImportSettings['export_import_settings_use_filter_group_id']) {
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('filter_group_id')+1);
		} else {
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('filter_group_name'),30)+1);
		}
		if ($this->exportImportSettings['export_import_settings_use_filter_id']) {
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('filter_id')+1);
		} else {
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('filter_name'),30)+1);
		}
		
		// The heading row and column styles
		$styles = array();
		$data = array();
		$i = 1;
		$j = 0;
		$data[$j++] = 'product_id';
		if ($this->exportImportSettings['export_import_settings_use_filter_group_id']) {
			$data[$j++] = 'filter_group_id';
		} else {
			$styles[$j] = &$text_format;
			$data[$j++] = 'filter_group_name';
		}
		if ($this->exportImportSettings['export_import_settings_use_filter_id']) {
			$data[$j++] = 'filter_id';
		} else {
			$styles[$j] = &$text_format;
			$data[$j++] = 'filter_name';
		}
		
		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data, $box_format );
		// The actual product filters data
		if (!$this->exportImportSettings['export_import_settings_use_filter_group_id']) {
			$filter_group_names = $this->getFilterGroupNames( );
		}
		if (!$this->exportImportSettings['export_import_settings_use_filter_id']) {
			$filter_names = $this->getFilterNames( );
		}
		$i += 1;
		$j = 0;
		$product_filters = $this->getProductFilters( $min_id, $max_id ,$shop_user_id);
		foreach ($product_filters as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = array();
			$data[$j++] = $row['product_id'];
			if ($this->exportImportSettings['export_import_settings_use_filter_group_id']) {
				$data[$j++] = $row['filter_group_id'];
			} else {
				$data[$j++] = html_entity_decode($filter_group_names[$row['filter_group_id']],ENT_QUOTES,'UTF-8');
			}
			if ($this->exportImportSettings['export_import_settings_use_filter_id']) {
				$data[$j++] = $row['filter_id'];
			} else {
				$data[$j++] = html_entity_decode($filter_names[$row['filter_id']],ENT_QUOTES,'UTF-8');
			}
			$this->setCellRow( $worksheet, $i, $data, $this->null_array, $styles );
			$i += 1;
			$j = 0;
		}
	}
	
	protected function populateProductShippingRatesWorksheet(&$worksheet, &$box_format, &$text_format, $min_id=null, $max_id=null,$shop_user_id=null)
	{
		// Set the column widths
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('product_id')+1);
		if ($this->exportImportSettings['export_import_settings_use_ship_country_id']) {
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('country_id')+1);
		} else {
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('country'),30)+5);
		}
		
		if ($this->exportImportSettings['export_import_settings_use_ship_company_id']) {
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('company_id')+1);
		} else {
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('company'),30)+5);
		}
		
		if ($this->exportImportSettings['export_import_settings_use_ship_duration_id']) {
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(strlen('duration_id')+1);
		} else {
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('duration'),30)+5);
		}
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('charges'),30)+3);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('additional_charges'),30)+3);
		
		// The heading row and column styles
		$styles = array();
		$data = array();
		$i = 1;
		$j = 0;
		$data[$j++] = 'product_id';
		if ($this->exportImportSettings['export_import_settings_use_ship_country_id']) {
			$data[$j++] = 'country_id';
		} else {
			$styles[$j] = &$text_format;
			$data[$j++] = 'country';			
		}
		
		if ($this->exportImportSettings['export_import_settings_use_ship_company_id']) {
			$data[$j++] = 'company_id';
		} else {
			$styles[$j] = &$text_format;
			$data[$j++] = 'company';			
		}
		
		if ($this->exportImportSettings['export_import_settings_use_ship_duration_id']) {
			$data[$j++] = 'duration_id';
		} else {
			$styles[$j] = &$text_format;
			$data[$j++] = 'duration';			
		}
		
		$data[$j++] = 'charges';
		$data[$j++] = 'additional_charges';
		
		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data, $box_format );
		
		// The actual product shipping rates
		if (!$this->exportImportSettings['export_import_settings_use_ship_country_id']) {
			$countryNames=$this->getCountries();
		}
		if (!$this->exportImportSettings['export_import_settings_use_ship_company_id']) {
			$companyNames=$this->getShippingCompNames();
		}
		if (!$this->exportImportSettings['export_import_settings_use_ship_duration_id']) {
			$durationNames=$this->getShippingDurations();
		}
		$i += 1;
		$j = 0;
		$product_shipping_rates = $this->getProductShippingRates( $min_id, $max_id ,$shop_user_id);
		foreach($product_shipping_rates as $row){
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data=array();
			$data[$j++] = $row['pship_prod_id'];
			if ($this->exportImportSettings['export_import_settings_use_ship_country_id']) {
				$data[$j++] = $row['pship_country'];
			} else {
				$data[$j++] = html_entity_decode($countryNames[$row['pship_country']],ENT_QUOTES,'UTF-8');
			}
			
			if ($this->exportImportSettings['export_import_settings_use_ship_company_id']) {
				$data[$j++] = $row['pship_company'];
			} else {
				$data[$j++] = html_entity_decode($companyNames[$row['pship_company']],ENT_QUOTES,'UTF-8');
			}
			
			if ($this->exportImportSettings['export_import_settings_use_ship_duration_id']) {
				$data[$j++] = $row['pship_duration'];
			} else {
				$data[$j++] = html_entity_decode($durationNames[$row['pship_duration']],ENT_QUOTES,'UTF-8');
			}			
			$data[$j++] = $row['pship_charges'];
			$data[$j++] = $row['pship_additional_charges'];
			$this->setCellRow( $worksheet, $i, $data, $this->null_array, $styles );
			$i += 1;
			$j = 0;
		}		
	}
	
	protected function populateOptionsWorksheet( &$worksheet, &$box_format, &$text_format ) {
		// Set the column widths
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('option_id'),4)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('type'),10)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('display_order'),5)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('name')+4,30)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('is_deleted')+4,30)+5);
		// The options headings row and column styles
		$styles = array();
		$data = array();
		$i = 1;
		$j = 0;
		$data[$j++] = 'option_id';
		$data[$j++] = 'type';
		$data[$j++] = 'display_order';
		$data[$j++] = 'name';
		$data[$j++] = 'is_deleted';
		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data, $box_format );
		// The actual options data
		$i += 1;
		$j = 0;
		$options = $this->getOptions( );
		$conf_option_types = $this->conf_option_types( );
		foreach ($options as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = array();
			$data[$j++] = $row['option_id'];
			$data[$j++] = (isset($conf_option_types[$row['option_type']]))?$conf_option_types[$row['option_type']]:'';
			$data[$j++] = $row['option_display_order'];
			$data[$j++] = html_entity_decode($row['option_name'],ENT_QUOTES,'UTF-8');
			$data[$j++] = ($row['option_is_deleted']==0 || $row['option_is_deleted']=='0')?'No':'Yes';
			$this->setCellRow( $worksheet, $i, $data, $this->null_array, $styles );
			$i += 1;
			$j = 0;
		}
	}
	
	protected function populateOptionValuesWorksheet( &$worksheet, &$box_format, &$text_format ) {
		// Set the column widths
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('option_value_id'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('option_id'),4)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('order'),8)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('name')+4,30)+1);
		// The option values headings row and column styles
		$styles = array();
		$data = array();
		$i = 1;
		$j = 0;
		$data[$j++] = 'option_value_id';
		$data[$j++] = 'option_id';
		$data[$j++] = 'display_order';
		$data[$j++] = 'name';
		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data, $box_format );
		// The actual option values data
		$i += 1;
		$j = 0;
		$options = $this->getOptionValues( );
		foreach ($options as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = array();
			$data[$j++] = $row['option_value_id'];
			$data[$j++] = $row['option_id'];
			$data[$j++] = $row['option_value_display_order'];
			$data[$j++] = html_entity_decode($row['option_value_name'],ENT_QUOTES,'UTF-8');
			$this->setCellRow( $worksheet, $i, $data, $this->null_array, $styles );
			$i += 1;
			$j = 0;
		}
	}
	
	protected function populateAttributeGroupsWorksheet( &$worksheet, &$box_format, &$text_format ) {
		// Set the column widths
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('attribute_group_id'),4)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('display_order'),5)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('name')+4,30)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('is_deleted')+4,30)+5);
		
		// The attribute groups headings row and column styles
		$styles = array();
		$data = array();
		$i = 1;
		$j = 0;
		$data[$j++] = 'attribute_group_id';
		$data[$j++] = 'display_order';
		$data[$j++] ='name';
		$data[$j++] ='is_deleted';
		
		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data, $box_format );
		// The actual attribute groups data
		$i += 1;
		$j = 0;
		$attributes = $this->getAttributeGroups( ); 
		foreach ($attributes as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = array();
			$data[$j++] = $row['attribute_group_id'];
			$data[$j++] = $row['attribute_group_display_order'];
			$data[$j++] = html_entity_decode($row['attribute_group_name'],ENT_QUOTES,'UTF-8');
			$data[$j++] = ($row['attribute_group_is_deleted']==0 || $row['attribute_group_is_deleted']=='0')?'No':'Yes';
			$this->setCellRow( $worksheet, $i, $data, $this->null_array, $styles );
			$i += 1;
			$j = 0;
		}
	}
	
	protected function populateAttributesWorksheet( &$worksheet, &$box_format, &$text_format ) {
		// Set the column widths
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('attribute_id'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('attribute_group_id'),4)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('display_order'),5)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('name')+4,30)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('is_deleted')+4,30)+5);
		// The attributes headings row and column styles
		$styles = array();
		$data = array();
		$i = 1;
		$j = 0;
		$data[$j++] = 'attribute_id';
		$data[$j++] = 'attribute_group_id';
		$data[$j++] = 'display_order';
		$data[$j++] = 'name';
		$data[$j++] = 'is_deleted';
		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data, $box_format );
		
		// The actual attributes values data
		$i += 1;
		$j = 0;
		$options = $this->getAttributes( );
		foreach ($options as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = array();
			$data[$j++] = $row['attribute_id'];
			$data[$j++] = $row['attribute_group'];
			$data[$j++] = $row['attribute_display_order'];
			$data[$j++] = html_entity_decode($row['attribute_name'],ENT_QUOTES,'UTF-8');
			$data[$j++] = ($row['attribute_is_deleted']==0 || $row['attribute_is_deleted']=='0')?'No':'Yes';
			$this->setCellRow( $worksheet, $i, $data, $this->null_array, $styles );
			$i += 1;
			$j = 0;
		}
	}
	
	protected function populateFilterGroupsWorksheet( &$worksheet, &$box_format, &$text_format ) {
		// Set the column widths
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('filter_group_id'),4)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('display_order'),5)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('name')+4,30)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('is_deleted')+4,5)+1);
		
		// The filter groups headings row and column styles
		$styles = array();
		$data = array();
		$i = 1;
		$j = 0;
		$data[$j++] = 'filter_group_id';
		$data[$j++] = 'display_order';
		$data[$j++] ='name';
		$data[$j++] ='is_deleted';
		
		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data, $box_format );
		// The actual filter groups data
        $i += 1;
		$j = 0;
		$filters = $this->getFilterGroups();
		foreach ($filters as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = array();
			$data[$j++] = $row['filter_group_id'];
			$data[$j++] = $row['filter_group_display_order'];
			$data[$j++] = html_entity_decode($row['filter_group_name'],ENT_QUOTES,'UTF-8');
			$data[$j++] = ($row['filter_group_is_deleted']==0 || $row['filter_group_is_deleted']=='0')?'No':'Yes';
			$this->setCellRow( $worksheet, $i, $data, $this->null_array, $styles );
			$i += 1;
			$j = 0;
		}		
	}
	
	protected function populateFiltersWorksheet( &$worksheet, &$box_format, &$text_format ) {
		// Set the column widths
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('filter_id'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('filter_group_id'),4)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('display_order'),5)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('name')+4,30)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('is_deleted')+4,5)+1);
		// The filters headings row and column styles
		$styles = array();
		$data = array();
		$i = 1;
		$j = 0;
		$data[$j++] = 'filter_id';
		$data[$j++] = 'filter_group_id';
		$data[$j++] = 'display_order';
		$data[$j++] = 'name';
		$data[$j++] = 'is_deleted';
		
		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data, $box_format );
		
		// The actual filters values data
		$i += 1;
		$j = 0;
		$options = $this->getFilters();
		foreach ($options as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = array();
			$data[$j++] = $row['filter_id'];
			$data[$j++] = $row['filter_group'];
			$data[$j++] = $row['filter_display_order'];
			$data[$j++] = html_entity_decode($row['filter_name'],ENT_QUOTES,'UTF-8');
			$data[$j++] = ($row['filter_is_deleted']==0 || $row['filter_is_deleted']=='0')?'No':'Yes';			
			$this->setCellRow( $worksheet, $i, $data, $this->null_array, $styles );
			$i += 1;
			$j = 0;
		}
	}
	
	protected function populateShippingDurationsWorksheet(&$worksheet, &$box_format, &$text_format)
	{ 
		// Set the column widths
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('duration_id'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('label'),10)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('duration_from'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('duration_to'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('days_or_weeks')+4,5)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('is_deleted'),2)+1);
		// The shipping durations headings row and column styles
		$styles = array();
		$data = array();
		$i = 1;
		$j = 0;
		$data[$j++] = 'duration_id';
		$data[$j++] = 'label';
		$data[$j++] = 'duration_from';
		$data[$j++] = 'duration_to';
		$data[$j++] = 'days_or_weeks';
		$data[$j++] = 'is_deleted';
		
		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data, $box_format ); 
		
		// The actual shipping durations values data
		$i += 1;
		$j = 0;
		$durations = $this->getShippingDuration(); 
		
		foreach ($durations as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = array();
			$data[$j++] = $row['sduration_id'];
			$data[$j++] = html_entity_decode($row['sduration_label'],ENT_QUOTES,'UTF-8');
			$data[$j++] = $row['sduration_from'];
			$data[$j++] = $row['sduration_to'];
			$data[$j++] = (strtolower($row['sduration_days_or_weeks'])=='w')?'Weeks':'Days';			
			$data[$j++] = ($row['sduration_is_deleted']==0 || $row['sduration_is_deleted']=='0')?'No':'Yes';			
			$this->setCellRow( $worksheet, $i, $data, $this->null_array, $styles );
			$i += 1;
			$j = 0;
		} 
	}
	
	protected function populateShippingCompaniesWorksheet(&$worksheet, &$box_format, &$text_format)
	{ 
		// Set the column widths
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('company_id'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('name'),10)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('website')+4,10)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('comments')+4,10)+1);		
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('is_deleted'),2)+1);
		// The shipping companies headings row and column styles
		$styles = array();
		$data = array();
		$i = 1;
		$j = 0;
		$data[$j++] = 'company_id';
		$data[$j++] = 'name';
		$data[$j++] = 'website';
		$data[$j++] = 'comments';
		$data[$j++] = 'is_deleted';
		
		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data, $box_format ); 
		
		// The actual shipping companies values data
		$i += 1;
		$j = 0;
		$companies = $this->getShippingCompanies(); 
		
		foreach ($companies as $row) {
			$worksheet->getRowDimension($i)->setRowHeight(13);
			$data = array();
			$data[$j++] = $row['scompany_id'];
			$data[$j++] = html_entity_decode($row['scompany_name'],ENT_QUOTES,'UTF-8');
			$data[$j++] = html_entity_decode($row['scompany_website'],ENT_QUOTES,'UTF-8');
			$data[$j++] = html_entity_decode($row['scompany_comments'],ENT_QUOTES,'UTF-8');
			$data[$j++] = ($row['scompany_is_deleted']==0 || $row['scompany_is_deleted']=='0')?'No':'Yes';			
			$this->setCellRow( $worksheet, $i, $data, $this->null_array, $styles );
			$i += 1;
			$j = 0;
		} 
	}
	
	protected function populateProductSkuStockWorksheet(&$worksheet,&$price_format, &$box_format, &$text_format,$shop_user_id)
	{		
		// Set the column widths
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('prod_id'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('name')+4,30)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('sku'),10)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('stock')+4,10)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('sale_price')+4,10)+1);
		
		// The shipping companies headings row and column styles
		$styles = array();
		$data = array();
		$i = 1;
		$j = 0;
		$data[$j++] = 'prod_id';
		$styles[$j] = &$text_format;
		$data[$j++] = 'name';
		$data[$j++] = 'sku';
		$data[$j++] = 'stock';
		$styles[$j] = &$price_format; 
		$data[$j++] = 'sale_price';			
		
		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data, $box_format );
		
		if(intVal($shop_user_id)==0){return;}
		
		// The actual product sku and stock companies values data
		$i += 1;
		$j = 0;
		$products = $this->getProducts($product_fields, $exist_meta_title, $offset, $rows, $min_id, $max_id ,$shop_user_id);
		foreach ($products as $row) {
			$data = array();
			$worksheet->getRowDimension($i)->setRowHeight(26);
			
			$product_id = $row['prod_id'];
			$data[$j++]=$product_id;			
			$data[$j++] = $row['prod_name'];
			$data[$j++] = $row['prod_sku'];
			$data[$j++] = $row['prod_stock'];
			$data[$j++] = $row['prod_sale_price'];
			$this->setCellRow( $worksheet, $i, $data, $this->null_array, $styles );
			$i += 1;
			$j = 0;
		}
	}
	
	protected function populateShopsWorksheet(&$worksheet, &$box_format, &$text_format)
	{ 	
		// Set the column widths
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('shop_id'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('name')+4,10)+1);
		if (!$this->exportImportSettings['export_import_settings_use_added_by_id']) {
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('added_by'),10)+1);
		}else{
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('added_by_id'),10)+1);
		}
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('title')+4,10)+1);		
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('description'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('state'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('country'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('city'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('logo'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('banner'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('announcement'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('general_message'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('welcome_message'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('payment_policy'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('delivery_policy'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('refund_policy'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('additional_info'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('sales'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('seller_info'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('page_title'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('meta_keywords'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('meta_description'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('tax_vat'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('featured'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('status'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('vendor_display_status'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('date'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('updated'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('default'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('deleted'),2)+1);
		// The shipping companies headings row and column styles
		$styles = array();
		$data = array();
		$i = 1;
		$j = 0;
		$data[$j++] = 'company_id';
		$data[$j++] = 'name';
		$data[$j++] = 'website';
		$data[$j++] = 'comments';
		$data[$j++] = 'is_deleted';
		
		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data, $box_format ); 
	}
	
	protected function populateBrandsWorksheet(&$worksheet, &$box_format, &$text_format)
	{
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('brand_id'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('name'),10)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('url_keyword'),10)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('description')+4,10)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('status')+4,10)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('meta_title')+4,10)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('meta_keywords')+4,10)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('meta_description')+4,10)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('is_deleted')+4,10)+1);
		$styles = array();
		$data = array();
		$i = 1;
		$j = 0;
		$data[$j++] = 'brand_id';
		$data[$j++] = 'name';
		$data[$j++] = 'url_keyword';
		$data[$j++] = 'description';
		$data[$j++] = 'status';
		$data[$j++] = 'meta_title';
		$data[$j++] = 'meta_keywords';
		$data[$j++] = 'meta_description';
		$data[$j++] = 'is_deleted';
				
		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data, $box_format );
		$i += 1;
		$j = 0;
		//$brands=$this->getProductBrandsName(array('deleted'=>0,'active'=>1));	
		$brands=$this->getProductBrands();	
		
		foreach ($brands as $row) {
			$data = array();
			$worksheet->getRowDimension($i)->setRowHeight(26);
			
			$brand_id = $row['brand_id'];
			$data[$j++]=$brand_id;			
			$data[$j++] = html_entity_decode($row['brand_name'],ENT_QUOTES,'UTF-8');			
			$data[$j++] = $row['brand_slug'];			
			$data[$j++] = html_entity_decode($row['brand_description'],ENT_QUOTES,'UTF-8');			
			$data[$j++] = ($row['brand_status']==0 || $row['brand_status']=='0') ? 'Disabled' : 'Enabled';
			$data[$j++] = html_entity_decode($row['brand_meta_title'],ENT_QUOTES,'UTF-8');			
			$data[$j++] = html_entity_decode($row['brand_meta_keywords'],ENT_QUOTES,'UTF-8');			
			$data[$j++] = html_entity_decode($row['brand_meta_description'],ENT_QUOTES,'UTF-8');
			$data[$j++] = ($row['brand_is_deleted']==1 || $row['brand_is_deleted']=='1') ? 'Yes' : 'No';				
			$this->setCellRow( $worksheet, $i, $data, $this->null_array, $styles );
			$i += 1;
			$j = 0;
		}
	}
	
	protected function populateCountriesWorksheet(&$worksheet, &$box_format, &$text_format)
	{
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('country_id'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('name')+5,10)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('code'),10)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('is_deleted')+4,10)+1);
		$styles = array();
		$data = array();
		$i = 1;
		$j = 0;
		$data[$j++] = 'country_id';
		$data[$j++] = 'name';
		$data[$j++] = 'code';	
		$data[$j++] = 'is_deleted';
				
		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data, $box_format );
		$i += 1;
		$j = 0;
		
		$countries=$this->getCountryList();	
		
		foreach ($countries as $row) {
			$data = array();
			$worksheet->getRowDimension($i)->setRowHeight(26);
			
			$country_id = $row['country_id'];
			$data[$j++]=$country_id;			
			$data[$j++] = html_entity_decode($row['country_name'],ENT_QUOTES,'UTF-8');			
			$data[$j++] = $row['country_code'];						
			$data[$j++] = ($row['country_delete']==1 || $row['country_delete']=='1') ? 'Yes' : 'No';				
			$this->setCellRow( $worksheet, $i, $data, $this->null_array, $styles );
			$i += 1;
			$j = 0;
		}
	}
	
	protected function populateTagsWorksheet(&$worksheet, &$box_format, &$text_format)
	{
		/* if (!$this->exportImportSettings['export_import_settings_use_added_by_id']) {
			$users = $this->getUsersName(); 
		} */
		
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('tag_id'),2)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('name')+5,30)+1);
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('url_alias_keyword')+5,20)+1);
		/* $worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('owner'),10)+1);
		
		if (!$this->exportImportSettings['export_import_settings_use_added_by_id']) {
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('added_by'),15)+1);
		}else{
			$worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('added_by_id'),4)+1);
		}
		 */
		/* $worksheet->getColumnDimensionByColumn($j++)->setWidth(max(strlen('added_by')+4,10)+1); */
		$styles = array();
		$data = array();
		$i = 1;
		$j = 0;
		$data[$j++] = 'tag_id';
		$data[$j++] = 'name';
		$data[$j++] = 'url_alias_keyword';
		/* $data[$j++] = 'owner';	 */
		/* $data[$j++] = 'added_by'; */
		/* if (!$this->exportImportSettings['export_import_settings_use_added_by_id']) {
			$data[$j++] = 'added_by';
		}else{
			$data[$j++] = 'added_by_id';
		} */
		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow( $worksheet, $i, $data, $box_format );
		$i += 1;
		$j = 0;
		
		$tags=$this->getTagList();	
		
		foreach ($tags as $row) {
			$data = array();
			$worksheet->getRowDimension($i)->setRowHeight(26);
			
			$country_id = $row['ptag_id'];
			$data[$j++]=$country_id;
			$data[$j++] = html_entity_decode($row['ptag_name'],ENT_QUOTES,'UTF-8');
			$data[$j++] = html_entity_decode($row['url_alias_keyword'],ENT_QUOTES,'UTF-8');
			/* $data[$j++] = $row['ptag_owner']; */
			/* $data[$j++] = $row['ptag_added_by']; */
			/* if (!$this->exportImportSettings['export_import_settings_use_added_by_id']) {
				$data[$j++] = (empty($row['ptag_added_by']) && ($row['ptag_owner']!='U')) ? 'Admin' : $users[$row['ptag_added_by']];
			}else{
				$data[$j++] = $row['ptag_added_by'];
			} */
			$this->setCellRow( $worksheet, $i, $data, $this->null_array, $styles );
			$i += 1;
			$j = 0;
		}
	}
	
	protected function setColumnStyles( &$worksheet, &$styles, $min_row, $max_row ) {
		if ($max_row < $min_row) {
			return;
		}
		foreach ($styles as $col=>$style) {
			$from = PHPExcel_Cell::stringFromColumnIndex($col).$min_row;
			$to = PHPExcel_Cell::stringFromColumnIndex($col).$max_row;
			$range = $from.':'.$to;
			$worksheet->getStyle( $range )->applyFromArray( $style, false );
		}
	}
	
	protected function setCellRow( $worksheet, $row/*1-based*/, $data, &$default_style=null, &$styles=null ) {
		if (!empty($default_style)) {
			$worksheet->getStyle( "$row:$row" )->applyFromArray( $default_style, false );
		}
		if (!empty($styles)) {
			foreach ($styles as $col=>$style) {
				$worksheet->getStyleByColumnAndRow($col,$row)->applyFromArray($style,false);
			}
		}
		$worksheet->fromArray( $data, null, 'A'.$row, true );
//		foreach ($data as $col=>$val) {
//			$worksheet->setCellValueExplicitByColumnAndRow( $col, $row-1, $val );
//		}
//		foreach ($data as $col=>$val) {
//			$worksheet->setCellValueByColumnAndRow( $col, $row, $val );
//		}
	}
	
	protected function setCell( &$worksheet, $row/*1-based*/, $col/*0-based*/, $val, &$style=null ) {
		$worksheet->setCellValueByColumnAndRow( $col, $row, $val );
		if (!empty($style)) {
			$worksheet->getStyleByColumnAndRow($col,$row)->applyFromArray( $style, false );
		}
	}
	
	protected function clearSpreadsheetCache() {
		$files = glob(DIR_CACHE . 'Spreadsheet_Excel_Writer' . '*');
		
		if ($files) {
			foreach ($files as $file) {
				if (file_exists($file)) {
					@unlink($file);
					clearstatcache();
				}
			}
		}
	}
	
	protected function multiquery( $sql ) {
		foreach (explode(";\n", $sql) as $sql) {
			$sql = trim($sql);
			if ($sql) {				
				$this->db->query($sql);
			}
		}
	}
	
	protected function getCategories( $offset=null, $rows=null, $min_id=null, $max_id=null ,$type=null) {
		$sql  = "SELECT c.*, ua.url_alias_keyword FROM `tbl_categories` c ";
		$sql .= "LEFT JOIN `tbl_url_alias` ua ON ua.url_alias_query=CONCAT('category_id=',c.category_id) ";
		$where="";		
		if(isset($type)){	
			$where=" WHERE c.category_type='".$type."' ";
		}
		
		if (isset($min_id) && isset($max_id)) {	
			if($where==''){$where='WHERE ';}
			else{$where.=" and ";}
			$where.= " c.category_id BETWEEN $min_id AND $max_id ";
		}		
		$sql.=$where;	
		$sql .= "GROUP BY c.`category_id` ";
		$sql .= "ORDER BY c.`category_id` ASC ";
		if (isset($offset) && isset($rows)) {
			$sql .= "LIMIT $offset,$rows; ";
		} else {
			$sql .= "; ";
		}
		$results = $this->db->query( $sql );
		$row = $this->db->fetch_all($results);		
		return $row;
	}
	
	protected function getCategoryFilters( $min_id, $max_id ) {
		$sql  = "SELECT cf.category_id, fg.filter_group_id, cf.filter_id ";
		$sql .= "FROM `tbl_category_filter` cf ";
		$sql .= "INNER JOIN `tbl_filters` f ON f.filter_id=cf.filter_id ";
		$sql .= "INNER JOIN `tbl_filter_groups` fg ON fg.filter_group_id=f.filter_group ";
		//$sql.="WHERE f.filter_is_deleted ='0' and fg.filter_group_is_deleted='0'";
		if (isset($min_id) && isset($max_id)) {
			$sql .= " WHERE category_id BETWEEN $min_id AND $max_id ";
		}
		$sql .= "ORDER BY cf.category_id ASC, fg.filter_group_id ASC, cf.filter_id ASC";
		$query = $this->db->query( $sql ); 
		$res = $this->db->fetch_all($query);		
		$category_filters = array();
		foreach ($res as $row) {
			$category_filter = array();
			$category_filter['category_id'] = $row['category_id'];
			$category_filter['filter_group_id'] = $row['filter_group_id'];
			$category_filter['filter_id'] = $row['filter_id'];
			$category_filters[] = $category_filter;
		}
		return $category_filters;
	}
	
	protected function getCollectionNames($type)
	{
		$sql =  "SELECT collection_id, collection_name FROM `tbl_collections` WHERE collection_type='".$type."'";
		$query = $this->db->query( $sql );
		$result = $this->db->fetch_all($query);
		$collections=array();
		foreach($result as $row){
			$collectionId=$row['collection_id'];
			$collection_name=$row['collection_name'];
			$collections[$collectionId]=$collection_name;
		}
		return $collections;
	}
	
	protected function getCollectionIds($type)
	{
		$sql =  "SELECT collection_id, collection_name FROM `tbl_collections` WHERE collection_type='".$type."'";
		$query = $this->db->query( $sql );
		$result = $this->db->fetch_all($query);
		$collections=array();
		foreach($result as $row){
			$collectionId=$row['collection_id'];
			$collection_name=$row['collection_name'];
			$collections[strtolower($collection_name)]=$collectionId;
		}
		return $collections;
	}
	
	protected function getCategoryCollections($min_id, $max_id)
	{
		$sql  = "SELECT c.category_id, co.collection_name, co.collection_id, colc.dctc_display_order ";
		$sql .= " FROM `tbl_categories` c ";
		$sql .= "INNER JOIN `tbl_collection_categories` colc ON colc.dctc_category_id = c.category_id ";
		$sql .= "INNER JOIN `tbl_collections` co ON co.collection_id = colc.dctc_collection_id ";
		$sql.="WHERE c.category_type ='1' and co.collection_is_deleted = '0'";
		if (isset($min_id) && isset($max_id)) {
			$sql .= " and c.category_id BETWEEN $min_id AND $max_id ";
		}
		$sql .= "ORDER BY c.category_id ASC, co.collection_id ASC, co.collection_name ASC";
		
		$query = $this->db->query( $sql ); 
		$res = $this->db->fetch_all($query);
		$categoryCollections=array();
		foreach($res as $row){
			$categoryCollections[$row['category_id']][$row['collection_id']]['collection_name']=$row['collection_name'];
			$categoryCollections[$row['category_id']][$row['collection_id']]['display_order']=$row['dctc_display_order'];
		}
		return $categoryCollections;
	}
	
	protected function getStoreIdsForProducts() {
		$sql =  "SELECT prod_id, prod_shop FROM `tbl_products` ps;";
		$store_ids = array();		
		$query = $this->db->query( $sql );
		$result = $this->db->fetch_all($query);
		foreach ($result as $row) {
			$productId = $row['prod_id'];
			$store_id = $row['prod_shop'];
			if (!isset($store_ids[$productId])) {
				$store_ids[$productId] = array();
			}
			if (!in_array($store_id,$store_ids[$productId])) {
				$store_ids[$productId][] = $store_id;
			}
		}
		return $store_ids;
	}
	
	protected function getProducts( $product_fields, $exist_meta_title, $offset=null, $rows=null, $min_id=null, $max_id=null ,$shop_user_id=0) {
		$sql  = "SELECT ";
		$sql .= " p.*,ua.url_alias_keyword,pd.prod_id as product_id,pd.*";		
		$sql .= "FROM `tbl_products` p ";
		$sql .= "LEFT JOIN `tbl_url_alias` ua ON ua.url_alias_query=CONCAT('products_id=',p.prod_id) ";		
		$sql .= "LEFT JOIN `tbl_prod_details` pd ON pd.prod_id=p.prod_id ";
		$sql .= "WHERE p.prod_type='1' " ;
		
		if($shop_user_id>0){
			$sql .= "AND p.prod_added_by='".$shop_user_id."'";
		}
		
		if (isset($min_id) && isset($max_id)) {
			$sql .= " AND p.prod_id BETWEEN $min_id AND $max_id ";
		}
		$sql .= "GROUP BY p.prod_id ";
		$sql .= "ORDER BY p.prod_id ";
		if (isset($offset) && isset($rows)) {
			$sql .= "LIMIT $offset,$rows; ";
		} else {
			$sql .= "; ";
		}			
		$query = $this->db->query( $sql );
		$results = $this->db->fetch_all($query);
		return $results;
	}
	
	protected function getUserProductIds($shop_user_id=0)
	{
		if(intVal($shop_user_id)==0){return;}
		$result=array();
		
		$srch = new SearchBase('tbl_products', 'p');
		$srch->addFld('prod_id');
		$srch->addCondition('p.prod_added_by','=',$shop_user_id);
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();
		while($row=$this->db->fetch($rs)){
			$result[]=$row['prod_id'];
		}	
		return $result;				
	}
	
	protected function getProductImages( $min_id=null, $max_id=null,$shop_user_id=null) {
		$srch = new SearchBase('tbl_product_images', 'pi');
		
		if(intVal($shop_user_id)>0){
			$srch->joinTable('tbl_products', 'INNER JOIN', 'p.prod_id = pi.image_prod_id', 'p');
			$srch->addCondition('p.prod_added_by','=',$shop_user_id);
		}
		
		if (isset($min_id) && isset($max_id)) {
			$srch->addDirectCondition("pi.image_prod_id BETWEEN $min_id AND $max_id");
		}
		$srch->addOrder('pi.image_prod_id', 'asc');
		$srch->addOrder('pi.image_file', 'asc');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();		
		$rs = $srch->getResultSet();		
		return $res =$this->db->fetch_all($rs);				
	}
	
	protected function getSpecials( $min_id=null, $max_id=null,$shop_user_id=null ) {
		// get the product specials
		$srch = new SearchBase('tbl_product_specials', 'ps');
		if (isset($min_id) && isset($max_id)) {
			$srch->addDirectCondition("ps.pspecial_product_id BETWEEN $min_id AND $max_id");
		}
		if(intVal($shop_user_id)>0){
			$srch->joinTable('tbl_products', 'INNER JOIN', 'p.prod_id = ps.pspecial_product_id', 'p');
			$srch->addCondition('p.prod_added_by','=',$shop_user_id);
		}
		$srch->addOrder('ps.pspecial_product_id', 'asc');
		$srch->addOrder('ps.pspecial_priority', 'asc');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();		
		return $res =$this->db->fetch_all($rs);		
	}
	
	protected function getDiscounts( $min_id=null, $max_id=null,$shop_user_id=null ) {
		$srch = new SearchBase('tbl_product_discounts', 'pd');
		if (isset($min_id) && isset($max_id)) {
			$srch->addDirectCondition("pd.pdiscount_product_id BETWEEN $min_id AND $max_id");
		}
		if(intVal($shop_user_id)>0){
			$srch->joinTable('tbl_products', 'INNER JOIN', 'p.prod_id = pd.pdiscount_product_id', 'p');
			$srch->addCondition('p.prod_added_by','=',$shop_user_id);
		}
		$srch->addOrder('pd.pdiscount_product_id', 'asc');
		$srch->addOrder('pd.pdiscount_qty', 'asc');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();		
		return $res =$this->db->fetch_all($rs);
	}
	
	protected function getProductOptions( $min_id, $max_id,$shop_user_id=null ) {
		$sql = "SHOW COLUMNS FROM `tbl_product_option` LIKE 'value'";
		$query = $this->db->query( $sql );		
		$exist_po_value = ($query->num_rows > 0) ? true : false;
			
		// DB query for getting the product options
		if ($exist_po_value) {
			$sql  = "SELECT p.prod_id, po.option_id, po.value AS option_value, po.required  FROM ";
		} else {
			$sql  = "SELECT p.prod_id, po.option_id, po.option_value, po.required FROM ";
		}
		$sql .= "( SELECT prod_id ";
		$sql .= "  FROM `tbl_products` ";
		$where="";
		if (isset($min_id) && isset($max_id)) {
			$where .= " prod_id BETWEEN $min_id AND $max_id ";
		}
		
		if(intVal($shop_user_id)>0){
			if($where!=''){$where.=" and ";}
			$where .= "prod_added_by = $shop_user_id";
		}
		if($where!=''){
			$sql .= " WHERE ".$where;
		}
		$sql .= "  ORDER BY prod_id ASC ";
		$sql .= ") AS p ";
		$sql .= "INNER JOIN `tbl_product_option` po ON po.product_id=p.prod_id ";		
		$sql .= "ORDER BY p.prod_id ASC, po.option_id ASC"; 		
		
		$query = $this->db->query( $sql );
		return $res = $this->db->fetch_all($query);
	}
	
	protected function getProductTags( $min_id, $max_id ,$shop_user_id ) {
		
		$sql  = "SELECT pt.pt_product_id, pt.pt_tag_id FROM ";
		
		$sql .= "( SELECT prod_id ";
		$sql .= "  FROM `tbl_products` ";
		$where="";
		if (isset($min_id) && isset($max_id)) {
			$where .= " prod_id BETWEEN $min_id AND $max_id ";
		}
		if(intVal($shop_user_id)>0){
			if($where!=''){$where.=" and ";}
			$where .= "prod_added_by = $shop_user_id";
		}
		if($where!=''){
			$sql .= " WHERE ".$where;
		}
		$sql .= "  ORDER BY prod_id ASC ";
		$sql .= ") AS p ";
		$sql .= "INNER JOIN `tbl_product_to_tags` pt ON pt.pt_product_id=p.prod_id ";		
		$sql .= "ORDER BY p.prod_id ASC, pt.pt_tag_id ASC"; 		
		
		$query = $this->db->query( $sql );
		return $res = $this->db->fetch_all($query);
	}
	
	protected function getProductOptionValues( $min_id, $max_id,$shop_user_id ) {
		
		$sql  = "SELECT ";
		$sql .= "  p.prod_id, pov.option_id, pov.option_value_id, pov.quantity, pov.subtract,";
		$sql .= "  pov.price, pov.price_prefix, pov.weight, pov.weight_prefix ";
		$sql .= "FROM ";
		$sql .= "( SELECT prod_id ";
		$sql .= "  FROM `tbl_products` ";
		
		$where="";
		if (isset($min_id) && isset($max_id)) {
			$where .= " prod_id BETWEEN $min_id AND $max_id ";
		}
		
		if(intVal($shop_user_id)>0){
			if($where!=''){$where.=" and ";}
			$where .= "prod_added_by = $shop_user_id";
		}
		if($where!=''){
			$sql .= " WHERE ".$where;
		}
		$sql .= "  ORDER BY prod_id ASC ";
		$sql .= ") AS p ";
		$sql .= "INNER JOIN `tbl_product_option_value` pov ON pov.product_id=p.prod_id ";
		$sql .= "ORDER BY p.prod_id ASC, pov.option_id ASC, pov.option_value_id";
		
		$query = $this->db->query( $sql );
		return $res = $this->db->fetch_all($query);
	}
	
	protected function getAttributeGroupNames()
	{
		$sql  = "SELECT attribute_group_id, attribute_group_name ";
		$sql .= "FROM `tbl_attribute_groups` ";		
		$sql .= "ORDER BY attribute_group_id ASC";				
		$query = $this->db->query( $sql );
		$res = $this->db->fetch_all($query);
		
		$attribute_group_names = array();
		foreach ($res as $row) {
			$attribute_group_id = $row['attribute_group_id'];
			$name = $row['attribute_group_name'];
			$attribute_group_names[$attribute_group_id] = $name;
		}
		return $attribute_group_names;
	}
	
	protected function getAttributeNames( ) {
		$sql  = "SELECT attribute_id, attribute_name ";
		$sql .= "FROM `tbl_attributes` ";	
		$sql .= "ORDER BY attribute_id ASC";
		$query = $this->db->query( $sql );
		$res = $this->db->fetch_all($query);
		$attribute_names = array();
		foreach ($res as $row) {
			$attribute_id = $row['attribute_id'];
			$attribute_name = $row['attribute_name'];
			$attribute_names[$attribute_id] = $attribute_name;
		}
		return $attribute_names;
	}
	
	protected function getProductAttributes( $min_id, $max_id ,$shop_user_id) {
		$sql  = "SELECT pa.product_id, ag.attribute_group_id, pa.attribute_id, pa.attribute_text ";
		$sql .= "FROM `tbl_product_attributes` pa ";
		$sql .= "INNER JOIN `tbl_attributes` a ON a.attribute_id=pa.attribute_id ";
		$sql .= "INNER JOIN `tbl_attribute_groups` ag ON ag.attribute_group_id=a.attribute_group ";
		
		$where="";	
		if (isset($min_id) && isset($max_id)) {
			$where .= " pa.product_id BETWEEN $min_id AND $max_id ";
		}
		
		if(intVal($shop_user_id)>0){
			$sql .= "INNER JOIN `tbl_products` p ON p.prod_id=pa.product_id";
			if($where!=''){$where.=" and ";}
			$where .= "p.prod_added_by = $shop_user_id ";
		}
		if($where!=''){
			$sql .= " WHERE ".$where;
		}
		$sql .= "ORDER BY pa.product_id ASC, ag.attribute_group_id ASC, pa.attribute_id ASC";
		
		$query = $this->db->query( $sql );
		$res = $this->db->fetch_all($query);
		
		$texts = array();
		foreach ($res as $row) {
			$product_id = $row['product_id'];
			$attribute_group_id = $row['attribute_group_id'];
			$attribute_id = $row['attribute_id'];			
			$text = $row['attribute_text'];
			$texts[$product_id][$attribute_group_id][$attribute_id] = $text;
		}
		$product_attributes = array();
		foreach ($texts as $product_id=>$level1) {
			foreach ($level1 as $attribute_group_id=>$level2) {
				foreach ($level2 as $attribute_id=>$text) {
					$product_attribute = array();
					$product_attribute['product_id'] = $product_id;
					$product_attribute['attribute_group_id'] = $attribute_group_id;
					$product_attribute['attribute_id'] = $attribute_id;
					$product_attribute['attribute_text'] = $text;					
					$product_attributes[] = $product_attribute;
				}
			}
		}
		return $product_attributes;
	}
	
	protected function getProductFilters( $min_id, $max_id,$shop_user_id ) {
		$sql  = "SELECT pf.product_id, fg.filter_group_id, pf.filter_id ";
		$sql .= "FROM `tbl_product_filter` pf ";
		$sql .= "INNER JOIN `tbl_filters` f ON f.filter_id=pf.filter_id ";
		$sql .= "INNER JOIN `tbl_filter_groups` fg ON fg.filter_group_id=f.filter_group ";
				
		$where="";	
		if (isset($min_id) && isset($max_id)) {
			$where .= " pf.product_id BETWEEN $min_id AND $max_id ";
		}
		
		if(intVal($shop_user_id)>0){
			$sql .= "INNER JOIN `tbl_products` p ON p.prod_id=pf.product_id";
			if($where!=''){$where.=" and ";}
			$where .= "p.prod_added_by = $shop_user_id ";
		}
		if($where!=''){
			$sql .= " WHERE ".$where;
		}
		$sql .= "ORDER BY pf.product_id ASC, fg.filter_group_id ASC, pf.filter_id ASC";
		
		$query = $this->db->query( $sql );
		$res = $this->db->fetch_all($query);
		$product_filters = array();
		foreach ($res as $row) {
			$product_filter = array();
			$product_filter['product_id'] = $row['product_id'];
			$product_filter['filter_group_id'] = $row['filter_group_id'];
			$product_filter['filter_id'] = $row['filter_id'];
			$product_filters[] = $product_filter;
		}
		return $product_filters;
	}
	
	protected function getProductShippingRates($min_id, $max_id,$shop_user_id )
	{
		$sql  = "SELECT p.* ";
		$sql .= "FROM `tbl_product_shipping_rates` p ";
				
		$where="";	
		if (isset($min_id) && isset($max_id)) {
			$where .= " p.pship_prod_id BETWEEN $min_id AND $max_id ";
		}
		
		if(intVal($shop_user_id)>0){
			$sql .= "INNER JOIN `tbl_products` pd ON pd.prod_id=p.pship_prod_id";
			if($where!=''){$where.=" and ";}
			$where .= "pd.prod_added_by = $shop_user_id ";
		}
		if($where!=''){
			$sql .= " WHERE ".$where;
		}
		$sql .= "ORDER BY p.pship_prod_id ASC, p.pship_country ASC, p.pship_company ASC,p.pship_duration ASC";
		$query = $this->db->query( $sql );
		$res = $this->db->fetch_all($query);		
		return $res;
	}	
	
	protected function getOptionValues( ) {
		$query = $this->db->query( "SELECT * FROM `tbl_option_values` ORDER BY option_id ASC, option_value_id ASC" );
		return $res = $this->db->fetch_all($query);
	}
	
	protected function getOptions() {
		$query = $this->db->query( "SELECT * FROM `tbl_options` ORDER BY option_id ASC" );
		return $res = $this->db->fetch_all($query);	
	}
	
	protected function conf_option_types()
	{
		global $conf_option_types;
		return $conf_option_types;
	}
	
	protected function conf_option_types_val()
	{
		global $conf_option_types;
		$arr=array();
		foreach($conf_option_types as $key=>$val){
			$arr[strtoupper($val)]=$key;
		}
		return $arr;
	}
	
	protected function getAttributeGroups( ) {
		$query = $this->db->query( "SELECT * FROM `tbl_attribute_groups` ORDER BY attribute_group_id ASC" );
		return $res = $this->db->fetch_all($query);			
	}
	
	protected function getAttributes( &$languages ) {
		$query = $this->db->query( "SELECT * FROM `tbl_attributes` ORDER BY attribute_group ASC, attribute_id ASC" );
		return $res = $this->db->fetch_all($query);			
	}
	
	protected function getFilterGroupNames( ) {
		$sql  = "SELECT filter_group_id, filter_group_name ";
		$sql .= "FROM `tbl_filter_groups` ";					
		$sql .= "ORDER BY filter_group_id ASC";
		
		$query = $this->db->query( $sql );
		$res = $this->db->fetch_all($query);		
		$filter_group_names = array();
		foreach ($res as $row) {
			$filter_group_id = $row['filter_group_id'];
			$name = $row['filter_group_name'];
			$filter_group_names[$filter_group_id] = $name;			
		}
		return $filter_group_names;
	}
	
	protected function getFilterNames() {
		$sql  = "SELECT filter_id, filter_name ";
		$sql .= "FROM `tbl_filters` ";					
		$sql .= "ORDER BY filter_id ASC";
		$query = $this->db->query( $sql );
		$res = $this->db->fetch_all($query);		
		$filter_names = array();
		foreach ($res as $row) {
			$filter_id = $row['filter_id'];
			$filter_name = $row['filter_name'];
			$filter_names[$filter_id] = $filter_name;
		}
		return $filter_names;
	}
	
		
	protected function getFilterGroups( ) {
		$query = $this->db->query( "SELECT * FROM `tbl_filter_groups` ORDER BY filter_group_id ASC" );
		return $res = $this->db->fetch_all($query);			
	}
	
	protected function getFilters( ) {
		$query = $this->db->query( "SELECT * FROM `tbl_filters` ORDER BY filter_group ASC, filter_id ASC" );
		return $res = $this->db->fetch_all($query);	
	}
	
	public function existFilter() { 
		// only newer Yokart versions support filters
		$query = $this->db->query( "SHOW TABLES LIKE 'tbl_filters'" );		
		$exist_table_filter = ($query->num_rows > 0);
		$query = $this->db->query( "SHOW TABLES LIKE 'tbl_filter_groups'" );
		$exist_table_filter_group = ($query->num_rows > 0);
		$query = $this->db->query( "SHOW TABLES LIKE 'tbl_product_filter'" );
		$exist_table_product_filter = ($query->num_rows > 0);
		$query = $this->db->query( "SHOW TABLES LIKE 'tbl_category_filter'" );
		$exist_table_category_filter = ($query->num_rows > 0);
		if (!$exist_table_filter) {
			return false;
		}
		if (!$exist_table_filter_group) {
			return false;
		}
		if (!$exist_table_product_filter) {
			return false;
		}
		if (!$exist_table_category_filter) {
			return false;
		}
		return true;
	}
	public function getCountryList()
	{
		$srch = new SearchBase('tbl_countries', 'c');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();
		return $row=$this->db->fetch_all($rs);		
	}
	public function getTagList()
	{
		$srch = new SearchBase('tbl_product_tags', 'pt');
		$srch->joinTable('tbl_url_alias' ,"LEFT JOIN" ,"ua.url_alias_query=CONCAT('tags_id=',pt.ptag_id)", 'ua');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();
		return $row=$this->db->fetch_all($rs);		
	}
	public function getCountries()
	{
		$sql  = "SELECT country_id, country_name ";
		$sql .= "FROM `tbl_countries` ";					
		$sql .= "ORDER BY country_id ASC";
		$query = $this->db->query( $sql );
		$res = $this->db->fetch_all($query);		
		$country_names = array();
		$country_names[-1]='all';
		foreach ($res as $row) {			
			$country_names[$row['country_id']] = $row['country_name'];
		}
		return $country_names;
	}	
	
	public function getCountryIds()
	{
		$sql  = "SELECT country_id, country_name ";
		$sql .= "FROM `tbl_countries` ";					
		$sql .= "ORDER BY country_name ASC,country_delete Desc";
		$query = $this->db->query( $sql );
		$res = $this->db->fetch_all($query);		
		$country_names = array();
		$country_names['all']='-1';
		foreach ($res as $row) {			
			$country_names[strtolower($row['country_name'])] = $row['country_id'];
		}
		return $country_names;
	}		
	
	public function getShippingCompNames()
	{
		$sql  = "SELECT scompany_id, scompany_name ";
		$sql .= "FROM `tbl_shipping_companies` ";					
		$sql .= "ORDER BY scompany_id ASC, scompany_is_deleted desc";
		$query = $this->db->query( $sql );
		$res = $this->db->fetch_all($query);		
		$company = array();
		foreach ($res as $row) {			
			$company[$row['scompany_id']] = $row['scompany_name'];
		}
		return $company;
	}
	
	public function getShippingCompIds()
	{
		$sql  = "SELECT scompany_id, scompany_name ";
		$sql .= "FROM `tbl_shipping_companies` ";					
		$sql .= "ORDER BY scompany_id ASC, scompany_is_deleted desc";
		$query = $this->db->query( $sql );
		$res = $this->db->fetch_all($query);		
		$company = array();
		foreach ($res as $row) {			
			$company[strtolower($row['scompany_name'])] = $row['scompany_id'];
		}
		return $company;
	}
	
	public function getShippingDuration()
	{
		$sql  = "SELECT * ";
		$sql .= "FROM `tbl_shipping_durations` ";					
		$sql .= "ORDER BY sduration_id ASC, sduration_is_deleted desc";
		$query = $this->db->query( $sql );
		$res = $this->db->fetch_all($query);		
		return $res;
	}
	
	public function getShippingDurations()
	{
		$sql  = "SELECT sduration_id, sduration_label ";
		$sql .= "FROM `tbl_shipping_durations` ";					
		$sql .= "ORDER BY sduration_id ASC, sduration_is_deleted desc";
		$query = $this->db->query( $sql );
		$res = $this->db->fetch_all($query);		
		$duration = array();
		foreach ($res as $row) {			
			$duration[$row['sduration_id']] = $row['sduration_label'];
		}
		return $duration;
	}
	
	public function getShippingDurationIds()
	{
		$sql  = "SELECT sduration_id, sduration_label ";
		$sql .= "FROM `tbl_shipping_durations` ";					
		$sql .= "ORDER BY sduration_id ASC, sduration_is_deleted desc";
		$query = $this->db->query( $sql );
		$res = $this->db->fetch_all($query);		
		$duration = array();
		foreach ($res as $row) {			
			$duration[strtolower($row['sduration_label'])] = $row['sduration_id'];
		}
		return $duration;
	}
	public function getShippingCompanies()
	{
		$sql  = "SELECT * ";
		$sql .= "FROM `tbl_shipping_companies` ";					
		$sql .= "ORDER BY scompany_id ASC, scompany_is_deleted desc";
		$query = $this->db->query( $sql );
		$res = $this->db->fetch_all($query);		
		return $res;
	}
	
	public function existCollection()
	{
		$query = $this->db->query( "SHOW TABLES LIKE 'tbl_collections'" );		
		$exist_table_collection = ($query->num_rows > 0);
		$query = $this->db->query( "SHOW TABLES LIKE 'tbl_collection_categories'" );
		$exist_table_collection_category = ($query->num_rows > 0);
		$query = $this->db->query( "SHOW TABLES LIKE 'tbl_collection_products'" );
		$exist_table_collection_products = ($query->num_rows > 0);
		
		if (!$exist_table_collection) {
			return false;
		}
		if (!$exist_table_collection_category) {
			return false;
		}
		if (!$exist_table_collection_products) {
			return false;
		}
		return true;		
	}
	
	protected function validateCategories( &$reader ) {
		$data = $reader->getSheetByName( 'Categories' );
		
		if ($data==null) {
			return true;
		}
		
		$expected_heading = array
		( "id","parent", "name", "description", "file", "meta_title", "meta_keywords", "meta_description", "display_order","url_keyword", "status","deleted" );
		
		return $this->validateHeading( $data, $expected_heading);
	}
	
	protected function validateCategoryFilters( &$reader ) {
		$data = $reader->getSheetByName( 'CategoryFilters' );
		if ($data==null) {
			return true;
		}
		if (!$this->existFilter()) {			
			throw new Exception( 'Filter not supported.' );
		}
		if ($this->exportImportSettings['export_import_settings_use_filter_group_id']) {
			if ($this->exportImportSettings['export_import_settings_use_filter_id']) {
				$expected_heading = array( "category_id", "filter_group_id", "filter_id" );
			} else {
				$expected_heading = array( "category_id", "filter_group_id", "filter_name" );
			}
		} else {
			if ($this->exportImportSettings['export_import_settings_use_filter_id']) {
				$expected_heading = array( "category_id", "filter_group_name", "filter_id" );
			} else {
				$expected_heading = array( "category_id", "filter_group_name", "filter_name" );
			}
		}
		$expected_multilingual = array();
		return $this->validateHeading( $data, $expected_heading);
	}
	
	protected function validateProducts( &$reader,$shop_user_id=0 ) {
		$data = $reader->getSheetByName( 'Products' );
		
		if ($data==null) {
			return true;
		}
		
		/* $rs=$this->db->query("DESCRIBE `tbl_products`");
		$prodFieldsArr=$this->db->fetch_all($rs);
		foreach ($prodFieldsArr as $row) {
			$product_fields[] = $row['Field'];
		} 
		
		$sql = "SHOW COLUMNS FROM `tbl_prod_details`";
		$query = $this->db->query( $sql );
		$prodDetailsFieldsArr=$this->db->fetch_all($query);
		foreach ($prodDetailsFieldsArr as $row) {
			$productDetailsfields[] = $row['Field'];
		}
		
		$expected_heading=array_merge($product_fields,$productDetailsfields); */				
		$expected_heading=array('id','name','category_id','sku','model');
		if($shop_user_id==0){
			if($this->exportImportSettings['export_import_settings_use_added_by_id']) {			
				$expected_heading[]='added_by_id';
			}else{
				$expected_heading[]='added_by';
			}
		}
		if ($this->exportImportSettings['export_import_settings_use_brand_id']) {
			$expected_heading[]='brand_id';
		}else{
			$expected_heading[]='brand';
		}
		
		if($shop_user_id==0){
			if ($this->exportImportSettings['export_import_settings_use_shop_id']) {
				$expected_heading[]='shop_id';
			}else{
				$expected_heading[]='shop';
			}
		}
		
		$expected_heading[]='sale_price';
		$expected_heading[]='stock';
		if (!$this->exportImportSettings['export_import_settings_use_ship_country_id']) {
			$expected_heading[]='shipping_country';
		}else{
			$expected_heading[]='shipping_country_id';
		}
		$arr=array('min_order_qty','subtract_stock','requires_shipping','track_inventory','notify_stock_level','condition','added_on '.$this->displayDateTimeFormat,'available_date '.$this->displayDateFormat,'status','display_order','url_keyword','length','length_class','width','height','weight','weight_class',/* 'tags', */'youtube_video','short_description','long_description','meta_title','meta_keywords','meta_description','ship_free','sold_count','view_count','is_deleted','related_products','product_addons');
		$expected_heading=array_merge($expected_heading,$arr);
		
		return $this->validateHeading( $data, $expected_heading);
		
	}
	
	protected function validateProductPrices( &$reader,$shop_user_id=0 ) {
		$data = $reader->getSheetByName( 'Products' );
		
		if ($data==null) {
			return true;
		}
		$ok = true;
		$productMinPrice = Settings::getSetting('CONF_MIN_PRODUCT_PRICE');
		$productMinPrice = floatval($productMinPrice) ? floatval($productMinPrice) : 0 ;
		
		$k = $data->getHighestRow();
		for ($i=1; $i<$k; $i+=1) {
			$prod_id = trim($this->getCell($data,$i,1));
			if(empty($shop_user_id)){
				$prod_sale_price = trim($this->getCell($data,$i,9));
			}
			else{
				$prod_sale_price = trim($this->getCell($data,$i,7));
			}
			$prod_id = trim($this->getCell($data,$i,1));
			$prod_sale_price = floatval($prod_sale_price)?floatval($prod_sale_price):0;
			
			if ( $prod_id && !($prod_sale_price >= $productMinPrice) ) {
				$ok = false;
				$msg = str_replace( '%1', 'Products', Utilities::getLabel( 'L_Error_Product_Sale_Price_Less_Than_Min_Price_Required' ) );
				$msg = str_replace( '%2', $productMinPrice, $msg );
				$msg = str_replace( '%3', $prod_id, $msg );
				Message::addErrorMessage($msg);
				$this->addLog(array('message'=>$msg));
			}
		}
		if($ok){
			return true;
		}
		return false;
		
	}
	
	protected function validateProductImages( &$reader ) {
		$data = $reader->getSheetByName( 'ProductImages' );
		if ($data==null) {
			return true;
		}
		$expected_heading = array( "image_prod_id", "image_file","main_image");
		return $this->validateHeading( $data, $expected_heading );
	}
	
	protected function validateSpecials( &$reader ) {
		$data = $reader->getSheetByName( 'SpecialDiscounts' );
		if ($data==null) {
			return true;
		}
		$expected_heading = array( "product_id", "priority", "price", "start_date ".$this->displayDateFormat, "end_date ".$this->displayDateFormat);		
		return $this->validateHeading( $data, $expected_heading);
	}
	
	protected function validateDiscounts( &$reader ) {
		$data = $reader->getSheetByName( 'QuantityDiscounts' );
		if ($data==null) {
			return true;
		}
		$expected_heading = array( "product_id", "qty", "priority", "price", "start_date ".$this->displayDateFormat, "end_date ".$this->displayDateFormat);
		return $this->validateHeading( $data, $expected_heading);
	}
	
	protected function validateProductOptions( &$reader ) {
		$data = $reader->getSheetByName( 'ProductOptions' );
		if ($data==null) {
			return true;
		}
		if ($this->exportImportSettings['export_import_settings_use_option_id']) {
			$expected_heading = array( "product_id", "id", "value", "required" );
		} else {
			$expected_heading = array( "product_id", "option", "value", "required" );
		}
		return $this->validateHeading( $data, $expected_heading );
	}
	
	protected function validateProductOptionValues( &$reader ) {
		$data = $reader->getSheetByName( 'ProductOptionValues' );
		if ($data==null) {
			return true;
		}
		if ($this->exportImportSettings['export_import_settings_use_option_id']) {
			if ($this->exportImportSettings['export_import_settings_use_option_value_id']) {
				$expected_heading = array( "product_id", "option_id", "option_value_id", "quantity", "subtract", "price", "price_prefix", "weight", "weight_prefix" );
			} else {
				$expected_heading = array( "product_id", "option_id", "option_value", "quantity", "subtract", "price", "price_prefix", "weight", "weight_prefix" );
			}
		} else {
			if ($this->exportImportSettings['export_import_settings_use_option_value_id']) {
				$expected_heading = array( "product_id", "option", "option_value_id", "quantity", "subtract", "price", "price_prefix", "weight", "weight_prefix" );
			} else {
				$expected_heading = array( "product_id", "option", "option_value", "quantity", "subtract", "price", "price_prefix", "weight", "weight_prefix" );
			}
		}	
		
		return $this->validateHeading( $data, $expected_heading);
	}
	
	protected function validateProductAttributes( &$reader ) {
		$data = $reader->getSheetByName( 'Specifications' );
		if ($data==null) {
			return true;
		}
		if ($this->exportImportSettings['export_import_settings_use_attribute_group_id']) {
			if ($this->exportImportSettings['export_import_settings_use_attribute_id']) {
				$expected_heading = array( "product_id", "group_id", "id", "text" );
			} else {
				$expected_heading = array( "product_id", "group_id", "name", "text" );
			}
		} else {
			if ($this->exportImportSettings['export_import_settings_use_attribute_id']) {
				$expected_heading = array( "product_id", "group_name", "id", "text" );
			} else {
				$expected_heading = array( "product_id", "group_name", "name", "text" );
			}
		}		
		
		return $this->validateHeading( $data, $expected_heading);
	}
	
	protected function validateProductFilters( &$reader ) {
		$data = $reader->getSheetByName( 'ProductFilters' );
		if ($data==null) {
			return true;
		}
		if (!$this->existFilter()) {
			throw new Exception('Error filter not supported.');
		}
		if ($this->exportImportSettings['export_import_settings_use_filter_group_id']) {
			if ($this->exportImportSettings['export_import_settings_use_filter_id']) {
				$expected_heading = array( "product_id", "filter_group_id", "filter_id" );
			} else {
				$expected_heading = array( "product_id", "filter_group_id", "filter_name" );
			}
		} else {
			if ($this->exportImportSettings['export_import_settings_use_filter_id']) {
				$expected_heading = array( "product_id", "filter_group_name", "filter_id" );
			} else {
				$expected_heading = array( "product_id", "filter_group_name", "filter_name" );
			}
		}
		
		return $this->validateHeading( $data, $expected_heading);
	}
	
	protected function validateProductShippingRates( &$reader ) {
		$data = $reader->getSheetByName( 'ProductShippingRates' );
		if ($data==null) {
			return true;
		}
		$expected_heading=array("product_id");
		if ($this->exportImportSettings['export_import_settings_use_ship_country_id']) {
			$expected_heading[]='country_id';
		} else {
			$expected_heading[]='country';
		}
			
		if ($this->exportImportSettings['export_import_settings_use_ship_company_id']) {
			$expected_heading[]='company_id';
		} else {
			$expected_heading[]='company';
		}
		
		if ($this->exportImportSettings['export_import_settings_use_ship_duration_id']) {
			$expected_heading[]='duration_id';
		} else {
			$expected_heading[]='duration';
		}
		$expected_heading[]='charges';
		$expected_heading[]='additional_charges';	
		
		return $this->validateHeading( $data, $expected_heading);
	}
	protected function validateOptions( &$reader ) {
		$data = $reader->getSheetByName( 'Options' );
		if ($data==null) {
			return true;
		}
		$expected_heading = array( "option_id", "type", "display_order", "name" ,"is_deleted");
		return $this->validateHeading( $data, $expected_heading);
	}
	
	protected function validateOptionValues( &$reader ) {
		$data = $reader->getSheetByName( 'OptionValues' );
		if ($data==null) {
			return true;
		}
		$expected_heading = array( "option_value_id", "option_id", "display_order", "name" );	
		return $this->validateHeading( $data, $expected_heading);
	}
	
	protected function validateAttributeGroups( &$reader ) {
		$data = $reader->getSheetByName( 'AttributeGroups' );
		if ($data==null) {
			return true;
		}
		$expected_heading = array( "attribute_group_id", "display_order", "name","is_deleted" );		
		return $this->validateHeading( $data, $expected_heading);
	}
	
	protected function validateAttributes( &$reader ) {
		$data = $reader->getSheetByName( 'Attributes' );
		if ($data==null) {
			return true;
		}
		$expected_heading = array( "attribute_id", "attribute_group_id", "display_order", "name","is_deleted" );		
		return $this->validateHeading( $data, $expected_heading);
	}
	
	protected function validateFilterGroups( &$reader ) {
		$data = $reader->getSheetByName( 'FilterGroups' );
		if ($data==null) {
			return true;
		}
		if (!$this->existFilter()) {
			throw new Exception('Error filter not supported' );
		}
		$expected_heading = array( "filter_group_id", "display_order", "name","is_deleted" );		
		return $this->validateHeading( $data, $expected_heading);
	}
	
	protected function validateFilters( &$reader ) {
		$data = $reader->getSheetByName( 'Filters' );
		if ($data==null) {
			return true;
		}
		if (!$this->existFilter()) {
			throw new Exception( 'Error filter not supported' );
		}
		$expected_heading = array( "filter_id", "filter_group_id", "display_order", "name" ,"is_deleted");		
		return $this->validateHeading( $data, $expected_heading);
	}
	
	protected function validateCategoryIdColumns(&$reader)
	{
		$data = $reader->getSheetByName( 'Categories' );
		if ($data==null) {
			return true;
		}
		$ok = true;
		
		// only unique numeric product_ids can be used in worksheet 'Products'
		$has_missing_product_ids = false;
		$category_ids = array();
		$k = $data->getHighestRow();
		for ($i=1; $i<$k; $i+=1) {
			$category_id = trim($this->getCell($data,$i,1));
			if ($category_id=="") {
				if (!$has_missing_category_ids) {
					$msg = str_replace( '%1', 'Categories', Utilities::getLabel( 'L_Error_Missing_Category_Id' ) );
					Message::addErrorMessage($msg);						
					$has_missing_category_ids = true;
				}
				$ok = false;
				continue;
			}
			if (!ctype_digit($category_id)) {
				$msg = str_replace( '%2', $category_id, str_replace( '%1', 'Categories',Utilities::getLabel( 'L_Error_Invalid_Category_Id' ) ) );
				Message::addErrorMessage($msg);					
				$ok = false;
				continue;
			}
			if (in_array( $category_id, $category_ids )) {
				$msg = str_replace( '%2', $category_id, str_replace( '%1', 'Products', Utilities::getLabel( 'L_Error_Duplicate_Category_Id' ) ) );
				Message::addErrorMessage($msg);				
				$ok = false;
				continue;
			}
			$category_ids[] = $category_id;
		}
		return $ok;
	}
	protected function validateProductIdColumns( &$reader ) {
		$data = $reader->getSheetByName( 'Products' );
		if ($data==null) {
			return true;
		}
		$ok = true;
		
		// only unique numeric product_ids can be used in worksheet 'Products'
		$has_missing_product_ids = false;
		$product_ids = array();
		$k = $data->getHighestRow();
		for ($i=1; $i<$k; $i+=1) {
			$product_id = trim($this->getCell($data,$i,1));
			if ($product_id=="") {
				if (!$has_missing_product_ids) {
					$msg = str_replace( '%1', 'Products', Utilities::getLabel( 'L_Error_Missing_Product_Id' ) );
					Message::addErrorMessage($msg);						
					$has_missing_product_ids = true;
				}
				$ok = false;
				continue;
			}
			if (!ctype_digit($product_id)) {
				$msg = str_replace( '%2', $product_id, str_replace( '%1', 'Products',Utilities::getLabel( 'L_Error_Invalid_Product_Id' ) ) );
				Message::addErrorMessage($msg);					
				$ok = false;
				continue;
			}
			if (in_array( $product_id, $product_ids )) {
				$msg = str_replace( '%2', $product_id, str_replace( '%1', 'Products', Utilities::getLabel( 'L_Error_Duplicate_Product_Id' ) ) );
				Message::addErrorMessage($msg);				
				$ok = false;
				continue;
			}
			$product_ids[] = $product_id;
		}
		
		// make sure product_ids are numeric entries and are also mentioned in worksheet 'Products'
		$worksheets = array( 'ProductImages', 'SpecialDiscounts', 'QuantityDiscounts', 'ProductOptions', 'ProductOptionValues', 'Specifications', 'ProductFilters','ProductShippingRates' );
		foreach ($worksheets as $worksheet) {
			$data = $reader->getSheetByName( $worksheet );
			if ($data==null) {
				continue;
			}
			$has_missing_product_ids = false;
			$unlisted_product_ids = array();
			$k = $data->getHighestRow();
			for ($i=1; $i<$k; $i+=1) {
				$product_id = trim($this->getCell($data,$i,1));
				if ($product_id=="") {
					if (!$has_missing_product_ids) {
						$msg = str_replace( '%1', $worksheet, Utilities::getLabel( 'L_Error_Missing_Product_id' ) );
						Message::addErrorMessage($msg);	
						$has_missing_product_ids = true;
					}
					$ok = false;
					continue;
				}
				if (!ctype_digit($product_id)) {
					$msg = str_replace( '%2', $product_id, str_replace( '%1', $worksheet, Utilities::getLabel( 'L_Error_Invalid_Product_Id' ) ) );
					Message::addErrorMessage($msg);	
					$ok = false;
					continue;
				}
				if (!in_array( $product_id, $product_ids )) {
					if (!in_array( $product_id, $unlisted_product_ids )) {
						$unlisted_product_ids[] = $product_id;
						$msg = str_replace( '%2', $product_id, str_replace( '%1', $worksheet, Utilities::getLabel( 'L_Error_Unlisted_Product_Id' ) ) );
						Message::addErrorMessage($msg);	
						$ok = false;
						continue;
					}
				}
			}
		}
		return $ok;
	}
	
	protected function validateOptionColumns( &$reader ) { 
		// get all existing options and option values
		$ok = true;
			
		$export_import_settings_use_option_id = $this->exportImportSettings['export_import_settings_use_option_id'];
		$export_import_settings_use_option_value_id = $this->exportImportSettings['export_import_settings_use_option_value_id'];
		$srch = new SearchBase('tbl_options', 'o');
		$srch->addFld('o.option_id, o.option_name, ov.option_value_id, ov.option_value_name');
		$srch->joinTable('tbl_option_values', 'LEFT JOIN', 'ov.option_id = o.option_id', 'ov');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();		
		
		$options = array();
		
		while ($row=$this->db->fetch($rs)) {		
			if ($export_import_settings_use_option_id) {
				$option_id = $row['option_id'];
				if (!isset($options[$option_id])) {
					$options[$option_id] = array();
				}
				if ($export_import_settings_use_option_value_id) {
					$option_value_id = $row['option_value_id'];
					if (!is_null($option_value_id)) {
						$options[$option_id][$option_value_id] = true;
					}
				} else {
					$option_value_name = htmlspecialchars_decode($row['option_value_name']);
					if (!is_null($option_value_name)) {
						$options[$option_id][$option_value_name] = true;
					}
				}
			} else {
				$option_name = htmlspecialchars_decode($row['option_name']);
				if (!isset($options[$option_name])) {
					$options[$option_name] = array();
				}
				if ($export_import_settings_use_option_value_id) {
					$option_value_id = $row['option_value_id'];
					if (!is_null($option_value_id)) {
						$options[$option_name][$option_value_id] = true;
					}
				} else {
					$option_value_name = htmlspecialchars_decode($row['option_value_name']);
					if (!is_null($option_value_name)) {
						$options[$option_name][$option_value_name] = true;
					}
				}
			}
		}
		
		// only existing options can be used in 'ProductOptions' worksheet
		
		$product_options = array();
		$data = $reader->getSheetByName( 'ProductOptions' );
		if ($data!==null) {			
			$has_missing_options = false;
			$i = 0;
			$k = $data->getHighestRow();
			for ($i=1; $i<$k; $i+=1) {
				$product_id = trim($this->getCell($data,$i,1));
				if ($product_id=="") {
					continue;
				}
				if ($export_import_settings_use_option_id) {
					$option_id = trim($this->getCell($data,$i,2));
					if ($option_id=="") {
						if (!$has_missing_options) {
							$msg = str_replace( '%1', 'ProductOptions', Utilities::getLabel( 'L_Error_Missing_Option_Id' ) );
							Message::addErrorMessage($msg);							
							$has_missing_options = true;
						}
						$ok = false;
						continue;
					}
					if (!isset($options[$option_id])) {
						$msg = str_replace( '%2', $option_id, str_replace( '%1', 'ProductOptions',Utilities::getLabel( 'L_Error_Invalid_Option_Id' )) );
						Message::addErrorMessage($msg);							
						$ok = false;
						continue;
					}
					$product_options[$product_id][$option_id] = true;
				} else {
					$option_name = trim($this->getCell($data,$i,2));
					if ($option_name=="") {
						if (!$has_missing_options) {
							$msg = str_replace( '%1', 'ProductOptions', Utilities::getLabel( 'L_Error_Missing_Option_Name' ) );
							Message::addErrorMessage($msg);						
							$has_missing_options = true;
						}
						$ok = false;
						continue;
					}
					if (!isset($options[$option_name])) {
						$msg = str_replace( '%2', $option_name, str_replace( '%1', 'ProductOptions',Utilities::getLabel( 'L_Error_Invalid_Option_Name' )) );
						Message::addErrorMessage($msg);						
						$ok= false;
						continue;
					}
					$product_options[$product_id][$option_name] = true;
				}
			}
		}
		
		// only existing options and option values can be used in 'ProductOptionValues' worksheet
		$data = $reader->getSheetByName( 'ProductOptionValues' );
		if ($data!==null) {			
			$has_missing_options = false;
			$has_missing_option_values = false;
			$i = 0;
			$k = $data->getHighestRow();
			for ($i=1; $i<$k; $i+=1) {
				$product_id = trim($this->getCell($data,$i,1));
				if ($product_id=="") {
					continue;
				}
				if ($export_import_settings_use_option_id) {
					$option_id = trim($this->getCell($data,$i,2));
					if ($option_id=="") {
						if (!$has_missing_options) {
							$msg = str_replace( '%1', 'ProductOptionValues', Utilities::getLabel( 'L_Error_Missing_Option_Id' ) );
							Message::addErrorMessage($msg);							
							$has_missing_options = true;
						}
						$ok = false;
						continue;
					}
					if (!isset($options[$option_id])) {
						$msg = str_replace( '%2', $option_id, str_replace( '%1', 'ProductOptionValues',Utilities::getLabel( 'L_Error_Invalid_Option_Id' )) );
						Message::addErrorMessage($msg);					
						$ok = false;
						continue;
					}
					if (!isset($product_options[$product_id][$option_id])) {
						$msg = Utilities::getLabel( 'L_Error_Invalid_Product_Id_Option_Id' );
						$msg = str_replace( '%1', 'ProductOptionValues', $msg );
						$msg = str_replace( '%2', $product_id, $msg );
						$msg = str_replace( '%3', $option_id, $msg );
						$msg = str_replace( '%4', 'ProductOptions', $msg );
						Message::addErrorMessage($msg);		
						$ok = false;
						continue;
					}
					if ($export_import_settings_use_option_value_id) {
						$option_value_id = trim($this->getCell($data,$i,3));
						if ($option_value_id=="") {
							if (!$has_missing_option_values) {
								$msg = str_replace( '%1', 'ProductOptionValues', Utilities::getLabel( 'L_Error_Missing_Option_Value_Id' ) );
								Message::addErrorMessage($msg);		
								$has_missing_option_values = true;
							}
							$ok = false;
							continue;
						}
						if (!isset($options[$option_id][$option_value_id])) {
							$msg = Utilities::getLabel( 'L_Error_Invalid_Option_Id_Option_Value_Id' );
							$msg = str_replace( '%1', 'ProductOptionValues', $msg );
							$msg = str_replace( '%2', $option_id, $msg );
							$msg = str_replace( '%3', $option_value_id, $msg );
							Message::addErrorMessage($msg);		
							$ok = false;
							continue;
						}
					} else {
						$option_value_name = trim($this->getCell($data,$i,3));
						if ($option_value_name=="") {
							if (!$has_missing_option_values) {
								$msg = str_replace( '%1', 'ProductOptionValues', Utilities::getLabel( 'L_Error_Missing_Option_Value_Name' ) );
								Message::addErrorMessage($msg);
								$has_missing_option_values = true;
							}
							$ok = false;
							continue;
						}
						if (!isset($options[$option_id][$option_value_name])) {
							$msg = Utilities::getLabel( 'L_Error_Invalid_Option_Id_Option_Value_Name' );
							$msg = str_replace( '%1', 'ProductOptionValues', $msg );
							$msg = str_replace( '%2', $option_id, $msg );
							$msg = str_replace( '%3', $option_value_name, $msg );
							Message::addErrorMessage($msg);
							$ok = false;
							continue;
						}
					}
				} else {
					$option_name = trim($this->getCell($data,$i,2));
					if ($option_name=="") {
						if (!$has_missing_options) {
							$msg = str_replace( '%1', 'ProductOptionValues', Utilities::getLabel( 'L_Error_Missing_Option_Name' ) );
							Message::addErrorMessage($msg);
							$has_missing_options = true;
						}
						$ok = false;
						continue;
					}
					if (!isset($options[$option_name])) {
						$msg = Utilities::getLabel( 'L_Error_Invalid_Option_Name' );
						$msg = str_replace( '%1', 'ProductOptionValues', $msg );
						$msg = str_replace( '%2', $option_name, $msg );
						Message::addErrorMessage($msg);
						$ok= false;
						continue;
					}
					if (!isset($product_options[$product_id][$option_name])) {
						$msg = Utilities::getLabel( 'L_Error_Invalid_Product_Id_Option_Name' );
						$msg = str_replace( '%1', 'ProductOptionValues', $msg );
						$msg = str_replace( '%2', $product_id, $msg );
						$msg = str_replace( '%3', $option_name, $msg );
						$msg = str_replace( '%4', 'ProductOptions', $msg );
						Message::addErrorMessage($msg);
						$ok = false;
						continue;
					}
					if ($export_import_settings_use_option_value_id) {
						$option_value_id = trim($this->getCell($data,$i,3));
						if ($option_value_id=="") {
							if (!$has_missing_option_values) {
								$msg = str_replace( '%1', 'ProductOptionValues', Utilities::getLabel( 'L_Error_Missing_Option_Value_Id' ) );
								Message::addErrorMessage($msg);
								$has_missing_option_values = true;
							}
							$ok = false;
							continue;
						}
						if (!isset($options[$option_name][$option_value_id])) {
							$msg = Utilities::getLabel( 'L_Error_Invalid_Option_Name_Option_Value_Id' );
							$msg = str_replace( '%1', 'ProductOptionValues', $msg );
							$msg = str_replace( '%2', $option_name, $msg );
							$msg = str_replace( '%3', $option_value_id, $msg );
							Message::addErrorMessage($msg);
							$ok = false;
							continue;
						}
					} else {
						$option_value_name = trim($this->getCell($data,$i,3));
						if ($option_value_name=="") {
							if (!$has_missing_option_values) {
								$msg = str_replace( '%1', 'ProductOptionValues', Utilities::getLabel( 'L_Error_Missing_Option_Value_Name' ) );
								Message::addErrorMessage($msg);
								$has_missing_option_values = true;
							}
							$ok = false;
							continue;
						}
						if (!isset($options[$option_name][$option_value_name])) {
							$msg = Utilities::getLabel( 'L_Error_Invalid_Option_Name_Option_Value_Name' );
							$msg = str_replace( '%1', 'ProductOptionValues', $msg );
							$msg = str_replace( '%2', $option_name, $msg );
							$msg = str_replace( '%3', $option_value_name, $msg );
							Message::addErrorMessage($msg);
							$ok = false;
							continue;
						}
					}
				}
			}	
		}
		
		// only existing options can be used in 'options' worksheet
		$data = $reader->getSheetByName( 'Options' );
		if ($data!==null) {			
			// only unique numeric option_ids can be used in worksheet 'Options'
			$has_missing_option_ids = false;
			$option_ids = array();
			$k = $data->getHighestRow();
			for ($i=1; $i<$k; $i+=1) {
				$option_id = trim($this->getCell($data,$i,1));
				if ($option_id=="") {
					if (!$has_missing_option_ids) {
						$msg = str_replace( '%1', 'Options', Utilities::getLabel( 'L_Error_Missing_Option_Id' ) );
						Message::addErrorMessage($msg);						
						$has_missing_option_ids = true;
					}
					$ok = false;
					continue;
				}
				if (!ctype_digit($option_id)) {
					$msg = str_replace( '%2', $option_id, str_replace( '%1', 'Options',Utilities::getLabel( 'L_Error_Invalid_Option_Id' ) ) );
					Message::addErrorMessage($msg);					
					$ok = false;
					continue;
				}
				if (in_array( $option_id, $option_ids )) {
					$msg = str_replace( '%2', $option_id, str_replace( '%1', 'Options', Utilities::getLabel( 'L_Error_Duplicate_Option_Id' ) ) );
					Message::addErrorMessage($msg);				
					$ok = false;
					continue;
				}
				$option_ids[] = $option_id;
			}	
		}
		// only existing options can be used in 'options' worksheet
		$data = $reader->getSheetByName( 'OptionValues' );
		if ($data!==null) {
			$has_missing_option_value_ids = false;
			$option_value_ids = array();
			$k = $data->getHighestRow();
			for ($i=1; $i<$k; $i+=1) {
				$option_value_id = trim($this->getCell($data,$i,1));
				if ($option_value_id=="") {
					if (!$has_missing_option_value_ids) {
						$msg = str_replace( '%1', 'OptionValues', Utilities::getLabel( 'L_Error_Missing_Option_Value_Id' ) );
						Message::addErrorMessage($msg);						
						$has_missing_option_value_ids = true;
					}
					$ok = false;
					continue;
				}
				if (!ctype_digit($option_value_id)) {
					$msg = str_replace( '%2', $option_value_id, str_replace( '%1', 'OptionValues',Utilities::getLabel( 'L_Error_Invalid_Option_Value_Id' ) ) );
					Message::addErrorMessage($msg);					
					$ok = false;
					continue;
				}
				if (in_array( $option_value_id, $option_value_ids )) {
					$msg = str_replace( '%2', $option_value_id, str_replace( '%1', 'OptionValues', Utilities::getLabel( 'L_Error_Duplicate_Option_Value_Id' ) ) );
					Message::addErrorMessage($msg);				
					$ok = false;
					continue;
				}
				$option_value_ids[] = $option_value_id;
			}
		}		
		return $ok;		
	}
	
	protected function validateAttributeColumns( &$reader ) {
		// get all existing attribute_groups and attributes
		$ok = true;
		
		$export_import_settings_use_attribute_group_id = $this->exportImportSettings['export_import_settings_use_attribute_group_id'];
		$export_import_settings_use_attribute_id = $this->exportImportSettings['export_import_settings_use_attribute_id'];
		
		$srch = new SearchBase('tbl_attribute_groups', 'ag');
		$srch->addFld('ag.attribute_group_id, ag.attribute_group_name,a.attribute_id, a.attribute_name');
		$srch->joinTable('tbl_attributes', 'LEFT JOIN', 'a.attribute_group = ag.attribute_group_id', 'a');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();		
		
		$attribute_groups = array();
		
		while ($row=$this->db->fetch($rs)) {
			if ($export_import_settings_use_attribute_group_id) {
				$attribute_group_id = $row['attribute_group_id'];
				if (!isset($attribute_groups[$attribute_group_id])) {
					$attribute_groups[$attribute_group_id] = array();
				}
				if ($export_import_settings_use_attribute_id) {
					$attribute_id = $row['attribute_id'];
					if (!is_null($attribute_id)) {
						$attribute_groups[$attribute_group_id][$attribute_id] = true;
					}
				} else {
					$attribute_name = htmlspecialchars_decode($row['attribute_name']);
					if (!is_null($attribute_name)) {
						$attribute_groups[$attribute_group_id][$attribute_name] = true;
					}
				}
			} else {
				$attribute_group_name = htmlspecialchars_decode($row['attribute_group_name']);
				if (!isset($attribute_groups[$attribute_group_name])) {
					$attribute_groups[$attribute_group_name] = array();
				}
				if ($export_import_settings_use_attribute_id) {
					$attribute_id = $row['attribute_id'];
					if (!is_null($attribute_id)) {
						$attribute_groups[$attribute_group_name][$attribute_id] = true;
					}
				} else {
					$attribute_name = htmlspecialchars_decode($row['attribute_name']);
					if (!is_null($attribute_name)) {
						$attribute_groups[$attribute_group_name][$attribute_name] = true;
					}
				}
			}
		}
		
		// only existing attribute_groups and attributes can be used in 'Specifications' worksheet
		$data = $reader->getSheetByName( 'Specifications' );
		if ($data!==null) {
			$has_missing_attribute_groups = false;
			$has_missing_attributes = false;
			$i = 0;
			$k = $data->getHighestRow();
			for ($i=1; $i<$k; $i+=1) {
				$product_id = trim($this->getCell($data,$i,1));
				if ($product_id=="") {
					continue;
				}
				if ($export_import_settings_use_attribute_group_id) {
					$attribute_group_id = trim($this->getCell($data,$i,2));
					if ($attribute_group_id=="") {
						if (!$has_missing_attribute_groups) {
							$msg = str_replace( '%1', 'Specifications', Utilities::getLabel( 'L_Error_Missing_Attribute_Group_Id' ) );
							Message::addErrorMessage($msg);
							$has_missing_attribute_groups = true;
						}
						$ok = false;
						continue;
					}
					if (!isset($attribute_groups[$attribute_group_id])) {
						$msg = Utilities::getLabel( 'L_Error_Invalid_Attribute_Group_Id' );
						$msg = str_replace( '%1', 'Specifications', $msg );
						$msg = str_replace( '%2', $attribute_group_id, $msg );
						Message::addErrorMessage($msg);
						$ok = false;
						continue;
					}
					if ($export_import_settings_use_attribute_id) {
						$attribute_id = trim($this->getCell($data,$i,3));
						if ($attribute_id=="") {
							if (!$has_missing_attributes) {
								$msg = str_replace( '%1', 'Specifications', Utilities::getLabel( 'L_Error_Missing_Attribute_Id' ) );
								Message::addErrorMessage($msg);
								$has_missing_attributes = true;
							}
							$ok = false;
							continue;
						}
						if (!isset($attribute_groups[$attribute_group_id][$attribute_id])) {
							$msg = Utilities::getLabel( 'L_Error_Invalid_Attribute_Group_Id_Attribute_Id' );
							$msg = str_replace( '%1', 'Specifications', $msg );
							$msg = str_replace( '%2', $attribute_group_id, $msg );
							$msg = str_replace( '%3', $attribute_id, $msg );
							Message::addErrorMessage($msg);
							$ok = false;
							continue;
						}
					} else {
						$attribute_name = trim($this->getCell($data,$i,3));
						if ($attribute_name=="") {
							if (!$has_missing_attributes) {
								$msg = str_replace( '%1', 'Specifications', Utilities::getLabel( 'L_Error_Missing_Attribute_Name' ) );
								Message::addErrorMessage($msg);
								$has_missing_attributes = true;
							}
							$ok = false;
							continue;
						}
						if (!isset($attribute_groups[$attribute_group_id][$attribute_name])) {
							$msg = Utilities::getLabel( 'L_Error_Invalid_Attribute_Group_Id_Attribute_Name' );
							$msg = str_replace( '%1', 'Specifications', $msg );
							$msg = str_replace( '%2', $attribute_group_id, $msg );
							$msg = str_replace( '%3', $attribute_name, $msg );
							Message::addErrorMessage($msg);
							$ok = false;
							continue;
						}
					}
				} else {
					$attribute_group_name = trim($this->getCell($data,$i,2));
					if ($attribute_group_name=="") {
						if (!$has_missing_attribute_groups) {
							$msg = str_replace( '%1', 'Specifications', Utilities::getLabel( 'L_Error_Missing_Attribute_Group_Name' ) );
							Message::addErrorMessage($msg);
							$has_missing_attribute_groups = true;
						}
						$ok = false;
						continue;
					}
					if (!isset($attribute_groups[$attribute_group_name])) {
						$msg = Utilities::getLabel( 'L_Error_Invalid_Attribute_Group_Name' );
						$msg = str_replace( '%1', 'Specifications', $msg );
						$msg = str_replace( '%2', $attribute_group_name, $msg );
						Message::addErrorMessage($msg);
						$ok= false;
						continue;
					}
					if ($export_import_settings_use_attribute_id) {
						$attribute_id = trim($this->getCell($data,$i,3));
						if ($attribute_id=="") {
							if (!$has_missing_attributes) {
								$msg = str_replace( '%1', 'Specifications', Utilities::getLabel( 'L_Error_Missing_Attribute_Id' ) );
								Message::addErrorMessage($msg);
								$has_missing_attributes = true;
							}
							$ok = false;
							continue;
						}
						if (!isset($attribute_groups[$attribute_group_name][$attribute_id])) {
							$msg = Utilities::getLabel( 'L_Error_Invalid_Attribute_Group_Name_Attribute_Id' );
							$msg = str_replace( '%1', 'Specifications', $msg );
							$msg = str_replace( '%2', $attribute_group_name, $msg );
							$msg = str_replace( '%3', $attribute_id, $msg );
							Message::addErrorMessage($msg);
							$ok = false;
							continue;
						}
					} else {
						$attribute_name = trim($this->getCell($data,$i,3));
						if ($attribute_name=="") {
							if (!$has_missing_attributes) {
								$msg = str_replace( '%1', 'Specifications', Utilities::getLabel( 'L_Error_Missing_Attribute_Name' ) );
								Message::addErrorMessage($msg);
								$has_missing_attributes = true;
							}
							$ok = false;
							continue;
						}
						if (!isset($attribute_groups[$attribute_group_name][$attribute_name])) {
							$msg = Utilities::getLabel( 'L_Error_Invalid_Attribute_Group_Name_Attribute_Name' );
							$msg = str_replace( '%1', 'Specifications', $msg );
							$msg = str_replace( '%2', $attribute_group_name, $msg );
							$msg = str_replace( '%3', $attribute_name, $msg );
							Message::addErrorMessage($msg);
							$ok = false;						
							continue;
						}
					}
				}
			}
		}
		
		// only unique numeric attribute_group_id can be used in worksheet 'AttributeGroups'
		$data = $reader->getSheetByName( 'AttributeGroups' );
		if ($data!==null) {						
			$has_missing_attribute_group_ids = false;
			$attribute_group_ids = array();
			$k = $data->getHighestRow();
			for ($i=1; $i<$k; $i+=1) {
				$attribute_group_id = trim($this->getCell($data,$i,1));
				if ($attribute_group_id=="") {
					if (!$has_missing_attribute_group_ids) {
						$msg = str_replace( '%1', 'AttributeGroups', Utilities::getLabel( 'L_Error_Missing_Attribute_Group_Id' ) );
						Message::addErrorMessage($msg);						
						$has_missing_attribute_group_ids = true;
					}
					$ok = false;
					continue;
				}
				if (!ctype_digit($attribute_group_id)) {
					$msg = str_replace( '%2', $attribute_group_id, str_replace( '%1', 'AttributeGroups',Utilities::getLabel( 'L_Error_Invalid_Attribute_Group_Id' ) ) );
					Message::addErrorMessage($msg);					
					$ok = false;
					continue;
				}
				if (in_array( $attribute_group_id, $attribute_group_ids )) {
					$msg = str_replace( '%2', $attribute_group_id, str_replace( '%1', 'AttributeGroups', Utilities::getLabel( 'L_Error_Duplicate_Attribute_Group_Id' ) ) );
					Message::addErrorMessage($msg);				
					$ok = false;
					continue;
				}
				$attribute_group_ids[] = $attribute_group_id;
			}	
		}
		
		// only unique numeric attribute_id can be used in worksheet 'Attributes'
		$data = $reader->getSheetByName( 'Attributes' );
		if ($data!==null) {						
			$has_missing_attribute_ids = false;
			$attribute_ids = array();
			$k = $data->getHighestRow();
			for ($i=1; $i<$k; $i+=1) {
				$attribute_id = trim($this->getCell($data,$i,1));
				if ($attribute_id=="") {
					if (!$has_missing_attribute_ids) {
						$msg = str_replace( '%1', 'Attributes', Utilities::getLabel( 'L_Error_Missing_Attribute_Id' ) );
						Message::addErrorMessage($msg);						
						$has_missing_attribute_ids = true;
					}
					$ok = false;
					continue;
				}
				if (!ctype_digit($attribute_id)) {
					$msg = str_replace( '%2', $attribute_id, str_replace( '%1', 'Attributes',Utilities::getLabel( 'L_Error_Invalid_Attribute_Id' ) ) );
					Message::addErrorMessage($msg);					
					$ok = false;
					continue;
				}
				if (in_array( $attribute_id, $attribute_ids )) {
					$msg = str_replace( '%2', $attribute_id, str_replace( '%1', 'Attributes', Utilities::getLabel( 'L_Error_Duplicate_Attribute_Id' ) ) );
					Message::addErrorMessage($msg);				
					$ok = false;
					continue;
				}
				$attribute_ids[] = $attribute_id;
			}	
		}
		
		return $ok;
	
	}
	protected function validateFilterColumns( &$reader ) {
		// get all existing filter_groups and filters
		$ok = true;
		$export_import_settings_use_filter_group_id = $this->exportImportSettings['export_import_settings_use_filter_group_id'];
		$export_import_settings_use_filter_id = $this->exportImportSettings['export_import_settings_use_filter_id'];
		
		$srch = new SearchBase('tbl_filter_groups', 'fg');
		$srch->addFld('fg.filter_group_id, fg.filter_group_name,f.filter_id, f.filter_name');
		$srch->joinTable('tbl_filters', 'LEFT JOIN', 'f.filter_group = fg.filter_group_id', 'f');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();		
		
		$filter_groups = array();
		
		while ($row=$this->db->fetch($rs)) {
			if ($export_import_settings_use_filter_group_id) {
				$filter_group_id = $row['filter_group_id'];
				if (!isset($filter_groups[$filter_group_id])) {
					$filter_groups[$filter_group_id] = array();
				}
				if ($export_import_settings_use_filter_id) {
					$filter_id = $row['filter_id'];
					if (!is_null($filter_id)) {
						$filter_groups[$filter_group_id][$filter_id] = true;
					}
				} else {
					$filter_name = htmlspecialchars_decode($row['filter_name']);
					if (!is_null($filter_name)) {
						$filter_groups[$filter_group_id][$filter_name] = true;
					}
				}
			} else {
				$filter_group_name = htmlspecialchars_decode($row['filter_group_name']);
				if (!isset($filter_groups[$filter_group_name])) {
					$filter_groups[$filter_group_name] = array();
				}
				if ($export_import_settings_use_filter_id) {
					$filter_id = $row['filter_id'];
					if (!is_null($filter_id)) {
						$filter_groups[$filter_group_name][$filter_id] = true;
					}
				} else {
					$filter_name = htmlspecialchars_decode($row['filter_name']);
					if (!is_null($filter_name)) {
						$filter_groups[$filter_group_name][$filter_name] = true;
					}
				}
			}
		}
		
		// only existing filter_groups and filters can be used in the 'ProductFilters' and 'CategoryFilters' worksheets
		$worksheet_names = array('ProductFilters','CategoryFilters');
		foreach ($worksheet_names as $worksheet_name) {
			$data = $reader->getSheetByName( 'ProductFilters' );
			if ($data!==null) {
				$has_missing_filter_groups = false;
				$has_missing_filters = false;
				$i = 0;
				$k = $data->getHighestRow();
				for ($i=1; $i<$k; $i+=1) {
					$id = trim($this->getCell($data,$i,1));
					if ($id=="") {
						continue;
					}
					if ($export_import_settings_use_filter_group_id) {
						$filter_group_id = trim($this->getCell($data,$i,2));
						if ($filter_group_id=="") {
							if (!$has_missing_filter_groups) {
								$msg = str_replace( '%1', $worksheet_name, Utilities::getLabel( 'L_Error_Missing_Filter_Group_Id' ) );
								Message::addErrorMessage($msg);
								$has_missing_filter_groups = true;
							}
							$ok = false;
							continue;
						}
						if (!isset($filter_groups[$filter_group_id])) {
							$msg = Utilities::getLabel( 'L_Error_Invalid_Filter_Group_Id' );
							$msg = str_replace( '%1', $worksheet_name, $msg );
							$msg = str_replace( '%2', $filter_group_id, $msg );
							Message::addErrorMessage($msg);
							$ok = false;
							continue;
						}
						if ($export_import_settings_use_filter_id) {
							$filter_id = trim($this->getCell($data,$i,3));
							if ($filter_id=="") {
								if (!$has_missing_filters) {
									$msg = str_replace( '%1', $worksheet_name, Utilities::getLabel( 'L_Error_Missing_Filter_Id' ) );
									Message::addErrorMessage($msg);
									$has_missing_filters = true;
								}
								$ok = false;
								continue;
							}
							if (!isset($filter_groups[$filter_group_id][$filter_id])) {
								$msg = Utilities::getLabel( 'L_Error_Invalid_Filter_Group_Id_Filter_Id' );
								$msg = str_replace( '%1', $worksheet_name, $msg );
								$msg = str_replace( '%2', $filter_group_id, $msg );
								$msg = str_replace( '%3', $filter_id, $msg );
								Message::addErrorMessage($msg);
								$ok = false;
								continue;
							}
						} else {
							$filter_name = trim($this->getCell($data,$i,3));
							if ($filter_name=="") {
								if (!$has_missing_filters) {
									$msg = str_replace( '%1', $worksheet_name, Utilities::getLabel( 'L_Error_Missing_Filter_Name' ) );
									Message::addErrorMessage($msg);
									$has_missing_filters = true;
								}
								$ok = false;
								continue;
							}
							if (!isset($filter_groups[$filter_group_id][$filter_name])) {
								$msg = Utilities::getLabel( 'L_Error_Invalid_Filter_Group_Id_Filter_Name' );
								$msg = str_replace( '%1', $worksheet_name, $msg );
								$msg = str_replace( '%2', $filter_group_id, $msg );
								$msg = str_replace( '%3', $filter_name, $msg );
								Message::addErrorMessage($msg);
								$ok = false;
								continue;
							}
						}
					} else {
						$filter_group_name = trim($this->getCell($data,$i,2));
						if ($filter_group_name=="") {
							if (!$has_missing_filter_groups) {
								$msg = str_replace( '%1', $worksheet_name, Utilities::getLabel( 'L_Error_Missing_Filter_Group_Name' ) );
								Message::addErrorMessage($msg);
								$has_missing_filter_groups = true;
							}
							$ok = false;
							continue;
						}
						if (!isset($filter_groups[$filter_group_name])) {
							$msg = Utilities::getLabel( 'L_Error_Invalid_Filter_Group_Name' );
							$msg = str_replace( '%1', $worksheet_name, $msg );
							$msg = str_replace( '%2', $filter_group_name, $msg );
							Message::addErrorMessage($msg);
							$ok= false;
							continue;
						}
						if ($export_import_settings_use_filter_id) {
							$filter_id = trim($this->getCell($data,$i,3));
							if ($filter_id=="") {
								if (!$has_missing_filters) {
									$msg = str_replace( '%1', $worksheet_name, Utilities::getLabel( 'L_Error_Missing_Filter_Id' ) );
									Message::addErrorMessage($msg);
									$has_missing_filters = true;
								}
								$ok = false;
								continue;
							}
							if (!isset($filter_groups[$filter_group_name][$filter_id])) {
								$msg = Utilities::getLabel( 'L_Error_Invalid_Filter_Group_Name_Filter_Id' );
								$msg = str_replace( '%1', $worksheet_name, $msg );
								$msg = str_replace( '%2', $filter_group_name, $msg );
								$msg = str_replace( '%3', $filter_id, $msg );
								Message::addErrorMessage($msg);
								$ok = false;
								continue;
							}
						} else {
							$filter_name = trim($this->getCell($data,$i,3));
							if ($filter_name=="") {
								if (!$has_missing_filters) {
									$msg = str_replace( '%1', $worksheet_name, Utilities::getLabel( 'L_Error_Missing_Filter_Name' ) );
									Message::addErrorMessage($msg);
									$has_missing_filters = true;
								}
								$ok = false;
								continue;
							}
							if (!isset($filter_groups[$filter_group_name][$filter_name])) {
								$msg = Utilities::getLabel( 'L_Error_Invalid_Filter_Group_Name_Filter_Name' );
								$msg = str_replace( '%1', $worksheet_name, $msg );
								$msg = str_replace( '%2', $filter_group_name, $msg );
								$msg = str_replace( '%3', $filter_name, $msg );
								Message::addErrorMessage($msg);
								$ok = false;
								continue;
							}
						}
					}
				}
			}
		}
		
		// only unique numeric filter_group_id can be used in worksheet 'FilterGroups'
		$data = $reader->getSheetByName( 'FilterGroups' );
		if ($data!==null) {						
			$has_missing_filter_group_ids = false;
			$filter_group_ids = array();
			$k = $data->getHighestRow();
			for ($i=1; $i<$k; $i+=1) {
				$filter_group_id = trim($this->getCell($data,$i,1));
				if ($filter_group_id=="") {
					if (!$has_missing_filter_group_ids) {
						$msg = str_replace( '%1', 'FilterGroups', Utilities::getLabel( 'L_Error_Missing_Filter_Group_Id' ) );
						Message::addErrorMessage($msg);						
						$has_missing_filter_group_ids = true;
					}
					$ok = false;
					continue;
				}
				if (!ctype_digit($filter_group_id)) {
					$msg = str_replace( '%2', $filter_group_id, str_replace( '%1', 'FilterGroups',Utilities::getLabel( 'L_Error_Invalid_Filter_Group_Id' ) ) );
					Message::addErrorMessage($msg);					
					$ok = false;
					continue;
				}
				if (in_array( $filter_group_id, $filter_group_ids )) {
					$msg = str_replace( '%2', $filter_group_id, str_replace( '%1', 'FilterGroups', Utilities::getLabel( 'L_Error_Duplicate_Filter_Group_Id' ) ) );
					Message::addErrorMessage($msg);				
					$ok = false;
					continue;
				}
				$filter_group_ids[] = $filter_group_id;
			}	
		}
		
		// only unique numeric filter_id can be used in worksheet 'Filters'
		$data = $reader->getSheetByName( 'Filters' );
		if ($data!==null) {						
			$has_missing_filter_ids = false;
			$filter_ids = array();
			$k = $data->getHighestRow();
			for ($i=1; $i<$k; $i+=1) {
				$filter_id = trim($this->getCell($data,$i,1));
				if ($filter_id=="") {
					if (!$has_missing_filter_ids) {
						$msg = str_replace( '%1', 'Filters', Utilities::getLabel( 'L_Error_Missing_Filter_Id' ) );
						Message::addErrorMessage($msg);						
						$has_missing_filter_ids = true;
					}
					$ok = false;
					continue;
				}
				if (!ctype_digit($filter_id)) {
					$msg = str_replace( '%2', $filter_id, str_replace( '%1', 'Filters',Utilities::getLabel( 'L_Error_Invalid_Filter_Id' ) ) );
					Message::addErrorMessage($msg);					
					$ok = false;
					continue;
				}
				if (in_array( $filter_id, $filter_ids )) {
					$msg = str_replace( '%2', $filter_id, str_replace( '%1', 'Filters', Utilities::getLabel( 'L_Error_Duplicate_Filter_Id' ) ) );
					Message::addErrorMessage($msg);				
					$ok = false;
					continue;
				}
				$filter_ids[] = $filter_id;
			}	
		}
		return $ok;
	}
	
	protected function validateShippingDurationColumns(&$reader)
	{	
		$ok = true;
		
		// only unique numeric duration_id can be used in worksheet 'ShippingDurations'
		$data = $reader->getSheetByName( 'ShippingDurations' );
		if ($data!==null) {						
			$has_missing_duration_ids = false;
			$duration_ids = array();
			$k = $data->getHighestRow();
			for ($i=1; $i<$k; $i+=1) {
				$duration_id = trim($this->getCell($data,$i,1));
				if ($duration_id=="") {
					if (!$has_missing_duration_ids) {
						$msg = str_replace( '%1', 'ShippingDurations', Utilities::getLabel( 'L_Error_Missing_Duration_Id' ) );
						Message::addErrorMessage($msg);						
						$has_missing_duration_ids = true;
					}
					$ok = false;
					continue;
				}
				if (!ctype_digit($duration_id)) {
					$msg = str_replace( '%2', $duration_id, str_replace( '%1', 'ShippingDurations',Utilities::getLabel( 'L_Error_Invalid_Duration_Id' ) ) );
					Message::addErrorMessage($msg);					
					$ok = false;
					continue;
				}
				if (in_array( $duration_id, $duration_ids )) {
					$msg = str_replace( '%2', $duration_id, str_replace( '%1', 'ShippingDurations', Utilities::getLabel( 'L_Error_Duplicate_Duration_Id' ) ) );
					Message::addErrorMessage($msg);				
					$ok = false;
					continue;
				}
				$duration_ids[] = $duration_id;
			}	
		}
		
		return $ok;
	}
	
	protected function validateShippingCompaniesColumns(&$reader)
	{	
		$ok = true;
		
		// only unique numeric company_id can be used in worksheet 'ShippingCompanies'
		$data = $reader->getSheetByName( 'ShippingCompanies' );
		if ($data!==null) {						
			$has_missing_company_ids = false;
			$company_ids = array();
			$k = $data->getHighestRow();
			for ($i=1; $i<$k; $i+=1) {
				$company_id = trim($this->getCell($data,$i,1));
				if ($company_id=="") {
					if (!$has_missing_company_ids) {
						$msg = str_replace( '%1', 'ShippingCompanies', Utilities::getLabel( 'L_Error_Missing_Company_Id' ) );
						Message::addErrorMessage($msg);						
						$has_missing_company_ids = true;
					}
					$ok = false;
					continue;
				}
				if (!ctype_digit($company_id)) {
					$msg = str_replace( '%2', $company_id, str_replace( '%1', 'ShippingCompanies',Utilities::getLabel( 'L_Error_Invalid_Company_Id' ) ) );
					Message::addErrorMessage($msg);					
					$ok = false;
					continue;
				}
				if (in_array( $company_id, $company_ids )) {
					$msg = str_replace( '%2', $company_id, str_replace( '%1', 'ShippingCompanies', Utilities::getLabel( 'L_Error_Duplicate_Company_Id' ) ) );
					Message::addErrorMessage($msg);				
					$ok = false;
					continue;
				}
				$company_ids[] = $company_id;
			}	
		}
		
		return $ok;
	}
	protected function validateShippingDurations(&$reader)
	{
		$data = $reader->getSheetByName( 'ShippingDurations' );
		if ($data==null) {
			return true;
		}
		$expected_heading = array( "duration_id", "label", "duration_from", "duration_to" ,"days_or_weeks","is_deleted");		
		return $this->validateHeading( $data, $expected_heading);
	}
	protected function validateProductStockAndSku(&$reader)
	{
		$data = $reader->getSheetByName( 'StockAndSKU' );
		if ($data==null) {
			return true;
		}
		$expected_heading = array( "prod_id", "name", "sku", "stock");		
		return $this->validateHeading( $data, $expected_heading);
	}
	protected function validateShippingCompanies(&$reader)
	{
		$data = $reader->getSheetByName( 'ShippingCompanies' );
		if ($data==null) {
			return true;
		}
		$expected_heading = array( "company_id", "name", "website", "comments" ,"is_deleted");		
		return $this->validateHeading( $data, $expected_heading);
	}
	protected function getCategoryUrlAliasIds() {
		$sql  = "SELECT url_alias_id, SUBSTRING( url_alias_query, CHAR_LENGTH('category_id=')+1 ) AS category_id ";
		$sql .= "FROM `tbl_url_alias` ";
		$sql .= "WHERE url_alias_query LIKE 'category_id=%'";
		$query = $this->db->query( $sql );
		$res = $this->db->fetch_all($query);		
		$url_alias_ids = array();
		foreach ($res as $row) {
			$url_alias_id = $row['url_alias_id'];
			$category_id = $row['category_id'];
			$url_alias_ids[$category_id] = $url_alias_id;
		}
		return $url_alias_ids;
	}
	protected function getTagUrlAliasIds($shop_user_id = 0) {
		$sql  = "SELECT url_alias_id, SUBSTRING( url_alias_query, CHAR_LENGTH('tags_id=')+1 ) AS tags_id ";
		$sql .= "FROM `tbl_url_alias` ";
		if(!empty($shop_user_id)){
			$sql .= "inner join tbl_product_tags on SUBSTRING( url_alias_query, CHAR_LENGTH('tags_id=')+1 ) = ptag_id and ptag_added_by= $shop_user_id and ptag_owner ='U' ";
		}
		$sql .= "WHERE url_alias_query LIKE 'tags_id=%'";
		$query = $this->db->query( $sql );
		$res = $this->db->fetch_all($query);		
		$url_alias_ids = array();
		foreach ($res as $row) {
			$url_alias_id = $row['url_alias_id'];
			$tags_id = $row['tags_id'];
			$url_alias_ids[$tags_id] = $url_alias_id;
		}
		return $url_alias_ids;
	}
	
	protected function getAvailableTagIds($shop_user_id = 0) {
		$sql = "SELECT `ptag_id` FROM `tbl_product_tags` ";
		if(!empty($shop_user_id)){
			$sql .=" WHERE ptag_owner='U' and ptag_added_by='$shop_user_id' ; ";
		}
		$query = $this->db->query( $sql );
		$res = $this->db->fetch_all($query);
		$tag_ids = array();
		foreach ($res as $row) {
			$tag_ids[$row['ptag_id']] = $row['ptag_id'];
		}
		return $tag_ids;
	}
	
	protected function deleteTags( &$url_alias_ids,$shop_user_id=0 ) {
		$sql  = "DELETE FROM `tbl_product_tags` ";
		if(!empty($shop_user_id)){
			$sql .=" WHERE ptag_owner='U' and ptag_added_by='$shop_user_id' ;\n ";
		}
		else{
			$sql .=" ;\n ";
		}
		if(empty($shop_user_id)){
			$sql .= "DELETE FROM `tbl_url_alias` WHERE `url_alias_query` LIKE 'tags_id=%';\n ";
		}
		elseif(!empty($url_alias_ids) && count($url_alias_ids)){
			foreach($url_alias_ids as $url_alias_id){
				$sql .= "DELETE FROM `tbl_url_alias` WHERE `url_alias_query` LIKE 'tags_id=%' and url_alias_id = $url_alias_id ;\n ";
			}
		}
	
		$this->multiquery( $sql );
				
		$sql = "SELECT (MAX(url_alias_id)+1) AS next_url_alias_id FROM `tbl_url_alias` LIMIT 1";
		$query = $this->db->query( $sql );
		$res = $this->db->fetch($query);
		
		if(isset($res['next_url_alias_id'])){
			$next_url_alias_id = $res['next_url_alias_id'];		
			$sql = "ALTER TABLE `tbl_url_alias` AUTO_INCREMENT = $next_url_alias_id";
			$this->db->query( $sql );
		}
		$remove = array();
		foreach ($url_alias_ids as $tag_id=>$url_alias_id) {
			if ($url_alias_id >= $next_url_alias_id) {
				$remove[$tag_id] = $url_alias_id;
			}
		}
		foreach ($remove as $tag_id=>$url_alias_id) {
			unset($url_alias_ids[$tag_id]);
		}
	}
	
	protected function getAvailableCategoryIds($type='1') {
		$sql = "SELECT `category_id` FROM `tbl_categories` WHERE category_type='".$type."'";
		$query = $this->db->query( $sql );
		$res = $this->db->fetch_all($query);		
		$category_ids = array();
		foreach ($res as $row) {
			$category_ids[$row['category_id']] = $row['category_id'];
		}
		return $category_ids;
	}
	
	protected function deleteCategories( &$url_alias_ids ) {
		$sql  = "DELETE FROM `tbl_categories` WHERE category_type='1';\n";		
		$sql .= "DELETE FROM `tbl_url_alias` WHERE `url_alias_query` LIKE 'category_id=%';\n";
	
		$this->multiquery( $sql );
				
		$sql = "SELECT (MAX(url_alias_id)+1) AS next_url_alias_id FROM `tbl_url_alias` LIMIT 1";
		$query = $this->db->query( $sql );
		$res = $this->db->fetch($query);
		
		if(isset($res['next_url_alias_id'])){
			$next_url_alias_id = $res['next_url_alias_id'];		
			$sql = "ALTER TABLE `tbl_url_alias` AUTO_INCREMENT = $next_url_alias_id";
			$this->db->query( $sql );
		}
		$remove = array();
		foreach ($url_alias_ids as $category_id=>$url_alias_id) {
			if ($url_alias_id >= $next_url_alias_id) {
				$remove[$category_id] = $url_alias_id;
			}
		}
		foreach ($remove as $category_id=>$url_alias_id) {
			unset($url_alias_ids[$category_id]);
		}
	}
	
	protected function deleteTag( $tag_id ) {
		$sql  = "DELETE FROM `tbl_product_tags` WHERE `ptag_id` = '".(int)$tag_id."' ;\n";
		$sql .= "DELETE FROM `tbl_url_alias` WHERE `url_alias_query` LIKE 'tags_id=".(int)$tag_id."';\n";
		$this->multiquery( $sql );
	}
	
	protected function deleteCategory( $category_id ) {
		$sql  = "DELETE FROM `tbl_categories` WHERE `category_id` = '".(int)$category_id."' ;\n";
		$sql .= "DELETE FROM `tbl_url_alias` WHERE `url_alias_query` LIKE 'category_id=".(int)$category_id."';\n";
		$this->multiquery( $sql );
	}
	
	protected function deleteCategoryFilters() {
		$sql = "TRUNCATE TABLE `tbl_category_filter`";
		$this->db->query( $sql );
	}
	
	protected function deleteCategoryFilter( $category_id ) {
		$sql = "DELETE FROM `tbl_category_filter` WHERE category_id='".(int)$category_id."'";
		$this->db->query( $sql );
	}
	
	// function for reading additional cells in class extensions
	protected function moreCategoryFilterCells( $i, &$j, &$worksheet, &$category_filter ) {
		return;
	}
	
	protected function getFilterGroupIds() {		
		$sql  = "SELECT filter_group_id, filter_group_name FROM `tbl_filter_groups` ";
		$sql .= "WHERE filter_group_is_deleted='0'";
		$query = $this->db->query( $sql );
		$res = $this->db->fetch_all($query);		
		$filter_group_ids = array();
		foreach ($res as $row) {
			$filter_group_id = $row['filter_group_id'];
			$name = $row['filter_group_name'];
			$filter_group_ids[strtolower($name)] = $filter_group_id;
		}
		return $filter_group_ids;
	}
	protected function getFilterIds() {
		$sql  = "SELECT f.filter_id, fg.filter_group_id, f.filter_name FROM `tbl_filter_groups` fg ";
		$sql .= "INNER JOIN `tbl_filters` f ON f.filter_group=fg.filter_group_id ";
		$sql .= "WHERE fg.filter_group_is_deleted='0' AND f.filter_is_deleted='0'";
		$query = $this->db->query( $sql );
		$res = $this->db->fetch_all($query);		
		$filter_ids = array();
		foreach ($res as $row) {
			$filter_group_id = $row['filter_group_id'];
			$filter_id = $row['filter_id'];
			$name = $row['filter_name'];
			$filter_ids[$filter_group_id][strtolower($name)] = $filter_id;
		}
		return $filter_ids;
	}
	// function for reading additional cells in class extensions
	protected function moreCategoryCells( $i, &$j, &$worksheet, &$category ) {
		return;
	}
	
	protected function storeCategoryFilterIntoDatabase( &$category_filter ) {		
		$assign_fields=array(
			'category_id'=>$category_filter['category_id'],
			'filter_id'=>$category_filter['filter_id'],
		);
		$this->db->insert_from_array('tbl_category_filter',$assign_fields,false,false,$assign_fields);		
	}
	
	protected function storeCategoryCollectionIntoDatabase(&$category_collection)
	{
		$assign_fields=array(
			'dctc_collection_id'=>$category_collection['collection_id'],
			'dctc_category_id'=>$category_collection['category_id'],
			'dctc_display_order'=>$category_collection['display_order'],
		);
		$this->db->insert_from_array('tbl_collection_categories',$assign_fields,false,false,$assign_fields);		
	}
	
	protected function deleteUnlistedCategoryFilters( &$unlisted_category_ids ) {
		foreach ($unlisted_category_ids as $category_id) {
			$sql = "DELETE FROM `tbl_category_filter` WHERE category_id='".(int)$category_id."'";
			$this->db->query( $sql );
		}
	}
	
	protected function deleteCategoryCollections()
	{
		$sql = "TRUNCATE TABLE `tbl_collection_categories`";
		$this->db->query( $sql );
	}
	
	protected function deleteCategoryCollection( $category_id ) {
		$sql = "DELETE FROM `tbl_collection_categories` WHERE dctc_category_id='".(int)$category_id."'";
		$this->db->query( $sql );
	}
	
	// function for reading additional cells in class extensions
	protected function moreCategoryCollectionCells( $i, &$j, &$worksheet, &$category ) {
		return;
	}
	
	protected function deleteUnlistedCategoryCollections( &$unlisted_category_ids ) {
		foreach ($unlisted_category_ids as $category_id) {
			$sql = "DELETE FROM `tbl_collection_categories` WHERE dctc_category_id='".(int)$category_id."'";
			$this->db->query( $sql );
		}
	}
	
	protected function getProductViewCounts() {
		$query = $this->db->query( "SELECT prod_id, prod_view_count FROM `tbl_products`" );
		$res=$this->db->fetch_all($query);
		$view_counts = array();
		foreach ($res as $row) {
			$product_id = $row['prod_id'];
			$viewed = $row['prod_view_count'];
			$view_counts[$product_id] = $viewed;
		}
		return $view_counts;
	}
	
	protected function getProductSoldCounts() {
		$query = $this->db->query( "SELECT prod_id, prod_sold_count FROM `tbl_products`" );
		$res=$this->db->fetch_all($query);
		$view_counts = array();
		foreach ($res as $row) {
			$product_id = $row['prod_id'];
			$sold = $row['prod_sold_count'];
			$sold_counts[$product_id] = $sold;
		}
		return $sold_counts;
	}
	
	protected function getUser()
	{
		$query = $this->db->query( "SELECT user_id, user_username FROM `tbl_users` WHERE user_is_deleted='0'" );
		$res=$this->db->fetch_all($query);
		$users = array();
		foreach ($res as $row) {
			$user_id = $row['user_id'];
			$user_username = $row['user_username'];
			$users[$user_id] = $user_username;
		}
		return $users;
	}
	protected function getProductBrands()
	{
		$srch = new SearchBase('tbl_product_brands', 'b');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();
		return $row=$this->db->fetch_all($rs);
	}
	
	protected function getProductBrandsName($criteria)
	{
		$srch = new SearchBase('tbl_product_brands', 'b');
		$srch->addFld('brand_id, brand_name');
		if($active){
			$srch->addCondition('b.brand_is_deleted','=',0);		
			$srch->addCondition('b.brand_status','=',1);		
		}
		foreach($criteria as $key=>$val) {
			if(strval($val)=='') continue;
            switch($key) {
            case 'deleted':
                $srch->addCondition('b.brand_is_deleted','=',$val);
                break;
			case 'status':			
               $srch->addCondition('b.brand_status','=',$val);
                break;									
            }
        }
		
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();
		while($row=$this->db->fetch($rs)){
			$productBrands[$row['brand_id']] = $row['brand_name'];
		}		
		return $productBrands;
	}
	
	protected function getProductBrandsIds()
	{		
		$query = $this->db->query( "SELECT brand_id, brand_name FROM `tbl_product_brands`" );
		$res=$this->db->fetch_all($query);
		$productBrands = array();
		foreach ($res as $row) {
			$brand_id = $row['brand_id'];
			$brand_name = $row['brand_name'];
			$productBrands[strtolower($brand_name)] = $brand_id;
		}
		return $productBrands;
	}
	
	protected function getRelatedProducts()
	{
		$query = $this->db->query( "SELECT relation_source_id, relation_to_id FROM `tbl_product_relations`" );
		$res=$this->db->fetch_all($query);
		$relatedProducts = array();
		foreach ($res as $row) {
			$source_id = $row['relation_source_id'];
			$relation_id = $row['relation_to_id'];
			$relatedProducts[$source_id][] = $relation_id;
		}
		return $relatedProducts;
	}
	
	protected function getProductAddons()
	{
		$query = $this->db->query( "SELECT addon_source_id, addon_to_id FROM `tbl_product_addons`" );
		$res=$this->db->fetch_all($query);
		$productAddons = array();
		foreach ($res as $row) {
			$source_id = $row['addon_source_id'];
			$relation_id = $row['addon_to_id'];
			$productAddons[$source_id][] = $relation_id;
		}
		return $productAddons;
	}
	
	protected function getUsersName()
	{
		$query = $this->db->query( "SELECT user_id, user_username FROM `tbl_users`" );
		$res=$this->db->fetch_all($query);
		$users = array();
		foreach ($res as $row) {
			$user_id = $row['user_id'];
			$user_username = $row['user_username'];
			$users[$user_id] = $user_username;
		}
		return $users;
	}
	
	protected function getUsersId()
	{
		$query = $this->db->query( "SELECT user_id, user_username FROM `tbl_users`" );
		$res=$this->db->fetch_all($query);
		$users = array();
		foreach ($res as $row) {
			$user_id = $row['user_id'];
			$user_username = $row['user_username'];
			$users[strtolower($user_username)] = $user_id;
		}
		return $users;
	}
	protected function getShopUsers()
	{
		$query = $this->db->query( "SELECT shop_id, shop_user_id FROM `tbl_shops`" );
		$res=$this->db->fetch_all($query);
		$shops = array();
		foreach ($res as $row) {
			$shop_id = $row['shop_id'];
			$shop_user_id = $row['shop_user_id'];
			$shops[$shop_id] = $shop_user_id;
		}
		return $shops;
	}
	protected function getShopNames()
	{
		$query = $this->db->query( "SELECT shop_id, shop_name FROM `tbl_shops`" );
		$res=$this->db->fetch_all($query);
		$shops = array();
		foreach ($res as $row) {
			$shop_id = $row['shop_id'];
			$shop_name = $row['shop_name'];
			$shops[$shop_id] = $shop_name;
		}
		return $shops;
	}
	
	protected function getShopIds()
	{
		$query = $this->db->query( "SELECT shop_id, shop_name FROM `tbl_shops`" );
		$res=$this->db->fetch_all($query);
		$shops = array();
		foreach ($res as $row) {
			$shop_id = $row['shop_id'];
			$shop_name = $row['shop_name'];
			$shops[strtolower($shop_name)] = $shop_id;
		}
		return $shops;
	}
	protected function getOption()
	{
		$query = $this->db->query( "SELECT option_id, option_name FROM `tbl_options`" );
		$res=$this->db->fetch_all($query);
		$options = array();
		foreach ($res as $row) {
			$option_id = $row['option_id'];
			$option_name = $row['option_name'];
			$options[$option_id] = $option_name;
		}
		return $options;
	}
	protected function getTagNames()
	{
		$query = $this->db->query( "SELECT ptag_id, ptag_name FROM `tbl_product_tags`" );
		$res=$this->db->fetch_all($query);
		$tags = array();
		foreach ($res as $row) {
			$tag_id = $row['ptag_id'];
			$tag_name = $row['ptag_name'];
			$tags[$tag_id] = $tag_name;
		}
		return $tags;
	}
	protected function getOptionIds()
	{
		$query = $this->db->query( "SELECT option_id, option_name FROM `tbl_options`" );
		$res=$this->db->fetch_all($query);
		$options = array();
		foreach ($res as $row) {
			$option_id = $row['option_id'];
			$option_name = $row['option_name'];
			$options[strtolower($option_name)] = $option_id;
		}
		return $options;
	}
	protected function getTagIds()
	{
		$query = $this->db->query( "SELECT ptag_id, ptag_name FROM `tbl_product_tags`" );
		$res=$this->db->fetch_all($query);
		$tags = array();
		foreach ($res as $row) {
			$ptag_id = $row['ptag_id'];
			$ptag_name = $row['ptag_name'];
			$tags[strtolower($ptag_name)] = $ptag_id;
		}
		return $tags;
	}
	
	protected function getOptionValueNames() {		
		$sql  = "SELECT option_id, option_value_id, option_value_name FROM `tbl_option_values` ";
		$query = $this->db->query( $sql );
		$res=$this->db->fetch_all($query);
		$option_value_names = array();
		foreach ($res as $row) {
			$option_id = $row['option_id'];
			$option_value_id = $row['option_value_id'];
			$name = $row['option_value_name'];
			$option_value_names[$option_id][$option_value_id] = $name;
		}
		return $option_value_names;
	}
	
	protected function getOptionValueIds() {		
		$sql  = "SELECT option_id, option_value_id, option_value_name FROM `tbl_option_values` ";
		$query = $this->db->query( $sql );
		$res=$this->db->fetch_all($query);
		$option_value_ids = array();
		foreach ($res as $row) {
			$option_id = $row['option_id'];
			$option_value_id = $row['option_value_id'];
			$name = $row['option_value_name'];
			$option_value_ids[$option_id][strtolower($name)] = $option_value_id;
		}
		return $option_value_ids;
	}
	
	
	protected function getAttributeGroupIds() {		
		$sql  = "SELECT attribute_group_id, attribute_group_name FROM `tbl_attribute_groups` ";
		$query = $this->db->query( $sql );
		$res=$this->db->fetch_all($query);
		$attribute_group_ids = array();
		foreach ($res as $row) {
			$attribute_group_id = $row['attribute_group_id'];
			$name = $row['attribute_group_name'];
			$attribute_group_ids[strtolower($name)] = $attribute_group_id;
		}
		return $attribute_group_ids;
	}
	
	protected function getAttributeIds() {		
		$sql  = "SELECT ag.attribute_group_id, a.attribute_id, a.attribute_name FROM `tbl_attribute_groups` ag ";
		$sql .= "INNER JOIN `tbl_attributes` a ON a.attribute_group=ag.attribute_group_id ";
		$query = $this->db->query( $sql );
		$res=$this->db->fetch_all($query);
		$attribute_ids = array();
		foreach ($res as $row) {
			$attribute_group_id = $row['attribute_group_id'];
			$attribute_id = $row['attribute_id'];
			$name = $row['attribute_name'];
			$attribute_ids[$attribute_group_id][strtolower($name)] = $attribute_id;
		}
		return $attribute_ids;
	}
	
	protected function getProductOptionIds( $product_id ) {
		$sql  = "SELECT product_option_id, option_id FROM `tbl_product_option` ";
		$sql .= "WHERE product_id='".(int)$product_id."'";
		$query = $this->db->query( $sql );
		$res=$this->db->fetch_all($query);
		$product_option_ids = array();
		foreach ($res as $row) {
			$product_option_id = $row['product_option_id'];
			$option_id = $row['option_id'];
			$product_option_ids[$option_id] = $product_option_id;
		}
		return $product_option_ids;
	}
		
	protected function getProductUrlAliasIds($prodIds=array()) {
		$sql  = "SELECT url_alias_id, SUBSTRING( url_alias_query, CHAR_LENGTH('products_id=')+1 ) AS product_id ";
		$sql .= "FROM `tbl_url_alias` ";
		$sql .= "WHERE url_alias_query LIKE 'products_id=%'";
		$query = $this->db->query( $sql );
		$res=$this->db->fetch_all($query);		
		$url_alias_ids = array();
		foreach ($res as $row) {
			$url_alias_id = $row['url_alias_id'];
			$product_id = $row['product_id'];
			
			if(!empty($prodIds)){
				if(in_array($product_id,$prodIds)){
					$url_alias_ids[$product_id] = $url_alias_id;
				}
			}else{
				$url_alias_ids[$product_id] = $url_alias_id;
			}		
		}
		return $url_alias_ids;
	}
	
	protected function getAvailableProductIds(&$data) {
		$available_product_ids = array();
		$k = $data->getHighestRow();
		for ($i=1; $i<$k; $i+=1) {
			$j = 1;
			$product_id = trim($this->getCell($data,$i,$j++));
			if ($product_id=="") {
				continue;
			}
			$available_product_ids[$product_id] = $product_id;
		}
		return $available_product_ids;
	}
	
	protected function getAvailableStoreIds() {
		$sql = "SELECT shop_id FROM `tbl_shops`;";
		$query = $this->db->query( $sql );
		$res=$this->db->fetch_all($query);
		$store_ids = array(0);
		foreach ($res as $row) {
			if (!in_array((int)$row['shop_id'],$store_ids)) {
				$store_ids[] = (int)$row['shop_id'];
			}
		}
		return $store_ids;
	}
	
	protected function getDefaultWeightUnit()
	{
		return 'KG';
	}
	
	protected function getDefaultMeasurementUnit() {
		return 'CM';
	}
	
	protected function getWeightClassIds() {
		global $conf_weight_class;
		foreach($conf_weight_class as $k=>$v){
			$weight_class_ids[$v] = $k;
		}
	}
	
	protected function getLengthClassIds() {
		global $conf_length_class;
		foreach($conf_length_class as $k=>$v){
			$length_class_ids[$v] = $k;
		}
	}
	
	
	protected function deleteProducts(&$url_alias_ids ) {
		$sql  = "DELETE FROM `tbl_products` WHERE `prod_type` = '1';\n";
		$sql .= "TRUNCATE TABLE `tbl_prod_details`;\n";
		$sql .= "TRUNCATE TABLE `tbl_product_relations`;\n";
		$sql .= "TRUNCATE TABLE `tbl_product_addons`;\n";
		$sql .= "DELETE FROM `tbl_url_alias` WHERE `url_alias_query` LIKE 'products_id=%';\n";
	
		$this->multiquery( $sql );
		$sql = "SELECT (MAX(url_alias_id)+1) AS next_url_alias_id FROM `tbl_url_alias` LIMIT 1";
		$query = $this->db->query( $sql );
		$res=$this->db->fetch($query);
		
		if(isset($res['next_url_alias_id'])){
			$next_url_alias_id = $res['next_url_alias_id'];
			$sql = "ALTER TABLE `tbl_url_alias` AUTO_INCREMENT = $next_url_alias_id";
			$this->db->query( $sql );
		}
		
		$remove = array();
		foreach ($url_alias_ids as $product_id=>$url_alias_id) {
			if ($url_alias_id >= $next_url_alias_id) {
				$remove[$product_id] = $url_alias_id;
			}
		}
		foreach ($remove as $product_id=>$url_alias_id) {
			unset($url_alias_ids[$product_id]);
		}
	}
	
	
	protected function deleteTagUrlAliases(&$url_alias_ids ,$shop_user_id = 0) {
		
		if(!empty($shop_user_id) && empty($url_alias_ids)){
			return;
		}
		if(empty($shop_user_id)){
			$sql = "DELETE FROM `tbl_url_alias` WHERE `url_alias_query` LIKE 'tags_id=%' ;\n";
		}
		elseif(!empty($url_alias_ids) && count($url_alias_ids)){
			$sql = "";
			foreach($url_alias_ids as $url_alias_id){
				$sql .= "DELETE FROM `tbl_url_alias` WHERE `url_alias_query` LIKE 'tags_id=%' and url_alias_id = $url_alias_id ;\n ";
			}
		}
		if(!empty(trim($sql))){
			$this->multiquery( $sql );
		}
	}
	
	protected function deleteProduct( $product_id ) {
		$sql  = "DELETE FROM `tbl_products` WHERE `prod_id` = '$product_id';\n";
		$sql .= "DELETE FROM `tbl_prod_details` WHERE `prod_id` = '$product_id';\n";
		$sql .= "DELETE FROM `tbl_url_alias` WHERE `url_alias_query` LIKE 'products_id=$product_id';\n";
		$sql .= "DELETE FROM `tbl_product_relations` WHERE `relation_source_id` = '$product_id';\n";				
		$sql .= "DELETE FROM `tbl_product_addons` WHERE `addon_source_id` = '$product_id';\n";				
		$this->multiquery( $sql );
	}
		
	// function for reading additional cells in class extensions
	protected function moreProductCells( $i, &$j, &$worksheet, &$product ) {
		return;
	}
	
	protected function deleteProductImages() {
		$sql = "TRUNCATE TABLE `tbl_product_images`";
		$this->db->query( $sql );
		$sql = "TRUNCATE TABLE `tbl_product_temp_images`";
		$this->db->query( $sql );
	}
		
	protected function deleteAdditionalImage( $product_id ) {
		$sql = "SELECT image_id, image_prod_id, image_file FROM `tbl_product_images` WHERE image_prod_id='".(int)$product_id."'";
		$query = $this->db->query( $sql );
		$res=$this->db->fetch_all($query);
		$old_product_image_ids = array();
		foreach ($res as $row) {
			$product_image_id = $row['image_id'];
			$product_id = $row['image_prod_id'];
			$image_name = $row['image_file'];
			$old_product_image_ids[$product_id][$image_name] = $product_image_id;
		}
		if ($old_product_image_ids) {
			$sql = "DELETE FROM `tbl_product_images` WHERE image_prod_id='".(int)$product_id."'";
			$this->db->query( $sql );
			$sql = "DELETE FROM `tbl_product_temp_images` WHERE image_prod_id='".(int)$product_id."'";
			$this->db->query( $sql );
		}
		return $old_product_image_ids;
	}
	
	protected function deleteUnlistedProductImages( &$unlisted_product_ids ) {
		foreach ($unlisted_product_ids as $product_id) {
			$sql = "DELETE FROM `tbl_product_images` WHERE image_prod_id='".(int)$product_id."'";
			$this->db->query( $sql );
		}
	}
	// function for reading additional cells in class extensions
	protected function moreAdditionalImageCells( $i, &$j, &$worksheet, &$image ) {
		return;
	}
	
	protected function deleteSpecials() {
		$sql = "TRUNCATE TABLE `tbl_product_specials`";
		$this->db->query( $sql );
	}
	protected function deleteSpecial( $product_id ) {
		$sql = "SELECT pspecial_id, pspecial_product_id FROM `tbl_product_specials` WHERE pspecial_product_id='".(int)$product_id."'";
		$query = $this->db->query( $sql );
		$res=$this->db->fetch_all($query);
		$old_product_special_ids = array();			
		foreach ($res as $row) {
			$product_special_id = $row['pspecial_id'];
			$product_id = $row['pspecial_product_id'];			
			$old_product_special_ids[$product_id][] = $product_special_id;
		}
		if ($old_product_special_ids) {
			$sql = "DELETE FROM `tbl_product_specials` WHERE pspecial_product_id='".(int)$product_id."'";
			$this->db->query( $sql );
		}
		return $old_product_special_ids;
	}
	
	protected function deleteUnlistedSpecials( &$unlisted_product_ids ) {
		foreach ($unlisted_product_ids as $product_id) {
			$sql = "DELETE FROM `tbl_product_specials` WHERE pspecial_product_id='".(int)$product_id."'";
			$this->db->query( $sql );
		}
	}
	// function for reading additional cells in class extensions
	protected function moreSpecialCells( $i, &$j, &$worksheet, &$special ) {
		return;
	}
	
	protected function deleteDiscounts() {
		$sql = "TRUNCATE TABLE `tbl_product_discounts`";
		$this->db->query( $sql );
	}
	protected function deleteDiscount( $product_id ) {
		$sql = "SELECT pdiscount_id, pdiscount_product_id, pdiscount_qty FROM `tbl_product_discounts` WHERE pdiscount_product_id='".(int)$product_id."' ORDER BY pdiscount_product_id ASC,pdiscount_qty ASC;";
		$query = $this->db->query( $sql );
		$res=$this->db->fetch_all($query);
		$old_product_discount_ids = array();
		foreach ($res as $row) {
			$product_discount_id = $row['pdiscount_id'];
			$product_id = $row['pdiscount_product_id'];			
			$quantity = $row['pdiscount_qty'];
			$old_product_discount_ids[$product_id][][$quantity] = $product_discount_id;
		}
		if ($old_product_discount_ids) {
			$sql = "DELETE FROM `tbl_product_discounts` WHERE pdiscount_product_id='".(int)$product_id."'";
			$this->db->query( $sql );
		}
		return $old_product_discount_ids;
	}
	protected function deleteUnlistedDiscounts( &$unlisted_product_ids ) {
		foreach ($unlisted_product_ids as $product_id) {
			$sql = "DELETE FROM `tbl_product_discounts` WHERE pdiscount_product_id='".(int)$product_id."'";
			$this->db->query( $sql );
		}
	}
	// function for reading additional cells in class extensions
	protected function moreDiscountCells( $i, &$j, &$worksheet, &$discount ) {
		return;
	}
	
	protected function deleteProductOptions() {
		$sql = "TRUNCATE TABLE `tbl_product_option`";
		$this->db->query( $sql );
	}
	
	protected function deleteProductTags($available_seller_prod_ids=array() , $shop_user_id = 0) {
		
		if(empty($shop_user_id)){
			$sql = "TRUNCATE TABLE `tbl_product_to_tags`";
		}
		else{
			foreach($available_seller_prod_ids as $prodIdInFile => $prodIdSaved){
				$sql .= "delete from `tbl_product_to_tags` where pt_product_id= $prodIdInFile ;\n " ;
			}
		}
		
		$this->multiquery( $sql );
	}
	
	protected function deleteProductOption( $product_id ) {
		$sql = "SELECT product_option_id, product_id, option_id FROM `tbl_product_option` WHERE product_id='".(int)$product_id."'";
		$query = $this->db->query( $sql );
		$res=$this->db->fetch_all($query);
		$old_product_option_ids = array();
		foreach ($res as $row) {
			$product_option_id = $row['product_option_id'];
			$product_id = $row['product_id'];
			$option_id = $row['option_id'];
			$old_product_option_ids[$product_id][$option_id] = $product_option_id;
		}
		if ($old_product_option_ids) {
			$sql = "DELETE FROM `tbl_product_option` WHERE product_id='".(int)$product_id."'";
			$this->db->query( $sql );
		}
		return $old_product_option_ids;
	}
	protected function deleteUnlistedProductOptions( &$unlisted_product_ids ) {
		foreach ($unlisted_product_ids as $product_id) {
			$sql = "DELETE FROM `tbl_product_option` WHERE product_id='".(int)$product_id."'";
			$this->db->query( $sql );
		}
	}
	// function for reading additional cells in class extensions
	protected function moreProductOptionCells( $i, &$j, &$worksheet, &$product_option ) {
		return;
	}
	
	protected function deleteProductOptionValues() {
		$sql = "TRUNCATE TABLE `tbl_product_option_value`";
		$this->db->query( $sql );
	}
	protected function deleteProductOptionValue( $product_id ) {
		$sql = "SELECT product_option_value_id, product_id, option_id, option_value_id FROM `tbl_product_option_value` WHERE product_id='".(int)$product_id."'";
		$query = $this->db->query( $sql );
		$res=$this->db->fetch_all($query);
		$old_product_option_value_ids = array();
		foreach ($res as $row) {
			$product_option_value_id = $row['product_option_value_id'];
			$product_id = $row['product_id'];
			$option_id = $row['option_id'];
			$option_value_id = $row['option_value_id'];
			$old_product_option_value_ids[$product_id][$option_id][$option_value_id] = $product_option_value_id;
		}
		if ($old_product_option_value_ids) {
			$sql = "DELETE FROM `tbl_product_option_value` WHERE product_id='".(int)$product_id."'";
			$this->db->query( $sql );
		}
		return $old_product_option_value_ids;
	}
	protected function deleteUnlistedProductOptionValues( &$unlisted_product_ids ) {
		foreach ($unlisted_product_ids as $product_id) {
			$sql = "DELETE FROM `tbl_product_option_value` WHERE product_id='".(int)$product_id."'";
			$this->db->query( $sql );
		}
	}
	protected function deleteProductAttributes() {
		$sql = "TRUNCATE TABLE `tbl_product_attributes`";
		$this->db->query( $sql );
	}
	protected function deleteProductAttribute( $product_id ) {
		$sql = "DELETE FROM `tbl_product_attributes` WHERE product_id='".(int)$product_id."'";
		$this->db->query( $sql );
	}
	protected function deleteUnlistedProductAttributes( &$unlisted_product_ids ) {
		foreach ($unlisted_product_ids as $product_id) {
			$sql = "DELETE FROM `tbl_product_attributes` WHERE product_id='".(int)$product_id."'";
			$this->db->query( $sql );
		}
	}
	// function for reading additional cells in class extensions
	protected function moreProductAttributeCells( $i, &$j, &$worksheet, &$product_attribute ) {
		return;
	}
	protected function deleteProductFilters() {
		$sql = "TRUNCATE TABLE `tbl_product_filter`";
		$this->db->query( $sql );
	}
	protected function deleteProductFilter( $product_id ) {
		$sql = "DELETE FROM `tbl_product_filter` WHERE product_id='".(int)$product_id."'";
		$this->db->query( $sql );
	}
	protected function deleteUnlistedProductFilters( &$unlisted_product_ids ) {
		foreach ($unlisted_product_ids as $product_id) {
			$sql = "DELETE FROM `tbl_product_filter` WHERE product_id='".(int)$product_id."'";
			$this->db->query( $sql );
		}
	}
	// function for reading additional cells in class extensions
	protected function moreProductFilterCells( $i, &$j, &$worksheet, &$product_filter ) {
		return;
	}
	
	protected function deleteProductShippingRates()
	{
		$sql = "TRUNCATE TABLE `tbl_product_shipping_rates`";
		$this->db->query( $sql );
	}
	
	protected function deleteProductShippingRate( $product_id ) {
		$sql = "DELETE FROM `tbl_product_shipping_rates` WHERE pship_prod_id='".(int)$product_id."'";
		$this->db->query( $sql );
	}
	
	protected function deleteUnlistedProductShippingRates( &$unlisted_product_ids ) {
		foreach ($unlisted_product_ids as $product_id) {
			$sql = "DELETE FROM `tbl_product_shipping_rates` WHERE pship_prod_id='".(int)$product_id."'";
			$this->db->query( $sql );
		}
	}
	
	// function for reading additional cells in class extensions
	protected function moreProductShippingRates( $i, &$j, &$worksheet, &$product_filter ) {
		return;
	}
	
	// function for reading additional cells in class extensions
	protected function moreProductStockSku( $i, &$j, &$worksheet, &$prodStockSku ) {
		return;
	}
	
	// function for reading additional cells in class extensions
	protected function moreProductOptionValueCells( $i, &$j, &$worksheet, &$product_option_value ) {
		return;
	}
	
	
	protected function deleteOptions() {
		$sql = "TRUNCATE TABLE `tbl_options`";
		$this->db->query( $sql );		
	}
	protected function deleteOption( $option_id ) {
		$sql = "DELETE FROM `tbl_options` WHERE option_id='".(int)$option_id."'";
		$this->db->query( $sql );		
	}
	
	// function for reading additional cells in class extensions
	protected function moreOptionCells( $i, &$j, &$worksheet, &$option ) {
		return;
	}
	
	protected function deleteOptionValues() {
		$sql = "TRUNCATE TABLE `tbl_option_values`";
		$this->db->query( $sql );		
	}
	protected function deleteOptionValue( $option_value_id ) {
		$sql = "DELETE FROM `tbl_option_values` WHERE option_value_id='".(int)$option_value_id."'";
		$this->db->query( $sql );		
	}
	
	// function for reading additional cells in class extensions
	protected function moreOptionValueCells( $i, &$j, &$worksheet, &$option ) {
		return;
	}
	
	protected function deleteAttributeGroups() {
		$sql = "TRUNCATE TABLE `tbl_attribute_groups`";
		$this->db->query( $sql );		
	}
	
	protected function deleteAttributeGroup( $attribute_group_id ) {
		$sql = "DELETE FROM `tbl_attribute_groups` WHERE attribute_group_id='".(int)$attribute_group_id."'";
		$this->db->query( $sql );		
	}
	
	// function for reading additional cells in class extensions
	protected function moreAttributeGroupCells( $i, &$j, &$worksheet, &$attribute_group ) {
		return;
	}
	
	protected function deleteAttributes() {
		$sql = "TRUNCATE TABLE `tbl_attributes`";
		$this->db->query( $sql );		
	}
	
	// function for reading additional cells in class extensions
	protected function moreAttributeCells( $i, &$j, &$worksheet, &$option ) {
		return;
	}
	protected function deleteAttribute( $attribute_id ) {
		$sql = "DELETE FROM `tbl_attributes` WHERE attribute_id='".(int)$attribute_id."'";
		$this->db->query( $sql );
	}
	
	protected function deleteFilterGroups() {
		$sql = "TRUNCATE TABLE `tbl_filter_groups`";
		$this->db->query( $sql );		
	}
		
	protected function deleteFilterGroup( $filter_group_id ) {
		$sql = "DELETE FROM `tbl_filter_groups` WHERE filter_group_id='".(int)$filter_group_id."'";
		$this->db->query( $sql );		
	}
	
	// function for reading additional cells in class extensions
	protected function moreFilterGroupCells( $i, &$j, &$worksheet, &$filter_group ) {
		return;
	}
	
	protected function deleteFilters() {
		$sql = "TRUNCATE TABLE `tbl_filters`";
		$this->db->query( $sql );		
	}
		
	protected function deleteFilter( $filter_id ) {
		$sql = "DELETE FROM `tbl_filters` WHERE filter_id='".(int)$filter_id."'";
		$this->db->query( $sql );		
	}
	
	// function for reading additional cells in class extensions
	protected function moreFilterCells( $i, &$j, &$worksheet, &$option ) {
		return;
	}
	
	protected function deleteShippingDurations() {
		$sql = "TRUNCATE TABLE `tbl_shipping_durations`";
		$this->db->query( $sql );		
	}
	
	protected function deleteShippingDuration( $duration_id ) {
		$sql = "DELETE FROM `tbl_shipping_durations` WHERE sduration_id='".(int)$duration_id."'";
		$this->db->query( $sql );		
	}
	
	// function for reading additional cells in class extensions
	protected function moreShippingDurationsCells( $i, &$j, &$worksheet, &$option ) {
		return;
	}
	
	protected function deleteShippingCompanies() {
		$sql = "TRUNCATE TABLE `tbl_shipping_companies`";
		$this->db->query( $sql );		
	}
	
	protected function deleteShippingCompany( $company_id ) {
		$sql = "DELETE FROM `tbl_shipping_companies` WHERE scompany_id='".(int)$company_id."'";
		$this->db->query( $sql );		
	}
	
	// function for reading additional cells in class extensions
	protected function moreShippingCompaniesCells( $i, &$j, &$worksheet, &$option ) {
		return;
	}
	
	protected function slugify($url_keyword='')
	{
		$oldSlug='';	
		$slug=Utilities::slugify($url_keyword);		
		$url_alias=new Url_alias();
		$res=$url_alias->getUrlAliasByKeyword($slug);		
		if(empty($res)){$oldSlug=$slug;}			
			if (($slug != $oldSlug) && (!empty($url_keyword))){  
				$i = 1; $baseSlug = $slug;
				while($url_alias->getUrlAliasByKeyword($slug)){                
					$slug = $baseSlug . "-" . $i++;     
					if($slug == $oldSlug){              
						break;                          
				   }
				}
			}			
		return $slug;
	}
	
	protected function storeCategoryIntoDatabase( &$category,&$available_store_ids, &$url_alias_ids ) 	{
		
		$url_keyword=trim($category['seo_keyword']);
		$category_id=$category['category_id'];
		
		$assign_fields = array();			
		$assign_fields['category_id'] = $category_id;
		$assign_fields['category_type'] = 1;
		$assign_fields['category_parent'] = $category['category_parent'];
		$assign_fields['category_name'] = $category['category_name'];
		$assign_fields['category_slug'] = Utilities::slugify($category['category_name']);
		$assign_fields['category_description'] = $category['category_description'];
		/* $assign_fields['category_featured'] = $category['category_featured']; */
		$assign_fields['category_file'] = $category['category_file'];
		$assign_fields['category_meta_title'] = $category['category_meta_title'];		
		$assign_fields['category_meta_keywords'] = $category['category_meta_keywords'];
		$assign_fields['category_meta_description'] = $category['category_meta_description'];
		$assign_fields['category_display_order'] = $category['category_display_order'];
		$assign_fields['category_status'] = $category['category_status'];
		$assign_fields['category_is_deleted'] = $category['category_is_deleted'];
		
		$this->db->insert_from_array('tbl_categories',$assign_fields,false,false,$assign_fields);
		
		if ($url_keyword) {
			$record = new TableRecord('tbl_url_alias');
			if (isset($url_alias_ids[$category_id])) {
				$url_alias_id = $url_alias_ids[$category_id];				
				if(!$this->db->insert_from_array('tbl_url_alias', array('url_alias_id'=>$url_alias_id,'url_alias_query'=>'category_id='.$category_id,'url_alias_keyword'=>$url_keyword),false,array('IGNORE'))){
					$err=$this->db->getError();
					$this->addLog(array('message'=>$err));					
				}				
				unset($url_alias_ids[$category_id]);
			} else {				
				if(!$this->db->insert_from_array('tbl_url_alias', array('url_alias_query'=>'category_id='.$category_id,'url_alias_keyword'=>$url_keyword),false,array('IGNORE'))){
					$err=$this->db->getError();
					$this->addLog(array('message'=>$err));					
				}	
			}			
		}		
	}	
	protected function storeProductIntoDatabase( &$product, &$product_fields, &$available_store_ids, &$weight_class_ids, &$length_class_ids, &$url_alias_ids,&$userProductIds,&$shop_user_id,&$available_seller_prod_ids ) {
		
		$shop_user_id=intVal($shop_user_id);
				
		$product_id=$product['prod_id'];		
		$assignField= array();
		$autoSetProductId=false;
		
		if($shop_user_id>0){
			if(isset($available_seller_prod_ids[$product_id]) && strtoupper($available_seller_prod_ids[$product_id])!='AUTO_INCREMENT')
			{	
				$autoSetProductId=false;
				$assignField['prod_id'] = $product_id;
			}else{
				$autoSetProductId=true;
			}
		}else{
			$autoSetProductId=false;
			$assignField['prod_id'] = $product_id;
		}
		
		$assignField['prod_added_by'] = $product['prod_added_by'];
		$assignField['prod_category'] = $product['prod_category'];			
		$assignField['prod_type'] = 1;			
		$assignField['prod_sku'] = $product['prod_sku'];
		$assignField['prod_name'] = trim($product['prod_name']);
		$assignField['prod_slug'] = Utilities::slugify($product['prod_name']);
		$assignField['prod_brand'] = $product['prod_brand'];
		$assignField['prod_model'] = $product['prod_model'];
		$assignField['prod_shop'] = $product['prod_shop'];
		$assignField['prod_sale_price'] = $product['prod_sale_price'];
		/* $assignField['prod_shipping'] = $product['prod_shipping']; */
		$assignField['prod_stock'] = $product['prod_stock'];
		$assignField['prod_shipping_country'] = $product['prod_shipping_country'];
		$assignField['prod_min_order_qty'] = $product['prod_min_order_qty'];
		$assignField['prod_subtract_stock'] = $product['prod_subtract_stock'];
		$assignField['prod_requires_shipping'] = $product['prod_requires_shipping']; 
		$assignField['prod_track_inventory'] = $product['prod_track_inventory'];
		$assignField['prod_threshold_stock_level'] = $product['prod_threshold_stock_level'];
		$assignField['prod_view_count'] = isset($view_counts[$product['prod_id']]) ? $view_counts[$product['prod_id']] : 0;
		$assignField['prod_sold_count'] = isset($sold_counts[$product['prod_id']]) ? $sold_counts[$product['prod_id']] : 0;
		$assignField['prod_condition'] = $product['prod_condition'];
		$assignField['prod_added_on'] = $product['prod_added_on'];
		/* $assignField['prod_published_on'] = $product['prod_published_on']; */
		$assignField['prod_status'] = $product['prod_status'];
		$assignField['prod_is_deleted'] = $product['prod_is_deleted'];
		$assignField['prod_display_order'] = $product['prod_display_order'];
		
		
		$url_keyword = $product['seo_keyword'];		
		$productDetails['prod_id'] = $product_id;
		$productDetails['prod_length'] = $product['prod_length'];
		$productDetails['prod_length_class'] = $product['prod_length_class'];
		$productDetails['prod_width'] = $product['prod_width'];
		$productDetails['prod_height'] = $product['prod_height'];
		$productDetails['prod_weight'] = $product['prod_weight'];
		$productDetails['prod_weight_class'] = $product['prod_weight_class'];
		$productDetails['prod_tags'] = $product['prod_tags'];
		$productDetails['prod_youtube_video'] = $product['prod_youtube_video'];
		$productDetails['prod_short_desc'] = $product['prod_short_desc'];
		$productDetails['prod_long_desc'] = $product['prod_long_desc'];
		$productDetails['prod_meta_title'] = $product['prod_meta_title'];
		$productDetails['prod_meta_keywords'] = $product['prod_meta_keywords'];
		$productDetails['prod_meta_description'] = $product['prod_meta_description'];
		/* $productDetails['prod_featuered'] = (strtolower($product['prod_featuered'])=='yes')?1:0; */
		$productDetails['prod_ship_free'] = $product['prod_ship_free'];
		/* $productDetails['prod_tax_free'] = (strtolower($product['prod_tax_free'])=='yes')?1:0; */
		$productDetails['prod_available_date'] = $product['prod_available_date'];
		
		$related_ids = $product['related_products'];
		$addon_ids = $product['product_addons'];
		
				
		if($this->db->insert_from_array('tbl_products',$assignField,false,false,$assignField)){	
			if($autoSetProductId==true){
				$product_id=$this->db->insert_id();
				$productDetails['prod_id'] = $product_id;
				$available_seller_prod_ids[$product['prod_id_in_file']]=$product_id;
			}
			
			$this->db->insert_from_array('tbl_prod_details',$productDetails,false,false,$productDetails);
			if($url_keyword){ 
				if (isset($url_alias_ids[$product_id])) {
					$url_alias_id = $url_alias_ids[$product_id];
						if($url_alias_id>0 && trim($url_keyword)!=''){
							$urlAliasArr=array(
								'url_alias_id'=>$url_alias_id,
								'url_alias_query'=>'products_id='.$product_id,
								'url_alias_keyword'=>$url_keyword,
							);	
						$this->db->insert_from_array('tbl_url_alias',$urlAliasArr,false,false,$urlAliasArr);	
						}	
					unset($url_alias_ids[$product_id]);
				} else {
					if(trim($url_keyword)!=''){
						$urlAliasArr=array(							
							'url_alias_query'=>'products_id='.$product_id,
							'url_alias_keyword'=>$url_keyword,
						);	
						$this->db->insert_from_array('tbl_url_alias',$urlAliasArr,false,false,$urlAliasArr);			
					}							
				}				
			}			
		}
		
		
		if (count($related_ids) > 0) {
			$sql = "INSERT INTO `tbl_product_relations` (`relation_source_id`,`relation_to_id`) VALUES ";
			$first = true;
			foreach ($related_ids as $related_id) { if(trim($related_id)==''){continue;}
				$sql .= ($first) ? "\n" : ",\n";
				$first = false;
				$sql .= "($product_id,$related_id)";
			}
			$sql .= ";";				
			$this->db->query($sql);
		}
		
		if (count($addon_ids) > 0) {
			$sql = "INSERT INTO `tbl_product_addons` (`addon_source_id`,`addon_to_id`) VALUES ";
			$first = true;
			foreach ($addon_ids as $addon_id) { if(trim($addon_id)==''){continue;}
				$sql .= ($first) ? "\n" : ",\n";
				$first = false;
				$sql .= "($product_id,$addon_id)";
			}
			$sql .= ";";				
			$this->db->query($sql);
		}		
	}
	protected function storeAdditionalImageIntoDatabase( &$image, &$old_product_image_ids,$saveToTempTable=false ) { 
		$imgTable=($saveToTempTable==false)?'tbl_product_images':'tbl_product_temp_images';
		$product_id = $image['image_prod_id'];
		$image_name = $image['image_file'];
		$image_default = $image['image_default'];
	
		if (isset($old_product_image_ids[$product_id][$image_name])) {
			$product_image_id = $old_product_image_ids[$product_id][$image_name];
				$imgArr=array(
					'old_image_id'=>$product_image_id,
					'image_prod_id'=>$product_id,
					'image_file'=>$image_name,
					'image_default'=>$image_default,
				);
				if($saveToTempTable==true){$imgArr['image_downloaded']=0;}				
			$this->db->insert_from_array($imgTable,$imgArr,false,false,$imgArr);	
			unset($old_product_image_ids[$product_id][$image_name]);
		} else {
			$imgArr=array(					
					'image_prod_id'=>$product_id,
					'image_file'=>$image_name,
					'image_default'=>$image_default,
				);
				if($saveToTempTable==true){$imgArr['image_downloaded']=0;}				
			$this->db->insert_from_array($imgTable,$imgArr,false,false,$imgArr);			
		}		
	}
	protected function storeSpecialIntoDatabase( &$special, &$old_product_special_ids ) {
		$product_id = $special['pspecial_product_id'];
		$priority = $special['pspecial_priority'];
		$price = $special['pspecial_price'];
		$date_start = $special['pspecial_start_date'];
		$date_end = $special['pspecial_end_date'];
		
		$first_key = key($old_product_special_ids[$product_id]);
		if (isset($old_product_special_ids[$product_id][$first_key])) {
				$product_special_id = $old_product_special_ids[$product_id][$first_key];	
				$assignValues=array(
					'pspecial_id'=>$product_special_id,
					'pspecial_product_id'=>$product_id,
					'pspecial_priority'=>$priority,
					'pspecial_price'=>$price,
					'pspecial_start_date'=>$date_start,
					'pspecial_end_date'=>$date_end,
				);
			$this->db->insert_from_array('tbl_product_specials',$assignValues,false,false,$assignValues);				
			unset($old_product_special_ids[$product_id][$first_key]);
		} else {
			$assignValues=array(					
					'pspecial_product_id'=>$product_id,
					'pspecial_priority'=>$priority,
					'pspecial_price'=>$price,
					'pspecial_start_date'=>$date_start,
					'pspecial_end_date'=>$date_end,
				);
			$this->db->insert_from_array('tbl_product_specials',$assignValues,false,false,$assignValues);
		}
	}
	protected function storeDiscountIntoDatabase( &$discount, &$old_product_discount_ids, &$customer_group_ids ) {
		$product_id = $discount['pdiscount_product_id'];
		$quantity = $discount['pdiscount_qty'];
		$priority = $discount['pdiscount_priority'];
		$price = $discount['pdiscount_price'];
		$date_start = $discount['pdiscount_start_date'];
		$date_end = $discount['pdiscount_end_date'];
		
		$first_key = key($old_product_discount_ids[$product_id]);
		
		if (isset($old_product_discount_ids[$product_id][$first_key][$quantity])) {
			$product_discount_id = $old_product_discount_ids[$product_id][$first_key][$quantity];
			$assignValues=array(
					'pdiscount_id'=>$product_discount_id,
					'pdiscount_product_id'=>$product_id,
					'pdiscount_priority'=>$priority,
					'pdiscount_qty'=>$quantity,
					'pdiscount_price'=>$price,
					'pdiscount_start_date'=>$date_start,
					'pdiscount_end_date'=>$date_end,
				);
			$this->db->insert_from_array('tbl_product_discounts',$assignValues,false,false,$assignValues);
			unset($old_product_discount_ids[$product_id][$first_key][$quantity]);
		} else {
			$assignValues=array(					
					'pdiscount_product_id'=>$product_id,
					'pdiscount_priority'=>$priority,
					'pdiscount_qty'=>$quantity,
					'pdiscount_price'=>$price,
					'pdiscount_start_date'=>$date_start,
					'pdiscount_end_date'=>$date_end,
				);
			$this->db->insert_from_array('tbl_product_discounts',$assignValues,false,false,$assignValues);
		}
	}
	protected function storeProductOptionIntoDatabase( &$product_option, &$old_product_option_ids ) {				
		// DB query for storing the product option
		$product_id = $product_option['product_id'];
		$option_id = $product_option['option_id'];
		$option_value = $product_option['value'];
		$required = $product_option['required'];		
		if (isset($old_product_option_ids[$product_id][$option_id])) {
			$product_option_id = $old_product_option_ids[$product_id][$option_id];
			$assignValues=array(
					'product_option_id'=>$product_option_id,
					'product_id'=>$product_id,
					'option_id'=>$option_id,
					'value'=>$option_value,
					'required'=>$required					
				);				
			$this->db->insert_from_array('tbl_product_option',$assignValues,false,false,$assignValues);			
			unset($old_product_option_ids[$product_id][$option_id]);
		} else {
			$assignValues=array(					
					'product_id'=>$product_id,
					'option_id'=>$option_id,
					'value'=>$option_value,
					'required'=>$required					
				);							
			$this->db->insert_from_array('tbl_product_option',$assignValues,false,false,$assignValues);		
		}		
	}
	protected function storeProductTagIntoDatabase( &$product_tag ) {
		// DB query for storing the product tag
		$product_id = $product_tag['product_id'];
		$tag_id = $product_tag['tag_id'];
		
		$assignValues=array(
				'pt_tag_id'=>$tag_id,
				'pt_product_id'=>$product_id,
			);				
		$this->db->insert_from_array('tbl_product_to_tags',$assignValues,false,false,$assignValues);
	}
	protected function storeProductOptionValueIntoDatabase( &$product_option_value, &$old_product_option_value_ids ) {
		$product_id = $product_option_value['product_id'];
		$option_id = $product_option_value['option_id'];
		$option_value_id = $product_option_value['option_value_id'];
		$quantity = $product_option_value['quantity'];
		$subtract = $product_option_value['subtract'];		
		$price = $product_option_value['price'];
		$price_prefix = $product_option_value['price_prefix'];
		$weight = $product_option_value['weight'];
		$weight_prefix = $product_option_value['weight_prefix'];
		$product_option_id = $product_option_value['product_option_id'];
		if (isset($old_product_option_value_ids[$product_id][$option_id][$option_value_id])) {
			$product_option_value_id = $old_product_option_value_ids[$product_id][$option_id][$option_value_id];
			
			$assignValues=array(					
					'product_option_value_id'=>$product_option_value_id,
					'product_option_id'=>$product_option_id,
					'product_id'=>$product_id,
					'option_id'=>$option_id,					
					'option_value_id'=>$option_value_id,					
					'quantity'=>$quantity,					
					'subtract'=>$subtract,					
					'price'=>$price,					
					'price_prefix'=>$price_prefix,					
					'weight'=>$weight,					
					'weight_prefix'=>$weight_prefix,					
				);
			$this->db->insert_from_array('tbl_product_option_value',$assignValues,false,false,$assignValues);							
			unset($old_product_option_value_ids[$product_id][$option_id][$option_value_id]);
		} else {		
			$assignValues=array(									
					'product_option_id'=>$product_option_id,
					'product_id'=>$product_id,
					'option_id'=>$option_id,					
					'option_value_id'=>$option_value_id,					
					'quantity'=>$quantity,					
					'subtract'=>$subtract,					
					'price'=>$price,					
					'price_prefix'=>$price_prefix,					
					'weight'=>$weight,					
					'weight_prefix'=>$weight_prefix,					
				);		
			$this->db->insert_from_array('tbl_product_option_value',$assignValues,false,false,$assignValues);		
		}
	}	
	
	protected function storeProductAttributeIntoDatabase( &$product_attribute ) {
		$product_id = $product_attribute['product_id'];	
		$attribute_id = $product_attribute['attribute_id'];
		$texts = $product_attribute['texts'];
		
		$assignValues=array(									
					'product_id'=>$product_id,
					'attribute_id'=>$attribute_id,
					'attribute_text'=>$texts					
				);		
		$this->db->insert_from_array('tbl_product_attributes',$assignValues,false,false,$assignValues);			
	}
	
	protected function storeProductFilterIntoDatabase( &$product_filter ) {
		$product_id = $product_filter['product_id'];
		$filter_id = $product_filter['filter_id'];
		
		$assignValues=array(									
					'product_id'=>$product_id,
					'filter_id'=>$filter_id							
				);		
		$this->db->insert_from_array('tbl_product_filter',$assignValues,false,false,$assignValues);		
	}
	
	protected function storeProductShippingRateIntoDatabase(&$shipping_rates)
	{
		$assignValues=array(									
					'pship_prod_id'=>$shipping_rates['product_id'],
					'pship_country'=>$shipping_rates['country_id'],							
					'pship_company'=>$shipping_rates['company_id'],							
					'pship_duration'=>$shipping_rates['duration_id'],							
					'pship_charges'=>$shipping_rates['charges'],							
					'pship_additional_charges'=>$shipping_rates['additional_charges'],							
				);
		$this->db->insert_from_array('tbl_product_shipping_rates',$assignValues,false,false,$assignValues);	
	}
	
	protected function updateProductStockSkuIntoDatabase(&$prodStockAndQtyArr)
	{
		$assignValues=array(									
					'prod_name'=>$prodStockAndQtyArr['prod_name'],
					'prod_stock'=>$prodStockAndQtyArr['prod_stock'],
					'prod_sku'=>$prodStockAndQtyArr['prod_sku'],											
					'prod_sale_price'=>$prodStockAndQtyArr['prod_sale_price']										
				);			
		$whr=array('smt' => 'prod_id = ? and prod_added_by = ?', 'vals' => array($prodStockAndQtyArr['prod_id'],$prodStockAndQtyArr['prod_added_by']));		
		$this->db->update_from_array('tbl_products',$assignValues,$whr);	
	}
	
	protected function storeOptionIntoDatabase( &$option) {
		$assign_fields=array(
			'option_id'=>$option['option_id'],
			'option_type'=>$option['type'],
			'option_name'=>$option['names'],
			'option_display_order'=>$option['display_order'],
			'option_is_deleted'=>$option['is_deleted']
		);		
		$this->db->insert_from_array('tbl_options',$assign_fields,false,false,$assign_fields);			
	}
	
	protected function storeTagIntoDatabase( &$tag ,$available_store_ids,&$url_alias_ids) {
		$tag_id =$tag['ptag_id'];
		$assign_fields=array(
			'ptag_id'=>$tag_id,
			'ptag_name'=>$tag['ptag_name'],
			'ptag_owner'=>$tag['ptag_owner'],
			'ptag_added_by'=>$tag['ptag_added_by'],
		);
		$this->db->insert_from_array('tbl_product_tags',$assign_fields,false,false,$assign_fields);
		if($url_keyword = $tag['tag_url_keyword']){
			if (isset($url_alias_ids[$tag_id])) {
				$url_alias_id = $url_alias_ids[$tag_id];
				if(!$this->db->insert_from_array('tbl_url_alias', array('url_alias_id'=>$url_alias_id,'url_alias_query'=>'tags_id='.$tag_id,'url_alias_keyword'=>$url_keyword),false,array('IGNORE'))){
					$err=$this->db->getError();
					$this->addLog(array('message'=>$err));
				}
				unset($url_alias_ids[$category_id]);
			} else {
				if(!$this->db->insert_from_array('tbl_url_alias', array('url_alias_query'=>'tags_id='.$tag_id,'url_alias_keyword'=>$url_keyword),false,array('IGNORE'))){
					$err=$this->db->getError();
					$this->addLog(array('message'=>$err));
				}
			}
		}
	}
	
	protected function storeOptionValueIntoDatabase( &$option_value, $exist_image=true ) {
		$assign_fields=array(
			'option_value_id'=>$option_value['option_value_id'],
			'option_id'=>$option_value['option_id'],
			'option_value_display_order'=>$option_value['display_order']
		);
		if ($exist_image) {
			$assign_fields['option_value_name'] = $option_value['image'];
		}else{
			$assign_fields['option_value_name'] =$option_value['names'];
		}
		$this->db->insert_from_array('tbl_option_values',$assign_fields,false,false,$assign_fields);				
	}
	
	protected function storeAttributeGroupIntoDatabase( &$attribute_group ) {
		$assign_fields=array(
			'attribute_group_id'=>$attribute_group['attribute_group_id'],
			'attribute_group_display_order'=>$attribute_group['display_order'],
			'attribute_group_name'=>$attribute_group['names'],
			'attribute_group_is_deleted'=>$attribute_group['is_deleted'],
		);		
		$this->db->insert_from_array('tbl_attribute_groups',$assign_fields,false,false,$assign_fields);	
	}
	
	protected function storeAttributeIntoDatabase( &$attribute ) {
		$assign_fields=array(
			'attribute_id'=>$attribute['attribute_id'],
			'attribute_group'=>$attribute['attribute_group_id'],
			'attribute_display_order'=>$attribute['display_order'],
			'attribute_name'=>$attribute['names'],
			'attribute_is_deleted'=>$attribute['is_deleted'],
		);		
		$this->db->insert_from_array('tbl_attributes',$assign_fields,false,array('IGNORE'));		
	}
	
	protected function storeFilterGroupIntoDatabase( &$filter_group ) {		
		$assign_fields=array(
			'filter_group_id'=>$filter_group['filter_group_id'],
			'filter_group_name'=>$filter_group['names'],
			'filter_group_display_order'=>$filter_group['display_order'],
			'filter_group_is_deleted'=>$filter_group['is_deleted'],
		);
		
		$this->db->insert_from_array('tbl_filter_groups',$assign_fields,false,array('IGNORE'));		
	}
	
	protected function storeFilterIntoDatabase( &$filter ) {
		$assign_fields=array(
			'filter_id'=>$filter['filter_id'],
			'filter_group'=>$filter['filter_group_id'],
			'filter_display_order'=>$filter['display_order'],
			'filter_name'=>$filter['names'],
			'filter_is_deleted'=>$filter['is_deleted'],
		);
		$this->db->insert_from_array('tbl_filters',$assign_fields,false,array('IGNORE'));				
	}	
	protected function storeShippingDurationsIntoDatabase( &$durations ) {
		$assign_fields=array(
			'sduration_id'=>$durations['sduration_id'],
			'sduration_label'=>$durations['sduration_label'],
			'sduration_from'=>$durations['sduration_from'],
			'sduration_to'=>$durations['sduration_to'],
			'sduration_days_or_weeks'=>$durations['sduration_days_or_weeks'],
			'sduration_is_deleted'=>$durations['sduration_is_deleted'],
		);
		$this->db->insert_from_array('tbl_shipping_durations',$assign_fields,false,array('IGNORE'));				
	}
	
	protected function storeShippingCompaniesIntoDatabase( &$companies ) {
		$assign_fields=array(
			'scompany_id'=>$companies['company_id'],
			'scompany_name'=>$companies['name'],
			'scompany_website'=>$companies['website'],
			'scompany_comments'=>$companies['comments'],			
			'scompany_is_deleted'=>$companies['is_deleted'],
		);
		$this->db->insert_from_array('tbl_shipping_companies',$assign_fields,false,array('IGNORE'));				
	}	
	
	protected function uploadCategories( &$reader, $incremental, &$available_category_ids=array() ) {
		// get worksheet if there
		$data = $reader->getSheetByName( 'Categories' );
		if ($data==null) {
			return;
		}
		
		// get old url_alias_ids
		$url_alias_ids = $this->getCategoryUrlAliasIds();
		// if incremental then find current category IDs else delete all old categories
		$available_category_ids = array();
		if ($incremental) {
			$available_category_ids = $this->getAvailableCategoryIds();
		} else {
			$this->deleteCategories($url_alias_ids);
		}
		
		$first_row = array();
		$i = 0;
		$k = $data->getHighestRow();
		for ($i=0; $i<$k; $i+=1) {
			if ($i==0) {
				$max_col = PHPExcel_Cell::columnIndexFromString( $data->getHighestColumn() );
				for ($j=1; $j<=$max_col; $j+=1) {
					$first_row[] = $this->getCell($data,$i,$j);
				}
				continue;
			}
			$j = 1;
			$category_id = trim($this->getCell($data,$i,$j++));
			
			if ($category_id=="") {
				continue;
			}
			
			$category_parent = trim($this->getCell($data,$i,$j++));				
			$name = $this->getCell($data,$i,$j++);
			//$category_name = htmlspecialchars( $name );
			$category_name = trim($name);
			if(trim($category_name)==''){continue;}
			
			$description = $this->getCell($data,$i,$j++);
			$category_description = htmlspecialchars( $description );
			$category_file=trim($this->getCell($data,$i,$j++));			
			$category_meta_title=htmlspecialchars( $this->getCell($data,$i,$j++) );
			$category_meta_keywords=htmlspecialchars( $this->getCell($data,$i,$j++) );
			$category_meta_description=htmlspecialchars( $this->getCell($data,$i,$j++) );
			
			$category_display_order=$this->getCell($data,$i,$j++,'0');
			
			$url_keyword=$this->getCell($data,$i,$j++);
			$url_keyword=(trim($url_keyword)!='')?$url_keyword:$category_name;
			$url_keyword=$this->slugify($url_keyword);
						
			$category_status=$this->getCell($data,$i,$j++,'true');
			$category_is_deleted=$this->getCell($data,$i,$j++,'true');
			
			$category = array();
			$category['category_id'] = $category_id;
			$category['category_type'] = 1;
			$category['category_parent'] = $category_parent;
			$category['category_name'] = $category_name;
			$category['category_description'] = $category_description;			
			$category['category_file'] = $category_file;
			$category['category_meta_title'] = $category_meta_title;
			$category['category_meta_keywords'] = $category_meta_keywords;
			$category['category_meta_description'] = $category_meta_description;
			$category['category_display_order'] = $category_display_order;
			$category['seo_keyword'] = $url_keyword;
			$category['category_status'] = ((strtoupper($category_status)=="TRUE") || (strtoupper($category_status)=="YES") || (strtoupper($category_status)=="ENABLED")|| (strtoupper($category_status)=="ENABLE"))?1:0;
			$category['category_is_deleted'] = ((strtoupper($category_is_deleted)=="TRUE") || (strtoupper($category_is_deleted)=="YES") || (strtoupper($category_is_deleted)=="ENABLED")|| (strtoupper($category_is_deleted)=="ENABLE"))?1:0;
			
			if ($incremental) {
				if ($available_category_ids) {
					if (in_array((int)$category_id,$available_category_ids)) {
						$this->deleteCategory( $category_id );
					}
				}
			}
			
			$this->moreCategoryCells( $i, $j, $data, $category );
			$this->storeCategoryIntoDatabase( $category, $available_store_ids, $url_alias_ids );
			
		}
	}	
	
	protected function uploadCategoryFilters( &$reader, $incremental, &$available_category_ids ) {
		// get worksheet, if not there return immediately
		$data = $reader->getSheetByName( 'CategoryFilters' );
		if ($data==null) {
			return;
		}
		
		// if incremental then find current category IDs else delete all old category filters
		if ($incremental) {
			$unlisted_category_ids = $available_category_ids;
		} else {
			$this->deleteCategoryFilters();
		}
		if (!$this->exportImportSettings['export_import_settings_use_filter_group_id']) {
			$filter_group_ids = $this->getFilterGroupIds();
		}
		if (!$this->exportImportSettings['export_import_settings_use_filter_id']) {
			$filter_ids = $this->getFilterIds();
		}
		// load the worksheet cells and store them to the database		
		$previous_category_id = 0;
		$first_row = array();
		$i = 0;
		$k = $data->getHighestRow();
		for ($i=0; $i<$k; $i+=1) {
			if ($i==0) {
				$max_col = PHPExcel_Cell::columnIndexFromString( $data->getHighestColumn() );
				for ($j=1; $j<=$max_col; $j+=1) {
					$first_row[] = $this->getCell($data,$i,$j);
				}
				continue;
			}
			$j = 1;
			$category_id = trim($this->getCell($data,$i,$j++));
			if ($category_id=='') {
				continue;
			}
			if ($this->exportImportSettings['export_import_settings_use_filter_group_id']) {
				$filter_group_id = $this->getCell($data,$i,$j++,'');
			} else {
				$filter_group_name = trim($this->getCell($data,$i,$j++));
				$filter_group_id = isset($filter_group_ids[strtolower($filter_group_name)]) ? $filter_group_ids[strtolower($filter_group_name)] : '';
			}
			if ($filter_group_id=='') {
				continue;
			}
			if ($this->exportImportSettings['export_import_settings_use_filter_id']) {
				$filter_id = $this->getCell($data,$i,$j++,'');
			} else {
				$filter_name = trim($this->getCell($data,$i,$j++));
				$filter_id = isset($filter_ids[$filter_group_id][strtolower($filter_name)]) ? $filter_ids[$filter_group_id][strtolower($filter_name)] : '';
			}
			if ($filter_id=='') {
				continue;
			}
			$category_filter = array();
			$category_filter['category_id'] = $category_id;
			$category_filter['filter_group_id'] = $filter_group_id;
			$category_filter['filter_id'] = $filter_id;
			if (($incremental) && ($category_id != $previous_category_id)) {
				//$this->deleteCategoryFilter( $category_id );
				if (isset($unlisted_category_ids[$category_id])) {
					unset($unlisted_category_ids[$category_id]);
				}
			}
			$this->moreCategoryFilterCells( $i, $j, $data, $category_filter );
			$this->storeCategoryFilterIntoDatabase( $category_filter);
			$previous_category_id = $category_id;
		}
		if ($incremental) {
			$this->deleteUnlistedCategoryFilters( $unlisted_category_ids );
		}
	}
	
	protected function uploadCategoryCollections(&$reader, $incremental, &$available_category_ids)
	{
		// get worksheet, if not there return immediately
		$data = $reader->getSheetByName( 'CategoryCollections' );
		if ($data==null) {
			return;
		}
		
		if (!$this->exportImportSettings['export_import_settings_use_collection_id']) {
			$collections = $this->getCollectionIds('C'); 
		}
		
		// if incremental then find current category IDs else delete all old category collections
		if ($incremental) {
			$unlisted_category_ids = $available_category_ids;
		} else {
			$this->deleteCategoryCollections();
		}
		
		// load the worksheet cells and store them to the database		
		$previous_category_id = 0;
		$first_row = array();
		$i = 0;
		$k = $data->getHighestRow();
		for ($i=0; $i<$k; $i+=1) {
			if ($i==0) {
				$max_col = PHPExcel_Cell::columnIndexFromString( $data->getHighestColumn() );
				for ($j=1; $j<=$max_col; $j+=1) {
					$first_row[] = $this->getCell($data,$i,$j);
				}
				continue;
			}
			$j = 1;
			if ($this->exportImportSettings['export_import_settings_use_collection_id']) {
				$collection_id = $this->getCell($data,$i,$j++,'');
			} else {
				$collection_name = trim($this->getCell($data,$i,$j++));
				$collection_id = isset($collections[strtolower($collection_name)]) ? $collections[strtolower($collection_name)] : '';
			}
			
			if ($collection_id=='') {
				continue;
			}
			
			$category_id = trim($this->getCell($data,$i,$j++));
			if ($category_id=='') {
				continue;
			}
			
			$display_order = $this->getCell($data,$i,$j++,'');
			
			$category_collection = array();
			$category_collection['collection_id'] = $collection_id;
			$category_collection['category_id'] = $category_id;
			$category_collection['display_order'] = $display_order;
			
			if (($incremental) && ($category_id != $previous_category_id)) {
				//$this->deleteCategoryCollection( $category_id );
				if (isset($unlisted_category_ids[$category_id])) {
					unset($unlisted_category_ids[$category_id]);
				}
			}
			$this->moreCategoryCollectionCells( $i, $j, $data, $category_collection );
			$this->storeCategoryCollectionIntoDatabase( $category_collection);
			$previous_category_id = $category_id;
		}
		if ($incremental) {
			$this->deleteUnlistedCategoryCollections( $unlisted_category_ids );
		} 
	}
	
	protected function uploadProducts( &$reader, $incremental, &$available_product_ids=array(),$shop_user_id=0,&$available_seller_prod_ids=array() ) { 
		global $conf_length_class,$conf_weight_class,$binary_status,$prod_properties,$prod_condition,$prod_inventory_status;
		$prodConditionsArr=array_flip($prod_condition);
		
		// $shop_user_id not null if action performed by seller		
		$shop_user_id=intVal($shop_user_id);
		
		// get worksheet, if not there return immediately
		$data = $reader->getSheetByName( 'Products' );
		if ($data==null) {
			return;
		}
		
		// save product view counts
		$shopUsers=$this->getShopUsers();
		$view_counts = $this->getProductViewCounts();
		$sold_counts = $this->getProductSoldCounts();
		/* $users = $this->getUser(); */
		
		if(!$this->exportImportSettings['export_import_settings_use_added_by_id']) {			
			$usersIds = $this->getUsersId(); 
		}
		if (!$this->exportImportSettings['export_import_settings_use_shop_id']) {
			$shopsIds = $this->getShopIds(); 
		}
		
		if (!$this->exportImportSettings['export_import_settings_use_brand_id']) {
			$productBrandsIds =$this->getProductBrandsIds();
		}	
		if (!$this->exportImportSettings['export_import_settings_use_ship_country_id']) {
			$country_ids = $this->getCountryIds();
		}	
		
		// save old url_alias_ids				
		if($shop_user_id>0){
			$userProductIds=$this->getUserProductIds($shop_user_id);
			//$url_alias_ids = $this->getProductUrlAliasIds($userProductIds);
		}else{			
			//$url_alias_ids = $this->getProductUrlAliasIds();
			$userProductIds=array();
		}
				
		// if incremental then find current product IDs else delete all old products
		$available_product_ids = array();
		if ($incremental) {  
			$available_product_ids = $this->getAvailableProductIds($data);
		} else { 
			if($shop_user_id>0){
				foreach($userProductIds as $pid){
					$this->deleteProduct( $pid);
				}
			}else{
				$this->deleteProducts($url_alias_ids);
			}
		}
	
		// get pre-defined shop_ids
		$available_store_ids = $this->getAvailableStoreIds();
		// find the default units
		$default_weight_unit = $this->getDefaultWeightUnit();
		$default_measurement_unit = $this->getDefaultMeasurementUnit();
		
		$productMinPrice = Settings::getSetting("CONF_MIN_PRODUCT_PRICE");
		$productMinPrice = $productMinPrice ? $productMinPrice : 0;
		
		// get weight classes
		$weight_class_ids = $this->getWeightClassIds();
		// get length classes
		$length_class_ids = $this->getLengthClassIds();
		// get list of the field names, some are only available for certain OpenCart versions
		/* $query = $this->db->query( "DESCRIBE `tbl_products`" );
		$product_fields = array();
		foreach ($query->rows as $row) {
			$product_fields[] = $row['Field'];
		} */
		// load the worksheet cells and store them to the database
		$first_row = array();
		$i = 0;
		$k = $data->getHighestRow();
		for ($i=0; $i<$k; $i+=1) {
			if ($i==0) {
				$max_col = PHPExcel_Cell::columnIndexFromString( $data->getHighestColumn() );
				for ($j=1; $j<=$max_col; $j+=1) {
					$first_row[] = $this->getCell($data,$i,$j);
				}
				continue;
			}
			$j = 1;
			$prod_id = trim($this->getCell($data,$i,$j++));
			
			if ($prod_id=="") {
				continue;
			}
			
			// Action performed by seller
			if($shop_user_id>0){
				$prodIdInFile=$prod_id;				
				if(!in_array($prod_id,$userProductIds)){
					$prod_id = 'AUTO_INCREMENT';
				}
				$available_seller_prod_ids[$prodIdInFile]=$prod_id;
			}
			
			$prod_name = $this->getCell($data,$i,$j++);
			if($prod_name==''){continue;}
			//$prod_name = htmlspecialchars( $prod_name );
			
			$prod_category = $this->getCell($data,$i,$j++);
			$prod_sku = $this->getCell($data,$i,$j++,'');
			$prod_model = $this->getCell($data,$i,$j++,'');
			
			if($shop_user_id==0){
				if (!$this->exportImportSettings['export_import_settings_use_added_by_id']) {
					$name=$this->getCell($data,$i,$j++,'');
					$prod_added_by =(trim($name)!='' && isset($usersIds[strtolower($name)]))?$usersIds[strtolower($name)]:'';					
				}else{
					$prod_added_by = $this->getCell($data,$i,$j++,'');			
				}
			}else{
				$prod_added_by =$shop_user_id;
			}
			
			if (!$this->exportImportSettings['export_import_settings_use_brand_id']) {
				$brand = $this->getCell($data,$i,$j++,'   ');
				$prod_brand =(trim($brand)!='' && isset($productBrandsIds[strtolower($brand)]))?$productBrandsIds[strtolower($brand)]:'';					
			}else{
				$prod_brand = $this->getCell($data,$i,$j++,'   ');
			}			
			
			if($shop_user_id==0){
				if (!$this->exportImportSettings['export_import_settings_use_shop_id']) {
					$shop = $this->getCell($data,$i,$j++);
					$prod_shop =(trim($shop)!='' && isset($shopsIds[strtolower($shop)]))?$shopsIds[strtolower($shop)]:'';
				}else{
					$prod_shop = $this->getCell($data,$i,$j++);
				}
			}else{
				$prod_shop= $this->storeId;
			}
			
			// check product for same shop and user
			if($shop_user_id>0){
				// Action perfomed by seller
				if($shopUsers[$prod_shop]!=$prod_added_by || $shop_user_id!=$prod_added_by){continue;}
			}else{
				// Action perfomed by manager
				if($shopUsers[$prod_shop]!=$prod_added_by){continue;}
			}
			
			$prod_sale_price = $this->getCell($data,$i,$j++);
			
			if($prod_sale_price < $productMinPrice){
				continue;
			}
			/* $prod_shipping = $this->getCell($data,$i,$j++); */
			$prod_stock = $this->getCell($data,$i,$j++);
			
			if (!$this->exportImportSettings['export_import_settings_use_ship_country_id']) {
				$country = $this->getCell($data,$i,$j++);
				$prod_shipping_country =(trim($country)!='' && isset($country_ids[strtolower($country)]))?$country_ids[strtolower($country)]:'';
			}else{
				$prod_shipping_country = $this->getCell($data,$i,$j++);
			}
					
			$prod_min_order_qty = $this->getCell($data,$i,$j++); 
			$prod_subtract_stock = $this->getCell($data,$i,$j++);
			$prod_requires_shipping = $this->getCell($data,$i,$j++);
			$prod_track_inventory = $this->getCell($data,$i,$j++);
			$prod_threshold_stock_level = $this->getCell($data,$i,$j++);
			$prod_condition = $this->getCell($data,$i,$j++);
			$date_added = $this->getCell($data,$i,$j++);
			$date_added = PHPExcel_Style_NumberFormat::toFormattedString($date_added, $this->convertDateFromExcelSheet);
			$prod_added_on = ((is_string($date_added)) && (strlen($date_added)>0)) ? $this->convertDateFormat($date_added,true ): "NOW()";
			
			/* $date_published = $this->getCell($data,$i,$j++);
			$prod_published_on = ((is_string($date_published)) && (strlen($date_published)>0)) ? $this->convertDateFormat($date_published,true) : "NOW()"; */
			$date_available = $this->getCell($data,$i,$j++);
			$date_available = PHPExcel_Style_NumberFormat::toFormattedString($date_available, $this->convertDateFromExcelSheet);
			$prod_available_date = ((is_string($date_available)) && (strlen($date_available)>0)) ? $this->convertDateFormat($date_available) : "NOW()";
			$prod_status = $this->getCell($data,$i,$j++,'true');
			$prod_display_order = $this->getCell($data,$i,$j++,'0');
			
			$url_keyword = $this->getCell($data,$i,$j++);
			$url_keyword=(trim($url_keyword)!='')?$url_keyword:$prod_name;			
			$url_keyword =$this->slugify($url_keyword);
						
			$prod_length = $this->getCell($data,$i,$j++,'0');
			$prod_length_class = $this->getCell($data,$i,$j++,$default_measurement_unit);
			$prod_width = $this->getCell($data,$i,$j++);
			$prod_height = $this->getCell($data,$i,$j++);
			$prod_weight = $this->getCell($data,$i,$j++);
			$prod_weight_class = $this->getCell($data,$i,$j++,$default_weight_unit);
			$prod_tags = htmlspecialchars($this->getCell($data,$i,$j++));
			$prod_youtube_video = htmlspecialchars($this->getCell($data,$i,$j++));
			$prod_short_desc = $this->getCell($data,$i,$j++);
			$prod_long_desc = $this->getCell($data,$i,$j++);
			$prod_meta_title = htmlspecialchars($this->getCell($data,$i,$j++));
			$prod_meta_keywords = htmlspecialchars($this->getCell($data,$i,$j++));
			$prod_meta_description = htmlspecialchars($this->getCell($data,$i,$j++));
			/* $prod_featuered = $this->getCell($data,$i,$j++,'no'); */
			$prod_ship_free = $this->getCell($data,$i,$j++);
			/* $prod_tax_free = $this->getCell($data,$i,$j++); */
			$prod_sold_count = $this->getCell($data,$i,$j++);
			$prod_view_count = $this->getCell($data,$i,$j++);
			$prod_is_deleted = $this->getCell($data,$i,$j++,'false');
			$related_products = $this->getCell($data,$i,$j++);
			$product_addons = $this->getCell($data,$i,$j++);
			
			$product = array();
			$product['prod_id'] = $prod_id;
			if($shop_user_id>0){	
				$product['prod_id_in_file'] = $prodIdInFile;				
			}	
			$product['prod_added_by'] = $prod_added_by;
			$product['prod_category'] = $prod_category;			
			$product['prod_sku'] = $prod_sku;
			$product['prod_name'] = $prod_name;
			$product['prod_slug'] = Utilities::slugify($prod_name);
			$product['prod_brand'] = $prod_brand;
			$product['prod_model'] = $prod_model;
			$product['prod_shop'] = $prod_shop;
			$product['prod_sale_price'] = $prod_sale_price;
			/* $product['prod_shipping'] = $prod_shipping; */
			$product['prod_stock'] = $prod_stock;
			$product['prod_shipping_country'] = $prod_shipping_country;
			$product['prod_min_order_qty'] = $prod_min_order_qty; 
			$product['prod_subtract_stock'] = ((strtoupper($prod_subtract_stock)=="TRUE") || (strtoupper($prod_subtract_stock)=="YES") || (strtoupper($prod_subtract_stock)=="ENABLED")|| (strtoupper($prod_subtract_stock)=="ENABLE")) ? 1 : 0;
			$product['prod_requires_shipping'] = (strtolower(trim($prod_requires_shipping))=='true' || $prod_requires_shipping == 1)?1:0; 
			$product['prod_track_inventory'] = ((strtoupper($prod_track_inventory)=="TRUE") || (strtoupper($prod_track_inventory)=="YES") || (strtoupper($prod_track_inventory)=="ENABLED")|| (strtoupper($prod_track_inventory)=="ENABLE")) ? 1 : 0;
			$product['prod_threshold_stock_level'] = $prod_threshold_stock_level;
			$product['prod_view_count'] = isset($view_counts[$prod_id]) ? $view_counts[$prod_id] : 0;
			$product['prod_sold_count'] = isset($sold_counts[$prod_id]) ? $sold_counts[$prod_id] : 0;
			$product['prod_condition'] = isset($prodConditionsArr[$prod_condition])?$prodConditionsArr[$prod_condition]:'';
			$product['prod_added_on'] = $prod_added_on;
			/* $product['prod_published_on'] = $prod_published_on; */
			$product['prod_status'] = ((strtoupper($prod_status)=="TRUE") || (strtoupper($prod_status)=="YES") || (strtoupper($prod_status)=="ENABLED")|| (strtoupper($prod_status)=="ENABLE")) ? 1 : 0;
			$product['prod_display_order'] = $prod_display_order;
			$product['seo_keyword'] = $url_keyword;
			$product['prod_length'] = $prod_length;
			$product['prod_length_class'] = $prod_length_class;
			$product['prod_width'] = $prod_width;
			$product['prod_height'] = $prod_height;
			$product['prod_weight'] = $prod_weight;
			$product['prod_weight_class'] = $prod_weight_class;
			$product['prod_tags'] = $prod_tags;
			$product['prod_youtube_video'] = $prod_youtube_video;
			$product['prod_short_desc'] = $prod_short_desc;
			$product['prod_long_desc'] = $prod_long_desc;
			$product['prod_meta_title'] = $prod_meta_title;
			$product['prod_meta_keywords'] = $prod_meta_keywords;
			$product['prod_meta_description'] = $prod_meta_description;
			/* $product['prod_featuered'] = $prod_featuered; */
			$product['prod_ship_free'] = ((strtoupper($prod_ship_free)=="TRUE") || (strtoupper($prod_ship_free)=="YES") || (strtoupper($prod_ship_free)=="ENABLED")|| (strtoupper($prod_ship_free)=="ENABLE")) ? 1 : 0;
			/* $product['prod_tax_free'] = $prod_tax_free; */
			$product['prod_available_date'] = $prod_available_date;
			$product['prod_is_deleted'] = ((strtoupper($prod_is_deleted)=="TRUE") || (strtoupper($prod_is_deleted)=="YES") || (strtoupper($prod_is_deleted)=="ENABLED")|| (strtoupper($prod_is_deleted)=="ENABLE")) ? 1 : 0;
			$product['related_products'] = (trim($related_products)!='')?explode(',',rtrim($related_products,',')):array();
			$product['product_addons'] = (trim($product_addons)!='')?explode(',',rtrim($product_addons,',')):array();
			
			if($shop_user_id>0){
				$product['related_products']=array();
				$product['product_addons']=array();
			}
			
			if ($incremental) {
				$this->deleteProduct( $prod_id );
			}
			
			$this->moreProductCells( $i, $j, $data, $product );
			
			$this->storeProductIntoDatabase( $product, $product_fields,$available_store_ids, $weight_class_ids, $length_class_ids, $url_alias_ids,$userProductIds,$shop_user_id,$available_seller_prod_ids );
			
		}
		
	}
	
	protected function uploadProductImages( &$reader, $incremental, &$available_product_ids,$shop_user_id,&$available_seller_prod_ids ) {
		// get worksheet, if not there return immediately
		$data = $reader->getSheetByName( 'ProductImages' );
		if ($data==null) {
			return;
		}
		// if incremental then find current product IDs else delete all old additional images
		if ($incremental) {
			if($shop_user_id>0){
				$unlisted_product_ids = $available_seller_prod_ids;
			}else{
				$unlisted_product_ids = $available_product_ids;
			}
		} else {
			if($shop_user_id>0){				
				$this->deleteUnlistedProductImages( $available_seller_prod_ids );
			}else{
				// Action performed when bullk import by admin
				$this->deleteProductImages();
			}
		}
		
		// load the worksheet cells and store them to the database
		$old_product_image_ids = array();
		$previous_product_id = 0;
		$i = 0;
		$k = $data->getHighestRow();
		for ($i=0; $i<$k; $i+=1) {
			$j= 1;
			if ($i==0) {
				continue;
			}
			$product_id = trim($this->getCell($data,$i,$j++));
			if ($product_id=="") {
				continue;
			}
			
			// Action performed by seller
			if($shop_user_id>0){
				$prod_id_in_file=$product_id;
				$product_id=(isset($available_seller_prod_ids[$prod_id_in_file]))?$available_seller_prod_ids[$prod_id_in_file]:'';
				if ($product_id=="") {
					continue;
				}
			}
			
			$saveToTempTable=false;
			$image_name = $this->getCell($data,$i,$j++,'');
			$isUrlArr=parse_url($image_name);
			if(is_array($isUrlArr) && isset($isUrlArr['host'])){
				$saveToTempTable=true;
			} 
			
			/* $img_str_or_url = $this->getCell($data,$i,$j++,'');
			$image_name=$this->getImageName($img_str_or_url); */
			
			$default_image = $this->getCell($data,$i,$j++,'No');
			
			
			$image = array();
			$image['image_prod_id'] = $product_id;
			$image['image_file'] = $image_name;
			$image['image_default'] = ((strtoupper($default_image)=="TRUE") || (strtoupper($default_image)=="YES") || (strtoupper($default_image)=="ENABLED")|| (strtoupper($default_image)=="ENABLE")) ? 1 : 0;
			
			if (($incremental) && ($product_id != $previous_product_id)) {
				//$old_product_image_ids = $this->deleteAdditionalImage( $product_id );
				if($shop_user_id>0){
					if (isset($unlisted_product_ids[$prod_id_in_file])) {
						unset($unlisted_product_ids[$prod_id_in_file]);
					}
				}else{
					if (isset($unlisted_product_ids[$product_id])) {
						unset($unlisted_product_ids[$product_id]);
					}
				}
			}
			$this->moreAdditionalImageCells( $i, $j, $data, $image );
			$this->storeAdditionalImageIntoDatabase( $image, $old_product_image_ids ,$saveToTempTable);
			$previous_product_id = $product_id;
		}
		if ($incremental) {
			$this->deleteUnlistedProductImages( $unlisted_product_ids );
		}
	}
	
	protected function uploadSpecials( &$reader, $incremental, &$available_product_ids,$shop_user_id,&$available_seller_prod_ids ) {
		// get worksheet, if not there return immediately
		$data = $reader->getSheetByName( 'SpecialDiscounts' );
		if ($data==null) {
			return;
		}
		// if incremental then find current product IDs else delete all old SpecialDiscounts
		if ($incremental) {
			if($shop_user_id>0){
				$unlisted_product_ids = $available_seller_prod_ids;
			}else{
				$unlisted_product_ids = $available_product_ids;
			}			
		} else {
			if($shop_user_id>0){				
				$this->deleteUnlistedSpecials( $available_seller_prod_ids );
			}else{
				// Action performed when bullk import by admin
				$this->deleteSpecials();
			}			
		}
		// load the worksheet cells and store them to the database
		$old_product_special_ids = array();
		$previous_product_id = 0;
		$i = 0;
		$k = $data->getHighestRow();
		for ($i=0; $i<$k; $i+=1) {
			$j = 1;
			if ($i==0) {
				continue;
			}
			$product_id = trim($this->getCell($data,$i,$j++));
			if ($product_id=="") {
				continue;
			}
			
			// Action performed by seller
			if($shop_user_id>0){
				$prod_id_in_file=$product_id;
				$product_id=(isset($available_seller_prod_ids[$prod_id_in_file]))?$available_seller_prod_ids[$prod_id_in_file]:'';
				if ($product_id=="") {
					continue;
				}
			}
			
			$priority = $this->getCell($data,$i,$j++,'0');
			$price = $this->getCell($data,$i,$j++,'0');
			$date_start = $this->getCell($data,$i,$j++,'0000-00-00');
			$date_start = PHPExcel_Style_NumberFormat::toFormattedString($date_start, $this->convertDateFromExcelSheet);
			
			$date_end = $this->getCell($data,$i,$j++,'0000-00-00');
			$date_end = PHPExcel_Style_NumberFormat::toFormattedString($date_end, $this->convertDateFromExcelSheet);
			
			$special = array();
			$special['pspecial_product_id'] = $product_id;
			$special['pspecial_priority'] = $priority;
			$special['pspecial_price'] = $price;
			$special['pspecial_start_date'] = $this->convertDateFormat($date_start);
			$special['pspecial_end_date'] = $this->convertDateFormat($date_end);
			
			if (($incremental) && ($product_id != $previous_product_id)) {
				$old_product_special_ids = $this->deleteSpecial( $product_id );				
				if($shop_user_id>0){
					if (isset($unlisted_product_ids[$prod_id_in_file])) {
						unset($unlisted_product_ids[$prod_id_in_file]);
					}
				}else{
					if (isset($unlisted_product_ids[$product_id])) {
						unset($unlisted_product_ids[$product_id]);
					}
				}
			}
			$this->moreSpecialCells( $i, $j, $data, $special );
			$this->storeSpecialIntoDatabase( $special, $old_product_special_ids );
			$previous_product_id = $product_id;
		}
		if ($incremental) {
			$this->deleteUnlistedSpecials( $unlisted_product_ids );
		}
	}
	
	protected function uploadDiscounts( &$reader, $incremental, &$available_product_ids,$shop_user_id,&$available_seller_prod_ids ) {
		// get worksheet, if not there return immediately
		$data = $reader->getSheetByName( 'QuantityDiscounts' );
		if ($data==null) {
			return;
		}
		// if incremental then find current product IDs else delete all old QuantityDiscounts
		if ($incremental) {
			if($shop_user_id>0){
				$unlisted_product_ids = $available_seller_prod_ids;
			}else{
				$unlisted_product_ids = $available_product_ids;
			}			
		} else {
			if($shop_user_id>0){				
				$this->deleteUnlistedDiscounts( $available_seller_prod_ids );
			}else{
				// Action performed when bullk import by admin
				$this->deleteDiscounts();
			}			
		}
		// load the worksheet cells and store them to the database
		$old_product_discount_ids = array();
		$previous_product_id = 0;
		$i = 0;
		$k = $data->getHighestRow();
		for ($i=0; $i<$k; $i+=1) {
			$j = 1;
			if ($i==0) {
				continue;
			}
			$product_id = trim($this->getCell($data,$i,$j++));
			if ($product_id=="") {
				continue;
			}
			
			// Action performed by seller
			if($shop_user_id>0){
				$prod_id_in_file=$product_id;
				$product_id=(isset($available_seller_prod_ids[$prod_id_in_file]))?$available_seller_prod_ids[$prod_id_in_file]:'';
				if ($product_id=="") {
					continue;
				}
			}
			
			$quantity = $this->getCell($data,$i,$j++,'0');
			$priority = $this->getCell($data,$i,$j++,'0');
			$price = $this->getCell($data,$i,$j++,'0');
			$date_start = $this->getCell($data,$i,$j++,'0000-00-00');
			$date_start = PHPExcel_Style_NumberFormat::toFormattedString($date_start, $this->convertDateFromExcelSheet);
			$date_end = $this->getCell($data,$i,$j++,'0000-00-00');
			$date_end = PHPExcel_Style_NumberFormat::toFormattedString($date_end, $this->convertDateFromExcelSheet);
			
			$discount = array();
			$discount['pdiscount_product_id'] = $product_id;
			$discount['pdiscount_qty'] = $quantity;
			$discount['pdiscount_priority'] = $priority;
			$discount['pdiscount_price'] = $price;
			$discount['pdiscount_start_date'] = $this->convertDateFormat($date_start);
			$discount['pdiscount_end_date'] = $this->convertDateFormat($date_end);
			
			if (($incremental) && ($product_id != $previous_product_id)) {
				$old_product_discount_ids = $this->deleteDiscount( $product_id );
				
				if($shop_user_id>0){
					if (isset($unlisted_product_ids[$prod_id_in_file])) {
						unset($unlisted_product_ids[$prod_id_in_file]);
					}
				}else{
					if (isset($unlisted_product_ids[$product_id])) {
						unset($unlisted_product_ids[$product_id]);
					}
				}				
			}
			$this->moreDiscountCells( $i, $j, $data, $discount );
			$this->storeDiscountIntoDatabase( $discount, $old_product_discount_ids );
			$previous_product_id = $product_id;
		}
		if ($incremental) {
			$this->deleteUnlistedDiscounts( $unlisted_product_ids );
		}
	}
	
	protected function uploadProductOptions( &$reader, $incremental, &$available_product_ids ,$shop_user_id,&$available_seller_prod_ids) {
		// get worksheet, if not there return immediately
		$data = $reader->getSheetByName( 'ProductOptions' );
		if ($data==null) {
			return;
		}
		
		// if incremental then find current product IDs else delete all old product options
		if ($incremental) {
			if($shop_user_id>0){
				$unlisted_product_ids = $available_seller_prod_ids;
			}else{
				$unlisted_product_ids = $available_product_ids;
			}			
		} else {
			if($shop_user_id>0){
				$this->deleteUnlistedProductOptions( $available_seller_prod_ids );
			}else{
				// Action performed when bulk import by admin
				$this->deleteProductOptions();
			}
		}
		if (!$this->exportImportSettings['export_import_settings_use_option_id']) {
			$option_ids = $this->getOptionIds();
		}
		// load the worksheet cells and store them to the database
		$old_product_option_ids = array();
		$previous_product_id = 0;
		$i = 0;
		$k = $data->getHighestRow();
		for ($i=0; $i<$k; $i+=1) {
			$j = 1;
			if ($i==0) {
				continue;
			}
			$product_id = trim($this->getCell($data,$i,$j++));
			if ($product_id=='') {
				continue;
			}
			
			// Action performed by seller
			if($shop_user_id>0){
				$prod_id_in_file=$product_id;
				$product_id=(isset($available_seller_prod_ids[$prod_id_in_file]))?$available_seller_prod_ids[$prod_id_in_file]:'';
				if ($product_id=="") {
					continue;
				}
			}
			
			if ($this->exportImportSettings['export_import_settings_use_option_id']) {
				$option_id = $this->getCell($data,$i,$j++,'');
			} else {
				$option_name = $this->getCell($data,$i,$j++);
				$option_id = isset($option_ids[strtolower($option_name)]) ? $option_ids[strtolower($option_name)] : '';
			}
			if ($option_id=='') {
				continue;
			}
			$option_value = $this->getCell($data,$i,$j++,'');
			$required = $this->getCell($data,$i,$j++,'No');
			
			$product_option = array();
			$product_option['product_id'] = $product_id;
			$product_option['option_id'] = $option_id;
			$product_option['value'] = $option_value;
			$product_option['required'] = ((strtoupper($required)=="TRUE") || (strtoupper($required)=="YES") || (strtoupper($required)=="ENABLED")|| (strtoupper($required)=="ENABLE")) ? 1 : 0;
			if (($incremental) && ($product_id != $previous_product_id)) {
				$old_product_option_ids = $this->deleteProductOption( $product_id );
				
				if($shop_user_id>0){
					if (isset($unlisted_product_ids[$prod_id_in_file])) {
						unset($unlisted_product_ids[$prod_id_in_file]);
					}
				}else{
					if (isset($unlisted_product_ids[$product_id])) {
						unset($unlisted_product_ids[$product_id]);
					}
				}
			}
			
			$this->moreProductOptionCells( $i, $j, $data, $product_option );
			$this->storeProductOptionIntoDatabase( $product_option, $old_product_option_ids );
			$previous_product_id = $product_id;
		} 
		if ($incremental) {
			$this->deleteUnlistedProductOptions( $unlisted_product_ids );
		}
		
	}
	
	protected function uploadProductToTags( &$reader, $incremental, &$available_product_ids ,$shop_user_id,&$available_seller_prod_ids) {
		// get worksheet, if not there return immediately
		$data = $reader->getSheetByName( 'ProductTags' );
		if ($data==null) {
			return;
		}
		
		if(!empty($shop_user_id)){
			$available_seller_prod_ids;
		}
		else{
			if($incremental){
				$available_product_ids;
			}
		}
		
		// if not incremental then delete all old product tags
		if (!$incremental) {
			if(!empty($shop_user_id)){
				$this->deleteProductTags( $available_seller_prod_ids,$shop_user_id );
			}
			else{
				$this->deleteProductTags();
			}
		}
		if (!$this->exportImportSettings['export_import_settings_use_product_tag_id']) {
			$tag_ids = $this->getTagIds();
		}
		// load the worksheet cells and store them to the database
		$i = 0;
		$k = $data->getHighestRow();
		for ($i=0; $i<$k; $i+=1) {
			$j = 1;
			if ($i==0) {
				continue;
			}
			$product_id = trim($this->getCell($data,$i,$j++));
			if ($product_id=='') {
				continue;
			}
			
			// Action performed by seller
			if($shop_user_id>0){
				$prod_id_in_file=$product_id;
				$product_id=(isset($available_seller_prod_ids[$prod_id_in_file]))?$available_seller_prod_ids[$prod_id_in_file]:'';
				if ($product_id=="") {
					continue;
				}
			}
			
			if ($this->exportImportSettings['export_import_settings_use_product_tag_id']) {
				$tag_id = $this->getCell($data,$i,$j++,'');
			} else {
				$tag_name = $this->getCell($data,$i,$j++);
				$tag_id = isset($tag_ids[strtolower($tag_name)]) ? $tag_ids[strtolower($tag_name)] : '';
			}
			if ($tag_id=='') {
				continue;
			}
			
			$product_tag = array();
			$product_tag['product_id'] = $product_id;
			$product_tag['tag_id'] = $tag_id;
			
			$this->storeProductTagIntoDatabase( $product_tag );
		}
	}
	
	protected function uploadProductOptionValues( &$reader, $incremental, &$available_product_ids,$shop_user_id,&$available_seller_prod_ids ) {
		// get worksheet, if not there return immediately
		$data = $reader->getSheetByName( 'ProductOptionValues' );
		if ($data==null) {
			return;
		}
		// if incremental then find current product IDs else delete all old product option values
		if ($incremental) {
			if($shop_user_id>0){
				$unlisted_product_ids = $available_seller_prod_ids;
			}else{
				$unlisted_product_ids = $available_product_ids;
			}			
		} else {
			if($shop_user_id>0){				
				$this->deleteUnlistedProductOptionValues( $available_seller_prod_ids );
			}else{
				// Action performed when bullk import by admin
				$this->deleteProductOptionValues();
			}			
		}
		if (!$this->exportImportSettings['export_import_settings_use_option_id']) {
			$option_ids = $this->getOptionIds();
		}
		if (!$this->exportImportSettings['export_import_settings_use_option_value_id' ]) {
			$option_value_ids = $this->getOptionValueIds();
		}
		// load the worksheet cells and store them to the database
		$old_product_option_ids = array();
		$previous_product_id = 0;
		$product_option_id = 0;
		$i = 0;
		$k = $data->getHighestRow();
		for ($i=0; $i<$k; $i+=1) {
			$j = 1;
			if ($i==0) {
				continue;
			}
			$product_id = trim($this->getCell($data,$i,$j++));
			if ($product_id=='') {
				continue;
			}
			
			// Action performed by seller
			if($shop_user_id>0){
				$prod_id_in_file=$product_id;
				$product_id=(isset($available_seller_prod_ids[$prod_id_in_file]))?$available_seller_prod_ids[$prod_id_in_file]:'';
				if ($product_id=="") {
					continue;
				}
			}
			
			if ($this->exportImportSettings['export_import_settings_use_option_id' ]) {
				$option_id = $this->getCell($data,$i,$j++,'');
			} else {
				$option_name = $this->getCell($data,$i,$j++);
				$option_id = isset($option_ids[strtolower($option_name)]) ? $option_ids[strtolower($option_name)] : '';
			}
			if ($option_id=='') {
				continue;
			}
			if ($this->exportImportSettings['export_import_settings_use_option_value_id']) {
				$option_value_id = $this->getCell($data,$i,$j++,'');
			} else {
				$option_value_name = $this->getCell($data,$i,$j++);
				$option_value_id = isset($option_value_ids[$option_id][strtolower($option_value_name)]) ? $option_value_ids[$option_id][strtolower($option_value_name)] : '';
			}
			if ($option_value_id=='') {
				continue;
			}
			$quantity = $this->getCell($data,$i,$j++,'0');
			$subtract = $this->getCell($data,$i,$j++,'false');
			$price = $this->getCell($data,$i,$j++,'0');
			$price_prefix = $this->getCell($data,$i,$j++,'+');
			$weight = $this->getCell($data,$i,$j++,'0.00');
			$weight_prefix = $this->getCell($data,$i,$j++,'+');
			if ($product_id != $previous_product_id) {
				$product_option_ids = $this->getProductOptionIds( $product_id );
			}
			$product_option_value = array();
			$product_option_value['product_id'] = $product_id;
			$product_option_value['option_id'] = $option_id;
			$product_option_value['option_value_id'] = $option_value_id;
			$product_option_value['quantity'] = $quantity;
			$product_option_value['subtract'] = ((strtoupper($subtract)=="TRUE") || (strtoupper($subtract)=="YES") || (strtoupper($subtract)=="ENABLED")|| (strtoupper($subtract)=="ENABLE")) ? 1 : 0;
			$product_option_value['price'] = $price;
			$product_option_value['price_prefix'] = $price_prefix;
			$product_option_value['weight'] = $weight;
			$product_option_value['weight_prefix'] = $weight_prefix;
			$product_option_value['product_option_id'] = isset($product_option_ids[$option_id]) ? $product_option_ids[$option_id] : 0;
			if (($incremental) && ($product_id != $previous_product_id)) {
				$old_product_option_value_ids = $this->deleteProductOptionValue( $product_id );
				
				if($shop_user_id>0){
					if (isset($unlisted_product_ids[$prod_id_in_file])) {
						unset($unlisted_product_ids[$prod_id_in_file]);
					}
				}else{
					if (isset($unlisted_product_ids[$product_id])) {
						unset($unlisted_product_ids[$product_id]);
					}
				}
			}
			$this->moreProductOptionValueCells( $i, $j, $data, $product_option_value );
			$this->storeProductOptionValueIntoDatabase( $product_option_value, $old_product_option_value_ids );
			$previous_product_id = $product_id;
		}
		if ($incremental) {
			$this->deleteUnlistedProductOptionValues( $unlisted_product_ids );
		}
	}
	
	protected function uploadProductAttributes( &$reader, $incremental, &$available_product_ids,$shop_user_id,&$available_seller_prod_ids ) {
		// get worksheet, if not there return immediately
		$data = $reader->getSheetByName( 'Specifications' );
		if ($data==null) {
			return;
		}
		// if incremental then find current product IDs else delete all old product attributes
		if ($incremental) {
			if($shop_user_id>0){
				$unlisted_product_ids = $available_seller_prod_ids;
			}else{
				$unlisted_product_ids = $available_product_ids;
			}			
		} else {
			if($shop_user_id>0){				
				$this->deleteUnlistedProductAttributes( $available_seller_prod_ids );
			}else{
				// Action performed when bullk import by admin
				$this->deleteProductAttributes();
			}			
		}
		if (!$this->exportImportSettings['export_import_settings_use_attribute_group_id']) {
			$attribute_group_ids = $this->getAttributeGroupIds();
		}
		if (!$this->exportImportSettings['export_import_settings_use_attribute_id']) {
			$attribute_ids = $this->getAttributeIds();
		}
		// load the worksheet cells and store them to the database
		$previous_product_id = 0;
		$first_row = array();
		$i = 0;
		$k = $data->getHighestRow();
		for ($i=0; $i<$k; $i+=1) {
			if ($i==0) {
				$max_col = PHPExcel_Cell::columnIndexFromString( $data->getHighestColumn() );
				for ($j=1; $j<=$max_col; $j+=1) {
					$first_row[] = $this->getCell($data,$i,$j);
				}
				continue;
			}
			$j = 1;
			$product_id = trim($this->getCell($data,$i,$j++));
			if ($product_id=='') {
				continue;
			}
			
			// Action performed by seller
			if($shop_user_id>0){
				$prod_id_in_file=$product_id;
				$product_id=(isset($available_seller_prod_ids[$prod_id_in_file]))?$available_seller_prod_ids[$prod_id_in_file]:'';
				if ($product_id=="") {
					continue;
				}
			}
			
			if ($this->exportImportSettings['export_import_settings_use_attribute_group_id']) {
				$attribute_group_id = $this->getCell($data,$i,$j++,'');
			} else {
				$attribute_group_name = $this->getCell($data,$i,$j++);
				$attribute_group_id = isset($attribute_group_ids[strtolower($attribute_group_name)]) ? $attribute_group_ids[strtolower($attribute_group_name)] : '';
			}
			if ($attribute_group_id=='') {
				continue;
			}
			if ($this->exportImportSettings['export_import_settings_use_attribute_id']) {
				$attribute_id = $this->getCell($data,$i,$j++,'');
			} else {
				$attribute_name = $this->getCell($data,$i,$j++);
				$attribute_id = isset($attribute_ids[$attribute_group_id][strtolower($attribute_name)]) ? $attribute_ids[$attribute_group_id][strtolower($attribute_name)] : '';
			}
			if ($attribute_id=='') {
				continue;
			}
			$texts = htmlspecialchars($this->getCell($data,$i,$j++));
			
			$product_attribute = array();
			$product_attribute['product_id'] = $product_id;
			$product_attribute['attribute_group_id'] = $attribute_group_id;
			$product_attribute['attribute_id'] = $attribute_id;
			$product_attribute['texts'] = $texts;
			
			if (($incremental) && ($product_id != $previous_product_id)) {
				$this->deleteProductAttribute( $product_id );
				if($shop_user_id>0){
					if (isset($unlisted_product_ids[$prod_id_in_file])) {
						unset($unlisted_product_ids[$prod_id_in_file]);
					}
				}else{
					if (isset($unlisted_product_ids[$product_id])) {
						unset($unlisted_product_ids[$product_id]);
					}
				}
			}
			$this->moreProductAttributeCells( $i, $j, $data, $product_attribute );
			$this->storeProductAttributeIntoDatabase( $product_attribute );
			$previous_product_id = $product_id;
		}
		if ($incremental) {
			$this->deleteUnlistedProductAttributes( $unlisted_product_ids );
		}
	}
	
	protected function uploadProductFilters( &$reader, $incremental, &$available_product_ids,$shop_user_id,&$available_seller_prod_ids ) {
		// get worksheet, if not there return immediately
		$data = $reader->getSheetByName( 'ProductFilters' );
		if ($data==null) {
			return;
		}
		// if incremental then find current product IDs else delete all old product filters
		if ($incremental) {
			if($shop_user_id>0){
				$unlisted_product_ids = $available_seller_prod_ids;
			}else{
				$unlisted_product_ids = $available_product_ids;
			}			
		} else {
			if($shop_user_id>0){				
				$this->deleteUnlistedProductFilters( $available_seller_prod_ids );
			}else{
				// Action performed when bullk import by admin
				$this->deleteProductFilters();
			}			
		}
		if (!$this->exportImportSettings['export_import_settings_use_filter_group_id']) {
			$filter_group_ids = $this->getFilterGroupIds();
		}
		if (!$this->exportImportSettings['export_import_settings_use_filter_id' ]) {
			$filter_ids = $this->getFilterIds();
		}
		// load the worksheet cells and store them to the database
		$previous_product_id = 0;
		$first_row = array();
		$i = 0;
		$k = $data->getHighestRow();
		for ($i=0; $i<$k; $i+=1) {
			if ($i==0) {
				$max_col = PHPExcel_Cell::columnIndexFromString( $data->getHighestColumn() );
				for ($j=1; $j<=$max_col; $j+=1) {
					$first_row[] = $this->getCell($data,$i,$j);
				}
				continue;
			}
			$j = 1;
			$product_id = trim($this->getCell($data,$i,$j++));
			if ($product_id=='') {
				continue;
			}
			
			// Action performed by seller
			if($shop_user_id>0){
				$prod_id_in_file=$product_id;
				$product_id=(isset($available_seller_prod_ids[$prod_id_in_file]))?$available_seller_prod_ids[$prod_id_in_file]:'';
				if ($product_id=="") {
					continue;
				}
			}
			
			if ($this->exportImportSettings['export_import_settings_use_filter_group_id' ]) {
				$filter_group_id = $this->getCell($data,$i,$j++,'');
			} else {
				$filter_group_name = $this->getCell($data,$i,$j++);
				$filter_group_id = isset($filter_group_ids[strtolower($filter_group_name)]) ? $filter_group_ids[strtolower($filter_group_name)] : '';
			}
			if ($filter_group_id=='') {
				continue;
			}
			if ($this->exportImportSettings['export_import_settings_use_filter_id']) {
				$filter_id = $this->getCell($data,$i,$j++,'');
			} else {
				$filter_name = $this->getCell($data,$i,$j++);
				$filter_id = isset($filter_ids[$filter_group_id][strtolower($filter_name)]) ? $filter_ids[$filter_group_id][strtolower($filter_name)] : '';
			}
			if ($filter_id=='') {
				continue;
			}
			$product_filter = array();
			$product_filter['product_id'] = $product_id;
			$product_filter['filter_group_id'] = $filter_group_id;
			$product_filter['filter_id'] = $filter_id;
			if (($incremental) && ($product_id != $previous_product_id)) {
				//$this->deleteProductFilter( $product_id );
				if($shop_user_id>0){
					if (isset($unlisted_product_ids[$prod_id_in_file])) {
						unset($unlisted_product_ids[$prod_id_in_file]);
					}
				}else{
					if (isset($unlisted_product_ids[$product_id])) {
						unset($unlisted_product_ids[$product_id]);
					}
				}
			}
			$this->moreProductFilterCells( $i, $j, $data, $product_filter );
			$this->storeProductFilterIntoDatabase( $product_filter);
			$previous_product_id = $product_id;
		}
		if ($incremental) {
			$this->deleteUnlistedProductFilters( $unlisted_product_ids );
		}
	}
	
	protected function uploadProductShippingRates(&$reader, $incremental, &$available_product_ids,$shop_user_id,&$available_seller_prod_ids)
	{
		// get worksheet, if not there return immediately
		$data = $reader->getSheetByName( 'ProductShippingRates' );
		if ($data==null) {
			return;
		}
		// if incremental then find current product IDs else delete all old product shipping rates
		if ($incremental) {
			if($shop_user_id>0){
				$unlisted_product_ids = $available_seller_prod_ids;
			}else{
				$unlisted_product_ids = $available_product_ids;
			}			
		} else {
			if($shop_user_id>0){				
				$this->deleteUnlistedProductShippingRates( $available_seller_prod_ids );
			}else{
				// Action performed when bullk import by admin
				$this->deleteProductShippingRates();
			}			
		}
		if (!$this->exportImportSettings['export_import_settings_use_ship_country_id']) {
			$country_ids = $this->getCountryIds();
		}
		if (!$this->exportImportSettings['export_import_settings_use_ship_company_id' ]) {
			$company_ids = $this->getShippingCompIds();
		}
		if (!$this->exportImportSettings['export_import_settings_use_ship_duration_id' ]) {
			$duration_ids = $this->getShippingDurationIds();
		}
		
		// load the worksheet cells and store them to the database
		$previous_product_id = 0;
		$first_row = array();
		$i = 0;
		$k = $data->getHighestRow();
		for ($i=0; $i<$k; $i+=1) {
			if ($i==0) {
				$max_col = PHPExcel_Cell::columnIndexFromString( $data->getHighestColumn() );
				for ($j=1; $j<=$max_col; $j+=1) {
					$first_row[] = $this->getCell($data,$i,$j);
				}
				continue;
			}
			$j = 1;
			$product_id = trim($this->getCell($data,$i,$j++));
			if ($product_id=='') {
				continue;
			}
			
			// Action performed by seller
			if($shop_user_id>0){
				$prod_id_in_file=$product_id;
				$product_id=(isset($available_seller_prod_ids[$prod_id_in_file]))?$available_seller_prod_ids[$prod_id_in_file]:'';
				if ($product_id=="") {
					continue;
				}
			}
			
			if ($this->exportImportSettings['export_import_settings_use_ship_country_id' ]) {
				$country_id = $this->getCell($data,$i,$j++,'');
			} else {
				$country_name = $this->getCell($data,$i,$j++);
				$country_id = isset($country_ids[strtolower($country_name)]) ? $country_ids[strtolower($country_name)] : 0;
			}
			if ($country_id=='') {
				continue;
			}
			if ($this->exportImportSettings['export_import_settings_use_ship_company_id']) {
				$company_id = $this->getCell($data,$i,$j++,'');
			} else {
				$company_name = $this->getCell($data,$i,$j++);
				$company_id = isset($company_ids[strtolower($company_name)]) ? $company_ids[strtolower($company_name)]: 0;
			}
			if ($company_id=='') {
				continue;
			}
			
			if ($this->exportImportSettings['export_import_settings_use_ship_duration_id']) {
				$duration_id = $this->getCell($data,$i,$j++,'');
			} else {
				$duration_name = $this->getCell($data,$i,$j++);
				$duration_id = isset($duration_ids[strtolower($duration_name)]) ? $duration_ids[strtolower($duration_name)]: 0;
			}
			if ($duration_id=='') {
				continue;
			}
			$charges=$this->getCell($data,$i,$j++);
			$additional_charges=$this->getCell($data,$i,$j++);
			
			$shipping_rates = array();
			$shipping_rates['product_id'] = $product_id;
			$shipping_rates['country_id'] = $country_id;
			$shipping_rates['company_id'] = $company_id;
			$shipping_rates['duration_id'] = $duration_id;
			$shipping_rates['charges'] = $charges;
			$shipping_rates['additional_charges'] = $additional_charges;
			
			if (($incremental) && ($product_id != $previous_product_id)) {
				$this->deleteProductShippingRate( $product_id );
				if($shop_user_id>0){
					if (isset($unlisted_product_ids[$prod_id_in_file])) {
						unset($unlisted_product_ids[$prod_id_in_file]);
					}
				}else{
					if (isset($unlisted_product_ids[$product_id])) {
						unset($unlisted_product_ids[$product_id]);
					}
				}
			}
			$this->moreProductShippingRates( $i, $j, $data, $shipping_rates );
			$this->storeProductShippingRateIntoDatabase( $shipping_rates);
			$previous_product_id = $product_id;
		}
		if ($incremental) {
			$this->deleteUnlistedProductShippingRates( $unlisted_product_ids );
		} 
		
	}
	
	protected function uploadProductSkuStock(&$reader, $incremental,$shop_user_id)
	{
		
		// get worksheet, if not there return immediately
		$data = $reader->getSheetByName( 'StockAndSKU' );
		if ($data==null) {
			return;
		}
				
		if($shop_user_id>0){
			$userProductIds=$this->getUserProductIds($shop_user_id);			
		}else{
			return;
		}
		
		$first_row = array();
		$i = 0;
		$k = $data->getHighestRow();
		for ($i=0; $i<$k; $i+=1) {
			if ($i==0) {
				$max_col = PHPExcel_Cell::columnIndexFromString( $data->getHighestColumn() );
				for ($j=1; $j<=$max_col; $j+=1) {
					$first_row[] = $this->getCell($data,$i,$j);
				}
				continue;
			}
			$j = 1;
			$product_id = trim($this->getCell($data,$i,$j++));
			if ($product_id=='') {
				continue;
			}
			
			if(!in_array($product_id,$userProductIds)){continue;}
			
			$name=$this->getCell($data,$i,$j++);
			if ($name=='') {
				continue;
			}
			$sku=$this->getCell($data,$i,$j++);
			$stock=$this->getCell($data,$i,$j++);
			$price=$this->getCell($data,$i,$j++);
			
			$prodStockAndQtyArr=array();
			$prodStockAndQtyArr['prod_id']=$product_id;
			$prodStockAndQtyArr['prod_name']=$name;
			$prodStockAndQtyArr['prod_sku']=$sku;
			$prodStockAndQtyArr['prod_stock']=$stock;
			$prodStockAndQtyArr['prod_added_by']=$shop_user_id;
			$prodStockAndQtyArr['prod_sale_price']=$price;
			$this->moreProductStockSku( $i, $j, $data, $prodStockAndQtyArr );
			$this->updateProductStockSkuIntoDatabase( $prodStockAndQtyArr);
		}	
		
	}
	
	protected function uploadTags( &$reader ,$incremental, &$available_tag_ids=array(),$shop_user_id=0 ) {
		
		// get worksheet, if not there return immediately
		$data = $reader->getSheetByName( 'Tags' );
		if ($data==null) {
			return;
		}
		// get old url_alias_ids
		$url_alias_ids = $this->getTagUrlAliasIds($shop_user_id);
		// if incremental then find current tag IDs else delete all old tags
		$available_tag_ids = array();
		/* var_dump($incremental);
		var_dump(Syspage::getPostedVar());
		exit; */
		if ($incremental) {
			$available_tag_ids = $this->getAvailableTagIds();
		} else {
			$this->deleteTags($url_alias_ids);
		}
		
		/* $this->deleteTagUrlAliases($url_alias_ids ,$shop_user_id); */
		// load the worksheet cells and store them to the database
		$first_row = array();
		$i = 0;
		$k = $data->getHighestRow();
		
		for ($i=0; $i<$k; $i+=1) {
			if ($i==0) {
				$max_col = PHPExcel_Cell::columnIndexFromString( $data->getHighestColumn() );
				for ($j=1; $j<=$max_col; $j+=1) {
					$first_row[] = $this->getCell($data,$i,$j);
				}
				continue;
			}
			$j = 1;
			$tag_id = trim($this->getCell($data,$i,$j++));
			if ($tag_id=='') {
				continue;
			}
			$tag_name = htmlspecialchars($this->getCell($data,$i,$j++,''));
			$tag_url_keyword = $this->slugify($this->getCell($data,$i,$j++,''));
			
			
			$tagData = $this->getProductTagByName($tag_name);
			
			if (!empty($tagData)){
				if($alias_id = $url_alias_ids[$tagData['ptag_id']]){
					
					$urlAliasArr = array();
					$urlAliasArr['url_alias_id'] = $alias_id;
					$urlAliasArr['url_alias_query'] = 'tags_id='.$tagData['ptag_id'];
					$urlAliasArr['url_alias_keyword'] = $tag_url_keyword;
					
					$this->addUpdateUrlAlias($urlAliasArr);
				}
				
				continue;
			}
			
			$tag = array();
			$tag['ptag_id'] = $tag_id;
			$tag['ptag_name'] = $tag_name;
			$tag['ptag_owner'] = '';
			$tag['ptag_added_by'] = 0;
			$tag['tag_url_keyword'] = $tag_url_keyword;
			if(!empty($shop_user_id)){
				$tag['ptag_owner'] = 'U';
				$tag['ptag_added_by'] = $shop_user_id;
			}
			if ($incremental) {
				if ($available_tag_ids) {
					if (in_array((int)$tag_id,$available_tag_ids)) {
						$this->deleteTag( $tag_id );
					}
				}
			}
			$this->storeTagIntoDatabase( $tag ,$available_store_ids, $url_alias_ids );
		}
	}
	protected function getProductTagByName($name) {
        if($name=="") return array();
       	$add_criteria['name'] = $name;
        $srch = ProductTags::search($add_criteria);
		$srch->joinTable('tbl_url_alias' ,"LEFT JOIN" ,"ua.url_alias_query=CONCAT('tags_id=',tpt.ptag_id)", 'ua');
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
		$rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
    protected function addUpdateUrlAlias($urlAliasData){
		
		if(!empty($urlAliasData)){
			$tableRecord = new TableRecord('tbl_url_alias');
			if(!empty($urlAliasData['url_alias_id'])){
				$tableRecord->setFldValue('url_alias_id',$urlAliasData['url_alias_id']);
			}
			$tableRecord->assignValues($urlAliasData);
			if(!$tableRecord->addNew(array(),$urlAliasData)){
				Message::addErrorMessage($tableRecord->getError());
			}
		}
		return ;
	}
	protected function uploadOptions( &$reader, $incremental ) {
		// get worksheet, if not there return immediately
		$data = $reader->getSheetByName( 'Options' );
		if ($data==null) {
			return;
		}
		// if not incremental then delete all old options
		if (!$incremental) {
			$this->deleteOptions();
		}
		$conf_option_types_val = $this->conf_option_types_val( );
		// load the worksheet cells and store them to the database
		$first_row = array();
		$i = 0;
		$k = $data->getHighestRow();
		for ($i=0; $i<$k; $i+=1) {
			if ($i==0) {
				$max_col = PHPExcel_Cell::columnIndexFromString( $data->getHighestColumn() );
				for ($j=1; $j<=$max_col; $j+=1) {
					$first_row[] = $this->getCell($data,$i,$j);
				}
				continue;
			}
			$j = 1;
			$option_id = trim($this->getCell($data,$i,$j++));
			if ($option_id=='') {
				continue;
			}
			$type = $this->getCell($data,$i,$j++,'');
			$sort_order = $this->getCell($data,$i,$j++,'0');
			$names = htmlspecialchars($this->getCell($data,$i,$j++));
			$is_deleted=$this->getCell($data,$i,$j++,'false');
			
			$option = array();
			$option['option_id'] = $option_id;
			$option['type'] = isset($conf_option_types_val[strtoupper($type)])?$conf_option_types_val[strtoupper($type)]:'text';
			$option['display_order'] = $sort_order;
			$option['names'] = $names;
			$option['is_deleted'] = ((strtoupper($is_deleted)=="TRUE") || (strtoupper($is_deleted)=="YES") || (strtoupper($is_deleted)=="ENABLED")|| (strtoupper($is_deleted)=="ENABLE")) ? 1 : 0;
			if ($incremental) {
				$this->deleteOption( $option_id );
			}
			$this->moreOptionCells( $i, $j, $data, $option );
			$this->storeOptionIntoDatabase( $option );
		}
	}
	
	protected function uploadOptionValues( &$reader, $incremental ) {
		// get worksheet, if not there return immediately
		$data = $reader->getSheetByName( 'OptionValues' );
		if ($data==null) {
			return;
		}
		// check for the existence of option_value_name.image field
		$sql = "SHOW COLUMNS FROM `tbl_option_values` LIKE 'image'";
		$query = $this->db->query( $sql );
		$exist_image = ($query->num_rows > 0) ? true : false;
		// if not incremental then delete all old option values
		if (!$incremental) {
			$this->deleteOptionValues();
		}
		// load the worksheet cells and store them to the database
		$first_row = array();
		$i = 0;
		$k = $data->getHighestRow();
		for ($i=0; $i<$k; $i+=1) {
			if ($i==0) {
				$max_col = PHPExcel_Cell::columnIndexFromString( $data->getHighestColumn() );
				for ($j=1; $j<=$max_col; $j+=1) {
					$first_row[] = $this->getCell($data,$i,$j);
				}
				continue;
			}
			$j = 1;
			$option_value_id = trim($this->getCell($data,$i,$j++));
			if ($option_value_id=='') {
				continue;
			}
			$option_id = trim($this->getCell($data,$i,$j++));
			if ($option_id=='') {
				continue;
			}
			if ($exist_image) {
				$image = $this->getCell($data,$i,$j++,'');
			}
			$sort_order = $this->getCell($data,$i,$j++,'0');
			$names = htmlspecialchars($this->getCell($data,$i,$j++));
			
			$option_value = array();
			$option_value['option_value_id'] = $option_value_id;
			$option_value['option_id'] = $option_id;
			if ($exist_image) {
				$option_value['image'] = $image;
			}
			$option_value['display_order'] = $sort_order;
			$option_value['names'] = $names;
			if ($incremental) {
				$this->deleteOptionValue( $option_value_id );
			}
			$this->moreOptionValueCells( $i, $j, $data, $option_value );
			$this->storeOptionValueIntoDatabase( $option_value, $exist_image );
		}
	}
	
	protected function uploadAttributeGroups( &$reader, $incremental ) {
		// get worksheet, if not there return immediately
		$data = $reader->getSheetByName( 'AttributeGroups' );
		if ($data==null) {
			return;
		}
		// if not incremental then delete all old attribute groups
		if (!$incremental) {
			$this->deleteAttributeGroups();
		}
		// load the worksheet cells and store them to the database
		$first_row = array();
		$i = 0;
		$k = $data->getHighestRow();
		for ($i=0; $i<$k; $i+=1) {
			if ($i==0) {
				$max_col = PHPExcel_Cell::columnIndexFromString( $data->getHighestColumn() );
				for ($j=1; $j<=$max_col; $j+=1) {
					$first_row[] = $this->getCell($data,$i,$j);
				}
				continue;
			}
			$j = 1;
			$attribute_group_id = trim($this->getCell($data,$i,$j++));
			if ($attribute_group_id=='') {
				continue;
			}
			$sort_order = $this->getCell($data,$i,$j++,'0');
			$names = htmlspecialchars($this->getCell($data,$i,$j++));
			$is_deleted=$this->getCell($data,$i,$j++,'false');
			
			$attribute_group = array();
			$attribute_group['attribute_group_id'] = $attribute_group_id;
			$attribute_group['display_order'] = $sort_order;
			$attribute_group['names'] = $names;
			$attribute_group['is_deleted'] = ((strtoupper($is_deleted)=="TRUE") || (strtoupper($is_deleted)=="YES") || (strtoupper($is_deleted)=="ENABLED")|| (strtoupper($is_deleted)=="ENABLE")) ? 1 : 0;
			if ($incremental) {
				$this->deleteAttributeGroup( $attribute_group_id );
			}
			$this->moreAttributeGroupCells( $i, $j, $data, $attribute_group );
			$this->storeAttributeGroupIntoDatabase( $attribute_group );
		}
	}
	
	protected function uploadAttributes( &$reader, $incremental ) {
		// get worksheet, if not there return immediately
		$data = $reader->getSheetByName( 'Attributes' );
		if ($data==null) {
			return;
		}
		// if not incremental then delete all old attributes
		if (!$incremental) {
			$this->deleteAttributes();
		}
		// load the worksheet cells and store them to the database
		$first_row = array();
		$i = 0;
		$k = $data->getHighestRow();
		for ($i=0; $i<$k; $i+=1) {
			if ($i==0) {
				$max_col = PHPExcel_Cell::columnIndexFromString( $data->getHighestColumn() );
				for ($j=1; $j<=$max_col; $j+=1) {
					$first_row[] = $this->getCell($data,$i,$j);
				}
				continue;
			}
			$j = 1;
			$attribute_id = trim($this->getCell($data,$i,$j++));
			if ($attribute_id=='') {
				continue;
			}
			$attribute_group_id = trim($this->getCell($data,$i,$j++));
			if ($attribute_group_id=='') {
				continue;
			}
			$sort_order = $this->getCell($data,$i,$j++,'0');
			$names = htmlspecialchars($this->getCell($data,$i,$j++));
			$is_deleted = $this->getCell($data,$i,$j++,'false');
			
			$attribute = array();
			$attribute['attribute_id'] = $attribute_id;
			$attribute['attribute_group_id'] = $attribute_group_id;
			$attribute['display_order'] = $sort_order;
			$attribute['names'] = $names;
			$attribute['is_deleted'] = ((strtoupper($is_deleted)=="TRUE") || (strtoupper($is_deleted)=="YES") || (strtoupper($is_deleted)=="ENABLED")|| (strtoupper($is_deleted)=="ENABLE")) ? 1 : 0;
			if ($incremental) {
				$this->deleteAttribute( $attribute_id );
			}
			$this->moreAttributeCells( $i, $j, $data, $attribute );
			$this->storeAttributeIntoDatabase( $attribute );
		}
	}
	
	protected function uploadFilterGroups( &$reader, $incremental ) {
		// get worksheet, if not there return immediately
		$data = $reader->getSheetByName( 'FilterGroups' );
		if ($data==null) {
			return;
		}
		// if not incremental then delete all old filter groups
		if (!$incremental) {
			$this->deleteFilterGroups();
		}
		// load the worksheet cells and store them to the database
		$first_row = array();
		$i = 0;
		$k = $data->getHighestRow();
		for ($i=0; $i<$k; $i+=1) {
			if ($i==0) {
				$max_col = PHPExcel_Cell::columnIndexFromString( $data->getHighestColumn() );
				for ($j=1; $j<=$max_col; $j+=1) {
					$first_row[] = $this->getCell($data,$i,$j);
				}
				continue;
			}
			$j = 1;
			$filter_group_id = trim($this->getCell($data,$i,$j++));
			if ($filter_group_id=='') {
				continue;
			}
			$sort_order = $this->getCell($data,$i,$j++,'0');
			$name = htmlspecialchars($this->getCell($data,$i,$j++));
			$is_deleted = $this->getCell($data,$i,$j++,'false');
			
			$filter_group = array();
			$filter_group['filter_group_id'] = $filter_group_id;
			$filter_group['display_order'] = $sort_order;
			$filter_group['names'] = $name;
			$filter_group['is_deleted'] = ((strtoupper($is_deleted)=="TRUE") || (strtoupper($is_deleted)=="YES") || (strtoupper($is_deleted)=="ENABLED")|| (strtoupper($is_deleted)=="ENABLE")) ? 1 : 0;
			if ($incremental) {
				$this->deleteFilterGroup( $filter_group_id );
			}
			$this->moreFilterGroupCells( $i, $j, $data, $filter_group );
			$this->storeFilterGroupIntoDatabase( $filter_group);
		}
	}
	
	protected function uploadFilters( &$reader, $incremental ) {
		// get worksheet, if not there return immediately
		$data = $reader->getSheetByName( 'Filters' );
		if ($data==null) {
			return;
		}
		// if not incremental then delete all old filters
		if (!$incremental) {
			$this->deleteFilters();
		}
		// load the worksheet cells and store them to the database
		$first_row = array();
		$i = 0;
		$k = $data->getHighestRow();
		for ($i=0; $i<$k; $i+=1) {
			if ($i==0) {
				$max_col = PHPExcel_Cell::columnIndexFromString( $data->getHighestColumn() );
				for ($j=1; $j<=$max_col; $j+=1) {
					$first_row[] = $this->getCell($data,$i,$j);
				}
				continue;
			}
			$j = 1;
			$filter_id = trim($this->getCell($data,$i,$j++));
			if ($filter_id=='') {
				continue;
			}
			$filter_group_id = trim($this->getCell($data,$i,$j++));
			if ($filter_group_id=='') {
				continue;
			}
			$sort_order = $this->getCell($data,$i,$j++,'0');
			$name=htmlspecialchars($this->getCell($data,$i,$j++));
			$is_deleted = $this->getCell($data,$i,$j++,'false');
			$filter = array();
			$filter['filter_id'] = $filter_id;
			$filter['filter_group_id'] = $filter_group_id;
			$filter['display_order'] = $sort_order;
			$filter['names'] = $name;
			$filter['is_deleted'] = ((strtoupper($is_deleted)=="TRUE") || (strtoupper($is_deleted)=="YES") || (strtoupper($is_deleted)=="ENABLED")|| (strtoupper($is_deleted)=="ENABLE")) ? 1 : 0;
			if ($incremental) {
				$this->deleteFilter( $filter_id );
			}
			$this->moreFilterCells( $i, $j, $data, $filter );
			$this->storeFilterIntoDatabase( $filter);
		}
	}
	
	protected function uploadShippingDurations(&$reader, $incremental)
	{ 
		// get worksheet, if not there return immediately
		$data = $reader->getSheetByName( 'ShippingDurations' );
		if ($data==null) {
			return;
		}
		
		// if not incremental then delete all old filters
		if (!$incremental) {
			$this->deleteShippingDurations();
		}
		
		// load the worksheet cells and store them to the database
		$first_row = array();
		$i = 0;
		$k = $data->getHighestRow();
		for ($i=0; $i<$k; $i+=1) {
			if ($i==0) {
				$max_col = PHPExcel_Cell::columnIndexFromString( $data->getHighestColumn() );
				for ($j=1; $j<=$max_col; $j+=1) {
					$first_row[] = $this->getCell($data,$i,$j);
				}
				continue;
			}
			$j = 1;
			$duration_id = trim($this->getCell($data,$i,$j++));
			if ($duration_id=='') {
				continue;
			}
			$label=htmlspecialchars($this->getCell($data,$i,$j++));
			$duration_from = $this->getCell($data,$i,$j++,'0');
			$duration_to = $this->getCell($data,$i,$j++,'0');
			$days_or_weeks = $this->getCell($data,$i,$j++,'D');
			$is_deleted = $this->getCell($data,$i,$j++,'false');
			
			$durations = array();
			$durations['sduration_id'] = $duration_id;
			$durations['sduration_label'] = $label;
			$durations['sduration_from'] = $duration_from;
			$durations['sduration_to'] = $duration_to;
			$durations['sduration_days_or_weeks'] = (strtoupper(trim($days_or_weeks))=='W' || strtoupper(trim($days_or_weeks))=='WEEK' || strtoupper(trim($days_or_weeks))=='WEEKS')?'W':'D';
			$durations['sduration_is_deleted'] = ((strtoupper($is_deleted)=="TRUE") || (strtoupper($is_deleted)=="YES") || (strtoupper($is_deleted)=="ENABLED")|| (strtoupper($is_deleted)=="ENABLE")) ? 1 : 0;
			if ($incremental) {
				$this->deleteShippingDuration( $duration_id );
			}
			$this->moreShippingDurationsCells( $i, $j, $data, $durations );
			$this->storeShippingDurationsIntoDatabase( $durations);
		}
	}
	
	protected function uploadShippingCompanies(&$reader, $incremental)
	{ 
		// get worksheet, if not there return immediately
		$data = $reader->getSheetByName( 'ShippingCompanies' );
		if ($data==null) {
			return;
		}
		
		// if not incremental then delete all old filters
		if (!$incremental) {
			$this->deleteShippingCompanies();
		}
		
		// load the worksheet cells and store them to the database
		$first_row = array();
		$i = 0;
		$k = $data->getHighestRow();
		for ($i=0; $i<$k; $i+=1) {
			if ($i==0) {
				$max_col = PHPExcel_Cell::columnIndexFromString( $data->getHighestColumn() );
				for ($j=1; $j<=$max_col; $j+=1) {
					$first_row[] = $this->getCell($data,$i,$j);
				}
				continue;
			}
			$j = 1;
			$company_id = trim($this->getCell($data,$i,$j++));
			if ($company_id=='') {
				continue;
			}
			$name=htmlspecialchars($this->getCell($data,$i,$j++));
			$website=htmlspecialchars($this->getCell($data,$i,$j++));
			$comments=htmlspecialchars($this->getCell($data,$i,$j++));			
			$is_deleted = $this->getCell($data,$i,$j++,'false');
			
			$companies = array();
			$companies['company_id'] = $company_id;
			$companies['name'] = $name;
			$companies['website'] = $website;
			$companies['comments'] = $comments;
			$companies['is_deleted'] = ((strtoupper($is_deleted)=="TRUE") || (strtoupper($is_deleted)=="YES") || (strtoupper($is_deleted)=="ENABLED")|| (strtoupper($is_deleted)=="ENABLE")) ? 1 : 0;
			if ($incremental) {
				$this->deleteShippingCompany( $company_id );
			}
			$this->moreShippingCompaniesCells( $i, $j, $data, $companies );
			$this->storeShippingCompaniesIntoDatabase( $companies);
		}
	}
	
	function getProductTempImages($val)
	{
		$srch = new SearchBase('tbl_product_temp_images', 'ti');
		$srch->addCondition('ti.image_downloaded', '=', 0);
		$srch->addOrder('ti.image_scrapped', 'asc');
		$srch->addOrder('ti.image_id', 'asc');
		if($val>0){$srch->setPageSize($val);}
		$rs = $srch->getResultSet();
        $row = $this->db->fetch_all($rs);		
		if($row==false) return array();
        else return $row;
	}
	
	function updateProductImages($imgArr=array())
	{
		if(empty($imgArr)){return;}		
		$this->db->insert_from_array('tbl_product_images',$imgArr,false,false,$imgArr);
	}
		
	function updateProductTempImages($imgTemp=array())
	{
		if(empty($imgTemp)){return;}
		$image_id=intval($imgTemp['image_id']);
		unset($imgTemp['image_id']);
		if($image_id>0){
			$this->db->update_from_array('tbl_product_temp_images', $imgTemp, array('smt' => 'image_id = ?', 'vals' => array($image_id)));
		}
	}
	
	
	function checkSubscriptionPlanForProductQty($totalProducts=array(),$incremental=false,$user_id){
		if (!Settings::getSetting("CONF_ENABLE_SELLER_SUBSCRIPTION")) return true;
		$subscription_status_assoc_arr = SubscriptionOrders::subscription_status_assoc_arr();
		$subscriptionOrderObj = new SubscriptionOrders();
		$order_filters = array(
			'user'						=>	$user_id,
			'subscription_with_in_date'	=>	date('Y-m-d'), 
			'pagesize'				=>	1
		);
		$orders = $subscriptionOrderObj->getSubscriptionOrders( $order_filters );  
		if(!$orders){
			Message::addMessage(Utilities::getLabel('M_Buy_any_subscription_Package_to_list_your_products'));
			Utilities::redirectUser(Utilities::generateUrl('import_export'));
		}
		if( $orders ){
			$order = array_shift($orders); 
			$prodObj=new Products(); 
			$prod_filter = array();
			$criteria=array("added_by"=>$user_id,"active"=>1,"type"=>1,"sort_by"=>"newproduct","sort_order"=>"desc");
			$products = $prodObj->getProducts( $criteria );
			$total_products = $prodObj->getTotalRecords(); 
			$totalProduct = array_keys($totalProducts);
			$newproducts = 0; 
			$product_ids = array_column($products, 'prod_id');
			foreach($totalProducts[$totalProduct[0]] as $key=>$val){ 
				if(!in_array($val,$product_ids)){
					$newproducts++;
					continue; 
				}   
			} 
			if($incremental){ 
				$total_products += $newproducts;
			}else{
				$total_products  = $totalProduct[0];
			} 
			 
			if( $total_products > $order['mporder_merchantpack_max_products'] && $order['mporder_merchantpack_max_products'] !='-1'){
				$maxProducts = $order['mporder_merchantpack_max_products'];
				$packageLink = '<a href="'.Utilities::generateUrl('account','packages').'">package</a>'; 
				Message::addErrorMessage(sprintf(Utilities::getLabel('M_Uploaded_Max_Products'),$maxProducts,$packageLink)); 
				Utilities::redirectUser( Utilities::generateUrl('import_export'));
			}
		} 
	}
	
	function checkSubscriptionPlanForProductImgs($totalImages=array(),$incremental=false,$user_id){
		if (!Settings::getSetting("CONF_ENABLE_SELLER_SUBSCRIPTION")) return true;
		$subscription_status_assoc_arr = SubscriptionOrders::subscription_status_assoc_arr();
		$subscriptionOrderObj = new SubscriptionOrders();
		$order_filters = array(
			'user'						=>	$user_id,
			'subscription_with_in_date'	=>	date('Y-m-d'), 
			'pagesize'				=>	1
		);
		$orders = $subscriptionOrderObj->getSubscriptionOrders( $order_filters );   
		if( $orders ){
			$order = array_shift($orders);  
			$prodObj=new Products(); 
			$productIds = implode(',',array_keys($totalImages)); 
			$productImages = $prodObj->getImagesCountByProduct($productIds); 
			$images=array();
			if(!empty($productImages)){
				foreach($productImages as $key=>$val){
					if(!$incremental)
						$images[$key] = array_unique(array_merge($val,$totalImages[$key]['name'])); 
					else
						$images[$key] = array_unique($totalImages[$key]['name']); 
				
				
					if( count($images[$key]) > $order['mporder_merchantpack_max_pimages'] && $order['mporder_merchantpack_max_pimages'] !='-1'){
						Message::addErrorMessage(sprintf(Utilities::getLabel('M_Uploaded_Max_Images'),$order['mporder_merchantpack_max_pimages'])); 
						Utilities::redirectUser( Utilities::generateUrl('import_export'));
					}						
				}
			} 
		} 
	}
}	