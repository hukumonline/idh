<?php
class Kutu_Lib_Formater
{
	/**
	 * he syntax is DateAdd (interval,number,date).
	 * The interval is a string expression that defines the interval you want to add. 
	 * For example minutes or days, 
	 * the number is the number of that interval that you wish to add, and the date is the date.
	 * Interval can be one of:
	 * @params yyyy	year
	 * @params q	Quarter
	 * @params m	Month
	 * @params y	Day of year
	 * @params d	Day
	 * @params w	Weekday
	 * @params ww	Week of year
	 * @params h	Hour
	 * @params n	Minute
	 * @params s	Second
	 * As far as I can tell, w,y and d do the same thing, 
	 * that is add 1 day to the current date, q adds 3 months and ww adds 7 days. 
	 *
	 */
		
	static function DateAdd($interval, $number, $date) {
	
	    $date_time_array = getdate($date);
	    $hours = $date_time_array['hours'];
	    $minutes = $date_time_array['minutes'];
	    $seconds = $date_time_array['seconds'];
	    $month = $date_time_array['mon'];
	    $day = $date_time_array['mday'];
	    $year = $date_time_array['year'];
	
	    switch ($interval) {
	    
	        case 'yyyy':
	            $year+=$number;
	            break;
	        case 'q':
	            $year+=($number*3);
	            break;
	        case 'm':
	            $month+=$number;
	            break;
	        case 'y':
	        case 'd':
	        case 'w':
	            $day+=$number;
	            break;
	        case 'ww':
	            $day+=($number*7);
	            break;
	        case 'h':
	            $hours+=$number;
	            break;
	        case 'n':
	            $minutes+=$number;
	            break;
	        case 's':
	            $seconds+=$number;
	            break;            
	    }
	    $timestamp= mktime($hours,$minutes,$seconds,$month,$day,$year);
	    return $timestamp;
	}	
	static function getRealIpAddr()
	{
	    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
	    {
	      $ip=$_SERVER['HTTP_CLIENT_IP'];
	    }
	    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
	    {
	      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
	    }
	    else
	    {
	      $ip=$_SERVER['REMOTE_ADDR'];
	    }
	    return $ip;
	}	
	static function updateUserLog()
	{
        $userId = Zend_Auth::getInstance()->getIdentity()->kopel;
		
        $model = new Kutu_Core_Orm_Table_UserLog();
        $model->updateUserLog($userId,array(
        	'lastlogin' => new Zend_Db_Expr('NOW()')
        ));
	}
	static function writeLog()
	{
        $userId = Zend_Auth::getInstance()->getIdentity()->kopel;
		
        $model = new Kutu_Core_Orm_Table_UserLog();
        $model->addUserLog(array(
        	'user_id' => $userId,
        	'user_ip' => self::getRealIpAddr(),
        	'login' => new Zend_Db_Expr('NOW()')
        ));
	}
	static function get_user_id($username)
	{ 
		$tblUser = new Kutu_Core_Orm_Table_User();
		$rowUser = $tblUser->fetchRow("username='".$username."'");
		return $rowUser->kopel;
	}	
	
	/**
	 * select month
	 * @param $montharray
	 * @return $month
	 */
	function monthPullDown($month, $montharray)
	{
	 	$monthSelect = "\n<select name=\"month\">\n";
		for($j=0; $j < 12; $j++) {
			if ($j != ($month - 1)) 
				$monthSelect .= " <option value=\"" . ($j+1) . "\">$montharray[$j]</option>\n";
			else
				$monthSelect .= " <option value=\"" . ($j+1) . "\" selected>$montharray[$j]</option>\n";
		}
		
		$monthSelect .= "</select>\n\n";
		return $monthSelect;
	}
	/**
	 * dayPullDown
	 * @param $day
	 * @return day
	 */
	 
	function dayPullDown($tday='')
	{
		$day = "<select name=\"day\" id=\"day\">\n";
		if ($tday) {
			$day .= "<option value=\"" . $tday . "\" selected>$tday</option>\n";
			$day .= "<option value=''>Tgl</option>";
		} else {
			$day .= "<option value='' selected>Tgl</option>";
		}
		for($i=1;$i <= 31; $i++) {
			if (($tday) and ($i == $tday)) {
				continue;
			} else {
				$day .= " <option value=\"" . $i ."\">$i</option>\n";
			}
		}
	
		$day .= "</select>\n\n";
		return $day;
	}
	
	/**
	 * educationPullDown
	 * @return education
	 */
	
