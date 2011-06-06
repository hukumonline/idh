<?php
class Kutu_Core_Hol_User
{
	public function changePassword($userGuid, $oldPassword, $newPassword)
	{
		$tblUser = new Kutu_Core_Orm_Table_User();
		$row = $tblUser->find($userGuid)->current();
		
		$obj = new Kutu_Crypt_Password();
		if($obj->matchPassword($oldPassword, $row->password))
		{
			$row->password = $obj->encryptPassword($newPassword);
			$row->save();
			return true;
		}
		else
			return false;
	}
	
	/**
	 * sendFeedback
	 * @return JSON
	 */
	function sendFeedback($email,$feedback)
	{
		$mailAttempt = $this->add_mail($email,"nihki@hukumonline.com","Nihki Prihadi","Feedback User",$feedback);
		
		// try to save mail before send
		if ($mailAttempt)
		{
			$sendAttempt = $this->send_mail();
			if ($sendAttempt)
			{
				// send confirm to client
				echo "Email has been sent to support@hukumonline.com";
			}
			else 
			{
				echo "Error send mail!";
			}
		}
		else 
		{
			echo "Error saved mail!";
		}
		
	}
	
	/**
	 * _writeConfirmIndividualEmail
	 * @return JSON
	 */
	function _writeForgotPassword($mailcontent,$username,$email)
	{
		$obj = new Kutu_Crypt_Password();
		$generateGuid = new Kutu_Core_Guid();
		
		$newPassword = $generateGuid->generateGuid();
		
		$mailcontent = str_replace('$fullname',$username,$mailcontent);
		$mailcontent = str_replace('$password',$newPassword,$mailcontent);
		
		$mail_body = $mailcontent;
		
		// parse ini_file
		$config = new Zend_Config_Ini(KUTU_ROOT_DIR.'/application/configs/mail.ini', 'mail');
		
		$mailAttempt = $this->add_mail($config->mail->sender->support->email, $email, $username, 'Bantuan Hukumonline',$mail_body);
		
		// try to save mail before send
		if ($mailAttempt)
		{
			$sendAttempt = $this->send_mail();
			
			if ($sendAttempt)
			{
				// update user password
				$tblUser = new Kutu_Core_Orm_Table_User();
				$tblUser->update(array('password' => $obj->encryptPassword($newPassword)),"username='".$username."'");
				// send confirm to client
				echo "Please check your email at $email!";
			}
			else 
			{
				echo "Error saving mail DB!";
			}
		}
		else 
		{
			echo "Email not provided!";
		}
		
	}
	
	/**
	 * getMailContent
	 */
	function getMailContent($title)
	{
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		$where = $tblCatalog->getAdapter()->quoteInto("shortTitle=?",$title);
		$rowset = $tblCatalog->fetchRow($where);
		$rowsetCatalogAttribute = $rowset->findDependentRowsetCatalogAttribute();
		$content = $rowsetCatalogAttribute->findByAttributeGuid('fixedContent')->value;
		
		return $content;
	}
	
	/**
	 * checkPromoValidation : Individual & Korporasi
	 * @return disc :: Total
	 */
	function checkPromoValidation($whatPromo,$package,$promotionId='',$payment=0)
	{
		$tblPackage = new Kutu_Core_Orm_Table_Package();
		$rowPackage = $tblPackage->fetchRow("packageId=$package");
		$periode = $rowPackage->charge * $payment;
		
		$tblPromosi = new Kutu_Core_Orm_Table_Promosi();
		$rowPromo = $tblPromosi->find($promotionId)->current();
		
		// check promotionID if exist then dischard query
		if (isset($rowPromo)) {
			
			if ($payment == 6) {
				$disc = $rowPromo->discount + 5;
			} elseif ($payment == 12) {
				$disc = $rowPromo->discount + 10;
			} else {
				$disc = $rowPromo->discount;
			}
			
			$total = ($periode - ($disc/100 * $periode)) * 1.1;
			
		} else {
			
			$getPromo = $tblPromosi->fetchRow("periodeStart <= '".date("Y-m-d")."' AND periodEnd >= '".date("Y-m-d")."' AND monthlySubscriber=".$payment."");
			
			if (!empty($getPromo))
			{
				if ($payment == 6) {
					$disc = $getPromo->discount + 5;
				} elseif ($payment == 12) {
					$disc = $getPromo->discount + 10;
				} else {
					$disc = $getPromo->discount;
				}
				
				$total = ($periode - ($disc/100 * $periode)) * 1.1;
				
			} else { 
				
				if ($payment == 6) {
					$disc = 5;
				} elseif ($payment == 12) {
					$disc = 10;
				} else {
					$disc = 0;
				}
				
				$total = ($periode - ($disc/100 * $periode)) * 1.1;
				
			}
		}
		
		switch ($whatPromo)
		{
			case 'Disc':
				return $disc;
			break;
			case 'Total':
				return $total;
			break;
		}
	}
	
