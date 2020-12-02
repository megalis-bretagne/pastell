<?php

namespace Pastell\Tests\Service\Droit;

use Pastell\Service\Droit\DroitService;
use PastellTestCase;
use RoleSQL;

class DroitServiceTest extends PastellTestCase
{
    public function tearDown()
    {
        $this->setListPack(["pack_test" => true]);
    }

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

    public function testPackEnableDroit()
    {
        $droitService = $this->getObjectInstancier()->getInstance(DroitService::class);
        $droit_test_lecture = $droitService->getDroitLecture("test");

        $this->setListPack(["pack_test" => false]);
        $this->assertTrue($droitService->isRestrictedDroit($droit_test_lecture));
        $this->assertFalse($droitService->hasDroit(1, $droit_test_lecture, 1));
        $this->assertFalse($droitService->hasOneDroit(1, $droit_test_lecture));
        $this->assertFalse(in_array("test", $droitService->getAllDocumentLecture(1, 1)));
        $this->assertFalse(in_array($droit_test_lecture, $droitService->getAllDroitEntite(1, 1)));
        $this->assertFalse(in_array($droit_test_lecture, $droitService->getAllDroit(1)));

        $this->setListPack(["pack_test" => true]);
        $this->assertFalse($droitService->isRestrictedDroit($droit_test_lecture));
        $this->assertTrue($droitService->hasDroit(1, $droit_test_lecture, 1));
        $this->assertTrue($droitService->hasOneDroit(1, $droit_test_lecture));
        $this->assertTrue(in_array("test", $droitService->getAllDocumentLecture(1, 1)));
        $this->assertTrue(in_array($droit_test_lecture, $droitService->getAllDroitEntite(1, 1)));
        $this->assertTrue(in_array($droit_test_lecture, $droitService->getAllDroit(1)));
    }
}
