<?php

class UserController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
        $users = new Application_Model_DbTable_Users();
        $this->view->entries = $users->fetchAll();
    }

    public function editAction()
    {
    $form = new Application_Form_User();
    $form->envoyer->setLabel('Modifier');
    $this->view->form = $form;
    
    $id = $this->_getParam('id',0);
    $users = new Application_Model_DbTable_Users();
    $user = $users->find($id)->current();
     
    if ($this->getRequest()->isPost()) {
        $formData = $this->getRequest()->getPost();
        if ($form->isValid($formData)) {
        	$user->username = $form->getValue('username');
        	$user->password = $form->getValue('password');
        	$user->role     = $form->getValue('role');
        	$user->token    = $form->getValue('token');
        	
        	$user->save();
        	/* Traitemeent supplémentaire */
        	/* Transfert du WSDL > le répertoire client */
        	/* maj de la base */
        	// TODO A clarifier quels est la meilheur méthode pour réaliser l'upload 
        	$fullFilePath = $form->wsdl->getFileName();
        	$uploadedData = $form->getValues();
        	
        	//Zend_Debug::dump( $form->getValues(), '$uploadedData');
        	//Zend_Debug::dump($fullFilePath, '$fullFilePath');
        	if ($form->wsdl->re)
        	
        	
        	$this->_helper->redirector('index');
        } else {
            $form->populate($formData);
        }
    } else {
    	if ($id > 0) {
    		$form->populate($user->toArray());
    	} 
    }
    }

    public function addAction()
    {
    	$form = new Application_Form_User();
    	$form->envoyer->setLabel('Ajouter');
    	$this->view->form = $form;
    	if ($this->getRequest()->isPost()) {
    		$formData = $this->getRequest()->getPost();
    		if ($form->isValid($formData)) {
    			$users = new Application_Model_DbTable_Users();
				/* 
				 * Test supplémentaire
				 */
				$erreur = false;
				/* Vérification que le username n'existe pas */
    			$where = "username = '".$form->getValue('username'). "'";
    			
    			$sel = $users->fetchAll($where);
    			if ($sel->count()> 1) {
    				/* Erreur le login existe déjà */
    				$this->_flashMessage('Login existe déjà');
    				$erreur = TRUE;
    			} 	
    			/* Vérification de la zone role */
    			$role = $form->getValue('role');
    			if ($role == '') {
    				$role == 'user';
    			} else {
    				if (strpos('admistrator,user', $role) === FALSE) {
    					$this->_flashMessage('pb role');
    					$erreur = TRUE;
    				} 
    			}
    			
    			if ($erreur) {
    				$form->populate($formData);
    			} else {
	    			$newUser = $users->fetchNew();
	    			$newUser->username = $form->getValue('username');
	    			$newUser->password = $form->getValue('password');
	    			$newUser->role     = $form->getValue('role');
	    			$newUser->token    = $form->getValue('token');
	    			$newUser->date_created = new Zend_Db_Expr('NOW()');
	    			Zend_Debug::dump($newUser);
	    			$id = $newUser->save();
	    			$this->_helper->redirector('index');
    			}
    		} else {
    			$form->populate($formData);
    		}
    	}
    }
    public function deleteAction()
    {
	   if ($this->getRequest()->isPost()) {
	      $id = intval($this->getRequest()->getPost('id'));
	      $users = new Application_Model_DbTable_Users();
	      
	      $where =  'id = '.$id;
	      $users->delete($where);
	      $this->_helper->redirector('index');
	   }
    }
    
    // TODO il est utilisé aussi dans le controller auth
    protected function _flashMessage($message) {
    	$flashMessenger = $this->_helper->FlashMessenger;
    	$flashMessenger->setNamespace('actionErrors');
    	$flashMessenger->addMessage($message);
    }
    
    /*
     * permet d'ajouter les action à la mise à jours d'un user
     * -> gestion des fichiers téléchargé
     * -> création de répertoire client....
     * Array $user  
     * 
     */
    protected function __traitement ($user,$form) {
    	/* 
    	 * Création d'un répertoire client 
    	 *      nke/var/repertoire_client
    	 * Il va contenir le répertoire template 
    	 * */
    	$rep = APPLICATION_PATH.'/../var/'.$user->username.'/template';
    	if (! is_dir($rep)) {
    		$ret = mkdir($rep, 0775,TRUE);
    		if ($ret == False) {
    			$this->_flashMessage('problème de création de répertoire :'.$rep);
    		}
    	}
    	/* transfert des fichiers vers le répertoire client */
    	
    	
    	/* Modification de $user */
    	
    }


}







