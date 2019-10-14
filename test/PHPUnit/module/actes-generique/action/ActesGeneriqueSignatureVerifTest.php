<?php

class ActesGeneriqueSignatureVerifTest extends PastellTestCase
{

	use SoapUtilitiesTestTrait;

	/**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testEditableFieldsAfterDocumentIsSigned()
    {
        $connector = $this->createConnector('iParapheur', 'parapheur');
        $this->configureConnector($connector['id_ce'], [
            'iparapheur_activate' => true,
            'iparapheur_wsdl' => 'wsdl'
        ]);
        $this->associateFluxWithConnector($connector['id_ce'], 'actes-generique', 'signature');

        $document = $this->createDocument('actes-generique');
        $id_d = $document['id_d'];

        $this->mockSoapClient(
        	function ($soapMethod, $arguments) use ($id_d) {
				if (in_array($soapMethod, ['GetHistoDossier', 'GetDossier'])) {
					$this->assertSame(
						$this->getDonneesFormulaireFactory()->get($id_d)->get('iparapheur_dossier_id'),
						$arguments[0]
					);
				}

				if ($soapMethod === 'GetHistoDossier') {
					// EP : j'arrive pas à faire ça.... je comprends pas comment soap transforme ces trucs-là...
					/*$xml = simplexml_load_file(__DIR__."/fixtures/iparapheur-histo-dossier-incomplete.xml");
					$json = json_encode($xml);
					return json_decode($json,false);*/
					return json_decode(json_encode([
						'LogDossier' => [
							[
								'timestamp' => 1,
								'annotation' => 'annotation',
								'status' => 'Archive'

							]
						]
					]), false);
				}
				return json_decode('{"MessageRetour":{"codeRetour":"OK","message":"message.","severite":"INFO"}}', false);
			}
		);

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $donneesFormulaire->addFileFromData('arrete', 'arrete.pdf', 'content');
        $this->getInternalAPI()->patch('/entite/1/document/' . $id_d, [
            'acte_nature' => '1',
            'numero_de_lacte' => '20190718',
            'objet' => 'objet',
            'date_de_lacte' => '2019-07-18',
            'classification' => '1.1',
            'envoi_signature' => true,
            'envoi_tdt' => true,
            'iparapheur_type' => 'TYPE',
            'iparapheur_sous_type' => 'SOUS_TYPE'
        ]);

        $this->triggerActionOnDocument($id_d, 'send-iparapheur');
        $this->assertLastMessage('Le document a été envoyé au parapheur électronique');
        $this->triggerActionOnDocument($id_d, 'verif-iparapheur');
        $this->assertLastMessage('La signature a été récupérée');
		$this->assertLastDocumentAction('recu-iparapheur',$id_d);

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $filename = substr($donneesFormulaire->getFileName('arrete'), 0, -4);
        $filename_signe = $filename . "_signe.pdf";
        $this->assertEquals($donneesFormulaire->getFileName('signature'),$filename_signe);
        $this->assertNotEquals($donneesFormulaire->getFileName('arrete'), $donneesFormulaire->getFileName('signature'));

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
        $this->assertTrue($donneesFormulaire->isEditable('date_de_lacte'));
        $this->assertTrue($donneesFormulaire->isEditable('type_acte'));
        $this->assertTrue($donneesFormulaire->isEditable('type_pj'));
    }

    /**
     * @throws NotFoundException
     * @throws Exception
     */
    public function testWhenParapheurReponseIsNotComplete(){
		$connector = $this->createConnector('iParapheur', 'parapheur');
		$this->configureConnector($connector['id_ce'], [
			'iparapheur_activate' => true,
			'iparapheur_wsdl' => 'wsdl'
		]);
		$this->associateFluxWithConnector($connector['id_ce'], 'actes-generique', 'signature');

		$document = $this->createDocument('actes-generique');
		$id_d = $document['id_d'];

		$this->mockSoapClient(
			function ($soapMethod, $arguments) use ($id_d) {
				if (in_array($soapMethod, ['GetHistoDossier', 'GetDossier'])) {
					$this->assertSame(
						$this->getDonneesFormulaireFactory()->get($id_d)->get('iparapheur_dossier_id'),
						$arguments[0]
					);
				}

				if ($soapMethod === 'GetHistoDossier') {
					$xml = simplexml_load_file(__DIR__."/fixtures/iparapheur-histo-dossier-incomplete.xml");
					$json = json_encode($xml);
					return json_decode($json,false);
				}
				return json_decode('{"MessageRetour":{"codeRetour":"OK","message":"message.","severite":"INFO"}}', false);
			}
		);

		$donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
		$donneesFormulaire->addFileFromData('arrete', 'arrete.pdf', 'content');
		$this->getInternalAPI()->patch('/entite/1/document/' . $id_d, [
			'acte_nature' => '1',
			'numero_de_lacte' => '20190718',
			'objet' => 'objet',
			'date_de_lacte' => '2019-07-18',
			'classification' => '1.1',
			'envoi_signature' => true,
			'envoi_tdt' => true,
			'iparapheur_type' => 'TYPE',
			'iparapheur_sous_type' => 'SOUS_TYPE'
		]);

		$this->triggerActionOnDocument($id_d, 'send-iparapheur');
		$this->assertLastMessage('Le document a été envoyé au parapheur électronique');
		$this->triggerActionOnDocument($id_d, 'verif-iparapheur');
		$this->assertLastMessage('23/07/2019 15:58:16 : [NonLu] Création de dossier');

		$this->assertLastDocumentAction('send-iparapheur',$id_d);

	}

}