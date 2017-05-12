<?php
class PpcController extends BackendController {
	
	private $admin;
    private $admin_id = 0;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
         $this->canviewPPCsettings = Admin::getAdminAccess($admin_id,PPCFEESETTINGS);
        $this->set('canviewPPCsettings', $this->canviewPPCsettings);
		
		$this->canviewpromotions = Admin::getAdminAccess($admin_id,PPC_PROMOTIONS);
        $this->set('canviewpromotions', $this->canviewpromotions);
        $this->b_crumb = new Breadcrumb();		
        
    }
	
	function default_action() {
        Utilities::redirectUser(Utilities::generateUrl('ppc','fees_settings'));
    }
	
	function fees_settings() {
		if ($this->canviewPPCsettings != true) {
            $this->notAuthorized();
        }
		$ppcObj=new Ppcfees();
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if($ppcObj->addUpdateFeesSettings($post)){
				Message::addMessage('Success: PPC fees Settings updated successfully.');
				Utilities::redirectUser(Utilities::generateUrl('ppc','fees_settings'));
			}else{
				Message::addErrorMessage($cmObj->getError());
			}
		}
		$ppcfees_settings=$ppcObj->getPPCSettings(0);
		$this->set('ppcfees_settings',$ppcfees_settings);
        $this->_template->render();
    }
	
	function remove(){
		$db = &Syspage::getdb();
		$post = Syspage::getPostedVar();
		$id = $post["id"];
		$rs = $db->update_from_array('tbl_ppc_fees_settings', array('ppcfeessetting_is_deleted' => 1), array('smt' => 'ppcfeessetting_id = ? AND ppcfeessetting_is_mandatory = ?', 'vals' => array($id,0)));
	    if (!$rs)
            dieJsonError($db->getError());
		else
			dieJsonSuccess('Success');
	}
	
	function trashed() {
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),PPCFEESETTINGS)) {
             die(Admin::getUnauthorizedMsg());
        }
		$ppcObj=new Ppcfees();
		$ppcfees_settings=$ppcObj->getPPCSettings(1);
		$this->set('ppcfees_settings',$ppcfees_settings);
        $this->_template->render();
    }
	
	protected function getSearchForm() {
        $frm=new Form('frmPromotionSearch','frmPromotionSearch');
		$frm->setFieldsPerRow(2);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('GET');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 's');
		$frm->addHiddenField('', 'user', "",'','user');
		$frm->addHiddenField('', 'mode', "search");
		$fld=$frm->addDateField('Date From', 'date_from', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld=$frm->addDateField('Date To', 'date_to', '', '', 'readonly="true" class="small dateTimeFld"');
		
		$frm->addSelectBox('Active', 'status',array(1=>"Yes",0=>"No"),'' , 'class="small"','All');
		$frm->addSelectBox('Approved', 'approved',array(1=>"Yes",0=>"No"),'' , 'class="small"','All');
		$fld_from=$frm->addTextBox('Impressions From (Number)', 'impressions_from')->requirements()->setIntPositive($val=true);
		$fld_to = $frm->addTextBox('Impressions To (Number)', 'impressions_to')->requirements()->setIntPositive($val=true);
		$fld_from=$frm->addTextBox('Clicks From (Number)', 'clicks_from')->requirements()->setIntPositive($val=true);
		$fld_to = $frm->addTextBox('Clicks To (Number)', 'clicks_to')->requirements()->setIntPositive($val=true);
		$fld=$frm->addTextBox('Promotion By', 'promotion_by');
		$frm->addSelectBox('Type', 'type',array("1"=>"Product","2"=>"Shop","3"=>"Banners"),'' , 'class="small"','All');
		$fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
		$frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchPromotions(this); return false;');
        return $frm;
    }
	
	
	
	function promotions() {
        if ($this->canviewpromotions != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("PPC Promotions", Utilities::generateUrl("brands"));
        $frm = $this->getSearchForm();
		$frm->fill(getQueryStringData());
        $this->set('frmPost', $frm);
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
	
	function listPromotions($page = 1) {
        if ($this->canviewpromotions != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
			$prmObj=new Promotions();
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
            $this->set('records', $prmObj->getPromotions($post));
            $this->set('pages', $prmObj->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $prmObj->getTotalRecords();
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
	
	function promotions_form($promotion_id) {
		if ($this->canviewpromotions != true) {
            $this->notAuthorized();
        }
		$this->b_crumb->add("PPC Promotions", Utilities::generateUrl("ppc","promotions"));
		$this->set('breadcrumb', $this->b_crumb->output());
		
		Syspage::addJs(array('../js/jquery.datetimepicker.js'), false);
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),PPC_PROMOTIONS)) {
     	   die(Admin::getUnauthorizedMsg());
	    }
		$prmObj=new Promotions();
        $promotion_id = intval($promotion_id);
        $frm = $prmObj->getPromotionForm();
        if ($promotion_id > 0) {
           	$promotion = $prmObj->getPromotion($promotion_id);
			
			if ($promotion["promotion_type"]==1){
				$userObj=new User();
				$user = $userObj->getUserById($promotion["promotion_user_id"]);
				$this->set('shop', $user['shop_id']);
				$frm->removeField($frm->getField('shop_name'));
			}elseif ($promotion["promotion_type"]==2){
				$frm->removeField($frm->getField('prod_name'));
				$frm->fill(array("shop_name"=>$promotion['shop_name'],"promotion_shop_id"=>$promotion['shop_id']));
			}elseif ($promotion["promotion_type"]==3){
				$frm->addRequiredField('<label>'.Utilities::getLabel('M_Banner_Name').'</label>', 'promotion_banner_name');
				$fld_position=$frm->addSelectBox('<label>'.Utilities::getLabel('M_Banner_Position').'</label>', 'promotion_banner_position',array('TB'=>Utilities::getLabel('M_Top'),'BB'=>Utilities::getLabel('M_Bottom')),' ',' class="small-select-box" ',Utilities::getLabel('M_Select'),'promotion_banner_position');
				$fld_position->requirements()->setRequired();
			
				$frm->addFileUpload('<label>'.Utilities::getLabel('M_Banner_File').'</label>', 'promotion_banner_file', 'promotion_banner_file')->requirements()->setRequired();
				$frm->addRequiredField('<label>'.Utilities::getLabel('M_Banner_URL').'</label>', 'promotion_banner_url');
				$frm->addSelectBox('<label>'.Utilities::getLabel('M_Banner_Target').'</label>', 'promotion_banner_target',array('_self'=>Utilities::getLabel('M_Self'),'_blank'=>Utilities::getLabel('M_Blank')))->requirements()->setRequired();
				$frm->getField('promotion_banner_file')->requirements()->setRequired(false);
				$frm->changeFieldPosition($frm->getField('promotion_banner_name')->getFormIndex(),1);
				$frm->changeFieldPosition($frm->getField('promotion_banner_position')->getFormIndex(),2);
				$frm->changeFieldPosition($frm->getField('promotion_banner_file')->getFormIndex(),3);
				$frm->changeFieldPosition($frm->getField('promotion_banner_url')->getFormIndex(),4);
				$frm->changeFieldPosition($frm->getField('promotion_banner_target')->getFormIndex(),5);
				$frm->removeField($frm->getField('prod_name'));
				$frm->removeField($frm->getField('shop_name'));
				
			}
			$frm->removeField($frm->getField('promotion_note'));
			$promotion['promotion_start_time']=date( 'H:i', strtotime($promotion['promotion_start_time']) );
			$promotion['promotion_end_time']=date( 'H:i', strtotime($promotion['promotion_end_time']) );
            $frm->fill($promotion);
        }else{
			Message::addErrorMessage('Error: Invalid Request!!');
			Utilities::redirectUser(Utilities::generateUrl('ppc','promotions'));
		}
		$post = Syspage::getPostedVar();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
		if(!$frm->validate($post)){
				Message::addErrorMessage($frm->getValidationErrors());
			}else{
						if (Utilities::isUploadedFileValidImage($_FILES['promotion_banner_file'])){
							if(!Utilities::saveImage($_FILES['promotion_banner_file']['tmp_name'],$_FILES['promotion_banner_file']['name'], $saved_banner_file, 'promotions/')){
		               			Message::addError($saved_banner_file);
	    		   			}
							$post["promotion_banner_file"]=$saved_banner_file;
    			    	}
						$arr=array_merge($post,array("promotion_user_id"=>$promotion['promotion_user_id']));
						if($prmObj->addUpdatePromotion($arr)){
								Message::addMessage(Utilities::getLabel('M_Promotion_Updated_Successfully'));	
								Utilities::redirectUser(Utilities::generateUrl('ppc', 'promotions'));
						}else{
							Message::addErrorMessage(Utilities::getLabel('M_ERROR_INVALID_REQUEST'));
						}
						
						
						
				}
			$frm->fill($post);
		}
        $this->set('frm', $frm);
        $this->_template->render(true,true);
    }
	
	
	function listPromotionClicks() {
        if ($this->canviewpromotions != true) {
            $this->notAuthorized();
        }
		$prmObj=new Promotions();
		$post = Syspage::getPostedVar();
		$promotion_id = intval($post['id']);
		$post["promotion"] = $promotion_id;
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
		$this->set('records', $prmObj->getPromotionClicks($post));
		$this->set('pages', $prmObj->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page - 1) * $pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $prmObj->getTotalRecords();
		if ($total_records < $end_record)
			$end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->_template->render(false, false);
    }
	
	function listPromotionPayments() {
        if ($this->canviewpromotions != true) {
            $this->notAuthorized();
        }
		$prmObj=new Promotions();
		$post = Syspage::getPostedVar();
		$promotion_id = intval($post['id']);
		$post["promotion"] = $promotion_id;
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
		$this->set('records', $prmObj->getPromotionPayments($post));
		$this->set('pages', $prmObj->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page - 1) * $pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $prmObj->getTotalRecords();
		if ($total_records < $end_record)
			$end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->_template->render(false, false);
    }
	
	function listPromotionLogs() {
        if ($this->canviewpromotions != true) {
            $this->notAuthorized();
        }
		$prmObj=new Promotions();
		$post = Syspage::getPostedVar();
		$promotion_id = intval($post['id']);
		$post["id"] = $promotion_id;
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
		$this->set('records', $prmObj->getPromotionLogs($post));
		$this->set('pages', $prmObj->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page - 1) * $pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $prmObj->getTotalRecords();
		if ($total_records < $end_record)
			$end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->_template->render(false, false);
        
    }
	
	
	function payments($promotion_id,$page) {
		if (!Admin::getAdminAccess($this->getLoggedAdminId(),PPC_PROMOTIONS)) {
             die(Admin::getUnauthorizedMsg());
        }
		$prmObj=new Promotions();
		$page = intval($page);
		if ($page < 1)
			$page = 1;
		$pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
		$criteria=array("promotion"=>$promotion_id);
		$criteria['pagesize'] = $pagesize;
		$criteria['page'] = $page;
		$frm=$this->getSearchLogForm();
		$get = (array) Utilities::getUrlQuery();
		if(isset($get['mode'])) {
			$frm->fill($get);
			$criteria=array_merge($criteria,$get);
		}
		$this->set('search_form', $frm);
		$this->set('arr_listing', $prmObj->getPromotionPayments($criteria));
		$this->set('pages', $prmObj->getTotalPages());
		$this->set('page', $page);
		$this->set('start_record', ($page-1)*$pagesize + 1);
		$end_record = $page * $pagesize;
		$total_records = $prmObj->getTotalRecords();
		if ($total_records < $end_record) $end_record = $total_records;
		$this->set('end_record', $end_record);
		$this->set('total_records', $total_records);
		$this->set('search_parameter',$get);
		$this->set('promotion_id',$promotion_id);
        $this->_template->render();
    }
	
	protected function getSearchLogForm() {
        $frm=new Form('frmSearchRecords','frmSearchRecords');
		$frm->setFieldsPerRow(3);
		$frm->setExtra('class="web_form last_td_nowrap"');
		$frm->setMethod('GET');
		$frm->captionInSameCell(true);
		$frm->setRequiredStarWith('not-required');
		$frm->addHiddenField('', 'mode', "search");
		$fld=$frm->addDateField('Date From', 'date_from', '', '', 'readonly="true" class="small dateTimeFld"');
		$fld=$frm->addDateField('Date To', 'date_to', '', '', 'readonly="true" class="small dateTimeFld"');
		$frm->addSubmitButton('','btn_submit','Search');
		$frm->getField("btn_submit")->html_after_field='&nbsp;&nbsp;<a href="?" class="clear_btn">Clear Search</a>';
		$frm->setJsErrorDisplay('afterfield');
        $frm->setAction($_SERVER['REQUEST_URI']);
		$frm->setTableProperties('width="100%" border="0" cellspacing="0" cellpadding="0" class="table_form_vertical"');
        return $frm;
    }
	
	
	function update_status() {
		if ($this->canviewpromotions != true) {
            dieJsonError("Unauthorized Access");
        }
		$post = Syspage::getPostedVar();
		$prmObj=new Promotions();
        $promotion_id = intval($post['id']);
        $promotion = $prmObj->getPromotion($promotion_id);
        if($promotion==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		$data_to_update = array('promotion_status'=>!$promotion['promotion_status']);
		if($prmObj->updatePromotionStatus($promotion_id,$data_to_update)){
			Message::addMessage('Success: Status has been modified successfully.');
			$arr = array('status'=>1, 'msg'=>Message::getHtml(), 'linktext'=>(($promotion['promotion_status'] == 1)?'Active':'Inactive'));
			die(convertToJson($arr));
		}else{
			Message::addErrorMessage($prmObj->getError());
			dieJsonError(Message::getHtml());
		}
    }
	
	function approve() {
		if ($this->canviewpromotions != true) {
            dieJsonError("Unauthorized Access");
        }
		$post = Syspage::getPostedVar();
		$prmObj=new Promotions();
        $promotion_id = intval($post['id']);
        $promotion = $prmObj->getPromotion($promotion_id);
        if($promotion==false) {
           Message::addErrorMessage('Error: Please perform this action on valid record.');
           dieJsonError(Message::getHtml());
        }
		$data_to_update = array('promotion_is_approved'=>1);
		if($prmObj->updatePromotionStatus($promotion_id,$data_to_update)){
			$emailNotObj=new Emailnotifications();	
			if (!$emailNotObj->promotionApproved($promotion_id)){
				Message::addErrorMessage($emailNotObj->getError());
				dieJsonError(Message::getHtml());
			}
			Message::addMessage('Success: Promotion approved successfully.');
			$arr = array('status'=>1, 'msg'=>Message::getHtml());
			die(convertToJson($arr));
		}else{
			Message::addErrorMessage($prmObj->getError());
			dieJsonError(Message::getHtml());
		}
    
	}
	
	
	function promoters_autocomplete(){
		$post = Syspage::getPostedVar();
		$json = array();
		$prmObj=new Promotions();
        $promoters=$prmObj->getDistinctPromotionMembers(urldecode($post["keyword"]));
		foreach($promoters as $ukey=>$uval){
			$json[] = array(
					'data' => $uval['user_id'],
					'value' => strip_tags(htmlentities($uval['name'], ENT_QUOTES, 'UTF-8'))
				);
		}
		$sort_order = array();
		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}
		array_multisort($sort_order, SORT_ASC, $json);
		$arr["suggestions"]=$json;
		echo json_encode($arr);
		//echo json_encode($aNew);
	}
	
	
    
}