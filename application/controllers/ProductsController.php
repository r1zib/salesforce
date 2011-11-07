<?php

class ProductsController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // Affiche de la liste des produits
        
    	$products = new Application_Model_Products();
    	$this->view->entries = $products->fetchAll('Name,ProductCode,Id');
    	
    }

    public function editAction()
    {
    	$id = $this->_getParam('id', 0);
    	if ($id > 0) {
    		$products = new Application_Model_Products();
    		$this->view->entries = $products->find($id,'Name,ProductCode,Description,Family,Id,Description__c,image__c');
    	}
    }


}



