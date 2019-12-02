<?php

require_once __DIR__."/../../../../connecteur/purge/Purge.class.php";


class PurgeTest extends PastellTestCase {

    public function getPurgeDataProvider() {

        return [
            'ActeAutoTermineEnvoiSAETrue' => [
                "actes-automatique",
                "modification",
                Purge::GO_TROUGH_STATE,
                "send-archive",
                "envoi_sae: on",
                ["modification", "termine"],
                true,
                ""
            ],
            'ActeAutoTermineEnvoiSAEFalse' => [
                "actes-automatique",
                "termine",
                Purge::IN_STATE,
                "send-archive",
                "envoi_sae: on",
                ["modification", "send-archive", "termine"],
                false,
                "#action impossible : or_1 n'est pas vérifiée#"
            ],
            'ActeAutoTerminePrepareSAEFalse' => [
                "actes-automatique",
                "termine",
                Purge::IN_STATE,
                "prepare-sae",
                "envoi_sae: on",
                ["modification", "send-archive", "termine"],
                false,
                "#action impossible : role_id_e n'est pas vérifiée#"
            ],
            'ActeAutoTermineEnvoiGEDFalse' => [
                "actes-automatique",
                "termine",
                Purge::IN_STATE,
                "send-ged",
                "envoi_ged: on",
                ["modification", "termine"],
                false,
                "#action impossible : content n'est pas vérifiée#"
            ],
        ];
    }


    /**
     * @param $document_type
     * @param $document_etat
     * @param $passer_par_l_etat
     * @param $document_etat_cible
     * @param $modification
     * @param $liste_etats
     * @param $expected_true
     * @param $message
     * @dataProvider getPurgeDataProvider
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function testPurgeDocument($document_type, $document_etat, $passer_par_l_etat, $document_etat_cible, $modification, $liste_etats, $expected_true, $message) {

        $document_info = $this->createDocument($document_type);
        $id_d = $document_info['id_d'];

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);

        $donneesFormulaire->setTabData([
            'objet'=>'test'
        ]);

        $actionCreatorSQL = $this->getObjectInstancier()->getInstance(ActionCreatorSQL::class);
        foreach ($liste_etats as $etat) {
            $actionCreatorSQL->addAction(1,0,$etat,"test",$id_d);
        }

        $purge = $this->getObjectInstancier()->getInstance(Purge::class);

        $connecteurConfig = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
        $connecteurConfig->setTabData([
            'actif'=>1,
            'document_type'=>$document_type,
            'document_etat'=>$document_etat,
            'passer_par_l_etat'=> $passer_par_l_etat,
            'document_etat_cible'=>$document_etat_cible,
            'modification'=>$modification
        ]);

        $purge->setConnecteurInfo(['id_e'=>1,'id_ce'=>42]);
        $purge->setConnecteurConfig($connecteurConfig);

        $jobManager = $this->getObjectInstancier()->getInstance(JobManager::class);
        $this->assertFalse($jobManager->hasActionProgramme(1,$id_d));
        $purge->purger();

        if ($expected_true) {
            $this->assertTrue($jobManager->hasActionProgramme(1,$id_d));
            $sql = "SELECT * FROM job_queue ";
            $result = $this->getSQLQuery()->query($sql);
            $this->assertEquals($document_etat_cible,$result[0]['etat_cible']);
            $this->assertRegExp("#$id_d#",$purge->getLastMessage());
        }
        else {
            $this->assertFalse($jobManager->hasActionProgramme(1,$id_d));
            $this->assertRegExp($message,$purge->getLastMessage());
        }
    }

    public function purgeLockNameProvider()
    {
        return [
            [
                'lock' => 'DEFAULT_FREQUENCE',
                'additionalConnectorConfig' => []
            ],
            [
                'lock' => 'CUSTOM_LOCK',
                'additionalConnectorConfig' => [
                    'verrou' => 'CUSTOM_LOCK'
                ]
            ],
        ];
    }

    /**
     * @dataProvider purgeLockNameProvider
     * @param string $lockName
     * @param array $aditionnalConnectorConfig
     * @throws UnrecoverableException
     */
    public function testPurge(string $lockName, array $aditionnalConnectorConfig){
		$result= $this->createDocument('actes-generique');
		$id_d = $result['id_d'];

		$purge = $this->getObjectInstancier()->getInstance(Purge::class);

		$connecteurConfig = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
		$connecteurConfig->setTabData([
			'actif'=>1,
			'document_type'=>'actes-generique',
			'document_etat'=>'creation',
		] + $aditionnalConnectorConfig);

		$purge->setConnecteurInfo(['id_e'=>1,'id_ce'=>42]);
		$purge->setConnecteurConfig($connecteurConfig);

		$jobManager = $this->getObjectInstancier()->getInstance(JobManager::class);
		$this->assertFalse($jobManager->hasActionProgramme(1,$id_d));
		$purge->purger();
		$this->assertTrue($jobManager->hasActionProgramme(1,$id_d));


		$sql = "SELECT * FROM job_queue ";
		$result = $this->getSQLQuery()->query($sql);
		$this->assertEquals('supression',$result[0]['etat_cible']);
        $this->assertSame($lockName, $result[0]['id_verrou']);
		$this->assertRegExp("#$id_d#",$purge->getLastMessage());
	}

