<?php
class ThemesController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,THEMES);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Themes Management", Utilities::generateUrl("themes"));
    }
	
	function default_action() {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->set('breadcrumb', $this->b_crumb->output());
		$tObj=new Themes();
		$this->set('arr_listing', $tObj->getThemes());
        $this->_template->render();
    }
	
	function form($theme_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("Theme Setup", Utilities::generateUrl("themes"));
		$this->set('breadcrumb', $this->b_crumb->output());
		Syspage::addJs(array('../js/admin/jscolor.js'), false);
		$tObj=new Themes();
		if ($theme_id>0){
	        $theme = $tObj->getThemeById($theme_id);
			if (!$theme["theme_added_by"]){
				Message::addErrorMessage('Error: Sorry, pre-configured themes are not editable.');
				Utilities::redirectUser(Utilities::generateUrl('themes'));
			}
		}
        $frm = $this->getForm($theme_id,$theme);
		
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				if($post['theme_id'] != $theme_id){
					Message::addErrorMessage('Error: Invalid Request!!');
					Utilities::redirectUser(Utilities::generateUrl('themes'));
				}else{
					$post['theme_added_by']=$this->getLoggedAdminId();
					if($tObj->addUpdate($post)){
						Message::addMessage('Success: Theme details added/updated successfully.');
						Utilities::redirectUser(Utilities::generateUrl('themes'));
					}else{
						Message::addErrorMessage($tObj->getError());
					}
				}
			}
			$frm->fill($post);
		}
		
        $this->set('frm', $frm);
        $this->_template->render(true,true);
    }
	
	
    protected function getForm($theme_id,$data=array()) {
        $frm = new Form('frmTheme','frmTheme');
		$frm->captionInSameCell(false);
        $frm->addHiddenField('', 'theme_id', $theme_id, 'theme_id');
        $frm->addRequiredField('Theme Name', 'theme_name');
		$frm->addRequiredField('Primary Color', 'theme_primary_color','','','class="jscolor"');
		$fld=$frm->addRequiredField('Secondary Color', 'theme_secondary_color','','','class="jscolor"');
		$fld->html_after_field ='<small>Please keep it in contrast with primary color</small>';
		
		$fld=$frm->addRequiredField('Product Thumb Icon/Price Color', 'theme_product_box_icon_price_color','','','class="jscolor"');
		$fld->html_after_field ='<small>This one reflects on product\'s thumb view overlay icons and price display only.</small>';
		
		$fld=$frm->addRequiredField('Top Navigation Text Color', 'theme_top_nav_text_color','','','class="jscolor"');
		$fld->html_after_field ='<small>Please keep it in contrast with primary color</small>';
		
		$fld=$frm->addRequiredField('Top Navigation Hover Text Color', 'theme_top_nav_hover_color','','','class="jscolor"');
		$fld->html_after_field ='<small>Please keep it in contrast with primary color</small>';
		
		$fld=$frm->addRequiredField('Secondary Button Text Color', 'theme_secondary_button_text_color','','','class="jscolor"');
		$fld->html_after_field ='<small>Please keep it in contrast with secondary color</small>';
		
		$fld=$frm->addRequiredField('Top Bar Color', 'theme_top_bar_color','','','class="jscolor"');
		$frm->addRequiredField('Top Bar Text Color', 'theme_top_bar_text_color','','','class="jscolor"');
		$fld=$frm->addRequiredField('Left Box Color', 'theme_left_box_color','','','class="jscolor"');
		$fld->html_after_field ='<small>This one reflects on category, shop, brands pages.</small>';
		$frm->addTextBox('Display Order', 'theme_display_order');
        $frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
        $frm->setExtra(' validator="themefrmValidator" class="web_form" rel="upload"');
        $frm->setValidatorJsObjectName('themefrmValidator');
		$frm->setJsErrorDisplay('afterfield');
        $frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="20%"');
	    $frm->fill($data);
        return $frm;
    }
	
    function delete() {
        if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
        $post = Syspage::getPostedVar();
		$tObj=new Themes();
        $theme_id = intval($post['id']);
        $theme = $tObj->getThemeById($theme_id);
		if($theme==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		if (!$theme["theme_added_by"]){
			 Message::addErrorMessage('Error: Sorry, pre-configured themes cannot be deleted.');
			 dieJsonError(Message::getHtml());
		}
		if($tObj->delete($theme_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($tObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	function activate($theme_id) {
        if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
        $tObj=new Themes();
		$post = Syspage::getPostedVar();
		if (!empty($post)){
        	$theme_id = intval($post['id']);
			$ajax_request=true;
		}
        $theme = $tObj->getThemeById($theme_id);
		if($theme==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		$settingsObj=new Settings();
		if($settingsObj->update(array('CONF_FRONT_THEME'=>$theme_id))){
				Message::addMessage('Theme activated successfully.');
				if ($ajax_request)	{
					dieJsonSuccess(Message::getHtml());			
				}
		}else{
				Message::addErrorMessage($settingsObj->getError());
				if ($ajax_request)	{	
					dieJsonError(Message::getHtml());		
				}
		}
		Utilities::redirectUser(Utilities::generateUrl('themes'));
    }
	
	
	
	function preview($theme_id) {
		$tObj=new Themes();
       	$theme = $tObj->getThemeById($theme_id);
		if (!$theme){
			Message::addErrorMessage('Error: Please select valid theme to preview!');
			Utilities::redirectUserReferer();
		}
		$_SESSION['preview_theme']=$theme_id;
		$this->set('theme', $theme_id);
        $this->_template->render(false,false);
    }
	
	
	
	function theme_clone($theme_id){
		$tObj=new Themes();
       	$theme = $tObj->getThemeById($theme_id);
		if (!$theme){
			Message::addErrorMessage('Error: Please select valid theme to preview!');
			Utilities::redirectUserReferer();
		}else{
				if($tObj->createClone($theme_id,$this->getLoggedAdminId())){
					Message::addMessage('Success: Theme clone created successfully.');
					Utilities::redirectUser(Utilities::generateUrl('themes'));
				}else{
					Message::addErrorMessage($tObj->getError());
				}
			}
			Utilities::redirectUser(Utilities::generateUrl('themes'));
	}
	
}