	/**
	 * _writeInvoice : Individual & Korporasi
	 * @return 
	 */
	function _writeInvoice($memberId, $totalPromo, $discPromo, $payment, $access='')
	{
		$aclMan	= new Kutu_Acl_Adapter_Local();
		
		$tblInvoice = new Kutu_Core_Orm_Table_Invoice();
		$where = $tblInvoice->getAdapter()->quoteInto("uid=?",$memberId);
		$rowInvoice = $tblInvoice->fetchAll($where);
		if (count($rowInvoice) <= 0)
		{
			$rowInvoice = $tblInvoice->fetchNew();
			$rowInvoice->uid = $memberId;
			$rowInvoice->price = $totalPromo;
			$rowInvoice->discount = $discPromo;
			$rowInvoice->invoiceOutDate = date("Y-m-d");
			$rowInvoice->invoiceConfirmDate = "0000-00-00";
			
			$temptime = time();
			$temptime = Kutu_Lib_Formater::DateAdd('d',5,$temptime);
			
			$rowInvoice->expirationDate = strftime('%Y-%m-%d',$temptime);
			
			$tblUser = new Kutu_Core_Orm_Table_User();
			$rowUser = $tblUser->fetchRow("kopel=".$memberId);
			// add user to gacl
			$aclMan->addUser($rowUser->username,'member_gratis');
				
			if (empty($access))
			{
				$rowInvoice->save();
			}
			else 
			{
				$result = $rowInvoice->save();
				
				if ($result)
				{
					$response['success'] = true;
				}
				else 
				{
					$response['failure'] = true;
				}
				
				echo Zend_Json::encode($response);
			}
		}
		else 
		{
			if (!empty($access))
			{
				$response['success'] = true;
				echo Zend_Json::encode($response);
			}
		}
	}
	
	/**
	 * _writeConfirmFreeEmail
	 * @return JSON
	 */
	function _writeConfirmFreeEmail($mailcontent, $fullname, $username, $password, $guid, $email, $package='')
	{
		$obj 			= new Kutu_Crypt_Password();
		$aclMan 		= new Kutu_Acl_Adapter_Local();
		
		$mailcontent 	= str_replace('$fullname',$fullname,$mailcontent);
		$mailcontent 	= str_replace('$username',$username,$mailcontent);
		$mailcontent 	= str_replace('$password',$password,$mailcontent);
		$mailcontent 	= str_replace('$guid',$guid,$mailcontent);
		$mailcontent 	= str_replace('$package',$package,$mailcontent);
		
		$mail_body 		= $mailcontent;
		
		// parse ini_file
		$config = new Zend_Config_Ini(KUTU_ROOT_DIR.'/application/configs/mail.ini', 'mail');
		
		$mailAttempt = $this->add_mail($config->mail->sender->support->email,$email,$username,$config->mail->sender->support->name,$mail_body);		
		
		// try to save mail before send
		if ($mailAttempt)			
		{
			$sendAttempt = $this->send_mail();
			if ($sendAttempt)
			{
				
				$message =  "Please check your email at $email!";
				
				// update user
				$tblUser = new Kutu_Core_Orm_Table_User();
				$rowUser = $tblUser->find($obj->decryptPassword($guid))->current();
				if ($rowUser)
				{
					$rowUser->isEmailSent = 'Y';
				
					$rowUser->save();
				}
					
			}
			else 
			{
				$message =  "Error send mail but register user successfully!<br>Please contact our customer service for more information";
			}
		}
		else 
		{
			$message =  "Error saving mail!";
		}
		
		return $message;
	}
	
