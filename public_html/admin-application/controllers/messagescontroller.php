<?php
class MessagesController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,MESSAGES);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Messages", Utilities::generateUrl("messages"));
    }
	
	protected function getSearchForm() {
		global $review_status;
        $frm=new Form('frmMessagesSearch','frmMessagesSearch');
		$frm->setFieldsPerRow(3);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addHiddenField('', 'from');
		$frm->addHiddenField('', 'to');
		$fld=$frm->addTextBox('Keyword', 'keyword','','',' class="medium"');
		$fld->html_after_field='<small>Sender or Receiver Username, Subject</small>';
		$fld->merge_cells=2;
		$fld=$frm->addTextBox('Message By', 'message_by','','message_by',' class="small"');
		$fld=$frm->addTextBox('Message To', 'message_to','','message_to',' class="small"');
		$fld=$frm->addDateField('Date From', 'date_from', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld=$frm->addDateField('Date To', 'date_to', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchMessages(this); return false;');
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
	
	function listmessages($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
			$msgObj=new Messages();
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
            $this->set('records', $msgObj->getAllMessages($post));
            $this->set('pages', $msgObj->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $msgObj->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
	function view($thread,$message){
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("View Message", '');
		$this->set('breadcrumb', $this->b_crumb->output());
		Syspage::addJs(array('../js/admin/jquery.jeditable.mini.js'), false);
		$mObj=new Messages();
		$user_id=$this->getLoggedAdminId();
		$thread_detail=$mObj->getAllMessages(array("thread"=>$thread));
		if ($thread_detail==false)
			Utilities::redirectUser(Utilities::generateUrl('messages'));
				
		$adminObj=new Admin();
		$admin = $adminObj->getAdminById($_SESSION['logged_admin']['admin_logged']);
		$this->set('adminProfile', $admin);
		
		$this->set('thread_detail',$thread_detail);
		$this->set('message',$message);
		$this->_template->render();
	}
	function update_message(){
		$msgObj=new Messages();
		$post = Syspage::getPostedVar();
		if($msgObj->updateMessageBody($post["id"],array("message_text"=>$post["value"]))){
			echo $post["value"];
		}else{
			echo $msgObj->getError();
		}
	}
	
	function delete() {
        if ($this->canview != true) {
            dieJsonError("Unauthorized Access");
        }
        $post = Syspage::getPostedVar();
		$msgObj=new Messages();
        $message_id = intval($post['id']);
        $message = $msgObj->getData($message_id);
		if($message==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		if($msgObj->delete($message_id)){
			Message::addMessage('Success: Record has been deleted.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($msgObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	/*function delete($message_id) {
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),MESSAGES)) {
             die(Admin::getUnauthorizedMsg());
        }
		$msgObj=new Messages();
        if($msgObj->delete($message_id)){
			Message::addMessage('Success: Record has been deleted.');
		}else{
			Message::addErrorMessage($msgObj->getError());
		}
		
		Utilities::redirectUserReferer();
    }*/
}