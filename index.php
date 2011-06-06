<?php

/**
 * @package kutump
 * @author Nihki Prihadi <nihki@madaniyah.com>
 *
 * $Id: index.php 2009-02-04 15:03
 */

require_once "init.php";

//$kutuSession = new Kutu_Session_Manager();
//$kutuSession->start();

// set up authentication storage
//$auth = Zend_Auth::getInstance();
// set session storage
//$storage = new Zend_Auth_Storage_Session(Kutu_Keys::SESSION_AUTH_NAMESPACE);
//$auth->setStorage($storage);

// set registry
//$registry = Zend_Registry::getInstance();
//$registry->set(Kutu_Keys::REGISTRY_AUTH_OBJECT,$auth);

$front = Zend_Controller_Front::getInstance();
$front->throwExceptions(false);
$front->addModuleDirectory(KUTU_ROOT_DIR.'/application/modules');

$router = $front->getRouter();

$route = new Zend_Controller_Router_Route_Static(
	'identity/daftar',
	array('module'=>'identity','controller'=>'account','action'=>'signup')
);
$router->addRoute('signup',$route);

$route = new Zend_Controller_Router_Route_Static(
	'identity/lupasandi',
	array('module'=>'identity','controller'=>'account','action'=>'lupasandi')
);
$router->addRoute('lupasandi',$route);

$route = new Zend_Controller_Router_Route_Static(
	'identity/daftar/penjelasan',
	array('module'=>'identity','controller'=>'account','action'=>'penjelasan')
);
$router->addRoute('penjelasan',$route);

/*
$route = new Zend_Controller_Router_Route_Static(
	'identity/daftar/gratis',
	array('module'=>'identity','controller'=>'account','action'=>'gratis')
);
$router->addRoute('identity-gratis',$route);

$route = new Zend_Controller_Router_Route_Static(
	'identity/daftar/individual',
	array('module'=>'identity','controller'=>'account','action'=>'individual')
);
$router->addRoute('identity-individual',$route);
*/

$route = new Zend_Controller_Router_Route_Static(
	'kodeetik',
	array('module'=>'misc','controller'=>'browser','action'=>'kodeetik')
);
$router->addRoute('kodeetik',$route);

$route = new Zend_Controller_Router_Route_Static(
	'mitrakami',
	array('module'=>'misc','controller'=>'browser','action'=>'mitrakami')
);
$router->addRoute('mitrakami',$route);

$route = new Zend_Controller_Router_Route_Static(
	'tentangkami',
	array('module'=>'misc','controller'=>'browser','action'=>'tentangkami')
);
$router->addRoute('tentangkami',$route);

$route = new Zend_Controller_Router_Route_Static(
	'produk',
	array('module'=>'misc','controller'=>'browser','action'=>'produk')
);
$router->addRoute('produk',$route);

$route = new Zend_Controller_Router_Route_Static(
	'identity/daftar/aturan.pakai',
	array('module'=>'identity','controller' => 'account', 'action' => 'aturan.pakai')
);
$router->addRoute('identity-aturanpakai',$route);

$route = new Zend_Controller_Router_Route(
	'identity/daftar/paket/:title',
	array('module'=>'identity','controller' => 'account', 'action' => 'paket')
);
$router->addRoute('identity-paket',$route);

$route = new Zend_Controller_Router_Route_Static(
	'identity/daftar/save',
	array('module'=>'identity','controller' => 'account', 'action' => 'save')
);
$router->addRoute('identity-save',$route);

$route = new Zend_Controller_Router_Route_Static(
	'identity/lupasandi/kirimsandi',
	array('module'=>'identity','controller' => 'account', 'action' => 'kirimsandi')
);
$router->addRoute('identity-kirimsandi',$route);

$route = new Zend_Controller_Router_Route_Static(
	'identity/personal.setting',
	array('module'=>'identity','controller' => 'account', 'action' => 'personal.setting')
);
$router->addRoute('identity-personal',$route);

$route = new Zend_Controller_Router_Route_Static(
	'identity/profile',
	array('module'=>'identity','controller' => 'account', 'action' => 'profile')
);
$router->addRoute('identity-profile',$route);

$route = new Zend_Controller_Router_Route_Static(
	'identity/editprofile',
	array('module'=>'identity','controller' => 'user', 'action' => 'edit')
);
$router->addRoute('identity-editprofile',$route);

$route = new Zend_Controller_Router_Route_Static(
	'identity/changeusername',
	array('module'=>'identity','controller' => 'account', 'action' => 'changeusername')
);
$router->addRoute('identity-changeusername',$route);

$route = new Zend_Controller_Router_Route_Static(
	'identity/feedback',
	array('module'=>'identity','controller' => 'account', 'action' => 'feedback')
);
$router->addRoute('identity-feedback',$route);

