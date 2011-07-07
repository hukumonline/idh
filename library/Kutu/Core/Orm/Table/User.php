<?php

/**
 * @package kutump
 * @copyright 2008-2009 hukumonline.com/en.hukumonline.com
 * @author Nihki Prihadi <nihki@hukumonline.com>
 *
 * $Id: User.php 2009-01-10 08:20: $
 */

class Kutu_Core_Orm_Table_User extends Kutu_Core_Orm_CoreDb 
{
	protected $_name = 'KutuUser';
	protected $_use_adapter = 'identity';
	protected $_rowClass = 'Kutu_Core_Orm_Table_Row_User';
	protected $_dependentTables = array('Kutu_Core_Orm_Table_UserLog','Kutu_Core_Orm_Table_UserDetail');
	
    public function getUserCount($guid)
    {
        if ($guid == "") {
            return 0;
        }

        $select = $this->select()
                  ->from($this, array(
                    'COUNT(kopel) as count_id'
                  ))
                  ->where('kopel = ?', $guid);

        $row = $this->fetchRow($select);

        return ($row !== null) ? $row->count_id : 0;
    }
    function fetchUser($order,$start,$end, $fields, $selectedRows, $where, $startdt, $enddt)
    {
    	$sql = $this->_db->select()
			->from(array('ku' => 'KutuUser'))
//			->joinLeft(array('kude' => 'KutuUserDetail'),
//			'ku.guid = kude.uid',array('idUser'=>'id','uidUser'=>'uid','packageId','promotionId','educationId','expenseId','paymentId','businessTypeId','periodeId','activationDate','isEmailSent','createdDate','isActive'))
			->joinLeft(array('gag' => 'gacl_aro_groups'),
			'ku.packageId = gag.id')
			->joinLeft(array('kui' => 'KutuUserInvoice'),
			'ku.kopel = kui.uid', array('kui.invoiceId', 'kui.expirationDate','kui.isPaid','DaysLeft' => 'DATEDIFF(NOW(),kui.expirationDate)'))
			->joinLeft(array('kus' => 'KutuUserStatus'),
			'ku.periodeId = kus.accountStatusId','kus.status')
			->where('gag.value=?',"$where");
			
		if ($startdt && $enddt)
		{
			$sql = $sql->where("kui.expirationDate BETWEEN '$startdt' AND '$enddt'");
		}
		
		if (isset($selectedRows))
		{ 
			if (isset($fields))
			{
				$i = 1;
				foreach ($selectedRows as $selrows)
				{				
					switch ($selrows) {
						case 'guid':
							$selrows = 'ku.'.$selrows;
							break;
						case 'kopel':
							$selrows = 'ku.'.$selrows;
							break;
						case 'fullName':
							$selrows = 'ku.'.$selrows;
							break;
						case 'username':
							$selrows = 'ku.'.$selrows;
							break;
						case 'company':
							$selrows = 'ku.'.$selrows;
							break;
						case 'email':
							$selrows = 'ku.'.$selrows;
							break;
						case 'createdDate':
							$selrows = 'ku.'.$selrows;
							break;
						case 'expirationDate':
							$selrows = 'kui.'.$selrows;
							break;
						case 'packageId':
							$selrows = 'gag.value';
							break;
						case 'periodeId':
							$selrows = 'kus.status';
							break;
						case 'paymentId':
							$selrows = 'ku.'.$selrows;
							break;
					}
					if ($i > 1)
					{
						if (preg_match('/%/i',$fields)) 
							$sql = $sql->orWhere($selrows.' like ?', "$fields");	
						else 
							$sql = $sql->orWhere($selrows.'= ?', "$fields");
					}
					else 
					{
						if (preg_match('/%/i',$fields))
							$sql = $sql->where($selrows.' like ?', "$fields");
						else 
							$sql = $sql->where($selrows.'= ?', "$fields");
					}			
					$i++;
				}
			}				
		}
		
		$sql = $sql->group('kopel');
    	$sql = $sql->order(array($order,'invoiceId DESC'))->limit($end, $start);
//    	$sql = $sql->group('kopel');
//    	$sql = $sql->order(array($order))->limitPage($start, $end);
    	
//    	$sql = $sql->__toString();
//    	print_r($sql);exit();

    	$db = $this->_db->query($sql);
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
        
    	$data  = array(
            'table'    => $this,
            'data'     => $dataFetch,
            'rowClass' => $this->_rowClass,
            'stored'   => true
        );

        Zend_Loader::loadClass($this->_rowsetClass);
        return new $this->_rowsetClass($data);    	
    }
    function fetchUserGroupFree($order,$start,$end, $fields, $selectedRows)
    {
    	$sql = $this->_db->select()
			->from(array('ku' => 'KutuUser'))
			->joinLeft(array('gag' => 'gacl_aro_groups'),
			'ku.packageId = gag.id')
			->joinLeft(array('kus' => 'KutuUserStatus'),
			'ku.periodeId = kus.accountStatusId','status')
			->where('gag.value=?','member_gratis');
		
		if (isset($selectedRows))
		{ 
			if (isset($fields))
			{
				$i = 1;
				foreach ($selectedRows as $selrows)
				{				
					switch ($selrows) {
						case 'guid':
							$selrows = 'ku.'.$selrows;
							break;
						case 'kopel':
							$selrows = 'ku.'.$selrows;
							break;
						case 'fullName':
							$selrows = 'ku.'.$selrows;
							break;
						case 'username':
							$selrows = 'ku.'.$selrows;
							break;
						case 'company':
							$selrows = 'ku.'.$selrows;
							break;
						case 'createdDate':
							$selrows = 'ku.'.$selrows;
							break;
						case 'periodeId':
							$selrows = 'kus.status';
							break;
					}
					if ($i > 1)
					{
						if (preg_match('/%/i',$fields)) 
							$sql = $sql->orWhere($selrows.' like ?', "$fields");	
						else 
							$sql = $sql->orWhere($selrows.'= ?', "$fields");
					}
					else 
					{
						if (preg_match('/%/i',$fields))
							$sql = $sql->where($selrows.' like ?', "$fields");
						else 
							$sql = $sql->where($selrows.'= ?', "$fields");
					}			
					$i++;
				}
			}				
		}
		
//		$sql = $sql->group('uidUser');
    	$sql = $sql->order(array($order))->limit($end, $start);
//    	$sql = $sql->order(array($order))->limitPage($start, $end);
    	
//    	$sql = $sql->__toString();
//    	print_r($sql);exit();

    	$db = $this->_db->query($sql);
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
        
    	$data  = array(
            'table'    => $this,
            'data'     => $dataFetch,
            'rowClass' => $this->_rowClass,
            'stored'   => true
        );

        Zend_Loader::loadClass($this->_rowsetClass);
        return new $this->_rowsetClass($data);    	
    }
    
