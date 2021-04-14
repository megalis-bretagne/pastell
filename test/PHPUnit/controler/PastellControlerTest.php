<?php

class PastellControlerTest extends ControlerTestCase
{
    public function testsetNavigationInfo()
    {
        $pastellControler = $this->getControlerInstance(PastellControler::class);
        $pastellControler->setNavigationInfo(0, 'test');
        $this->assertCount(1, $pastellControler->navigation);
        $this->assertEquals(
            "Bourg-en-Bresse",
            $pastellControler->navigation[0]['children'][0]['denomination']
        );
    }

    public function testsetNavigationInfoSubNav()
    {
        $pastellControler = $this->getControlerInstance(PastellControler::class);
        $pastellControler->setNavigationInfo(1, 'test');
        $this->assertCount(2, $pastellControler->navigation);
        $this->assertEquals(
            "Bourg-en-Bresse",
            $pastellControler->navigation[1]['name']
        );
    }

    public function testsetNavigationInfoSubSubNav()
    {
        $pastellControler = $this->getControlerInstance(PastellControler::class);
        $pastellControler->setNavigationInfo(2, 'test');
        $this->assertCount(3, $pastellControler->navigation);
        $this->assertEquals(
            "CCAS",
            $pastellControler->navigation[2]['name']
        );
    }

    public function testSetNavigationWhenUserHasNoEntiteLectureRight()
    {
        $entiteCreator = $this->getObjectInstancier()->getInstance(EntiteCreator::class);
        $entiteCreator->edit(0, "000000000", "Nouvelle entité");

        $roleUtilisateur = $this->getObjectInstancier()->getInstance(RoleUtilisateur::class);
        $roleUtilisateur->removeAllRole(2);
        $roleUtilisateur->addRole(2, "utilisateur", 1);

        $roleSQL = $this->getObjectInstancier()->getInstance(RoleSQL::class);
        $roleSQL->addDroit('utilisateur', 'helios-generique:edition');

        $this->getObjectInstancier()->getInstance(Authentification::class)->Connexion('eric', 2);
        $pastellControler = $this->getObjectInstancier()->getInstance(PastellControler::class);

        $pastellControler->setNavigationInfo(0, 'test');
        $this->assertCount(1, $pastellControler->navigation[0]['children']);
    }

    public function testSetNavigationWhenUserHasNoRightAtAll()
    {
        $entiteCreator = $this->getObjectInstancier()->getInstance(EntiteCreator::class);
        $entiteCreator->edit(0, "000000000", "Nouvelle entité");

        $roleUtilisateur = $this->getObjectInstancier()->getInstance(RoleUtilisateur::class);
        $roleUtilisateur->removeAllRole(2);
        $roleUtilisateur->addRole(2, "utilisateur", 1);

        $this->getObjectInstancier()->getInstance(Authentification::class)->Connexion('eric', 2);
        $pastellControler = $this->getObjectInstancier()->getInstance(PastellControler::class);

        $pastellControler->setNavigationInfo(0, 'test');
        $this->assertEmpty($pastellControler->navigation);
    }
}
