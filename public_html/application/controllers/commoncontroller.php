<?php
class CommonController extends Controller{
	protected $userLogged = false;
	function __construct($model, $controller, $action){
		parent::__construct($model, $controller, $action);
		$get=getQueryStringData();
		$ptObj=new Producttags();
		$arr = explode(' ',$get['tags']);
		foreach($arr as $tkey=>$tval){
			$product_tag=$ptObj->getTagByUrlAlias($tval);
			if ($product_tag){
				$product_tag_id = $product_tag['ptag_id'];
				$this->tags[] = $product_tag_id;
				$ptObj->recordTagWeightage($product_tag_id);	
			}
		}
		$this->currency = &Syspage::getCurrency();
		$this->set('currencyObj', $this->currency);
		if (Settings::getSetting("CONF_MAINTENANCE") && ($controller!="maintenance")){
			Utilities::redirectUser(Utilities::generateUrl('maintenance'));
		}
		$user_logged_in_pages = array(
			'account'=>array('default_action','dashboard_supplier', 'dashboard_buyer','recent_activity','sales','logout','sales_view_order','cancel_order','messages','view_message','credits','request_withdrawal','orders','view_order','send_message','feedback','return_request','publications','paused_publications','finalized_publications','remove_product','finalize_product','product_status','favorites','delete_list','favorite_items','view_list','favorite_shops','addresses','default_address','delete_address','address_form','change_password','change_email','profile_info','bank_info','return_info','shop','product_form','brand_request','cancellation_request','cancellation_requests','option_form','options','orders','return_requests','reward_points','share_earn','supplier_approval_form','view_request','view_return_request','view_supplier_request'),
			'cart'=>array('checkout_summary','checkout_payment'),
//'order'=>array('detail'),
			);
		if(array_key_exists($controller,$user_logged_in_pages)){
			if(in_array($action,$user_logged_in_pages[$controller])){
				if(!$this->isUserLogged()){
					Message::addErrorMessage(Utilities::getLabel('L_Please_Login_Continue'));
					Utilities::redirectUser(Utilities::generateUrl('user','account'));
				}
			}	
		}
		if($this->isUserLogged()){
			if($controller=='user' && ($action=='login' || $action=='forgot' || $action=='repwd')){
				Utilities::redirectUser(Utilities::generateUrl('user'));
			}
			$this->userLogged = true;
		}
		$this->short_header_footer=in_array($controller,array("account","import_export"))?true:false;
		$this->controller=$controller;
		$this->action=$action;
		$this->initiate();
	}
	private function initiate(){
		$this->setTemplateValues($this->_template);
	}
	protected function setTemplateValues($tpl){
		require_once (CONF_INSTALLATION_PATH . 'public/includes/phpfastcache.php');
		global $conf_arr_buyer_types;
		global $conf_arr_seller_types;
		global $conf_arr_advertiser_types;
		phpFastCache::setup("storage","files");
		phpFastCache::setup("path", CONF_USER_UPLOADS_PATH."caching");
		$extraPageObj=new Extrapage();
		$arr_content_block=$extraPageObj->getExtraBlockData(array('identifier'=>'FOOTER_OPTION_CONTENT_BLOCK'));
		$footer_content_block=$arr_content_block["epage_content"];
		$contact_email = str_replace('@','[@]',Settings::getSetting("CONF_ADMIN_EMAIL"));
		$footer_content_block = str_replace('{SITE_EMAIL_ADDRESS}',$contact_email,$footer_content_block);
		$tpl->set('footer_content_block', $footer_content_block);
		$tpl->set('controller', $this->controller);
		$tpl->set('action', $this->action);
		$tpl->set('short_header_footer', $this->short_header_footer);
		$tpl->set('is_front_user_logged', $this->userLogged);
		$usr = new User();
		$tpl->set('action', $this->_action);
		if($this->userLogged === true){
			$usr = new User();
			$user_details=$usr->getUser(array('user_id'=>$this->getLoggedUserId(), 'get_flds'=>array('user_id', 'user_type','user_name','user_email','user_phone','user_buyer_supp_pref','user_googleplus_id','user_facebook_id')));
			$tpl->set('logged_user_name', $user_details['user_name']);
			$tpl->set('logged_user_is_customer', $user_details['user_name']);
			$tpl->set('preferred_dashboard', $user_details['user_buyer_supp_pref']);
			$cartObj=new Cart();
			$tpl->set('total_cart_items', $cartObj->countProducts());
			$tpl->set('is_buyer_logged', in_array($user_details['user_type'],$conf_arr_buyer_types));
			$tpl->set('is_seller_logged', in_array($user_details['user_type'],$conf_arr_seller_types));
			$tpl->set('is_advertiser_logged', in_array($user_details['user_type'],$conf_arr_advertiser_types));
			$tpl->set('is_social_user_logged', !empty($user_details['user_googleplus_id']) || !empty($user_details['user_facebook_id']) );
		}
		$afObj = new Affiliate();
		if ($afObj->isAffiliateLogged()) {
			$tpl->set('is_affiliate_logged',true);
		}
		$cartObj=new cart();
		$this->set('cart_items',$cartObj->countProducts());
		$tpl->set('front_theme', $_COOKIE['visitor_theme_cookie']);
		$validation_messages=array(
			'required'=>sprintf(Utilities::getLabel('L_VALIDATION_MANDATORY'),'{caption}'),
			'charonly'=>sprintf(Utilities::getLabel('L_VALIDATION_CHARACTER_ONLY'),'{caption}'),
			'integer'=>sprintf(Utilities::getLabel('L_VALIDATION_INTEGER_ONLY'),'{caption}'),
			'floating'=>sprintf(Utilities::getLabel('L_VALIDATION_NUMERIC_ONLY'),'{caption}'),
			'lengthrange'=>sprintf(Utilities::getLabel('L_VALIDATION_LENGTH_RANGE_ERROR'),'{caption}','{minlength}','{maxlength}'),
			'range'=>sprintf(Utilities::getLabel('L_VALIDATION_VALUE_RANGE_ERROR'),'{caption}','{minval}','{maxval}'),
			'username'=>sprintf(Utilities::getLabel('L_VALIDATION_USERNAME'),'{caption}'),
			'password'=>sprintf(Utilities::getLabel('L_VALIDATION_PASSWORD'),'{caption}'),
			'comparewith'=>sprintf(Utilities::getLabel('L_VALIDATION_COMPARE_WITH'),'{caption}'),
			'email'=>sprintf(Utilities::getLabel('L_VALIDATION_EMAIL'),'{caption}'),
			'user_regex'=>sprintf(Utilities::getLabel('L_VALIDATION_INVALID'),'{caption}'),						
			);
		$this->set('validation_messages',$validation_messages);
		$headerSearchForm=$this->getSiteSearchForm();
		$get = getQueryStringData();
		$headerSearchForm->fill($get);
		$this->set('headerSearchForm',$headerSearchForm);
		$footerNewsletterForm=$this->getNewsletterForm();
		$this->set('footerNewsletterForm',$footerNewsletterForm);
		$cache = phpFastCache();
		$common_elements = $cache->get("common_elements");
		$nav = new Navigation();
		$top_header_mobile_navigation=$nav->getNavigation(1);
		$footer_mobile_navigation=$nav->getNavigation(2);
		$this->set('top_header_mobile_navigation',$top_header_mobile_navigation);
		$this->set('footer_mobile_navigation',$footer_mobile_navigation);
		if($common_elements == null) {
			$cat = new Categories();
			$header_categories=$cat->getCategoriesAssocArrayFront(0,1);
			/*Utilities::printArray($header_categories);
			die();*/
			$footer_nav=$nav->getNavigationByType(2);
			$foot_nav = array();
			$previous_gid = $j = $k =0;
			foreach ($footer_nav as $i=>$nav){           
				if($i==0 || $previous_gid!=$nav['nav_id']){
					$previous_gid = $nav['nav_id'];
					$j++;
				}
				$footer_navigation[$previous_gid][] = array(
					'navgroup_id'=>$nav['nav_id'],
					'parent'=> $nav['nav_name'],
					'navlink_caption' => Utilities::strip_javascript($nav['nl_html']),
					'navlink_cmspage_id' => $nav['nl_cms_page_id'],
					'navlink_caption' => Utilities::strip_javascript($nav['nl_caption']),
					'navlink_html' => Utilities::strip_javascript($nav['nl_html']),
					'navlink_type' => $nav['nl_type'],
					'navlink_bullet_icon' => $nav['nl_bullet_image'],
					'navlink_href_target' => $nav['nl_target']
					);
			}
			$brands = new Brands();
			$top_brands=$brands->getBrands(array("status"=>1,"order"=>"brand_name","must_products"=>1,"pagesize"=>25));
			$themeObj = new Themes();
			$front_themes=$themeObj->getThemes();
			$sObj=new Socialmedia();
			$social_platforms=$sObj->getSocialmedias(array("status"=>1));
			$common_elements=array(
									"header_categories"=>$header_categories,
									"social_platforms"=>$social_platforms,
									"front_themes"=>$front_themes,
									//"top_header_mobile_navigation"=>$top_header_mobile_navigation,
									//"footer_mobile_navigation"=>$footer_mobile_navigation,
									"top_brands"=>$top_brands,
									"footer_navigation"=>$footer_navigation
							);
			$cache->set("common_elements",$common_elements , 4*60*60); // 4 Hours Cacheing
		}
		foreach($common_elements as $elemkey=>$elemval){
			$this->set($elemkey,$elemval);
		}
		
	}
protected function getNewsletterForm()	{
	$frm=new Form('subscribeNewsletter','subscribeNewsletter');
	$frm->setValidatorJsObjectName('newsletterFrmValidator');
	$frm->setAction(Utilities::generateUrl('common', 'subscribe'));
	$frm->setFieldsPerRow(3);
	$fldEmail=$frm->addEmailField('', 'email','','email',' Title="'.Utilities::getLabel('F_Email_Address').'" Placeholder="'.Utilities::getLabel('F_Your_e-mail_address').'"');
	$fldButton=$frm->addSubmitButton('','btn_submit','Subscribe');
	$fldEmail->attachField($fldButton);
	$frm->setExtra('class="newsletter-form" rel="newsletter" validator="newsletterFrmValidator"');
	$frm->setRequiredStarPosition("X");
	$frm->setJsErrorDisplay('summary');
	$frm->setJsErrorDiv("ajax_newsletter_message");
	return $frm;
}
protected function getSiteSearchForm()	{
	$frm=new Form('frmSiteSearch','frmSiteSearch');
	$frm->setAction(Utilities::generateUrl('products', 'search'));
	$frm->setFieldsPerRow(3);
	$fldKeyword=$frm->addTextBox('', 'keyword','','search_keyword','Placeholder="'.Utilities::getLabel('F_Search_for_item').'"');
	$categoryObj=new Categories();
	$all_cats=$categoryObj->getCategoriesAssocArrayFront();
	foreach($all_cats[0] as $cat){
		$arr_cat[$cat["category_id"]]=$cat["category_name"];
	}
	$fldCategory=$frm->addSelectBox('', 'category',$arr_cat,'', '',Utilities::getLabel('F_All'),'');
	$fldButton=$frm->addSubmitButton('','search_submit','');
	$fldCategory->attachField($fldButton);
	$fldKeyword->attachField($fldCategory);
	$frm->setExtra('class="frmSearch" autocomplete="off"');
	$frm->setAction(Utilities::generateUrl('products', 'search'));
	$frm->setTableProperties(' width="100%" border="0" class="tableform"');
	return $frm;
}
public function default_action(){
	echo $this->getError();
	exit(0);
}
protected function isUserLogged(){
	$usr = new User();
	if ($usr->isUserLogged()){
		return true;
	}
	return false;
}
protected function isGuestUserLogged(){
	return false;
}
protected function getUserTypes(){
	return array('3', '4','5');
}
protected function cryptPwd($str){
	return crypt($str, 'NxhPwrR07zYijkhgdfg46M2fad9a5189454d05879a76f5e8b569xf2CVo6JpNxhPwr587988a76f5e');
}
protected function validateEmail($email){
	return filter_var($email, FILTER_VALIDATE_EMAIL);
}
function getError(){
	return ('Invalid page request!!');
}
protected function getCountriesAssociativeArray($active_only = false){
	$common = new Common();
	return $common->getCountriesAssoc($active_only); /* Get countries associative array id=>name */
}
protected function getStatesAssociativeArray($country_id, $active_only = false){
	$common = new Common();
	return $common->getStatesAssoc($country_id, $active_only); /* Get states associative array id=>name */
}
protected function getLoggedUserId(){
	return $_SESSION['logged_user']['user_id'];
}
protected function getLoggedUserEmail(){
	return $_SESSION['logged_user']['email'];
}
protected function getLoggedUserType(){
	return $_SESSION['logged_user']['type'];
}
function check_ajax_user_logged_in(){
	echo $this->isUserLogged()?true:false;
}
	function favourite_product($product_id){
		$pObj=new Products();
		if($this->isUserLogged()){
			$this->User=new User();
			$user_id=$this->getLoggedUserId();
			$favorite_item = $this->User->getUserFavoriteItem(intval($product_id),$user_id);
			if ($favorite_item){
				if($this->User->deleteUserFavoriteProduct(intval($product_id), $user_id)){
					$pObj->recordProductWeightage($product_id,'products#favorite');
					$action_performed="R"; // Removed
					$display_message=Utilities::getLabel('L_Removed_from_Favorite_product'); // Removed
					$title=Utilities::getLabel('L_Favorite'); 
				}else{
					$action_performed=$this->User->getError(); // Error
				}
			}else{
				if($this->User->addUserFavoriteProduct(intval($product_id), $user_id)){
					$pObj->recordProductWeightage($product_id,'products#unfavorite');
					$pObj->addUpdateProductBrowsingHistory($product_id,array("favorite"=>1));
					$action_performed="A"; // Added
					$display_message=Utilities::getLabel('L_Marked_Favorite_Product');
					$title=Utilities::getLabel('L_Un-Favorite'); 
				}else{
					$action_performed=$this->User->getError(); // Error
				}
			}
		}
		$arr=array("title"=>$title,"action_performed"=>$action_performed,"display_message"=>$display_message,"logged_in"=>$this->isUserLogged()?true:false);
		echo json_encode($arr);
	}
	function favourite_shop($shop_id){
		$this->User=new User();
		if($this->isUserLogged()){
			$user_id=$this->getLoggedUserId();
			$favorite_shop = $this->User->getUserFavoriteShop(intval($shop_id),$user_id);
			if ($favorite_shop){
				if($this->User->deleteUserFavoriteShop(intval($shop_id), $user_id)){
					$action_performed="R"; // Removed
					$display_message=Utilities::getLabel('L_Removed_from_Favorite_Shop'); // Removed
					$title=Utilities::getLabel('L_Favorite'); 
				}else{
					$action_performed=$this->User->getError(); // Error
				}
			}else{
				if($this->User->addUserFavoriteShop(intval($shop_id), $user_id)){
					$action_performed="A"; // Added
					$display_message=Utilities::getLabel('L_Marked_Favorite_Shop');
					$title=Utilities::getLabel('L_Un-Favorite');  
				}else{
					$action_performed=$this->User->getError(); // Error
				}
			}
		}
		$arr=array("title"=>$title,"action_performed"=>$action_performed,"display_message"=>$display_message,"logged_in"=>$this->isUserLogged()?true:false);
		echo json_encode($arr);
	}
	function add_remove_list_item(){
		if($this->isUserLogged()){
			$pObj=new Products();
			$this->User=new User();
			$user_id=$this->getLoggedUserId();
			$post = Syspage::getPostedVar();
			$list_id=$post["list_id"];
			$product_id=$post["prod"];
			$list_details = $this->User->getUserList($list_id,$user_id);
			if ($list_details==true){
				$list_product = $this->User->getUserListProduct($list_id,$product_id);
				if ($list_product){
					if($this->User->deleteUserListProduct($product_id, $list_id)){
						$pObj->recordProductWeightage($product_id,'products#unwishlist');
						$action_performed="R"; // Removed
						Message::addMessage(Utilities::getLabel('L_Removed_List_Product'));
					}else{
					$action_performed=$this->User->getError(); // Error
					}
				}else{
					if($this->User->addUserListProduct(intval($product_id), $list_id)){
						$pObj->recordProductWeightage($product_id,'products#wishlist');
						$pObj->addUpdateProductBrowsingHistory($product_id,array("wishlist"=>1));
						$action_performed="A"; // Added
						Message::addMessage(Utilities::getLabel('L_Added_List_Product'));
					}else{
						$action_performed=$this->User->getError(); // Error
					}
				}
			}else{
					$action_performed=Utilities::getLabel('M_ERROR_INVALID_REQUEST');
			}
		}
		$arr=array("action_performed"=>$action_performed,"display_message"=>Message::getHtml(),"logged_in"=>$this->isUserLogged()?true:false);
		echo json_encode($arr);
	}
	function create_list_item(){
		$this->User=new User();
		$user_id=$this->getLoggedUserId();
		$post = Syspage::getPostedVar();
		$product_id=$post["product_id"];
		$post["user_id"]=$user_id;
		if($list_id=$this->User->addList($post)){
			if($this->User->addUserListProduct($product_id, $list_id)){
				$pObj=new Products();
				$pObj->recordProductWeightage($product_id,'products#wishlist');
				$pObj->addUpdateProductBrowsingHistory($product_id,array("wishlist"=>1));
				$action_performed="A"; // Added
				Message::addMessage(Utilities::getLabel('L_Added_List'));
			}else{
			$action_performed=$this->User->getError(); // Error
		}
		}else{
			$action_performed=Utilities::getLabel('M_ERROR_INVALID_REQUEST');
		}
		$arr=array("action_performed"=>$action_performed,"display_message"=>Message::getHtml());
		echo json_encode($arr);
	}
function view_lists($product_id){
	$this->User=new User();
	$user_lists=$this->User->getUserLists($this->getLoggedUserId());
	$this->set('user_lists', $user_lists);
	$this->set('product_id', $product_id);
	$this->set('frm', $this->getNewListForm($product_id));
	$this->_template->render(false,false);
}
function ajax_show_reviews(){
	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		$post = Syspage::getPostedVar();
		$page = 1;
		if(isset($post['page']) && intval($post['page']) > 0) $page = intval($post['page']); 
		else $post['page'] = $page;
		$pagesize = isset($post['pagesize'])?$post['pagesize']:10;
		$post['pagesize'] = $pagesize;
		$post["status"]=1;
		$prodFeedBackObj=new Productfeedbacks();
		
		$arr=$prodFeedBackObj->getFeedbacksWithCriteria($post);
		$this->set('reviews', $arr);
		$total_pages=$prodFeedBackObj->getTotalPages();
		$this->set('pages',$total_pages);
		$this->set('page', $page);
		$this->set('start_record', ($page-1)*$pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $prodFeedBackObj->getTotalRecords();
		if ($total_records < $end_record) $end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$load_more = ($total_pages > $page)?1:0;
//die(convertToJson(array('total_pages'=>$total_pages, 'html'=>"Page ".$page."<br/>")));
		die(convertToJson(array('total_pages'=>$total_pages, 'html'=>$this->_template->render(false,false,NULL,true))));
	}
	die(0);
}
function ajax_show_products_json(){
	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		$post = Syspage::getPostedVar();
		$cObj=new Categories();
		$category_id = $post['category'];
		$page = 1;
		if(isset($post['page']) && intval($post['page']) > 0) $page = intval($post['page']); 
		else $post['page'] = $page;
		$pagesize = isset($post['pagesize'])?$post['pagesize']:10;
		$post['pagesize'] = $pagesize;
		$post['favorite']=User::isUserLogged()?$this->getLoggedUserId():0;
		$criteria=array_merge($post,array("status"=>1));
		$conditions = array_diff_key($criteria,array_flip(array("pagesize","sort")));
		if ($post['page']==1){
			$arr_brands = array();
			if ($post['brand_page']!=1){
				$pObj= new Products();
				$pObj->joinWithDetailTable();
				$pObj->joinWithCategoryTable();
				$pObj->addSpecialPrice();
				$brands = $pObj->getProductsSearchedBrands(array('category'=>$category_id,"min"=>$post['min'],"max"=>$post['max'],"keyword"=>$post['keyword'],"property"=>$post['property'],"tags"=>$post['tags']),array_diff_key($conditions,array_flip(array("brand"))));	
				foreach($brands as $bkey=>$bval){
					$bval['is_brand_checked'] = in_array($bval["brand_id"],$post["brand"])?1:'';
					$arr_brands[] = $bval;
					if ($bval['is_disabled']==0)
						$display_brands_box = true; 
				}
			}
			$pObj= new Products();
			$pObj->joinWithDetailTable();
			$pObj->joinWithCategoryTable();
			$pObj->joinWithBrandsTable();
			$pObj->addSpecialPrice();
			$price_ranges = $pObj->getProductsMinMaxPrice(array_diff_key($conditions,array_flip(array("min","max"))));
			$filter_groups = $cObj->getCategoryFiltersDetailed($category_id);
			if ($filter_groups) {
				foreach ($filter_groups as $filter_group) {
					$display_filter_group = false;
					$childen_data = array();
					foreach ($filter_group['filter'] as $filter) {
						$filter_data = array(
							'filter_category_id' => $category_id,
							'filter_filter'      => $filter['filter_id']
							);
						$prObj= new Products();
						$prObj->joinWithDetailTable();
						$prObj->joinWithCategoryTable();
						$prObj->joinWithBrandsTable();
						$prObj->addSpecialPrice();
						$prObj->joinWithDetailTable();
						$products_count = $prObj->getProductsCount(array_merge($conditions,array("filters"=>$filter['filter_id'])));
						if ($products_count)
							$display_filter_group = true;
						$childen_data[] = array(
							'filter_id' => $filter['filter_id'],
							'name'      => $filter['name'] . ' (' . $products_count . ')',
							'is_filter_checked' => in_array($filter["filter_id"],$post["filters"])?1:'',
							'is_disabled' => $products_count==0?1:'',
							'count' => $products_count
							);
					}
					$data['filter_groups'][] = array(
						'filter_group_id' => $filter_group['filter_group_id'],
						'name'            => $filter_group['name'],
						'display_filter_group'            => $display_filter_group,
						'filters'          => $childen_data
						);
				}
			}
			$arr_filter_groups=$data["filter_groups"];
		}
		$pObj= new Products();
		$pObj->joinWithDetailTable();
		$pObj->joinWithCategoryTable();
		$pObj->joinWithBrandsTable();
		$pObj->joinWithPromotionsTable();
		$pObj->addSpecialPrice();
		$pObj->setPageSize($criteria['pagesize']);
		$pObj->setPageNumber($criteria['page']);
		$pObj->selectFields(array('tp.*','ts.shop_id','ts.shop_user_id','ts.shop_name','ts.shop_title','tu.user_id','tu.user_name','tu.user_username','tu.user_email','IF(prod_stock >0, "1", "0" ) as available'));
		$products = $pObj->getProducts($criteria);
		//die($pObj->getquery());
		foreach($products as $pkey=>$pval){
			$arr = $pObj->product_additional_info($pval);
			$arr_products[] = array_merge($pval,$arr); 
		}
		$total_records = $pObj->getTotalRecords();
		$total_pages = $pObj->getTotalPages();
		die(convertToJson(array('page'=>$page,'count'=>$total_records,'price_ranges'=>$price_ranges,'display_brands_box'=>$display_brands_box,'brands'=>$arr_brands,'filtergroups'=>$arr_filter_groups,'total_pages'=>$total_pages,'products'=>$arr_products,'html'=>$this->_template->render(false,false,'common/json/product_thumb_template.php',true),'brands_box_html'=>$this->_template->render(false,false,'common/json/brands_box_template.php',true),'filtergroups_box_html'=>$this->_template->render(false,false,'common/json/filters_box_template.php',true),'empty_box_html'=>$this->_template->render(false,false,'common/json/empty_template.php',true))));
	}
	die(0);
}
protected function getNewListForm($product_id)	{
	$random_id=rand(1,1000);
	$frm=new Form('frmList','frmList'.$random_id);
	$frm->setOnSubmit('add_ajax_list(this, system_validator'.$random_id.'); return(false);');
	$frm->setRequiredStarWith('xx');
	$frm->setValidatorJsObjectName('system_validator'.$random_id);
	$frm->setAction('?');
	$frm->addHiddenField('', 'random_id',$random_id);
	$frm->addHiddenField('', 'product_id',$product_id);
	$fld=$frm->addRequiredField('', 'ulist_title','','name','class="form-control" Title="'.Utilities::getLabel('M_List_Name').'" Placeholder="'.Utilities::getLabel('M_New_List').'"');
	$fld1=$frm->addSubmitButton('','btn_submit',Utilities::getLabel('F_Add'),'','class="btn-submit"');
	$fld->attachField($fld1);
	$frm->setJsErrorDisplay('afterfield');
	return $frm;
}
public function loadDropDown($parent_id = 0, $type = ''){
	$states = new States();
	$dd = array();
	switch(strtoupper($type)){
		case 'STATES':
		$dd[0] = Utilities::getLabel('F_SELECT_STATE');
		$dd = $states->getStatesAssoc($parent_id);
		break;
		case 'SUBPACKAGES':
		$dd[0] = Utilities::getLabel('F_SELECT_SUBPACKAGE');
		$subPack = new SubPackages();
		$criteria['merchantsubpack_merchantpack_id'] = $parent_id;
		$criteria['exclude_free_package'] = true;
		$dd = $subPack->getAssocSubPackages($criteria);
		break;
		default:
		$dd = array();
	}
	ksort($dd);
	if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['a']) && intval($_POST['a']) == 1){
		echo convertToJson($dd);
		return false;
	}
	return $dd;
}
function brands_autocomplete(){
	$post = Syspage::getPostedVar();
	$json = array();
	$criteria=array(
		"keyword"=>urldecode($post["keyword"]),
		"status"=>1,
		"pagesize"=>10
		);
	$brandObj=new Brands();			
	$brands = $brandObj->getBrands($criteria);
	foreach ($brands as $key => $brand) {
		$json[] = array(
			'data' => $brand['brand_id'],
			'value'      => strip_tags(htmlentities($brand['brand_name'], ENT_QUOTES, 'UTF-8'))
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
function products_autocomplete($show_vendor_name){
	$post = Syspage::getPostedVar();
	$json = array();
	$criteria=array(
		"name"=>urldecode($post["keyword"]),
		"shop"=>urldecode($post["shop"]),
		"added_by"=>urldecode($post["user_id"]),
		"pagesize"=>10,
		"status"=>1
		);
	$productObj= new Products();
	$products = $productObj->getProducts($criteria);
	foreach ($products as $key => $product) {
		$product_name=$product['prod_name'];
		if ($show_vendor_name)
		$product_name.=' ('.Utilities::getLabel('L_Vendor').':'.$product['user_username'].')';
		$json[] = array(
			'data' => $product['prod_id'],
			'value'      => strip_tags($product_name),
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
function filtergroupoptions_autocomplete(){
	$post = Syspage::getPostedVar();
	$json = array();
	$criteria=array(
		"keyword"=>urldecode($post["keyword"]),
		"pagesize"=>10
		);
	$filterGroupOptionObj=new Filtergroupoptions();			
	$filterGroupOptions = $filterGroupOptionObj->getFilterGroupOptions($criteria);
	foreach ($filterGroupOptions as $key => $filterGroupOption) {
		$json[] = array(
			'data' => $filterGroupOption['filter_id'],
			'value'      => strip_tags(html_entity_decode($filterGroupOption['filter_group_name'] . ' &gt; ' . $filterGroupOption['filter_name'], ENT_QUOTES, 'UTF-8'))
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
function child_categories($id){
	$cObj=new Categories();
	$arr = array();
	if ($id>0)
		$arr = $cObj->getParentAssociativeArray($id,1);
	echo json_encode($arr);
}
function categories_autocomplete($type){
	$post = Syspage::getPostedVar();
	$json = array();
	$categories=Categories::getCatgeoryTreeStructure(0,'','','',$type);
	$matches=$categories;
	if (!empty($post["keyword"])){
		$search_keyword=urldecode($post["keyword"]);
		$matches = array();
		foreach($categories as $k=>$v) {
			if(!(stripos($v, $search_keyword) === false)) {
				$matches[$k] = $v;
			}
		}
	}
	foreach($matches as $key=>$val){
		$json[] = array(
			'data' => $key,
			'value'      => strip_tags(html_entity_decode($val, ENT_QUOTES, 'UTF-8'))
//strip_tags(html_entity_decode($val, ENT_QUOTES, 'UTF-8'))//strip_tags(html_entity_decode($val, ENT_QUOTES, 'UTF-8'))
			);
	}
	$sort_order = array();
	foreach ($json as $key => $value) {
		$sort_order[$key] = $value['name'];
	}
	array_multisort($sort_order, SORT_ASC, $json);
	$arr["suggestions"]=array_slice($json,0,20);
	echo json_encode($arr);
//echo json_encode(array_slice($json,0,10));
}
function options_autocomplete(){
	$post = Syspage::getPostedVar();
	$json = array();
	$criteria=array(
		"keyword"=>urldecode($post["keyword"]),
		"pagesize"=>10,
		"owner_check"=>"Y",
		"owner_check_by_id"=>$post["owner"] 
		);
	$optionObj=new Options();			
	$Options = $optionObj->getOptions($criteria);
	foreach ($Options as $key => $option) {
		$option_value_data = array();
		if ($option['option_type'] == 'select' || $option['option_type'] == 'radio' || $option['option_type'] == 'checkbox' || $option['option_type'] == 'image') {
			$option_values = $optionObj->getOptionValues($option['option_id']);
			foreach ($option_values as $option_value) {
				$option_value_data[] = array(
					'option_value_id' => $option_value['option_value_id'],
					'name'            => strip_tags(htmlentities($option_value['option_value_name'], ENT_QUOTES, 'UTF-8')),
					'image'           => $option_value['option_value_image']
					);
			}
			$sort_order = array();
			foreach ($option_value_data as $key => $value) {
				$sort_order[$key] = $value['name'];
			}
//array_multisort($sort_order, SORT_ASC, $option_value_data);
		}
		$type = '';
		if ($option['option_type'] == 'select' || $option['option_type'] == 'radio' || $option['option_type'] == 'checkbox' || $option['option_type'] == 'image') {
			$type = "Choose";
		}
		if ($option['option_type'] == 'text' || $option['option_type'] == 'textarea') {
			$type = "Input";
		}
		if ($option['option_type'] == 'file') {
			$type = "File";
		}
		if ($option['option_type'] == 'date' || $option['option_type'] == 'datetime' || $option['option_type'] == 'time') {
			$type = "Date";
		}
		$json[] = array(
			'option_id'    => $option['option_id'],
			'name'         => strip_tags(html_entity_decode($option['option_name'], ENT_QUOTES, 'UTF-8')),
			'category'     => $type,
			'type'         => $option['option_type'],
			'option_value' => $option_value_data
			);
	}		
	$sort_order = array();
	foreach ($json as $key => $value) {
		$sort_order[$key] = $value['name'];
	}
	array_multisort($sort_order, SORT_ASC, $json);
	echo json_encode($json);
}
function zones_autocomplete(){
	$post = Syspage::getPostedVar();
	$json = array();
	$criteria=array(
		"keyword"=>urldecode($post["keyword"]),
		"pagesize"=>10
		);
	$zoneObj=new Zones();			
	$zones = $zoneObj->getZones($criteria);
	foreach ($zones as $key => $zone) {
		$json[] = array(
			'id' => $zone['zone_id'],
			'name'      => strip_tags(htmlentities($zone['zone_name'], ENT_QUOTES, 'UTF-8'))
			);
	}						
	$sort_order = array();
	foreach ($json as $key => $value) {
		$sort_order[$key] = $value['name'];
	}
	array_multisort($sort_order, SORT_ASC, $json);
	echo json_encode($json);
}
function shipping_autocomplete(){
	$post = Syspage::getPostedVar();
	$json = array();
	$criteria=array(
		"keyword"=>urldecode($post["keyword"]),
		"pagesize"=>10
		);
	$shippingObj=new Shippingcompany();			
	$shippingCompanies = $shippingObj->getShippingcompanies($criteria);
	foreach ($shippingCompanies as $key => $shippingcompany) {
		$json[] = array(
			'data' => $shippingcompany['scompany_id'],
			'value'      => strip_tags(htmlentities($shippingcompany['scompany_name'], ENT_QUOTES, 'UTF-8'))
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
function countries_autocomplete(){
	$post = Syspage::getPostedVar();
	$json = array();
	$criteria=array(
		"keyword"=>urldecode($post["keyword"]),
		"pagesize"=>10
		);
	$countryObj=new Countries();			
	$countries = $countryObj->getCountries($criteria);
	foreach ($countries as $key => $country) {
		$json[] = array(
			'data' => $country['country_id'],
			'value'      => strip_tags(htmlentities($country['country_name'], ENT_QUOTES, 'UTF-8'))
			);
	}			  
	$sort_order = array();
	foreach ($json as $key => $value) {
		$sort_order[$key] = $value['name'];
	}
	$arr["suggestions"]=$json;
	echo json_encode($arr);
//echo json_encode($json);
}
function shippingduration_autocomplete(){
	$post = Syspage::getPostedVar();
	$json = array();
	$criteria=array(
		"keyword"=>urldecode($post["keyword"]),
		"pagesize"=>10
		);
	$shippingDurationObj=new Shippingduration();			
	$shippingDurations = $shippingDurationObj->getShippingdurations($criteria);
	foreach ($shippingDurations as $key => $shippingduration) {
		$json[] = array(
			'data' => $shippingduration['sduration_id'],
			'value'      => strip_tags(htmlentities($shippingduration['sduration_label'], ENT_QUOTES, 'UTF-8'))
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
function attributes_autocomplete(){
	$post = Syspage::getPostedVar();
	$json = array();
	$criteria=array(
		"keyword"=>urldecode($post["keyword"]),
		"pagesize"=>10
		);
	$attributesObj=new Attributes();			
	$attributes = $attributesObj->getAttributes($criteria);
	foreach ($attributes as $key => $attribute) {
		$json[] = array(
			'attribute_id' => $attribute['attribute_id'],
			'name'      => strip_tags(htmlentities($attribute['attribute_name'], ENT_QUOTES, 'UTF-8')),
			'attribute_group'      => htmlentities($attribute['attribute_group_name'])
			);
	}	
	$sort_order = array();
	foreach ($json as $key => $value) {
		$sort_order[$key] = $value['name'];
	}
	array_multisort($sort_order, SORT_ASC, $json);
	echo json_encode($json);
}
function producttags_autocomplete(){
	$post = Syspage::getPostedVar();
	$json = array();
	$criteria=array(
		"keyword"=>urldecode($post["keyword"]),
		"pagesize"=>10
		);
	$ptObj=new Producttags();			
	$tags = $ptObj->getProductTags($criteria);
	foreach ($tags as $key => $tag) {
		$json[] = array(
			'data' => $tag['ptag_id'],
			'value'      => strip_tags(htmlentities($tag['ptag_name'], ENT_QUOTES, 'UTF-8')),
			);
	}
	$sort_order = array();
	foreach ($json as $key => $value) {
		$sort_order[$key] = $value['value'];
	}
	//array_multisort($sort_order, SORT_ASC, $json);
	$arr["suggestions"]=$json;
	echo json_encode($arr);
//echo json_encode($json);
}
function search_producttags_autocomplete(){
	$post = Syspage::getPostedVar();
	$json = array();
	$criteria=array(
		"keyword"=>urldecode($post["keyword"]),
		"pagesize"=>10
		);
	$ptObj=new Producttags();			
	$tags = $ptObj->getProductTags($criteria);
	foreach ($tags as $key => $tag) {
		$json[] = array(
			'data' => $tag['ptag_id'],
			'value'      => strip_tags(htmlentities($tag['ptag_name'], ENT_QUOTES, 'UTF-8')),
			);
	}
	$sort_order = array();
	foreach ($json as $key => $value) {
		$sort_order[$key] = $value['value'];
	}
	//array_multisort($sort_order, SORT_ASC, $json);
	$arr["suggestions"]=$json;
	echo json_encode($arr);
}
function affiliates_autocomplete(){
	$post = Syspage::getPostedVar();
	$json = array();
	$criteria=array(
		"keyword"=>urldecode($post["keyword"]),
		"pagesize"=>10
		);
	$aObj=new Affiliate();	
	$affiliates = $aObj->getAffiliates($criteria);
	foreach ($affiliates as $key => $val) {
		$json[] = array(
			'data' => $val['affiliate_id'],
			'value'      => strip_tags(html_entity_decode(htmlentities($val['affiliate_name']) . ' [' . $val['affiliate_username'].']', ENT_QUOTES, 'UTF-8'))
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
function categories_autocomplete_without_level(){
	$cObj=new Categories();
	$post = Syspage::getPostedVar();
	$json = array();
	$categories=$cObj->getCategories(array("keyword"=>urldecode($post["keyword"]),"parent"=>0,"type"=>1,"pagesize"=>10));
	foreach($categories as $ckey=>$cval){
		$json[] = array(
			'data' => $cval['category_id'],
			'value'      => strip_tags(html_entity_decode($cval['category_name'], ENT_QUOTES, 'UTF-8'))
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
function users_autocomplete(){
	$post = Syspage::getPostedVar();
	$json = array();
	$criteria=array(
		"keyword"=>urldecode($post["keyword"]),
		"pagesize"=>10
		);
	$uObj=new User();	
	$users = $uObj->getUsers($criteria);
	foreach ($users as $key => $val) {
		$json[] = array(
			'data' => $val['user_id'],
			'value'      => strip_tags(html_entity_decode($val['user_name'] . ' [' . $val['user_username'].']', ENT_QUOTES, 'UTF-8'))
			);
	}		
	$sort_order = array();
	foreach ($json as $key => $value) {
		$sort_order[$key] = $value['name'];
	}
	array_multisort($sort_order, SORT_ASC, $json);
	$arr["suggestions"]=$json;
	echo json_encode($arr);
}
function file_upload(){
	$post = Syspage::getPostedVar();
	$json = array();
	$json['button_text']=Utilities::getLabel('M_Upload_file');
	if (!empty($_FILES['file']['name']) && is_file($_FILES['file']['tmp_name'])) {
// Sanitize the filename
		$filename = basename(preg_replace('/[^a-zA-Z0-9\.\-\s+]/', '', html_entity_decode($_FILES['file']['name'], ENT_QUOTES, 'UTF-8')));
// Validate the filename length
		if ((Utilities::utf8_strlen($filename) < 3) || (Utilities::utf8_strlen($filename) > 64)) {
			$json['error'] = Utilities::getLabel('M_Error_Filename');
		}
// Allowed file extension types
		$allowed = array();
		$extension_allowed = preg_replace('~\r?\n~', "\n", Settings::getSetting("CONF_FILE_EXT_ALLOWED"));
		$filetypes = explode("\n", $extension_allowed);
		foreach ($filetypes as $filetype) {
			$allowed[] = trim($filetype);
		}
		if (!in_array(strtolower(substr(strrchr($filename, '.'), 1)), $allowed)) {
			$json['error'] = Utilities::getLabel('M_Error_Filetype');
		}
// Allowed file mime types
		$allowed = array();
		$mime_allowed = preg_replace('~\r?\n~', "\n", Settings::getSetting("CONF_FILE_MIME_ALLOWED"));
		$filetypes = explode("\n", $mime_allowed);
		foreach ($filetypes as $filetype) {
			$allowed[] = trim($filetype);
		}
		if (!in_array($_FILES['file']['type'], $allowed)) {
			$json['error'] = Utilities::getLabel('M_Error_Filetype');
		}
// Check to see if any PHP files are trying to be uploaded
		$content = file_get_contents($_FILES['file']['tmp_name']);
		if (preg_match('/\<\?php/i', $content)) {
			$json['error'] = Utilities::getLabel('M_Error_Filetype');
		}
// Return any upload error
		if ($_FILES['file']['error'] != UPLOAD_ERR_OK) {
			$json['error'] = Utilities::getLabel('M_Error_Upload_'. $_FILES['file']['error']);
		}
	} else {
		$json['error'] = Utilities::getLabel('M_Error_Upload');
	}
	if (!$json['error']) {
		$file = $filename . '.' . md5(mt_rand());
		if(!Utilities::saveFile($_FILES['file']['tmp_name'],$_FILES['file']['name'], $response, 'front-users/')){
			$json['error'] = $response;
		}else{
			$json['code']=$response;
			$json['success'] = sprintf(Utilities::getLabel('M_File_Uploaded_Successfully'),$_FILES['file']['name']);
		}
	}
	echo json_encode($json);
}
function subscribe(){
	require_once ('Mailchimp.php');
	$post = Syspage::getPostedVar();
	$json = array();
	$api_key = Settings::getSetting("CONF_MAILCHIMP_KEY");
	$list_id = Settings::getSetting("CONF_MAILCHIMP_LIST_ID");
	$Mailchimp = new Mailchimp( $api_key );
	$Mailchimp_Lists = new Mailchimp_Lists( $Mailchimp );
	try{
		$subscriber = $Mailchimp_Lists->subscribe( $list_id, array( 'email' => htmlentities($post['email'])));
		if (!empty( $subscriber['leid'] ) ) {
			$json['message'] = Utilities::getLabel('M_Successfully_subscribed');
		} else {
			$json['message'] = Utilities::getLabel('M_Newsletter_subscription_valid_email');
		}
	} catch(Mailchimp_Error $e){
			$json['message'] = $e->getMessage();
    		
	}
	echo json_encode($json);
}
function min_price_criteria() {
	$json = array();
	$post = Syspage::getPostedVar();
	$price=$post["prod_sale_price"];
	if (is_numeric($price) && ($price < Settings::getSetting("CONF_MIN_PRODUCT_PRICE"))){
		$json['error']=1;
		$json['message']=sprintf(Utilities::getLabel('M_Minimum_Selling_Price'),Utilities::displayMoneyFormat(Settings::getSetting("CONF_MIN_PRODUCT_PRICE")));
	}
	echo json_encode($json);
}
function check_is_shipping_mode() {
	$json = array();
	$post = Syspage::getPostedVar();
	if ($post["val"]==Settings::getSetting("CONF_DEFAULT_SHIPPING_ORDER_STATUS")){
		$json["shipping"]=1;
	}
	echo json_encode($json);
}
function set_cookie(){
	$post = Syspage::getPostedVar();
	$tObj=new Themes();
	$theme = $tObj->getThemeById($post['theme']);
	setcookie('visitor_theme_cookie', $post['theme'], time()+3600*24*7,'/');
	unset($_SESSION['preview_theme']);
	echo json_encode($theme);
}
function chat(){
	$this->_template->render(false,false);
}
function promotion_track_clicks(){
	$post = Syspage::getPostedVar();
	$arr_p = explode("-",$post['u']);
	$promotion_id = $arr_p[count($arr_p)-1];
	if (!empty($promotion_id)){
		$promObj=new Promotions();
		$arrData=array('promotion_id'=>$promotion_id);	
		$promObj->addPromotionAnalysisRecord($arrData,"clicks");
//die("Done");
	}
}
function promotion_track_impressions(){
	$post = Syspage::getPostedVar();
	$arr_p = explode("-",$post['u']);
	$promotion_id = $arr_p[count($arr_p)-1];
	if (!empty($promotion_id)){
		$promObj=new Promotions();
		$arrData=array('promotion_id'=>$promotion_id);	
		$promObj->addPromotionAnalysisRecord($arrData);
	}
}	
}
