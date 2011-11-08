<?php

class OutilPdfTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
		parent::setUp();
	}
	public function testTransfertImage() {
		$rep_doc = Zend_Registry::get('config')->livedocx->image;
		$livedoc = new Application_Model_LiveDocs();
		
		$url = 'http://apps.nke-marine-electronics.fr/img/98-60-250.png';
		list($rep, $img) = $livedoc->transfertImage($url,$rep_doc);
		echo $rep.$img;		
		$this->assertEquals($rep, $rep_doc);
		$this->assertEquals($img, '98-60-250.png');
		
		
		/* test avec répertoire ou l'on a pas les droits */
		$url = 'http://apps.nke-marine-electronics.fr/img/98-60-250.png';
		$rep_doc = '/home/';
		
		try {
			$result = $livedoc->transfertImage($url,$rep_doc);
			// normallement on doit lever un exception et ne jamais passer par ce test
			$this->assertEquals($result, '');
		} catch (Exception $e) {
			$this->assertEquals($e->getCode(), 2);
		}	
		
	}
}