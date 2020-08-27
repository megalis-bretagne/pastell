<?php

require_once __DIR__ . "/../pastell-core/type-dossier/TypeDossierLoader.class.php";

class ModificationActionTest extends PastellTestCase
{
    private const FILENAME_FIXTURE = 'foo.pdf';

    /**
     * @throws NotFoundException
     */
    public function testNoEditableContent()
    {
        $document = $this->createDocument('test');

        $this->configureDocument($document['id_d'], [
            'test2' => 'test required field'
        ]);

        $donnesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);
        $this->assertSame('test required field', $donnesFormulaire->get('test2'));

        $this->triggerActionOnDocument($document['id_d'], 'useless');

        $this->configureDocument($document['id_d'], [
            'test2' => 'test new value'
        ]);
        $donnesFormulaire = $this->getDonneesFormulaireFactory()->get($document['id_d']);
        $this->assertSame('test required field', $donnesFormulaire->get('test2'));
    }

    public function testDontRedirectOnAPICall()
    {
        $document = $this->createDocument('helios-generique');
        $this->expectOutputString("");
        $result = $this->getInternalAPI()->patch("/Entite/1/Document/{$document['id_d']}", ['envoi_sae' => 1]);
        $this->assertEquals(1, $result['content']['data']['envoi_sae']);
    }

    /**
     * @throws NotFoundException
     */
    public function testModifActionWhenFieldIsNotEditable()
    {
        $id_d = $this->createDocument('actes-generique')['id_d'];

        $actionChange = $this->getObjectInstancier()->getInstance(ActionChange::class);

        $actionChange->addAction($id_d, 1, 0, 'recu-iparapheur', 'test');

        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage("Le contenu de arrete n'est pas éditable");
        $this->getInternalAPI()->post(
            "/Entite/1/Document/$id_d/file/arrete",
            ['file_content' => 'test', 'file_name' => self::FILENAME_FIXTURE]
        );

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);

        $this->assertEmpty($donneesFormulaire->getFileContent('arrete'));
    }

    /**
     * @throws NotFoundException
     */
    public function testModifActionWhenFieldIsEditable()
    {
        $id_d = $this->createDocument('actes-generique')['id_d'];

        $this->getInternalAPI()->post(
            "/Entite/1/Document/$id_d/file/arrete",
            [
                'file_content' => file_get_contents(__DIR__ . "/../fixtures/vide.pdf"),
                'file_name' => self::FILENAME_FIXTURE
            ]
        );

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);

        $this->assertEquals(
            file_get_contents(__DIR__ . "/../fixtures/vide.pdf"),
            $donneesFormulaire->getFileContent('arrete')
        );
    }

    /**
     * @throws TypeDossierException
     */
    public function testWhenTitreFieldIsAfile()
    {
        $this->getObjectInstancier()
            ->getInstance(TypeDossierLoader::class)
            ->createTypeDossierFromFilepath(__DIR__ . "/fixtures/test-bug-1096.json");

        $id_d = $this->createDocument('test-bug-1096')['id_d'];

        $result = $this->getInternalAPI()->post(
            "/Entite/1/Document/$id_d/file/un_fichier",
            [
                'file_content' => 'bar',
                'file_name' => self::FILENAME_FIXTURE
            ]
        );
        $this->assertEquals(self::FILENAME_FIXTURE, $result['content']['info']['titre']);
        $this->getObjectInstancier()
            ->getInstance(TypeDossierLoader::class)->unload();
    }
}