	function educationPullDown($edu='')
	{
		$tblEducation = new Kutu_Core_Orm_Table_Education();
		$row = $tblEducation->fetchAll();
		$education = "<select name=\"education\" id=\"education\">\n";
		if ($edu) {
			$rowEducation = $tblEducation->find($edu)->current();
			$education .= "<option value='$rowEducation->educationId' selected>$rowEducation->description</option>";
			$education .= "<option value =''>----- Pilih -----</option>";
		} else {
			$education .= "<option value ='' selected>----- Pilih -----</option>";
		}
		foreach ($row as $rowset) {
			if (($edu) and ($rowset->educationId == $rowEducation->educationId)) {
				continue;
			} else {
				$education .= "<option value='$rowset->educationId'>$rowset->description</option>";
			}
		}
		$education .= "</select>\n\n";
		return $education;
	}
	
	/**
	 * expensePullDown
	 * @return expense
	 */
	
	function expensePullDown($exp='')
	{
		$tblExpense = new Kutu_Core_Orm_Table_Expense();
		$row = $tblExpense->fetchAll();
		$expense = "<select name=\"expense\" id=\"expense\">\n";
		if ($exp) {
			$rowExpense = $tblExpense->find($exp)->current();	
			$expense .= "<option value='$rowExpense->expenseId' selected>$rowExpense->description</option>";
			$expense .= "<option value=''>----- Pilih -----</option>";
		} else {
			$expense .= "<option value='' selected>----- Pilih -----</option>";
		}
		foreach ($row as $rowset) {
			if (($exp) and ($rowset->expenseId == $rowExpense->expenseId)) {
				continue;
			} else {
				$expense .= "<option value='$rowset->expenseId'>$rowset->description</option>";
			}
		}
		$expense .= "</select>\n\n";
		return $expense;
	}
	
	/**
	 * businessTypePullDown
	 * @return businessType
	 */
	
	function businessTypePullDown($businessTypeId='')
	{
		$tblBusiness = new Kutu_Core_Orm_Table_Business();
		$row = $tblBusiness->fetchAll();
		$businessType = "<select name=\"businessType\" id=\"businessType\">\n";
		if ($businessTypeId) {
			$rowBusinessType = $tblBusiness->find($businessTypeId)->current();
			$businessType .= "<option value='$rowBusinessType->businessTypeId' selected>$rowBusinessType->description</option>";
			$businessType .= "<option value=''>----- Pilih -----</option>";			
		} else {
			$businessType .= "<option value='' selected>----- Pilih -----</option>";
		}
		foreach ($row as $rowset) {
			if (($businessTypeId) and ($rowset->businessTypeId == $rowBusinessType->businessTypeId)) {
				continue;
			} else {
				$businessType .= "<option value='$rowset->businessTypeId'>$rowset->description</option>";
			}
		}
		$businessType .= "</select>\n\n";
		return $businessType;		
	}	
    /**
     * province
     */
    function chooseProvince($province=null)
    {
        $tblProvince = new Kutu_Core_Orm_Table_Province();
        $row = $tblProvince->fetchAll();

        $select_province = "<select name=\"taxProvince\" id=\"taxProvince\">\n";
        if ($province) {
            $rowProvince = $tblProvince->find($province)->current();
            $select_province .= "<option value='$rowProvince->pid' selected>$rowProvince->pname</option>";
            $select_province .= "<option value =''>----- Pilih -----</option>";
        } else {
            $select_province .= "<option value ='' selected>----- Pilih -----</option>";
        }
        
        foreach ($row as $rowset) {
            if (($province) and ($rowset->pid == $rowProvince->pid)) {
                continue;
            } else {
                $select_province .= "<option value='$rowset->pid'>$rowset->pname</option>";
            }
        }

        $select_province .= "</select>\n\n";
        return $select_province;
    }
	function list_years() {
	
		$list = "<select name='year'>\n";
  $min_year=date("Y")-50;
  $max_year=date("Y")-10;

    // Mean of users age: 25 years old
  $default_year=date("Y");
$list .= "<option value='' selected>Tahun</option>";
  for($i=$max_year;$i>=$min_year;$i--)
     if($i==$default_year)
        $list .= '<option selected="selected" value="'.$i.'">'.
              $i.'</option>';
     else
        $list .= '<option value="'.$i.'">'.
              $i.'</option>';	
              
              
              	
	  	$list .= "</select>\n";
	
	  	return $list;
	} 	
	static function get_date($tanggal) {
		$id = $tanggal;
		$id = substr($id,8,2).".".substr($id,5,2).".".substr($id,2,2)." ".substr($id,11,2).":".substr($id,14,2);
		return $id; 
	}
}
?>