<?php

class Application_Model_Contact extends Zend_Db_Table_Row_Abstract 
{
	function __get($key)
	{
		if(method_exists($this, $key))
		{
			return $this->$key();
		}
		return parent::__get($key);
	}
	


}

