<?php

class Typo3Controller extends Zend_Controller_Action
{

    public function init()
    {
    	$auth = Zend_Auth::getInstance();
    	if ($auth->hasIdentity()) {

    	} else {
    		$this->__connect();
    	}
    	
    }

    public function indexAction()
    {
        // action body
    }

    public function categoriesAction()
    {

    	$data = array('categories',explode(',', "Alubat,Archambault,Beneteau,Catamaran,Class 40,Delher,Dufour,Elan,Grand soleil,Heol,Jeanneau,J Boats,JPK,Mini,Nautitech,Pogo,RM,Swan,Wauquiez,X Yatchs"));
    	$this->_helper->json($data);
    	    	
    }

    public function opportunitiesAction()
    {
          	// action body
    	$category = $this->_getParam('category', '');
    	$opportunities = new Application_Model_Opportunities();
    	$sel = '';
    	if ($category != '') {
    		$sel = "Familly__c='".$category."'";
    	}	 
    	$result = $opportunities->fetchAll('Name,Id,Code__c',$sel);
    	$this->_helper->json($result['opportunities']);
    }

    public function opportunityAction()
    {
    	// action body
    	$id = $this->_getParam('id', 0);
    	$code = $this->_getParam('code', '');
    	 
    	$opportunities = new Application_Model_Opportunities();
    	if ($id > 0) {
    		$this->_helper->json($opportunities->createJson($id));
    	}
        if ($code != '') {
    		$code = trim($code); 
    		$where = "Code__c='".$code."'";
    		$info = $opportunities->fetchAll('Id',$where);
    		if(isset($info['opportunities'][0]['Id'])) {
    			$id = $info['opportunities'][0]['Id'];
    			$result = $opportunities->createJson($id);
    			$this->_helper->json($result);
    		} else {
    		   $this->_helper->json('');
    	    }
       	}
    	else {
    		
    		$this->_helper->json('');
    	}
    	
    }
    public function productsAction()
    {
    	// action body
    	$family = $this->_getParam('family', '');
    	$opportunities = new Application_Model_Products();
    	$sel = '';
    	if ($family != '') {
    		$sel = "Family='".$family."'";
    	}
    	
    	$result = $opportunities->fetchAll('Name,Id,ProductCode',$sel);
    	$this->_helper->json(@$result['products']);
    }
    
    public function productAction()
    {
    	// action body
    	$id = $this->_getParam('id', 0);
    	$code = $this->_getParam('productcode', '');
    	
    	$product = new Application_Model_Products();
    	if ($id > 0) {
    		$this->_helper->json($product->find($id));
    	}
    	if ($code != '') {
    		$code = trim($code); 
    		$where = "ProductCode='".$code."'";
    		$info = $product->fetchAll('Id',$where);
    		if(isset($info['products'][0]['Id'])) {
    			$this->_helper->json($product->find($info['products'][0]['Id']));
    		} else {
    		   $this->_helper->json('');
    	    }
       	}

    }

    public function __connect()
    {

    	/* Utilisation de l'adaptateur de Db */
    	$dbAdapter = Zend_Db_Table::getDefaultAdapter();
    	$authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
    	$authAdapter->setTableName('users')
    	->setIdentityColumn('username')
    	->setCredentialColumn('password')
    	->setCredentialTreatment('SHA1(CONCAT(?,salt))');

    	$authAdapter->setIdentity('admin');
    	$authAdapter->setCredential('password');
    	
    	/* On teste si le login est bon */
    	$auth = Zend_Auth::getInstance();
    	$result = $auth->authenticate($authAdapter);
    	if ($result->isValid()) {
    		/* il faut mettre les données comme pour un login 'normal' */
    		$user = $authAdapter->getResultRowObject();
    		$auth->getStorage()->write($user);
    		Azeliz_Registreconfig::getInstance()->init_config();
    	}
    		 
    	
    }



}











