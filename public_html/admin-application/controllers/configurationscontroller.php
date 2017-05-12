<?php
class ConfigurationsController extends CommonController{
	private $arr_date_format_php;
	private $arr_date_format_mysql;
	private $arr_date_format_jquery;
	
	function __construct($model, $controller, $action){
		parent::__construct($model, $controller, $action);
		$this->arr_date_format_php=array('Y-m-d','d/m/Y','m-d-Y','M d, Y');
		$this->arr_date_format_mysql=array('%Y-%m-%d','%d/%m/%Y','%m-%d-%Y','%b %d, %Y');
		$this->arr_languages=array('en'=>'English','es'=>"Alternate Language");
	}
	
	function view_server_info() {
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),SERVER_INFO)) {
     	   die(Admin::getUnauthorizedMsg());
	    }
		if(Utilities::isOurDemoSystem()) {	
     	   die('This feature is disabled for security reasons. Please feel free to contact our sales team to request more details about this feature.');
	    }
		ob_start();
		phpinfo();
		$phpinfo = ob_get_contents();
		ob_end_clean();
		$phpinfo = str_replace('border: 1px', '', $phpinfo);
		preg_match('#<body>(.*?)</body>#is', $phpinfo, $regs);
		$frm=new Form('frmServerInfo');
		$frm->setExtra('class="form-horizontal"');
		$frm->captionInSameCell(false);
		$frm->setFieldsPerRow(2);
		$frm->setLeftColumnProperties(' class="text_black_new"');
		$frm->setTableProperties(' width="95%" border="0" cellspacing="10" cellpadding="0"');
		$frm->addHTML('<strong>PHP Version:</strong>','', phpversion(), '');
		$frm->addHTML('<strong>DB Version:</strong>','', 'MySQL ' . mysqli_get_server_info(), '');
		$frm->addHTML('<strong>Database Server:</strong>','', CONF_DB_SERVER, '');
		$frm->addHTML('<strong>Database Name:</strong>','', CONF_DB_NAME, '');
		$frm->addHTML('<strong>PHP Details</strong>','', '<div id="php_details" class="gray">'.$regs[1].'</div>', '');
        $this->set('frmServerInfo', $frm);
        $this->_template->render();
    }
	
	function remove_watermark(){
		Utilities::unlinkFile(Settings::getSetting("CONF_WATERMARK_IMAGE"));
		$settingsObj=new Settings();
		if($settingsObj->update(array("CONF_WATERMARK_IMAGE"=>""))){
			Message::addMessage('Success: Watermark successfully removed.');
		}else{
			Message::addErrorMessage($settingsObj->getError());
		}
		Utilities::redirectUserReferer();
	}
	
	function remove_footer_graphic(){
		Utilities::unlinkFile(Settings::getSetting("CONF_FOOTER_LOGO_GRAPHIC"));
		$settingsObj=new Settings();
		if($settingsObj->update(array("CONF_FOOTER_LOGO_GRAPHIC"=>""))){
			Message::addMessage('Success: Footer Graphic successfully removed.');
		}else{
			Message::addErrorMessage($settingsObj->getError());
		}
		Utilities::redirectUserReferer();
	}
	
	function remove_mobile_icon(){
		Utilities::unlinkFile(Settings::getSetting("CONF_FRONT_MOBILE_LOGO_ICON"));
		$settingsObj=new Settings();
		if($settingsObj->update(array("CONF_FRONT_MOBILE_LOGO_ICON"=>""))){
			Message::addMessage('Success: Mobile Icon/Logo successfully removed.');
		}else{
			Message::addErrorMessage($settingsObj->getError());
		}
		Utilities::redirectUserReferer();
	}
	
	function remove_favicon(){
		Utilities::unlinkFile(Settings::getSetting("CONF_FAVICON"));
		$settingsObj=new Settings();
		if($settingsObj->update(array("CONF_FAVICON"=>""))){
			Message::addMessage('Success: Favicon successfully removed.');
		}else{
			Message::addErrorMessage($settingsObj->getError());
		}
		Utilities::redirectUserReferer();
	}
	
	function remove_apple_touch_icon(){
		Utilities::unlinkFile(Settings::getSetting("CONF_APPLE_TOUCH_ICON"));
		$settingsObj=new Settings();
		if($settingsObj->update(array("CONF_APPLE_TOUCH_ICON"=>""))){
			Message::addMessage('Success: Apple touch icon successfully removed.');
		}else{
			Message::addErrorMessage($settingsObj->getError());
		}
		Utilities::redirectUserReferer();
	}
	
	function return_bytes($val) {
		$val = trim($val);
		$last = strtolower($val[strlen($val)-1]);
		switch($last) {
			// The 'G' modifier is available since PHP 5.1.0
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}
		return $val;
	}

	function default_action($type="general"){
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),GENERALSETTINGS)) {
     	   die(Admin::getUnauthorizedMsg());
	    }
		$settingsObj=new Settings();
		$frm=$this->getForm($type);
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				
				if(is_uploaded_file($_FILES['CONF_ADMIN_LOGO']['tmp_name'])){
					if (Utilities::isUploadedFileValidImage($_FILES['CONF_ADMIN_LOGO'])){
						if(!Utilities::saveImage($_FILES['CONF_ADMIN_LOGO']['tmp_name'],$_FILES['CONF_ADMIN_LOGO']['name'], $saved_image_name, '',true)){
							Message::addError($saved_image_name);
						}
						$post["CONF_ADMIN_LOGO"]=$saved_image_name;
					}else{
						Message::addError('Please upload valid image file for admin logo.');
					}
				}
				
				if(is_uploaded_file($_FILES['CONF_FRONT_LOGO']['tmp_name'])){
					if (Utilities::isUploadedFileValidImage($_FILES['CONF_FRONT_LOGO'])){
						if(!Utilities::saveImage($_FILES['CONF_FRONT_LOGO']['tmp_name'],$_FILES['CONF_FRONT_LOGO']['name'], $saved_image_name, '',true)){
							Message::addError($saved_image_name);
						}
						$post["CONF_FRONT_LOGO"]=$saved_image_name;
					}else{
						Message::addError('Please upload valid image file for site logo.');
					}
				}
				
				if(is_uploaded_file($_FILES['CONF_EMAIL_LOGO']['tmp_name'])){
					if (Utilities::isUploadedFileValidImage($_FILES['CONF_EMAIL_LOGO'])){
						if(!Utilities::saveImage($_FILES['CONF_EMAIL_LOGO']['tmp_name'],$_FILES['CONF_EMAIL_LOGO']['name'], $saved_image_name, '',true)){
							Message::addError($saved_image_name);
						}
						$post["CONF_EMAIL_LOGO"]=$saved_image_name;
					}else{
						Message::addError('Please upload valid image file for email logo.');
					}
				}
				
				if(is_uploaded_file($_FILES['CONF_FRONT_MOBILE_LOGO_ICON']['tmp_name'])){
					if (Utilities::isUploadedFileValidImage($_FILES['CONF_FRONT_MOBILE_LOGO_ICON'])){
						if(!Utilities::saveImage($_FILES['CONF_FRONT_MOBILE_LOGO_ICON']['tmp_name'],$_FILES['CONF_FRONT_MOBILE_LOGO_ICON']['name'], $saved_image_name, '',true)){
							Message::addError($saved_image_name);
						}
						$post["CONF_FRONT_MOBILE_LOGO_ICON"]=$saved_image_name;
					}else{
						Message::addError('Please upload valid image file for mobile logo.');
					}
				}
				
				if(is_uploaded_file($_FILES['CONF_FAVICON']['tmp_name'])){
					if (Utilities::isUploadedFileValidImage($_FILES['CONF_FAVICON'])){
						if(!Utilities::saveImage($_FILES['CONF_FAVICON']['tmp_name'],$_FILES['CONF_FAVICON']['name'], $saved_image_name, '',true)){
							Message::addError($saved_image_name);
						}
						$post["CONF_FAVICON"]=$saved_image_name;
					}else{
						Message::addError('Please upload valid image file for favicon.');
					}
				}
				
				if(is_uploaded_file($_FILES['CONF_APPLE_TOUCH_ICON']['tmp_name'])){
					if (Utilities::isUploadedFileValidImage($_FILES['CONF_APPLE_TOUCH_ICON'])){
						if(!Utilities::saveImage($_FILES['CONF_APPLE_TOUCH_ICON']['tmp_name'],$_FILES['CONF_APPLE_TOUCH_ICON']['name'], $saved_image_name, '',true)){
							Message::addError($saved_image_name);
						}
						$post["CONF_APPLE_TOUCH_ICON"]=$saved_image_name;
					}else{
						Message::addError('Please upload valid image file for apple touch icon.');
					}
				}
				
				if(is_uploaded_file($_FILES['CONF_FOOTER_LOGO_GRAPHIC']['tmp_name'])){
					if (Utilities::isUploadedFileValidImage($_FILES['CONF_FOOTER_LOGO_GRAPHIC'])){
						if(!Utilities::saveImage($_FILES['CONF_FOOTER_LOGO_GRAPHIC']['tmp_name'],$_FILES['CONF_FOOTER_LOGO_GRAPHIC']['name'], $saved_image_name, '',true)){
							Message::addError($saved_image_name);
						}
						$post["CONF_FOOTER_LOGO_GRAPHIC"]=$saved_image_name;
					}else{
						Message::addError('Please upload valid image file for footer logo graphic.');
					}
				}
				
				if(is_uploaded_file($_FILES['CONF_WATERMARK_IMAGE']['tmp_name'])){				
					if (Utilities::isUploadedFileValidImage($_FILES['CONF_WATERMARK_IMAGE'])){
						if(!Utilities::saveImage($_FILES['CONF_WATERMARK_IMAGE']['tmp_name'],$_FILES['CONF_WATERMARK_IMAGE']['name'], $saved_image_name, '',true)){
							Message::addError($saved_image_name);
						}
						$post["CONF_WATERMARK_IMAGE"]=$saved_image_name;
					}else{
						Message::addError('Please upload valid image file for watermark.');
					}
				}
				
				if(is_uploaded_file($_FILES['CONF_SOCIAL_FEED_IMAGE']['tmp_name'])){
					if (Utilities::isUploadedFileValidImage($_FILES['CONF_SOCIAL_FEED_IMAGE'])){
						if(!Utilities::saveImage($_FILES['CONF_SOCIAL_FEED_IMAGE']['tmp_name'],$_FILES['CONF_SOCIAL_FEED_IMAGE']['name'], $saved_image_name, '',true)){
							Message::addError($saved_image_name);
						}
						$post["CONF_SOCIAL_FEED_IMAGE"]=$saved_image_name;
					}else{
						Message::addError('Please upload valid image file for social feed image.');
					}
				}
				
				//die($this->return_bytes(ini_get('upload_max_filesize'))."#");
				if(($post['CONF_DIGITAL_MAX_FILE_SIZE']>0) && ($post['CONF_DIGITAL_MAX_FILE_SIZE']>$this->return_bytes(ini_get('upload_max_filesize')))){
					unset($post['CONF_DIGITAL_MAX_FILE_SIZE']);
					Message::addError('Please keep "Max File Size" within allowed limit, which is '.$this->return_bytes(ini_get("upload_max_filesize")).' byte(s)');
				}
				
				
				
				if (isset($post['CONF_DATE_FORMAT_PHP']))
				$post['CONF_DATE_FORMAT_PHP'] = $this->arr_date_format_php[$post['CONF_DATE_FORMAT_PHP']];
				if ($post["mode"]=="options") {
					$post = array_merge(array(
										'CONF_ADMIN_APPROVAL_REGISTRATION'=>0,
										'CONF_EMAIL_VERIFICATION_REGISTRATION'=>0,
										'CONF_AUTO_LOGIN_REGISTRATION'=>0,
										'CONF_NOTIFY_ADMIN_REGISTRATION'=>0,
										'CONF_WELCOME_EMAIL_REGISTRATION'=>0,
										'CONF_AUTO_LOGOUT_PASSWORD_CHANGE'=>0,
										'CONF_ENABLE_FACEBOOK_LOGIN'=>0,
										'CONF_ENABLE_GOOGLEPLUS_LOGIN'=>0,
										'CONF_ACTIVATE_SEPARATE_SIGNUP_FORM'=>0,
										'CONF_ADMIN_APPROVAL_SUPPLIER_REGISTRATION'=>0,
										'CONF_BUYER_CAN_SEE_SELLER_TAB'=>0,
										'CONF_ALLOW_USED_PRODUCTS_LISTING'=>0,
										'CONF_ENABLE_BUYING_OWN_PRODUCTS'=>0,
										'CONF_NEW_SUBSCRIPTION_EMAIL'=>0,
										'CONF_ENABLE_SELLER_SUBSCRIPTION'=>0,
										'CONF_ENABLE_DIGITAL_PRODUCTS'=>0,
										'CONF_ENABLE_COD_PAYMENTS'=>0,
										'CONF_ENABLE_COD_SELLER_NOTIFICATION'=>0,
										
										'CONF_VENDOR_ORDER_STATUS'=>in_array('CONF_VENDOR_ORDER_STATUS',$post)?$post['CONF_VENDOR_ORDER_STATUS']:0,
										'CONF_BUYER_ORDER_STATUS'=>in_array('CONF_BUYER_ORDER_STATUS',$post)?$post['CONF_BUYER_ORDER_STATUS']:0,
										'CONF_PROCESSING_ORDER_STATUS'=>in_array('CONF_PROCESSING_ORDER_STATUS',$post)?$post['CONF_PROCESSING_ORDER_STATUS']:0,
										'CONF_COMPLETED_ORDER_STATUS'=>in_array('CONF_COMPLETED_ORDER_STATUS',$post)?$post['CONF_COMPLETED_ORDER_STATUS']:0,
										'CONF_DIGITAL_DOWNLOAD_STATUS'=>in_array('CONF_DIGITAL_DOWNLOAD_STATUS',$post)?$post['CONF_DIGITAL_DOWNLOAD_STATUS']:0,
										'CONF_REVIEW_READY_ORDER_STATUS'=>in_array('CONF_REVIEW_READY_ORDER_STATUS',$post)?$post['CONF_REVIEW_READY_ORDER_STATUS']:0,
										'CONF_ALLOW_CANCELLATION_ORDER_STATUS'=>in_array('CONF_ALLOW_CANCELLATION_ORDER_STATUS',$post)?$post['CONF_ALLOW_CANCELLATION_ORDER_STATUS']:0,
										'CONF_RETURN_EXCHANGE_READY_ORDER_STATUS'=>in_array('CONF_RETURN_EXCHANGE_READY_ORDER_STATUS',$post)?$post['CONF_RETURN_EXCHANGE_READY_ORDER_STATUS']:0,
										'CONF_SALES_ORDER_STATUS'=>in_array('CONF_SALES_ORDER_STATUS',$post)?$post['CONF_SALES_ORDER_STATUS']:0,
										'CONF_PURCHASE_ORDER_STATUS'=>in_array('CONF_PURCHASE_ORDER_STATUS',$post)?$post['CONF_PURCHASE_ORDER_STATUS']:0,
										),$post);
					}
					
				
				if ($post["CONF_SEND_EMAIL"] && $post["CONF_SEND_SMTP_EMAIL"] && ( ($post["CONF_SEND_SMTP_EMAIL"]!=Settings::getSetting("CONF_SEND_SMTP_EMAIL")) || ($post["CONF_SMTP_HOST"]!=Settings::getSetting("CONF_SMTP_HOST")) || ($post["CONF_SMTP_PORT"]!=Settings::getSetting("CONF_SMTP_PORT")) || ($post["CONF_SMTP_USERNAME"]!=Settings::getSetting("CONF_SMTP_USERNAME")) || ($post["CONF_SMTP_SECURE"]!=Settings::getSetting("CONF_SMTP_SECURE")) || ($post["CONF_SMTP_PASSWORD"]!=Settings::getSetting("CONF_SMTP_PASSWORD")))){
					
						$smtp_arr=array("host"=>$post["CONF_SMTP_HOST"],"port"=>$post["CONF_SMTP_PORT"],"username"=>$post["CONF_SMTP_USERNAME"],"password"=>$post["CONF_SMTP_PASSWORD"],"secure"=>$post["CONF_SMTP_SECURE"]);
							if($settingsObj->send_smtp_test_email($smtp_arr)){
								Message::addMessage('We have sent a test email to administrator account i.e. <em>'.Settings::getSetting("CONF_ADMIN_EMAIL").'</em> Please make sure that you\'ve received that email message. If you have not received that message, the system is unable to deliver and emails and you need to contact developer.');
							}else{
								Message::addErrorMessage("Error: SMTP settings provided is invalid or unable to send email so we have not saved SMTP settings.<br/>".$settingsObj->getError());
								unset($post["CONF_SEND_SMTP_EMAIL"]);
								foreach($smtp_arr as $skey => $sval){
									unset($post['CONF_SMTP_'.strtoupper($skey)]);
								}
							}
				}
				
				if($settingsObj->update($post)){
					Message::addMessage('Success: Settings updated successfully.');
					Utilities::redirectUser();
				}else{
					Message::addErrorMessage($settingsObj->getError());
				}
			}
		}	
        $this->set('tabSelected',$type);
		$this->set('frmConf', $frm);
		$this->_template->render();
	}
		
	protected function getForm($type="general"){
		global $db;
		global $binary_status,$conf_length_class,$conf_weight_class,$review_status;
		$osObj=new Orderstatus();
		$frm = new Form('frmConfigurations','frmConfigurations');
		$frm->addHiddenField('', 'mode',$type);
		switch($type)
		{
			case 'general':
			$frm->addRequiredField('Site Name:', 'CONF_WEBSITE_NAME', Settings::getSetting("CONF_WEBSITE_NAME"), '', 'class="medium"');
			$frm->addRequiredField('Site Owner:', 'CONF_SITE_OWNER', Settings::getSetting("CONF_SITE_OWNER"), '', 'class="medium"');
			$fld=$frm->addRequiredField('Store Owner Email:', 'CONF_ADMIN_EMAIL', Settings::getSetting("CONF_ADMIN_EMAIL"), '', 'class="medium"');
			$fld->requirements()->setEmail();
			$frm->addTextArea('ADDRESS:', 'CONF_ADDRESS',Settings::getSetting("CONF_ADDRESS") , '', ' class="medium" rows="5"');
			$frm->addTextBox('Telephone:', 'CONF_SITE_PHONE', Settings::getSetting("CONF_SITE_PHONE"), '', 'class="small"');
			$frm->addTextBox('Fax:', 'CONF_SITE_FAX', Settings::getSetting("CONF_SITE_FAX"), '', 'class="small"');
			$fld=$frm->addFileUpload('Admin Logo:', 'CONF_ADMIN_LOGO', 'CONF_ADMIN_LOGO', '');
			$admin_logo=Settings::getSetting("CONF_ADMIN_LOGO");
			$fld->html_before_field='<div class="filefield"><span class="filename"></span>';
			if (!empty($admin_logo)){
            $fld->html_after_field='<label class="filelabel">Browse File</label></div><br/><br/>
			<div style="background-color:#ccc; padding:5px;"><img src="'.Utilities::generateUrl('image', 'site_admin_logo',array('THUMB'), CONF_WEBROOT_URL).'" /> </div><br/><br/>Preferred dimensions 172 X 55';
			}else{
				 $fld->html_after_field='<label class="filelabel">Browse File</label></div><br/>Preferred dimensions 172 X 55';
			}
			
			$fld=$frm->addFileUpload('Desktop Logo:', 'CONF_FRONT_LOGO', 'CONF_FRONT_LOGO', '');
			$front_logo=Settings::getSetting("CONF_FRONT_LOGO");
			$fld->html_before_field='<div class="filefield"><span class="filename"></span>';
			if (!empty($front_logo)){
            $fld->html_after_field='<label class="filelabel">Browse File</label></div><br/><br/><img src="'.Utilities::generateUrl('image', 'site_logo',array(Settings::getSetting("CONF_FRONT_LOGO"),'THUMB'), CONF_WEBROOT_URL).'" /> <br/><br/>Preferred dimensions 172 X 55';
			}else{
				 $fld->html_after_field='<label class="filelabel">Browse File</label></div><br/>Preferred dimensions 172 X 55';
			}
			
			$fld=$frm->addFileUpload('Email Template Logo:', 'CONF_EMAIL_LOGO', 'CONF_EMAIL_LOGO', '');
			$email_logo=Settings::getSetting("CONF_EMAIL_LOGO");
			$fld->html_before_field='<div class="filefield"><span class="filename"></span>';
			if (!empty($email_logo)){
            $fld->html_after_field='<label class="filelabel">Browse File</label></div><br/><br/><img src="'.Utilities::generateUrl('image', 'site_email_logo',array(Settings::getSetting("CONF_EMAIL_LOGO")), CONF_WEBROOT_URL).'" />';
			}else{
				 $fld->html_after_field='<label class="filelabel">Browse File</label></div>';
			}
			
			$fld=$frm->addFileUpload('Mobile Logo/Icon:', 'CONF_FRONT_MOBILE_LOGO_ICON', 'CONF_FRONT_MOBILE_LOGO_ICON', '');
			$mobile_logo_icon=Settings::getSetting("CONF_FRONT_MOBILE_LOGO_ICON");
			$fld->html_before_field='<div class="filefield"><span class="filename"></span>';
			if (!empty($mobile_logo_icon)){
            $fld->html_after_field='<label class="filelabel">Browse File</label></div><br/><br/><img src="'.Utilities::generateUrl('image', 'mobile_icon_logo',array(Settings::getSetting("CONF_FRONT_MOBILE_LOGO_ICON")), CONF_WEBROOT_URL).'" /><br/><a href="'.Utilities::generateUrl('configurations', 'remove_mobile_icon').'">Remove Graphic Icon</a><br/><br/>Preferred dimensions 60 X 60';
			}else{
				 $fld->html_after_field='<label class="filelabel">Browse File</label></div><br/>Preferred dimensions 60 X 60';
			}
			
			
			$fld=$frm->addFileUpload('Website Favicon:', 'CONF_FAVICON', 'CONF_FAVICON', '');
			$favicon=Settings::getSetting("CONF_FAVICON");
			$fld->html_before_field='<div class="filefield"><span class="filename"></span>';
			if (!empty($favicon)){
            $fld->html_after_field='<label class="filelabel">Browse File</label></div><br/><br/><img src="'.Utilities::generateUrl('image', 'site_favicon',array(Settings::getSetting("CONF_FAVICON")), CONF_WEBROOT_URL).'" /><br/><a href="'.Utilities::generateUrl('configurations', 'remove_favicon').'">Remove Favicon</a>';
			}else{
				$fld->html_after_field='<label class="filelabel">Browse File</label></div>';
			}
			
			$fld=$frm->addFileUpload('Apple Touch Icon:', 'CONF_APPLE_TOUCH_ICON', 'CONF_APPLE_TOUCH_ICON', '');
			$apple_touch_icon=Settings::getSetting("CONF_APPLE_TOUCH_ICON");
			$fld->html_before_field='<div class="filefield"><span class="filename"></span>';
			if (!empty($apple_touch_icon)){
            $fld->html_after_field='<label class="filelabel">Browse File</label></div><br/><br/><img src="'.Utilities::generateUrl('image', 'apple_touch_icon',array(Settings::getSetting("CONF_APPLE_TOUCH_ICON")), CONF_WEBROOT_URL).'" /><br/><a href="'.Utilities::generateUrl('configurations', 'remove_apple_touch_icon').'">Remove Apple Touch Icon</a>';
			}else{
				$fld->html_after_field='<label class="filelabel">Browse File</label></div>';
			}
			
			$fld=$frm->addFileUpload('Footer Logo/Graphic:', 'CONF_FOOTER_LOGO_GRAPHIC', 'CONF_FOOTER_LOGO_GRAPHIC', '');
			$footer_logo_graphic=Settings::getSetting("CONF_FOOTER_LOGO_GRAPHIC");
			$fld->html_before_field='<div class="filefield"><span class="filename"></span>';
			if (!empty($footer_logo_graphic)){
            $fld->html_after_field='<label class="filelabel">Browse File</label></div><br/><br/><img src="'.Utilities::generateUrl('image', 'footer_logo_graphic',array(Settings::getSetting("CONF_FOOTER_LOGO_GRAPHIC")), CONF_WEBROOT_URL).'" /><br/><a href="'.Utilities::generateUrl('configurations', 'remove_footer_graphic').'">Remove Graphic</a><br/><br/>Preferred graphic/logo dimensions 268 X 82';
			}else{
				 $fld->html_after_field='<label class="filelabel">Browse File</label></div><br/>Preferred graphic/logo dimensions 268 X 82';
			}
			
			$fld=$frm->addFileUpload('Watermark Image:', 'CONF_WATERMARK_IMAGE', 'CONF_WATERMARK_IMAGE', '');
			$watermark_image=Settings::getSetting("CONF_WATERMARK_IMAGE");
			$fld->html_before_field='<div class="filefield"><span class="filename"></span>';
			if (!empty($watermark_image)){
            $fld->html_after_field='<label class="filelabel">Browse File</label></div><br/><br/><img src="'.Utilities::generateUrl('image', 'watermark',array(Settings::getSetting("CONF_WATERMARK_IMAGE")), CONF_WEBROOT_URL).'" /><br/><a href="'.Utilities::generateUrl('configurations', 'remove_watermark').'">Remove Watermark</a><br/><br/>Supports only gif, jpg & png file.';
			}else{
				$fld->html_after_field='<label class="filelabel">Browse File</label></div><br/>Supports only gif, jpg & png file.';
			}
		break;	
	case 'local':
			//die(date('Y-m-d H:i:s'));
			$cnObj=new Countries();
			$arr=DateTimeZone::listIdentifiers();
            $arr=array_combine($arr, $arr);
			$frm->addSelectBox('Language:', 'CONF_LANGUAGE', $this->arr_languages, Settings::getSetting("CONF_LANGUAGE"), 'class="medium"', '');
            $fld=$frm->addSelectBox('Timezone:', 'CONF_TIMEZONE', $arr, Settings::getSetting("CONF_TIMEZONE"), 'class="medium"', '');
            $fld->html_after_field='<div class="input-tip">Now according to ' . Settings::getSetting("CONF_TIMEZONE") . ' = ' . displayDate(date('Y-m-d H:i:s'), true, true, CONF_TIMEZONE) . '</div>';
			$frm->addSelectBox('Country:', 'CONF_COUNTRY',$cnObj->getAssociativeArray(),Settings::getSetting("CONF_COUNTRY"),'class="medium"')->requirements()->setRequired();
			$frm->addSelectBox('Date Format:', 'CONF_DATE_FORMAT_PHP', $this->arr_date_format_php, array_keys($this->arr_date_format_php, Settings::getSetting("CONF_DATE_FORMAT_PHP")), 'class="medium"', '')->requirements()->setRequired();
			
			$crObj=new Currency();
			$frm->addSelectBox('Currency:', 'CONF_CURRENCY',$crObj->getAssociativeArray(),Settings::getSetting("CONF_CURRENCY"),'class="medium"')->requirements()->setRequired();
			
			
			
			$frm->addTextArea('Allowed File Extensions', 'CONF_FILE_EXT_ALLOWED',Settings::getSetting("CONF_FILE_EXT_ALLOWED"), '', '  rows="5" class="large"');
			$frm->addTextArea('Allowed File Mime Types', 'CONF_FILE_MIME_ALLOWED',Settings::getSetting("CONF_FILE_MIME_ALLOWED"), '', '  rows="5" class="large"');
			
			$frm->addTextArea('Allowed Image Mime Types', 'CONF_IMAGE_MIME_ALLOWED',Settings::getSetting("CONF_IMAGE_MIME_ALLOWED"), '', '  rows="5" class="large"');
			
 		 break;
	case 'seo':
		$frm->addTextBox('Page Title:', 'CONF_PAGE_TITLE',Settings::getSetting("CONF_PAGE_TITLE"), '', 'class="medium"');
		$frm->addTextArea('Meta Keyword:', 'CONF_META_KEYWORD',Settings::getSetting("CONF_META_KEYWORD"), '', '  rows="5" class="large"');
		$frm->getField("CONF_META_KEYWORD")->html_after_field='<small>These are the keywords used for improving search engine results of our site. (Comma separated for multiple keywords.)</small>';
		$frm->addTextArea('Meta Description:', 'CONF_META_DESCRIPTION',Settings::getSetting("CONF_META_DESCRIPTION"), '', ' rows="5" class="large"');
		$frm->getField("CONF_META_DESCRIPTION")->html_after_field='<small>This is the short description of your site, used by search engines on search result pages to display preview snippets for a given page.</small>';
		
		$frm->addTextBox('Twitter Username:', 'CONF_TWITTER_USERNAME',Settings::getSetting("CONF_TWITTER_USERNAME"), '', 'class="medium"');
		$frm->getField("CONF_TWITTER_USERNAME")->html_after_field='<small>This is required for Twitter Card code SEO Update.</small>';
		
		$frm->addTextArea('Site Tracker Code:', 'CONF_SITE_TRACKER_CODE',Settings::getSetting("CONF_SITE_TRACKER_CODE"), '', ' rows="5" class="large"');
		$frm->getField("CONF_SITE_TRACKER_CODE")->html_after_field='<small>This is the site tracker script, used to track and analyze data about how people are getting to your website. e.g., Google Analytics. http://www.google.com/analytics/</small>';
	break;
	case 'options':
		$frm->addHtml('<h5><strong>Products</strong></h5>', 'htmlNote','');
		
		$fldMaxEnt=$frm->addrequiredField('Product\'s Minimum Price ['.CONF_CURRENCY_SYMBOL.']', 'CONF_MIN_PRODUCT_PRICE', Settings::getSetting("CONF_MIN_PRODUCT_PRICE"), '', 'class="small"');
		$fldMaxEnt->requirements()->setFloatPositive();
		$fldMaxEnt->html_after_field='<small>This is Product\'s Minumum Price allowed for listing.</small>';
		
		/*$frm->addRadioButtons('Display Prices inclusive of Tax:', 'CONF_DISP_INC_TAX',$binary_status,Settings::getSetting("CONF_DISP_INC_TAX"),2,'width="15%" ');*/
		
		$frm->addRadioButtons('Product\'s Meta Title Mandatory:', 'CONF_PRODUCT_META_TITLE_MANDATORY',$binary_status,Settings::getSetting("CONF_PRODUCT_META_TITLE_MANDATORY"),2,'width="15%" ');
		$frm->getField("CONF_PRODUCT_META_TITLE_MANDATORY")->html_after_field='<small>This will make Product\'s meta title mandatory.</small>';
		
		$frm->addRadioButtons('Product\'s Model Mandatory:', 'CONF_PRODUCT_MODEL_MANDATORY',$binary_status,Settings::getSetting("CONF_PRODUCT_MODEL_MANDATORY"),2,'width="15%" ');
		$frm->getField("CONF_PRODUCT_MODEL_MANDATORY")->html_after_field='<small>This will make Product\'s model mandatory.</small>';
		
		$frm->addRadioButtons('Product\'s SKU Mandatory:', 'CONF_PRODUCT_SKU_MANDATORY',$binary_status,Settings::getSetting("CONF_PRODUCT_SKU_MANDATORY"),2,'width="15%" ');
		$frm->getField("CONF_PRODUCT_SKU_MANDATORY")->html_after_field='<small>This will make Product\'s SKU mandatory.</small>';
	
		$frm->addRequiredField('Default Items Per Page (Catalog):', 'CONF_DEF_ITEMS_PER_PAGE_CATALOG',Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_CATALOG"), '', 'class="small"');
		$frm->getField("CONF_DEF_ITEMS_PER_PAGE_CATALOG")->html_after_field='<small>Determines how many catalog items are shown per page (products, categories, etc)</small>';
		
		$frm->addRequiredField('Featured Products (Home Page):', 'CONF_FEATURED_ITEMS_HOME_PAGE',Settings::getSetting("CONF_FEATURED_ITEMS_HOME_PAGE"), '', 'class="small"');
		$frm->getField("CONF_FEATURED_ITEMS_HOME_PAGE")->html_after_field='<small>Determines how many featured products are shown on home page. Keep it to zero (0) to hide.</small>';
		
		$frm->addRequiredField('Default Items Per Page (Admin):', 'CONF_DEF_ITEMS_PER_PAGE_ADMIN',Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN"), '', 'class="small"');
		$frm->getField("CONF_DEF_ITEMS_PER_PAGE_ADMIN")->html_after_field='<small>Determines how many admin items are shown per page (orders, customers, etc)</small>';
		
		$fldEV=$frm->addCheckBox('Enable Used/Refurbished Items Listing:', 'CONF_ALLOW_USED_PRODUCTS_LISTING','1','CONF_ALLOW_USED_PRODUCTS_LISTING', 'class="field4"',Settings::getSetting("CONF_ALLOW_USED_PRODUCTS_LISTING"));
		$fldEV->html_after_field='<small>On enabling this feature, seller will be able to add listing for used/refurbished items as well.</small>';
		$fldALA=$frm->addCheckBox('Enable Buying from own Store:', 'CONF_ENABLE_BUYING_OWN_PRODUCTS','1','CONF_ENABLE_BUYING_OWN_PRODUCTS', 'class="field4"',Settings::getSetting("CONF_ENABLE_BUYING_OWN_PRODUCTS"));
		$fldALA->html_after_field='<small>On enabling this feature, user will be able to buy products from his own store.</small>';
		
		$frm->addRequiredField('Add On Items:', 'CONF_MAX_NUMBER_PRODUCT_ADDONS',Settings::getSetting("CONF_MAX_NUMBER_PRODUCT_ADDONS"), '', 'class="small"');
		$frm->getField("CONF_MAX_NUMBER_PRODUCT_ADDONS")->html_after_field='<small>Determines how many add-ons items can be defined for a product.</small>';
		
		$frm->addHtml('<h5><strong>Digital Products</strong></h5>', 'htmlNote','');
		$fldALA=$frm->addCheckBox('Enable Digital Products:', 'CONF_ENABLE_DIGITAL_PRODUCTS','1','CONF_ENABLE_DIGITAL_PRODUCTS', 'class="field4"',Settings::getSetting("CONF_ENABLE_DIGITAL_PRODUCTS"));
		$fldALA->html_after_field='<small>On enabling this feature, seller will be able to list downloadable products in his store.</small>';
		$frm->addTextArea('Allowed File Extensions', 'CONF_DIGITAL_FILE_EXT_ALLOWED',Settings::getSetting("CONF_DIGITAL_FILE_EXT_ALLOWED"), '', '  rows="5" class="large"');
		$frm->addRequiredField('Max File Size:', 'CONF_DIGITAL_MAX_FILE_SIZE',Settings::getSetting("CONF_DIGITAL_MAX_FILE_SIZE"), '', 'class="small"');
		$frm->getField("CONF_DIGITAL_MAX_FILE_SIZE")->html_after_field='<small>The maximum file size you can upload. Enter as byte. Maximim '.$this->return_bytes(ini_get("upload_max_filesize")).' byte(s) allowed as per your hosting/server settings.</small>';
		
		$fld=$frm->addCheckBoxes('Enable Digital Downloads', 'CONF_DIGITAL_DOWNLOAD_STATUS',$osObj->getAssociativeArray() ,Settings::getSetting("CONF_DIGITAL_DOWNLOAD_STATUS"),'5', 'class=""');
		$fld->html_after_field='<small>Set the order status the customer\'s order must reach before they are allowed to access their downloadable products.</small>';
		
		$frm->addHtml('<h5><strong>COD Payments</strong></h5>', 'htmlNote','');
		$fldALA=$frm->addCheckBox('Enable COD:', 'CONF_ENABLE_COD_PAYMENTS','1','CONF_ENABLE_COD_PAYMENTS', 'class="field4"',Settings::getSetting("CONF_ENABLE_COD_PAYMENTS"));
		
		$fldMW=$frm->addTextBox('Minimum COD Order Total:', 'CONF_MIN_COD_ORDER_LIMIT', Settings::getSetting("CONF_MIN_COD_ORDER_LIMIT"), '', 'class="small"');
		$fldMW->requirements()->setFloatPositive();
		$fldMW->html_after_field='&nbsp;<strong>'.CONF_CURRENCY_SYMBOL.'</strong> <small>This is the minimum cash on delivery order total, eligible for COD payments.</small>';
		
		$fldMW=$frm->addTextBox('Maximum COD Order Total:', 'CONF_MAX_COD_ORDER_LIMIT', Settings::getSetting("CONF_MAX_COD_ORDER_LIMIT"), '', 'class="small"');
		$fldMW->requirements()->setFloatPositive();
		$fldMW->html_after_field='&nbsp;<strong>'.CONF_CURRENCY_SYMBOL.'</strong> <small>This is the maximum cash on delivery order total, eligible for COD payments.</small>';
		
		$fldMW=$frm->addTextBox('Minimum Wallet Balance:', 'CONF_COD_MIN_WALLET_BALANCE', Settings::getSetting("CONF_COD_MIN_WALLET_BALANCE"), '', 'class="small"');
		$fldMW->requirements()->setFloatPositive();
		$fldMW->html_after_field='&nbsp;<strong>'.CONF_CURRENCY_SYMBOL.'</strong> <small>This is the minimum wallet balance, seller needs to maintain to accept COD orders.</small>';
		
		$fldALA=$frm->addCheckBox('Notify Seller:', 'CONF_ENABLE_COD_SELLER_NOTIFICATION','1','CONF_ENABLE_COD_SELLER_NOTIFICATION', 'class="field4"',Settings::getSetting("CONF_ENABLE_COD_SELLER_NOTIFICATION"));
		$fldALA->html_after_field='&nbsp;<small>If enabled, this will keep seller informed if balance goes below Minimum wallet balance required to accept COD orders.</small>';
		
		$pmObj = new PaymentMethods();
		$frm->addSelectBox('Payment Method', 'CONF_COD_PAYMENT_METHOD', $pmObj->getAssociativeArray(), Settings::getSetting("CONF_COD_PAYMENT_METHOD"), 'class="small"','')->requirements()->setRequired();
		$frm->getField("CONF_COD_PAYMENT_METHOD")->html_after_field='<small>Select the Payment Method to be considered as COD (cash on delivery).</small>';
		
		$frm->addSelectBox('Default COD Order Status', 'CONF_DEFAULT_COD_ORDER_STATUS', $osObj->getAssociativeArray(), Settings::getSetting("CONF_DEFAULT_COD_ORDER_STATUS"), 'class="small"','')->requirements()->setRequired();
		$frm->getField("CONF_DEFAULT_COD_ORDER_STATUS")->html_after_field='<small>Set the default child order status when an order is placed with COD Payment Method.</small>';
		
		
		$frm->addHtml('<h5><strong>Recommended Items</strong></h5>', 'htmlNote','');
		
		$frm->addRequiredField('Recommended Items (Home Page):', 'CONF_RECOMMENDED_ITEMS_HOME_PAGE',Settings::getSetting("CONF_RECOMMENDED_ITEMS_HOME_PAGE"), '', 'class="small"');
		$frm->getField("CONF_RECOMMENDED_ITEMS_HOME_PAGE")->html_after_field='<small>Determines how many recommended items are shown on home page</small>';
		
		$frm->addRequiredField('Recommended Items (Product Page):', 'CONF_RECOMMENDED_ITEMS_PRODUCT_PAGE',Settings::getSetting("CONF_RECOMMENDED_ITEMS_PRODUCT_PAGE"), '', 'class="small"');
		$frm->getField("CONF_RECOMMENDED_ITEMS_PRODUCT_PAGE")->html_after_field='<small>Determines how many recommended items are shown on product page</small>';
		
		$frm->addHtml('<h5><strong>Customer Bought Items</strong></h5>', 'htmlNote','');
		
		$frm->addRequiredField('Customer Bought Items (Product Page):', 'CONF_CUSTOMER_BOUGHT_ITEMS_PRODUCT_PAGE',Settings::getSetting("CONF_CUSTOMER_BOUGHT_ITEMS_PRODUCT_PAGE"), '', 'class="small"');
		$frm->getField("CONF_CUSTOMER_BOUGHT_ITEMS_PRODUCT_PAGE")->html_after_field='<small>Determines how many customer bought items are shown on product page</small>';
		
		$frm->addRequiredField('Customer Bought Items (Cart Page):', 'CONF_CUSTOMER_BOUGHT_ITEMS_CART_PAGE',Settings::getSetting("CONF_CUSTOMER_BOUGHT_ITEMS_CART_PAGE"), '', 'class="small"');
		$frm->getField("CONF_CUSTOMER_BOUGHT_ITEMS_CART_PAGE")->html_after_field='<small>Determines how many customer bought items are shown on cart page</small>';
		
		
		
		$frm->addHtml('<h5><strong>Reviews</strong></h5>', 'htmlNote','');
		$frm->addSelectBox('Default Review Status', 'CONF_DEFAULT_REVIEW_STATUS', $review_status, Settings::getSetting("CONF_DEFAULT_REVIEW_STATUS"), 'class="small"', 'Select');
		$frm->getField("CONF_DEFAULT_REVIEW_STATUS")->html_after_field='<small>Set the default review order status when a new review is placed.</small>';
		
		$frm->addRadioButtons('Allow Reviews:', 'CONF_ALLOW_REVIEWS',$binary_status,Settings::getSetting("CONF_ALLOW_REVIEWS"),2,'width="15%" ');
		//$frm->addRadioButtons('Allow Guest Reviews:', 'CONF_ALLOW_GUEST_REVIEWS',$binary_status,Settings::getSetting("CONF_ALLOW_GUEST_REVIEWS"),2,'width="15%" ');
		$frm->addRadioButtons('New Review Alert Email:', 'CONF_REVIEW_ALERT_EMAIL',$binary_status,Settings::getSetting("CONF_REVIEW_ALERT_EMAIL"),2,'width="15%" ');
		
		$frm->addHtml('<h5><strong>Tax</strong></h5>', 'htmlNote','');
		$fldMW=$frm->addTextBox('Global Tax/VAT:', 'CONF_SITE_TAX', Settings::getSetting("CONF_SITE_TAX"), '', 'class="small"');
		$fldMW->requirements()->setFloatPositive();
		$fldMW->html_after_field='&nbsp;<strong>%</strong> <small>Global Tax/VAT applicable on products.</small>';
		
		
		$frm->addHtml('<h5><strong>Commission</strong></h5>', 'htmlNote','');
		
		$fldMaxEnt=$frm->addrequiredField('Maximum Site Commission ['.CONF_CURRENCY_SYMBOL.']', 'CONF_MAX_COMMISSION', Settings::getSetting("CONF_MAX_COMMISSION"), '', 'class="small"');
		$fldMaxEnt->requirements()->setFloatPositive();
		$fldMaxEnt->html_after_field='<small>This is maximum commission/Fees that will be charged on a particular product.</small>';
		
		$frm->addHtml('<h5><strong>Withdrawal</strong></h5>', 'htmlNote','');
		$fldMW=$frm->addTextBox('Minimum Withdrawal Amount:', 'CONF_MIN_WITHDRAW_LIMIT', Settings::getSetting("CONF_MIN_WITHDRAW_LIMIT"), '', 'class="small"');
		$fldMW->requirements()->setFloatPositive();
		$fldMW->html_after_field='&nbsp;<small>This is the minimum withdrawable amount.</small>';
		
		$fldMWA=$frm->addTextBox('Minimum Interval:', 'CONF_MIN_INTERVAL_WITHDRAW_REQUESTS', Settings::getSetting("CONF_MIN_INTERVAL_WITHDRAW_REQUESTS"), '', 'class="small"');
		$fldMWA->requirements()->setIntPositive();
		$fldMWA->html_after_field='&nbsp;<strong>days</strong> <small>This is the minimum interval in days between two withdrawal requests.</small>';
		
		$frm->addHtml('<h5><strong>PPC</strong></h5>', 'htmlNote','');
		$fldMW=$frm->addTextBox('Minimum Wallet Balance:', 'CONF_MIN_WALLET_BALANCE', Settings::getSetting("CONF_MIN_WALLET_BALANCE"), '', 'class="small"');
		$fldMW->requirements()->setFloatPositive();
		$fldMW->html_after_field='&nbsp;<small>This is the minimum wallet balance to start promotion.</small>';
		
		$fld_wb=$frm->addTextBox('Wallet Balance Alert:', 'CONF_WALLET_BALANCE_ALERT', Settings::getSetting("CONF_WALLET_BALANCE_ALERT"), '', 'class="small"');
		$fld_wb->requirements()->setFloatPositive();
		$fld_wb->requirements()->setCompareWith('CONF_MIN_WALLET_BALANCE', 'gt','Wallet Balance Alert');
		$fld_wb->html_after_field='&nbsp;<small>Email notification will be sent to Sellers/Advertisers if wallet balance goes below value defined here.</small>';
		
		$fldMW=$frm->addTextBox('Cost Per Click (Product):', 'CONF_CPC_PRODUCT', Settings::getSetting("CONF_CPC_PRODUCT"), '', 'class="small"');
		$fldMW->requirements()->setFloatPositive();
		$fldMW->html_after_field='&nbsp;<small>This is the cost per click for Product.</small>';
		
		$fldMW=$frm->addTextBox('Cost Per Click (Shop):', 'CONF_CPC_SHOP', Settings::getSetting("CONF_CPC_SHOP"), '', 'class="small"');
		$fldMW->requirements()->setFloatPositive();
		$fldMW->html_after_field='&nbsp;<small>This is the cost per click for Shop.</small>';
		
		$fldMW=$frm->addTextBox('Cost Per Click (Banner):', 'CONF_CPC_BANNER', Settings::getSetting("CONF_CPC_BANNER"), '', 'class="small"');
		$fldMW->requirements()->setFloatPositive();
		$fldMW->html_after_field='&nbsp;<small>This is the cost per click for Banner.</small>';
		
		$frm->addTextBox('PPC Products (Home Page) Caption:', 'CONF_PPC_PRODUCTS_HOME_PAGE_CAPTION',Settings::getSetting("CONF_PPC_PRODUCTS_HOME_PAGE_CAPTION"), '', 'class="small"');
		
		$frm->addRequiredField('PPC Products (Home Page):', 'CONF_PPC_PRODUCTS_HOME_PAGE',Settings::getSetting("CONF_PPC_PRODUCTS_HOME_PAGE"), '', 'class="small"');
		$frm->getField("CONF_PPC_PRODUCTS_HOME_PAGE")->html_after_field='<small>Determines how many PPC products are shown on home page. Keep it to zero (0) to hide.</small>';
		
		$frm->addTextBox('PPC Shops (Home Page) Caption:', 'CONF_PPC_SHOPS_HOME_PAGE_CAPTION',Settings::getSetting("CONF_PPC_SHOPS_HOME_PAGE_CAPTION"), '', 'class="small"');
		
		$frm->addRequiredField('PPC Shops (Home Page):', 'CONF_PPC_SHOPS_HOME_PAGE',Settings::getSetting("CONF_PPC_SHOPS_HOME_PAGE"), '', 'class="small"');
		$frm->getField("CONF_PPC_SHOPS_HOME_PAGE")->html_after_field='<small>Determines how many PPC shops are shown on home page. Keep it to zero (0) to hide.</small>';
	
		$frm->addHtml('<h5><strong>Account</strong></h5>', 'htmlNote','');
		$fldAr=$frm->addCheckBox('Enable Administrator Approval [Signup] After Registration:', 'CONF_ADMIN_APPROVAL_REGISTRATION','1','CONF_ADMIN_APPROVAL_REGISTRATION', 'class="field4"',Settings::getSetting("CONF_ADMIN_APPROVAL_REGISTRATION"));
		$fldAr->html_after_field='<small>On enabling this feature, admin need to approve each user (buyer, seller & advertiser) after registration (User cannot login until admin approves)</small>';
		
		$fldEV=$frm->addCheckBox('Enable Email Verification After Registration:', 'CONF_EMAIL_VERIFICATION_REGISTRATION','1','CONF_EMAIL_VERIFICATION_REGISTRATION', 'class="field4"',Settings::getSetting("CONF_EMAIL_VERIFICATION_REGISTRATION"));
		$fldEV->html_after_field='<small>On enabling this feature, user (buyer, seller & advertiser) need to verify their email address provided during registration. (User cannot login until email address is verified)</small>';
		
		$fldAL=$frm->addCheckBox('Enable Auto Login After Registration:', 'CONF_AUTO_LOGIN_REGISTRATION','1','CONF_AUTO_LOGIN_REGISTRATION', 'class="field4"',Settings::getSetting("CONF_AUTO_LOGIN_REGISTRATION"));
		$fldAL->html_after_field='<small>On enabling this feature, users (buyer, seller & advertiser) will be automatically logged-in after registration. (Only when "Email Verification" & "Admin Approval" is disabled)</small>';
		
		$fldNA=$frm->addCheckBox('Enable Notify Administrator on Each Registration:', 'CONF_NOTIFY_ADMIN_REGISTRATION','1','CONF_NOTIFY_ADMIN_REGISTRATION', 'class="field4"',Settings::getSetting("CONF_NOTIFY_ADMIN_REGISTRATION"));
		$fldNA->html_after_field='<small>On enabling this feature, notification mail will be sent to administrator on each registration.</small>';
		
		$fldSW=$frm->addCheckBox('Enable Sending Welcome Mail After Registration:', 'CONF_WELCOME_EMAIL_REGISTRATION','1','CONF_WELCOME_EMAIL_REGISTRATION', 'class="field4"',Settings::getSetting("CONF_WELCOME_EMAIL_REGISTRATION"));
		$fldSW->html_after_field='<small>On enabling this feature, users (buyer, seller & advertiser) will receive a welcome mail after registration.</small>';
		
		$fldALA=$frm->addCheckBox('Enable Auto-Logout After Password Change:', 'CONF_AUTO_LOGOUT_PASSWORD_CHANGE','1','CONF_AUTO_LOGOUT_PASSWORD_CHANGE', 'class="field4"',Settings::getSetting("CONF_AUTO_LOGOUT_PASSWORD_CHANGE"));
		$fldALA->html_after_field='<small>On enabling this feature, users (buyer, seller & advertiser) will be asked to log-in again.</small>';
		
			
		$fldALA=$frm->addCheckBox('Activate Separate Seller Sign Up Form:', 'CONF_ACTIVATE_SEPARATE_SIGNUP_FORM','1','CONF_ACTIVATE_SEPARATE_SIGNUP_FORM', 'class="field4"',Settings::getSetting("CONF_ACTIVATE_SEPARATE_SIGNUP_FORM"));
		$fldALA->html_after_field='<small>On enabling this feature, buyers and seller will have a separate sign up form.</small>';
		
		$fldALA=$frm->addCheckBox('Enable Administrator Approval On Seller Request:', 'CONF_ADMIN_APPROVAL_SUPPLIER_REGISTRATION','1','CONF_ADMIN_APPROVAL_SUPPLIER_REGISTRATION', 'class="field4"',Settings::getSetting("CONF_ADMIN_APPROVAL_SUPPLIER_REGISTRATION"));
		$fldALA->html_after_field='<small>On enabling this feature, admin need to approve Seller\'s request after registration (Seller rights will not be accessible until admin approves, only when "Activate Separate Seller Sign Up Form" is enabled)</small>';
		
		$fldALA=$frm->addCheckBox('Buyers can see Seller Tab:', 'CONF_BUYER_CAN_SEE_SELLER_TAB','1','CONF_BUYER_CAN_SEE_SELLER_TAB', 'class="field4"',Settings::getSetting("CONF_BUYER_CAN_SEE_SELLER_TAB"));
		$fldALA->html_after_field='<small>On enabling this feature, buyers will be able to see Seller tab.(only when "Activate Separate Seller Sign Up Form" is enabled)</small>';
		
		
		
		
		$fldALA=$frm->addCheckBox('Enable Facebook Login:', 'CONF_ENABLE_FACEBOOK_LOGIN','1','CONF_ENABLE_FACEBOOK_LOGIN', 'class="field4"',Settings::getSetting("CONF_ENABLE_FACEBOOK_LOGIN"));
		$fldALA->html_after_field='<small>On enabling this feature, users (buyer, seller & advertiser) will be able to login using facebook account. Please define settings for facebook login if enabled under "Third Party APIs" Tab.</small>';
		
		$fldALA=$frm->addCheckBox('Enable Google Plus Login:', 'CONF_ENABLE_GOOGLEPLUS_LOGIN','1','CONF_ENABLE_GOOGLEPLUS_LOGIN', 'class="field4"',Settings::getSetting("CONF_ENABLE_GOOGLEPLUS_LOGIN"));
		$fldALA->html_after_field='<small>On enabling this feature, users (buyer, seller & advertiser) will be able to login using google plus account. Please define settings for facebook login if enabled under "Third Party APIs" Tab.</small>';
		
		
		$cmsObj=new Cms();
		$frm->addSelectBox('Account Terms', 'CONF_ACCOUNT_TERMS',$cmsObj->getAssociativeArray(),Settings::getSetting("CONF_ACCOUNT_TERMS"),'class="small"','');
		$frm->getField("CONF_ACCOUNT_TERMS")->html_after_field='<small>Forces people to agree to terms before an account can be created.</small>';
		/*$frm->addSelectBox('Sell on '.Settings::getSetting("CONF_WEBSITE_NAME").' Page', 'CONF_SELL_SITENAME_PAGE',$cmsObj->getAssociativeArray(),Settings::getSetting("CONF_SELL_SITENAME_PAGE"),'class="small"','');*/
		$frm->addRequiredField('Sell on '.Settings::getSetting("CONF_WEBSITE_NAME").' Page', 'CONF_SELL_SITENAME_PAGE',Settings::getSetting("CONF_SELL_SITENAME_PAGE"),'class="small"','');
		$frm->getField("CONF_SELL_SITENAME_PAGE")->html_after_field='<small>Visitors can views "Sell on '.Settings::getSetting("CONF_WEBSITE_NAME").'" related terms & information.</small>';
		
		$fldML=$frm->addTextBox('Max Login Attempts', 'CONF_MAX_LOGIN_ATTEMPTS', Settings::getSetting("CONF_MAX_LOGIN_ATTEMPTS"), '', 'class="small"');
		$fldML->requirements()->setIntPositive();
		$fldML->html_after_field='&nbsp;<small>Maximum login attempts allowed before the account is locked for 1 hour.</small>';
		
		$frm->addHtml('<h5><strong>Subscription</strong></h5>', 'htmlNote','');
		
		$fldEV=$frm->addCheckBox('Enable Subscription Module for Sellers:', 'CONF_ENABLE_SELLER_SUBSCRIPTION','1','CONF_ENABLE_SELLER_SUBSCRIPTION', 'class="field4"',Settings::getSetting("CONF_ENABLE_SELLER_SUBSCRIPTION"));
		$fldEV->html_after_field='<small>On enabling this feature, sellers with active subscription packages will be able to list their products on this system.</small>';
		$frm->addRadioButtons('New Subscription Alert Email:', 'CONF_NEW_SUBSCRIPTION_EMAIL',$binary_status,Settings::getSetting("CONF_NEW_SUBSCRIPTION_EMAIL"),2,'width="15%" ');
		$frm->getField("CONF_NEW_SUBSCRIPTION_EMAIL")->html_after_field='<small>Send an email to store owner when new subscription is purchased.</small>';
		
		$frm->addRadioButtons('Trial Package Expiry Alert & Email:', 'CONF_SUBSCRIPTION_EXPIRY_EMAIL',$binary_status,Settings::getSetting("CONF_SUBSCRIPTION_EXPIRY_EMAIL"),2,'width="15%" ');
		$frm->getField("CONF_SUBSCRIPTION_EXPIRY_EMAIL")->html_after_field='<small>Send an email to subscriber when trial package is about to expire.</small>';
		
		$fldMWA=$frm->addTextBox('Activate Trial Package Expiry Alert & Email (Days):', 'CONF_SUBSCRIPTION_EXPIRY_EMAIL_DAYS', Settings::getSetting("CONF_SUBSCRIPTION_EXPIRY_EMAIL_DAYS"), '', 'class="small"');
		$fldMWA->requirements()->setIntPositive();
		$fldMWA->requirements()->setRange(1,7);
		$fldMWA->html_after_field='&nbsp;<strong>days</strong> <small>Trial Package Expiry Alert & Email will be activated, number of days specified here, before trial package is scheduled to expire.</small>';
		
		
		$packagesObj = new SubscriptionPackages();
		$package_order_status_arr = $packagesObj->getPackageOrderStatusAssoc();
		$fld=$frm->addSelectBox('Subscription Status (Pending)','CONF_PENDING_SUBSCRIPTION_STATUS',$package_order_status_arr,Settings::getSetting("CONF_PENDING_SUBSCRIPTION_STATUS"))->requirements()->setRequired();
		
		$frm->getField("CONF_PENDING_SUBSCRIPTION_STATUS")->html_after_field='<small>Set the status when subscription is created. This is the default status assigned to subscription.</small>';
		
		$fld=$frm->addSelectBox('Subscription Status (Active)','CONF_ACTIVE_SUBSCRIPTION_STATUS',$package_order_status_arr,Settings::getSetting("CONF_ACTIVE_SUBSCRIPTION_STATUS"))->requirements()->setRequired();
		$frm->getField("CONF_ACTIVE_SUBSCRIPTION_STATUS")->html_after_field='<small>Set the status when subscription is paid. This status is considered as \'Active\' Subscription.</small>';
		
		$fld=$frm->addSelectBox('Subscription Status (Cancelled)','CONF_CANCELLED_SUBSCRIPTION_STATUS',$package_order_status_arr,Settings::getSetting("CONF_CANCELLED_SUBSCRIPTION_STATUS"))->requirements()->setRequired();
		$frm->getField("CONF_CANCELLED_SUBSCRIPTION_STATUS")->html_after_field='<small>Set the status when subscription is marked Cancelled.</small>';
		
		
		$frm->addHtml('<h5><strong>Affiliate Accounts</strong></h5>', 'htmlNote','');
		$frm->addRadioButtons('Requires Approval:', 'CONF_AFFILIATES_REQUIRES_APPROVAL',$binary_status,Settings::getSetting("CONF_AFFILIATES_REQUIRES_APPROVAL"),2,'width="15%" ');
		$frm->getField("CONF_AFFILIATES_REQUIRES_APPROVAL")->html_after_field='<small>Automatically approve any new affiliates who sign up.</small>';
		
		$fldML=$frm->addTextBox('Sign UP Commission ', 'CONF_AFFILIATE_SIGNUP_COMMISSION', Settings::getSetting("CONF_AFFILIATE_SIGNUP_COMMISSION"), '', 'class="small"');
		$fldML->requirements()->setFloatPositive();
		$fldML->html_after_field='&nbsp;<small>Affiliate will get commission when new registration is received through affiliate.</small>';
		
		$frm->addSelectBox('Affiliate Terms', 'CONF_AFFILIATES_TERMS',$cmsObj->getAssociativeArray(),Settings::getSetting("CONF_AFFILIATES_TERMS"),'class="small"','');
		$frm->getField("CONF_AFFILIATES_TERMS")->html_after_field='<small>Forces people to agree to terms before an affiliate account can be created.</small>';
		
		$frm->addRadioButtons('New Affiliate Alert Mail', 'CONF_AFFILIATES_ALERT_EMAIL',$binary_status,Settings::getSetting("CONF_AFFILIATES_ALERT_EMAIL"),2,'width="15%" ');
		$frm->getField("CONF_AFFILIATES_ALERT_EMAIL")->html_after_field='<small>Send an email to the store owner when a new affiliate is registered.</small>';
		
		
		
		$frm->addHtml('<h5><strong>Checkout</strong></h5>', 'htmlNote','');
		
		$frm->addRadioButtons('New Order Alert Email:', 'CONF_NEW_ORDER_EMAIL',$binary_status,Settings::getSetting("CONF_NEW_ORDER_EMAIL"),2,'width="15%" ');
		$frm->getField("CONF_NEW_ORDER_EMAIL")->html_after_field='<small>Send an email to store owner when new order is placed.</small>';
		
		$frm->addRadioButtons('Order Cancellation/Refund in form of:', 'CONF_PROCESS_ORDER_REFUND_CANCELLATION',array("A_C"=>"Credits","R_P"=>"Reward Points"),Settings::getSetting("CONF_PROCESS_ORDER_REFUND_CANCELLATION"),2,'width="25%" ');
		$frm->getField("CONF_PROCESS_ORDER_REFUND_CANCELLATION")->html_after_field='<small>These both are equivalent and can be used at the time of checkout but reward points can\'t be withdrawn while credits can be withdrawn.</small>';
		
		$frm->addSelectBox('Default Child Order Status', 'CONF_DEFAULT_ORDER_STATUS', $osObj->getAssociativeArray(), Settings::getSetting("CONF_DEFAULT_ORDER_STATUS"), 'class="small"','')->requirements()->setRequired();
		$frm->getField("CONF_DEFAULT_ORDER_STATUS")->html_after_field='<small>Set the default child order status when an order is placed.</small>';
		
		$frm->addSelectBox('Default Paid Order Status', 'CONF_DEFAULT_PAID_ORDER_STATUS', $osObj->getAssociativeArray(), Settings::getSetting("CONF_DEFAULT_PAID_ORDER_STATUS"), 'class="small"','')->requirements()->setRequired();
		$frm->getField("CONF_DEFAULT_PAID_ORDER_STATUS")->html_after_field='<small>Set the default child order status when an order is marked Paid.</small>';
		
		$frm->addSelectBox('Default Shipping Order Status', 'CONF_DEFAULT_SHIPPING_ORDER_STATUS', $osObj->getAssociativeArray(), Settings::getSetting("CONF_DEFAULT_SHIPPING_ORDER_STATUS"), 'class="small"','')->requirements()->setRequired();
		$frm->getField("CONF_DEFAULT_SHIPPING_ORDER_STATUS")->html_after_field='<small>Set the default child order status when an order is marked Shipped.</small>';
		
		$frm->addSelectBox('Default Cancelled Order Status', 'CONF_DEFAULT_CANCEL_ORDER_STATUS', $osObj->getAssociativeArray(), Settings::getSetting("CONF_DEFAULT_CANCEL_ORDER_STATUS"), 'class="small"','')->requirements()->setRequired();
		$frm->getField("CONF_DEFAULT_CANCEL_ORDER_STATUS")->html_after_field='<small>Set the default child order status when an order is marked Cancelled.</small>';
		
		$frm->addSelectBox('Return Requested Order Status', 'CONF_RETURN_REQUEST_ORDER_STATUS', $osObj->getAssociativeArray(), Settings::getSetting("CONF_RETURN_REQUEST_ORDER_STATUS"), 'class="small"','')->requirements()->setRequired();
		$frm->getField("CONF_RETURN_REQUEST_ORDER_STATUS")->html_after_field='<small>Set the default child order status when return request is opened on any order.</small>';
		
		$frm->addSelectBox('Return Request Withdrawn Order Status', 'CONF_RETURN_REQUEST_WITHDRAWN_ORDER_STATUS', $osObj->getAssociativeArray(), Settings::getSetting("CONF_RETURN_REQUEST_WITHDRAWN_ORDER_STATUS"), 'class="small"','')->requirements()->setRequired();
		$frm->getField("CONF_RETURN_REQUEST_WITHDRAWN_ORDER_STATUS")->html_after_field='<small>Set the default child order status when return request is withdrawn.</small>';
		
		$frm->addSelectBox('Return Request Approved Order Status', 'CONF_RETURN_REQUEST_APPROVED_ORDER_STATUS', $osObj->getAssociativeArray(), Settings::getSetting("CONF_RETURN_REQUEST_APPROVED_ORDER_STATUS"), 'class="small"','')->requirements()->setRequired();
		$frm->getField("CONF_RETURN_REQUEST_APPROVED_ORDER_STATUS")->html_after_field='<small>Set the default child order status when return request is accepted by the vendor.</small>';
		
		
		$fld=$frm->addCheckBoxes('Vendor Order Status', 'CONF_VENDOR_ORDER_STATUS',$osObj->getAssociativeArray() ,Settings::getSetting("CONF_VENDOR_ORDER_STATUS"),'5', 'class=""');
		$fld->html_after_field='<small>Set the order status the customer\'s order must reach before the order starts displaying to Sellers</small>';
		
		$fld=$frm->addCheckBoxes('Buyer Order Status', 'CONF_BUYER_ORDER_STATUS',$osObj->getAssociativeArray() ,Settings::getSetting("CONF_BUYER_ORDER_STATUS"),'5', 'class=""');
		$fld->html_after_field='<small>Set the order status the customer\'s order must reach before the order starts displaying to Buyers</small>';
		$fld=$frm->addCheckBoxes('Stock Subtraction Order Status (Processing)', 'CONF_PROCESSING_ORDER_STATUS',$osObj->getAssociativeArray() ,Settings::getSetting("CONF_PROCESSING_ORDER_STATUS"),'5', 'class=""');
		$fld->html_after_field='<small>Set the order status the customer\'s order must reach before the order starts stock subtraction.</small>';
		
		$fld=$frm->addCheckBoxes('Completed Order Status', 'CONF_COMPLETED_ORDER_STATUS',$osObj->getAssociativeArray() ,Settings::getSetting("CONF_COMPLETED_ORDER_STATUS"),'5', 'class=""');
		$fld->html_after_field='<small>Set the order status the customer\'s order must reach before they are considered completed and payment released to vendors.</small>';
		
		$fld=$frm->addCheckBoxes('Feedback ready Order Status', 'CONF_REVIEW_READY_ORDER_STATUS',$osObj->getAssociativeArray() ,Settings::getSetting("CONF_REVIEW_READY_ORDER_STATUS"),'5', 'class=""');
		$fld->html_after_field='<small>Set the order status the customer\'s order must reach before they are allowed to review the orders.</small>';
		
		$fld=$frm->addCheckBoxes('Allow Order Cancellation by Buyers', 'CONF_ALLOW_CANCELLATION_ORDER_STATUS',$osObj->getAssociativeArray() ,Settings::getSetting("CONF_ALLOW_CANCELLATION_ORDER_STATUS"),'5', 'class=""');
		$fld->html_after_field='<small>Set the order status the customer\'s order must reach before they are allowed to place cancellation request on orders.</small>';
		
		$fld=$frm->addCheckBoxes('Allow Return/Exchange', 'CONF_RETURN_EXCHANGE_READY_ORDER_STATUS',$osObj->getAssociativeArray() ,Settings::getSetting("CONF_RETURN_EXCHANGE_READY_ORDER_STATUS"),'5', 'class=""');
		$fld->html_after_field='<small>Set the order status the customer\'s order must reach before they are allowed to place return/exchange request on orders.</small>';
		
		/*$fld=$frm->addCheckBoxes('Sales Calculation', 'CONF_SALES_ORDER_STATUS',$osObj->getAssociativeArray() ,Settings::getSetting("CONF_SALES_ORDER_STATUS"),'5', 'class=""');
		$fld->html_after_field='<small>Set the order status the customer\'s order must reach before they are considered in vendor\'s or site sales.</small>';*/
		
		$fld=$frm->addCheckBoxes('Purchases Calculation (For Buyers)', 'CONF_PURCHASE_ORDER_STATUS',$osObj->getAssociativeArray() ,Settings::getSetting("CONF_PURCHASE_ORDER_STATUS"),'5', 'class=""');
		$fld->html_after_field='<small>Set the order status the customer\'s order must reach before they are are considered in buyer\'s purchase.</small>';
			
		$frm->addHtml('<h5><strong>Stock</strong></h5>', 'htmlNote','');
		$frm->addRadioButtons('Check Stock:', 'CONF_CHECK_STOCK',$binary_status,Settings::getSetting("CONF_CHECK_STOCK"),2,'width="15%"');
		$frm->getField("CONF_CHECK_STOCK")->html_after_field='<small>Display out of stock message on the shopping cart page if a product is out of stock but allow checkout is yes.</small>';
		
		$frm->addRadioButtons('Allow Checkout:', 'CONF_ALLOW_CHECKOUT',$binary_status,Settings::getSetting("CONF_ALLOW_CHECKOUT"),2,'width="15%"');
		$frm->getField("CONF_ALLOW_CHECKOUT")->html_after_field='<small>Allow customers to still checkout if the products they are ordering are not in stock.</small>';
		
		/*$frm->addRadioButtons('Subtract Stock:', 'CONF_SUBTRACT_STOCK',$binary_status,Settings::getSetting("CONF_SUBTRACT_STOCK"),2,'width="15%"');
		$frm->getField("CONF_SUBTRACT_STOCK")->html_after_field='<small>Subtract stock when an order is placed.</small>';
		*/
	
	break;
	
	
	
	case 'live_chat':
		$frm->addRadioButtons('Enable Live Chat:', 'CONF_ENABLE_LIVECHAT',$binary_status,Settings::getSetting("CONF_ENABLE_LIVECHAT"),2,'width="15%"');
		$frm->getField("CONF_ENABLE_LIVECHAT")->html_after_field='<small>Enable 3rd Party Live Chat.</small>';
		
		$frm->addTextArea('Live Chat Code:', 'CONF_LIVE_CHAT_CODE',Settings::getSetting("CONF_LIVE_CHAT_CODE"), '', ' rows="5" class="large"');
		$frm->getField("CONF_LIVE_CHAT_CODE")->html_after_field='<small>This is the live chat script/code provided by the 3rd party API for integration.</small>';
		//$frm->addRadioButtons('Enable Friendly URL:', 'CONF_FRIEND_URL_ENABLED',$binary_status,CONF_FRIEND_URL_ENABLED,3,'width="15%" ');
	break;
	case 'third_party_api':
		
		
		$fldFBA=$frm->addTextBox('Facebook APP ID:', 'CONF_FACEBOOK_APP_ID', Settings::getSetting("CONF_FACEBOOK_APP_ID"), '', 'class="input-xlarge"');
		$fldFBA->html_after_field='<small>This is the application ID used in login and post.</small>';
		
		$fldFBA=$frm->addTextBox('Facebook App Secret:', 'CONF_FACEBOOK_APP_SECRET', Settings::getSetting("CONF_FACEBOOK_APP_SECRET"), '', 'class="input-xlarge"');
		$fldFBA->html_after_field='<small>This is the Facebook secret key used for authentication and other Facebook related plugins support.</small>';
		
		
		
		$fldFBA=$frm->addTextBox('Twitter APP Key:', 'CONF_TWITTER_API_KEY', Settings::getSetting("CONF_TWITTER_API_KEY"), '', 'class="input-xlarge"');
		$fldFBA->html_after_field='<small>This is the application Key used in login and post.</small>';
		
		$fldFBA=$frm->addTextBox('Twitter App Secret:', 'CONF_TWITTER_API_SECRET', Settings::getSetting("CONF_TWITTER_API_SECRET"), '', 'class="input-xlarge"');
		$fldFBA->html_after_field='<small>This is the Twitter secret key used for authentication and other Twitter related plugins support.</small>';
		
		
		$fldGP=$frm->addTextBox('Google Plus Developer Key:', 'CONF_GOOGLEPLUS_DEVELOPER_KEY', Settings::getSetting("CONF_GOOGLEPLUS_DEVELOPER_KEY"), '', 'class="input-xlarge"');
		$fldGP->html_after_field='<small>This is the google plus developer key.</small>';
		
		$fldGP=$frm->addTextBox('Google Plus Client ID:', 'CONF_GOOGLEPLUS_CLIENT_ID', Settings::getSetting("CONF_GOOGLEPLUS_CLIENT_ID"), '', 'class="input-xlarge"');
		$fldGP->html_after_field='<small>This is the application Client Id used to Login.</small>';
		
		$fldGPA=$frm->addTextBox('Google Plus Client Secret:', 'CONF_GOOGLEPLUS_CLIENT_SECRET', Settings::getSetting("CONF_GOOGLEPLUS_CLIENT_SECRET"), '', 'class="input-xlarge"');
		$fldGPA->html_after_field='<small>This is the Google Plid client secret key used for authentication.</small>';
		
		$frm->addHtml('<h5><strong>Newsletter Subscription</strong></h5>', 'htmlNote','');
		$frm->addRadioButtons('Enable Newsletter Subscription', 'CONF_ENABLE_NEWSLETTER_SUBSCRIPTION',$binary_status,Settings::getSetting("CONF_ENABLE_NEWSLETTER_SUBSCRIPTION"),2,'width="25%"');
		
		$fldML=$frm->addRadioButtons('Email Marketing System:', 'CONF_NEWSLETTER_SYSTEM',array("Mailchimp"=>"Mailchimp","Aweber"=>"Aweber"),Settings::getSetting("CONF_NEWSLETTER_SYSTEM"),3,'width="25%"');
		$fldML->html_after_field='<small>Please select the system you wish to use for email marketing.</small>';
		
		$fldML=$frm->addTextBox('Mailchimp Key:', 'CONF_MAILCHIMP_KEY', Settings::getSetting("CONF_MAILCHIMP_KEY"), '', 'class="input-xlarge"');
		$fldML->html_after_field='<small>This is the Mailchimp\'s application key used in subscribe and send newsletters.</small>';
		
		$fldML=$frm->addTextBox('Mailchimp List ID:', 'CONF_MAILCHIMP_LIST_ID', Settings::getSetting("CONF_MAILCHIMP_LIST_ID"), '', 'class="input-xlarge"');
		$fldML->html_after_field='<small>This is the Mailchimp\'s subscribers List ID.</small>';
		
		$fldML=$frm->addTextArea('Aweber Signup Form Code:', 'CONF_AWEBER_SIGNUP_CODE',Settings::getSetting("CONF_AWEBER_SIGNUP_CODE"), '', '  rows="5" class="large"');
		$fldML->html_after_field='<small>Enter the newsletter signup code received from Aweber.</small>';
		
		$frm->addHtml('<h5><strong>Google ReCaptcha</strong></h5>', 'htmlNote','');
		
		$fldML=$frm->addTextBox('Secret Key:', 'CONF_RECAPTCHA_SECRET_KEY', Settings::getSetting("CONF_RECAPTCHA_SECRET_KEY"), '', 'class="input-xlarge"');
		$fldML->html_after_field='<small>This is the Recaptcha secret key used in generating captcha.</small>';
		
		$fldML=$frm->addTextBox('Site Key:', 'CONF_RECAPTCHA_SITE_KEY', Settings::getSetting("CONF_RECAPTCHA_SITE_KEY"), '', 'class="input-xlarge"');
		$fldML->html_after_field='<small>This is the Recaptcha site key used in generating captcha.</small>';
		
		$frm->addHtml('<h5><strong>Google Analytics</strong></h5>', 'htmlNote','');	
		$fldML=$frm->addTextBox('Client Id:', 'CONF_ANALYTICS_CLIENT_ID', Settings::getSetting("CONF_ANALYTICS_CLIENT_ID"), '', 'class="input-xlarge"');
		$fldML->html_after_field='<br/><small>This is the application Client Id used in Analytics dashboard.</small>';
		
		$fldML=$frm->addTextBox('Secret Key:', 'CONF_ANALYTICS_SECRET_KEY', Settings::getSetting("CONF_ANALYTICS_SECRET_KEY"), '', 'class="input-xlarge"');
		$fldML->html_after_field='<br/><small>This is the application secret key used in Analytics dashboard.</small>';
		
		$fldML=$frm->addTextBox('Analytics Id:', 'CONF_ANALYTICS_ID', Settings::getSetting("CONF_ANALYTICS_ID"), '', 'class="input-xlarge"');
		$fldML->html_after_field='<br/><small>This is the Google Analytics ID. Ex. UA-xxxxxxx-xx.</small>';
		$accessToken=Settings::getSetting("CONF_ANALYTICS_ACCESS_TOKEN");		
		require_once (CONF_INSTALLATION_PATH . 'public/includes/APIs/analytics/analyticsapi.php');
		$analyticArr=array(
			'clientId'=>Settings::getSetting("CONF_ANALYTICS_CLIENT_ID"),
			'clientSecretKey'=>Settings::getSetting("CONF_ANALYTICS_SECRET_KEY"),
			'redirectUri'=>Utilities::generateAbsoluteUrl('configurations','redirect'),
			'googleAnalyticsID'=>Settings::getSetting("CONF_ANALYTICS_ID")
			);
		try{	
			$analytics=new Ykart_analytics($analyticArr);			
			$authUrl=$analytics->buildAuthUrl();		
		}catch(exception $e){ 
			//Message::addErrorMessage($e->getMessage());			
		}
		if($authUrl){
		$authenticateText=($accessToken=='')?'Authenticate':'Re-Authenticate';
		$fld=$frm->addHTML('', 'accessToken','Please save your settings & <a href="'.$authUrl.'" >click here</a> to '.$authenticateText.' settings.', '', 'class="medium"');
		}else{
			$fld=$frm->addHTML('', 'accessToken','Please configure your settings and then authenticate them', '', 'class="medium"');
		}
		
		
		$frm->addHtml('<h5><strong>Shipstation Shipping API</strong></h5>', 'htmlNote','');
		
		$frm->addRadioButtons('Enable Shipstation APIs:', 'CONF_SHIPSTATION_API_STATUS', $binary_status, Settings::getSetting("CONF_SHIPSTATION_API_STATUS"), 3, 'width="15%"');
		$fld=$frm->addTextBox('Shipstation Api Key', 'CONF_SHIPSTATION_API_KEY', Settings::getSetting("CONF_SHIPSTATION_API_KEY"), '', 'class="small"');
		$fld->html_after_field='<small>Please enter your shipstation API Key here.</small>';
		$fld=$frm->addTextBox('Shipstation Secret Key', 'CONF_SHIPSTATION_API_SECRET_KEY', Settings::getSetting("CONF_SHIPSTATION_API_SECRET_KEY"), '', 'class="small"');
		$fld->html_after_field='<small>Please enter your shipstation API Secret Key here.</small>';
		break;  
		
	break;
	
	
	case 'mail':
		$frm->addRadioButtons('Send Email:', 'CONF_SEND_EMAIL',$binary_status,Settings::getSetting("CONF_SEND_EMAIL"),3,'width="15%"');
		
		$frm->getField("CONF_SEND_EMAIL")->html_after_field='<small>Disable it to turn "Off" system wide emails.</small>';
		if(Settings::getSetting("CONF_SEND_EMAIL")){
			$fld=$frm->addHTML('', 'testsmtp','<a href="#" id="test_mail">Click here</a> to test email. This will send Test Email to Site Owner Email - '.Settings::getSetting("CONF_ADMIN_EMAIL") , '', 'class="medium"');
			$fld->html_after_field='<br/><small id="result_mail"></small>';
		}


		$fld=$frm->addTextBox('From Email:', 'CONF_FROM_EMAIL', Settings::getSetting("CONF_FROM_EMAIL"), '', 'class="medium"');
		$fld->requirements()->setRequired();
		$fld->requirements()->setEmail();
		$fld=$frm->addTextBox('From Name:', 'CONF_FROM_NAME', Settings::getSetting("CONF_FROM_NAME"), '', 'class="medium"');
		$fld->requirements()->setRequired();
		$fld=$frm->addTextBox('Reply to Email Address', 'CONF_REPLY_TO_EMAIL', Settings::getSetting("CONF_REPLY_TO_EMAIL"), '', 'class="medium"');
		$fld=$frm->addTextBox('Contact Email Address', 'CONF_CONTACT_EMAIL', Settings::getSetting("CONF_CONTACT_EMAIL"), '', 'class="medium"');
		
		$frm->addTextArea('Additional Alert E-Mails', 'CONF_ADDITIONAL_ALERT_EMAILS',Settings::getSetting("CONF_ADDITIONAL_ALERT_EMAILS"), '', '  rows="5" class="large"');
		$frm->getField("CONF_ADDITIONAL_ALERT_EMAILS")->html_after_field='<small>Any additional emails you want to receive the alert email, in addition to the main store email. (comma separated).</small>';
		
		
		$frm->addRadioButtons('SMTP Email:', 'CONF_SEND_SMTP_EMAIL',$binary_status,Settings::getSetting("CONF_SEND_SMTP_EMAIL"),3,'width="15%"');
		$fld=$frm->addTextBox('SMTP Host', 'CONF_SMTP_HOST', Settings::getSetting("CONF_SMTP_HOST"), 'smtp_host', 'class="medium"');
		$fld=$frm->addTextBox('SMTP Port', 'CONF_SMTP_PORT', Settings::getSetting("CONF_SMTP_PORT"), 'smtp_port', 'class="medium"');
		$fld=$frm->addTextBox('SMTP Username', 'CONF_SMTP_USERNAME', Settings::getSetting("CONF_SMTP_USERNAME"), 'smtp_username', 'class="medium"');
		$fld=$frm->addPasswordField('SMTP Password', 'CONF_SMTP_PASSWORD', Settings::getSetting("CONF_SMTP_PASSWORD"), 'smtp_password', 'class="medium"');
		
		$frm->addRadioButtons('SMTP Secure', 'CONF_SMTP_SECURE',array("TLS","SSL"),Settings::getSetting("CONF_SMTP_SECURE"),2,'width="15%" ');
		
		$fld=$frm->addHTML('', 'testsmtp','Please save your settings & <a href="#" id="test_smtp_mail">click here</a> to test SMTP settings. This will send Test Email to Site Owner Email - '.Settings::getSetting("CONF_ADMIN_EMAIL") , '', 'class="medium"');
		$fld->html_after_field='<br/><small id="result_smtp_mail"></small>';
		
	break;
	case 'server':
		$frm->setFieldsPerRow(1);
		
		$frm->addRadioButtons('Use SSL:', 'CONF_USE_SSL',$binary_status,Settings::getSetting("CONF_USE_SSL"),2,'width="15%"');
		$frm->getField("CONF_USE_SSL")->html_after_field='<small>To use SSL, check with your host if a SSL certificate is installed and enable it from here.</small>';
			
		$fld=$frm->addSelectBox('Enable Maintenance Mode:', 'CONF_MAINTENANCE',$binary_status,Settings::getSetting("CONF_MAINTENANCE"),'class="small" 
		"CONF_MAINTENANCE"','');
		$frm->getField("CONF_MAINTENANCE")->html_after_field='<small>On enabling this feature, only administrator can access the site (e.g., http://yourdomain.com/manager). Users will see a temporary page until you return to turn this off. (Turn this on, whenever you need to perform maintenance in the site.)</small>';
		$frm->addHtml('','','Maintenance Text:');
		$fld=$frm->addHtmlEditor('', 'CONF_MAINTENANCE_TEXT', Settings::getSetting("CONF_MAINTENANCE_TEXT"), 'conf_maintenance_text', '" class="cleditor"');
		//$fld->html_after_field='<div id="conf_maintenance_text_editor"></div>';
	break;
	
	case 'sharing':
		$fld=$frm->addFileUpload('Social Feed Image:', 'CONF_SOCIAL_FEED_IMAGE', 'CONF_SOCIAL_FEED_IMAGE', '');
			$social_feed_image=Settings::getSetting("CONF_SOCIAL_FEED_IMAGE");
			$fld->html_before_field='<div class="filefield"><span class="filename"></span>';
		if (!empty($social_feed_image)){
           $fld->html_after_field='<label class="filelabel">Browse File</label></div><br/><br/><img src="'.Utilities::generateUrl('image', 'social_feed_image',array(Settings::getSetting("CONF_SOCIAL_FEED_IMAGE"),'THUMB'), CONF_WEBROOT_URL).'" />';
		}else{
		  $fld->html_after_field='<label class="filelabel">Browse File</label></div><small>Works best with 200px X 200px. This Image will be shared on facebook when affiliate will share post on facebook.';
		}
		
		$frm->addHtml('<h5><strong>Facebook</strong></h5>', 'htmlNote','');
		$frm->addTextBox('Post Title:', 'CONF_SOCIAL_FEED_FACEBOOK_POST_TITLE',Settings::getSetting("CONF_SOCIAL_FEED_FACEBOOK_POST_TITLE"), '', 'class="medium"');
		
		$fld=$frm->addTextArea('Post Caption:', 'CONF_SOCIAL_FEED_FACEBOOK_POST_CAPTION',Settings::getSetting("CONF_SOCIAL_FEED_FACEBOOK_POST_CAPTION"), '', '  rows="5" class="large"');
		
		$fld->html_after_field='Admin can manage the caption of post when any user shares your online store on his Facebook profile.%s is the dynamic variable for your store name.';
		
		$fld=$frm->addTextArea('Post Description:', 'CONF_SOCIAL_FEED_FACEBOOK_POST_DESCRIPTION',Settings::getSetting("CONF_SOCIAL_FEED_FACEBOOK_POST_DESCRIPTION"), '', ' rows="5" class="large"');
		
		$fld->html_after_field='Admin can manage the caption of post when any user shares your online store on his Facebook profile.%s is the dynamic variable for your store name.';
		
		
		$frm->addHtml('<h5><strong>Twitter</strong></h5>', 'htmlNote','');
		$frm->addTextBox('Post Title:', 'CONF_SOCIAL_FEED_TWITTER_POST_TITLE',Settings::getSetting("CONF_SOCIAL_FEED_TWITTER_POST_TITLE"), '', 'class="medium"')->html_after_field='<small>This is the post published on twiiter, please don\'t keep more than 140 Characters here.%s is the dynamic variable for your store name.</small>';
		
	break;
	
	case 'referral':
		$frm->addRadioButtons('Enable Referral Module:', 'CONF_ENABLE_REFERRER_MODULE',$binary_status,Settings::getSetting("CONF_ENABLE_REFERRER_MODULE"),2,'width="15%"');
		
		$frm->addHtml('<h5><strong>Reward Benefits on Registration</strong></h5>', 'htmlNote','');
		$fld=$frm->addTextBox('Referrer Reward Points:', 'CONF_REGISTRATION_REFERRER_REWARD_POINTS', Settings::getSetting("CONF_REGISTRATION_REFERRER_REWARD_POINTS"), '', 'class="small"');
		$fld->requirements()->setIntPositive();
		$fld->html_after_field='&nbsp;<small>Referrers get this reward points when their referrals (friends) will register.</small>';
		
		$fld=$frm->addTextBox('Referrer Reward Points Validity:', 'CONF_REGISTRATION_REFERRER_REWARD_POINTS_VALIDITY', Settings::getSetting("CONF_REGISTRATION_REFERRER_REWARD_POINTS_VALIDITY"), '', 'class="small"');
		$fld->requirements()->setIntPositive();
		$fld->html_after_field='&nbsp;<strong>days</strong><small>Rewards points validity in days from the date of credit. Please leave it blank if you don\'t want reward points to expire.</small>';
		
		
		$fld=$frm->addTextBox('Referral Reward Points:', 'CONF_REGISTRATION_REFEREE_REWARD_POINTS', Settings::getSetting("CONF_REGISTRATION_REFEREE_REWARD_POINTS"), '', 'class="small"');
		$fld->requirements()->setIntPositive();
		$fld->html_after_field='&nbsp;<small>Referrals get this reward points when they register through referrer.</small>';
		
		$fld=$frm->addTextBox('Referral Reward Points Validity:', 'CONF_REGISTRATION_REFEREE_REWARD_POINTS_VALIDITY', Settings::getSetting("CONF_REGISTRATION_REFEREE_REWARD_POINTS_VALIDITY"), '', 'class="small"');
		$fld->requirements()->setIntPositive();
		$fld->html_after_field='&nbsp;<strong>days</strong><small>Rewards points validity in days from the date of credit. Please leave it blank if you don\'t want reward points to expire.</small>';
		
		$frm->addHtml('<h5><strong>Reward Benefits on First Purchase</strong></h5>', 'htmlNote','');
		$fld=$frm->addTextBox('Referrer Reward Points:', 'CONF_SALE_REFERRER_REWARD_POINTS', Settings::getSetting("CONF_SALE_REFERRER_REWARD_POINTS"), '', 'class="small"');
		$fld->requirements()->setIntPositive();
		$fld->html_after_field='&nbsp;<small>Referrers get this reward points when their referrals (friends) will make first purchase.</small>';
		
		$fld=$frm->addTextBox('Referrer Reward Points Validity:', 'CONF_SALE_REFERRER_REWARD_POINTS_VALIDITY', Settings::getSetting("CONF_SALE_REFERRER_REWARD_POINTS_VALIDITY"), '', 'class="small"');
		$fld->requirements()->setIntPositive();
		$fld->html_after_field='&nbsp;<strong>days</strong><small>Rewards points validity in days from the date of credit. Please leave it blank if you don\'t want reward points to expire.</small>';
		
		
		$fld=$frm->addTextBox('Referral Reward Points:', 'CONF_SALE_REFEREE_REWARD_POINTS', Settings::getSetting("CONF_SALE_REFEREE_REWARD_POINTS"), '', 'class="small"');
		$fld->requirements()->setIntPositive();
		$fld->html_after_field='&nbsp;<small>Referrals get this reward points when they will make first purchase through their referrers.</small>';
		
		$fld=$frm->addTextBox('Referral Reward Points Validity:', 'CONF_SALE_REFEREE_REWARD_POINTS_VALIDITY', Settings::getSetting("CONF_SALE_REFEREE_REWARD_POINTS_VALIDITY"), '', 'class="small"');
		$fld->requirements()->setIntPositive();
		$fld->html_after_field='&nbsp;<strong>days</strong><small>Rewards points validity in days from the date of credit. Please leave it blank if you don\'t want reward points to expire.</small>';
		
		break;
		}
		
		$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="25%" valign="baseline"');
		
		//$frm->setAction(Utilities::generateUrl('configurations', 'update'));
		$frm->setExtra('class="web_form"');
		$frm->setJsErrorDisplay('afterfield');
		return $frm;
	}
	
	function redirect() {
		require_once (CONF_INSTALLATION_PATH . 'public/includes/APIs/analytics/analyticsapi.php');
		$analyticArr=array(
			'clientId'=>Settings::getSetting("CONF_ANALYTICS_CLIENT_ID"),
			'clientSecretKey'=>Settings::getSetting("CONF_ANALYTICS_SECRET_KEY"),
			'redirectUri'=>Utilities::generateAbsoluteUrl('configurations','redirect'),
			'googleAnalyticsID'=>Settings::getSetting("CONF_ANALYTICS_ID")
			);
		try{	
			$analytics=new Ykart_analytics($analyticArr);
			$get=getQueryStringData();
		}catch(exception $e){
			Message::addErrorMessage($e->getMessage());
		}		
		
		if(isset($get['code']) && $get['code']!=''){
			$code=$get['code'];	
			$auth = $analytics->getAccessToken($code);	
			if($auth['refreshToken']!=''){
				$arr=array('CONF_ANALYTICS_ACCESS_TOKEN'=>$auth['refreshToken']);
				$settingsObj=new Settings();
				if($settingsObj->update($arr)){
					Message::addMessage('Success: Settings updated successfully.');					
				}else{
					Message::addErrorMessage($settingsObj->getError());
				}
			}else{
				Message::addError('Invalid Access Token.');
			}			
		}else{
			Message::addError('Invalid Access.');
		}		
		Utilities::redirectUser(Utilities::generateUrl('configurations','default_action',array('third_party_api')));
	}
}
