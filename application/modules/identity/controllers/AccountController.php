<?php

class Identity_AccountController extends Zend_Controller_Action 
{
	function preDispatch()
	{
		$this->view->addHelperPath(KUTU_ROOT_DIR.'/library/Kutu/View/Helper','Kutu_View_Helper');
		//$this->_helper->layout->setLayout('layout-hukumonlineid');
		//$this->_helper->layout->setLayout('layout-hukumonlineid-welastic');
		$this->_helper->layout->setLayout('layout-newholid');
		$this->_helper->layout->setLayoutPath(array('layoutPath'=>KUTU_ROOT_DIR.'/application/modules/identity/views/layouts'));
	}
	function lupasandiAction() 			
	{
		$this->_helper->layout->setLayout('layout-hukumonlineid-ext');
	}
	function kirimsandiAction()
	{
		$this->_helper->layout->disableLayout();
		
		$request = $this->getRequest();

		$validator = new Zend_Validate_EmailAddress();
		
		if ($request->getParam('email') == '') {
			$error[] = '- Email harus diisi';
		}
		if (!$validator->isValid($request->getParam('email'))) {
			$error[] = '- Penulisan email salah!';
		}
		if ($request->getParam('user_name') == '') {
			$error[] = '- Nama pengguna diisi!';
		}
		 
		if (isset($error)) {
			
			echo '<b>Error</b>: <br />'.implode('<br />', $error);
			
		} else {
		
		$formater = new Kutu_Core_Hol_User();
				
		$username = $this->_getParam('user_name');
		$email = $this->_getParam('email');
		
		$tblUser = new Kutu_Core_Orm_Table_User();
		$rowUser = $tblUser->fetchRow("username='".$username."' AND email='".$email."'");
		
		if ($rowUser)
		{
			// get mail content
			$mailcontent = $formater->getMailContent("lupa-password");
			// write forgotPassword
			$formater->_writeForgotPassword($mailcontent, $rowUser->username, $rowUser->email);
		}
		else 
		{
			echo "Invalid email/user";
		}
		}
	}
	function penjelasanAction()
	{
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		
		$rowset = $tblCatalog->fetchRow("shortTitle='signup-indonesia' AND status=99");
		$rowsetCatalogAttribute = $rowset->findDependentRowsetCatalogAttribute();
		
		$this->view->description = $rowsetCatalogAttribute->findByAttributeGuid('fixedDescription')->value;
		$this->view->content = $rowsetCatalogAttribute->findByAttributeGuid('fixedContent')->value;
	}
	function loginAction()
	{
//		$this->preProcessSession();
		
		$returnTo = ($this->_getParam('returnTo'))? $this->_getParam('returnTo') : '';
		
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
		$this->view->returnTo = $returnTo;
	}
	
