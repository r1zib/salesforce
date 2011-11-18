<?php
require_once 'ProductSalesforce.php';

class Application_Model_Opportunities
{
	
		function __construct() {
		}
		
		/*
		 * Find permet de trouver 1 produit
		 */
		function createpdf ($id) {
		try {
			
			$template = Zend_Registry::get('config')->livedocx->template;
			$repertoire = Zend_Registry::get('config')->livedocx->repertoire;
			$web       = Zend_Registry::get('config')->livedocx->web;
				
			$user = Zend_Registry::get('config')->livedocx->user;
			$password = Zend_Registry::get('config')->livedocx->password;
			
	
			$mailMerge = new Application_Model_LiveDocs();
			$mailMerge->setUsername($user)
			->setPassword($password);
			
			$mailMerge->setLocalTemplate($template);

			$info = $this->find($id);
			
			if (isset($info['opportunity']['image__c'])) {
				$url = $info['opportunity']['image__c'];
				$mailMerge->assignImage($url,'image:photo' );
			}
			
			
			/* Information sur l'opportunitée */
			$mailMerge->assign('opportunities_name', @$info['opportunity']['Name']);
			
			if (isset($info['opportunity']['Description'])) {
				$mailMerge->assign('opportunities_description', @$info['opportunity']['Description']);
			}
			
			if (isset($info['opportunity']['Amount'])) {
				$mailMerge->assign('opportunities_amount', @$info['opportunity']['Amount']);
				/* Calcul avec TVA */
				$prix = round(intval(@$info['opportunity']['Amount']) *1.196,2) ;
				$mailMerge->assign('opportunities_amount_ttc', $prix);
			}
			
			
			if (count($info['products']) > 0) {
				$products = array();
				foreach ($info['products'] as $product ) {
					$elt = array();
					$elt['product_quantity'] = @$product['Quantity'];
					$elt['product_code'] = @$product['ProductCode'];
					$elt['product_name'] = @$product['Name'];
					$elt['product_unitprice'] = @$product['UnitPrice'];
					$products[] = $elt;
				}
				$mailMerge->assign('product', $products);
				
			}
			
			
			$mailMerge->createDocument();
			$document = $mailMerge->retrieveDocument('pdf');
			
			/* nom du fichier pdf, le nom du code de pack ou id de l'opportunities */
			if (isset($info['opportunity']['opportunity_code__c']) &&  $info['opportunity']['opportunity_code__c'] !== '') {
				$name = $info['opportunity']['opportunity_code__c'].'.pdf';
			} else {
				$name = $id.'.pdf';
			}
			file_put_contents($repertoire.$name, $document);
			
			$vue['pdf']= $web.$name;
			return $vue;
		} catch (Exception $e) {
		    
			echo '<h1>Exception : ' .$e->getMessage().'</h1>';
			//Zend_Debug::dump($e);
		}
			
		}
		/*
		* cas particulier du modèle nke
		* @param $inso array tableau des informations de salesforces
		* @param Zend_Service_LiveDocx_MailMerge le service pour créer le pdf
		*/
		function optionnke ($info,Zend_Service_LiveDocx_MailMerge $mailMerge) {
					

		/* la liste des produits ont une destination  :
		 * art1 std 10
		 * art2 std 10 : dans le block Standard : prix = sommme des produits = 20 
		 * art3 opt1 100 
		 * art4 opt1 100 : dans le block option1 : prix = block std + block opt1 = 20 + 200
		 * ....
		 * 
		 */					
		if (count($info['products']) > 0) {
			$produits = array();
			$montant = array();
			foreach ($info['products'] as $product ) {
				$elt = array();
				
				$opt = 'std';
				if (isset($product['option__c'])) {
					$opt = $product['option__c'];
				}	
				
				/* Montant des différents blocks */
				if (!isset($montant[$opt])) {
					$montant[$opt] = 0;
				}
				// TODO faire test si la zone n'est pas renseigné dans salesforces
				$produits[$opt] = $product;

				$montant[$opt] += $product['UnitPrice'] * $product['Quantity'];
				
			}
			Zend_Debug::dump($produits);			
			foreach($produits as $cle=>$val) {

				$mailMerge->assign('product_'.$cle, $val);
			}
			$mt_std = 0;
			/* calcul des prix : */
			if (isset($montant['std'])) {
				$mt_std = $montant['std'];
				$mailMerge->assign('product_amount_std', $mt_std);
			}
			foreach($montant as $cle=>$val) {
				$mt = 0;
				if (isset($montant[$cle])) {
					$mt = $montant[$cle];
				}
				$mt += $mt_std;
				$mailMerge->assign('product_amount_'.$cle, $mt_std);
			}
				
			
					
		}
			
		}		
		/*
		* Recherche des information d'une Opportunitée
		* @param string id 
		* @param string lstCol liste des champs 
		* @return un tableau de la liste des enregistrements
		*/
		function find ($id, 
					   $lstCol='Id,Name,Description,opportunity_code__c,image__c,description2__c,Amount',
					   $lstColOpportunityLineItem='PricebookEntryId,Quantity,UnitPrice',
					   $lstColPricebookEntry='Product2Id',
					   $lstColProduct2 = 'ProductCode,Name') {
	                		
			$where = "Id='".$id."'";
			$result = $this->fetchAll($lstCol,$where);
			
            $vue['opportunity'] = $result['opportunities'][0];
                
			/* On compléte avec le liste des produits */
			$sales = Application_Model_SalesforceConnect::getInstance();
				
			
			$where = "OpportunityId='".$id."'";
				
				
			$vue['products'] = $sales->query('OpportunityLineItem', $lstColOpportunityLineItem, $where);

			if (count($vue['products']) > 0) {
				/* Une petite jointure pour récupérer les produits */
				for ($i =0; ($i< count($vue['products'])); $i++) {
					/* On va chercher id du produit*/
					$opportunityLineItem = $vue['products'][$i];
					
					$where = "Id='".$opportunityLineItem['PricebookEntryId']."'";
					$cols = $sales->query('PricebookEntry', $lstColPricebookEntry, $where);
					
					$pid = $cols[0]['Product2Id'];
					$vue['products'][$i]['Product2Id'] =  $pid;
					
					/* recherche des informations produits */
					$where = "Id='".$pid."'";
					$cols = $sales->query('Product2', $lstColProduct2, $where);
					
					$vue['products'][$i]['ProductCode'] =  $cols[0]['ProductCode'];
					$vue['products'][$i]['Name'] =  $cols[0]['Name'];
				}
				$lstCol .= ','.$lstColPricebookEntry.','.$lstColProduct2;
			}
			$vue['cols'] = explode(',', $lstCol);

			return $vue;
			
		}
		
		
	/*
		 * Recherche la liste des Opportunities
		 * @param string lstCol liste des champs 
		 * @param string where Condition dans la recherche
  		 * @return un tableau de la liste des enregistrements
     	 */

		function fetchAll ($lstCol='Name,Id',  $where="") {
			
			$sales = Application_Model_SalesforceConnect::getInstance();

			$vue['opportunities'] = $sales->query('Opportunity', $lstCol, $where);
			$vue['cols'] = explode(',', $lstCol);
			return $vue;
		}

}
	
