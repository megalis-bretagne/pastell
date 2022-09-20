<?php

declare(strict_types=1);

namespace Pastell\Tests\Bootstrap;

use Exception;
use Pastell\Bootstrap\InstallResult;
use Pastell\Bootstrap\SEDAGeneratorInstall;
use Pastell\Service\Connecteur\ConnecteurCreationService;
use PastellTestCase;
use SedaGenerique;

class SEDAGeneratorInstallTest extends PastellTestCase
{
    /**
     * @throws Exception
     */
    public function testInstall(): void
    {
        $connecteurCreationService = $this->getObjectInstancier()->getInstance(
            ConnecteurCreationService::class
        );
        self::assertFalse($connecteurCreationService->hasConnecteurGlobal(SedaGenerique::CONNECTEUR_GLOBAL_TYPE));
        $sedaGeneratorInstall = $this->getObjectInstancier()->getInstance(SEDAGeneratorInstall::class);
        self::assertEquals(InstallResult::InstallOk, $sedaGeneratorInstall->install());
        self::assertTrue($connecteurCreationService->hasConnecteurGlobal(SedaGenerique::CONNECTEUR_GLOBAL_TYPE));
    }

    /**
     * @throws Exception
     */
    public function testReinstall(): void
    {
        $this->testInstall();
        $sedaGeneratorInstall = $this->getObjectInstancier()->getInstance(SEDAGeneratorInstall::class);
        self::assertEquals(InstallResult::NothingToDo, $sedaGeneratorInstall->install());
    }

    public function testGetName(): void
    {
        $sedaGeneratorInstall = $this->getObjectInstancier()->getInstance(SEDAGeneratorInstall::class);
        self::assertNotEmpty($sedaGeneratorInstall->getName());
    }
}
