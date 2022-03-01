<?php

class EntiteControler extends PastellControler
{
    public function _beforeAction()
    {
        parent::_beforeAction();
        $id_e = $this->getPostOrGetInfo()->getInt('id_e', 0);
        if ($id_e != 0) {
            $this->hasDroitLecture($id_e);
        }
        $this->setNavigationInfo($id_e, "Entite/detail?");
        $this->{'menu_gauche_template'} = "EntiteMenuGauche";
        $this->{'menu_gauche_select'} = "Entite/detail";
        $this->setDroitLectureOnConnecteur($id_e);
    }

    private function getAgentSQL()
    {
        return $this->getInstance(AgentSQL::class);
    }

    /**
     * @throws Exception
     */
    public function detailAction()
    {
        $recuperateur = new Recuperateur($_GET);
        $this->{'id_e'} = $recuperateur->getInt('id_e', 0);

        $this->{'has_many_collectivite'} = $this->hasManyCollectivite();
        $this->{'info'} = $this->getEntiteSQL()->getInfo($this->{'id_e'});

        if ($this->{'id_e'}) {
            $this->detailEntite();
        } else {
            $this->listEntite();
        }
    }

    private function setPageTitle($texte)
    {
        if ($this->isViewParameter('id_e')) {
            $info = $this->getEntiteSQL()->getInfo($this->{'id_e'});

            if ($info) {
                $texte = $info['denomination'] . " - $texte ";
            }
        }
        $this->{'page_title'} = $texte;
    }

    public function utilisateurAction()
    {
        $recuperateur = $this->getGetInfo();
        $id_e = $recuperateur->getInt('id_e', 0);
        $descendance = $recuperateur->get('descendance');
        $role = $recuperateur->get('role');
        $search = $recuperateur->get('search');
        $offset = $recuperateur->getInt('offset');
        $this->hasDroitLecture($id_e);

        $all_role = $this->getRoleSQL()->getAllRole();
        $all_role[] = array('role' => RoleUtilisateur::AUCUN_DROIT,'libelle' => RoleUtilisateur::AUCUN_DROIT);

        $this->{'all_role'} = $all_role;
        $this->{'droitEdition'} = $this->getRoleUtilisateur()->hasDroit($this->getId_u(), "utilisateur:edition", $id_e);

        $this->{'nb_utilisateur'} = $this->getUtilisateurListe()->getNbUtilisateur($id_e, $descendance, $role, $search);
        $this->{'liste_utilisateur'} = $this->getUtilisateurListe()->getAllUtilisateur($id_e, $descendance, $role, $search, $offset);
        $this->{'id_e'} = $id_e;
        $this->{'role_selected'} = !empty($role) ? $role : $recuperateur->get('role_selected');
        $this->{'offset'} = $offset;
        $this->{'search'} = $search;
        $this->{'descendance'} = $descendance;

        $this->{'template_milieu'} = "UtilisateurList";
        $this->{'menu_gauche_select'} = "Entite/utilisateur";
        $this->setPageTitle("Liste des utilisateurs");
        $this->renderDefault();
    }

    public function exportUtilisateurAction()
    {
        $recuperateur = $this->getGetInfo();
        $id_e = $recuperateur->getInt('id_e', 0);
        $descendance = $recuperateur->get('descendance');
        $the_role = $recuperateur->get('role_selected');
        $search = $recuperateur->get('search');

        $this->hasDroitLecture($id_e);

        $result = array();
        $result[] = array("id_u","login","prénom","nom","email","collectivité de base","id_e","rôles");

        $allUtilisateur = $this->getUtilisateurListe()->getAllUtilisateur($id_e, $descendance, $the_role, $search, -1);
        foreach ($allUtilisateur as $i => $user) {
            $r = array();
            foreach ($user['all_role'] as $role) {
                $r[] = ($role['libelle'] ?: "Aucun droit") . " - " . ($role['denomination'] ?: 'Entite racine');
            }
            $user['all_role'] = implode(",", $r);
            $result[]  = array($user['id_u'],$user['login'],
                $user['prenom'],$user['nom'],$user['email'],
                $user['denomination'] ?: "Entité racine",$user['id_e'],$user['all_role']);
        }

        $filename = "utilisateur-pastell-$id_e-$descendance-$the_role-$search.csv";

        /** @var CSVoutput $csvOutput */
        $csvOutput = $this->getInstance(CSVoutput::class);
        $csvOutput->send($filename, $result);
    }

