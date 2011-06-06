<?php

/**
 * module Access Control List (ACL) 
 * 
 * @author Himawan Anindya Putra <putra@langit.biz>
 * @package Kutu
 * 
 */

class Kutu_Acl_Manager
{
	/**
	 * factory()
	 *
	 * Removes an Object from a group.
	 *
	 * @return bool Returns TRUE if successful, FALSE otherwise
	 *
	 * @param string username
	 * @param string groupValue
	 */
	static function factory()
	{
		$registry = Zend_Registry::getInstance(); 
		$config = $registry->get('config');
		
		switch (strtolower($config->acl->adapter))
		{
			case 'direct_phpgacl':
				$aclAdapter = new Kutu_Acl_Adapter_Local();
				return $aclAdapter;
			case 'remote':
			case 'remote_phpgacl':
			case 'proxy_phpgacl':
				$remoteUrl = $config->acl->config->remote->url;
				$aclAdapter = new Kutu_Acl_Adapter_Remote($remoteUrl);
				return $aclAdapter;
			default :
				throw new Zend_Exception('Kutu_Acl_Manager does not support adapter: '. $config->acl->adapter. '. Please check your configuration.', 101);
		}
		
	}
	static function getAdapter()
	{
		$registry = Zend_Registry::getInstance(); 
		$config = $registry->get('config');
		if($config->acl->adapter == 'remote')
		{
			$remoteUrl = $config->acl->config->remote->url;
			$aclAdapter = new Kutu_Acl_Adapter_Remote($remoteUrl);
			return $aclAdapter;
		}
		else 
		{
			$aclAdapter = new Kutu_Acl_Adapter_Local();
			return $aclAdapter;
		}
	}
}