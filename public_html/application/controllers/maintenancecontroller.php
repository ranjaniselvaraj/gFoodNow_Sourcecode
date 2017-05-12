<?php
class maintenanceController extends CommonController{
	function default_action(){
		$this->set('maintenance_text', Settings::getSetting("CONF_MAINTENANCE_TEXT"));
		$this->_template->render();	
	}
}
