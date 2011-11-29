<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialisation du fichier de log */
    }

    public function indexAction()
    {
        // action body
    	$auth = Zend_Auth::getInstance();
    	if ($auth->hasIdentity()) {
    		//$this->view->setScriptPath(APPLICATION_PATH.'/views/scripts/index/');
    		//$this->view->render("login.phtml");
    		$this->_helper->viewRenderer('login');
     	}
    }

    public function loginAction()
    {
        // action body
    }


}



