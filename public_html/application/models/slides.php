<?php
class Slides extends Model {
	function __construct(){
		$this->db = Syspage::getdb();
    }
	function getHomePageSlides() {
		$srch = new SearchBase('tbl_slides', 'ts');
		$srch->addCondition('ts.slide_is_deleted', '=', 0);
		$srch->addCondition('ts.slide_status', '=', 1);
		$rs = $srch->getResultSet();
        $banners=$this->db->fetch_all($rs);
		return $banners;
    }
   
}