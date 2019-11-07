<?php
class FluxControler extends PastellControler
{

    const FLUX_NUM_ONGLET = 4;

    public function _beforeAction()
    {
        parent::_beforeAction();
        $id_e = $this->getPostOrGetInfo()->getInt('id_e');

        $this->hasDroitLecture($id_e);
        $this->setNavigationInfo($id_e, "Flux/index?");
        $this->{'menu_gauche_template'} = "EntiteMenuGauche";
        $this->{'menu_gauche_select'} = "Flux/index";
    }

    /**
     * @throws NotFoundException
     */
    public function indexAction()
    {
        $id_e = $this->getGetInfo()->getInt('id_e', 0);
        $this->hasDroitLecture($id_e);
        $this->{'id_e'} = $id_e;

        if ($id_e) {
            /** @var FluxEntiteHeritageSQL $fluxEntiteHeritageSQL */
            $fluxEntiteHeritageSQL = $this->getInstance(FluxEntiteHeritageSQL::class);
            $this->{'id_e_mere'} = $this->getEntiteSQL()->getEntiteMere($id_e);
            $this->{'all_herited'} = $fluxEntiteHeritageSQL->hasInheritanceAllFlux($id_e);
            $this->{'flux_connecteur_list'} = $this->getListFlux($id_e);
            $this->{'template_milieu'} = "FluxList";
        } else {
            $all_connecteur_type = $this->getConnecteurDefinitionFiles()->getAllGlobalType();
            $all_type = array();
            foreach ($all_connecteur_type as $connecteur_type) {
                try {
                    $global_connecteur = $this->getConnecteurFactory()->getGlobalConnecteur($connecteur_type);
                } catch (Exception $e) {
                    $global_connecteur =  false;
                }
                $all_type[$connecteur_type] = $global_connecteur;
            }

            $this->{'all_connecteur_type'} = $all_type;
            $this->{'all_flux_entite'} = $this->getFluxEntiteSQL()->getAllWithSameType($id_e);
            if (isset($this->{'all_flux_entite'}['global'])) {
                $this->{'all_flux_global'} = $this->{'all_flux_entite'}['global'];
            } else {
                $this->{'all_flux_global'} = array();
            }
            $this->{'template_milieu'} = "FluxGlobalList";
        }
        $this->setNavigationInfo($id_e, "Flux/index?");
        $this->{'menu_gauche_select'} = "Flux/index";
        $this->{'entite_denomination'} = $this->getEntiteSQL()->getDenomination($this->{'id_e'});
        $this->{'page_title'} = "{$this->{'entite_denomination'}} : " . ($id_e ? 'Liste des types de dossier' : 'Associations connecteurs globaux');

        $this->renderDefault();
    }

    /**
     * @throws NotFoundException
     */
    public function editionAction()
    {
        $this->{'id_e'} = $this->getGetInfo()->getInt('id_e');
        $this->{'flux'} = $this->getGetInfo()->get('flux', '');
        $this->{'type_connecteur'} = $this->getGetInfo()->get('type');
        $this->{'num_same_type'} = $this->getGetInfo()->getInt('num_same_type');
        
        $this->hasDroitEdition($this->{'id_e'});
        $this->{'entite_denomination'} = $this->getEntiteSQL()->getDenomination($this->{'id_e'});
        
        $this->{'connecteur_disponible'} = $this->getConnecteurDispo($this->{'id_e'}, $this->{'type_connecteur'});
        $this->{'connecteur_info'} = $this->getFluxEntiteSQL()->getConnecteur($this->{'id_e'}, $this->{'flux'}, $this->{'type_connecteur'}, $this->{'num_same_type'});


        $all_info = $this->getDocumentTypeFactory()->getFluxDocumentType($this->{'flux'})->getConnecteurAllInfo();
        $type_connecteur_info = [];
        foreach ($all_info as $connecteur_info) {
            if ($connecteur_info['connecteur_id'] == $this->{'type_connecteur'}) {
                if ($connecteur_info['num_same_type'] == $this->{'num_same_type'}) {
                    $type_connecteur_info = $connecteur_info;
                    break;
                }
            }
        }

        $this->{'type_connecteur_info'} = $type_connecteur_info;

        if ($this->{'flux'}) {
            $this->{'flux_name'} = $this->getDocumentTypeFactory()->getFluxDocumentType($this->{'flux'})->getName() ;
        } else {
            $this->{'flux_name'} = "global";
        }
        
        $this->{'page_title'} = "{$this->{'entite_denomination'}} : Association d'un connecteur et d'un type de dossier";
        $this->{'template_milieu'} = "FluxEdition";
        $this->renderDefault();
    }

