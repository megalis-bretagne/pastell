<?php

use Pastell\Service\Connecteur\ConnecteurAssociationService;

class FluxControler extends PastellControler
{
    public function _beforeAction()
    {
        parent::_beforeAction();
        $id_e = $this->getPostOrGetInfo()->getInt('id_e');

        $this->hasDroitLecture($id_e);
        $this->setNavigationInfo($id_e, "Flux/index?");
        $this->setViewParameter('menu_gauche_template', "EntiteMenuGauche");
        $this->setViewParameter('menu_gauche_select', "Flux/index");
        $this->setDroitLectureOnConnecteur($id_e);
        $this->setDroitImportExportConfig($id_e);
        $this->setDroitLectureOnUtilisateur($id_e);
    }

    public function hasDroitEdition($id_e): void
    {
        $this->hasConnecteurDroitEdition($id_e);
    }

    public function hasDroitLecture($id_e): void
    {
        $this->hasConnecteurDroitLecture($id_e);
    }

    /**
     * @return ConnecteurAssociationService
     */
    private function getConnecteurAssociationService(): ConnecteurAssociationService
    {
        return $this->getObjectInstancier()->getInstance(ConnecteurAssociationService::class);
    }

    /**
     * @throws NotFoundException
     */
    public function indexAction()
    {
        $id_e = $this->getGetInfo()->getInt('id_e', 0);
        $this->hasDroitLecture($id_e);
        $this->setViewParameter('id_e', $id_e);

        if ($id_e) {
            /** @var FluxEntiteHeritageSQL $fluxEntiteHeritageSQL */
            $fluxEntiteHeritageSQL = $this->getInstance(FluxEntiteHeritageSQL::class);
            $this->setViewParameter('id_e_mere', $this->getEntiteSQL()->getEntiteMere($id_e));
            $this->setViewParameter('all_herited', $fluxEntiteHeritageSQL->hasInheritanceAllFlux($id_e));

            $fluxList = $this->getFluxDefinitionFiles()->getAll();
            foreach ($fluxList as $fluxId => $fluxInfo) {
                $fluxList[$fluxId]['nb_connector'] = 0;
                foreach ($this->getConnectorForFlux($id_e, $fluxId) as $connectorInfo) {
                    if ($connectorInfo['connecteur_info']) {
                        $fluxList[$fluxId]['nb_connector']++;
                    }
                }
                unset($fluxList[$fluxId]['formulaire'], $fluxList[$fluxId]['action']);
            }

            $possibleFluxList = $this->apiGet('/flux');
            foreach ($possibleFluxList as $fluxId => $fluxInfo) {
                if (empty($fluxList[$fluxId]['connecteur'])) {
                    unset($possibleFluxList[$fluxId]);
                }
            }
            foreach ($fluxList as $fluxId => $fluxInfo) {
                if ($fluxInfo['nb_connector'] === 0) {
                    unset($fluxList[$fluxId]);
                }
            }
            $this->setViewParameter('flux_list', $fluxList);

            $this->setViewParameter('possible_flux_list', $possibleFluxList);
            $this->setViewParameter('template_milieu', "FluxList");
        } else {
            $all_connecteur_type = $this->getConnecteurDefinitionFiles()->getAllGlobalType();
            $all_type = [];
            foreach ($all_connecteur_type as $connecteur_type) {
                try {
                    $global_connecteur = $this->getConnecteurFactory()->getGlobalConnecteur($connecteur_type);
                } catch (Exception $e) {
                    $global_connecteur =  false;
                }
                $all_type[$connecteur_type] = $global_connecteur;
            }

            $this->setViewParameter('all_connecteur_type', $all_type);
            $this->setViewParameter('all_flux_entite', $this->getFluxEntiteSQL()->getAllWithSameType($id_e));
            if (isset($this->getViewParameterOrObject('all_flux_entite')['global'])) {
                $this->setViewParameter('all_flux_global', $this->getViewParameterOrObject('all_flux_entite')['global']);
            } else {
                $this->setViewParameter('all_flux_global', []);
            }
            $this->setViewParameter('template_milieu', "FluxGlobalList");
        }
        $this->setNavigationInfo($id_e, "Flux/index?");
        $this->setViewParameter('menu_gauche_select', "Flux/index");
        $this->setViewParameter('entite_denomination', $this->getEntiteSQL()->getDenomination($this->getViewParameterOrObject('id_e')));
        $this->setViewParameter('page_title', "{$this->getViewParameterOrObject('entite_denomination')} : " . ($id_e ? 'Liste des types de dossier' : 'Associations connecteurs globaux'));

        $this->renderDefault();
    }

