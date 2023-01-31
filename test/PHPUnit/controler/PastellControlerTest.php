<?php

use Pastell\Service\Entite\EntityCreationService;

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

    /**
     * @throws UnrecoverableException
     */
    public function testSetNavigationWhenUserHasNoEntiteLectureRight(): void
    {
        $entityCreationService = $this->getObjectInstancier()->getInstance(EntityCreationService::class);
        $entityCreationService->create('Nouvelle entité', '000000000');

        $this->authenticateNewUserWithPermission(['helios-generique:edition'], 1);

        $pastellControler = $this->getObjectInstancier()->getInstance(PastellControler::class);

        $pastellControler->setNavigationInfo(0, 'test');
        static::assertCount(1, $pastellControler->getViewParameterByKey('navigation')[0]['children']);
    }

    /**
     * @throws UnrecoverableException
     */
    public function testSetNavigationWhenUserHasNoRightAtAll(): void
    {
        $entityCreationService = $this->getObjectInstancier()->getInstance(EntityCreationService::class);
        $entityCreationService->create('Nouvelle entité', '000000000');

        $this->authenticateNewUserWithPermission(['helios-generique:edition'], 1);

        $pastellControler = $this->getObjectInstancier()->getInstance(PastellControler::class);

        $pastellControler->setNavigationInfo(0, 'test');
        static::assertCount(1, $pastellControler->getViewParameterByKey('navigation')[0]['children']);
    }

    /**
     * @throws UnrecoverableException
     */
    public function testSetNavigationWhenUserHasNoRightOnSecondLevel(): void
    {
        $entityCreationService = $this->getObjectInstancier()->getInstance(EntityCreationService::class);
        $id_e_fille = $entityCreationService->create('Nouvelle entité', '000000000', EntiteSQL::TYPE_COLLECTIVITE, 2);

        $id_e_fille2 = $entityCreationService->create(
            'Nouvelle entité 2',
            '000000000',
            EntiteSQL::TYPE_COLLECTIVITE,
            2
        );

        $this->authenticateNewUserWithPermission(['helios-generique:edition'], $id_e_fille);

        $pastellControler = $this->getObjectInstancier()->getInstance(PastellControler::class);
        $pastellControler->setNavigationInfo($id_e_fille, 'test');
        static::assertCount(1, $pastellControler->getViewParameterByKey('navigation')[1]['same_level_entities']);
        static::assertEquals(
            'Nouvelle entité',
            $pastellControler->getViewParameterByKey('navigation')[1]['same_level_entities'][0]['denomination']
        );
    }
}
