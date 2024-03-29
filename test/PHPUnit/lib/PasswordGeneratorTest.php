<?php

class PasswordGeneratorTest extends PHPUnit\Framework\TestCase {

	public function testGetPassword(){
		$passwordGenerator = new PasswordGenerator();
		$password = $passwordGenerator->getPassword();
		$this->assertEquals(PasswordGenerator::NB_SIGNE_DEFAULT,mb_strlen($password));
		$this->assertRegExp("/^[".PasswordGenerator::SIGNE."]*$/",$password);
	}

	public function testSetPasswordLength(){
		$passwordGenerator = new PasswordGenerator();
		$password = $passwordGenerator->getPassword();
		$this->assertEquals(PasswordGenerator::NB_SIGNE_DEFAULT,mb_strlen($password));
	}

	public function testSetSignePassword(){
		$passwordGenerator = new PasswordGenerator();
		$passwordGenerator->setSigne("X");
		$password = $passwordGenerator->getPassword();
		$this->assertEquals("XXXXXXX",$password);
	}
}