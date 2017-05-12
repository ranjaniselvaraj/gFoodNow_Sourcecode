<?php
class Admin extends Model {
	protected $db;
	function __construct() {
		$this->db = Syspage::getdb();
    }
	
	protected function loadData() {
        $this->attributes = self::getAdminById($this->admin_id);
    }
	
	function getData() {
        return $this->attributes;
    }
    function getAttribute($attr) {
        return isset($this->attributes[$attr])?$this->attributes[$attr]:'';
    }
	
    function login($username, $password,$passwordAlreadyEncripted=false) {
        $srch = new SearchBase('tbl_admin');
		$srch->addCondition('admin_username', '=', $username);
		if (!$passwordAlreadyEncripted){
			$password = Utilities::encryptPassword($password);
		}
		$srch->addCondition('admin_password', '=', $password);
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();
		if (!$row = $this->db->fetch($rs)) {
			$this->error = 'Invalid username or password';
			return false;
		}
		if ($row['admin_username'] !== $username || $row['admin_password'] !== $password) {
			$this->error = 'Invalid username or password';
			return false;
		}
		$this->admin_id = $row["admin_id"];
		$this->loadData();
		$_SESSION['logged_admin']['admin_logged_id'] = $row['admin_id'];
		$_SESSION['logged_admin']['admin_logged'] = 1;
		$_SESSION['logged_admin']['admin_username'] = $row['admin_username'];
		$_SESSION['logged_admin']['admin_name'] = $row['admin_full_name'];
		$_SESSION['logged_admin']['super_admin'] = $row['admin_is_super_admin'];
		
		return true;
    }
    static function isLogged() {
		if (isset($_SESSION['logged_admin'])){
			if(strlen($_SESSION['logged_admin']['admin_username']) < 4 || intval($_SESSION['logged_admin']['admin_logged_id']) <= 0){
				unset($_SESSION['logged_admin']['admin_logged']);
				unset($_SESSION['logged_admin']['admin_logged_id']);
				unset($_SESSION['logged_admin']['admin_username']);
				unset($_SESSION['logged_admin']['admin_name']);
				unset($_SESSION['logged_admin']['super_admin']);
			}
		}
		
		if (isset($_SESSION['logged_admin'])){
			if ($_SESSION['logged_admin']['admin_logged']===1){
				return true;
			}
		}
		
		if(isset($_COOKIE['remembertoken'])){
			$adminObj = new Admin();
			if ($adminToken=$adminObj->validateRememberMeToken($_COOKIE['remembertoken'])){
				$admin = $adminObj->getAdminById($adminToken['art_admin_id']);
				if($adminObj->login($admin['admin_username'], $admin['admin_password'],true)){
					return true;
				}
			}
		}
		return false;
    }
	