	/**
	 * _writeConfirmIndividualEmail
	 * @return JSON
	 */
	function _writeConfirmIndividualEmail($mailcontent, $fullname, $username, $password, $payment, $disc, $total, $guid, $email)
	{
		$obj 			= new Kutu_Crypt_Password();
		
		$mailcontent 	= str_replace('$fullname',$fullname,$mailcontent);
		$mailcontent 	= str_replace('$username',$username,$mailcontent);
		$mailcontent 	= str_replace('$password',$password,$mailcontent);
		$mailcontent 	= str_replace('$disc',$disc,$mailcontent);
		$mailcontent 	= str_replace('$timeline',$payment,$mailcontent);
		$mailcontent 	= str_replace('$price',number_format($total),$mailcontent);
		$mailcontent 	= str_replace('$guid',$guid,$mailcontent);
		
		$mail_body 		= $mailcontent;
		
		// parse ini_file
		$config = new Zend_Config_Ini(KUTU_ROOT_DIR.'/application/configs/mail.ini', 'mail');
		
		$mailAttempt = $this->add_mail($config->mail->sender->support->email,$email,$username,$config->mail->sender->support->name,$mail_body);		
		
		// try to save mail before send
		if ($mailAttempt)			
		{
			$sendAttempt = $this->send_mail();
			if ($sendAttempt)
			{
				
				$message =  "Please check your email at $email!";
				
				// update user
				$tblUser = new Kutu_Core_Orm_Table_User();
				$rowUser = $tblUser->find($obj->decryptPassword($guid))->current();
				if ($rowUser)
				{
					$rowUser->isEmailSent = 'Y';
				
					$rowUser->save();
				}
					
			}
			else 
			{
				$message =  "Error send mail but register user successfully!<br>Please contact our customer service for more information";
			}
		}
		else 
		{
			$message =  "Error saving mail!";
		}
		
		return $message;
	}
	
	/**
	 * _writeConfirmCorporateEmail
	 * @return JSON
	 */
	function _writeConfirmCorporateEmail($mailcontent, $fullname, $company, $payment, $disc, $total, $username, $guid, $email)
	{
		$obj 			= new Kutu_Crypt_Password();
		
		$mailcontent 	= str_replace('$fullname',$fullname,$mailcontent);
		$mailcontent 	= str_replace('$company',$company,$mailcontent);
		$mailcontent 	= str_replace('$timeline',$payment,$mailcontent);
		$mailcontent 	= str_replace('$disc',$disc,$mailcontent);
		$mailcontent 	= str_replace('$price',number_format($total),$mailcontent);
		$mailcontent 	= str_replace('$username1',$username,$mailcontent);
		$mailcontent 	= str_replace('$guid',$guid,$mailcontent);
		
		// table User
		$tblUser = new Kutu_Core_Orm_Table_User();
		$where = $tblUser->getAdapter()->quoteInto('company=?',$company);
		$rowUser = $tblUser->fetchAll($where,'username ASC');
		
		$tag = '<table>';
		$tag .= '<tr><td><b>Username</b></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td><b>Password</b></td></tr>';
		
		foreach ($rowUser as $rowsetUser)
		{
			$tag .= '<tr><td>'.$rowsetUser->username.'</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>'.$obj->decryptPassword($rowsetUser->password).'</td></tr>';					
		}
		
		$tag .= '</table>';
		
		$mailcontent = str_replace('$tag',$tag,$mailcontent);
		
		$mail_body = $mailcontent;
		
		// parse ini_file
		$config = new Zend_Config_Ini(KUTU_ROOT_DIR.'/application/configs/mail.ini', 'mail');
		
		$mailAttempt = $this->add_mail($config->mail->sender->support->email,$email,$username,$config->mail->sender->support->name,$mail_body);		
		
		// try to save mail before send
		if ($mailAttempt)			
		{
			$sendAttempt = $this->send_mail();
			if ($sendAttempt)
			{
				
				$message =  "Please check your email at $email!";
				
				// update user
				$rowUser = $tblUser->find($obj->decryptPassword($guid))->current();
				if ($rowUser)
				{
					$rowUser->isEmailSent = 'Y';
				
					$rowUser->save();
				}
					
			}
			else 
			{
				$message =  "Error send mail but register user successfully!<br>Please contact our customer service for more information";
			}
		}
		else 
		{
			$message =  "Error saving mail!";
		}
		
		return $message;
	}
	
