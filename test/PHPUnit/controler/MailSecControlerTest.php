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
        $objectInstancier->{'RoleUtilisateur'} = $roleUtilisateur;

        $entiteSQL = $this->getMockObject(EntiteSQL::class);
        $entiteSQL->method("getAncetre")->willReturn(array());
        $objectInstancier->{'EntiteSQL'} = $entiteSQL;

        $documentTypeFactory = $this->getMockObject(DocumentTypeFactory::class);
        $documentTypeFactory->method("getAllType")->willReturn(array());
        $objectInstancier->{'DocumentTypeFactory'} = $documentTypeFactory;

        $droitService = $this->getMockObject(DroitService::class);
        $droitService->method("hasDroit")->willReturn(true);
        $objectInstancier->setInstance(DroitService::class, $droitService);

        $manifestReader = $this->getMockObject(ManifestReader::class);

        $manifestFactory = $this->getMockObject(ManifestFactory::class);
        $manifestFactory->method("getPastellManifest")->willReturn($manifestReader);
        $objectInstancier->{'ManifestFactory'} = $manifestFactory;


        $daemonManager = $this->getMockObject(DaemonManager::class);
        $objectInstancier->{'DaemonManager'} = $daemonManager;

        $annuaireSQL  = $this->getMockObject(AnnuaireSQL::class);
        $annuaireSQL->method("getUtilisateurList")->willReturn(array());
        $objectInstancier->{'AnnuaireSQL'} = $annuaireSQL;

        $sqlQuery = $this->getMockObject(SQLQuery::class);
        $objectInstancier->{'SQLQuery'} = $sqlQuery;

        $gabarit = $this->getMockObject(Gabarit::class);
        $objectInstancier->{'Gabarit'} = $gabarit;

        $formulaire = $this->getMockObject(Formulaire::class);

        $donneesFormulaire = $this->getMockObject(DonneesFormulaire::class);
        $donneesFormulaire->method("getFormulaire")->willReturn($formulaire);

        $donneesFormulaireFactory = $this->getMockObject(DonneesFormulaireFactory::class);
        $donneesFormulaireFactory->method("get")->willReturn($donneesFormulaire);

        $objectInstancier->{'DonneesFormulaireFactory'} = $donneesFormulaireFactory;

        $documentEmail = $this->getMockObject(DocumentEmail::class);
        $documentEmail->method("getInfoFromKey")->willReturn(array('id_d' => 42));
        $objectInstancier->{'DocumentEmail'} = $documentEmail;

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
