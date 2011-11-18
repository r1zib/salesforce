<?php

class Application_Model_LiveDocs extends Zend_Service_LiveDocx_MailMerge

{
	
	/**
	* Assign values to template fields
	*
	* @param array|string $field
	* @param array|string $value
	* @return Zend_Service_LiveDocx_MailMerge
	* @throws Zend_Service_LiveDocx_Exception
	* @since  LiveDocx 1.0
	*/
	
	public $info = array();
	
	public function assign($field, $value = null) {
	
		//echo 'assign '. $field. ' = '.$value .  "\n"  ;
		$this->info[$field] = $value;
		parent::assign($field, $value);
	}
	
	/*
	 * Permet de tracer les informations envoyés vers liveDocs
	 */
		
	public function getInfo() {
		return $this->info;
	}
	
	
	/**
	* Assign image to template fields
	*   Si l'image n'est pas sur le serveur alors on fait le téléchargement au préalable
	*   Retaille de l'image
	*   
	* @param string $url url de l'image
	* @param string $field champ dans la template
	* @param string $rep répertoire ou stocker les images sur le serveur (par défaut le fichier de config livedocx.image)  
	* @return Zend_Service_LiveDocx_MailMerge
	* @throws Zend_Service_LiveDocx_Exception
	* @since  LiveDocx 1.0
	*/
	
	public function assignImage($url, $field, $rep = null) {
		
		list($rep, $nom) = $this->transfertImage($url, $rep);
		Zend_Debug::dump($rep.$nom);
		if (!$this->imageExists($rep.$nom)) {
			$this->uploadImage($rep.$nom);
		} else {
			/* l'image n'existe pas alors erreur */
			
		}
				
		$this->assign($field, $nom);
		
	}
	
	/**
	* transfertImage
	*   Transfert une image sur le serveur
	*
	* @param string $url url de l'image
	* @param string $rep répertoire ou stocker les images sur le serveur (par défaut le fichier de config livedocx.image)
	* @return array ($repertoire de l'image, $nom de l'image)
	* @throws Zend_Service_Exception
	* @since  LiveDocx 1.0
	*/
	
	
	public function transfertImage($url, $rep=null) {
		
		if ($rep == null) {
			$rep = Zend_Registry::get('config')->livedocx->image;
		}	
		
		// Rien à faire, image est vide 
		if ($url == '') return ; 
		
		$file = file_get_contents($url);
				
		if ($file === false) {
			throw new Zend_Service_Exception('PB dans la lecture de'.$url."\n",1);
		} 
		// nom de l'image
		$tab = explode('/',$url);
		$nom = $tab[count($tab) - 1 ];
		$out = $rep.$nom;
		
		if (file_put_contents($out,$file) === false) {
			throw new Zend_Service_Exception("PB d'écriture dans le répertoire".$rep."\n",2);
		} 
		return array($rep, $nom);
		
	}
	
}

