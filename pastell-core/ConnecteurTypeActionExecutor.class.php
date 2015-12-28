<?php

abstract class ConnecteurTypeActionExecutor extends ActionExecutor {

	protected $mapping;

	public function setMapping(array $mapping){
		$this->mapping = $mapping;
	}

	public function getMappingValue($key){
		if (empty($this->mapping[$key])){
			return $key;
		}
		return $this->mapping[$key];
	}


}