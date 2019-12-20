<?php

class TypeDossierEtapeDefinitionTest extends PastellTestCase
{

    public function testWhenHasEtapeWithSameType()
    {
        $typeDossierEtapeDefinition = $this->getObjectInstancier()
            ->getInstance(TypeDossierEtapeManager::class);

        $typeDossierEtape = new TypeDossierEtapeProperties();
        $typeDossierEtape->type = 'depot';
        $typeDossierEtape->num_etape_same_type = 1;
        $typeDossierEtape->etape_with_same_type_exists = true;

        $action_list = $typeDossierEtapeDefinition->getActionForEtape($typeDossierEtape);

        $this->assertEquals(array(
            'preparation-send-ged_2' =>
                array(
                    'name' => 'Préparation de l\'envoi à la GED #2',
                    'rule' =>
                        array(
                            'role_id_e' => 'no-role',
                        ),
                    'action-automatique' => 'send-ged_2',
                ),
            'send-ged_2' =>
                array(
                    'name-action' => 'Verser à la GED #2',
                    'name' => 'Versé à la GED #2',
                    'rule' =>
                        array(
                            'last-action' =>
                                array(
                                    0 => 'preparation-send-ged_2',
                                    1 => 'error-ged_2'
                                ),
                        ),
                    'action-automatique' => 'orientation',
                    'action-class' => 'StandardAction',
                    'connecteur-type' => 'GED',
                    'connecteur-type-action' => 'GEDEnvoyer',
                    'connecteur-type-mapping' =>
                        array(
                            'fatal-error' => 'error-ged_2',
                        ),
                ),
            'error-ged_2' =>
                array(
                    'name' => 'Erreur irrécupérable lors du dépôt #2',
                    'rule' =>
                        array(
                            'role_id_e' => 'no-role',
                        ),
                ),
        ), $action_list);
    }

    public function testWhenHasNoEtapeWithSameType()
    {
        $typeDossierEtapeDefinition = $this->getObjectInstancier()->getInstance(TypeDossierEtapeManager::class);

        $typeDossierEtape = new TypeDossierEtapeProperties();
        $typeDossierEtape->type = 'depot';

        $action_list = $typeDossierEtapeDefinition->getActionForEtape($typeDossierEtape);
        $this->assertEquals(array(
            'preparation-send-ged' =>
                array(
                    'name' => 'Préparation de l\'envoi à la GED',
                    'rule' =>
                        array(
                            'role_id_e' => 'no-role',
                        ),
                    'action-automatique' => 'send-ged',
                ),
            'send-ged' =>
                array(
                    'name-action' => 'Verser à la GED',
                    'name' => 'Versé à la GED',
                    'rule' =>
                        array(
                            'last-action' =>
                                array(
                                    0 => 'preparation-send-ged',
                                    1 => 'error-ged'
                                ),
                        ),
                    'action-automatique' => 'orientation',
                    'action-class' => 'StandardAction',
                    'connecteur-type' => 'GED',
                    'connecteur-type-action' => 'GEDEnvoyer',
                    'connecteur-type-mapping' =>
                        array(
                            'fatal-error' => 'error-ged',
                        ),
                ),
            'error-ged' =>
                array(
                    'name' => 'Erreur irrécupérable lors du dépôt',
                    'rule' =>
                        array(
                            'role_id_e' => 'no-role',
                        ),
                ),
        ), $action_list);
    }


