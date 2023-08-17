<?php

use Pastell\Service\Crypto;
use Pastell\Service\Entite\EntityCreationService;
use Pastell\Service\Entite\EntityUpdateService;
use Pastell\Service\FeatureToggleService;
use Pastell\Service\ImportExportConfig\ExportConfigService;
use Pastell\Service\ImportExportConfig\ImportConfigService;
use Pastell\Service\FeatureToggle\CDGFeature;
use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;

class EntiteControler extends PastellControler
{
    public function _beforeAction()
    {
        parent::_beforeAction();
        $id_e = $this->getPostOrGetInfo()->getInt('id_e', 0);
        if ($id_e != 0) {
            $this->hasEntiteDroitLecture($id_e);
        }
        $this->setNavigationInfo($id_e, "Entite/detail?");
        $this->setViewParameter(
            'droitLectureAnnuaire',
            $this->getRoleUtilisateur()->hasDroit($this->getId_u(), 'annuaire:lecture', $id_e)
        );
        $this->setViewParameter('menu_gauche_template', "EntiteMenuGauche");
        $this->setViewParameter('menu_gauche_select', "Entite/detail");
        $this->setDroitLectureOnConnecteur($id_e);
        $this->setDroitImportExportConfig($id_e);
        $this->setViewParameter('cdg_feature', $this->getObjectInstancier()
            ->getInstance(FeatureToggleService::class)
            ->isEnabled(CDGFeature::class));
        $this->setDroitLectureOnUtilisateur($id_e);
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
        $this->setViewParameter('id_e', $recuperateur->getInt('id_e', 0));

        $this->setViewParameter('has_many_collectivite', $this->hasManyCollectivite());
        $this->setViewParameter('info', $this->getEntiteSQL()->getInfo($this->getViewParameterOrObject('id_e')));

        if ($this->getViewParameterOrObject('id_e')) {
            $this->detailEntite();
        } else {
            $this->listEntite();
        }
    }

    private function setPageTitle($texte)
    {
        if ($this->isViewParameter('id_e')) {
            $info = $this->getEntiteSQL()->getInfo($this->getViewParameterOrObject('id_e'));

            if ($info) {
                $texte = $info['denomination'] . " - $texte ";
            }
        }
        $this->setViewParameter('page_title', $texte);
    }

    public function utilisateurAction()
    {
        $recuperateur = $this->getGetInfo();
        $id_e = $recuperateur->getInt('id_e', 0);
        $descendance = $recuperateur->get('descendance');
        $role = $recuperateur->get('role');
        $search = $recuperateur->get('search');
        $offset = $recuperateur->getInt('offset');
        $this->hasUtilisateurDroitLecture($id_e);

        $all_role = $this->getRoleSQL()->getAllRole();
        $all_role[] = ['role' => RoleUtilisateur::AUCUN_DROIT,'libelle' => RoleUtilisateur::AUCUN_DROIT];

        $this->setViewParameter('all_role', $all_role);
        $this->setViewParameter('droitCreation', $this->getRoleUtilisateur()->hasDroit($this->getId_u(), 'utilisateur:creation', $id_e));

        $this->setViewParameter('nb_utilisateur', $this->getUtilisateurListe()->getNbUtilisateur($id_e, $descendance, $role, $search));
        $listeUtilisateur = $this->getUtilisateurListe()
            ->getAllUtilisateur($id_e, $descendance, $role, $search, $offset);
        foreach ($listeUtilisateur as $key => $utilisateur) {
            foreach ($utilisateur['all_role'] as $j => $userRole) {
                $listeUtilisateur[$key]['all_role'][$j]['ancetre'] =
                    $this->getEntiteSQL()->getTreeEntity($userRole['entite_mere']);
            }
        }
        $this->setViewParameter('liste_utilisateur', $listeUtilisateur);
        $this->setViewParameter('id_e', $id_e);
        $this->setViewParameter('role_selected', !empty($role) ? $role : $recuperateur->get('role_selected'));
        $this->setViewParameter('offset', $offset);
        $this->setViewParameter('search', $search);
        $this->setViewParameter('descendance', $descendance);

        $this->setViewParameter('template_milieu', 'UtilisateurList');
        $this->setViewParameter('menu_gauche_select', 'Entite/utilisateur');
        $this->setPageTitle('Liste des utilisateurs');
        $this->renderDefault();
    }

