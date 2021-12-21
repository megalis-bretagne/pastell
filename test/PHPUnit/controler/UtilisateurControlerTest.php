<?php

class UtilisateurControlerTest extends ControlerTestCase
{
    /**
     * @return UtilisateurControler
     */
    private function getUtilisateurControler()
    {
        return $this->getControlerInstance(UtilisateurControler::class);
    }

    /**
     * @throws LastErrorException
     */
    public function testDoEditionAction()
    {
        $this->setPostInfo([
            'login' => 'foo',
            'password' => 'bar',
            'password2' => 'bar',
            'nom' => 'baz',
            'prenom' => 'buz',
            'email' => 'boz@byz.fr'
        ]);

        try {
            $this->getUtilisateurControler()->doEditionAction();
        } catch (LastMessageException $e) {
            /** Nothing to do */
        }

        $utilisateurSQL = $this->getObjectInstancier()->getInstance(UtilisateurSQL::class);

        $this->assertArraySubset(
            array (
                'id_u' => '3',
                'email' => 'boz@byz.fr',
                'login' => 'foo',
                'mail_verifie' => '1',
                'nom' => 'baz',
                'prenom' => 'buz',
                'certificat' => '',
                'certificat_verif_number' => '',
                'id_e' => '0',
            ),
            $utilisateurSQL->getInfo(3)
        );

        $this->assertTrue($utilisateurSQL->verifPassword(3, "bar"));
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function testWhenPassword2IsNotSet()
    {
        $this->setPostInfo([
            'login' => 'foo',
            'password' => 'bar',
            'password2' => '',
            'nom' => 'baz',
            'prenom' => 'buz',
            'email' => 'boz@byz.fr'
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Les mots de passe ne correspondent pas");
        $this->getUtilisateurControler()->doEditionAction();
    }


    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws NotFoundException
     */
    public function testModifPasswordAction()
    {
        $this->getUtilisateurControler()->_beforeAction();
        $this->getUtilisateurControler()->modifPasswordAction();
        $this->expectOutputRegex('#<h3 data-toggle="collapse" data-target="\#collapse-0" aria-expanded="false" aria-controls="collapse-0">Administration</h3>#');
    }
}
