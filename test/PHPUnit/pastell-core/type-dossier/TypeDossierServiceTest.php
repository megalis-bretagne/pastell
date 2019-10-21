<?php

class TypeDossierServiceTest extends PastellTestCase {

	private function getTypeDossierService(){
		return $this->getObjectInstancier()->getInstance(TypeDossierService::class);
	}

	/**
	 * @throws Exception
	 */
	public function testGetEmptyDossierData(){
		$typeDossierData42 = $this->getTypeDossierService()->getTypeDossierProperties(42);
		$typeDossierData = new TypeDossierProperties();
		$this->assertEquals($typeDossierData,$typeDossierData42);
	}

	/**
	 * @throws Exception
	 */
	public function testEditLibelleInfo(){
		$id_t = $this->getTypeDossierService()->create("test");
		$this->getTypeDossierService()->editLibelleInfo(
			$id_t,
			"arrete-rh",
			"Flux CD 99",
			"Ceci est un flux de test",
			"Information"
		);
		$this->assertEquals(
			array (
				'id_type_dossier' => 'test',
				'nom' => 'arrete-rh',
				'type' => 'Flux CD 99',
				'description' => 'Ceci est un flux de test',
				'nom_onglet' => 'Information',
				'formulaireElement' =>
					array (
					),
				'etape' =>
					array (
					),
			),
			$this->getTypeDossierService()->getRawData($id_t)
		);
	}

	/**
	 * @throws Exception
	 */
    public function testEditionElement(){
		$id_t = $this->getTypeDossierService()->create("test");
        $recuperateur = new Recuperateur([
            'element_id' => 'nom_agent',
            'name' => "Nom de l'agent",
	        'type' => 'text',
	        'commentaire' => "Mettre ici le nom de l'agent",
	        'requis' => true,
	        'champs_affiches' => true,
	        'champs_recherche_avancee' => true,
	        'titre' => true
        ]);
	    $this->getTypeDossierService()->editionElement($id_t,$recuperateur);
	    $file_content = $this->getTypeDossierService()->getRawData($id_t);
        $this->assertEquals(
			array (
				'id_type_dossier' => 'test',
				'nom' => '',
				'type' => '',
				'description' => '',
				'nom_onglet' => '',
				'formulaireElement' =>
					array (
						0 =>
							array (
								'element_id' => 'nom_agent',
								'name' => 'Nom de l\'agent',
								'type' => 'text',
								'commentaire' => 'Mettre ici le nom de l\'agent',
								'requis' => '1',
								'champs_affiches' => '1',
								'champs_recherche_avancee' => '1',
								'titre' => '1',
                                'select_value' => ''
                            ),
					),
				'etape' =>
					array (
					),
			),
            $file_content
        );
       $type_dossier_data = $this->getTypeDossierService()->getTypeDossierProperties($id_t);
       $this->assertEquals(
           "Mettre ici le nom de l'agent",
           $type_dossier_data->formulaireElement[0]->commentaire
       );
    }


    /**
     * @param string $filepath
     * @return mixed
     * @throws TypeDossierException
     * @throws UnrecoverableException
     */
    private function copyTypeDossierTest($filepath = __DIR__."/fixtures/cas-nominal.json"){

		$typeDossierImportExport = $this->getObjectInstancier()->getInstance(TypeDossierImportExport::class);
		return $typeDossierImportExport->importFromFilePath($filepath)['id_t'];
	}

	/**
	 * @throws Exception
	 */
    public function testChangeTitreElement(){
        $id_t = $this->copyTypeDossierTest();
        $typeDossierDefinition = $this->getTypeDossierService()->getTypeDossierProperties($id_t);

        $this->assertTrue((bool)$typeDossierDefinition->formulaireElement[0]->titre);
        $this->assertFalse((bool)$typeDossierDefinition->formulaireElement[2]->titre);
        $this->getTypeDossierService()->editionElement($id_t,new Recuperateur([
        	'orig_element_id'=>'nom_agent',
            'element_id' => 'nom_agent',
            'type' => 'text',
            'titre' => 'on'
        ]));
        $typeDossierDefinition = $this->getTypeDossierService()->getTypeDossierProperties($id_t);
        $this->assertFalse((bool)$typeDossierDefinition->formulaireElement[0]->titre);
        $this->assertTrue((bool)$typeDossierDefinition->formulaireElement[2]->titre);
    }


