<?php

class TypeDossierDefinitionTest extends PastellTestCase {

	private function getTypeDossierDefinition(){
		return $this->getObjectInstancier()->getInstance(TypeDossierDefinition::class);
	}

	private function getWorkspacePath(){
        return $this->getObjectInstancier()->getInstance('workspacePath');
    }

	/**
	 * @throws Exception
	 */
	public function testGetEmptyDossierData(){
		$typeDossierData42 = $this->getTypeDossierDefinition()->getTypeDossierData(42);
		$typeDossierData = new TypeDossierData();
		$this->assertEquals($typeDossierData,$typeDossierData42);
	}

	/**
	 * @throws Exception
	 */
	public function testEditLibelleInfo(){
		$this->getTypeDossierDefinition()->editLibelleInfo(
			41,
			"arrete-rh",
			"Flux CD 99",
			"Ceci est un flux de test",
			"Information"
		);

		$this->assertEquals(
			'{"nom":"arrete-rh","type":"Flux CD 99","description":"Ceci est un flux de test","nom_onglet":"Information","formulaireElement":[],"etape":[]}',
			file_get_contents($this->getObjectInstancier()->getInstance('workspacePath')."/type_dossier_41.json")
		);
	}

    public function testEditionElement(){
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
	    $this->getTypeDossierDefinition()->editionElement(41,$recuperateur);
        $file_content = file_get_contents($this->getWorkspacePath()."/type_dossier_41.json");
        $this->assertEquals(
            '{"nom":"","type":"","description":"","nom_onglet":"","formulaireElement":{"nom_agent":{"element_id":"nom_agent","name":"Nom de l\'agent","type":"text","commentaire":"Mettre ici le nom de l\'agent","requis":"1","champs_affiches":"1","champs_recherche_avancee":"1","titre":"1"}},"etape":[]}',
            $file_content
        );
       $type_dossier_data = $this->getTypeDossierDefinition()->getTypeDossierData(41);
       $this->assertEquals(
           "Mettre ici le nom de l'agent",
           $type_dossier_data->formulaireElement['nom_agent']->commentaire
       );
    }

    public function testChangeTitreElement(){
        $this->copyTypeDossierTest();
        $typeDossierDefinition = $this->getTypeDossierDefinition()->getTypeDossierData(3);
        $this->assertTrue((bool)$typeDossierDefinition->formulaireElement['objet']->titre);
        $this->assertFalse((bool)$typeDossierDefinition->formulaireElement['nom_agent']->titre);
        $this->getTypeDossierDefinition()->editionElement(3,new Recuperateur([
            'element_id' => 'nom_agent',
            'type' => 'text',
            'titre' => 'on'
        ]));
        $typeDossierDefinition = $this->getTypeDossierDefinition()->getTypeDossierData(3);
        $this->assertFalse((bool)$typeDossierDefinition->formulaireElement['objet']->titre);
        $this->assertTrue((bool)$typeDossierDefinition->formulaireElement['nom_agent']->titre);
    }

    private function copyTypeDossierTest(){
        copy(
            __DIR__."/fixtures/type_dossier_3.json",
            $this->getWorkspacePath()."/type_dossier_3.json"
        );
    }

    public function testDelete(){
        $this->copyTypeDossierTest();
        $typeDossierDefinition = $this->getTypeDossierDefinition()->getTypeDossierData(3);
        $this->assertEquals('Arrêté RH',$typeDossierDefinition->nom);
        $this->getTypeDossierDefinition()->delete(3);
        $typeDossierDefinition = $this->getTypeDossierDefinition()->getTypeDossierData(3);
        $this->assertEquals('',$typeDossierDefinition->nom);
    }

    public function testGetFormulaireElement(){
        $this->copyTypeDossierTest();
        $typeDossierFormulaireElement =
            $this->getTypeDossierDefinition()->getFormulaireElement(3,'objet');
        $this->assertEquals('Objet',$typeDossierFormulaireElement->name);
    }

