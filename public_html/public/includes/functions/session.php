<?php

function setSessionRedirectUrl($url) {
    $_SESSION['sess_return_url'] = $url;
}

function getSessionRedirectUrl() {
    return $_SESSION['sess_return_url'];
}

function unsetSessionRedirectUrl() {
    $_SESSION['sess_return_url'] = null;
}

function setAnonymousUser($data=array()) {
    $_SESSION['anonymous_user'] = $data;
}

function resetAnonymousUser() {
    unset($_SESSION['anonymous_user']);
}

function getAnonymousUserData() {
    return (array) $_SESSION['anonymous_user'];
}

function setSessionValue($key, $value='') {
    $_SESSION[$key]=$value;
}

function getSessionValue($key) {
    return $_SESSION[$key];
}

function unsetSessionValue($key) {
    unset($_SESSION[$key]);
}

function checkLogin($redirect=true) {
    if (!User::isUserLogged()) {
        $_SESSION['go_to_referer_page'] = Utilities::getCurrUrl();
        if($redirect==true) {
	        Utilities::redirectUser(generateUrl('user', 'account'));
        } else {
        	return false;
        }
    }
    return true;
}

function checkBuyerLogin($redirect=true) {
    if (!User::isBuyerLogged()) {
        $_SESSION['go_to_referer_page'] = Utilities::getCurrUrl();
        if($redirect==true) {
	        Utilities::redirectUser(generateUrl('user', 'account'));
        } else {
        	return false;
        }
    }
    return true;
}



function checkIsAlreadyLoggedIn(){
	if (User::isUserLogged()) {
		Utilities::redirectUser(generateUrl('account'));
	}
}