	/**
	 * @throws Exception
	 */
    public function testDelete(){
        $id_t = $this->copyTypeDossierTest();
        $typeDossierDefinition = $this->getTypeDossierService()->getTypeDossierProperties($id_t);
        $this->assertEquals('Cas nominal',$typeDossierDefinition->nom);
        $this->getTypeDossierService()->delete($id_t);
        $typeDossierDefinition = $this->getTypeDossierService()->getTypeDossierProperties($id_t);
        $this->assertEquals('',$typeDossierDefinition->nom);
    }

	/**
	 * @throws Exception
	 */
    public function testGetFormulaireElement(){
        $id_t = $this->copyTypeDossierTest();
        $typeDossierFormulaireElement =
            $this->getTypeDossierService()->getFormulaireElement($id_t,'objet');
        $this->assertEquals('Objet',$typeDossierFormulaireElement->name);
    }

	/**
	 * @throws Exception
	 */
    public function testGetFormulaireElementEmpty(){
        $id_t = $this->copyTypeDossierTest();
        $typeDossierFormulaireElement =
            $this->getTypeDossierService()->getFormulaireElement($id_t,'foo');
        $this->assertEquals('',$typeDossierFormulaireElement->name);
    }

	/**
	 * @throws Exception
	 */
    public function testEditionElementChangeElementId(){
        $id_t = $this->copyTypeDossierTest();
        $typeDossierFormulaireElement =
            $this->getTypeDossierService()->getFormulaireElement($id_t,'objet');
        $this->assertEquals('Objet',$typeDossierFormulaireElement->name);
        $this->getTypeDossierService()->editionElement(
			$id_t,new Recuperateur([
                'orig_element_id'=>'objet',
                'element_id' => 'new_objet',
                'name'=> 'Objet',
                'type'=> 'text'
            ])
        );
        $typeDossierFormulaireElement =
            $this->getTypeDossierService()->getFormulaireElement($id_t,'objet');
        $this->assertEquals('',$typeDossierFormulaireElement->name);
        $typeDossierFormulaireElement =
            $this->getTypeDossierService()->getFormulaireElement($id_t,'new_objet');
        $this->assertEquals('Objet',$typeDossierFormulaireElement->name);
    }

	/**
	 * @throws Exception
	 */
    public function testEditWithoutElementId(){
        $id_t = $this->copyTypeDossierTest();
        $this->expectException(TypeDossierException::class);
        $this->expectExceptionMessage("L'identifiant de l'élément est obligatoire");
        $this->getTypeDossierService()->editionElement($id_t,new Recuperateur([]));
    }


	/**
	 * @throws Exception
	 */
	public function testEditWithSameElementId(){
		$id_t = $this->copyTypeDossierTest();
		$typeDossierProperties = $this->getTypeDossierService()->getTypeDossierProperties($id_t);
		$this->assertEquals(5,count($typeDossierProperties->formulaireElement));
		try {
			$this->getTypeDossierService()->editionElement($id_t,new Recuperateur(['element_id'=>'prenom_agent','type'=>'text']));
			$this->assertFalse(true);
		} catch (TypeDossierException $e){
			$this->assertEquals("L'identifiant « prenom_agent » existe déjà sur ce formulaire",$e->getMessage());
		}
		$typeDossierProperties = $this->getTypeDossierService()->getTypeDossierProperties($id_t);
		$this->assertEquals(5,count($typeDossierProperties->formulaireElement));
	}