    function fetchUserGroupOther($order,$start,$end, $fields, $selectedRows)
    {
    	$sql = $this->_db->select()
			->from(array('ku' => 'KutuUser'))
			->joinLeft(array('gag' => 'gacl_aro_groups'),
			'ku.packageId = gag.id')
			->joinLeft(array('kus' => 'KutuUserStatus'),
			'ku.periodeId = kus.accountStatusId','status')
			->where('gag.value NOT IN ("member_gratis","member_corporate","member_individual","member_bonus")');
		
		if (isset($selectedRows))
		{ 
			if (isset($fields))
			{
				$i = 1;
				foreach ($selectedRows as $selrows)
				{				
					switch ($selrows) {
						case 'guid':
							$selrows = 'ku.'.$selrows;
							break;
						case 'kopel':
							$selrows = 'ku.'.$selrows;
							break;
						case 'fullName':
							$selrows = 'ku.'.$selrows;
							break;
						case 'username':
							$selrows = 'ku.'.$selrows;
							break;
						case 'company':
							$selrows = 'ku.'.$selrows;
							break;
						case 'createdDate':
							$selrows = 'ku.'.$selrows;
							break;
						case 'periodeId':
							$selrows = 'kus.status';
							break;
					}
					if ($i > 1)
					{
						if (preg_match('/%/i',$fields)) 
							$sql = $sql->orWhere($selrows.' like ?', "$fields");	
						else 
							$sql = $sql->orWhere($selrows.'= ?', "$fields");
					}
					else 
					{
						if (preg_match('/%/i',$fields))
							$sql = $sql->where($selrows.' like ?', "$fields");
						else 
							$sql = $sql->where($selrows.'= ?', "$fields");
					}			
					$i++;
				}
			}				
		}
		
//		$sql = $sql->group('uidUser');
    	$sql = $sql->order(array($order))->limit($end, $start);
//    	$sql = $sql->order(array($order))->limitPage($start, $end);
    	
//    	$sql = $sql->__toString();
//    	print_r($sql);exit();

    	$db = $this->_db->query($sql);
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
        
    	$data  = array(
            'table'    => $this,
            'data'     => $dataFetch,
            'rowClass' => $this->_rowClass,
            'stored'   => true
        );

        Zend_Loader::loadClass($this->_rowsetClass);
        return new $this->_rowsetClass($data);    	
    }
    
 	function fetchUserHistory($guid, $order,$start,$end)
    {
    	$id = $this->fetchUserGroupHistory($guid);
    	$sql = $this->_db->select()
			->from(array('ku' => 'KutuUser'))
			->joinLeft(array('kude' => 'KutuUserDetail'),
			'ku.guid = kude.uid',array('idUser'=>'id','uidUser'=>'uid','packageId','promotionId','educationId','expenseId','paymentId','businessTypeId','periodeId','activationDate','isEmailSent','createdDate','isActive'))
			->joinLeft(array('gag' => 'gacl_aro_groups'),
			'kude.packageId = gag.id')
			->joinLeft(array('kui' => 'KutuUserInvoice'),
			'ku.kopel = kui.uid', array('expirationDate','DaysLeft' => 'DATEDIFF(NOW(),kui.expirationDate)'))
			->joinLeft(array('kus' => 'KutuUserStatus'),
			'kude.periodeId = kus.accountStatusId','status');
		
		$sql = $sql->where('kude.uid=?',"$guid");
//		$sql = $sql->where('kude.id NOT IN ("'.$id.'")');
//		$sql = $sql1->group('uidUser');
    	$sql = $sql->order(array($order,'idUser DESC'))->limit($end, $start);
//    	$sq = $sql->order(array($order))->limitPage($start, $end);
    	
//    	$sql = $sql->__toString();
//    	print_r($sql);exit();

    	$db = $this->_db->query($sql);
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
        
    	$data  = array(
            'table'    => $this,
            'data'     => $dataFetch,
            'rowClass' => $this->_rowClass,
            'stored'   => true
        );

        Zend_Loader::loadClass($this->_rowsetClass);
        return new $this->_rowsetClass($data);    	
    }
    
