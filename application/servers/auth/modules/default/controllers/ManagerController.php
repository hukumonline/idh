<?php

/**
 * manage Remote Adapter Authentication for application
 * @package Kutu
 * 
 * $Id: ManagerController.php 2009-01-10 15:43: $
 */

class ManagerController extends Zend_Controller_Action  
{
	public function authenticateAction()
	{
		$registry = Zend_Registry::getInstance(); 
		$config = $registry->get('config');
		$request = $this->getRequest();
		$username = $request->getParam('identity');
		$password = $request->getParam('credential');
		//$db = Zend_Db::factory($config->db->adapter, $config->db->config->toArray());
		/*
        $db = Zend_Db::factory('Pdo_Mysql', array(
             'host'     => 'localhost'
            ,'username' => 'root'
            ,'password' => ''
            ,'dbname'   => 'hid'
        ));
        */
		$dbAdapters = Zend_Registry::get ( 'dbAdapters' );
		$config1 = ($dbAdapters ['hol']);
		$config2 = ($dbAdapters ['identity']);

		
		//$a = $db->fetchAll("SELECT * FROM KutuUser WHERE username ='$username' AND isActive=1");
		//$b = $db->fetchAll("SELECT sessionId FROM session WHERE sessionData LIKE '%$username%'");
		$a = $config2->fetchAll("SELECT * FROM KutuUser WHERE username ='$username' AND isActive=1");
		//$b = Zend_Db_Table::getDefaultAdapter()->fetchAll("SELECT sessionId FROM session WHERE sessionData LIKE '%$username%'");
		$b = $config1->fetchAll("SELECT sessionId FROM session WHERE sessionData LIKE '%$username%'");
		
		if (count($b) >= 1)
		{
			$b[0]['password'] = '---';
   			$b[0]['username'] = '---';
   			$b[0]['packageId'] = '---';
   			$b[0]['picture'] = '---';
   			$b[0]['kopel'] = 'XXISLOGINXX'; 
   			echo Zend_Json::encode($b);
		}
		else 
		{
			if(count($a)<1)
			{
				echo '[]';  //dummy data for the remote auth adapter
			}
			else 
			{
				if(count($a) > 1)
				{
					echo '[{"id":"xx"},{"id":"yy"}]'; //dummy data for the remote auth adapter
				}
				else 
				{
					$obj = new Kutu_Crypt_Password();
					$resultIdentity = $a[0];
					if (strtoupper(substr(sha1($password),0,30)) == $resultIdentity['password'])
					{
						$resultIdentity['password'] = $obj->encryptPassword($password);
						$config2->update('KutuUser',$resultIdentity,"username='".$username."'");
						$this->authenticateAction();
					}
					elseif($obj->matchPassword($password, $resultIdentity['password']))
		       		{
		       			echo Zend_Json::encode($a);
		       		}
		       		else 
		       		{
		       			$a[0]['password'] = '---';
		       			$a[0]['username'] = '---';
		       			$a[0]['kopel'] = '---'; 
		       			echo Zend_Json::encode($a);
		       		}
				}
			}
		}
	}
}
