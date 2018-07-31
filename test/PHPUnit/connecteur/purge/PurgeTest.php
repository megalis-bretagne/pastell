<?php

require_once __DIR__."/../../../../connecteur/purge/Purge.class.php";


class PurgeTest extends PastellTestCase {

	/**
	 * @throws Exception
	 */
	public function testPurge(){
		$result= $this->getInternalAPI()->post(
			"/Document/".PastellTestCase::ID_E_COL,array('type'=>'actes-generique')
		);
		$id_d = $result['id_d'];

		$purge = $this->getObjectInstancier()->getInstance(Purge::class);

		$connecteurConfig = $this->getDonneesFormulaireFactory()->getNonPersistingDonneesFormulaire();
		$connecteurConfig->setTabData([
			'actif'=>1,
			'document_type'=>'actes-generique',
			'document_etat'=>'creation',
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


}