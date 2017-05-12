<?php
class CustomController extends CommonController{
	function contact_us(){
		$extraPageObj=new Extrapage();
		$arr_contact_content=$extraPageObj->getExtraBlockData(array('identifier'=>'CONTACT_US_CONTENT_BLOCK'));
		$contact_content=$arr_contact_content["epage_content"];
		$frm=new Form('frmContact','frmContact');
		$frm->setValidatorJsObjectName('system_validator');
		$frm->setExtra('class="website-form contact-us"');
		$frm->setAction('?');
		$frm->captionInSameCell(true);
		$frm->setFieldsPerRow(1);
		$frm->setLeftColumnProperties('');
		$frm->setTableProperties(' width="100%" border="0" class="tableform"');
		$frm->addRequiredField('<label>'.Utilities::getLabel('F_Your_Name').'</label>', 'name','','name');
		$fld=$frm->addEmailField('<label>'.Utilities::getLabel('F_Your_Email').'</label>', 'email');
		$fld_phn=$frm->addRequiredField('<label>'.Utilities::getLabel('F_Your_Phone').'</label>', 'phone');
		$fld_phn->requirements()->setRegularExpressionToValidate('^[\s()+-]*([0-9][\s()+-]*){5,20}$');
		$fld=$frm->addTextArea('<label>'.Utilities::getLabel('F_Your_Message').'</label>', 'message');
		$fld->requirements()->setRequired();
		if (!empty(CONF_RECAPTACHA_SITEKEY)){
			$frm->addHtml('', 'htmlNote','<div class="g-recaptcha" data-sitekey="'.CONF_RECAPTACHA_SITEKEY.'"></div>');
		}
		$fld->merge_caption=true;
		$frm->addSubmitButton('','btn_submit',Utilities::getLabel('F_Submit'),'');
		$frm->setJsErrorDisplay('afterfield');
		$post = Syspage::getPostedVar();
		if(isset($post['btn_submit'])) {
			if(!$frm->validate($post)) {
				$frm->fill($post);
				Message::addErrorMessage($frm->getValidationErrors());
			} else if(!Utilities::verifyCaptcha()) {
				Message::addErrorMessage(Utilities::getLabel('M_ERROR_PLEASE_VERIFY_YOURSELF'));
				$frm->fill($post);
			} else {
				$email = explode(',', Settings::getSetting("CONF_CONTACT_EMAIL"));
				foreach($email as $email_id) {
					$email_id = trim($email_id);
					if(!Utilities::isValidEmail($email_id)) continue;
					$rs = Utilities::sendMailTpl($email_id, 'contact_us', array(
						'{name}' => $post['name'],
						'{email_address}' => $post['email'],
						'{phone_number}' => $post['phone'],
						'{message}' => nl2br($post['message']),
						'{site_domain}' => CONF_SERVER_PATH,
						'{website_name}' => Settings::getSetting("CONF_WEBSITE_NAME"),
						));
				}
				$rs = true;
				if($rs==true) {
					Message::addMessage(Utilities::getLabel('M_your_message_sent_successfully'));
				} else {
					Message::addErrorMessage(Utilities::getLabel('M_email_not_sent_server_issue'));
				}
				Utilities::redirectUser(Utilities::generateUrl('custom', 'contact_us'));
			}
		}
		$this->set('frm', $frm);
		$this->set('contact_content', $contact_content);
		$this->_template->render();	
	}
	function sitemap(){
//$this->set('start_letter', 'a');
		$letters=array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
		$navObj=new Navigation();
		$this->set('pages', $navObj->getNavigationPages(2));
		$categories=Categories::getCategoriesAssocArrayFront(0,1);
		$this->set('categories', $categories);
		$this->set('letters', $letters);
		$this->_template->render();
	}
	function favorite_shops($user_id){
		$userObj=new User();
		$user_details=$userObj->getUserById($user_id);
		if (!$user_details)
			Utilities::show404();
		$this->set('user_details', $user_details);
		$favorite_user_id=$userObj->getLoggedUserId()?$userObj->getLoggedUserId():0;
		$favorite_shops=$userObj->getUserFavoriteShops(array("user"=>$user_id,"favorite"=>$favorite_user_id));
		foreach($favorite_shops as $key=>$val){
			$prodObj= new Products();
			$prodObj->joinWithPromotionsTable();
			$prodObj->addSpecialPrice();
			$prodObj->selectFields(array('tp.*','ts.shop_id','ts.shop_user_id','ts.shop_name','ts.shop_title','tu.user_id','tu.user_name','tu.user_username','tu.user_email','IF(prod_stock >0, "1", "0" ) as available'));
			$prodObj->setPagesize(4);
			$shop_products=$prodObj->getProducts(array("shop"=>$val["shop_id"]));
			$arr_shop_prods=array("products"=>$shop_products);
			$favorite_shops_items[]=array_merge($val,$arr_shop_prods);
		}
		$this->set('favourite_shops', $favorite_shops_items);
		$this->_template->render();	
	}
	function favorite_items($user_id){
		$userObj=new User();	
		$user_details=$userObj->getUserById($user_id);
		if (!$user_details)
			Utilities::show404();
		$this->set('user_details', $user_details);
		$favorite_user_id=$userObj->getLoggedUserId()?$userObj->getLoggedUserId():0;
		$favorite_items=$userObj->getUserFavoriteItems(array("user"=>$user_id,"favorite"=>$favorite_user_id));
		$this->set('products', $favorite_items);
		$this->_template->render();	
	}
	function payment_success(){
		$hide_header_footer=Utilities::isHideHeaderFooter();
		if ($this->isUserLogged()) {
			if($hide_header_footer){
				$text_message = Utilities::getLabel('M_APP_customer_success_order');
			}else{
				$text_message = sprintf(Utilities::getLabel('M_customer_success_order'), Utilities::generateUrl('account','dashboard_buyer'), Utilities::generateUrl('account','orders'), Utilities::generateUrl('custom','contact_us'));
			}			
		} else {
			if($hide_header_footer){
				$text_message = Utilities::getLabel('M_APP_guest_success_order');
			}else{
				$text_message = sprintf(Utilities::getLabel('M_guest_success_order'),Utilities::generateUrl('custom','contact_us'));
			}
		}
		$this->set('text_message', $text_message);
		$this->set('hide_header_footer', $hide_header_footer);
		if($hide_header_footer){
			$this->_template->render(false,false);	
		}else{
			Syspage::addJs(array(
			'js/owl.carousel.js'
			) , false);
			$pObj= new Products();
			$pObj->joinWithPromotionsTable();
			$pObj->addSpecialPrice();
			$pObj->selectFields(array('tp.*','ts.shop_id','ts.shop_user_id','ts.shop_name','ts.shop_title','tu.user_id','tu.user_name','tu.user_username','tu.user_email','IF(prod_stock >0, "1", "0" ) as available'));
			$smart_recommended_products = $pObj->getSmartRecommendedProducts(0);
			$this->set('smart_recommended_products', $smart_recommended_products);
			$this->_template->render();	
		}
	}
	function payment_failed(){
		$hide_header_footer=Utilities::isHideHeaderFooter();
		if($hide_header_footer){
			$text_message = Utilities::getLabel('M_APP_customer_failure_order');
		}else{
			$text_message = sprintf(Utilities::getLabel('M_customer_failure_order'),Utilities::generateUrl('custom','contact_us'));
		}		
		$this->set('text_message', $text_message);
		$this->set('hide_header_footer', $hide_header_footer);
		if($hide_header_footer){
			$this->_template->render(false,false);	
		}else{
			$this->_template->render();	
		}	
	}
	function package_payment_success(){
		$hide_header_footer=Utilities::isHideHeaderFooter();
		if ($this->isUserLogged()) {
			if($hide_header_footer){
				$text_message = Utilities::getLabel('M_APP_seller_package_success_order');
			}else{
				$text_message = sprintf(Utilities::getLabel('M_seller_package_success_order'), Utilities::generateUrl('account','dashboard_supplier'), Utilities::generateUrl('account','subscriptions'), Utilities::generateUrl('custom','contact_us'));
			}			
		} else {
			if($hide_header_footer){
				$text_message = Utilities::getLabel('M_APP_guest_package_success_order');
			}else{
				$text_message = sprintf(Utilities::getLabel('M_guest_package_success_order'),Utilities::generateUrl('custom','contact_us'));
			}
		}
		
		$this->set('text_message', $text_message);
		$this->set('hide_header_footer', $hide_header_footer);
		if($hide_header_footer){
			$this->_template->render(false,false);	
		}else{
			$this->_template->render();	
		}
	}
	function package_payment_failed(){
		$hide_header_footer=Utilities::isHideHeaderFooter();
		$text_message = sprintf(Utilities::getLabel('M_seller_package_failure_order'), Utilities::generateUrl('custom','contact_us'));
		$this->set('text_message', $text_message);
		$this->set('hide_header_footer', $hide_header_footer);
		if($hide_header_footer){
			$this->_template->render(false,false);	
		}else{
			$this->_template->render();	
		}
	}
	
}
