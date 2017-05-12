<?php
class BlogcontributionsController extends BackendController {
    private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
		$this->canview = Admin::getAdminAccess($admin_id,BLOG_CONTRIBUTIONS);
		$this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();
        $this->b_crumb->add("Blog Contributions Management", Utilities::generateUrl("blogcontributions"));
    }
    function default_action() {
        if ($this->canview != true) {
            dieWithError("Unauthorized Access");
        }
        $frm = $this->getSearchForm();
        $this->set('frmContributions', $frm);
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
    function listContributions() {
        if ($this->canview != true) {
            dieWithError("Unauthorized Access");
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
            $post = Syspage::getPostedVar();
            $page = 1;
            if (isset($post['page']) && intval($post['page']) > 0)
                $page = intval($post['page']);
            else
                $post['page'] = $page;
            if (!empty($post['contribution_author_first_name']) || (isset($post['contribution_status'])) && $post['contribution_status'] != '') {
                $this->set('srch', $post);
            }
            $pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");;
            $post['pagesize'] = $pagesize;
            $this->set('records', $this->Blogcontributions->getContributionsData($post));
            $this->set('pages', $this->Blogcontributions->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $this->Blogcontributions->getTotalRecords();
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
    private function getSearchForm() {
        $frm = new Form('frmSearchContri', 'frmSearchContri');
        $frm->setExtra('class="web_form"');
        $frm->setJsErrorDisplay('afterfield');
        $frm->setTableProperties('class="table_form_vertical" width="100%" cellspacing="0" cellpadding="0" border="0"');
        $frm->setFieldsPerRow(4);
        $frm->captionInSameCell(true);
        $frm->addTextBox('First Name', 'contribution_author_first_name', '', 'contribution_author_first_name', '');
        $frm->addSelectBox('Contribution Status', 'contribution_status', array(0 => 'Pending', 1 => 'Approved', 2 => 'Posted/Published', 3 => 'Rejected'), '', '', 'Select', 'contribution_status');
        $fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick=location.href="' . Utilities::generateUrl("blogcontributions") . '"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
        $frm->addHiddenField('', 'page', 1);
        $frm->setOnSubmit('searchContributions(this); return false;');
        return $frm;
    }
    private function getContributionStatusUpdateForm() {
        $frm = new Form('frmBlogContributions', 'frmBlogContributions');
        $frm->setExtra('class="web_form"');
        $frm->setJsErrorDisplay('afterfield');
        $frm->addHiddenField('', 'contribution_id', '');
        $status_array = array(0 => 'Pending', 1 => 'Approved', 2 => 'Posted', 3 => 'Rejected');
        $frm->addSelectBox('Contribution Status', 'contribution_status', $status_array, '', '', 'Select', 'contribution_status');
        $frm->addSubmitButton('', 'update', 'Update', 'update');
        //->html_after_field = '<input type="button"  class="" value="Cancel" onclick = "cancelContribution();">';
        $frm->setOnSubmit('updateStatus(this); return false;');
        return $frm;
    }
    function view($contribution_id) {
        if ($this->canview != true) {
            dieWithError("Unauthorized Access");
        }
        $contribution_id = intval($contribution_id);
        if ($contribution_id < 1) {
            dieWithError("Unauthorized Access");
        }
        $contribution_data = $this->Blogcontributions->getContributionPost($contribution_id);
        $status_array = array('0' => 'Pending', '1' => 'Approved', '2' => 'Posted/Published', '3' => 'Rejected');
        //unset($status_array[$contribution_data['contribution_status']]);
        $frm = $this->getContributionStatusUpdateForm();
        $frm->getField('contribution_status')->options = $status_array;
        $frm->getField('contribution_status')->value = $contribution_data['contribution_status'];
        $frm->getField('contribution_id')->value = $contribution_data['contribution_id'];
        $this->set('frmContributionStatusUpdate', $frm);
        $this->set('contribution_data', $contribution_data);
        $this->b_crumb->add("&nbsp;&nbsp;View Contribution", Utilities::generateUrl("blogcontributions", "view"));
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
    function updateStatus() {
        if ($this->canview != true) {
            dieWithError("Unauthorized Access");
        }
        $post = Syspage::getPostedVar();
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $post['contribution_status'] != '') {
            if ($this->Blogcontributions->updateStatus($post)) {
                $contribution_status = $post['contribution_status'];
                $contribution_id = $post['contribution_id'];
                $record = $this->Blogcontributions->getContributionPost($contribution_id);
				$replacements = array(
                        '{USER_FULL_NAME}' => ucfirst($record['contribution_author_first_name']) . ' ' . ucfirst($record['contribution_author_last_name']),
                        '{POSTED_ON}' => displayDate($record['contribution_date_time']),
                        '{USER_EMAIL}' => $data['contribution_author_email'],
                        
                    );
                if ($contribution_status == '2') {
                    $replacements['{STATUS}']="Published.";
                    Message::addMessage('Status updated successfully.');
                }
                if ($contribution_status == '1') {
                  $replacements['{STATUS}']="Approved.";
                  Message::addMessage('Status updated successfully.');
                }
                Message::addMessage('Status updated successfully.');
                dieJsonSuccess(Message::getHtml());
            } else {
                Message::addErrorMessage($this->Blogcontributions->getError());
                dieJsonError(Message::getHtml());
            }
        }
        Message::addErrorMessage('Invalid request!!');
        dieJsonError(Message::getHtml());
        exit(0);
    }
    function download($filename, $display_name) {
        if ($this->canview != true) {
            dieWithError("Unauthorized Access");
        }
        $file = $filename;
        $filename = CONF_INSTALLATION_PATH . 'user-uploads/contributions/' . $filename;
        $finfo = finfo_open(FILEINFO_MIME_TYPE); //will return mime type
        $file_mime_type = finfo_file($finfo, $filename);
        /* $accepted_files = array(
          'application/pdf',
          'application/octet-stream',
          'application/msword',
          'text/plain',
          'application/zip',
          'application/x-rar'
          );
          if(!in_array(trim($file_mime_type), $accepted_files)){
          return false;
          } */
        header('Content-Type: ' . $file_mime_type);
        header('Content-Disposition: attachment; filename="' . $display_name . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filename));
        echo file_get_contents($filename, true);
        exit(0);
    }
    function delete($contribution_id, $contribution_file_name = '') {
		
		$post = Syspage::getPostedVar();
		  
        if ($this->canview != true) { 
			Message::addErrorMessage("Unauthorized Access");
            Utilities::redirectUser(Utilities::generateUrl('blogcontributions'));
        }
        $contribution_id = intval($contribution_id);
        if ($contribution_id < 1) {
            Message::addErrorMessage("Unauthorized Access");
            Utilities::redirectUser(Utilities::generateUrl('blogcontributions'));
        }
        $data = $this->Blogcontributions->getContributionPost($contribution_id);
        if ($this->Blogcontributions->deleteContribution($contribution_id)) {
            $contribution_file_name = $data['contribution_file_name'];
            if (isset($contribution_file_name) && is_string($contribution_file_name) && strlen($contribution_file_name) > 1) {
                unlink(CONF_INSTALLATION_PATH . 'user-uploads/contributions/' . $contribution_file_name);
            }
            Message::addMessage('Contribution deleted successfully.');
            Utilities::redirectUser(Utilities::generateUrl('blogcontributions'));
        } else {
            Message::addErrorMessage($this->Blogcontributions->getError());
            Utilities::redirectUser(Utilities::generateUrl('blogcontributions'));
        }
        $this->_template->render(false, false);
    }
}
