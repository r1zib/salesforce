<?php
require_once 'ProductSalesforce.php';

class Application_Model_Opportunities
{
	
		function __construct() {
		}
		
		/*
		 * Permet de creer de d'initialiser mailMerge
		 * return $mailMerge
		 */
		function init_liveDocs() {
			try {
				$mailMerge = new Application_Model_LiveDocs();
				
				$config = Azeliz_Registreconfig::getInstance()->getConfig();
				$template = $config->livedocx->template;
				$repertoire = $config->livedocx->repertoire;
				$web       = $config->livedocx->web;
				$repertoireImage = $config->livedocx->image;
			
				$user = $config->livedocx->user;
				$password = $config->livedocx->password;
					
			
				$mailMerge = new Application_Model_LiveDocs();
				$mailMerge->setUsername($user)
				->setPassword($password);
					
				$mailMerge->setLocalTemplate($template);
				$mailMerge->setRepertoire($repertoire)->setWeb($web)->setRepertoireImage($repertoireImage);
				
				return $mailMerge;
			} catch (Exception $e) {
				
				echo '<h1>Exception : ' .$e->getMessage().'</h1>';
				//Zend_Debug::dump($e);
			}
		}
		
		function init_Docx() {
			try {
				$config = Azeliz_Registreconfig::getInstance()->getConfig();
				$template = $config->livedocx->template;
				$repertoire = $config->livedocx->repertoire;
				$web       = $config->livedocx->web;
				$repertoireImage = $config->livedocx->image;
									
				$phpdocx = new Application_Model_MyPHPWord($template);
				$phpdocx->setRepertoire($repertoire)->setWeb($web)->setRepertoireImage($repertoireImage);
				return $phpdocx;
			} catch (Exception $e) {
		
				echo '<h1>Exception : ' .$e->getMessage().'</h1>';
				//Zend_Debug::dump($e);
			}
		}		
		/*
		 * Find permet de trouver 1 produit
		 */
		function createpdf ($id) {
		try {
			$mailMerge = $this->init_liveDocs();
			
			$info = $this->find($id);
			
			
			$this->info_generale($info, $mailMerge);

			
			if (count($info['products']) > 0) {
				$mailMerge->assign('products', $info['products']);
			}

 		    $this->info_nke($info, $mailMerge);
			
			
			$mailMerge->createDocument();
			$document = $mailMerge->retrieveDocument('pdf');
			
			/* nom du fichier pdf, le nom du code de pack ou id de l'opportunities */
			if (isset($info['opportunity']['opportunity_code__c']) &&  $info['opportunity']['opportunity_code__c'] !== '') {
				$name = $info['opportunity']['opportunity_code__c'].'.pdf';
			} else {
				$name = $id.'.pdf';
			}
			
			file_put_contents($mailMerge->getRepertoire().$name, $document);
			/* copie dans un répertoire accéssible */
			copy($docx->getRepertoire().$name,APPLICATION_PATH.'/../public'.$docx->getWeb().$name);
				
			
			$vue['pdf']= 'http://'.$_SERVER['SERVER_NAME'].$mailMerge->getWeb().$name;
			
			Zend_Debug::dump($info);
			return $vue;
		} catch (Exception $e) {
		    
			echo '<h1>Exception : ' .$e->getMessage().'</h1>';
			//Zend_Debug::dump($e);
		}
			
		}
		/*
		* Find permet de trouver 1 produit
		*/
		function createDocx ($id) {
			try {
				$docx = $this->init_Docx();
				$vue['Id']= $id;
				
				$info = $this->find($id);
				$vue['Name']= $info['opportunity']['Name'];
				
				$this->info_generale($info, $docx);
				$this->info_nke($info, $docx);

				
				/* nom du fichier pdf, le nom du code de pack ou id de l'opportunities */
				if (isset($info['opportunity']['opportunity_code__c']) &&  $info['opportunity']['opportunity_code__c'] !== '') {
					$name = $info['opportunity']['opportunity_code__c'].'.docx';
				} else {
					$name = $id.'.docx';
				}
				$docx->save($docx->getRepertoire().$name);
				/* copie dans un répertoire accéssible */
				copy($docx->getRepertoire().$name,APPLICATION_PATH.'/../public'.$docx->getWeb().$name);
				$vue['pdf']= 'http://'.$_SERVER['SERVER_NAME'].$docx->getWeb().$name;
				return $vue;
			} catch (Exception $e) {
				$vue['log'] = $e->getMessage();
				$vue['pdf'] = "KO";
				return $vue;
			}
				
		}
		
		/*
		* cas particulier du modèle nke
		* @param $inso array tableau des informations de salesforces
		* @param Zend_Service_LiveDocx_MailMerge le service pour créer le pdf
		*/
		// TO Utiliser un interface à la place de la class 
		
