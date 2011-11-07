<?php


class OutilPdfTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
		parent::setUp();
	}
    public function testPdf()
    {
    	
    	$rep_doc = __DIR__.'/../../../docs/';
    	$file_doc = $rep_doc.'pack.doc';
    	$file_pdf = $rep_doc.'pack.pdf';
    	echo $file_doc;
    		
    	$mailMerge = new Zend_Service_LiveDocx_MailMerge();
    	
    	$mailMerge->setUsername('erwand')
    		->setPassword('online1ld');
    		
    		$mailMerge->setLocalTemplate($file_doc);
    	
    	$mailMerge->assign('pack_nom', "Performer >45’")
    		->assign('pack_intro', 'nke propose toute une gamme de capteurs et de configurations spécifiquement adaptés à la course ou à la croisière.
De l’afficheur TL25 pied de mât au kit sécurité, tout est prévu pour vous permettre d’atteindre le meilleur niveau de
performance en toute sécurité.')
    		->assign('pack_designation', 'Pack 98-60-259');
    	
    	$lstProduct =  array(
    			array('pack_product_nb'   => '3',  'pack_product_designation' => 'Multifonctions SL50'),
    			array('pack_product_nb'   => '1',  'pack_product_designation' => 'Télécommande radio'),
    			array('pack_product_nb'   => '1',  'pack_product_designation' => 'capteur Carbowind (hauteur 1,10m)'),
    			array('pack_product_nb'   => '1',  'pack_product_designation' => 'Câble Avionic (25m)'),
    			array('pack_product_nb'   => '1',  'pack_product_designation' => '1 ultrasonic speedo'),
    			array('pack_product_nb'   => '3',  'pack_product_designation' => 'capteur compas Regatta'));
    	
    	$mailMerge->assign('pack_product', $lstProduct);
    	
    	$mailMerge->createDocument();
    	
    	$document = $mailMerge->retrieveDocument('pdf');
    	
    	file_put_contents($file_pdf, $document);
    	
    	
    	
    	
    }


}



