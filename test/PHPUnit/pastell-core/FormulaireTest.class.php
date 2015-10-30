<?php

require_once __DIR__.'/../init.php';

class FormulaireTest extends PHPUnit_Framework_TestCase {


	public function testGetField(){
		$formulaire = new Formulaire(array("onglet1"=>false));
		$this->assertFalse($formulaire->getField("bar"));
	}

	public function testGetFieldInOnglet(){
		$formulaire = new Formulaire(array("onglet1"=>false));
		$this->assertFalse($formulaire->getField("foo","onglet2"));
	}

	public function testGetFieldOk(){
		$formulaire = new Formulaire(array("onglet1"=>array("foo"=>array())));
		$this->assertInstanceOf("Field",$formulaire->getField("foo"));
	}

	public function testGetFieldOngletOk(){
		$formulaire = new Formulaire(array("onglet1"=>array("foo"=>array())));
		$this->assertInstanceOf("Field",$formulaire->getField("foo","onglet1"));
	}

}