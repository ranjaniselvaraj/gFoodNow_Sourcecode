<?php
class Emptycartitems extends Model {
	function __construct(){
		$this->db = Syspage::getdb();
    }
	function getEmptyCartItems() {
		$srch = new SearchBase('tbl_empty_cart_items', 'tci');
		$srch->addCondition('tci.emptycartitem_is_deleted', '=', 0);
		$srch->addCondition('tci.emptycartitem_status', '=', 1);
		$srch->addOrder('tci.emptycartitem_priority', 'asc');
		$rs = $srch->getResultSet();
        $emptycartitems=$this->db->fetch_all($rs);
		return $emptycartitems;
    }
   
}