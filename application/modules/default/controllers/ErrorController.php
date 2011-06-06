<?php

/**
 * @package holemp
 * @copyright 2009-2010 www.hukumonline.com
 * @author Nihki Prihadi <nihki@hukumonline.com>
 *
 * $error: ErrorController 2009-07-11 11:09
 */

class ErrorController extends Zend_Controller_Action {

    public function init()
    {
    	$this->view->addHelperPath(KUTU_ROOT_DIR.'/library/Kutu/View/Helper','Kutu_View_Helper');
        $contextSwitch = $this->_helper->contextSwitch();

        $contextSwitch->addActionContext('error', 'json')
                      ->initContext();
    }

    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');

        $error = array();

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_OTHER:
                $error = Kutu_Error::fromException($errors->exception);
            break;
        }

        $this->getResponse()->clearBody();
        $this->view->success = false;
        $this->view->error   = $error->getDto();
    }

}