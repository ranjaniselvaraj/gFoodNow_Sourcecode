<?php
class BrandsController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,BRANDS);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Brands Management", Utilities::generateUrl("brands"));
    }
	
	protected function getSearchForm() {
        $frm=new Form('frmBrandSearch','frmBrandSearch');
		$frm->setFieldsPerRow(3);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addTextBox('Keyword', 'keyword','','',' class="small"');
		$frm->addSelectBox('Active', 'status',array(1=>"Yes",0=>"No"),'' , 'class="small"','All');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchBrands(this); return false;');
        return $frm;
    }
	
	function default_action() {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $frm = $this->getSearchForm();
        $this->set('frmPost', $frm);
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listBrands($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
			$bObj=new Brand();
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
            $this->set('records', $bObj->getBrands($post));
            $this->set('pages', $bObj->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $bObj->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
	
    function form($brand_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("Brands Setup", Utilities::generateUrl("brands"));
		$this->set('breadcrumb', $this->b_crumb->output());
		$bObj=new Brand();
        $brand_id = intval($brand_id);
        $frm = $this->getForm();
        if ($brand_id > 0) {
            $data = $bObj->getData($brand_id);
            $frm->fill($data);
        }
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){	
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
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
					if (!$error){
							if($bObj->addUpdate($post)){
								Message::addMessage('Success: Brand details added/updated successfully.');
								Utilities::redirectUser(Utilities::generateUrl('brands'));
							}else{
								Message::addErrorMessage($bObj->getError());
							}
					}
				}
			$frm->fill($post);
		}
        $this->set('frm', $frm);
        $this->_template->render(true,true);
    }
	
    protected function getForm() {
        $frm = new Form('frmBrands');
		$frm->setExtra(' validator="BrandsfrmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('BrandsfrmValidator');
		$frm->addHtml('<h6>Section 1: Basic Information about Brand.</h6>', 'htmlNote','','&nbsp;');
        $frm->addHiddenField('', 'brand_id',0,'brand_id');
		$frm->addRequiredField('Name', 'brand_name','', '', ' class="input-xlarge" onKeyUp="Slugify(this.value,\'seo_url_keyword\',\'brand_id\')"');
		$fld=$frm->addRequiredField('URL Keywords', 'seo_url_keyword','', 'seo_url_keyword', ' class="input-xlarge"');
		$fld->html_after_field='<small>Do not use spaces, instead replace spaces with - and make sure the keyword is globally unique.</small>';
		
		$fld=$frm->addTextArea('Description', 'brand_description', '', 'brand_description', ' class="cleditor" rows="3"');
		$fld->html_after_field='<div id="brand_description_editor"></div>';
		$frm->addHtml('<h6>Section 2: SEO/Meta Data (Optional)</h6>', 'htmlNote','','&nbsp;');
		$frm->addTextBox('Page Title', 'brand_meta_title','','brand_meta_title','class="field95Width"');
		$frm->addTextArea('Meta Keywords', 'brand_meta_keywords','','brand_meta_keywords','class="fieldtextAreaBig" cols="112"');
		$frm->addTextArea('Meta Description', 'brand_meta_description','','brand_meta_description','class="fieldtextAreaBig" cols="112"');
		$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		//$frm->setLeftColumnProperties('width="20%"');
        return $frm;
    }
	function update_brand_status() {
		if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
		$post = Syspage::getPostedVar();
		$bObj=new Brand();	
        $brand_id = intval($post['id']);
        $brand = $bObj->getData($brand_id);
        if($brand==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		$data_to_update = array('brand_status'=>!$brand['brand_status']);
		if($bObj->updateBrandStatus($brand_id,$data_to_update)){
			Message::addMessage('Success: Status has been modified successfully.');
			$arr = array('status'=>1, 'msg'=>Message::getHtml(), 'linktext'=>(($brand['brand_status'] == 1)?'Active':'Inactive'));
			die(convertToJson($arr));
		}else{
			Message::addErrorMessage($bObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	function delete() {
        if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
        $post = Syspage::getPostedVar();
		$bObj=new Brand();	
        $brand_id = intval($post['id']);
        $brand = $bObj->getData($brand_id);
		if($brand==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		if($bObj->delete($brand_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($bObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	
	function autocomplete(){
	    $post = Syspage::getPostedVar();
		$json = array();
		$bObj=new Brand();
        $brands=$bObj->getBrands(array("keyword"=>urldecode($post["keyword"]),"pagesize"=>10));
		foreach($brands as $bkey=>$bval){
			$json[] = array(
					'id' => $bval['brand_id'],
					'name'      => strip_tags(htmlentities($bval['brand_name'], ENT_QUOTES, 'UTF-8'))
				);
		}
		$sort_order = array();
		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}
		array_multisort($sort_order, SORT_ASC, $json);
		echo json_encode($json);
	}
    
}