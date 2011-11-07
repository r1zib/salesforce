<?php

class Application_Model_OutilPdf
{
function test () {
	
	
	$rep_doc = __DIR__.'/../../docs/';
	$file_doc = $rep_doc.'pack2.doc';
	$file_pdf = $rep_doc.'pack2.pdf';
	
	$mailMerge = new Zend_Service_LiveDocx_MailMerge();
	
	$mailMerge->setUsername('erwand')
	->setPassword('online1ld');
	
	$mailMerge->setLocalTemplate($file_doc);
	
	$mailMerge->assign('pack_nom', 'Test Pack nom')
	->assign('pack_description', 'Test Pack descritpion')
	->assign('pack_ref',  'Tet pack_ref');
	
	$mailMerge->createDocument();
	
	$document = $mailMerge->retrieveDocument('pdf');
	
	file_put_contents($file_pdf, $document);
	
	
	
}

}

