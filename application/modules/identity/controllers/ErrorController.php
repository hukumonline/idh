<?php
class Identity_ErrorController extends Zend_Controller_Action {
	function preDispatch()
	{
		$this->view->addHelperPath(KUTU_ROOT_DIR.'/library/Kutu/View/Helper','Kutu_View_Helper');
		$this->_helper->layout->setLayout('layout-hukumonlineid-err');
		$this->_helper->layout->setLayoutPath(array('layoutPath'=>KUTU_ROOT_DIR.'/application/modules/identity/views/layouts'));
	}
	function restrictedAction()
	{
		$req = $this->getRequest();
		$type = ($req->getParam('type'))? $req->getParam('type') : '';
		$num =  ($req->getParam('num'))? $req->getParam('num') : '';
		switch ($type)
		{
			case "identity":
				switch ($num) 
				{
					case 101:
						$error_msg = "Page restricted!!";
						break;
				}
			break;
		}
		$this->view->error_msg = $error_msg;
	}
}
?>