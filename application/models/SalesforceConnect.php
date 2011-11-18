<?php
/*
 * Permet de se connecter à Salesforce
 * Pattern Singleton 
 * 
 */
require_once ('developerforce/soapclient/SforcePartnerClient.php');
require_once ('developerforce/soapclient/SforceEnterpriseClient.php');


class Application_Model_SalesforceConnect {
	
    private static $_instance;
    private $mySforceConnection;    
 
    /**
     * Empêche la création externe d'instances.
     */
    private function __construct () {
    	/* On se connecte à salesforce */
    	$this->connect();
    }
 
    /**
     * Empêche la copie externe de l'instance.
     */
    private function __clone () {}
 
    /**
     * Renvoi de l'instance et initialisation si nécessaire.
     */
    public static function getInstance () {
        if (!(self::$_instance instanceof self))
            self::$_instance = new self();
 
        return self::$_instance;
    }
 
    /**
     * Permet de se connecter à salesForce
     */
    private function connect () {
    	try {
    		/* lecture du fichier de configs/application.ini */
    		//$config = Registreconfig::getInstance()->getConfig();
    		$config = Zend_Registry::get('config');
    		$user = $config->salesforce->user;
    		$password = $config->salesforce->password;
    		$token = $config->salesforce->token;
    		$wdls = $config->salesforce->wdls;
    			
    		$this->mySforceConnection = new SforceEnterpriseClient();
    		$this->mySforceConnection->createConnection($wdls);
    		$this->mySforceConnection->login($user, $password.$token);
    	} catch (Exception $e) {
    		$msg = '<pre>Problème de connection :'."Exception ".$e->faultstring."<br/><br/>\n";
    		$msg .= $this->infoMsg().'</pre>';
    		// Affichage à l'écran
    		 $log = Zend_Registry::get('log');
    		 $log->log($msg,Zend_log::ALERT);
    		
    		return $msg;
    	}
    	return true;
    	
    }
    private function infoMsg(){
    	$msg = "Last Request:<br/>\n";
    	$msg .= $this->mySforceConnection->getLastRequestHeaders()."<br/>\n";
    	$msg .= $this->mySforceConnection->getLastRequest()."<br/>\n";
    	$msg .= "Last Response:<br/><br/>\n";
    	$msg .= $this->mySforceConnection->getLastResponseHeaders()."<br/>\n";
    	$msg .= $this->mySforceConnection->getLastResponse()."<br/>\n";
    	return $msg;
    }
    
   /**
    * Recherche dans un table de salesforce
    * @param string table  de la table de salesforce
    * @param string cols list des champs recherché
    * @param string where condition de recherche
    * @return un tableau de la liste des enregistrements
    */
    
    public function query ($table,$cols, $where="") {
    	try {
    		$vue = array();
    		$query = "SELECT ".$cols." from ".$table;
    			
    		if (!empty($where)) {
    			$query .= " Where ".$where;
    		}
    		$response = $this->mySforceConnection->query($query);
    
    		if (!$response->done) {
    			return 'PB Query <br/>';
    		}
    		if (count($response->size > 0)) {
    			foreach ($response->records as $sObject) {
    				$vue[] = get_object_vars($sObject);
    			}
    		}
    
    	} catch (Exception $e) {
    		$msg = 'Problème de Query :'."Exception ".$e->faultstring."<br/><br/>\n";
    		$msg .= $this->infoMsg();
    		// Affichage à l'écran
    		$log = Zend_Registry::get('log');
    		$log->log($msg,Zend_log::ALERT);
    		return $msg;
    	}
    	return $vue;
    }   
    /* retourne timestamp en  microseconde
     * @return float 
     */
    
    function microtime_float()
    {
    	list($usec, $sec) = explode(" ", microtime());
    	return ((float)$usec + (float)$sec);
    } 
}
