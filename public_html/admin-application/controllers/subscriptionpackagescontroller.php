<?php
class SubscriptionPackagesController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,SUBSCRIPTION_PACKAGES);
		$this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Subscription Packages",Utilities::generateUrl('subscriptionpackages'));
    }
	
	function default_action() {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->set('breadcrumb', $this->b_crumb->output());
		$spObj=new SubscriptionPackages();
        $this->set('arr_listing', $spObj->getSubscriptionPackages());
        $this->_template->render();
    }
    
    function form($merchantpack_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("Subscription Package Setup",'');
		$this->set('breadcrumb', $this->b_crumb->output());
		$spObj=new SubscriptionPackages();
        $sub_package_id = intval($merchantpack_id);
        $frm = $this->getForm();
        if ($merchantpack_id > 0) {
			$subPackObj = new SubPackages();
			$subPacks = $subPackObj->getSubPackages(array('exclude_free_package' => TRUE ,'merchantsubpack_merchantpack_id' =>$merchantpack_id , 'status' => 1 ) , array('tmsp.merchantsubpack_active' ,'tmsp.merchantsubpack_subs_frequency','tmsp.merchantsubpack_subs_period' , 'tmsp.merchantsubpack_id' , 'tmsp.merchantsubpack_actual_price' , 'tmsp.merchantsubpack_recurring_price' , 'tmsp.merchantsubpack_total_occurrance') );
			//Utilities::printarray($subPacks);
			//die();
            $data = $spObj->getData($merchantpack_id);
			$data['data'] = json_encode($subPacks);
            $frm->fill($data);
			
        }
		
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){
			$post = Syspage::getPostedVar();
			/*Utilities::printarray($post);
			die();*/
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
					if( $post['merchantpack_id'] != $merchantpack_id ){
						Message::addErrorMessage('Error: Invalid Request!!');
						Utilities::redirectUser(Utilities::generateUrl('packages'));
					}else{
						$subPackData = json_decode($post['data'] ,true);
						$data_removed = json_decode($post['data_removed'] ,true);
						unset($post['data']);
						if($packageId = $spObj->addUpdate($post)){
							$subPackObj = new SubPackages();
							if($free_subs_days = $post['merchantpack_free_trial_days']){
								$free_sub_pack_id = $subPackObj->getFreeTrialPack($packageId);
								$subPackObj->addUpdateFreeTrial($free_sub_pack_id , $packageId , $free_subs_days);
							}
							if($free_subs_days == 0){
								$subPackObj->deleteFreePlan($packageId);
							}
							foreach($data_removed as $subPackId){
								$subPackObj->deleteSubPackage($subPackId);
							}
							foreach($subPackData as $subPack){
								$subPack['merchantsubpack_merchantpack_id'] = $packageId ;
								if(!$subPackObj->addUpdate($subPack)){
									$error = true;
									Message::addErrorMessage($subPackObj->getError());
								}
							}
							if(empty($error)){
								Message::addMessage('Success: Package details added/updated successfully.');
								Utilities::redirectUser(Utilities::generateUrl('subscriptionpackages'));
							}
						}else{
							Message::addErrorMessage($packObj->getError());
						}
					}
			}
			$frm->fill($post);
		
		}
		
        $this->set('frm', $frm);
        $this->_template->render(true,true);
    }
    
	
    protected function getForm($sub_package_id=0,$data=array()) {
		global $duration_subscription_freq_arr;
		/*Utilities::printarray($duration_subscription_freq_arr);
		die();*/
        $frm = new Form('frmPackages','frmPackages');
		$frm->setExtra(' validator="PackagesfrmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('PackagesfrmValidator');
		$frm->captionInSameCell(false);
        $frm->addHiddenField('', 'merchantpack_id', $sub_package_id, 'merchantpack_id');
        $frm->addTextBox('Title', 'merchantpack_name','', '', ' class="medium"');
		$frm->addTextarea('Sub Title', 'merchantpack_description','', '', ' class="cleditor" rows="3"');
		$frm->addFloatField('Commission Rate (%)', 'merchantpack_commission_rate','', '', ' class="cleditor" rows="3"');
		$fld = $frm->addTextBox('Active Products','merchantpack_max_products');
		$fld->requirements()->setPositive();
		$fld = $frm->addTextBox('Images per product','merchantpack_images_per_product');
		$fld->requirements()->setPositive();
		$fld = $frm->addTextBox('Free Trial Period','merchantpack_free_trial_days');
		$fld->requirements()->setPositive();
		$fld->html_after_field='Days';
		$fld = $frm->addTextBox('Display Order','merchantpack_display_order');
		$fld->requirements()->setPositive();
		
		
		$frm->addHtml('Subscription Billing  <span>Max. 5 Entries allowed<span>' , '' ,'<div class="merged"></div>' , '');
		$frm->addTextBox('','merchantsubpack_id','','' , 'class="merchantsubpack_id merge" style="display:none;"');
		$fld = $frm->addFloatField('Package Price ['.CONF_CURRENCY_SYMBOL.']','merchantsubpack_actual_price','','' , 'class="merchantsubpack_actual_price merge"');
		$fld->requirements()->setPositive();
		$fld = $frm->addFloatField('Recurring Price ['.CONF_CURRENCY_SYMBOL.']','merchantsubpack_recurring_price','','' , 'class="merchantsubpack_recurring_price merge"');
		$fld->requirements()->setPositive();
		$fld = $frm->addIntegerField('Time Interval (Frequency)','merchantsubpack_subs_frequency','','' , 'class="merchantsubpack_subs_frequency merge"');
		$fld->requirements()->setPositive();
		
		//$fld = $frm->addSelectBox('Period', 'merchantsubpack_period', $duration_freq_arr, 'D', 'class="merchantsubpack_period merge"', '', 'merchantsubpack_period');
		//$fld->requirements()->setRequired();
		
		$fld = $frm->addSelectbox('Period','merchantsubpack_subs_period' , $duration_subscription_freq_arr , 'D'  , 'class="merchantsubpack_subs_period merge"' , 'Select' );
		$fld->requirements()->setRequired();
		
		
		$fld = $frm->addIntegerField('No. Of Total Occurrence','merchantsubpack_total_occurrance','','' , 'class="merchantsubpack_total_occurrance merge"');
		$fld->requirements()->setPositive();
		$fld->html_after_field = '<span class="merge">To submit a subscription with unlimited number of occurances (an ongoing subscription), this field must be submitted with a value of "9999".</span>';
		
		
		$fld = $frm->addSelectbox('Status','merchantsubpack_active' , array(1=>"Active",0=>"In-Active") , ''  , 'class="merchantsubpack_active merge"' , 'Select' );
		$fld->requirements()->setRequired();
		
		
		
		$frm->addHtml('' , '' , '<a class="addMoreSubs" href="javascript:void(0);">Add More +</a>' );
		$frm->addHiddenField('', 'data' ,'' ,'data');
		$frm->addHiddenField('', 'data_removed' ,'' ,'data_removed');
		$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setJsErrorDisplay('afterfield');  
        $frm->fill($data);
        return $frm;
    }
	
	function update_status() {
		if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
		$post = Syspage::getPostedVar();
		$spObj=new SubscriptionPackages();
		$subscription_package_id = intval($post['id']);
        $subscription_package = $spObj->getData($subscription_package_id);
		if($subscription_package==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
        $data_to_update = array('merchantpack_active'=>!$subscription_package['merchantpack_active']);
		if($spObj->updatePackageStatus($subscription_package_id,$data_to_update)){
			Message::addMessage('Success: Status has been modified successfully.');
			$arr = array('status'=>1, 'msg'=>Message::getHtml(), 'linktext'=>(($subscription_package['merchantpack_active'] == 1)?'Active':'Inactive'));
			die(convertToJson($arr));
		}else{
			Message::addErrorMessage($spObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	
	
}
