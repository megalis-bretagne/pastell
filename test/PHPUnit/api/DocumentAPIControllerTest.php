<?php


class DocumentAPIControllerTest extends PastellTestCase {

	private function createTestDocument(){
	    $info = $this->createDocument('test');
		return $info['id_d'];
	}

	public function testList(){
		$id_d = $this->createTestDocument();
		$list = $this->getInternalAPI()->get("entite/1/document");
		$this->assertEquals($id_d,$list[0]['id_d']);
	}

	public function testDetail(){
		$id_d = $this->createTestDocument();
		$info = $this->getInternalAPI()->get("entite/1/document/$id_d");
		$this->assertEquals('test',$info['info']['type']);
	}

	public function testDetailAll(){
		$id_d_1 = $this->createTestDocument();
		$id_d_2 = $this->createTestDocument();
		$list = $this->getInternalAPI()->get("entite/1/document/?id_d[]=$id_d_1&id_d[]=$id_d_2");
		$this->assertEquals($id_d_1,$list[$id_d_1]['info']['id_d']);
		$this->assertEquals($id_d_2,$list[$id_d_2]['info']['id_d']);
	}

	public function testDetailAllFail(){
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Le paramètre id_d[] ne semble pas valide");

		$this->getInternalAPI()->get("entite/1/document/?id_d=42");
	}

	public function testRecherche(){
		$id_d = $this->createTestDocument();
		$list = $this->getInternalAPI()->get("entite/1/document?date_in_fr=true");
		$this->assertEquals($id_d,$list[0]['id_d']);
	}

	public function testRechercheNoIdEntite(){
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("id_e est obligatoire");

		$this->getInternalAPI()->get("entite/0/document");
	}

	public function testRechercheIndexedField(){
		$id_d = $this->createTestDocument();
		$this->getInternalAPI()->patch("entite/1/document/$id_d",array('test1'=>'toto'));
		$list = $this->getInternalAPI()->get("entite/1/document?test1=toto");
		$this->assertEquals($id_d,$list[0]['id_d']);
	}

	public function testRechercheIndexedDateField(){
		$id_d = $this->createTestDocument();
		$this->getInternalAPI()->patch("entite/1/document/$id_d",array('date_indexed'=>'2001-09-11'));
		$list = $this->getInternalAPI()->get("entite/1/document?type=test&date_in_fr=true&date_indexed=2001-09-11");
		$this->assertEquals($id_d,$list[0]['id_d']);
	}

	public function testExternalData(){
		$id_d = $this->createTestDocument();
		$list = $this->getInternalAPI()->get("entite/1/document/$id_d/externalData/test_external_data");
		$this->assertEquals("Spock",$list[4]);
	}

	public function testExternalDataFaild(){
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Type test42 introuvable");

        $id_d = $this->createTestDocument();
		$this->getInternalAPI()->get("entite/1/document/$id_d/externalData/test42");
	}

	public function testPatchExternalData(){
		$id_d = $this->createTestDocument();
		$info = $this->getInternalAPI()->patch(
			"entite/1/document/$id_d/externalData/test_external_data",
			array('choix'=>'foo')
		);
		$this->assertEquals('foo',$info['data']['test_external_data']);
	}

	public function testPatchExternalDataFailed(){
		$id_d = $this->createTestDocument();
		$this->expectException(Exception::class);
		$this->expectExceptionMessage("Type test_external_data_not_existing introuvable");
		$this->getInternalAPI()->patch(
			"entite/1/document/$id_d/externalData/test_external_data_not_existing",
			array('choix'=>'foo')
		);
	}

	public function testEditAction(){
		$id_d = $this->createTestDocument();
		$info = $this->getInternalAPI()->patch("entite/1/document/$id_d",array('test1'=>'toto'));
		$this->assertEquals("toto",$info['content']['data']['test1']);
	}

    private function sendFile($id_d, $fileNumber = 0) {
		$info = $this->getInternalAPI()->post("entite/1/document/$id_d/file/fichier/$fileNumber",
			array(
				'file_name'=>'toto.txt',
				'file_content'=>'xxxx'
			)
		);
		return $info;
	}

	public function testSendFile(){
		$id_d = $this->createTestDocument();
		$info = $this->sendFile($id_d);
		$this->assertEquals("toto.txt",$info['content']['data']['fichier'][0]);
	}

