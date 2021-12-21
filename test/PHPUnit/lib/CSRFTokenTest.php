<?php

use PHPUnit\Framework\TestCase;

class CSRFTokenTest extends TestCase
{
    /** @var  CSRFToken */
    private $csrfToken;

    private $session = array();

    protected function setUp()
    {
        parent::setUp();
        $this->csrfToken = new CSRFToken();
        $this->csrfToken->setPostParameter(array());
        $this->csrfToken->setSession($this->session);
    }

    public function testDisplayInputForm()
    {
        $this->expectOutputRegex("#<input type=\"hidden\" name=\"csrf_token\" value=\".*\" />#");
        $this->csrfToken->displayFormInput();
    }

    /**
     * @throws Exception
     */
    public function testVerif()
    {
        $this->session[CSRFToken::TOKEN_NAME] = 'foo';
        $this->csrfToken->setPostParameter(array(CSRFToken::TOKEN_NAME => 'foo'));
        $this->assertTrue($this->csrfToken->verifToken());
    }

    /**
     * @throws Exception
     */
    public function testVerifFailed()
    {
        $this->session[CSRFToken::TOKEN_NAME] = 'foo';
        $this->csrfToken->setPostParameter(array(CSRFToken::TOKEN_NAME => 'bar'));
        $this->expectException("Exception");
        $this->expectExceptionMessage("Votre session n'était plus valide.");
        $this->csrfToken->verifToken();
    }

    /**
     * @throws Exception
     */
    public function testDeleteToken()
    {
        $this->session[CSRFToken::TOKEN_NAME] = 'foo';
        $this->csrfToken->setPostParameter(array(CSRFToken::TOKEN_NAME => 'foo'));
        $this->csrfToken->deleteToken();
        $this->expectException("Exception");
        $this->expectExceptionMessage("Votre session n'était plus valide.");
        $this->csrfToken->verifToken();
    }
}
