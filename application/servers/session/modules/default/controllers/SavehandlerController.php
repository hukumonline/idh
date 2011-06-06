<?php

/**
 * manage Session for application
 * @package Kutu
 * 
 */

class SaveHandlerController extends Zend_Controller_Action 
{
	protected $_db;
	
	function preDispatch()
	{
		//$registry = Zend_Registry::getInstance(); 
		//$config = $registry->get('config');
		
		$dbAdapters = Zend_Registry::get ( 'dbAdapters' );
		$config = ($dbAdapters ['hol']);
		
		//$this->_db = Zend_Db::factory($config->db->adapter, $config->db->config->toArray());
		$this->_db = $config;
	}
	
	function open() 
	{
		global $sess_save_path;
		$r = $this->getRequest();
		$save_path = $r->getParam('savePath');
		$sess_save_path = $save_path;
	}
	function close() 
	{
	}
	
	function readAction()
	{ 
		$r = $this->getRequest();
		$key = $r->getParam('key');
		$time = time();
		$db = $this->_db;
		$aRows = $db->fetchAll("SELECT sessionData FROM `session` WHERE sessionId='$key' AND sessionExpiration > FROM_UNIXTIME($time)");
	
		if(count($aRows) > 0)
		{
			$sSessionData = $aRows[0]['sessionData'];
			echo $sSessionData;
		}
		else
		{
			echo '';
		}
	}
	function writeAction()
	{
		$db = $this->_db;
		
		$lifeTime = ini_get('session.gc_maxlifetime'); //get_cfg_var("session.gc_maxlifetime");
		$time = time() + $lifeTime - 600;
		
		$date = date("Y-m-d H:i:s", $time);
		
		$r = $this->getRequest();
		$key = $r->getParam('key');
		$val = $r->getParam('value');
		
		error_log("$key = $val");
		$val = addslashes($val);
		$insert_stmt  = "insert into session values('$key','$val',FROM_UNIXTIME($time))";
	
		$update_stmt  = "update session set sessionData ='$val', ";
		$update_stmt .= "sessionExpiration = FROM_UNIXTIME($time) ";
		$update_stmt .= "where sessionId ='$key '";
		
		$aRows = $db->fetchAll("SELECT * FROM `session` WHERE sessionId='$key'");
		if(count($aRows) > 0)
		{
			$db->query($update_stmt);
		}
		else
		{
			$db->query($insert_stmt);
		}
		
		
	}
	 function destroyAction() 
	 {
	 	$db = $this->_db;

		$r = $this->getRequest();
		$key = $r->getParam('key');
		// Build query
		//$newid = mysql_real_escape_string($id);
		$sql = "DELETE FROM `session` WHERE `sessionId` = '$key'";
		$db->query($sql);
	}
		
	function gcAction() 
	{
		$db = $this->_db;
		
		// Garbage Collection
		$time = time();
		$date = date("Y-m-d H:i:s", $time);
		
		//the expiration time
		$sql = "DELETE FROM session WHERE sessionExpiration < FROM_UNIXTIME($time)";
		$db->query($sql);
	}
}