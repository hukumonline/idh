<?php

class Kutu_Acl_Adapter_Remote
{
	private $_aclEngine;
	private $_acl;
	private $_httpClient;
	private $_remoteUrl;
	
	function __construct($remoteUrl)
	{
		$this->_remoteUrl = $remoteUrl;
		$this->_httpClient = new Zend_Http_Client();
        
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
		$this->_httpClient->setHeaders("User-Agent: $userAgent");
	}
	private function _sendHttpRequest()
	{
		$client = $this->_httpClient;
		
		try {
			
			//[TODO] we might need to remove this Zend_Http_Client::POST to each function that need to post variable. This is to remedy
			// the possibility of server throwing HTTP INTERNAL SERVER ERROR.
			$response = $client->request(Zend_Http_Client::POST);
		} catch (Zend_Exception $e) {
			
			throw new Zend_Exception('Failed sending http request. Message:'.$e->getMessage());
		}
		
		if($response->isError())
		{
			throw new Zend_Exception('ACL server has been reached, but return HTTP Error.' . $response->getBody());
		}
		
		return $response->getBody();
	}
	function getUsers()
	{
		$client = $this->_httpClient;
		$client->setUri($this->_remoteUrl."/get-users/");
		
		//if we don't put dummy post variable, in some servers, will throw HTTP ERROR internal server error
		$client->setParameterPost(array(
            'dummypost' => 'test'
        ));
		$sResponse = $this->_sendHttpRequest();
		
		$aResult = Zend_Json::decode($sResponse);
        
        if(!is_array($aResult))
        	throw new Zend_Exception('ACL server returned Exception. Message: '.$aResult);
        else 
			return $aResult;
	}
	
	function addUser($username, $groupValue=NULL)
	{
		$client = $this->_httpClient;
		$client->setParameterPost(array(
            'username' => $username,
            'groupValue' => $groupValue
        ));
        $client->setUri($this->_remoteUrl."/add-user/");
		
		$sResponse = $this->_sendHttpRequest();
		if(!empty($sResponse))
		{
			throw new Zend_Exception('ACL server returned Exception. Message: '.$sResponse);
		}
		return $sResponse;
		
	}
	function getUserGroupIds($username)
	{
		$client = $this->_httpClient;
		$client->setParameterPost(array(
            'username' => $username
        ));
        $client->setUri($this->_remoteUrl."/get-user-group-ids/");
		
		$sResponse = $this->_sendHttpRequest();
		
		$aResult = Zend_Json::decode($sResponse);
        
        if(!is_array($aResult))
        	throw new Zend_Exception('ACL server returned Exception. Message: '.$aResult);
        else 
			return $aResult;
		
	}
	function addUserToGroup($username, $groupValue)
	{
		$client = $this->_httpClient;
		$client->setParameterPost(array(
            'username' => $username,
            'groupValue' => $groupValue
        ));
        $client->setUri($this->_remoteUrl."/add-user-to-group/");
		
		$sResponse = $this->_sendHttpRequest();
		
		$aResult = Zend_Json::decode($sResponse);
        
        if(!is_bool($aResult))
        	throw new Zend_Exception('ACL server returned Exception. Message: '.$aResult);
        else 
			return $aResult;
	}
	function removeUserFromGroup($username, $groupValue)
	{
		$client = $this->_httpClient;
		$client->setParameterPost(array(
            'username' => $username,
            'groupValue' => $groupValue
        ));
        $client->setUri($this->_remoteUrl."/remove-user-from-group/");
		
		$sResponse = $this->_sendHttpRequest();
		
		$aResult = Zend_Json::decode($sResponse);
        
        if(!is_bool($aResult))
        	throw new Zend_Exception('ACL server returned Exception. Message: '.$aResult);
        else 
        	return $aResult;
	}
	function getGroups()
	{
		$client = $this->_httpClient;
        $client->setUri($this->_remoteUrl."/get-groups/");
		
		$sResponse = $this->_sendHttpRequest();
		
		$aResult = Zend_Json::decode($sResponse);
        
        if(!is_array($aResult))
        	throw new Zend_Exception('ACL server returned Exception. Message: '.$aResult);
        else 
			return $aResult;
	}
	
	//WILL ONLY RETURN TRUE, FALSE or ACL ID
	function allow($username=NULL, $groupValue=NULL, $action, $section='content', $itemGuid)
	{
		$client = $this->_httpClient;
		$client->setParameterPost(array(
            'username' => $username,
            'groupValue' => $groupValue,
            'perm' => $action,
            'section' => $section,
            'itemGuid' => $itemGuid
        ));
        $client->setUri($this->_remoteUrl."/allow/");
		
		$sResponse = $this->_sendHttpRequest();
		
		$aResult = Zend_Json::decode($sResponse);
        
		if(is_bool($aResult))
		{
			return $aResult;
		}
		else 
		{
	        if(is_numeric($aResult))
	        {
	        	if($aResult > 0)
	        		return $aResult;
	        	else 
	        		throw new Zend_Exception('ACL server: Allow action failed '.$aResult);
	        }
	        else 
				throw new Zend_Exception('ACL server returned Exception. Message: '.$aResult);
		}
	}
	
	// return TRUE or FALSE
	function removeAllow($username=NULL, $groupValue=NULL, $action, $section='content', $itemGuid)
	{
		$client = $this->_httpClient;
		$client->setParameterPost(array(
            'username' => $username,
            'groupValue' => $groupValue,
            'perm' => $action,
            'section' => $section,
            'itemGuid' => $itemGuid
        ));
        $client->setUri($this->_remoteUrl."/remove-allow/");
		
		$sResponse = $this->_sendHttpRequest();
		
		$result = Zend_Json::decode($sResponse);
        
		if(is_bool($result))
		{
			return $result;
		}
		else 
		{
			throw new Zend_Exception('ACL server returned Exception. Message: '.$result);
		}
	}
	
	/**
	 * this function will return action array: a[0]='read', a[1]='delete'
	 *
	 * @param string $username
	 * @param string $groupValue
	 * @param string $itemGuid
	 */
	function getPermissionsOnContent($username=NULL, $groupValue=NULL, $itemGuid)
	{
		$client = $this->_httpClient;
		$client->setParameterPost(array(
            'username' => $username,
            'groupValue' => $groupValue,
            'itemGuid' => $itemGuid
        ));
        $client->setUri($this->_remoteUrl."/get-permissions-on-content/");
		
		$sResponse = $this->_sendHttpRequest();
		$result = Zend_Json::decode($sResponse);
		
		if(is_array($result))
			return $result;
		else 
			throw new Zend_Exception('ACL server returned Exception. Message: '.$result);
	}
	
	function isAllowed($username, $itemGuid, $action, $section='content')
	{
		$client = $this->_httpClient;
		$client->setParameterPost(array(
            'username' => $username,
            'itemGuid' => $itemGuid,
            'perm' => $action,
            'section' => $section
        ));
        $client->setUri($this->_remoteUrl."/is-allowed/");
		
		$sResponse = $this->_sendHttpRequest();
		$result = Zend_Json::decode($sResponse);
		
		if(is_bool($result))
			return $result;
		else 
			throw new Zend_Exception('ACL server returned Exception. Message: '.$result);
	}
}