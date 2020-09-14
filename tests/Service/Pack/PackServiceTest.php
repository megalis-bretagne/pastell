<?php

namespace Pastell\Tests\Service\Pack;

use Pastell\Service\Pack\PackService;
use PastellTestCase;

class PackServiceTest extends PastellTestCase
{
    public function testNoRestrictionPack()
    {
        $restriction_pack = [];
        $packService = $this->getObjectInstancier()->getInstance(PackService::class);
        $this->assertTrue($packService->hasOneOrMorePackEnabled($restriction_pack));
    }

    public function testHasRestrictionPackWithNoEnabledPack()
    {
        $restriction_pack = ['pack_chorus_pro', 'pack_marche'];
        $packService = $this->getObjectInstancier()->getInstance(PackService::class);
        $this->assertFalse($packService->hasOneOrMorePackEnabled($restriction_pack));
    }

    public function testHasRestrictionPackWithEnabledPack()
    {
        $restriction_pack = ['pack_chorus_pro', 'pack_marche'];
        $this->getObjectInstancier()->setInstance('enable_pack_chorus_pro', true);
        $packService = $this->getObjectInstancier()->getInstance(PackService::class);
        $this->assertTrue($packService->hasOneOrMorePackEnabled($restriction_pack));
    }

    public function testGetListEnabledPack()
    {
        $this->getObjectInstancier()->setInstance('enable_pack_chorus_pro', true);
        $this->getObjectInstancier()->setInstance('enable_pack_marche', true);
        $packService = $this->getObjectInstancier()->getInstance(PackService::class);
        $expected_list_enabled_pack = ['pack_chorus_pro', 'pack_marche'];
        $this->assertEquals($expected_list_enabled_pack, $packService->getListEnabledPack());
    }
}
