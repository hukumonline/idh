<?php

/**
 * module Authentication
 * 
 * @package Kutu
 * 
 */

class Kutu_Auth_Manager
{
	private $_identity;
	private $_credential;
	private $_authResult;
	
	public function __construct($identity, $credential)
	{
		$this->_identity = $identity;
		$this->_credential = $credential;
	}
	public function authenticate()
	{
		$registry = Zend_Registry::getInstance(); 
		$config = $registry->get('config');
		
		if ($config->auth->adapter == 'remote')
		{ 
			$authAdapter = new Kutu_Auth_Adapter_Remote($config->auth->config->remote->url,$this->_identity,$this->_credential);
		} 
		else 
		{
		}
		
		$auth = Zend_Auth::getInstance();
		$this->_authResult = $auth->authenticate($authAdapter);
		
		if ($this->_authResult->isValid())
		{
			// success : store database row to auth's storage
			$data = $authAdapter->getResultRowObject();
			$auth->getStorage()->write($data);	
			return $this->_authResult;
		} else {
			if($this->_authResult->getCode() != -51)
			{
				// failure : clear database row from session
				Zend_Auth::getInstance()->clearIdentity();
			}
			return $this->_authResult;
		}
	}
}