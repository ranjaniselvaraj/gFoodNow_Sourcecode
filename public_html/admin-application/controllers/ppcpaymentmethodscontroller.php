<?php
class PPCPaymentmethodsController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,PPCPAYMENTMETHODS);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("PPC Payment Methods Management", Utilities::generateUrl("ppcpaymentmethods"));
    }
    
	function default_action() {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->set('breadcrumb', $this->b_crumb->output());
		$pmObj=new PPCPaymentmethods();
        $this->set('arr_listing', $pmObj->getPaymentMethods());
        $this->_template->render();
    }
    function form($ppcpmethod_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("Payment Method Setup", '');
		$this->set('breadcrumb', $this->b_crumb->output());
		$pmObj=new PPCPaymentmethods();
        $ppcpmethod_id = intval($ppcpmethod_id);
        if ($ppcpmethod_id > 0) {
			$data = $pmObj->getData($ppcpmethod_id);
			$frm = $this->getForm($data);
			$frm->fill($data);
        }
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
					if($post['ppcpmethod_id'] != $ppcpmethod_id){
						Message::addErrorMessage('Error: Invalid Request!!');
						Utilities::redirectUser(Utilities::generateUrl('ppcppcpaymentmethods'));
					}else{
						
						if (Utilities::isUploadedFileValidImage($_FILES['ppcpmethod_icon'])){
							if(!Utilities::saveImage($_FILES['ppcpmethod_icon']['tmp_name'],$_FILES['ppcpmethod_icon']['name'], $saved_image_name, 'ppcpaymentmethods/')){
		               			Message::addError($saved_image_name);
    		   				}
							$post["ppcpmethod_icon"]=$saved_image_name;
    		    		}
					
						if($pmObj->addUpdate($post)){
							Message::addMessage('Success: Payment method details added/updated successfully.');
							Utilities::redirectUser(Utilities::generateUrl('ppcpaymentmethods'));
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
        $frm->addHiddenField('', 'ppcpmethod_id',$info["ppcpmethod_id"]);
		$frm->addRequiredField('Name', 'ppcpmethod_name',$info["ppcpmethod_name"], '', ' class="input-xlarge"');
		$frm->addTextArea('Details', 'ppcpmethod_details', '', 'ppcpmethod_details', 'rows="3"');
		$frm->addRequiredField('Display Order', 'ppcpmethod_display_order', $info["ppcpmethod_display_order"],'','class="small"')->requirements()->setIntPositive();
		$fld = $frm->addFileUpload('Icon', 'ppcpmethod_icon');
		if($info["ppcpmethod_icon"]!=""){
        	$fld->html_before_field = '<div id="ppcpmethod_image" >';
			$fld->html_after_field = '<p><b>Current Icon:</b><br /><img src="'.Utilities::generateUrl('image', 'ppcpayment_icon', array($info["ppcpmethod_icon"]),CONF_WEBROOT_URL).'" /></p></div>';
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
		$ppcmObj=new PPCPaymentmethods();
		$pmethod_id = intval($post['id']);
        $ppcpaymentmethod = $ppcmObj->getData($pmethod_id);
		if($ppcpaymentmethod==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
        $data_to_update = array('ppcpmethod_status'=>!$ppcpaymentmethod['ppcpmethod_status']);
		if($ppcmObj->updatePaymentMethodStatus($pmethod_id,$data_to_update)){
			Message::addMessage('Success: Status has been modified successfully.');
			$arr = array('status'=>1, 'msg'=>Message::getHtml(), 'linktext'=>(($paymentmethod['ppcpmethod_status'] == 1)?'Active':'Inactive'));
			die(convertToJson($arr));
		}else{
			Message::addErrorMessage($ppcmObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	
}