<?php

/**
 * Description of UserFinance
 *
 * @author nihki <nihki@madaniyah.com
 */
class Kutu_Core_Orm_Table_UserFinance extends Kutu_Core_Orm_CoreDb
{
    protected $_name = 'KutuUserFinance';
    protected $_use_adapter = 'hol';
    
    public function getUserFinance($where)
    {
        $db = $this->_db->query("SELECT KUF.*, KU.fullName AS FN, KU.username AS UN, KU.createdDate, KU.createdBy, KU.modifiedDate, KU.modifiedBy FROM KutuUserFinance AS KUF, hid.KutuUser AS KU WHERE userId = '$where' AND KU.kopel = KUF.userId ");

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
