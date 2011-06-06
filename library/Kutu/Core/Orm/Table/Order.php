<?php

/**
 * Description of Order
 *
 * @author nihki <nihki@madaniyah.com>
 */

class Kutu_Core_Orm_Table_Order extends Kutu_Core_Orm_CoreDb
{
    protected $_name = 'KutuOrder';
    protected $_use_adapter = 'hol';
    
	public function getOrderDetail($orderId){
		$db = $this->_db->query("SELECT KO.*, KOD.*
										FROM KutuOrder AS KO
										JOIN KutuOrderDetail AS KOD
										ON KOD.orderId = KO.orderId
										WHERE KO.orderId = $orderId");
		
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
    function countOrders($userId)
    {
        $db = $this->_db->query
        ("Select count(KO.orderId) AS count From KutuOrder as KO, KutuOrderDetail AS KOD
    	where KOD.orderID =KO.orderID AND KO.userId=$userId");
        $dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);

        return ($dataFetch[0]['count']);
    }
    public function getOrderSummary($userId){
        $db = $this->_db->query("SELECT KO.*,KOS.ordersStatus,
                                COUNT(itemId) AS countTotal,KU.*
                                FROM
                                ((KutuOrder AS KO
                                LEFT JOIN KutuOrderDetail AS KOD
                                    ON KOD.orderId=KO.orderId)
                                LEFT JOIN hid.KutuUser AS KU
                                    ON KU.kopel = KO.userId)
                                LEFT JOIN KutuOrderStatus AS KOS
                                    ON KOS.orderStatusId = KO.orderStatus
                                WHERE KO.userId = $userId
                                GROUP BY(KO.orderId) DESC");
        
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
    public function getTransactionToConfirm($userId){
        $db = $this->_db->query("SELECT 
                                    KO.*,KOS.ordersStatus,
                                    COUNT(itemId) AS countTotal,KU.kopel 
                                FROM
                                    ((KutuOrder AS KO 
                                LEFT JOIN KutuOrderDetail AS KOD 
                                    ON KOD.orderId = KO.orderId)
                                LEFT JOIN hid.KutuUser AS KU 
                                    ON KU.kopel = KO.userId)
                                LEFT JOIN KutuOrderStatus AS KOS 
                                    ON KOS.orderStatusId = KO.orderStatus
                                WHERE 
                                    KO.userId = '$userId' 
                                AND 
                                    (paymentMethod = 'bank' 
                                AND
                                    (
                                    orderStatus = 5 
									OR orderStatus = 1  
									OR orderStatus = 4
									OR orderStatus = 6
                                    ))
                                GROUP BY(KO.orderId) ASC");
        
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
    public function getTransactionToConfirmCount($userId){
        $db = $this->_db->query("SELECT 
                                    COUNT(orderId) AS countConfirm
                                FROM
                                    KutuOrder 
                                WHERE 
                                    userId = '$userId' 
                                AND 
                                    (
                                    paymentMethod = 'bank'
                                AND
                                    (
                                    orderStatus = 5 
									OR orderStatus = 1 
									OR orderStatus = 4 
									OR orderStatus = 6 
                                    ))");
    	
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	
    	return ($dataFetch[0]['countConfirm']);
    }
    function outstandingUserAmout($userId)
    {
    	$db = $this->_db->query
    	("SELECT SUM(orderTotal) AS total FROM KutuOrder where userId = '$userId' AND  orderStatus=5");
    	
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	
    	return ($dataFetch[0]['total']);
    }
	public function getDocumentSummary($userId){
        $db = $this->_db->query("SELECT KOD.*, KO.datePurchased AS purchasingDate
                                FROM
                                KutuOrderDetail AS KOD,
								KutuOrder AS KO 
                                WHERE 
									KO.orderId = KOD.orderId
								AND
									userId = '$userId'
								AND
									(KO.orderStatus = 3 
									OR
									KO.orderStatus = 5)");
        
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
	function countDocument($userId)
    {
    	$db = $this->_db->query("SELECT count(itemId) as totalDoc
                                FROM
									KutuOrderDetail AS KOD,
									KutuOrder AS KO 
                                WHERE 
									KO.orderId = KOD.orderId
								AND
									userId = '$userId'
								AND
									(KO.orderStatus = 3 
									OR
									KO.orderStatus = 5)");
    	
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	
    	return ($dataFetch[0]['totalDoc']);
    }
}
