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
					'41_NC' => 'Notification de création ou de vacance de poste',
					'41_DE' => 'Délibération établissant la liste de postes à pourvoir',
					'41_CA' => 'Avis de commission administrative paritaire',
					'41_IC' => 'Information du centre de gestion',
					'41_AT' => 'Attestation',
					'41_CM' => 'Avis de la commission mixte paritaire',
					'99_AI' => 'Acte individuel',
					'99_AU' => 'Autre Document',
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
		$this->assertEquals('arrete : Notification de création ou de vacance de poste',$info['data']['type_piece']);
		$this->assertEquals('41_NC',$info['data']['type_acte']);
	}



}