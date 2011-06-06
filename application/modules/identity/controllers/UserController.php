<?php
class Identity_UserController extends Zend_Controller_Action 
{
	protected $_user;
	
	function preDispatch()
	{
		$this->view->addHelperPath(KUTU_ROOT_DIR.'/library/Kutu/View/Helper','Kutu_View_Helper');
		
		$sReturn = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		$sReturn = base64_encode($sReturn);
		
		$auth = Zend_Auth::getInstance();
		if (!$auth->hasIdentity())
		{
			$registry = Zend_Registry::getInstance();
			$config = $registry->get('config');
			
			$loginUrl = $config->identity->config->local->login->url;
						
			$this->_redirect(KUTU_ROOT_URL.$loginUrl.'?returnTo='.$sReturn);
		}
		else 
		{
            $this->_helper->layout->setLayout('layout-profile');
            $this->_helper->layout->setLayoutPath(array('layoutPath'=>KUTU_ROOT_DIR.'/application/modules/identity/views/layouts'));

            $this->_user = $auth->getIdentity();
		}
	}
	function profileAction()
	{
		$this->view->rowset = $this->_user;
		
		$this->view->identity = "Profile";
	}
    function editAction()
    {
        $tblUser = new Kutu_Core_Orm_Table_User();
        $rowset = $tblUser->find($this->_user->kopel)->current();
        $this->view->row = $rowset;
        
        $this->view->identity = "Edit Profile";

        $r = $this->getRequest();

        if ($r->isPost())
        {
            $aData = $r->getParams();
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
    function pictureAction()
    {
        $r = $this->getRequest();
        
        $this->view->row = $this->_user;

		$registry = Zend_Registry::getInstance(); 
		$config = $registry->get('config');
		
		$this->view->cdn = $config->cdn->static->images;
		
        if ($r->isPost())
        {
            $guid = $this->_user->kopel;
            $aData = $r->getParams();
            $arraypictureformat = array("jpg", "jpeg", "gif");
            
			$registry = Zend_Registry::getInstance();
		    $config = $registry->get('config');
		    $cdn = $config->cdn;
		    $sDir = $cdn->static->dir->photo;
			
            //$sDir = KUTU_ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'photo';

            if ($r->getParam('txt_erase') == 'on') {
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
        
        $this->view->identity = "Upload Photo";
    }
    function changePasswordAction()
    {
        $tblUser = new Kutu_Core_Orm_Table_User();
        $rowset = $tblUser->find($this->_user->kopel)->current();
        $this->view->row = $rowset;
        
        $this->view->identity = "Change Password";

        $r = $this->getRequest();

        if ($r->isPost())
        {
            $aData = $r->getParams();
            try {
                $hol = new Kutu_Core_Hol_User();
                if($hol->changePassword($this->_user->kopel, $r->getParam('opasswd'), $r->getParam('newpasswd')))
                {
                    $this->view->message = "Password was sucessfully changed.";
                }
                else
                {
                    $this->view->message = "Old password was wrong. Please retry with correct password.";
                }

            }
            catch (Zend_Exception $e)
            {
                $this->view->message = $e->getMessage();
            }
        }

    }
	function upgradeAction()
	{
		$this->view->identity = "Perbaharui Keanggotaan";
		$this->view->packageId = $this->_user->packageId;
	}
	function upgradesubAction()
	{
		$packageId = $this->_getParam('packageId');
		
		$this->view->packageId = $packageId;
		$this->view->rowUser = $this->_user;
		
		$modelUserFinance = new Kutu_Core_Orm_Table_UserFinance();
		$userFinanceInfo = $modelUserFinance->fetchRow("userId='".$this->_user->kopel."'");
		if (!$userFinanceInfo) {
			$finance = $modelUserFinance->fetchNew();
			$finance->userId = $this->_user->kopel;
			$finance->taxNumber = '';
			$finance->taxCompany = $this->_user->company;
			$finance->taxAddress = $this->_user->address;
			$finance->taxCity = $this->_user->city;
			$finance->taxProvince = $this->_user->state;
			$finance->taxCountryId = $this->_user->countryId;
			$finance->taxZip = $this->_user->zip;
			$finance->save();
		}
		
		$userFinanceInfo = $modelUserFinance->fetchRow("userId='".$this->_user->kopel."'");
		
		$this->view->userInfo = $userFinanceInfo;
	}
    function changeemailAction()
    {
        $tblUser = new Kutu_Core_Orm_Table_User();
        $rowset = $tblUser->find($this->_user->kopel)->current();
        $this->view->row = $rowset;
        
        $this->view->identity = "Ubah Email";

        $r = $this->getRequest();

        if ($r->isPost())
        {
            $aData = $r->getParams();
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
    function checkemailAction()
    {
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $email = ($this->_getParam('email'))? $this->_getParam('email') : '';

        $modelUser = new Kutu_Core_Orm_Table_User();
        $rowset = $modelUser->fetchRow("email='$email'");

        if($rowset)
            $valid = 'false';
        else
            $valid = 'true';

        echo $valid;
        die();
    }
}