    /**
     * @throws UnrecoverableException
     * @throws TypeDossierException
     */
    public function testMappingWhenHasSameEtape()
    {

        $typeDossierImportExport = $this->getObjectInstancier()->getInstance(TypeDossierImportExport::class);
        $typeDossierDefintion = $this->getObjectInstancier()->getInstance(TypeDossierService::class);
        $typeDossierEtapeDefinition = $this->getObjectInstancier()->getInstance(TypeDossierEtapeManager::class);

        $id_t = $typeDossierImportExport->importFromFilePath(__DIR__ . "/fixtures/double-parapheur.json")['id_t'];
        $typeDossierData = $typeDossierDefintion->getTypeDossierProperties($id_t);
        $typeDossierEtape = $typeDossierData->etape[1];

        $mapping = $typeDossierEtapeDefinition->getMapping($typeDossierEtape)->getAll();

        $this->assertEquals(array(
            'i-Parapheur' => 'i-Parapheur #2',
            'iparapheur_type' => 'iparapheur_type_2',
            'iparapheur_sous_type' => 'iparapheur_sous_type_2',
            'json_metadata' => 'json_metadata_2',
            'has_date_limite' => 'has_date_limite_2',
            'date_limite' => 'date_limite_2',
            'Signature' => 'Signature #2',
            'iparapheur_dossier_id' => 'iparapheur_dossier_id_2',
            'iparapheur_historique' => 'iparapheur_historique_2',
            'parapheur_last_message' => 'parapheur_last_message_2',
            'has_signature' => 'has_signature_2',
            'signature' => 'signature_2',
            'bordereau_signature' => 'bordereau_signature_2',
            'document_original' => 'document_original_2',
            'iparapheur_annexe_sortie' => 'iparapheur_annexe_sortie_2',
            'preparation-send-iparapheur' => 'preparation-send-iparapheur_2',
            'send-iparapheur' => 'send-iparapheur_2',
            'verif-iparapheur' => 'verif-iparapheur_2',
            'erreur-verif-iparapheur' => 'erreur-verif-iparapheur_2',
            'recu-iparapheur' => 'recu-iparapheur_2',
            'rejet-iparapheur' => 'rejet-iparapheur_2',
            'iparapheur-sous-type' => 'iparapheur-sous-type_2',
            'envoi_signature' => 'envoi_signature_2',
            'envoi_iparapheur' => 'envoi_iparapheur_2',
            'Parapheur FAST' => 'Parapheur FAST #2',
            'envoi_fast' => 'envoi_fast_2',
            'fast_parapheur_circuit' => 'fast_parapheur_circuit_2',
            'fast_parapheur_circuit_configuration' => 'fast_parapheur_circuit_configuration_2'
        ), $mapping);
    }

    /**
     * @throws UnrecoverableException
     * @throws TypeDossierException
     */
    public function testMappingWhenHasNotSameEtape()
    {

        $typeDossierImportExport = $this->getObjectInstancier()->getInstance(TypeDossierImportExport::class);
        $id_t = $typeDossierImportExport->importFromFilePath(__DIR__ . "/fixtures/ged-only.json")['id_t'];

        $typeDossierService = $this->getObjectInstancier()->getInstance(TypeDossierService::class);
        $typeDossierData = $typeDossierService->getTypeDossierProperties($id_t);
        $typeDossierEtape = $typeDossierData->etape[0];
        $typeDossierEtapeDefinition = $this->getObjectInstancier()->getInstance(TypeDossierEtapeManager::class);
        $mapping = $typeDossierEtapeDefinition->getMapping($typeDossierEtape)->getAll();
        $this->assertEmpty($mapping);
    }


