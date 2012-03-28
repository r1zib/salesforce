<?php

class ContactController extends Zend_Controller_Action
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
    	$sel = $this->_getParam('sel');
    	$query = null;
    	switch ($sel) {
    		case 'new' : $query = 'etat = "new"'; break;
    		case 'spam' : $query = 'spam = 1'; break;
    	}
    	// action body
        $contacts = new Application_Model_DbTable_Contacts();
        $this->view->entries = $contacts->fetchAll($query, 'date_create DESC');
        
        
        
    }
    
    public function editAction()
    {
    	$id = $this->_getParam('id', 0);
    	if ($id > 0) {
    		$contact = new Application_Model_DbTable_Contacts();
    		$this->view->entries = $contact->find($id);
    	}
    }
    public function majAction()
    {
    	$id = $this->_getParam('id', 0);
    	if ($id > 0) {
    		$action = $this->_getParam('trait', '');
    		$contacts = new Application_Model_DbTable_Contacts();
    		switch ($action) {
    			case 'spam':
    				$ret = $contacts->actionSpam($id, $action);
    				break;
    			case 'ham':
    				$ret = $contacts->actionSpam($id, $action);
    				break;
    			case 'read':
    				$ret = $contacts->actionLu($id);
    				break;
    		    case 'isspam':
    				$ret = $contacts->actionSpam($id,$action);
    				break;
    			default:
    				;
    				break;
    		}
    		
    		$contact = new Application_Model_DbTable_Contacts();
    		$this->view->entries = $contact->find($id);
    		
    	}
    	$this->_helper->viewRenderer->setScriptAction('edit');
    }
    
    

    public function majallAction()
    {
    	// action body
    	$lstId = $this->_getParam('selected_id', '');
    	$action = $this->_getParam('trait', '');
    	$contacts = new Application_Model_DbTable_Contacts();
    	
    	 
    	if ($lstId == '') {
    		$compte_rendu[]= array('log'=>'Vous devez sélectionner des lignes!!');
    	} else {
    		foreach ($lstId as $id) {
    			if ($id == '') continue;
    			switch ($action) {
    				case 'spam':
    					$ret = $contacts->actionSpam($id, $action);
	    				break;
    				case 'ham':
    					$ret = $contacts->actionSpam($id, $action);
    					break;
    				case 'read':
    					$ret = $contacts->actionLu($id);
    					break;
    				default:
    					;
    				break;
    			}
    		
    		}
    
    	}
    	//$this->_redirect('/contact/index');
    	$contacts = new Application_Model_DbTable_Contacts();
    	$this->view->entries = $contacts->fetchAll(null, 'date_create DESC');
    	$this->_helper->viewRenderer->setScriptAction('index');
   }

}

