<?php
class ApiController extends CommonController {
	function __construct($model, $controller, $action){
		$this->pagesize=10;
		$uObj=new user();
		$post = Syspage::getPostedVar();
		$user_token=$post["_token"];
		foreach($post as $key=>$val){
			$mail_body_str.=$key."#".$val."<br/>";
		}
		if (!(empty($user_token))){
			$user = $uObj->getUser(array('token'=>$user_token));
			if ($user){
				$this->app_user=$user;
			}else{
				dieJsonError(Utilities::getLabel('L_Invalid_Code'));
			}
		}
		$user_logged_in_pages = array(
			'messages',
			'addresses',
			'addresses_setup',
			'delete_addresses',
			'primary_address',
			'favorite_products',
			'add_to_cart',
			'cart_billing_address_update',
			'cart_shipping_address_update',
			'cart',
			'cart_edit_qty',
			'cart_remove_item',
			'cart_apply_coupon',
			'update_cart_items_shipping_method',
			'ask_a_question',
			'shop_send_message',
			'mark_product_favorite',
			'get_temp_token',
			'buy_product',
			'orders'
			);
		if(in_array($action,$user_logged_in_pages)){
			if(!$this->app_user["user_id"]>0){
				dieJsonError(Utilities::getLabel('L_Please_login_or_login_again'));
			}
		}
		$cartObj=new cart($this->app_user["user_id"]);
		$this->cart_items=$cartObj->countProducts();
		$userObj=new user();
		$this->user_details=$userObj->getUserById($this->app_user["user_id"]);
	}
	function home(){
		require_once (CONF_INSTALLATION_PATH . 'public/includes/phpfastcache.php');
// simple Caching with:
		phpFastCache::setup("storage","files");
		phpFastCache::setup("path", CONF_USER_UPLOADS_PATH."caching");
		$cache = phpFastCache();
//$api_home_page_elements = $cache->get("api_home_page_elements");
		if($api_home_page_elements == null) {
			$collectionObj=new Collections();
			$pObj=new ApiProducts();					
//$home_page_collections=$collectionObj->getCollections(array("status"=>1,"front_end"=>1,"type"=>array("P","C")));
			$home_page_collections=$collectionObj->getCollections(array("status"=>1,"front_end"=>1));
			foreach($home_page_collections as $hcol=>$hval){
				$collection_products=array();
				$collection_shops=array();
				$banner_enabled=(!empty($hval["collection_image"]))?1:0;
				$banner_image=$banner_enabled?Utilities::generateAbsoluteUrl('image', 'collection', array($hval['collection_image'],1)):'';
//$collection_products=$hval["collection_products"];
//$collection_shops=$hval["collection_shops"];
				if ($hval["collection_type"]=="C"){
					foreach($hval["collection_categories"] as $colcat=>$colcatval){
						foreach($colcatval["products"] as $colcatprodkey=>$colcatprodval){
							array_push($collection_products,$colcatprodval);
						}
					}
				}else if ($hval["collection_type"]=="S"){
					foreach($hval["collection_shops"] as $colcat=>$colcatval){
//foreach($colcatval["products"] as $colcatprodkey=>$colcatprodval){
						array_push($collection_shops,$colcatval);
//}
					}
				}
				$collection_products=array_slice($collection_products,0,3);
				$collection_shops=array_slice($collection_shops,0,3);
				if (count($collection_products)>0){
					$arr = array(
						'caption'=>$hval["collection_display_title"],
						'show_more_enabled'=>0,
						'show_more_keyword'=>'',
						'banner_enabled'=>$banner_enabled,
						'banner_image'=>$banner_image,
						'banner_action_type'=>0, /* 0=>no action 1=>open link in browser, 2=>Search products with keyword */
						'banner_action_title'=>'', /* needed for search */
						'banner_action_string'=>'', /* url if to open in browser or search keyword  */
						'products'=>array()
						);
					foreach($collection_products as $pkey=>$colprod){
						$pObj=new ApiProducts();
						$pObj->addSpecialPrice();
						$pObj->selectFields(array('tp.prod_id','tp.prod_name','tp.prod_sale_price','tp.prod_stock','ts.shop_name','IF(prod_stock >0, "1", "0" ) as available'));			
						$product=$pObj->getData($colprod["prod_id"],array("favorite"=>$this->app_user["user_id"]));
						if ($product){
							$price=$product['prod_sale_price'];
							$product_price=(!$product['special'])?$price:$product['special'];
							$arr['products'][] = array(
								'product_id'=>$product["prod_id"],
								'product_name' => $product["prod_name"], 
								'store_name' => $product["shop_name"], 
								'price_currency' => CONF_CURRENCY_SYMBOL,
								'product_price'=>$product_price,
								'favorite'=>$product["favorite"]?1:0,
								'product_stock'=>max($product['prod_stock'],0),
								'product_available'=>($product["available"]>0)?1:0,
								'product_image'=>Utilities::generateAbsoluteUrl('image','product_image',array($product["prod_id"]))
								);
						}
					}
					$api_home_page_elements[] = $arr;
				}
				if(count($collection_shops)>0){
					foreach($collection_shops as $val){
						$arr_shops['shops'][]=array(
							'id'=>$val['shop_id'],
							'name'=>$val['shop_name'],
							'logo'=>Utilities::generateAbsoluteUrl('image','shop_logo',array($val['shop_logo'])),
							);
					}
					$api_home_page_elements[] = $arr_shops;
				} 
			}
			$cache->set("api_home_page_elements",$api_home_page_elements , 12*60*60);
		}
		die (json_encode(array('status'=>1, 'fav_count'=>$this->user_details['favItems'],'cart_count'=>$this->cart_items, 'unread_messages'=>$this->user_details['unreadMessages'],'data'=>$api_home_page_elements)));
	}
	function categories($id){
		$id=intVal($id);
		$cObj=new Categories();
		$categories=$cObj->getCategories(array("category_parent_id"=>$id));
		$pObj= new ApiProducts();
		$pObj->joinWithCategoryTable();
		$pObj->addSpecialPrice();
		foreach($categories as $subcat=>$subval){
			if($id==$subval["category_id"]){continue;}			
			$has_subcategories=0;												
			$category_structure=$cObj->getCategoriesAssocArrayFront($subval["category_id"]);			 
			foreach($category_structure[$subval["category_id"]] as $tkey=>$tval){
				$criteria=array("category"=>$tval["category_id"]);
				$sub_category_products=$pObj->getProducts($criteria);				
				if (is_array($sub_category_products) && !empty($sub_category_products)){
					$has_subcategories=1;
					break;	
				}
			} 
			$sub_category_id = $subval["category_id"];						
			$criteria=array("category"=>$sub_category_id,"favorite"=>$this->app_user["user_id"]/* ,"pagesize"=>3 */);
			$pObj= new ApiProducts();
			$pObj->joinWithDetailTable();
			$pObj->joinWithCategoryTable();
			$pObj->addSpecialPrice();
			$pObj->selectFields(array('tp.prod_id','tp.prod_name','tp.prod_sale_price','tp.prod_stock','ts.shop_name','IF(prod_stock >0, "1", "0" ) as available'));	
			$category_products=$pObj->getProducts($criteria);				
			if ((count($category_products)>0) && is_array($category_products)){				
				$catcount++;
				$arr_categories[] = array(
					'category_id'=>$sub_category_id,
					'category_name' => $subval["category_name"], 
					'has_subcategories'=>$has_subcategories,					
					'product_count'=>count($category_products),					
					'products'=>array()
					);
				$count=1;
				foreach($category_products as $ckey=>$product){
					if($count>3){break;}
					$price=$product['prod_sale_price'];
					$product_price=(!$product['special'])?$price:$product['special'];
					$arr_categories[$catcount-1]['products'][] = array(
						'product_id'=>$product["prod_id"],
						'product_name' => $product["prod_name"], 
						'store_name' => $product["shop_name"], 
						'price_currency' => CONF_CURRENCY_SYMBOL,
						'product_price'=>$product_price,
						'favorite'=>$product["favorite"]?1:0,
						'product_stock'=>max($product['prod_stock'],0),
						'product_available'=>($product["available"]>0)?1:0,
						'product_image'=>Utilities::generateAbsoluteUrl('image','product_image',array($product["prod_id"]))
						);
					$count++;
				}				
			}
		}
//die (json_encode(array('status'=>1, 'categories'=>$arr_categories)));
		die (json_encode(array('status'=>1,'fav_count'=>$this->user_details['favItems'],'cart_count'=>$this->cart_items,'unread_messages'=>$this->user_details['unreadMessages'], 'categories'=>$arr_categories)));
	}
	function category_products(){
		$arr_products = array();
		$page = 1;
		$pagesize=$this->pagesize;
		$category_id=(isset($_REQUEST['category_id']) && intval($_REQUEST['category_id']) > 0)?intval($_REQUEST['category_id']):0;
		if(isset($_REQUEST['page']) && intval($_REQUEST['page']) > 0) $page = intval($_REQUEST['page']);
		if(isset($_REQUEST['pagesize']) && intval($_REQUEST['pagesize']) > 0) $pagesize = intval($_REQUEST['pagesize']);
		$criteria=array("category"=>$category_id,"sort"=>"feat","favorite"=>$this->app_user["user_id"],"pagesize"=>$pagesize,"page"=>$page);
		$pObj=new ApiProducts();
		$pObj->joinWithDetailTable();
		$pObj->joinWithPromotionsTable();
		$pObj->joinWithCategoryTable();
		$pObj->addSpecialPrice();
		$pObj->selectFields(array('tp.prod_id','tp.prod_name','tp.prod_sale_price','tp.prod_stock','ts.shop_name','IF(prod_stock >0, "1", "0" ) as available'));	
		$all_products=$pObj->getProducts($criteria);
		foreach($all_products as $pkey=>$product){
			$price=$product['prod_sale_price'];
			$product_price=(!$product['special'])?$price:$product['special'];
			$arr_products[] = array(
				'product_id'=>$product["prod_id"],
				'product_name' => $product["prod_name"], 
				'store_name' => $product["shop_name"], 
				'price_currency' => CONF_CURRENCY_SYMBOL,
				'product_price'=>$product_price,
				'favorite'=>$product["favorite"]?1:0,
				'product_stock'=>max($product['prod_stock'],0),
				'product_available'=>($product["available"]>0)?1:0,
				'product_image'=>Utilities::generateAbsoluteUrl('image','product_image',array($product["prod_id"]))
				);
		}
		die (json_encode(array('status'=>1,'fav_count'=>$this->user_details['favItems'],'cart_count'=>$this->cart_items,'unread_messages'=>$this->user_details['unreadMessages'],'products'=>$arr_products,'total_records'=>$pObj->getTotalRecords())));
	}
	function products(){
		$arr_products = array();
		$page = 1;
		$pagesize=$this->pagesize;
		if(isset($_REQUEST['page']) && intval($_REQUEST['page']) > 0) $page = intval($_REQUEST['page']);
		if(isset($_REQUEST['pagesize']) && intval($_REQUEST['pagesize']) > 0) $pagesize = intval($_REQUEST['pagesize']);
		$criteria=array("sort"=>"feat","favorite"=>$this->app_user["user_id"],"pagesize"=>$pagesize,"page"=>$page);
		$pObj=new ApiProducts();
		$pObj->joinWithDetailTable();
		$pObj->joinWithPromotionsTable();
		$pObj->joinWithCategoryTable();
		$pObj->addSpecialPrice();
		$pObj->selectFields(array('tp.prod_id','tp.prod_name','tp.prod_sale_price','tp.prod_stock','ts.shop_name','IF(prod_stock >0, "1", "0" ) as available'));	
		$all_products=$pObj->getProducts($criteria);
		foreach($all_products as $pkey=>$product){
			$price=$product['prod_sale_price'];
			$product_price=(!$product['special'])?$price:$product['special'];
			$arr_products[] = array(
				'product_id'=>$product["prod_id"],
				'product_name' => $product["prod_name"], 
				'store_name' => $product["shop_name"], 
				'price_currency' => CONF_CURRENCY_SYMBOL,
				'product_price'=>$product_price,
				'favorite'=>$product["favorite"]?1:0,					
				'product_image'=>Utilities::generateAbsoluteUrl('image','product_image',array($product["prod_id"]))
				);
		}
		die (json_encode(array('status'=>1,'fav_count'=>$this->user_details['favItems'],'cart_count'=>$this->cart_items, 'unread_messages'=>$this->user_details['unreadMessages'],'products'=>$arr_products,'total_records'=>$pObj->getTotalRecords())));
	}
	function featured_products(){
		$arr_products = array();
		$page = 1;
		$pagesize=$this->pagesize;
		if(isset($_REQUEST['page']) && intval($_REQUEST['page']) > 0) $page = intval($_REQUEST['page']);
		if(isset($_REQUEST['pagesize']) && intval($_REQUEST['pagesize']) > 0) $pagesize = intval($_REQUEST['pagesize']);
		$criteria=array("property"=>'prod_featuered',"favorite"=>$this->app_user["user_id"],"pagesize"=>$pagesize,"page"=>$page);
		$pObj=new ApiProducts();
		$pObj->joinWithDetailTable();
		$pObj->addSpecialPrice();
		$pObj->selectFields(array('tp.prod_id','tp.prod_name','tp.prod_sale_price','tp.prod_stock','ts.shop_name','IF(prod_stock >0, "1", "0" ) as available'));	
		$all_products=$pObj->getProducts($criteria);
		foreach($all_products as $pkey=>$product){
			$price=$product['prod_sale_price'];
			$product_price=(!$product['special'])?$price:$product['special'];
			$arr_products[] = array(
				'product_id'=>$product["prod_id"],
				'product_name' => $product["prod_name"], 
				'store_name' => $product["shop_name"], 
				'price_currency' => CONF_CURRENCY_SYMBOL,
				'product_price'=>$product_price,
				'favorite'=>$product["favorite"]?1:0,
				'product_image'=>Utilities::generateAbsoluteUrl('image','product_image',array($product["prod_id"]))
				);
		}
		die (json_encode(array('status'=>1,'fav_count'=>$this->user_details['favItems'],'cart_count'=>$this->cart_items, 'unread_messages'=>$this->user_details['unreadMessages'],'products'=>$arr_products,'total_records'=>$pObj->getTotalRecords())));
	}
	function search($keyword){ 
		$arr_products = array();
		$page = 1;
		$pagesize=$this->pagesize;
		if(isset($_REQUEST['keyword']) && trim($_REQUEST['keyword']) !=''){ $keyword = $_REQUEST['keyword'];}
		if(isset($_REQUEST['page']) && intval($_REQUEST['page']) > 0) $page = intval($_REQUEST['page']);
		if(isset($_REQUEST['pagesize']) && intval($_REQUEST['pagesize']) > 0) $pagesize = intval($_REQUEST['pagesize']);
//"favorite"=>$this->app_user["user_id"],
		$criteria=array("sort"=>"best","pagesize"=>$pagesize,"page"=>$page,"donotspecial"=>1);
		if($keyword!=''){
			$criteria['keyword']=$keyword;
		}			
		$pObj= new ApiProducts();
		$pObj->joinWithDetailTable();
		$pObj->joinWithCategoryTable();
		$pObj->joinWithBrandsTable();
		$pObj->joinWithPromotionsTable();
		$pObj->addSpecialPrice();
		$pObj->selectFields(array('tp.prod_id','tp.prod_name','tp.prod_sale_price','tp.prod_stock','ts.shop_name','IF(prod_stock >0, "1", "0" ) as available'));	
		$all_products=$pObj->getProducts($criteria);
		foreach($all_products as $pkey=>$product){
			$price=$product['prod_sale_price'];
			$product_price=(!$product['special'])?$price:$product['special'];	
			$arr_products[] = array(
				'product_id'=>$product["prod_id"],
				'product_name' => $product["prod_name"], 
				'store_name' => $product["shop_name"], 
				'price_currency' => CONF_CURRENCY_SYMBOL,
				'product_price'=>$product_price,
				'favorite'=>$product["favorite"]?1:0,
				'product_stock'=>max($product['prod_stock'],0),
				'product_available'=>($product["available"]>0)?1:0,
				'product_image'=>Utilities::generateAbsoluteUrl('image','product_image',array($product["prod_id"]))
				);
		}
		die (json_encode(array('status'=>1,'fav_count'=>$this->user_details['favItems'],'cart_count'=>$this->cart_items, 'unread_messages'=>$this->user_details['unreadMessages'],'products'=>$arr_products,'total_records'=>$pObj->getTotalRecords())));
	}
	function product_details($product_id){
		$uObj=new User();
		$post = Syspage::getPostedVar();
		$pObj= new ApiProducts();
		$pObj->joinWithDetailTable();
		$pObj->joinWithCategoryTable();
		$pObj->joinWithBrandsTable();
		$pObj->joinWithReviewsTable();
		$pObj->addSpecialPrice();
		$pObj->selectFields(array('tp.*','prod_length','prod_length_class', 'prod_width', 'prod_height','prod_weight','prod_weight_class','prod_long_desc', 'prod_youtube_video','prod_meta_title', 'prod_meta_keywords', 'prod_meta_description', 'prod_featuered','prod_ship_free', 'prod_tax_free','prod_available_date','tu.user_id','tu.user_username','tu.user_name','tu.user_email','tu.user_phone','IF(prod_stock >0, "1", "0" ) as available','tpb.brand_id','tpb.brand_name','tpb.brand_status','ts.shop_id','ts.shop_user_id','ts.shop_name','ts.shop_title','ts.shop_description'));
		$sObj=new Shops();
		$cObj=new Categories();
		$product_id=isset($_REQUEST['product_id'])?$_REQUEST['product_id']:$product_id;
		$product = $pObj->getData($product_id,array("status"=>1,"favorite"=>$this->app_user["user_id"]));
		if ($product){
			$product_category_structure=$cObj->funcGetCategoryStructure($product["prod_category"]);
			$product_images=$pObj->getProductImages($product_id);
			$product['options'] = array();
			$product_options = $pObj->getProductOptions($product_id);
			foreach ($product_options as $optionkey=>$option) {
				$product_option_value_data = array();
				foreach ($option['product_option_value'] as $option_value) {
					if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
						$out_of_stock="";
					}else{
						$out_of_stock=" - <span class='red'>(".Utilities::getLabel("L_Out_of_stock").")</span>";
					}
					$product_option_value_data[] = array(
						'product_option_value_id' => $option_value['product_option_value_id'],
						'option_value_id'         => $option_value['option_value_id'],
						'name'                    => $option_value['name'].$out_of_stock,
						'price'                   => $option_value['price'],
						'price_prefix'            => $option_value['price_prefix']
						);
				}
				$product['options'][] = array(
					'product_option_id'    => $option['product_option_id'],
					'product_option_value' => $product_option_value_data,
					'option_id'            => $option['option_id'],
					'name'                 => $option['name'],
					'type'                 => $option['type'],
					'value'                => $option['value'],
					'required'             => $option['required']
					);
			}
			$pObj= new ApiProducts();
			$related_products = $pObj->getProductRelated($product_id);
			$product_shop = $sObj->getData($product["prod_shop"],array("favorite"=>$this->app_user["user_id"]));
			$price=$product['prod_sale_price'];
			$product_price=(!$product['special'])?$price:$product['special'];
			$product_price_html='';
			if ($product['special']) {
				$discount_perc=round(($price-$product_price)/$price*100,1);
				$product_price_html='<strike>'.Utilities::displayMoneyFormat($price).'</strike><br><big><b>'.Utilities::displayMoneyFormat($product_price).'</b></big><br><font color="#FF0000">'.$discount_perc.'% Off</font>';
			}
			$arr = array(
				'product_id'=>$product['prod_id'],
				'product_name' => $product['prod_name'], 
				'store_id' => $product["shop_id"],
				'store_name' => $product["shop_name"],
				'store_rating' => $product_shop["shop_rating"],
				'store_logo' => Utilities::generateAbsoluteUrl('image','shop_logo',array($product["shop_logo"])), 
				'store_banner' => Utilities::generateAbsoluteUrl('image','shop_banner',array($product["shop_banner"])), 
				'store_state_name' => $product_shop["state_name"],
				'store_country_name' => $product_shop["country_name"],
				'total_store_products' => $product_shop["totProducts"],  
				'total_store_reviews' => $product_shop["totReviews"],  
				'price_currency' => CONF_CURRENCY_SYMBOL,
				'product_price'=>$product_price,
				'product_price_html'=>$product_price_html,
				'favorite'=>$product['favorite'],
				'product_brief'=>str_replace("\t","",subStringByWords(strip_tags($product['prod_long_desc']),200)),
				'product_rating'=>$product['prod_rating'],
				'product_review_count'=>$product['totReviews'],
				'product_options'=>$product['options'],
				'product_seller_info'=>$product['shop_description'],
				'product_sku'=>$product['prod_sku'],
				'product_model'=>$product['prod_model'],
				'product_brand_id'=>$product['brand_id'],
				'product_brand'=>$product['brand_name'],
				'product_stock'=>max($product['prod_stock'],0),
				'product_available'=>$product["available"],			
				'product_video_url'=>$product['prod_youtube_video'],
				'images'=>array(),
				'product_category_structure'=>array(),
				'related_products'=>array(),
				'seller_more_products'=>array(),
				'product_reviews'=>array(),
				);
			foreach($product_images as $pimg){
				$arr['images'][]=Utilities::generateAbsoluteUrl('image','product',array('MEDIUM',$pimg["image_file"]));
			}
			foreach($product_category_structure as $pcst){
				$arr['product_category_structure'][]=array("id"=>$pcst["category_id"],"name"=>$pcst["category_name"],"parent"=>$pcst["category_parent"]);
			}
			foreach($related_products as $pkey=>$relProd){
				$pObj= new ApiProducts();
				$product = $pObj->getData($relProd["relation_to_id"],array("favorite"=>$this->app_user["user_id"]));
				if ($product){
					$price=$product['prod_sale_price'];
					$product_price=(!$product['special'])?$price:$product['special'];
					$arr['related_products'][] = array(
						'product_id'=>$product["prod_id"],
						'product_name' => $product["prod_name"], 
						'store_name' => $product["shop_name"], 
						'price_currency' => CONF_CURRENCY_SYMBOL,
						'product_price'=>$product_price,
						'favorite'=>$product["favorite"]?1:0,
						'product_image'=>Utilities::generateAbsoluteUrl('image','product_image',array($product["prod_id"]))
						);
				}
			}
			$pObj= new ApiProducts();
			$pObj->joinWithDetailTable();
			$pObj->joinWithCategoryTable();
			$pObj->joinWithBrandsTable();
			$pObj->joinWithPromotionsTable();
			$pObj->addSpecialPrice();
			$pObj->selectFields(array('tp.prod_id','tp.prod_name','tp.prod_sale_price','tp.prod_stock','ts.shop_name','IF(prod_stock >0, "1", "0" ) as available'));	
			$seller_more_products=$pObj->getProducts(array("sort"=>"feat","pagesize"=>4,"shop"=>$product["shop_id"]));
			foreach($seller_more_products as $skey=>$product){
				$price=$product['prod_sale_price'];
				$product_price=(!$product['special'])?$price:$product['special'];
				$arr['seller_more_products'][] = array(
					'product_id'=>$product["prod_id"],
					'product_name' => $product["prod_name"], 
					'store_name' => $product["shop_name"], 
					'price_currency' => CONF_CURRENCY_SYMBOL,
					'product_price'=>$product_price,
					'favorite'=>$product["favorite"]?1:0,
					'product_image'=>Utilities::generateAbsoluteUrl('image','product_image',array($product["prod_id"]))
					);
			}
			die (json_encode(array('status'=>1,'fav_count'=>$this->user_details['favItems'],'cart_count'=>$this->cart_items, 'unread_messages'=>$this->user_details['unreadMessages'], 'data'=>$arr)));
		}else{
			dieJsonError (Utilities::getLabel('M_INVALID_REQUEST'));
		}
	}
	function product_description($product_id){
		$pObj=new ApiProducts();
		$pObj->joinWithDetailTable();
		$pObj->selectFields(array('tpd.prod_long_desc'));	
		$product_id=isset($_REQUEST['product_id'])?$_REQUEST['product_id']:$product_id;
		$product = $pObj->getData($product_id,array("status"=>1,"favorite"=>$this->app_user["user_id"]));
		echo $product["prod_long_desc"];
	}
	function product_reviews($product_id){
		$reviews=array();
		$page = 1;
		$pagesize=$this->pagesize;
		if(isset($_REQUEST['page']) && intval($_REQUEST['page']) > 0) $page = intval($_REQUEST['page']);
		if(isset($_REQUEST['pagesize']) && intval($_REQUEST['pagesize']) > 0) $pagesize = intval($_REQUEST['pagesize']);
		$pObj=new ApiProducts();
		$pfObj=new Productfeedbacks();
		$product_id = isset($_REQUEST['product_id'])?$_REQUEST['product_id']:$product_id;
		$product = $pObj->getData($product_id);
		if ($product){
			$product_reviews = $pfObj->getFeedbacksWithCriteria(array("product"=>$product_id,"status"=>1,"pagesize"=>$pagesize,"page"=>$page));
			foreach($product_reviews as $key=>$review){
				$reviews[] = array(
					'id'=>$review['review_id'],
					'rating' => $review['review_rating'], 
					'text' => $review['review_text'],
					'date'=>$review['reviewed_on'],
					'reviewed_by'=>$review["user_username"],
					'reviewed_by_image'=>Utilities::generateAbsoluteUrl('image', 'user',array($review["user_profile_image"]))
					);
			}
			die (json_encode(array('status'=>1,'fav_count'=>$this->user_details['favItems'],'cart_count'=>$this->cart_items,'unread_messages'=>$this->user_details['unreadMessages'],'product_name'=>$product['prod_name'],'reviews'=>$reviews,'total_records'=>$pfObj->getTotalRecords())));
		}else{
			dieJsonError (Utilities::getLabel('M_INVALID_REQUEST'));
		}
	}
	function search_suggestions($search_keyword){
		$ptObj=new ProductTags();
		$json=$ptObj->getProductTags(array('keyword'=>$search_keyword));
		$arr = array();
		foreach ($json as $key => $value) {
			$arr[] = $value['ptag_name'];
		}
		sort($arr);
		echo implode("\n",$arr);
	}
	function change_password()
	{
		$uObj=new User();
		$user_id = $this->app_user["user_id"];
		$post = Syspage::getPostedVar();
		$arr=array(
			'current_pwd'=>$post['current_pwd'],
			'new_pwd'=>$post['new_pwd'],
			'confirm_pwd'=>$post['confirm_pwd']					
			);
		$arr=array_merge($arr,array("user_id"=>$user_id));
		if($uObj->savePassword($arr)){			
			$res=array('status'=>1,'msg'=>Utilities::getLabel('M_Your_password_has_been_updated'));
			if (Settings::getSetting("CONF_AUTO_LOGOUT_PASSWORD_CHANGE")){
				$res['auto_logout']=1;
			}
			$res['auto_logout']=0;	
		}else{
			$res=array('status'=>0, 'msg'=>$uObj->getError());
		}	
		die (json_encode($res));
	}
	function profile_info()
	{
		$uObj=new User();
		$user_id = $this->app_user["user_id"];
		$fields=array('user_id','user_name','user_phone','user_city_town','user_country','country_name','user_state_county','state_name','user_profile_image');
		$user=$uObj->getUser(array('user_id'=>$user_id,'get_flds'=>$fields));		
		$arr=array();
		if(!empty($user)){			
			$arr=array(
				'user_id'=>$user['user_id'],
				'name'=>$user['user_name'],
				'phone'=>$user['user_phone'],
				'city_town'=>$user['user_city_town'],
				'country_id'=>$user['user_country'],
				'country'=>$user['country_name'],
				'state_id'=>$user['user_state_county'],
				'state'=>$user['state_name'],
				'user_image_url'=>$this->user_image(),
				'user_image'=>($user['user_profile_image']=='')?0:1,
				);
		}
		die(json_encode($arr));
	}
	function update_profile_pic()
	{
		$uObj=new User();
		$post = Syspage::getPostedVar();
		$data=array('user_id'=>$this->app_user["user_id"]);
		if(isset($post['type']) && strtoupper($post['type'])=='REMOVE'){
			$data['remove_profile_img'] = 1;
			if($uObj->updateUser($data)){				
				$arr=array('status'=>true,'msg'=>Utilities::getLabel('M_MSG_Your_Profile_Image_Updated'),'user_image_url'=>$this->user_image());							
			}else{
				$arr=array('status'=>false,'msg'=>Utilities::getLabel('M_Error_Image_not_saved'));
			}
			die(json_encode($arr));			
		}
		if (!empty($_FILES['user_profile_image']['name']) && is_file($_FILES['user_profile_image']['tmp_name'])) {
			if(Utilities::saveImage($_FILES['user_profile_image']['tmp_name'],$_FILES['user_profile_image']['name'], $response, 'user-avatar/')){	
				$data['user_profile_image'] = $response;						
				if($uObj->updateUser($data)){				
					$arr=array('status'=>true,'msg'=>Utilities::getLabel('M_MSG_Your_Profile_Image_Updated'),'user_image_url'=>$this->user_image());							
				}else{
					$arr=array('status'=>false,'msg'=>Utilities::getLabel('M_Error_Image_not_saved'));
				}
			}else{
				$arr=array('status'=>false,'msg'=>Utilities::getLabel('M_Error_Image_not_saved'));
			}
		}		
		die(json_encode($arr));
	}
	function update_profile_info()
	{
		$uObj=new User();
		$user_id = $this->app_user["user_id"];
		$post = Syspage::getPostedVar();
		$data=array(
			'user_name'=>$post['name'],
			'user_phone'=>$post['phone'],
			'user_city_town'=>$post['city'],					
			'user_country'=>$post['country_id'],					
			'ua_state'=>$post['state_id']				
			);
		$data=array_merge($data,array("user_id"=>$user_id));
		if($uObj->updateUser($data)){			
			$arr=array('status'=>0, 'msg'=>Utilities::getLabel('M_MSG_Your_Account_Details_Updated'));
		}else{			
			$arr=array('status'=>0, 'msg'=>Utilities::getLabel('M_Error_details_not_saved'));
		}	
		die (json_encode($arr));		
	}
	function signup(){
		$uObj=new User();
		$post = Syspage::getPostedVar();
		$arr=array(
			'user_username'=>$post['username'],
			'user_email'=>$post['email'],
			'user_password'=>$post['password'],
			'user_name'=>$post['name'],
			'user_email_verified'=>0,
			'user_type'=>CONF_BUYER_SELLER_USER_TYPE,
			);
		if ($uObj->addUser($arr)){
			$this->setMobileAppToken($uObj->getAttribute("user_id"));
			$arr=array('status'=>1, 'token'=>$uObj->getAttribute("user_app_token"), 'user_name'=>$uObj->getAttribute("user_name"),'auto_login'=>Settings::getSetting("CONF_AUTO_LOGIN_REGISTRATION"));
		}else{
			$arr=array('status'=>0, 'msg'=>$uObj->getError());
		}
		die (json_encode($arr));
	}
	function user_image()
	{
		$uObj=new User();
		$user_id = $this->app_user["user_id"];
		$fields=array('user_profile_image');
		$user=$uObj->getUser(array('user_id'=>$user_id,'get_flds'=>$fields));				
		return Utilities::generateAbsoluteUrl('image', 'user', array($user["user_profile_image"]));		
	}
	function login(){
		$uObj=new User();
		$post = Syspage::getPostedVar();
		if ($uObj->login($post['username'], $post['password']) === true){
			if ($uObj->setMobileAppToken($uObj->getAttribute("user_id"))){				
				$arr=array('status'=>1, 'token'=>$uObj->getAttribute("user_app_token"), 'user_name'=>$uObj->getAttribute("user_name"),'user_image_url'=>Utilities::generateAbsoluteUrl('image', 'user', array($uObj->getAttribute("user_profile_image"))));
			}else{
				$arr=array('status'=>0, 'msg'=>'Invalid Action');
			}
		}else{
			$arr=array('status'=>0, 'msg'=>$uObj->getError());
		}
		die (json_encode($arr));
	}
	function forgot_password(){
		$uObj=new User();
		$post = Syspage::getPostedVar();
		if (!empty($post['username'])){
			$user = $uObj->getUser(array('user_email_username'=>$post['username']));
			if(!$user){
				$json_msg=Utilities::getLabel('M_ERROR_INVALID_EMAIL_USERNAME');
			}elseif($user['user_status'] != 1){
				$json_msg=Utilities::getLabel('M_ERROR_YOUR_ACCOUNT_NOT_ACTIVE');
			}elseif($user['user_is_deleted'] == 1){
				$json_msg=Utilities::getLabel('M_ERROR_YOUR_ACCOUNT_IS_DELETED');
			}elseif($user['user_email_verified'] != 1){
				$json_msg=sprintf(Utilities::getLabel('M_ERROR_YOU_HAVE_NOT_VERIFIED_EMAIL'),'<a href="'.Utilities::generateUrl('user', 'resend_verification_code').'" class="greenAnchorLink">'.Utilities::getLabel('M_Click_here').'</a>');
			}elseif(!$uObj->canResetPassword($user['user_id'])){
				$json_msg=Utilities::getLabel('M_WARNING_FORGOT_PASSWORD_DUPLICATE_REQUEST');
			}else{
				$reset_token = substr(md5(rand(1, 99999) . microtime()), 1, 25);
				$data = array('user_id'=>$user['user_id'], 'reset_token'=>$reset_token, 'token_expiry'=>date('Y-m-d H:i:s', strtotime("+24 HOUR")));
				if($uObj->updateForgotRequest($data) === true){
					$reset_url = Utilities::generateAbsoluteUrl('user', 'reset_password', array($user["user_id"].".".$reset_token));
					$website_name = Settings::getSetting("CONF_WEBSITE_NAME");
					$website_url = Utilities::getUrlScheme();
					if (Utilities::sendMailTpl($user['user_email'], 'forgot_password', array(
						'{reset_url}' => $reset_url,
						'{website_name}' => $website_name,
						'{website_url}' => $website_url,
						'{site_domain}' => CONF_SERVER_PATH,
						'{user_full_name}' => trim($user['user_name']),
						))){
						$json_msg=Utilities::getLabel('M_SUCCESS_FORGOT_PASSWORD_REQUEST');
					dieJsonSuccess($json_msg);
				}else{
					$json_msg=Utilities::getLabel('M_email_not_sent_server_issue');
				}
			}else{
				$json_msg=$uObj->getError();
			}
		}
	}else{
		$json_msg='Username/Email Address required';
	}
	dieJsonError($json_msg);
}
function login_fb(){
	$post = Syspage::getPostedVar();
	$uObj=new User();
	require_once (CONF_INSTALLATION_PATH . 'public/includes/APIs/facebook/facebook.php');
	$facebook = new Facebook(array(
		'appId' => '907983859259641',
		'cookie' => false
		));
	$arr=array('status'=>0, 'msg'=>"Invalid Token");
// Use the token you sent
	$facebook->setAccessToken($post['fb_token']);					
	$user = $facebook->getUser();
	if ($user) {
		try {
// Proceed knowing you have a logged in user who's authenticated.
			$user_profile = $facebook->api('/me?fields=id,name,email');
		} catch (FacebookApiException $e) {
			dieJsonError ($e);
			$user = null;
		}
		if (!empty($user_profile )) {
# User info ok? Let's print it (Here we will be adding the login and registering routines)
			$facebook_name = $user_profile['name'];
			$user_facebook_id = $user_profile['id'];
			$facebook_email = $user_profile['email'];
			$user = $uObj->getUser(array('user_email'=>$facebook_email));
			if (!$user){
				$arr = array(
					'user_password'=>uniqid(),
					'user_email_verified'=>1,
					'user_name'=>$facebook_name,
					'user_email'=>$facebook_email,
					'user_username'=>str_replace(" ","",$facebook_name).$user_facebook_id,
					'user_facebook_id'=>$user_facebook_id,
					'user_type'=>CONF_BUYER_SELLER_USER_TYPE,
					);
				if(!$uObj->addUser($arr)){
					dieJsonError ($uObj->getError());
				}
			}else{
//if (!$user['user_facebook_id']) {}
				$uObj->setUserId($user["user_id"]);
				if(!$uObj->updateAttributes(array('user_facebook_id' => $user_facebook_id))){
					dieJsonError ($uObj->getError());
				}
			}
			$user = $uObj->getUser(array('facebook_id'=>$user_facebook_id),true);
//die($user_facebook_id."#".$user['user_username']."==".$user['user_password']);
			if($uObj->login($user['user_username'], $user['user_password'],true) === true){
				$uObj->setMobileAppToken($uObj->getAttribute("user_id"));
				$arr=array(array('status'=>1, 'token'=>$uObj->getAttribute("user_app_token"), 'user_name'=>$uObj->getAttribute("user_name"),'user_image_url'=>$this->user_image()));
			}else{
				dieJsonError ($uObj->getError());
			}
		} else {
			dieJsonError (Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
		}
	}
	die(json_encode($arr));
}
function login_gplus(){
	$uObj=new User();
	$post = Syspage::getPostedVar();		
	if(isset($post['gp_token'])) {
		$content=file_get_contents($gplus_url="https://www.googleapis.com/oauth2/v1/userinfo?access_token=".$post['gp_token']);
		$me=json_decode($content);
$user_googleplus_email = filter_var($me->email, FILTER_SANITIZE_EMAIL); // get the USER EMAIL ADDRESS using OAuth2
$user_googleplus_id = $me->id;
$user_googleplus_name = $me->name;
if (isset($user_googleplus_email) && (!empty($user_googleplus_email))){
	$user = $uObj->getUser(array('user_email'=>$user_googleplus_email));
	if (!$user){
		$arr = array(
			'user_password'=>uniqid(),
			'user_email_verified'=>1,
			'user_name'=>$user_googleplus_name,
			'user_email'=>$user_googleplus_email,
			'user_username'=>str_replace(" ","",$user_googleplus_name).$user_googleplus_id,
			'user_googleplus_id'=>$user_googleplus_id,
			'user_type'=>CONF_BUYER_SELLER_USER_TYPE,
			);
		if(!$uObj->addUser($arr)){
			dieJsonError($uObj->getError());
		}
	}else{
		if (!$user['user_googleplus_id']) {
			$uObj->setUserId($user["user_id"]);
			if(!$uObj->updateAttributes(array('user_googleplus_id' => $user_googleplus_id))){
				dieJsonError($uObj->getError());
			}
		}								
	}
	$user = $uObj->getUser(array('googleplus_id'=>$user_googleplus_id),true);					
	if($uObj->login($user['user_username'], $user['user_password'],true) === true){						
		$uObj->setMobileAppToken($uObj->getAttribute("user_id"));	
		$arr=array(array('status'=>1, 'token'=>$uObj->getAttribute("user_app_token"), 'user_name'=>$uObj->getAttribute("user_name"),'user_image_url'=>$this->user_image()));
		die(json_encode($arr));
	}else{
		dieJsonError($uObj->getError());
	}
}else{
	$arr=array('status'=>0, 'msg'=>"Something wrong with this token, not returning user's email.");
}
}else{ 			
	$arr=array('status'=>0, 'msg'=>"Invalid Token");
}
die(json_encode($arr));
}
function messages(){
	$uObj=new User();
	$post = Syspage::getPostedVar();
	$arr=array();
	$page = 1;
	$pagesize=$this->pagesize;
	if(isset($_REQUEST['page']) && intval($_REQUEST['page']) > 0) $page = intval($_REQUEST['page']);
	if(isset($_REQUEST['pagesize']) && intval($_REQUEST['pagesize']) > 0) $pagesize = intval($_REQUEST['pagesize']);
	$all_messages=$uObj->getMessages(array("to"=>$this->app_user["user_id"],"order"=>"message_id","group"=>"thread_id","page"=>$page),$pagesize);
	$totalRecords=$uObj->getTotalRecords();
	foreach($all_messages as $pkey=>$message){
		$messages=$uObj->getMessages(array("to"=>$this->app_user["user_id"],"thread"=>$message["thread_id"],"unread"=>1));
		$arr_message[] = array(
			'thread'=>$message["thread_id"],
			'subject'=>$message["thread_subject"],
			'message_id'=>$message["message_id"],
			'sent_by' => $message["message_sent_by_username"], 
			'sent_by_pic' => Utilities::generateAbsoluteUrl('image', 'user',array($message["message_sent_by_profile"])),
			'text' => $message["message_text"],
			'date_time'=>$message["message_date"],
			'timestamp'=>$message["message_date_timestamp"],
			'unread'=>$message["message_is_unread"],
			'total_unread'=>count($messages),
			);			
	}
	$arr=array('status'=>1,'fav_count'=>$this->user_details['favItems'],'cart_count'=>$this->cart_items,'unread_messages'=>$this->user_details['unreadMessages'], 'messages'=>$arr_message,'total_records'=>$totalRecords);
	die(json_encode($arr));
}
function view_thread_messages(){ 
	$uObj=new User();
	$post = Syspage::getPostedVar();
	$arr=array();
	$page = 1;
	$pagesize=$this->pagesize;
	if(isset($_REQUEST['thread_id']) && intval($_REQUEST['thread_id']) > 0) $thread_id = intval($_REQUEST['thread_id']);
	if(isset($_REQUEST['page']) && intval($_REQUEST['page']) > 0) $page = intval($_REQUEST['page']);
	if(isset($_REQUEST['pagesize']) && intval($_REQUEST['pagesize']) > 0) $pagesize = intval($_REQUEST['pagesize']);
	$all_messages=$uObj->getMessages(array("thread"=>$thread_id,"all"=>$this->app_user["user_id"],"order"=>"message_id","page"=>$page),$pagesize);	
	$uObj->markUserMessageRead($thread_id,$this->app_user["user_id"]);
	foreach($all_messages as $pkey=>$message){	
		$arr_message[] = array(
			'thread'=>$message["thread_id"],
			'subject'=>$message["thread_subject"],
			'message_id'=>$message["message_id"],
			'sent_by' => $message["message_sent_by_username"], 
			'sent_to' => $message["message_sent_to_name"], 
			'sent_by_username' => $message["message_sent_by_username"], 
			'sent_by_pic' => Utilities::generateAbsoluteUrl('image', 'user',array($message["message_sent_by_profile"])),
			'text' => $message["message_text"],
			'date_time'=>$message["message_date"],
			'timestamp'=>$message["message_date_timestamp"]
			);
	}
	$arr_message=($page>1)?$arr_message:array_reverse($arr_message);		
	$arr=array('status'=>1,'fav_count'=>$this->user_details['favItems'],'cart_count'=>$this->cart_items,'unread_messages'=>$this->user_details['unreadMessages'], 'messages'=>$arr_message,'total_records'=>$uObj->getTotalRecords());
	die(json_encode($arr));
}
function send_thread_message(){
	$uObj=new User();
	$user_id = $this->app_user["user_id"];
	$arr=array('status'=>0, 'msg'=>Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
	$post = Syspage::getPostedVar();
	if(isset($_REQUEST['thread_id']) && intval($_REQUEST['thread_id']) > 0) $thread_id = intval($_REQUEST['thread_id']);
	$thread_detail=$uObj->getMessageThread($thread_id,$user_id);
	$message_sent_to=$thread_detail["message_from"]==$user_id?$thread_detail["message_to"]:$thread_detail["message_from"];
	if ($thread_detail){
		$info=array(
			"user_id"=>$user_id,
			"thread_id"=>$thread_id,
			"message_text"=>$post["message"],
			"message_sent_to"=>$message_sent_to,
			);
		if ($uObj->addThreadMessage($info)){
			$arr=array('status'=>1, 'msg'=>Utilities::getLabel('M_YOUR_MESSAGE_SENT_SUCCESSFULLY'));
		}
	}else{
		dieJsonError (Utilities::getLabel('M_INVALID_REQUEST'));
	}
	die(json_encode($arr));
}
function addresses(){
	$uObj=new User();
	$post = Syspage::getPostedVar();
	$arr=array();
	$my_addresses=$uObj->getUserAddresses($this->app_user["user_id"]);
	foreach($my_addresses as $addkey=>$addval){	
		$arr_addresses[] = array(
			'id'=>$addval["ua_id"], 
			'name'=>$addval["ua_name"],
			'address1'=>$addval["ua_address1"], 
			'address2'=>$addval["ua_address2"], 
			'city'=>$addval["ua_city"], 
			'country'=>$addval["country_code"], 
			'country_id'=>$addval["country_id"], 
			'country_name'=>$addval["country_name"], 
			'state_id'=>$addval["state_id"], 
			'state_name'=>$addval["state_name"], 
			'zip'=>$addval["ua_zip"], 
			'phone'=>$addval["ua_phone"], 
			'primary'=>$addval["ua_is_default"],
			);
	}
	$arr=array('status'=>1,'fav_count'=>$this->user_details['favItems'],'cart_count'=>$this->cart_items,'unread_messages'=>$this->user_details['unreadMessages'], 'addresses'=>$arr_addresses);
	die(json_encode($arr));
}
function addresses_setup(){
	$uObj=new User();
	$post = Syspage::getPostedVar();
	$arr=array(
		"ua_user_id"=>$this->app_user["user_id"],
		"ua_id"=>$post["id"],
		"ua_name"=>$post["name"],
		"ua_address1"=>$post["address1"],
		"ua_address2"=>$post["address2"],
		"ua_city"=>$post["city"],
		"ua_state"=>$post["state"],
		"ua_zip"=>$post["zip"],
		"ua_country"=>$post["country"],
		"ua_phone"=>$post["phone"],
		);
	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		if ($ua_id=$uObj->addUpdateAddress($arr)){
			$cartObj=new Cart();
			if ($post['setCartBillingAddress'] == 1) {
				$cartObj->setCartBillingAddress($ua_id);
			}
			elseif ($post['setCartShippingAddress'] == 1) {
				$cartObj->setCartShippingAddress($ua_id);
			}
			elseif ($post['setCartBillingShippingAddress'] == 1) {
				$cartObj->setCartBillingAddress($ua_id);
				$cartObj->setCartShippingAddress($ua_id);
			}
			dieJsonSuccess(Utilities::getLabel('L_OK'));
		}else{
			dieJsonError ($uObj->getError());
		}
	}else{
		dieJsonError (Utilities::getLabel('M_INVALID_REQUEST'));
	}
}
function delete_addresses(){
	$uObj=new User();
	$post = Syspage::getPostedVar();
	$arr=array();
// Delete User Address and Retrive Addresses again
	if ($uObj->deleteUserAddress(intval($_REQUEST["id"]),$this->app_user["user_id"])){
		$my_addresses=$uObj->getUserAddresses($this->app_user["user_id"]);
		foreach($my_addresses as $addkey=>$addval){	
			$arr_addresses[] = array(
				'id'=>$addval["ua_id"], 
				'name'=>$addval["ua_name"],
				'address1'=>$addval["ua_address1"], 
				'address2'=>$addval["ua_address2"],
				'city'=>$addval["ua_city"], 
				'country'=>$addval["country_code"], 
				'country_id'=>$addval["country_id"], 
				'country_name'=>$addval["country_name"], 
				'state_id'=>$addval["state_id"], 
				'state_name'=>$addval["state_name"], 
				'zip'=>$addval["ua_zip"],  
				'phone'=>$addval["ua_phone"], 
				'primary'=>$addval["ua_is_default"],
				);
		}
		$arr=array('status'=>1, 'addresses'=>$arr_addresses);
	}else{
		dieJsonError($uObj->getError());
	}
	die(json_encode($arr));
}
function primary_address(){
	$uObj=new User();
	$post = Syspage::getPostedVar();
	$arr=array();
	$address_details = $uObj->getUserAddress(intval($_REQUEST["id"]),$this->app_user["user_id"]);
	if ($address_details==true){
		if ($uObj->setAddressDefault(intval($_REQUEST["id"]),$this->app_user["user_id"])){
			$my_addresses=$uObj->getUserAddresses($this->app_user["user_id"]);
			foreach($my_addresses as $addkey=>$addval){	
				$arr_addresses[] = array(
					'id'=>$addval["ua_id"], 
					'name'=>$addval["ua_name"],
					'address1'=>$addval["ua_address1"], 
					'address2'=>$addval["ua_address2"], 
					'city'=>$addval["ua_city"], 
					'country'=>$addval["country_code"], 
					'country_id'=>$addval["country_id"], 
					'country_name'=>$addval["country_name"], 
					'state_id'=>$addval["state_id"], 
					'state_name'=>$addval["state_name"], 
					'zip'=>$addval["ua_zip"], 
					'phone'=>$addval["ua_phone"], 
					'primary'=>$addval["ua_is_default"],
					);
			}
			$arr=array('status'=>1,'fav_count'=>$this->user_details['favItems'],'cart_count'=>$this->cart_items,'unread_messages'=>$this->user_details['unreadMessages'], 'addresses'=>$arr_addresses);
		}else{
			dieJsonError($uObj->getError());
		}
		die(json_encode($arr));
	}else{
		dieJsonError (Utilities::getLabel('M_INVALID_REQUEST'));
	}
}
function favorite_products(){
	$uObj=new User();
	$post = Syspage::getPostedVar();
	$arr=array();
	$user_id = $this->app_user["user_id"];
	$favorite_products=$uObj->getUserFavoriteItems(
		array(
			"user"=>$user_id,
			"favorite"=>$user_id
			));
	foreach($favorite_products as $fkey=>$product){
		$price=$product['prod_sale_price'];
		$product_price=(!$product['special'])?$price:$product['special'];
		$products[] = array(
			'product_id'=>$product["prod_id"],
			'product_name' => $product["prod_name"], 
			'store_name' => $product["shop_name"], 
			'price_currency' => CONF_CURRENCY_SYMBOL,
			'product_price'=>$product_price,
			'favorite'=>$product["favorite"]?1:0,
			'product_stock'=>max($product['prod_stock'],0),					
			'product_image'=>Utilities::generateAbsoluteUrl('image','product_image',array($product["prod_id"]))
			);
	}
	$arr = array('status'=>1,'fav_count'=>$this->user_details['favItems'],'cart_count'=>$this->cart_items,'unread_messages'=>$this->user_details['unreadMessages'], 'products'=>$products);
	die(json_encode($arr));
}
function my_file_upload()
{	
	$this->file_upload();
}
function add_to_cart(){
	$user_id = $this->app_user["user_id"];
	$pObj=new ApiProducts();
	$cObj=new cart($user_id);
	$post = Syspage::getPostedVar();
	$show_cart_page = 0;
	$show_detail_page = 0;
	if (isset($post['product_id'])) {
		$product_id = (int)$post['product_id'];
	} else {
		$product_id = 0;
	}
	$product_info = $pObj->getData($product_id,array("status"=>1));
	if ($product_info) {
		$products = $pObj->getData($product_id,array("available_date"=>1));
		if ($products) {
			if (isset($post['quantity']) && ((int)$post['quantity'] >= 1)) {
				$quantity = (int)$post['quantity'];
			} else {
				$quantity = 1;
			}
			if (isset($post['option'])) {
				$option = array_filter($post['option']);
			} else {
				$option = array();
			}
			$product_options = $pObj->getProductOptions($product_id);
			foreach ($product_options as $product_option) {
				if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
					$message = sprintf(Utilities::getLabel('M_Label_Required'), $product_option['name']);
					$error = true;
				}
			}
			if (!$error) {
				$cObj->cart_user_id = $user_id;
				if ($cObj->add($product_id, $quantity, $option,$user_id)){
					$message=sprintf(Utilities::getLabel('M_PRODUCT_ADDED_CART'),$product_info['prod_name']);
					$show_cart_page=1; 
				}
			}else{
				$show_detail_page=1; 
			}
		}else{
			$error = true;
			$message=sprintf(Utilities::getLabel('L_Warning_Product_Available_Date'), "<i>".Utilities::formatDateOnly($product_info["prod_available_date"])."</i>");
			$show_detail_page=1; 
		}
	}else{
		$error = true;
		$message="Invalid Product";
	}
	die(json_encode(array('status'=>!$error, 'msg'=>$message, 'cart_count'=>$cObj->countProducts(), 'show_cart'=>$show_cart_page, 'show_detail_page'=>$show_detail_page)));
}
function cart_billing_address_update() {
	$user_id = $this->app_user["user_id"];
	$cObj=new cart($user_id);
	$post = Syspage::getPostedVar();
	$json = array();
	$address_id=$post['address_id'];
	if (isset($address_id)) {
//$user_id=$this->app_user["user_id"];
		$user=new User();
		$address_details = $user->getUserAddress($address_id,$user_id);
		if ($address_details){
//$cObj->cart_user_id = $user_id;
			if($cObj->setCartBillingAddress($address_id)){
				dieJsonSuccess(Utilities::getLabel('M_cart_billing_address_modified'));
			}
		}
	}
	dieJsonError (Utilities::getLabel('M_INVALID_REQUEST'));
}
function cart_shipping_address_update() {
	$user_id=$this->app_user["user_id"];
	$cObj=new cart($user_id);
	$post = Syspage::getPostedVar();
	$json = array();
	$address_id=$post['address_id'];
	if (isset($address_id)) {
		$user=new User();
		$address_details = $user->getUserAddress($address_id,$user_id);
		if ($address_details){
//$cObj->cart_user_id = $user_id;
			if($cObj->setCartShippingAddress($address_id)){
				dieJsonSuccess(Utilities::getLabel('M_cart_shipping_address_modified'));
			}
		}
	}
	dieJsonError (Utilities::getLabel('M_INVALID_REQUEST'));
}
function cart(){
	$user_id = $this->app_user["user_id"];
	$cartObj=new cart($user_id);
//$cartObj->cart_user_id=$user_id;
	$cart_products=$cartObj->getProducts();
	$uObj=new User();
	$productObj=new ApiProducts();
	$cart_financial_summary=$cartObj->getCartFinancialSummary();
	$billing_address = $uObj->getUserAddress($cartObj->getCartBillingAddress(), $user_id);
	$billing_address_text="<strong>".$billing_address['ua_name'].'</strong><br/>'.((strlen($billing_address['ua_address1']) > 0)?$billing_address['ua_address1']:'') .((strlen($billing_address['ua_address2']) > 0)?'<br/>'.$billing_address['ua_address2'].', ':'') . ((strlen($billing_address['ua_city']) > 0)?'<br/>'.$billing_address['ua_city'] . ', ':'') . '<br/>'. $billing_address['state_name'].' - '.$billing_address['ua_zip'].'<br/>'.$billing_address['country_name']
	.'<br/>T: '.$billing_address['ua_phone'];
	$shipping_address = $uObj->getUserAddress($cartObj->getCartShippingAddress(), $user_id);
	$shipping_address_text="<strong>".$shipping_address['ua_name'].'</strong><br/>'.((strlen($shipping_address['ua_address1']) > 0)?$shipping_address['ua_address1']:'') .((strlen($shipping_address['ua_address2']) > 0)?'<br/>'.$shipping_address['ua_address2'].', ':'') . ((strlen($shipping_address['ua_city']) > 0)?'<br/>'.$shipping_address['ua_city'] . ', ':'') . '<br/>'. $shipping_address['state_name'].' - '.$shipping_address['ua_zip'].'<br/>'.$shipping_address['country_name']
	.'<br/>T: '.$shipping_address['ua_phone'];
//printArray($cart_financial_summary);
//die();
	$cart = array(
		'billing_address'=>$billing_address_text,
		'shipping_address'=>$shipping_address_text,
		'billing_address_info'=>array(
			'name'=>$billing_address['ua_name'],
			'address1'=>$billing_address['ua_address1'],
			'address2'=>$billing_address['ua_address2'],
			'city'=>$billing_address['ua_city'],
			'state'=>$billing_address['state_name'],
			'zip'=>$billing_address['ua_zip'],
			'country'=>$billing_address['country_name'],
			'phone'=>$billing_address['ua_phone']
			),
		'shipping_address_info'=>array(
			'name'=>$shipping_address['ua_name'],
			'address1'=>$shipping_address['ua_address1'],
			'address2'=>$shipping_address['ua_address2'],
			'city'=>$shipping_address['ua_city'],
			'state'=>$shipping_address['state_name'],
			'zip'=>$shipping_address['ua_zip'],
			'country'=>$shipping_address['country_name'],
			'phone'=>$shipping_address['ua_phone']
			),
		'total_value'=>Utilities::displayMoneyFormat($cart_financial_summary['net_total_after_discount'],true,false),
		'products_total'=>Utilities::displayMoneyFormat($cart_financial_summary['cart_total'],true,false),
		'shipping'=>Utilities::displayMoneyFormat($cart_financial_summary['cart_shipping_total'],true,false),
		'shipping_caption'=>Utilities::getLabel('L_SHIPPING_HANDLING'),
		'discount'=>Utilities::displayMoneyFormat($cart_financial_summary['cart_discounts']['value'],true,false),
		'discount_caption'=>$cart_financial_summary['cart_discounts']['code']!=""?Utilities::getLabel('L_Discount').' (' . $cart_financial_summary['cart_discounts']['code'] . ')':Utilities::getLabel('L_DISCOUNT').':',
		'can_apply_coupon'=>$cart_financial_summary['cart_discounts']['code']!=""?0:1,
		'tax'=>Utilities::displayMoneyFormat($cart_financial_summary['cart_tax_total'],true,false),
		'tax_caption'=>Utilities::getLabel('L_VAT').' @'.Settings::getSetting("CONF_SITE_TAX").'%',
		'show_discount'=>$cart_financial_summary['cart_discounts']>0?1:0,
		'show_shipping'=>$cart_financial_summary['cart_shipping_total']>0?1:0,
		'show_tax'=>$cart_financial_summary['cart_tax_total']>0?1:0,
		'shipping_address_exists'=>$cartObj->isShippingAddressSet()?1:0,
		'billing_address_exists'=>($cartObj->isBillingAddressSet() && $billing_address['ua_name']!='')?1:0,
		'shipping_addresses_set'=>($cartObj->hasShippingOptionSet() && $billing_address['ua_name']!='')?1:0,
		'price_currency'=>CONF_CURRENCY_SYMBOL,
		'products'=>array()
		);
/* printArray($cart_products);
die(); */			
foreach($cart_products as $cartkey=>$product) {								
	$extra_html='';
	foreach ($product['option'] as $option) {
		$extra_html.='<b>'.$option['name'].'</b>: '.$option['value'].'<br/>';
	}				
/* $shippingId=0;
if($product['shipping_id']==0 && !empty($product['shipping_options'])){
foreach($product['shipping_options'] as $val){
if($shippingId>0){break;}
$shippingId=$val['pship_id'];
$product['shipping_id']=$shippingId;						
$shipping_address = $uObj->getUserAddress($cartObj->getCartShippingAddress(), $user_id);
$shipping_options = $productObj->getProductShippingRates($product['product_id'],array("country"=>$shipping_address["ua_country"]));			
//echo base64_encode($product['key']);						
if ((in_array($val['pship_id'],array_column($shipping_options, 'pship_id')))){	
$arr['shipping_locations'][md5($product['key'])]=$val['pship_id'];
$cartObj->setProductsShipping($arr);							
}						
}
} */ 
$products = array(
	'id'=>$product['product_id'],
	'key'=>$product['key'],
	'product_name'=>$product['name'],
	'store_name' => $product['shop_name'], 
	'unit_price'=>$product['price'],
	'qty'=>$product['quantity'],
	'qty_number'=>$product['quantity'],
	'extra_html'=>$extra_html,
	'product_total'=>Utilities::displayMoneyFormat($product['total'],true,false),
	'product_image'=>Utilities::generateAbsoluteUrl('image','product_image',array($product["product_id"])),
	'shipping_options'=>$product['shipping_options'],
	'shipping_options_message'=>Utilities::getLabel('M_Message_Product_not_available_shipping'),
	'display_shipping_option_message'=>count($product['shipping_options'])>0?0:1,
	'selected_shipping_option'=>($product['shipping_id']>0)?$product['shipping_id']:0,
	'stock'=>(!$product['stock'] && Settings::getSetting("CONF_CHECK_STOCK"))?false:true,
	'stock_msg'=>"<span class='red'>(".Utilities::getLabel("M_Error_Stock").")</span>",					
	);
$cart['products'][] = $products;				
}		
/*Utilities::printArray($cart);
die();*/
die (json_encode(array('status'=>1,'fav_count'=>$this->user_details['favItems'],'cart_count'=>$this->cart_items,'unread_messages'=>$this->user_details['unreadMessages'], 'cart'=>$cart), JSON_FORCE_OBJECT));
}
function cart_edit_qty(){
	$user_id = $this->app_user["user_id"];
	$cartObj=new cart($user_id);
	$post = Syspage::getPostedVar();		
	if (!empty($post['quantity']) && !empty($post['product_key'])) {			
		$cartObj->update($post['product_key'], $post['quantity']);
/* if ((!$this->hasStock('key',$post['product_key'])) && Settings::getSetting("CONF_CHECK_STOCK")) {								
dieJsonError(Utilities::getLabel('M_Error_Stock'));
}	 */
dieJsonSuccess(Utilities::getLabel('M_cart_modified'));			
}
dieJsonError(Utilities::getLabel('L_ACTION_TRYING_PERFORM_NOT_VALID'));
}
function hasStock($attr='key',$keyValue) {		
	$stock = true;
	$user_id = $this->app_user["user_id"];
	$cartObj=new cart($user_id);
	foreach ($cartObj->getProducts() as $product) {
		if($product[$attr]!=$keyValue){continue;}			
		if (!$product['stock']) {
			$stock = false;
		}
	}
	return $stock;
}
function cart_remove_item(){
	$user_id = $this->app_user["user_id"];
	$cartObj=new cart($user_id);
	$post = Syspage::getPostedVar();
	if (!empty($post['item_key'])) {
		$cartObj->remove($post['item_key']);
		dieJsonSuccess(Utilities::getLabel('M_cart_modified'));
	}
	dieJsonError(Utilities::getLabel('L_ACTION_TRYING_PERFORM_NOT_VALID'));
}
function cart_apply_coupon(){
	$user_id = $this->app_user["user_id"];
	$cartObj=new cart($user_id);
	$json = array();
	$post = Syspage::getPostedVar();
	$coupon = isset($post['coupon'])?$post['coupon']:"";
	$couponObj=new Coupons();
	$couponObj->logged_user_id=$user_id;
	$coupon_info = $couponObj->getCoupon($coupon);
	if (empty($post['coupon'])) {
		$error_message = Utilities::getLabel('L_Warning_Enter_Coupon');
	} elseif ($coupon_info) {
		if($cartObj->updateCartDiscountCoupon($coupon_info['coupon_code'])){
			dieJsonSuccess(Utilities::getLabel('M_cart_discount_coupon_applied'));
		}else{
			$error_message = Utilities::getLabel('L_ACTION_TRYING_PERFORM_NOT_VALID');
		}
	} else {
		$error_message = Utilities::getLabel('L_Warning_Coupon_Invalid');
	}
	dieJsonError($error_message);
}
function update_cart_items_shipping_method(){
	$user_id = $this->app_user["user_id"];
	$cartObj=new cart($user_id);
	$cart_products=$cartObj->getProducts();
	$uObj=new User();
	$product=new ApiProducts();
	$post = Syspage::getPostedVar();
	$json = array();
	foreach($cart_products as $cartkey=>$cartval){
		if ($cartval['key']==$post['product_key']){
			$item_key=$cartkey;
			break;
		}
	}
/*foreach($cart_products as $cartkey=>$cartval){
$sn++;
$shipping_address = $uObj->getUserAddress($cartObj->getCartShippingAddress(), $user_id);
$shipping_options = $product->getProductShippingRates($cartval['product_id'],array("country"=>$shipping_address["ua_country"]));
if (empty($post["shipping_locations"][$cartval["key"]]) || (!in_array($post["shipping_locations"][$cartval["key"]],array_column($shipping_options, 'pship_id')))){	
$error_message = sprintf(Utilities::getLabel('M_Shipping_Info_Required'), $cartval['name']);	
break;
}
}*/
if (!empty($post['pship_id']) && !empty($post['product_key'])) {
	$shipping_address = $uObj->getUserAddress($cartObj->getCartShippingAddress(), $user_id);
	$shipping_options = $product->getProductShippingRates($cart_products[$item_key]['product_id'],array("country"=>$shipping_address["ua_country"]));
	if ((!in_array($post["pship_id"],array_column($shipping_options, 'pship_id')))){	
		dieJsonError(Utilities::getLabel('L_ACTION_TRYING_PERFORM_NOT_VALID'));
	}
	$arr['shipping_locations'][md5($post['product_key'])]=$post['pship_id'];
	if ($cartObj->setProductsShipping($arr)){
		dieJsonSuccess(Utilities::getLabel('M_cart_shipping_address_modified'));
	}else{
		dieJsonError(Utilities::getLabel('L_ACTION_TRYING_PERFORM_NOT_VALID'));
	}
}
dieJsonError($error_message);
}
function ask_a_question(){
	$uObj=new User();
	$pObj=new ApiProducts();
	$arr=array('status'=>0, 'msg'=>Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
	$post = Syspage::getPostedVar();
	$product = $pObj->getData($post["product_id"],array("status"=>1));
	if ($product){
		$user_id = $this->app_user["user_id"];
		$arr=array();
		$info=array(
			"user_id"=>$user_id,
			"thread_subject"=>$post["subject"],
			"message_text"=>$post["question"],
			"message_sent_to"=>$product["prod_added_by"],
			"thread_type"=>'P',
			"thread_record"=>$product["prod_id"],
			);
		if ($uObj->addThreadMessage($info)){
			$arr=array('status'=>1, 'msg'=>Utilities::getLabel('M_YOUR_MESSAGE_SENT_SUCCESSFULLY'));
		}
	}else{
		dieJsonError (Utilities::getLabel('M_INVALID_REQUEST'));
	}
	die(json_encode($arr));
}
function shop_send_message(){
	$uObj=new User();
	$sObj=new Shops();
	$arr=array('status'=>0, 'msg'=>Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
	$post = Syspage::getPostedVar(); 
	$shop = $sObj->getData($post["shop_id"],array("status"=>1));
	if ($shop){
		$user_id = $this->app_user["user_id"];
		$arr=array();
		$info=array(
			"user_id"=>$user_id,
			"thread_subject"=>$post["subject"],
			"message_text"=>$post["message"],
			"message_sent_to"=>$shop["shop_user_id"],
			"thread_type"=>'S',
			"thread_record"=>$shop["shop_id"],
			);
		if ($uObj->addThreadMessage($info)){
			$arr=array('status'=>1, 'msg'=>Utilities::getLabel('M_YOUR_MESSAGE_SENT_SUCCESSFULLY'));
		}
	}else{
		dieJsonError (Utilities::getLabel('M_INVALID_REQUEST'));
	}
	die(json_encode($arr));
}
function mark_product_favorite(){
	$uObj=new User();
	$pObj=new ApiProducts();
	$post = Syspage::getPostedVar();
	$product_id = $post["product_id"];
	$product = $pObj->getData($product_id,array("status"=>1));
	if ($product){
		$user_id = $this->app_user["user_id"];
		if ($_REQUEST['favorite']==1){
			if($uObj->addUserFavoriteProduct($product['prod_id'],$user_id)){
				$userObj=new user();
				$this->user_details=$userObj->getUserById($this->app_user["user_id"]);
				$cartObj=new cart($this->app_user["user_id"]);
				$this->cart_items=$cartObj->countProducts();
				die(json_encode(array('status'=>true,'fav_count'=>$this->user_details['favItems'],'cart_count'=>$this->cart_items,'unread_messages'=>$this->user_details['unreadMessages'])));
//echo 'OK';
			}else{
				dieJsonError ($uObj->getError());
			}
		} else {
			if($uObj->deleteUserFavoriteProduct($product['prod_id'],$user_id)){	
				$userObj=new user();
				$this->user_details=$userObj->getUserById($this->app_user["user_id"]);
				$cartObj=new cart($this->app_user["user_id"]);
				$this->cart_items=$cartObj->countProducts();
				die(json_encode(array('status'=>true,'fav_count'=>$this->user_details['favItems'],'cart_count'=>$this->cart_items,'unread_messages'=>$this->user_details['unreadMessages'])));
//echo 'OK';
			}else{
				dieJsonError ($uObj->getError());
			}
		}
	}else{
		dieJsonError (Utilities::getLabel('M_INVALID_REQUEST'));
	}
}
function get_temp_token(){
	$uObj=new User();
	$user_id = $this->app_user["user_id"];
	$temp_token = substr(md5(rand(1, 99999) . microtime()), 1, 25);
	$data = array('user_id'=>$user_id, 'temp_token'=>$temp_token, 'token_expiry'=>date('Y-m-d H:i:s', strtotime("+10 MINUTE")));
	if($uObj->createUserTempToken($data) === true){
		die(json_encode(array('status'=>1,'fav_count'=>$this->user_details['favItems'],'cart_count'=>$this->cart_items,'unread_messages'=>$this->user_details['unreadMessages'], 'tkn'=>$temp_token)));
	}else{
		dieJsonError ($uObj->getError());
	}
}
function buy_product(){ 
	$uObj=new User();
	$post = Syspage::getPostedVar();
	$user_id = $this->app_user["user_id"];	
	$get = (array) Utilities::getUrlQuery();
	if (isset($post['ttkn'])) { 
		$temp_token=$post['ttkn'];
		if( strlen($temp_token) != 25){
			dieJsonError (Utilities::getLabel('M_INVALID_REQUEST'));
		}
		if(!$user_temp_token_data = $uObj->validateAPITempToken($user_id, $temp_token)){
			dieJsonError (Utilities::getLabel('M_ERROR_INVALID_TEMP_TOCKEN'));
		}
		if(!$user = $uObj->getUser(array('id'=>$user_temp_token_data["uttr_user_id"]),true)){
			dieJsonError (Utilities::getLabel('M_INVALID_REQUEST'));
		}
		if($user['user_status'] != 1){
			dieJsonError(Utilities::getLabel('M_ERROR_YOUR_ACCOUNT_NOT_ACTIVE'));
		}
		if($user['user_is_deleted'] == 1){
			dieJsonError(Utilities::getLabel('M_ERROR_YOUR_ACCOUNT_IS_DELETED'));
		}
		if($user['user_email_verified'] != 1){
			dieJsonError(sprintf(Utilities::getLabel('M_ERROR_YOU_HAVE_NOT_VERIFIED_EMAIL'),'<a href="'.Utilities::generateUrl('user', 'resend_verification_code').'" class="greenAnchorLink">'.Utilities::getLabel('M_Click_here').'</a>'));
		}
		if ($uObj->login($user['user_email'], $user['user_password'],true) === true){ 					
			if ($uObj->deleteUserAPITempToken($user['user_id'])){						
				Utilities::redirectUser(Utilities::generateUrl('cart','checkout_payments',array(1)));					
			} 
		}else{
			dieJsonError($uObj->getError());
		}
	}
}
function orders(){
	$user_id = $this->app_user["user_id"];
	$uObj=new User();
	$orderObj=new Orders();
	$pObj=new ApiProducts();
	$arr=array();
	$pagesize=$this->pagesize;
	if(isset($_REQUEST['page']) && intval($_REQUEST['page']) > 0) $page = intval($_REQUEST['page']);
	if(isset($_REQUEST['pagesize']) && intval($_REQUEST['pagesize']) > 0) $pagesize = intval($_REQUEST['pagesize']);
	$criteria=array("customer"=>$user_id);
	$criteria=array_merge($criteria,array("status"=>(array)Settings::getSetting("CONF_BUYER_ORDER_STATUS"),"page"=>$page,"pagesize"=>$pagesize));
	$my_orders=$orderObj->getChildOrders($criteria);
	foreach($my_orders as $addkey=>$order){
		$cObj=new Currencies($order['order_currency_code']);
		$order['order_currency_symbol_left'] = $cObj->getSymbolLeft();
		$order['order_currency_symbol_right'] = $cObj->getSymbolRight();
		$product = $pObj->getData($order["opr_product_id"],array("status"=>1,"favorite"=>$this->app_user["user_id"]));
		$arr = array(
			'order_id'=>$order['opr_order_invoice_number'],
			'primary_order_id'=>$order['opr_order_id'],
			'primary_order_invoice_number'=>$order['order_invoice_number'],
			'status'=>$order["orders_status_name"],
			'order_date'=>$order["order_date_added"],
			'order_date_timestamp'=>$order["order_date_timestamp"],
			'order_total_amount'=>(!($order["totOrders"]>1))?Utilities::displayMoneyFormat($order["opr_net_charged"]-$order["order_discount_total"]):"-",
			'has_child_orders'=>(($order["totOrders"]>1))?1:0,
			'display_order_message'=>$order["totOrders"]>1?Utilities::getLabel('L_Part_combined_order').' '.$order["order_invoice_number"]:'',
			'products'=>array(
				array(
					'product_id'=>$order['opr_product_id'],
					'product_name' => $order['opr_name'], 
					'product_extra_html' => $order['opr_customization_string'], 
					'shop_id' => $order['opr_product_shop'], 
					'shop_name' => $order['opr_product_shop_name'], 
					'price_currency' => !empty($order['order_currency_symbol_left'])?$order['order_currency_symbol_left']:$order['order_currency_symbol_right'],
					'product_price'=>$order['opr_sale_price'],
					'customer_buying_price'=>$order['opr_customer_buying_price'],
					'customer_buying_customization_charge'=>$order['opr_customization_price'],
					'shipping'=>$order['opr_shipping_charges'],
					'tax'=>$order['opr_tax'],
					'shipping_method'=>$order['opr_shipping_label'],
					'favorite'=>$product["favorite"]?1:0,
					'product_image'=>Utilities::generateAbsoluteUrl('image','product_image',array($order["opr_product_id"]))
					)
				)
			);
		$orders[$order['opr_id']] = $arr;
	}
	$arr=array('status'=>1,'fav_count'=>$this->user_details['favItems'],'cart_count'=>$this->cart_items,'unread_messages'=>$this->user_details['unreadMessages'], 'orders'=>$orders,'total_records'=>$orderObj->getTotalRecords());
	die(json_encode($arr));
}
function primary_order_detail(){
	global $payment_status_arr;
	$user_id = $this->app_user["user_id"];
	$pObj=new ApiProducts();
	$orderObj=new Orders();
	if(isset($_REQUEST['order_id']) && intval($_REQUEST['order_id']) > 0) $order_id = intval($_REQUEST['order_id']);
	$order = $orderObj->getOrderById($order_id,array("user"=>$user_id));
	if ($order){
		$cObj=new Currencies($order['order_currency_code']);
		$order['order_currency_symbol_left'] = $cObj->getSymbolLeft();
		$order['order_currency_symbol_right'] = $cObj->getSymbolRight();
/*print_r($order);
die();*/
$arr = array(
	'id'=>$order['order_id'],
	'invoice_number' => $order['order_invoice_number'], 
	'customer_name' => $order["order_user_name"],
	'customer_phone' => $order["order_user_phone"],
	'shipping_name' => $order["order_shipping_name"],
	'shipping_address_1' => $order["order_shipping_address1"],
	'shipping_address_2' => $order["order_shipping_address2"],
	'shipping_city' => $order["order_shipping_city"],
	'shipping_postcode' => $order["order_shipping_postcode"],
	'shipping_state' => $order["order_shipping_state"],
	'shipping_country' => $order["order_shipping_country"],
	'shipping_phone' => $order["order_shipping_phone"],
	'billing_name' => $order["order_billing_name"],
	'billing_address_1' => $order["order_billing_address1"],
	'billing_address_2' => $order["order_billing_address2"],
	'billing_city' => $order["order_billing_city"],
	'billing_postcode' => $order["order_billing_postcode"],
	'billing_state' => $order["order_billing_state"],
	'billing_country' => $order["order_billing_country"],
	'billing_phone' => $order["order_billing_phone"],
	'date' => $order["order_date_added"],
	'date_timestamp' => $order["order_date_timestamp"],
	'payment_status' => $payment_status_arr[$order["order_payment_status"]],
	'coupon' => $order["order_discount_coupon"],
	'discount_total' => $order["order_discount_total"],
	'cart_total' => $order["order_cart_total"],
	'shipping' => $order["order_shipping_charged"],
	'tax' => $order["order_tax_charged"],
	'tax_perc' => $order["order_vat_perc"],
	'tax' => $order["order_tax_charged"],
	'net_paid' => $order["order_net_charged"],
	'price_currency' => !empty($order['order_currency_symbol_left'])?$order['order_currency_symbol_left']:$order['order_currency_symbol_right'],
	'order_products'=>array(),
	);
$order_products=$orderObj->getOrderProductsById($order_id);
foreach($order_products as $skey=>$orderproducts){
	$product = $pObj->getData($orderproducts["opr_product_id"],array("status"=>1,"favorite"=>$this->app_user["user_id"]));
//printArray($orderproducts);
	$arr['order_products'][] = array(
		'product_id'=>$orderproducts['opr_product_id'],
		'product_name' => $orderproducts['opr_name'], 
		'product_extra_html' => $orderproducts['opr_customization_string'], 
		'shop_id' => $orderproducts['opr_product_shop'], 
		'shop_name' => $orderproducts['opr_product_shop_name'], 
		'price_currency' => !empty($order['order_currency_symbol_left'])?$order['order_currency_symbol_left']:$order['order_currency_symbol_right'],
		'product_price'=>$orderproducts['opr_sale_price'],
		'customer_buying_price'=>$orderproducts['opr_customer_buying_price'],
		'customer_buying_customization_charge'=>$orderproducts['opr_customization_price'],
		'shipping'=>$orderproducts['opr_shipping_charges'],
		'tax'=>$orderproducts['opr_tax'],
		'qty'=>$orderproducts['opr_qty'],
		'shipping_method'=>$orderproducts['opr_shipping_label'],
		'favorite'=>$product["favorite"]?1:0,
		'product_image'=>Utilities::generateAbsoluteUrl('image','product_image',array($orderproducts["opr_product_id"]))
		);
}
die (json_encode(array('status'=>1,'fav_count'=>$this->user_details['favItems'],'cart_count'=>$this->cart_items,'unread_messages'=>$this->user_details['unreadMessages'],'data'=>$arr)));
}else{
	dieJsonError (Utilities::getLabel('M_INVALID_REQUEST'));
}
}
function child_order_detail(){
	global $payment_status_arr;
	$user_id = $this->app_user["user_id"];
	$pObj=new ApiProducts();
	$orderObj=new Orders();
	if(isset($_REQUEST['child_order_id']) && intval($_REQUEST['child_order_id']) > 0) $child_order_id = intval($_REQUEST['child_order_id']);
	$order = $orderObj->getOrderProductsByOprId($child_order_id,array("user"=>$user_id));
//printArray($order);exit;
	if ($order){
		$cObj=new Currencies($order['order_currency_code']);
		$order['order_currency_symbol_left'] = $cObj->getSymbolLeft();
		$order['order_currency_symbol_right'] = $cObj->getSymbolRight();
		$arr = array(
			'id'=>$order['order_id'],
			'invoice_number' => $order['order_invoice_number'], 
			'customer_name' => $order["order_user_name"],
			'customer_phone' => $order["order_user_phone"],
			'shipping_name' => $order["order_shipping_name"],
			'shipping_address_1' => $order["order_shipping_address1"],
			'shipping_address_2' => $order["order_shipping_address2"],
			'shipping_city' => $order["order_shipping_city"],
			'shipping_postcode' => $order["order_shipping_postcode"],
			'shipping_state' => $order["order_shipping_state"],
			'shipping_country' => $order["order_shipping_country"],
			'shipping_phone' => $order["order_shipping_phone"],
			'billing_name' => $order["order_billing_name"],
			'billing_address_1' => $order["order_billing_address1"],
			'billing_address_2' => $order["order_billing_address2"],
			'billing_city' => $order["order_billing_city"],
			'billing_postcode' => $order["order_billing_postcode"],
			'billing_state' => $order["order_billing_state"],
			'billing_country' => $order["order_billing_country"],
			'billing_phone' => $order["order_billing_phone"],
			'date' => $order["order_date_added"],
			'date_timestamp' => $order["order_date_timestamp"],				
			'order_status' => $order["orders_status_name"],							
			'payment_status' => $payment_status_arr[$order["order_payment_status"]],
			'coupon' => $order["order_discount_coupon"],
			'discount_total' => ($order["totOrders"]>1)?0:$order["order_discount_total"],
			'cart_total' => Utilities::displayMoneyFormat(($order["opr_customer_buying_price"]+$order["opr_customer_customization_price"])*$order['opr_qty'],true,false),
			'shipping' => ($order["totOrders"]>1)?$order["opr_shipping_charges"]:$order["order_shipping_charged"],
			'tax_perc' => $order["order_vat_perc"],
			'tax' => ($order["totOrders"]>1)?$order["opr_tax"]:$order["order_tax_charged"],				
			'net_paid' => ($order["totOrders"]>1)?$order["opr_net_charged"]:$order["order_net_charged"],
			'price_currency' => !empty($order['order_currency_symbol_left'])?$order['order_currency_symbol_left']:$order['order_currency_symbol_right'],
			'order_products'=>array(),
			);
		$order_products[]=$order;
		foreach($order_products as $key=>$child_order){
			$product = $pObj->getData($child_order["opr_product_id"],array("status"=>1,"favorite"=>$this->app_user["user_id"]));
			$arr['order_products'][] = array(
				'product_id'=>$child_order['opr_product_id'],
				'product_name' => $child_order['opr_name'], 
				'product_extra_html' => ltrim($child_order['opr_customization_string'],'<br />'), 
				'shop_id' => $child_order['opr_product_shop'], 
				'shop_name' => $child_order['opr_product_shop_name'], 
				'price_currency' => !empty($child_order['order_currency_symbol_left'])?$child_order['order_currency_symbol_left']:$child_order['order_currency_symbol_right'],
				'product_price'=>$child_order['opr_sale_price'],
				'customer_buying_price'=>$child_order['opr_customer_buying_price'],
				'customer_buying_customization_charge'=>$child_order['opr_customization_price'],
				'shipping'=>$child_order['opr_shipping_charges'],
				'tax'=>$child_order['opr_tax'],
				'qty'=>$order['opr_qty'],
				'shipping_method'=>$child_order['opr_shipping_label'],
				'favorite'=>$product["favorite"]?1:0,
				'product_image'=>Utilities::generateAbsoluteUrl('image','product_image',array($child_order["opr_product_id"]))
				);
		}
		die (json_encode(array('status'=>1,'fav_count'=>$this->user_details['favItems'],'cart_count'=>$this->cart_items,'unread_messages'=>$this->user_details['unreadMessages'],'data'=>$arr)));
	}else{
		dieJsonError (Utilities::getLabel('M_INVALID_REQUEST'));
	}
}
function shops(){
	$arr_shops = array();
	$page = 1;
	$pagesize=$this->pagesize;
	if(isset($_REQUEST['page']) && intval($_REQUEST['page']) > 0) $page = intval($_REQUEST['page']);
	if(isset($_REQUEST['pagesize']) && intval($_REQUEST['pagesize']) > 0) $pagesize = intval($_REQUEST['pagesize']);
	$criteria=array("status"=>1,"must_products"=>1,"favorite"=>$this->app_user["user_id"],"pagesize"=>$pagesize,"page"=>$page);
	$sObj=new Shops();
	$pObj=new ApiProducts();
	$all_shops=$sObj->getShopsByCriteria($criteria);
	foreach($all_shops as $skey=>$shop){ $shopInc++;
		$arr_shops[] = array(
			'id'=>$shop['shop_id'],
			'name' => $shop['shop_name'], 
			'slogan' => $shop['shop_slogan'], 
			'description' => $shop['shop_description'],
			'owner'=>$shop['shop_owner_username'],
			'products_count'=>$shop['totProducts'],
			'rating'=>$shop['shop_rating'],
			'reviews'=>$shop['totReviews'],
			'favorite'=>$shop["favorite"]?1:0,
			'logo'=>Utilities::generateAbsoluteUrl('image','shop_logo',array($shop["shop_logo"])),
			'banner'=>Utilities::generateAbsoluteUrl('image','shop_banner',array($shop["shop_banner"])),
			'products'=>array()
			);
		$pObj= new ApiProducts();
		$pObj->joinWithDetailTable();
		$pObj->joinWithCategoryTable();
		$pObj->joinWithBrandsTable();
		$pObj->joinWithPromotionsTable();
		$pObj->addSpecialPrice();
		$pObj->setPageSize(3);
		$pObj->selectFields(array('tp.prod_id','tp.prod_name','tp.prod_sale_price','tp.prod_stock','ts.shop_name','IF(prod_stock >0, "1", "0" ) as available'));	
		$shop_products=$pObj->getProducts(array("sort"=>"feat","shop"=>$shop["shop_id"]));
		foreach($shop_products as $skey=>$product){
			$price=$product['prod_sale_price'];
			$product_price=(!$product['special'])?$price:$product['special'];
			$arr_shops[$shopInc-1]['products'][] = array(
				'product_id'=>$product["prod_id"],
				'product_name' => $product["prod_name"], 
				'store_name' => $product["shop_name"], 
				'price_currency' => CONF_CURRENCY_SYMBOL,
				'product_price'=>$product_price,
				'favorite'=>$product["favorite"]?1:0,
				'product_image'=>Utilities::generateAbsoluteUrl('image','product_image',array($product["prod_id"]))
				);
		}
	}
	die (json_encode(array('status'=>1,'fav_count'=>$this->user_details['favItems'],'cart_count'=>$this->cart_items, 'unread_messages'=>$this->user_details['unreadMessages'],'shops'=>$arr_shops,'total_records'=>$sObj->getTotalRecords())));
}
function featured_shops(){
	$arr_shops = array();
	$page = 1;
	$pagesize=$this->pagesize;
	if(isset($_REQUEST['page']) && intval($_REQUEST['page']) > 0) $page = intval($_REQUEST['page']);
	if(isset($_REQUEST['pagesize']) && intval($_REQUEST['pagesize']) > 0) $pagesize = intval($_REQUEST['pagesize']);
	$criteria=array("featured"=>1,
		"status"=>1,
		"must_products"=>1,
		/* "favorite"=>$this->app_user["user_id"], */
		"pagesize"=>$pagesize,
		"page"=>$page);
	$sObj=new Shops();
	$pObj=new ApiProducts();
	$all_shops=$sObj->getShopsByCriteria($criteria);
	foreach($all_shops as $skey=>$shop){ $shopInc++;
		$arr_shops[] = array(
			'id'=>$shop['shop_id'],
			'name' => $shop['shop_name'], 
			'slogan' => $shop['shop_slogan'], 
			'description' => $shop['shop_description'],
			'owner'=>$shop['shop_owner_username'],
			'products_count'=>$shop['totProducts'],
			'rating'=>$shop['shop_rating'],
			'reviews'=>$shop['totReviews'],
			'favorite'=>$shop["favorite"]?1:0,
			'logo'=>Utilities::generateAbsoluteUrl('image','shop_logo',array($shop["shop_logo"])),
			'banner'=>Utilities::generateAbsoluteUrl('image','shop_banner',array($shop["shop_banner"])),
			'products'=>array()
			);
//$shop_products=$pObj->getProducts(array("sort"=>"feat","status"=>1,"shop"=>$shop["shop_id"],"pagesize"=>3));
		$pObj= new ApiProducts();
		$pObj->joinWithDetailTable();
		$pObj->joinWithCategoryTable();
		$pObj->joinWithBrandsTable();
		$pObj->joinWithPromotionsTable();
		$pObj->addSpecialPrice();
		$pObj->setPageSize(3);
		$pObj->selectFields(array('tp.prod_id','tp.prod_name','tp.prod_sale_price','tp.prod_stock','ts.shop_name','IF(prod_stock >0, "1", "0" ) as available'));	
		$shop_products=$pObj->getProducts(array("sort"=>"feat","shop"=>$shop["shop_id"]));
		foreach($shop_products as $skey=>$product){
			$price=$product['prod_sale_price'];
			$product_price=(!$product['special'])?$price:$product['special'];
			$arr_shops[$shopInc-1]['products'][] = array(
				'product_id'=>$product["prod_id"],
				'product_name' => $product["prod_name"], 
				'store_name' => $product["shop_name"], 
				'price_currency' => CONF_CURRENCY_SYMBOL,
				'product_price'=>$product_price,
				'favorite'=>$product["favorite"]?1:0,
				'product_image'=>Utilities::generateAbsoluteUrl('image','product_image',array($product["prod_id"]))
				);
		}
	}
/*Utilities::printarray($arr_shops);
die();*/
die (json_encode(array('status'=>1,'fav_count'=>$this->user_details['favItems'],'cart_count'=>$this->cart_items,'unread_messages'=>$this->user_details['unreadMessages'], 'shops'=>$arr_shops,'total_records'=>$sObj->getTotalRecords())));
}
function brands(){
	$arr_brands = array();
	$page = 1;
	$pagesize=$this->pagesize;
	if(isset($_REQUEST['page']) && intval($_REQUEST['page']) > 0) $page = intval($_REQUEST['page']);
	if(isset($_REQUEST['pagesize']) && intval($_REQUEST['pagesize']) > 0) $pagesize = intval($_REQUEST['pagesize']);
	$criteria=array(
		"status"=>1,
		"page"=>$page,
		"pagesize"=>$pagesize,
		"must_products"=>1,
		"order"=>"brand_name");
	$bObj=new brands();
	$pObj=new ApiProducts();
	$all_brands=$bObj->getBrands($criteria);
	foreach($all_brands as $bkey=>$brand){ $brand_inc++;
		$arr_brands[] = array(
			'id'=>$brand['brand_id'],
			'name' => $brand['brand_name'], 
			'products'=>array()
			);
		$pObj= new ApiProducts();
		$pObj->joinWithDetailTable();
		$pObj->joinWithPromotionsTable();
		$pObj->joinWithBrandsTable();
		$pObj->addSpecialPrice();
		$pObj->setPageSize(3);
		$pObj->selectFields(array('tp.prod_id','tp.prod_name','tp.prod_sale_price','tp.prod_stock','ts.shop_name','IF(prod_stock >0, "1", "0" ) as available'));	
		$brand_products=$pObj->getProducts(array("sort"=>"feat","brand"=>$brand["brand_id"]));
		foreach($brand_products as $bkey=>$product){
			$price=$product['prod_sale_price'];
			$product_price=(!$product['special'])?$price:$product['special'];
			$arr_brands[$brand_inc-1]['products'][] = array(
				'product_id'=>$product["prod_id"],
				'product_name' => $product["prod_name"], 
				'store_name' => $product["shop_name"], 
				'price_currency' => CONF_CURRENCY_SYMBOL,
				'product_price'=>$product_price,
				'favorite'=>$product["favorite"]?1:0,
				'product_image'=>Utilities::generateAbsoluteUrl('image','product_image',array($product["prod_id"]))
				);
		}
	}
	die (json_encode(array('status'=>1,'fav_count'=>$this->user_details['favItems'],'cart_count'=>$this->cart_items,'unread_messages'=>$this->user_details['unreadMessages'], 'brands'=>$arr_brands,'total_records'=>$bObj->getTotalRecords())));
}
function brand_products(){
	$arr_products = array();
	$page = 1;
	$brand_id=(isset($_REQUEST['brand_id']) && intval($_REQUEST['brand_id']) > 0)?intval($_REQUEST['brand_id']):-1;
	$pagesize=$this->pagesize;
	if(isset($_REQUEST['page']) && intval($_REQUEST['page']) > 0) $page = intval($_REQUEST['page']);
	if(isset($_REQUEST['pagesize']) && intval($_REQUEST['pagesize']) > 0) $pagesize = intval($_REQUEST['pagesize']);
	$criteria=array("brand"=>$brand_id,"status"=>1,"sort"=>"feat","favorite"=>$this->app_user["user_id"],"pagesize"=>$pagesize,"page"=>$page);
	$pObj= new ApiProducts();
	$pObj->joinWithDetailTable();
	$pObj->joinWithPromotionsTable();
	$pObj->joinWithBrandsTable();
	$pObj->addSpecialPrice();
	$pObj->setPageSize($pagesize);
	$pObj->selectFields(array('tp.prod_id','tp.prod_name','tp.prod_sale_price','tp.prod_stock','ts.shop_name','IF(prod_stock >0, "1", "0" ) as available'));	
	$all_products=$pObj->getProducts($criteria);
	foreach($all_products as $pkey=>$product){
		$price=$product['prod_sale_price'];
		$product_price=(!$product['special'])?$price:$product['special'];
		$arr_products[] = array(
			'product_id'=>$product["prod_id"],
			'product_name' => $product["prod_name"], 
			'store_name' => $product["shop_name"], 
			'price_currency' => CONF_CURRENCY_SYMBOL,
			'product_price'=>$product_price,
			'favorite'=>$product["favorite"]?1:0,
			'product_stock'=>max($product['prod_stock'],0),
			'product_available'=>$product["available"],
			'product_image'=>Utilities::generateAbsoluteUrl('image','product_image',array($product["prod_id"]))
			);
	}
	die (json_encode(array('status'=>1,'fav_count'=>$this->user_details['favItems'],'cart_count'=>$this->cart_items,'unread_messages'=>$this->user_details['unreadMessages'], 'products'=>$arr_products,'total_records'=>$pObj->getTotalRecords())));
}
function shop_products(){
	$arr_products = array();
	$page = 1;
	$shop_id=(isset($_REQUEST['shop_id']) && intval($_REQUEST['shop_id']) > 0)?intval($_REQUEST['shop_id']):0;
	$pagesize=$this->pagesize;
	if(isset($_REQUEST['page']) && intval($_REQUEST['page']) > 0) $page = intval($_REQUEST['page']);
	if(isset($_REQUEST['pagesize']) && intval($_REQUEST['pagesize']) > 0) $pagesize = intval($_REQUEST['pagesize']);
	$criteria=array("shop"=>$shop_id,"status"=>1,"sort"=>"dhtl",/* "favorite"=>$this->app_user["user_id"], */"pagesize"=>$pagesize,"page"=>$page);
	$sObj=new Shops();
	$product_shop = $sObj->getData($shop_id,array("favorite"=>$this->app_user["user_id"]));
	$pObj= new ApiProducts();
	$pObj->joinWithDetailTable();
	$pObj->joinWithPromotionsTable();
	$pObj->joinWithBrandsTable();
	$pObj->addSpecialPrice();
	$pObj->setPageSize($pagesize);
	$pObj->selectFields(array('tp.prod_id','tp.prod_name','tp.prod_sale_price','tp.prod_stock','ts.shop_name','IF(prod_stock >0, "1", "0" ) as available'));	
	$all_products=$pObj->getProducts($criteria);
	foreach($all_products as $pkey=>$product){
		$price=$product['prod_sale_price'];
		$product_price=(!$product['special'])?$price:$product['special'];
		$arr_products[] = array(
			'product_id'=>$product["prod_id"],
			'product_name' => $product["prod_name"], 
			'store_name' => $product["shop_name"],
			'store_rating' => $product_shop["shop_rating"],
			'store_logo' => Utilities::generateAbsoluteUrl('image','shop_logo',array($product["shop_logo"])), 
			'store_banner' => Utilities::generateAbsoluteUrl('image','shop_banner',array($product["shop_banner"])), 
			'store_state_name' => $product_shop["state_name"],
			'store_country_name' => $product_shop["country_name"],
			'total_store_products' => $product_shop["totProducts"],  
			'total_store_reviews' => $product_shop["totReviews"],   
			'price_currency' => CONF_CURRENCY_SYMBOL,
			'product_price'=>$product_price,
			'product_stock'=>max($product['prod_stock'],0),
			'product_available'=>$product["available"],
			'favorite'=>$product["favorite"]?1:0,
			'product_image'=>Utilities::generateAbsoluteUrl('image','product_image',array($product["prod_id"]))
			);
	}
	die (json_encode(array('status'=>1,'fav_count'=>$this->user_details['favItems'],'cart_count'=>$this->cart_items,'unread_messages'=>$this->user_details['unreadMessages'], 'products'=>$arr_products,'total_records'=>$pObj->getTotalRecords())));
}
function shop_detail($shop_id){
	$uObj=new User();
	$post = Syspage::getPostedVar();
	$pObj=new ApiProducts();
	$sObj=new Shops();
	$cObj=new Categories();
	$shop_id=isset($_REQUEST['shop_id'])?$_REQUEST['shop_id']:$shop_id;
	$shop = $sObj->getData($shop_id,array("status"=>1,"favorite"=>$this->app_user["user_id"]));
	if ($shop){
		$arr = array(
			'id'=>$shop['shop_id'],
			'name' => $shop['shop_name'], 
			'description' => $shop["shop_description"],
			'city' => $shop["shop_city"],
			'state' => $shop["state_name"],
			'country' => $shop["country_name"],
			'logo' => Utilities::generateAbsoluteUrl('image','shop_logo',array($shop["shop_logo"])), 
			'banner' => Utilities::generateAbsoluteUrl('image','shop_banner',array($shop["shop_banner"])), 
			'products_count' => $shop["totProducts"],
			'reviews_count' => $shop["totReviews"],
			'rating' => $shop["shop_rating"],  
			'shop_owner' => $shop["shop_owner"],  
			'shop_owner_username' => $shop["shop_owner_username"],
			'shop_owner_image'=>Utilities::generateAbsoluteUrl('image','user',array($shop["shop_owner_profile_image"])),
			'shop_owner_state_name'=>$shop['shop_owner_state_name'],
			'shop_owner_country_name'=>$shop['shop_owner_country_name'],
			'shop_date'=>$shop['shop_date'],
			'favorite'=>$shop["favorite"]?1:0,
			'policy_url'=>Utilities::generateAbsoluteUrl('shops','api_policies',array($shop["shop_id"])),
			'products'=>array(),
			);
		$pObj= new ApiProducts();
		$pObj->joinWithDetailTable();
		$pObj->joinWithPromotionsTable();
		$pObj->joinWithBrandsTable();
		$pObj->addSpecialPrice();
		$pObj->setPageSize(4);
		$pObj->selectFields(array('tp.prod_id','tp.prod_name','tp.prod_sale_price','tp.prod_stock','ts.shop_name','IF(prod_stock >0, "1", "0" ) as available'));	
		$shop_products=$pObj->getProducts(array("sort"=>"feat","status"=>1,"shop"=>$shop_id));
		foreach($shop_products as $skey=>$product){
			$price=$product['prod_sale_price'];
			$product_price=(!$product['special'])?$price:$product['special'];
			$arr['products'][] = array(
				'product_id'=>$product["prod_id"],
				'product_name' => $product["prod_name"], 
				'store_name' => $product["shop_name"], 
				'price_currency' => CONF_CURRENCY_SYMBOL,
				'product_price'=>$product_price,
				'favorite'=>$product["favorite"]?1:0,
				'product_image'=>Utilities::generateAbsoluteUrl('image','product_image',array($product["prod_id"]))
				);
		}
		die (json_encode(array('status'=>1,'fav_count'=>$this->user_details['favItems'],'cart_count'=>$this->cart_items, 'unread_messages'=>$this->user_details['unreadMessages'], 'data'=>$arr)));
	}else{
		dieJsonError (Utilities::getLabel('M_INVALID_REQUEST'));
	}
}
function shop_reviews($shop_id){
	$reviews=array();
	$page = 1;
	$pagesize=$this->pagesize;
	if(isset($_REQUEST['page']) && intval($_REQUEST['page']) > 0) $page = intval($_REQUEST['page']);
	if(isset($_REQUEST['pagesize']) && intval($_REQUEST['pagesize']) > 0) $pagesize = intval($_REQUEST['pagesize']);
	$sObj=new Shops();
	$pfObj=new Productfeedbacks();
	$shop_id = isset($_REQUEST['shop_id'])?$_REQUEST['shop_id']:$shop_id;
	$shop = $sObj->getData($shop_id);		
	if ($shop){
		$shop_reviews = $pfObj->getFeedbacksWithCriteria(array("shop"=>$shop_id,"status"=>1,"pagesize"=>$pagesize,"page"=>$page));
		foreach($shop_reviews as $key=>$review){
			$reviews[] = array(
				'id'=>$review['review_id'],
				'rating' => $review['review_rating'], 
				'text' => $review['review_text'],
				'date'=>$review['reviewed_on'],
				'reviewed_by'=>$review["user_username"],
				'reviewed_by_image'=>Utilities::generateAbsoluteUrl('image', 'user',array($review["user_profile_image"]))
				);
		}
		die (json_encode(array('status'=>1,'fav_count'=>$this->user_details['favItems'],'cart_count'=>$this->cart_items,'unread_messages'=>$this->user_details['unreadMessages'],'product_name'=>$product['prod_name'],'reviews'=>$reviews,'total_records'=>$pfObj->getTotalRecords())));
	}else{
		dieJsonError (Utilities::getLabel('M_INVALID_REQUEST'));
	}
}
function countries(){
	$countryObj=new Countries();
	$countries = $countryObj->getAssociativeArray();
	foreach($countries as $key=>$val){
		$arr_country[]=array("id"=>$key,'name'=>$val);
	}
	die (json_encode(array('status'=>1, 'countries'=>$arr_country)));
}
function states(){
	$arr_states=array();
	$country_id=(isset($_REQUEST['country_id']) && intval($_REQUEST['country_id']) > 0)?intval($_REQUEST['country_id']):0;
	$stateObj=new States();
	$states = $stateObj->getStatesAssoc($country_id);
	foreach($states as $key=>$val){
		$arr_states[]=array("id"=>$key,'name'=>$val);
	}
	die (json_encode(array('status'=>1, 'states'=>$arr_states)));
}
}