	/**	
	 * Login authentication
	 * @param username, password 
	 */
	function kloginAction()
	{
		$this->_helper->layout()->disableLayout();
		$request = $this->getRequest();
		$userName = ($request->getParam('u'))? $request->getParam('u') : '';
		$password = ($request->getParam('p'))? $request->getParam('p') : '';
		$remember = ($request->getParam('s'))? $request->getParam('s') : '';
		
		$response = array();
			
		$authMan = new Kutu_Auth_Manager($userName, $password);
		$authResult = $authMan->authenticate();
				
		$zendAuth = Zend_Auth::getInstance();
		if($zendAuth->hasIdentity())
		{
			if($authResult->isValid())
			{
				$r = $this->getRequest();
				$returnUrl = base64_decode($r->getParam('r'));
				if(!empty($returnUrl))
				{
					if(strpos($returnUrl,'?'))
					{
						$sAddition = '&';
					}
					else 
					{
						$sAddition = '?';
						
						Kutu_Lib_Formater::writeLog();
						
						if (isset($remember) && $remember == 'yes') {
						$hol = new Kutu_Core_Hol_Auth();
						$hol->user = $userName;
						$hol->user_pw = $password;
						$hol->save_login = $remember;
						$hol->login_saver();
						}
						/*
						$phpBB = new Kutu_Lib_Forum();
						$bvars = array(
							'username' => $userName,
							'password' => $password
						);
						$phpBB->user_login( $bvars );
						*/
						
						$response['success'] = true;
						$response['msg'] = 'Logging in';
						$response['message'] = "$returnUrl".$sAddition."PHPSESSID=".Zend_Session::getId();
					}
				}
				
			}
			else 
			{
				if($authResult->getCode() != -51)
				{
					// failure : clear database row from session
					Zend_Auth::getInstance()->clearIdentity();
				}
				$messages = $authResult->getMessages();
				$response['error'] = $messages[0];
				$response['success'] = false;
			}
		}
		else 
		{
			if ($authResult->getCode() == -2)
			{
				$r = $this->getRequest();
				$returnUrl = $r->getParam('r');
				
				$sessLog = new Zend_Session_Namespace("RELOGIN");
				$sessLog->reloginUser = $userName;
				
				$response['success'] = true;
				$response['msg'] = 'Re-Login';
				$response['message'] = KUTU_ROOT_URL."/identity/relogin/$returnUrl";
//				$this->_forward('relogin','account','identity',array('u'=>$userName,'p'=>$password,'s'=>$remember,'r'=>$returnUrl));
			}
			else 
			{
				$response['failure'] = true;
				$messages = $authResult->getMessages();
				$response['error'] = $messages[0];
			}
		}
		
		echo Zend_Json::encode($response);
	}
	function reloginAction()
	{
		$sessLog = new Zend_Session_Namespace("RELOGIN");
		
		$username = ($sessLog->reloginUser)? $sessLog->reloginUser : '';
		
		$this->view->username = $username;
		
		$dbAdapters = Zend_Registry::get ( 'dbAdapters' );
		$config = ($dbAdapters ['hol']);
		$config->query("DELETE FROM `session` WHERE `sessionData` LIKE '%$username%'");		
		
		setcookie("PHPSESSID", "", time()-3600);
		
		unset($sessLog->reloginUser);
				
		$r = $this->getRequest();
		$returnUrl = $r->getParam('returnTo');
		
		$this->view->returnTo = $returnUrl;
	}
	function logoutAction()
	{
		/*
		$phpBB = new Kutu_Lib_Forum();
		$phpBB->user_logout();
		*/
		
		Kutu_Lib_Formater::updateUserLog();
		
		Zend_Auth::getInstance()->clearIdentity();
		$returnUrl = base64_decode($this->_getParam('returnTo'));
        $this->_redirect($returnUrl); 
	}
	function feedbackAction()		
	{
		$this->_helper->layout->setLayout('layout-hukumonlineid-ext');
		
	}
	function sendFeedbackAction()
	{
		$this->_helper->layout->disableLayout();
		
		$request = $this->getRequest();

		$validator = new Zend_Validate_EmailAddress();
		
		if ($request->getParam('email') == '') {
			$error[] = '- Email harus diisi';
		}
		if (!$validator->isValid($request->getParam('email'))) {
			$error[] = '- Penulisan email salah!';
		}
		if ($request->getParam('feedback') == '') {
			$error[] = '- Masukkan anda?';
		}
		 
		if (isset($error)) {
			
			echo '<b>Error</b>: <br />'.implode('<br />', $error);
			
		} else {
		
		$formater = new Kutu_Core_Hol_User();
		
		$email = $this->_getParam('email');
		$feedback = $this->_getParam('feedback');
		
		// send Feedback
		$formater->sendFeedback($email,$feedback);
		}
	}
	function aturanPakaiAction()
	{
		$this->view->identity = 'Aturan-Pakai';
		$this->_helper->layout->setLayout('layout-newhukumonlineid-daftar');
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		$rowset = $tblCatalog->fetchRow("shortTitle='aturan-pakai' AND profileGuid='kutu_signup'");
		$rowsetCatalogAttribute = $rowset->findDependentRowsetCatalogAttribute();
		
		$this->view->title = $rowsetCatalogAttribute->findByAttributeGuid('fixedTitle')->value;
		$this->view->content = $rowsetCatalogAttribute->findByAttributeGuid('fixedContent')->value;
	}
	function paketAction()
	{
		$shortTitle = ($this->_getParam('title'))? $this->_getParam('title') : '';
		
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		$rowset = $tblCatalog->fetchRow("shortTitle='".$shortTitle."'");
		$rowsetCatalogAttribute = $rowset->findDependentRowsetCatalogAttribute();
		
		$this->view->title = $rowsetCatalogAttribute->findByAttributeGuid('fixedTitle')->value;
		$this->view->content = $rowsetCatalogAttribute->findByAttributeGuid('fixedContent')->value;
	}
	function personalSettingAction()
	{
		$this->_helper->layout->setLayout('layout-hukumonlineid-ps');
		
		$auth = Zend_Auth::getInstance();
		if (!$auth->hasIdentity())
		{
			$this->_forward('restricted','error','identity',array('type' => 'identity','num' => 101));			
		}
	}
	function profileAction()
	{
		$this->_helper->viewRenderer->setNoRender(TRUE);
		$this->_forward('profile','user','identity');
	}
//	function editprofileAction()
//	{
//		$this->_helper->layout->setLayout('layout-hukumonlineid-ps');
//		
//		$auth = Zend_Auth::getInstance();
//		if (!$auth->hasIdentity())
//		{
//			$this->_forward('restricted','error','identity',array('type' => 'identity','num' => 101));			
//		}
//		else
//		{
//			$guid = $auth->getIdentity()->guid;
//			if (isset($guid)) {
//				$tblUser = new Kutu_Core_Orm_Table_User();
//				$rowset = $tblUser->find($guid)->current();
//				/*
//				if ($rowset->packageId == 27)
//				{
//					$this->_forward('member_corporate_edit','account');
//				} 
//				elseif ($rowset->packageId == 26)
//				{
//					$this->_forward('member_individual_edit','account');
//				}
//				else 
//				{
//				*/
//					$this->_forward('member_edit','account','identity',array('guid'=>$guid));
//				//}
//			}
//		}
//	}
	function membereditAction()
	{
		$this->_helper->layout->setLayout('layout-hukumonlineid-ps');
		
		
		$g = $this->getRequest();
		$guid = $g->getParam('guid');
		$tblUser = new Kutu_Core_Orm_Table_User();
		$rowUser = $tblUser->find($guid)->current();
		$this->view->row = $rowUser;
		
		if ($g->isPost()) {
			
			$aData = $g->getParams();
			$aData['guid'] = $guid;
			
			try {
				$hol = new Kutu_Core_Hol_User();
				$rowUser = $hol->editprofile($aData);
				
				$this->view->row = $rowUser;
				$this->view->message = "Data has been successfully saved.";
			}
			catch (Zend_Exception $e)
			{
				$this->view->message = $e->getMessage();
			}
		}
	}
	function changeusernameAction()
	{
		$this->_helper->layout->setLayout('layout-hukumonlineid-ps');
		
		$auth = Zend_Auth::getInstance();
		if (!$auth->hasIdentity())
		{
			$this->_forward('restricted','error','identity',array('type' => 'identity','num' => 101));			
		}
		else
		{
			$guid = $auth->getIdentity()->guid;
			$tblUser = new Kutu_Core_Orm_Table_User();
			$rowUser = $tblUser->find($guid)->current();
			$this->view->row = $rowUser;
			
			$g = $this->getRequest();
			
			if ($g->isPost()) {
				
				$aData = $g->getParams();
				$aData['guid'] = $guid;
				
				try {
					$hol = new Kutu_Core_Hol_User();
					$rowUser = $hol->editprofile($aData);
					
					$this->view->row = $rowUser;
					$this->view->message = "Data has been successfully saved.";
				}
				catch (Zend_Exception $e)
				{
					$this->view->message = $e->getMessage();
				}
			}
			
		}
	}
	
