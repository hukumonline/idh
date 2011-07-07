<?php

/**
 * @package kutump
 * @copyright 2008-2009 hukumonline.com/en.hukumonline.com
 * @author Nihki Prihadi <nihki@hukumonline.com>
 *
 * $Id: User.php 2009-01-10 19:45: $
 * $modifiedDate: 2011-04-04 10:20: $
 */

class Kutu_Core_Orm_Table_Row_User extends Zend_Db_Table_Row_Abstract
{
	protected function _insert()
	{
		/*
		if (empty($this->guid))
		{
			$generateGuid = new Kutu_Core_Guid();
			$this->guid = $generateGuid->generateGuid();
		}
		
		if (empty($this->kopel))
		{
			$num = new Kutu_Core_Number();
			$this->kopel = $num->generateNumber();
		}
		*/
		
		$today = date('Y-m-d H:i:s');
		
		if (empty($this->createdDate)) 	
			$this->createdDate = $today;	
		
			
		/*		
		if (empty($this->updatedDate))
			$this->updatedDate = $today;
		
				
		$userName = '';
		$auth = Zend_Auth::getInstance();
		if($auth->hasIdentity())
		{
			$userName = $auth->getIdentity()->username;
		}
		
		if (empty($this->createdBy))
			$this->createdBy = $userName;
		
		if (empty($this->updatedBy))		
			$this->updatedBy = $userName;

		*/
						
		if (empty($this->educationId))
			$this->educationId = 0;
		
		if (empty($this->expenseId))
			$this->expenseId = 0;
		
		if (empty($this->activationDate))
			$this->activationDate = '0000-00-00 00:00:00';
		
		if (empty($this->paymentId))
			$this->paymentId = 0;
		
		if (empty($this->periodeId))		
			$this->periodeId = 1;
		
		if (empty($this->isEmailSent))		
			$this->isEmailSent = 'N';
			
	}
	protected function _update()
	{
    	$this->modifiedDate = date("Y-m-d h:i:s");
    	
		$auth = Zend_Auth::getInstance();
		if($auth->hasIdentity())
		{
			$userName = $auth->getIdentity()->username;
			$this->modifiedBy = $userName;
		}
	}
	protected function _postDelete()
	{
		$tblUserDetail = new Kutu_Core_Orm_Table_UserDetail();
		$rowsetUserDetail = $tblUserDetail->fetchAll("uid='$this->guid'");
		foreach ($rowsetUserDetail as $row)
		{
			// delete
			$row->delete();
		}
		
		//delete from table KutuUserInvoice
		$tblInvoice = new Kutu_Core_Orm_Table_Invoice();
		$tblInvoice->delete("uid='$this->kopel'");
		//delete from table KutuUserAccessLog
		$tblUserLog = new Kutu_Core_Orm_Table_UserLog();
		$tblUserLog->delete("user_id='$this->guid'");
			
		//delete from ACL
		$aclMan = new Kutu_Acl_Adapter_Local();
		$aclMan->deleteUser($this->username);
		
		// delete physical user folder define by guid 
		$sDir = KUTU_ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.$this->guid;
		try {
			$this->removeRessource($sDir);
		}
		catch (Exception $e)
		{
			throw new Exception($e);
		}
	}
	public function findParentRowUserDetail()
	{
		return $this->findParentRow('Kutu_Core_Orm_Table_UserDetail');
	}
	public function findDependentRowsetUserDetail()
	{
		return $this->findDependentRowset('Kutu_Core_Orm_Table_UserDetail');
	}
	public function removeRessource( $_target ) {
	   
	    //file?
	    if( is_file($_target) ) {
	        if( is_writable($_target) ) {
	            if( @unlink($_target) ) {
	                return true;
	            }
	        }
	       
	        return false;
	    }
	       
	    //dir?
	    if( is_dir($_target) ) {
	        if( is_writeable($_target) ) {
	            foreach( new DirectoryIterator($_target) as $_res ) {
	                if( $_res->isDot() ) {
	                    unset($_res);
	                    continue;
	                }
	                   
	                if( $_res->isFile() ) {
	                    removeRessource( $_res->getPathName() );
	                } elseif( $_res->isDir() ) {
	                    removeRessource( $_res->getRealPath() );
	                }
	               
	                unset($_res);
	            }
	                   
	            if( @rmdir($_target) ) {
	                return true;
	            }
	        }
	       
	        return false;
	    }
	} 	
}

?>