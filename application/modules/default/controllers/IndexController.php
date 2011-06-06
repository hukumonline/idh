<?php
class IndexController extends Zend_Controller_Action
{
	function preDispatch()
	{
		$this->view->addHelperPath(KUTU_ROOT_DIR.'/library/Kutu/View/Helper','Kutu_View_Helper');
	}
	function indexAction()
	{
		$sReturn = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		$sReturn = base64_encode($sReturn);
		
		$auth =  Zend_Auth::getInstance();
		if(!$auth->hasIdentity())
		{
			$registry = Zend_Registry::getInstance();
			$config = $registry->get('config');
			
			$loginUrl = $config->identity->config->local->login->url;
						
			$this->_redirect(KUTU_ROOT_URL.$loginUrl.'?returnTo='.$sReturn);
		}
		
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		$rowset = $tblCatalog->fetchRow("shortTitle='halaman-depan-login' AND status=99");
		
		if(!empty($rowset))
		{
			$rowsetCatalogAttribute = $rowset->findDependentRowsetCatalogAttribute();
			$fixedContent = $rowsetCatalogAttribute->findByAttributeGuid('fixedContent')->value;
		}
		else 
		{
			$fixedContent = '';
		}
		
		$this->view->content = $fixedContent;
		
		$this->view->sReturn = $sReturn;
	}
}
?>