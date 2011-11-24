<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	
	
	
	protected function _initDoctype()
	{
		/* permet d'indiquer à la view son doctype */
		$this->bootstrap('view');
		$view = $this->getResource('view');
		$view->doctype('XHTML1_STRICT');
	}

	protected function _initConfig()
	{
		
	    $config = new Zend_Config($this->getOptions(), true);
	    Zend_Registry::set('config', $config);
	    
	    // TODO faut-il utiliser les sessions ou les registres ?
	    $configSession = new Zend_Config_Ini(__dir__.'/configs/session.ini', APPLICATION_ENV);
	    Zend_Session::setOptions($configSession->toArray());
	     
	    return $config;
	}
	protected function _initRequest()
	{
		// TODO Je ne sais pas s'il faut le mettre là
		$autoloader = Zend_Loader_Autoloader::getInstance();
		$autoloader->setFallbackAutoloader(true);
	}
	
	
	

}

