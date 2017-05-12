<?php
class ReturnrequestsController extends BackendController {
	
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,RETURN_REQUESTS);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Return Requests Management", Utilities::generateUrl("returnrequests"));
    }
	
	protected function getSearchForm() {
		global $return_status_arr;
	    $frm=new Form('frmReturnRequestsSearch','frmReturnRequestsSearch');
		$frm->setFieldsPerRow(3);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('GET');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addTextBox('Keyword', 'keyword','','',' class="small"');
		$frm->addSelectBox('Active', 'status',$return_status_arr,'', 'class="small"','All');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchReturnRequests(this); return false;');
        return $frm;
    }
	
	function default_action() {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $frm = $this->getSearchForm();
		$frm->fill(getQueryStringData());
        $this->set('frmPost', $frm);
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listReturnRequests($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
			$pObj=new Products();
            $post = Syspage::getPostedVar();
            $page = 1;
            if (isset($post['page']) && intval($post['page']) > 0) {
                $page = intval($post['page']);
            } else {
                $post['page'] = $page;
            }
            if (!empty($post)) {
                $this->set('srch', $post);
            }
           	$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
            $post['pagesize'] = $pagesize;
            $this->set('records', $pObj->getProductReturnRequests($post));
            $this->set('pages', $pObj->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $pObj->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
	
	function view_return_request($id){
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("View Return Reques",'');
		$this->set('breadcrumb', $this->b_crumb->output());
		$userObj=new User();
		$prodObj=new Products();
		$admin_id=$this->getLoggedAdminId();
		$request_detail = $prodObj->getProductReturnRequests(array("id"=>$id,"pagesize"=>1),1);
		if (!$request_detail){
			 Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
			 Utilities::redirectUserReferer();
		}
		$request_detail["messages"] = $prodObj->getReturnRequestMessages($id);
		$frm=$this->return_request_message_form();
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])){	
				if(!$frm->validate($post)){
					Message::addErrorMessage($frm->getValidationErrors());
					$frm->fill($post);
				}else{
						if (Utilities::isUploadedFileValidFile($_FILES['refmsg_attachment'])){
							if(!Utilities::saveFile($_FILES['refmsg_attachment']['tmp_name'],$_FILES['refmsg_attachment']['name'], $attachment, 'messages/')){
		               			Message::addError($attachment);
    		   				}
							$post["attachment"]=$attachment;
    			    	}
						$arr=array_merge($post,array("user_id"=>$admin_id,"id"=>$id,"type"=>"A"));
						if($userObj->addProductReturnRequestMessage($arr)){
							$emailNotificationObj=new Emailnotifications();
							if ($emailNotificationObj->SendReturnRequestMessageNotification($userObj->getReturnRequestMessageId())){
								Message::addMessage(Utilities::getLabel('M_YOUR_MESSAGE_SENT_SUCCESSFULLY'));	
							}else{
								Message::addErrorMessage($emailNotificationObj->getError());
							}
						}else{
							Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
						}
						Utilities::redirectUser(Utilities::generateUrl('returnrequests', 'view_return_request',array($id)));
					}
				
		}
		$this->set('frm',$frm);
		$this->set('request_detail', $request_detail);
		$this->_template->render();	
	}
	
	private function return_request_message_form(){
		$frm = new Form('frmSendMessage','frmSendMessage');
		$fld = $frm->addTextArea("Comment", 'refmsg_text');
		$fld->requirements()->setCustomErrorMessage(Utilities::getLabel('L_Please_enter_your_message'));
		$fld->requirements()->setRequired(true);
		$fld=$frm->addFileUpload('Attachment', 'refmsg_attachment');
		$fld->html_before_field = '<div class="filefield"><span class="filename"></span>';
		$fld->html_after_field = '<label class="filelabel">Browse File</label></div>';
		$frm->addSubmitButton('', 'btn_submit', Utilities::getLabel('M_Send'), 'btn_submit');
		$frm->setExtra('class="web_form"');		
		$frm->captionInSameCell(false);		
		$frm->setValidatorJsObjectName('frmValidator');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		return $frm;
	}
	
	function update_request_status() {
		if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
		$post = Syspage::getPostedVar();
		$mod = $post['mod'];
		$prodObj=new Products();
        $request_id = intval($post['id']);
        $request=$prodObj->getReturnRequest($request_id,array("status"=>array("0","1")));
        if($request==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		$admin_id=$this->getLoggedAdminId();
		$userObj=new User();
		$action = $mod=='cancel'?$userObj->withdrawRequest($request_id,$admin_id,'A'):$userObj->approveRequest($request_id,$admin_id,'A');
		if($action){
				$emailNotificationObj=new Emailnotifications();
				if ($emailNotificationObj->SendReturnRequestStatusChangeNotification($request_id)){
					Message::addMessage('Success: Status has been modified successfully.');
					dieJsonSuccess(Message::getHtml());	
				}else{
					Message::addErrorMessage($emailNotificationObj->getError());
					dieJsonError(Message::getHtml());
				}
		}else{
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
			dieJsonError(Message::getHtml());
		}
		
    }
	
	
	function download_attachment($return_request_message) {
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),RETURN_REQUESTS)) {
             die(Admin::getUnauthorizedMsg());
        }
		$p=new Products();
		$return_request=$p->getReturnRequestMessage($return_request_message);
		if ($return_request){
			Utilities::outputFile("messages/".$return_request["refmsg_attachment"],false,false,'',false);
		}else{
			Message::addErrorMessage(Utilities::getLabel('M_ERROR_NOT_AUTHORIZED'));
			Utilities::redirectUser(Utilities::generateUrl('returnrequests'));
		}
    }
    
	
}
