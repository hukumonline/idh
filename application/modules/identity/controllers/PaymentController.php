<?php
class Identity_PaymentController extends Zend_Controller_Action
{
	function preDispatch()
	{
		$this->view->addHelperPath(KUTU_ROOT_DIR.'/library/Kutu/View/Helper','Kutu_View_Helper');
		$this->_helper->layout->setLayoutPath(array('layoutPath'=>KUTU_ROOT_DIR.'/application/modules/identity/views/layouts'));
	}
	function confirmAction()
	{
		$this->_helper->layout->setLayout('layout-hukumonlineid-ps');
		
		$auth = Zend_Auth::getInstance();
		if (!$auth->hasIdentity())
		{
			$this->_forward('restricted','error','identity',array('type' => 'identity','num' => 101));			
		}
		
	}
}