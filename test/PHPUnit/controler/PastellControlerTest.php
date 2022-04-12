<?php

class PastellControlerTest extends ControlerTestCase
{
    public function testsetNavigationInfo()
    {
        $pastellControler = $this->getControlerInstance(PastellControler::class);
        $pastellControler->setNavigationInfo(0, 'test');
        $this->assertCount(1, $pastellControler->getViewParameterOrObject('navigation'));
        $this->assertEquals(
            "Bourg-en-Bresse",
            $pastellControler->getViewParameterOrObject('navigation')[0]['children'][0]['denomination']
        );
    }

    public function testsetNavigationInfoSubNav()
    {
        $pastellControler = $this->getControlerInstance(PastellControler::class);
        $pastellControler->setNavigationInfo(1, 'test');
        $this->assertCount(2, $pastellControler->getViewParameterOrObject('navigation'));
        $this->assertEquals(
            "Bourg-en-Bresse",
            $pastellControler->getViewParameterOrObject('navigation')[1]['name']
        );
    }

    public function testsetNavigationInfoSubSubNav()
    {
        $pastellControler = $this->getControlerInstance(PastellControler::class);
        $pastellControler->setNavigationInfo(2, 'test');
        $this->assertCount(3, $pastellControler->getViewParameterOrObject('navigation'));
        $this->assertEquals(
            "CCAS",
            $pastellControler->getViewParameterOrObject('navigation')[2]['name']
        );
    }

    public function testSetNavigationWhenUserHasNoEntiteLectureRight()
    {
        $entiteCreator = $this->getObjectInstancier()->getInstance(EntiteCreator::class);
        $entiteCreator->edit(0, "000000000", "Nouvelle entité");

        $this->authenticateNewUserWithPermission(["helios-generique:edition"], 1);

        $pastellControler = $this->getObjectInstancier()->getInstance(PastellControler::class);

        $pastellControler->setNavigationInfo(0, 'test');
        $this->assertCount(1, $pastellControler->getViewParameterOrObject('navigation')[0]['children']);
    }

    public function testSetNavigationWhenUserHasNoRightAtAll()
    {
        $entiteCreator = $this->getObjectInstancier()->getInstance(EntiteCreator::class);
        $entiteCreator->edit(0, "000000000", "Nouvelle entité");

        $this->authenticateNewUserWithPermission(["helios-generique:edition"], 1);

        $pastellControler = $this->getObjectInstancier()->getInstance(PastellControler::class);

        $pastellControler->setNavigationInfo(0, 'test');
        $this->assertCount(1, $pastellControler->getViewParameterOrObject('navigation')[0]['children']);
    }

    public function testSetNavigationWhenUserHasNoRightOnSecondLevel()
    {
        $entiteCreator = $this->getObjectInstancier()->getInstance(EntiteCreator::class);
        $id_e_fille = $entiteCreator->edit(
            0,
            "000000000",
            "Nouvelle entité",
            Entite::TYPE_COLLECTIVITE,
            2
        );
        $id_e_fille2 = $entiteCreator->edit(
            0,
            "000000000",
            "Nouvelle entité 2",
            Entite::TYPE_COLLECTIVITE,
            2
        );

        $this->authenticateNewUserWithPermission(["helios-generique:edition"], $id_e_fille);

        $pastellControler = $this->getObjectInstancier()->getInstance(PastellControler::class);
        $pastellControler->setNavigationInfo($id_e_fille, 'test');
        $this->assertCount(1, $pastellControler->getViewParameterOrObject('navigation')[1]['same_level_entities']);
        $this->assertEquals(
            "Nouvelle entité",
            $pastellControler->getViewParameterOrObject('navigation')[1]['same_level_entities'][0]['denomination']
        );
    }
}
