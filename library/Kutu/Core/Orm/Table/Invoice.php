<?php

/**
 * @package kutump
 * @copyright 2008-2009 hukumonline.com/en.hukumonline.com
 * @author Nihki Prihadi <nihki@hukumonline.com>
 *
 * $Id: Invoice.php 2009-01-10 19:47: $
 */

class Kutu_Core_Orm_Table_Invoice extends Zend_Db_Table_Abstract 
{
	protected $_name = 'KutuUserInvoice';
	protected $_use_adapter = 'identity';
	protected $_referenceMap = array(
		'User' => array(
			'columns'		=> 'user_id',
			'refTableClass'	=> 'Kutu_Core_Orm_Table_User',
			'refColumns'	=> 'guid'
		)
	);
	
	public function insert(array $data)
	{
		return parent::insert($data);
	}
}