<?php
class LabelsController extends BackendController {
    
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,LANGUAGELABELS);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Language Labels Management", Utilities::generateUrl("labels"));
    }
	
	protected function getSearchForm() {
        $frm=new Form('frmSearchLabels','frmSearchLabels');
		$frm->setFieldsPerRow(2);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('GET');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addTextBox('Keyword', 'keyword','','',' class="small"');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
		$fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchLabels(this); return false;');
        return $frm;
    }
	
	function default_action() {
		Syspage::addJs(array('../js/admin/jquery.jeditable.mini.js'), false);
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $frm = $this->getSearchForm();
		$frm->fill(getQueryStringData());
        $this->set('frmPost', $frm);
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listLabels($page = 1) {
		
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
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
            $this->set('records', $this->Labels->getLabels($post));
            $this->set('pages', $this->Labels->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $this->Labels->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
    function form($label_id) {
		 if ($this->canview != true) {
            $this->notAuthorized();
        }
        $label_id = intval($label_id);
        $frm = $this->getForm($label_id);
        if ($label_id > 0) {
            $data = $this->Labels->getData($label_id);
            $frm->fill($data);
        }
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
					if($post['label_id'] != $label_id){
						Message::addErrorMessage('Error: Invalid Request!!');
						Utilities::redirectUser(Utilities::generateUrl('labels'));
					}else{
						if($this->Labels->addUpdateLabel($post)){
							Message::addMessage('Success: Language label details added/updated successfully.');
							Utilities::redirectUser(Utilities::generateUrl('labels'));
						}else{
							Message::addErrorMessage($this->Labels->getError());
						}
					}
			}
			$frm->fill($post);
		}
        $this->set('frm', $frm);
        $this->_template->render(true,true);
    }
    protected function getForm($id) {
        $frm = new Form('frmLabels');
		$frm->setExtra(' validator="LabelsfrmValidator" class="web_form"');
        $frm->setValidatorJsObjectName('LabelsfrmValidator');
        $frm->addHiddenField('', 'label_id');
		if ($id>0)
			$frm->addRequiredField('Key', 'label_key','', '', 'readonly class="input-xlarge"');
		else	
			$frm->addRequiredField('Key', 'label_key','', '', ' class="input-xlarge"');
		
		$fld=$frm->addTextArea('Caption English', 'label_caption_en', '', 'label_caption_en', ' class="cleditor" rows="3"');
		$fld=$frm->addTextArea('Caption Alternate Language', 'label_caption_es', '', 'label_caption_es', ' class="cleditor" rows="3"');
		$frm->addSubmitButton('&nbsp;','btn_submit','Save changes');
		$frm->setJsErrorDisplay('afterfield');
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_horizontal"');
		$frm->setLeftColumnProperties('width="20%"');
        return $frm;
    }
	
	function delete($label_id) {
		 if ($this->canview != true) {
            $this->notAuthorized();
        }
        if($this->Labels->deleteLabel($label_id)){
			Message::addMessage('Success: Record has been deleted.');
		}else{
			Message::addErrorMessage($this->Labels->getError());
		}
		
		Utilities::redirectUserReferer();
    }
	
	function updateLabelField(){
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$post = Syspage::getPostedVar();
		$this->Labels->updateLabelText($post);
		echo $post["editval"];
		/*if ($this->canview != true) {
            $this->notAuthorized();
        }
		$post = Syspage::getPostedVar();
		$post["value"] = html_entity_decode($post["value"]);
		$this->Labels->updateLabelText($post);
		echo htmlentities($post["value"]);*/
		//exit();
		
	}
	/*function updateLabelField(){
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$post = Syspage::getPostedVar();
		$this->Labels->updateLabelText($post);
		echo $post["value"];
		//exit();
	}*/	
}