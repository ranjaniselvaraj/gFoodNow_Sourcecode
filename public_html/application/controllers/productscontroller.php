<?php
class ProductsController extends CommonController {
	function __construct($model, $controller, $action){
		parent::__construct($model, $controller, $action);
//ini_set('memory_limit', '512M');
//$this->set('checked_conditions',array("N"));
	}
	private function getSearchForm(){
		$frm = new Form('frmSearch', 'frmSearch');
		$frm->addHiddenField('', 'status', 1);
		$frm->addHiddenField('', 'page', 1);
		$frm->addHiddenField('', 'product', 0);
		$frm->addHiddenField('', 'pagesize', 10);
		$frm->setOnSubmit('searchProducts(this); return false;');
		return $frm;
	}

	function default_action(){
		$get = getQueryStringData();
		$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_CATALOG");
		$sort = isset($get['sort'])?$get['sort']:'feat';
		$criteria=array("category"=>$category_id,"status"=>1,"pagesize"=>$pagesize,"page"=>1,"donotspecial"=>1,"sort"=>$sort);
		$primarySearchForm = createHiddenFormFromPost("primarySearchForm",'',array(),$criteria);
		$arr_conditions = array_merge($criteria,$get);
		$this->set('primarySearchForm',$primarySearchForm);
		$pObj= new Products();
		$total_records = $pObj->getProductsCount($arr_conditions);
		$pObj= new Products();
		$brands = $pObj->getProductsSearchedBrands($criteria);	
		$pObj= new Products();
		$price_ranges = $pObj->getProductsMinMaxPrice($criteria);
		$categoryObj = new Categories();
		$all_categories=$categoryObj->getProductCategoriesHavingRecords(0);
		$first_level_categories=$all_categories[0];
		Utilities::aasort($first_level_categories,"category_name");
		$this->set('all_categories', $first_level_categories);
		$this->set('brands',$brands);
		$this->set('total_records', $total_records);
		$this->set('price_range', $price_ranges);
		$this->set('get',$get);
		$this->_template->render();
	}
	function top_50(){
		$get = getQueryStringData();
		$criteria=array("status"=>1,"pagesize"=>50,"page"=>1,"donotspecial"=>1,"sort"=>'best');
		$primarySearchForm = createHiddenFormFromPost("primarySearchForm",'',array(),$criteria);
		$arr_conditions = array_merge($criteria,$get);
		$this->set('primarySearchForm',$primarySearchForm);
		$pObj= new Products();
		$total_records = $pObj->getProductsCount($arr_conditions);
		$this->set('total_records', $total_records);
		$this->_template->render();
	}
	function new_stuff(){
		$get = getQueryStringData();
		$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_CATALOG");
		$sort = isset($get['sort'])?$get['sort']:'rece';
		$criteria=array("category"=>$category_id,"status"=>1,"pagesize"=>$pagesize,"page"=>1,"donotspecial"=>1,"sort"=>$sort);
		$primarySearchForm = createHiddenFormFromPost("primarySearchForm",'',array(),$criteria);
		$arr_conditions = array_merge($criteria,$get);
		$this->set('primarySearchForm',$primarySearchForm);
		$pObj= new Products();
		$total_records = $pObj->getProductsCount($arr_conditions);
		$pObj= new Products();
		$brands = $pObj->getProductsSearchedBrands($criteria);	
		$pObj= new Products();
		$price_ranges = $pObj->getProductsMinMaxPrice($criteria);
		$categoryObj = new Categories();
		$all_categories=$categoryObj->getProductCategoriesHavingRecords(0);
		$first_level_categories=$all_categories[0];
		Utilities::aasort($first_level_categories,"category_name");
		$this->set('all_categories', $first_level_categories);
		$this->set('brands',$brands);
		$this->set('total_records', $total_records);
		$this->set('price_range', $price_ranges);
		$this->set('get',$get);
		$this->_template->render();
	}
	function view($prod_id){
		Syspage::addJs(array('js/owl.carousel.js','js/eagle.gallery.js','js/jQueryTab.js'), false);
		Syspage::addCss(array('css/eagle.gallery.css','css/product-detail.css','css/jQueryTab.css'), false);
		$pObj= new Products();
		$pObj->joinWithDetailTable();
		$pObj->joinWithCategoryTable();
		$pObj->joinWithBrandsTable();
		$pObj->joinWithReviewsTable();
		$pObj->addSpecialPrice();
		$pObj->selectFields(array('tp.*','prod_length','prod_length_class', 'prod_width', 'prod_height','prod_weight','prod_weight_class','prod_long_desc', 'prod_youtube_video','prod_meta_title', 'prod_meta_keywords', 'prod_meta_description', 'prod_featuered','prod_ship_free', 'prod_tax_free','prod_available_date','tu.user_id','tu.user_username','tu.user_name','tu.user_email','tu.user_phone','IF(prod_stock >0, "1", "0" ) as available','tpb.brand_id','tpb.brand_name','tpb.brand_status','ts.shop_id','ts.shop_user_id','ts.shop_name','ts.shop_title','ts.shop_description','ts.shop_enable_cod_orders'));
		$this->Shops=new Shops();
		$this->Categories=new Categories();
		$user_id=User::isUserLogged()?User::getLoggedUserId():0;
		$product = $pObj->getData($prod_id,array("favorite"=>$user_id));
		$uObj = new User();
		$user_balance = $uObj->getUserBalance($product['user_id']);
		if ($product['prod_enable_cod_orders'] && $product['shop_enable_cod_orders'] && ($product['prod_type']==1) && Settings::getSetting("CONF_ENABLE_COD_PAYMENTS") && ($user_balance>Settings::getSetting("CONF_COD_MIN_WALLET_BALANCE")))
		$product['cod_enabled']=1;
		
		$_SESSION["visited_time_".$prod_id] = time();
		if (!$product)
			Utilities::show404();
		$pObj->setProductVisitorCookie($prod_id);
		$pObj->recordProductWeightage($prod_id,'products#view');
		$pObj->addUpdateProductBrowsingHistory($prod_id,array("visits"=>1));
		$product_shop = $this->Shops->getData($product["prod_shop"],array("favorite"=>$user_id));
		$product_category_structure=$this->Categories->funcGetCategoryStructure($product["prod_category"]);
		if(isset($product_category_structure[0]) && $product["prod_category"] == $product_category_structure[0]['category_id']){
   			 $product_category_structure = array_reverse($product_category_structure);
   	 	}
		$product_images=$pObj->getProductImages($prod_id);
		$product_discounts = $pObj->getProductDiscounts($prod_id,array("date"=>'1'));
		$pObj= new Products();
		$also_Bought_Products=$pObj->getAlsoBoughtProducts($prod_id);
		$product['options'] = array();
		$product_options = $pObj->getProductOptions($prod_id);
		foreach ($product_options as $optionkey=>$option) {
			$product_option_value_data = array();
			foreach ($option['product_option_value'] as $option_value) {
				if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
					$out_of_stock="";
				}else{
					$out_of_stock=" - (".Utilities::getLabel("L_Out_of_stock").")";
				}
				$product_option_value_data[] = array(
					'product_option_value_id' => $option_value['product_option_value_id'],
					'option_value_id'         => $option_value['option_value_id'],
					'name'                    => $option_value['name'].$out_of_stock,
					'price'                   => $option_value['price'],
					'price_prefix'            => $option_value['price_prefix']
					);
			}
			if (count($option['product_option_value'])>0){
				$product['options'][] = array(
					'product_option_id'    => $option['product_option_id'],
					'product_option_value' => $product_option_value_data,
					'option_id'            => $option['option_id'],
					'name'                 => $option['name'],
					'type'                 => $option['type'],
					'value'                => $option['value'],
					'required'             => $option['required']
					);
			}
		}
		$product_attributes = $pObj->getProductDetailedAttributes($prod_id);
		$shipping_rates = $pObj->getProductShippingRates($prod_id);
		$pObj= new Products();
		$related = $pObj->getProductRelated($prod_id);
		$pObj= new Products();
		$addons = $pObj->getProductAddons($prod_id);
		$pObj= new Products();
		$smart_recommended_products=$pObj->getSmartRecommendedProducts(0,$prod_id);
		$product=array_merge($product,array("images"=>$product_images,"related"=>$related,"discounts"=>$product_discounts,"shipping_rates"=>$shipping_rates,"attribute_groups"=>$product_attributes,"shop"=>$product_shop,"product_category"=>$product_category_structure,"also_bought_products"=>$also_Bought_Products,"smart_recommended_products"=>$smart_recommended_products,"addon_products"=>$addons));
		$criteria=array("product"=>$prod_id,"pagesize"=>5);
		$frm = $this->getSearchForm();
		$frm->fill($criteria);	
		$this->set('frmSearch', $frm);
		$frm=new Form('frmBuyProuct','frmBuyProuct');
		$frm->setRequiredStarWith('caption');
		$frm->setFieldsPerRow(1);
		$frm->setExtra('class="siteForm" validator="frmValidator" autocomplete="off"');
		$frm->captionInSameCell(true);
		$frm->addHiddenField('', 'product_id', $prod_id);
		foreach ($product["options"] as $option) {
			switch($option['type']) {
				case 'select':
				foreach ($option['product_option_value'] as $option_value) {
					$opt_price='';
					if ($option_value['price']) { 
						$opt_price= ' ('.$option_value['price_prefix'].' '.Utilities::displayMoneyFormat($option_value['price'],true,true).')';
					}
					$opt_values[$option['product_option_id']][$option_value['product_option_value_id']]=htmlentities($option_value['name']).$opt_price;
				}
				$fld=$frm->addSelectBox(htmlentities($option['name']), 'option['.$option['product_option_id'].']',$opt_values[$option['product_option_id']],'','',Utilities::getLabel('M_Please_select'),'input-option'.$option['product_option_id']);
				if ($option['required'])
					$fld->requirements()->setRequired();
				break;
				case 'radio':
				foreach ($option['product_option_value'] as $option_value) {
					$opt_price='';
					if ($option_value['price']) { 
						$opt_price= ' ('.$option_value['price_prefix'].' '.Utilities::displayMoneyFormat($option_value['price'],true,true).')';
					}
					$opt_values[$option['product_option_id']][$option_value['product_option_value_id']]=htmlentities($option_value['name']).$opt_price;
				}
				$fld=$frm->addRadioButtons(htmlentities($option['name']), 'option['.$option['product_option_id'].']',$opt_values[$option['product_option_id']],'','1','id=input-option'.$option['product_option_id']);
				if ($option['required'])
					$fld->requirements()->setRequired();
				break;
				case 'checkbox':
				foreach ($option['product_option_value'] as $option_value) {
					$opt_price='';
					if ($option_value['price']) { 
						$opt_price= ' ('.$option_value['price_prefix'].' '.Utilities::displayMoneyFormat($option_value['price'],true,true).')';
					}
					$opt_values[$option['product_option_id']][$option_value['product_option_value_id']]=htmlentities($option_value['name']).$opt_price;
				}
				$fld=$frm->addCheckBoxes(htmlentities($option['name']), 'option['.$option['product_option_id'].']',$opt_values[$option['product_option_id']],'','1','id=input-option'.$option['product_option_id']);
				if ($option['required'])
					$fld->requirements()->setRequired();
				break;
				case 'file':
	//$fld=$frm->addFileUpload($option['name'],'button-upload'.$option['product_option_id']);
				$fld=$frm->addButton(htmlentities($option['name']), 'button['.$option['product_option_id'].']',Utilities::getLabel('M_Upload_File'),'button-upload'.$option['product_option_id'],'data-loading-text="Loading" class="btn btn-default btn-block"');
				if ($option['required'])
					$fld->requirements()->setRequired();
				$fld->html_after_field='<input type="hidden"  name="option['.$option['product_option_id'].']"/><span id="input-option'.$option['product_option_id'].'"></span>';
				break;
				case 'text':
				$fld=$frm->addTextBox(htmlentities($option['name']), 'option['.$option['product_option_id'].']',$option['value'],'input-option'.$option['product_option_id'],'Placeholder="'.$option['name'].'"');
				if ($option['required'])
					$fld->requirements()->setRequired();
				break;
				case 'textarea':
				$fld=$frm->addTextArea(htmlentities($option['name']), 'option['.$option['product_option_id'].']',$option['value'],'input-option'.$option['product_option_id'],'Placeholder="'.$option['name'].'"');
				if ($option['required'])
					$fld->requirements()->setRequired();
				break;
				case 'date':
				$fld=$frm->addTextBox($option['name'], 'option['.$option['product_option_id'].']',$option['value'],'input-option'.$option['product_option_id'],'class="date calendar" Placeholder="'.$option['name'].'"');
				if ($option['required'])
					$fld->requirements()->setRequired();
				break;
				case 'datetime':
				$fld=$frm->addTextBox($option['name'], 'option['.$option['product_option_id'].']',$option['value'],'input-option'.$option['product_option_id'],'class="datetime calendar" Placeholder="'.$option['name'].'"');
				if ($option['required'])
					$fld->requirements()->setRequired();
				break;
				case 'time':
				$fld=$frm->addTextBox($option['name'], 'option['.$option['product_option_id'].']',$option['value'],'input-option'.$option['product_option_id'],'class="time calendar" Placeholder="'.$option['name'].'"');
				if ($option['required'])
					$fld->requirements()->setRequired();
				break;
				}
			}
			if($product){			
				$title  = $product['prod_name'];
				$product_description = trim(subStringByWords(strip_tags(Utilities::renderHtml($product["prod_long_desc"],true)),500));
				$product_description .= ' - '.Utilities::getLabel('L_See_more_at').": ".Utilities::getCurrUrl();
				$productImageUrl = '';
				if (count($product["images"])){		
					$productImageUrl = Utilities::generateAbsoluteUrl('image','product',array('ORIGINAL',$product["image_file"]));
				}
				$socialShareContent = array(
					'title'=>$title,
					'description'=>preg_replace("/\s\s+/", " ", $product_description),
					'image'=>$productImageUrl,
				);
				$this->set( 'socialShareContent', $socialShareContent);
			}
			$fld=$frm->addTextBox(Utilities::getLabel('M_QTY'), 'quantity',1,'','maxlength="3"');
			$fld->requirements()->setIntPositive();
			$fld->requirements()->setCustomErrorMessage(Utilities::getLabel('M_PLEASE_ENTER_POSITIVE_INTEGER'));
			$fld_submit = $frm->addSubmitButton(null, 'btn_cart', Utilities::getLabel('M_Add_to_Cart'), 'cart-button', '');
			$frm->setJsErrorDisplay('afterfield');
			$frm->setAction('?');
			$frm->setValidatorJsObjectName('frmValidator');
			$this->set('product_form', $frm);
			$this->set('product_info', $product);
			$this->_template->render();	
		}
	
	protected function getProductsSearchForm()	{
		$frm=new Form('frmProductsSearch','frmProductsSearch');
		$frm->setMethod('GET');
		$frm->setAction(Utilities::generateUrl('products', 'search'));
		$frm->setFieldsPerRow(3);
		$fldKeyword=$frm->addTextBox('', 'keyword','','search_keyword','Placeholder="'.Utilities::getLabel('F_Search_for_item').'"');
		$fldButton=$frm->addSubmitButton('','search_submit','Submit');
		$fldKeyword->attachField($fldButton);
		$frm->setExtra('class="siteForm" autocomplete="off"');
		$frm->setAction(Utilities::generateUrl('products', 'search'));
		$frm->setTableProperties(' width="100%" border="0" class="tableform"');
		return $frm;
	}
	
	function search(){
		$get = getQueryStringData();
		$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_CATALOG");
		$sort = isset($get['sort'])?$get['sort']:'relv';
		$criteria=array("status"=>1,"pagesize"=>$pagesize,"page"=>1,"donotspecial"=>1,"sort"=>$sort);
		$primarySearchForm = createHiddenFormFromPost("primarySearchForm",'',array(),$criteria);
		$arr_conditions = array_merge($criteria,$get);
		$this->set('primarySearchForm',$primarySearchForm);
		$searchObj=new Search();
		if(isset($get['keyword'])){ 
			$searchData=array('keyword'=>$get['keyword']);	
			$searchObj->addSearchResult($searchData);
		}
		$user_id=User::isUserLogged()?User::getLoggedUserId():0;
		$pObj= new Products();
		$pObj->joinWithDetailTable();
		$pObj->joinWithCategoryTable();
		$pObj->joinWithBrandsTable();
		$total_records = $pObj->getProductsCount($arr_conditions);
		//die($pObj->getquery());
		//die($total_records);
		$pObj= new Products();
		$pObj->joinWithDetailTable();
		$pObj->joinWithCategoryTable();
		$brands = $pObj->getProductsSearchedBrands(array_merge($criteria,array("keyword"=>$get['keyword'])));
		$pObj= new Products();
		$pObj->joinWithDetailTable();
		$pObj->joinWithCategoryTable();
		$pObj->joinWithBrandsTable();
		$price_ranges = $pObj->getProductsMinMaxPrice(array_merge($criteria,array("keyword"=>$get['keyword'])));
		$pObj= new Products();
		$pObj->joinWithDetailTable();
		$pObj->joinWithBrandsTable();
		$categories = $pObj->getProductsSearchedCategories(array_merge($criteria,array("keyword"=>$get['keyword'])));
		$this->set('brands',$brands);
		$this->set('total_records', $total_records);
		$this->set('price_range', $price_ranges);
		$this->set('all_categories',$categories);
		$this->set('get',$get);
		$SearchForm=$this->getProductsSearchForm();
		$this->set('SearchForm',$SearchForm);
		$this->set('top_searched_keywords',$searchObj->getTopSearchedKeywords());
		$this->_template->render();
	}
	
	function send_message($product_id){
		Utilities::checkLogin();
		$this->Shops=new Shops();
		$this->Categories=new Categories();
		$this->Brands=new Brands();
		$this->User=new User();
		$user_id=User::isUserLogged()?User::getLoggedUserId():0;
		$pObj = new Products();
		$pObj->selectFields(array('tp.*','ts.shop_id','ts.shop_user_id','ts.shop_name','ts.shop_title','tu.user_id','tu.user_name','tu.user_username','tu.user_email','IF(prod_stock >0, "1", "0" ) as available'));
		$product = $pObj->getData($product_id,array("status"=>1,"favorite"=>$user_id));
		
		if (!$product)
			Utilities::show404();
		$product_shop = $this->Shops->getData($product["prod_shop"],array("favorite"=>$user_id));
		$product_category_structure=$this->Categories->funcGetCategoryStructure($product["prod_category"]);
		$pObj = new Products();
		/*$pObj->joinWithDetailTable();
		$pObj->selectFields(array('tp.*','ts.shop_id','ts.shop_user_id','ts.shop_name','ts.shop_title','tu.user_id','tu.user_name','tu.user_username','tu.user_email','IF(prod_stock >0, "1", "0" ) as available'));*/
		
		$product_images=$pObj->getProductImages($id);
		$product=array_merge($product,array("images"=>$product_images,"shop"=>$product_shop,"product_category"=>$product_category_structure));
		$all_categories=Categories::getCategoriesAssocArrayFront(0,1);
		$this->set('all_categories', $all_categories[0]);
		$this->set('brands',$this->Brands->getBrands(array("order"=>"brand_name","must_products"=>1)));
	//$user_details=$this->User->getUserById($user_id);
		$userObj=new User();
		$user_details=$userObj->getUser(array('user_id'=>$user_id, 
			'get_flds'=>array(
				'user_id', 
				'user_username',
				'user_name',
				'user_email',
				)));
		$frm=$this->product_send_message_form();
		$vendor_name="<b>".$product["user_username"].'</b> (<em>'.$product["shop_name"].'</em>)';
		$message_to=$product["prod_added_by"];	
		$info=array("user_id"=>$user_id,"product_id"=>$product_id,"send_message_from"=>$user_details["user_name"],"send_message_to"=>$vendor_name,"about_product"=>"<b>".$product["prod_name"]."<b>");
		$frm->fill($info);
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				if($user_id != $post['user_id'] || $product_id != $post['product_id']){
					Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
					Utilities::redirectUser(Utilities::generateUrl('products', 'send_message',array($id)));
				}else{
					$post["message_sent_to"]=$message_to;
					$post["thread_type"]='P';
					$post["thread_record"]=$product_id;
					if($this->User->addThreadMessage($post)){
						Message::addMessage(Utilities::getLabel('M_YOUR_MESSAGE_SENT_SUCCESSFULLY'));	
						Utilities::redirectUser(Utilities::generateUrl('products', 'send_message',array($product_id)));
					}else{
						Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
					}
				}
			}
			$frm->fill($post);
		}
		$this->set('product', $product);
		$this->set('frm', $frm);
		$this->_template->render();	
	}
	protected function product_send_message_form(){
		$frm = new Form('frmSendMessage','frmSendMessage');
		$frm->setTableProperties('width="100%" border="0" cellspacing="10" cellpadding="10"');
		$frm->setLeftColumnProperties('width="30%"');
		$frm->addHiddenField('', 'user_id', 0, 'user_id');
		$frm->addHiddenField('', 'product_id', 0, 'product_id');
		$fld=$frm->addHtml('<label>'.Utilities::getLabel('M_From').'</label>', 'send_message_from');
		$fld->html_after_field='<br/><span class="smalltext">'.Utilities::getLabel('M_Contact_info_not_shared').'</span>';
		$frm->addHtml('<label>'.Utilities::getLabel('M_To').'</label>', 'send_message_to','');
		$frm->addHtml('<label>'.Utilities::getLabel('M_About_Product').'</label>', 'about_product');
		$frm->addRequiredField('<label>'.Utilities::getLabel('M_Subject').'</label>', 'thread_subject', '', 'message_subject', '');
		$fld = $frm->addTextArea('<label>'.Utilities::getLabel('M_Your_Message').'</label>', 'message_text');
		$fld->requirements()->setRequired(true);
		$frm->addSubmitButton('', 'btn_submit', Utilities::getLabel('M_Send'), 'btn_submit');
		$frm->setLeftColumnProperties('width="25%" valign="top" align="left"');
		$frm->setExtra('class="siteForm" validator="frmValidator" autocomplete="off"');
		$frm->captionInSameCell(false);
		$frm->setValidatorJsObjectName('frmValidator');
		$frm->setJsErrorDisplay('afterfield');
		return $frm;
	}
	function uploadProductImages(){
		$pObj=new Products();
		$post = Syspage::getPostedVar();
		$product_tmp_id = intval($post['prod_id']);
		$arr['image_prod_id'] = $product_tmp_id ;
		$arr['image_session'] = session_id() ;
		$user_id=User::isUserLogged()?User::getLoggedUserId():0;
		if(!UserPermissions::canAddProductImages($user_id,$arr['image_prod_id'],$arr['image_session'])){
			$products = new Products();
			$totalImageAllowed  =  $products->getTotalImagesAddedByUser($user_id,$arr['image_prod_id'],$arr['image_session']); 
			Message::addError(sprintf(Utilities::getLabel('M_Product_images_limit_expired'),$totalImageAllowed));
			dieJsonError(Message::getHtml());
		}
		
		$fileObj = new Files();
		if ($fileObj->isUploadedFileValidImage($_FILES['prod_image'])){
				if(!Utilities::saveImage($_FILES['prod_image']['tmp_name'],$_FILES['prod_image']['name'], $prod_image, 'products/')){
		       		Message::addError($prod_image);
    			}
				$arr["image_file"]=$prod_image;
				if($pObj->addProductImage($arr)){
					Message::addMessage(Utilities::getLabel('M_Your_Image_Uploaded_Successfully'));
					dieJsonSuccess(Message::getHtml());
				}else{
				Message::addErrorMessage($pObj->getError());
				dieJsonError(Message::getHtml());
    		}
		}else{
			Message::addErrorMessage($fileObj->getError());
			dieJsonError(Message::getHtml());
		}	
			
		/*if (Utilities::isUploadedFileValidImage($_FILES['prod_image'])){
			if(!Utilities::saveImage($_FILES['prod_image']['tmp_name'],$_FILES['prod_image']['name'], $prod_image, 'products/')){
				Message::addError($prod_image);
			}
			$arr["image_file"]=$prod_image;
			if($pObj->addProductImage($arr)){
				dieJsonSuccess('Your image uploaded successfully.');
			}else{
				dieJsonError($pObj->getError());
			}
		}else{
			dieJsonError($_FILES['prod_image']['name'].' - Invalid: Image Type.');
		}*/
	}
	function getImageUploadTab($product_id=0){
		$post = Syspage::getPostedVar();
		$pObj = new Products();
		$prod_images = $pObj->getProductImages($product_id,array("session"=>session_id()));
		$this->set('prod_images', $prod_images );
		$this->_template->render(false,false,'common/product_images.php');
	}
	function deleteImage($image_id){
		$pObj= new Products(false,true);
		$user_id=$this->getLoggedUserId();
		if($image_id < 1) dieJsonError( Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
		$data = $pObj->getProductImageData($image_id);
		if ($data["image_prod_id"]>0){
			$prod = $pObj->getData($data["image_prod_id"],array("added_by"=>$user_id));
		}else{
			$prod = true;
		}
		if (($data) && ($prod)){
			Utilities::unlinkFile($data["image_file"]);
			if (!$pObj->deleteProductImage($image_id)) {
				dieJsonError($pObj->getError());
			}else{
				dieJsonSuccess( Utilities::getLabel('L_Your_image_removed_successfully') );
			}
		}else{
		dieJsonError( Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
		}
	}
	function setDefaultImage($image_id,$product_id){
		$pObj= new Products();
		if($pObj->makeProductImageDefault($image_id,$product_id)){
			dieJsonSuccess(Utilities::getLabel('L_Image_Set_Default'));
		}else{
			dieJsonError($pObj->getError());
		}
	}
	
	function featured(){
		$get = getQueryStringData();
		$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_CATALOG");
		$sort = isset($get['sort'])?$get['sort']:'feat';
		$criteria=array("property"=>array("prod_featuered"),"status"=>1,"pagesize"=>$pagesize,"page"=>1,"donotspecial"=>1,"sort"=>$sort);
		$primarySearchForm = Utilities::createHiddenFormFromArray("primarySearchForm",'',$criteria);
		$arr_conditions = array_merge($criteria,$get);
		$this->set('primarySearchForm',$primarySearchForm);
		$pObj= new Products();
		$pObj->joinWithDetailTable();
		$total_records = $pObj->getProductsCount($arr_conditions);
		$pObj= new Products();
		$pObj->joinWithDetailTable();
		$brands = $pObj->getProductsSearchedBrands($criteria);	
		$pObj= new Products();
		$pObj->joinWithDetailTable();
		$price_ranges = $pObj->getProductsMinMaxPrice($criteria);
		$this->set('brands',$brands);
		$this->set('total_records', $total_records);
		$this->set('price_range', $price_ranges);
		$this->set('get',$get);
		$this->_template->render();	
	}
	function log_time(){
		$srObj=new SmartRecommendations();
		$weightageSettings= $srObj->getWeightSettings();
		$post = Syspage::getPostedVar();
		$product_id = $post['product_id'];
		$ses_var_name = "visited_time_".$product_id;
		$seconds = time()-$_SESSION[$ses_var_name];
		$_SESSION[$ses_var_name] = time();
		if ($seconds<5000){
			$this->Products->addUpdateProductBrowsingHistory($product_id,array('seconds'=>$seconds));
			$this->Products->recordProductWeightage($product_id,'time_spent',$weightageSettings['products#time_spent']*floor($seconds/30),true);
		}
	}
	
	function uploadProductDownloads(){
		foreach($_FILES as $file){
			$pObj=new Products();
			$post = Syspage::getPostedVar();
			$arr['pfile_session_id'] = session_id() ;
			if (Utilities::isUploadedFileValidFile($file)){
				if(!Utilities::saveFile($file['tmp_name'],time()."_".$file['name'], $product_file, 'product_downloads/')){
		       		Message::addError($product_file);
    			}
				Message::addMessage('Success: Your file uploaded successfully.');
				$arr = array('msg'=>Message::getHtml(), 'file'=>$product_file);
				die(convertToJson($arr));
				
			}else{
			dieJsonError($file['name'].' - Invalid: File Type.');
			}
		}
	}
	
	function remove_download_record(){
		$post = Syspage::getPostedVar();
		$id = $post["id"];
		$pObj= new Products(false,true);
		$user_id=$this->getLoggedUserId();
		if($id < 1) dieJsonError( Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
		$data = $pObj->getProductDownloadFileData($id);
		if ($data["pfile_product_id"]>0){
			$prod = $pObj->getData($data["pfile_product_id"],array("added_by"=>$user_id));
			Utilities::unlinkFile($data["pfile_name"]);
			if (!$pObj->deleteProductDownloadFile($id)) {
				dieJsonError($pObj->getError());
			}else{
				dieJsonSuccess( Utilities::getLabel('L_Your_file_removed_successfully') );
			}
		}
		dieJsonSuccess( Utilities::getLabel('L_Record_not_found') );
		
		
		
	}
}
