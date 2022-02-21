<?php

class UtilisateurNewEmailSQLTest extends PastellTestCase
{
    /** @var  UtilisateurNewEmailSQL */
    private $utilisateurNewEmailSQL;

    protected function setUp(): void
    {
        parent::setUp();
        $this->utilisateurNewEmailSQL = new UtilisateurNewEmailSQL($this->getSQLQuery(), new PasswordGenerator());
    }

    private function add()
    {
        return $this->utilisateurNewEmailSQL->add(1, "newmail@pastell.com");
    }

    private function listTable()
    {
        return $this->getSQLQuery()->query("SELECT * FROM utilisateur_new_email");
    }

    public function testAdd()
    {
        $this->add();
        $result = $this->listTable();
        $this->assertEquals("newmail@pastell.com", $result[0]['email']);
    }

    public function testConfirm()
    {
        $password = $this->add();
        $this->assertEquals("newmail@pastell.com", $this->utilisateurNewEmailSQL->confirm($password)['email']);
    }

    public function testDelete()
    {
        $this->add();
        $this->utilisateurNewEmailSQL->delete(1);
        $this->assertEmpty($this->listTable());
    }
}
