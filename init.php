<?php

/**
 * @package kutump
 * @author 
 *
 * $Id: baseinit.php 2009-01-10 08:10: $
 * Revisi 2010-05-09 17:00: $
 * Sekarang memakai library Zend bawaan Zend_Server
 */

class kutu_init
{
	function init()
	{
		define('KUTU_ROOT_DIR', dirname(__FILE__));	
		
		error_reporting(E_ALL|E_STRICT);
		date_default_timezone_set('Asia/Jakarta');		
		set_include_path('.' . PATH_SEPARATOR . KUTU_ROOT_DIR.'/library' . PATH_SEPARATOR . get_include_path()); 		
		require_once 'Zend/Loader/Autoloader.php';
		$loader = Zend_Loader_Autoloader::getInstance();
		$loader->setFallbackAutoloader(true);
		$loader->suppressNotFoundWarnings(false);
//		include "Zend/Loader.php"; 		
//		Zend_Loader::registerAutoload();
		require_once(KUTU_ROOT_DIR.'/library/phpgacl/gacl.class.php');
		require_once(KUTU_ROOT_DIR.'/library/phpgacl/gacl_api.class.php');
		require_once(KUTU_ROOT_DIR.'/js/jcart/jcart.php');
		
		$config = new Zend_Config_Ini(KUTU_ROOT_DIR.'/application/configs/application.ini','general');
		$registry = Zend_Registry::getInstance();
		$registry->set('config', $config);
		$registry->set('files', $_FILES);
		//$db = Zend_Db::factory($config->db->adapter, $config->db->config->toArray());		
		//Zend_Db_Table_Abstract::setDefaultAdapter($db);
		$databases = new Zend_Config_Ini(KUTU_ROOT_DIR.'/application/configs/application.ini','databases');
		$dbAdapters = array();
		foreach ($databases->db as $config_name => $db)
		{
			$dbAdapters[$config_name] = Zend_Db::factory($db->adapter, $db->config->toArray());
			if ((boolean)$db->default)
			{
				Zend_Db_Table::setDefaultAdapter($dbAdapters[$config_name]);
			}
		}
		Zend_Registry::set('dbAdapters',$dbAdapters);
		
		$kutuUtil = new Kutu_Core_Util();
		define('KUTU_ROOT_URL', $kutuUtil->getRootUrl(KUTU_ROOT_DIR));
		
		require_once 'Kutu/Session/Manager.php';
		$kutuSession = new Kutu_Session_Manager();
		$kutuSession->start();
		// set the expiration time for auth session to expire
		$authNamespace = new Zend_Session_Namespace('Zend_Auth');
		$authNamespace->setExpirationSeconds(86400); // will expire in one day
		
		$frontendOptions = array(
		'lifetime' => 7200, // cache lifetime of 2 hours
	    'automatic_serialization' => true
	    );
	
		$backendOptions  = array(
		    'cache_dir'                => KUTU_ROOT_DIR.'/data/cache'
		    );
		
		$cacheDbTable = Zend_Cache::factory('Core',
		                             'File',
		                             $frontendOptions,
		                             $backendOptions);
	
	
		// Next, set the cache to be used with all table objects
		Zend_Db_Table_Abstract::setDefaultMetadataCache($cacheDbTable);
		
//		define('IN_PHPBB', true);
//		define('PBB_ROOT_PATH', "../phpBB3");
//		global $phpbb_root_path, $phpEx, $user, $db, $config, $cache, $template;
//		$phpEx = "php";
//		$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : PBB_ROOT_PATH . '/';
//		require_once($phpbb_root_path . 'config.' . $phpEx);
//		include($phpbb_root_path . 'common.' . $phpEx);
//		$user->session_begin();
//		$auth->acl($user->data);
	}
	
	/**
	 * Raise the memory limit when it is lower than the needed value
	 *
	 * @param string $setLimit Example: 16M
	 */
	
	function ext_RaiseMemoryLimit( $setLimit ) {
		$memLimit = @ini_get('memory_limit');
		if( stristr( $memLimit, 'k') ) {
			$memLimit = str_replace( 'k', '', str_replace( 'K', '', $memLimit )) * 1024;
		}
		elseif( stristr( $memLimit, 'm') ) {
			$memLimit = str_replace( 'm', '', str_replace( 'M', '', $memLimit )) * 1024 * 1024;
		}
		if( stristr( $setLimit, 'k') ) {
			$setLimitB = str_replace( 'k', '', str_replace( 'K', '', $setLimit )) * 1024;
		}
		elseif( stristr( $setLimit, 'm') ) {
			$setLimitB = str_replace( 'm', '', str_replace( 'M', '', $setLimit )) * 1024 * 1024;
		}
		if( $memLimit < $setLimitB ) {
			@ini_set('memory_limit', $setLimit );
		}	
	}
}

kutu_init::ext_RaiseMemoryLimit("24M");
kutu_init::init();

?>