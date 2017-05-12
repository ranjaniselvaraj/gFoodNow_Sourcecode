<?php
class Common extends Model {
	protected $db;
	public static $site_currencies;
	function __construct(){
		$this->db = Syspage::getdb();
    }
	
	static function getPdoObj(){
		if(!class_exists('PDO')) return false;
		$dsn = 'mysql:dbname=' . CONF_DB_NAME . ';host=' . CONF_DB_SERVER;
		$user = CONF_DB_USER;
		$password = CONF_DB_PASS;
		try{
			$pdo = new PDO($dsn, $user, $password);
			return $pdo;
		} catch (PDOException $e) {
			return false;
			/* echo 'Connection failed: ' . $e->getMessage(); */
		}
	}
	
	function getCountriesAssoc($active_only = false){
		$srch = new SearchBase('tbl_countries', 'c');
		if($active_only === true){
			$srch->addCondition('country_active', '=', 1);
		}
		$srch->addMultipleFields(array('country_id', 'country_name'));
		$srch->addOrder('country_name', 'ASC');
		$rs = $srch->getResultSet();
		return $this->db->fetch_all_assoc($rs);
	}
	
	function getStatesAssoc($country_id, $active_only = false){
		$country_id = intval($country_id);
		if($country_id < 1) return array();
		$srch = new SearchBase('tbl_states', 's');
		if($active_only === true){
			$srch->addCondition('state_active', '=', 1);
		}
		$srch->addCondition('state_country_id', '=', $country_id);
		$srch->addMultipleFields(array('state_id', 'state_name'));
		$srch->addOrder('state_name', 'ASC');
		$rs = $srch->getResultSet();
		return $this->db->fetch_all_assoc($rs);
	}
	
	function getCountryById($country_id, $flds = array()){
		$country_id = intval($country_id);
		if($country_id < 1) return false;
		$srch = new SearchBase('tbl_countries');
		$srch->addCondition('country_id', '=', $country_id);
		if(is_array($flds) && sizeof($flds) > 0){
			$srch->addMultipleFields($flds);
		}
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();
		return $this->db->fetch($rs);
	}
	
	function getStateById($state_id, $flds = array()){
		$state_id = intval($state_id);
		if($state_id < 1) return false;
		$srch = new SearchBase('tbl_states', 's');
		$srch->addCondition('state_id', '=', $state_id);
		if(is_array($flds) && sizeof($flds) > 0){
			$srch->addMultipleFields($flds);
		}
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();
		return $this->db->fetch($rs);
	}
	
	
	
	function getNavigation(){
		$srch = new SearchBase('tbl_navigation_links');
		$srch->addCondition('nav_link_active', '=', 1);
		$srch->addOrder('nav_link_display_order', 'ASC');
		$srch->doNotCalculateRecords();
		$srch->doNotLimitRecords();
		$rs = $srch->getResultSet();
		$navigation = array(
							'top'=>array(),
							'bottom'=>array()
						);
		while($row = $this->db->fetch($rs)){
			if(strlen(trim($row['nav_link_url'])) > 0){
				$nav_url = $row['nav_link_url'];
			}else{
				$nav_url = 'javascript:void(0)';
			}
			if($row['nav_link_type'] == 1 && intval($row['nav_link_cpage_id']) > 0){
				$nav_url = Utilities::generateUrl('content', 'page', array(intval($row['nav_link_cpage_id'])));
			}
			$nav = array(
						'text'=>$row['nav_link_text'],
						'link'=>$nav_url,
						'target'=>(($row['nav_link_target'] == 1)?'_blank':''),
						'parent'=>$row['nav_link_parent'],
						'display_order'=>$row['nav_link_display_order']
					);
			if($row['nav_link_nav_type'] == 1){
				$navigation['top'][$row['nav_link_id']] = $nav;
			}else{
				$navigation['bottom'][$row['nav_link_id']] = $nav;
			}
		}
		return $navigation;
	}
}