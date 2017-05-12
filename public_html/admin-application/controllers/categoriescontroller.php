<?php
class CategoriesController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,PRODUCTCATEGORIES);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Product Categories Management", Utilities::generateUrl("categories"));
    }
	
	protected function getSearchForm() {
        $frm=new Form('frmCategoriesSearch','frmCategoriesSearch');
		$frm->setFieldsPerRow(3);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('GET');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addTextBox('Keyword', 'keyword','','',' class="small"');
		$frm->addSelectBox('Active', 'status',array(1=>"Yes",0=>"No"),'' , 'class="small"','All');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->addHiddenField('', 'parent', 0);
		$frm->setOnSubmit('searchCategories(this); return false;');
        return $frm;
    }
	
	function default_action($parent_id=0) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $frm = $this->getSearchForm();
		$frm->fill(array('parent'=>$parent_id));
        $this->set('frmPost', $frm);
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listCategories($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
			$catObj=new Categories();
            $post = Syspage::getPostedVar();
			$post['type']=1;
            $page = 1;
            if (isset($post['page']) && intval($post['page']) > 0) {
                $page = intval($post['page']);
            } else {
                $post['page'] = $page;
            }
            $this->set('srch', $post);
           	$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
            $post['pagesize'] = $pagesize;
            $this->set('records', $catObj->getCategories($post));
            $this->set('pages', $catObj->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $catObj->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
			$this->set('category_structure', $catObj->funcGetCategoryStructure($post['parent']));
            $this->_template->render(false, false);
        }
    }
	
	function form($category_id,$parent) {
        if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
		
		$catObj=new Categories();
        $category_id = intval($category_id);
		$post = Syspage::getPostedVar();
        if ($category_id > 0) {
            $data = $catObj->getData($category_id);
			$categories=$catObj->getCatgeoryTreeStructure();
			$data["path"]=strip_tags(html_entity_decode($categories[$data["parent"]], ENT_QUOTES, 'UTF-8'));
			$data["category_parent"]=$data["parent"];
			$filters = $catObj->getCategoryFilters($category_id);
			$data['category_filters'] = array();
			$fgObj=new Filtergroupoptions();
			foreach ($filters as $filter_id) {
				$filter_info = $fgObj->getData($filter_id);
				if ($filter_info) {
					$data['category_filters'][] = array(
						'filter_id' => $filter_info['filter_id'],
						'name'      => $filter_info['filter_group_name'] . ' &gt; ' . $filter_info['filter_name']
					);
				}
			}
        }
		$frm = $this->getForm($parent,$data);
        $frm->fill($data);
		
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				
					if (($post["category_id"]>0) && ($post["category_parent"]>0)) {
							$categoriesArray = $catObj->checkParentCatgeory($category_id,1,0,$post["category_parent"]); 
							if(in_array($post["category_parent"],$categoriesArray)){
								Message::addErrorMessage('Category parent can\'t be assigned to self sub category.');
								$error=true;
							} 
							
					}
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
						if (Utilities::isUploadedFileValidImage($_FILES['category_file'])){
							if(!Utilities::saveImage($_FILES['category_file']['tmp_name'],$_FILES['category_file']['name'], $category_file, 'categories/')){
			               		Message::addError($category_file);
    			   			}
							$post["category_file"]=$category_file;
    		    		}
						$arr=array_merge($post,array("category_type"=>1,"category_slug"=>Utilities::slugify($post["category_name"])));
						if($catObj->addUpdate($arr)){
							Message::addMessage('Success: Category details added/updated successfully.');
							Utilities::redirectUser(Utilities::generateUrl('categories','default_action',array($post["category_parent"])));
						}else{
							Message::addErrorMessage($catObj->getError());
						}
					}
				
			}
			$frm->fill($post);
		}
        $this->set('frm', $frm);
		$this->set('parent',$parent);
		$category_structure = $catObj->funcGetCategoryStructure($parent);
		foreach($category_structure as $catkey=>$catVal){
			$this->b_crumb->add($catVal["category_name"], Utilities::generateUrl("categories","default_action",array($catVal["category_id"])));
		}
		$this->b_crumb->add('Category Setup', Utilities::generateUrl());
		$this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render(true,true,'',false,true);
    }
    protected function getForm($parent_id,$info) {
        $parent_id = intval($parent_id);
		$category_id=$info["category_id"];
        $frm = new Form('frmCategory');
        $frm->addHtml('<h6>Section 1: Basic Information about Category.</h6>', 'htmlNote','','&nbsp;');
        $frm->addHiddenField('', 'category_parent');
        $fld1 = $frm->addHiddenField('', 'category_id',0,'category_id');
        $fld1->requirements()->setInt(true);
        $fld2 = $frm->addTextBox('Category Name', 'category_name','','','onKeyUp="Slugify(this.value,\'seo_url_keyword\',\'category_id\')"');
        $fld2->requirements()->setRequired(true);
		
		$fld=$frm->addRequiredField('URL Keywords', 'seo_url_keyword','', 'seo_url_keyword', ' class="input-xlarge"');
		$fld->html_after_field='<small>Do not use spaces, instead replace spaces with - and make sure the keyword is globally unique.</small>';
		
		
		$fld=$frm->addTextArea('Description', 'category_description', '', 'category_description', ' class="cleditor" rows="3"');
		$fld->html_after_field='<div id="category_description_editor"></div>';
        
		/*$fld = $frm->addFileUpload('Category Image', 'category_file');
		$fld->html_before_field='<div class="filefield"><span class="filename"></span>';
		if ($category_id>0){
			$fld->html_after_field = '<label class="filelabel">Browse File</label></div><p><b>Current Image:</b><br /><img src="'.Utilities::generateUrl('image', 'category', array('THUMB',$info["category_file"]),CONF_WEBROOT_URL).'" /></p>';
		}else{
			$fld->html_after_field ='<label class="filelabel">Browse File</label></div>';
		}*/
		
		$fld = $frm->addTextBox('Category Parent', 'path');
		
        $category_filters = '';
		foreach ($info["category_filters"] as $category_filter) { 
				$category_filters.='<div id="category-filter'.$category_filter['filter_id'].'"><i class="remove_filter remove_param"><img src="'.CONF_WEBROOT_URL.'images/admin/closelabels.png"/></i>'.$category_filter['name'].'<input type="hidden" name="category_filter[]" value="'. $category_filter['filter_id'].'" /></div>';
		}
		$fld = $frm->addTextBox('Category Filters', 'filter');
		$fld->html_after_field = '<div id="category-filter" class="well well-sm" style="height: 150px; overflow: auto;">'.$category_filters.'</div>';
		 
        $frm->addTextBox('Display Order', 'category_display_order','','',' class="small"');
		//$frm->addCheckBox('Featured', 'category_featured', 1);
		
		$frm->addHtml('<h6>Section 2: SEO/Meta Data (Optional)</h6>', 'htmlNote','','&nbsp;');
		$frm->addTextBox('Page Title', 'category_meta_title','','category_meta_title','class="medium"');
		$frm->addTextArea('Meta Keywords', 'category_meta_keywords','','category_meta_keywords','class="medium" cols="112"');
		$frm->addTextArea('Meta Description', 'category_meta_description','','category_meta_description','class="medium" cols="112"');
		
        $frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
        $frm->setValidatorJsObjectName('categoryValidator');
        $frm->setExtra(' validator="categoryValidator" class="web_form"');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="20%"');
		$frm->setJsErrorDisplay('afterfield');
        return $frm;
	}
	
	function update_category_status() {
		if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
		$post = Syspage::getPostedVar();
		$catObj=new Categories();
        $category_id = intval($post['id']);
        $category = $catObj->getData($category_id);
        if($category==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		$data_to_update = array('category_status'=>!$category['category_status']);
		if($catObj->updateCategoryStatus($category_id,$data_to_update)){
			Message::addMessage('Success: Status has been modified successfully.');
			$arr = array('status'=>1, 'msg'=>Message::getHtml(), 'linktext'=>(($category['category_status'] == 1)?'Active':'Inactive'));
			die(convertToJson($arr));
		}else{
			Message::addErrorMessage($catObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	function delete() {
        if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
      	$post = Syspage::getPostedVar();
		$catObj=new Categories();
        $category_id = intval($post['id']);
        $category = $catObj->getData($category_id);
        if($category==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		if($catObj->delete($category_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($catObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	
	
	/*
	
	function default_action($parent=0,$page) {
		$catObj=new Categories();
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),PRODUCTCATEGORIES)) {
             die(Admin::getUnauthorizedMsg());
        }
		$page = intval($page);
		if ($page < 1)
			$page = 1;
		$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
		$arr=array("type"=>1,"pagesize"=>$pagesize,"page"=>$page,"parent"=>$parent);
		$this->set('arr_listing', $catObj->getCategories($arr));
		$this->set('pages', $catObj->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page-1)*$pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $catObj->getTotalRecords();
		if ($total_records < $end_record) $end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->set('parent',$parent);
		$this->set('category_structure',$catObj->funcGetCategoryStructure($parent));
        $this->_template->render();
    }
	
    
	function delete($category_id) {
		$catObj=new Categories();
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),PRODUCTCATEGORIES)) {
             die(Admin::getUnauthorizedMsg());
        }
        if($catObj->delete($category_id)){
			Message::addMessage('Success: Record has been deleted.');
		}else{
			Message::addErrorMessage($catObj->getError());
		}
		
		Utilities::redirectUserReferer();
    }
	
	function status($category_id, $mod='block') {
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),PRODUCTCATEGORIES)) {
             die(Admin::getUnauthorizedMsg());
        }	
        $category_id = intval($category_id);
		$catObj=new Categories();
        $category = $catObj->getData($category_id);
        if($category==false) {
            Message::addErrorMessage('Error: Please perform this action on valid record.');
            Utilities::redirectUserReferer();
        }
        switch($mod) {
            case 'block':
            	$data_to_update = array(
					'category_status'=>0,
	            );
            break;
            case 'unblock':
    	        $data_to_update = array(
					'category_status'=>1,
            	);
            break;
           
        }
		if($catObj->updateCategoryStatus($category_id,$data_to_update)){
			Message::addMessage('Success: Status has been modified successfully.');
		}else{
			Message::addErrorMessage($catObj->getError());
		}
        Utilities::redirectUserReferer();
    }
*/
	
	
	function autocomplete($type){
		$cObj=new Categories();
	    $post = Syspage::getPostedVar();
		$search_keyword=urldecode($post["keyword"]);
		$json = array();
		$categories=$cObj->getCatgeoryTreeStructure(0,'','','',$type);
		$matches=$categories;
		if (!empty($search_keyword)){
			$matches = array();
			foreach($categories as $k=>$v) {
			    if(!(stripos($v, $search_keyword) === false)) {
			        $matches[$k] = $v;
		    	}
			}
		}
	    foreach($matches as $key=>$val){
				$json[] = array(
					'category_id' => $key,
					'name'      => strip_tags(html_entity_decode($val, ENT_QUOTES, 'UTF-8'))
				);
		}
		$sort_order = array();
		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}
		array_multisort($sort_order, SORT_ASC, $json);
		echo json_encode(array_slice($json,0,10));
	}
	
	
}