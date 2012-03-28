<?php

class Application_Model_Products
{
	/* permet de recherche en base locale ou directement dans salesfores */
	private $rech_db = true;
	
		function __construct() {
		}

		/*
		* Recherche des information d'une Opportunitée
		* @param string id
		* @param string lstCol liste des champs
		* @return un tableau de la liste des enregistrements
		*/
		function find ($id) {
			$info = array();
			if ($this->rech_db) {
				$info = $this->find_db($id);
			} else {
				$info = $this->find_salesforce($id);
			}
			if (isset($info['UnitPrice'])) {
				/* Formatage de prix */
				$prix = $info['UnitPrice'];
				$info['UnitPrice'] = $this->montant($prix);
				$info['UnitPrice_ttc'] = $this->montant_ttc($prix);
			}
			$info['pdf'] = Zend_Json::prettyPrint(Zend_Json::encode($info));  
			
			return $info;
		}	
		
		function find_salesforce ($id) {
			
			/* la liste des colonnes provient du fichier de application.ini ou celui du login */
			$config = Azeliz_Registreconfig::getInstance()->getConfig();
			$lstCol = $config->product->Product2;
			
				
			$where = "Id='".$id."'";
			$vue = $this->fetchAll($lstCol,'',$where);
			 
			return $vue['products'][0];
		}
		function find_db ($id) {
		
			/* la liste des colonnes provient du fichier de application.ini ou celui du login */
			$config = Azeliz_Registreconfig::getInstance()->getConfig();
			$colProduct2 = $config->product->Product2;
			$colPricebook = 'Name,Description,IsStandard,Id';
			$colPricebookEntry = 'UnitPrice,Pricebook2Id';
		
			/* Recherche du prix standard */
			$product = new Application_Model_Product2Mapper();
			$produit = $product->findDetail($id, $colProduct2, $colPricebook, $colPricebookEntry);
							
			return $produit;
		}
		
		/*
		* Recherche des information d'une Opportunitée
		* @param string id
		* @param string lstCol liste des champs
		* @return un tableau de la liste des enregistrements
		*/
		function fetchAll ($lstCol='Name,ProductCode,Id', $where ='') {
			if ($this->rech_db) {
				return $this->fetchAll_db($lstCol,$where);
			} else {
				return $this->fetchAll_salesforce($lstCol,$where);
			}
		}
		
		
		
		function fetchAll_db ($lstCol='Name,ProductCode,Id', $where="") {
		
			$product = new Application_Model_Product2Mapper();
            
            $vue['products'] = $product->fetchAll($lstCol,$where);
				
			if (!is_array($vue['products'])) {
				/* pb de query */
				echo $vue['products'];
			}
			$vue['cols'] = explode(',', $lstCol.',UnitPrice');
			$vue['tps'] = 0;
			return $vue;
		}
		
		function fetchAll_salesforce ($lstCol='Name,ProductCode,Id', $where="") {

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
		/*
		* Calcul du montant en TTC
		*/
		function montant_ttc ($mt, $taxe = 1.196) {
			return number_format(round(floatval($mt) *1.196,2),2,',','.');
		}
		function montant ($mt) {
			return number_format($mt,2,',','.');
		}
		

}
	
