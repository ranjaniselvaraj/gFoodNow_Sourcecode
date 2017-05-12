<?php
class CollectionsController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
	
	function __construct($model, $controller, $action){
		parent::__construct($model, $controller, $action);
		$this->collection_criterias_arr=array(
											"0"=>"Price Low to High",
											"1"=>"Price High to Low",
											"2"=>"Most Popular (Top Selling)",
											"3"=>"New Arrivals",
											"4"=>"Ratings High to Low",
											"5"=>"Featured",
											//"6"=>"Top Sellers",
											//"7"=>"Top Brands",
											);
		$this->collection_types_arr=array(
										 "C"=>"Categories",
										 "P"=>"Products",
										 "S"=>"Shops",
										);
		
		$admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,COLLECTIONS);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Collections Management", Utilities::generateUrl("collections"));
												
	}
	
	protected function getSearchForm() {
        $frm=new Form('frmCollectionSearch','frmCollectionSearch');
		$frm->setFieldsPerRow(3);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('GET');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchCollections(this); return false;');
        return $frm;
    }
	
	/*function test() {
		$collectionObj=new Collections();
        $r = $collectionObj->getCollections($post);
		Utilities::printArray($r);
		die(); 
        
    }*/
	
	function default_action() {
		$frm = $this->getSearchForm();
        $this->set('frmPost', $frm);
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listCollections($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mode'])) {
			$collectionObj=new Collections();
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
            $this->set('records', $collectionObj->getCollections($post,true));
            $this->set('pages', $collectionObj->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $collectionObj->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
			$this->set('collection_types',$this->collection_types_arr);
            $this->_template->render(false, false);
        }
    }
	
    function form($collection_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("Collection Setup", Utilities::generateUrl("collections"));
		$this->set('breadcrumb', $this->b_crumb->output());
		$collectionObj=new Collections();
		$collection_id = intval($collection_id);
        if ($collection_id > 0) {
            $data = $collectionObj->getData($collection_id);
			$collection_type=$data["collection_type"];
			unset($data["collection_type"]);
			$data['collection_categories'] = array();
			$categories = $collectionObj->getCollectionCategories($collection_id,false);
			foreach ($categories as $key=>$val) {
				$data['collection_categories'][]=$val;
			}
			$products = $collectionObj->getCollectionProducts($collection_id,false);
			foreach ($products as $key=>$val) {
				$data['collection_products'][]=$val;
			}
			$shops = $collectionObj->getCollectionShops($collection_id,false);
			foreach ($shops as $key=>$val) {
				$data['collection_shops'][]=$val;
			}
        }
		
		$frm = $this->getForm($data);
		$frm->fill($data);
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				if($post['collection_id'] != $collection_id){
					Message::addErrorMessage('Error: Invalid Request!!');
					Utilities::redirectUser(Utilities::generateUrl('collections'));
				}else{
					
					if(isset($post['collection_image'])){
						unset($post['collection_image']);
					}
					if (Utilities::isUploadedFileValidImage($_FILES['collection_image'])){
						if(!Utilities::saveImage($_FILES['collection_image']['tmp_name'],$_FILES['collection_image']['name'], $saved_image_name, 'collections/')){
		               		Message::addError($saved_image_name);
    		   			}
						$post["collection_image"]=$saved_image_name;
    		    	}
					
					if($collectionObj->addUpdate($post)){
						Message::addMessage('Success: Collection details added/updated successfully.');
						Utilities::redirectUser(Utilities::generateUrl('collections'));
					}else{
						Message::addErrorMessage($collectionObj->getError());
					}
				}
			}
			$frm->fill($post);
		}
        $this->set('frm', $frm);
		$this->set('collection_type', $collection_type);
		$this->_template->render();
    }
	
	
    protected function getForm($info) {
        $frm = new Form('frmCollections');
		$frm->setExtra(' validator="CollectionsfrmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('CollectionsfrmValidator');
        $frm->addHiddenField('', 'collection_id');
		$frm->addRequiredField('Name', 'collection_name','', '', ' class="medium"');
		$frm->addRequiredField('Display Title', 'collection_display_title','', '', ' class="medium"');
		$fld = $frm->addFileUpload('Collection Image', 'collection_image');
		$fld->html_before_field='<div class="filefield"><span class="filename"></span>';
		$fld->html_after_field='<label class="filelabel">Browse File</label></div>';
		$collection_id=$info["collection_id"];
		if($info["collection_image"]!=""){
        	$fld->html_before_field = '<div id="collection_image" >';
			$fld->html_after_field = '<p><b>Current Image:</b><br /><img src="'.Utilities::generateUrl('image', 'collection', array($info['collection_image'],'THUMB'),CONF_WEBROOT_URL).'" /></p></div>';
		}
		
		$frm->addRadioButtons('Type', 'collection_type',$this->collection_types_arr,"0",4,' class="large"');
		
		$collection_categories = '';
		if (isset($info["collection_categories"])){
			foreach ($info["collection_categories"] as $collection_category) { 
					$collection_categories.='<div id="collection-category'.$collection_category['id'].'"><i class="remove_filter remove_param"><img src="'.CONF_WEBROOT_URL.'images/admin/closelabels.png"/></i> '.$collection_category['name'].'&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" class="reorder-up"><img src="'.CONF_WEBROOT_URL.'images/admin/Arrows-Up-icon.png"/></a> <a href="#" class="reorder-down"><img src="'.CONF_WEBROOT_URL.'images/admin/Arrows-Down-icon.png"/></a><input type="hidden" name="categories[]" value="'. $collection_category['id'].'" /></div>';
			}
		}
		$fld = $frm->addTextBox('Categories', 'category');
		$fld->html_after_field = '<small>Choose specific categories the collection will contain.</small><br/><div id="collection-categories" class="well well-sm" style="height: 150px; overflow: auto;">'.$collection_categories.'</div>';
		
		$collection_products = '';
		if (isset($info["collection_products"])){
			foreach ($info["collection_products"] as $collection_product) { 
					$collection_products.='<div id="collection-products'.$collection_product['id'].'"><i class="remove_product remove_param"><img src="'.CONF_WEBROOT_URL.'images/admin/closelabels.png"/></i> '.$collection_product['name'].'&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" class="reorder-up"><img src="'.CONF_WEBROOT_URL.'images/admin/Arrows-Up-icon.png"/></a> <a href="#" class="reorder-down"><img src="'.CONF_WEBROOT_URL.'images/admin/Arrows-Down-icon.png"/></a><input type="hidden" name="products[]" value="'. $collection_product['id'].'" /></div>';
			}
		}
		$fld = $frm->addTextBox('Products', 'products');
		$fld->html_after_field = '<small>Choose specific products the collection will contain.</small><br/><div id="collection-products" class="well well-sm" style="height: 150px; overflow: auto;">'.$collection_products.'</div>';

		$collection_shops = '';		
		if (isset($info["collection_shops"])){
			foreach ($info["collection_shops"] as $collection_shop) { 
					$collection_shops.='<div id="collection-shops'.$collection_shop['id'].'"><i class="remove_shop remove_param"><img src="'.CONF_WEBROOT_URL.'images/admin/closelabels.png"/></i> '.$collection_shop['name'].'&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" class="reorder-up"><img src="'.CONF_WEBROOT_URL.'images/admin/Arrows-Up-icon.png"/></a> <a href="#" class="reorder-down"><img src="'.CONF_WEBROOT_URL.'images/admin/Arrows-Down-icon.png"/></a><input type="hidden" name="shops[]" value="'. $collection_shop['id'].'" /></div>';
			}
		}
		$fld = $frm->addTextBox('Shops', 'shops');
		$fld->html_after_field = '<small>Choose specific shops the collection will contain.</small><br/><div id="collection-shops" class="well well-sm" style="height: 150px; overflow: auto;">'.$collection_shops.'</div>';
		
		$fld=$frm->addRadioButtons('Criteria', 'collection_criteria',$this->collection_criterias_arr,"0",4,' class="large"');
		$fld->html_after_field = '<br/><small>This is applicable only on category collections.</small>';
		$fld=$frm->addRequiredField('Primary Records', 'collection_primary_records','','',' class="small"');
		$fld->html_after_field = '<br/><small>Number of primary level records we need to display on front end.</small>';
		$fld->requirements()->setIntPositive();
		$fld=$frm->addRequiredField('Child Records', 'collection_child_records','','',' class="small"');
		$fld->requirements()->setIntPositive();
		$fld->html_after_field = '<br/><small>Number of child records from primary selection we need to display on front end. Say X number of products from Y category.</small>';
		$frm->addTextBox('Display Order', 'collection_display_order','','',' class="small"');
		$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="20%"');
        return $frm;
    }
	
	function update_collection_status() {
		if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
		$post = Syspage::getPostedVar();
		$collectionObj=new Collections();
        $collection_id = intval($post['id']);
        $collection = $collectionObj->getData($collection_id);
        if($collection==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		$data_to_update = array('collection_status'=>!$collection['collection_status']);
		if($collectionObj->updateCollectionStatus($collection_id,$data_to_update)){
			Message::addMessage('Success: Status has been modified successfully.');
			$arr = array('status'=>1, 'msg'=>Message::getHtml(), 'linktext'=>(($collection['collection_status'] == 1)?'Active':'Inactive'));
			die(convertToJson($arr));
		}else{
			Message::addErrorMessage($collectionObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	function delete() {
        if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
        $post = Syspage::getPostedVar();
		$collectionObj=new Collections();
        $collection_id = intval($post['id']);
        $collection = $collectionObj->getData($collection_id);
		if($collection==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		if($collectionObj->delete($collection_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($collectionObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
   
	
}