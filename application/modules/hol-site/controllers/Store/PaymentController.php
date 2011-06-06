<?php
class HolSite_Store_PaymentController extends Zend_Controller_Action
{
    protected $_user;
    protected $_userFinanceInfo;
    protected $_paymentVars;
    protected $_testMode;
    protected $_orderIdNumber;
    protected $_defaultCurrency;
    protected $_currencyValue;
    protected $_holMail;

    function  preDispatch()
    {
    	$this->view->addHelperPath(KUTU_ROOT_DIR.'/library/Kutu/View/Helper','Kutu_View_Helper');
        $this->_helper->layout->setLayout('layout-storepayment');
        $this->_helper->layout->setLayoutPath(array('layoutPath'=>KUTU_ROOT_DIR.'/application/modules/hol-site/views/layouts'));
        
        $this->_testMode=true;
		$this->_defaultCurrency='USD';
		$tblPaymentSetting = new Kutu_Core_Orm_Table_PaymentSetting();
		$usdIdrEx = $tblPaymentSetting->fetchAll($tblPaymentSetting->select()->where(" settingKey= 'USDIDR'"));
		$this->_currencyValue = $usdIdrEx[0]->settingValue;
		
		Zend_Session::start();
		
        $tblPaymentSetting = new Kutu_Core_Orm_Table_PaymentSetting();        
        $rowSet = $tblPaymentSetting->fetchAll();
        
        for($iRow=0; $iRow<count($rowSet);$iRow++){
            $key=$rowSet[$iRow]->settingKey;
            $this->_paymentVars[$key]=$rowSet[$iRow]->settingValue;
        }
		
        $tblSetting = new Kutu_Core_Orm_Table_PaymentSetting();
        $this->_holMail = $tblSetting->fetchAll($tblSetting->select()->where("settingKey = 'paypalBusiness'"));
    }
	function listAction()
	{
        $this->_checkAuth();

        $where=$this->_user->kopel;
        
        $modelOrder = new Kutu_Core_Orm_Table_Order();

        $rowsetTotal = $modelOrder->countOrders("'".$where."'");
        $rowset = $modelOrder->getOrderSummary("'".$where."'");

        $this->view->numCount = $rowsetTotal;
        $this->view->listOrder = $rowset;
		
	}
    function transactionAction()
    {
        $this->_checkAuth();

        $where=$this->_user->kopel;
        
        $modelOrder = new Kutu_Core_Orm_Table_Order();

        $rowsetTotal = $modelOrder->countOrders("'".$where."' AND (orderStatus = 3 OR orderStatus = 5)");
        $rowset = $modelOrder->getOrderSummary("'".$where."' AND (orderStatus = 3 OR orderStatus = 5)");

        $this->view->numCount = $rowsetTotal;
        $this->view->listOrder = $rowset;
    }
    public function confirmAction()
    {
        $this->_checkAuth();
        
        $userId = $this->_user->kopel;
        
        $modelOrder = new Kutu_Core_Orm_Table_Order();

        $rowset = $modelOrder->getTransactionToConfirm($userId);
        $numCount = $modelOrder->getTransactionToConfirmCount($userId);

        $modelPaymentSetting = new Kutu_Core_Orm_Table_PaymentSetting();
        $bankAccount = $modelPaymentSetting->fetchAll("settingKey = 'bankAccount'");

        if($this->_request->get('sended') == 1){
            $this->view->sended = 'Payment Confirmation Sent';
        }

        $this->view->numCount = $numCount;
        $this->view->rowset = $rowset;
        $this->view->bankAccount = $bankAccount;
    }
    public function billingAction()
    {
        $this->_checkAuth();

        $modelUserFinance = new Kutu_Core_Orm_Table_UserFinance();
        $rowset = $modelUserFinance->getUserFinance($this->_user->kopel);
        $this->view->rowset = $rowset;

        $modelOrder = new Kutu_Core_Orm_Table_Order();
        $outstandingAmount = $modelOrder->outstandingUserAmout($this->_userFinanceInfo->userId);
        $this->view->outstandingAmount = $outstandingAmount;

        if($this->_request->isPost()){
            $data['taxNumber'] = $this->_request->getParam('taxNumber');
            $data['taxCompany'] = $this->_request->getParam('taxCompany');
            $data['taxAddress'] = $this->_request->getParam('taxAddress');
            $data['taxCity'] = $this->_request->getParam('taxCity');
            $data['taxProvince'] = $this->_request->getParam('taxProvince');
            $data['taxZip'] = $this->_request->getParam('taxZip');
            $data['taxPhone'] = $this->_request->getParam('taxPhone');
            $data['taxFax'] = $this->_request->getParam('taxFax');
            $data['taxCountryId'] = $this->_request->getParam('taxCountryId');
            $where = "userId = '".$this->_user->kopel."'";
            
            $userFinance = new Kutu_Core_Orm_Table_UserFinance();
            $userFinance->update($data,$where);
            
            $this->_helper->redirector('bilupdsuc');
        }
    }
    public function bilupdsucAction()
    {
        $this->_checkAuth();
        $this->_redirect(KUTU_ROOT_URL.'/store/payment/billing');
    }
    public function documentAction()
    {
        $this->_checkAuth();

        $userId = $this->_userFinanceInfo->userId;
        
        $modelOrder = new Kutu_Core_Orm_Table_Order();

        $rowset = $modelOrder->getDocumentSummary($userId);
        $rowsetTotal = $modelOrder->countDocument($userId);

        $this->view->numCount = $rowsetTotal;
        $this->view->rowset = $rowset;
    }
    public function instructionAction(){
		
		$this->_checkAuth();
		
		$orderId = $this->_request->getParam('orderId');
		
		$tblOrder = new Kutu_Core_Orm_Table_Order();
		$row = $tblOrder->find($orderId)->current();
		if(empty($row))
			die('NO ORDER DATA AVAILABLE');
			
		//var_dump($rowset);
		
		$this->view->row = $row;
		
		$_SESSION['jCart'] = null;         
    }
    private function _checkAuth()
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity())
        {
	        $sReturn = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
	        $sReturn = base64_encode($sReturn);
	
			$registry = Zend_Registry::getInstance();
			$config = $registry->get('config');
			
			$loginUrl = $config->identity->config->local->login->url;
						
			$this->_redirect(KUTU_ROOT_URL.$loginUrl.'?returnTo='.$sReturn);
            
        }
        else
        {
            $this->_user = $auth->getIdentity();
        }

        $modelUserFinance = new Kutu_Core_Orm_Table_UserFinance();
        $this->_userFinanceInfo = $modelUserFinance->find($this->_user->kopel)->current();
        if (empty($this->_userFinanceInfo))
        {
            $finance = $modelUserFinance->fetchNew();
            $finance['userId'] = $this->_user->kopel;
            $finance->save();
            $this->_userFinanceInfo = $modelUserFinance->find($this->_user->kopel)->current();
        }
    }	
}