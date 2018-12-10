<?php

class TedetisRecupTest extends PastellTestCase {

	/**
	 * @throws Exception
	 */
	public function testCasNominal(){
		// Préparation du test
		$curlWrapper = $this->getMockBuilder(CurlWrapper::class)->getMock();
		$curlWrapper->expects($this->any())->method("get")->will($this->returnCallback(function ($url){

			if ($url == "/admin/users/api-list-login.php"){
				return true;
			} else if($url =="/modules/actes/actes_transac_get_status.php?transaction=42"){
				return "OK\n4\n".file_get_contents(__DIR__."/../fixtures/aractes.xml");
			} else if($url=="/modules/actes/actes_create_pdf.php?trans_id=42"){
				return "bordereau content";
			} else if($url == "/modules/actes/actes_transac_get_files_list.php?transaction=42"){
				return file_get_contents(__DIR__."/../fixtures/actes_transac_get_files_list.json");
			} else if($url == "/modules/actes/actes_download_file.php?file=3968&tampon=true"){
				return "some pdf stuff tamponne";
			} else if($url == "/modules/actes/actes_download_file.php?file=3969&tampon=true"){
				return "some annexe tamponne";
			}

			throw new Exception("$url inatendu");
		}));

		$curlWrapperFactory = $this->getMockBuilder(CurlWrapperFactory::class)->getMock();
		$curlWrapperFactory->expects($this->any())->method("getInstance")->willReturn($curlWrapper);

		$this->getObjectInstancier()->setInstance(CurlWrapperFactory::class,$curlWrapperFactory);


		$result = $this->getInternalAPI()->post(
			"/entite/1/connecteur/",
			['libelle'=>'s2low','id_connecteur'=>'s2low']
		);
		$id_ce = $result['id_ce'];

		$this->getInternalAPI()->post(
			"/entite/1/flux/actes-generique/connecteur/$id_ce" ,
			['type'=>'TdT']
		);

		$result= $this->getInternalAPI()->post(
			"/Document/1",array('type'=>'actes-generique')
		);
		$id_d = $result['id_d'];


		$this->getObjectInstancier()->getInstance(DonneesFormulaireFactory::class);

		$donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);

		$donneesFormulaire->setTabData([
			'objet'=>"achat d'un bus logiciel",
			'numero_de_lacte'=>'201812101049',
		]);

		$documentSQL = $this->getObjectInstancier()->getInstance(Document::class);
		$documentSQL->setTitre($id_d,"achat d'un bus logiciel");

		$donneesFormulaire->setData('tedetis_transaction_id',42);
		$donneesFormulaire->addFileFromData('arrete','mon_acte.pdf','');
		$donneesFormulaire->addFileFromData('autre_document_attache','ma_premiere_annexe.pdf','');

		$actionChange = $this->getObjectInstancier()->getInstance(ActionChange::class);
		$actionChange->addAction($id_d,PastellTestCase::ID_E_COL,0,'send-tdt','phpunit');

		// Test
		try {
			$result = $this->getInternalAPI()->post(
				"/entite/" . PastellTestCase::ID_E_COL . "/document/{$id_d}/action/verif-tdt"
			);
		} catch (Exception $e){
			print_r($this->getLogRecords());
			throw $e;
		}

		//Analyse des résultats
		$this->assertEquals(1,$result['result']);
		$this->assertEquals("L'acquittement du contrôle de légalité a été reçu.",$result['message']);

		$documentActionEntite = $this->getObjectInstancier()->getInstance(DocumentActionEntite::class);

		$info_action = $documentActionEntite->getInfo($id_d,1);
		$this->assertEquals('acquiter-tdt',$info_action['last_action']);

		$donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);

		$this->assertStringEqualsFile(__DIR__."/../fixtures/aractes.xml",$donneesFormulaire->getFileContent('aractes'));
		$this->assertEquals("bordereau content",$donneesFormulaire->getFileContent('bordereau'));
		$this->assertEquals("some pdf stuff tamponne",$donneesFormulaire->getFileContent('acte_tamponne'));
		$this->assertEquals("some annexe tamponne",$donneesFormulaire->getFileContent('annexes_tamponnees'));

		$this->assertEquals(
			'201812101049-bordereau-tdt.pdf',
			$donneesFormulaire->getFileName('bordereau')
		);

		$this->assertEquals(
			'201812101049-ar-actes.xml',
			$donneesFormulaire->getFileName('aractes')
		);

		$this->assertEquals(
			'mon_acte-tampon.pdf',
			$donneesFormulaire->getFileName('acte_tamponne')
		);

		$this->assertEquals(
			[0 => 'ma_premiere_annexe-tampon.pdf'],
			$donneesFormulaire->get('annexes_tamponnees')
		);

		$this->assertEquals("2017-12-27",$donneesFormulaire->get('date_ar'));
	}

	/**
	 * @throws Exception
	 */
	public function testS2lowSendError()
	{
		// Préparation du test
		$curlWrapper = $this->getMockBuilder(CurlWrapper::class)->getMock();
		$curlWrapper->expects($this->any())->method("get")->will($this->returnCallback(function ($url) {

			if ($url == "/admin/users/api-list-login.php") {
				return true;
			} else if ($url == "/modules/actes/actes_transac_get_status.php?transaction=42") {
				return "OK\n-1\nEnveloppe invalide : raison de l'erreur hyper détaillé";
			}

			throw new Exception("$url inatendu");
		}));

		$curlWrapperFactory = $this->getMockBuilder(CurlWrapperFactory::class)->getMock();
		$curlWrapperFactory->expects($this->any())->method("getInstance")->willReturn($curlWrapper);

		$this->getObjectInstancier()->setInstance(CurlWrapperFactory::class, $curlWrapperFactory);


		$result = $this->getInternalAPI()->post(
			"/entite/1/connecteur/",
			['libelle' => 's2low', 'id_connecteur' => 's2low']
		);
		$id_ce = $result['id_ce'];

		$this->getInternalAPI()->post(
			"/entite/1/flux/actes-generique/connecteur/$id_ce",
			['type' => 'TdT']
		);

		$result = $this->getInternalAPI()->post(
			"/Document/1", array('type' => 'actes-generique')
		);
		$id_d = $result['id_d'];


		$this->getObjectInstancier()->getInstance(DonneesFormulaireFactory::class);

		$donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);

		$donneesFormulaire->setData('tedetis_transaction_id', 42);

		$actionChange = $this->getObjectInstancier()->getInstance(ActionChange::class);
		$actionChange->addAction($id_d, PastellTestCase::ID_E_COL, 0, 'send-tdt', 'phpunit');

		// Test
		try {
			$this->getInternalAPI()->post(
				"/entite/" . PastellTestCase::ID_E_COL . "/document/{$id_d}/action/verif-tdt"
			);
		} catch (Exception $e) { /* Nothing to do */}

		//Analyse des résultats
		$documentActionEntite = $this->getObjectInstancier()->getInstance(DocumentActionEntite::class);
		$info_action = $documentActionEntite->getInfo($id_d, 1);
		$this->assertEquals('erreur-verif-tdt', $info_action['last_action']);

		$sql = "SELECT message FROM journal ORDER BY id_j DESC LIMIT 1";
		$message = $this->getSQLQuery()->queryOne($sql);
		$this->assertEquals("Transaction en erreur sur le TdT : Enveloppe invalide : raison de l'erreur hyper détaillé",$message);
	}

}