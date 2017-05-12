<?php
class Template {

	protected $variables = array();
	protected $_controller;
	protected $_action;

	function __construct($controller,$action) {
		$this->_controller = $controller;
		$this->_action = $action;
	}

	/** Set Variables **/

	function set($name,$value) {
		$this->variables[$name] = $value;
	}

	/** Display Template **/

	function render($include_header=true, $include_footer=true, $tplpath=NULL, $return_content = false, $convertVariablesToHtmlentities = true, $themeHeader = true) {
		if ($return_content) ob_start();
		
		if ($convertVariablesToHtmlentities) $this->variables = $this->addHtmlEntities($this->variables); 
		
		extract($this->variables);
		
		/* Include header */
		if ($include_header){
			
			/* Add page js and page css */
			global $tpl_for_js_css;
			if ($tplpath == NULL){
				$tpl_for_js_css = $this->_controller . '/' . $this->_action . '.php';
			}
			else {
				$tpl_for_js_css = $tplpath;
			}
			/* Add page js and page css ends */
			
			if (file_exists(CONF_THEME_PATH . $this->_controller . '/header.php') && ($themeHeader)){
				include CONF_THEME_PATH . $this->_controller . '/header.php';
			} else{
				include CONF_THEME_PATH . 'header.php';
			}
		}
		/* Include header ends */
		
		/* Include Main */
		if ($tplpath == NULL){
			if (file_exists(CONF_THEME_PATH . $this->_controller . '/' . $this->_action . '.php')){
				include CONF_THEME_PATH . $this->_controller . '/' . $this->_action . '.php';
			}
			elseif (file_exists(CONF_THEME_PATH . $this->_controller . '/default.php')){
				include CONF_THEME_PATH . $this->_controller . '/default.php';
			}
			else {
				include CONF_THEME_PATH . 'default.php';
			}
		}
		else {
			include CONF_THEME_PATH . $tplpath;
		}
		/* Include Main ends */
		
		/* Include footer */
		if ($include_footer){
			if (file_exists(CONF_THEME_PATH . $this->_controller . '/footer.php')){
				include CONF_THEME_PATH . $this->_controller . '/footer.php';
			} else{
				include CONF_THEME_PATH . 'footer.php';
			}
		}
		
		/* Include footer ends */
		if ($return_content) return ob_get_clean();
	}
	
	function getVariablesAsHtmlEntities(){
		return $this->addHtmlEntities($this->variables);
	}
	
	private function addHtmlEntities($var){
		if (is_array($var)){
			foreach ($var as $key=>$val) $var[$key] = $this->addHtmlEntities($val);
		}
		elseif (is_string($var) || is_numeric($var)) {
			$var = htmlentities($var,ENT_QUOTES,'UTF-8');
            $var = str_replace(htmlentities('&copy;'), '&copy;', $var);
            $var = str_replace(htmlentities('&reg;'), '&reg;', $var);
            $var = str_replace(htmlentities('&trade;'), '&trade;', $var);
		}
		return $var;
	}
	

	
}
