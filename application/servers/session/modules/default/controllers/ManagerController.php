<?php

/**
 * manage Session for application
 * @package Kutu
 * 
 */

class ManagerController extends Zend_Controller_Action 
{
	public function generateAction()
	{
		$r = $this->getRequest();
		$returnUrl = base64_decode($r->getParam('returnTo'));
		
		if(strpos($returnUrl,'?'))
			$sAddition = '&';
		else 
			$sAddition = '?';
		
		header("location: $returnUrl".$sAddition."PHPSESSID=".Zend_Session::getId());
	}
}
