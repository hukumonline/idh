<?php

/**
 * module URL
 * 
 * @package Kutu
 * 
 */

class Kutu_Core_Util
{
	function getRootUrl($kutuRootDir)
	{
		$aPath = (pathinfo($kutuRootDir));
		
		$serverHttpHost = $_SERVER['HTTP_HOST'];
		$serverHttpHost = str_replace(':443','',$serverHttpHost);
		
		if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on")
		{
			$httpPrefix = 'https://';
		}
		else 
		{
			$httpPrefix = 'http://';
		}
		
		$sTmpPathUrl = $serverHttpHost .'/'.$aPath['basename'];
		$sTmpPathUrl = strstr($this->selfURLNoPort(), $sTmpPathUrl);
		
		if(!empty($sTmpPathUrl))
			return $httpPrefix.$serverHttpHost.'/'.$aPath['basename'];
		else 
			return $httpPrefix.$serverHttpHost; 
	}
	
	function selfURL() 
	{ 
		$s = empty($_SERVER["HTTPS"]) ? '' 
				: ($_SERVER["HTTPS"] == "on") ? "s" 
				: ""; 
		$protocol = $this->strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s; 
		$port = ($_SERVER["SERVER_PORT"] == "80") ? "" 
				: (":".$_SERVER["SERVER_PORT"]); 
		return $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI']; 
	} 
	function selfURLNoPort() 
	{ 
		$s = empty($_SERVER["HTTPS"]) ? '' 
				: ($_SERVER["HTTPS"] == "on") ? "s" 
				: ""; 
		$protocol = $this->strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s; 
		$port = ($_SERVER["SERVER_PORT"] == "80") ? "" 
				: (":".$_SERVER["SERVER_PORT"]); 
		return $protocol."://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']; 
	} 
	function strleft($s1, $s2) 
	{ 
		return substr($s1, 0, strpos($s1, $s2)); 
	}
	
	function getControllerUrl()
	{
		$front = Zend_Controller_Front::getInstance();
		$request = $front->getRequest();
		$module  = $request->getModuleName();
		$dirs    = $front->getControllerDirectory();
		if (empty($module) || !isset($dirs[$module])) {
			$module = $front->getFrontController()->getDispatcher()->getDefaultModule();
		}
		$baseDir = dirname($dirs[$module]);
		$kutuRootDir = str_replace("\\", "/", KUTU_ROOT_DIR);
		$baseDir = str_replace("\\", "/", dirname($baseDir));
		$baseDir = str_replace($kutuRootDir,'', $baseDir);
		$baseDir = dirname($baseDir);
		return KUTU_ROOT_URL . $baseDir.'/'.$module.'/'.$request->getControllerName();
	}
}