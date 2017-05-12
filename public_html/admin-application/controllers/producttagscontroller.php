<?php
class ProducttagsController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,PRODUCTTAGS);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Product Tags Management", Utilities::generateUrl("producttags"));
    }
	
	protected function getSearchForm() {
        $frm=new Form('frmProductTagSearch','frmProductTagSearch');
		$frm->setFieldsPerRow(3);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('GET');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addTextBox('Keyword', 'keyword','','',' class="small"');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchProductTags(this); return false;');
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
	
	function listProductTags($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
			$ptOBj=new Producttags();
            $post = Syspage::getPostedVar();
            $page = 1;
            if (isset($post['page']) && intval($post['page']) > 0) {
                $page = intval($post['page']);
            } else {
                $post['page'] = $page;
            }
            if (!empty($post['keyword'])) {
                $this->set('srch', $post);
            }
           	$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
            $post['pagesize'] = $pagesize;
			$ptags = $ptOBj->getProductTags($post);
			foreach($ptags as $ptag){
				$product_tags[] = $ptOBj->getData($ptag['ptag_id']);
			}
            $this->set('records', $product_tags);
            $this->set('pages', $ptOBj->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $ptOBj->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	  
    function form($ptag_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$ptOBj=new Producttags();
        $ptag_id = intval($ptag_id);
        $frm = $this->getForm();
        if ($ptag_id > 0) {
            $data = $ptOBj->getData($ptag_id);
            $frm->fill($data);
        }
		
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				if($post['ptag_id'] != $ptag_id){
					Message::addErrorMessage('Error: Invalid Request!!');
					Utilities::redirectUser(Utilities::generateUrl('producttags'));
				}else{
					
						$tag = $ptOBj->getProductTagByName($post['ptag_name']);
						if (($tag==true) && ($tag["ptag_id"]!=$post["ptag_id"]) ){
							Message::addErrorMessage('Product tag with this name already exists.');
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
							if($ptOBj->addUpdate($post)){
								Message::addMessage('Success: Product tag details added/updated successfully.');
								Utilities::redirectUser(Utilities::generateUrl('producttags'));
							}else{
								Message::addErrorMessage($ptOBj->getError());
							}
						}
					}
			}
			$frm->fill($post);
		}
		
        $this->set('frm', $frm);
        $this->_template->render(true,true);
    }
    protected function getForm() {
        $frm = new Form('frmProducttag');
		$frm->setExtra(' validator="ProducttagfrmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('ProducttagfrmValidator');
        $frm->addHiddenField('', 'ptag_id','','ptag_id');
		$frm->addRequiredField('Name', 'ptag_name','', '', ' class="input-xlarge" onKeyUp="Slugify(this.value,\'seo_url_keyword\',\'ptag_id\')"');
		$fld=$frm->addRequiredField('URL Keywords', 'seo_url_keyword','', 'seo_url_keyword', ' class="input-xlarge"');
		$fld->html_after_field='<small>Do not use spaces, instead replace spaces with - and make sure the keyword is globally unique.</small>';
		$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="20%"');
        return $frm;
    }
	function delete() {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $post = Syspage::getPostedVar();
		$ptOBj=new Producttags();
        $ptag_id = intval($post['id']);
        $product_tag = $ptOBj->getData($ptag_id);
		if($product_tag==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		if($ptOBj->delete($ptag_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($ptOBj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
}