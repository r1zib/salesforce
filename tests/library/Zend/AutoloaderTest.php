<?php
/*
 * Permet de tester l'utilisation de la classe Zend_loader
 * But : 
 * 	- charger dynamiquement les class de Zend et de l'application
 *  - Charger des classes de la library
 *  - Ne plus utiliser Require_once()
 *  
 *  Conclusion :
 *  Le plus simple : dans le boostrap 
 *   $autoloader = Zend_Loader_Autoloader::getInstance();
	 $autoloader->setFallbackAutoloader(true);
 */


class AutoloaderTest extends PHPUnit_Framework_TestCase
{

	public function setUp()
	{
		$this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
		parent::setUp();
		// Ensure library/ is on include_path
		set_include_path(implode(PATH_SEPARATOR, array(
		realpath(APPLICATION_PATH . '/../tests/library'),
		realpath(APPLICATION_PATH . '/../library'),
		get_include_path(),
		)));
	}
	public function test_Zend_Loader() {
		/* permet d'inclure des classes */
		Zend_Loader::loadClass('ClassVide1');
		$class = new ClassVide1();
		$this->assertTrue($class instanceof  ClassVide1);
	}
	
	public function test_cas_simple() {
		/* Zend_Loader_Autoloader va par défaut capturer les espaces de noms "Zend_" et "ZendX_
		 * C'est à dire qu'il va charger les classe de zend et Zendx
		 */
		$autoloader = Zend_Loader_Autoloader::getInstance();
		
		Zend_Debug::dump("Super plus besoins de faire un require de zend_debug");
		
		/* exemple d'utilisation de classe de zend */
		
		/* Zend_Loader_Autoloader va par défaut capturer les espaces de noms "Zend_" et "ZendX_
		 * C'est à dire qu'il va charger les classe de zend 
		 * Il ne trouvera pas les classes qui sont dans le répertoire library
		 * Si on veut qu'il s'occupe aussi d'un autre espace de nom
		 * 		library/Azeliz/MyConfig.php
		 *  $autoloader->registerNamespace('Azeliz_');
		 * 
		 */
		
		
		try {
			/* normallement cela fait planter */
			//$registre = Azeliz_Registreconfig::getInstance();
		} catch (Exception $e) {
			Zend_Debug::dump($e);
		}

				
		$autoloader->registerNamespace('Azeliz_');
		// Cela marche, il va bien retrouver la class 
		$registre = Azeliz_Registreconfig::getInstance();
		$this->assertTrue($registre instanceof Azeliz_Registreconfig);	
		
		/* suppression des l'espace de nom */
		$autoloader->resetInstance();
		
	}
	public function test_cas() {
		/*
		* Autre solution pour charger les classe de la library
		* Enfin, il se peut que vous vouliez que l'autoloader par défaut charge toutes les classes de tous les espaces de noms.
		*  $autoloader->setFallbackAutoloader(true);
		*/
		$autoloader = Zend_Loader_Autoloader::getInstance();
		$autoloader->setFallbackAutoloader(true);
		$class = new ClassVide2();
		$this->assertTrue($class instanceof  ClassVide2);
		

	}
	
}