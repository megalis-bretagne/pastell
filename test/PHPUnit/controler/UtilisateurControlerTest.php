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
        $this->assertEquals('boz@byz.fr', $utilisateurSQL->getInfo(3)['email']);
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
        $this->getObjectInstancier()->setInstance('password_min_entropy', 0);
        $this->getUtilisateurControler()->_beforeAction();
        $this->getUtilisateurControler()->modifPasswordAction();
        $this->expectOutputRegex('#<h1>Modification de votre mot de passe</h1#');
    }
}