    /**
     * @throws Exception
     */
    public function detailEntite()
    {
        $id_e = $this->getGetInfo()->getInt('id_e', 0);
        if (! $id_e) {
            throw new Exception("L'entité 0 n'existe pas");
        }
        $this->hasDroitLecture($id_e);
        $info = $this->getEntiteSQL()->getInfo($id_e);
        if (! $info) {
            $this->setLastError("Cette entité n'existe pas ou n'existe plus.");
            $this->redirect("/Entite/detail");
        }

        $this->{'droit_edition'} = $this->getRoleUtilisateur()->hasDroit($this->getId_u(), "entite:edition", $id_e);
        $this->{'droit_lecture_cdg'} = (isset($info['cdg']['id_e']) && $this->getRoleUtilisateur()->hasDroit($this->getId_u(), "entite:lecture", $info['cdg']['id_e']));
        $this->{'entiteExtendedInfo'} = $this->getEntiteSQL()->getExtendedInfo($id_e);
        $this->{'is_supprimable'} = $this->isSupprimable($id_e);

        $this->setPageTitle("Informations");

        $this->{'menu_gauche_select'} = "Entite/detail";

        $this->{'template_milieu'} = "EntiteDetail";
        $this->renderDefault();
    }

    public function hasManyCollectivite()
    {
        $liste_collectivite = $this->getRoleUtilisateur()->getEntiteWithDenomination($this->getId_u(), 'entite:lecture');
        $nbCollectivite = count($liste_collectivite);
        if ($nbCollectivite == 1) {
            return ($liste_collectivite[0]['id_e'] == 0 );
        }
        return true;
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws NotFoundException
     */
    public function listEntite()
    {
        $recuperateur = $this->getGetInfo();
        $offset = $recuperateur->getInt('offset', 0);
        $search = $recuperateur->get('search', '');

        $liste_collectivite = $this->getRoleUtilisateur()->getEntiteWithDenomination(
            $this->getId_u(),
            'entite:lecture'
        );
        $nbCollectivite = count($liste_collectivite);

        if ($nbCollectivite === 1 && $liste_collectivite[0]['id_e'] != 0) {
            $this->redirect("/Entite/detail?id_e=" . $liste_collectivite[0]['id_e']);
        }
        if ($nbCollectivite >= 1 && $liste_collectivite[0]['id_e'] == 0) {
            $liste_collectivite = $this->getEntiteListe()->getAllCollectivite($offset, $search);
            $nbCollectivite = $this->getEntiteListe()->getNbCollectivite($search);
        }
        $this->{'liste_collectivite'} = $liste_collectivite;
        $this->{'nbCollectivite'} = $nbCollectivite;
        $this->{'search'} = $search;
        $this->{'offset'} = $offset;

        $this->setPageTitle("Entité Racine");
        $this->{'menu_gauche_select'} = "Entite/detail";

        $this->{'template_milieu'} = "EntiteList";
        $this->renderDefault();
    }

    public function exportAction()
    {
        $id_e = $this->getGetInfo()->getInt('id_e', 0);
        $this->hasDroitLecture($id_e);

        $entite_list = $this->getEntiteListe()->getAllFille($id_e);
        $result = array(
            array(
                "ID_E","SIREN","DENOMINATION","TYPE","DATE INSCRIPTION","ACTIVE","CENTRE DE GESTION"
            )
        );

        foreach ($entite_list as $i => $entite_info) {
            $result[]  = array(
                $entite_info['id_e'],
                $entite_info['siren'],
                $entite_info['denomination'],
                $entite_info['type'],
                $entite_info['date_inscription'],
                $entite_info['is_active'],
                $entite_info['centre_de_gestion'],
            );
        }

        $filename = "entite-pastell-$id_e.csv";

        /** @var CSVoutput $csvOutput */
        $csvOutput = $this->getInstance(CSVoutput::class);
        $csvOutput->send($filename, $result);
    }

    public function importAction()
    {
        $recuperateur = new Recuperateur($_GET);
        $id_e = $recuperateur->getInt('id_e', 0);
        $page =  $recuperateur->getInt('page', 0);
        $this->hasDroitEdition($id_e);
        $this->{'entite_info'} = $this->getEntiteSQL()->getInfo($id_e);
        $this->{'template_milieu'} = "EntiteImport";
        $this->{'page_title'} = "Importer";

        if ($page == 0) {
            $this->{'allCDG'} = $this->getEntiteListe()->getAll(Entite::TYPE_CENTRE_DE_GESTION);
            $this->{'cdg_selected'} = false;
        }

        $this->{'onglet_tab'} = array("Collectivités","Agents","Grades");
        $onglet_content = array("EntiteImportCollectivite","EntiteImportAgent","EntiteImportGrade");
        $this->{'template_onglet'} = $onglet_content[$page];
        $this->{'page'} = $page;
        $this->{'id_e'} = $id_e;
        $this->renderDefault();
    }

    public function editionAction()
    {
        $recuperateur = new Recuperateur($_GET);
        $entite_mere = $recuperateur->getInt('entite_mere', 0);
        $id_e = $recuperateur->getInt('id_e', 0);
        if ($entite_mere) {
            $this->hasDroitEdition($entite_mere);
        }
        if ($id_e) {
            $this->hasDroitEdition($id_e);
        }
        if ($entite_mere == 0 && $id_e == 0) {
            $this->hasDroitEdition(0);
        }

        if ($id_e) {
            $infoEntite = $this->getEntiteSQL()->getInfo($id_e);
            $infoEntite['centre_de_gestion'] = $this->getEntiteSQL()->getCDG($id_e);
            $this->{'page_title'} = "Modification de " . $infoEntite['denomination'];
        } else {
            $infoEntite = $this->getEntiteInfoFromLastError();
            if ($entite_mere) {
                $this->{'infoMere'} = $this->getEntiteSQL()->getInfo($entite_mere);
                $this->{'page_title'} = "Ajout d'une entité fille pour " . $this->{'infoMere'}['denomination'];
            } else {
                $this->{'page_title'} = "Ajout d'une entité";
            }
        }
        $this->{'infoEntite'} = $infoEntite;
        $this->{'cdg_selected'} = $infoEntite['centre_de_gestion'];
        $this->{'allCDG'} = $this->getEntiteListe()->getAll(Entite::TYPE_CENTRE_DE_GESTION);
        $this->{'template_milieu'} = "EntiteEdition";
        $this->{'id_e'} = $id_e;
        $this->{'entite_mere'} = $entite_mere;

        $this->renderDefault();
    }

    private function getEntiteInfoFromLastError()
    {
        $field_list = array("type","denomination","siren","entite_mere","id_e","has_ged","has_archivage","centre_de_gestion");
        $infoEntite = array();
        foreach ($field_list as $field) {
            $infoEntite[$field] = $this->getLastError()->getLastInput($field);
        }
        return $infoEntite;
    }


    public function choixAction()
    {
        $recuperateur = new Recuperateur($_GET);
        $this->{'id_d'} = $recuperateur->get('id_d');
        $this->{'id_e'} =  $recuperateur->get('id_e');
        $this->{'action'} = $recuperateur->get('action');
        $this->{'type'} = $recuperateur->get('type', Entite::TYPE_COLLECTIVITE);

        if ($this->{'type'} == 'service') {
            $this->{'liste'} = $this->getEntiteListe()->getAllDescendant($this->{'id_e'});
        } else {
            $this->{'liste'} = $this->getEntiteListe()->getAll($this->{'type'});
        }

        if (! $this->{'liste'}) {
            $this->setLastError("Aucune entité ({$this->{'type'}}) n'est disponible pour cette action");
            $this->redirect("/Document/detail?id_e={$this->{'id_e'}}&id_d={$this->{'id_d'}}");
        }
        $this->{'page_title'} = "Veuillez choisir le ou les destinataires du document ";
        $this->{'template_milieu'} = "EntiteChoix";
        $this->renderDefault();
    }

    /**
     * @param $id_e
     * @param $nom
     * @param $siren
     * @param $type
     * @param $entite_mere
     * @param $centre_de_gestion
     * @return array|bool|mixed|string
     * @throws Exception
     */
    public function edition($id_e, $nom, $siren, $type, $entite_mere, $centre_de_gestion)
    {
        //  Suppression du controle des droits. Ce controle doit être remonté sur l'appelant
        if (!$nom) {
            throw new Exception("Le nom est obligatoire");
        }
        //Ajout du controle sur le type d'entité
        if (!$type || ($type != Entite::TYPE_SERVICE && $type != Entite::TYPE_CENTRE_DE_GESTION && $type != Entite::TYPE_COLLECTIVITE )) {
            throw new Exception("Le type d'entité doit être renseigné. Les valeurs possibles sont collectivite, service ou centre_de_gestion.");
        }

        if ($type == Entite::TYPE_SERVICE && ! $entite_mere) {
            throw new Exception("Un service doit être ataché à une entité mère (collectivité, centre de gestion ou service)");
        }

        if ($type != Entite::TYPE_SERVICE) {
            if (! $siren) {
                throw new Exception("Le siren est obligatoire");
            }
            // Pourquoi en modification, les sirens invalides sont acceptés ???

            /** @var Siren $siren */
            $siren = $this->getInstance(Siren::class);
            if (! ( $siren->isValid($siren) || ($id_e && $this->getEntiteSQL()->exists($id_e)))) {
                throw new Exception("Votre siren ne semble pas valide");
            }
        }

        /** @var EntiteCreator $entiteCreator */
        $entiteCreator = $this->getInstance(EntiteCreator::class);

        $id_e = $entiteCreator->edit($id_e, $siren, $nom, $type, $entite_mere, $centre_de_gestion);
        return $id_e;
    }

    public function doEditionAction()
    {
        $recuperateur = $this->getPostInfo();
        $id_e = $recuperateur->get('id_e');
        $entite_mere =  $recuperateur->get('entite_mere', 0);
        try {
            // Ajout du controle des droits qui ne se fait plus sur la function "edition" commune aux APIs et à la console Pastell
            if ($id_e) {
                $this->hasDroitEdition($id_e);
            }
            $this->hasDroitEdition($entite_mere);

            if ($id_e) {
                $this->apiPatch("/entite/$id_e");
            } else {
                $result = $this->apiPost("/entite");
                $id_e = $result['id_e'];
            }
        } catch (Exception $e) {
            $this->setLastError($e->getMessage());
            $this->redirect("/Entite/edition?id_e=$id_e&entite_mere=$entite_mere");
        }

        $this->getLastError()->deleteLastInput();
        $this->redirect("/Entite/detail?id_e=$id_e");
    }

    public function agentsAction()
    {
        $recuperateur = new Recuperateur($_GET);
        $id_e = $recuperateur->getInt('id_e', 0);
        $offset = $recuperateur->getInt('offset', 0);
        $page = $recuperateur->getInt('page', 0);
        $search = $recuperateur->get('search');

        $this->hasDroitLecture($id_e);
        $info = $this->getEntiteSQL()->getInfo($id_e);
        $id_ancetre = $this->getEntiteSQL()->getCollectiviteAncetre($id_e);
        if ($id_ancetre == $id_e) {
            $siren = $info['siren'];
        } else {
            $this->{'infoAncetre'} = $this->getEntiteSQL()->getInfo($id_ancetre);
            $siren = $this->{'infoAncetre'}['siren'];
        }

        /** @var AgentSQL $agentSQL */
        $agentSQL = $this->getInstance(AgentSQL::class);

        if ($id_e) {
            $this->{'nbAgent'} = $agentSQL->getNbAgent($siren, $search);
            $this->{'listAgent'} = $agentSQL->getBySiren($siren, $offset, $search);
        } else {
            $this->{'nbAgent'} = $agentSQL->getNbAllAgent($search);
            $this->{'listAgent'} = $agentSQL->getAllAgent($search, $offset);
        }
        $this->{'offset'} = $offset;
        $this->{'page'} = $page;
        $this->{'id_ancetre'} = $id_ancetre;
        $this->{'droit_edition'} = $this->getRoleUtilisateur()->hasDroit($this->getId_u(), "entite:edition", $id_e);
        $this->{'id_e'} = $id_e;
        $this->{'search'} = $search;
        $this->setPageTitle("Agents");
        $this->{'menu_gauche_select'} = "Entite/agents";
        $this->{'template_milieu'} = "AgentList";

        $this->renderDefault();
    }

    public function connecteurAction()
    {
        $recuperateur = new Recuperateur($_GET);
        $id_e = $recuperateur->getInt('id_e', 0);
        $this->hasConnecteurDroitLecture($id_e);
        $this->hasDroitLecture($id_e);
        $this->{'droit_edition'} = $this->getRoleUtilisateur()->hasDroit($this->getId_u(), "entite:edition", $id_e);
        $this->{'id_e'} = $id_e;
        $this->{'all_connecteur'} = $this->getConnecteurEntiteSQL()->getAll($id_e);
        if ($id_e) {
            $this->{'all_connecteur_definition'} =
                $this->getObjectInstancier()->getInstance(ConnecteurDefinitionFiles::class)->getAll();
        } else {
            $this->{'all_connecteur_definition'} =
                $this->getObjectInstancier()->getInstance(ConnecteurDefinitionFiles::class)->getAllGlobal();
        }
        $this->{'template_milieu'} = "ConnecteurList";
        $this->{'menu_gauche_select'} = "Entite/connecteur";
        $this->setPageTitle("Liste des connecteurs" . ($id_e ? "" : " globaux"));
        $this->setNavigationInfo($id_e, "Entite/connecteur?");
        $this->renderDefault();
    }

    /**
     * @deprecated 3.0.0 use FluxControler::indexAction instead
     *
     * Entite/flux => Flux/index
     *
     */
    public function fluxAction()
    {
        $id_e = $this->getGetInfo()->get('id_e');
        $this->redirect("Flux/index?id_e=$id_e");
    }

    private function isSupprimable($id_e)
    {
        if ($this->getDocumentEntite()->getNbAll($id_e)) {
            return false;
        }
        if (count($this->getEntiteSQL()->getFille($id_e))) {
            return false;
        }
        if ($this->getUtilisateurListe()->getNbUtilisateurWithEntiteDeBase($id_e)) {
            return false;
        }
        if ($this->getUtilisateurListe()->getNbUtilisateur($id_e)) {
            return false;
        }
        if ($this->getConnecteurEntiteSQL()->getAll($id_e)) {
            return false;
        }
        return true;
    }

    public function supprimerAction()
    {
        $recuperateur = new Recuperateur($_GET);
        $id_e = $recuperateur->getInt('id_e', 0);
        $this->hasDroitEdition($id_e);

        if (! $this->isSupprimable($id_e)) {
            $this->setLastError("L'entité ne peut pas être supprimée");
            $this->redirect("/Entite/detail?id_e=$id_e");
        }

        $info = $this->getEntiteSQL()->getInfo($id_e);
        $this->getJournal()->add(Journal::MODIFICATION_ENTITE, $info['entite_mere'], $this->getId_u(), "Suppression", "Suppression de l'entité $id_e qui contenait : \n" . implode("\n,", $info));
        $this->getEntiteSQL()->delete($id_e);

        $this->setLastMessage("L'entité « {$info['denomination']} » a été supprimée");
        $this->redirect("/Entite/detail?id_e={$info['entite_mere']}");
    }

    public function activerAction()
    {
        $recuperateur = new Recuperateur($_GET);
        $id_e = $recuperateur->getInt('id_e', 0);
        $active = $recuperateur->getInt('active', 0);
        $this->hasDroitEdition($id_e);

        $this->getEntiteSQL()->setActive($id_e, $active);
        $info = $this->getEntiteSQL()->getInfo($id_e);
        if ($active) {
            $message = "L'entite «{$info['denomination']}» est désormais " . ($info['is_active'] ? 'active' : 'inactive');
            $this->setLastMessage($message);
        }

        $this->redirect("/Entite/detail?id_e=$id_e");
    }

    public function importAgentAction()
    {
        $recuperateur = new Recuperateur($_POST);

        $id_e = $recuperateur->getInt('id_e');

        $delete_all = $recuperateur->get('delete_all');

        $this->verifDroit(0, "entite:edition");

        $fileUploader = new FileUploader();
        $file_path = $fileUploader->getFilePath('csv_agent');
        if (! $file_path) {
            $this->setLastError("Impossible de lire le fichier : " . $fileUploader->getLastError());
            $this->redirect("/Entite/import?page=1");
        }

        $CSV = new CSV();

        $infoCollectivite = array();
        if ($id_e) {
            $entiteSQL = $this->getEntiteSQL();
            $infoCollectivite = $entiteSQL->getInfo($id_e);
            $this->getAgentSQL()->clean($infoCollectivite['siren']);
        } elseif ($delete_all) {
            $this->getAgentSQL()->cleanAll();
        }

        $fileContent = $CSV->get($file_path);

        $nb_agent = 0;
        foreach ($fileContent as $col) {
            if (count($col) != 14) {
                continue;
            }
            $this->getAgentSQL()->add($col, $infoCollectivite);
            $nb_agent++;
        }


        $this->setLastMessage("$nb_agent agents ont été créées");
        $this->redirect("/Entite/import?page=1&id_e=$id_e");
    }

    public function doImportAction()
    {

        $recuperateur = new Recuperateur($_POST);

        $id_e = $recuperateur->getInt('id_e', 0);
        $centre_de_gestion = $recuperateur->getInt('centre_de_gestion');
        $this->verifDroit($id_e, "entite:edition");


        $fileUploader = new FileUploader();
        $file_path = $fileUploader->getFilePath('csv_col');
        if (! $file_path) {
            $this->setLastError("Impossible de lire le fichier");
            $this->redirect("/Entite/import?id_e=$id_e");
        }

        $CSV = new CSV();
        $colList = $CSV->get($file_path);

        $entiteCreator = new EntiteCreator($this->getSQLQuery(), $this->getJournal());
        $nb_col = 0;
        foreach ($colList as $col) {
            $entiteCreator->edit(0, $col[1], $col[0], Entite::TYPE_COLLECTIVITE, $id_e, $centre_de_gestion);
            $nb_col++;
        }

        $this->setLastMessage("$nb_col collectivités ont été créées");
        $this->redirect("/Entite/detail/?id_e=$id_e");
    }

    public function importGradeAction()
    {
        $this->verifDroit(0, "entite:edition");

        $fileUploader = new FileUploader();
        $file_path = $fileUploader->getFilePath('csv_grade');

        if (! $file_path) {
            $this->setLastError("Impossible de lire le fichier : " . $fileUploader->getLastError());
            $this->redirect("/Entite/import?page=1");
        }

        $CSV = new CSV();
        $gradeSQL = new GradeSQL($this->getSQLQuery());
        $gradeSQL->clean();

        $fileContent = $CSV->get($file_path);

        $nb_grade = 0;
        foreach ($fileContent as $info) {
            if (count($info) != 6) {
                continue;
            }
            $gradeSQL->add($info);
            $nb_grade++;
        }
        $this->setLastMessage("$nb_grade grades ont été importés");
        $this->redirect("/Entite/import?page=2");
    }
}