		function info_generale ($info, $mailMerge) {
			foreach ($info['opportunity'] as $cle =>$value) {
				if (strpos($cle,'image') === 0 ) {
					$mailMerge->assignImage($cle,$value );
					
				} else {
					$mailMerge->assign($cle, $value);
				}
			}
			/* cas particulier du montant */
			if (isset($info['opportunity']['Amount'])) {
				/* Calcul avec TVA */
				$prix = $this->montant_ttc($info['opportunity']['Amount']);
				$mailMerge->assign('Amount_ttc', $prix);
			}

		}		
		
		
		/*
		* cas particulier du modèle nke
		* @param $inso array tableau des informations de salesforces
		* @param Zend_Service_LiveDocx_MailMerge le service pour créer le pdf
		*/
		function info_nke ($info,$mailMerge) {
					

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
				if (isset($product['Description'])) {
					$opt = $product['Description'];
				}	
				
				/* Montant des différents blocks */
				if (!isset($montant[$opt])) {
					$montant[$opt] = 0;
				}
				// TODO faire test si la zone n'est pas renseigné dans salesforces
				// On ne sait pas gérer plusieurs champs sur un ligne
				$produits[$opt][] = $product;
				//$produits[$opt][] = $product['Quantity']. ' '.$product['Name'] ;

				$montant[$opt] += $product['UnitPrice'] * $product['Quantity'];
				
								
			}

			foreach($produits as $cle=>$val) {
				switch ($cle) {
					case 'std': case 'opt1': case 'opt2' : case 'opt3' :
						$lignes = array();
						foreach ($val as $product) {
							$lignes[] = $product['Quantity']. ' '.$product['Name'] ;
						}
						$mailMerge->assign('products_'.$cle, $lignes);
						break;
					case 'c1' : case 'c2' :case 'c3' :
						$lignes = array();
						foreach ($val as $product) {
							$lignes[] = $product['Name'] ;
							if (isset($product['complement__c'])) {
								$lignes[] = $product['complement__c'] ;
							}
							$lignes[] = $product['UnitPrice'].'€ HT '.$this->montant_ttc($product['UnitPrice']). ' € TTC' ;
							$lignes[] = "";
						}
						$mailMerge->assign('products_'.$cle, $lignes);
						break;
				}
			}
			
			$mt_std = 0;
			/* calcul des prix : */
			if (isset($montant['std'])) {
				$mt_std = $montant['std'];
				$mailMerge->assign('products_std_amount', $mt_std);
				$mailMerge->assign('products_std_amount_ttc', $this->montant_ttc($mt_std));
			}
			foreach($montant as $cle=>$val) {
				$mt = 0;
				if ($cle == 'std') continue;
				if (isset($montant[$cle])) {
					$mt = $montant[$cle];
				}
				$mt += $mt_std;
				$mailMerge->assign('products_'.$cle.'_amount', $mt);
				$mailMerge->assign('products_'.$cle.'_amount_ttc', $this->montant_ttc($mt));
			}
			
			
		}
			
		}		
		/*
		* Recherche des information d'une Opportunitée
		* @param string id 
		* @param string lstCol liste des champs 
		* @return un tableau de la liste des enregistrements
		*/
		function find ($id) {

			
			/* la liste des colonnes provient du fichier de application.ini ou celui du login */
			$config = Azeliz_Registreconfig::getInstance()->getConfig();
			
			$lstCol = $config->opportunity->Opportunity;
			$lstColOpportunityLineItem=$config->opportunity->OpportunityLineItem;
			$lstColPricebookEntry=$config->opportunity->PricebookEntry;
			$lstColProduct2=$config->opportunity->Product2;
				
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
					
					if (count($cols) == 0 ) {
						
						
					} 
					foreach ( $cols[0] as $cle => $val) {
						$vue['products'][$i][$cle] = $val;
					}
					
					$pid = $cols[0]['Product2Id'];
										
					/* recherche des informations produits */
					$where = "Id='".$pid."'";
					$cols = $sales->query('Product2', $lstColProduct2, $where);
					/* il n'y a qu'une seule ligne */
					foreach ( $cols[0] as $cle => $val) {
						$vue['products'][$i][$cle] = $val;
					}
				}
			}
			$vue['cols'] = explode(',', $lstColOpportunityLineItem.','.$lstColPricebookEntry.','.$lstColProduct2);

			return $vue;
			
		}
		
		
	/*
		 * Recherche la liste des Opportunities
		 * @param string lstCol liste des champs 
		 * @param string where Condition dans la recherche
  		 * @return un tableau de la liste des enregistrements
     	 */

		function fetchAll ($lstCol='Name,Id',$where="") {
			/* la liste des colonnes provient du fichier de application.ini ou celui du login */
			$sales = Application_Model_SalesforceConnect::getInstance();

			$vue['opportunities'] = $sales->query('Opportunity', $lstCol, $where);
			$vue['cols'] = explode(',', $lstCol);
			return $vue;
		}
		
		/*
		 * Calcul du montant en TTC
		 */
		function montant_ttc ($mt, $taxe = 1.196) {
			return number_format(round(floatval($mt) *1.196,2),2,',','.');
		}
		

}
	
