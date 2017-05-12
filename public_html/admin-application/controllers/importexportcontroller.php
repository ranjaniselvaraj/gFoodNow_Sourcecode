<?php
class ImportexportController extends CommonController{
	
	function __construct($model, $controller, $action) {
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),EXPORT_IMPORT)) {
            die(Admin::getUnauthorizedMsg());
        }		
        parent::__construct($model, $controller, $action);	
		$this->Importexport=new Importexport();	
    }
	
	function default_action($type='export')
	{			
		//$this->Importexport->upload($post["attachment"],$incremental);		
		$frm=$this->getForm($type);
		$post = Syspage::getPostedVar();
		
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{						
				switch($post["mode"]){
					case 'settings':
						$this->Importexport->editSetting('export_import',$post);
						Message::addMessage('Setting updated successfully.');
						Utilities::redirectUser(Utilities::generateUrl('importexport', 'default_action',array($type)));
					break;
					case 'import': 	 			
						if ($this->Importexport->isUploadedFileValidFile($_FILES['upload'])){							
							if(!Utilities::saveFile($_FILES['upload']['tmp_name'],$_FILES['upload']['name'], $attachment, 'importexport/')){
		               			Message::addError($attachment);
    		   				}
							
							$settingsObj=new Settings();
							$settingsObj->backupDatabase('before_import');
							
							$post["attachment"]=$attachment;
							$incremental = ($post['incremental']) ? true : false;											
							if ($this->Importexport->upload($_FILES['upload']['tmp_name'],$incremental)===true) {
								Message::addMessage('Import successfully.');
								Utilities::redirectUser(Utilities::generateUrl('importexport', 'default_action',array($type)));
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
		
		$res=$this->Importexport->getSetting('export_import');	
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
				$frm->addHtml('','','Export requested data to a XLSX spreadsheet file.<br /><small>Select what data you want to export:</small>');
				$arr=array('c'=>'Categories (including category data and filters)','p'=>'Products (including product data, options, specials, discounts, attributes , filters and shipping rates)','o'=>'Option definitions','a'=>'Attribute definitions','f'=>'Filter definitions','sd'=>'Shipping Durations','sc'=>'Shipping Companies'/* ,'s'=>'Shops' */,'b'=>'Brands','cn'=>'Countries');
				$frm->addRadioButtons('','export_type',$arr,'p','1','class="field4"');
				$frm->addHtml('','','Please select the data range you want to export:<br /><small class="showHide">(Optional, leave empty if not needed)</small>');	
				$frm->addRadioButtons('','range_type',array('id'=>'By id range','page'=>'By batches'),'','1','','class="field4 showHide range_type" onClick="loadRangeType(this)"');
				$frm->addTextBox('<span id="minLabel">Start id</span>','min','','min','class="showHide"');
				$frm->addTextBox('<span id="maxLabel">End id</span>','max','','max','class="showHide"');
			break;
			
			case 'import':
				$frm->addHtml('','','Import from a XLS, XLSX or ODS spreadsheet file<br /><small>Spreadsheet can have categories, products, attribute definitions, option definitions, filter definitions, Shipping Durations or Shipping Companies.<br /> Do an Export first to see the exact format of the worksheets!</small>');
				$frm->addRadioButtons('','incremental',array(1=>"Yes (Update and/or add data)",0=>"No (Delete all old data before Import) "),1,1,'class="field4"');				
				$frm->addHtml('','','File to be uploaded');
				$fld=$frm->addFileUpload('', 'upload', 'upload', '');
				$fld->html_before_field='<div class="filefield"><span class="filename"></span>';
				$fld->html_after_field='<label class="filelabel">Browse File</label></div>';
			break;
				
			case 'settings':
				$fld=$frm->addCheckBox('','export_import_settings_use_collection_id','1','export_import_settings_use_collection_id','class="field4"');
				$fld->html_after_field=" Use collection_id instead of collection Name in worksheets 'categoryCollections' ";
				
				$fld=$frm->addCheckBox('','export_import_settings_use_added_by_id','1','export_import_settings_use_added_by_id','class="field4"');
				$fld->html_after_field=" Use added_by_id instead of added by (user name) in worksheets 'Products' ";
				
				$fld=$frm->addCheckBox('','export_import_settings_use_brand_id','1','export_import_settings_use_brand_id','class="field4"');
				$fld->html_after_field=" Use brand_id instead of brand name in worksheets 'Products' ";
				
				$fld=$frm->addCheckBox('','export_import_settings_use_shop_id','1','export_import_settings_use_shop_id','class="field4"');
				$fld->html_after_field=" Use shop_id instead of shop name in worksheets 'Products' ";
				
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
				
				/* $fld=$frm->addCheckBox('','export_import_settings_use_state_id','1','export_import_settings_use_state_id','class="field4"');
				$fld->html_after_field=" Use state_id instead of state name in worksheets 'Shops' "; */
				
				$fld=$frm->addCheckBox('','export_import_settings_use_ship_company_id','1','export_import_settings_use_ship_company_id','class="field4"');
				$fld->html_after_field=" Use company_id instead of company name in worksheets 'ProductShippingRates' ";
				
				$fld=$frm->addCheckBox('','export_import_settings_use_ship_duration_id','1','export_import_settings_use_ship_duration_id','class="field4"');
				$fld->html_after_field=" Use duration_id instead of duration name in worksheets 'ProductShippingRates' ";
				
				
				$fld=$frm->addCheckBox('','export_import_settings_use_export_cache','1','export_import_settings_use_export_cache','class="field4"');
				$fld->html_after_field=" Use phpTemp cache for large Exports (will be slightly slower)  ";
				
				$fld=$frm->addCheckBox('','export_import_settings_use_import_cache','1','export_import_settings_use_import_cache','class="field4"');
				$fld->html_after_field=" Use phpTemp cache for large Imports (will be slightly slower) ";					
			break;
		}
		
		$btnLable=($type=='settings')?'Update':ucwords($type);
		$frm->addSubmitButton('&nbsp;','btn_submit',$btnLable);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="25%" valign="baseline"');
		
		//$frm->setAction(Utilities::generateUrl('configurations', 'update'));
		$frm->setExtra('class="web_form"');
		$frm->setJsErrorDisplay('afterfield');
		return $frm;
	}		
	
	function download()
	{
		$post=Syspage::getPostedVar();
		$export_type = $post['export_type'];		
		$offset=0;
		$limit=1000;
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
				if (isset( $post['max'] ) && ($post['max']!='') && (intval($post['max'])>1)) {
					$max = intval($post['max']);
				}
				
				if (($min==null) || ($max==null)) {
					$this->Importexport->download($export_type, $offset, $limit, null, null);
				} else if ($post['range_type'] == 'id') {
					$this->Importexport->download($export_type, null, null, $min, $max);
				} else { 										
					$this->Importexport->download($export_type, $min*($max-1-1), $min, null, null);
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
	
	function validateDownloadForm()
	{
		$exportImportSettings=$this->Importexport->getSetting('export_import');
		if(empty($exportImportSettings)){
			//Message::addErrorMessage(Utilities::getLabel(''));
			return false;
		}else{
			if(!$exportImportSettings['export_import_settings_use_option_id']){
				
			}
		}	
	}
}