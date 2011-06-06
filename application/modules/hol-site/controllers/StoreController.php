<?php
class HolSite_StoreController extends Zend_Controller_Action 
{
	protected $_user;
	
	function preDispatch()
	{
    	$this->view->addHelperPath(KUTU_ROOT_DIR.'/library/Kutu/View/Helper','Kutu_View_Helper');
        $this->_helper->layout->setLayout('layout-store');
        $this->_helper->layout->setLayoutPath(array('layoutPath'=>KUTU_ROOT_DIR.'/application/modules/hol-site/views/layouts'));
		
		$sReturn = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		$sReturn = base64_encode($sReturn);
			
		$registry = Zend_Registry::getInstance();
		$config = $registry->get('config');
		
		$loginUrl = $config->identity->config->local->login->url;
		
		$auth =  Zend_Auth::getInstance();
		if(!$auth->hasIdentity())
		{
			$this->_redirect(KUTU_ROOT_URL.$loginUrl.'?returnTo='.$sReturn);
		}
		else
		{
			$this->_user = $auth->getIdentity();
		}
	}
	function viewinvoiceAction()
	{
		$orderId = $this->_request->getParam('orderId');
		
		$tblOrder = new Kutu_Core_Orm_Table_Order();
		$items = $tblOrder->getOrderDetail($orderId);
		
		$this->view->orderId = $orderId;
		$this->view->invoiceNumber = $items[0]['invoiceNumber'];
		$this->view->datePurchased = Kutu_Lib_Formater::get_date($items[0]['datePurchased']);
		
		$tblPaymentSetting = new Kutu_Core_Orm_Table_PaymentSetting();        
        $rowTaxRate = $tblPaymentSetting->fetchRow("settingKey='taxRate'");
		
		if($this->_user->kopel != $items[0]['userId'])
		{
			$this->_redirect(KUTU_ROOT_URL.'/store/cartempty');
		}
		
		$result = array();
		$result['subTotal'] = 0;
		for($iCart=0;$iCart<count($items);$iCart++){
            
			$itemId = $items[$iCart]['itemId'];
            $qty= 1;
            $itemPrice = $items[$iCart]['price'];
            
            $result['items'][$iCart]['itemId']= $itemId;
            $result['items'][$iCart]['item_name'] = $items[$iCart]['documentName']; 
            $result['items'][$iCart]['itemPrice']= $itemPrice;
            $result['items'][$iCart]['qty']= $qty;
            $result['subTotal'] += $itemPrice*$qty;
        }

		$result['taxAmount']= $result['subTotal'] * $rowTaxRate->settingValue/100;
        $result['grandTotal'] = $result['subTotal'] + $result['taxAmount'];

		$this->view->cart = $result;
		
		$data = array();
		$data['taxNumber'] = $items[0]['taxNumber'];
		$data['taxCompany'] = $items[0]['taxCompany'];
		$data['taxAddress'] = $items[0]['taxAddress'];
		$data['taxCity'] = $items[0]['taxCity'];
		$data['taxZip'] = $items[0]['taxZip'];
		$data['taxProvince'] = $items[0]['taxProvince'];
		$data['taxCountry'] = $items[0]['taxCountryId'];
		$data['paymentMethod'] = $items[0]['paymentMethod'];
		$data['currencyValue'] = $items[0]['currencyValue'];
		
		$this->view->data = $data;
	}
	public function cartemptyAction()
	{
		
	}
}