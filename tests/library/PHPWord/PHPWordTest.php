<?php
/*
 * Permet de tester l'utilisation de la librairie PHPWord
*  site : http://phpword.codeplex.com//
*  licence : LGPL Licence
*
* But du test :
*  Modifier un docx
*  - changer le texte
*  - remplacer une image
*  - creer une liste
*
*  Remarque sur le template :
*  il faut ajouter des "zone" ${Info}
*  		Attention, il ne faut pas avoir de correcteur orthographique, sinon il ajouter des caractères particulier
*  Pour le vérifier :
*  Enregistrer le fichier puis changer l'extension en .zip
*  Regarder dans le fichier : document.xml
*  On doit trouver <w:t>${Description}</w:t>
*  Ce n'est pas bon si c'est dans plusieurs champs
*
*
*
*
*  Conclusion :
*
*/
require_once APPLICATION_PATH . '/../library/PHPWord/PHPWord.php';

class PHPWordTest extends PHPUnit_Framework_TestCase
{
	/* Repertoire de résultats */
	private $out;
	private $in;

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
		$autoloader->setFallbackAutoloader(false);
		//Zend_Loader::loadClass('PHPWord',APPLICATION_PATH . '/../library/PHPWord/');
		$this->out = APPLICATION_PATH . '/../tests/var';
		$this->in = __DIR__.'/src';
		
		
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
	public function test_modif_doc1_install () {
		$PHPWord = new PHPWord();
		$document = $PHPWord->loadTemplate($this->in.'/Template.docx');
		$document->setValue('Value1', 'Sun');
		$document->setValue('Value2', 'Mercury');
		$document->setValue('Value3', 'Venus');
		$document->setValue('Value4', 'Earth');
		$document->setValue('Value5', 'Mars');
		$document->setValue('Value6', 'Jupiter');
		$document->setValue('Value7', 'Saturn');
		$document->setValue('Value8', 'Uranus');
		$document->setValue('Value9', 'Neptun');
		$document->setValue('Value10', 'Pluto');

		$document->setValue('weekday', date('l'));
		$document->setValue('time', date('H:i'));

		$document->save($this->out.'/Template1-result.docx');
	}
	
	
	
	/*
	* Test de create_p : création d'une ligne dans le dom
	*/
	public function test_phpdoc_template_create_p () {
	
	
		$PHPWord = new PHPWord_Template($this->in.'/phpword_template_test.docx');
		$dom = new DOMDocument();
		$ret = $dom->load($this->in.'/document.xml');
		$this->assertTrue($ret,'chargement du xml');
		$listBalise = $dom->getElementsByTagName('t');
		Zend_Debug::dump('nombre de balise :' .$listBalise->length);
	
	
		foreach ($listBalise as $balise) {
			$text = $balise->nodeValue;
	
			if ($text == '${products_std}') {
				$PHPWord->create_p($balise);
				break;
			}
		}
		$dom->save($this->out.'/document.out.create_p.xml');
	
		$result = $dom->saveXML();
	
		$expect = file_get_contents($this->in.'/document.create_p.xml');
		$this->assertEquals($expect, $result);
		$PHPWord->save($this->out.'/document.out.create_p.docx');
	
	
	}
	
	/*
	 * Test de maj_list : création et maj d'une liste dans le dom
	*/
	public function test_phpdoc_template_maj_list () {
	
	
		$PHPWord = new PHPWord_Template($this->in.'/phpword_template_test.docx');
		$dom = new DOMDocument();
		$ret = $dom->load($this->in.'/document.xml');
	
		$this->assertTrue($ret,'chargement du xml');
	
		$listBalise = $dom->getElementsByTagName('t');
	
		$list = array ('produit 1 ','produit2', 'produit3');
	
		foreach ($listBalise as $balise) {
	
			$text = $balise->nodeValue;
	
			if ($text == '${products_std}') {
				$PHPWord->maj_list($text, $balise, $list);
				break;
			}
		}
		$dom->save($this->out.'/document.out.maj_list.xml');
	
		$result = $dom->saveXML();
			
		$expect = file_get_contents($this->in.'/document.maj_list.xml');
		$this->assertEquals($expect, $result);
		$PHPWord->save($this->out.'/phpword_template_test.docx');
	}
	
	/*
	 * Test de maj_list : création et maj d'une liste dans le dom
	*/
	public function test_phpdoc_template_replace_image () {
	
		$ret = copy($this->in.'/Templatenke4.docx',$this->out.'/test_template_replace_image.docx');
		$this->assertTrue($ret);
		$PHPWord = new PHPWord_Template($this->out.'/test_template_replace_image.docx');
	
		$ret = $PHPWord->remplace_image('image2.png', $this->in.'/first-image1.png');
		$this->assertTrue($ret);
		/* vérifi*/
		
		$ret = $PHPWord->remplace_image('image3.jpg', $this->in.'/first-image2.jpg');
		$this->assertTrue($ret);
	    
		/* Test des messages d'erreur */
		$ret = $PHPWord->remplace_image('imageTOTO.jpg', $this->in.'/first-image2.jpg');
		$this->assertStringStartsWith('RI', $ret);
		$ret = $PHPWord->remplace_image('image2.png', $this->in.'/firstTOTO-image2.jpg');
		$this->assertStringStartsWith('RI', $ret);
		
		
		$PHPWord->save($this->out.'/test_template_replace_image.docx');
		
		$getImagesize = $PHPWord->getImageSize($this->in.'/first-image1.png');
		Zend_Debug::dump($getImagesize);
		$this->assertEquals($getImagesize[0], 529);
		$this->assertEquals($getImagesize[1], 487);
		
		
			
		
	}
	
