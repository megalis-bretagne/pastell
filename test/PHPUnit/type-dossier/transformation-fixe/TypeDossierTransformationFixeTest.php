<?php

declare(strict_types=1);

class TypeDossierTransformationFixeTest extends PastellTestCase
{
    public const STUDIO_TRANSFORMATION_FIXE = 'double-transformation-fixe';

    private TypeDossierLoader $typeDossierLoader;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->typeDossierLoader = $this->getObjectInstancier()->getInstance(TypeDossierLoader::class);
    }

    /**
     * @throws NotFoundException
     * @throws TypeDossierException
     */
    public function testEtapeTransformationFixe(): void
    {
        $this->typeDossierLoader->createTypeDossierDefinitionFile(self::STUDIO_TRANSFORMATION_FIXE);
        $info = $this->createDocument(self::STUDIO_TRANSFORMATION_FIXE);

        static::assertTrue(
            $this->triggerActionOnDocument($info['id_d'], 'orientation')
        );
        $this->assertLastMessage("sélection automatique de l'action suivante");

        $this->assertLastDocumentAction('preparation-transformation-fixe_1', $info['id_d']);
        static::assertTrue(
            $this->triggerActionOnDocument($info['id_d'], 'transformation-fixe_1')
        );
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info['id_d']);
        static::assertEquals(
            'objet transformé par fixe sur entité Bourg-en-Bresse',
            $donneesFormulaire->get('objet')
        );
        $this->assertLastDocumentAction('transformation-fixe_1', $info['id_d']);
        $this->assertLastMessage('Transformation fixe terminée');


        static::assertFalse(
            $this->triggerActionOnDocument($info['id_d'], 'transformation-fixe_2')
        );
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info['id_d']);
        static::assertEquals(
            'du texte en minuscule',
            $donneesFormulaire->get('champ_majuscule')
        );
        $this->assertLastDocumentAction('transformation-fixe-error_2', $info['id_d']);
        $this->assertLastMessage(
            "[transformation fixe] Le dossier n'est pas valide : Le champ «Champ en majuscule» est incorrect (Ce champ ne peut comporter que des majuscules) "
        );
    }
}
