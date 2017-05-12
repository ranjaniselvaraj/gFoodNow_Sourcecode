<?php
class CommonController extends Controller{
	protected $adminLogged;
			
	function __construct($model, $controller, $action){
		parent::__construct($model, $controller, $action);
		$m_time = explode(" ",microtime());
		$m_time = $m_time[0] + $m_time[1];
		$loadstart = $m_time;
		$this->loadstart = $loadstart;
		$this->set('loadstart', $loadstart);
		$this->currency = &Syspage::getCurrency();
		$this->set('currencyObj', $this->currency);
		$this->initialize();
		
	}
	
	private function initialize(){
	if($this->isAdminLogged()) $this->adminLogged = true;
		if(!$this->adminLogged){
			 if (!isset($_SESSION['go_to_referer_admin_page'])  && ($this->_controller != 'admin')){	 
		 		$_SESSION['go_to_referer_admin_page'] = Utilities::getCurrUrl();
		 	}
		}
	
		if(!$this->adminLogged && !($this->_controller == 'admin' && in_array($this->_action, array('login', 'forgot_password', 'rpa')))){
		/*if (!isset($_SESSION['go_to_referer_admin_page']) && isset($_SERVER['HTTP_REFERER']) && filter_var($_SERVER['HTTP_REFERER'], FILTER_VALIDATE_URL) && strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) == true && ($this->_controller != 'admin')){	 
		 	$_SESSION['go_to_referer_admin_page'] = Utilities::getCurrUrl();
		 }*/
		 Utilities::redirectUser(Utilities::generateUrl('admin','login'));
	}
	
	if($this->adminLogged && $this->_controller == 'admin' && in_array($this->_action, array('login', 'forgot_password', 'rpa'))) {
		Utilities::redirectUser(Utilities::generateUrl('home',''));
	}
	
	$this->set('is_admin_logged', $this->adminLogged);
	if($this->adminLogged === true){
		$adminObj=new Admin();
		$adminData=$adminObj->getAdminById($this->getLoggedAdminId());
		$this->set('admin_name', $adminData['admin_full_name']);
		$this->set('admin_image', $adminData['admin_image']);
		$this->set('dashboard_layout', $adminData['admin_dashboard_layout']);
		$this->set('dashboard_color', $adminData['admin_dashboard_color']);
		$this->set('is_super_admin_logged', $_SESSION['logged_admin']['super_admin']);
		$this->set('admin_id', $this->getLoggedAdminId());
		$this->set('front_theme',Settings::getSetting("CONF_FRONT_THEME"));
	}		
	$this->setCommonValues();
}
	
	function setting_update(){
		$post = Syspage::getPostedVar();
		$json = array();
		if($_SESSION['logged_admin']['admin_logged']){
			if (isset($post['front_color'])){
				$settingsObj=new Settings();
				if($settingsObj->update(array('CONF_FRONT_THEME'=>$post['front_color']))){
					dieJsonSuccess('Setting updated successfully.');				
				}else{
					dieJsonError($settingsObj->getError());			
				}
			}else{
				$adminObj=new Admin();
				$post['admin_id']=$_SESSION['logged_admin']['admin_logged'];				
				if($adminObj->layoutUpdate($post)){
					dieJsonSuccess('Setting updated successfully.');				
				}else{
					dieJsonError($adminObj->getError());			
				}
			}
		}else{
			dieJsonError('Invalid Request!');	
		}		
	}
    
    public function default_action(){
    	echo $this->getError();
		exit(0);
    }
    
    private function isAdminLogged(){
        return Admin::isLogged();
    }
	private function setCommonValues(){
		$this->set('model', $this->_model);
		$this->set('action', $this->_action);
		$this->set('controller', $this->_controller);
		$get = getQueryStringData();
		$url = $get['url'];
		$urlArray = explode("/",$url);
		$this->set('query', isset($urlArray[2])?$urlArray[2]:'');	
	}
	
	function getError(){
		echo 'Invalid Page Request!';
	}
	protected function getLoggedAdminId(){
		return intval($_SESSION['logged_admin']['admin_logged_id']);
	}
	
	public function confirm_box() {
       $post = getPostedData();
       $text = $post['text'];
       $this->set('text', $text);
       $this->layout = NULL;
       $this->_template->render(false, false);
   }
}