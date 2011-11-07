<?php
require_once (__dir__.'/developerforce/soapclient/SforcePartnerClient.php');
require_once (__dir__.'/developerforce/soapclient/SforceEnterpriseClient.php');

class ProductSalesforce {
	private $user;
	private $password;
	private $token;
	private $wsdl;
	
	private $mySforceConnection;
	
	function __construct($user,$password,$token,$wsdl ) {
		$this->user = $user;
		$this->password = $password;
		$this->token = $token;
		$this->wsdl = $wsdl;
	}
	
	function connection () {
		try {
			$this->mySforceConnection = new SforceEnterpriseClient();
			$this->mySforceConnection->createConnection($this->wsdl);
			$this->mySforceConnection->login($this->user, $this->password.$this->token);
        } catch (Exception $e) {
        	$msg = '<pre>Problème de connection :'."Exception ".$e->faultstring."<br/><br/>\n";
        	$msg .= $this->infoMsg().'</pre>';
        	return $msg;
        }
        return true;
	}
	function infoMsg(){
		$msg = "Last Request:<br/>\n";
		$msg .= $this->mySforceConnection->getLastRequestHeaders()."<br/>\n";
		$msg .= $this->mySforceConnection->getLastRequest()."<br/>\n";
		$msg .= "Last Response:<br/><br/>\n";
		$msg .= $this->mySforceConnection->getLastResponseHeaders()."<br/>\n";
		$msg .= $this->mySforceConnection->getLastResponse()."<br/>\n";
		return $msg;
	}	
	
	private function query ($table,$cols,$where="") {
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
			return $msg;
		}
		return $vue;
	}
	
	function listProduct ($cols='Family,Name,ProductCode',$where="") {
		return $this->query('Product2',$cols,$where);
	}
	function listPricebook ($cols='Name,Description',$where="") {
		return $this->query('Pricebook2',$cols,$where);
	}
	function listPricebookEntry ($cols='Name,UnitPrice',$where="") {
		return $this->query('PricebookEntry',$cols,$where);
	}	
}	
