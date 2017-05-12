<?php
class Import_exportController extends CommonController
{
	function __construct($model,$controller,$action)
	{
		parent::__construct($model, $controller, $action);
		$this->db = Syspage::getdb();
		if ($action!="update_product_image"){
			Utilities::checkLogin();
			$userObj=new User();
			$this->user_details=$userObj->getUserById($this->getLoggedUserId());
			$this->shop_id=	$this->user_details['shop_id'];
			$this->user_id=	$this->user_details['shop_user_id'];
			if(!$userObj->canAccessSupplierDashboard($this->getLoggedUserId())){
				Utilities::redirectUser(Utilities::generateUrl('account','supplier_approval_form'));
			}
			$this->set('user_details',$this->user_details);	
			$this->set('controller',$controller);
			$this->set('action',$action);
			$display_buyer_supplier_tab="S";
			$_SESSION["buyer_supplier_tab"]=$display_buyer_supplier_tab;
			$this->set('buyer_supplier_tab',$display_buyer_supplier_tab);
			$this->Importexport=new Importexport($this->shop_id);		
		}
	}
	function default_action($type='export')
	{		
		$frm=$this->getForm($type);
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(intVal($this->shop_id)==0 || intVal($this->user_id)==0){
				Message::addErrorMessage(Utilities::getLabel('L_UserId_Or_UserShop_Does_Not_Exist'));
				Utilities::redirectUser(Utilities::generateUrl('import_export', 'default_action',array($type)));
			}else{
				if(!$frm->validate($post)){
					Message::addErrorMessage($frm->getValidationErrors());
				}					
				switch($post["mode"]){
					case 'settings':
					$this->Importexport->editSetting('export_import',$post,$this->shop_id);
					Message::addMessage(Utilities::getLabel('L_Setting_Updated_Successfully'));
					Utilities::redirectUser(Utilities::generateUrl('import_export', 'default_action',array($type)));
					break;
					case 'import': 	
					if ($this->Importexport->isUploadedFileValidFile($_FILES['upload'])){ 						
						if(!Utilities::saveFile($_FILES['upload']['tmp_name'],$_FILES['upload']['name'], $attachment, 'importexport/')){
							Message::addError($attachment);
						}
/* $settingsObj=new Settings();
$settingsObj->backupDatabase('before_import'); */
$post["attachment"]=$attachment;
//$incremental = ($post['incremental']) ? true : false;
$incremental = true;
if ($this->Importexport->upload($_FILES['upload']['tmp_name'],$incremental,$this->user_id)===true) {
	Message::addMessage(Utilities::getLabel('M_YOUR_ACTION_PERFORMED_SUCCESSFULLY'));
	Utilities::redirectUser(Utilities::generateUrl('import_export', 'default_action',array($type)));
}
}else{
	Message::addErrorMessage(Utilities::getLabel('L_Invalid_File_Upload'));
}						
break;
case 'export':						
$this->download();						
break;						
}				
}
}
$res=$this->Importexport->getSetting('export_import',$this->shop_id);	
$frm->fill($res);		
$this->set('tabSelected',$type);
$this->set('frm',$frm);
$this->_template->render();		
}
private function getForm($type)
{ 
	$frm = new Form('frmSettings','frmSettings');
	$frm->addHiddenField('', 'mode',$type);		
	switch($type){
		case 'export':
		$fld=$frm->addHtml('','','<span class="panelTitleHeading">'.Utilities::getLabel('L_Export_Data_XLSX_Spreadsheet_File.').'</span><small>'.Utilities::getLabel('L_Select_What_You_Want_Export').'</small>');
		$fld->merge_caption=true;
		$arr=array('c'=>Utilities::getLabel('L_Categories').' ('.Utilities::getLabel('L_Including_Category_Data_Filter').')','p'=>Utilities::getLabel('L_Products').' ('.Utilities::getLabel('M_Including_Product_Options_Specials_Discounts_Attributes_Filters_Shipping').')','o'=>Utilities::getLabel('L_Option_Definitions'),'a'=>Utilities::getLabel('L_Attribute_Definitions'),'f'=>Utilities::getLabel('L_Filter_Definitions'),'sd'=>Utilities::getLabel('L_Shipping_Durations'),'sc'=>Utilities::getLabel('L_Shipping_Companies')/* ,'s'=>'Shops' */,'b'=>Utilities::getLabel('L_Brands'),'cn'=>Utilities::getLabel('L_Countries'),'sq'=>Utilities::getLabel('L_Products_Stock_And_SKU'));
		$frm->addRadioButtons('','export_type',$arr,'p','1','class="field4"');
		$frm->addHtml('','',Utilities::getLabel('M_Select_Data_Range').'<br /><small class="showHide">('.Utilities::getLabel('L_Optional_Leave_Empty').')</small>');	
		//$frm->addRadioButtons('','range_type',array('id'=>Utilities::getLabel('L_By_Id_Range'),'page'=>Utilities::getLabel('L_By_Batches')),'','','','class="field4 showHide range_type" onClick="loadRangeType(this)"');
		$frm->addRadioButtons('','range_type',array('id'=>Utilities::getLabel('L_By_Id_Range'),'page'=>Utilities::getLabel('L_By_Batches')),'','1','','class="field4 showHide range_type" onClick="loadRangeType(this)"');
		$frm->addTextBox('<span id="minLabel">'.Utilities::getLabel('L_Start_Id').'</span>','min','','min','class="showHide"');
		$frm->addTextBox('<span id="maxLabel">'.Utilities::getLabel('L_End_ID').'</span>','max','','max','class="showHide"');
		break;
		case 'import':
		$fld=$frm->addHtml('','','<span class="panelTitleHeading">'.Utilities::getLabel('L_Import_XlS_XLSX_Or_ODS_File').'</span><small>'.Utilities::getLabel('L_Spreadsheet_Can_Have_Only_Products').'<br />'.Utilities::getLabel('L_Do_Export_First_For_Format').'</small>');
		$fld->merge_caption=true;
		$frm->addRadioButtons('','incremental',array(1=>Utilities::getLabel('L_Yes')." (".Utilities::getLabel('L_Update_And_Or_Add_Data').")",0=>Utilities::getLabel('L_No')." (".Utilities::getLabel('L_Delete_All_Old_Data').")"),'','1','class="field4"');				
		$frm->addHtml('','',Utilities::getLabel('L_File_To_Be_Uploaded'));
		$fld=$frm->addFileUpload('', 'upload', 'upload', '');
/* $fld->html_before_field='<div class="filefield"><span class="filename"></span>';
$fld->html_after_field='<label class="filelabel">Browse File</label></div>'; */
break;
case 'settings':
$fld=$frm->addHtml('','','<span class="panelTitleHeading">'.Utilities::getLabel('L_Bulk_Import_Export_Settings').'</span>');
$fld->merge_caption=true;			
$fld=$frm->addCheckBox('','export_import_settings_use_brand_id','1','export_import_settings_use_brand_id','class="field4"');
$fld->html_after_field=" Use brand_id instead of brand name in worksheets 'Products' ";
$fld=$frm->addCheckBox('','export_import_settings_use_option_id','1','export_import_settings_use_option_id','class="field4"');
$fld->html_after_field=" Use option_id instead of option name in worksheets 'ProductOption' and 'ProductOptionValues' ";
$fld=$frm->addCheckBox('','export_import_settings_use_option_value_id','1','export_import_settings_use_option_value_id','class="field4"');
$fld->html_after_field=" Use option_value_id instead of option_value name in worksheet 'ProductOptionValues'  </small>";
$fld=$frm->addCheckBox('','export_import_settings_use_attribute_group_id','1','export_import_settings_use_attribute_group_id','class="field4"');
$fld->html_after_field=" Use attribute_group_id instead of attribute_group name in worksheet 'ProductAttributes'";
$fld=$frm->addCheckBox('','export_import_settings_use_attribute_id','1','export_import_settings_use_attribute_id','class="field4"');
$fld->html_after_field=" Use attribute_id instead of attribute name in worksheet 'ProductAttributes' ";	
$fld=$frm->addCheckBox('','export_import_settings_use_filter_group_id','1','export_import_settings_use_filter_group_id','class="field4"');
$fld->html_after_field=" Use filter_group_id instead of filter_group name in worksheets 'ProductFilters' and 'CategoryFilters' ";
$fld=$frm->addCheckBox('','export_import_settings_use_filter_id','1','export_import_settings_use_filter_id','class="field4"');
$fld->html_after_field=" Use filter_id instead of filter name in worksheets 'ProductFilters' and 'CategoryFilters' ";
$fld=$frm->addCheckBox('','export_import_settings_use_ship_country_id','1','export_import_settings_use_ship_country_id','class="field4"');
$fld->html_after_field=" Use country_id instead of country name in worksheets 'Products' and 'ProductShippingRates' ";
$fld=$frm->addCheckBox('','export_import_settings_use_ship_company_id','1','export_import_settings_use_ship_company_id','class="field4"');
$fld->html_after_field=" Use company_id instead of company name in worksheets 'ProductShippingRates' ";
$fld=$frm->addCheckBox('','export_import_settings_use_ship_duration_id','1','export_import_settings_use_ship_duration_id','class="field4"');
$fld->html_after_field=" Use duration_id instead of duration name in worksheets 'ProductShippingRates' ";
break;
}
$btnLable=($type=='settings')?Utilities::getLabel('L_Update'):ucwords(Utilities::getLabel('L_'.$type));		
$frm->addSubmitButton('&nbsp;','btn_submit',$btnLable);
$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="siteForm"');
$frm->setLeftColumnProperties('width="25%" valign="baseline"');
//$frm->setAction(Utilities::generateUrl('configurations', 'update'));
$frm->setExtra('class="siteForm"');
$frm->setJsErrorDisplay('afterfield');
return $frm;
}
function download()
{
	$post=Syspage::getPostedVar();
	$export_type = $post['export_type'];		
	if($post['range_type']=='page'){
		$post['max']=($post['max']>1)?$post['max']:2;
	}
	switch ($export_type) {
		case 'c':
		case 'p':
		$min = null;
		if (isset( $post['min'] ) && ($post['min']!='')&& (intval($post['min'])>0)) {
			$min = intval($post['min']);
		}
		$max = null;
		if (isset( $post['max'] ) && ($post['max']!='')&& (intval($post['max'])>1)) {
			$max = intval($post['max']);
		}
		if (($min==null) || ($max==null)) {
			$this->Importexport->download($export_type, null, null, null, null,$this->user_id);
		} else if ($post['range_type'] == 'id') {
			$this->Importexport->download($export_type, null, null, $min, $max,$this->user_id);
		} else { 										
			$this->Importexport->download($export_type, $min*($max-1-1), $min, null, null,$this->user_id);
		}
		break;
		case 'o': 
		$this->Importexport->download('o', null, null, null, null);
		break;
		case 'a':
		$this->Importexport->download('a', null, null, null, null);
		break;
		case 'f': 
		if ($this->Importexport->existFilter()) {
			$this->Importexport->download('f', null, null, null, null);
			break;
		}
		break;
		case 'sd':
		$this->Importexport->download('sd', null, null, null, null);
		break;
		case 'sc':
		$this->Importexport->download('sc', null, null, null, null);
		break;
		case 'sq':
		$this->Importexport->download('sq', null, null, null, null,$this->user_id);
		break;	
		case 's':
		$this->Importexport->download('s', null, null, null, null);
		break;
		case 'b':
		$this->Importexport->download('b', null, null, null, null);
		break;
		case 'cn':
		$this->Importexport->download('cn', null, null, null, null);
		break;			
		default:
		break;
	}
}
function update_product_image()
{
	$Importexport=new Importexport();
	$row=$Importexport->getProductTempImages(150);		
	foreach($row as $val){			
		$image_name=$Importexport->getImageName($val['image_file']);			
		if($image_name!=''){
			$imgArr = array();
			$imgArr['image_prod_id'] = $val['image_prod_id'];
			$imgArr['image_default'] = $val['image_default'];
			$imgArr['image_temp'] = $val['image_temp'];
			$imgArr['image_file'] = $image_name;
			if($val['old_image_id']>0){
				$imgArr['image_id'] =$val['old_image_id'];
			}
			$Importexport->updateProductImages($imgArr);				
			$imgTemp=array('image_id'=>$val['image_id'],'image_downloaded'=>1,'image_scrapped'=>date('Y-m-d H:i:s'));
		}else{
			$imgTemp=array('image_id'=>$val['image_id'],'image_scrapped'=>date('Y-m-d H:i:s'));
		}						
		$Importexport->updateProductTempImages($imgTemp);			
	}
	echo "Done";
}
}