    public function testGetFormulaireElementEmpty(){
        $this->copyTypeDossierTest();
        $typeDossierFormulaireElement =
            $this->getTypeDossierDefinition()->getFormulaireElement(3,'foo');
        $this->assertEquals('',$typeDossierFormulaireElement->name);
    }

    public function testEditionElementChangeElementId(){
        $this->copyTypeDossierTest();
        $typeDossierFormulaireElement =
            $this->getTypeDossierDefinition()->getFormulaireElement(3,'objet');
        $this->assertEquals('Objet',$typeDossierFormulaireElement->name);
        $this->getTypeDossierDefinition()->editionElement(
            3,new Recuperateur([
                'orig_element_id'=>'objet',
                'element_id' => 'new_objet',
                'name'=> 'Objet',
                'type'=> 'text'
            ])
        );
        $typeDossierFormulaireElement =
            $this->getTypeDossierDefinition()->getFormulaireElement(3,'objet');
        $this->assertEquals('',$typeDossierFormulaireElement->name);
        $typeDossierFormulaireElement =
            $this->getTypeDossierDefinition()->getFormulaireElement(3,'new_objet');
        $this->assertEquals('Objet',$typeDossierFormulaireElement->name);
    }

    public function testEditWithoutElementId(){
        $this->copyTypeDossierTest();
        $this->expectException(TypeDossierException::class);
        $this->expectExceptionMessage("L'identifiant de l'élément est obligatoire");
        $this->getTypeDossierDefinition()->editionElement(3,new Recuperateur([]));
    }

    public function testDeleteElement(){
        $this->copyTypeDossierTest();
        $typeDossierData =
            $this->getTypeDossierDefinition()->getTypeDossierData(3);
        $this->assertArrayHasKey('nom_agent',$typeDossierData->formulaireElement);
        $this->getTypeDossierDefinition()->deleteElement(3,'nom_agent');
        $typeDossierData =
            $this->getTypeDossierDefinition()->getTypeDossierData(3);
        $this->assertArrayNotHasKey('nom_agent',$typeDossierData->formulaireElement);
    }

    public function testSortElement(){
        $this->copyTypeDossierTest();
        $sort_order = [
            'objet',
            'nom_agent',
            'prenom_agent',
            'annexe',
            'arrete'
        ];
        $this->getTypeDossierDefinition()->sortElement(3,$sort_order);
        $typeDossierData =
            $this->getTypeDossierDefinition()->getTypeDossierData(3);
        $this->assertEquals($sort_order,array_keys($typeDossierData->formulaireElement));
    }
    public function testSortElementMissedElement(){
        $this->copyTypeDossierTest();
        $sort_order = [
            'objet',
            'prenom_agent',
            'annexe',
            'arrete'
        ];
        $this->expectException(TypeDossierException::class);
        $this->expectExceptionMessage("Impossible de retrier le tableau");
        $this->getTypeDossierDefinition()->sortElement(3,$sort_order);
    }

    public function testgetFieldWithType(){
        $this->copyTypeDossierTest();
        $this->assertEquals(['objet','prenom_agent','nom_agent'],
            array_keys($this->getTypeDossierDefinition()->getFieldWithType(
                3,
                TypeDossierFormulaireElementManager::TYPE_TEXT))
        );
    }

    public function testNewEtape(){
        $this->copyTypeDossierTest();
        $typeDossierData = $this->getTypeDossierDefinition()->getTypeDossierData(3);
        $this->assertFalse(
            isset($typeDossierData->etape[3])
        );
        $this->getTypeDossierDefinition()->newEtape(3,new Recuperateur([
            'type'=>'signature'
        ]));
        $typeDossierData = $this->getTypeDossierDefinition()->getTypeDossierData(3);
        $this->assertEquals('signature',
            $typeDossierData->etape[3]->type
        );
    }