	/**
	 * @throws UnrecoverableException
	 */
	public function testPurgeNotActif(){
		$purge = $this->getObjectInstancier()->getInstance(Purge::class);
		$connecteurConfig = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
		$purge->setConnecteurConfig($connecteurConfig);
		$this->expectException(Exception::class);
		$this->expectExceptionMessage("Le connecteur n'est pas actif");
		$purge->purger();
	}

	/**
	 * @throws UnrecoverableException
	 */
	public function testPurgeActionImpossible(){
		$actionPossible = $this->getMockBuilder(ActionPossible::class)->disableOriginalConstructor()->getMock();
		$actionPossible->expects($this->any())->method('isActionPossible')->willReturn(false);
		$this->getObjectInstancier()->setInstance(ActionPossible::class,$actionPossible);
		$result= $this->getInternalAPI()->post(
			"/Document/".PastellTestCase::ID_E_COL,array('type'=>'actes-generique')
		);
		$id_d = $result['id_d'];

		$purge = $this->getObjectInstancier()->getInstance(Purge::class);

		$connecteurConfig = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
		$connecteurConfig->setTabData([
			'actif'=>1,
			'document_type'=>'actes-generique',
			'document_etat'=>'creation'
		]);

		$purge->setConnecteurInfo(['id_e'=>1,'id_ce'=>42]);
		$purge->setConnecteurConfig($connecteurConfig);

		$jobManager = $this->getObjectInstancier()->getInstance(JobManager::class);
		$this->assertFalse($jobManager->hasActionProgramme(1,$id_d));
		$purge->purger();
		$this->assertFalse($jobManager->hasActionProgramme(1,$id_d));
	}

	/**
	 * @throws UnrecoverableException
	 *  @throws Exception
	 */
	public function testPurgePasserParLEtat(){
		$result= $this->getInternalAPI()->post(
			"/Document/".PastellTestCase::ID_E_COL,array('type'=>'actes-generique')
		);
		$id_d = $result['id_d'];

		$this->getInternalAPI()->patch("/entite/1/document/$id_d",array('objet'=>'test'));

		$purge = $this->getObjectInstancier()->getInstance(Purge::class);

		$connecteurConfig = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
		$connecteurConfig->setTabData([
			'actif'=>1,
			'document_type'=>'actes-generique',
			'document_etat'=>'creation',
			'passer_par_l_etat'=> Purge::GO_TROUGH_STATE
		]);

		$purge->setConnecteurInfo(['id_e'=>1,'id_ce'=>42]);
		$purge->setConnecteurConfig($connecteurConfig);

		$jobManager = $this->getObjectInstancier()->getInstance(JobManager::class);
		$this->assertFalse($jobManager->hasActionProgramme(1,$id_d));
		$purge->purger();
		$this->assertTrue($jobManager->hasActionProgramme(1,$id_d));


		$sql = "SELECT * FROM job_queue ";
		$result = $this->getSQLQuery()->query($sql);
		$this->assertEquals('supression',$result[0]['etat_cible']);
		$this->assertRegExp("#$id_d#",$purge->getLastMessage());
	}

	/**
	 * @throws Exception
	 */
	public function testPurgeModifDocument(){
		$info_document = $this->createDocument('actes-generique');

		$actionCreatorSQL = $this->getObjectInstancier()->getInstance(ActionCreatorSQL::class);
		$actionCreatorSQL->addAction(1,0,'acquiter-tdt',"test",$info_document['id_d']);

		$connecteurConfig = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
		$connecteurConfig->setTabData([
			'actif'=>1,
			'document_type'=>'actes-generique',
			'document_etat'=>'acquiter-tdt',
			'passer_par_l_etat'=> Purge::IN_STATE,
			'document_etat_cible'=>'send-ged',
			'modification'=>"envoi_ged: on\nenvoi_sae: on\nfoo: bar\nobjet: modification non prise en compte\nno_value\n\n"
		]);
		$purge = $this->getObjectInstancier()->getInstance(Purge::class);
		$purge->setConnecteurInfo(['id_e'=>1,'id_ce'=>42]);
		$purge->setConnecteurConfig($connecteurConfig);


		$donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info_document['id_d']);
		$donneesFormulaire->setData('objet','bar');
		$this->assertFalse($donneesFormulaire->get('envoi_ged'));
		$this->assertFalse($donneesFormulaire->get('envoi_sae'));
		$this->assertFalse($donneesFormulaire->get('foo'));
		$this->assertEquals('bar',$donneesFormulaire->get('objet'));

