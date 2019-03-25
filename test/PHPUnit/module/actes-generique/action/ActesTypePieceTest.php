<?php

class ActesTypePieceTest extends PastellTestCase {



	private function postActes(){
		$fileUploader = new FileUploaderMock();
		$fileUploader->setFiles(array('file_content'=>file_get_contents(__DIR__."/../fixtures/classification.xml")));

		$this->getInternalAPI()->setFileUploader($fileUploader);

		$this->getInternalAPI()->post("/entite/1/connecteur/2/file/classification_file");

		$info = $this->getInternalAPI()->post(
			"/entite/1/document",
			array('type'=>'actes-generique')
		);

		$id_d =  $info['id_d'];

		$fileUploader->setFiles(
			array(
				'arrete'=>file_get_contents(__DIR__."/../fixtures/Delib Adullact.pdf")
			)

		);

		$this->getInternalAPI()->patch(
			"/entite/1/document/$id_d",
			array('acte_nature'=>'3','classification'=>'4.1')
		);
		$this->getInternalAPI()->setFileUploader(new FileUploader());

		return $id_d;
	}

	public function testDisplayAPI(){
		$id_d = $this->postActes();
		$info = $this->getInternalAPI()->get("/entite/1/document/$id_d/externalData/type_piece");
		
		$expected = array (
			'actes_type_pj_list' =>
				array (
                    '99_AI' => 'Acte individuel (99_AI)',
                    '22_AR' => 'Accusé de réception (22_AR)',
                    '22_AG' => 'Agrément ou certificat (22_AG)',
                    '22_AT' => 'Attestation (22_AT)',
                    '41_AT' => 'Attestation (41_AT)',
                    '22_AV' => 'Avis (22_AV)',
                    '41_CA' => 'Avis de commission administrative paritaire (41_CA)',
                    '41_CM' => 'Avis de la commission mixte paritaire (41_CM)',
                    '22_CO' => 'Convention (22_CO)',
                    '22_DD' => 'Demande (22_DD)',
                    '22_DP' => 'Document photographique (22_DP)',
                    '22_DN' => 'Décision (22_DN)',
                    '41_DE' => 'Délibération établissant la liste de postes à pourvoir (41_DE)',
                    '41_IC' => 'Information du centre de gestion (41_IC)',
                    '22_LE' => 'Lettre (22_LE)',
                    '22_NE' => 'Notice explicative (22_NE)',
                    '41_NC' => 'Notification de création ou de vacance de poste (41_NC)',
                    '22_PN' => 'Plans (22_PN)',
                    '22_PE' => 'Présentation des états initiaux et futurs (22_PE)',
                    '22_TA' => 'Tableau (22_TA)',
				),
			'pieces' =>
				array (
					0 => 'arrete',
				),
		);

		$this->assertEquals($expected,$info);
	}

	public function testGo(){
		$id_d = $this->postActes();
		$info = $this->getInternalAPI()->patch("/entite/1/document/$id_d/externalData/type_piece",array('type_pj'=>array('41_NC')));
		$this->assertEquals('arrete : Notification de création ou de vacance de poste (41_NC)',$info['data']['type_piece']);
		$this->assertEquals('41_NC',$info['data']['type_acte']);
	}



}