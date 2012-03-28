<?php

/*
 * Principe Général :
 * 
 * Exemple : Table Products2
 * Description de la table dans Salesforces :
 * Products2 
 *  Id
 *  Name
 *  ....
 *  Champs personnalisé
 *   
 *  Description de la table dans base locale
 *  Id
 *  fields BLOB
 *  
 *  Dans le champ fields, il y a tous les champ de la table Products2 , Pour prendre moins de place, on a utiliser json
 * 
 */



class Application_Model_Import
{

/*
 * 
 */
function import ($table,$lstCol,&$erreur = null,&$id=null) {
	
	Zend_Debug::dump('test import');
	$info = array();
	$config = Zend_Registry::get('config');
	
	$db = Zend_Db::factory($config->resources->db );
	/* supression de la table */
	$nb = $db->delete($table);
	$info[] = 'Suppression de la table '.$table.' : '.$nb.' nb';

	/* Information de la base */
	$sql = 'SHOW COLUMNS FROM '.$table;
	$res = $db->query($sql);
	if (!$res) {
	   $info[] = 'Impossible d\'exécuter la requête : ' . mysql_error();
	   return;
		
	}
	$champs = array();
	
	if (count($res) > 0) {
		foreach($res as $champ) {
			$champs[]=$champ['Field'];
		}
	}
    // Zend_Debug::dump($champs);
    /* recherche des informations dans salesforce */
     $erreur = "";	
     $info[] =  $this->import_salesforce ($db,$table,$champs, 
     										$lstCol, $where='');
      
	
	return $info;	
}
/*
 * 
 */


function import_salesforce ($db,$table,$champs,
							$lstCol,$where='', $option='ORDER BY Id ASC') {
	/* importation en masse de salesforces */
	$sales = Application_Model_SalesforceConnect::getInstance();
	$erreur = "";
	$lst = $sales->query($table,$lstCol,$where,$option,&$erreur);
    
	$info = array();
	
	$info[] = 'Importation de la table '.$table.' : '.count($lst).' nb';
	$id = "";
	try {
	
		foreach ($lst as $elt){
				
			$fields = array('fields' => Zend_Json::encode($elt));
			/* On rajoute les champs nécessaires aux index */
			foreach ($champs as $champ) {
				if ($champ == 'fields') continue;
				if (isset($elt[$champ])) {
					$fields[$champ] = $elt[$champ];
				}
			}
			//Zend_Debug::dump($fields);
			/* Liste des champs de la base */
			$ret = $db->insert($table, $fields);
			$id = $fields['Id'];
		}
	} catch (Exception $e) {
		$info[] = 'PB importation  table '.$table.' : '.$e->getMessage();
	}
    if (!empty($erreur) && ($where=='')) {
    	// Appel récurssif pour importer le reste des enregristrements
    	 
    	$where = "(Id > '".$id."' )";
    	$info[] = 'Import :'.$where;
    	
    	$info[] = $this->import_salesforce($db, $table,$champs,$lstCol,$where,$option);
    }
	
	return $info;
	
}
function import_OpportunityLineItem ($table,$lstCol,&$erreur = null,&$id=null) {
	Zend_Debug::dump('test import');
	$info = array();
	$config = Zend_Registry::get('config');

	$db = Zend_Db::factory($config->resources->db );
	/* supression de la table */
	$nb = $db->delete($table);
	$info[] = 'Suppression de la table '.$table.' : '.$nb.' nb';

	/* Information de la base */
	$sql = 'SHOW COLUMNS FROM '.$table;
	$res = $db->query($sql);
	if (!$res) {
		$info[] = 'Impossible d\'exécuter la requête : ' . mysql_error();
		return;

	}
	$champs = array();

	if (count($res) > 0) {
		foreach($res as $champ) {
			$champs[]=$champ['Field'];
		}
	}
	// Zend_Debug::dump($champs);
	/* recherche des informations dans salesforce */
	$erreur = "";
	$lst = explode(',','std,opt1,opt2,opt3,c1,c2,c3,');
	foreach ($lst as $elt) {
		$where = "Description = '".$elt."'";
		$info[] =  $this->import_salesforce ($db,$table,$champs,
		$lstCol, $where);
	}

	return $info;
}


function importAll () {
	$info = array();
	/* la liste des colonnes provient du fichier de application.ini ou celui du login */
	// TODO  il faut rajouter les Rajouter les champs "obligatoire" nécessaire aux index (Id, .....) 
	
 	$config = Azeliz_Registreconfig::getInstance()->getConfig();
 	$lstCol = $config->product->Product2;

 	$lstCol = $this->unionListe($lstCol, 'Id,Name,Family');
 	
	$info['Product2'][] = $this->import('Product2', $lstCol);
	
	$lstCol = 'Name,Description,IsStandard,Id';
	$info['Pricebook2'][] = $this->import('Pricebook2', $lstCol);
		
	$lstCol = $config->opportunity->PricebookEntry;
	$lstCol = $this->unionListe($lstCol, 'Id,Product2Id,Pricebook2Id,UnitPrice');
	$info['PricebookEntry'][] = $this->import('PricebookEntry', $lstCol);
		
	
	
	$lstCol = $config->opportunity->OpportunityLineItem;
	$lstCol = $this->unionListe($lstCol, 'Id,OpportunityId,PricebookEntryId');
	$erreur = '';
	$info['OpportunityLineItem'][] = $this->import_OpportunityLineItem('OpportunityLineItem', $lstCol, &$erreur);
	if (!empty($erreur)) 	$info['OpportunityLineItem'][] = $erreur;
	
	
	
	$lstCol = $config->opportunity->Opportunity;
	Zend_Debug::dump($lstCol);
	$lstCol = $this->unionListe($lstCol, 'Id');
	$info['Opportunity'][] = $this->import('Opportunity', $lstCol);
	
	
	
	
	return $info;
}

/*
 * Permet de faire l'union entre 2 liste
 */
function unionListe($lstCol,$lst){
	if ($lstCol == '') return $lst;
	$tab = array_merge(explode(',',$lstCol),explode(',',$lst) );
	/* suppression des doublons */
	$tab = array_unique($tab);
	
	Zend_Debug::dump($tab);
	return implode(',', $tab);
}



}

