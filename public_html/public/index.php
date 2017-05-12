<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
define('HTTP_YOKART_PUBLIC', $protocol . $_SERVER['HTTP_HOST'] . rtrim(rtrim(dirname($_SERVER['SCRIPT_NAME']), 'install'), '/.\\') . '/');
define('HTTP_YOKART', preg_replace('~/[^/]*/([^/]*)$~', '/\1', HTTP_YOKART_PUBLIC));

if (is_file('settings.php')) {
	require_once('settings.php');
}

if (!defined('CONF_WEBROOT_URL') || is_file('install/install_step.php')) {
	require_once('install/install.php');
	die();
}
require_once dirname(__FILE__) . '/application-top.php';
unregisterGlobals();
$post = getPostedData();
callHook();
