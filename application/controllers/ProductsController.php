<?php

class ProductsController extends Zend_Controller_Action
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
        // Affiche de la liste des produits
        
    	$products = new Application_Model_Products();
    	
    	$this->view->entries = $products->fetchAll();
    	
    	//Zend_Debug::dump($this->view->entries);
    	
    }

    public function editAction()
    {
    	$id = $this->_getParam('id', 0);
    	if ($id > 0) {
    		$products = new Application_Model_Products();
    		$this->view->entries = $products->find($id);
    	}
    }


}



