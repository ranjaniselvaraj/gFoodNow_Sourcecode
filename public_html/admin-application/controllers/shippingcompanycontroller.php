<?php
class ShippingcompanyController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,SHIPPINGCOMPANY);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Shipping Company Management",Utilities::generateUrl('shippingcompany'));
    }
	
    function default_action() {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->set('breadcrumb', $this->b_crumb->output());
		$scObj=new Shippingcompany();
        $this->set('arr_listing', $scObj->getShippingcompanies());
        $this->_template->render();
    }
    function form($scompany_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("Shipping Company Setup",'');
		$this->set('breadcrumb', $this->b_crumb->output());
		$scObj=new Shippingcompany();
        $scompany_id = intval($scompany_id);
        $frm = $this->getForm();
        if ($scompany_id > 0) {
            $data = $scObj->getData($scompany_id);
            $frm->fill($data);
        }
		
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
				if($post['scompany_id'] != $scompany_id){
					Message::addErrorMessage('Error: Invalid Request!!');
					Utilities::redirectUser(Utilities::generateUrl('shippingcompany'));
				}else{
					
					if($scObj->addUpdate($post)){
						Message::addMessage('Success: Shipping company details added/updated successfully.');
						Utilities::redirectUser(Utilities::generateUrl('shippingcompany'));
					}else{
						Message::addErrorMessage($scObj->getError());
					}
				}
			}
			$frm->fill($post);
		}
		
        $this->set('frm', $frm);
        $this->_template->render(true,true);
    }
    protected function getForm() {
        $frm = new Form('frmShippingcompany');
		$frm->setExtra(' validator="ShippingcompanyfrmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('ShippingcompanyfrmValidator');
        $frm->addHiddenField('', 'scompany_id');
		$frm->addRequiredField('Name', 'scompany_name','', '', ' class="input-xlarge"');
		$fld=$frm->addRequiredField('Website URL', 'scompany_website','', '', ' class="input-xlarge"');
		$fld->requirements()->setRegularExpressionToValidate('^(http(?:s)?\:\/\/[a-zA-Z0-9]+(?:(?:\.|\-)[a-zA-Z0-9]+)+(?:\:\d+)?(?:\/[\w\-]+)*(?:\/?|\/\w+\.[a-zA-Z]{2,4}(?:\?[\w]+\=[\w\-]+)?)?(?:\&[\w]+\=[\w\-]+)*)$');
		$fld->html_after_field='<small>Please enter website URL here. Example - http://www.fedex.com</small>';
		$fld=$frm->addTextArea('Comments', 'scompany_comments', '', 'scompany_comments', ' class="cleditor" rows="3"');
		$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="20%"');
        return $frm;
    }
	function delete() {
        if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
        $post = Syspage::getPostedVar();
		$scObj=new Shippingcompany();
        $shippingcompany_id = intval($post['id']);
        $shippingCompany = $scObj->getData($shippingcompany_id);
		if($shippingCompany==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		if($scObj->delete($shippingcompany_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($scObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	function autocomplete(){
	    $post = Syspage::getPostedVar();
		$json = array();
		$scObj=new Shippingcompany();
        $shipping_companies=$scObj->getShippingcompanies(array("keyword"=>urldecode($post["keyword"]),"pagesize"=>10));
		foreach($shipping_companies as $skey=>$sval){
			$json[] = array(
					'id' => $sval['scompany_id'],
					'name'      => strip_tags(htmlentities($sval['scompany_name'], ENT_QUOTES, 'UTF-8'))
				);
		}
		$sort_order = array();
		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}
		array_multisort($sort_order, SORT_ASC, $json);
		echo json_encode($json);
	}
	
}