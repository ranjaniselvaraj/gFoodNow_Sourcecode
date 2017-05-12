<?php
class SubscriptionPaymentmethodsController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,SUBSCRIPTIONPAYMENTMETHODS);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Subscription Payment Methods Management", Utilities::generateUrl("subscriptionpaymentmethods"));
    }
    
	function default_action() {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->set('breadcrumb', $this->b_crumb->output());
		$pmObj=new SubscriptionPaymentmethods();
        $this->set('arr_listing', $pmObj->getPaymentMethods());
        $this->_template->render();
    }
    function form($subscriptionpmethod_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("Subscription Payment Method Setup", '');
		$this->set('breadcrumb', $this->b_crumb->output());
		$pmObj=new SubscriptionPaymentmethods();
        $subscriptionpmethod_id = intval($subscriptionpmethod_id);
        if ($subscriptionpmethod_id > 0) {
			$data = $pmObj->getData($subscriptionpmethod_id);
			$frm = $this->getForm($data);
			$frm->fill($data);
        }
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
					if($post['subscriptionpmethod_id'] != $subscriptionpmethod_id){
						Message::addErrorMessage('Error: Invalid Request!!');
						Utilities::redirectUser(Utilities::generateUrl('subscriptionpaymentmethods'));
					}else{
						
						if (Utilities::isUploadedFileValidImage($_FILES['subscriptionpmethod_icon'])){
							if(!Utilities::saveImage($_FILES['subscriptionpmethod_icon']['tmp_name'],$_FILES['subscriptionpmethod_icon']['name'], $saved_image_name, 'subscriptionpaymentmethods/')){
		               			Message::addError($saved_image_name);
    		   				}
							$post["subscriptionpmethod_icon"]=$saved_image_name;
    		    		}
					
						if($pmObj->addUpdate($post)){
							Message::addMessage('Success: Payment method details added/updated successfully.');
							Utilities::redirectUser(Utilities::generateUrl('subscriptionpaymentmethods'));
						}else{
							Message::addErrorMessage($pmObj->getError());
						}
					}
			}
			$frm->fill($post);
		}
        $this->set('frm', $frm);
        $this->_template->render(true,true);
    }
	
    protected function getForm($info) {
		global $payment_status_arr;
        $frm = new Form('frmPaymentMethods','frmPaymentMethods');
		$frm->setExtra(' validator="PaymentMethodfrmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('PaymentMethodfrmValidator');
        $frm->addHiddenField('', 'subscriptionpmethod_id',$info["subscriptionpmethod_id"]);
		$frm->addRequiredField('Name', 'subscriptionpmethod_name',$info["subscriptionpmethod_name"], '', ' class="input-xlarge"');
		$frm->addTextArea('Details', 'subscriptionpmethod_details', '', 'subscriptionpmethod_details', 'rows="3"');
		$frm->addRequiredField('Display Order', 'subscriptionpmethod_display_order', $info["subscriptionpmethod_display_order"],'','class="small"')->requirements()->setIntPositive();
		$fld = $frm->addFileUpload('Icon', 'subscriptionpmethod_icon');
		if($info["subscriptionpmethod_icon"]!=""){
        	$fld->html_before_field = '<div id="subscriptionpmethod_image" >';
			$fld->html_after_field = '<p><b>Current Icon:</b><br /><img src="'.Utilities::generateUrl('image', 'subscriptionpayment_icon', array($info["subscriptionpmethod_icon"]),CONF_WEBROOT_URL).'" /></p></div>';
		}
		$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="20%"');
        return $frm;
    }
    
	function update_status() {
		if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
		$post = Syspage::getPostedVar();
		$ppcmObj=new SubscriptionPaymentmethods();
		$pmethod_id = intval($post['id']);
        $ppcpaymentmethod = $ppcmObj->getData($pmethod_id);
		if($ppcpaymentmethod==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
        $data_to_update = array('subscriptionpmethod_status'=>!$ppcpaymentmethod['subscriptionpmethod_status']);
		if($ppcmObj->updatePaymentMethodStatus($pmethod_id,$data_to_update)){
			Message::addMessage('Success: Status has been modified successfully.');
			$arr = array('status'=>1, 'msg'=>Message::getHtml(), 'linktext'=>(($paymentmethod['subscriptionpmethod_status'] == 1)?'Active':'Inactive'));
			die(convertToJson($arr));
		}else{
			Message::addErrorMessage($ppcmObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	
}