    public function testEditionEtape(){
        $this->copyTypeDossierTest();
        $typeDossierEtapeInfo = $this->getTypeDossierDefinition()->getEtapeInfo(3,0);
        $this->assertEquals('arrete',$typeDossierEtapeInfo->specific_type_info['document_a_signer']);

        $this->getTypeDossierDefinition()->editionEtape(3,new Recuperateur([
            'num_etape' => 0,
            'document_a_signer' => 'foo'
        ]));
        $typeDossierEtapeInfo = $this->getTypeDossierDefinition()->getEtapeInfo(3,0);
        $this->assertEquals('foo',$typeDossierEtapeInfo->specific_type_info['document_a_signer']);
    }

    public function testDeleteEtape(){
        $this->copyTypeDossierTest();
        $typeDossierEtapeInfo = $this->getTypeDossierDefinition()->getEtapeInfo(3,1);
        $this->assertEquals('mailsec',$typeDossierEtapeInfo->type);
        $typeDossierEtapeInfo = $this->getTypeDossierDefinition()->getEtapeInfo(3,2);
        $this->assertEquals('depot',$typeDossierEtapeInfo->type);
        $this->assertEquals(3,count($this->getTypeDossierDefinition()->getTypeDossierData(3)->etape));
        $this->getTypeDossierDefinition()->deleteEtape(3,1);
        $typeDossierEtapeInfo = $this->getTypeDossierDefinition()->getEtapeInfo(3,1);
        $this->assertEquals('depot',$typeDossierEtapeInfo->type);
        $this->assertEquals(2,count($this->getTypeDossierDefinition()->getTypeDossierData(3)->etape));
    }

    public function testSortEtape(){
        $this->copyTypeDossierTest();
        $typeDossierEtapeInfo = $this->getTypeDossierDefinition()->getEtapeInfo(3,0);
        $this->assertEquals('signature',$typeDossierEtapeInfo->type);
        $typeDossierEtapeInfo = $this->getTypeDossierDefinition()->getEtapeInfo(3,1);
        $this->assertEquals('mailsec',$typeDossierEtapeInfo->type);
        $typeDossierEtapeInfo = $this->getTypeDossierDefinition()->getEtapeInfo(3,2);
        $this->assertEquals('depot',$typeDossierEtapeInfo->type);
        $this->getTypeDossierDefinition()->sortEtape(3,[1,0,2]);
        $typeDossierEtapeInfo = $this->getTypeDossierDefinition()->getEtapeInfo(3,1);
        $this->assertEquals('signature',$typeDossierEtapeInfo->type);
        $typeDossierEtapeInfo = $this->getTypeDossierDefinition()->getEtapeInfo(3,0);
        $this->assertEquals('mailsec',$typeDossierEtapeInfo->type);
        $typeDossierEtapeInfo = $this->getTypeDossierDefinition()->getEtapeInfo(3,2);
        $this->assertEquals('depot',$typeDossierEtapeInfo->type);
    }

    public function testSortEtapeFailed(){
        $this->copyTypeDossierTest();
        $this->expectException(TypeDossierException::class);
        $this->expectExceptionMessage("Impossible de retrier le tableau");
        $this->getTypeDossierDefinition()->sortEtape(3,[1,0]);
    }

    public function testNewEtapeInfo(){
        $this->copyTypeDossierTest();
        $etapeInfo = $this->getTypeDossierDefinition()->getEtapeInfo(3,4);
        $this->assertEquals('new',$etapeInfo->num_etape);
    }


	/**
	 * @throws TypeDossierException
	 */
    public function testGetNextActionFirstEtape(){
		$this->copyTypeDossierTest();
		$this->assertEquals(
			'preparation-send-iparapheur',
			$this->getTypeDossierDefinition()->getNextAction(3,'modification')
		);
	}

	/**
	 * @throws TypeDossierException
	 */
	public function testGetNextAction(){
		$this->copyTypeDossierTest();
		$this->assertEquals(
			'preparation-envoi-mail',
			$this->getTypeDossierDefinition()->getNextAction(3,'recu-iparapheur')
		);
	}

	/**
	 * @throws TypeDossierException
	 */
	public function testGetLastAction(){
		$this->copyTypeDossierTest();
		$this->assertEquals(
			'termine',
			$this->getTypeDossierDefinition()->getNextAction(3,'send-ged')
		);
	}

}