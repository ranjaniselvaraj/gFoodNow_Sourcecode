<?php
class PaymentController {

	protected $_model;
	protected $_controller;
	protected $_action;
	protected $_template;

    /* modified */
    private $_user_id;

	function __construct($model, $controller, $action) {
		$this->_controller = $controller;
		$this->_action = $action;
		$this->_model = $model;
		
		if (file_exists(CONF_APPLICATION_PATH . 'models/' . strtolower($model) . '.php')){
			$this->$model = new $model;
		}
		else {
			$this->$model = new Model();
		}
		$this->_template = new PaymentTemplate($controller,$action);

        /* modified */
        //$this->_user_id = User::getLoggedMainUserId();
	}

	function set($name,$value) {
		//print($name);
		//print_r($value);
		
		$this->_template->set($name,$value);
	}
	
	function default_action(){
		$this->_template->render();
	}

	function __destruct() {
			//$this->_template->render();
	}

    /* modified */
    protected function getUserId() {
        return $this->_user_id;
    }

    protected function getLoggedinUserId() {
        return User::getLoggedUserId();
    }

    function setTplVars($vars=array()) {
        foreach($vars as $key=>$value) {
            $this->set($key, $value);
        }
    }

    function drawPage($header=true, $footer=true, $tpl=null, $return=false, $encode=true) {
        /**
         * @todo: Need to discuss about code below modified to add page-js/action.js file
         */
        if ($header==false) {
            /* Add page js and page css */
            global $tpl_for_js_css;
            if ($tplpath == NULL){
                $tpl_for_js_css = $this->_controller . '/' . $this->_action . '.php';
            }
            else {
                $tpl_for_js_css = $tplpath;
            }
            /* Add page js and page css ends */
        }
        $this->_template->render($header, $footer, $tpl, $return, $encode);
    }

}
