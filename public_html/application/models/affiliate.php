<?php
class Affiliate {
	
    function __construct(){
		$this->db = Syspage::getdb();
    }	
	
	function getTotalPages(){
		return $this->total_pages;
	}
	
	function getTotalRecords(){
		return $this->total_records;
	}
    function getAffiliateId() {
        return $this->affiliate_id;
    }
	
	function setAffiliateId($affiliate_id) {
        $this->affiliate_id=$affiliate_id;
    }
    
    function getError() {
        return $this->error;
    }
	
	protected function loadData() {
        $this->attributes = self::getAffiliateById($this->affiliate_id);
    }
	
    function getData() {
        return $this->attributes;
    }
    function getAttribute($attr) {
        return isset($this->attributes[$attr])?$this->attributes[$attr]:'';
    }
    function addAffiliate($data) {
        unset($data['affiliate_id']);
		$affiliate = $this->getAffiliate(array('affiliate_username'=>$data["affiliate_username"]),false);
		if(isset($affiliate['affiliate_id']) && $affiliate['affiliate_id']!='') {
			$this->error = Utilities::getLabel('M_ERROR_DUPLICATE_USERNAME')." - ".$data["affiliate_username"];
            return false;
        }
		$affiliate = $this->getAffiliate(array('affiliate_email'=>$data["affiliate_email"]),false);
		if(isset($affiliate['affiliate_id']) && $affiliate['affiliate_id']!='') {
            $this->error = Utilities::getLabel('M_ERROR_DUPLICATE_EMAIL');
            return false;
        }
		
		$assign_fields = array(
							'affiliate_password'=>Utilities::encryptPassword($data['affiliate_password']),
							'affiliate_added_on'=>date('Y-m-d H:i:s'),
							'affiliate_status'=>1,
							'affiliate_is_approved'=>Settings::getSetting("CONF_AFFILIATES_REQUIRES_APPROVAL")?0:1,
							'affiliate_ip'=>$_SERVER['REMOTE_ADDR'],
							'affiliate_is_deleted'=>0,
							'affiliate_commission'=>Settings::getSetting("CONF_AFFILIATES_COMMISSION"),
							'affiliate_code'=>uniqid(),
						);
		$record_data=array_merge($data,$assign_fields);
		$record = new TableRecord('tbl_affiliates');
		$record->assignValues($record_data);
		if($record->addNew()){
			$this->affiliate_id=$record->getId();
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		
		$emailNotObj=new Emailnotifications();	
		if (!$emailNotObj->sendWelcomeAffiliateRegistrationMail($this->affiliate_id)){
			$this->error=$emailNotObj->getError();
			return false;
		}
		
		if (Settings::getSetting("CONF_AFFILIATES_ALERT_EMAIL")){
			if (!$emailNotObj->sendNotifyAdminAffiliateRegistration($this->affiliate_id)){
				$this->error=$emailNotObj->getError();
				return false;
			}
		}
				
		return $this->affiliate_id;
	}
	
	function updateAffiliate($data){
		$affiliate_id = intval($data['affiliate_id']);
		if($affiliate_id < 1) return false;
		$record = new TableRecord('tbl_affiliates');
		
		if(isset($data['remove_profile_img']) && intval($data['remove_profile_img']) == 1){
			$data['affiliate_profile_image'] = '';
		}
		$record->assignValues($data);
		if($affiliate_id > 0 && $record->update(array('smt'=>'affiliate_id=?', 'vals'=>array($affiliate_id)))){
			$this->affiliate_id=$affiliate_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return $this->affiliate_id;
	}
	
	function login($affiliate_email_username, $affiliate_password,$passwordAlreadyEncripted=false,$is_admin_login=false){
		$srch = new SearchBase('tbl_affiliates');
		$cnd=$srch->addCondition('affiliate_email', '=', $affiliate_email_username);
		$cnd->attachCondition('affiliate_username', '=', $affiliate_email_username,'OR');
		if (!$passwordAlreadyEncripted){
			$affiliate_password = Utilities::encryptPassword($affiliate_password);
		}
		$srch->addCondition('affiliate_password', '=', $affiliate_password);
		$rs = $srch->getResultSet();
		if(!$row = $this->db->fetch($rs)) {
			$this->error = Utilities::getLabel('M_ERROR_INVALID_EMAIL_PASSWORD');
			return false;
		}
		if ( (!($row['affiliate_email'] === $affiliate_email_username || $row['affiliate_username'] === $affiliate_email_username)) || $row['affiliate_password'] !== $affiliate_password) {	
			$this->error = Utilities::getLabel('M_ERROR_INVALID_EMAIL_PASSWORD');
			return false;
		}
		
		if (($row['affiliate_is_approved'] == 0) && ($is_admin_login==false)) {			
           $this->error = Utilities::getLabel('M_ERROR_YOUR_ACCOUNT_APPROVAL_PENDING');
           return false;
        }
		
		if ($row['affiliate_is_deleted'] == 1){
           $this->error = Utilities::getLabel('M_ERROR_YOUR_ACCOUNT_IS_DELETED');
           return false;
        }
		
		if (($row['affiliate_status'] != 1) && ($is_admin_login==false)) {				
		    $this->error = Utilities::getLabel('M_ERROR_YOUR_ACCOUNT_NOT_ACTIVE');
		   return false;
		}
		$this->affiliate_id = $row["affiliate_id"];
        $this->setLoginAttributes($row);
        $this->loadData();
		return true;
	}
	
	
	function setLoginAttributes($data){
		$_SESSION['logged_affiliate']['affiliate_id'] = $data['affiliate_id'];
		$_SESSION['logged_affiliate']['name'] = $data['affiliate_name'];
		$_SESSION['logged_affiliate']['username'] = $data['affiliate_username'];
		$_SESSION['logged_affiliate']['email'] = $data['affiliate_email'];
	}
	
	function canResetPassword($affiliate_id){
		$affiliate_id = intval($affiliate_id);
		if($affiliate_id < 1) return false;
		$srch = new SearchBase('tbl_affiliate_password_reset_requests');
		$srch->addCondition('aprr_affiliate_id', '=', $affiliate_id);
		$srch->addCondition('aprr_expiry', '>', date('Y-m-d H:i:s'));
		$srch->addFld('aprr_affiliate_id');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();
		if(!$row=$this->db->fetch($rs)){
			return true;
		}
		return false;
	}
	
	function updateForgotRequest($data){
		$affiliate_id = intval($data['affiliate_id']);
		if($affiliate_id < 1 || strlen($data['reset_token']) != 25){
			return false;
		}
		$this->db->deleteRecords('tbl_affiliate_password_reset_requests', array('smt'=>'`aprr_affiliate_id`=?', 'vals'=>array($affiliate_id)));
		if($this->db->insert_from_array('tbl_affiliate_password_reset_requests', array(
				'aprr_affiliate_id'=>$affiliate_id,
				'aprr_token'=>$data['reset_token'],
				'aprr_expiry'=>$data['token_expiry']
			)
		)) return true;
		$this->error = $this->db->getError();
		return false;
	}
	
	function validateToken($affiliate_id, $token){
		$affiliate_id = intval($affiliate_id);
		if($affiliate_id < 1){
			return false;
		}
		$srch = new SearchBase('tbl_affiliate_password_reset_requests');
		$srch->addCondition('aprr_affiliate_id', '=', $affiliate_id);
		$srch->addCondition('aprr_token', '=', $token);
		$srch->addCondition('aprr_expiry', '>', date('Y-m-d H:i:s'));
		$srch->addMultipleFields(array('aprr_affiliate_id', 'aprr_token'));
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();
		if(!$row = $this->db->fetch($rs)){
			return false;
		}
		if($row['aprr_token'] !== $token){
			return false;
		}
		return $row;
	}
	
	
	function updatePassword($affiliate_id, $password){
		$affiliate_id = intval($affiliate_id);
		if($affiliate_id < 1 || strlen(trim($password)) < 1){
			return false;
		}
		if($this->db->update_from_array('tbl_affiliates', array('affiliate_password'=>Utilities::encryptPassword($password)), array('smt'=>'`affiliate_id` = ?', 'vals'=> array($affiliate_id)))){
			$this->db->deleteRecords('tbl_affiliate_password_reset_requests', array('smt'=>'`aprr_affiliate_id`=?', 'vals'=>array($affiliate_id)));
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	function savePassword($data){
		$affiliate_id = intval($data['affiliate_id']);
		if($affiliate_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		$cur_pwd = Utilities::encryptPassword($data['current_pwd']);
		$user = $this->getAffiliateById($affiliate_id);
		if($data['new_pwd']!=$data['confirm_pwd']){
			$this->error = Utilities::getLabel('M_Incorrect_new_and_confirm_password');
			return false;
		}
		if($cur_pwd != $user["affiliate_password"]){
			$this->error = Utilities::getLabel('M_Incorrect_current_password');
			return false;
		}
		$values = array(
					'affiliate_password'=>Utilities::encryptPassword($data['new_pwd'])
				);
		$whr = array(
					'smt'=>'`affiliate_id`=? AND `affiliate_password`=?',
					'vals'=>array($affiliate_id, $cur_pwd)
				);
		if($this->db->update_from_array('tbl_affiliates', $values, $whr) && $this->db->rows_affected()){
			return true;
		}
		$this->error = Utilities::getLabel('M_PASSWORD_NOT_SAVED');
		return false;
	}
	
	
    
    function updateAttributes($array) {
        return $this->db->update_from_array('tbl_affiliates', $array,
            array('smt' => 'affiliate_id=?', 'vals' => array($this->getAffiliateId())
            ) );
    }
	
	function getAffiliate($data = array(), $password = false, $chkDeleted=true){
		$srch = new SearchBase('tbl_affiliates','ta');
		$srch->joinTable('tbl_states', 'LEFT JOIN', 'ta.affiliate_state=ts.state_id', 'ts');
		$srch->joinTable('tbl_countries', 'LEFT JOIN','ta.affiliate_country=tc.country_id', 'tc');
		if($chkDeleted==true){
			$srch->addCondition('ta.affiliate_is_deleted', '=',0);
		}
		foreach($data as $key=>$val) {
		if(strval($val)=='') continue;
        switch($key) {
	        case 'affiliate_id':
			case 'id':
        	    $srch->addCondition('ta.affiliate_id', '=', intval($val));
            break;
			case 'code':
        	    $srch->addCondition('ta.affiliate_code', '=', $val);
            break;
	        case 'affiliate_email':
    	        $srch->addCondition('ta.affiliate_email', '=', $val);
            break;
			case 'affiliate_username':
            	$srch->addCondition('ta.affiliate_username', '=', $val);
            break;
			case 'affiliate_name':
    	        $srch->addCondition('ta.affiliate_name', '=', $val);
            break;
			case 'affiliate_email_username':
    	        $cndCondition=$srch->addCondition('ta.affiliate_email', '=', $val);
				$cndCondition->attachCondition('ta.affiliate_username', '=', $val,'OR');
	        break;
	        }
        }
		
		if(isset($data['get_flds']) && is_array($data['get_flds']) && sizeof($data['get_flds']) > 0){
			if($search_by_email === true && !in_array('affiliate_email', $data['get_flds'])){
				$data['get_flds'][] = 'affiliate_email';
			}
			$data['get_flds'] = array_unique($data['get_flds']);
			$srch->addMultipleFields($data['get_flds']);
		}
		$rs = $srch->getResultSet();
		if(!$row = $this->db->fetch($rs)){
			return false;
		}
		if($password === false) unset($row['affiliate_password']);
		return $row;
	}
	
	function getAffiliateBalance($affiliate_id){
		$srch = new SearchBase('tbl_affiliate_transactions', 'atxn');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('atxn.utxn_affiliate_id');
		$srch->addMultipleFields(array("SUM(autxn_credit-autxn_debit) as affBalance"));
		$srch->addCondition('atxn_affiliate_id', '=', $affiliate_id);
		$rs = $srch->getResultSet();
		if(!$row = $this->db->fetch($rs)){
			return false;
		}
		return $row["affBalance"];
	}
    function getAffiliateById($id, $add_criteria=array(),$not_in_ids=array()) {
        $add_criteria['affiliate_id'] = $id;
        $srch = self::search($add_criteria,$not_in_ids);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $sql = $srch->getQuery();
        $rs = $this->db->query($sql);
        if ($rs) return $this->db->fetch($rs);
        else return false;
    }
	
	function getAffiliates($criteria) {
		foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
		//$srch->addOrder("affiliate_status","desc");
		$srch->addOrder("affiliate_id","desc");
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return false;
		}
        return $this->db->fetch_all($rs);
    }
    static function getLoggedAffiliateId() {
        return $_SESSION['logged_affiliate']['affiliate_id'];
    }
	
    static function getLoggedAffiliateAttribute($attr) {
        return $_SESSION['logged_affiliate'][$attr];
    }
	
	
	
    static function isAffiliateLogged() {
		if(isset($_SESSION['logged_affiliate']) && is_array($_SESSION['logged_affiliate']) && isset($_SESSION['logged_affiliate']['name']) && $_SESSION['logged_affiliate']['name'] != '' && isset($_SESSION['logged_affiliate']['username']) && $_SESSION['logged_affiliate']['username'] != '' && isset($_SESSION['logged_affiliate']['email']) && filter_var($_SESSION['logged_affiliate']['email'], FILTER_VALIDATE_EMAIL)){
			return true;
		}
		
		if(isset($_COOKIE['remember_affiliate_token'])){
			$affObj = new Affiliate();
			if ($affToken=$affObj->validateRememberMeToken($_COOKIE['remember_affiliate_token'])){
				$affiliate = $affObj->getAffiliate(array('affiliate_id'=>$affToken['art_affiliate_id'], 'get_flds'=>array('affiliate_password,affiliate_username')), true);
				if($affObj->login($affiliate['affiliate_username'], $affiliate['affiliate_password'],true)){
					return true;
				}
			}
		}
        return false;
    
    }
	
	protected function cryptPwd($str){
		return crypt($str, 'NxhPwrT09zYijkhgdfg46M2fad9a5123454d05879a76f5e8b569xf2CVo8KpNxhPwr587988a98f5e');
	}
    
    function search($criteria,$not_in_ids=array()) {
		
		$orderObj=new Orders();
		$affiliate_pending_order_statuses=$orderObj->getAffiliatePendingOrderStatuses();
		
		$srch = new SearchBase('tbl_order_products', 'torp');
		$srch->joinTable('tbl_orders', 'INNER JOIN', 'tord.order_id =torp.opr_order_id', 'tord');
		$srch->addCondition('tord.order_payment_status', 'IN',array(1,2));
		if (!empty($affiliate_pending_order_statuses)){
			$srch->addCondition('opr_status', 'IN', $affiliate_pending_order_statuses );
		}
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tord.order_affiliate_id');
		$srch->addMultipleFields(array('tord.order_affiliate_id',"SUM(opr_affiliate_commission) as affPending"));
		$qry_affiliate_pending = $srch->getQuery();
		
		$srch = new SearchBase('tbl_order_products', 'torp');
		$srch->joinTable('tbl_orders', 'INNER JOIN', 'tord.order_id =torp.opr_order_id', 'tord');
		$srch->addCondition('tord.order_payment_status', 'IN',array(1,2));
		if (!empty(Settings::getSetting("CONF_COMPLETED_ORDER_STATUS"))){
			$srch->addCondition('opr_status', 'IN', (array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS"));
		}
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tord.order_affiliate_id');
		$srch->addMultipleFields(array('tord.order_affiliate_id',"SUM(opr_affiliate_commission) as affOrdersReceived"));
		$qry_affiliate_received = $srch->getQuery();
		
		$srch = new SearchBase('tbl_affiliate_transactions', 'atxn');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('atxn.atxn_affiliate_id');
		$srch->addMultipleFields(array('atxn.atxn_affiliate_id',"SUM(atxn_credit-atxn_debit) as affBalance"));
		$qry_affiliate_balance = $srch->getQuery();
		
		$srch = new SearchBase('tbl_affiliate_transactions', 'atxn');
		$srch->addCondition('atxn_withdrawal_id', '=',0);
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('atxn.atxn_affiliate_id');
		$srch->addMultipleFields(array('atxn.atxn_affiliate_id',"SUM(atxn_credit) as affRevenue"));
		$qry_affiliate_revenue = $srch->getQuery();
		
		$srch = new SearchBase('tbl_orders', 'tord');
		$srch->addCondition('tord.order_payment_status', 'IN',array(1,2));
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tord.order_affiliate_id');
		$srch->addMultipleFields(array('tord.order_affiliate_id',"COUNT(order_id) as affOrders"));
		$qry_affiliate_orders = $srch->getQuery();
		
		$srch = new SearchBase('tbl_users', 'tu');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tu.user_affiliate_id');
		$srch->addMultipleFields(array('tu.user_affiliate_id',"COUNT(user_affiliate_id) as affSignups"));
		$qry_affiliate_signups = $srch->getQuery();
		
		
        $srch = new SearchBase('tbl_affiliates', 'ta');
		$srch->joinTable('tbl_states', 'LEFT JOIN', 'ta.affiliate_state=ts.state_id', 'ts');
		$srch->joinTable('tbl_countries', 'LEFT JOIN', 'ta.affiliate_country=tc.country_id', 'tc');
		$srch->joinTable('(' . $qry_affiliate_balance . ')', 'LEFT OUTER JOIN', 'ta.affiliate_id = tqab.atxn_affiliate_id', 'tqab');
		$srch->joinTable('(' . $qry_affiliate_pending . ')', 'LEFT OUTER JOIN', 'ta.affiliate_id = tqap.order_affiliate_id', 'tqap');
		$srch->joinTable('(' . $qry_affiliate_received . ')', 'LEFT OUTER JOIN', 'ta.affiliate_id = tqar.order_affiliate_id', 'tqar');
		$srch->joinTable('(' . $qry_affiliate_revenue . ')', 'LEFT OUTER JOIN', 'ta.affiliate_id = tqarev.atxn_affiliate_id', 'tqarev');
		$srch->joinTable('(' . $qry_affiliate_signups . ')', 'LEFT OUTER JOIN', 'ta.affiliate_id = tqas.user_affiliate_id', 'tqas');
		$srch->joinTable('(' . $qry_affiliate_orders . ')', 'LEFT OUTER JOIN', 'ta.affiliate_id = tqao.order_affiliate_id', 'tqao');
		$srch->addCondition('ta.affiliate_is_deleted', '=', 0);
        $srch->addMultipleFields(array('ta.*','ts.*','tc.*','COALESCE(tqab.affBalance,0) as balance','COALESCE(tqarev.affRevenue,0) as affTotalRevenue','COALESCE(tqar.affOrdersReceived,0) as received','COALESCE(tqap.affPending,0) as pending','COALESCE(tqas.affSignups,0) as signups','COALESCE(tqao.affOrders,0) as orders'));
		
        foreach($criteria as $key=>$val) {
        switch($key) {
        case 'affiliate_id':
		case 'id':
            $srch->addCondition('ta.affiliate_id', '=', intval($val));
            break;
        case 'affiliate_email':
            $srch->addCondition('ta.affiliate_email', '=', $val);
            break;
		case 'affiliate_username':
            $srch->addCondition('ta.affiliate_username', '=', $val);
            break;
		case 'affiliate_name':
            $srch->addCondition('ta.affiliate_name', '=', $val);
            break;
		case 'date_from':
                $srch->addCondition('ta.affiliate_added_on', '>=', $val. ' 00:00:00');
                break;
		case 'date_to':
                $srch->addCondition('ta.affiliate_added_on', '<=', $val. ' 23:59:59');
                break;
		case 'minbalance':
                $srch->addHaving('balance', '>=', $val);
                break;
		case 'maxbalance':
                $srch->addHaving('balance', '<=', $val);
             	break;
		case 'affiliate_email_username':
            $cndCondition=$srch->addCondition('ta.affiliate_email', '=', $val);
			$cndCondition->attachCondition('ta.affiliate_username', '=', $val,'OR');
        break;
				
        case 'name':
			$srch->addDirectCondition('(ta.affiliate_name LIKE '. $this->db->quoteVariable('%' . $val . '%') .')');
            break;
		case 'keyword':
            $srch->addDirectCondition('(ta.affiliate_name LIKE '. $this->db->quoteVariable('%' . $val . '%') .' OR ta.affiliate_email LIKE '. $this->db->quoteVariable('%' . $val . '%') .' OR ta.affiliate_username LIKE '. $this->db->quoteVariable('%' . $val . '%') .' )');
            break;	
        case 'status':
				if ($val!="")
    		        $srch->addCondition('ta.affiliate_status', '=', intval($val));
	            break;
		case 'approved':
				if ($val!="")
    		        $srch->addCondition('ta.affiliate_is_approved', '=', intval($val));
	            break;			
		case 'pagesize':
				$srch->setPageSize($val);
				break;
			case 'page':
				$srch->setPageNumber($val);
				break;			
        case 'sort':
                if($val=='') continue;
                switch($val) {
                case 'latest':
                    $srch->addOrder('ta.affiliate_added_on', 'desc');
                    break;
                }
            break;
	        }
        }
		//die($srch->getquery());
        return $srch;
    }
    
    static function encryptPassword($text) {
        return Utilities::encryptPassword($text);
    }
	
	function getAssociativeArray($affiliate_type=null) {
		$srch = new SearchBase('tbl_affiliates', 't1');
		$srch->addCondition('t1.affiliate_is_deleted', '=', 0);
		$srch->addMultipleFields(array("affiliate_id", "affiliate_username"));
		$srch->addOrder('affiliate_username');
		$query=$srch->getquery();
        $rs = $this->db->query($query);
        return $this->db->fetch_all_assoc($rs);
    }
	
	function updateAffiliateStatus($affiliate_id,$data_update=array()) {
		$affiliate_id = intval($affiliate_id);
		if($affiliate_id < 1 || count($data_update) < 1){
			$this->error = 'Error: Invalid request!!';
			return false;
		}
		if($this->db->update_from_array('tbl_affiliates', $data_update, array('smt'=>'`affiliate_id` = ?', 'vals'=> array($affiliate_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function changeAffiliatePassword($data){
		$affiliate_id = intval($data['affiliate_id']);
		$user = $this->getAffiliate(array('id'=>$affiliate_id),false);
		if(!$user){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_affiliates', array("affiliate_password"=>Utilities::encryptPassword($data["affiliate_password"])), array('smt'=>'`affiliate_id` = ?', 'vals'=> array($affiliate_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function delete($affiliate_id){
		$affiliate_id = intval($affiliate_id);
		if($affiliate_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_affiliates', array("affiliate_is_deleted"=>1), array('smt'=>'`affiliate_id` = ?', 'vals'=> array($affiliate_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	
	
	
	
	function addLoginAttempt($username_email){
		$srch = new SearchBase('tbl_affiliate_login');
		$cnd=$srch->addCondition('alogin_username_email', '=', $username_email);
		$srch->addCondition('alogin_ip', '=',$_SERVER['REMOTE_ADDR']);
		$rs = $srch->getResultSet();
		if(!$row = $this->db->fetch($rs)) {
				if(!$this->db->insert_from_array('tbl_affiliate_login', array(
					'alogin_username_email'=>$username_email,
					'alogin_ip'=>$_SERVER['REMOTE_ADDR'],
					'alogin_total'=>1,
					'alogin_date_added'=>date('Y-m-d H:i:s'),
					'alogin_date_modified'=>date('Y-m-d H:i:s'),
					),true
				)){
					$this->error = $this->db->getError();
					return false;
				}
			}else{
				if(!$this->db->update_from_array('tbl_affiliate_login', 
							array(
								"alogin_total"=>$row['alogin_total']+1,
								"alogin_date_modified"=>date('Y-m-d H:i:s')), 
							array(
								'smt'=>'`alogin_id` = ?', 
								'vals'=> array($row['alogin_id'])),true)){
					$this->error = $this->db->getError();
					return false;
			}
		};
		
		return true;
	}
	
	public function getLoginAttempts($username_email) {
			$srch = new SearchBase('tbl_affiliate_login');
			$cnd=$srch->addCondition('alogin_username_email', '=', $username_email);
			$rs = $srch->getResultSet();
			return $row = $this->db->fetch($rs);
	}
	public function deleteLoginAttempts($username_email) {
		$this->db->deleteRecords('tbl_affiliate_login', array('smt' => 'alogin_username_email = ?', 'vals' => array($username_email)));
	}
	
	function getAffiliateLastWithdrawalRequest($affiliate_id){
		$srch = new SearchBase('tbl_affiliate_withdrawal_requests', 'tawr');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addCondition('afwithdrawal_affiliate_id', '=', $affiliate_id);
		$srch->addOrder('afwithdrawal_request_date','desc');
		$rs = $srch->getResultSet();
		if(!$row = $this->db->fetch($rs)){
			return false;
		}
		return $row;
	}
	
	
	function getWithdrawalRequestId(){
		return $this->withdraw_request_id;
	}
	
	function addAffiliateWithdrawalRequest($data){
		$affiliate_id = intval($data['affiliate_id']);
		unset($data['affiliate_id']);
		if($affiliate_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		$assign_fields = array(
						'afwithdrawal_amount'=>$data['withdrawal_amount'],
						'afwithdrawal_payment_mode'=>$data['affiliate_payment'],
						'afwithdrawal_paypal'=>$data['affiliate_paypal'],
						'afwithdrawal_cheque'=>$data['affiliate_cheque'],
						'afwithdrawal_bank_name'=>$data['affiliate_bank_name'],
						'afwithdrawal_bank_branch_number'=>$data['affiliate_bank_branch_number'],
						'afwithdrawal_bank_swift_code'=>$data['affiliate_bank_swift_code'],
						'afwithdrawal_bank_account_name'=>$data['affiliate_bank_account_name'],
						'afwithdrawal_bank_account_number'=>$data['affiliate_bank_account_number'],
						'afwithdrawal_comments'=>$data['withdrawal_comments'],
						'afwithdrawal_status'=>0,
						'afwithdrawal_request_date'=>date('Y-m-d H:i:s'),
						'afwithdrawal_affiliate_id'=>$affiliate_id,
					);
		$broken = false;
		
		if($this->db->startTransaction() && $this->db->insert_from_array('tbl_affiliate_withdrawal_requests', $assign_fields, true)){
				$this->withdraw_request_id = $this->db->insert_id();
				$formatted_request_value=str_pad($this->withdraw_request_id,6,'0',STR_PAD_LEFT);
				$formatted_request_value="#".$formatted_request_value;
				$txnArray["atxn_affiliate_id"]=$affiliate_id;
				$txnArray["atxn_debit"]=$data["withdrawal_amount"];
				$txnArray["atxn_status"]=0;
				$txnArray["atxn_description"]=Utilities::getLabel('L_Funds_Withdrawn').'. '.Utilities::getLabel('L_Request_ID').' <a href="{AffiliateWithdrawalUrl}">'.$formatted_request_value.'</a>';
				$txnArray["atxn_withdrawal_id"]=$this->withdraw_request_id;
				$aftransObj=new Affiliatetransactions();
				if ($aftransObj->addAffiliateTransaction($txnArray)){
					
					// Success Operations will go here
					
				}else{
					$this->error = $aftransObj->getError();
					$broken=true;
				}
		}else{
			$broken = true;
		}
		if($broken === false && $this->db->commitTransaction()){
			return true;
		}
		$this->db->rollbackTransaction();
		return false;
		
	}
	function deleteRememberMeToken($affiliate_id){
		$affiliate_id = intval($affiliate_id);
		if ($this->db->deleteRecords('tbl_affiliates_remember_me_tokens', array('smt'=>'`art_affiliate_id`=?', 'vals'=>array($affiliate_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function updateRememberMeToken($data){
		$affiliate_id = intval($data['affiliate_id']);
		if($affiliate_id < 1 || strlen($data['remember_token']) != 25){
			return false;
		}
		$this->db->deleteRecords('tbl_affiliates_remember_me_tokens', array('smt'=>'`art_affiliate_id`=?', 'vals'=>array($affiliate_id)));
		if($this->db->insert_from_array('tbl_affiliates_remember_me_tokens', array(
				'art_affiliate_id'=>$affiliate_id,
				'art_browser'=>$_SERVER['HTTP_USER_AGENT'],
				'art_token'=>$data['remember_token'],
				'art_expiry'=>$data['token_expiry']
			)
		)) return true;
		$this->error = $this->db->getError();
		return false;
	}
	
	function validateRememberMeToken($token){
		if(strlen($token) != 25){
			return false;
		}
		$srch = new SearchBase('tbl_affiliates_remember_me_tokens');
		$srch->addCondition('art_token', '=', $token);
		$srch->addCondition('art_browser', '=', $_SERVER['HTTP_USER_AGENT']);
		$srch->addCondition('art_expiry', '>', date('Y-m-d H:i:s'));
		$srch->addMultipleFields(array('art_affiliate_id', 'art_token'));
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();
		if(!$row = $this->db->fetch($rs)){
			return false;
		}
		if($row['art_token'] !== $token){
			return false;
		}
		return $row;
	}
	
	function getDefaultAffiliateCommission(){
		$rs = $this->db->query("SELECT afcommsetting_fees FROM tbl_affiliate_commission_settings WHERE (afcommsetting_affiliate = '0' AND afcommsetting_category = '0') and afcommsetting_is_deleted = 0 limit 0,1");	
		if($row = $this->db->fetch($rs)) {
			return $row['afcommsetting_fees'];
		}
		
	}
	
}