	/**
	 * @throws Exception
	 */
    public function testDeleteElement(){
        $id_t = $this->copyTypeDossierTest();
        $typeDossierData =
            $this->getTypeDossierService()->getTypeDossierProperties($id_t);
        $this->assertTrue($this->getTypeDossierService()->hasFormulaireElement($typeDossierData,'nom_agent'));

        $this->getTypeDossierService()->deleteElement($id_t,'nom_agent');
        $typeDossierData =
            $this->getTypeDossierService()->getTypeDossierProperties($id_t);
		$this->assertFalse($this->getTypeDossierService()->hasFormulaireElement($typeDossierData,'nom_agent'));
    }

	/**
	 * @throws Exception
	 */
    public function testSortElement(){
		$id_t = $this->copyTypeDossierTest();
        $sort_order = [
            'objet',
            'nom_agent',
            'prenom_agent',
            'annexe',
            'arrete'
        ];
        $this->getTypeDossierService()->sortElement($id_t,$sort_order);

        $typeDossierData =
            $this->getTypeDossierService()->getTypeDossierProperties($id_t);

        $result = [];
        foreach($typeDossierData->formulaireElement as $i => $formulaireElementProperties){
        	$result[] = $formulaireElementProperties->element_id;
		}

        $this->assertEquals($sort_order,$result);
    }


	/**
	 * @throws Exception
	 */
    public function testSortElementMissedElement(){
		$id_t = $this->copyTypeDossierTest();
        $sort_order = [
            'objet',
            'prenom_agent',
            'annexe',
            'arrete'
        ];
        $this->expectException(TypeDossierException::class);
        $this->expectExceptionMessage("Impossible de retrier le tableau");
        $this->getTypeDossierService()->sortElement($id_t,$sort_order);
    }

	/**
	 * @throws Exception
	 */
    public function testgetFieldWithType(){
		$id_t = $this->copyTypeDossierTest();
        $this->assertEquals(['objet','prenom_agent','nom_agent'],
            array_keys($this->getTypeDossierService()->getFieldWithType(
				$id_t,
                TypeDossierFormulaireElementManager::TYPE_TEXT))
        );
    }

    public function testNewEtape(){
		$id_t = $this->copyTypeDossierTest();
        $typeDossierData = $this->getTypeDossierService()->getTypeDossierProperties($id_t);
        $this->assertFalse(
            isset($typeDossierData->etape[5])
        );
        $this->getTypeDossierService()->newEtape($id_t,new Recuperateur([
            'type'=>'signature'
        ]));
        $typeDossierData = $this->getTypeDossierService()->getTypeDossierProperties($id_t);
        $this->assertEquals('signature',
            $typeDossierData->etape[5]->type
        );
    }

	/**
	 * @throws Exception
	 */
    public function testEditionEtape(){
		$id_t = $this->copyTypeDossierTest();
        $typeDossierEtapeInfo = $this->getTypeDossierService()->getEtapeInfo($id_t,0);
        $this->assertEquals('arrete',$typeDossierEtapeInfo->specific_type_info['document_a_signer']);

        $this->getTypeDossierService()->editionEtape($id_t,new Recuperateur([
            'num_etape' => 0,
            'document_a_signer' => 'foo'
        ]));
        $typeDossierEtapeInfo = $this->getTypeDossierService()->getEtapeInfo($id_t,0);
        $this->assertEquals('foo',$typeDossierEtapeInfo->specific_type_info['document_a_signer']);
    }

	/**
	 * @throws Exception
	 */
    public function testDeleteEtape(){
		$id_t = $this->copyTypeDossierTest();
        $typeDossierEtapeInfo = $this->getTypeDossierService()->getEtapeInfo($id_t,2);
        $this->assertEquals('mailsec',$typeDossierEtapeInfo->type);
        $typeDossierEtapeInfo = $this->getTypeDossierService()->getEtapeInfo($id_t,3);
        $this->assertEquals('depot',$typeDossierEtapeInfo->type);
        $this->assertEquals(5,count($this->getTypeDossierService()->getTypeDossierProperties($id_t)->etape));
        $this->getTypeDossierService()->deleteEtape($id_t,2);
        $typeDossierEtapeInfo = $this->getTypeDossierService()->getEtapeInfo($id_t,2);
        $this->assertEquals('depot',$typeDossierEtapeInfo->type);
        $this->assertEquals(4,count($this->getTypeDossierService()->getTypeDossierProperties($id_t)->etape));
    }

