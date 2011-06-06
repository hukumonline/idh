<?php

/**
 * @package kutump
 * @copyright 2008-2009 hukumonline.com/en.hukumonline.com
 * @author Nihki Prihadi <nihki@hukumonline.com>
 *
 * $Id: UserDetail.php 2009-01-10 08:20: $
 */

class Kutu_Core_Orm_Table_UserDetail extends Zend_Db_Table_Abstract 
{
	protected $_name = 'KutuUserDetail';
	protected $_rowsetClass = 'Kutu_Core_Orm_Table_Rowset_UserDetail';
	protected $_referenceMap    = array(
        'User' => array(
            'columns'           => array('userId'),
            'refTableClass'     => 'Kutu_Core_Orm_Table_User',
            'refColumns'        => array('kopel')
        )
    );
	
	public function insert(array $data)
	{
		if (empty($data['id']))
		{
			$guidMan = new Kutu_Core_Guid();
			$data['id'] = $guidMan->generateGuid();
		}
		
		$today = date('Y-m-d h:i:s');
		$data['createdDate'] = $today;
		$data['updatedDate'] = $today;
		
		$userName = '';
		$auth = Zend_Auth::getInstance();
		if($auth->hasIdentity())
		{
			$userName = $auth->getIdentity()->username;
		}
		
		$data['createdBy'] = $userName;
		$data['updatedBy'] = $userName;
		
		if (empty($data['educationId']))
			$data['educationId'] = 0;
		
		if (empty($data['expenseId']))
			$data['expenseId'] = 0;
		
		if (empty($data['activationDate']))
			$data['activationDate'] = '0000-00-00 00:00:00';
		
		if (empty($data['paymentId']))
			$data['paymentId'] = 0;
		
		if (empty($data['periodeId']))		
			$data['periodeId'] = 1;
		
		if (empty($data['isEmailSent']))		
			$data['isEmailSent'] = 'N';
			
		return parent::insert($data);
	}
	
	public function update(array $data, $where)
	{
    	$data['updatedDate'] = date("Y-m-d h:i:s");
    	
		$auth = Zend_Auth::getInstance();
		if($auth->hasIdentity())
		{
			$userName = $auth->getIdentity()->username;
			$data['updatedBy'] = $userName;
		}

		return parent::update($data, $where);
	}
}