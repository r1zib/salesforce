<?php
/*
* Permet de tester l'utilisation de la librairie phpdocx
*  site : http://www.phpdocx.com/
*  licence : LGPL Licence 
* 
* But du test :
*  Modifier un docx
*  - changer le texte
*  - remplacer une image
*  - creer une liste
*
*  Conclusion : KO
*  Je n'arrive pas à modifier un fichiers docx, il faut forcement en créer un document
*  C'est la version payante qui permet d'utiliser les templates
*  
*
*/



class PhpdocxTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
		parent::setUp();
		// Ensure library/ is on include_path
		set_include_path(implode(PATH_SEPARATOR, array(
		realpath(APPLICATION_PATH . '/../library'),
		get_include_path(),
		)));
		$autoloader = Zend_Loader_Autoloader::getInstance();
		$autoloader->setFallbackAutoloader(true);

	}
	
	public function test_install () {
		/*
		 * Les modules ZipArchive and XSLT doivent être installés.
		 */
		/* ne marche pas avec apache2
		$lstmodule = apache_get_modules();
		
		$ret = is_int(in_array('php5-zip', $lstmodule)); 
	    $this->assertTrue($ret,'module php php5-zip');
	    
	    $ret = is_int(in_array('php5-xsl.', $lstmodule));
	    $this->assertTrue($ret,'module php php5-xsl');
	     */
		
	}
	
	
	
	
	
	
	
}
	