	/**
	 * @throws Exception
	 */
    public function testSortEtape(){
		$id_t = $this->copyTypeDossierTest();
        $typeDossierEtapeInfo = $this->getTypeDossierService()->getEtapeInfo($id_t,0);
        $this->assertEquals('signature',$typeDossierEtapeInfo->type);
        $typeDossierEtapeInfo = $this->getTypeDossierService()->getEtapeInfo($id_t,1);
        $this->assertEquals('depot',$typeDossierEtapeInfo->type);
        $typeDossierEtapeInfo = $this->getTypeDossierService()->getEtapeInfo($id_t,2);
        $this->assertEquals('mailsec',$typeDossierEtapeInfo->type);
        $this->getTypeDossierService()->sortEtape($id_t,[1,0,2,3,4]);

        $typeDossierEtapeInfo = $this->getTypeDossierService()->getEtapeInfo($id_t,0);
        $this->assertEquals('depot',$typeDossierEtapeInfo->type);
		$typeDossierEtapeInfo = $this->getTypeDossierService()->getEtapeInfo($id_t,1);
		$this->assertEquals('signature',$typeDossierEtapeInfo->type);
        $typeDossierEtapeInfo = $this->getTypeDossierService()->getEtapeInfo($id_t,2);
        $this->assertEquals('mailsec',$typeDossierEtapeInfo->type);
    }

	/**
	 * @throws Exception
	 */
    public function testSortEtapeFailed(){
		$id_t = $this->copyTypeDossierTest();
        $this->expectException(TypeDossierException::class);
        $this->expectExceptionMessage("Impossible de retrier le tableau");
        $this->getTypeDossierService()->sortEtape($id_t,[1,0]);
    }

	/**
	 * @throws Exception
	 */
    public function testNewEtapeInfo(){
		$id_t = $this->copyTypeDossierTest();
        $etapeInfo = $this->getTypeDossierService()->getEtapeInfo($id_t,6);
        $this->assertEquals('new',$etapeInfo->num_etape);
    }


	/**
	 * @throws TypeDossierException
	 * @throws Exception
	 */
    public function testGetNextActionFirstEtape(){
		$id_t = $this->copyTypeDossierTest();
		$this->assertEquals(
			'preparation-send-iparapheur',
			$this->getTypeDossierService()->getNextAction($id_t,'modification')
		);
	}

    /**
     * @throws TypeDossierException
     * @throws Exception
     */
    public function testGetNextActionNoStep(){
        $this->expectException(TypeDossierException::class);
        $this->expectExceptionMessage('Impossible de trouver la première action à effectuer sur le document');
        $id_t = $this->copyTypeDossierTest(__DIR__ . '/fixtures/no-step.json');
        $this->getTypeDossierService()->getNextAction($id_t,'modification');
	}

	/**
	 * @throws TypeDossierException
	 * @throws Exception
	 */
	public function testGetNextAction(){
		$id_t = $this->copyTypeDossierTest();
		$this->assertEquals(
			'preparation-send-ged_1',
			$this->getTypeDossierService()->getNextAction($id_t,'recu-iparapheur')
		);
	}

	/**
	 * @throws TypeDossierException
	 * @throws Exception
	 */
	public function testGetLastAction(){
		$id_t = $this->copyTypeDossierTest();
		$this->assertEquals(
			'termine',
			$this->getTypeDossierService()->getNextAction($id_t,'accepter-sae')
		);
	}