    private function getConnecteurDispo($id_e, $type_connecteur)
    {
        /** @var ConnecteurDisponible $connecteurDisponible */
        $connecteurDisponible = $this->getInstance("ConnecteurDisponible");

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
                $this->hasGoodType($id_ce, $type);
                $this->editionModif($id_e, $flux, $type, $id_ce, $num_same_type);
                $this->setLastMessage("Connecteur associé au type de dossier avec succès");
            } else {
                $this->getFluxEntiteSQL()->deleteConnecteur($id_e, $flux, $type);
                $this->setLastMessage("Connecteur désélectionné avec succès");
            }
        } catch (Exception $ex) {
            $this->setLastError($ex->getMessage());
        }
        $this->redirect("/Flux/index?id_e=$id_e");
    }

    /**
     * @param $id_ce
     * @param $type
     * @throws Exception
     */
    private function hasGoodType($id_ce, $type)
    {
        $info = $this->getConnecteurEntiteSQL()->getInfo($id_ce);
        if ($info['type'] != $type) {
            throw new UnrecoverableException("Le connecteur n'est pas du bon type :  {$info['type']} présenté, $type requis");
        }
    }

    /**
     * @param $id_e
     * @param $flux
     * @param $type
     * @param $id_ce
     * @return string
     * @throws Exception
     */
    public function editionModif($id_e, $flux, $type, $id_ce, $num_same_type = 0)
    {
        $this->hasGoodType($id_ce, $type);

        $info = $this->getConnecteurEntiteSQL()->getInfo($id_ce);
        $this->hasDroitEdition($info['id_e']);

        if ($flux != null) {
            $info = $this->getFluxDefinitionFiles()->getInfo($flux);
            if (!$info) {
                throw new UnrecoverableException("Le type de flux « $flux » n'existe pas.");
            }
        }
        return $this->getFluxEntiteSQL()->addConnecteur($id_e, $flux, $type, $id_ce, $num_same_type);
    }

    public function getListFlux($id_e)
    {
        $result = array();

        $fluxEntiteHeritageSQL = $this->getObjectInstancier()->getInstance(FluxEntiteHeritageSQL::class);

        $all_flux_entite = $fluxEntiteHeritageSQL->getAllWithSameType($id_e);

        foreach ($this->getFluxDefinitionFiles()->getAll() as $id_flux => $flux_definition) {
            $documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($id_flux);
            foreach ($documentType->getConnecteurAllInfo() as $j => $connecteur_type_info) {
                $connecteur_id = $connecteur_type_info[DocumentType::CONNECTEUR_ID];
                $line = array();
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
        }

        return $result;
    }
    
    public function toogleHeritageAction()
    {
        /** @var FluxEntiteHeritageSQL $fluxEntiteHeritageSQL */
        $fluxEntiteHeritageSQL = $this->getInstance("FluxEntiteHeritageSQL");

        $id_e = $this->getPostInfo()->getInt('id_e');
        $flux = $this->getPostInfo()->get('flux');
        $this->hasDroitEdition($id_e);
        $fluxEntiteHeritageSQL->toogleInheritance($id_e, $flux);
        $this->setLastMessage("L'héritage a été modifié");
        $this->redirect("/Flux/index?id_e=$id_e");
    }
}