	function add_mail($sender,$recepientMail,$recepientName,$subject,$body)
	{
		$data=array('sender'        => $sender,
					'recepientMail' => $recepientMail,
					'recepientName' => $recepientName,
					'subject'       => $subject,
					'body'          => $body,
					'ContentType'	=> 'text/html'
					);
					
		$newsletter = new Kutu_Lib_Newsletter();
		
		$add = $newsletter->addMail($data);
		
		if ($add===false) return $newsletter->errorMsg;
	}
	
	function send_mail()
	{
		require_once(KUTU_ROOT_DIR.'/library/Kutu/Lib/class.phpmailer.php');
		// set all attribute
		// ------------------------------- LOAD FROM CONFIG.ini
		$config = new Zend_Config_Ini(KUTU_ROOT_DIR.'/application/configs/mail.ini', 'mail');
		$data=array('method'   => $config->mail->method,
					'From'     => $config->mail->sender->support->email,
					'FromName' => $config->mail->sender->support->name,
					'Host'     => $config->mail->host,
					'SMTPAuth' => $config->mail->auth,
					'Username' => $config->mail->username,
					'Password' => $config->mail->password
					);
		
		$newsletter = new Kutu_Lib_Newsletter();

		return $newsletter->Sendmail();
	}
	public function signup($aData)
	{
		$row = $this->save($aData);
		
		//Must also assign assign user as group:member_free
		$acl = new Kutu_Acl_Adapter_Local();
		$acl->addUserToGroup($row->username,"member_gratis");
		
		if ($row->packageId == 27) {
			
		}
		elseif($row->packageId == 26)
		{
			
		}
		else
		{
			$mailcontent = $this->getMailContent('konfirmasi email gratis');
			$this->_writeConfirmFreeEmail($mailcontent, $row->fullName, $row->username, $aData['password'], $row->guid, $row->email, 'gratis');
		}
	}
	public function editprofile($aData) {
		return $this->save($aData);
	}
    public function save($aData)
    {
        $guid = $aData['kopel'];

        //if not empty, there are 2 possibilities
        $tblUser = new Kutu_Core_Orm_Table_User();
        $row = $tblUser->fetchRow("kopel='$guid'");

        if(isset($aData['email']))
                $row->email = $aData['email'];
        if(isset($aData['fullName']))
                $row->fullName = $aData['fullName'];
        if(isset($aData['chkGender']))
                $row->gender = ($aData['chkGender'] == 1)? 'L' : 'P';
        if(isset($aData['year']))
                $row->birthday = $aData['year'].'-'.$aData['month'].'-'.$aData['day'];
        if(isset($aData['education']))
                $row->educationId = $aData['education'];
        if(isset($aData['expense']))
                $row->expenseId = $aData['expense'];
        if(isset($aData['company']))
                $row->company = $aData['company'];
        if(isset($aData['businessType']))
                $row->businessTypeId = $aData['businessType'];
        if(isset($aData['phone']))
                $row->phone = $aData['phone'];
        if(isset($aData['fax']))
                $row->phone = $aData['fax'];
        if(isset($aData['packageId']))
                $row->packageId = $aData['packageId'];
        if(isset($aData['newArticle']) && ($aData['newArticle'] == 1)) {
                $row->newArticle = 'Y';
        }
        else
        {
            if(!isset($aData['email']) && !isset($aData['username']))
                    $row->newArticle = 'N';
        }
        if(isset($aData['newRegulation']) && ($aData['newRegulation'] == 1)) {
            $row->monthlyList = 'Y';
        }
        else
        {
            if(!isset($aData['email']) && !isset($aData['username']))
                    $row->monthlyList = 'N';
        }
        if(isset($aData['newWeeklyRegulation']) && ($aData['newWeeklyRegulation'] == 1)) {
            $row->weeklyList = 'Y';
        }
        else
        {
            if(!isset($aData['email']) && !isset($aData['username']))
                    $row->weeklyList = 'N';
        }


        $row->save();

        return $row;
    }
	/*
	public function save($aData)
	{
		$gman = new Kutu_Core_Guid();
		$guid = (isset($aData['guid']) && !empty($aData['guid']))? $aData['guid'] : $gman->generateGuid();
		
		//if not empty, there are 2 possibilities
		$tblUser = new Kutu_Core_Orm_Table_User();
		$row = $tblUser->fetchRow("guid='$guid'");
		
		if(empty($row)) {
			if(empty($aData['username']))
				throw new Zend_Exception('Username can not be EMPTY!');
			if(empty($aData['password']))
				throw new Zend_Exception('Password can not be EMPTY!');
				
			$row = $tblUser->createRow();
			
			if(isset($aData['password']) && !empty($aData['password']))
			{
				$password = $aData['password'];
				$crypt = new Kutu_Crypt_Password();
				$password = $crypt->encryptPassword($password);
				
				$row->password = $password;
			}
		}

		if(isset($aData['username']) && !empty($aData['username']))
		{
			//check if username was already taken
			$username = $aData['username'];
			$tblUser = new Kutu_Core_Orm_Table_User();
			$rowUsername = $tblUser->fetchRow("username='$username'");
			if($rowUsername)
			{
				throw new Zend_Exception('Username exists');
			}
				
			$row->username = $aData['username'];
		}
			
		if(isset($aData['email']))
			$row->email = $aData['email'];
		if(isset($aData['fullName']))
			$row->fullName = $aData['fullName'];
		if(isset($aData['chkGender']))
			$row->gender = ($aData['chkGender'] == 1)? 'L' : 'P';
		if(isset($aData['year']))
			$row->birthday = $aData['year'].'-'.$aData['month'].'-'.$aData['day'];
		if(isset($aData['education']))
			$row->educationId = $aData['education'];
		if(isset($aData['expense']))
			$row->expenseId = $aData['expense'];
		if(isset($aData['company']))
			$row->company = $aData['company'];
		if(isset($aData['businessType']))
			$row->businessTypeId = $aData['businessType'];
		if(isset($aData['phone']))
			$row->phone = $aData['phone'];
		if(isset($aData['fax']))
			$row->phone = $aData['fax'];
		if(isset($aData['packageId']))
			$row->packageId = $aData['packageId'];
		if(isset($aData['newArtikel']) && ($aData['newArtikel'] == 1)) {	
			$row->newArticle = 'Y';
		}
		else
		{
			if(!isset($aData['email']) && !isset($aData['username']))
				$row->newArticle = 'N';
		}
		if(isset($aData['newRegulation']) && ($aData['newRegulation'] == 1)) {
			$row->monthlyList = 'Y';
		}
		else
		{
			if(!isset($aData['email']) && !isset($aData['username']))
				$row->monthlyList = 'N';
		}
		if(isset($aData['newWeeklyRegulation']) && ($aData['newWeeklyRegulation'] == 1)) {
			$row->weeklyList = 'Y';
		}
		else
		{
			if(!isset($aData['email']) && !isset($aData['username']))
				$row->weeklyList = 'N';
		}
			
			
		$row->save();
			
		return $row;
	}
	*/
}
?>