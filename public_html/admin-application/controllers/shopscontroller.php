<?php
class ShopsController extends BackendController {
	
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,SHOPS);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Shops Management", Utilities::generateUrl("shops"));
    }
	
	protected function getSearchForm() {
        $frm=new Form('frmShopSearch','frmShopSearch');
		$frm->setFieldsPerRow(3);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$fld=$frm->addTextBox('Keyword', 'keyword','','',' class="medium"');
		$frm->addSelectBox('Active', 'active',array(1=>"Yes",0=>"No"),'' , 'class="small"','All');
		$frm->addSelectBox('Display Status', 'display',array(1=>"Yes",0=>"No"),'' , 'class="small"','All');
		$fld=$frm->addDateField('Date From', 'date_from', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld=$frm->addDateField('Date To', 'date_to', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchShops(this); return false;');
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
	
	function listShops($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
			$shopObj=new Shop();
            $post = Syspage::getPostedVar();
            $page = 1;
            if (isset($post['page']) && intval($post['page']) > 0) {
                $page = intval($post['page']);
            } else {
                $post['page'] = $page;
            }
            if (!empty($post)) {
                $this->set('srch', $post);
            }
           	$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
            $post['pagesize'] = $pagesize;
            $this->set('records', $shopObj->getShops($post));
            $this->set('pages', $shopObj->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $shopObj->getTotalRecords();
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
	function form($shop_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
        $shop_id = intval($shop_id);
		$this->b_crumb->add("Shop Setup");
		$this->set('breadcrumb', $this->b_crumb->output());
		$shopObj=new Shop();
		$frm = $this->getForm();
		if (!intval($shop_id)>0) {
           $this->notAuthorized();
		}
		
		 $data = $shopObj->getData($shop_id);
		$data["ua_state"]=$data["shop_state"];
		
		if ($data['shop_logo']!="")	{
		$frm->getField('shop_logo')->html_after_field = $frm->getField('shop_logo')->html_after_field.'<br/><div class="uploadedphoto"><img alt="" src="'. Utilities::generateUrl('image', 'shop_logo', array($data['shop_logo'],'thumb'),CONF_WEBROOT_URL) .'" id="lpic" /></div>';
		}
		
		if ($data['shop_banner']!="")	{
			$frm->getField('shop_banner')->html_after_field = $frm->getField('shop_banner')->html_after_field.'<br/><div class="uploadedphoto"><img alt="" src="'. Utilities::generateUrl('image', 'shop_banner', array($data['shop_banner'],'THUMB'),CONF_WEBROOT_URL) .'" id="bpic" /></div>';
		}
		
		$frm->fill($data);
		$frm->removeField($frm->getField('shop_user_id'));
			
		
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				if($post['shop_id'] != $shop_id){
					Message::addErrorMessage('Error: Invalid Request!!');
					Utilities::redirectUser(Utilities::generateUrl('shops'));
				}else{
					
					
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
					
					$shop_name = preg_replace('/\s+/', ' ',$post['shop_name']);
					$shop = $shopObj->getShops(array("name"=>$shop_name,"pagesize"=>1),1);
					if (($shop==true) && ($shop["shop_id"]!=$post["shop_id"])){
						Message::addErrorMessage(Utilities::getLabel('L_Shop_name_already_exists'));
						$error = true;
					}
					
					$pmObj=new Paymentmethods();
					$payment_method=$pmObj->getPaymentMethodByCode(CONF_PAPYAL_ADAPTIVE_KEY);
					$shop_paypal_account_verified = 0;
					if ($payment_method && $payment_method['pmethod_status']){
						$paObj = new Paypaladaptive_pay();
						if (!$paObj->verifyPayPalAccount($post['shop_payment_paypal_account'],$post['shop_payment_paypal_firstname'],$post['shop_payment_paypal_lastname'])){
							Message::addErrorMessage(Utilities::getLabel('L_Paypal_account_not_verified'));
							$error = true;
						}
						$shop_paypal = $shopObj->getShops(array("paypal"=>$post['shop_payment_paypal_account'],"pagesize"=>1),1);
						if (($shop_paypal==true) && ($shop_paypal["shop_id"]!=$post["shop_id"])){
							Message::addErrorMessage(Utilities::getLabel('L_Paypal_account_already_exists'));
							$error = true;
						}
						$shop_paypal_account_verified = 1;
					}
						
					if (!$error){
							
							if (Utilities::isUploadedFileValidImage($_FILES['shop_logo'])){
								if(!Utilities::saveImage($_FILES['shop_logo']['tmp_name'],$_FILES['shop_logo']['name'], $shop_logo, 'shops/logo/')){
			               		Message::addError($shop_logo);
    			   			}
								$post["shop_logo"]=$shop_logo;
    		    			}
					
							if (Utilities::isUploadedFileValidImage($_FILES['shop_banner'])){
								if(!Utilities::saveImage($_FILES['shop_banner']['tmp_name'],$_FILES['shop_banner']['name'], $shop_banner, 'shops/banner/')){
		               			Message::addError($shop_banner);
		    		   			}
								$post["shop_banner"]=$shop_banner;
    		    			}
							
							$post  = array_merge(array('shop_enable_cod_orders'=>0,'shop_featured'=>0),$post);
							$arr = array_merge($post,array('shop_name'=>$shop_name,"shop_paypal_account_verified"=>$shop_paypal_account_verified,'shop_logo'=>$shop_logo,'shop_banner'=>$shop_banner,'shop_user_id'=>$post["shop_id"]==0?$post['shop_user_id']:$data['shop_user_id']));
							
							if($shopObj->addUpdateShopInfo($arr)){
								Message::addMessage('Success: Shop details added/updated successfully.');
								Utilities::redirectUser(Utilities::generateUrl('shops'));
							}else{
								Message::addErrorMessage($shopObj->getError());
							}
					}
				}
			}
			$frm->fill($post);
		}
		
		
        $frm->fill($data);        
        $this->set('frm', $frm);
        $this->_template->render(true,true);
    }
	
    protected function getForm() {
		//$shop_id=$shop["shop_id"];
        $frm = new Form('frmShops');
		$frm->setExtra(' validator="ShopsfrmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('ShopsfrmValidator');
        $frm->addHiddenField('', 'shop_id','0','shop_id');
		
		$frm->addHtml('<h6>Basic Information</h6>', 'htmlNoteTop','','&nbsp;');
		$u=new User();
		$shop_owner_fld=$frm->addSelectBox('Shop Owner', 'shop_user_id',$u->getAssociativeArray(array("4","5")),'', 'class="input-xlarge"','');
	
		$fld=$frm->addRequiredField('Name', 'shop_name','', '', ' onKeyUp="Slugify(this.value,\'seo_url_keyword\',\'shop_id\')" ');
		$fld->requirements()->setLength(4,50);
		
		$fld=$frm->addRequiredField('URL Keywords', 'seo_url_keyword','', 'seo_url_keyword', ' class="input-xlarge"');
		$fld->html_after_field='<small>Do not use spaces, instead replace spaces with - and make sure the keyword is globally unique.</small>';
		
		$frm->addTextArea('Description', 'shop_description', '', 'shop_description', ' class="cleditor" rows="3"');
		
		$fld=$frm->addFileUpload('Shop Logo', 'shop_logo');
		$fld->html_before_field = '<div class="filefield"><span class="filename"></span>';
		$fld->html_after_field = '<label class="filelabel">Browse File</label></div><small class="small">Upload a .jpg, .gif or .png. This will be displayed in 165px x 165px on your store.</small>';
		
		
		$fld = $frm->addFileUpload('Shop Banner', 'shop_banner');
		$fld->html_before_field = '<div class="filefield"><span class="filename"></span>';
		$fld->html_after_field = '<label class="filelabel">Browse File</label></div><small class="small">Upload a .jpg, .gif or .png. This will be displayed in 1200px x 360px on your store.</small>';
		
		$fld=$frm->addCheckBox('Featured Shop', 'shop_featured',1);
		$fld->html_after_field='<span class="clear"></span><small>Featured Shops will be listed on Featured Shops Page. Featured Shops will get priority.</small>';
		
		if (Settings::getSetting("CONF_ENABLE_COD_PAYMENTS")){
			$fldALA=$frm->addCheckBox('<label>'.Utilities::getLabel('M_Enable_COD_Orders').'</label>', 'shop_enable_cod_orders','1','shop_enable_cod_orders');
			if (Settings::getSetting("CONF_COD_MIN_WALLET_BALANCE")>0){
				$frm->getField("shop_enable_cod_orders")->html_after_field='<br/><small>'.sprintf(Utilities::getLabel('M_Enable_COD_Text'),Utilities::displayMoneyFormat(Settings::getSetting("CONF_COD_MIN_WALLET_BALANCE"))).'</small>';
			}
		}
		
		$frm->addHtml('<h6>'.Utilities::getLabel('M_Shop_Address').' ('.sprintf(Utilities::getLabel('L_Shop_Address_Text'),Settings::getSetting("CONF_WEBSITE_NAME"),Utilities::getUrlScheme()).')</h6>', 'htmlShopAddress','','&nbsp;');
		$frm->addRequiredField('<label>'.Utilities::getLabel('F_Contact_Person_Name').'</label>', 'shop_contact_person');
		$fld_phn=$frm->addRequiredField('<label>'.Utilities::getLabel('M_Phone').'</label>', 'shop_phone');
		$fld_phn->requirements()->setRegularExpressionToValidate('^[\s()+-]*([0-9][\s()+-]*){5,20}$');
		$frm->addRequiredField('<label>'.Utilities::getLabel('L_Pickup_Dispatch_Address_1').'</label>', 'shop_address_line_1');
		$frm->addTextBox('<label>'.Utilities::getLabel('L_Pickup_Dispatch_Address_2').'</label>', 'shop_address_line_2');
		$frm->addRequiredField('<label>'.Utilities::getLabel('L_CITY').'</label>', 'shop_city');
		$frm->addRequiredField('<label>'.Utilities::getLabel('M_POSTCODE_ZIP').'</label>', 'shop_postcode');
		$countryObj=new Countries();
		$statesObj=new States();
		$fld_country=$frm->addSelectBox('<label>'.Utilities::getLabel('M_Country').'</label>', 'shop_country', $countryObj->getAssociativeArray(), Settings::getSetting("CONF_COUNTRY"), 'onchange="loadStates(this);"', Utilities::getLabel('M_Country'), 'ua_country')->requirements()->setRequired(true);
		$frm->addSelectBox('<label>'.Utilities::getLabel('M_State_County_Province').'</label>', 'ua_state', $statesObj->getAssociativeArray(), '', '', Utilities::getLabel('M_State_County_Province'), 'ua_state')->requirements()->setRequired(true);
	
	
		/*$fld=$frm->addCheckBox('Featured Shop', 'shop_featured',1);
		$fld->html_after_field='<span class="clear"></span><small>Featured Shops will be listed on Featured Shops Page. Featured Shops will get priority.</small>';
		
		$countryObj=new Countries();
		$statesObj=new States();
		$fld_country=$frm->addSelectBox(Utilities::getLabel('M_Country'), 'shop_country', $countryObj->getAssociativeArray(), '', 'onchange="loadStates(this);"', Utilities::getLabel('M_Country'), 'ua_country')->requirements()->setRequired(true);
		$frm->addSelectBox(Utilities::getLabel('M_State_County_Province'), 'ua_state', $statesObj->getAssociativeArray(), '', '', Utilities::getLabel('M_State_County_Province'), 'ua_state')->requirements()->setRequired(true);
		$frm->addRequiredField(Utilities::getLabel('M_Shop_City'), 'shop_city','', '', ' class="small"');*/
		
		
		
		$frm->addHtml('<h6>'.Utilities::getLabel('M_Shop_Section_2').'</h6>', 'htmlNote','','&nbsp;');
		
		$frm->addTextArea('<label>'.Utilities::getLabel('L_Payment_Policy').'</label>', 'shop_payment_policy', '', 'shop_payment_policy', ' class="height120" rows="3"');
		$frm->getField("shop_payment_policy")->html_after_field=Utilities::getLabel('L_Payment_Policy_Text');
		$frm->addTextArea('<label>'.Utilities::getLabel('M_Delivery_Policy').'</label>', 'shop_delivery_policy', '', 'shop_delivery_policy', ' class="height120" rows="3"');
		$frm->getField("shop_delivery_policy")->html_after_field='<small>'.Utilities::getLabel('M_Delivery_Policy_Text').'</small>';
		$frm->addTextArea('<label>'.Utilities::getLabel('L_Refund_Policy').'</label>', 'shop_refund_policy', '', 'shop_refund_policy', ' class="height120" rows="3"');
		$frm->getField("shop_refund_policy")->html_after_field=Utilities::getLabel('L_Refund_Policy_Text');
		$frm->addTextArea('<label>'.Utilities::getLabel('M_Additional_Information').'</label>', 'shop_additional_info', '', 'shop_additional_info', ' class="height120" rows="3"');
		$frm->getField("shop_additional_info")->html_after_field='<small>'.Utilities::getLabel('M_Additional_Information_Text').'</small>';
		$frm->addTextArea('<label>'.Utilities::getLabel('L_SELLER_INFORMATION').'</label>', 'shop_seller_info', '', 'shop_seller_info', ' class="height120" rows="3"');
		$frm->getField("shop_seller_info")->html_after_field='<small>'.Utilities::getLabel('M_Seller_Information_Text').'</small>';
		$frm->addHtml('<h6>'.Utilities::getLabel('M_SECTION_SHOP_SEO_INFORMATION').'</h6>', 'htmlNote','','&nbsp;');
		
		$frm->addTextBox('<label>'.Utilities::getLabel('M_Meta_Tag_Title').'</label>', 'shop_page_title', '', 'shop_page_title', '');
		$frm->addTextArea('<label>'.Utilities::getLabel('M_Meta_Tag_Keywords').'</label>', 'shop_meta_keywords', '', 'shop_meta_keywords', ' rows="3"');
		$frm->addTextArea('<label>'.Utilities::getLabel('M_Meta_Tag_Description').'</label>', 'shop_meta_description', '', 'shop_meta_description', ' rows="3"');
		
		$pmObj=new Paymentmethods();
		$payment_method=$pmObj->getPaymentMethodByCode(CONF_PAPYAL_ADAPTIVE_KEY);
		
		if ($payment_method && $payment_method['pmethod_status']){
		$frm->addHtml('<span class="panelTitleHeading">'.Utilities::getLabel('M_Payment_Information_Text').'</span>', 'htmlNote','','&nbsp;');
		$frm->addRequiredField('<label>'.Utilities::getLabel('M_First_Name').'</label>', 'shop_payment_paypal_firstname', '', 'shop_payment_paypal_firstname', '');
		$frm->getField("shop_payment_paypal_firstname")->html_after_field='<small>'.Utilities::getLabel('M_Please_enter_your_paypal_account_first_name').'</small>';
		$frm->addRequiredField('<label>'.Utilities::getLabel('M_Last_Name').'</label>', 'shop_payment_paypal_lastname', '', 'shop_payment_paypal_lastname', '');
		$frm->getField("shop_payment_paypal_lastname")->html_after_field='<small>'.Utilities::getLabel('M_Please_enter_your_paypal_account_last_name').'</small>';
		
		$frm->addRequiredField('<label>'.Utilities::getLabel('M_Paypal_account_details').'</label>', 'shop_payment_paypal_account', '', 'shop_payment_paypal_account', '');
		$frm->getField("shop_payment_paypal_account")->html_after_field='<small>'.Utilities::getLabel('M_paypal_account_text').'</small>';
	}
		$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="25%"');
        return $frm;
    }
	
	function update_shop_status() {
		if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
		$post = Syspage::getPostedVar();
		$shopObj=new Shop();	
		$shop_id = intval($post['id']);
        $shop = $shopObj->getData($shop_id);
        if($shop==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		$data_to_update = array('shop_status'=>!$shop['shop_status']);
		if($shopObj->updateShopStatus($shop_id,$data_to_update)){
			Message::addMessage('Success: Status has been modified successfully.');
			$arr = array('status'=>1, 'msg'=>Message::getHtml(), 'linktext'=>(($shop['shop_status'] == 1)?'Active':'Inactive'));
			die(convertToJson($arr));
		}else{
			Message::addErrorMessage($shopObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	function delete() {
        if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
        $post = Syspage::getPostedVar();
		$shopObj=new Shop();	
		$shop_id = intval($post['id']);
        $shop = $shopObj->getData($shop_id);
		if($shop==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		if($shopObj->delete($shop_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($shopObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	function reports($shop_id){
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),SHOPS)) {
             die(Admin::getUnauthorizedMsg());
        }
		$shopObj=new Shop();
		$this->set('arr_listing', $shopObj->getShopReports($shop_id));
		$this->_template->render();
	}
	
	function delete_report() {
        if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
        $post = Syspage::getPostedVar();
		$shopObj=new Shop();	
		$shop_id = intval($post['id']);
		if($shopObj->delete_report($shop_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($shopObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	function autocomplete(){
	    $post = Syspage::getPostedVar();
		$json = array();
		$shopObj=new Shop();
        $shops=$shopObj->getShops(array("keyword"=>urldecode($post["keyword"]),"pagesize"=>10));
		foreach($shops as $skey=>$sval){
			$json[] = array(
					'data' => $sval['shop_id'],
					'value'      => strip_tags(htmlentities($sval['shop_name'], ENT_QUOTES, 'UTF-8'))
				);
		}
		$sort_order = array();
		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}
		array_multisort($sort_order, SORT_ASC, $json);
		$arr["suggestions"]=$json;
		echo json_encode($arr);
		//echo json_encode($json);
	}	
	
}