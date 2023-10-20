<?php

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Pastell\Client\IparapheurV5\ClientFactory;
use Psr\Http\Client\ClientInterface;

class TenantNameActionTest extends PastellTestCase
{
    private function mockParapheur()
    {
        $clientInterface = $this->getMockBuilder(ClientInterface::class)->getMock();

        $clientInterface->method('sendRequest')
            ->willReturnCallback(function (Request $request): Response {
                return match ($request->getUri()->getPath()) {
                    '/auth/realms/api/protocol/openid-connect/token' => new Response(
                        200,
                        ['Content-type' => 'application/json'],
                        file_get_contents(__DIR__ . "/../fixtures/authenticate_ok.json")
                    ),
                    '/api/v1/tenant' => new Response(
                        200,
                        ['Content-type' => 'application/json'],
                        file_get_contents(__DIR__ . "/../fixtures/tenant_list.json")
                    ),
                    default => throw new UnrecoverableException("Unknown path"),
                };
            });


        /** @var ClientFactory $clientFactory */
        $clientFactory = $this->getObjectInstancier()->getInstance(ClientFactory::class);
        $clientFactory->setClientInterface($clientInterface);
    }

    public function testGo(): void
    {
        $this->mockParapheur();
        $id_ce = $this->createConnector('recup-parapheur-corbeille', 'Recup parapheur')['id_ce'];
        $this->configureConnector($id_ce, ['url' => 'https://aaaa.bbb', 'pastell_module_id' => 'ls-recup-parapheur']);
        $_POST = ['tenant_id' => 'bc75c516-7fa6-4edd-8a3e-9318d3263996'];
        $this->triggerActionOnConnector($id_ce, 'tenant_name');
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($id_ce);
        static::assertEquals('bc75c516-7fa6-4edd-8a3e-9318d3263996', $donneesFormulaire->get('tenant_id'));
        static::assertEquals('Pastell 2', $donneesFormulaire->get('tenant_name'));
    }
    public function testGoWhenTenantNotExists(): void
    {
        $this->mockParapheur();
        $id_ce = $this->createConnector('recup-parapheur-corbeille', 'recup-parapheur-corbeille')['id_ce'];
        $this->configureConnector($id_ce, ['url' => 'https://aaaa.bbb', 'pastell_module_id' => 'ls-recup-parapheur']);
        $_POST = ['tenant_id' => 12];
        $this->triggerActionOnConnector($id_ce, 'tenant_name');
        $this->assertLastMessage("Cet élément n'existe pas");
    }

    public function testDisplay(): void
    {
        $this->mockParapheur();
        $id_ce = $this->createConnector('recup-parapheur-corbeille', 'recup-parapheur-corbeille')['id_ce'];
        $this->configureConnector($id_ce, ['url' => 'https://aaaa.bbb', 'pastell_module_id' => 'ls-recup-parapheur']);
        $actionExecutorFatory = $this->getObjectInstancier()->getInstance(ActionExecutorFactory::class);
        $this->expectOutputRegex("#<select name='tenant_id'#");
        $actionExecutorFatory->displayChoiceOnConnecteur($id_ce, 1, "tenant_name", "tenant_name", false);
    }
}
