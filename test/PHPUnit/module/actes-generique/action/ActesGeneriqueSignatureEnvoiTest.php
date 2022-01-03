<?php

class ActesGeneriqueSignatureEnvoiTest extends PastellTestCase
{
    /**
     * @throws Exception
     */
    public function testCasNominal()
    {

        $soapClientFactory = $this->getMockBuilder('SoapClientFactory')->getMock();

        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();

        $soapClient->expects($this->any())
                ->method("__call")
                ->will($this->returnCallback(function ($called_function_name, $called_function_args) {

                    if ($called_function_name == "CreerDossier") {
                        $called_function_args[0]['DossierID'] = "NOT TESTABLE";
                        //var_export($called_function_args);
                        //file_put_contents(__DIR__."/../fixtures/actes-iparapheur-cree-dossier-input.json",json_encode($called_function_args));
                        $this->assertStringEqualsFile(__DIR__ . "/../fixtures/actes-iparapheur-cree-dossier-input.json", json_encode($called_function_args));

                        return json_decode(' {"MessageRetour":{"codeRetour":"OK","message":"Dossier 201812101413 Achat_de_libriciels soumis dans le circuit","severite":"INFO"}}');
                    } else {
                        throw new Exception("La méthode $called_function_name n'est pas prévu !");
                    }
                }));

        $soapClientFactory->expects($this->any())
            ->method('getInstance')
            ->willReturn($soapClient);

        $this->getObjectInstancier()->setInstance(SoapClientFactory::class, $soapClientFactory);


        $result = $this->getInternalAPI()->post(
            "/entite/1/connecteur/",
            ['libelle' => 'i-Parapheur', 'id_connecteur' => 'iParapheur']
        );
        $id_ce = $result['id_ce'];


        $this->getInternalAPI()->patch("/entite/1/connecteur/$id_ce/content", [
            'iparapheur_wsdl' => 'https://wsdl_du_parapheur/',
            'iparapheur_metadata' => 'acte_nature:acte_nature_pour_ip'
        ]);

        $this->getInternalAPI()->post(
            "/entite/1/flux/actes-generique/connecteur/$id_ce",
            ['type' => 'signature']
        );



        $result = $this->getInternalAPI()->post(
            "/Document/1",
            array('type' => 'actes-generique')
        );
        $id_d = $result['id_d'];


        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);

        $donneesFormulaire->addFileFromData("arrete", "ma_deliberation.pdf", "le contenu de ma délibération");

        $donneesFormulaire->addFileFromData("autre_document_attache", "ma_premiere_annexe.pdf", "Le contenu de ma première annexe", 0);
        $donneesFormulaire->addFileFromData("autre_document_attache", "ma_seconde_annexe.pdf", "Le contenu de ma seconde annexe", 1);

        $donneesFormulaire->addFileFromData('type_piece_fichier', 'ok', 'ok');

        $result = $this->getInternalAPI()->patch("/entite/1/document/$id_d", [
            'envoi_signature' => 1,
            'acte_nature' => '1',
            'numero_de_lacte' => '201812101413',
            'objet' => 'Achat de libriciels',
            'date_de_lacte' => '2018-12-10',
            'classification' => '1.1',
            'iparapheur_type' => 'ACTES',
            'iparapheur_sous_type' => 'DELIBERATION',
            'type_piece' => 'ok',
        ]);
        $this->assertEquals(1, $result['formulaire_ok']);

        $this->getObjectInstancier()->getInstance(DonneesFormulaireFactory::class);
        $result = $this->getInternalAPI()->post(
            "/entite/" . PastellTestCase::ID_E_COL . "/document/{$id_d}/action/send-iparapheur"
        );

        $this->assertEquals(
            [
                'result' => true,
                'message' => 'Le document a été envoyé au parapheur électronique',
            ],
            $result
        );

        $result = $this->getInternalAPI()->get("/entite/1/document/$id_d");
        $this->assertEquals('send-iparapheur', $result['last_action']['action']);
    }

    /**
     * @throws Exception
     */
    public function testErreurParapheur()
    {

        $soapClientFactory = $this->getMockBuilder('SoapClientFactory')->getMock();

        $soapClient = $this->getMockBuilder(SoapClient::class)->disableOriginalConstructor()->getMock();

        $soapClient->expects($this->any())
            ->method("__call")
            ->will($this->returnCallback(function ($called_function_name, $called_function_args) {

                if ($called_function_name == "CreerDossier") {
                    $called_function_args[0]['DossierID'] = "NOT TESTABLE";
                    //var_export($called_function_args);
                    //file_put_contents(__DIR__."/../fixtures/actes-iparapheur-cree-dossier-input.json",json_encode($called_function_args));
                    $this->assertStringEqualsFile(__DIR__ . "/../fixtures/actes-iparapheur-cree-dossier-input.json", json_encode($called_function_args));
                    throw new Exception("Impossible de se connecter au parapheur !");
                } else {
                    throw new Exception("La méthode $called_function_name n'est pas prévu !");
                }
            }));

        $soapClientFactory->expects($this->any())
            ->method('getInstance')
            ->willReturn($soapClient);

        $this->getObjectInstancier()->setInstance(SoapClientFactory::class, $soapClientFactory);


        $result = $this->getInternalAPI()->post(
            "/entite/1/connecteur/",
            ['libelle' => 'i-Parapheur', 'id_connecteur' => 'iParapheur']
        );
        $id_ce = $result['id_ce'];


        $this->getInternalAPI()->patch("/entite/1/connecteur/$id_ce/content", [
            'iparapheur_wsdl' => 'https://wsdl_du_parapheur/',
            'iparapheur_metadata' => 'acte_nature:acte_nature_pour_ip'
        ]);

        $this->getInternalAPI()->post(
            "/entite/1/flux/actes-generique/connecteur/$id_ce",
            ['type' => 'signature']
        );



        $result = $this->getInternalAPI()->post(
            "/Document/1",
            array('type' => 'actes-generique')
        );
        $id_d = $result['id_d'];


        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);

        $donneesFormulaire->addFileFromData("arrete", "ma_deliberation.pdf", "le contenu de ma délibération");

        $donneesFormulaire->addFileFromData("autre_document_attache", "ma_premiere_annexe.pdf", "Le contenu de ma première annexe", 0);
        $donneesFormulaire->addFileFromData("autre_document_attache", "ma_seconde_annexe.pdf", "Le contenu de ma seconde annexe", 1);

        $donneesFormulaire->addFileFromData('type_piece_fichier', 'ok', 'ok');

        $result = $this->getInternalAPI()->patch("/entite/1/document/$id_d", [
            'envoi_signature' => 1,
            'acte_nature' => '1',
            'numero_de_lacte' => '201812101413',
            'objet' => 'Achat de libriciels',
            'date_de_lacte' => '2018-12-10',
            'classification' => '1.1',
            'iparapheur_type' => 'ACTES',
            'iparapheur_sous_type' => 'DELIBERATION',
            'type_piece' => 'ok',
        ]);
        $this->assertEquals(1, $result['formulaire_ok']);

        $this->getObjectInstancier()->getInstance(DonneesFormulaireFactory::class);
        try {
            $this->getInternalAPI()->post(
                "/entite/" . PastellTestCase::ID_E_COL . "/document/{$id_d}/action/send-iparapheur"
            );
        } catch (Exception $e) {
            $this->assertEquals("Impossible de se connecter au parapheur !", $e->getMessage());
        }

        $result = $this->getInternalAPI()->get("/entite/1/document/$id_d");
        $this->assertEquals('modification', $result['last_action']['action']);
    }
}
