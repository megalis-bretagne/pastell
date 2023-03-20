<?php

declare(strict_types=1);

class HelpURLTest extends ControlerTestCase
{
    /**
     * @throws NotFoundException
     */
    public function testWithHelpURL(): void
    {
        $id_ce = $this->createConnector('help-url', "URL d'aide", 0)['id_ce'];
        $this->configureConnector($id_ce, ['help_url' => 'https://foo.bar/baz'], 0);
        $this->associateGlobalConnector((int)$id_ce);
        $this->getObjectInstancier()->getInstance(Authentification::class)->connexion('admin', 1);
        $aideController = $this->getControlerInstance(AideControler::class);
        $this->expectOutputRegex('#https://foo.bar/baz#');
        $aideController->RGPDAction();
    }

    /**
     * @return void
     * @throws NotFoundException
     */
    public function testWithoutHelpURL(): void
    {
        $this->getObjectInstancier()->getInstance(Authentification::class)->connexion('admin', 1);
        $aideController = $this->getControlerInstance(AideControler::class);
        ob_start();
        $aideController->RGPDAction();
        $result = ob_get_clean();
        self::assertStringNotContainsString('<span>Aide</span>', $result);
    }
}
