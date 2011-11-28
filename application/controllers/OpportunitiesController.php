<?php

class OpportunitiesController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // Affiche de la liste des produits
        
    	$opportunities = new Application_Model_Opportunities();
    	$this->view->entries = $opportunities->fetchAll();
    	
    }

    public function editAction()
    {
    	$id = $this->_getParam('id', 0);
    	if ($id > 0) {
    		$opportunities = new Application_Model_Opportunities();
    		$this->view->entries = $opportunities->find($id);
    	
    	}
    }

    public function createpdfAction()
    {
    	// action body
    	$id = $this->_getParam('id', 0);
    	if ($id > 0) {
    		$opportunities = new Application_Model_Opportunities();
    		$this->view->entries = $opportunities->createDocx($id);
    		
    	}	 
    	
    }
    public function createallpdfAction()
    {
    	// action body
    	$lstId = $this->_getParam('selected_id', '');
    	
    	if ($lstId == '') {
    		$compte_rendu[]= array('log'=>'Vous devez sélectionner des lignes!!');
    	} else {
	    	$opportunities = new Application_Model_Opportunities();
	    	$compte_rendu =  array();
	    	foreach ($lstId as $id) {
	    		if ($id == '') continue;
	    		$compte_rendu[] = $opportunities->createDocx($id);
	    	}
	    	
    	}
    	$this->view->entries = $compte_rendu;
    }
    
}