	/*
	function changeemailAction()
	{
		$this->_helper->layout->setLayout('layout-hukumonlineid-ps');
		
		$auth = Zend_Auth::getInstance();
		if (!$auth->hasIdentity())
		{
			$this->_forward('restricted','error','identity',array('type' => 'identity','num' => 101));			
		}
		else
		{
			$guid = $auth->getIdentity()->guid;
			$tblUser = new Kutu_Core_Orm_Table_User();
			$rowUser = $tblUser->find($guid)->current();
			$this->view->row = $rowUser;
			
			$g = $this->getRequest();
			
			if ($g->isPost()) {
				
				$aData = $g->getParams();
				$aData['guid'] = $guid;
				
				try {
					$hol = new Kutu_Core_Hol_User();
					$rowUser = $hol->editprofile($aData);
					
					$this->view->row = $rowUser;
					$this->view->message = "Data has been successfully saved.";
				}
				catch (Zend_Exception $e)
				{
					$this->view->message = $e->getMessage();
				}
			}
		}
	}
	
	function changePasswordAction()
	{
		$this->_helper->layout->setLayout('layout-hukumonlineid-ps');
		
		$auth = Zend_Auth::getInstance();
		if (!$auth->hasIdentity())
		{
			$this->_forward('restricted','error','identity',array('type' => 'identity','num' => 101));			
		}
		else
		{
			$guid = $auth->getIdentity()->guid;
			$tblUser = new Kutu_Core_Orm_Table_User();
			$rowUser = $tblUser->find($guid)->current();
			$this->view->row = $rowUser;
			
			$g = $this->getRequest();
			
			if ($g->isPost()) {
				
				$aData = $g->getParams();
				
				$hol = new Kutu_Core_Hol_User();
				
				if($hol->changePassword($guid, $g->getParam('opasswd'), $g->getParam('newpasswd')))
				{
					$this->view->message = "Password was sucessfully changed.";
				}
				else
				{
					$this->view->message = "Old password was wrong. Please retry with correct password.";
				}
			}
			
		}
	}
	*/
	
