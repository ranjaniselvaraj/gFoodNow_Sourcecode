<?php
class ProductsController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,PRODUCTS);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Products Management", Utilities::generateUrl("products"));
    }
	
	
	
	
	function getSearchForm(){
		global $product_types;
		$sObj=new Shops();
		$bObj=new Brands();
		$cObj=new Categories();
		$frm=new Form('frmSearchProducts','frmSearchProducts');
		$frm->setFieldsPerRow(4);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setRequiredStarWith('caption');
		$frm->setMethod('GET');
		$frm->captionInSameCell(true);
		$frm->addHiddenField('', 'mode', "search");
		$frm->addHiddenField('', 'shop');
		$frm->addHiddenField('', 'brand');
		
		$fld=$frm->addTextBox('Keyword', 'keyword','','',' class="medium"');
		$fld->merge_cells=2;
		$frm->addTextBox('Product Shop', 'product_shop','', '', 'class="small"');
		$frm->addTextBox('Product Brand', 'brand_manufacturer','', '', 'class="small"');
		$frm->addSelectBox('Category', 'category', $cObj->getCategoriesAssocArray(), '', 'class="small"', 'Select');
		$fld=$frm->addSelectBox('Active', 'active',array(1=>"Yes",0=>"No"),'' , 'class="small"','All');
		$frm->addTextBox('Price From', 'minprice','','',' class="small"')->requirements()->setFloatPositive($val=true);
		$frm->addTextBox('Price To', 'maxprice','','',' class="small"')->requirements()->setFloatPositive($val=true);
		$fld=$frm->addDateField('Date From', 'date_from', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld=$frm->addDateField('Date To', 'date_to', '', '', 'readonly="true" class="small dateTimeFld"');
		$frm->addSelectBox(Utilities::getLabel('M_Type'), 'type',$product_types,'1','class="medium"','','type');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchProducts(this); return false;');
		return $frm;
	}
    
	function default_action() {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $frm = $this->getSearchForm();
		$frm->fill(getQueryStringData());
        $this->set('frmPost', $frm);
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listProducts($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
			
            $post = Syspage::getPostedVar();
			$post['sort']='rece';
            $page = 1;
			$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
            if (isset($post['page']) && intval($post['page']) > 0) {
                $page = intval($post['page']);
            } 
			$criteria=array("sort"=>"rece");
            if (!empty($post)) {
				$criteria=array_merge($criteria,$post);
                $this->set('srch', $post);
            }
           	
			$pObj=new Product(array('pagesize'=>$pagesize,'page'=>$page));
			$pObj->joinWithDetailTable();
			$pObj->joinWithBrandsTable(array('tpb.brand_id','tpb.brand_name'));
			$pObj->joinWithCategoryTable();
			$pObj->addSpecialPrice();
            $this->set('records', $pObj->getProducts($criteria,true));
            $this->set('pages', $pObj->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $pObj->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
    function form($prod_id,$tab="") {
		//Syspage::addJs(array('../js/jquery-ui/jquery-ui.js'), false);
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("Products Setup", Utilities::generateUrl("products"));
		$this->set('breadcrumb', $this->b_crumb->output());
		$prod_id = intval($prod_id);
		$pObj=new Products();
		$paObj=new Product();
		$paObj->joinWithUrlAliasTable();
		$paObj->joinWithDetailTable(array('tpd.*'));
		$paObj->joinWithBrandsTable(array('tpb.brand_id','tpb.brand_name'));
		$paObj->joinWithCategoryTable();
		if ($prod_id > 0) {
            $data = $paObj->getData($prod_id);
			
			$data["embed_code"]=Utilities::parse_yturl($data["prod_youtube_video"]);
			
			$countryObj=new Countries();
			$country_info = $countryObj->getData($data["prod_shipping_country"]);
			$data["shipping_country"]=isset($country_info["country_name"])?$country_info["country_name"]:'';
			
			$data["prod_tab"]=$tab;
			//$data["category"]=strip_tags(html_entity_decode($categories[$data["prod_category"]], ENT_QUOTES, 'UTF-8'));
			$data["shop"]=$data["shop_name"];
			$data["brand_manufacturer"]=$data["brand_name"];
			
			$pObj=new Products();
			$tags = $pObj->getProductTags($prod_id);
			$data['product_tags'] = array();
			$ptOBj=new Producttags();
			foreach ($tags as $tag_id) {
				$product_tag = $ptOBj->getData($tag_id);
				if ($product_tag) {
					$data['product_tags'][] = array(
						'tag_id' => $product_tag['ptag_id'],
						'name'      => $product_tag['ptag_name'] 
					);
				}
			}
			
			$filters = $pObj->getProductFilters($prod_id);
			$data['product_filters'] = array();
			$fgObj=new Filtergroupoptions();
			foreach ($filters as $filter_id) {
				$filter_info = $fgObj->getData($filter_id);
				if ($filter_info) {
					$data['product_filters'][] = array(
						'filter_id' => $filter_info['filter_id'],
						'name'      => $filter_info['filter_group_name'] . ' &gt; ' . $filter_info['filter_name']
					);
				}
			}
			
			$related = $pObj->getProductRelated($prod_id);
			$data['products_related'] = array();
			foreach ($related as $key=>$val) {
				$data['products_related'][]=$val;
			}
			
			$addons = $pObj->getProductAddons($prod_id);
			$data['products_addons'] = array();
			foreach ($addons as $key=>$val) {
				$data['products_addons'][]=$val;
			}
			
			$attributes = $pObj->getProductAttributes($prod_id);
			$data['product_attributes'] = array();
			foreach ($attributes as $key=>$val) {
				$attributeObj=new Attributes();
				$attribute_info = $attributeObj->getData($val["id"]);
				if ($attribute_info) {
					$data['product_attributes'][] = array(
						'id' => $attribute_info['attribute_id'],
						'name'      => $attribute_info['attribute_name'],
						'text'      => $val['text']
					);
				}
			}
			
			$shipping_rates = $pObj->getProductShippingRates($prod_id);
			$data['product_shipping_rates'] = array();
			foreach ($shipping_rates as $key=>$val) {
				$data['product_shipping_rates'][]=$val;
			}
			
			$product_discounts = $pObj->getProductDiscounts($prod_id);
			$data['product_discounts'] = array();
			foreach ($product_discounts as $key=>$val) {
				$data['product_discounts'][]=$val;
			}
			
			$product_specials = $pObj->getProductSpecials($prod_id);
			$data['product_specials'] = array();
			foreach ($product_specials as $key=>$val) {
				$data['product_specials'][]=$val;
			}
						
			$product_options = $pObj->getProductOptions($prod_id);
			$data['product_options'] = array();
			foreach ($product_options as $key=>$val) {
				$data['product_options'][]=$val;
			}
			
			$product_downloads = $pObj->getProductDownloads($prod_id);
			$data['product_downloads'] = array();
			foreach ($product_downloads as $key=>$val) {
				$data['product_downloads'][]=$val;
			}
			
			$data['option_values'] = array();
			$optionObj=new Options();
			foreach ($data['product_options'] as $product_option) {
				if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
					if (!isset($data['option_values'][$product_option['option_id']])) {
						$data['option_values'][$product_option['option_id']] = $optionObj->getOptionValues($product_option['option_id']);
					}
				}
			}
			$this->set('data', $data);
			$this->set('shop', $data["prod_shop"]);
		}
		
        $frm = $this->getForm($data);
		unset($data['prod_category']);
		$frm->fill($data);
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
				if ($post['prod_type']==2){
					$frm->getField('prod_length')->requirements()->setRequired(false);
					$frm->getField('prod_width')->requirements()->setRequired(false);
					$frm->getField('prod_height')->requirements()->setRequired(false);
					$frm->getField('prod_weight')->requirements()->setRequired(false);
				}
				if(!$frm->validate($post)){
					Message::addErrorMessage($frm->getValidationErrors());
				}elseif(!$pObj->validateOptionQuantity($post['product_option'],$post['prod_stock'])){
					Message::addErrorMessage(Utilities::getLabel('M_OPTION_QUANTITY_ERROR'));
				}else{
					$arr_cnt = count($post['prod_category']);
					$prod_category=empty($post['prod_category'][$arr_cnt-1])?$post['prod_category'][$arr_cnt-2]:$post['prod_category'][$arr_cnt-1];
					
					$post['prod_category']= $prod_category;
					//Utilities::printArray($post['prod_category']);
					//die();
					$oldSlug=$data['seo_url_keyword'];
					$slug=Utilities::slugify($post['seo_url_keyword']);
					if (($slug != $oldSlug) && (!empty($post['seo_url_keyword']))){  
					    $i = 1; $baseSlug = $slug;
						$url_alias=new Url_alias();              
					    while($url_alias->getUrlAliasByKeyword($slug)){                
					        $slug = $baseSlug . "-" . $i++;     
					        if($slug == $oldSlug){              
				           		break;                          
					       }
					    }
					}	
					
					$post['seo_url_keyword']=$slug;
					if (!$error){
							$shopObj=new Shop();
							$shop = $shopObj->getData($post["prod_shop"]);
							if ($shop){
								$post=array_merge(array('prod_enable_cod_orders'=>0,'prod_featuered'=>0,'prod_ship_free'=>0,'prod_added_by'=>$shop["shop_user_id"],"requested_by"=>"A"),$post);
								if($pObj->addUpdateProduct($post)){
									Message::addMessage(Utilities::getLabel('M_YOUR_ACTION_PERFORMED_SUCCESSFULLY'));
									$product_id = $pObj->getProdId();
									Utilities::redirectUser(Utilities::generateUrl('products','form',array($product_id,$post['prod_tab'])));	
								}else{
									Message::addErrorMessage($pObj->getError());
								}
							}
						}
					}
				$frm->fill($post);
		}
        $this->set('frm', $frm);
		$this->set('tab', $tab);
		session_regenerate_id(false);
        $this->_template->render(true,true);
	}
    protected function getForm($info) {
		global $binary_status,$active_inactive_status,$product_types;
		global $conf_length_class,$conf_weight_class,$binary_status,$prod_condition,$prod_inventory_status;
		$frm = new Form('frmProducts','frmProducts');
		$frm->setOnSubmit('return validateProductForm(this, ProductfrmValidator);');
		$frm->setExtra(' validator="ProductfrmValidator" class="web_form" autocomplete="off"');
        $frm->setValidatorJsObjectName('ProductfrmValidator');
		$frm->setRequiredStarWith('caption');
        $frm->addHiddenField('', 'prod_id',0,'prod_id');
		$frm->addHiddenField('', 'prod_tab','general','prod_tab');
		$frm->addHiddenField('', 'prod_brand');
		$frm->addHiddenField('', 'prod_shop');
		//$frm->addHiddenField('', 'prod_category');
		$frm->addHiddenField('', 'prod_shipping_country');
		
		/*****************    START TAB 1      *******************/
		$frm->addRequiredField('Product Shop', 'shop','', 'product_shop_focus', 'class="medium"');
		$frm->addRequiredField('Name', 'prod_name','', '', 'class="medium" onKeyUp="Slugify(this.value,\'seo_url_keyword\',\'prod_id\');copyValue(this.value,\'prod_meta_title\',\'prod_id\')"' );
		
		$fld=$frm->addRequiredField('URL Keywords', 'seo_url_keyword','', 'seo_url_keyword', 'class="medium"' );
		$fld->html_after_field='<small>Do not use spaces, instead replace spaces with - and make sure the keyword is globally unique.</small>';
		
		//$frm->addSelectBox('Type', 'prod_type',$product_types,'1','class="medium"','');
		$frm->addSelectBox('Type', 'prod_type',$product_types,'1','class="medium"','','prod_type');
		
		$fld=$frm->addRequiredField('Price ['.CONF_CURRENCY_SYMBOL.']', 'prod_sale_price', '', 'prod_sale_price', ' class="medium"');
		$fld->requirements()->setFloatPositive($val=true);
		$fld->html_after_field='<small>'.sprintf(Utilities::getLabel('M_Minimum_Selling_Price'),Utilities::displayMoneyFormat(Settings::getSetting("CONF_MIN_PRODUCT_PRICE"))).'</small>';
		$fld=$frm->addRequiredField('Quantity', 'prod_stock', '1', '', ' class="medium"');
		$fld->requirements()->setIntPositive($val=true);
		
		$fld=$frm->addRequiredField('Minimum Quantity', 'prod_min_order_qty', '1', '', ' class="medium"');
		$fld->requirements()->setIntPositive($val=true);
		$fld->html_after_field='<small>Force a minimum ordered quantity.</small>';
		
		$frm->addTextBox('Brand/Manufacturer', 'brand_manufacturer','', '', 'class="medium"');
		//$frm->addTextBox('Product Category', 'category','', '', 'class="medium"');
		
		
		$cObj = new Categories();
		$cat_arr = $cObj->funcGetCategoryStructure($info["prod_category"]);
		$cat_id = $cat_arr[0]['category_id'];
		if ($cat_id>0){
			$incr = 0;
			foreach($cat_arr as $ckey=>$cval){
				$incr++;
				${'fld_'.$incr} = $frm->addSelectBox('Product Category', 'prod_category[]', $cObj->getParentAssociativeArray($cval['category_parent'],1),$cval['category_id'], 'class="product_category small primary"', 'Select');
				if ($incr>1){
					$j=$incr-1;
					${'fld_'.$j}->attachField(${'fld_'.$incr});
				}
				if ($incr==count($cat_arr))
					${'fld_'.$incr}->html_after_field='<span id="show_sub_categories"></span>';
				
			}
		}else{
			$fld = $frm->addSelectBox('Product Category', 'prod_category[]', $cObj->getParentAssociativeArray(0,1),$cat_id, 'class="product_category small primary"', 'Select');
			$fld->html_after_field='<span id="show_sub_categories"></span>';
		}
		//die ($frm->getFormHtml());
		$fld_model = $frm->addTextBox('Model', 'prod_model','', '', 'class="medium"' );
		if (Settings::getSetting("CONF_PRODUCT_MODEL_MANDATORY")){
			$fld_model->requirements()->setRequired();
		}
		
		$fld_sku=$frm->addTextBox('SKU', 'prod_sku','', '', 'class="medium"' );
		if (Settings::getSetting("CONF_PRODUCT_SKU_MANDATORY")){
			$fld_sku->requirements()->setRequired();
		}
		
		$fld_sku->html_after_field='<small>Stock Keeping Unit</small>';
		
		if (Settings::getSetting("CONF_ALLOW_USED_PRODUCTS_LISTING")){
			$fldC=$frm->addSelectBox(Utilities::getLabel('M_Product_Condition'), 'prod_condition', $prod_condition,'N', 'class="medium"','Select Condition','prod_condition');
			$fldC->requirements()->setRequired();
		}
		$frm->addSelectBox('Status', 'prod_status',$active_inactive_status,'1','class="medium"','');
		
		if (Settings::getSetting("CONF_ENABLE_COD_PAYMENTS")){
			$fldALA=$frm->addCheckBox('<label>'.Utilities::getLabel('M_Enable_COD').'</label>', 'prod_enable_cod_orders','1','prod_enable_cod_orders');
		}
		
		$fldPI=$frm->addFileUpload('Photo(s):', 'prod_image', '', ' id="prod_image" multiple onchange="submitImageUploadImageForm(); return false;" ');
		$fldPI->html_before_field='<div class="filefield"><span class="filename"></span>';
		$fldPI->html_after_field='<label class="filelabel">Browse File</label></div><small>'.Utilities::getLabel('M_PLEASE_KEEP_IMAGE_DIMENSIONS').' '.Utilities::getLabel('M_YOU_CAN_UPLOAD_MULTIPLE_PHOTOS').'</small><br/><span id="imageupload_div"></span>';
			
			
		$fld=$frm->addHtmlEditor('Description', 'prod_long_desc', '', 'prod_long_desc', 'class="fieldtextAreaEditor cleditor"');
		
		$fld_meta_title = $frm->addTextBox('Meta Tag Title', 'prod_meta_title','', 'prod_meta_title', 'class="medium"');
		if (Settings::getSetting("CONF_PRODUCT_META_TITLE_MANDATORY")){
			$fld_meta_title->requirements()->setRequired();
		}
		
		$fld=$frm->addTextArea('Meta Tag Description', 'prod_meta_description','', '', 'class="medium"');
		//$fld->requirements()->setRequired();
		$frm->addTextArea('Meta Tag Keywords', 'prod_meta_keywords','', '', 'class="medium"');
		$product_tags  = '';
		foreach ($info["product_tags"] as $product_tag) { 
				$product_tags.='<div id="product-tag'.$product_tag['tag_id'].'"><i class="remove_tag remove_param"><img src="'.CONF_WEBROOT_URL.'images/admin/closelabels.png"/></i>'.$product_tag['name'].'<input type="hidden" name="product_tag[]" value="'. $product_tag['tag_id'].'" /></div>';
		}
		$fld=$frm->addTextBox('Product Tags', 'prod_tags','', '', 'class="medium"');
		$fld->html_after_field = '<div id="product-tag" class="well well-sm" style="height: 150px; overflow: auto;">'.$product_tags.'</div>';
		
		//$frm->addRadioButtons('Requires Shipping', 'prod_requires_shipping',$binary_status,1,2,'width="45%" ');
		$frm->addSelectBox('Requires Shipping', 'prod_requires_shipping',$binary_status,'1','class="medium"','','prod_requires_shipping');
		
		//$fld=$frm->addTextBox('Tags', 'prod_tags','', '', 'class="medium autocomplete"');
		//$fld->html_after_field='<small>Please enter comma separated</small>';
		
		/*****************    END TAB 1    *******************/
		
		/*****************    START TAB 2      *******************/
		
		$frm->addSelectBox('Subtract Stock', 'prod_subtract_stock',$binary_status,'1','class="medium"','','');
		
		$frm->addSelectBox('Inventory', 'prod_track_inventory',$prod_inventory_status,'0' , 'class="medium"','','prod_track_inventory');
		$fld=$frm->addTextBox('Threshold Level', 'prod_threshold_stock_level', '', '', ' class="medium" maxlength="5" ');
		$fld->requirements()->setIntPositive($val=true);
		$fld->html_after_field='<small>Note: You will receive email notification when product stock qty is below or equal to threshold level and Inventory tracking is enabled.</small>';
		
			
		$fld=$frm->addTextBox('Youtube Video', 'prod_youtube_video','', '', 'class="medium"');
		$fld->html_after_field='<small>Please enter the youtube video URL here.</small>';
				
		$frm->addTextBox('Date Available', 'prod_available_date', date('Y-m-d'), '', 'readonly="true" class="date-pick medium"');
		$fldLength=$frm->addTextBox('Dimensions (L x W x H)', 'prod_length','', 'prod_length', 'Placeholder="Length" class="mini"');
		$fldLength->html_after_field = ' ';
		$fldWidth=$frm->addTextBox('Dimensions (L x W x H)', 'prod_width','', 'prod_width', 'Placeholder="Width" class="mini"');
		$fldWidth->html_after_field = ' ';
		$fldHeight=$frm->addTextBox('Dimensions (L x W x H)', 'prod_height','', 'prod_height', 'Placeholder="Height" class="mini"');
		$fldWidth->attachField($fldHeight);
		$fldLength->attachField($fldWidth);
		
		$frm->addSelectBox('Length Class', 'prod_length_class',$conf_length_class,'','class="medium"','');
		$field_weight=$frm->addTextBox('Weight', 'prod_weight','', 'prod_weight', 'Placeholder="Weight" class="medium"');
		if (Settings::getSetting("CONF_SHIPSTATION_API_STATUS")){
			//die('AA');
			$fldLength->requirements()->setRequired();
			$fldLength->requirements()->setFloatPositive($val=true);
			$fldWidth->requirements()->setRequired();
			$fldWidth->requirements()->setFloatPositive($val=true);
			$fldHeight->requirements()->setRequired();
			$fldHeight->requirements()->setFloatPositive($val=true);
			$field_weight->requirements()->setRequired();
			$field_weight->requirements()->setFloatPositive($val=true);
		}
		
		$frm->addSelectBox('Weight Class', 'prod_weight_class',$conf_weight_class,'','class="medium"','');
		
		$frm->addTextBox('Display Order', 'prod_display_order','1', '', 'class="medium"');
		$fld=$frm->addCheckBox('Featured Product', 'prod_featuered',1);
		$fld->html_after_field='<span class="clear"></span><small>Featured Products will be listed on Featured Products Page.</small>';
		/*****************    END TAB 2      *******************/
	
		/*****************    START TAB 3      *******************/	
		$product_filters  = '';	
		foreach ($info["product_filters"] as $product_filter) { 
				$product_filters.='<div id="product-filter'.$product_filter['filter_id'].'"><i class="remove_filter remove_param"><img src="'.CONF_WEBROOT_URL.'images/admin/closelabels.png"/></i>'.$product_filter['name'].'<input type="hidden" name="product_filter[]" value="'. $product_filter['filter_id'].'" /></div>';
		}
		$fld=$frm->addTextBox('Product Filters', 'filter','', '', 'class="medium"');
		$fld->html_after_field = '<div id="product-filter" class="well well-sm" style="height: 150px; overflow: auto;">'.$product_filters.'</div>';
		$products_related = '';
		foreach ($info["products_related"] as $product_related) { 
				$products_related.='<div id="product-related'.$product_related['prod_id'].'"><i class="remove_related remove_param"><img src="'.CONF_WEBROOT_URL.'images/admin/closelabels.png"/></i>'.$product_related['prod_name'].'<input type="hidden" name="product_related[]" value="'. $product_related['prod_id'].'" /></div>';
		}
		$fld=$frm->addTextBox('Related Products', 'related','', '', 'class="medium"');
		$fld->html_after_field = '<div id="product-related" class="well well-sm" style="height: 150px; overflow: auto;">'.$products_related.'</div>';
		$product_addons = '';
		foreach ($info["products_addons"] as $product_addon) { 
				$product_addons.='<div id="product-addon'.$product_addon['prod_id'].'"><i class="remove_addon remove_param"><img src="'.CONF_WEBROOT_URL.'images/admin/closelabels.png"/></i>'.$product_addon['prod_name'].'<input type="hidden" name="product_addon[]" value="'. $product_addon['prod_id'].'" /></div>';
		}
		$fld=$frm->addTextBox('Add-on Products', 'addons','', '', 'class="medium"');
		$fld->html_after_field = '<div id="product-addon" class="well well-sm" style="height: 150px; overflow: auto;">'.$product_addons.'</div><small>Maximum '.Settings::getSetting("CONF_MAX_NUMBER_PRODUCT_ADDONS").' add-ons can be selected for a particular product.</small>';
		
		/*****************    ENF TAB 3      *******************/
		
		/*****************    START TAB 6      *******************/		
		$fld=$frm->addTextBox(Utilities::getLabel('M_Country'), 'shipping_country','', '', 'class="medium"' );
		//$fld_country=$frm->addSelectBox(Utilities::getLabel('M_Country'), 'prod_ship_country', Countries::getAssociativeArray(), '', 'class="medium"', Utilities::getLabel('M_Country'), 'prod_ship_country');
		
		$fld=$frm->addCheckBox('Ship Free', 'prod_ship_free',1,'prod_ship_free');
		$fld->html_after_field='<small>Shipping Prices will not be considered for any location for ship free products.</small>';	
		//$fld=$frm->addTextBox('Default Shipping ['.CONF_CURRENCY_SYMBOL.']', 'prod_shipping', '', 'prod_shipping', ' class="medium"');
		//$fld->requirements()->setFloatPositive($val=true);
		
		/*****************    ENF TAB 6      *******************/
		
		
		$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
        //$frm->setAction(Utilities::generateUrl('products', 'setup'));
		$frm->setJsErrorDisplay('afterfield');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="15%"');
        return $frm;
    }
	
	/*function uploadProductImages(){
		$pObj=new Products();
		$post = Syspage::getPostedVar();
		$product_tmp_id = intval($post['prod_id']);
		$arr['image_prod_id'] = $product_tmp_id ;
		$arr['image_session'] = session_id() ;
		if (Utilities::isUploadedFileValidImage($_FILES['prod_image'])){
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
		}
		
	}*/
	
	function uploadProductImages(){
		$pObj=new Products();
		$post = Syspage::getPostedVar();
		$product_tmp_id = intval($post['prod_id']);
		$arr['image_prod_id'] = $product_tmp_id ;
		$arr['image_session'] = session_id() ;
		$fileObj = new Files();
		if ($fileObj->isUploadedFileValidImage($_FILES['prod_image'])){
				if(!Utilities::saveImage($_FILES['prod_image']['tmp_name'],$_FILES['prod_image']['name'], $prod_image, 'products/')){
		       		Message::addError($prod_image);
    			}
				$arr["image_file"]=$prod_image;
				if($pObj->addProductImage($arr)){
					Message::addMessage('Your image uploaded successfully.');
					dieJsonSuccess(Message::getHtml());
				}else{
				Message::addErrorMessage($pObj->getError());
				dieJsonError(Message::getHtml());
    		}
		}else{
			Message::addErrorMessage($fileObj->getError());
			dieJsonError(Message::getHtml());
		}
		
	}
	
	function getImageUploadTab($product_id=0){
		$pObj=new Products();
		$prod_images = $pObj->getProductImages($product_id,array("session"=>session_id()));
		$this->set('prod_images', $prod_images );
		$this->_template->render(false,false,'products/product_images.php');
	}
	
	function deleteImage($image_id){
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$pObj=new Products();
		if($image_id < 1) dieJsonError( 'Invalid request');
        if($pObj->deleteProductImage($image_id)){
			dieJsonSuccess('Your image removed successfully.');
		}else{
			dieJsonError($pObj->getError());
		}
	}
	
	function setDefaultImage($image_id,$product_id){
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$pObj=new Products();
        if($pObj->makeProductImageDefault($image_id,$product_id)){
			dieJsonSuccess('Image set as default successfully.');
		}else{
			dieJsonError($pObj->getError());
		}
	}
	
	function setProductImagesOrdering(){
		$this->db = Syspage::getdb();
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$post = Syspage::getPostedVar();
		$pObj=new Products();
		if(isset($post['do_submit']))  {
				$ids = explode(',',$post['sort_order']);
				foreach($ids as $index=>$id) {
					$id = (int) $id;
					if($id != '') {
						$this->db->update_from_array('tbl_product_images', array('image_ordering' => ($index + 1)), array('smt' => 'image_id = ?', 'vals' => array($id)));
					}
				}
				if($post['byajax']) { die(); } else { $message = 'Sortation has been saved.'; }
		}
		
	}
	
	function update_product_status() {
		if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
		$post = Syspage::getPostedVar();
		$pObj=new Product();
        $product_id = intval($post['id']);
        $product = $pObj->getData($product_id);
		if($product==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		$mod = $product['prod_status']?'block':'unblock';
		$pObj=new Products();
		if($pObj->updateProductStatus($product_id,$mod)){
			Message::addMessage('Success: Status has been modified successfully.');
			$arr = array('status'=>1, 'msg'=>Message::getHtml(), 'linktext'=>(($product['prod_status'] == 1)?'Active':'Inactive'));
			die(convertToJson($arr));
		}else{
			Message::addErrorMessage($pObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	function delete() {
        if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
        $post = Syspage::getPostedVar();
		$pObj=new Product();
        $product_id = intval($post['id']);
        $product = $pObj->getData($product_id);
		if($product==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		$pObj=new Products();
		if($pObj->deleteProduct($product_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($pObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	
	
	function remove_download_record(){
		if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
		$this->db = &Syspage::getdb();
		$post = Syspage::getPostedVar();
		$id = $post["id"];
		if ($this->db->deleteRecords('tbl_product_files', array('smt'=>'pfile_id=? ', 'vals'=>array($id)))){
			dieJsonSuccess('Success');
		}else{
			dieJsonError($db->getError());
		}
	}
	
	function save_image_orientation(){
		$this->db = Syspage::getdb();
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$post = Syspage::getPostedVar();
		$pObj=new Products();
		if(isset($post['do_submit']))  {
			$product_image = $pObj->getProductImageData($post['image']);
			if (isset($product_image)){
				/*require_once (CONF_INSTALLATION_PATH . 'public/includes/imagemanipulation.php');
				$img = new ImageManipulation();
				$filename= CONF_INSTALLATION_PATH . 'user-uploads/products/'.$product_image['image_file'];
				$ext = pathinfo($filename, PATHINFO_EXTENSION);
				$img->load($filename);
				$img->rotate_image(($post['rotation']), 'ffffff', 127);
				$img->save_image($filename, $ext);*/
				$filename= CONF_INSTALLATION_PATH . 'user-uploads/products/'.$product_image['image_file'];
				$size = getimagesize($filename);
				$arr = array('rotate'=>$post['rotation'],'width'=>$size[0],'height'=>$size[1],'x'=>0,'y'=>0);
				Utilities::rotateimage($post['rotation'],$filename);
				die(Utilities::getLabel('L_Image_with_orientation_saved'));
			}
		}
		
	}
	
}