<?php
class PaymentmethodsController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,PAYMENTMETHODS);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Payment Methods Management", Utilities::generateUrl("paymentmethods"));
    }
	
	function default_action() {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->set('breadcrumb', $this->b_crumb->output());
		$pmObj=new Paymentmethods();
        $this->set('arr_listing', $pmObj->getPaymentMethods());
        $this->_template->render();
    }
    function form($pmethod_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("Payment Method Setup", '');
		$this->set('breadcrumb', $this->b_crumb->output());
		$pmObj=new Paymentmethods();
		$pmethod_id = intval($pmethod_id);
        if ($pmethod_id > 0) {
			$data = $pmObj->getData($pmethod_id);
			$frm = $this->getForm($data);
			$frm->fill($data);
        }
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
					if($post['pmethod_id'] != $pmethod_id){
						Message::addErrorMessage('Error: Invalid Request!!');
						Utilities::redirectUser(Utilities::generateUrl('paymentmethods'));
					}else{
						
						if (Utilities::isUploadedFileValidImage($_FILES['pmethod_icon'])){
							if(!Utilities::saveImage($_FILES['pmethod_icon']['tmp_name'],$_FILES['pmethod_icon']['name'], $saved_image_name, 'paymentmethods/')){
		               			Message::addError($saved_image_name);
    		   				}
							$post["pmethod_icon"]=$saved_image_name;
    		    		}
					
						if($pmObj->addUpdate($post)){
							Message::addMessage('Success: Payment method details added/updated successfully.');
							Utilities::redirectUser(Utilities::generateUrl('paymentmethods'));
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
        $frm->addHiddenField('', 'pmethod_id',$info["pmethod_id"]);
		$frm->addRequiredField('Name', 'pmethod_name',$info["pmethod_name"], '', ' class="input-xlarge"');
		$frm->addTextArea('Details', 'pmethod_details', '', 'pmethod_details', 'rows="3"');
		$frm->addRequiredField('Display Order', 'pmethod_display_order', $info["pmethod_display_order"],'','class="small"')->requirements()->setIntPositive();
		$fld = $frm->addFileUpload('Icon', 'pmethod_icon');
		if($info["pmethod_icon"]!=""){
        	$fld->html_before_field = '<div id="pmethod_image" >';
			$fld->html_after_field = '<p><b>Current Icon:</b><br /><img src="'.Utilities::generateUrl('image', 'payment_icon', array($info["pmethod_icon"]),CONF_WEBROOT_URL).'" /></p></div>';
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
		$pmObj=new Paymentmethods();
		$pmethod_id = intval($post['id']);
        $paymentmethod = $pmObj->getData($pmethod_id);
		if($paymentmethod==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
        $data_to_update = array('pmethod_status'=>!$paymentmethod['pmethod_status']);
		if($pmObj->updatePaymentMethodStatus($pmethod_id,$data_to_update)){
			Message::addMessage('Success: Status has been modified successfully.');
			$arr = array('status'=>1, 'msg'=>Message::getHtml(), 'linktext'=>(($paymentmethod['pmethod_status'] == 1)?'Active':'Inactive'));
			die(convertToJson($arr));
		}else{
			Message::addErrorMessage($pmObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	
}