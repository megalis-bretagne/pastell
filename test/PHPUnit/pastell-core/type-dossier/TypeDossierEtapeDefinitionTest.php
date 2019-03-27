<?php

class TypeDossierEtapeDefinitionTest extends PastellTestCase {

	public function testWhenHasEtapeWithSameType(){
		$typeDossierEtapeDefinition = $this->getObjectInstancier()->getInstance(TypeDossierEtapeDefinition::class);

		$typeDossierEtape = new TypeDossierEtape();
		$typeDossierEtape->type = 'depot';
		$typeDossierEtape->num_etape_same_type = 1;
		$typeDossierEtape->etape_with_same_type_exists = true;

		$action_list = $typeDossierEtapeDefinition->getActionForEtape($typeDossierEtape);

		$this->assertEquals(array (
			'preparation-send-ged_2' =>
				array (
					'name' => 'Préparation de l\'envoi à la GED',
					'rule' =>
						array (
							'role_id_e' => 'no-role',
						),
					'action-automatique' => 'send-ged_2',
				),
			'send-ged_2' =>
				array (
					'name-action' => 'Verser à la GED',
					'name' => 'Versé à la GED',
					'rule' =>
						array (
							'last-action' =>
								array (
									0 => 'preparation-send-ged_2',
								),
						),
					'action-class' => 'PDFGeneriqueSendGED',
					'action-automatique' => 'orientation',
				),
		),$action_list);
	}

	public function testWhenHasNoEtapeWithSameType(){
		$typeDossierEtapeDefinition = $this->getObjectInstancier()->getInstance(TypeDossierEtapeDefinition::class);

		$typeDossierEtape = new TypeDossierEtape();
		$typeDossierEtape->type = 'depot';

		$action_list = $typeDossierEtapeDefinition->getActionForEtape($typeDossierEtape);

		$this->assertEquals(array (
			'preparation-send-ged' =>
				array (
					'name' => 'Préparation de l\'envoi à la GED',
					'rule' =>
						array (
							'role_id_e' => 'no-role',
						),
					'action-automatique' => 'send-ged',
				),
			'send-ged' =>
				array (
					'name-action' => 'Verser à la GED',
					'name' => 'Versé à la GED',
					'rule' =>
						array (
							'last-action' =>
								array (
									0 => 'preparation-send-ged',
								),
						),
					'action-class' => 'PDFGeneriqueSendGED',
					'action-automatique' => 'orientation',
				),
		),$action_list);
	}


}