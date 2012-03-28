<?php

class Application_Model_Product2Mapper
{
	protected $_dbTable;

	public function setDbTable($dbTable)
	{
		if (is_string($dbTable)) {
			$dbTable = new $dbTable();
		}
		if (!$dbTable instanceof Zend_Db_Table_Abstract) {
			throw new Exception('Invalid table data gateway provided');
		}
		$this->_dbTable = $dbTable;
		return $this;
	}

	public function getDbTable()
	{
		if (null === $this->_dbTable) {
			$this->setDbTable('Application_Model_DbTable_Product2');
		}
		return $this->_dbTable;
	}

	public function save(Application_Model_Guestbook $guestbook)
	{
	}

	/*
	* Permet de trouver 1 produit grace à Id de Salesforce
	* @Return Array la liste des champs
	* 		   False : on n'a pas trouvé le produit
	*/
	public function find($id,$colProduct2='')
	{
		$result = $this->getDbTable()->find($id);
		if (0 == count($result)) {
			return FALSE;
		}
		$row = $result->current();
		
		$fields = Zend_Json::decode($row->fields);
		if ($colProduct2 != '') {
			$this->filtreChamp(&$fields, $colOpportunity);
		}
				
		return $fields;		
	}
	/*
	* findDetail permet de recherche les produits de l'opportunitée
	*/
	public function findDetail($id,
								$colProduct2,
								$colPricebook,
								$colPricebookEntry)
	{
		
		/* Recherche duu prix standard */
		$db = $this->getDbTable()->getAdapter();
		$select = $db->select();
		$select->from('Pricebook2')
		->where('fields LIKE ?', '%IsStandard":true%');
		
		$lst = $db->fetchRow($select);
		$pricebookid = "";
		if ($pricebookid == "") {
			$pricebookid = @$lst['Id'];
		}
				
		$select = $db->select();
		$select->from(array('p' => 'Product2'),
		array('Id','fields'))
		->joinLeft(array('pb' => 'PricebookEntry'),
											"pb.Product2Id = p.Id And pb.Pricebook2Id = '".$pricebookid."'",
		array('Id','Product2Id','Pricebook2Id','pbfields'=>'fields'))
		->where("p.Id = '".$id."'");
		
		$produit = $db->fetchRow($select);
		
		//Zend_Debug::dump($select->__toString());
		/* Décode des champs Fiedls */
		if (isset($produit['fields']) && !empty($produit['fields'])) {
			$lst = Zend_Json::decode($produit['fields']);
			$this->filtreChamp($lst, $colProduct2);
			if (count($lst)>0) {
				$produit = array_merge($lst,$produit);
			}
		}
		if (isset($produit['pbfields']) && !empty($produit['pbfields'])) {
			$lst = Zend_Json::decode($produit['pbfields']);
			$this->filtreChamp($lst, $colPricebookEntry);
			if (count($lst)>0) {
				$produit = array_merge($lst,$produit);
			}
		}
		//Zend_Debug::dump($produit);
		return $produit;
		
		
	}
	
	
	
	

	public function fetchAll($lstCol= '0',$where)
	{
		
		$db = $this->getDbTable();
		if ($where != '') {
			/*
			 * Pour l'instant on ne sait faire que une selection
			* exemple : 'Familly__c='toto'
			*/
			$wherefield = $this->selFields($where);
			$select = $db->select();
			$select->from('Product2')
			->where($wherefield);
			
			$resultSet = $db->fetchAll($select);
			
		} else {
			$resultSet = $this->getDbTable()->fetchAll();
		}
		$entries   = array();
		foreach ($resultSet as $row) {
			$entrie = Zend_Json::decode($row->fields);
			/* suppression des colonnes non demandé */
			$this->filtreChamp(&$entrie,$lstCol);
			$entries[] = $entrie;
		}
		
		return $entries;
	}
	/*
	* Permet de supprimer les champs qui sont en trop
	* @param array tableau indexé
	* @param String contenant la liste des champs à garder
	*/
	function filtreChamp (&$lstChamp,$lstCol) {
		if ($lstCol == '' || $lstChamp == '' || count($lstChamp) == 0 ) return;
	
		foreach ($lstChamp as $cle=>$val) {
			if (strpos($lstCol, $cle)===FALSE) {
				unset($lstChamp[$cle]);
			}
		}
	}
	/*
	* Permet de convertir une sélection sql
	* avec la stucture particulière avec le fields
	* Exemple : exemple : 'Familly__c='toto'
	* On veut : fields LIKE "%Familly__c:toto%"
	* @param String Selection des champs
	* @Return String Sélection pour le champs fields
	*/
	function selFields ($where) {
		/*
		* Pour l'instant on ne sait faire que une selection
		* exemple : 'Familly__c='toto'
		*/
		list($champ,$cond) = explode('=',$where);
		$champ = trim($champ);
		$cond = str_replace("'",'',trim($cond));
		/* On veut avoir
		 * fields Like "%champ:valeur%"
		*/
		$wherefield = 'fields LIKE "%\"'.$champ.'\":\"'.$cond.'\"%"';
		
		return $wherefield;
	}
	
	
}

