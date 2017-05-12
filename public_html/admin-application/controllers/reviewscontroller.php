<?php
class ReviewsController extends BackendController {
	
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->canview = Admin::getAdminAccess($admin_id,PRODUCTS);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Reviews Management", Utilities::generateUrl("reviews"));
    }
	
	protected function getSearchForm() {
		global $review_status;
        $frm=new Form('frmReviewSearch','frmReviewSearch');
		$frm->setFieldsPerRow(3);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('GET');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$frm->addHiddenField('', 'product_id');
		$frm->addHiddenField('', 'shop_id');
		$frm->addHiddenField('', 'reviewed_by_id');
		$fld=$frm->addTextBox('Product', 'product_name','','product',' class="small"');
		$fld=$frm->addTextBox('Shop', 'shop_name','','shop',' class="small"');
		$fld=$frm->addTextBox('Reviewed By', 'reviewed_by','','reviewed_by',' class="small"');
		$frm->addSelectBox('Status', 'status',$review_status,'' , 'class="small"','All');
		$fld=$frm->addDateField('Date From', 'date_from', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld=$frm->addDateField('Date To', 'date_to', '', '', 'readonly="true" class="small dateTimeFld"');
 	    $fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchReviews(this); return false;');
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
	
	function listReviews($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
			$pfObj=new Productfeedbacks();
            $post = Syspage::getPostedVar();
            $page = 1;
            if (isset($post['page']) && intval($post['page']) > 0) {
                $page = intval($post['page']);
            } else {
                $post['page'] = $page;
            }
            if ($post['mode']=='search') {
                $this->set('srch', $post);
            }
           	$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
            $post['pagesize'] = $pagesize;
            $this->set('records', $pfObj->getFeedbacksWithCriteria($post));
            $this->set('pages', $pfObj->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $pfObj->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
	protected function getForm($data) {
        $frm = new Form('frmReviews');
		$frm->setExtra('class="web_form"');
		$frm->addHtml('<strong>Product</strong>', 'prod_name');
		$frm->addHtml('<strong>Shop Name</strong>', 'shop_name');
		$frm->addHtml('<strong>Reviewed By</strong>', 'user_username');
		$frm->addHtml('<strong>Reviewed On</strong>', 'reviewed_on');
		$frm->addHtml('<strong>Review Rating</strong>','', '<span class="ratings" id="rating" data-score='.$data["review_rating"].'></span>');
		$fld=$frm->addHtml('<strong>Review Message</strong>', 'review_text',html_entity_decode($data['review_text']));
		$fld->html_before_field='<span contenteditable="true" onBlur="saveToDatabase(this,'.$data["review_id"].')" onClick="showEdit(this);">';
		$fld->html_after_field='</span>';
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_listing"');
		$frm->setLeftColumnProperties('width="15%" valign="top"');
        return $frm;
    }
	
	function view($review_id) {
		if ($this->canview != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("View Review", '');
		$this->set('breadcrumb', $this->b_crumb->output());
		if ($review_id > 0) {
			$pfObj=new Productfeedbacks();
         	$review = $pfObj->getProductFeedback($review_id);
			$review['review_text']=htmlentities($review['review_text']);
			if (!$review) die("Invalid Action !!");
				$review["reviewed_on"]=Utilities::formatDate($review["reviewed_on"]);
			$frm = $this->getForm($review);
			unset($review['review_text']);
			$frm->fill($review);
		}
		$this->set('frm', $frm);
        $this->_template->render();
    }
	
	function update_ajax(){
		$post = Syspage::getPostedVar();
		$pfObj=new Productfeedbacks();
		if($pfObj->updateProductFeedback($post["id"],array('review_status' => $post["val"]))){
			Message::addMessage('Success: Status has been updated.');
			dieJsonSuccess(Message::getHtml());
		}else{
			Message::addErrorMessage($pfObj->getError());
			dieJsonError(Message::getHtml());
		}
		echo $msg;
	}
	
	function update_review(){
		$post = Syspage::getPostedVar();
		$pfObj=new Productfeedbacks();
		if($pfObj->updateProductFeedback($post["id"],array("review_text"=>$post["value"]))){
			echo $post["value"];
		}else{
			echo $pfObj->getError();
		}
	}
}