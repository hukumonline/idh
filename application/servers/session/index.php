<?php

/**
 * manage session for application
 * @package Kutu
 * 
 */

include_once("../../../baseinit.php");

require_once 'Zend/Session.php';

$cookie_timeout = 60 * 60 * 24;

$garbage_timeout = $cookie_timeout + 600;

session_set_cookie_params($cookie_timeout);

ini_set('session.gc_maxlifetime', $garbage_timeout);

Zend_Session::start();

$front = Zend_Controller_Front::getInstance();
$front->throwExceptions(true);
$front->addModuleDirectory(KUTU_ROOT_DIR.'/application/servers/session/modules');

$front->dispatch(); 

?>