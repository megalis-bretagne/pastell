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


	public function testMappingWhenHasSameEtape(){
		$typeDossierDefintion = $this->getObjectInstancier()->getInstance(TypeDossierDefinition::class);
		$typeDossierData = $typeDossierDefintion->getTypeDossierFromArray(json_decode(file_get_contents(__DIR__."/fixtures/type_dossier_double_parapheur.json"),true));
		$typeDossierEtape = $typeDossierData->etape[1];
		$typeDossierEtapeDefinition  = $this->getObjectInstancier()->getInstance(TypeDossierEtapeDefinition::class);
		$mapping = $typeDossierEtapeDefinition->getMapping($typeDossierEtape)->getAll();

		$this->assertEquals(array (
			'i-Parapheur' => 'i-Parapheur #2',
			'iparapheur_type' => 'iparapheur_type_2',
			'iparapheur_sous_type' => 'iparapheur_sous_type_2',
			'json_metadata' => 'json_metadata_2',
			'has_date_limite' => 'has_date_limite_2',
			'date_limite' => 'date_limite_2',
			'Signature' => 'Signature #2',
			'iparapheur_dossier_id' => 'iparapheur_dossier_id_2',
			'iparapheur_historique' => 'iparapheur_historique_2',
			'has_signature' => 'has_signature_2',
			'signature' => 'signature_2',
			'bordereau' => 'bordereau_2',
			'document_original' => 'document_original_2',
			'iparapheur_annexe_sortie' => 'iparapheur_annexe_sortie_2',
			'preparation-send-iparapheur' => 'preparation-send-iparapheur_2',
			'send-iparapheur' => 'send-iparapheur_2',
			'verif-iparapheur' => 'verif-iparapheur_2',
			'erreur-verif-iparapheur' => 'erreur-verif-iparapheur_2',
			'recu-iparapheur' => 'recu-iparapheur_2',
			'rejet-iparapheur' => 'rejet-iparapheur_2',
			'iparapheur-sous-type' => 'iparapheur-sous-type_2',
		),$mapping);
	}

	public function testMappingWhenHasNotSameEtape(){
		$typeDossierDefintion = $this->getObjectInstancier()->getInstance(TypeDossierDefinition::class);
		$typeDossierData = $typeDossierDefintion->getTypeDossierFromArray(json_decode(file_get_contents(__DIR__."/fixtures/type_dossier_ged_only.json"),true));
		$typeDossierEtape = $typeDossierData->etape[0];
		$typeDossierEtapeDefinition  = $this->getObjectInstancier()->getInstance(TypeDossierEtapeDefinition::class);
		$mapping = $typeDossierEtapeDefinition->getMapping($typeDossierEtape)->getAll();
		$this->assertEmpty($mapping);
	}


	public function testGetFormulaireWhenHasSameEtape(){
		$typeDossierEtapeDefinition = $this->getObjectInstancier()->getInstance(TypeDossierEtapeDefinition::class);

		$typeDossierEtape = new TypeDossierEtape();
		$typeDossierEtape->type = 'signature';
		$typeDossierEtape->num_etape_same_type = 1;
		$typeDossierEtape->etape_with_same_type_exists = true;

		$action_list = $typeDossierEtapeDefinition->getFormulaireForEtape($typeDossierEtape);

		$this->assertEquals(array (
			'i-Parapheur #2' =>
				array (
					'iparapheur_type_2' =>
						array (
							'name' => 'Type iParapheur',
							'read-only' => true,
						),
					'iparapheur_sous_type_2' =>
						array (
							'name' => 'Sous-type i-Parapheur',
							'requis' => true,
							'index' => true,
							'read-only' => true,
							'type' => 'externalData',
							'choice-action' => 'iparapheur-sous-type_2',
							'link_name' => 'Sélectionner un sous-type',
						),
					'json_metadata_2' =>
						array (
							'name' => 'Métadonnées parapheur (JSON)',
							'commentaire' => 'Au format JSON {clé:valeur,...}',
							'type' => 'file',
						),
					'has_date_limite_2' =>
						array (
							'name' => 'Utiliser une date limite',
							'type' => 'checkbox',
						),
					'date_limite_2' =>
						array (
							'name' => 'Date limite',
							'type' => 'date',
						),
				),
			'Signature #2' =>
				array (
					'iparapheur_dossier_id_2' =>
						array (
							'name' => '#ID dossier parapheur',
						),
					'iparapheur_historique_2' =>
						array (
							'name' => 'Historique iparapheur',
							'type' => 'file',
						),
					'has_signature_2' =>
						array (
							'no-show' => true,
						),
					'signature_2' =>
						array (
							'name' => 'Signature détachée',
							'type' => 'file',
						),
					'bordereau_2' =>
						array (
							'name' => 'Bordereau de signature',
							'type' => 'file',
						),
					'document_original_2' =>
						array (
							'name' => 'Document original',
							'type' => 'file',
						),
					'iparapheur_annexe_sortie_2' =>
						array (
							'name' => 'Annexe(s) de sortie du parapheur',
							'type' => 'file',
							'multiple' => true,
						),
				),
		),$action_list);
	}



	public function testGetActionWhenHasSameEtape(){
		$typeDossierEtapeDefinition = $this->getObjectInstancier()->getInstance(TypeDossierEtapeDefinition::class);

		$typeDossierEtape = new TypeDossierEtape();
		$typeDossierEtape->type = 'signature';
		$typeDossierEtape->num_etape_same_type = 1;
		$typeDossierEtape->etape_with_same_type_exists = true;

		$action_list = $typeDossierEtapeDefinition->getActionForEtape($typeDossierEtape);

		$this->assertEquals(array (
			'preparation-send-iparapheur_2' =>
				array (
					'name' => 'Préparation de l\'envoi au parapheur',
					'rule' =>
						array (
							'role_id_e' => 'no-role',
						),
					'action-automatique' => 'send-iparapheur_2',
				),
			'send-iparapheur_2' =>
				array (
					'name-action' => 'Transmettre au parapheur',
					'name' => 'Transmis au parapheur',
					'rule' =>
						array (
							'last-action' =>
								array (
									0 => 'preparation-send-iparapheur_2',
								),
						),
					'action-class' => 'StandardAction',
					'connecteur-type' => 'signature',
					'connecteur-type-action' => 'SignatureEnvoie',
					'connecteur-type-mapping' =>
						array (
							'iparapheur_type' => 'iparapheur_type_2',
							'iparapheur_sous_type' => 'iparapheur_sous_type_2',
							'iparapheur_dossier_id' => 'iparapheur_dossier_id_2',
							'json_metadata' => 'json_metadata_2'
						),
					'action-automatique' => 'verif-iparapheur_2',
				),
			'verif-iparapheur_2' =>
				array (
					'name-action' => 'Vérifier le statut de signature',
					'name' => 'Vérification de la signature',
					'rule' =>
						array (
							'last-action' =>
								array (
									0 => 'erreur-verif-iparapheur_2',
									1 => 'send-iparapheur_2',
								),
						),
					'action-class' => 'StandardAction',
					'connecteur-type' => 'signature',
					'connecteur-type-action' => 'SignatureRecuperation',
					'connecteur-type-mapping' =>
						array (
							'iparapheur_historique' => 'iparapheur_historique_2',
							'has_signature' => 'has_signature_2',
							'signature' => 'signature_2',
							'document_original' => 'document_original_2',
							'bordereau' => 'bordereau_2',
							'iparapheur_annexe_sortie' => 'iparapheur_annexe_sortie_2',
							'iparapheur_dossier_id' => 'iparapheur_dossier_id_2',
							'recu-iparapheur' => 'recu-iparapheur_2',
							'rejet-iparapheur' => 'rejet-iparapheur_2',
							'erreur-verif-iparapheur' => 'erreur-verif-iparapheur_2',
						),
				),
			'erreur-verif-iparapheur_2' =>
				array (
					'name' => 'Erreur lors de la vérification du statut de signature',
					'rule' =>
						array (
							'role_id_e' => 'no-role',
						),
				),
			'recu-iparapheur_2' =>
				array (
					'name' => 'Signature récuperée',
					'rule' =>
						array (
							'role_id_e' => 'no-role',
						),
					'action-automatique' => 'orientation',
				),
			'rejet-iparapheur_2' =>
				array (
					'name' => 'Signature refusée',
					'rule' =>
						array (
							'role_id_e' => 'no-role',
						),
				),
			'iparapheur-sous-type_2' =>
				array (
					'name' => 'Liste des sous-type iParapheur',
					'no-workflow' => true,
					'rule' =>
						array (
							'role_id_e' => 'no-role',
						),
					'action-class' => 'IparapheurSousType',
					'connecteur-type-mapping' =>
						array (
							'iparapheur_type' => 'iparapheur_type_2',
							'iparapheur_sous_type' => 'iparapheur_sous_type_2',
						),
				),
		),$action_list);
	}

	public function testGetPageCondition(){
		$typeDossierEtapeDefinition = $this->getObjectInstancier()->getInstance(TypeDossierEtapeDefinition::class);

		$typeDossierEtape = new TypeDossierEtape();
		$typeDossierEtape->type = 'signature';
		$typeDossierEtape->num_etape_same_type = 1;
		$typeDossierEtape->etape_with_same_type_exists = true;

		$page_condition = $typeDossierEtapeDefinition->getPageCondition($typeDossierEtape);

		$this->assertEquals(array (
			'i-Parapheur #2' => [],
			'Signature #2' =>
				array (
					'has_signature_2' => true,
				),
		),$page_condition);
	}


}