<?php

/**
 * manage Authentication for application
 * 
 * @package Kutu
 * 
 */

include_once("../../../baseinit.php");

$front = Zend_Controller_Front::getInstance();
$front->throwExceptions(true);
$front->addModuleDirectory(KUTU_ROOT_DIR.'/application/servers/auth/modules');

$front->dispatch(); 


?>