	function checkAdminEmail($email){
		$resp = array('exists'=>false);
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
			return $resp;
		}
		$srch = new SearchBase('tbl_admin');
		$srch->addCondition('admin_email', '=', $email);
		$srch->addMultipleFields(array('admin_id','admin_full_name','admin_email'));
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();
		if(!$row = $this->db->fetch($rs)){
			$this->error = 'Invalid email address!';
			return $resp;
		}
		if($row['admin_email'] !== $email) {
			$this->error = 'Invalid email address!';
			return $resp;
		}
		$resp = $row;
		$resp['exists'] = true;
		return $resp;
	}
	
	function checkAdminPwdResetRequest($admin_id){
		$srch = new SearchBase('tbl_admin_password_reset_requests');
		$srch->addCondition('apr_admin_id', '=', $admin_id);
		$srch->addCondition('apr_expiry', '>', date('Y-m-d H:i:s'));
		$srch->addFld('apr_admin_id');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();
		if(!$row = $this->db->fetch($rs)){
			return false;
		}
		$this->error = 'Your request to reset password has already been placed within last 24 hours. Please check your emails or retry after 24 hours of your previous request';
		return true;
	}
	
	function addPasswordResetRequest($data = array()){
		if(!isset($data['admin_id']) || intval($data['admin_id']) < 1 || strlen($data['token']) < 20){
			return false;
		}
		
		if($this->db->insert_from_array('tbl_admin_password_reset_requests', array(
				'apr_admin_id'=>intval($data['admin_id']),
				'apr_token'=>$data['token'],
				'apr_expiry'=>date('Y-m-d H:i:s', strtotime("+1 DAY"))
			)
		)) return true;
		
		return false;
	}
	
	function checkResetLink($a_id, $token){
		if(intval($a_id) < 1 || strlen($token) < 20){
			return false;
		}
		$srch = new SearchBase('tbl_admin_password_reset_requests');
		$srch->addCondition('apr_admin_id', '=', intval($a_id));
		$srch->addCondition('apr_token', '=', $token);
		$srch->addCondition('apr_expiry', '>', date('Y-m-d H:i:s'));
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();
		if(!$row = $this->db->fetch($rs)){
			$this->error = 'Link is invalid or expired!';
			return false;
		}
		if($row['apr_token'] === $token && $row['apr_admin_id'] == $a_id){
			return true;
		}
		$this->error = 'Link is invalid or expired!';
		return false;
	}
	function changeAdminPwd($a_id, $password){ /* Sending encrypted password to this function */
		if(!isset($_SESSION['fat_es-auth_change']) || $_SESSION['fat_es-auth_change'] !== true || intval($a_id) < 1 || strlen(trim($password)) < 10){
			$this->error = 'Invalid Request!';
			return false;
		}
		unset($_SESSION['fat_es-auth_change']);
		if($this->db->update_from_array('tbl_admin', array('admin_password'=>$password), array('smt'=>'admin_id=?', 'vals'=>array(intval($a_id))))){
			$this->db->query('delete from `tbl_admin_password_reset_requests` where apr_admin_id='.intval($a_id));
			return true;
		}
		return false;
	}
	
	private function checkCurrentPassword($admin_id, $current_password){
		$admin_id = intval($admin_id);
		if($admin_id < 1){
			return false;
		}
		
        $srch = new SearchBase('tbl_admin');
		$srch->addCondition('admin_id', '=', $admin_id);
		$password = Utilities::encryptPassword($current_password);
		$srch->addCondition('admin_password', '=', $password);
		$rs = $srch->getResultSet();
		if(!$row = $this->db->fetch($rs)){
			$this->error = 'Incorrect current password';
			return false;
		}
		
		if($row['admin_password'] !== $password){
			$this->error = 'Incorrect current password';
			return false;
		}
        return true;
    }
	
	function layoutUpdate($data)
	{
		$admin_id = intval($data['admin_id']);
		if (!($admin_id > 0))
            $admin_id = 0;
        unset($data['admin_id']);
		$arr_fields = array();
		
		if(isset($data['layout'])&& $data['layout']!=''){
			$arr_fields['admin_dashboard_layout']=$data['layout'];
		}
		
		if(isset($data['admin_color'])&& $data['admin_color']!=''){
			$arr_fields['admin_dashboard_color']=$data['admin_color'];
		}
		
		if ($admin_id > 0) {
			$success = $this->db->update_from_array('tbl_admin', $arr_fields, array('smt' => 'admin_id = ?', 'vals' => array($admin_id)));
		}else {
            return false;
        }	
		
		if (!$success) {
            $this->error = $this->db->getError();
            return false;
        }
        return true;
		
	}
	
	function getAdmin($data = array(), $password = false){
		$srch = new SearchBase('tbl_admin','ta');
		foreach($data as $key=>$val) {
		if(strval($val)=='') continue;
        switch($key) {
	        case 'email':
    	        $srch->addCondition('ta.admin_email', '=', $val);
            break;
			case 'username':
            	$srch->addCondition('ta.admin_username', '=', $val);
            break;
	        }
        }
		if(isset($data['get_flds']) && is_array($data['get_flds']) && sizeof($data['get_flds']) > 0){
			$data['get_flds'] = array_unique($data['get_flds']);
			$srch->addMultipleFields($data['get_flds']);
		}
		$rs = $srch->getResultSet();
		if(!$row = $this->db->fetch($rs)){
			return false;
		}
		if($password === false) unset($row['admin_password']);
		return $row;
	}
    function addUpdate($data) {
        $admin_id = intval($data['admin_id']);
        if (!($admin_id > 0))
            $admin_id = 0;
        unset($data['admin_id']);
        $arr_fields = array();
        $arr_fields['admin_username'] = $data['admin_username'];
        $arr_fields['admin_full_name'] = $data['admin_full_name'];
        $arr_fields['admin_email'] = $data['admin_email'];
		
		$admin = $this->getAdmin(array('username'=>$data["admin_username"]),false);
		if(isset($admin['admin_id']) && $admin['admin_id']!=$admin_id) {
			$this->error = 'This username already exists in our record.';
            return false;
        }
		$admin = $this->getAdmin(array('email'=>$data["admin_email"]),false);
		if(isset($admin['admin_id']) && $admin['admin_id']!=$admin_id) {
            $this->error = 'This Email Address already exists in our record.';
            return false;
        }
		
        if ($data['a_pwd'] != '')
            $arr_fields['admin_password'] = Utilities::encryptPassword($data['a_pwd']);
			
		
		
        if ($admin_id > 0) {
			$update = true;
			
			if(isset($data['a_current_pwd']) && strlen(trim($data['a_current_pwd'])) > 0) {
				if($data['admin_password'] != '' || ($this->checkCurrentPassword($admin_id, $data['a_current_pwd']) != true)){
					$update = false;
					unset($arr_fields['admin_password']);
					return false;
				}
			}
			
			if($update == true){
				$success = $this->db->update_from_array('tbl_admin', $arr_fields, array('smt' => 'admin_id = ?', 'vals' => array($admin_id)));
			}
        } else {
            $success = $this->db->insert_from_array('tbl_admin', $arr_fields);
        }
        if (!$success) {
            $this->error = $this->db->getError();
            return false;
        }
        return true;
    }
	
	function updateAdminPass($data)
	{
		$admin_id = intval($data['admin_id']);
        if (!($admin_id > 0))
            $admin_id = 0;
        unset($data['admin_id']);
        $arr_fields = array();
		
		$arr_fields['admin_password'] = Utilities::encryptPassword($data['a_pwd']);
			
		if ($admin_id > 0) {
			$update = true;
			
			if(isset($data['a_current_pwd']) && strlen(trim($data['a_current_pwd'])) > 0) {
				if($data['admin_password'] != '' || ($this->checkCurrentPassword($admin_id, $data['a_current_pwd']) != true)){
					$update = false;
					unset($arr_fields['admin_password']);
					return false;
				}
			}
			
			if($update == true){
				$success = $this->db->update_from_array('tbl_admin', $arr_fields, array('smt' => 'admin_id = ?', 'vals' => array($admin_id)));
			}
        } 
        if (!$success) {
            $this->error = $this->db->getError();
            return false;
        }
        return true;	
	}
	
	function updateAdminImage($data){
		$admin_id = intval($data['admin_id']);
        if (!($admin_id > 0))
            $admin_id = 0;
        unset($data['admin_id']);
        $arr_fields = array();
		$arr_fields['admin_image']=$data['admin_image'];
		if ($admin_id > 0) {			
			$success = $this->db->update_from_array('tbl_admin', $arr_fields, array('smt' => 'admin_id = ?', 'vals' => array($admin_id)));	
		}
		if (!$success) {
            $this->error = $this->db->getError();
            return false;
        }		
	    return true;	
	}
	
    function getAdminById($id,$return_password=false) {
        if (!is_numeric($id)) {
            $this->error = 'Invalid Request';
            return false;
        }
        $id = intval($id);
        $record = new TableRecord('tbl_admin');
        if (!$record->loadFromDb(array('smt' => 'admin_id = ?', 'vals' => array($id)))) {
            $this->error = $record->getError();
            return false;
        }
        $data = $record->getFlds();
		if ($return_password)
	    unset($data['admin_password']);
        return $data;
    }
	
	
	
	function getAllAdmins(){
		$srch = new SearchBase('tbl_admin');
		$srch->doNotLimitRecords();
		$srch->addMultipleFields(array('admin_id', 'admin_full_name', 'admin_email', 'admin_username','admin_is_super_admin'));
		$srch->addOrder('admin_is_super_admin', 'desc');
		$srch->addOrder('admin_id', 'desc');
		$rs = $srch->getResultSet();
		if($srch->recordCount() < 1){
			return false;
		}
		return $this->db->fetch_all($rs);
	}
    
	
	function getPermissions($a_id, $module=''){
		if(intval($a_id) < 1) return false;
		$srch = new SearchBase('tbl_admin_permissions');
		$srch->addCondition('ap_admin_id', '=', intval($a_id));
		
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();
		if($srch->recordCount() < 1){
			return false;
		}
		$permissions = array();
		while($row = $this->db->fetch($rs)){
			$p = array();
			if(in_array($row['ap_permission'], array('1','3','5','7'))) $p[] = 1;
			$permissions[$row['ap_module']] = $p;
		}
		return $permissions;
	}
	
	function updatePermissions($data = array()){
		$admin_id = intval($data['admin_id']);
		unset($data['admin_id']);
		if($admin_id < 1 || $this->getAdminById($admin_id) == false) return false;
		
		$this->db->deleteRecords('tbl_admin_permissions', array('smt'=>'ap_admin_id=? ', 'vals'=>array($admin_id)));
		foreach($data["user_permissions"] as $key=>$val){
			$this->db->insert_from_array('tbl_admin_permissions',array('ap_admin_id'=>$admin_id, 'ap_module'=>$val[0], 'ap_permission'=>1), false, array(), array('ap_permission'=>intval($v)));
		}
		$error = $this->db->getError();
		if(strlen($error) < 1){
			return true;
		}
		$this->error = $error;
		return false;
	}
	
	static function isSuperAdminLogged() {
		return self::isLogged() && $_SESSION['logged_admin']['super_admin']==1;
    }
	
	static function getAdminAccess($admin_id, $module_id) {
		if(intval($admin_id) < 1) return false;
		if (self::isSuperAdminLogged())
			return true;
		$db = Syspage::getdb();	
		$srch = new SearchBase('tbl_admin_permissions');
		$srch->addCondition('ap_admin_id', '=', (int)($admin_id));
		$srch->addCondition('ap_module', '=', (int)$module_id);
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();
		if($srch->recordCount() < 1){
			return false;
		}
		$row = $db->fetch($rs);
		return $row['ap_permission']==1?true:false;
	}
	
	function getPermissionsAssocArr() {
		$srch = new SearchBase('tbl_admin_permission_names');
		$srch->addCondition('permission_is_active', '=',1);
		$srch->addOrder('permission_display_order','asc');
		$rs = $srch->getResultSet();
        $row = $this->db->fetch_all_assoc($rs);
        return $row;
    }
	
	function getAdminPermissionsArr($admin_id) {
		$srch = new SearchBase('tbl_admin_permissions');
		$srch->addCondition('ap_admin_id', '=', (int)$admin_id);
		$rs = $srch->getResultSet();
        $row = $this->db->fetch_all($rs);
        return $row;
    }
	
	function delete($admin_id){
		$admin_id = intval($admin_id);
		if($admin_id < 1){
			$this->error = 'Invalid request!!';
			return false;
		}
		if($this->db->deleteRecords('tbl_admin', array('smt'=>'admin_id=?', 'vals'=>array($admin_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	
	function updateAdminUserPassword($data)
	{
		$admin_id = intval($data['admin_id']);
        if (!($admin_id > 0))
            $admin_id = 0;
        unset($data['admin_id']);
        $arr_fields = array();
		
		$arr_fields['admin_password'] = Utilities::encryptPassword($data['user_password']);
		
		if ($admin_id > 0) {
			$update = true;
			
			if($data['user_password'] == '' || ($data['user_password']!=$data['user_conpassword'])){
				$update = false;
				unset($arr_fields['admin_password']);
				return false;
			}
			
			if($update == true){
				$success = $this->db->update_from_array('tbl_admin', $arr_fields, array('smt' => 'admin_id = ?', 'vals' => array($admin_id)));
			}
		}
		
		if (!$success) {
            $this->error = $this->db->getError();
            return false;
        }
        return true;
	}
	
	function deleteRememberMeToken($admin_id){
		$admin_id = intval($admin_id);
		if ($this->db->deleteRecords('tbl_admin_remember_me_tokens', array('smt'=>'`art_admin_id`=?', 'vals'=>array($admin_id)))){
			return true;
		}
		$this->error = $this->db->getError();
		return false;
	}
	function updateRememberMeToken($data){
		$admin_id = intval($data['admin_id']);
		if($admin_id < 1 || strlen($data['remember_token']) != 25){
			return false;
		}
		$this->db->deleteRecords('tbl_admin_remember_me_tokens', array('smt'=>'`art_admin_id`=?', 'vals'=>array($admin_id)));
		if($this->db->insert_from_array('tbl_admin_remember_me_tokens', array(
				'art_admin_id'=>$admin_id,
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
		$srch = new SearchBase('tbl_admin_remember_me_tokens');
		$srch->addCondition('art_token', '=', $token);
		$srch->addCondition('art_browser', '=', $_SERVER['HTTP_USER_AGENT']);
		$srch->addCondition('art_expiry', '>', date('Y-m-d H:i:s'));
		$srch->addMultipleFields(array('art_admin_id', 'art_token'));
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
			
	 /* 'Access denied' error message. */
    function getUnauthorizedMsg() {
        return 'Invalid Access!!';
    }	
	
	/******************      Start # Added by Anup for Blog      ********************/
	static function getLoggedId() {
        return $_SESSION['logged_admin']['admin_logged_id'];
    }
	static function getLoggedUsername() {
        return $_SESSION['logged_admin']['admin_username'];
    }
	static function getLoggedUserEmail() {
        return $_SESSION['logged_admin']['admin_email'];
    }
	static function getLoggedinUserId() {
        if (self::isLogged())
            return $_SESSION['logged_admin']['admin_logged_id'];
        else
            return false;
    }
		/******************      End # Added by Anup for Blog     ********************/
	
	
}