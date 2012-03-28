<?php
/*
 * Permet de se connecter à Salesforce et de faire des requetes dans la base
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
    		/*
    		 *  lecture du fichier de configs/application.ini 
    		 *  Si on est connecté alors on prend le paramétage du user
    		 *  */
    		$config = Azeliz_Registreconfig::getInstance()->getConfig();
    		$user = $config->salesforce->user;
    		$password = $config->salesforce->password;
    		$token = $config->salesforce->token;
    		$wsdl = $config->salesforce->wsdl;
    		
    		Zend_Debug::dump($token);
    			
    		$this->mySforceConnection = new SforceEnterpriseClient();
    		$this->mySforceConnection->createConnection($wsdl);
    		$this->mySforceConnection->login($user, $password.$token);
    	} catch (Exception $e) {
    		$msg = '<pre>Problème de connection :'."Exception ".$e->faultstring."<br/><br/>\n";
    		$msg .= 'user = '.$user.'<br/>Password = '.$password.'<br/>token = '.$token.'<br/>';
    		$msg .= $this->infoMsg().'</pre>';
    		// Affichage à l'écran
    		 $log = Zend_Registry::get('log');
    		 $log->log($msg,Zend_log::ALERT);
    		
    		return $msg;
    	}
    	return true;
    	
    }
    /**
    * Retourne les messages d'erreur de salesforces
    * @return String Info de salesforce
    */
    
    
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
    
    public function query ($table,$cols, $where="",$option="", &$erreur=null) {
    	try {
    		$options = new QueryOptions(2000);
    		$this->mySforceConnection->setQueryOptions($options);
    		
    		$vue = array();
    		$query = "SELECT ".$cols." from ".$table;
    			
    		if (!empty($where)) {
    			$query .= " Where ".$where;
    		}
    		if (!empty($option)) {
    			$query .= " ".$option;
    		}
    		Zend_Debug::dump($query);
    		$response = $this->mySforceConnection->query($query);
    		$this->infoMsg();
    
    		if (!$response->done) {
    			// la requete c'est mal passé 
    			if (isset($erreur)) {
    				$erreur = 'PB Query <br/>';
    			}
    			
    			//return 'PB Query <br/>';
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
