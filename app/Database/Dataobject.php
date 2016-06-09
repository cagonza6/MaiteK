<?php

namespace App\Database;
/**
 * Objectifies a given object.
 */
class Dataobject {
	/**
	 * Storage object.
	 *
	 * @access protected
	 * @var array
	 */
	protected $_data = array();

	public function __construct(array $data = null, $defaults = array()){
	}

	public function fromArray(){
		return $this->_data;
	}

	public function __set($prop, $value)
	{
		$this->_data[$prop] = $value;
		return $value;
	}
	
	public function __get($prop)
	{
		if (isset($this->_data[$prop])) {
			return $this->_data[$prop];
		}
		else {
			return null;
		}
	}
}
?>
