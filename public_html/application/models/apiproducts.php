<?php
class ApiProducts extends Products {
	
	function __construct(){
		$this->db = Syspage::getdb();
		parent::__construct('tbl_products', 'tp');
    }
	
}