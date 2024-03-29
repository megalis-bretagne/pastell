<?php


class ObjectInstancier {

	private $objects;
		
	public function __construct(){
		$this->objects = array('ObjectInstancier' => $this);
	}
	
	public function __get($name){
		return $this->getInstance($name);
	}
	
	public function __set($name,$value){
		$this->setInstance($name,$value);
	}

	public function getInstance($class_name){
		if (! isset($this->objects[$class_name])){
			$this->objects[$class_name] =  $this->newInstance($class_name);
		}
		return $this->objects[$class_name];
	}

	public function setInstance($class_name,$value){
		$this->objects[$class_name] = $value;
	}

	public function newInstance($className){
		try {
			$reflexionClass = new ReflectionClass($className);
			if (! $reflexionClass->hasMethod('__construct')){
				return $reflexionClass->newInstance();
			}
			$constructor = $reflexionClass->getMethod('__construct');
			$allParameters = $constructor->getParameters();
			$param = $this->bindParameters($allParameters,$className);
			return $reflexionClass->newInstanceArgs($param);
		} catch (Exception $e){			
			throw new Exception("En essayant d'inclure $className : {$e->getMessage()}",0,$e);
		}
	}
	
	private function bindParameters(array $allParameters,$className){
		$param = array();
		/** @var ReflectionParameter $parameters */
		foreach($allParameters as $parameters){
        	$param_name = $parameters->getClass() ? $parameters->getClass()->name : $parameters->name;
        	$bind_value = $this->$param_name;
        	if (! $bind_value ) {
        		if ($parameters->isOptional()){
        			return $param;
        		}
        		throw new Exception("Impossible d'instancier $className car le paramètre {$parameters->name} est manquant");
        	}
        	$param[] = $bind_value;
        }
        return $param;
	}
}