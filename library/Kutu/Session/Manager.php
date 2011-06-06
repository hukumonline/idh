<?php

/**
 * module Session for Application
 * 
 * @package Kutu
 * 
 */

class Kutu_Session_Manager
{
	private $_flagDoSyncSession;
	
	public function __construct($doSyncSession=true)
    {
    	$this->_flagDoSyncSession = $doSyncSession;
    	
    	$cookie_timeout = 60 * 60 * 24;
		$garbage_timeout = $cookie_timeout + 600;
		
		session_set_cookie_params($cookie_timeout);
		
		ini_set('session.gc_maxlifetime', $garbage_timeout);
    }
	
	function start()
	{
		$registry = Zend_Registry::getInstance(); 
		$config = $registry->get('config');
		
		$url = $config->session->config->remote->sessionidgenerator->url;
		
		require_once 'Zend/Session.php';
		
		$saveHandler = $config->session->savehandler;
		$flagDoSyncSession = $this->_flagDoSyncSession;
		
		switch (strtolower($saveHandler))
		{
			case 'remote':
				require_once('Kutu/Session/SaveHandler/Remote.php');
				$sessionHandler = new Kutu_Session_SaveHandler_Remote();
				Zend_Session::setSaveHandler($sessionHandler);
				break;
			default:
				$flagDoSyncSession = false;
				break;
				
		}
		
		if($this->_flagDoSyncSession)
		{
			$flagSessionIdSent = false;
			
			if(isset($_POST['PHPSESSID']) && !empty($_POST['PHPSESSID']))
			{
				$sessid = $_POST['PHPSESSID'];
				Zend_Session::setId($sessid);
				$flagSessionIdSent = true;
				
			}
			if(isset($_GET['PHPSESSID']) && !empty($_GET['PHPSESSID']))
			{
				$sessid = $_GET['PHPSESSID'];
				Zend_Session::setId($sessid);
				$flagSessionIdSent = true;
				
			}
			if(isset($_COOKIE['PHPSESSID']) && !empty($_COOKIE['PHPSESSID']))
			{
				$flagSessionIdSent = true;
			}
			
			if(!$flagSessionIdSent)
			{
				//redirect to session local sync startpoint
				$sReturn = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
				$sReturn = base64_encode($sReturn);
				
				$url = $config->session->config->local->sync->url;
				$url = KUTU_ROOT_URL.$url;
				
				header("location: $url?returnTo=".$sReturn);
				
				exit();
			}
			else 
			{
				Zend_Session::start();
			}
		}
		else 
		{
			Zend_Session::start();
		}
	}
}
?>