$route = new Zend_Controller_Router_Route_Static(
	'identity/send.feedback',
	array('module'=>'identity','controller' => 'account', 'action' => 'send.feedback')
);
$router->addRoute('identity-sendfeedback',$route);

$route = new Zend_Controller_Router_Route_Static(
	'identity/change.password',
	array('module'=>'identity','controller' => 'user', 'action' => 'change.password')
);
$router->addRoute('identity-changepassword',$route);

$route = new Zend_Controller_Router_Route_Static(
	'identity/changeemail',
	array('module'=>'identity','controller' => 'user', 'action' => 'changeemail')
);
$router->addRoute('identity-changeemail',$route);

$route = new Zend_Controller_Router_Route_Static(
	'identity/picture',
	array('module'=>'identity','controller' => 'user', 'action' => 'picture')
);
$router->addRoute('identity-picture',$route);

$route = new Zend_Controller_Router_Route_Static(
	'identity/upgrade',
	array('module'=>'identity','controller' => 'user', 'action' => 'upgrade')
);
$router->addRoute('identity-upgrade',$route);

$route = new Zend_Controller_Router_Route(
	'identity/upgradesub/*',
	array('module'=>'identity','controller' => 'user', 'action' => 'upgradesub')
);
$router->addRoute('identity-upgradesub',$route);

$route = new Zend_Controller_Router_Route(
	'produk/detail/:guid',
	array('module'=>'misc','controller'=>'browser','action'=>'detail.produk')
);
$router->addRoute('produk-detail',$route);

$route = new Zend_Controller_Router_Route(
    'identity/relogin/:returnTo',
    array('module'=>'identity','controller' => 'account', 'action' => 'relogin')
);
$router->addRoute('relogin', $route);
		
$route = new Zend_Controller_Router_Route(
    'identity/login/:returnTo',
    array('module'=>'identity','controller' => 'account', 'action' => 'login')
);
$router->addRoute('login', $route);
		
$route = new Zend_Controller_Router_Route(
    'identity/logout/:returnTo',
    array('module'=>'identity','controller' => 'account', 'action' => 'logout')
);
$router->addRoute('logout', $route);
		
$route = new Zend_Controller_Router_Route(
    'identity/klogin/*',
    array('module'=>'identity','controller' => 'account', 'action' => 'klogin')
);
$router->addRoute('identity-login', $route);
		
$route = new Zend_Controller_Router_Route(
    'identity/get.me.username/*',
    array('module'=>'identity','controller' => 'account', 'action' => 'get.me.username')
);
$router->addRoute('identity-getusername', $route);
		
$route = new Zend_Controller_Router_Route(
    'identity/get.me.email/*',
    array('module'=>'identity','controller' => 'account', 'action' => 'get.me.email')
);
$router->addRoute('identity-getemail', $route);
		
$route = new Zend_Controller_Router_Route(
    'identity/checkemail/*',
    array('module'=>'identity','controller' => 'user', 'action' => 'checkemail')
);
$router->addRoute('identity-checkemail', $route);
		
$route = new Zend_Controller_Router_Route(
    'identity/daftar/checkusername/*',
    array('module'=>'identity','controller' => 'account', 'action' => 'checkusername')
);
$router->addRoute('identity-checkusername', $route);
		
$route = new Zend_Controller_Router_Route(
    'identity/redirect.url',
    array('module'=>'identity','controller' => 'account', 'action' => 'redirect.url')
);
$router->addRoute('identity-redirect.url', $route);
		
$route = new Zend_Controller_Router_Route(
    'membership/user/activate/:id',
    array('module'=>'membership','controller' => 'manager', 'action' => 'activate')
);
$router->addRoute('membership-activate', $route);
		
$route = new Zend_Controller_Router_Route(
    'membership/payment/complete',
    array('module'=>'membership','controller' => 'payment', 'action' => 'complete')
);
$router->addRoute('membership-payment-complete', $route);
		
$route = new Zend_Controller_Router_Route(
    'store/payment/:action/*',
    array('module'=>'hol-site','controller' => 'store_payment')
);
$router->addRoute('store-payment', $route);

$route = new Zend_Controller_Router_Route(
	'store/viewinvoice/*',
	array('module'=>'hol-site','controller'=>'store','action'=>'viewinvoice')
);
$router->addRoute('store', $route);

$route = new Zend_Controller_Router_Route(
	'store/cartempty',
	array('module'=>'hol-site','controller'=>'store','action'=>'cartempty')
);
$router->addRoute('cartempty', $route);
		
// add the plugins
// authentication plugin, checks on each request if the user is logged in
// in the preDispatch()-method
//$authenticationPlugin = new Kutu_Controller_Plugin_Auth($auth);
//$front->registerPlugin($authenticationPlugin);

Zend_Layout::startMvc(
	array(
		'layoutPath' => KUTU_ROOT_DIR.'/application/modules/default/views/layouts',
		'layout' => 'main'	
	)
);

$front->dispatch(); 

?>