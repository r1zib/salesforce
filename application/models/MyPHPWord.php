<?php

//Zend_Loader::loadClass('PHPWord_Template',APPLICATION_PATH . '/../library/PHPWord/PHPWord/');
require_once APPLICATION_PATH . '/../library/PHPWord/PHPWord/Template.php';

/* 
 * Class pour créer un document docx 
 */


class Application_Model_MyPHPWord extends PHPWord_Template

{
	
	
	/* Répertoire pour stocke le pdf */
	public $_repertoire ;
	/* acces web pour accéder au pdf */
	public $_web;
	/* Répertoire pour stocker les images */
	public $_repertoireImage;
	
	
	
	public function __construct($strFilename) {
	 	parent::__construct($strFilename);
	 	
	 	
	 }

	public function assign($field, $value = null) {
		$this->info[$field] = $value;
			
		if (!is_array($value)) {
			parent::setValue($field, $value);
		} else {
			parent::setValueList($field, $value);
		}
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

	public function assignImage($field,$url, $rep = null) {

		list($rep, $nom) = $this->transfertImage($url, $rep);
		
		switch ($field) {
			case 'image__c': $cleimg = 'image2.png'; break;
			case 'image2__c' : $cleimg = 'image3.jpg'; break;
			default:
				throw new Zend_Service_Exception("Le champ n'est pas géré : ".$field."\n",1);
			break;
		}
		
		$this->remplace_image($field,$rep.$nom);
		
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
	/*
	* Permet de tracer les informations envoyés vers liveDocs
	*/
	
	public function getInfo() {
		return $this->info;
	}
	
	/**
	 * @param String $repertoire
	 * @return Application_Model_LiveDocs
	 */
	public function setRepertoire($repertoire)
	{
		$this->_repertoire = $repertoire;
		return $this;
	}
	
	/**
	 * @return String
	 */
	public function getRepertoire()
	{
		return $this->_repertoire;
	}
	
	/**
	 * @param String $web
	 * @return Application_Model_LiveDocs
	 */
	public function setWeb($web)
	{
		$this->_web = $web;
		return $this;
	}
	
	/**
	 * @return String
	 */
	public function getWeb()
	{
		return $this->_web;
	}
	
	/**
	 * @param String $repertoireImage
	 * @return Application_Model_LiveDocs
	 */
	public function setRepertoireImage($repertoireImage)
	{
		$this->_repertoireImage = $repertoireImage;
		return $this;
	}
	
	/**
	 * @return String
	 */
	public function getRepertoireImage()
	{
		return $this->_repertoireImage;
	}	

}

