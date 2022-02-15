<?php

namespace Pastell\Tests\Service\Pack;

use Pastell\Service\Pack\PackService;
use PastellTestCase;

class PackServiceTest extends PastellTestCase
{
    protected function tearDown()
    {
        $this->setListPack(["pack_chorus_pro" => true, "pack_marche" => true, "pack_test" => true]);
    }

    public function testNoRestrictionPack()
    {
        $restriction_pack = [];
        $this->setListPack(["pack_test" => false]);
        $packService = $this->getObjectInstancier()->getInstance(PackService::class);
        $this->assertTrue($packService->hasOneOrMorePackEnabled($restriction_pack));
    }

    public function testHasRestrictionPackWithDisabledPack()
    {
        $restriction_pack = ['pack_chorus_pro', 'pack_marche', 'pack_test'];
        $this->setListPack(["pack_chorus_pro" => false, "pack_marche" => false, "pack_test" => false]);
        $packService = $this->getObjectInstancier()->getInstance(PackService::class);
        $this->assertFalse($packService->hasOneOrMorePackEnabled($restriction_pack));
    }

    public function testHasRestrictionPackWithEnabledPack()
    {
        $restriction_pack = ['pack_chorus_pro', 'pack_marche', 'pack_test'];
        $this->setListPack(["pack_chorus_pro" => false, "pack_marche" => false, "pack_test" => true]);
        $packService = $this->getObjectInstancier()->getInstance(PackService::class);
        $this->assertTrue($packService->hasOneOrMorePackEnabled($restriction_pack));
    }
}
