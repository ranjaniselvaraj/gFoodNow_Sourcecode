<?php
class Search {
	function __construct() {
		$this->db = Syspage::getdb();
		
	}
	function addSearchResult($data=array()){	
		$keyword = str_replace('mysql_func_', 'mysql_func ', $data['keyword']);
		$assign_fields=array(			
			'searchitem_keyword'=>$keyword,			
			'searchitem_date'=>date('Y-m-d'),
		);
		$onDuplicateKeyUpdate=array_merge($assign_fields,array('searchitem_count'=>'mysql_func_searchitem_count+1'));		
		$this->db->insert_from_array('tbl_search_items',$assign_fields,true,false,$onDuplicateKeyUpdate);
	}
	
	function getTopSearchedKeywords() {
        $srch = new SearchBase('tbl_search_items', 'ts');
		$srch->addDirectCondition("LENGTH(searchitem_keyword) > 10 and searchitem_keyword REGEXP '^[A-Za-z0-9 ]+$'");
		$srch->addMultipleFields(array('DISTINCT searchitem_keyword'));
		//column REGEXP '^[A-Za-z0-9]+$'
		$srch->addOrder('searchitem_count','desc');
		$srch->setPageSize(4);
		//die($srch->getquery());
        $rs = $srch->getResultSet();
		$this->total_records = $srch->recordCount();
		$this->total_pages = $srch->pages();
        $row = $this->db->fetch_all($rs);
        if($row==false) return array();
        else return $row;
	}	
}
?>
 