    /**
     * @throws TypeDossierException
	 * @throws Exception
     */
	public function testGetNextActionCheminementFacultatif(){
		$id_t = $this->copyTypeDossierTest();
        $this->assertEquals(
            'preparation-send-mailsec',
            $this->getTypeDossierService()->getNextAction($id_t,'recu-iparapheur',[1,0,1,1,1])
        );
    }

    /**
     * @throws TypeDossierException
	 * @throws Exception
     */
    public function testGetNextActionCheminementFacultatifFirstStep(){
		$id_t = $this->copyTypeDossierTest();
        $this->assertEquals(
            'preparation-send-mailsec',
            $this->getTypeDossierService()->getNextAction($id_t,'importation',[0,0,1,1,1])
        );
    }

	/**
	 * @throws Exception
	 */
    public function testGetEtapeWithSameType(){
    	$typeDossierImportExport = $this->getObjectInstancier()->getInstance(TypeDossierImportExport::class);
		$id_t = $typeDossierImportExport->importFromFilePath(__DIR__."/fixtures/double-ged.json")['id_t'];
		$typeDossierData = $this->getTypeDossierService()->getTypeDossierProperties($id_t);
		$this->assertEquals(0,$typeDossierData->etape[0]->num_etape_same_type);
		$this->assertEquals(1,$typeDossierData->etape[1]->num_etape_same_type);
		$this->assertTrue($typeDossierData->etape[0]->etape_with_same_type_exists);
		$this->assertTrue($typeDossierData->etape[1]->etape_with_same_type_exists);
	}

	/**
	 *
	 * @throws TypeDossierException
	 * @throws Exception
	 */
    public function testGetNextActionDoubleConnecteur(){
		$typeDossierImportExport = $this->getObjectInstancier()->getInstance(TypeDossierImportExport::class);
		$id_t = $typeDossierImportExport->importFromFilePath(__DIR__."/fixtures/double-ged.json")['id_t'];
		$this->assertEquals(
			'preparation-send-ged_1',
			$this->getTypeDossierService()->getNextAction($id_t,"modification")
		);
	}

	/**
	 * @throws Exception
	 */
	public function testRebuildAll(){
		$typeDossierImportExport = $this->getObjectInstancier()->getInstance(TypeDossierImportExport::class);
		$typeDossierImportExport->importFromFilePath(__DIR__."/fixtures/double-ged.json");
		$definition_path = $this->getObjectInstancier()->getInstance("workspacePath")
			."/type-dossier-personnalise/module/double-ged/definition.yml";
		$this->assertFileExists($definition_path);
		unlink($definition_path);
		$this->assertFileNotExists($definition_path);
		$this->getTypeDossierService()->rebuildAll();
		$this->assertFileExists($definition_path);
		//file_put_contents(__DIR__."/fixtures/double-ged.yml",file_get_contents($definition_path));
		$this->assertFileEquals(__DIR__."/fixtures/double-ged.yml",$definition_path);
	}


    /**
     * @throws Exception
     */
    public function testAddSameSecondStep()
    {
        $id_t = $this->copyTypeDossierTest(__DIR__ . '/fixtures/ged-only.json');

        $typeDossierData = $this->getTypeDossierService()->getTypeDossierProperties($id_t);
        $this->assertSame(1, count($typeDossierData->etape));
        $this->getTypeDossierService()->newEtape($id_t, new Recuperateur([
            'type' => 'depot'
        ]));

        $typeDossierRawData = $this->getTypeDossierService()->getRawData($id_t);
        $this->assertSame(2, count($typeDossierRawData['etape']));

        $this->assertTrue($typeDossierRawData['etape'][0]['etape_with_same_type_exists']);
        $this->assertTrue($typeDossierRawData['etape'][1]['etape_with_same_type_exists']);

        $this->assertSame(0, $typeDossierRawData['etape'][0]['num_etape_same_type']);
        $this->assertSame(1, $typeDossierRawData['etape'][1]['num_etape_same_type']);
    }
}