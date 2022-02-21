<?php

namespace Pastell\Tests\Service\Droit;

use Pastell\Service\Droit\DroitService;
use PastellTestCase;

class DroitServiceTest extends PastellTestCase
{
    protected function tearDown(): void
    {
        $this->setListPack(["pack_test" => true]);
    }

    public function testHasDroitConnecteur()
    {
        $droitService = $this->getObjectInstancier()->getInstance(DroitService::class);
        $this->assertTrue($droitService->hasDroitConnecteurEdition(1, 1));
        $this->assertTrue($droitService->hasDroitConnecteurLecture(1, 1));
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
        $this->assertTrue($droitService->isRestrictedConnecteur("test", true));
        $this->assertTrue($droitService->isRestrictedConnecteur("test"));
        $this->assertFalse($droitService->hasDroit(1, $droit_test_lecture, 1));
        $this->assertFalse($droitService->hasOneDroit(1, $droit_test_lecture));
        $this->assertFalse(in_array("test", $droitService->getAllDocumentLecture(1, 1)));
        $this->assertFalse(in_array($droit_test_lecture, $droitService->getAllDroitEntite(1, 1)));
        $this->assertFalse(in_array($droit_test_lecture, $droitService->getAllDroit(1)));

        $this->setListPack(["pack_test" => true]);
        $this->assertFalse($droitService->isRestrictedDroit($droit_test_lecture));
        $this->assertFalse($droitService->isRestrictedConnecteur("test", true));
        $this->assertFalse($droitService->isRestrictedConnecteur("test"));
        $this->assertTrue($droitService->hasDroit(1, $droit_test_lecture, 1));
        $this->assertTrue($droitService->hasOneDroit(1, $droit_test_lecture));
        $this->assertTrue(in_array("test", $droitService->getAllDocumentLecture(1, 1)));
        $this->assertTrue(in_array($droit_test_lecture, $droitService->getAllDroitEntite(1, 1)));
        $this->assertTrue(in_array($droit_test_lecture, $droitService->getAllDroit(1)));
    }
}