    function fetchUserGroupHistory($guid)
    {
    	$db = $this->_db->query("SELECT id from KutuUserDetail WHERE uid='$guid' GROUP BY uid ORDER BY id DESC");
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	if ($dataFetch) return ($dataFetch[0]['id']);
    }
    
    function countUser()
    {
    	$db = $this->_db->query
    	("Select count(*) count From KutuUser");
    	
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	
    	return ($dataFetch[0]['count']);
    }
    
    function countUserGroup($where, $startdt, $enddt)
    {
    	$sql = $this->_db->select()
			->from(array('ku' => 'KutuUser'),array('count'=>'COUNT(*)'))
			->joinLeft(array('gag' => 'gacl_aro_groups'),'ku.packageId = gag.id')
			->joinLeft(array('kui' => 'KutuUserInvoice'),'ku.kopel = kui.uid', array('kui.expirationDate','kui.isPaid'))
			->where('gag.value=?',$where);
			
		if ($startdt && $enddt)
		{
			$sql = $sql->where("kui.expirationDate BETWEEN '$startdt' AND '$enddt'");
		}
		
//    	$db = $this->_db->query
//    	("Select count(*) count From KutuUser,gacl_aro_groups where KutuUser.packageId=gacl_aro_groups.id AND gacl_aro_groups.value='$where'");
    	
//    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	
		$db = $this->_db->query($sql);
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	
    	return ($dataFetch[0]['count']);
    }
    
    function countUserGroupOther()
    {
    	$sql = $this->_db->select()
			->from(array('ku' => 'KutuUser'),'count(*) as count')
			->joinLeft(array('gag' => 'gacl_aro_groups'),'ku.packageId = gag.id')
			->joinLeft(array('kus' => 'KutuUserStatus'),'ku.periodeId = kus.accountStatusId','status')
			->where('gag.value NOT IN ("member_gratis","member_corporate","member_individual","member_bonus")');
			
		$db = $this->_db->query($sql);
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	
    	return ($dataFetch[0]['count']);
    }
    
    /**
     * Returs the row that matches both the username and the password.
     *
     * @param string $userName
     * @param string $password
     *
     * @return Zend_Db_Table_Row
     */
    public function getUserForUserNameCredentials($userName, $password)
    {
        if ($userName == "" || $password == "") {
            return null;
        }

        /**
         * @todo make sure username and password is a unique index
         */
        $select = $this->select()
                       ->from($this)
                       ->where('username = ?', $userName)
//                       ->where('password=?', $password)
                       ->where('isActive=?',1);

        $row = $this->fetchRow($select);

        return $row;
    }

    /**
     * Returns the number of users that share the same username,
     * in case the index on the user_name field is missing.
     *
     * @param string $userName
     *
     * @return integer
     */
    public function getUserNameCount($userName)
    {
        $userName = strtolower(trim((string)$userName));

        if ($userName == "") {
            return 0;
        }

        /**
         * @todo make sure comparison uses lowercase even in db field
         */
        $select = $this->select()
                  ->from($this, array(
                    'COUNT(guid) as count_id'
                  ))
                  ->where('username = ?', $userName);

        $row = $this->fetchRow($select);

        return ($row !== null) ? $row->count_id : 0;
    }
    
    /**
     * Looks up the row with the primary key $guid, fetches the data and
     * creates a new instance of Anoa_User out of it or returns the data
     * as fetched from the table.
     *
     * @param $guid
     *
     *
     * @return Anoa_User or null if no data with the primary key $guid exists.
     *
     * @throws Anoa_BeanContext_Exception The method will throw an exception
     * of the type Anoa_BeanContext_Exception if the BeanContext-object
     * Anoa_User could not be created automatically.
     */
    public function getUser($guid)
    {
        $guid = (int)$guid;

        if ($guid <= 0) {
            return null;
        }

        $row = $this->fetchRow($this->select()->where('guid=?', $guid));

        return $row;
    }

// -------- interface Anoa_BeanContext_Decoratable

    public function getRepresentedEntity()
    {
        return 'Kutu_Modules_Default_User';
    }

    public function getDecoratableMethods()
    {
        return array(
            'getUser',
            'getUserForUserNameCredentials'
        );
    }

}