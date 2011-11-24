<?php
/*
 * Permet d'avoir les informations générales
* soit du configs/apllication.ini
* soit du users
* Pattern Singleton
*
*/

class Azeliz_Registreconfig {

	private static $_instance;
	private $config;

	/**
	 * Empêche la création externe d'instances.
	 */
	private function __construct () {
		/* On se connecte à salesforce */
		$this->init_config ();
	}

	/**
	 * Empêche la copie externe de l'instance.
	 */
	private function __clone () {
	}

	/**
	 * Renvoi de l'instance et initialisation si nécessaire.
	 */
	public static function getInstance () {
	 if (!(self::$_instance instanceof self))
	 self::$_instance = new self();

		return self::$_instance;
	}

	/**
	* Permet d'initialiser le fichier de config
	* doit être appellé à chaque connection ou déconnection d'un utilisateur
	*/
	public function init_config () {

		/* lecture du fichier de configs/application.ini */
		$config = Zend_Registry::get('config');
		
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
			/* information de la base  */
			$user = $auth->getIdentity();
			if (!empty($user->config)) {
				$configUser = new Zend_Config_Ini($user->config,
				                              APPLICATION_ENV);
			}
			/* Salesforce */
			if (!empty($user->sf_login)) {
				$config->salesforce->user = $user->sf_login;
			}
			if (!empty($user->sf_password)) {
				$config->salesforce->password = $user->sf_password;
			}
			if (!empty($user->token)) {
				$config->salesforce->token = $user->token;
			}
			if (!empty($user->wsdl)) {
				$config->salesforce->wsdl = $user->wsdl;
			}
			
			
			if (isset($configUser->livedocx->template)) {
				$config->livedocx->template = $configUser->livedocx->template;
			}
			if (isset($configUser->livedocx->repertoire)) {
				$config->livedocx->repertoire = $configUser->livedocx->repertoire;
			}
			if (isset($configUser->livedocx->web)) {
				$config->livedocx->web = $configUser->livedocx->web;
			}
			if (isset($configUser->livedocx->image)) {
				$config->livedocx->image = $configUser->livedocx->image;
			}
			/* Liste des champ à afficher */
			
			if (isset($configUser->product->Product2)) {
				$config->product->Product2 = $configUser->product->Product2;
			}
			if (isset($configUser->opportunity->Opportunity)) {
				$config->opportunity->Opportunity = $configUser->opportunity->Opportunity;
			}
			if (isset($configUser->opportunity->OpportunityLineItem)) {
				$config->opportunity->OpportunityLineItem = $configUser->opportunity->OpportunityLineItem;
			}
			if (isset($configUser->opportunity->Product2)) {
				$config->opportunity->Product2 = $configUser->opportunity->Product2;
			}
			
			//Zend_Debug::dump($config->opportunity);
			//Zend_Debug::dump($configUser->opportunity);
			
		}
		$this->config = $config;

	}
	
	
	public function getConfig() {
		return $this->config;
	}
}	
	