    public function exportUtilisateurAction()
    {
        $recuperateur = $this->getGetInfo();
        $id_e = $recuperateur->getInt('id_e', 0);
        $descendance = $recuperateur->get('descendance');
        $the_role = $recuperateur->get('role_selected');
        $search = $recuperateur->get('search');

        $this->hasUtilisateurDroitLecture($id_e);

        $result = [];
        $result[] = ["id_u","login","prénom","nom","email","collectivité de base","id_e","rôles"];

        $allUtilisateur = $this->getUtilisateurListe()->getAllUtilisateur($id_e, $descendance, $the_role, $search, -1);
        foreach ($allUtilisateur as $i => $user) {
            $r = [];
            foreach ($user['all_role'] as $role) {
                $r[] = ($role['libelle'] ?: "Aucun droit") . " - " . ($role['denomination'] ?: 'Entite racine');
            }
            $user['all_role'] = implode(",", $r);
            $result[]  = [$user['id_u'],$user['login'],
                $user['prenom'],$user['nom'],$user['email'],
                $user['denomination'] ?: "Entité racine",$user['id_e'],$user['all_role']
            ];
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
        $this->hasEntiteDroitLecture($id_e);
        $info = $this->getEntiteSQL()->getInfo($id_e);
        if (! $info) {
            $this->setLastError("Cette entité n'existe pas ou n'existe plus.");
            $this->redirect("/Entite/detail");
        }

        $this->setViewParameter('droit_edition', $this->getRoleUtilisateur()->hasDroit($this->getId_u(), "entite:edition", $id_e));
        $this->setViewParameter('droit_lecture_cdg', isset($info['cdg']['id_e']) && $this->getRoleUtilisateur()->hasDroit($this->getId_u(), "entite:lecture", $info['cdg']['id_e']));
        $this->setViewParameter('entiteExtendedInfo', $this->getEntiteSQL()->getExtendedInfo($id_e));
        $this->setViewParameter('is_supprimable', $this->isSupprimable($id_e));

        $this->setPageTitle("Informations");

        $this->setViewParameter('menu_gauche_select', "Entite/detail");

        $this->setViewParameter('template_milieu', "EntiteDetail");
        $this->setViewParameter('id_e', $id_e);

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
        $this->setViewParameter('liste_collectivite', $liste_collectivite);
        $this->setViewParameter('nbCollectivite', $nbCollectivite);
        $this->setViewParameter('search', $search);
        $this->setViewParameter('offset', $offset);

        $this->setPageTitle("Entité Racine");
        $this->setViewParameter('menu_gauche_select', "Entite/detail");

        $this->setViewParameter('template_milieu', "EntiteList");
        $this->renderDefault();
    }

    public function exportAction()
    {
        $id_e = $this->getGetInfo()->getInt('id_e', 0);
        $this->hasEntiteDroitLecture($id_e);

        $entite_list = $this->getEntiteListe()->getAllFille($id_e);
        $result = [
            [
                "ID_E","SIREN","DENOMINATION","TYPE","DATE INSCRIPTION","ACTIVE","CENTRE DE GESTION"
            ]
        ];

        foreach ($entite_list as $i => $entite_info) {
            $result[]  = [
                $entite_info['id_e'],
                $entite_info['siren'],
                $entite_info['denomination'],
                $entite_info['type'],
                $entite_info['date_inscription'],
                $entite_info['is_active'],
                $entite_info['centre_de_gestion'],
            ];
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
        $page =  (int)$recuperateur->getInt('page', 0);
        $this->hasDroitEdition($id_e);
        $this->setViewParameter('entite_info', $this->getEntiteSQL()->getInfo($id_e));
        $this->setViewParameter('template_milieu', "EntiteImport");
        $this->setViewParameter('page_title', "Importer (fichier CSV)");

        if ($page === 0) {
            $this->setViewParameter('allCDG', $this->getEntiteListe()->getAll(EntiteSQL::TYPE_CENTRE_DE_GESTION));
            $this->setViewParameter('cdg_selected', false);
        }

        $this->setViewParameter('onglet_tab', ["Collectivités","Agents","Grades"]);
        $onglet_content = ["EntiteImportCollectivite","EntiteImportAgent","EntiteImportGrade"];
        $this->setViewParameter('template_onglet', $onglet_content[$page]);
        $this->setViewParameter('page', $page);
        $this->setViewParameter('id_e', $id_e);
        $this->renderDefault();
    }

    public function editionAction()
    {
        $recuperateur = new Recuperateur($_GET);
        $entite_mere = (int)$recuperateur->getInt('entite_mere', 0);
        $id_e = (int)$recuperateur->getInt('id_e', 0);
        if ($entite_mere) {
            $this->hasDroitEdition($entite_mere);
        }
        if ($id_e) {
            $this->hasDroitEdition($id_e);
        }
        if ($entite_mere === 0 && $id_e === 0) {
            $this->hasDroitEdition(0);
        }

        if ($id_e) {
            $infoEntite = $this->getEntiteSQL()->getInfo($id_e);
            $infoEntite['centre_de_gestion'] = $this->getEntiteSQL()->getCDG($id_e);
            $this->setViewParameter('page_title', "Modification de " . $infoEntite['denomination']);
        } else {
            $infoEntite = $this->getEntiteInfoFromLastError();
            if ($entite_mere) {
                $this->setViewParameter('infoMere', $this->getEntiteSQL()->getInfo($entite_mere));
                $this->setViewParameter('page_title', "Ajout d'une entité fille pour " . $this->getViewParameterOrObject('infoMere')['denomination']);
            } else {
                $this->setViewParameter('page_title', "Ajout d'une entité");
            }
        }
        $this->setViewParameter('infoEntite', $infoEntite);
        $this->setViewParameter('cdg_selected', $infoEntite['centre_de_gestion']);
        $this->setViewParameter('allCDG', $this->getEntiteListe()->getAll(EntiteSQL::TYPE_CENTRE_DE_GESTION));
        $this->setViewParameter('template_milieu', "EntiteEdition");
        $this->setViewParameter('id_e', $id_e);
        $this->setViewParameter('entite_mere', $entite_mere);

        $this->renderDefault();
    }

    private function getEntiteInfoFromLastError()
    {
        $field_list = ["type","denomination","siren","entite_mere","id_e","has_ged","has_archivage","centre_de_gestion"];
        $infoEntite = [];
        foreach ($field_list as $field) {
            $infoEntite[$field] = $this->getLastError()->getLastInput($field);
        }
        return $infoEntite;
    }


    /**
     * @throws UnrecoverableException
     * @throws NotFoundException
     * @throws LastMessageException
     * @throws LastErrorException
     */
    public function choixAction()
    {
        $recuperateur = new Recuperateur($_GET);
        $this->setViewParameter('id_d', $recuperateur->get('id_d'));
        $this->setViewParameter('id_e', $recuperateur->get('id_e'));
        $this->setViewParameter('action', $recuperateur->get('action'));
        $this->setViewParameter('type', $recuperateur->get('type', EntiteSQL::TYPE_COLLECTIVITE));
        $this->setViewParameter('liste', $this->getEntiteListe()->getAll($this->getViewParameterByKey('type')));

        if (! $this->getViewParameterByKey('liste')) {
            $this->setLastError(
                "Aucune entité ({$this->getViewParameterByKey('type')}) n'est disponible pour cette action"
            );
            $this->redirect(
                "/Document/detail?id_e={$this->getViewParameterByKey('id_e')}&id_d={$this->getViewParameterByKey('id_d')}"
            );
        }
        $this->setViewParameter('page_title', "Veuillez choisir le ou les destinataires du document ");
        $this->setViewParameter('template_milieu', "EntiteChoix");
        $this->renderDefault();
    }

    /**
     * @throws UnrecoverableException
     * @throws LastMessageException
     * @throws LastErrorException
     */
    public function doEditionAction(): void
    {
        $recuperateur = $this->getPostInfo();
        $id_e = $recuperateur->getInt('id_e');
        $name = $recuperateur->get('denomination');
        $siren = $recuperateur->get('siren');
        $entite_mere =  $recuperateur->getInt('entite_mere', 0);
        $type = EntiteSQL::TYPE_COLLECTIVITE;
        $cdg = 0;

        if ($this->getViewParameterByKey('cdg_feature')) {
            $type = $recuperateur->get('type');
            $cdg = $recuperateur->getInt('centre_de_gestion', 0);
        }

        try {
            // Ajout du controle des droits qui ne se fait plus sur la function "edition" commune aux APIs et à la console Pastell
            if ($id_e) {
                $this->hasDroitEdition($id_e);
            }
            $this->hasDroitEdition($entite_mere);

            if ($id_e) {
                $this->getInstance(EntityUpdateService::class)->update(
                    $id_e,
                    $name,
                    $siren,
                    $type,
                    $entite_mere,
                    $cdg,
                );
            } else {
                $id_e = $this->getInstance(EntityCreationService::class)->create(
                    $name,
                    $siren,
                    $type,
                    $entite_mere,
                    $cdg,
                );
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

        $this->hasEntiteDroitLecture($id_e);

        /** @var AgentSQL $agentSQL */
        $agentSQL = $this->getInstance(AgentSQL::class);

        if ($id_e) {
            $siren = $this->getEntiteSQL()->getInfo($id_e)['siren'];
            $this->setViewParameter('nbAgent', $agentSQL->getNbAgent($siren, $search));
            $this->setViewParameter('listAgent', $agentSQL->getBySiren($siren, $offset, $search));
        } else {
            $this->setViewParameter('nbAgent', $agentSQL->getNbAllAgent($search));
            $this->setViewParameter('listAgent', $agentSQL->getAllAgent($search, $offset));
        }
        $this->setViewParameter('offset', $offset);
        $this->setViewParameter('page', $page);
        $this->setViewParameter('droit_edition', $this->getRoleUtilisateur()->hasDroit($this->getId_u(), "entite:edition", $id_e));
        $this->setViewParameter('id_e', $id_e);
        $this->setViewParameter('search', $search);
        $this->setPageTitle("Agents");
        $this->setViewParameter('menu_gauche_select', "Entite/agents");
        $this->setViewParameter('template_milieu', "AgentList");

        $this->renderDefault();
    }

    public function connecteurAction()
    {
        $recuperateur = new Recuperateur($_GET);
        $id_e = $recuperateur->getInt('id_e', 0);
        $this->hasConnecteurDroitLecture($id_e);
        $this->hasEntiteDroitLecture($id_e);
        $this->setViewParameter('droit_edition', $this->getRoleUtilisateur()->hasDroit($this->getId_u(), 'connecteur:edition', $id_e));
        $this->setViewParameter('id_e', $id_e);
        $this->setViewParameter('all_connecteur', $this->getConnecteurEntiteSQL()->getAll($id_e));
        if ($id_e) {
            $this->setViewParameter('all_connecteur_definition', $this->getObjectInstancier()->getInstance(ConnecteurDefinitionFiles::class)->getAll());
        } else {
            $this->setViewParameter('all_connecteur_definition', $this->getObjectInstancier()->getInstance(ConnecteurDefinitionFiles::class)->getAllGlobal());
        }
        $this->setViewParameter('template_milieu', "ConnecteurList");
        $this->setViewParameter('menu_gauche_select', "Entite/connecteur");
        $this->setPageTitle("Liste des connecteurs" . ($id_e ? "" : " globaux"));
        $this->setNavigationInfo($id_e, "Entite/connecteur?");
        $this->renderDefault();
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

        $infoCollectivite = [];
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


        $this->setLastMessage("$nb_agent agents ont été créés");
        $this->redirect("/Entite/import?page=1&id_e=$id_e");
    }

    /**
     * @throws UnrecoverableException
     * @throws LastMessageException
     * @throws LastErrorException
     */
    public function doImportAction(): void
    {
        $recuperateur = $this->getPostInfo();

        $id_e = $recuperateur->getInt('id_e', 0);
        $centre_de_gestion = $recuperateur->getInt('centre_de_gestion');
        $this->verifDroit($id_e, 'entite:edition');


        $fileUploader = new FileUploader();
        $file_path = $fileUploader->getFilePath('csv_col');
        if (! $file_path) {
            $this->setLastError('Impossible de lire le fichier');
            $this->redirect("/Entite/import?id_e=$id_e");
        }

        $CSV = new CSV();
        $colList = $CSV->get($file_path);

        $nb_col = 0;
        foreach ($colList as $col) {
            $this->getInstance(EntityCreationService::class)->create(
                $col[1],
                $col[0],
                EntiteSQL::TYPE_COLLECTIVITE,
                $id_e,
                $centre_de_gestion,
            );
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

    /**
     * @throws NotFoundException
     * @throws LastMessageException
     * @throws LastErrorException
     */
    public function exportConfigAction(): void
    {
        $recuperateur = $this->getGetInfo();
        $id_e = $recuperateur->getInt('id_e', 0);

        $this->verifDroit($id_e, 'system:edition');

        $this->setViewParameter('id_e', $id_e);
        $this->setViewParameter('template_milieu', 'EntiteExportConfig');
        $this->setViewParameter('menu_gauche_select', 'Entite/exportConfig');
        $this->setPageTitle('Export de la configuration');
        $this->renderDefault();
    }

    /**
     * @throws NotFoundException
     * @throws LastMessageException
     * @throws LastErrorException
     */
    public function importConfigAction(): void
    {
        $recuperateur = $this->getGetInfo();
        $id_e = $recuperateur->getInt('id_e', 0);

        $this->verifDroit($id_e, 'system:edition');

        $this->setViewParameter('id_e', $id_e);
        $this->setViewParameter('template_milieu', 'EntiteImportConfig');
        $this->setViewParameter('menu_gauche_select', 'Entite/importConfig');
        $this->setPageTitle('Import de la configuration');
        $this->renderDefault();
    }

    /**
     * @throws NotFoundException
     * @throws LastMessageException
     * @throws LastErrorException
     */
    public function exportConfigVerifAction(): void
    {
        $recuperateur = $this->getPostInfo();
        $id_e = $recuperateur->getInt('id_e', 0);
        $this->verifDroit($id_e, 'system:edition');
        $options = [];
        foreach (ExportConfigService::getOptions() as $id => $label) {
            $options[$id] = $recuperateur->get($id);
        }
        $exportConfigService = $this->getObjectInstancier()->getInstance(ExportConfigService::class);
        $exportInfo = $exportConfigService->getInfo($id_e, $options);

        $generator = new UriSafeTokenGenerator();
        $password = $generator->generateToken();
        $this->getObjectInstancier()->getInstance(MemoryCache::class)->store(
            "export_configuration_password_$id_e",
            $password,
            60
        );

        $this->setViewParameter('id_e', $id_e);
        $this->setViewParameter('password', $password);
        $this->setViewParameter('exportInfo', $exportInfo);
        $this->setViewParameter('template_milieu', 'EntiteExportConfigVerif');
        $this->setViewParameter('menu_gauche_select', 'Entite/exportConfig');
        $this->setViewParameter('options', $options);
        $this->setPageTitle("Vérification de l'export de la configuration");
        $this->renderDefault();
    }

    /**
     * @throws LastMessageException
     * @throws LastErrorException
     * @throws JsonException
     */
    public function doExportConfigAction(): void
    {
        $recuperateur = $this->getPostInfo();
        $id_e = $recuperateur->getInt('id_e', 0);
        $this->verifDroit($id_e, 'system:edition');

        $options = [];
        $link = '/Entite/exportConfigVerif?';
        foreach (ExportConfigService::getOptions() as $id => $label) {
            $options[$id] = $recuperateur->get($id);
            $link .= sprintf('%s=%s&', $id, $options[$id]);
        }

        $password = $this->getObjectInstancier()
            ->getInstance(MemoryCache::class)
            ->fetch("export_configuration_password_$id_e");
        if (empty($password)) {
            $this->setLastError('Export impossible : Expiration du mot de passe généré');
            $this->redirect($link);
        }

        $exportConfigService = $this->getObjectInstancier()->getInstance(ExportConfigService::class);
        $exportInfo = $exportConfigService->getExportedFile($id_e, $options);
        $encryptedInfo = $this->getInstance(Crypto::class)
            ->encrypt($exportInfo, $password);

        $filename = 'export.json';

        $this->getObjectInstancier()->getInstance(SendFileToBrowser::class)
            ->sendData($encryptedInfo, $filename, 'application/json');
    }

    /**
     * @throws DonneesFormulaireException
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function doImportConfigAction(): void
    {
        $recuperateur = $this->getPostInfo();
        $id_e = $recuperateur->getInt('id_e', 0);
        $this->verifDroit($id_e, 'system:edition');
        $fileUploader = new FileUploader();
        $file_content = $fileUploader->getFileContent('pser');
        $password = $this->getPostInfo()->get('password');
        $id_e = $this->getPostInfo()->getInt('id_e');
        $message = $this->getInstance(Crypto::class)->decrypt($file_content, $password);

        $message = json_decode($message, true, 512, JSON_THROW_ON_ERROR);
        $importConfigService = $this->getObjectInstancier()->getInstance(ImportConfigService::class);
        $importConfigService->import($message, $id_e);
        $lastErrors = $importConfigService->getLastErrors();
        $this->setLastMessage('Les données ont été importées<br/>' . implode('<br/>', $lastErrors));
        $this->redirect("/Entite/detail?id_e=$id_e");
    }
}