		$this->assertTrue(
			$purge->purger()
		);

		$donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info_document['id_d']);
		$this->assertTrue($donneesFormulaire->get('envoi_ged'));
		$this->assertTrue($donneesFormulaire->get('envoi_sae'));
		$this->assertFalse($donneesFormulaire->get('foo'));
		$this->assertEquals('bar',$donneesFormulaire->get('objet'));
	}

	/**
	 * @throws Exception
	 */
	public function testPurgeModifDocumentWhenThereIsNoEditableContent(){
		$info_document = $this->createDocument('actes-generique');

		$actionCreatorSQL = $this->getObjectInstancier()->getInstance(ActionCreatorSQL::class);
		$actionCreatorSQL->addAction(1,0,'send-tdt',"test",$info_document['id_d']);

		$connecteurConfig = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
		$connecteurConfig->setTabData([
			'actif'=>1,
			'document_type'=>'actes-generique',
			'document_etat'=>'send-tdt',
			'passer_par_l_etat'=> Purge::IN_STATE,
			'document_etat_cible'=>'verif-tdt',
			'modification'=>"envoi_ged: on\nenvoi_sae: on\nfoo: bar\nobjet: modification non prise en compte\nno_value\n\n"
		]);
		$purge = $this->getObjectInstancier()->getInstance(Purge::class);
		$purge->setConnecteurInfo(['id_e'=>1,'id_ce'=>42]);
		$purge->setConnecteurConfig($connecteurConfig);


		$donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info_document['id_d']);
		$donneesFormulaire->setData('objet','bar');
		$this->assertFalse($donneesFormulaire->get('envoi_ged'));
		$this->assertFalse($donneesFormulaire->get('envoi_sae'));
		$this->assertFalse($donneesFormulaire->get('foo'));
		$this->assertEquals('bar',$donneesFormulaire->get('objet'));

		$this->assertTrue(
			$purge->purger()
		);

		$donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info_document['id_d']);
		$this->assertFalse($donneesFormulaire->get('envoi_ged'));
		$this->assertFalse($donneesFormulaire->get('envoi_sae'));
		$this->assertFalse($donneesFormulaire->get('foo'));
		$this->assertEquals('bar',$donneesFormulaire->get('objet'));
	}

	/**
	 * @throws Exception
	 */
	public function testPurgeModifDocumentWhenDocumentIsInModification(){
		$info_document = $this->createDocument('actes-generique');

		$actionCreatorSQL = $this->getObjectInstancier()->getInstance(ActionCreatorSQL::class);
		$actionCreatorSQL->addAction(1,0,'modification',"test",$info_document['id_d']);

		$connecteurConfig = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
		$connecteurConfig->setTabData([
			'actif'=>1,
			'document_type'=>'actes-generique',
			'document_etat'=>'modification',
			'passer_par_l_etat'=> Purge::IN_STATE,
			'document_etat_cible'=>'modification',
			'modification'=>"envoi_ged: on\nenvoi_sae: on\nfoo: bar\nobjet: modification non prise en compte\nno_value\n\n"
		]);
		$purge = $this->getObjectInstancier()->getInstance(Purge::class);
		$purge->setConnecteurInfo(['id_e'=>1,'id_ce'=>42]);
		$purge->setConnecteurConfig($connecteurConfig);


		$donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info_document['id_d']);
		$donneesFormulaire->setData('objet','bar');
		$this->assertFalse($donneesFormulaire->get('envoi_ged'));
		$this->assertFalse($donneesFormulaire->get('envoi_sae'));
		$this->assertFalse($donneesFormulaire->get('foo'));
		$this->assertEquals('bar',$donneesFormulaire->get('objet'));

		$this->assertTrue(
			$purge->purger()
		);

		$donneesFormulaire = $this->getDonneesFormulaireFactory()->get($info_document['id_d']);
		$this->assertTrue($donneesFormulaire->get('envoi_ged'));
		$this->assertTrue($donneesFormulaire->get('envoi_sae'));
		$this->assertEquals("bar",$donneesFormulaire->get('foo'));
		$this->assertEquals('modification non prise en compte',$donneesFormulaire->get('objet'));
	}


}
