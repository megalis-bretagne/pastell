<?php

class UtilisateurListTest extends PastellTestCase
{

    /**
     * @var UtilisateurListe
     */
    private $utilisateurListe;

    protected function setUp()
    {
        parent::setUp();
        $this->utilisateurListe = new UtilisateurListe($this->getSQLQuery());
    }

    public function testGetUtilisateurByLogin()
    {
        $this->assertEquals(1, $this->utilisateurListe->getUtilisateurByLogin('admin'));
    }

    public function testGtAllUtilisateurSimple()
    {
        $all = $this->utilisateurListe->getAllUtilisateurSimple();
        $this->assertEquals(1, $all[0]['id_u']);
    }

    public function testGetNbUtilisateurWithEntiteDeBase()
    {
        $this->assertEquals(
            2,
            $this->utilisateurListe->getNbUtilisateurWithEntiteDeBase(0)
        );
    }

    public function testGetNbUsersWithRoleThatDoesNotExist()
    {
        $this->assertSame(
            '0',
            $this->utilisateurListe->getNbUtilisateur(0, true, 'does not exist', 'eric')
        );
    }

    public function testgetByVerifPassword()
    {
        $utilisateurSQL = $this->getObjectInstancier()->getInstance(UtilisateurSQL::class);
        $utilisateurSQL->reinitPassword(1, "foo");
        $this->assertEquals(1, $this->utilisateurListe->getByVerifPassword('foo'));
    }

    public function testgetByVerifPasswordWhenFailed()
    {
        $utilisateurSQL = $this->getObjectInstancier()->getInstance(UtilisateurSQL::class);
        $utilisateurSQL->reinitPassword(1, "foo");
        $this->getSQLQuery()->query(
            "UPDATE utilisateur SET mail_verif_date = date_add(now(), INTERVAL -30 MINUTE) where id_u = ?",
            1
        );
        $this->assertEmpty(
            $this->utilisateurListe->getByVerifPassword('foo', 29 * 60)
        );
        $this->assertEquals(
            1,
            $this->utilisateurListe->getByVerifPassword('foo', 31 * 60)
        );
    }
}
