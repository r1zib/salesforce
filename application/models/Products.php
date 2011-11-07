<?php
require_once 'ProductSalesforce.php';

class Application_Model_Products
{
		function __construct() {
		}

		function microtime_float()
		{
			list($usec, $sec) = explode(" ", microtime());
			return ((float)$usec + (float)$sec);
		}
		/*
		 * Find permet de trouver 1 produit
		 */
		
		function find ($id, $lstCol='', $pricebookid= '') {
			
			$where = "Id='".$id."'";
			$vue = $this->fetchAll($lstCol,'',$where);
			 
			return $vue['products'][0];
		}
		
		

		function fetchAll ($lstCol, $pricebookid= '', $where="") {
			
			$user = Zend_Registry::get('config')->salesforce->user;
			$password = Zend_Registry::get('config')->salesforce->password;
			$token = Zend_Registry::get('config')->salesforce->token;
			$wdls = Zend_Registry::get('config')->salesforce->wdls;
			
			$sales = new ProductSalesforce($user, $password, $token, $wdls);
			$sales->connection();
			$time_start = $this->microtime_float();
			$vue = array();
			//$where = "";
			$vue['products'] = $sales->listProduct($lstCol, $where);
			if (!is_array($vue['products'])) {
				/* pb de query */
				echo $vue['products'];
			}
			$time_end = $this->microtime_float();
			
			$vue['cols'] = explode(',', $lstCol);
			echo 'tps : '.  ($time_end - $time_start);
			
			$colsPrice = 'Name,Description,IsStandard,Id';
			$vue['Pricebooks'] = $sales->listPricebook($colsPrice);
			$vue['colsPrice'] = explode(',', $colsPrice);
			
			/* Quel est le pricebook sélectionné ?
			 *
			*/
			$pricebookid = "";
			if ($pricebookid == "") {
		   	    /* on prend le 1er de la liste */
				if (count($vue['Pricebooks'])>0) $pricebookid = @$vue['Pricebooks'][0]['Id'];
			}
			
			/*recherche des prix */
			for ($i=0;$i<count($vue['products']); $i++) {
				$id = $vue['products'][$i]['Id'];
				$sel = "Product2Id='".$id."' and Pricebook2Id = '".$pricebookid."'";
				$info = $sales->listPricebookEntry('UnitPrice,Pricebook2Id', $sel);
			
				$vue['products'][$i]['UnitPrice'] = $info[0]['UnitPrice'];
				foreach ($info as $pricebookEntry) {
					$vue['products'][$i]['UnitPrice'] = $pricebookEntry['UnitPrice'];
				}
			
				/* affichage des images */
				if (isset($vue['products'][$i]['image1__c'])) {
					$vue['products'][$i]['image1__c'] = '<img src="'.$vue['products'][$i]['image1__c'].'" height="100" width="100"';
				}
			}
			
			$vue['cols'] = explode(',', $lstCol.',UnitPrice');
			return $vue;
		}

}
	
