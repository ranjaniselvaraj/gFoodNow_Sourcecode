<?php
class OffersController extends CommonController{
	function default_action(){
		Syspage::addJs(array('js/jquery.readmore.js'), false);
		$offerObj=new Coupons();	
		$offers=$offerObj->getCoupons(array());
		$this->set('offers',$offers);
		$this->_template->render();	
	}
}
