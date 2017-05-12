<?php
class Url_alias extends Model {
	
	function __construct(){
		$this->db = Syspage::getdb();
    }
	
	 function getUrlAliasByKeyword($keyword) {
		$srch = new SearchBase('tbl_url_alias', 'tua');
		$srch->addCondition('tua.url_alias_keyword', '=', $keyword);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
    function getUrlAliasByQuery($query) {
		$srch = new SearchBase('tbl_url_alias', 'tua');
		$srch->addCondition('tua.url_alias_query', '=', $query);
        $rs = $srch->getResultSet();
        $row = $this->db->fetch($rs);
        if($row==false) return array();
        else return $row;
	}
	
}