<?php

class Application_Model_DbTable_Contacts extends Zend_Db_Table_Abstract
{


	protected $akismet_api = '07e8acd042d7';
	protected $akismet_blog = 'http://www.erwand.fr/wordpress';
	
	protected $_name = 'contact';
    protected $_rowClass = 'Application_Model_Contact';
    protected $_primary = 'id';
    
    
    /*
     * Action Lu : Indique que le message a été traité
     * Passage de l'état new à l'état ENC
     */
    
    function actionLu ($id) {
    	$data = array('etat' => 'read');
    	$where = 'id = '. intval($id);
    	$ret = $this->update($data, $where);
    	if ($ret == 1 )	return true;
    	else return false;
    }
    /* actionSpam : permet d'indiquer à akismet si le message est un spam ou pas
     * @param int id : id de Contact
     * @param String action -> "spam" "ham" "isspam" 
     * @return booblean/String True-> Ok String : message d'erreur 
     */
    
    function actionSpam ($id,$action) {
    	$where = 'id = '. intval($id);
    	$row = $this->fetchRow($where);
    	if ($row == null) {
    		return 'Id invalid : ' . $id; 
    	} 
    	$data = Zend_Json::decode($row['fields']);
    	Zend_Debug::dump($data);
    	
    	$akismet = new Zend_Service_Akismet(
    		$this->akismet_api,
    		$this->akismet_blog);
    	
    	switch ($action) {
    		case "spam":
    			/* C'est un spam */
    			$akismet->submitSpam($data);
    			$data['spam'] = true;
    			break;
    		case 'ham': 
    			$akismet->submitHam($data);
    			$data['spam'] = false;
    			break;
    			
    	    case 'isspam':
    	    	$data['spam'] = $akismet->isSpam($data);
    	    	break;
    	}
    	
    	$maj = array('spam' => $data['spam'],
    				'fields' => Zend_Json::encode($data));
    	$ret = $this->update($maj, $where);
    	if ($ret == 1 )	return true;
    	else return false;
    	
    }
    public function find($id)
    {
    	$where = 'id = '. intval($id);
    	$row = $this->fetchRow($where);
        	if ($row == null) {
    		return 'Id invalid : ' . $id; 
    	} 
    	$fields = Zend_Json::decode($row['fields']);
    	$fields = array_merge($fields,$row->toArray() );
    	return $fields;
    }
    
    

}

