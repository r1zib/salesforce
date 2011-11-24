<?php

class Application_Model_Products
{
		function __construct() {
		}

		/*
		 * Find permet de trouver 1 produit
		 */
		
		function find ($id, $lstCol='Name,ProductCode,Description,Family,Id,Description__c,image__c', $pricebookid= '') {
			
			$where = "Id='".$id."'";
			$vue = $this->fetchAll($lstCol,'',$where);
			 
			return $vue['products'][0];
		}
		
		

		function fetchAll ($lstCol='Name,ProductCode,Id', $pricebookid= '', $where="") {

			$sales = Application_Model_SalesforceConnect::getInstance();
			$vue = array();
			$time_start = $sales->microtime_float();
			
			$vue['products'] = $sales->query('Product2', $lstCol, $where);
			
			if (!is_array($vue['products'])) {
				/* pb de query */
				echo $vue['products'];
			}
			$time_end = $sales->microtime_float();
			
			$vue['cols'] = explode(',', $lstCol);
			$vue['tps'] = $time_end - $time_start;
			
			
			$colsPrice = 'Name,Description,IsStandard,Id';
			$vue['Pricebooks'] = $sales->query('Pricebook2',$colsPrice);
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
				$info = $sales->query('PricebookEntry','UnitPrice,Pricebook2Id', $sel);
                if (isset($info[0])) {			
					$vue['products'][$i]['UnitPrice'] = $info[0]['UnitPrice'];
                }
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
	
