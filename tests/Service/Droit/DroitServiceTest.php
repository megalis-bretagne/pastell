<?php

namespace Pastell\Tests\Service\Droit;

use Pastell\Service\Droit\DroitService;
use PastellTestCase;
use RoleSQL;

class DroitServiceTest extends PastellTestCase
{
    public function testHasDroitConnecteurLectureOldFashion()
    {
        $droitService = $this->getObjectInstancier()->getInstance(DroitService::class);
        $this->assertTrue($droitService->hasDroitConnecteurEdition(1, 1));
        $this->assertTrue($droitService->hasDroitConnecteurLecture(1, 1));
    }

    public function testHasDroitConnecteurLectureNoDroit()
    {
        $this->getObjectInstancier()->setInstance('connecteur_droit', true);
        $droitService = $this->getObjectInstancier()->getInstance(DroitService::class);
        $this->assertFalse($droitService->hasDroitConnecteurEdition(1, 1));
        $this->assertFalse($droitService->hasDroitConnecteurLecture(1, 1));
    }

    public function testHasDroitConnecteurLecture()
    {
        $roleSQL = $this->getObjectInstancier()->getInstance(RoleSQL::class);
        $roleSQL->addDroit('admin', 'connecteur:lecture');
        $roleSQL->addDroit('admin', 'connecteur:edition');
        $this->getObjectInstancier()->setInstance('connecteur_droit', true);
        $droitService = $this->getObjectInstancier()->getInstance(DroitService::class);
        $this->assertTrue($droitService->hasDroitConnecteurEdition(1, 1));
    }

    public function testHasDroitNoUser()
    {
        $droitService = $this->getObjectInstancier()->getInstance(DroitService::class);
        $this->assertTrue($droitService->hasDroitConnecteurEdition(1, 0));
    }

    public function testPackEnabledDroit()
    {
        $droitService = $this->getObjectInstancier()->getInstance(DroitService::class);

        $this->defineListPack(["pack_test" => false]);
        $this->assertTrue($droitService->isRestrictedDroit("test:lecture"));
        $this->assertFalse($droitService->hasDroit(1, "test:lecture", 1));
        $this->assertFalse($droitService->hasOneDroit(1, "test:lecture"));
        $this->assertFalse(in_array("test", $droitService->getAllDocumentLecture(1, 1)));
        $this->assertFalse(in_array("test:lecture", $droitService->getAllDroitEntite(1, 1)));
        $this->assertFalse(in_array("test:lecture", $droitService->getAllDroit(1)));

        $this->defineListPack(["pack_test" => true]);
        $this->assertFalse($droitService->isRestrictedDroit("test:lecture"));
        $this->assertTrue($droitService->hasDroit(1, "test:lecture", 1));
        $this->assertTrue($droitService->hasOneDroit(1, "test:lecture"));
        $this->assertTrue(in_array("test", $droitService->getAllDocumentLecture(1, 1)));
        $this->assertTrue(in_array("test:lecture", $droitService->getAllDroitEntite(1, 1)));
        $this->assertTrue(in_array("test:lecture", $droitService->getAllDroit(1)));
    }
}
