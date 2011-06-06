<?php  

/**
 * manage synchronization session for application
 * @package Kutu
 * 
 */

include_once("../../../baseinit.php");

require_once 'Zend/Session.php';

if(isset($_GET['returnTo']) && !empty($_GET['returnTo']))
{
	setcookie('returnTo', base64_decode($_GET['returnTo']), null, '/');
}

$flagSessionIdSent = false;
if(isset($_GET['PHPSESSID']) && !empty($_GET['PHPSESSID']))
{
	$sessid = $_GET['PHPSESSID'];
	
	Zend_Session::setId($sessid);
	$flagSessionIdSent = true;
}

if($flagSessionIdSent)
{
	Zend_Session::start();
	if(isset($_COOKIE['returnTo']) && !empty($_COOKIE['returnTo']))
	{
		header("location: ".$_COOKIE['returnTo']);
		exit();
	}
}
else 
{
	$registry = Zend_Registry::getInstance(); 
	$config = $registry->get('config');
	$url = $config->session->config->remote->sessionidgenerator->url;
	$sReturn = KUTU_ROOT_URL.'/application/services/session/syncsession.php?';
	$sReturn = base64_encode($sReturn);
	header("location: $url/returnTo/".$sReturn); 
	exit();
}

?>