	function signupAction()
	{
		$this->_helper->layout->setLayout('layout-newhukumonlineid-daftar');
		
		$this->view->identity = 'Daftar';
		
		$r = $this->getRequest();
		
		if ($r->isPost()) {
			
            $fullName = $r->getParam('fullname');
            $username = $r->getParam('username');
            $password = $r->getParam('password');
            $email = $r->getParam('email');
            $package = $r->getParam('aro_groups');

            $kopel = $this->generateKopel();

            $obj = new Kutu_Crypt_Password();
            $data = array(
                'kopel'		=> $kopel
                ,'username'	=> $username
                ,'password'	=> $obj->encryptPassword($password)
                ,'fullName'	=> $fullName
                ,'email'	=> $email
                ,'packageId'	=> $package
                ,'createdBy'	=> $username
            );

            $modelUser = new Kutu_Core_Orm_Table_User();
            $modelUser->insert($data);

            $this->updateKopel();

            $acl = new Kutu_Acl_Adapter_Local();
            //$acl->addUser($username,"Free");
            $acl->addUserToGroup($username, "Free");

            $formater = new Kutu_Core_Hol_User();

            $mailcontent = $formater->getMailContent('konfirmasi email gratis');
            $m = $formater->_writeConfirmFreeEmail($mailcontent,$fullName,$username,$password,base64_encode($kopel),$email,'gratis');

            $this->view->message = $m;
            
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
		
	}
	/*
	function signupAction()
	{
		$this->_helper->layout->setLayout('layout-hukumonlineid-daftar');
		
		$r = $this->getRequest();
		
		if ($r->isPost()) {
			
			$id				= ($r->getParam('id'))? $r->getParam('id') : '';
			$promotionCode	= ($r->getParam('promotionCode'))? $r->getParam('promotionCode') : '';
			$package		= ($r->getParam('paket'))? $r->getParam('paket') : '';
			$fullName		= ($r->getParam('fullName'))? $r->getParam('fullName') : '';
			$gender			= ($r->getParam('chkGender'))? $r->getParam('chkGender') : '';
			$month			= ($r->getParam('month'))? $r->getParam('month') : '';
			$day			= ($r->getParam('day'))? $r->getParam('day') : '';
			$year			= ($r->getParam('year'))? $r->getParam('year') : '';
			$education		= ($r->getParam('education'))? $r->getParam('education') : '';
			$expense		= ($r->getParam('expense'))? $r->getParam('expense') : '';
			$company		= ($r->getParam('company'))? $r->getParam('company') : '';
			$businessType	= ($r->getParam('businessType'))? $r->getParam('businessType') : '';
			$phone			= ($r->getParam('phone'))? $r->getParam('phone') : '';
			$fax			= ($r->getParam('fax'))? $r->getParam('fax') : '';
			$payment		= ($r->getParam('payment'))? $r->getParam('payment') : '';
			$email			= ($r->getParam('email'))? $r->getParam('email') : '';
			$newArtikel		= ($r->getParam('newArtikel'))? $r->getParam('newArtikel') : '';
			$newRegulation	= ($r->getParam('newRegulation'))? $r->getParam('newRegulation') : '';
			$newWRegulation	= ($r->getParam('newWeeklyRegulation'))? $r->getParam('newWeeklyRegulation') : '';
			$iscontact 		= ($r->getParam('iscontact'))? $r->getParam('iscontact') : '';
		
			$obj	 	= new Kutu_Crypt_Password();
			$formater	= new Kutu_Core_Hol_User();
			$aclMan 	= new Kutu_Acl_Adapter_Local();
			
			try {
				
				for ($x=1; $x <= $id; $x++) {
					$username = ($r->getParam('username'.$x))? $r->getParam('username'.$x) : '';
					$password = ($r->getParam('password'.$x))? $r->getParam('password'.$x) : '';
					
					$tblUser = new Kutu_Core_Orm_Table_User();
					Zend_Db_Table::getDefaultAdapter()->beginTransaction();
					$rowUser = $tblUser->fetchNew();
					
					$rowUser->username			= $username;
					$rowUser->password			= $obj->encryptPassword($password);
					$rowUser->fullName			= $fullName;
					$rowUser->gender			= ($gender == 1)? 'L' : 'P';
					$rowUser->birthday			= $year.'-'.$month.'-'.$day;
					$rowUser->indexCol			= $x;
					$rowUser->phone				= $phone;
					$rowUser->fax				= $fax;
					$rowUser->email				= $email;
					$rowUser->company			= $company;
					$rowUser->newArticle		= ($newArtikel == 1)? 'Y' : 'N';
					$rowUser->weeklyList		= ($newWRegulation == "1")? 'Y' : 'N';
					$rowUser->monthlyList		= ($newRegulation == 1)? 'Y' : 'N';
					$rowUser->isContact			= ($iscontact == $x)? 'Y' : 'N';
					$rowUser->packageId			= $package;
					$rowUser->promotionId		= $promotionCode;
					$rowUser->educationId		= $education;
					$rowUser->expenseId			= $expense;
					$rowUser->paymentId			= $payment;
					$rowUser->businessTypeId	= $businessType;
					
					$rowUser->save();
					Zend_Db_Table::getDefaultAdapter()->commit();
					
					$aclMan->addUser($username,'member_gratis');
				}
				
				switch ($package)
				{
					case 25:
							$mailcontent = $formater->getMailContent('konfirmasi email gratis');
							$m = $formater->_writeConfirmFreeEmail($mailcontent,$fullName,$r->getParam('username1'),$r->getParam('password1'),base64_encode(Kutu_Lib_Formater::get_user_id($r->getParam('username1'))),$email,'gratis');
						break;
					case 26:
							$disc = $formater->checkPromoValidation('Disc',$aclMan->getGroupIds('member_individual'),$promotionCode,$payment);
							$total = $formater->checkPromoValidation('Total',$aclMan->getGroupIds('member_individual'),$promotionCode,$payment);
							$mailcontent = $formater->getMailContent('konfirmasi-email-individual');
							$m = $formater->_writeConfirmIndividualEmail($mailcontent,$fullName,$r->getParam('username1'),$r->getParam('password1'),$payment,$disc,$total,base64_encode(Kutu_Lib_Formater::get_user_id($r->getParam('username1'))),$email);
						break;
					case 27:
							$disc = $formater->checkPromoValidation('Disc',$aclMan->getGroupIds('member_corporate'),$promotionCode,$payment);
							$total = $formater->checkPromoValidation('Total',$aclMan->getGroupIds('member_corporate'),$promotionCode,$payment);
							$mailcontent = $formater->getMailContent('konfirmasi-email-korporasi');
							$m = $formater->_writeConfirmCorporateEmail($mailcontent,$fullName,$company,$payment,$disc,$total,$r->getParam('username1'),base64_encode(Kutu_Lib_Formater::get_user_id($r->getParam('username1'))),$email);
						break;
				}
				
				
				$this->view->message = $m;
			}
			catch (Zend_Exception $e)
			{
				Zend_Db_Table::getDefaultAdapter()->rollBack();
				$this->view->message = $e->getMessage();
			}
		}
		
	}
	*/
    protected function generateKopel()
    {
    	$modelNumber = new Kutu_Core_Orm_Table_Number();
        $rowset = $modelNumber->fetchRow();
        $num = $rowset->user;
        $totdigit = 5;
        $num = strval($num);
        $jumdigit = strlen($num);
        $kopel = str_repeat("0",$totdigit-$jumdigit).$num;

        return $kopel;
    }
    protected function updateKopel()
    {
        $modelNumber = new Kutu_Core_Orm_Table_Number();
        $rowset = $modelNumber->fetchRow();
        $rowset->user = $rowset->user += 1;
        $rowset->save();
    }	
	
	/*
	function gratisAction()
	{
		$r = $this->getRequest();
		
		if($r->isPost())
		{
			$aData = $r->getParams();
			// set package manually
			$aData['packageId'] = 25;
			$hol = new Kutu_Core_Hol_User();
			try
			{
				$row = $hol->signup($aData);
				$this->_helper->layout->setLayout('layout-hukumonlineid-signup');
				$this->_helper->layout->setLayoutPath(array('layoutPath'=>KUTU_ROOT_DIR.'/application/modules/identity/views/layouts'));
				$this->_helper->viewRenderer->setScriptAction('signup-success');
			}
			catch (Exception $e)
			{
				print_r($e->getMessage());
				$this->_helper->layout->setLayout('layout-hukumonlineid-signup');
				$this->_helper->layout->setLayoutPath(array('layoutPath'=>KUTU_ROOT_DIR.'/application/modules/identity/views/layouts'));
				$this->_helper->viewRenderer->setScriptAction('signup-error');
			}
		}
	}
	function individualAction()
	{
	}
	
	function pictureAction()
	{
		$this->_helper->layout->setLayout('layout-hukumonlineid-ps');
		$this->_helper->layout->setLayoutPath(array('layoutPath'=>KUTU_ROOT_DIR.'/application/modules/identity/views/layouts'));
		$auth = Zend_Auth::getInstance();
		if (!$auth->hasIdentity())
		{
			$this->_forward('restricted','error','identity',array('type' => 'identity','num' => 101));			
		}
		else
		{
			$guid = $auth->getIdentity()->guid;
			$tblUser = new Kutu_Core_Orm_Table_User();
			$rowUser = $tblUser->find($guid)->current();
			$this->view->row = $rowUser;
			
			$g = $this->getRequest();
			
			if ($g->isPost()) {
				
				$aData = $g->getParams();
				
					$arraypictureformat = array("jpg", "jpeg", "gif");
					$sDir = KUTU_ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'images';
						
					if ($g->getParam('txt_erase') == 'on') {
						foreach ($arraypictureformat as $key => $val) {
							if (is_file($sDir."/".$guid.".".$val)) {
								unlink($sDir."/".$guid.".".$val);
								break;
							}
						}
					}
					
					$registry = Zend_Registry::getInstance();
					$files = $registry->get('files');
						
					if (isset($files['file_picture']))
					{
						$file = $files['file_picture'];
					}
					
					if ($files['file_picture']['error'] == 0 && $files['file_picture']['size'] > 0) {
						$file = $files['file_picture']['name'];
						$ext = explode(".",$file);
						$ext = strtolower(array_pop($ext));
						if (in_array($ext,$arraypictureformat)) {
							$image_size = getimagesize($files['file_picture']['tmp_name']);
							
							if ($image_size[0] > 200 || $image_size[1] > 250)
							{
								$this->view->message = 'Ukuran gambar melebihi batas maksimal. Proses pengunggahan batal!';
								
							}
							else 
							{
								foreach ($arraypictureformat as $key => $val)
								{
									if (is_file($sDir."/".$guid.".".$val)) {
										unlink($sDir."/".$guid.".".$val);
										break;
									}
								}
								
								if (is_uploaded_file($files['file_picture']['tmp_name'])) {
									@move_uploaded_file($files['file_picture']['tmp_name'], $sDir."/".$guid.".".$ext);
									@chmod($files['file_picture']['tmp_name'], $sDir."/".$guid.".".$ext, 0755);
								}
								
								$this->view->message = "Data has been successfully saved.";
							}
						}
						
						
					}
			}
			
		}
	}
	
	function saveAction()
	{
		$this->_helper->layout()->disableLayout();
		$id				= ($this->_getParam('id'))? $this->_getParam('id') : '';
		$promotionCode	= ($this->_getParam('promotionCode'))? $this->_getParam('promotionCode') : '';
		$package		= ($this->_getParam('paket'))? $this->_getParam('paket') : '';
		$fullName		= ($this->_getParam('fullName'))? $this->_getParam('fullName') : '';
		$gender			= ($this->_getParam('chkGender'))? $this->_getParam('chkGender') : '';
		$month			= ($this->_getParam('month'))? $this->_getParam('month') : '';
		$day			= ($this->_getParam('day'))? $this->_getParam('day') : '';
		$year			= ($this->_getParam('year'))? $this->_getParam('year') : '';
		$education		= ($this->_getParam('education'))? $this->_getParam('education') : '';
		$expense		= ($this->_getParam('expense'))? $this->_getParam('expense') : '';
		$company		= ($this->_getParam('company'))? $this->_getParam('company') : '';
		$businessType	= ($this->_getParam('businessType'))? $this->_getParam('businessType') : '';
		$phone			= ($this->_getParam('phone'))? $this->_getParam('phone') : '';
		$fax			= ($this->_getParam('fax'))? $this->_getParam('fax') : '';
		$payment		= ($this->_getParam('payment'))? $this->_getParam('payment') : '';
		$email			= ($this->_getParam('email'))? $this->_getParam('email') : '';
		$newArtikel		= ($this->_getParam('newArtikel'))? $this->_getParam('newArtikel') : '';
		$newRegulation	= ($this->_getParam('newRegulation'))? $this->_getParam('newRegulation') : '';
		$newWRegulation	= ($this->_getParam('newWeeklyRegulation'))? $this->_getParam('newWeeklyRegulation') : '';
		$iscontact 		= ($this->_getParam('iscontact'))? $this->_getParam('iscontact') : '';
		
		$obj	 	= new Kutu_Crypt_Password();
		$formater	= new Kutu_Lib_Formater();
		$aclMan 	= new Kutu_Acl_Adapter_Local();
		
		// check email		
		$this->checkUserEmail($email);
		
		for ($n=1; $n < $id; $n++)
		{
			$usernamex = ($this->_getParam('username'.$n))? $this->_getParam('username'.$n) : '';
			if (!empty($username))
			{
				// check user
				$this->checkUserExist($usernamex);		
			}
		}
		
		for ($x=1; $x < $id; $x++) {
			$username = ($this->_getParam('username'.$x))? $this->_getParam('username'.$x) : '';
			$password = ($this->_getParam('password'.$x))? $this->_getParam('password'.$x) : '';
			
			$tblUser = new Kutu_Core_Orm_Table_User();
			$rowUser = $tblUser->fetchNew();
			
			$rowUser->username			= $username;
			$rowUser->password			= $obj->encryptPassword($password);
			$rowUser->fullName			= $fullName;
			$rowUser->gender			= ($gender == 1)? 'L' : 'P';
			$rowUser->birthday			= $year.'-'.$month.'-'.$day;
			$rowUser->indexCol			= $x;
			$rowUser->phone				= $phone;
			$rowUser->fax				= $fax;
			$rowUser->email				= $email;
			$rowUser->company			= $company;
			$rowUser->newArticle		= ($newArtikel == 1)? 'Y' : 'N';
			$rowUser->weeklyList		= ($newWRegulation == "1")? 'Y' : 'N';
			$rowUser->monthlyList		= ($newRegulation == 1)? 'Y' : 'N';
			$rowUser->isContact			= ($iscontact == $x)? 'Y' : 'N';
			$rowUser->packageId			= $package;
			$rowUser->promotionId		= $promotionCode;
			$rowUser->educationId		= $education;
			$rowUser->expenseId			= $expense;
			$rowUser->paymentId			= $payment;
			$rowUser->businessTypeId	= $businessType;
			
			$rowUser->save();
			
			$aclMan->addUser($username,'member_gratis');
		}
		
		switch ($package)
		{
			case 25:
					$mailcontent = $formater->getMailContent('konfirmasi email gratis');
					$formater->_writeConfirmFreeEmail($mailcontent,$fullName,$this->_getParam('username1'),$this->_getParam('password1'),$obj->encryptPassword($formater->get_user_id($this->_getParam('username1'))),$email,'gratis');
				break;
			case 26:
					$disc = $formater->checkPromoValidation('Disc',$aclMan->getGroupIds('member_individual'),$promotionCode,$payment);
					$total = $formater->checkPromoValidation('Total',$aclMan->getGroupIds('member_individual'),$promotionCode,$payment);
					$mailcontent = $formater->getMailContent('konfirmasi-email-individual');
					$formater->_writeConfirmIndividualEmail($mailcontent,$fullName,$this->_getParam('username1'),$this->_getParam('password1'),$payment,$disc,$total,$obj->encryptPassword($formater->get_user_id($this->_getParam('username1'))),$email);
				break;
			case 27:
					$disc = $formater->checkPromoValidation('Disc',$aclMan->getGroupIds('member_corporate'),$promotionCode,$payment);
					$total = $formater->checkPromoValidation('Total',$aclMan->getGroupIds('member_corporate'),$promotionCode,$payment);
					$mailcontent = $formater->getMailContent('konfirmasi-email-korporasi');
					$formater->_writeConfirmCorporateEmail($mailcontent,$company,$payment,$disc,$total,$this->_getParam('username1'),$obj->encryptPassword($formater->get_user_id($this->_getParam('username1'))),$email);
				break;
		}
	}
	*/
	
	function redirectUrlAction()
	{
		$this->_helper->layout()->disableLayout();
	}
	
	/*
	private function checkUserExist($username)
	{
		$tbluser = new Kutu_Core_Orm_Table_User();
		$where = $tbluser->getAdapter()->quoteInto("username=?",$username);
		$rowset = $tbluser->fetchRow($where);
		if ($rowset)
		{
			$response['failure'] = true;
			$response['message'] = "$username already exist, please another username";
			echo Zend_Json::encode($response);
			exit();
		}
	}
	private function checkUserEmail($email)
	{
		$tbluser = new Kutu_Core_Orm_Table_User();
		$where = $tbluser->getAdapter()->quoteInto("email=?",$email);
		$rowset = $tbluser->fetchRow($where);
		if ($rowset) 
		{
			$response['failure'] = true;
			$response['message'] = "Your email $email is not available";
			echo Zend_Json::encode($response);
			exit();
		}
	}
	function checkusernameAction()
	{
		$id = $this->_getParam('id');
		$username = $this->_getParam('username'.$id);
		$tbluser = new Kutu_Core_Orm_Table_User();
		$where = $tbluser->getAdapter()->quoteInto("username=?",$username);
		$rowset = $tbluser->fetchRow($where);
		if(count($rowset)>0)
			echo "0";
		else
			echo "1";
		die();
	}
	*/
	
    function checkusernameAction()
    {
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $username = ($this->_getParam('username'))? $this->_getParam('username') : '';

        $modelUser = new Kutu_Core_Orm_Table_User();
        $rowset = $modelUser->fetchRow("username='$username'");

        if($rowset)
            $valid = 'false';
        else
            $valid = 'true';

        echo $valid;
        die();
    }
	function checkemailAction()
	{
		$email = $this->_getParam('email');
		$tbluser = new Kutu_Core_Orm_Table_User();
		$where = $tbluser->getAdapter()->quoteInto("email=?",$email);
		$rowset = $tbluser->fetchRow($where);
		if ($rowset) 
			echo 'false';
		else 
			echo 'true';
		
		die();	
	}
	function getMeUsernameAction()
	{
		$this->_helper->layout()->disableLayout();
		$request = $this->getRequest();
		$uname = ($request->getParam('username'))? $request->getParam('username') : '';
		
		$response = array();
		
		if ($uname == "undefined") {
			
			$response['error'] = '2';
			$response['err2'] = 'Username is Empty';
			
		} elseif (strlen($uname) < 6) {
			
			$response['error'] = '1';
			$response['err1'] = 'Sorry, your username must be between 6 and 30<br>characters long.';
			
		} else {	
			
			$tableUser = new Kutu_Core_Orm_Table_User();
			$rowUser = $tableUser->fetchRow("username='".$uname."'");
	
			if (!empty($rowUser->username)) {
				
				$response['error'] = '3';
				$response['err3'] = '<i><b>'.$uname.'</b></i> is not available';
				
			} else {
				
				$response['success'] = 'true';
				$response['data'] = '<i><b>'.$uname.'</b></i> is available';
				
			}		
		}
		
		echo Zend_Json::encode($response);
		
	}
	function getMeEmailAction()
	{
		$this->_helper->layout()->disableLayout();
		$request = $this->getRequest();
		$email = ($request->getParam('email'))? $request->getParam('email') : '';
		$response = array();
		if ($email == "undefined") {
			$response['failure'] = true;
			$response['message'] = 'Email is Empty';
		} else {	
			$tableUser = new Kutu_Core_Orm_Table_User();
			$rowUser = $tableUser->fetchRow("email='".$email."'");
			if (!empty($rowUser->email)) {
				$response['failure'] = true;
				$response['message'] = '<i><b>'.$email.'</b></i> is not available';
			} else {
				$response['success'] = true;
				$response['message'] = '<i><b>'.$email.'</b></i> is available';
			}		
		}
		echo Zend_Json::encode($response);
	}
	function preProcessSession()
	{
		$zendAuth = Zend_Auth::getInstance();
		if($zendAuth->hasIdentity())
		{
			$r = $this->getRequest();
			$returnUrl = base64_decode($r->getParam('returnTo'));
			
			if(!empty($returnUrl))
			{
				if(strpos($returnUrl,'?'))
					$sAddition = '&';
				else 
					$sAddition = '?';
					header("location: $returnUrl".$sAddition."PHPSESSID=".Zend_Session::getId());
			}
			else 
			{
				echo "AccountController:PreProcessSession => Anda sudah login kok";
			}
		}
		else 
		{
			Zend_Session::rememberMe(86000);
			Zend_Session::regenerateId();
		}
	}
}

?>