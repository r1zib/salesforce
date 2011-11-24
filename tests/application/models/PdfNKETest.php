<?php

class PdfNKETest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
		parent::setUp();
	}
	/*
	 * Permet de tester si les informations de salesforces sont bien dans le format attendu de livedocs
	 */
	
	
	public function testPdf()
	{
		
	$info = array (
		"opportunity" =>array("Description" => "Test description",
							  "Name" => "Crosière",
							  "Amount" => 120),
		"products" =>	Array(Array ('Name' => 'Gyropilot Graphic', 'option__c' => 'std', 'UnitPrice' => 10, 'Quantity'=>1),
		 					  Array ('Name' => 'Produit2', 'option__c' => 'std', 'UnitPrice' => 10, 'Quantity'=>1),
							  Array ('Name' => 'Produit-opt1', 'option__c' => 'opt1', 'UnitPrice' => 100, 'Quantity'=>1)
 						)
	);
	$opportunites = new Application_Model_Opportunities();
	
	$mailMerge = $opportunites->init_liveDocs();
	
	$opportunites->info_generale($info, $mailMerge);
	$opportunites->info_nke($info, $mailMerge);
	
	$infoLivedoc = $mailMerge->getInfo();

	$info2 = array (
			"Description" => "Test description",
			"Name" => "Crosière",
			"products_std" =>	Array(Array ('Name' => 'Gyropilot Graphic', 'option__c' => 'std', 'UnitPrice' => 10, 'Quantity'=>1),
									  Array ('Name' => 'Produit2', 'option__c' => 'std', 'UnitPrice' => 10, 'Quantity'=>1)),
			"products_std_amount" => 20,
			"products_opt1" => Array(Array ('Name' => 'Produit-opt1', 'option__c' => 'opt1', 'UnitPrice' => 100, 'Quantity'=>1)),
			"products_opt1_amount" => 120,
			"products" =>	Array(Array ('Name' => 'Gyropilot Graphic', 'option__c' => 'std', 'UnitPrice' => 10, 'Quantity'=>1),
								  Array ('Name' => 'Produit2', 'option__c' => 'std', 'UnitPrice' => 10, 'Quantity'=>1),
								  Array ('Name' => 'Produit-opt1', 'option__c' => 'opt1', 'UnitPrice' => 100, 'Quantity'=>1)),
   		    "Amount" => 120,
			"Amount_ttc" => 143.52
	);
	
	$ret = asort($info2);
	$ret = asort($infoLivedoc);
		
	$this->assertEquals($info2, $infoLivedoc);
		
	Zend_Debug::dump($infoLivedoc);
	
	
	}
	public function testModele()
	{
		$opportunites = new Application_Model_Opportunities();
		$mailMerge = $opportunites->init_liveDocs();
		
		$file_doc = APPLICATION_PATH. "/../var/test/test.doc";
		$file_pdf = APPLICATION_PATH. "/../var/test/test.pdf";
		 
		$mailMerge->setLocalTemplate($file_doc);
		$mailMerge->assign('intro__c', 'tested');
		

		
		$mailMerge->createDocument();
		 
		$document = $mailMerge->retrieveDocument('pdf');
		 
		file_put_contents($file_pdf, $document);
	}
	
}



