<?php
Class ZipTest extends PHPUnit_Framework_TestCase
{
	/* Repertoire de résultats */
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
	}

	public function	test_lecture () {
		$zip = zip_open(__DIR__."/Templatenke3.docx");
		
		while($zipFile = zip_read($zip))
		{
			echo "Filename: " . zip_entry_name($zipFile) . "<br>\n";
			echo "Real Size: " . zip_entry_filesize($zipFile) . "<br>\n";
			$name = zip_entry_name($zipFile);
			if ('word/media/image2.png' == '/word/media/'.$name) {
					// image est trouvé, il reste à la remplacer
					echo 'trouvé';
			//		$this->_objZip->deleteName($name);
			//		$this->_objZip->addFile($name,$image);
					
			}
			
		}
	}
	public function	test_ajout () {
		$objZip = new ZipArchive();
		$ret = $objZip->open(__dir__.'/zip.docx');
		$this->assertTrue($ret);
		$file = __dir__.'/first-image1-2.png';
		$this->assertTrue(file_exists($file),'existence du fichier');
		
		$ret = $objZip->addFile($file,'word/media/image.png');
		
		
		$this->assertTrue($ret);
		echo 'ajout de image dans le zip.docx : '.$ret;
		$objZip->close();
			
	}
}	