    public function detailAction(): void
    {
        $id_e = $this->getGetInfo()->getInt('id_e', 0);
        if ($id_e === 0) {
            $this->redirect("/Flux/index?id_e=0");
        }
        $this->hasDroitLecture($id_e);
        $this->setViewParameter('id_e', $id_e);

        $flux = $this->getGetInfo()->get('flux', '');
        if ($flux === '') {
            $this->redirect("/Flux/index?id_e=$id_e");
        }

        $fluxEntiteHeritageSQL = $this->getInstance(FluxEntiteHeritageSQL::class);
        $this->setViewParameter('id_e_mere', $this->getEntiteSQL()->getEntiteMere($id_e));
        $this->setViewParameter('all_herited', $fluxEntiteHeritageSQL->hasInheritanceAllFlux($id_e));
        $this->setViewParameter('flux_connecteur_list', $this->getConnectorForFlux($id_e, $flux));
        $this->setViewParameter('template_milieu', "FluxDetail");

        $this->setNavigationInfo($id_e, "Flux/index?");
        $this->setViewParameter('menu_gauche_select', "Flux/index");
        $this->setViewParameter('entite_denomination', $this->getEntiteSQL()->getDenomination($id_e));

        $documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($flux);

        $this->setViewParameter('subtitle', sprintf("Type de dossier « %s » (%s)", $documentType->getName(), $documentType->getModuleId()));

        $this->setViewParameter(
            'page_title',
            sprintf(
                '%s : Association pour le type de dossier %s',
                $this->getViewParameterByKey('entite_denomination'),
                $documentType->getName()
            )
        );

        $this->renderDefault();
    }

    /**
     * @throws NotFoundException
     */
    public function editionAction()
    {
        $this->setViewParameter('id_e', $this->getGetInfo()->getInt('id_e'));
        $this->setViewParameter('flux', $this->getGetInfo()->get('flux', ''));
        $this->setViewParameter('type_connecteur', $this->getGetInfo()->get('type'));
        $this->setViewParameter('num_same_type', $this->getGetInfo()->getInt('num_same_type'));

        $this->hasDroitEdition($this->getViewParameterOrObject('id_e'));
        $this->setViewParameter('entite_denomination', $this->getEntiteSQL()->getDenomination($this->getViewParameterOrObject('id_e')));

        $this->setViewParameter('connecteur_disponible', $this->getConnecteurDispo($this->getViewParameterOrObject('id_e'), $this->getViewParameterOrObject('type_connecteur')));
        $this->setViewParameter(
            'connecteur_info',
            $this->getFluxEntiteSQL()->getConnecteur(
                $this->getViewParameterOrObject('id_e'),
                $this->getViewParameterOrObject('flux'),
                $this->getViewParameterOrObject('type_connecteur'),
                $this->getViewParameterOrObject('num_same_type')
            )
        );

        $all_info = $this->getDocumentTypeFactory()->getFluxDocumentType($this->getViewParameterOrObject('flux'))->getConnecteurAllInfo();
        $type_connecteur_info = [];
        foreach ($all_info as $connecteur_info) {
            if ($connecteur_info['connecteur_id'] == $this->getViewParameterOrObject('type_connecteur')) {
                if ($connecteur_info['num_same_type'] == $this->getViewParameterOrObject('num_same_type')) {
                    $type_connecteur_info = $connecteur_info;
                    break;
                }
            }
        }

        $this->setViewParameter('type_connecteur_info', $type_connecteur_info);

        if ($this->getViewParameterOrObject('flux')) {
            $this->setViewParameter('flux_name', $this->getDocumentTypeFactory()->getFluxDocumentType($this->getViewParameterOrObject('flux'))->getName()) ;
        } else {
            $this->setViewParameter('flux_name', "global");
        }

        $this->setViewParameter('page_title', "{$this->getViewParameterOrObject('entite_denomination')} : Association d'un connecteur et d'un type de dossier");
        $this->setViewParameter('template_milieu', "FluxEdition");
        $this->renderDefault();
    }

