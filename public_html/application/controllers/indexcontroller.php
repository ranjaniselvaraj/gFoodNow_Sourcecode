<?php
class IndexController extends CommonController {
    function error($code=404) {
        switch($code) {
            case 404 :
            header('HTTP/1.0 404 Not Found');
            Syspage::addCss('css/404.css');
            $this->_template->render(true, true, '404.php');
            break;
            case 'invalid' :
//header('HTTP/1.0 404 Not Found');
            $this->_template->render(true, true, 'invalid.php');
            break;  
        }
        exit;
    }
    function default_action() {
        $this->_template->render();
    }
}
