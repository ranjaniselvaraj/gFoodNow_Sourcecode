<?php
class CurrencyController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,CURRENCY);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Currency Management",Utilities::generateUrl('currency'));
    }
	
    function default_action() {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->set('breadcrumb', $this->b_crumb->output());
		$crObj=new Currency();
        $this->set('arr_listing', $crObj->getAllCurrencyRecords());
        $this->_template->render();
    }
    function form($currency_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("Currency Setup", Utilities::generateUrl("currency"));
		$this->set('breadcrumb', $this->b_crumb->output());
		$crObj=new Currency();
        $currency_id = intval($currency_id);
        $frm = $this->getForm($currency_id);
        if ($currency_id > 0) {
            $data = $crObj->getData($currency_id);
            $frm->fill($data);
        }
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
					if($post['currency_id'] != $currency_id){
						Message::addErrorMessage('Error: Invalid Request!!');
						Utilities::redirectUser(Utilities::generateUrl('currency'));
					}else{
						$currency = $crObj->getCurrencyByCode($post['currency_code']);
						if (($currency==true) && ($currency["currency_id"]!=$post["currency_id"]) ){
							Message::addErrorMessage('Currency with this code already exists.');
						}else{
							if($crObj->addUpdate($post)){
								Message::addMessage('Success: Currency details added/updated successfully.');
							Utilities::redirectUser(Utilities::generateUrl('currency'));
							}else{
								Message::addErrorMessage($crObj->getError());
							}
						}
					
					}
			}
			$frm->fill($post);
		}
        $this->set('frm', $frm);
        $this->_template->render(true,true);
    }
    protected function getForm($id) {
        $frm = new Form('frmCurrency','frmCurrency');
		$frm->setExtra(' validator="CurrencyfrmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('CurrencyfrmValidator');
        $frm->addHiddenField('', 'currency_id');
		$frm->addRequiredField('Title', 'currency_title');
		$frm->addRequiredField('Code', 'currency_code');
		$frm->addRequiredField('Decimal Places', 'currency_decimal')->requirements()->setIntPositive();
		$frm->addTextBox('Symbol Left', 'currency_symbol_left');
		$frm->addTextBox('Symbol Right', 'currency_symbol_right');
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
		$crObj=new Currency();
        $currency_id = intval($post['id']);
		$ordObj=new Orders();
		$currencies = $ordObj->getOrders(array("currency"=>$currency_id));
		if (!empty($currencies)){
			Message::addErrorMessage('Warning: This currency cannot be deleted as it is currently assigned to orders!');
			dieJsonError(Message::getHtml());
		}
		
        $currency = $crObj->getData($currency_id);
		if ($currency['currency_code']==Settings::getSetting("CONF_CURRENCY")){
           Message::addErrorMessage('Warning: This currency cannot be deleted as it is currently assigned as the default store currency!');
           dieJsonError(Message::getHtml());
        }
		if($crObj->delete($currency_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($crObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
    
}