<?php
class Kutu_Controller_Plugin_Auth extends Zend_Controller_Plugin_Abstract {
	
	private $auth;
	
	public function __construct(Zend_Auth $auth)
	{
		$this->auth = $auth;
	}
	
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
        // check here if the user's authentity is already set
        if ($this->auth->hasIdentity()) {
            return;
        }

        // anything other means the user is not logged in
        $request->setModuleName('identity');
        $request->setControllerName('account');
        $request->setActionName('login');
	}
}
?>