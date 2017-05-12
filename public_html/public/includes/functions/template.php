<?php

public static function getViewsPath() {
    return CONF_APPLICATION_PATH . 'views/';
}

public static function getViewsSidebarPath() {
    return CONF_APPLICATION_PATH . 'views/_sidebar/';
}

public static function getViewsPartialPath() {
    return CONF_APPLICATION_PATH . 'views/_partial/';
}

public static function renderView($fname, $vars=array(), $return=true) {
    ob_start();
    extract($vars);
    include $fname;
    $contents = ob_get_clean();
    if ($return==true) {
        return $contents;
    } else {
        echo $contents;
    }
}
