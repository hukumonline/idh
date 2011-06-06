<?php

class Kutu_Core_Orm_Table_Rowset_UserDetail extends Zend_Db_Table_Rowset_Abstract 
{
	function findByUserDetail($guid)
	{
        foreach ($this as $row) {
            if ($row->id == $guid) 
            {
                return $row;
            }
        }
        return null;		
	}
}