    public function testGetFormulaireWhenHasSameEtape()
    {
        $typeDossierEtapeDefinition = $this->getObjectInstancier()->getInstance(TypeDossierEtapeManager::class);

        $typeDossierEtape = new TypeDossierEtapeProperties();
        $typeDossierEtape->type = 'signature';
        $typeDossierEtape->num_etape_same_type = 1;
        $typeDossierEtape->etape_with_same_type_exists = true;

        $action_list = $typeDossierEtapeDefinition->getFormulaireForEtape($typeDossierEtape);

        $this->assertEquals(array(
            'i-Parapheur #2' =>
                array(
                    'iparapheur_type_2' =>
                        array(
                            'name' => 'Type iParapheur',
                            'read-only' => true,
                        ),
                    'iparapheur_sous_type_2' =>
                        array(
                            'name' => 'Sous-type i-Parapheur',
                            'requis' => true,
                            'index' => true,
                            'read-only' => true,
                            'type' => 'externalData',
                            'choice-action' => 'iparapheur-sous-type_2',
                            'link_name' => 'Sélectionner un sous-type',
                        ),
                    'json_metadata_2' =>
                        array(
                            'name' => 'Métadonnées parapheur (JSON)',
                            'commentaire' => 'Au format JSON {"clé" : valeur,...}',
                            'type' => 'file',
                        ),
                    'has_date_limite_2' =>
                        array(
                            'name' => 'Utiliser une date limite',
                            'type' => 'checkbox',
                        ),
                    'date_limite_2' =>
                        array(
                            'name' => 'Date limite',
                            'type' => 'date',
                        ),
                    'envoi_iparapheur_2' => [
                        'no-show' => true
                    ]
                ),
            'Signature #2' =>
                array(
                    'iparapheur_dossier_id_2' =>
                        array(
                            'name' => '#ID dossier parapheur',
                        ),
                    'iparapheur_historique_2' =>
                        array(
                            'name' => 'Historique iparapheur',
                            'type' => 'file',
                        ),
                    'parapheur_last_message_2' => [
                        'name' => 'Dernier message reçu du parapheur',
                    ],
                    'has_signature_2' =>
                        array(
                            'no-show' => true,
                        ),
                    'signature_2' =>
                        array(
                            'name' => 'Signature détachée',
                            'type' => 'file',
                        ),
                    'bordereau_signature_2' => [
                        'name' => 'Bordereau de signature',
                        'type' => 'file',
                    ],
                    'document_original_2' =>
                        array(
                            'name' => 'Document original',
                            'type' => 'file',
                        ),
                    'iparapheur_annexe_sortie_2' =>
                        array(
                            'name' => 'Annexe(s) de sortie du parapheur',
                            'type' => 'file',
                            'multiple' => true,
                        ),
                ),
            'Parapheur FAST #2' => [
                'envoi_fast_2' => [
                    'no-show' => true
                ],
                'fast_parapheur_circuit_2' => [
                    'name' => 'Circuit sur le parapheur',
                    'type' => 'externalData',
                    'choice-action' => 'iparapheur-sous-type_2',
                    'link_name' => 'Liste des circuits'
                ],
                'fast_parapheur_circuit_configuration_2' => [
                    'name' => 'Configuration du circuit à la volée (au format JSON)',
                    'commentaire' => 'Si ce fichier est déposé, il remplace le circuit choisi dans le champ "Circuit sur le parapheur"',
                    'type' => 'file'
                ]
            ]
        ), $action_list);
    }


