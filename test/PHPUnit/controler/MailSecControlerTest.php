<?php

use Pastell\Service\Droit\DroitService;

class MailSecControlerTest extends PastellTestCase
{
    protected function getMockObject($class_name)
    {
        return $this->createMock($class_name);
    }

    private function getMailSecControler()
    {
        $objectInstancier = new ObjectInstancier();

        $roleUtilisateur = $this->getMockObject(RoleUtilisateur::class);
        $roleUtilisateur->method("hasDroit")->willReturn(true);
        $objectInstancier->setInstance(RoleUtilisateur::class, $roleUtilisateur);

        $entiteSQL = $this->getMockObject(EntiteSQL::class);
        $entiteSQL->method("getAncetre")->willReturn([]);
        $objectInstancier->setInstance(EntiteSQL::class, $entiteSQL);

        $documentTypeFactory = $this->getMockObject(DocumentTypeFactory::class);
        $documentTypeFactory->method("getAllType")->willReturn([]);
        $objectInstancier->setInstance(DocumentTypeFactory::class, $documentTypeFactory);

        $droitService = $this->getMockObject(DroitService::class);
        $droitService->method("hasDroit")->willReturn(true);
        $objectInstancier->setInstance(DroitService::class, $droitService);

        $manifestReader = $this->getMockObject(ManifestReader::class);

        $manifestFactory = $this->getMockObject(ManifestFactory::class);
        $manifestFactory->method("getPastellManifest")->willReturn($manifestReader);
        $objectInstancier->setInstance(ManifestFactory::class, $manifestFactory);


        $daemonManager = $this->getMockObject(DaemonManager::class);
        $objectInstancier->setInstance(DaemonManager::class, $daemonManager);

        $annuaireSQL = $this->getMockObject(AnnuaireSQL::class);
        $annuaireSQL->method("getUtilisateurList")->willReturn([]);
        $objectInstancier->setInstance(AnnuaireSQL::class, $annuaireSQL);

        $sqlQuery = $this->getMockObject(SQLQuery::class);
        $objectInstancier->setInstance(SQLQuery::class, $sqlQuery);

        $gabarit = $this->getMockObject(Gabarit::class);
        $objectInstancier->setInstance(Gabarit::class, $gabarit);

        $formulaire = $this->getMockObject(Formulaire::class);

        $donneesFormulaire = $this->getMockObject(DonneesFormulaire::class);
        $donneesFormulaire->method("getFormulaire")->willReturn($formulaire);

        $donneesFormulaireFactory = $this->getMockObject(DonneesFormulaireFactory::class);
        $donneesFormulaireFactory->method("get")->willReturn($donneesFormulaire);

        $objectInstancier->setInstance(DonneesFormulaireFactory::class, $donneesFormulaireFactory);

        $documentEmail = $this->getMockObject(DocumentEmail::class);
        $documentEmail->method("getInfoFromKey")->willReturn(['id_d' => 42]);
        $objectInstancier->setInstance(DocumentEmail::class, $documentEmail);

        $journal = $this->getMockObject(Journal::class);
        $objectInstancier->setInstance(Journal::class, $journal);


        return new MailSecControler($objectInstancier);
    }

    public function testAnnuaire()
    {
        $mailseController = $this->getMailSecControler();
        $mailseController->annuaireAction();
        $view_parameter = $mailseController->getViewParameter();
        $this->assertEquals(0, $view_parameter['id_e']);
    }


    /**
     * @throws NotFoundException
     */
    public function testAnnuaireImport()
    {
        $mailsecController = $this->getMailSecControler();
        $mailsecController->importAction();
        $view_parameter = $mailsecController->getViewParameter();

        $this->assertSame(0, $view_parameter['id_e']);
        $this->assertSame('Annuaire global', $view_parameter['infoEntite']['denomination']);
    }
}
