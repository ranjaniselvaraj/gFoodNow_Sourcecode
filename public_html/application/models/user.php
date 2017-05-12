<?php
class User {
    private $user_id;
    protected $error;
	private $deleted_records=false;
	
	function __construct($id=0) {
		$this->db = Syspage::getdb();
        if (!is_numeric($id))
            $id = 0;
        $this->user_id = intval($id);
        if (!($this->user_id > 0)) {
            return;
        }
        $this->loadData();
    }
	
	function getReturnRequestMessageId(){
		return $this->refund_request_message_id;
	}
	
	function getCancellationRequestId(){
		return $this->cancellation_request;
	}
	
	function getWithdrawalRequestId(){
		return $this->withdraw_request_id;
	}
	
	function getProductFeedbackId(){
		return $this->product_feedback_id;
	}
	
	function getUserRequestId(){
		return $this->user_request_id;
	}
	
	function getTotalPages(){
		return $this->total_pages;
	}
	
	function getTotalRecords(){
		return $this->total_records;
	}
    function getUserId() {
        return $this->user_id;
    }
	function setDeletedRecords($val) {
        $this->deleted_records = $val;
    }
	function setUserId($user_id) {
        $this->user_id=$user_id;
		$this->loadData();
    }
    
    function getError() {
        return $this->error;
    }
	
	function getRedirectPage(){
		return $this->redirectPage;
	}
	
	function getBuyerSupplierTab(){
		return $this->tab;
	}
	
	protected function loadData() {
        $this->attributes = self::getUserById($this->user_id, array('with_registration_info'=>true));
    }
    function getData() {
        return $this->attributes;
    }
    function getAttribute($attr) {
        return isset($this->attributes[$attr])?$this->attributes[$attr]:'';
    }
	
	
	function getPartialRegUserId() {
        return $_SESSION['registered_supplier']['id'];
    }
	
	
	function is_valid_username($username){
		if (preg_match('/^[A-Za-z][A-Za-z0-9_.]{3,19}$/', $username))
    	    return true;
	    else
    	    return false;
	}
	
	function is_valid_password($pwd){
	    if (preg_match("/^[a-zA-Z0-9][0-9a-zA-Z_!$@#^&]{3,19}$/", $pwd))
    	    return true;
	    else
    	    return false;
	}
    function addUser($data) {
		
        unset($data['user_id']);
        if($data['user_facebook_id']=='') {
        	unset($data['user_facebook_id']);
        }
		if($data['user_googleplus_id']=='') {
        	unset($data['user_googleplus_id']);
        }
		
			
		
		$usr=new User();
		$user = $usr->getUser(array('user_username'=>$data["user_username"]),false);
		if(isset($user['user_id']) && $user['user_id']!='') {
           	$this->error = Utilities::getLabel('M_ERROR_DUPLICATE_USERNAME')." - ".$data["user_username"];
            return false;
        }
		$user = $usr->getUser(array('user_email'=>$data["user_email"]),false);
		if(isset($user['user_id']) && $user['user_id']!='') {
            $this->error = Utilities::getLabel('M_ERROR_DUPLICATE_EMAIL');
            return false;
        }
		if(isset($data['user_facebook_id']) && $data['user_facebook_id']!='') {
			$user = $usr->getUser(array('facebook_id'=>$data["user_facebook_id"]),false);
			if(isset($user['user_id']) && $user['user_id']!='') {
        	    $this->error = Utilities::getLabel('M_ERROR_DUPLICATE_FACEBOOK_ID');
            	return false;
	        }
		}
		if(isset($data['user_googleplus_id']) && $data['user_googleplus_id']!='') {
			$user = $usr->getUser(array('googleplus_id'=>$data["user_googleplus_id"]),false);
			if(isset($user['user_id']) && $user['user_id']!='') {
        	    $this->error = Utilities::getLabel('M_ERROR_DUPLICATE_GOOGLEPLUS_ID');
            	return false;
	        }
		}
		
		$user_email_verified = $data["user_email_verified"];
		if ($user_email_verified==0){
			$user_email_verified=Settings::getSetting("CONF_EMAIL_VERIFICATION_REGISTRATION")?0:1;	
		}
		
		if (!empty($_COOKIE['tracking'])){
			$afObj = new Affiliate();
			$affiliate_info = $afObj->getAffiliate(array("code"=>$_COOKIE['tracking']));
		}
		
		if ($affiliate_info) {
			$affiliate_id = $affiliate_info['affiliate_id'];
		}else{
			$affiliate_id = 0;
		}
		
		if (!empty($_COOKIE['referrer_tracking'])){
			$referrer_info = $this->getUser(array('refer_code'=>$_COOKIE['referrer_tracking'], 'get_flds'=>array('user_name,user_id')));
		}
		if ($referrer_info) {
			$referrer_id = $referrer_info['user_id'];
			$referrer_name = $referrer_info['user_name'];
		}else{
			$referrer_id = 0;
		}
					
		$assign_fields = array(
							'user_password'=>Utilities::encryptPassword($data['user_password']),
							'user_added_on'=>date('Y-m-d H:i:s'),
							'user_status'=>Settings::getSetting("CONF_ADMIN_APPROVAL_REGISTRATION")?0:1,
							'user_is_deleted'=>0,
							'user_email_verified'=>$user_email_verified,
							'user_name'=>$data['user_name'],
							'user_email'=>$data['user_email'],
							'user_username'=>$data['user_username'],
							'user_type'=>$data['user_type'],
							'user_phone'=>$data['user_phone'],
							'user_facebook_id'=>$data['user_facebook_id'],
							'user_googleplus_id'=>$data['user_googleplus_id'],
							'user_company'=>$data['user_company'],
							'user_profile'=>$data['user_profile'],
							'user_products_services'=>$data['user_products_services'],
							'user_affiliate_id'=>$affiliate_id,
							'user_referrer_id'=>$referrer_id,
							'user_referral_code'=>uniqid(),
							'user_buyer_supp_pref'=>$data['pref'],
						);
		$broken = false;
		
		
		if($this->db->startTransaction() && $this->db->insert_from_array('tbl_users', $assign_fields, true)){
				$this->user_id = $this->db->insert_id();
				if (!empty($data['user_facebook_id']) || !empty($data['user_googleplus_id'])){
					$this->db->update_from_array('tbl_users', array('user_email_verified' => 1), array('smt' => 'user_id = ?', 'vals' => array($this->user_id)));
				}
				
				$emailNotObj=new Emailnotifications();	
				if (Settings::getSetting("CONF_NOTIFY_ADMIN_REGISTRATION")){
					if (!$emailNotObj->sendNotifyAdminRegistration($this->user_id)){
						$this->error=$emailNotObj->getError();
						$broken = true;
					}
				}
				
				if (Settings::getSetting("CONF_WELCOME_EMAIL_REGISTRATION")){
					if (!$emailNotObj->sendWelcomeRegistrationMail($this->user_id)){
						$this->error=$emailNotObj->getError();
						$broken = true;
					}
				}
				
				if ($user_email_verified!=1){
					if (!$emailNotObj->sendVerificationEmail($this->user_id)){
						$this->error=$emailNotObj->getError();
						$broken = true;
					}
				}
				
				if (($affiliate_id>0) && Settings::getSetting("CONF_AFFILIATE_SIGNUP_COMMISSION")>0){
					$afftxnOBj=new Affiliatetransactions();
					$txnArray=array(
						"atxn_affiliate_id"=>$affiliate_id,
						"atxn_credit"=>Settings::getSetting("CONF_AFFILIATE_SIGNUP_COMMISSION"),
						"atxn_debit"=>0,
						"atxn_status"=>1,
						"atxn_description"=>sprintf(Utilities::getLabel('L_AFFILIATE_SIGNUP_COMMISSION_RECEIVED'),'<i>'.$data['user_name'].'</i>'),
					);
					if($aftxn_id=$afftxnOBj->addAffiliateTransaction($txnArray)){
						$emailNotificationObj=new Emailnotifications();
						$emailNotificationObj->sendAffiliateTxnNotification($aftxn_id);
					}
				}
				
				if (($referrer_id>0) && Settings::getSetting("CONF_ENABLE_REFERRER_MODULE") && Settings::getSetting("CONF_REGISTRATION_REFERRER_REWARD_POINTS")>0){
					if ((int)Settings::getSetting("CONF_REGISTRATION_REFERRER_REWARD_POINTS_VALIDITY")>0){
						$reward_expiry_date = date('Y-m-d', strtotime('+'.(int)Settings::getSetting("CONF_REGISTRATION_REFERRER_REWARD_POINTS_VALIDITY").' days'));
					}
					
					$rewardObj=new Rewards();
					$rewardArray=array(
						"urp_user_id"=>$referrer_id,
						"urp_points"=>Settings::getSetting("CONF_REGISTRATION_REFERRER_REWARD_POINTS"),
						"urp_date_expiry"=>$reward_expiry_date,
						"urp_description"=>sprintf(Utilities::getLabel('L_REFEREE_SIGNUP_REWARD_POINTS_RECEIVED'),'<i>'.$data['user_name'].'</i>'),
					);
					if($reward_point_id = $rewardObj->addRewardPoints($rewardArray)){
						$emailNotificationObj=new Emailnotifications();
						$emailNotificationObj->sendRewardPointsNotification($reward_point_id);
					}else{
						$this->error=$rewardObj->getError();
						$broken = true;
					}
				}
				
				if (($referrer_id>0) && Settings::getSetting("CONF_ENABLE_REFERRER_MODULE") && Settings::getSetting("CONF_REGISTRATION_REFEREE_REWARD_POINTS")>0){
					if ((int)Settings::getSetting("CONF_REGISTRATION_REFEREE_REWARD_POINTS_VALIDITY")>0){
						$reward_referee_expiry_date = date('Y-m-d', strtotime('+'.(int)Settings::getSetting("CONF_REGISTRATION_REFEREE_REWARD_POINTS_VALIDITY").' days'));
					}
					
					$rewardObj=new Rewards();
					$rewardArray=array(
						"urp_user_id"=>$this->user_id,
						"urp_points"=>Settings::getSetting("CONF_REGISTRATION_REFEREE_REWARD_POINTS"),
						"urp_date_expiry"=>$reward_referee_expiry_date,
						"urp_description"=>sprintf(Utilities::getLabel('L_REFERRAL_SIGNUP_REWARD_POINTS_RECEIVED'),'<i>'.$referrer_name.'</i>'),
					);
					if($reward_point_id = $rewardObj->addRewardPoints($rewardArray)){
						$emailNotificationObj=new Emailnotifications();
						$emailNotificationObj->sendRewardPointsNotification($reward_point_id);
					}else{
						$this->error=$rewardObj->getError();
						$broken = true;
					}
				}
				
			}else{
			$broken = true;
		}
		if($broken === false && $this->db->commitTransaction()){
			$this->loadData();
			return true;
		}
		$this->db->rollbackTransaction();
		return false;
	}
    function confirmEmail($code) {
        $arr_code = explode('.', $code, 2);
        if (!is_numeric($arr_code[0])) {
            $this->error = Utilities::getLabel('M_ERROR_INVALID_CODE');
            return false;
        }
        $user_id = intval($arr_code[0]);
        $rs =$this->db->query("SELECT * FROM tbl_users tu inner join tbl_user_email_verification_codes tuevc on tu.user_id=tuevc.uevc_user_id WHERE uevc_user_id = '" . $user_id."'");
        $row =$this->db->fetch($rs);
        if ($row['uevc_user_id'] != $user_id || $row['uevc_code'] != $arr_code[1]) {
            $this->error = Utilities::getLabel('M_ERROR_INVALID_CODE');
            return false;
        }
		if ($row["uevc_email"]!=""){
			$usr=new User();
			$row_duplicate = $usr->getUser(array('user_email'=>$row["uevc_email"]));
			if ($row_duplicate){
				$this->error = Utilities::getLabel('M_ERROR_DUPLICATE_EMAIL');
				return false;
			}
			$arr_data["user_email"]=$row["uevc_email"];
		}
		
		$arr_data["user_email_verified"] = 1;
        if (!$this->db->update_from_array('tbl_users', $arr_data, array('smt' => 'user_id = ?', 'vals' => array($user_id)))) {
            $this->error =$this->db->getError();
            return false;
        }
        $this->db->deleteRecords('tbl_user_email_verification_codes', array('smt' => 'uevc_user_id = ?', 'vals' => array($user_id)));
        $this->user_id = $user_id;
        $this->loadData();
		if ($this->login($this->attributes['user_email'], $this->attributes['user_password'], true)) {
			return true;
        } else {
			$this->error=$this->getError();
			return false;
        }
        return true;
    }
	
	function login($user_email_username, $user_password,$passwordAlreadyEncripted=false,$is_admin_login=false){
		global $conf_arr_buyer_seller_advertiser_types;
		$srch = new SearchBase('tbl_users');
		$cnd=$srch->addCondition('user_email', '=', $user_email_username);
		$cnd->attachCondition('user_username', '=', $user_email_username,'OR');
		if (!$passwordAlreadyEncripted){
			$user_password = Utilities::encryptPassword($user_password);
		}
		$srch->addCondition('user_password', '=', $user_password);
		$srch->addOrder('user_id','desc');
		$rs = $srch->getResultSet();
		if(!$row = $this->db->fetch($rs)) {
			$this->error = Utilities::getLabel('M_ERROR_INVALID_EMAIL_PASSWORD');
			return false;
		}
		if ( (!($row['user_email'] === $user_email_username || $row['user_username'] === $user_email_username)) || $row['user_password'] !== $user_password) {	
			$this->error = Utilities::getLabel('M_ERROR_INVALID_EMAIL_PASSWORD');
			return false;
		}
		
		if (($row['user_email_verified'] != 1) && ($is_admin_login==false)) {
			$this->error = sprintf(Utilities::getLabel('M_ERROR_YOU_HAVE_NOT_VERIFIED_EMAIL'),'<a href="'.Utilities::generateUrl('user', 'resend_verification_code').'" class="greenAnchorLink">'.Utilities::getLabel('M_Click_here').'</a>');
            return false;
        }
		
		
		if ($row['user_type']==0) {
			$_SESSION['registered_supplier']['id']=$row['user_id'];
			$this->error = sprintf(Utilities::getLabel('M_Error_complete_registration'),'<a href="'.Utilities::generateUrl('supplier', 'profile_activation').'" class="greenAnchorLink">'.Utilities::getLabel('M_Click_here').'</a>');
            return false;
        }
		
		if (!in_array($row['user_type'],array_keys($conf_arr_buyer_seller_advertiser_types))) {	
            $this->error = Utilities::getLabel('M_ERROR_NOT_BUYER_TYPE');
            return false;
        }
		
		if ($row['user_is_deleted'] == 1){
           $this->error = Utilities::getLabel('M_ERROR_YOUR_ACCOUNT_IS_DELETED');
           return false;
        }
		
		
		if (($row['user_status'] != 1) && ($is_admin_login==false)){
		    $this->error = Utilities::getLabel('M_ERROR_YOUR_ACCOUNT_NOT_ACTIVE');
		   return false;
		}
		$this->user_id = $row["user_id"];
        $this->setLoginAttributes($row);
		$this->setCartAttributes($row["user_id"]);
		$this->setUserSmartRecommendations($this->user_id);
        $this->loadData();
		return true;
	}
	
