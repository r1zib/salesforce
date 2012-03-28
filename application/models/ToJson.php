<?php

/* 
 * Class pour créer un Json  
 */


class Application_Model_ToJson 

{
	private $_info = array();
	
	public function assign($field, $value = null) {
		$this->_info[$field] = $value;
	}

	public function assignImage($field,$url, $rep = null) {
		$this->_info[$field] = $url;
	}
	/**
	* @return array
	*/
	public function getInfo()
	{
	    return $this->_info;
	}

}

