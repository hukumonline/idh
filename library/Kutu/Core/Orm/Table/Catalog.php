<?php

/**
 * manage Table Catalog
 * 
 * @author Nihki Prihadi <nihki@hukumonline.com>
 * @package Kutu
 * 
 */

class Kutu_Core_Orm_Table_Catalog extends Kutu_Core_Orm_CoreDb  
{ 
    protected $_name = 'KutuCatalog'; 
    //protected $_sequence = false;
    protected $_use_adapter = 'hol';
    protected $_rowClass = 'Kutu_Core_Orm_Table_Row_Catalog';
    protected $_rowsetClass = 'Kutu_Core_Orm_Table_Rowset_Catalog';
    protected $_dependentTables = array('Kutu_Core_Orm_Table_CatalogAttribute','Kutu_Core_Orm_Table_CatalogFolder');
    
    function fetchFromFolder($folderGuid,$start = 0 ,$end = 0)
    {
		$sql = $this->_db->select()
			->from(array('KC' => 'KutuCatalog'))
			->join(array('KCF' => 'KutuCatalogFolder'),
			'KC.guid = KCF.catalogGuid')
			->where('KCF.folderGuid=?',"$folderGuid");
		
		$sql = $sql->order('KC.createdDate DESC')->limit($end, $start);
		
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
    function fetchFromFolderByDate($folderGuid, $gDate, $start = 0 ,$end = 0)
    {
		$sql = $this->_db->select()
			->from(array('KC' => 'KutuCatalog'))
			->join(array('KCF' => 'KutuCatalogFolder'),
			'KC.guid = KCF.catalogGuid')
			->where("DATE_FORMAT(KC.createdDate,'%Y-%m-%d') = '$gDate'")
			->where('KCF.folderGuid=?',"$folderGuid");
		
		$sql = $sql->order('KC.createdDate DESC')->limit($end, $start);
		
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

	function implode_with_keys($glue, $array, $valwrap='')
    {
    	if ($array) {
	        foreach($array AS $key => $value) {
	            $ret[] = $valwrap.$value.$valwrap;
	        }
	        return implode($glue, $ret);
    	}
    }    
    
    function countCatalogsInFolder($folderGuid)
    {
    	$db = $this->_db->query
    	("Select count(*) count From KutuCatalog,KutuCatalogFolder 
    	where KutuCatalog.guid=KutuCatalogFolder.catalogGuid AND KutuCatalogFolder.folderGuid='$folderGuid'");
    	
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	
    	return ($dataFetch[0]['count']);
    }
    function countCatalogsInFolderAndChildren($folderGuid)
    {
    	$db = $this->_db->query
    	("Select count(*) count From KutuFolder,KutuCatalogFolder, KutuCatalog 
    	where KutuCatalog.guid=KutuCatalogFolder.catalogGuid AND KutuFolder.guid=KutuCatalogFolder.folderGuid AND KutuFolder.path LIKE '%$folderGuid%'");
    	
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	
    	return ($dataFetch[0]['count']) + $this->countCatalogsInFolder($folderGuid);
    }
    function countCatalogsInBetween($start,$end)
    {
    	$db = $this->_db->query
    	("SELECT count(*) count From KutuCatalog WHERE createdDate BETWEEN '$start' AND '$end'");
    	
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	
    	return ($dataFetch[0]['count']);
    }
    function countCatalogsInBetweenProfile($start,$end,$profile)
    {
    	$db = $this->_db->query
    	("SELECT count(*) count From KutuCatalog WHERE createdDate BETWEEN '$start' AND '$end' AND profileGuid = '$profile'");
    	
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	
    	return ($dataFetch[0]['count']);
    }
    function countCatalogsPubBetween($start,$end)
    {
    	$db = $this->_db->query
    	("SELECT count(*) count From KutuCatalog WHERE publishedDate BETWEEN '$start' AND '$end'");
    	
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	
    	return ($dataFetch[0]['count']);
    }
    function countCatalogsProfile($profile)
    {
    	$db = $this->_db->query
    	("SELECT count(*) count From KutuCatalog WHERE profileGuid = '$profile'");
    	
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	
    	return ($dataFetch[0]['count']);
    }
    function countCatalogsForAuthor($author)
    {
    	$db = $this->_db->query
    	("SELECT count(*) count From KutuCatalog WHERE createdBy = '$author'");
    	
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	
    	return ($dataFetch[0]['count']);
    }
    function countMainNews()
    {
    	$db = $this->_db->query
    	("SELECT count(*) count From KutuCatalog WHERE KutuCatalog.guid IN (SELECT catalogGuid FROM KutuCatalogAttribute WHERE KutuCatalogAttribute.attributeGuid = 'fixedMainNews' AND KutuCatalogAttribute.value = '1') ORDER BY KutuCatalog.createdDate DESC");
    	
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	
    	return ($dataFetch[0]['count']);
    }
    
    function getPublishedDate()
    {
    	$sql = $this->_db->select()
    		->from('KutuCatalog', array('mpublish' => 'max(DATEDIFF( `expiredDate` , `publishedDate` ))'))
    		->where('profileGuid = ?','kutu_contentjp');
    	
//		$sql = $this->_db->select()
//			->from('KutuCatalog')
//			->where('DATEDIFF( `expiredDate` , `publishedDate` ) = ?',
//				$this->_db->select()
//	    		->from('KutuCatalog', 'max(DATEDIFF( `expiredDate` , `publishedDate` ))')
//	    		->where('profileGuid = ?','kutu_contentjp'))
//	    	->where('profileGuid = ?','kutu_contentjp')
//	    	->where('status = ?',1);

    	$db = $this->_db->query($sql);
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	
    	return ($dataFetch[0]['mpublish']);
    }
    function fetchNews(array $folderGuid,$limit = 0)
    {
    	$data = $this->implode_with_keys(", ", $folderGuid, "'");
		$sql = $this->_db->select()
			->from(array('KC' => 'KutuCatalog'))
			->join(array('KCF' => 'KutuCatalogFolder'),
			'KC.guid = KCF.catalogGuid')
			->where("KC.status=?",99)
			->where("KCF.folderGuid IN ($data)")
			->group("KCF.catalogGuid")
			->order('KC.createdDate DESC')->limit($limit);
		
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
    function export($folderGuid)
    {
    	$db = $this->_db->query
    	("SELECT * FROM KutuCatalog,KutuCatalogFolder WHERE KutuCatalog.guid=KutuCatalogFolder.catalogGuid AND KutuCatalogFolder.folderGuid IN (SELECT guid FROM `KutuFolder` WHERE `path` LIKE '%/$folderGuid/%' OR `path` LIKE '$folderGuid/%' OR `guid` LIKE '$folderGuid') GROUP BY KutuCatalogFolder.catalogGuid");
    	
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
} 