	public function testReceiveFile(){
		$id_d = $this->createTestDocument();
		$this->sendFile($id_d);
		$info =$this->getInternalAPI()->get("entite/1/document/$id_d/file/fichier?receive=true");
		$this->assertEquals("xxxx",$info['file_content']);
	}

	public function testAction(){
		$id_d = $this->createTestDocument();
		$info =$this->getInternalAPI()->post("entite/1/document/$id_d/action/ok");
		$this->assertEquals("OK !",$info['message']);
	}

	public function testActionNotPossible(){
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("L'action « not-possible »  n'est pas permise : role_id_e n'est pas vérifiée");

		$id_d = $this->createTestDocument();
		$this->getInternalAPI()->post("entite/1/document/$id_d/action/not-possible");
	}

	public function testActionFailed(){
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Raté !");

        $id_d = $this->createTestDocument();
		$this->getInternalAPI()->post("entite/1/document/$id_d/action/fail");
	}

	public function testEditOnChange(){
		$id_d = $this->createTestDocument();
		$info =$this->getInternalAPI()->patch("entite/1/document/$id_d",array('test_on_change'=>'foo'));
		$this->assertEquals("foo",$info['content']['data']['test2']);
	}

	public function testEditCantModify(){
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("L'action « modification »  n'est pas permise");

		$id_d = $this->createTestDocument();
		$this->getInternalAPI()->post("entite/1/document/$id_d/action/no-way");
		$this->getInternalAPI()->patch("entite/1/document/$id_d",array('test2'=>'ok'));
	}

	public function testRecuperationFichier(){
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Exit called with code 0");

		$id_d = $this->createTestDocument();
		$this->sendFile($id_d);
		$this->expectOutputRegex("#xxxx#");
		$this->getInternalAPI()->get("entite/1/document/$id_d/file/fichier");
	}

	public function testRecuperationFichierFailed(){
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Ce fichier n'existe pas");

		$id_d = $this->createTestDocument();
		$this->getInternalAPI()->get("entite/1/document/$id_d/file/fichier");
	}

    public function testLengthOfDocumentObject(){
        $info = $this->getInternalAPI()->post("entite/1/document", array('type' => 'actes-generique'));
        $id_d = $info['id_d'];
        $info = $this->getInternalAPI()->patch("entite/1/document/$id_d", [
            'acte_nature' => '4',
            'numero_de_lacte' => 'D443_2017A',
            'objet' => 'Ceci est un message qui fait 498 caractères.Ceci est un message qui fait 498 caractères.Ceci est un message qui fait 498 caractères.Ceci est un message qui fait 498 caractères.Ceci est un message qui fait 498 caractères.Ceci est un message qui fait 498 caractères.Ceci est un message qui fait 498 caractères.Ceci est un message qui fait 498 caractères.Ceci est un message qui fait 498 caractères.Ceci est un message qui fait 498 caractères mais avec &quot; il en fait 503 lorsqu\'il est encodé',
        ]);
        $this->assertEquals("Le formulaire est incomplet : le champ «Acte» est obligatoire.",$info['message']);
    }

    public function testCount(){
		$this->getInternalAPI()->post("entite/1/document", array('type' => 'actes-generique'));
		$info = $this->getInternalAPI()->get("document/count",array('id_e'=>1,'type'=>'actes-generique'));
		$this->assertEquals(array (
			1 =>
				array (
					'flux' =>
						array (
							'actes-generique' =>
								array (
									'creation' => '1',
								),
						),
					'info' =>
						array (
							'id_e' => '1',
							'type' => 'collectivite',
							'denomination' => 'Bourg-en-Bresse',
							'siren' => '123456789',
							'date_inscription' => '0000-00-00 00:00:00',
							'etat' => '0',
							'entite_mere' => '0',
							'centre_de_gestion' => '0',
							'is_active' => '1',
						),
				),
		)
		,$info);

	}

    public function testUploadFileWithoutActionPossible()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("L'action « modification »  n'est pas permise");
        $id_d = $this->createTestDocument();

        $this->sendFile($id_d);
        $this->sendFile($id_d, 1);

        $this->getInternalAPI()->post("entite/1/document/$id_d/action/no-way");
        $this->sendFile($id_d, 2);
    }

    public function testUploadFileWithoutFieldBeingEditable()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Le champ « fichier »  n'est pas modifiable");
        $id_d = $this->createTestDocument();

        $this->sendFile($id_d);
        $this->sendFile($id_d, 1);

        $this->getInternalAPI()->post("entite/1/document/$id_d/action/editable");
        $this->sendFile($id_d, 2);
    }

}