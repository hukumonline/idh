<?php
class Account
{
	function lastLogin()
	{
		if (empty($_POST['kopel'])) $this->failAccess("No user specified");
		
		$tblUserAccessLog = new App_Model_Db_Table_Log();
		$rowUserAccessLog = $tblUserAccessLog->fetchRow("user_id='".$_POST['kopel']."' AND NOT (lastlogin='0000-00-00 00:00:00' or isnull(lastlogin))",'user_access_log_id DESC');
		
		if (isset($rowUserAccessLog)) 
			echo date('l F jS, Y \a\t g:ia',strtotime($rowUserAccessLog->lastlogin)). ' from '.$rowUserAccessLog->user_ip;
		else
			echo '-';
	}
	
	/*
	function selectUserBusiness()
	{
        $tblBusiness = new App_Model_Db_Table_Business();
        $row = $tblBusiness->fetchAll();

        $businessType = "<select name=\"businessType\" id=\"businessType\">\n";
        if ($_POST['businessTypeId']) {
            $rowBusinessType = $tblBusiness->find($_POST['businessTypeId'])->current();
            $businessType .= "<option value='$rowBusinessType->businessTypeId' selected>$rowBusinessType->description</option>";
            $businessType .= "<option value=''>Choose:</option>";
        } else {
            $businessType .= "<option value='' selected>Choose:</option>";
        }
        foreach ($row as $rowset) {
            if (($_POST['businessTypeId']) and ($rowset->businessTypeId == $rowBusinessType->businessTypeId)) {
                continue;
            } else {
                $businessType .= "<option value='$rowset->businessTypeId'>$rowset->description</option>";
            }
        }
        $businessType .= "</select>\n\n";
        
        echo $businessType;
	}
	
	function selectUserExpense()
	{
        $tblExpense = new App_Model_Db_Table_Expense();
        $row = $tblExpense->fetchAll();
        
        $expense = "<select name=\"expense\" id=\"expense\">\n";
        if ($_POST['exp']) {
            $rowExpense = $tblExpense->find($_POST['exp'])->current();
            $expense .= "<option value='$rowExpense->expenseId' selected>$rowExpense->description</option>";
            $expense .= "<option value=''>Choose:</option>";
        } else {
            $expense .= "<option value='' selected>Choose:</option>";
        }
        foreach ($row as $rowset) {
            if (($_POST['exp']) and ($rowset->expenseId == $rowExpense->expenseId)) {
                continue;
            } else {
                $expense .= "<option value='$rowset->expenseId'>$rowset->description</option>";
            }
        }
        $expense .= "</select>\n\n";
        echo $expense;
	}
	
	function selectUserEducation()
	{
        $tblEducation = new App_Model_Db_Table_Education();
        $row = $tblEducation->fetchAll();
        
        $education = "<select name=\"education\" id=\"education\">\n";
        if ($_POST['edu']) {
            $rowEducation = $tblEducation->find($_POST['edu'])->current();
            $education .= "<option value='$rowEducation->educationId' selected>$rowEducation->description</option>";
            $education .= "<option value =''>Choose:</option>";
        } else
        {
            $education .= "<option value ='' selected>Choose:</option>";
        }

        foreach ($row as $rowset) {
            if (($_POST['edu']) and ($rowset->educationId == $rowEducation->educationId)) {
                continue;
            } else {
                $education .= "<option value='$rowset->educationId'>$rowset->description</option>";
            }
        }

        $education .= "</select>\n\n";
        echo $education;
	}
	
	function selectState()
	{
        $tblProvince = new App_Model_Db_Table_State();
        $row = $tblProvince->fetchAll();

        $select_province = "<select name=\"province\" id=\"province\">\n";
        if ($_POST['state']) {
            $rowProvince = $tblProvince->find($_POST['state'])->current();
            $select_province .= "<option value='$rowProvince->pid' selected>$rowProvince->pname</option>";
            $select_province .= "<option value =''>Choose:</option>";
        } else {
            $select_province .= "<option value ='' selected>Choose:</option>";
        }

        foreach ($row as $rowset) {
            if (($_POST['state']) and ($rowset->pid == $rowProvince->pid)) {
                continue;
            } else {
                $select_province .= "<option value='$rowset->pid'>$rowset->pname</option>";
            }
        }

        $select_province .= "</select>\n\n";
        echo $select_province;
	}
	*/
	function migrationUser()
	{
		$data = $this->transformMigrationUser($_POST);
		
		/*
		echo '<pre>';
		print_r($data);
		echo '</pre>';
		*
		*/
		
		$modelUser = new Kutu_Core_Orm_Table_User();
		$rowUser = $modelUser->fetchRow("username='".$_POST['username']."'");
		if (!$rowUser) $modelUser->insert($data);
		
		$this->updateKopel();
		
		$groupName = $this->getGroupName($_POST['packageId']);
		
		$acl = new Kutu_Acl_Adapter_Local();
		$acl->addUser($_POST['username'],$groupName);
	}
	function transformMigrationUser($value)
	{
		if (($value["birthday"] == "1970-01-01") || ($value["birthday"] == ""))
		{
			$birthday = "0000-00-00";
		}
		else
		{
			$birthday = $value["birthday"];
		}
		
		$groupName = $this->getGroupName($_POST['packageId']);
		
		$acl = new Kutu_Acl_Adapter_Local();
		$groupId = $acl->getGroupIds($groupName);
		
		
		$data = array(
			 'kopel'			=> $this->generateKopel()
			,'username'			=> $value['username']
			,'password'			=> $value['password']
			,'fullName'			=> ($value['fullName'])? $value['fullName'] : ''
			,'birthday'			=> $birthday
			,'phone'			=> ($value['phone'])? $value['phone'] : ''
			,'fax'				=> ($value['fax'])? $value['fax'] : ''
			,'gender'			=> $value['gender']
			,'email'			=> $value['email']
			,'company'			=> ($value['company'])? $value['company'] : ''
			,'address'			=> ($value['address'])? $value['address'] : '' 
			,'state'			=> 7
			,'countryId'		=> 'ID'
			,'newArticle'		=> $value['newArticle']
			,'weeklyList'		=> $value['weeklyList']
			,'monthlyList'		=> $value['monthlyList']
			,'packageId'		=> $groupId
			,'promotionId'		=> $value['promotionId']
			,'educationId'		=> $value['educationId']
			,'expenseId'		=> $value['expenseId']
			,'paymentId'		=> $value['paymentId']
			,'businessTypeId'	=> $value['businessTypeId']
			,'periodeId'		=> $value['periodeId']
			,'activationDate'	=> $value['activationDate']
			,'isEmailSent'		=> $value['isEmailSent']
			,'isEmailSentOver'	=> $value['isEmailSentOver']
			,'createdDate'		=> $value['createdDate']
			,'createdBy'		=> $value['createdBy']
			,'modifiedDate'		=> ($value['updatedDate'])? $value['updatedDate'] : ''
			,'modifiedBy'		=> ($value['updatedBy'])? $value['updatedBy'] : ''
			,'isActive'			=> $value['isActive']
			,'isContact'		=> $value['isContact']
		);
		
		return $data;
	}
	function register()
	{
		$data = $this->transformRegister($_POST);
		
		$modelUser = new App_Model_Db_Table_User();
		$modelUser->insert($data);
		
		$this->updateKopel();
		
		/**
		 * SELECT id, parent_id, value, name, lft, rgt
		 * eg. $aReturn = $acl->getGroupData(15)
		 * print_r($aReturn);
		 * output: Array ( [0] => 15 [1] => 10 [2] => Super Administrator [3] => super_admin [4] => 10 [5] => 11 ) 
		 */
		$acl = Glis_Acl::manager();
		$aReturn = $acl->getGroupData($_POST['aro_groups']);
		//print_r($aReturn);
		$acl->addUser($_POST['username'],$aReturn[3]);
		
		// Do you want Email Confirmation send?
		if (($_POST['ec'] == 1))
		{
			//echo 'y';
		}
		else
		{
			//echo 't';
		}
	}
	function transformRegister($value)
	{
		$obj = new Glis_Crypt_Password();
		
		$month 			= ($value['month'])? $value['month'] : '00';
		$day 			= ($value['day'])? $value['day'] : '00';
		$year 			= ($value['year'])? $value['year'] : '0000';
		$newArticle		= ($value['newArticle'])? $value['newArticle'] : '';
		$newRegulation	= ($value['newRegulation'])? $value['newRegulation'] : '';
		$newWRegulation	= ($value['newWeeklyRegulation'])? $value['newWeeklyRegulation'] : '';
		$isContact 		= ($value['iscontact'])? $value['iscontact'] : '';
		
		if ($value['gender'] == 1)
		{
			$gender = 'L';
		}
		else if($value['gender'] == 2)
		{
			$gender = 'P';
		}
		else
		{
			$gender = 'N';
		}
		
		$data = array(
			 'kopel'			=> $this->generateKopel()
			,'username'			=> $value['username']
			,'password'			=> $obj->encryptPassword($value['password'])
			,'fullName'			=> ($value['fullname'])? $value['fullname'] : ''
			,'birthday'			=> $year.'-'.$month.'-'.$day
			,'phone'			=> ($value['phone'])? $value['phone'] : ''
			,'fax'				=> ($value['fax'])? $value['fax'] : ''
			,'gender'			=> $gender
			,'email'			=> $value['email']
			,'company'			=> ($value['company'])? $value['company'] : ''
			,'address'			=> ($value['address'])? $value['address'] : '' 
			,'city'				=> ($value['city'])? $value['city'] : ''
			,'state'			=> ($value['province'])? $value['province'] : ''
			,'countryId'		=> ($value['countryId'])? $value['countryId'] : ''
			,'zip'				=> ($value['zip'])? $value['zip'] : ''
			,'indexCol'			=> 0
			,'newArticle'		=> ($newArticle == 1)? 'Y' : 'N'
			,'weeklyList'		=> ($newWRegulation == 1)? 'Y' : 'N'
			,'monthlyList'		=> ($newRegulation == 1)? 'Y' : 'N'
			,'packageId'		=> $value['aro_groups']
			,'promotionId'		=> ($value['promotioncode'])? $value['promotioncode'] : ''
			,'educationId'		=> ($value['education'])? $value['education'] : 0
			,'expenseId'		=> ($value['expense'])? $value['expense'] : 0
			,'paymentId'		=> ($value['payment'])? $value['payment'] : 0
			,'businessTypeId'	=> ($value['businessType'])? $value['businessType'] : 0
			,'periodeId'		=> 1
			,'createdDate'		=> date('Y-m-d h:i:s')
			,'createdBy'		=> $value['createdBy']
			,'isContact'		=> ($isContact == 1)? 'Y' : 'N'
		);
		
		return $data;
	}
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
	protected function getGroupName($groupId)
	{
		if ($groupId == 11)
		{
			$groupName = "Admin";
		}
		else if ($groupId == 41) 
		{
			$groupName = "Clinic Admin";
		}
		else if ($groupId == 39) 
		{
			$groupName = "Marketing";
		}
		else if ($groupId == 36) 
		{
			$groupName = "Member";
		}
		else if ($groupId == 34) 
		{
			$groupName = "News Admin";
		}
		else if ($groupId == 40) 
		{
			$groupName = "HolProject";
		}
		else if ($groupId == 20) 
		{
			$groupName = "Dc Admin";
		}
		
		return $groupName;
	}
	protected function failAccess($message)
	{
		echo $message;
		exit;
	}
}

// Execute controller command
if (realpath($_SERVER["SCRIPT_FILENAME"]) == realpath(__FILE__) && isset($_GET['cmd'])) {
	
	require_once "../../../baseinit.php";
	
	//Glis_Application::getResource('db');
	
    $ctl = new Account();
    $ctl->$_GET['cmd']();
}