	public function test_nke3_install () {
		$PHPWord = new PHPWord();
		$document = $PHPWord->loadTemplate($this->in.'/Template2-filigrane.docx');

		$info2 = array (
					"intro__c" => "Nous vous proposons au travers de cette offre de la qualité des produits nke pour votre First.",
					"Name" => "FIRST",
					"image__c" => $this->in.'/first-image1.png',
					"image2__c" => $this->in.'/first-image2.jpg',
			
					"products_std" =>	Array(
		Array ('Name' => 'Multifonction TL25', 'option__c' => 'std', 'UnitPrice' => 10, 'Quantity'=>1),
		Array ('Name' => 'Support alu brossé', 'option__c' => 'std', 'UnitPrice' => 0, 'Quantity'=>1),
		Array ('Name' => 'compas Fluxgate',    'option__c' => 'std', 'UnitPrice' => 0, 'Quantity'=>1),
		Array ('Name' => 'télcommande filaire', 'option__c' => 'std', 'UnitPrice' => 0, 'Quantity'=>1),
		Array ('Name' => 'capteur sondeur + passe coque', 'option__c' => 'std', 'UnitPrice' => 0, 'Quantity'=>1),
		Array ('Name' => 'capteur loch roue à aubes + passe coque', 'option__c' => 'std', 'UnitPrice' => 0, 'Quantity'=>1),
		Array ('Name' => 'capteur anémo-girouette avec cable', 'option__c' => 'std', 'UnitPrice' => 0, 'Quantity'=>1),
		Array ('Name' => 'interface loch sondeur', 'option__c' => 'std', 'UnitPrice' => 0, 'Quantity'=>1),
		Array ('Name' => 'x 15 m de cable', 'option__c' => 'std', 'UnitPrice' => 0, 'Quantity'=>1),
		Array ('Name' => 'boite de connexion Bus', 'option__c' => 'std', 'UnitPrice' => 0, 'Quantity'=>1)),
					"products_std_amount" => 3452.48,
					"products_std_amount_ttc" => 4129.17,
					"lib_std__c" => "Pack régate",
			
					"products_opt1" => Array(
		Array ('Name' => 'Multifonction Gyroplilot Graphic', 'option__c' => 'opt1', 'UnitPrice' => 100, 'Quantity'=>1),
		Array ('Name' => 'calculateur avec gyro intégré', 'option__c' => 'opt1', 'UnitPrice' => 0, 'Quantity'=>1),
		Array ('Name' => 'ensemble linéraire hydraulique type40', 'option__c' => 'opt1', 'UnitPrice' => 0, 'Quantity'=>1),
		Array ('Name' => 'convertisseur 12V/12V', 'option__c' => 'opt1', 'UnitPrice' => 0, 'Quantity'=>1),
		Array ('Name' => 'capteur angle de barre', 'option__c' => 'opt1', 'UnitPrice' => 0, 'Quantity'=>1),
		Array ('Name' => 'boîte connexion bus', 'option__c' => 'opt1', 'UnitPrice' => 0, 'Quantity'=>1),
		Array ('Name' => 'filtre alimentation', 'option__c' => 'opt1', 'UnitPrice' => 0, 'Quantity'=>1),
		Array ('Name' => 'x 15 m de câble bus', 'option__c' => 'opt1', 'UnitPrice' => 0, 'Quantity'=>1)	),
					"products_opt1_amount" => 4741.88,
					"products_opt1_amount_ttc" => 5671.29,
					"lib_opt1__c" => "Pack Gyropilote type 40",
					
		"products_opt2" => Array(
		Array ('Name' => 'Carbowind (capteur anémomètre giroutte HR 1,10m', 'option__c' => 'opt2', 'UnitPrice' => 100, 'Quantity'=>1),
		Array ('Name' => 'Cable carbowind avionic - 25m (17g/m)', 'option__c' => 'opt1', 'UnitPrice' => 0, 'Quantity'=>1),
		Array ('Name' => 'Capteur de vitesse Ultrason', 'option__c' => 'opt1', 'UnitPrice' => 0, 'Quantity'=>1),
		Array ('Name' => 'Compas Régatta (Gyrostabilisé 3 axes', 'option__c' => 'opt1', 'UnitPrice' => 0, 'Quantity'=>1)),
					"products_opt2_amount" => 3148.24,
					"products_opt2_amount_ttc" => 3765.29,
					"lib_opt2__c" => "Capteurs Haute Résolution",
		"products_opt3" => Array(
		Array ('Name' => 'Processor Régatta', 'option__c' => 'opt2', 'UnitPrice' => 100, 'Quantity'=>1)),
							"products_opt3_amount" => 5129.40,
							"products_opt3_amount_ttc" => 6134.76,
							"lib_opt3__c" => "Capteurs vent réel 25HZ",
		
		);
		echo "\nInfo Template \n";
		foreach ($info2 as $cle =>$value) {
			if (strpos( $cle,'image') === 0) {
			   switch ($cle) {
			   	case 'image__c': $cleimg = 'image1.png'; break;
			   	case 'image2__c' : $cleimg = 'image2.jpg'; break;
			   }
			   $ret = $document->remplace_image($cleimg, $value);
			   $this->assertTrue($ret, 'return of remplace_image');
			   echo 'SetValue '.$cleimg . '= '. $value."\n";
			} else {
				if (!is_array($value)) {
					$document->setValue($cle, $value);
					//echo 'SetValue '.$cle . '= '. $value."\n";
				} else {
					$lstProd = array();
					foreach ($value as $prod) {
						$lstProd[] = $prod['Quantity'].' '.$prod['Name'];
					}
					$document->setValueList($cle, $lstProd);
					//echo 'SetValue '.$cle . '= '. implode(',',$lstProd)."\n";
				}
			}
				
		}

		$document->save($this->out.'/Templatenke3-result.docx');

	}

}