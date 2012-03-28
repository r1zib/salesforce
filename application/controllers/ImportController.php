<?php

class ImportController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }
    
    public function preDispatch() {
        // preDispatch est lancé avant chaque action 
    	$auth = Zend_Auth::getInstance();
    	
       	if (!$auth->hasIdentity()) {
    		 $this->_redirect('/index/forbidden');
    	} 
    	
    }

    public function indexAction()
    {
    }
    

    public function startAction()
    {

    	// action body
    	$import = new Application_Model_Import();
    	$this->view->entries = $import->importAll();
    		
    }


}



