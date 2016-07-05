<?php

class CSRFTokenTest extends PHPUnit_Framework_TestCase {

	/** @var  CSRFToken */
	private $csrfToken;

	private $session = array();

	protected function setUp() {
		parent::setUp();
		$this->csrfToken = new CSRFToken();
		$this->csrfToken->setPostParameter(array());
		$this->csrfToken->setSession($this->session);
	}

	public function testDisplayInputForm(){
		$this->expectOutputRegex("#<input type=\"hidden\" name=\"csrf_token\" value=\".*\" />#");
		$this->csrfToken->displayFormInput();
	}

	public function testVerif(){
		$this->session[CSRFToken::TOKEN_NAME] = 'foo';
		$this->csrfToken->setPostParameter(array(CSRFToken::TOKEN_NAME => 'foo'));
		$this->assertTrue($this->csrfToken->verifToken());
	}

	public function testVerifFailed(){
		$this->session[CSRFToken::TOKEN_NAME] = 'foo';
		$this->csrfToken->setPostParameter(array(CSRFToken::TOKEN_NAME => 'bar'));
		$this->setExpectedException("Exception","Votre session n'était plus valide.");
		$this->csrfToken->verifToken();
	}

	public function testDeleteToken(){
		$this->session[CSRFToken::TOKEN_NAME] = 'foo';
		$this->csrfToken->setPostParameter(array(CSRFToken::TOKEN_NAME => 'foo'));
		$this->csrfToken->deleteToken();
		$this->setExpectedException("Exception","Votre session n'était plus valide.");
		$this->csrfToken->verifToken();
	}

}