<?php

class Application_Model_LiveDocs extends Zend_Service_LiveDocx_MailMerge

{
	
	/**
	* Assign values to template fields
	*
	* @param array|string $field
	* @param array|string $value
	* @return Zend_Service_LiveDocx_MailMerge
	* @throws Zend_Service_LiveDocx_Exception
	* @since  LiveDocx 1.0
	*/
	public function assign($field, $value = null) {
	
		//echo 'assign '. $field. ' = '.$value .  "\n"  ;
		parent::assign($field, $value);
	}
	
	
	
	
}

