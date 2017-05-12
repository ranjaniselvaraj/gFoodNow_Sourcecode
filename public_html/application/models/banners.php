<?php
class Banners extends Model {
	
	function __construct(){
		$this->db = Syspage::getdb();
    }
	
	
	function getHomePageBanners() {
		$srch = new SearchBase('tbl_banners', 'tb');
		$srch->addCondition('tb.banner_is_deleted', '=', 0);
		$srch->addCondition('tb.banner_status', '=', 1);
		$srch->addCondition('tb.banner_position', '=', 0);
		$srch->addOrder('rand()');
		$srch->setPageSize('4');
		//$srch->addOrder('tb.banner_priority','ASC');
		$rs = $srch->getResultSet();
        $banners=$this->db->fetch_all($rs);
		return $banners;
    }
	
   
}