	function setMobileAppToken($user_id){
		$generatedToken = substr(md5(rand(1, 99999) . microtime()), 1, 25);
		if($this->db->update_from_array('tbl_users', array('user_app_token'=>hash('sha256', $generatedToken)), array('smt'=>'`user_id` = ?', 'vals'=> array($user_id)))){
			$this->user_id = $user_id;
			$this->loadData();
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function setUserSmartRecommendations($user_id){
		$user_id = intval($user_id);
		if($user_id < 1){
			return false;
		}
		
		$srch = new SearchBase('tbl_smart_user_activity_browsing');
		$srch->addCondition('uab_session_id', '=', session_id());
		$srch->addCondition('uab_user_id', '=', 0);
		$rs = $srch->getResultSet();
		$record = new TableRecord('tbl_smart_user_activity_browsing');
		while ($row=$this->db->fetch($rs)){
			$this->db->deleteRecords('tbl_smart_user_activity_browsing', array('smt' => 'uab_id = ? ', 'vals' => array($row['ab_id'])));
			$record->assignValues(array_merge($row,array('uab_user_id'=>$user_id)));
			$record->addNew(array('IGNORE'),array('uab_last_action_datetime'=>date('Y-m-d H:i:s')));
		}
		
		
		return true;
	}
	
	function setCartAttributes($user_id){
		$cartObj=new Cart($user_id);
		$rs = $this->db->query("SELECT * FROM `tbl_user_cart` WHERE cart_user_id = '" . session_id() . "'");
		if ($row=$this->db->fetch($rs)){
			$cart_info=unserialize($row["cart_details"]);
			foreach($cart_info as $key=>$quantity){
				$product = unserialize(base64_decode($key));
				$product_id = $product['product_id'];
				$cartObj->add($product_id, $quantity, $product['option']);
			}
			$this->db->deleteRecords('tbl_user_cart', array('smt'=>'`cart_user_id`=?', 'vals'=>array(session_id())));
		}
		$cartObj->updateUserCart();
	}
	
	function setLoginAttributes($data){
		$_SESSION['logged_user']['user_id'] = $data['user_id'];
		$_SESSION['logged_user']['name'] = $data['user_name'];
		$_SESSION['logged_user']['username'] = $data['user_username'];
		$_SESSION['logged_user']['email'] = $data['user_email'];
		$_SESSION['logged_user']['type'] = $data['user_type'];
	}
	
	function canResetPassword($user_id){
		$user_id = intval($user_id);
		if($user_id < 1) return false;
		$srch = new SearchBase('tbl_user_password_reset_requests');
		$srch->addCondition('uprr_user_id', '=', $user_id);
		$srch->addCondition('uprr_expiry', '>', date('Y-m-d H:i:s'));
		$srch->addFld('uprr_user_id');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();
		if(!$row=$this->db->fetch($rs)){
			return true;
		}
		return false;
	}
	
	function updateForgotRequest($data){
		$user_id = intval($data['user_id']);
		if($user_id < 1 || strlen($data['reset_token']) != 25){
			return false;
		}
		$this->db->deleteRecords('tbl_user_password_reset_requests', array('smt'=>'`uprr_user_id`=?', 'vals'=>array($user_id)));
		if($this->db->insert_from_array('tbl_user_password_reset_requests', array(
				'uprr_user_id'=>$user_id,
				'uprr_token'=>$data['reset_token'],
				'uprr_expiry'=>$data['token_expiry']
			)
		)) return true;
		$this->error = $this->db->getError();
		return false;
	}
	
	function validateToken($user_id, $token){
		$user_id = intval($user_id);
		if($user_id < 1){
			return false;
		}
		$srch = new SearchBase('tbl_user_password_reset_requests');
		$srch->addCondition('uprr_user_id', '=', $user_id);
		$srch->addCondition('uprr_token', '=', $token);
		$srch->addCondition('uprr_expiry', '>', date('Y-m-d H:i:s'));
		$srch->addMultipleFields(array('uprr_user_id', 'uprr_token'));
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();
		if(!$row = $this->db->fetch($rs)){
			return false;
		}
		if($row['uprr_token'] !== $token){
			return false;
		}
		return $row;
	}
	
	function validateAPITempToken($user_id, $token){
		$user_id = intval($user_id);
		if($user_id < 1){
			return false;
		}
		$srch = new SearchBase('tbl_user_temp_token_requests');
		$srch->addCondition('uttr_user_id', '=', $user_id);
		$srch->addCondition('uttr_token', '=', $token);
		$srch->addCondition('uttr_expiry', '>', date('Y-m-d H:i:s'));
		$srch->addMultipleFields(array('uttr_user_id', 'uttr_token'));
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();
		if(!$row = $this->db->fetch($rs)){
			return false;
		}
		if($row['uttr_token'] !== $token){
			return false;
		}
		return $row;
	}
	
	function deleteUserAPITempToken($user_id){
		$user_id = intval($user_id);
		if($user_id < 1){
			return false;
		}
		$this->db->deleteRecords('tbl_user_temp_token_requests', array('smt'=>'`uttr_user_id`=?', 'vals'=>array($user_id)));
		return true;
		
	}
	
	function updatePassword($user_id, $password){
		$user_id = intval($user_id);
		if($user_id < 1 || strlen(trim($password)) < 1){
			return false;
		}
		if($this->db->update_from_array('tbl_users', array('user_password'=>Utilities::encryptPassword($password)), array('smt'=>'`user_id` = ?', 'vals'=> array($user_id)))){
			$this->db->deleteRecords('tbl_user_password_reset_requests', array('smt'=>'`uprr_user_id`=?', 'vals'=>array($user_id)));
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	function savePassword($data){
		$user_id = intval($data['user_id']);
		if($user_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		$cur_pwd = Utilities::encryptPassword($data['current_pwd']);
		$user = $this->getUserById($user_id);
		if($data['new_pwd']!=$data['confirm_pwd']){
			$this->error = Utilities::getLabel('M_Incorrect_new_and_confirm_password');
			return false;
		}
		if($cur_pwd != $user["user_password"]){
			$this->error = Utilities::getLabel('M_Incorrect_current_password');
			return false;
		}
		$values = array(
					'user_password'=>Utilities::encryptPassword($data['new_pwd'])
				);
		$whr = array(
					'smt'=>'`user_id`=? AND `user_password`=?',
					'vals'=>array($user_id, $cur_pwd)
				);
		if($this->db->update_from_array('tbl_users', $values, $whr) && $this->db->rows_affected()){
			return true;
		}
		$this->error = Utilities::getLabel('M_PASSWORD_NOT_SAVED');
		return false;
	}
	
	
	function changeEmail($data) {
		$user_id = intval($data['user_id']);
		if($user_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		$cur_pwd = Utilities::encryptPassword($data['current_password']);
		$user = $this->getUserById($user_id);
		if($cur_pwd != $user["user_password"]){
			$this->error = Utilities::getLabel('M_Incorrect_current_password');
			return false;
		}
		$this->user_id=$user["user_id"];
		$emailNotObj=new Emailnotifications();
		if (!$emailNotObj->sendVerificationEmail($this->user_id,$data["user_email"])){
			$this->error = Utilities::getLabel('M_ERROR_CANNOT_RESEND_VERIFICATION_EMAIL');
			return false;
		}
		return true;	
    }
    
    function updateAttributes($array) {
        return $this->db->update_from_array('tbl_users', $array,
            array('smt' => 'user_id=?', 'vals' => array($this->getUserId())
            ) );
    }
	
	function getUser($data = array(), $password = false, $chkDeleted=true){
		$srch = new SearchBase('tbl_users','tu');
		$srch->joinTable('tbl_states', 'LEFT JOIN', 'tu.user_state_county=ts.state_id', 'ts');
		$srch->joinTable('tbl_countries', 'LEFT JOIN','tu.user_country=tc.country_id', 'tc');
		if($chkDeleted==true){
			$srch->addCondition('tu.user_is_deleted', '=',0);
		}
		foreach($data as $key=>$val) {
		if(strval($val)=='') continue;
        switch($key) {
	        case 'user_id':
			case 'id':
        	    $srch->addCondition('tu.user_id', '=', intval($val));
            break;
	        case 'user_email':
    	        $srch->addCondition('tu.user_email', '=', $val);
            break;
			case 'user_username':
            	$srch->addCondition('tu.user_username', '=', $val);
            break;
			case 'user_name':
    	        $srch->addCondition('tu.user_name', '=', $val);
            break;
			case 'user_email_username':
    	        $cndCondition=$srch->addCondition('tu.user_email', '=', $val);
				$cndCondition->attachCondition('tu.user_username', '=', $val,'OR');
	        break;
			case 'facebook_id':
    	        $srch->addCondition('tu.user_facebook_id', '=', $val);
            break;
			case 'googleplus_id':
            	$srch->addCondition('tu.user_googleplus_id', '=', $val);
	            break;		
			case 'token':
            	$srch->addCondition('tu.user_app_token', '=', $val);
	            break;
			case 'refer_code':
            	$srch->addCondition('tu.user_referral_code', '=', $val);
	            break;				
	        }
        }
		
		if(isset($data['get_flds']) && is_array($data['get_flds']) && sizeof($data['get_flds']) > 0){
			if($search_by_email === true && !in_array('user_email', $data['get_flds'])){
				$data['get_flds'][] = 'user_email';
			}
			$data['get_flds'] = array_unique($data['get_flds']);
			$srch->addMultipleFields($data['get_flds']);
		}
		$rs = $srch->getResultSet();
		if(!$row = $this->db->fetch($rs)){
			return false;
		}
		if($password === false) unset($row['user_password']);
		return $row;
	}
	
	function getUserBalance($user_id){
		$srch = new SearchBase('tbl_user_transactions', 'txn');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('txn.utxn_user_id');
		$srch->addMultipleFields(array("SUM(utxn_credit-utxn_debit) as userBalance"));
		$srch->addCondition('utxn_user_id', '=', $user_id);
		$rs = $srch->getResultSet();
		if(!$row = $this->db->fetch($rs)){
			return 0;
		}
		return $row["userBalance"];
	}
    function getUserById($id, $add_criteria=array(),$not_in_ids=array()) {
        $add_criteria['user_id'] = $id;
        $srch = self::search($add_criteria,$not_in_ids);
        $srch->doNotLimitRecords(true);
        $srch->doNotCalculateRecords(true);
        $sql = $srch->getQuery();
        $rs = $this->db->query($sql);
        if ($rs) return $this->db->fetch($rs);
        else return false;
    }
	
	function getUsers($criteria) {
		foreach($criteria as $key=>$val) {
        	if (!is_array($val)){
				if(strval($val)=='') continue;
					$add_criteria[$key] = $val;
			}else{
				$add_criteria[$key] = $val;
			}
		}
        $srch = self::search($add_criteria);
		//$srch->addOrder("user_status","desc");
		$srch->addOrder("user_id","desc");
		//die($srch->getquery());
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return false;
		}
        return $this->db->fetch_all($rs);
    }
    static function getLoggedUserId() {
        return isset($_SESSION['logged_user']['user_id'])?$_SESSION['logged_user']['user_id']:0;
    }
	
    static function getLoggedUserAttribute($attr) {
        return isset($_SESSION['logged_user'][$attr])?$_SESSION['logged_user'][$attr]:'';
    }
	
	protected static function getUserTypes(){
		global $conf_arr_buyer_seller_advertiser_types;
		return array_keys($conf_arr_buyer_seller_advertiser_types);
	}
	
	static function isBuyerLogged() {
		global $conf_arr_buyer_types;
		if(isset($_SESSION['logged_user']) && is_array($_SESSION['logged_user']) && isset($_SESSION['logged_user']['name']) && isset($_SESSION['logged_user']['username']) && $_SESSION['logged_user']['username'] != '' && isset($_SESSION['logged_user']['email']) && filter_var($_SESSION['logged_user']['email'], FILTER_VALIDATE_EMAIL) && isset($_SESSION['logged_user']['type']) && in_array($_SESSION['logged_user']['type'], $conf_arr_buyer_types)){
			return true;
			
		}
    }
	
    static function isUserLogged() {
		if(isset($_SESSION['logged_user']) && is_array($_SESSION['logged_user']) && isset($_SESSION['logged_user']['name']) && isset($_SESSION['logged_user']['username']) && $_SESSION['logged_user']['username'] != '' && isset($_SESSION['logged_user']['email']) && filter_var($_SESSION['logged_user']['email'], FILTER_VALIDATE_EMAIL) && isset($_SESSION['logged_user']['type']) && in_array($_SESSION['logged_user']['type'], (self::getUserTypes()))){
			return true;
			
		}
		if(isset($_COOKIE['remembertoken'])){
			$usr = new User();
			if ($userToken=$usr->validateRememberMeToken($_COOKIE['remembertoken'])){
				$user = $usr->getUser(array('user_id'=>$userToken['urt_user_id'], 'get_flds'=>array('user_password,user_username')), true);
				if($usr->login($user['user_username'], $user['user_password'],true)){
					return true;
				}
			}
		}
        return false;
		
    }
	
	protected function cryptPwd($str){
		return crypt($str, 'NxhPwrR07zYijkhgdfg46M2fad9a5189454d05879a76f5e8b569xf2CVo6JpNxhPwr587988a76f5e');
	}
    
    function search($criteria,$not_in_ids=array()) {
		$srch = new SearchBase('tbl_user_favourite_products', 'tfp');
		$srch->joinTable('tbl_products', 'INNER JOIN', 'tfp.ufp_prod_id=t2.prod_id and prod_is_deleted=0 and prod_is_expired=0 and prod_status=1', 't2');
		$srch->joinTable('tbl_shops', 'INNER JOIN', 't2.prod_shop=t3.shop_id and shop_is_deleted=0 and shop_status=1 and shop_vendor_display_status=1', 't3');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tfp.ufp_user_id');
		$srch->addMultipleFields(array('tfp.ufp_user_id',"count(ufp_prod_id) as favItems"));
		$qry_fav_items = $srch->getQuery();
		
		$srch = new SearchBase('tbl_user_favourite_shops', 'tfs');
		$srch->joinTable('tbl_shops', 'INNER JOIN', 'tfs.ufs_shop_id=t2.shop_id and shop_is_deleted=0 and shop_status=1 and shop_vendor_display_status=1', 't2');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tfs.ufs_user_id');
		$srch->addMultipleFields(array('tfs.ufs_user_id',"count(ufs_shop_id) as favShops"));
		$qry_fav_shops = $srch->getQuery();
		
		$srch = new SearchBase('tbl_thread_messages', 'tmsgs');
		$srch->addCondition('tmsgs.message_is_unread', '=', 1);
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tmsgs.message_to');
		$srch->addMultipleFields(array('tmsgs.message_to',"count(message_id) as unreadMessages"));
		$qry_unread_messages = $srch->getQuery();
		
		$srch = new SearchBase('tbl_order_products', 'tx');
		$srch->joinTable('tbl_orders', 'LEFT OUTER JOIN', 'tx.opr_order_id = `tord`.order_id', 'tord');
		$srch->addCondition('tord.order_payment_status', 'IN',array(1,2));
		$srch->addCondition('`tx`.opr_status', 'IN', (array)Settings::getSetting("CONF_PURCHASE_ORDER_STATUS"));
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tx.opr_order_id');
		$srch->addMultipleFields(array('tx.opr_order_id',"SUM(opr_qty-opr_refund_qty) as totQtys","SUM(opr_net_charged-opr_refund_amount) as totUserPurchase"));
		$qry_order_product_qty = $srch->getQuery();
		//die($qry_order_product_qty);
		
		$srch = new SearchBase('tbl_orders', 'tord');
		$srch->addCondition('tord.order_payment_status', 'IN',array(1,2));
		$srch->joinTable('(' . $qry_order_product_qty . ')', 'LEFT OUTER JOIN', 'tord.order_id = top.opr_order_id', 'top');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tord.order_user_id');
		$srch->addMultipleFields(array('tord.order_user_id',"count(order_id) as totUserOrders","SUM(totQtys) as totUserOrderQtys","SUM(totUserPurchase) as totUserOrderPurchases"));
		$qry_order_qty = $srch->getQuery();
		
		$srch = new SearchBase('tbl_user_transactions', 'txn');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('txn.utxn_user_id');
		$srch->addMultipleFields(array('txn.utxn_user_id',"SUM(utxn_credit-utxn_debit) as userBalance"));
		$qry_user_balance = $srch->getQuery();
		//die($qry_user_balance);
		
		/*$srch = new SearchBase('tbl_user_reward_points', 'turp');
		$srch->addDirectCondition("(turp.urp_date_expiry >= CURRENT_DATE() OR turp.urp_date_expiry = '0000-00-00')");
		//$srch->addCondition('turp.urp_is_expired', '=', 0);
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('turp.urp_user_id');
		$srch->addMultipleFields(array('turp.urp_user_id',"SUM(urp_points) as userRewardPoints"));
		$qry_user_reward_points = $srch->getQuery();*/
		
		$srch = new SearchBase('tbl_user_reward_point_breakup', 'turpb');
		$srch->joinTable('tbl_user_reward_points', 'INNER JOIN', 'turpb.urpdetail_urp_reward_id = turp.urp_reward_id', 'turp');
		$srch->addDirectCondition("( turpb.urpdetail_expiry >= CURRENT_DATE() OR turpb.urpdetail_expiry = '0000-00-00' )");
		//$srch->addDirectCondition("( turpb.urpdetail_expiry >= '2017-04-05' OR turpb.urpdetail_expiry = '0000-00-00' )");
		$srch->addCondition('turpb.urpdetail_used', '=', 0);
		$srch->addCondition('turpb.urpdetail_points', '>', 0);
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('turp.urp_user_id');
		$srch->addMultipleFields(array('turp.urp_user_id',"SUM(turpb.urpdetail_points) as userRewardPoints"));
		$qry_user_reward_points = $srch->getQuery();
		
		$pObj=new Products(false);
		$pObj->addMultipleFields(array('ts.shop_user_id',"count(prod_id) as publishedItems"));
		$pObj->addGroupBy('ts.shop_user_id');
		$pObj->doNotCalculateRecords();
		$pObj->doNotLimitRecords();	
		$qry_published_items = $pObj->getQuery();
		
		$srch = new SearchBase('tbl_order_products', '`top`');
		$srch->joinTable('tbl_orders', 'INNER JOIN', '`top`.opr_order_id=`to`.order_id', '`to`');
		$srch->joinTable('tbl_orders_status', 'INNER JOIN', '`top`.opr_status = `tos`.orders_status_id', '`tos`');
		$srch->joinTable('tbl_shops', 'INNER JOIN', '`top`.opr_product_shop=`ts`.shop_id', '`ts`');
		$srch->addCondition('`top`.opr_status', 'IN', (array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS"));
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('`ts`.shop_user_id');
		$srch->addMultipleFields(array('`top`.opr_product_shop','`ts`.shop_user_id',"COUNT(distinct opr_order_id) as totVendorOrders","SUM(opr_qty-opr_refund_qty) as totSoldQty","SUM(((opr_customer_buying_price+opr_customization_price))*opr_qty - opr_refund_amount) as totalVendorSales"));
		$qry_vendor_orders = $srch->getQuery();
		//die($qry_vendor_orders);
		
		
        $srch = new SearchBase('tbl_users', 'tu');
		$srch->joinTable('tbl_shops', 'LEFT JOIN', 'tu.user_id=tsh.shop_user_id', 'tsh');
		$srch->joinTable('tbl_states', 'LEFT JOIN', 'tu.user_state_county=ts.state_id', 'ts');
		$srch->joinTable('tbl_countries', 'LEFT JOIN', 'tu.user_country=tc.country_id', 'tc');
		
		$srch->joinTable('tbl_user_bank_details', 'LEFT JOIN', 'tu.user_id=tubd.ub_user_id', 'tubd');
		//$srch->joinTable('tbl_banks', 'LEFT JOIN', 'tubd.ub_bank_name=tb.bank_id', 'tb');
		//$srch->joinTable('tbl_user_refund_address', 'LEFT JOIN', 'tu.user_id=tura.ura_user_id', 'tura');
		$srch->joinTable('tbl_states', 'LEFT JOIN', 'tsh.shop_state=tsret.state_id', 'tsret');
		$srch->joinTable('tbl_countries', 'LEFT JOIN', 'tsh.shop_country=tcret.country_id', 'tcret');
		$srch->joinTable('(' . $qry_fav_items . ')', 'LEFT OUTER JOIN', 'tu.user_id = tqfi.ufp_user_id', 'tqfi');
		$srch->joinTable('(' . $qry_fav_shops . ')', 'LEFT OUTER JOIN', 'tu.user_id = tqfs.ufs_user_id', 'tqfs');
		$srch->joinTable('(' . $qry_unread_messages . ')', 'LEFT OUTER JOIN', 'tu.user_id = tqum.message_to', 'tqum');
		$srch->joinTable('(' . $qry_order_qty . ')', 'LEFT OUTER JOIN', 'tu.user_id = tqoq.order_user_id', 'tqoq');
		$srch->joinTable('(' . $qry_user_balance . ')', 'LEFT OUTER JOIN', 'tu.user_id = tqub.utxn_user_id', 'tqub');
		$srch->joinTable('(' . $qry_published_items . ')', 'LEFT OUTER JOIN', 'tu.user_id = tqpi.shop_user_id', 'tqpi');
		$srch->joinTable('(' . $qry_vendor_orders . ')', 'LEFT OUTER JOIN', 'tu.user_id = tqvo.shop_user_id', 'tqvo');
		$srch->joinTable('(' . $qry_user_reward_points . ')', 'LEFT OUTER JOIN', 'tu.user_id = tqurp.urp_user_id', 'tqurp');
		
		$srch->joinTable('tbl_affiliates', 'LEFT JOIN', 'tu.user_affiliate_id=ta.affiliate_id', 'ta');
		
		if ($this->deleted_records===false)
			$srch->addCondition('tu.user_is_deleted', '=', 0);
		$srch->addMultipleFields(array('tu.*','tsh.*','ts.*','tc.*','tubd.*','ta.affiliate_name','COALESCE(tqvo.totVendorOrders,0) as totVendorOrders','COALESCE(tqvo.totSoldQty,0) as totSoldQty','COALESCE(tqvo.totalVendorSales,0) as totalVendorSales','tsret.state_name as shop_state_name','tcret.country_name as shop_country_name','user_name user_full_name','COALESCE(tqfi.favItems,0) as favItems','COALESCE(tqfs.favShops,0) as favShops','COALESCE(tqum.unreadMessages,0) as unreadMessages','COALESCE(tqoq.totUserOrders,0) as totUserOrders','COALESCE(tqoq.totUserOrderQtys,0) as totUserOrderQtys','COALESCE(tqoq.totUserOrderPurchases,0) as totUserOrderPurchases','COALESCE(tqub.userBalance,0) as totUserBalance','COALESCE(tqpi.publishedItems,0) as publishedItems','COALESCE(tqurp.userRewardPoints,0) as totUserRewardPoints'));
		if (count($not_in_ids)>0)
		$srch->addCondition('tu.user_id', 'NOT IN', ($not_in_ids));
        foreach($criteria as $key=>$val) {
        //if(strval($val)=='') continue;
        switch($key) {
        case 'user_id':
		case 'id':
            $srch->addCondition('tu.user_id', '=', intval($val));
            break;
        case 'user_email':
            $srch->addCondition('tu.user_email', '=', $val);
            break;
		case 'user_username':
            $srch->addCondition('tu.user_username', '=', $val);
            break;
		case 'user_name':
            $srch->addCondition('tu.user_name', '=', $val);
            break;
			
		case 'is_deleted':
            $srch->addCondition('tu.user_is_deleted', '=', $val);
            break;	
		case 'date_from':
                $srch->addCondition('tu.user_added_on', '>=', $val. ' 00:00:00');
                break;
		case 'date_to':
                $srch->addCondition('tu.user_added_on', '<=', $val. ' 23:59:59');
                break;
		case 'minbalance':
                $srch->addHaving('totUserBalance', '>=', $val);
                break;
		case 'maxbalance':
                $srch->addHaving('totUserBalance', '<=', $val);
             	break;
		case 'user_email_username':
            $cndCondition=$srch->addCondition('tu.user_email', '=', $val);
			$cndCondition->attachCondition('tu.user_username', '=', $val,'OR');
        break;
				
		case 'facebook_id':
            $srch->addCondition('tu.user_facebook_id', '=', $val);
            break;
		case 'googleplus_id':
            $srch->addCondition('tu.user_googleplus_id', '=', $val);
            break;		
		 case 'type':
		 	if (is_array($val)){
    	        	$srch->addCondition('tu.user_type', 'IN', $val);
				}
			else
				$srch->addCondition('tu.user_type', '=', $val);
            break;	
        case 'name':
            //$srch->addDirectCondition('tu.user_name LIKE \'%'.mysql_escape_string($val).'%\'');
			$srch->addDirectCondition('(tu.user_name LIKE '. $this->db->quoteVariable('%' . $val . '%') .')');
            break;
		case 'keyword':
            $srch->addDirectCondition('(tu.user_name LIKE '. $this->db->quoteVariable('%' . $val . '%') .' OR tu.user_email LIKE '. $this->db->quoteVariable('%' . $val . '%') .' OR tu.user_username LIKE '. $this->db->quoteVariable('%' . $val . '%') .' )');
            break;	
        case 'status':
				if ($val!="")
    		        $srch->addCondition('tu.user_status', '=', intval($val));
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
                    $srch->addOrder('tu.user_added_on', 'desc');
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
	
	function getAssociativeArray($user_type=null) {
		$srch = new SearchBase('tbl_users', 't1');
		$srch->addCondition('t1.user_is_deleted', '=', 0);
		$srch->addCondition('t1.user_status', '=', 1);
		$srch->addMultipleFields(array("user_id", "user_username"));
		if (isset($user_type)){
			if (is_array($user_type))
   	        	$srch->addCondition('t1.user_type', 'IN', $user_type);
			else
				$srch->addCondition('t1.user_type', '=', $user_type);
		}
		$srch->addOrder('user_username');
		$query=$srch->getquery();
        $rs = $this->db->query($query);
        return $this->db->fetch_all_assoc($rs);
    }
	
	function updateUserStatus($user_id,$data_update=array()) {
		$user_id = intval($user_id);
		if($user_id < 1 || count($data_update) < 1){
			$this->error = 'Error: Invalid request!!';
			return false;
		}
		if($this->db->update_from_array('tbl_users', $data_update, array('smt'=>'`user_id` = ?', 'vals'=> array($user_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function changeUserPassword($data){
		$user_id = intval($data['user_id']);
		$user = $this->getUser(array('id'=>$user_id),false);
		if(!$user){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_users', array("user_password"=>Utilities::encryptPassword($data["user_password"])), array('smt'=>'`user_id` = ?', 'vals'=> array($user_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function delete($user_id){
		$user_id = intval($user_id);
		if($user_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_users', array("user_is_deleted"=>1), array('smt'=>'`user_id` = ?', 'vals'=> array($user_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	
	function restore($user_id){
		$user_id = intval($user_id);
		if($user_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_users', array("user_is_deleted"=>0), array('smt'=>'`user_id` = ?', 'vals'=> array($user_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	/* All New Methods */
	function updateUser($data){
		$user_id = intval($data['user_id']);
		if($user_id < 1) return false;
		
		if(isset($data['user_name']) && strlen($data['user_name']) > 0){
			$values['user_name'] = $data['user_name'];
		}
		if(isset($data['user_phone']) && strlen($data['user_phone']) > 0){
			$values['user_phone'] = $data['user_phone'];
		}
		if(isset($data['user_city_town']) && strlen($data['user_city_town']) > 0){
			$values['user_city_town'] = $data['user_city_town'];
		}
		if(isset($data['ua_state']) && strlen($data['ua_state']) > 0){
			$values['user_state_county'] = $data['ua_state'];
		}
		if(isset($data['user_country']) && strlen($data['user_country']) > 0){
			$values['user_country'] = $data['user_country'];
		}
		
		if(isset($data['user_buyer_supp_pref']) && strlen($data['user_buyer_supp_pref']) > 0){
			$values['user_buyer_supp_pref'] = $data['user_buyer_supp_pref'];
		}
		
		if(isset($data['user_profile_image']) && strlen($data['user_profile_image']) > 0){
			$values['user_profile_image'] = $data['user_profile_image'];
		}elseif(isset($data['remove_profile_img']) && intval($data['remove_profile_img']) == 1){
			$values['user_profile_image'] = '';
		}
		
		if(isset($data['user_company']) && strlen($data['user_company']) > 0){
			$values['user_company'] = $data['user_company'];
		}
		
		if(isset($data['user_profile']) && strlen($data['user_profile']) > 0){
			$values['user_profile'] = $data['user_profile'];
		}
		if(isset($data['user_products_services']) && strlen($data['user_products_services']) > 0){
			$values['user_products_services'] = $data['user_products_services'];
		}
		
		if(!$this->db->update_from_array('tbl_users', $values, array('smt'=>'user_id=?', 'vals'=>array($user_id)))){
			$this->error = $this->db->getError();
			return false;
		}
		return true;
	}
	
	/* All New Methods */
	
	function updateUserBankDetails($data){
		$user_id = intval($data['ub_user_id']);
		if($user_id < 1) return false;
		$record = new TableRecord('tbl_user_bank_details');
		$record->assignValues($data);
		$sqlquery=$record->getinsertquery();
		$arr=$record->getFlds();
		foreach($arr as $field => $val) {
 			  $fields[] = "$field = '".addslashes($val)."'";
		}
		$sqlquery = $sqlquery." on duplicate KEY UPDATE " . join(', ', $fields);
		if(!$this->db->query($sqlquery)){
			$this->error = $this->db->getError();
			return false;
		}
		return true;
	}
	
	
	function getUserFavoriteProducts($user_id,$pagesize=''){
		$user_id = intval($user_id);
		if($user_id < 1) return false;
		$srch = new SearchBase('tbl_user_favourite_products', 'tufp');
		$srch->joinTable('tbl_products', 'INNER JOIN', 'tufp.ufp_prod_id=tp.prod_id and tp.prod_is_deleted=0 and tp.prod_is_expired=0 and tp.prod_status=1', 'tp');
		$srch->joinTable('tbl_shops', 'INNER JOIN', 'tp.prod_shop=ts.shop_id and ts.shop_is_deleted=0', 'ts');
        $srch->addCondition('tufp.ufp_user_id', '=', intval($user_id));
		if ($pagesize)
			$srch->setPageSize($pagesize);
		else
			$srch->doNotLimitRecords();
		$srch->addMultipleFields(array('tufp.*','tp.*','ts.*','ROUND(prod_stock*100/(prod_stock+prod_sold_count)) as remaining_stock','IF(prod_stock >0, "1", "0" ) as available'));
		$srch->addOrder('available', 'DESC');
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		$favorite_products = array();
		while($row = $this->db->fetch($rs)){
			$favorite_products[$row['ufp_id']] = $row;
		}
		return $favorite_products;
	}
	
	function getUserActivities($user_id,$criteria=array()){
		$user_id = intval($user_id);
		if($user_id < 1) return false;
		
		$pObj=new Products();
		$pObj->addMultipleFields(array('tp.prod_id','tp.prod_name','ts.shop_name as prod_shop_name','tp.prod_shop as shop_id','0 as shop_total_items','1 as type'));
		$pObj->doNotCalculateRecords();
		$pObj->doNotLimitRecords();	
		$qry_prods = $pObj->getQuery();
		
		$pObj=new Products();
		$pObj->addMultipleFields(array('tp.prod_shop',"count(prod_id) as shop_total_items"));
		$pObj->addGroupBy('tp.prod_shop');
		$pObj->doNotCalculateRecords();
		$pObj->doNotLimitRecords();	
		$qry_store_products = $pObj->getQuery();
		
		$srch = new SearchBase('tbl_shops', 'tbls');
		$srch->joinTable('tbl_users', 'INNER JOIN', 'tbls.shop_user_id=tu.user_id', 'tu');
		$srch->addCondition('tu.user_is_deleted', '=',0);
		$srch->addCondition('tu.user_status', '=',1);
		$srch->joinTable('(' . $qry_store_products . ')', 'LEFT JOIN', 'tbls.shop_id = tqsp.prod_shop', 'tqsp');
		$srch->addCondition('tbls.shop_status', '=', 1);
		$srch->addCondition('tbls.shop_is_deleted', '=', 0);
		$srch->addCondition('tbls.shop_vendor_display_status', '=', 1);
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addMultipleFields(array('tbls.shop_id','tbls.shop_name','tbls.shop_name as prod_shop_name','tbls.shop_id shop_id','tqsp.shop_total_items','2 as type'));
		$qry_shops = $srch->getQuery();
		$qry_prod_shops=$qry_prods. " UNION ".$qry_shops;
		
		//die($qry_prod_shops);
		
		
        $srch = new SearchBase('tbl_user_activities', 'tua');
		$srch->joinTable('tbl_users', 'INNER JOIN', 'tua.uact_to_user=tactto.user_id and tactto.user_is_deleted=0 and tactto.user_status=1', 'tactto');
		$srch->joinTable('tbl_users', 'LEFT JOIN', 'tua.uact_from_user=tactfrom.user_id and tactfrom.user_is_deleted=0 and tactfrom.user_status=1', 'tactfrom');
		$srch->joinTable('(' . $qry_prod_shops . ')', 'INNER JOIN', 'tua.uact_prod_shop_record = tqps.prod_id and tua.uact_prod_shop_type=tqps.type', 'tqps');
		
		$srch->addMultipleFields(array('tua.*','tactto.user_name as activity_to_name','tactto.user_profile_image as activity_to_profile_image','tactfrom.user_name as activity_from_name','tactfrom.user_profile_image as activity_from_profile_image','tqps.*','COALESCE(tqps.shop_total_items,0) as shop_total_items'));
		$srch->addCondition('tua.uact_to_user', '=', intval($user_id));
		$srch->addOrder('tua.uact_date', 'desc');
		
		foreach($criteria as $key=>$val) {
			if(strval($val)=='') continue;
            switch($key) {
				case 'page':
					$srch->setPageNumber($val);
				break;	
				case 'pagesize':
					$srch->setPageSize($val);
				break;
            }
        }
		
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return false;
		}
		return $this->db->fetch_all($rs);
	}
	
	
	function getUserAddresses($user_id,$pagesize=0){
		$user_id = intval($user_id);
		if($user_id < 1) return false;
		$srch = new SearchBase('tbl_user_address', 'ua');
		$srch->joinTable('tbl_countries', 'LEFT OUTER JOIN', 'c.country_id = ua.ua_country', 'c');
		$srch->joinTable('tbl_states', 'LEFT OUTER JOIN', 's.state_id = ua.ua_state', 's');
		$srch->addCondition('ua_is_deleted', '=', 0);
		$srch->addCondition('ua_user_id', '=', $user_id);
		if(intval($pagesize)>0){
			$srch->setPageSize($pagesize);
		}else{
			$srch->doNotLimitRecords();
		}
		$srch->doNotCalculateRecords();
		$srch->addMultipleFields(array('ua.*', 'c.country_name','c.country_id','c.country_code', 's.state_id','s.state_name'));
		$srch->addOrder('ua_is_default', 'DESC');
		$rs = $srch->getResultSet();
		return $pagesize==1?$this->db->fetch($rs):$this->db->fetch_all($rs);
	}
	
	function getUserAddress($ua_id, $user_id){
		$ua_id = intval($ua_id);
		$user_id = intval($user_id);
		if($ua_id < 1 || $user_id < 1){
			return false;
		}
		$srch = new SearchBase('tbl_user_address', 'ua');
		$srch->joinTable('tbl_countries', 'LEFT OUTER JOIN', 'c.country_id = ua.ua_country', 'c');
		$srch->joinTable('tbl_states', 'LEFT OUTER JOIN', 's.state_id = ua.ua_state', 's');
		$srch->addCondition('ua_is_deleted', '=', 0);
		$srch->addCondition('ua_id', '=', $ua_id);
		$srch->addCondition('ua_user_id', '=', $user_id);
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addMultipleFields(array('ua.*', 'c.country_name', 's.state_name'));
		//die($srch->getquery());
		$rs = $srch->getResultSet();
		return $this->db->fetch($rs);
	}
	
	function getAddress($ua_id){
		$ua_id = intval($ua_id);
		$srch = new SearchBase('tbl_user_address', 'ua');
		$srch->joinTable('tbl_countries', 'LEFT OUTER JOIN', 'c.country_id = ua.ua_country', 'c');
		$srch->joinTable('tbl_states', 'LEFT OUTER JOIN', 's.state_id = ua.ua_state', 's');
		$srch->addCondition('ua_is_deleted', '=', 0);
		$srch->addCondition('ua_id', '=', $ua_id);
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addMultipleFields(array('ua.*', 'c.country_name', 'c.country_code', 's.state_name', 's.state_code'));
		$rs = $srch->getResultSet();
		return $this->db->fetch($rs);
	}
	
	function addUpdateAddress($data){
		$ua_id = intval($data['ua_id']);
		$user_id = intval($data['ua_user_id']);
		unset($data['ua_id']);
		unset($data['ua_user_id']);
		if($user_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		$record = new TableRecord('tbl_user_address');
		$assign_fields = array(
						'ua_name'=>$data['ua_name'],
						'ua_address1'=>$data['ua_address1'],
						'ua_address2'=>$data['ua_address2'],
						'ua_address3'=>$data['ua_address3'],
						'ua_city'=>$data['ua_city'],
						'ua_state'=>intval($data['ua_state']),
						'ua_country'=>intval($data['ua_country']),
						'ua_zip'=>$data['ua_zip'],
						'ua_phone'=>$data['ua_phone'],
						'ua_active'=>intval(1),
					);
		if($ua_id === 0){
			$assign_fields['ua_user_id'] = $user_id;
		}
		$record->assignValues($assign_fields);
		if($ua_id === 0 && $record->addNew()){
			$this->setAddressDefault($record->getId(),$user_id);
			return intval($record->getId());
		}elseif($ua_id > 0 && $record->update(array('smt'=>'`ua_id`=? AND `ua_user_id`=?', 'vals'=>array($ua_id, $user_id)))){
			return $ua_id;
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return false;
	}
	
	function deleteUserAddress($ua_id, $user_id){
		$ua_id = intval($ua_id);
		$user_id = intval($user_id);
		if($ua_id < 1 || $user_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->deleteRecords('tbl_user_address', array('smt'=>'`ua_id`=? AND `ua_user_id`=?', 'vals'=>array($ua_id, $user_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function setAddressDefault($ua_id, $user_id){
		$ua_id = intval($ua_id);
		$user_id = intval($user_id);
		if($ua_id < 1 || $user_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->startTransaction() && $this->db->update_from_array('tbl_user_address', array('ua_is_default'=>0), array('smt'=>'ua_user_id=?', 'vals'=>array($user_id))) && $this->db->update_from_array('tbl_user_address', array('ua_is_default'=>1), array('smt'=>'`ua_id`=? AND `ua_user_id`=?', 'vals'=>array($ua_id, $user_id))) && $this->db->commitTransaction()){
			return true;
		}
		$this->error = $this->db->getError();
		$this->db->rollbackTransaction();
		return false;
	}
	
	function getUserFavoriteItems($criteria=array(),$pagesize=0){
		if (count($criteria) < 1) return false;
		
		$pObj = new Products();
		$pObj->joinWithDetailTable();
		$pObj->addSpecialPrice();
		$pObj->selectFields(array('tp.*','ts.shop_id','ts.shop_user_id','ts.shop_name','ts.shop_title','tu.user_id','tu.user_name','tu.user_username','tu.user_email','IF(prod_stock >0, "1", "0" ) as available'));
		$pObj->doNotCalculateRecords();
		$pObj->doNotLimitRecords();	
		
		$srch = new SearchBase('tbl_user_favourite_products', 'tufp');
		$srch->joinTable('(' . $pObj->getQuery() . ')', 'INNER JOIN', 'tufp.ufp_prod_id = p.prod_id', 'p');
		$srch->addMultipleFields(array('tufp.*', 'p.*'));
		foreach($criteria as $key=>$val) {
        if(strval($val)=='') continue;
	        switch($key) {
	    	    case 'user':
    	    	    $srch->addCondition('tufp.ufp_user_id', '=', intval($val));
	            break;
		        case 'shop':
		            $srch->addCondition('ts.shop_id', '=', $val);
    		    break;
				case 'favorite':
					$srch->joinTable('tbl_user_favourite_products', 'LEFT JOIN', 'p.prod_id=tufavp.ufp_prod_id and tufavp.ufp_user_id='.$val, 'tufavp');
					$srch->addFld("IF(tufavp.ufp_prod_id>0,'1','0') as favorite");
				break;	
				case 'pagesize':
					$srch->setPageSize($val);
				break;
				case 'page':
					$srch->setPageNumber($val);
				break;
	        }
        }
		$srch->addOrder('ufp_id','desc');
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		return $this->db->fetch_all($rs);
	
	}	
	
	function getUserFavoriteShops($criteria=array()){
		if (count($criteria) < 1) return false;
		
		$pObj=new Products();
		$pObj->addMultipleFields(array('tp.prod_shop',"count(prod_id) as totStoreProducts"));
		$pObj->addGroupBy('tp.prod_shop');
		$pObj->doNotCalculateRecords();
		$pObj->doNotLimitRecords();	
		$qry_store_products = $pObj->getQuery();
		
		$srch = new SearchBase('tbl_user_favourite_shops', 'tufs');
		$srch->joinTable('tbl_shops', 'INNER JOIN', 'tufs.ufs_shop_id=ts.shop_id and ts.shop_is_deleted=0 and ts.shop_status=1 and ts.shop_vendor_display_status=1', 'ts');
		$srch->joinTable('(' . $qry_store_products . ')', 'LEFT JOIN', 'ts.shop_id = tqsp.prod_shop', 'tqsp');
		$srch->addCondition('tqsp.totStoreProducts', '>', 0);
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tufs.ufs_user_id');
		$srch->addMultipleFields(array('tufs.ufs_user_id',"count(ufs_shop_id) as favShops"));
		$qry_fav_shops = $srch->getQuery();
		
        $srch = new SearchBase('tbl_user_favourite_shops', 'tufs');
		$srch->joinTable('tbl_shops', 'INNER JOIN', 'tufs.ufs_shop_id=ts.shop_id and ts.shop_is_deleted=0 and ts.shop_status=1 and ts.shop_vendor_display_status=1', 'ts');
		$srch->joinTable('(' . $qry_store_products . ')', 'LEFT JOIN', 'ts.shop_id = tqsp.prod_shop', 'tqsp');
		
		$srch->joinTable('tbl_users', 'INNER JOIN', 'ts.shop_user_id=tus.user_id and tus.user_is_deleted=0 and tus.user_status=1', 'tus');
		$srch->joinTable('tbl_users', 'INNER JOIN', 'tufs.ufs_user_id=tuf.user_id and tuf.user_is_deleted=0 and tuf.user_status=1', 'tuf');
		$srch->joinTable('(' . $qry_fav_shops . ')', 'LEFT OUTER JOIN', 'tufs.ufs_user_id = tqfs.ufs_user_id', 'tqfs');
		$srch->joinTable('tbl_states', 'LEFT JOIN', 'ts.shop_state=tst.state_id and tst.state_delete=0', 'tst');
		$srch->joinTable('tbl_countries', 'LEFT JOIN', 'ts.shop_country=tco.country_id and tco.country_delete=0', 'tco');
		$srch->addCondition('tqsp.totStoreProducts', '>', 0);
		
		$srch->addMultipleFields(array('tufs.*','ts.*','tqsp.*','tus.*','tqsp.*','tst.state_name','tco.country_name','tuf.user_id as userfav_id','tuf.user_username as userfav_username','tuf.user_profile_image as userfav_profile_image','COALESCE(tqfs.favShops,0) as favShops','COALESCE(tqsp.totStoreProducts,0) as totStoreProducts'));
		
		foreach($criteria as $key=>$val) {
        if(strval($val)=='') continue;
	        switch($key) {
    	    case 'user':
        	    $srch->addCondition('tufs.ufs_user_id', '=', intval($val));
            break;
	        case 'shop':
	            $srch->addCondition('ts.shop_id', '=', $val);
    	    break;
			case 'favorite':
				$srch->joinTable('tbl_user_favourite_shops', 'LEFT JOIN', 'ts.shop_id=tf.ufs_shop_id and tf.ufs_user_id='.$val, 'tf');
				$srch->addFld("IF(tf.ufs_shop_id>0,'1','0') as favorite");
			break;
			case 'page':
				$srch->setPageNumber($val);
			break;	
			case 'pagesize':
	            $srch->setPageSize($val);
    	    break;
	        }
        }
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		return $this->db->fetch_all($rs);
	}
	
	function getUserFavoriteItem($product_id,$user_id){
		$user_id = intval($user_id);
		$product_id = intval($product_id);
		if (($user_id < 1) || ($product_id < 1)) return false;
		$srch = new SearchBase('tbl_user_favourite_products', 'tufp');
		$srch->joinTable('tbl_products', 'INNER JOIN', 'tufp.ufp_prod_id=tp.prod_id and tp.prod_is_deleted=0 and tp.prod_is_expired=0 and tp.prod_status=1', 'tp');
		$srch->joinTable('tbl_shops', 'INNER JOIN', 'tp.prod_shop=ts.shop_id and ts.shop_is_deleted=0', 'ts');
		$srch->addCondition('tufp.ufp_user_id', '=', $user_id);
		$srch->addCondition('tufp.ufp_prod_id', '=', $product_id);
		$rs = $srch->getResultSet();
		return $this->db->fetch($rs);
	}	
	
	function getUserFavoriteShop($shop_id,$user_id){
		$user_id = intval($user_id);
		$shop_id = intval($shop_id);
		if (($user_id < 1) || ($shop_id < 1)) return false;
        $srch = new SearchBase('tbl_user_favourite_shops', 'tufs');
		$srch->addCondition('tufs.ufs_user_id', '=', intval($user_id));
		$srch->addCondition('tufs.ufs_shop_id', '=', $shop_id);
		$rs = $srch->getResultSet();
		return $this->db->fetch($rs);
	}
	
	function deleteUserFavoriteShop($shop_id, $user_id){
		$shop_id = intval($shop_id);
		$user_id = intval($user_id);
		if($shop_id < 1 || $user_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->deleteRecords('tbl_user_favourite_shops', array('smt'=>'ufs_user_id=? and ufs_shop_id=?', 'vals'=>array($user_id,$shop_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function addUserFavoriteShop($shop_id, $user_id){
		$shop_id = intval($shop_id);
		$user_id = intval($user_id);
		if($shop_id < 1 || $user_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->insert_from_array('tbl_user_favourite_shops', array('ufs_user_id' => $user_id,'ufs_shop_id' => $shop_id))){
			if (!$this->addUserActivity($user_id,array("prod_shop_id"=>$shop_id,"prod_shop_type"=>2,"action"=>"FV"))){
				return false;
			}
			return true;
		}
		$this->error = $this->db->getError();
		return true;
	}
	
	function addUserActivity($user_id,$data){
		$user_id = intval($user_id);
		if($user_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		$record = new TableRecord('tbl_user_activities');
		$actArray["uact_to_user"]=$user_id;
		$actArray["uact_from_user"]=$user_id;
		$actArray["uact_prod_shop_record"]=$data["prod_shop_id"];
		$actArray["uact_prod_shop_type"]=$data["prod_shop_type"];
		$actArray["uact_action_performed"]=$data["action"];
		$actArray["uact_date"]=date('Y-m-d H:i:s');
		$record->assignValues($actArray);
		$strInsQuery = $record->getInsertQuery();
		$strInsQuery = $strInsQuery." on duplicate KEY UPDATE `uact_date`=NOW()";
		if ($this->db->query($strInsQuery)){
			return true;
		}
		else{
			$this->error = $this->db->getError();
			return false;
		}
		
		$this->error = $this->db->getError();
	}
	
	function getUserLists($user_id,$pagesize=0){
		$user_id = intval($user_id);
		if($user_id < 1) return false;
		$srch = new SearchBase('tbl_user_list_products', 'tulp');
		$srch->joinTable('tbl_products', 'INNER JOIN', 'tulp.ulp_prod_id=tp.prod_id and tp.prod_status=1 and tp.prod_is_deleted=0 and tp.prod_is_expired=0', 'tp');
		$srch->joinTable('tbl_shops', 'INNER JOIN', 'tp.prod_shop=ts.shop_id and ts.shop_status=1 and ts.shop_vendor_display_status=1 and ts.shop_is_deleted=0', 'ts');
		$srch->joinTable('tbl_users', 'INNER JOIN', 'ts.shop_user_id=tu.user_id and tu.user_is_deleted=0 and tu.user_status=1', 'tu');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tulp.ulp_list_id');
		$srch->addMultipleFields(array('tulp.ulp_list_id',"count(ulp_id) as listProducts","GROUP_CONCAT(ulp_prod_id) as ulist_products"));
		$qry_list_products = $srch->getQuery();
		
        $srch = new SearchBase('tbl_user_lists', 'tul');
		$srch->addCondition('tul.ulist_is_deleted', '=', 0);
		$srch->joinTable('(' . $qry_list_products . ')', 'LEFT OUTER JOIN', 'tul.ulist_id = tqlp.ulp_list_id', 'tqlp');
		
		$srch->addCondition('tul.ulist_user_id', '=', $user_id);
		if(intval($pagesize)>0){
			$srch->setPageSize($pagesize);
		}else{
			$srch->doNotLimitRecords();
		}
		$srch->doNotCalculateRecords();
		$srch->addMultipleFields(array('tul.*','COALESCE(tqlp.listProducts,0) as listProducts','tqlp.ulist_products'));
		$srch->addOrder('tul.ulist_id','ASC');
		$rs = $srch->getResultSet();
		return $this->db->fetch_all($rs);
	}
	
	function getUserList($id,$user_id){
		$user_id = intval($user_id);
		if($user_id < 1) return false;
		$srch = new SearchBase('tbl_user_list_products', 'tulp');
		$srch->joinTable('tbl_products', 'INNER JOIN', 'tulp.ulp_prod_id=tp.prod_id and tp.prod_status=1 and tp.prod_is_deleted=0 and tp.prod_is_expired=0', 'tp');
		$srch->joinTable('tbl_shops', 'INNER JOIN', 'tp.prod_shop=ts.shop_id and ts.shop_status=1 and ts.shop_vendor_display_status=1 and ts.shop_is_deleted=0', 'ts');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addGroupBy('tulp.ulp_list_id');
		$srch->addMultipleFields(array('tulp.ulp_list_id',"count(ulp_id) as listProducts","GROUP_CONCAT(ulp_prod_id) as ulist_products"));
		$qry_list_products = $srch->getQuery();
		
        $srch = new SearchBase('tbl_user_lists', 'tul');
		$srch->addCondition('tul.ulist_is_deleted', '=', 0);
		$srch->joinTable('(' . $qry_list_products . ')', 'LEFT OUTER JOIN', 'tul.ulist_id = tqlp.ulp_list_id', 'tqlp');
		$srch->addCondition('tul.ulist_user_id', '=', $user_id);
		$srch->addCondition('tul.ulist_id', '=', $id);
		$srch->addMultipleFields(array('tul.*','COALESCE(tqlp.listProducts,0) as listProducts','tqlp.ulist_products'));
		$rs = $srch->getResultSet();
		return $this->db->fetch($rs);
	}
	
	function deleteUserFavoriteProduct($product_id, $user_id){
		$product_id = intval($product_id);
		$user_id = intval($user_id);
		if($product_id < 1 || $user_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->deleteRecords('tbl_user_favourite_products', array('smt'=>'ufp_user_id=? and ufp_prod_id=?', 'vals'=>array($user_id,$product_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function addUserFavoriteProduct($product_id, $user_id){
		$product_id = intval($product_id);
		$user_id = intval($user_id);
		if($product_id < 1 || $user_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->insert_from_array('tbl_user_favourite_products', array('ufp_user_id' => $user_id,'ufp_prod_id' => $product_id))){
			
			if (!$this->addUserActivity($user_id,array("prod_shop_id"=>$product_id,"prod_shop_type"=>1,"action"=>"FV"))){
				return false;
			}
			return true;
		}
		$this->error = $this->db->getError();
	}
	
	function deleteUserList($id, $user_id){
		$id = intval($id);
		$user_id = intval($user_id);
		if($id < 1 || $user_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_user_lists', array('ulist_is_deleted' => 1),array('smt' => 'ulist_id = ? AND ulist_user_id = ?', 'vals' => array($id,$user_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function getUserListProduct($list_id,$product_id){
		$list_id = intval($list_id);
		$product_id = intval($product_id);
		if(($list_id < 1) || ($product_id < 1)) return false;
	
		
		$srch = new SearchBase('tbl_user_list_products', 'tulp');
		$srch->joinTable('tbl_products', 'INNER JOIN', 'tulp.ulp_prod_id=tp.prod_id and tp.prod_status=1 and tp.prod_is_deleted=0 and tp.prod_is_expired=0', 'tp');
		$srch->joinTable('tbl_shops', 'INNER JOIN', 'tp.prod_shop=ts.shop_id and ts.shop_status=1 and ts.shop_vendor_display_status=1 and ts.shop_is_deleted=0', 'ts');
		$srch->joinTable('tbl_user_lists', 'INNER JOIN', 'tulp.ulp_list_id=tul.ulist_id and tul.ulist_is_deleted=0', 'tul');
		$srch->addCondition('tulp.ulp_list_id', '=', $list_id);
		$srch->addCondition('tulp.ulp_prod_id', '=', $product_id);
		$srch->addMultipleFields(array('tulp.*','tp.*','ts.*','tul.*','ROUND(prod_stock*100/(prod_stock+prod_sold_count)) as remaining_stock','IF(prod_stock >0, "1", "0" ) as available'));
		$rs = $srch->getResultSet();
		return $this->db->fetch($rs);
	}
	
	function getUserListProducts($criteria=array(),$pagesize=0){
		if (count($criteria) < 1) return false;
		$pObj = new Products();
		$pObj->joinWithDetailTable();
		$pObj->joinWithPromotionsTable();
		$pObj->addSpecialPrice();
		$pObj->selectFields(array('tp.*','ts.shop_id','ts.shop_user_id','ts.shop_name','ts.shop_title','tu.user_id','tu.user_name','tu.user_username','tu.user_email','IF(prod_stock >0, "1", "0" ) as available'));
		$pObj->doNotCalculateRecords();
		$pObj->doNotLimitRecords();	
		
		$srch = new SearchBase('tbl_user_list_products', 'tulp');
		$srch->joinTable('tbl_user_lists', 'INNER JOIN', 'tulp.ulp_list_id=tul.ulist_id and tul.ulist_is_deleted=0', 'tul');
		$srch->joinTable('(' . $pObj->getQuery() . ')', 'INNER JOIN', 'tulp.ulp_prod_id = p.prod_id', 'p');
		$srch->addMultipleFields(array('tulp.*','p.*','tul.*','ROUND(prod_stock*100/(prod_stock+prod_sold_count)) as remaining_stock','IF(prod_stock >0, "1", "0" ) as available'));
		foreach($criteria as $key=>$val) {
	        if(strval($val)=='') continue;
	        switch($key) {
	    	    case 'list':
	        	    $srch->addCondition('tulp.ulp_list_id', '=', intval($val));
	            break;
				case 'favorite':
					$srch->joinTable('tbl_user_favourite_products', 'LEFT JOIN', 'p.prod_id=tufp.ufp_prod_id and tufp.ufp_user_id='.$val, 'tufp');
					$srch->addFld("IF(tufp.ufp_prod_id>0,'1','0') as favorite");
				break;
				case 'page':
					$srch->setPageNumber(intval($val));
				break;	
				case 'pagesize':
				   $srch->setPageSize($val);
	    	    break;
	        }
        }
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		return $this->db->fetch_all($rs);
	}	
		
	
	function addList($data){
		$user_id = intval($data['user_id']);
		unset($data['user_id']);
		if($user_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		$record = new TableRecord('tbl_user_lists');
		$assign_fields = array(
						'ulist_title'=>$data['ulist_title'],
						'ulist_added_on'=>date('Y-m-d H:i:s'),
						'ulist_user_id'=>$user_id,
					);
		$record->assignValues($assign_fields);
		if($record->addNew()){
			return intval($record->getId());
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return false;
	}
	function deleteUserListProduct($product_id, $list_id){
		$product_id = intval($product_id);
		$list_id = intval($list_id);
		if($product_id < 1 || $list_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->deleteRecords('tbl_user_list_products', array('smt'=>'ulp_list_id=? and ulp_prod_id=?', 'vals'=>array($list_id,$product_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function addUserListProduct($product_id, $list_id){
		$product_id = intval($product_id);
		$list_id = intval($list_id);
		if($product_id < 1 || $list_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->insert_from_array('tbl_user_list_products', array('ulp_list_id' => $list_id,'ulp_prod_id' => $product_id))){
			return true;
		}
		$this->error = $this->db->getError();
	}
	
	function getMessageThread($thread_id,$user_id){
		$thread_id = intval($thread_id);
		$user_id = intval($user_id);
		if(($thread_id < 1) || ($user_id < 1)) return false;
		$srch = new SearchBase('tbl_threads', 'tthr');
		$srch->joinTable('tbl_thread_messages', 'INNER JOIN', 'tthr.thread_id=ttm.message_thread', 'ttm');
		$srch->joinTable('tbl_products', 'LEFT JOIN', 'tthr.thread_record=tp.prod_id and tthr.thread_type="P"', 'tp');
		$srch->joinTable('tbl_order_products', 'LEFT JOIN', 'tthr.thread_record=tord.opr_id and tthr.thread_type="O"', 'tord');
		$srch->joinTable('tbl_shops', 'LEFT JOIN', 'tthr.thread_record=ts.shop_id and tthr.thread_type="S"', 'ts');
		$srch->joinTable('tbl_orders_status', 'LEFT JOIN', 'tord.opr_status=tos.orders_status_id', 'tos');
		$srch->addGroupBy('ttm.message_thread');
		$srch->addCondition('tthr.thread_id', '=', $thread_id);
		$cnd=$srch->addCondition('ttm.message_from', '=', $user_id);
		$cnd->attachCondition('ttm.message_to','=',$user_id,'OR');
		$rs = $srch->getResultSet();
		return $this->db->fetch($rs);
	}
	
	function getMessages($criteria=array(),$pagesize=0){
		$srch = new SearchBase('tbl_thread_messages', 'tthm');
		$srch->addCondition('tthm.message_is_deleted', '=', 0);
		$srch->addOrder('tthm.message_id', 'DESC');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$threads= $srch->getQuery();
		
		$srch = new SearchBase('tbl_threads', 'tthr');
		$srch->joinTable('(' . $threads . ')', 'INNER JOIN', 'tthr.thread_id=ttm.message_thread', 'ttm');
		$srch->joinTable('tbl_products', 'LEFT JOIN', 'tthr.thread_record=tp.prod_id and tthr.thread_type="P" ', 'tp');
		$srch->joinTable('tbl_order_products', 'LEFT JOIN', 'tthr.thread_record=tord.opr_id and tthr.thread_type="O"', 'tord');
		$srch->joinTable('tbl_orders_status', 'LEFT JOIN', 'tord.opr_status=ts.orders_status_id', 'ts');
		$srch->joinTable('tbl_users', 'INNER JOIN', 'ttm.message_from=tmf.user_id', 'tmf');
		$srch->joinTable('tbl_users', 'INNER JOIN', 'ttm.message_to=tmt.user_id', 'tmt');		
		$srch->addCondition('ttm.message_is_deleted', '=', 0);		
		$srch->addMultipleFields(array('tthr.*','ttm.*','tp.*','tord.*','ts.*','tmf.user_id as message_sent_by','tmf.user_username as message_sent_by_username','tmf.user_profile_image as message_sent_by_profile','tmt.user_id as message_sent_to','tmt.user_username as message_sent_to_username','tmt.user_email as message_sent_to_email','tmt.user_name as message_sent_to_name','tmt.user_profile_image as message_sent_to_profile','UNIX_TIMESTAMP(ttm.message_date) as message_date_timestamp'));
		foreach($criteria as $key=>$val) {
            switch($key) {
            case 'thread':
                $srch->addCondition('ttm.message_thread', '=', intval($val));
                break;
			case 'id':
                $srch->addCondition('ttm.message_id', '=', intval($val));
                break;
			case 'from':
                $srch->addCondition('ttm.message_from', '=', intval($val));
                break;
			case 'to':
                $srch->addCondition('ttm.message_to', '=', intval($val));
                break;
			case 'all':
                	$cndCondition=$srch->addCondition('ttm.message_from', '=', intval($val));
					$cndCondition->attachCondition('message_to', '=', intval($val),'OR');
                break;
			case 'page':
				$srch->setPageNumber(intval($val));
				break;
			case 'unread':
				$srch->addCondition('ttm.message_is_unread', '=', intval($val));
				break;
			case 'order':
				if (in_array($val,array("message_id"))){
					//$srch->addOrder($val,'DESC');
					$srch->addOrder($val,isset($criteria['orderby'])?$criteria['orderby']:'DESC');
				}
			break;												
			case 'group':
				if (in_array($val,array("thread_id"))){					
					$srch->addGroupBy($val);
				}
				break;			
			case 'keyword':
                if ($val!=""){
					$val=urldecode($val);
					$cndCondition=$srch->addCondition('tthr.thread_subject', 'like', '%' . $val . '%');
					$cndCondition->attachCondition('tmf.user_username', 'like', '%' . $val . '%','OR');
					$cndCondition->attachCondition('tmt.user_username', 'like', '%' . $val . '%','OR');
					$cndCondition->attachCondition('ttm.message_text', 'like', '%' . $val . '%','OR');
				}	
                break;			
            }
        }
		if(intval($pagesize)>0){
			$srch->setPageSize($pagesize);
		}else{
			$srch->doNotLimitRecords();
		}
		
		$rs = $srch->getResultSet();
		//die($srch->getQuery());
		//echo $srch->getQuery(); exit;
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		return ($pagesize==1)?$this->db->fetch($rs):$this->db->fetch_all($rs);
	}
	
	function addWithdrawalRequest($data){
		$user_id = intval($data['ub_user_id']);
		unset($data['ub_user_id']);
		if($user_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		$assign_fields = array(
						'withdrawal_amount'=>$data['withdrawal_amount'],
						'withdrawal_bank'=>$data['ub_bank_name'],
						'withdrawal_account_holder_name'=>$data['ub_account_holder_name'],
						'withdrawal_account_number'=>$data['ub_account_number'],
						'withdrawal_ifc_swift_code'=>$data['ub_ifsc_swift_code'],
						'withdrawal_bank_address'=>$data['ub_bank_address'],
						'withdrawal_comments'=>$data['withdrawal_comments'],
						'withdrawal_status'=>0,
						'withdrawal_request_date'=>date('Y-m-d H:i:s'),
						'withdrawal_user_id'=>$user_id,
					);
		$broken = false;
		
		if($this->db->startTransaction() && $this->db->insert_from_array('tbl_user_withdrawal_requests', $assign_fields, true)){
				$this->withdraw_request_id = $this->db->insert_id();
				$formatted_request_value=str_pad($this->withdraw_request_id,6,'0',STR_PAD_LEFT);
				$formatted_request_value="#".$formatted_request_value;
				$txnArray["utxn_user_id"]=$user_id;
				$txnArray["utxn_debit"]=$data["withdrawal_amount"];
				$txnArray["utxn_status"]=0;
				$txnArray["utxn_comments"]=Utilities::getLabel('L_Funds_Withdrawn').'. '.Utilities::getLabel('L_Request_ID').' '.$formatted_request_value;
				$txnArray["utxn_withdrawal_id"]=$this->withdraw_request_id;
				$transObj=new Transactions();
				if ($transObj->addTransaction($txnArray)){
					
					// Success Operations will go here
					
				}else{
					$this->error = $transObj->getError();
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
	
	function addThreadMessage($data){
		$thread_id = intval($data['thread_id']);
		$user_id = intval($data['user_id']);
		unset($data['thread_id']);
		unset($data['user_id']);
		if($user_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		$record = new TableRecord('tbl_threads');
		$assign_fields = array(
						'thread_subject'=>$data['thread_subject'],
						'thread_start_date'=>date('Y-m-d H:i:s'),
						'thread_type'=>$data['thread_type'],
						'thread_record'=>$data['thread_record'],
					);
		if($thread_id === 0){
			$assign_fields['thread_started_by'] = $user_id;
		}
		$record->assignValues($assign_fields);
		if($thread_id === 0){
			if ($record->addNew()){
				$thread_id=intval($record->getId());
			}else{
				$this->error = $this->db->getError();
				return false;
			}
		}
		$record = new TableRecord('tbl_thread_messages');
		$assign_fields = array(
							'message_thread'=>$thread_id,
							'message_date'=>date('Y-m-d H:i:s'),
							'message_from'=>$user_id,
							'message_to'=>$data['message_sent_to'],
							'message_text'=>$data['message_text'],
						);
		$record->assignValues($assign_fields);
		if($record->addNew()){
			$message_id=$record->getId();
			$emailNotificationObj=new Emailnotifications();
			if ($emailNotificationObj->SendMessageNotification($message_id)){
				return intval($message_id);
			}else{
				$this->error=$emailNotificationObj->getError();
			}
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return false;
		
	}
	
	function addProductReturnRequest($data){
		$user_id = intval($data['user_id']);
		$opr_id = intval($data['opr_id']);
		unset($data['user_id']);
		unset($data['opr_id']);
		if (($user_id < 1) || ($opr_id < 1)){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		$criteria=array("user"=>$user_id,"id"=>$opr_id,"pagesize"=>1);
		$orderObj=new Orders();
		$order_detail=$orderObj->getChildOrders($criteria);
		$assign_fields["refund_user_id"]=$user_id;
		$assign_fields["refund_prod_id"]=$order_detail["opr_product_id"];
		$assign_fields["refund_order"]=$order_detail["opr_id"];
		$assign_fields["refund_request_date"]=date('Y-m-d H:i:s');
		$assign_fields["refund_qty"]=$data["refund_qty"];
		$assign_fields["refund_reason"]=$data["refund_reason"];
		$assign_fields["refund_or_replace"]=$data["refund_or_replace"];
		$broken = false;
		
		if($this->db->insert_from_array('tbl_prod_refund_requests', $assign_fields, true)){
				$refund_request=$this->db->insert_id();
				$record=new TableRecord('tbl_prod_refund_request_messages');
				$arr_fields["refmsg_request"]=$refund_request;
				$arr_fields["refmsg_from"]=$user_id;
				$arr_fields["refmsg_text"]=$data["refmsg_text"];
				$arr_fields["refmsg_date"]=date('Y-m-d H:i:s');
				$record->assignValues($arr_fields);
				if($record->addNew()){
					$this->refund_request_message_id=$record->getId();
				}else{
					$broken=true;
				}
				
		}else{
			$broken = true;
		}
		if($broken === false){
			return true;
		}
		return false;
		
	}
	
	function addOrderCancellationRequest($data){
		$user_id = intval($data['user_id']);
		$opr_id = intval($data['opr_id']);
		unset($data['user_id']);
		unset($data['opr_id']);
		if (($user_id < 1) || ($opr_id < 1)){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		$criteria=array("user"=>$user_id,"id"=>$opr_id,"pagesize"=>1);
		$orderObj=new Orders();
		$order_detail=$orderObj->getChildOrders($criteria);
		$assign_fields["cancellation_request_user_id"]=$user_id;
		$assign_fields["cancellation_request_order"]=$order_detail["opr_id"];
		$assign_fields["cancellation_request_date"]=date('Y-m-d H:i:s');
		$assign_fields["cancellation_request_reason"]=$data["reason"];
		$assign_fields["cancellation_request_message"]=$data["message"];
		if($this->db->insert_from_array('tbl_order_cancel_requests', $assign_fields, true)){
			$this->cancellation_request=$this->db->insert_id();
			return true;
		}else{
			return false;
		}
		
	}
	
	
	function addProductFeedback($data){
		$srObj=new SmartRecommendations();
		$weightageSettings= $srObj->getWeightSettings();
		$pOBj=new Products();
		$user_id = intval($data['user_id']);
		$product_id = intval($data['product_id']);
		unset($data['user_id']);
		unset($data['product_id']);
		if(($user_id < 1) || ($product_id < 1)){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		$record = new TableRecord('tbl_prod_reviews');
		$assign_fields = array(
						'reviewed_on'=>date('Y-m-d H:i:s'),
						'review_status'=>Settings::getSetting("CONF_DEFAULT_REVIEW_STATUS"),
						'review_user_id'=>$user_id,
						'review_prod_id'=>$product_id,
						'review_order'=>$data["opr_id"],
						'review_rating'=>$data["review_rating"],
						'review_title'=>$data["review_title"],
						'review_text'=>$data["review_text"],
					);
		
		$record->assignValues($assign_fields);
		if($record->addNew()){
			$pOBj->recordProductWeightage($product_id,'rating',$weightageSettings['products#rating_multiply_factor']*$data["review_rating"]);
			$this->product_feedback_id=$record->getId();
			return intval($this->product_feedback_id);
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return false;
	}
	
	function markUserMessageRead($thread_id, $user_id){
		$thread_id = intval($thread_id);
		$user_id = intval($user_id);
		if($thread_id < 1 || $user_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_thread_messages', array('message_is_unread'=>0), array('smt'=>'`message_thread`=? AND ( `message_to`=? )', 'vals'=>array($thread_id, $user_id)),true)){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function getUserSales($user_id) {
		$user_id = intval($user_id);
		if($user_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		$last12Months=Utilities::getLast12MonthsDetails();
		foreach($last12Months as $key=>$val ){
			$rsSales=$this->db->query("SELECT SUM((opr_customer_buying_price+opr_customization_price)*opr_qty - opr_refund_amount) AS Sales FROM `tbl_order_products` t1 INNER JOIN tbl_orders t2 on t1.opr_order_id=t2.order_id INNER JOIN tbl_shops ts on ts.shop_id=t1.opr_product_shop  WHERE t2.order_payment_status IN (1,2) and t1.opr_status in (".implode(",",(array)Settings::getSetting("CONF_COMPLETED_ORDER_STATUS")).") and month( t2.`order_date_added` )= $val[monthCount] and year( t2.`order_date_added` )=$val[year] and ts.shop_user_id=".(int)$user_id);
			$row=$this->db->fetch($rsSales);
			$sales_arr[$val[monthShort]."-".$val[yearShort]]=$row["Sales"];
			//die($val[monthShort]."-".$val[year]);
		}
		return $sales_arr;
		
    }
	
	function search_withdrawal_requests($criteria, $count='') {
        $srch = new SearchBase('tbl_user_withdrawal_requests', 't1');
		$srch->joinTable('tbl_users', 'INNER JOIN', 't1.withdrawal_user_id=t2.user_id', 't2');
		//$srch->joinTable('tbl_banks', 'INNER JOIN', 't1.withdrawal_bank=t3.bank_id', 't3');
        foreach($criteria as $key=>$val) {
			if(strval($val)=='') continue;
            switch($key) {
			case 'id':
                $srch->addCondition('t1.withdrawal_id', '=', intval($val));
                break;
			case 'user':
                $srch->addCondition('t1.withdrawal_user_id', '=', intval($val));
                break;
			case 'status':
                $srch->addCondition('t1.withdrawal_status', '=', intval($val));
                break;	
			case 'minprice':
                $srch->addCondition('t1.withdrawal_amount', '>=', $val);
                break;
			case 'maxprice':
                $srch->addCondition('t1.withdrawal_amount', '<=', $val);
                break;
			case 'date_from':
                $srch->addCondition('t1.withdrawal_request_date', '>=', $val. ' 00:00:00');
                break;
			case 'date_to':
                $srch->addCondition('t1.withdrawal_request_date', '<=', $val. ' 23:59:59');
                break;	
					
            }
        }
        return $srch;
    }
	
	function getUserLastWithdrawalRequest($user_id){
		$srch = new SearchBase('tbl_user_withdrawal_requests', 'tuwr');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$srch->addCondition('withdrawal_user_id', '=', $user_id);
		$srch->addOrder('withdrawal_request_date','desc');
		$rs = $srch->getResultSet();
		if(!$row = $this->db->fetch($rs)){
			return false;
		}
		return $row;
	}
	
	function addProductReturnRequestMessage($data){
		$user_id = intval($data['user_id']);
		$request = intval($data['id']);
		unset($data['user_id']);
		unset($data['id']);
		if (($user_id < 1) || ($request < 1)){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		$message_id = intval($data['message_id']);
		$record = new TableRecord('tbl_prod_refund_request_messages');
		$assign_fields = array();
		$assign_fields['refmsg_request'] = $request;
		$assign_fields['refmsg_from'] = $user_id;
		$assign_fields['refmsg_text'] = $data['refmsg_text'];
		$assign_fields['refmsg_from_type'] = $data['type'];
		if(isset($data['attachment']) && $data['attachment'] != ''){
			$assign_fields['refmsg_attachment'] = $data['attachment'];
		}
		$assign_fields['refmsg_date'] = date('Y-m-d H:i:s');
		$record->assignValues($assign_fields);
		if($record->addNew()){
			$this->refund_request_message_id=$record->getId();
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		return true;
	}
	
	function escalateRequest($id,$user_id,$action_by="U"){
		$id = intval($id);
		$user_id = intval($user_id);
		if($id < 1 || $user_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		
		if($this->db->startTransaction() && $this->db->update_from_array('tbl_prod_refund_requests', array('refund_request_status'=>1,'refund_request_updated_by'=>$user_id,'refund_request_action_by'=>$action_by), array('smt'=>'refund_id=?', 'vals'=>array($id))) && $this->db->insert_from_array('tbl_prod_refund_request_messages', array("refmsg_request"=>$id,"refmsg_from"=>$user_id,"refmsg_text"=>sprintf(Utilities::getLabel('L_Return_Request_Escalated'),Settings::getSetting("CONF_WEBSITE_NAME")),"refmsg_date"=>date('Y-m-d H:i:s')), true) && $this->db->commitTransaction()){
			return true;
		}
		$this->error = $this->db->getError();
		$this->db->rollbackTransaction();
		return false;
	}
	
	function withdrawRequest($id,$user_id,$action_by="U"){
		$id = intval($id);
		$user_id = intval($user_id);
		if($id < 1 || $user_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		
		$pObj=new Products();
		$return_request=$pObj->getReturnRequest($id);
		if($this->db->update_from_array('tbl_prod_refund_requests', array('refund_request_status'=>3,'refund_request_updated_by'=>$user_id,'refund_request_action_by'=>$action_by), array('smt'=>'refund_id=?', 'vals'=>array($id))) && $this->db->insert_from_array('tbl_prod_refund_request_messages', array("refmsg_request"=>$id,"refmsg_from"=>$user_id,"refmsg_text"=>Utilities::getLabel('L_Return_Request_Withdrawn'),"refmsg_date"=>date('Y-m-d H:i:s'),"refmsg_from_type"=>$action_by), true)){
			$oObj = new Orders();
			$oObj->addChildOrderHistory($return_request["refund_order"],Settings::getSetting("CONF_RETURN_REQUEST_WITHDRAWN_ORDER_STATUS"),Utilities::getLabel('M_Buyer_Withdrawn_Return_Request'),1);
			
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function approveRequest($id,$user_id,$action_by="U"){
		$id = intval($id);
		$user_id = intval($user_id);
		if($id < 1 || $user_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		$p=new Products();
		$return_request=$p->getReturnRequest($id);
		
		$approved_by=($action_by=="U")?$return_request["opr_shop_owner_name"]:Settings::getSetting("CONF_WEBSITE_NAME");
		$approved_by_label = ($action_by=="U")?sprintf(Utilities::getLabel('M_Approved_Return_Request'),$return_request['opr_shop_owner_name']):sprintf(Utilities::getLabel('M_Approved_Return_Request'),Settings::getSetting("CONF_WEBSITE_NAME"));
		
		if($this->db->update_from_array('tbl_prod_refund_requests', array('refund_request_status'=>2,'refund_request_updated_by'=>$user_id,'refund_request_action_by'=>$action_by), array('smt'=>'refund_id=?', 'vals'=>array($id))) && $this->db->insert_from_array('tbl_prod_refund_request_messages', array("refmsg_request"=>$id,"refmsg_from"=>$user_id,"refmsg_text"=>sprintf(Utilities::getLabel('L_Return_Request_Approved_By'),$approved_by),"refmsg_date"=>date('Y-m-d H:i:s'),"refmsg_from_type"=>$action_by), true)){
			
				$refund_amount=($return_request["opr_customer_buying_price"]+$return_request["opr_customer_customization_price"])*$return_request["refund_qty"];
				$refund_tax=round($refund_amount*$return_request["order_vat_perc"]/100,2);
				
				$refund_commission=round(($refund_amount*$return_request['opr_commission_percentage']/100),2);
				$refund_commission=min($refund_commission,Settings::getSetting("CONF_MAX_COMMISSION"));
				
				$affiliate_commission=$return_request["opr_affiliate_commission"] - round($refund_amount*$return_request["opr_affiliate_commission_percentage"]/100,2);
				
				if($this->db->update_from_array('tbl_order_products', array('opr_refund_qty'=>$return_request["refund_qty"],'opr_refund_tax'=>$refund_tax,'opr_refund_amount'=>$refund_amount,'opr_refund_commission'=>$refund_commission,'opr_affiliate_commission'=>$affiliate_commission), array('smt'=>'opr_id=?', 'vals'=>array($return_request["refund_order"])))){
					$oObj = new Orders();
					$oObj->addChildOrderHistory($return_request["refund_order"],Settings::getSetting("CONF_RETURN_REQUEST_APPROVED_ORDER_STATUS"),$approved_by_label,1);
				}
				return true;	
					
		}
		$this->error = $this->db->getError();
		$this->db->rollbackTransaction();
		return false;
	}
	
	
	function getSupplierFormFields() {
		$srch = new SearchBase('tbl_user_supplier_form_fields', 'tsff');
		$srch->addCondition('tsff.sformfield_is_active', '=',1);
		$srch->addOrder('tsff.sformfield_order', 'asc');
		
        $rs = $srch->getResultSet();
        $row = $this->db->fetch_all($rs);
        if($row==false) return array();
        else return $row;
	}
	
	function addUpdateSupplierFormFields($data){
		if (!$this->db->update_from_array('tbl_user_supplier_form_fields', array('sformfield_is_active' => 0), array('smt' => '1 = ?', 'vals' => array(1)))){	
			$this->error = $this->db->getError();
			return false;
		}
		
		$record = new TableRecord('tbl_user_supplier_form_fields');
		if (isset($data['form_field'])) {
			foreach ($data['form_field'] as $key=>$val){
				$arr=array("sformfield_id"=>(int)$val["id"],"sformfield_type"=>$val["type"],"sformfield_caption"=>$val["caption"],"sformfield_extra"=>$val["extra"],"sformfield_required"=>(int)$val["required"],"sformfield_order"=>(int)$val["order"],"sformfield_mandatory"=>(int)$val["mandatory"],"sformfield_is_active"=>1);
				$record->assignValues($arr);
				foreach($arr as $skey => $sval){
					$fields[] = "$skey = ".$this->db->quoteVariable($sval)."";
				}
				if (($val["caption"]!="") && ($val["type"]!="")){ 
					$sqlquery=$record->getinsertquery();
					$sqlquery = $sqlquery." on duplicate KEY UPDATE " . join(', ', $fields);
					//die($sqlquery);
					if (!$this->db->query($sqlquery)){
						$this->error = $this->db->getError();
						return false;
					}
				}
			}
		}
		
		return true;
	}	
	
	/*function canAccessSupplierDashboard($user_id){
		$user_id = intval($user_id);
		if($user_id < 1) return false;
		$user = self::getUser(array('id'=>$user_id),false);
		return (!Settings::getSetting("CONF_ADMIN_APPROVAL_SUPPLIER_REGISTRATION") || (Settings::getSetting("CONF_ADMIN_APPROVAL_SUPPLIER_REGISTRATION") && in_array($user["user_type"],array_merge(array(CONF_SELLER_USER_TYPE),array(CONF_BUYER_SELLER_USER_TYPE)))))?true:false;
	}
	
	function canAccessBuyerDashboard($user_id){
		$user_id = intval($user_id);
		if($user_id < 1) return false;
		$user = self::getUser(array('id'=>$user_id),false);
		return (in_array($user["user_type"],array_merge(array(CONF_BUYER_USER_TYPE),array(CONF_BUYER_SELLER_USER_TYPE))))?true:false;
	}
	
	function canAccessAdverriserDashboard($user_id){
		$user_id = intval($user_id);
		if($user_id < 1) return false;
		$user = self::getUser(array('id'=>$user_id),false);
		return (in_array($user["user_type"],(array)CONF_ADVERTISER_USER_TYPE))?true:false;
	}*/
	
	protected function loadRequestData() {
        $this->attributes = self::getUserSupplierRequests(array("id"=>$this->request_id,"pagesize"=>1));
    }
  
	
	function addSupplierRequestData($data){
		$user_id = intval($data['user']);
		unset($data['user_id']);
		if (($user_id < 1)){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		
		$record = new TableRecord('tbl_user_supplier_requests');
		$assign_fields = array();
		$assign_fields['usuprequest_user_id'] = $user_id;
		$assign_fields['usuprequest_reference'] = $data["reference"];
		$assign_fields['usuprequest_date'] = date('Y-m-d H:i:s');
		$assign_fields['usuprequest_attempts'] = 1;
		$assign_fields['usuprequest_status'] = Settings::getSetting("CONF_ADMIN_APPROVAL_SUPPLIER_REGISTRATION")?0:1;
		$record->assignValues($assign_fields);
		$sql= str_replace("INSERT","INSERT IGNORE",$record->getinsertquery()." ON DUPLICATE KEY UPDATE `usuprequest_status`=0,`usuprequest_attempts` = `usuprequest_attempts`+1");
		if ($this->db->query($sql)){
			if ($this->db->insert_id()>0)
				$this->request_id=$this->db->insert_id();
			else	
				$this->request_id=$data['id'];
		}else{
			$this->error = $this->db->getError();
			return false;
		}
		$this->loadRequestData();
		$supplier_request_id=$this->request_id;
		if ($supplier_request_id>0) {
			if (!$this->db->deleteRecords('tbl_user_supplier_request_values', array('smt' => 'sfreqvalue_request_id = ?', 'vals' => array($supplier_request_id)))){
				$this->error = $this->db->getError();
				return false;
			}
		
			$record = new TableRecord('tbl_user_supplier_request_values');
			if (isset($data['sformfield'])) {
				foreach ($data['sformfield'] as $key=>$val){
					$record->assignValues(array("sfreqvalue_request_id"=>(int)$supplier_request_id,"sfreqvalue_formfield_id"=>(int)$key,"sfreqvalue_text"=>$val));	
					if (!$record->addNew()){
						$this->error = $this->db->getError();
						return false;
					}
				}
			}
			
			
			if (!Settings::getSetting("CONF_ADMIN_APPROVAL_SUPPLIER_REGISTRATION")){	
				$uObj = new User();
				$user = $uObj->getUser(array('user_id'=>$user_id, 'get_flds'=>array('user_type')), false);
				$user_type = $user["user_type"]>0?CONF_BUYER_SELLER_USER_TYPE:CONF_SELLER_USER_TYPE;
				if (!$this->db->update_from_array('tbl_users', array('user_type' => $user_type),
						array('smt' => 'user_id = ? ', 'vals' => array((int)$user_id)))){
						$this->error = $this->db->getError();
						return false;
				}
			}
			
			$emailNotObj=new Emailnotifications();	
			if (!$emailNotObj->sendNotifyAdminSupplierApproval($user_id)){
					$this->error=$emailNotObj->getError();
					return false;
			}
		}
		return true;
	}
	
	function getUserSupplierRequests($criteria=array()){
        $srch = new SearchBase('tbl_user_supplier_requests', 'tusr');
		$srch->joinTable('tbl_users', 'INNER JOIN', 'tusr.usuprequest_user_id=tu.user_id and tu.user_is_deleted=0', 'tu');
		foreach($criteria as $key=>$val) {
        if(strval($val)=='') continue;
	        switch($key) {
    		    case 'id':
        		    $srch->addCondition('tusr.usuprequest_id', '=', intval($val));
            	break;
				case 'user':
        		    $srch->addCondition('tusr.usuprequest_user_id', '=', intval($val));
            	break;
				case 'keyword':
					$srch->addDirectCondition('(tu.user_name LIKE '. $this->db->quoteVariable('%' . $val . '%') .' OR tu.user_email LIKE '. $this->db->quoteVariable('%' . $val . '%') .' OR tu.user_username LIKE '. $this->db->quoteVariable('%' . $val . '%') .' OR tusr.usuprequest_reference LIKE '. $this->db->quoteVariable('%' . $val . '%') .' )');
            	break;
				case 'status':
				    if (is_array($val)){
        		    	$srch->addCondition('tusr.usuprequest_status', 'IN', $val);
					}else{
						$srch->addCondition('tusr.usuprequest_status', '=', intval($val));
					}
            	break;
				case 'date_from':
        	        $srch->addCondition('tusr.usuprequest_date', '>=', $val. ' 00:00:00');
                break;
				case 'date_to':
            	    $srch->addCondition('tusr.usuprequest_date', '<=', $val. ' 23:59:59');
                break;
				case 'page':
					$srch->setPageNumber($val);
				break;	
				case 'pagesize':
	            	$srch->setPageSize($val);
	    	    break;
	        }
        }
		$srch->addOrder("case when usuprequest_status = '0' then 1 else 2 end",'ASC');
		$srch->addOrder('usuprequest_id','DESC');
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		return $criteria["pagesize"]==1?$this->db->fetch($rs):$this->db->fetch_all($rs);
	}
	
	function getSupplierRequestFieldsValues($id){
        $srch = new SearchBase('tbl_user_supplier_requests', 'tusr');
		$srch->joinTable('tbl_user_supplier_request_values', 'INNER JOIN', 'tusr.usuprequest_id=tusrv.sfreqvalue_request_id', 'tusrv');
		$srch->joinTable('tbl_user_supplier_form_fields', 'INNER JOIN', 'tusrv.sfreqvalue_formfield_id=tusff.sformfield_id', 'tusff');
		$srch->addCondition('tusr.usuprequest_id', '=', intval($id));
		$rs = $srch->getResultSet();
		return $this->db->fetch_all($rs);
	}
	
	function updateSupplierRequest($srequest_id, $status, $comment = '') {
		$supplier_request=$this->getUserSupplierRequests(array("id"=>$srequest_id,"pagesize"=>1));
		if ($supplier_request) {
			if (!in_array($supplier_request['usuprequest_status'],array("1","2")) && in_array($status,array("1","2"))) {
				if (!$this->db->update_from_array('tbl_user_supplier_requests', array('usuprequest_status' => (int)$status,'usuprequest_comments' => $comment),
					array('smt' => 'usuprequest_id = ? ', 'vals' => array((int)$srequest_id)))){
					$this->error = $this->db->getError();
					return false;
				}
				$emailNotificationObj=new Emailnotifications();
				$emailNotificationObj->SendSupplierRequestStatusChangeNotification($srequest_id);
			}
			
			if (!in_array($supplier_request['usuprequest_status'],array("1")) && in_array($status,array("1"))) {
				
				$uObj = new User();
				$user = $uObj->getUser(array('user_id'=>$supplier_request['usuprequest_user_id'], 'get_flds'=>array('user_type')), false);
				$user_type = $user["user_type"]>0?CONF_BUYER_SELLER_USER_TYPE:CONF_SELLER_USER_TYPE;
				if (!$this->db->update_from_array('tbl_users', array('user_type' => $user_type),
						array('smt' => 'user_id = ? ', 'vals' => array((int)$supplier_request["user_id"])))){
						$this->error = $this->db->getError();
						return false;
				}
			}
		}
		return true;
	}
	
	function createUserTempToken($data){
		$user_id = intval($data['user_id']);
		if($user_id < 1 || strlen($data['temp_token']) != 25){
			return false;
		}
		$this->db->deleteRecords('tbl_user_temp_token_requests', array('smt'=>'`uttr_user_id`=?', 'vals'=>array($user_id)));
		if($this->db->insert_from_array('tbl_user_temp_token_requests', array(
				'uttr_user_id'=>$user_id,
				'uttr_token'=>$data['temp_token'],
				'uttr_expiry'=>$data['token_expiry']
			)
		)) return true;
		$this->error = $this->db->getError();
		return false;
	}
	
	function addLoginAttempt($username_email){
		$srch = new SearchBase('tbl_user_login');
		$cnd=$srch->addCondition('ulogin_username_email', '=', $username_email);
		$srch->addCondition('ulogin_ip', '=',$_SERVER['REMOTE_ADDR']);
		$rs = $srch->getResultSet();
		if(!$row = $this->db->fetch($rs)) {
				if(!$this->db->insert_from_array('tbl_user_login', array(
					'ulogin_username_email'=>$username_email,
					'ulogin_ip'=>$_SERVER['REMOTE_ADDR'],
					'ulogin_total'=>1,
					'ulogin_date_added'=>date('Y-m-d H:i:s'),
					'ulogin_date_modified'=>date('Y-m-d H:i:s'),
					),true
				)){
					$this->error = $this->db->getError();
					return false;
				}
			}else{
				if(!$this->db->update_from_array('tbl_user_login', 
							array(
								"ulogin_total"=>$row['ulogin_total']+1,
								"ulogin_date_modified"=>date('Y-m-d H:i:s')), 
							array(
								'smt'=>'`ulogin_id` = ?', 
								'vals'=> array($row['ulogin_id'])),true)){
					$this->error = $this->db->getError();
					return false;
			}
		};
		
		return true;
	}
	
	public function getLoginAttempts($username_email) {
			$srch = new SearchBase('tbl_user_login');
			$cnd=$srch->addCondition('ulogin_username_email', '=', $username_email);
			$rs = $srch->getResultSet();
			return $row = $this->db->fetch($rs);
	}
	public function deleteLoginAttempts($username_email) {
		if($this->db->deleteRecords('tbl_user_login', array('smt' => 'ulogin_username_email = ?', 'vals' => array($username_email)))){
			return true;
		}
	}
	
	/*public function expireRewardPoints($user_id) {
		if($this->db->update_from_array('tbl_user_reward_points', array('urp_is_expired' => 1), array('smt'=>'`urp_user_id` = ? AND urp_date_expiry <= CURRENT_DATE() AND urp_date_expiry!="0000-00-00"', 'vals'=> array($user_id)))){
			return true;
		}
	}*/
	
	function deleteRememberMeToken($user_id){
		$user_id = intval($user_id);
		if ($this->db->deleteRecords('tbl_user_remember_me_tokens', array('smt'=>'`urt_user_id`=?', 'vals'=>array($user_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function updateRememberMeToken($data){
		$user_id = intval($data['user_id']);
		if($user_id < 1 || strlen($data['remember_token']) != 25){
			return false;
		}
		$this->db->deleteRecords('tbl_user_remember_me_tokens', array('smt'=>'`urt_user_id`=?', 'vals'=>array($user_id)));
		if($this->db->insert_from_array('tbl_user_remember_me_tokens', array(
				'urt_user_id'=>$user_id,
				'urt_browser'=>$_SERVER['HTTP_USER_AGENT'],
				'urt_token'=>$data['remember_token'],
				'urt_expiry'=>$data['token_expiry']
			)
		)) return true;
		$this->error = $this->db->getError();
		return false;
	}
	
	function validateRememberMeToken($token){
		if(strlen($token) != 25){
			return false;
		}
		$srch = new SearchBase('tbl_user_remember_me_tokens');
		$srch->addCondition('urt_token', '=', $token);
		$srch->addCondition('urt_browser', '=', $_SERVER['HTTP_USER_AGENT']);
		$srch->addCondition('urt_expiry', '>', date('Y-m-d H:i:s'));
		$srch->addMultipleFields(array('urt_user_id', 'urt_token'));
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();
		if(!$row = $this->db->fetch($rs)){
			return false;
		}
		if($row['urt_token'] !== $token){
			return false;
		}
		return $row;
	}
	
	function addUserRequest($data){
		$user_id = intval($data['user_id']);
		unset($data['user_id']);
		if (($user_id < 1)){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		$assign_fields["urequest_user_id"]=$user_id;
		$assign_fields["urequest_type"]=$data['type'];
		$assign_fields["urequest_date"]=date('Y-m-d H:i:s');
		$assign_fields["urequest_status"]=0;
		$assign_fields["urequest_text"]=$data["request"];
		if($this->db->insert_from_array('tbl_user_requests', $assign_fields, true)){
			$this->user_request_id=$this->db->insert_id();
			return true;
		}else{
			return false;
		}
		
	}
	
	function getUserRequests($criteria=array()){
        $srch = new SearchBase('tbl_user_requests', 'tur');
		$srch->joinTable('tbl_users', 'INNER JOIN', 'tur.urequest_user_id=tu.user_id', 'tu');
		foreach($criteria as $key=>$val) {
        if(strval($val)=='') continue;
	        switch($key) {
    		    case 'id':
        		    $srch->addCondition('tur.urequest_id', '=', intval($val));
            	break;
				case 'type':
        		    $srch->addCondition('tur.urequest_type', '=',$val);
            	break;
				case 'user':
        		    $srch->addCondition('tur.urequest_user_id', '=', intval($val));
            	break;
				case 'text':
        		    $srch->addCondition('tur.urequest_text', '=', ($val));
            	break;
				case 'status':
        		    $srch->addCondition('tur.urequest_status', '=', intval($val));
            	break;
				case 'date_from':
        	        $srch->addCondition('tur.urequest_date', '>=', $val. ' 00:00:00');
                break;
				case 'date_to':
            	    $srch->addCondition('tur.urequest_date', '<=', $val. ' 23:59:59');
                break;
				case 'page':
					$srch->setPageNumber($val);
				break;	
				case 'pagesize':
	            	$srch->setPageSize($val);
	    	    break;
	        }
        }
		$srch->addOrder("case when urequest_status = '0' then 1 else 2 end",'ASC');
		$srch->addOrder('urequest_id','DESC');
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		return $criteria["pagesize"]==1?$this->db->fetch($rs):$this->db->fetch_all($rs);
	}
	
	function getUserRequestById($urequest_id){
		$request = self::getUserRequests(array("id"=>$urequest_id,"pagesize"=>1));
		if(!$request){
			return false;
		}
		return $request;
	}
	
	function updateUserRequestStatus($urequest_id,$data_update=array()) {
		$urequest_id = intval($urequest_id);
		if($urequest_id < 1 || count($data_update) < 1){
			$this->error = 'Error: Invalid request!!';
			return false;
		}
		if($this->db->update_from_array('tbl_user_requests', $data_update, array('smt'=>'`urequest_id` = ?', 'vals'=> array($urequest_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function getMessagesThreads($criteria=array(),$pagesize=0){
		
		$srchSqlQuery = "SELECT t.* FROM (SELECT * FROM tbl_thread_messages where message_is_deleted=0 ORDER BY message_id DESC) t GROUP BY t.message_thread";
		
		$srch = new SearchBase('tbl_threads', 'tthr');
		$srch->joinTable('(' . $srchSqlQuery . ')', 'INNER JOIN', 'tthr.thread_id=ttm.message_thread', 'ttm');
		$srch->joinTable('tbl_users', 'INNER JOIN', 'ttm.message_from=tmf.user_id', 'tmf');
		$srch->joinTable('tbl_users', 'INNER JOIN', 'ttm.message_to=tmt.user_id', 'tmt');		
		$srch->addMultipleFields(array('tthr.*','ttm.*','tmf.user_id as message_sent_by','tmf.user_username as message_sent_by_username','tmf.user_profile_image as message_sent_by_profile','tmt.user_id as message_sent_to','tmt.user_username as message_sent_to_username','tmt.user_email as message_sent_to_email','tmt.user_name as message_sent_to_name','tmt.user_profile_image as message_sent_to_profile','UNIX_TIMESTAMP(ttm.message_date) as message_date_timestamp'));
		foreach($criteria as $key=>$val) {
            switch($key) {
            case 'user':
                	$cndCondition=$srch->addCondition('ttm.message_from', '=', intval($val));
					$cndCondition->attachCondition('message_to', '=', intval($val),'OR');
                break;
			case 'page':
				$srch->setPageNumber(intval($val));
				break;
			case 'keyword':
                if ($val!=""){
					$val=urldecode($val);
					$cndCondition=$srch->addCondition('tthr.thread_subject', 'like', '%' . $val . '%');
					$cndCondition->attachCondition('tmf.user_username', 'like', '%' . $val . '%','OR');
					$cndCondition->attachCondition('tmt.user_username', 'like', '%' . $val . '%','OR');
					$cndCondition->attachCondition('ttm.message_text', 'like', '%' . $val . '%','OR');
				}	
                break;			
            }
        }
		if(intval($pagesize)>0){
			$srch->setPageSize($pagesize);
		}else{
			$srch->doNotLimitRecords();
		}
		$srch->addOrder('thread_id','desc');
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		return $this->db->fetch_all($rs);
	}
	static function isSupplierRegistered() {
		$uObj = new User();
		$user = $uObj->getUser(array('user_id'=>$_SESSION['registered_supplier']['id'], 'get_flds'=>array('user_password,user_username')), false);
		if(isset($_SESSION['registered_supplier']) && ($_SESSION['registered_supplier']>0) && $user){
			return true;
		}
        return false;
		
    }
	
	function canAccessMyAccountPage($action){
		$advertiser_pages = array(
								"dashboard_advertiser",
								"request_withdrawal",
								"credits",
								"promote",
								"promote_banner",
								"promote_form",
								"promotion_status",
								"promotion_clicks",
								"promotion_analytics"
							);
		$buyer_pages = array(
								"dashboard_buyer",
								"supplier_approval_form",
								"view_supplier_request",
								"bank_info",
								"recent_activity",
								"ajax_recent_activity",
								"orders",
								"view_order",
								"print_order",
								"feedback",
								"addresses",
								"address_form",
								"default_address",
								"delete_address",
								"send_message",
								"messages",
								"view_message",
								"share_earn",
								"send_email",
								"reward_points",
								"return_request",
								"return_requests",
								"cancellation_requests",
								"cancellation_request",
								"favorites",
								"favorite_shops",
								"favorite_items",
								"view_list",
								"delete_list",
								"view_return_request",
								"escalate_request",
								"withdraw_request",
								"ajax_load_favorite_products_json",
								"ajax_load_list_products_json",
								"downloads",
								"download_file"
							);
		$seller_pages = array(
							"dashboard_supplier",
							"bank_info",
							"messages",
							"view_message",
							"share_earn",
							"send_email",
							"reward_points",
							"sales",
							"sales_view_order",
							"sales_print_order",
							"cancel_order",
							"publications",
							"paused_publications",
							"finalized_publications",
							"product_form",
							"brand_request",
							"product_tag",
							"shop",
							"promote",
							"promote_product",
							"promote_shop",
							"promote_banner",
							"promote_form",
							"promotion_status",
							"promotion_clicks",
							"promotion_analytics",
							"options",
							"option_form",
							"return_request",
							"return_requests",
							"cancellation_requests",
							"cancellation_request",
							"favorites",
							"favorite_shops",
							"favorite_items",
							"view_list",
							"delete_list",
							"view_return_request",
							"escalate_request",
							"withdraw_request",
							"approve_request",
							"remove_product",
							"finalize_product",
							"product_status",
							"product_setup_info",
							"ajax_load_favorite_products_json",
							"ajax_load_list_products_json",
							"packages",
							"subscriptions",
							"view_subscription",
							"cancel_subscription",
							"confirm_change_subscription",
							"save_image_orientation",
							"setProductImagesOrdering"
						);
		
		$common_pages = array(
							"default_action",
							"profile_info",
							"change_email",
							"credits",
							"request_withdrawal",
							"change_password",
							"download_attachment",
							"referral_tracking_url",
							"twitter_callback",
							"payment_failed",
							"payment_success",
							"logout"
						);
		$user_type = $this->getAttribute('user_type');
		if (in_array($user_type,(array)CONF_ADVERTISER_USER_TYPE)){
			if (in_array($action,array_merge($advertiser_pages,$common_pages))){
				return true;
			}
		}elseif (in_array($user_type,array(CONF_BUYER_USER_TYPE))){
			if (in_array($action,array_merge($buyer_pages,$common_pages))){
				$this->tab = 'B';
				return true;
			}else{
				if (in_array($action,$seller_pages)){
					$this->redirectPage=Utilities::generateUrl('account','supplier_approval_form');
					return false;
				}
			}
		}elseif (in_array($user_type,array(CONF_SELLER_USER_TYPE))){
			if (in_array($action,array_merge($seller_pages,$common_pages))){
				$pmObj=new Paymentmethods();
				$payment_method=$pmObj->getPaymentMethodByCode(CONF_PAPYAL_ADAPTIVE_KEY);
				if ($payment_method && $payment_method['pmethod_status'] && ($action!="shop") && ($action!="logout")  && !empty(
					$this->getAttribute("shop_id")) && $this->getAttribute("shop_paypal_account_verified")!=1){
					$this->error=Utilities::getLabel('M_ERROR_PAYPAL_ACCOUNT_VERIFIED');
					$this->redirectPage=Utilities::generateUrl('account','shop');
					return false;
				}
				$this->tab = 'S';
				return true;
			}
		}elseif (in_array($user_type,array(CONF_BUYER_SELLER_USER_TYPE))){
			$buyer_seller_pages= array_merge($seller_pages,$buyer_pages);
			if (in_array($action,array_merge($buyer_seller_pages,$common_pages))){
				$pmObj=new Paymentmethods();
				$payment_method=$pmObj->getPaymentMethodByCode(CONF_PAPYAL_ADAPTIVE_KEY);
				if ($payment_method && $payment_method['pmethod_status'] && ($action!="shop") && !empty(
				$this->getAttribute("shop_id")) && $this->getAttribute("shop_paypal_account_verified")!=1){
					$this->error=Utilities::getLabel('M_ERROR_PAYPAL_ACCOUNT_VERIFIED');
					$this->redirectPage=Utilities::generateUrl('account','shop');
					return false;
				}
				$urlArray = explode("/",strtok($_SERVER['HTTP_REFERER'],"?"));
				$ref_action=$urlArray[count($urlArray)-1];
				$buyer_seller_common_pages= array_intersect($seller_pages,$buyer_pages);
				if (in_array($action,array_merge($buyer_seller_common_pages,$common_pages))){
					$tab=$_SESSION["buyer_supplier_tab"];
				}elseif (in_array($action,$buyer_pages)){
					$tab="B";
				}elseif (in_array($action,$seller_pages)){
					$tab="S";
				}
				$this->tab = $tab;
				return true;
			}
		}
		$this->redirectPage=Utilities::generateUrl('account');
		return false;
	}
	
	function canAccessSupplierDashboard($user_id){
		$user_id = intval($user_id);
		if($user_id < 1) return false;
		$user = self::getUser(array('id'=>$user_id),false);
		return (!Settings::getSetting("CONF_ADMIN_APPROVAL_SUPPLIER_REGISTRATION") || (Settings::getSetting("CONF_ADMIN_APPROVAL_SUPPLIER_REGISTRATION") && in_array($user["user_type"],array_merge(array(CONF_SELLER_USER_TYPE),array(CONF_BUYER_SELLER_USER_TYPE)))))?true:false;
	}
	
	function getUserUnsettledPPCPayment($user_id){
		$pending_ppc_payment=0;
		$prmObj=new Promotions();
		$promotions = $prmObj->getPromotions(array("user"=>$user_id));
		foreach($promotions as $pkey=>$pval){
			$promotion_id = $pval['pclick_promotion_id'];
			if ($promotion_id>0){
				$promotion_last_charged=$prmObj->getPromotionLastChargedEntry($promotion_id);
				$promotion_clicks= $prmObj->getPromotionClicksSummary(array("promotion"=>$promotion_id,"start_id"=>$promotion_last_charged['pcharge_end_click_id'],"pagesize"=>1));
				$pending_ppc_payment += $promotion_clicks['total_cost'];
			}
		}
		//die($pending_ppc_payment."#");
		return $pending_ppc_payment;
	}
	
	 static function getUserIdFromCookies() {
		$user_id = 0;
		if(isset($_COOKIE['uc_id'])){
			$user_id = $_COOKIE['uc_id'];
		}
        return $user_id;
    }
	
	
	function getDeletedUsers($criteria) {
		foreach($criteria as $key=>$val) {
        	if(strval($val)=='') continue;
				$add_criteria[$key] = $val;
		}
        $srch = self::search($add_criteria);
		$srch->addOrder("user_status","desc");
		$srch->addOrder("user_id","desc");
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		if($this->total_records < 1){
			return false;
		}
        return $this->db->fetch_all($rs);
    }
	
	function getUserDownloads($criteria=array()){
        $srch = new SearchBase('tbl_order_product_files', 'topf');
		$srch->joinTable('tbl_order_products', 'INNER JOIN', 'topf.opf_opr_id=torp.opr_id', 'torp');
		$srch->joinTable('tbl_orders', 'INNER JOIN', 'torp.opr_order_id=tord.order_id', 'tord');
		$srch->addCondition('tord.order_payment_status', '=',1);
		foreach($criteria as $key=>$val) {
        if(strval($val)=='') continue;
	        switch($key) {
    		    case 'id':
        		    $srch->addCondition('topf.opf_id', '=', intval($val));
            	break;
				case 'user':
        		    $srch->addCondition('tord.order_user_id', '=', intval($val));
            	break;
				case 'status':
					if (is_array($val))
	                	$srch->addCondition('torp.opr_status', 'IN', $val);
					else
						$srch->addCondition('torp.opr_status', '=', intval($val));	
        	    break;
				case 'downloads_remaining':
        		    $cnd=$srch->addCondition('topf.opf_remaining_downloaded_times', '>', 0);
					$cnd->attachCondition('topf.opf_remaining_downloaded_times', '=','-1','OR');
            	break;
				case 'page':
					$srch->setPageNumber($val);
				break;	
				case 'pagesize':
	            	$srch->setPageSize($val);
	    	    break;
	        }
        }
		$srch->addOrder('opf_id','DESC');
		$rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
		return $criteria["pagesize"]==1?$this->db->fetch($rs):$this->db->fetch_all($rs);
	}
	
	function incrementUserFileDownloadCount($file_id){
		$file_id = intval($file_id);
		if($file_id < 1){
			$this->error = Utilities::getLabel('M_INVALID_REQUEST');
			return false;
		}
		if($this->db->update_from_array('tbl_order_product_files', array('opf_remaining_downloaded_times' => 'mysql_func_opf_remaining_downloaded_times-1'), array('smt'=>'`opf_id`=? and opf_remaining_downloaded_times != ?', 'vals'=>array($file_id,'-1')),true)){	
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
}