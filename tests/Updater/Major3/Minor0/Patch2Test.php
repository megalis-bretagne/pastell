<?php

namespace Pastell\Tests\Updater\Major3\Minor0;

use Exception;
use FastParapheur;
use NotFoundException;
use Pastell\Updater\Major3\Minor0\Patch2;
use PastellTestCase;
use RoleSQL;
use TypeDossierLoader;
use UnrecoverableException;

class Patch2Test extends PastellTestCase
{
    private function getConnectorThroughApi(int $connectorId, int $entityId = self::ID_E_COL): array
    {
        return $this->getInternalAPI()->get("/entite/$entityId/connecteur/$connectorId");
    }

    /**
     * @throws NotFoundException
     */
    public function testReplaceFastParapheurUrl()
    {
        // TODO: move legacy autoload into composer
        require_once PASTELL_PATH . '/connecteur/fast-parapheur/FastParapheur.class.php';

        $connectorId = $this->createConnector('fast-parapheur', 'FAST PARAPHEUR')['id_ce'];
        $defaultUrl = 'https://test.tld';
        $this->configureConnector($connectorId, [
            'wsdl' => $defaultUrl . FastParapheur::WSDL_URI
        ]);
        $connector = $this->getConnectorThroughApi($connectorId);
        $this->assertSame($defaultUrl . FastParapheur::WSDL_URI, $connector['data']['wsdl']);

        $this->getObjectInstancier()->getInstance(Patch2::class)->update();

        $connector = $this->getConnectorThroughApi($connectorId);
        $this->assertSame($defaultUrl, $connector['data']['wsdl']);
    }

    /**
     * @throws NotFoundException
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function testBordereauName()
    {
        $typeDossier = 'arrete-rh';

        // Can't just use TypeDossierImportExport because we need the yaml to be generated in the workspace
        require_once PASTELL_PATH . '/test/PHPUnit/pastell-core/type-dossier/TypeDossierLoader.class.php';
        $typeDossierLoader = $this->getObjectInstancier()->getInstance(TypeDossierLoader::class);
        $typeDossierLoader->createTypeDossierFromFilepath(
            PASTELL_PATH . "/test/PHPUnit/pastell-core/type-dossier/fixtures/$typeDossier.json"
        );
        $this->getObjectInstancier()->getInstance(RoleSQL::class)->addDroit('admin', "$typeDossier:lecture");
        $this->getObjectInstancier()->getInstance(RoleSQL::class)->addDroit('admin', "$typeDossier:edition");

        $documentId = $this->createDocument($typeDossier)['id_d'];
        $document = $this->getDonneesFormulaireFactory()->get($documentId);
        $document->addFileFromData('bordereau', 'test.pdf', '%PDF1-4');

        $bordereau_signature = 'bordereau_signature';
        $this->assertFalse($document->get($bordereau_signature));

        $this->getObjectInstancier()->getInstance(Patch2::class)->update();

        $document = $this->getDonneesFormulaireFactory()->get($documentId);

        $this->assertFalse($document->get('bordereau'));
        $this->assertNotFalse($document->get($bordereau_signature));
        $this->assertSame('test.pdf', $document->getFileName($bordereau_signature));
    }
}