    private function getConnecteurDispo($id_e, $type_connecteur)
    {
        /** @var ConnecteurDisponible $connecteurDisponible */
        $connecteurDisponible = $this->getInstance(ConnecteurDisponible::class);

        $connecteur_disponible = $connecteurDisponible->getList($this->getId_u(), $id_e, $type_connecteur);

        $this->getConnecteurEntiteSQL()->getDisponible($id_e, $type_connecteur);
        if (! $connecteur_disponible) {
            $this->setLastError("Aucun connecteur « $type_connecteur » disponible !");
            $this->redirect("/Flux/index?id_e=$id_e");
        } // @codeCoverageIgnore

        return $connecteur_disponible;
    }

    public function doEditionAction()
    {
        $id_e = $this->getPostInfo()->getInt('id_e');
        $flux = $this->getPostInfo()->get('flux');
        $type = $this->getPostInfo()->get('type');
        $id_ce = $this->getPostInfo()->getInt('id_ce');
        $num_same_type = $this->getPostInfo()->getInt('num_same_type');

        $this->hasDroitEdition($id_e);
        try {
            if ($id_ce) {
                $this->getConnecteurAssociationService()->addConnecteurAssociation(
                    $id_e,
                    $id_ce,
                    $type,
                    $this->getId_u(),
                    $flux,
                    $num_same_type
                );
                $this->setLastMessage("Connecteur associé avec succès");
            } else {
                $this->getConnecteurAssociationService()->deleteConnecteurAssociation(
                    $id_e,
                    $type,
                    $this->getId_u(),
                    $flux,
                    $num_same_type
                );
                $this->setLastMessage("Connecteur dissocié avec succès");
            }
        } catch (Exception $ex) {
            $this->setLastError($ex->getMessage());
        }
        if ($id_e) {
            $this->redirect("/Flux/detail?id_e=$id_e&flux=$flux");
        } else {
            $this->redirect("/Flux/index?id_e=$id_e");
        }
    }

    public function getConnectorForFlux(int $id_e, string $id_flux): array
    {
        $fluxEntiteHeritageSQL = $this->getObjectInstancier()->getInstance(FluxEntiteHeritageSQL::class);
        $all_flux_entite = $fluxEntiteHeritageSQL->getAllWithSameType($id_e);

        $result = [];
        $documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($id_flux);
        foreach ($documentType->getConnecteurAllInfo() as $j => $connecteur_type_info) {
            $connecteur_id = $connecteur_type_info[DocumentType::CONNECTEUR_ID];
            $line = [];
            $line['nb_connecteur'] = count($documentType->getConnecteur());
            $line['num_connecteur'] = $j;
            $line['id_flux'] = $id_flux;
            $line['nom_flux'] = $documentType->getName();
            $line['connecteur_type'] = $connecteur_id;
            $line[DocumentType::CONNECTEUR_WITH_SAME_TYPE] = $connecteur_type_info[DocumentType::CONNECTEUR_WITH_SAME_TYPE];
            $line[DocumentType::NUM_SAME_TYPE] = $connecteur_type_info[DocumentType::NUM_SAME_TYPE];
            $line['inherited_flux'] = false;
            if (isset($all_flux_entite[$id_flux][$connecteur_id][$line[DocumentType::NUM_SAME_TYPE]])) {
                $line['connecteur_info'] = $all_flux_entite[$id_flux][$connecteur_id][$line[DocumentType::NUM_SAME_TYPE]];
            } else {
                $line['connecteur_info'] = false;
            }
            if (isset($all_flux_entite[$id_flux]['inherited_flux'])) {
                $line['inherited_flux'] = $all_flux_entite[$id_flux]['inherited_flux'];
            }
            $result[] = $line;
        }
        return $result;
    }

    public function toogleHeritageAction()
    {
        /** @var FluxEntiteHeritageSQL $fluxEntiteHeritageSQL */
        $fluxEntiteHeritageSQL = $this->getInstance(FluxEntiteHeritageSQL::class);

        $id_e = $this->getPostInfo()->getInt('id_e');
        $flux = $this->getPostInfo()->get('flux');
        $this->hasDroitEdition($id_e);
        $fluxEntiteHeritageSQL->toogleInheritance($id_e, $flux);
        $this->setLastMessage("L'héritage a été modifié");
        if ($flux === FluxEntiteHeritageSQL::ALL_FLUX) {
            $this->redirect("/Flux/index?id_e=$id_e");
        }
        $this->redirect("/Flux/detail?id_e=$id_e&flux=$flux");
    }
}