    public function testGetActionWhenHasSameEtape()
    {
        $typeDossierEtapeDefinition = $this->getObjectInstancier()->getInstance(TypeDossierEtapeManager::class);

        $typeDossierEtape = new TypeDossierEtapeProperties();
        $typeDossierEtape->type = 'signature';
        $typeDossierEtape->num_etape_same_type = 1;
        $typeDossierEtape->etape_with_same_type_exists = true;

        $action_list = $typeDossierEtapeDefinition->getActionForEtape($typeDossierEtape);

        $this->assertEquals(array(
            'preparation-send-iparapheur_2' =>
                array(
                    'name' => 'Préparation de l\'envoi au parapheur #2',
                    'rule' =>
                        array(
                            'role_id_e' => 'no-role',
                        ),
                    'action-automatique' => 'send-iparapheur_2',
                ),
            'send-iparapheur_2' =>
                array(
                    'name-action' => 'Transmettre au parapheur #2',
                    'name' => 'Transmis au parapheur #2',
                    'rule' =>
                        array(
                            'last-action' =>
                                array(
                                    0 => 'preparation-send-iparapheur_2',
                                ),
                        ),
                    'action-class' => 'StandardAction',
                    'connecteur-type' => 'signature',
                    'connecteur-type-action' => 'SignatureEnvoie',
                    'connecteur-type-mapping' =>
                        array(
                            'iparapheur_type' => 'iparapheur_type_2',
                            'iparapheur_sous_type' => 'iparapheur_sous_type_2',
                            'iparapheur_dossier_id' => 'iparapheur_dossier_id_2',
                            'json_metadata' => 'json_metadata_2',
                            'fast_parapheur_circuit' => 'fast_parapheur_circuit_2'
                        ),
                    'action-automatique' => 'verif-iparapheur_2',
                ),
            'verif-iparapheur_2' =>
                array(
                    'name-action' => 'Vérifier le statut de signature #2',
                    'name' => 'Vérification de la signature #2',
                    'rule' =>
                        array(
                            'last-action' =>
                                array(
                                    0 => 'erreur-verif-iparapheur_2',
                                    1 => 'send-iparapheur_2',
                                ),
                        ),
                    'action-class' => 'StandardAction',
                    'connecteur-type' => 'signature',
                    'connecteur-type-action' => 'SignatureRecuperation',
                    'connecteur-type-mapping' =>
                        array(
                            'iparapheur_historique' => 'iparapheur_historique_2',
                            'has_signature' => 'has_signature_2',
                            'signature' => 'signature_2',
                            'document_original' => 'document_original_2',
                            'bordereau' => 'bordereau_signature_2',
                            'iparapheur_annexe_sortie' => 'iparapheur_annexe_sortie_2',
                            'iparapheur_dossier_id' => 'iparapheur_dossier_id_2',
                            'recu-iparapheur' => 'recu-iparapheur_2',
                            'rejet-iparapheur' => 'rejet-iparapheur_2',
                            'erreur-verif-iparapheur' => 'erreur-verif-iparapheur_2',
                        ),
                ),
            'erreur-verif-iparapheur_2' =>
                array(
                    'name' => 'Erreur lors de la vérification du statut de signature #2',
                    'rule' =>
                        array(
                            'role_id_e' => 'no-role',
                        ),
                ),
            'recu-iparapheur_2' =>
                array(
                    'name' => 'Signature récuperée #2',
                    'rule' =>
                        array(
                            'role_id_e' => 'no-role',
                        ),
                    'action-automatique' => 'orientation',
                ),
            'rejet-iparapheur_2' =>
                array(
                    'name' => 'Signature refusée #2',
                    'rule' =>
                        array(
                            'role_id_e' => 'no-role',
                        ),
                ),
            'iparapheur-sous-type_2' =>
                array(
                    'name' => 'Liste des sous-type iParapheur #2',
                    'no-workflow' => true,
                    'rule' =>
                        array(
                            'role_id_e' => 'no-role',
                        ),
                    'action-class' => 'IparapheurSousType',
                    'connecteur-type-mapping' =>
                        array(
                            'iparapheur_type' => 'iparapheur_type_2',
                            'iparapheur_sous_type' => 'iparapheur_sous_type_2',
                            'fast_parapheur_circuit' => 'fast_parapheur_circuit_2'
                        ),
                ),
        ), $action_list);
    }

    public function testGetPageCondition()
    {
        $typeDossierEtapeDefinition = $this->getObjectInstancier()->getInstance(TypeDossierEtapeManager::class);

        $typeDossierEtape = new TypeDossierEtapeProperties();
        $typeDossierEtape->type = 'signature';
        $typeDossierEtape->num_etape_same_type = 1;
        $typeDossierEtape->etape_with_same_type_exists = true;

        $page_condition = $typeDossierEtapeDefinition->getPageCondition($typeDossierEtape);

        $this->assertEquals(array(
            'i-Parapheur #2' => [
                'envoi_iparapheur_2' => true
            ],
            'Signature #2' =>
                array(
                    'has_signature_2' => true,
                ),
            'Parapheur FAST #2' => [
                'envoi_fast_2' => true
            ]
        ), $page_condition);
    }
}
