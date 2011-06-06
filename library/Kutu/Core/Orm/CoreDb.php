<?php

abstract class Kutu_Core_Orm_CoreDb extends Zend_Db_Table_Abstract
{

 

      function Kutu_Core_Orm_CoreDb($config = null) {

 

            if (isset ( $this->_use_adapter )) {

 

                  $dbAdapters = Zend_Registry::get ( 'dbAdapters' );

                  $config = ($dbAdapters [$this->_use_adapter]);

 

            }

 

            return parent::__construct ( $config );

 

      }

}