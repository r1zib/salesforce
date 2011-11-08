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
    	
    	$rep_doc = Zend_Registry::get('config')->livedocx->repertoire;
    	$user = Zend_Registry::get('config')->livedocx->user;
    	$password = Zend_Registry::get('config')->livedocx->password;;
    	
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
    
    function testPdfsuite () {
    	$rep_doc = Zend_Registry::get('config')->livedocx->repertoire;
    	$template = $rep_doc.'test1-template.doc';
    	$file_pdf = $rep_doc.'test.pdf';
    	
    	$user = Zend_Registry::get('config')->livedocx->user;
    	$password = Zend_Registry::get('config')->livedocx->password;;
    	 
    	$code = 0; 
    	$id = "006U0000002uQNcIAM";
    	
    	try {
    		
    	$mailMerge = new Zend_Service_LiveDocx_MailMerge();
    	$mailMerge->setUsername($user)
    		->setPassword($password);
    	$code = 1;
    	$mailMerge->setLocalTemplate($template);
    	
    	echo 'test';
    	$code = 2;
    	$mailMerge->assign('Ammount', 50000);
    	$mailMerge->assign('opportunities_name', 'University of AZ Portable Generators');
    	
    	$code = 3;
    	$mailMerge->createDocument();
    	$code = 4;
    	$document = $mailMerge->retrieveDocument('pdf');
    	$code = 5;
    	
    	file_put_contents($file_pdf, $document);
    	  	
    	} catch (Exception $e) {
    		$msg = '['.$code.']'. $e->getCode() . $e->getMessage();
    		switch ($code) {
    			case 0 : 
    				$msg = "Vérifier le profil pour accéder à livedocx.com ".
    				       "User name : ".$user."/n
    				       Password : ".$password . " /n".
    				       $e->getCode() . $e->getMessage();
    				
    				break;
   				case 1 :
   					$msg = "Vérifier le tempate ".
   				   	       "template : ".$template."/n".
  							$e->getCode() . $e->getMessage();
   				
  					break;
				case 3 :
					$msg = "Problème dans la création du document : /n".
					$e->getCode() . $e->getMessage();
				
 					break;
  							
    				
    		}
    		$msg = '['.$code.'] '.$msg;
    		echo $msg;
    		
    		
    	} 
    	
    }
    
    function testPdfsuite2 () {
    	
    	echo 'Test création :' . Zend_Registry::get('config')->livedocx->template;
    	$oppor = new Application_Model_Opportunities();
    	$oppor->createpdf("006U0000002KGHKIA4");
    	echo 'Test création 2 :' . Zend_Registry::get('config')->livedocx->template;
    	$oppor = new Application_Model_Opportunities();
    	$oppor->createpdf("006U0000002uQNwIAM");
    	
    	
    }	 
    
    
    


}



