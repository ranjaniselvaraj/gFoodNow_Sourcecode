<?php
class ShopsController extends CommonController{
	function __construct($model, $controller, $action){
		parent::__construct($model, $controller, $action);
		global $prod_condition;
		$this->set('prod_condition',$prod_condition);
//$this->set('checked_conditions',array("N"));
	}
/*private function getSearchForm(){
$frm = new Form('frmSearch', 'frmSearch');
$frm->addHiddenField('', 'page', 1);
$frm->addHiddenField('', 'status', 1);
$frm->addHiddenField('', 'shop', 0);
$frm->addHiddenField('', 'category', 0);
$frm->addHiddenField('', 'pagesize', 10);
$frm->addHiddenField('', 'must_products', 0);
$frm->addHiddenField('', 'sort', 'dhtl');
$frm->addHiddenField('', 'featured');
//$frm->addHiddenField('', 'condition', 'N');
$frm->setOnSubmit('searchProducts(this); return false;');
return $frm;
}*/
function default_action(){
	Utilities::redirectUser(Utilities::generateUrl('shops','all'));
}
function all(){
	$criterias=array("status"=>1,"must_products"=>1,"pagesize"=>9,"page"=>1);
	$frm = createHiddenFormFromPost("primarySearchForm",'',array(),$criterias);
	$this->set('frmSearch', $frm);
	$this->_template->render();	
}
function featured(){
	$criterias=array("status"=>1,"must_products"=>1,"pagesize"=>9,"featured"=>1);
	$frm = createHiddenFormFromPost("primarySearchForm",'',array(),$criterias);
	$this->set('frmSearch', $frm);
	$this->_template->render();	
}
function ajax_sitemap_shops($paging_page,$letter){
	$this->Shops=new Shops();
	$page = 1;
	if(isset($paging_page) && intval($paging_page) > 0) $page = intval($paging_page); 
	$pagesize = 40;
	$arr=$this->Shops->getShopsByCriteria(array("status"=>1,"page"=>$page,"pagesize"=>$pagesize,"start"=>$letter));
	$this->set('shops', $arr);
	$this->set('pages', $this->Shops->getTotalPages());
	$this->set('page', $page);
	$this->set('start_record', ($page-1)*$pagesize + 1);
	$end_record = $page * $pagesize;
	$total_records = $this->Shops->getTotalRecords();
	if ($total_records < $end_record) $end_record = $total_records;
	$this->set('end_record', $end_record);
	$this->set('total_records', $total_records);
	$this->set('letter', $letter);
	$this->_template->render(false,false);
}
function ajax_show_shops(){
	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		$post = Syspage::getPostedVar();
		$page = 1;
		if(isset($post['page']) && intval($post['page']) > 0) $page = intval($post['page']); 
		else $post['page'] = $page;
		$pagesize = isset($post['pagesize'])?$post['pagesize']:1;
		$post['pagesize'] = $pagesize;
		$favorite_user_id=$this->getLoggedUserId()?$this->getLoggedUserId():0;
		$post['favorite'] = $favorite_user_id;
		$arr=$this->Shops->getShopsByCriteria($post,$pagesize);
		foreach($arr as $key=>$val){
			$product= new Products();
			$product->joinWithPromotionsTable();
			$product->addSpecialPrice();
			$product->selectFields(array('tp.*','ts.shop_id','ts.shop_user_id','ts.shop_name','ts.shop_title','tu.user_id','tu.user_name','tu.user_username','tu.user_email','IF(prod_stock >0, "1", "0" ) as available'));
			$product->setPagesize(4);
			$shop_products=$product->getProducts(array("shop"=>$val["shop_id"]));
			$arr_shop_prods=array("products"=>$shop_products);
			$arr_shops_items[]=array_merge($val,$arr_shop_prods);
		}
//die($this->Shops->getTotalPages()."=".$this->Shops->getTotalRecords());
		$this->set('shops', $arr_shops_items);
		$this->set('pages', $this->Shops->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page-1)*$pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $this->Shops->getTotalRecords();
		if ($total_records < $end_record) $end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->_template->render(false,false);
	}
	die(0);
}
function view($shop_id,$category){
	$get = getQueryStringData();
	$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_CATALOG");
	$sort = isset($get['sort'])?$get['sort']:'dhtl';
	$criteria=array("shop"=>$shop_id,"category"=>$category,"status"=>1,"pagesize"=>$pagesize,"page"=>1,"donotspecial"=>1,"sort"=>$sort);
	$primarySearchForm = createHiddenFormFromPost("primarySearchForm",'',array(),$criteria);
	$arr_conditions = array_merge($criteria,$get);
	$this->set('primarySearchForm',$primarySearchForm);
	$user_id=$this->getLoggedUserId()?$this->getLoggedUserId():0;
	$shop = $this->Shops->getData($shop_id,array("favorite"=>$user_id,"status"=>1));
	if (!$shop)
		Utilities::show404();
		
	$this->set('shop', $shop);
	$pObj= new Products();
	$price_ranges = $pObj->getProductsMinMaxPrice($criteria);
	$this->set('price_range', $price_ranges);
	$categoryObj=new Categories();
	$shop_categories=$categoryObj->getProductCategoriesHavingRecords($category,$shop_id);
	$this->set('shop_categories', $shop_categories);
	$this->set('category', $category);
	$category_info=$categoryObj->getData($category,array("status"=>1));
	$this->set('category_info', $category_info);
	$category_structure=$categoryObj->funcGetCategoryStructure($category_info["parent"]);
	$this->set('category_structure',$category_structure);
	$this->_template->render();	
}
function policies($shop_id){
	$user_id=$this->getLoggedUserId()?$this->getLoggedUserId():0;
	$shop = $this->Shops->getData($shop_id,array("favorite"=>$user_id));
	if (!$shop)
		Utilities::show404();
	$this->set('shop', $shop);
	$categoryObj=new Categories();
	$shop_categories=$categoryObj->getProductCategoriesHavingRecords($category,$shop_id);
	$this->set('shop_categories', $shop_categories);
	$this->set('category', $category);
	$this->_template->render();	
}
function api_policies($shop_id){
	$user_id=$this->getLoggedUserId()?$this->getLoggedUserId():0;
	$shop = $this->Shops->getData($shop_id,array("favorite"=>$user_id));
	if (!$shop)
		Utilities::show404();
	$this->set('shop', $shop);
	$this->_template->render(false,false);	
}
function reviews($shop_id){
	if (!(Settings::getSetting("CONF_ALLOW_REVIEWS"))){
		Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_ACCESS') );
		Utilities::redirectUserReferer();
	}
	$user_id=$this->getLoggedUserId()?$this->getLoggedUserId():0;
	$shop = $this->Shops->getData($shop_id,array("favorite"=>$user_id));
	if (!$shop)
		Utilities::show404();
	$criteria=array(
		"shop"=>$shop_id,
		"pagesize"=>5,
		"status"=>1,
		);
	$prodFeedBackObj=new Productfeedbacks();
	$reviews=$prodFeedBackObj->getFeedbacksWithCriteria($criteria);
	$shop=array_merge($shop,array("reviews"=>$reviews));
	$frm = createHiddenFormFromPost("frmSearch",'',array(),$criteria);
	$this->set('frmSearch', $frm);
//$frm = $this->getSearchForm();
//$frm->fill($criteria);	
//$this->set('frmSearch', $frm);
	$this->set('shop', $shop);
	$categoryObj=new Categories();
	$shop_categories=$categoryObj->getProductCategoriesHavingRecords($category,$shop_id);
	$this->set('shop_categories', $shop_categories);
	$this->set('category', $category);
	$this->_template->render();	
}
/*function who_favorite_shop($shop_id){
	$user_id=$this->getLoggedUserId()?$this->getLoggedUserId():0;
	$shop = $this->Shops->getData($shop_id,array("favorite"=>$user_id));
	if (!$shop)
		Utilities::show404();
	$user=new User();	
	$favorite_users=$user->getUserFavoriteShops(array("shop"=>$shop_id,"pagesize"=>10));
	$frm = createHiddenFormFromPost("frmSearch",'',array(),array("shop"=>$shop_id));
	$this->set('frmSearch', $frm);
	$this->set('total_records', count($favorite_users));
	$this->set('favorite_users', $favorite_users);
	$this->set('shop', $shop);
	$categoryObj=new Categories();
	$shop_categories=$categoryObj->getProductCategoriesHavingRecords($category,$shop_id);
	$this->set('shop_categories', $shop_categories);
	$this->set('category', $category);
	$this->_template->render();	
}*/
function ajax_show_favorite_list(){
	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		$post = Syspage::getPostedVar();
		$page = 1;
		if(isset($post['page']) && intval($post['page']) > 0) $page = intval($post['page']); 
		else $post['page'] = $page;
		$pagesize = isset($post['pagesize'])?$post['pagesize']:10;
		$post['pagesize'] = $pagesize;
		$userFavItemObj=new User();
		$user=new User();
		$favorite_users=$user->getUserFavoriteShops($post);
		foreach($favorite_users as $key=>$val){
			$user_fav_products=$userFavItemObj->getUserFavoriteProducts($val["userfav_id"],$pagesize=3);
			$arr_user_fav_prods=array("products"=>$user_fav_products);
			$val["total_records"]=$userFavItemObj->getTotalRecords();
			$favorite_users_items[]=array_merge($val,$arr_user_fav_prods);
		}
		$this->set('favorite_users', $favorite_users_items);
		$this->set('pages', $user->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page-1)*$pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $user->getTotalRecords();
		if ($total_records < $end_record) $end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->_template->render(false,false);
	}
	die(0);
}
function send_message($shop_id){
	Utilities::checkLogin();
	$user_id=$this->getLoggedUserId()?$this->getLoggedUserId():0;
	$shop = $this->Shops->getData($shop_id,array("favorite"=>$user_id));
	if (!$shop)
		Utilities::show404();
	$userObj=new User();
//$user_details=$userObj->getUserById($user_id);
	$user_details=$userObj->getUser(array(
		'user_id'=>$user_id, 
		'get_flds'=>array(
			'user_id', 
			'user_username',
			'user_name',
			'user_email',
			)));
	$frm=$this->shop_send_message_form();
	$info=array(
		"user_id"=>$user_id,
		"shop_id"=>$shop_id,
		"send_message_from"=>$user_details['user_username']. ' (<em>' . htmlentities($user_details['user_name']) . '</em>)',
		"send_message"=>"<b>".$shop["shop_owner_username"].'</b> (<em>'.$shop["shop_name"].'</em>)'
		);
	$frm->fill($info);
	$post = Syspage::getPostedVar();
	if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
		if(!$frm->validate($post)){
			Message::addErrorMessage($frm->getValidationErrors());
		}else{
			if($user_id != $post['user_id'] || $shop_id != $post['shop_id']){
				Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
				Utilities::redirectUser(Utilities::generateUrl('shops', 'send_message',array($shop_id)));
			}else{
				$post["message_sent_to"]=$shop["shop_user_id"];
				$post["thread_type"]='S';
				$post["thread_record"]=$shop_id;
				if($userObj->addThreadMessage($post)){
					Message::addMessage(Utilities::getLabel('M_YOUR_MESSAGE_SENT_SUCCESSFULLY'));	
					Utilities::redirectUser(Utilities::generateUrl('shops', 'send_message',array($shop_id)));
				}else{
					Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
				}
			}
		}
		$frm->fill($post);
	}		   	
	$this->set('frm', $frm);
	$this->set('shop', $shop);
	$categoryObj=new Categories();
	$shop_categories=$categoryObj->getProductCategoriesHavingRecords($category,$shop_id);
	$this->set('shop_categories', $shop_categories);
	$this->set('category', $category);
	$this->_template->render();	
}
protected function shop_send_message_form(){
	$frm = new Form('frmSendMessage','frmSendMessage');
	$frm->setTableProperties('width="100%" border="0" cellspacing="10" cellpadding="10"');
	$frm->setLeftColumnProperties('width="30%"');
	$frm->addHiddenField('', 'user_id', 0, 'user_id');
	$frm->addHiddenField('', 'shop_id', 0, 'shop_id');
//$fld=$frm->addHtml('<label>'.Utilities::getLabel('M_From').'</label>', 'from_message');
	$fld=$frm->addHtml('<label>'.Utilities::getLabel('M_From').'</label>', 'send_message_from');
	$fld->html_after_field='<br/><span class="smalltext">'.Utilities::getLabel('M_Contact_info_not_shared').'</span>';
	$frm->addHtml('<label>'.Utilities::getLabel('M_To').'</label>', 'send_message','');
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
function report($shop_id){
	Utilities::checkLogin();
	$userObj=new User();
	$shop = $this->Shops->getData($shop_id);
	if (!$shop)
		Utilities::show404();
	$user_id=$this->getLoggedUserId();	
	$frm=$this->report_shop_form();
	$criterias=array(
		"user_id"=>$user_id,
		"shop_id"=>$shop_id
		);
	$frm->fill($criterias);
	$post = Syspage::getPostedVar();
	if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
		if(!$frm->validate($post)){
			Message::addErrorMessage($frm->getValidationErrors());
		}else{
			if($user_id != $post['user_id'] || $shop_id !=  $post['shop_id']){
				Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
				Utilities::redirectUser(Utilities::generateUrl('shops', 'report',array($shop_id)));
			}else{
				if($this->Shops->addShopReport($post)){
					Message::addMessage(Utilities::getLabel('F_Your_report_sent_review'));	
					Utilities::redirectUser(Utilities::generateUrl('shops', 'report',array($shop_id)));
				}else{
					Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
				}
			}
		}
		$frm->fill($post);
	}		   	
	$this->set('frm', $frm);
	$this->set('shop', $shop);
	$categoryObj=new Categories();
	$shop_categories=$categoryObj->getProductCategoriesHavingRecords($category,$shop_id);
	$this->set('shop_categories', $shop_categories);
	$this->set('category', $category);
	$this->_template->render();	
}
protected function report_shop_form(){
	$frm = new Form('frmReportShop','frmReportShop');
	$frm->setTableProperties('width="100%" border="0" cellspacing="10" cellpadding="10"');
	$frm->addHiddenField('', 'user_id', 0, 'user_id');
	$frm->addHiddenField('', 'shop_id', 0, 'shop_id');
	$reportReasonObj=new Reportreasons();
	$fld=$frm->addSelectBox('', 'sreport_reason',$reportReasonObj->getAssociativeArray(),'',1,Utilities::getLabel('M_Select_Reason'));
	$fld->merge_caption=true;
	$fld->requirements()->setRequired(true);
	$fld->requirements()->setCustomErrorMessage(Utilities::getLabel('F_Please_select_your_reason'));
	$fld = $frm->addTextArea("", 'sreport_message','','','placeholder="'.Utilities::getLabel('F_Please_explain_shop_violates').'"');
	$fld->requirements()->setRequired(true);
	$fld->merge_caption=true;
	$fld->requirements()->setCustomErrorMessage(Utilities::getLabel('F_Please_enter_message'));
	$fld=$frm->addSubmitButton('', 'btn_submit', Utilities::getLabel('M_Submit_Report'), 'btn_submit');
	$fld->merge_caption=true;
	$frm->setExtra('class="siteForm" validator="frmValidator" autocomplete="off"');
	$frm->captionInSameCell(false);
	$frm->setValidatorJsObjectName('frmValidator');
	$frm->setJsErrorDisplay('afterfield');
	return $frm;
}
}
