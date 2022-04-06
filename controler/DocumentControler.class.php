<?php

use Flow\Config;
use Flow\Request;
use Flow\Basic;
use Flow\Uploader;

class DocumentControler extends PastellControler
{
    public function _beforeAction()
    {
        parent::_beforeAction();
        $id_e = $this->getPostOrGetInfo()->getInt('id_e');
        $id_d = $this->getPostOrGetInfo()->get('id_d');
        $type = $this->getPostOrGetInfo()->get('type');


        if ($id_d && ! is_array($id_d)) {
            $document_info = $this->getDocument()->getInfo($id_d);
            $type = $document_info['type'];
            $this->{'type_e_menu'} = $type;
        }

        $this->setNavigationInfo($id_e, "Document/list?type=$type");
    }

    public function renderDefault()
    {
        $this->{'show_choice_entity_message'} =
            !(bool)$this->getPostOrGetInfo()->getInt('id_e')
            && $this->{'id_e'} === '0';
        parent::renderDefault();
    }

    /**
     * @return DocumentActionEntite
     */
    protected function getDocumentActionEntite()
    {
        return $this->getInstance(DocumentActionEntite::class);
    }

    private function redirectToList($id_e, $type = false)
    {
        $this->redirect("/Document/list?id_e=$id_e&type=$type");
    }

    private function verifDroitLecture($id_e, $id_d)
    {
        $info = $this->getDocumentSQL()->getInfo($id_d);
        if (!$info) {
            $this->redirectToList($id_e);
        }

        if (! $this->getDroitService()->hasDroit($this->getId_u(), $this->getDroitService()->getDroitLecture($info['type']), $id_e)) {
            $this->redirectToList($id_e, $info['type']);
        }

        $my_role = $this->getDocumentEntite()->getRole($id_e, $id_d);
        if (! $my_role) {
            $this->redirectToList($id_e, $info['type']);
        }
        return $info;
    }

    public function arAction()
    {
        $recuperateur = new Recuperateur($_GET);
        $id_d = $recuperateur->get('id_d');
        $id_e = $recuperateur->getInt('id_e');

        $info_document = $this->verifDroitLecture($id_e, $id_d);


        $true_last_action = $this->getDocumentActionEntite()->getTrueAction($id_e, $id_d);
        /** @var DocumentType $documentType */
        $documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($info_document['type']);

        $action = $documentType->getAction();
        if (! $action->getProperties($true_last_action, 'accuse_de_reception_action')) {
            $this->redirect("/Document/detail?id_e=$id_e&id_d=$id_d");
        }
        $this->{'action'} = $action->getProperties($true_last_action, 'accuse_de_reception_action');
        $this->{'id_e'} = $id_e;
        $this->{'id_d'} = $id_d;

        $this->{'page_title'} = "Accusé de réception";
        $this->{'template_milieu'} = "DocumentAR";
        $this->renderDefault();
    }

    public function detailAction()
    {
        $recuperateur = $this->getGetInfo();
        $id_d = $recuperateur->get('id_d');
        $id_e = $recuperateur->getInt('id_e');
        $page = $recuperateur->getInt('page', 0);

        $info_document = $this->verifDroitLecture($id_e, $id_d);

        /** @var DocumentType $documentType */
        $documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($info_document['type']);

        $true_last_action = $this->getDocumentActionEntite()->getTrueAction($id_e, $id_d);

        $action = $documentType->getAction();
        if ($action->getProperties($true_last_action, 'accuse_de_reception_action')) {
            $this->redirect("/Document/ar?id_e=$id_e&id_d=$id_d");
        }

        $this->getJournal()->addConsultation($id_e, $id_d, $this->getId_u());

        $this->{'info'} = $info_document;
        $this->{'id_e'} = $id_e;
        $this->{'id_d'} = $id_d;
        $this->{'page'} = $page;
        $this->{'documentType'} = $documentType;
        $this->{'infoEntite'} = $this->getEntiteSQL()->getInfo($id_e);
        $this->{'formulaire'} =  $documentType->getFormulaire();
        $this->{'donneesFormulaire'} = $this->getDonneesFormulaireFactory()->get($id_d, $info_document['type']);
        $this->{'donneesFormulaire'}->getFormulaire()->setTabNumber($page);

        $this->{'actionPossible'} = $this->getActionPossible();
        $this->{'theAction'} = $documentType->getAction();
        $this->{'documentEntite'} = $this->getDocumentEntite();
        $this->{'my_role'} = $this->getDocumentEntite()->getRole($id_e, $id_d);
        $this->{'documentEmail'} = $this->getInstance(DocumentEmail::class);
        $this->{'documentActionEntite'} = $this->getDocumentActionEntite();

        $this->{'next_action_automatique'} =  $this->{'theAction'}->getActionAutomatique($true_last_action);
        $this->{'droit_erreur_fatale'} = $this->getDroitService()->hasDroit($this->getId_u(), $this->getDroitService()->getDroitEdition($info_document['type']), 0);

        $this->{'is_super_admin'} = $this->getRoleUtilisateur()->hasDroit($this->getId_u(), "system:edition", 0);
        if ($this->{'is_super_admin'}) {
            $this->{'all_action'} = $documentType->getAction()->getWorkflowAction();
        }

        $this->{'page_title'} =  $info_document['titre'] . " (" . $documentType->getName() . ")";

        if ($documentType->isAfficheOneTab()) {
            $this->{'fieldDataList'} = $this->{'donneesFormulaire'}->getFieldDataListAllOnglet($this->{'my_role'});
        } else {
            $this->{'fieldDataList'} = $this->{'donneesFormulaire'}->getFieldDataList($this->{'my_role'}, $page);
        }

        $document_email_reponse_list =
            $this->getObjectInstancier()->getInstance(DocumentEmailReponseSQL::class)->getAllReponse($id_d);


        $this->{'document_email_reponse_list'} = $document_email_reponse_list;

        $this->{'recuperation_fichier_url'} = "Document/recuperationFichier?id_d=$id_d&id_e=$id_e";
        if ($this->hasDroit($this->{'id_e'}, "system:lecture")) {
            $this->{'job_list'} = $this->getWorkerSQL()->getJobListWithWorkerForDocument($this->{'id_e'}, $this->{'id_d'});
        } else {
            $this->{'job_list'} = false;
        }
        $this->{'return_url'} = urlencode("Document/detail?id_e={$this->{'id_e'}}&id_d={$this->{'id_d'}}");

        $this->{'template_milieu'} = "DocumentDetail";
        $this->{'inject'} = array('id_e' => $id_e,'id_ce' => '','id_d' => $id_d,'action' => $action);

        $this->renderDefault();
    }

    /**
     * @throws Exception
     */
    public function detailMailReponseAction()
    {

        $id_e = $this->getPostOrGetInfo()->get('id_e');
        $id_d = $this->getPostOrGetInfo()->get('id_d');
        $id_d_reponse = $this->getPostOrGetInfo()->get('id_d_reponse');

        $info_document = $this->verifDroitLecture($id_e, $id_d);

        $reponse_info =
            $this->getObjectInstancier()
                ->getInstance(DocumentEmailReponseSQL::class)
                ->getInfoFromIdReponse($id_d_reponse);

        $mail_info =
            $this->getObjectInstancier()
                ->getInstance(DocumentEmail::class)
                ->getInfoFromPK($reponse_info['id_de']);

        if ($mail_info['id_d'] != $id_d) {
            $this->setLastError("Impossible de lire ce document");
            $this->redirect();
        }

        if (! $reponse_info['is_lu']) {
            $this->getObjectInstancier()
                ->getInstance(DocumentEmailReponseSQL::class)
                ->setLu($id_d_reponse);

            $this->getJournal()->add(
                Journal::MAIL_SECURISE,
                $id_e,
                $id_d,
                "Lecture d'une réponse",
                $this->getAuthentification()->getLogin() . " a lu la réponse de {$mail_info['email']} (id_de={$mail_info['id_de']}, id_d_reponse={$reponse_info['id_d_reponse']})"
            );
        }

        $this->{'donneesFormulaire'} = $this->getDonneesFormulaireFactory()->get($id_d_reponse);
        $this->{'fieldDataList'} = $this->{'donneesFormulaire'}->getFieldDataList("", 0);
        $this->{'recuperation_fichier_url'} = "Document/recuperationFichier?id_d=$id_d_reponse&id_e=$id_e";

        $this->{'page_title'} =  $info_document['titre'] . " ( Réponse de " . $mail_info['email'] . ")";
        $this->{'id_e'} = $id_e;
        $this->{'id_d'} = $id_d;

        $this->inject = [
            'id_d' => $id_d_reponse,
            'id_e' => $id_e,
            'id_ce' => false,
            'action' => false,
        ];

        $this->{'template_milieu'} = "DocumentMailReponse";
        $this->renderDefault();
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws UnrecoverableException
     */
    public function editionAction()
    {
        $id_d = $this->getGetInfo()->get('id_d');
        $type = $this->getGetInfo()->get('type');
        $id_e = $this->getGetInfo()->getInt('id_e');
        $page = $this->getGetInfo()->getInt('page', 0);
        $action = $this->getGetInfo()->get('action');

        $document = $this->getDocument();

        if (! $id_d) {
            $this->setLastError("id_d n'a pas été fourni");
            $this->redirect("/Document/list");
        }

        if ($action) {
            $info = $document->getInfo($id_d);
            $type = $info['type'];
        } elseif ($id_d) {
            $info = $document->getInfo($id_d);
            $type = $info['type'];
            $action = 'modification';
        }

        $this->verifDroit($id_e, $type . ":edition", "/Document/list");

        $actionPossible = $this->getActionPossible();

        if (! $actionPossible->isActionPossible($id_e, $this->getId_u(), $id_d, $action)) {
            $this->setLastError("L'action « $action »  n'est pas permise : " . $actionPossible->getLastBadRule());
            header("Location: detail?id_d=$id_d&id_e=$id_e&page=$page");
            exit;
        }


        $documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($type);

        $infoEntite = $this->getEntiteSQL()->getInfo($id_e);

        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d, $type);

        $formulaire = $donneesFormulaire->getFormulaire();
        if (! $formulaire->tabNumberExists($page)) {
            $page = 0;
        }


        $this->{'inject'} = array('id_e' => $id_e,'id_d' => $id_d,'form_type' => $type,'action' => $action,'id_ce' => '');
        $this->{'page_title'} = "Modification du dossier « " . $documentType->getName() . " » ( " . $infoEntite['denomination'] . " ) ";

        $this->{'info'} = $info;
        $this->{'id_e'} = $id_e;
        $this->{'id_d'} = $id_d;
        $this->{'page'} = $page;
        $this->{'type'} = $type;
        $this->{'action'} = $action;
        $this->{'documentType'} = $documentType;
        $this->{'infoEntite'} = $this->getEntiteSQL()->getInfo($id_e);
        $this->{'formulaire'} =  $documentType->getFormulaire();
        $this->{'donneesFormulaire'} = $donneesFormulaire;
        $this->{'ActionPossible'} = $this->getActionPossible();
        $this->{'theAction'} = $documentType->getAction();
        $this->{'documentEntite'} = $this->getDocumentEntite();
        $this->{'my_role'} = $this->getDocumentEntite()->getRole($id_e, $id_d);
        $this->{'documentEmail'} = $this->getInstance(DocumentEmail::class);
        $this->{'documentActionEntite'} = $this->getDocumentActionEntite();

        $this->{'action_url'} = "Document/doEdition";
        $this->{'recuperation_fichier_url'} = "Document/recuperationFichier?id_d=$id_d&id_e=$id_e";
        $this->{'suppression_fichier_url'} = "Document/supprimerFichier?id_d=$id_d&id_e=$id_e&page=$page&action=$action";
        $this->{'externalDataURL'} = "Document/externalData" ;

        $this->{'template_milieu'} = "DocumentEdition";
        $this->renderDefault();
    }


    /**
     * @throws LastErrorException
     * @throws LastMessageException
     * @throws NotFoundException
     */
    public function indexAction()
    {
        $recuperateur = $this->getGetInfo();
        $id_e = $recuperateur->getInt   ('id_e', 0);
        $offset = $recuperateur->getInt('offset', 0);
        $search = $recuperateur->get('search');
        $limit = 20;

        $liste_type = array();
        $allDroit = $this->getDroitService()->getAllDroit($this->getId_u());

        foreach ($allDroit as $droit) {
            if (preg_match('/^(.*):lecture$/u', $droit, $result)) {
                $liste_type[] = $result[1];
            }
        }

        $liste_collectivite = $this->getRoleUtilisateur()->getEntiteWithSomeDroit($this->getId_u());

        if (! $id_e) {
            if (count($liste_collectivite) == 0) {
                $this->redirect("/Connexion/nodroit");
            }
            if (count($liste_collectivite) == 1) {
                $id_e = $liste_collectivite[0];
            }
        }
        if ($id_e) {
            foreach ($liste_type as $i => $the_type) {
                if (! $this->getDroitService()->hasDroit($this->getId_u(), $this->getDroitService()->getDroitLecture($the_type), $id_e)) {
                    unset($liste_type[$i]);
                }
            }
            if (! $liste_type) {
                $this->setLastError("Vous n'avez pas les droits nécessaires pour accéder à cette page");
                $this->redirect();
            }
        }

        $this->{'tri'} =  $recuperateur->get('tri', 'date_dernier_etat');
        $this->{'sens_tri'} = $recuperateur->get('sens_tri', 'DESC');

        $this->{'url_tri'} = false;


        if ($id_e) {
            $this->{'listDocument'} = $this->getDocumentActionEntite()->getListDocumentByEntite($id_e, $liste_type, $offset, $limit, $search);
            $this->{'count'} = $this->getDocumentActionEntite()->getNbDocumentByEntite($id_e, $liste_type, $search);
            $this->{'type_list'} = $this->getAllType($this->{'listDocument'});
        }

        $this->{'infoEntite'} = $this->getEntiteSQL()->getInfo($id_e);
        $this->{'id_e'} = $id_e;
        $this->{'search'} = $search;
        $this->{'offset'} = $offset;
        $this->{'limit'} = $limit;
        $this->{'url'} = "id_e=$id_e&search=$search";

        $this->{'champs_affiches'} = DocumentType::getDefaultDisplayField();

        $this->setNavigationInfo($id_e, "Document/index?a=a");
        $this->{'page_title'} = "Liste des dossiers " . $this->{'infoEntite'}['denomination'] ;
        $this->{'template_milieu'} = "DocumentIndex";
        $this->renderDefault();
    }

    private function getAllType(array $listDocument)
    {
        $type = array();
        foreach ($listDocument as $doc) {
            $type[$doc['type']] = $doc['type'];
        }
        return array_keys($type);
    }

    public function listAction()
    {
        $recuperateur = $this->getGetInfo();
        $id_e = $recuperateur->get('id_e', 0);
        $offset = $recuperateur->getInt('offset', 0);
        $search = $recuperateur->get('search');
        $type = $recuperateur->get('type');
        $filtre = $recuperateur->get('filtre');
        $last_id = $recuperateur->get('last_id');

        $limit = 20;

        if (! $type) {
            $this->redirect("/Document/index?id_e=$id_e");
        }

        if ($id_e == '0') {
            $this->redirect("/Document/index?id_e=$id_e&type=$type");
        }

        $documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($type);

        $liste_collectivite = $this->getRoleUtilisateur()->getEntite($this->getId_u(), $type . ":lecture");

        if (! $liste_collectivite) {
            $this->redirect("/Document/index");
        }

        if (!$id_e && (count($liste_collectivite) == 1)) {
            $id_e = $liste_collectivite[0];
            $this->{'id_e_menu'} = $id_e;
            $this->{'type_e_menu'} = $type;
        }


        $this->verifDroit($id_e, "$type:lecture");
        $this->{'infoEntite'} = $this->getEntiteSQL()->getInfo($id_e);

        $page_title = "Liste des dossiers " . $documentType->getName();
        if ($id_e) {
            $page_title .= " pour " . $this->{'infoEntite'}['denomination'];
        }

        $this->{'page_title'} = $page_title;
        $this->{'documentActionEntite'} = $this->getDocumentActionEntite();
        $this->{'ActionPossible'} = $this->getActionPossible();


        $this->{'all_action'} = $documentType->getAction()->getWorkflowAction();


        if ($last_id) {
            $offset =  $this->getObjectInstancier()->getInstance(DocumentActionEntite::class)->getOffset($last_id, $id_e, $type, $limit);
        }

        if ($this->getActionPossible()->isCreationPossible($id_e, $this->getId_u(), $type)) {
            $this->{'nouveau_bouton_url'} = "Document/new?type=$type&id_e=$id_e";
        }
        $this->{'id_e'} = $id_e;
        $this->{'search'} = $search;
        $this->{'offset'} = $offset;
        $this->{'limit'} = $limit;
        $this->{'filtre'} = $filtre;
        $this->{'last_id'} = $last_id;
        $this->{'type'} = $type;
        $this->{'url'} = "id_e=$id_e&search=$search&type=$type&lastetat=$filtre";

        $this->{'tri'} =  $recuperateur->get('tri', 'date_dernier_etat');
        $this->{'sens_tri'} = $recuperateur->get('sens_tri', 'DESC');


        $this->{'documentTypeFactory'} = $this->getDocumentTypeFactory();
        $this->setNavigationInfo($id_e, "Document/list?type=$type");

        $this->{'champs_affiches'} = $documentType->getChampsAffiches();


        $this->{'allDroitEntite'} = $this->getDroitService()->getAllDocumentLecture($this->getId_u(), $this->{'id_e'});

        $this->{'indexedFieldsList'} = $documentType->getFormulaire()->getIndexedFields();
        $indexedFieldValue = array();
        foreach ($this->{'indexedFieldsList'} as $indexField => $indexLibelle) {
            $indexedFieldValue[$indexField] = $recuperateur->get($indexField);
        }

        $this->{'listDocument'} = $this->getDocumentActionEntite()->getListBySearch(
            $id_e,
            $type,
            $offset,
            $limit,
            $search,
            $filtre,
            false,
            false,
            $this->{'tri'},
            $this->{'allDroitEntite'},
            false,
            false,
            false,
            $indexedFieldValue,
            $this->{'sens_tri'}
        );


        $this->{'url_tri'} = "Document/list?id_e=$id_e&type=$type&search=$search&filtre=$filtre";

        $this->{'type_list'} = $this->getAllType($this->{'listDocument'});

        $this->{'template_milieu'} = "DocumentList";
        $this->renderDefault();
    }

    public function searchDocument()
    {
        $recuperateur = new Recuperateur($_REQUEST);
        $this->{'id_e'} = $recuperateur->getInt('id_e', 0);
        $this->{'type'} = $recuperateur->get('type');
        $this->{'lastEtat'} = $recuperateur->get('lastetat');
        $this->{'last_state_begin'} = $recuperateur->get('last_state_begin');
        $this->{'last_state_end'} = $recuperateur->get('last_state_end');
        $this->{'state_begin'} = $recuperateur->get('state_begin');
        $this->{'state_end'} = $recuperateur->get('state_end');


        $this->{'last_state_begin_iso'} = getDateIso($this->{'last_state_begin'});
        $this->{'last_state_end_iso'} = getDateIso($this->{'last_state_end'});
        $this->{'state_begin_iso'} =  getDateIso($this->{'state_begin'});
        $this->{'state_end_iso'} =    getDateIso($this->{'state_end'});

        if (! $this->{'id_e'}) {
            $error_message = "id_e est obligatoire";
            $this->setLastError($error_message);
            $this->redirect("");
        }
        $this->verifDroit($this->{'id_e'}, "entite:lecture");

        $this->{'allDroitEntite'} = $this->getDroitService()->getAllDocumentLecture($this->getId_u(), $this->{'id_e'});

        $this->{'etatTransit'} = $recuperateur->get('etatTransit');


        $this->{'tri'} =  $recuperateur->get('tri', 'date_dernier_etat');
        $this->{'sens_tri'} = $recuperateur->get('sens_tri', 'DESC');
        $this->{'go'} = $recuperateur->get('go', 0);
        $this->{'offset'} = $recuperateur->getInt('offset', 0);
        $this->{'search'} = $recuperateur->get('search');
        $this->{'limit'} = $recuperateur->getInt('limit', 100);

        $indexedFieldValue = array();
        if ($this->{'type'}) {
            $documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($this->{'type'});
            $this->{'indexedFieldsList'} = $documentType->getFormulaire()->getIndexedFields();

            foreach ($this->{'indexedFieldsList'} as $indexField => $indexLibelle) {
                $indexedFieldValue[$indexField] = $recuperateur->get($indexField);
                if ($documentType->getFormulaire()->getField($indexField)->getType() == 'date') {
                    $indexedFieldValue[$indexField] = date_fr_to_iso($recuperateur->get($indexField));
                }
            }
            $this->{'champs_affiches'} = $documentType->getChampsAffiches();
        } else {
            $this->{'champs_affiches'} = DocumentType::getDefaultDisplayField();
            $this->{'indexedFieldsList'} = array();
        }

        $this->{'indexedFieldValue'} = $indexedFieldValue;


        $allDroit = $this->getDroitService()->getAllDroit($this->getId_u());
        $this->{'listeEtat'} = $this->getDocumentTypeFactory()->getActionByRole($allDroit);

        $this->{'documentActionEntite'} = $this->getDocumentActionEntite();
        $this->{'documentTypeFactory'} = $this->getDocumentTypeFactory();

        $this->{'my_id_e'} = $this->{'id_e'};


        try {
            $this->{'listDocument'} = $this->apiGet("entite/{$this->id_e}/document");
        } catch (Exception $e) {
            $this->setLastError($e->getMessage());
            $this->redirect("");
        }

        $url_tri = "Document/search?id_e={$this->{'id_e'}}&search={$this->{'search'}}&type={$this->{'type'}}&lastetat={$this->{'lastEtat'}}" .
                        "&last_state_begin={$this->{'last_state_begin_iso'}}&last_state_end={$this->{'last_state_end_iso'}}&etatTransit={$this->{'etatTransit'}}" .
                        "&state_begin={$this->{'state_begin_iso'}}&state_end={$this->{'state_end_iso'}}&date_in_fr=true";

        if ($this->{'type'}) {
            foreach ($indexedFieldValue as $indexName => $indexValue) {
                $url_tri .= "&" . urlencode($indexName) . "=" . urlencode($indexValue);
            }
        }

        $this->{'url_tri'} = $url_tri;
        $this->{'type_list'} = $this->getAllType($this->{'listDocument'});
    }

    public function exportAction()
    {
        $recuperateur = new Recuperateur($_GET);
        $id_e = $recuperateur->get('id_e', 0);
        $type = $recuperateur->get('type');
        $search = $recuperateur->get('search');

        $lastEtat = $recuperateur->get('lastetat');
        $last_state_begin = $recuperateur->get('last_state_begin');
        $last_state_end = $recuperateur->get('last_state_end');

        $last_state_begin_iso = getDateIso($last_state_begin);
        $last_state_end_iso = getDateIso($last_state_end);

        $etatTransit = $recuperateur->get('etatTransit');
        $state_begin =  $recuperateur->get('state_begin');
        $state_end =  $recuperateur->get('state_end');
        $tri =  $recuperateur->get('tri');
        $sens_tri = $recuperateur->get('sens_tri');

        $offset = 0;

        $allDroitEntite = $this->getDroitService()->getAllDocumentLecture($this->getId_u(), $id_e);


        $indexedFieldValue = array();
        if ($type) {
            $documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($type);
            $indexedFieldsList = $documentType->getFormulaire()->getIndexedFields();
            foreach ($indexedFieldsList as $indexField => $indexLibelle) {
                $indexedFieldValue[$indexField] = $recuperateur->get($indexField);
            }
            /*$champs_affiches = $documentType->getChampsAffiches();*/
        } else {
            //$champs_affiches = array('titre'=>'Objet','type'=>'Type','entite'=>'Entité','dernier_etat'=>'Dernier état','date_dernier_etat'=>'Date');
            $indexedFieldsList = array();
        }


        $limit = $this->getDocumentActionEntite()->getNbDocumentBySearch($id_e, $type, $search, $lastEtat, $last_state_begin_iso, $last_state_end_iso, $allDroitEntite, $etatTransit, $state_begin, $state_end, $indexedFieldValue);
        $listDocument = $this->getDocumentActionEntite()->getListBySearch($id_e, $type, $offset, $limit, $search, $lastEtat, $last_state_begin_iso, $last_state_end_iso, $tri, $allDroitEntite, $etatTransit, $state_begin, $state_end, $indexedFieldValue, $sens_tri);

        $line = array("ENTITE","ID_D","TYPE","TITRE","DERNIERE ACTION","DATE DERNIERE ACTION");
        foreach ($indexedFieldsList as $indexField => $indexLibelle) {
            $line[] = $indexLibelle;
        }
        $result = array($line);
        foreach ($listDocument as $i => $document) {
             $line = array(
                    $document['denomination'],
                    $document['id_d'],
                    $document['type'],
                    $document['titre'],
                    $document['last_action'],
                    $document['last_action_date'],

             );
             foreach ($indexedFieldsList as $indexField => $indexLibelle) {
                 $line[] = $this->getInstance(DocumentIndexSQL::class)->get($document['id_d'], $indexField);
             }
             $result[] = $line;
        }

        $this->getInstance(CSVoutput::class)->sendAttachment("pastell-export-$id_e-$type-$search-$lastEtat-$tri.csv", $result);
    }


    public function searchAction()
    {
        $this->searchDocument();
        $this->{'page_title'} = "Recherche avancée de dossiers";
        $this->{'template_milieu'} = "DocumentSearch";
        $this->renderDefault();
    }

    public function warningAction()
    {
        $recuperateur = new Recuperateur($_GET);
        $this->{'id_d'} = $recuperateur->get('id_d');
        $this->{'action'} = $recuperateur->get('action');
        $this->{'id_e'} = $recuperateur->get('id_e');
        $this->{'page'} = $recuperateur->getInt('page', 0);


        $this->{'infoDocument'} = $this->getDocument()->getInfo($this->{'id_d'});

        $type = $this->{'infoDocument'}['type'];
        $documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($type);
        $theAction = $documentType->getAction();

        $this->{'actionName'} = $theAction->getDoActionName($this->{'action'});

        $this->{'page_title'} = "Attention ! Action irréversible";
        $this->{'template_milieu'} = "DocumentWarning";
        $this->renderDefault();
    }


    private function validTraitementParLot($input)
    {
        $recuperateur = new Recuperateur($input);
        $this->{'id_e'} = $recuperateur->get('id_e', 0);
        $this->{'offset'} = $recuperateur->getInt('offset', 0);
        $this->{'search'} = $recuperateur->get('search');
        $this->{'type'} = $recuperateur->get('type');
        $this->{'filtre'} = $recuperateur->get('filtre');
        $this->{'limit'} = 20;

        if (! $this->{'type'}) {
            $this->redirect("/Document/index?id_e={$this->{'id_e'}}");
        }
        if (!$this->{'id_e'}) {
            $this->redirect("/Document/index");
        }

        $this->{'id_e_menu'} = $this->{'id_e'};
        $this->verifDroit($this->{'id_e'}, "{$this->{'type'}}:lecture");
        $this->{'infoEntite'} = $this->getEntiteSQL()->getInfo($this->{'id_e'});

        $this->{'id_e_menu'} = $this->{'id_e'};
        $this->{'type_e_menu'} = $this->{'type'};
        $this->{'url_retour'} = $recuperateur->get('url_retour');
    }

    public function traitementLotAction()
    {
        $this->validTraitementParLot($_GET);
        $documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($this->{'type'});
        $page_title = "Traitement par lot pour les  documents " . $documentType->getName();
        $page_title .= " pour " . $this->{'infoEntite'}['denomination'];
        $this->{'page_title'} = $page_title;

        $this->{'documentTypeFactory'} = $this->getDocumentTypeFactory();
        $this->setNavigationInfo($this->{'id_e'}, "Document/list?type={$this->{'type'}}");
        $this->{'theAction'} = $documentType->getAction();



        $this->searchDocument();
        $listDocument = $this->listDocument;

        $all_action = array();
        foreach ($this->listDocument as $i => $document) {
            $listDocument[$i]['action_possible'] =  $this->getActionPossible()->getActionPossibleLot($this->{'id_e'}, $this->getId_u(), $document['id_d']);
            $all_action = array_merge($all_action, $listDocument[$i]['action_possible']);
        }
        $this->{'listDocument'} = $listDocument;

        $all_action = array_unique($all_action);

        $this->{'all_action'} = $all_action;
        $this->{'type_list'} = $this->getAllType($this->{'listDocument'});
        $this->{'template_milieu'} = "DocumentTraitementLot";
        $this->renderDefault();
    }

    public function confirmTraitementLotAction()
    {

        $this->validTraitementParLot($_GET);
        $documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($this->{'type'});
        $this->{'page_title'} = "Confirmation du traitement par lot pour les  documents " . $documentType->getName() . " pour " .
            $this->{'infoEntite'}['denomination'];

        $this->{'url_retour'} = "Document/traitementLot?id_e={$this->{'id_e'}}&type={$this->{'type'}}&search={$this->{'search'}}&filtre={$this->{'filtre'}}&offset={$this->{'offset'}}";



        $recuperateur = new Recuperateur($_GET);
        $this->{'action_selected'} = $recuperateur->get('action');
        $this->{'theAction'} = $documentType->getAction();

        $action_libelle = $this->{'theAction'}->getActionName($this->{'action_selected'});

        $all_id_d = $recuperateur->get('id_d');
        if (! $all_id_d) {
            $this->setLastError("Vous devez sélectionner au moins un document");
            $this->redirect($this->{'url_retour'});
        }

        $error = "";
        $listDocument = array();

        foreach ($all_id_d as $id_d) {
            $infoDocument  = $this->getDocumentActionEntite()->getInfo($id_d, $this->{'id_e'});
            if (! $this->getActionPossible()->isActionPossible($this->{'id_e'}, $this->getId_u(), $id_d, $this->{'action_selected'})) {
                $error .= "L'action « $action_libelle » n'est pas possible pour le document « {$infoDocument['titre']} »<br/>";
            }
            if ($this->getInstance(JobManager::class)->hasActionProgramme($this->{'id_e'}, $id_d)) {
                $error .= "Il y a déjà une action programmée pour le document « {$infoDocument['titre']} »<br/>";
            }
            $listDocument[] = $infoDocument;
        }
        if ($error) {
            $this->setLastError($error . "<br/><br/>Aucune action n'a été executée");
            $this->redirect($this->{'url_retour'});
        }

        $this->{'listDocument'} = $listDocument;
        $this->{'template_milieu'} = "DocumentConfirmTraitementLot";
        $this->renderDefault();
    }

    public function doTraitementLotAction()
    {
        $this->validTraitementParLot($_POST);
        $recuperateur = new Recuperateur($_POST);
        $action_selected = $recuperateur->get('action');
        $all_id_d = $recuperateur->get('id_d');
        $documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($this->{'type'});

        $action_libelle = $documentType->getAction()->getDoActionName($action_selected);

        $error = "";
        $message = "";
        foreach ($all_id_d as $id_d) {
            $infoDocument  = $this->getDocumentActionEntite()->getInfo($id_d, $this->{'id_e'});
            if (! $this->getActionPossible()->isActionPossible($this->{'id_e'}, $this->getId_u(), $id_d, $action_selected)) {
                $error .= "L'action « $action_libelle » n'est pas possible pour le document « {$infoDocument['titre']} »<br/>";
            }

            if ($this->getInstance(JobManager::class)->hasActionProgramme($this->{'id_e'}, $id_d)) {
                $error .= "Il y a déjà une action programmée pour le document « {$infoDocument['titre']} »<br/>";
            }

            $listDocument[] = $infoDocument;
            $document_titre = $infoDocument['titre'] ?: $id_d;
            $message .= "L'action « $action_libelle » est programmée pour le document « {$document_titre} »<br/>";
        }
        if ($error) {
            $this->setLastError($error . "<br/><br/>Aucune action n'a été executée");
            $this->redirect($this->{'url_retour'});
        }

        $this->getActionExecutorFactory()->executeLotDocument($this->{'id_e'}, $this->getId_u(), $all_id_d, $action_selected);
        $this->setLastMessage($message);
        $url_retour = "Document/list?id_e={$this->{'id_e'}}&type={$this->{'type'}}&search={$this->{'search'}}&filtre={$this->{'filtre'}}&offset={$this->{'offset'}}";
        $this->redirect($url_retour);
    }

    /**
     * @throws Exception
     */
    public function retourTeletransmissionAction()
    {

        $recuperateur = new Recuperateur($_GET);
        $id_e = $recuperateur->get('id_e', 0);
        $id_u = $recuperateur->get('id_u');
        $type = $recuperateur->get('type');
        $all_id_d = $recuperateur->get('id_d');
        $action = $recuperateur->get('action', 'return-teletransmission-tdt');

        $url_retour = "Document/list?id_e={$id_e}&type={$type}";
        $message = "";

        /* FIXME FIXME : Il y a une référence vers un connecteur !!! */
        /** @var TdtConnecteur $tdt */
        $tdt = $this->getConnecteurFactory()->getConnecteurByType($id_e, $type, 'TdT');

        $stringMapper = $this->getDocumentTypeFactory()->getFluxDocumentType($type)->getAction()->getConnecteurMapper($action);


        foreach ($all_id_d as $id_d) {
            $infoDocument  = $this->getDocumentActionEntite()->getInfo($id_d, $id_e);
            $listDocument[] = $infoDocument;

            $tedetis_transaction_id = $this->getDonneesFormulaireFactory()->get($id_d)->get($stringMapper->get('tedetis_transaction_id'));
            $status =  $tdt->getStatus($tedetis_transaction_id);

            if (in_array($status, array(TdtConnecteur::STATUS_ACTES_EN_ATTENTE_DE_POSTER))) {
                $message .= "La transaction pour le document « {$infoDocument['titre']} » n'a pas le bon status : " . TdtConnecteur::getStatusString($status) . " trouvé<br/>";
            } else {
                $this->getActionChange()->addAction($id_d, $id_e, $id_u, $stringMapper->get("send-tdt"), "Le document a été télétransmis à la préfecture");
                $message .= "Le document « {$infoDocument['titre']} » a été télétransmis<br/>";
            }
            /** @var JobManager $jobManager */
            $jobManager = $this->getInstance(JobManager::class);
            $jobManager->setJobForDocument($id_e, $id_d, "suite traitement par lot");
        }

        $this->setLastMessage($message);
        $this->redirect($url_retour);
    }


    public function reindex($document_type, $all_field_name, $offset = 0, $limit = -1)
    {
        if (! $this->getDocumentTypeFactory()->isTypePresent($document_type)) {
            echo "[ERREUR] Le type de dossier $document_type n'existe pas sur cette plateforme.\n";
            return;
        }
        $documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($document_type);
        $formulaire = $documentType->getFormulaire();

        if (!is_array($all_field_name)) {
            $all_field_name = array($all_field_name);
        }
        foreach ($all_field_name as $field_name) {
            $field = $formulaire->getField($field_name);
            if (!$field) {
                echo "[ERREUR] Le champs $field_name n'existe pas pour le type de dossier $document_type\n";
                return;
            }
            if (!$field->isIndexed()) {
                echo "[ERREUR] Le champs $document_type:$field_name n'est pas indexé\n";
                return;
            }
        }

        $document_list = $this->getDocument()->getAllByType($document_type);
        if ($limit > 0) {
            $document_list = array_slice($document_list, $offset, $limit);
        }
        echo "Nombre de documents : " . count($document_list) . "\n";
        $document_index = 0;

        foreach ($document_list as $document_info) {
            $id_d = $document_info['id_d'];
            echo "Réindexation du document {$document_info['titre']} ($id_d)\n";
            $documentIndexor = new DocumentIndexor($this->getInstance(DocumentIndexSQL::class), $id_d);
            $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
            foreach ($all_field_name as $field_name) {
                $fieldData = $donneesFormulaire->getFieldData($field_name);

                $documentIndexor->index($field_name, $fieldData->getValueForIndex());
            }
            // Libération mémoire; GC par paquet pour optimiser la fréquence d'appel
            $this->DonneesFormulaireFactory->clearCache();
            if (++$document_index % 100 == 0) {
                gc_collect_cycles();
            }
        }
    }

    public function fixModuleChamps($document_type, $old_field_name, $new_field_name)
    {
        foreach ($this->getDocument()->getAllByType($document_type) as $document_info) {
            $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document_info['id_d']);
            $value = $donneesFormulaire->get($old_field_name);
            $donneesFormulaire->setData($new_field_name, $value);
            $donneesFormulaire->deleteField($old_field_name);

            echo $document_info['id_d'] . " : OK\n";
        }
    }

    /**
     * @throws LastMessageException
     * @throws LastErrorException
     */
    public function changeEtatAction()
    {
        if (!$this->getRoleUtilisateur()->hasDroit($this->getId_u(), "system:edition", 0)) {
            $this->redirect("");
        }

        $recuperateur = $this->getPostInfo();
        $id_d = $recuperateur->get('id_d');
        $id_e = $recuperateur->getInt('id_e');
        $action = $recuperateur->get('action');
        $message = $recuperateur->get('message');

        $role = $this->getDocumentEntite()->getRole($id_e, $id_d);
        if (!$role) {
            $this->setLastError("Le document $id_d n'appartient pas à l'entité $id_e");
            $this->redirect();
        }
        $infoDocument = $this->getDocumentSQL()->getInfo($id_d);
        $type = $infoDocument['type'];

        $documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($type);
        $actions = $documentType->getAction()->getAll();

        if (!in_array($action, $actions, true)) {
            $this->setLastError("L'action $action n'existe pas");
            $this->redirect();
        }

        $this->getActionChange()->addAction(
            $id_d,
            $id_e,
            $this->getId_u(),
            $action,
            "Modification manuelle de l'état - $message"
        );
        $this->setLastMessage("L'état du document a été modifié : -> $action");

        $this->redirect("Document/detail?id_d=$id_d&id_e=$id_e");
    }

    public function bulkModification($id_e, $type, $etat, $field_name, $field_value)
    {
        $result = $this->getDocumentActionEntite()->getDocument($id_e, $type, $etat);

        if (!$result) {
            throw new Exception("Il n'y a pas de document de type $type pour l'id_e $id_e");
        }
        foreach ($result as $document_info) {
            $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($document_info['id_d'], $type);
            $donneesFormulaire->setData($field_name, $field_value);
        }
        return count($result);
    }

    public function actionAction()
    {

        $recuperateur = $this->getPostOrGetInfo();
        $id_d = $recuperateur->get('id_d');
        $action = $recuperateur->get('action');
        $id_e = $recuperateur->get('id_e');
        $page = $recuperateur->getInt('page', 0);
        $go = $recuperateur->getInt('go', 0);

        /** @var Document $document */
        $document = $this->getDocument();
        $infoDocument = $document->getInfo($id_d);
        $type = $infoDocument['type'];

        $documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($type);
        $theAction = $documentType->getAction();


        $actionPossible = $this->getActionPossible();

        $this->verifDroit($id_e, "$type:edition", "/Document/detail?id_d=$id_d&id_e=$id_e&page=$page");

        if (! $actionPossible->isActionPossible($id_e, $this->getId_u(), $id_d, $action)) {
            $this->setLastError("L'action « $action »  n'est pas permise (elle a peut-être déjà été effectuée) : " . $actionPossible->getLastBadRule());
            $this->redirect("/Document/detail?id_d=$id_d&id_e=$id_e&page=$page");
        }

        if ($action == Action::MODIFICATION) {
            $this->redirect("/Document/edition?id_d=$id_d&id_e=$id_e&page=$page");
        }


        $id_destinataire = $recuperateur->get('destinataire') ?: array();

        $action_destinataire =  $theAction->getActionDestinataire($action);
        if ($action_destinataire) {
            if (! $id_destinataire) {
                $this->redirect("/Entite/choix?id_d=$id_d&id_e=$id_e&action=$action&type=$action_destinataire");
            }
        }

        if ($theAction->getWarning($action) && ! $go) {
            $this->redirect("/Document/warning?id_d=$id_d&id_e=$id_e&action=$action&page=$page");
        }
        $result = $this->getActionExecutorFactory()->executeOnDocument($id_e, $this->getId_u(), $id_d, $action, $id_destinataire);
        $message = $this->getActionExecutorFactory()->getLastMessage();

        if (! $result) {
            $this->setLastError($message);
        } else {
            $this->setLastMessage($message);
        }
        $this->redirect("/Document/detail?id_d=$id_d&id_e=$id_e&page=$page");
    }

    public function newAction()
    {
        $type = $this->getPostInfo()->get('type');
        $id_e = $this->getPostInfo()->getInt('id_e');
        try {
            $id_d = $this->getObjectInstancier()
                ->getInstance(DocumentCreationService::class)
                ->createDocument($id_e, $this->getId_u(), $type);
        } catch (Exception $e) {
            $this->setLastError("Impossible de créer le document : " . $e->getMessage());
            $this->redirect("/Document/list?id_e=$id_e&type=$type");
        }

        $this->setLastMessage("Le document $id_d a été créé");
        $this->redirect("/Document/edition?id_e=$id_e&id_d=$id_d");
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function doEditionAction()
    {
        $id_d = $this->getPostInfo()->get('id_d');
        $id_e = $this->getPostInfo()->get('id_e');
        $page = $this->getPostInfo()->getInt('page');

        $documentModificationService =
            $this->getObjectInstancier()->getInstance(DocumentModificationService::class);

        try {
            $documentModificationService->modifyDocument(
                $id_e,
                $this->getId_u(),
                $id_d,
                $this->getPostInfo(),
                new FileUploader()
            );

            $fieldSubmitted = $this->getPostInfo()->get('fieldSubmittedId');
            if ($fieldSubmitted) {
                $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
                $field = $donneesFormulaire->getFormulaire()->getField($fieldSubmitted);
                if ($field) {
                    $onchange = $field->getOnChange();
                    if ($onchange) {
                        $this->getActionExecutorFactory()->executeOnDocument($id_e, $this->getId_u(), $id_d, $onchange);
                    }
                }
            }
        } catch (Exception $e) {
            $this->setLastError($e->getMessage());
            $this->redirect("/Document/edition?id_d=$id_d&id_e=$id_e&page=$page");
        }

        /* On a appuyer sur un bouton "Ajouter un fichier" */
        if ($this->getPostInfo()->get('ajouter')) {
            $this->redirect("/Document/edition?id_d=$id_d&id_e=$id_e&page=$page");
        }

        /* On a appuyer sur un bouton de type externalData */
        if ($this->getPostInfo()->get('external_data_button')) {
            $this->redirect(urldecode($this->getPostInfo()->get('external_data_button')));
        }

        /* On a appuyer sur un onglet */
        $enregistrer = $this->getPostInfo()->get('enregistrer');
        if ($enregistrer !== 'enregistrer') {
            $this->redirect("/Document/edition?id_d=$id_d&id_e=$id_e&page=$enregistrer");
        }

        /* On a appuyer sur le bouton enregistrer */
        $this->redirect("/Document/detail?id_d=$id_d&id_e=$id_e&page=$page");
    }

    /**
     * @throws LastErrorException
     * @throws LastMessageException
     */
    public function externalDataAction()
    {
        $recuperateur = $this->getGetInfo();
        $id_d = $recuperateur->get('id_d');
        $id_e = $recuperateur->get('id_e');
        $field = $recuperateur->get('field');
        $page = $recuperateur->get('page');

        $document = $this->getDocumentSQL();

        $info = $document->getInfo($id_d);
        $type = $info['type'];
        if (! $this->getDroitService()->hasDroit($this->getId_u(), $this->getDroitService()->getDroitEdition($type), $id_e)) {
            $this->setLastError("Vous n'avez pas le droit de faire cette action ($type:edition)");
            $this->redirect("/Document/edition?id_d=$id_d&id_e=$id_e");
        }

        $documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($type);
        $formulaire = $documentType->getFormulaire();

        $theField = $formulaire->getField($field);
        if (!$theField) {
            $this->setLastError("Le champ ($field) n'existe pas pour ce document.");
            $this->redirect("/Document/edition?id_d=$id_d&id_e=$id_e");
        }

        try {
            $action_name = $theField->getProperties('choice-action');
            $this->getActionExecutorFactory()->displayChoice($id_e, $this->getId_u(), $id_d, $action_name, false, $field, $page);
        } catch (Exception $e) {
            $this->setLastError($e->getMessage());
            $this->redirect("/Document/edition?id_d=$id_d&id_e=$id_e&page=$page");
        }
    }

    public function doExternalDataAction()
    {
        $recuperateur = new Recuperateur($_REQUEST);
        $id_d = $recuperateur->get('id_d');
        $id_e = $recuperateur->get('id_e');
        $field = $recuperateur->get('field');
        $page = $recuperateur->get('page');

        $document = $this->getDocument();
        $info = $document->getInfo($id_d);
        $type = $info['type'];

        if (! $this->getDroitService()->hasDroit($this->getId_u(), $this->getDroitService()->getDroitEdition($type), $id_e)) {
            $this->setLastError("Vous n'avez pas le droit de faire cette action ($type:edition)");
            $this->redirect("/Document/edition?id_d=$id_d&id_e=$id_e");
        }

        $actionPossible = $this->getActionPossible();


        if (! $actionPossible->isActionPossible($id_e, $this->getId_u(), $id_d, 'modification')) {
            $this->setLastError("L'action « modification »  n'est pas permise : " . $actionPossible->getLastBadRule());
            header("Location: detail?id_d=$id_d&id_e=$id_e&page=$page");
            exit;
        }

        $documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($type);
        $formulaire = $documentType->getFormulaire();
        $formulaire->setTabNumber($page);

        $theField = $formulaire->getField($field);


        $action_name = $theField->getProperties('choice-action');

        $this->getActionExecutorFactory()->goChoice($id_e, $this->getId_u(), $id_d, $action_name, false, $field, $page);
    }

    /**
     * @throws Exception
     */
    public function doExternalDataApiAction()
    {
        $recuperateur = $this->getPostOrGetInfo();
        $id_d = $recuperateur->get('id_d');
        $id_e = $recuperateur->get('id_e');
        $field = $recuperateur->get('field');

        $document = $this->getDocumentSQL();
        $info = $document->getInfo($id_d);
        $type = $info['type'];

        if (! $this->getDroitService()->hasDroit($this->getId_u(), $this->getDroitService()->getDroitEdition($type), $id_e)) {
            echo "Vous n'avez pas le droit de faire cette action ($type:edition)";
            return;
        }

        $actionPossible = $this->getActionPossible();

        if (! $actionPossible->isActionPossible($id_e, $this->getId_u(), $id_d, 'modification')) {
            echo "L'action « modification »  n'est pas permise : " . $actionPossible->getLastBadRule();
            return;
        }

        $documentType = $this->getDocumentTypeFactory()->getFluxDocumentType($type);
        $formulaire = $documentType->getFormulaire();

        $theField = $formulaire->getField($field);

        $action_name = $theField->getProperties('choice-action');

        $this->getActionExecutorFactory()->goChoice($id_e, $this->getId_u(), $id_d, $action_name, true, $field);
    }


    public function recuperationFichierAction()
    {
        $recuperateur = $this->getGetInfo();
        $id_d = $recuperateur->get('id_d');
        $id_e = $recuperateur->get('id_e');
        $field = $recuperateur->get('field');
        $num = $recuperateur->getInt('num');

        $this->verifDroitLecture($id_e, $id_d);

        $document = $this->getDocument();
        $info = $document->getInfo($id_d);


        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d, $info['type']);


        $file_path = $donneesFormulaire->getFilePath($field, $num);
        $file_name_array = $donneesFormulaire->get($field);
        $file_name = $file_name_array[$num];

        if (! file_exists($file_path)) {
            $this->setLastError("Ce fichier n'existe pas");
            $this->redirect();
        }

        $utilisateur = new UtilisateurSQL($this->getSQLQuery());
        $infoUtilisateur = $utilisateur->getInfo($this->getId_u());
        $nom = $infoUtilisateur['prenom'] . " " . $infoUtilisateur['nom'];

        $this->getJournal()->add(
            Journal::DOCUMENT_CONSULTATION,
            $id_e,
            $id_d,
            "Consulté",
            "$nom a consulté le document $file_name"
        );

        if (mb_strlen($file_name) > 80) {
            $pos = mb_strrpos($file_name, ".");
            $name = mb_substr($file_name, 0, $pos);
            $extension = mb_substr($file_name, $pos + 1, mb_strlen($file_name));
            $file_name = mb_substr($name, 0, 76) . "." . $extension;
        }

        $sendFileToBrowser = $this->getObjectInstancier()->getInstance(SendFileToBrowser::class);
        $sendFileToBrowser->send($file_path, $file_name);
    }

    /**
     * @throws Exception
     */
    public function supprimerFichierAction()
    {
        $id_d = $this->getPostInfo()->get('id_d');
        $page = $this->getPostInfo()->get('page');
        $id_e = $this->getPostInfo()->get('id_e');
        $field = $this->getPostInfo()->get('field');
        $num = $this->getPostInfo()->getInt('num', 0);

        $documentModificationService = $this->getObjectInstancier()
            ->getInstance(DocumentModificationService::class);

        try {
            $documentModificationService->removeFile($id_e, $this->getId_u(), $id_d, $field, $num);
        } catch (Exception $e) {
            $this->setLastError($e->getMessage());
            $this->redirect("/Document/edition?id_d=$id_d&id_e=$id_e&page=$page");
        }

        $this->redirect("/Document/edition?id_d=$id_d&id_e=$id_e&page=$page");
    }

    public function mailsecErrorAction()
    {
        $recuperateur = new Recuperateur($_GET);
        $id_de = $recuperateur->get('id_de');
        $id_e = $recuperateur->get('id_e');
        /** @var DocumentEmail $documentEmail */
        $documentEmail = $this->{'DocumentEmail'};
        $info = $documentEmail->getInfoFromPK($id_de);

        $this->verifDroitLecture($id_e, $info['id_d']);

        header_wrapper("Content-type: text/plain");
        echo str_replace($info['key'], "XXXX-LA-CLE-NE-PEUT-ETRE-DIVULGUEE-ICI-XXXX", $info['last_error']);
    }

    private function isDocumentEmailChunkUpload()
    {
        /* mailsec ? */
        $key = $this->getPostOrGetInfo()->get('key');
        $documentEmail = $this->getObjectInstancier()->getInstance(DocumentEmail::class);
        $mailsec_info = $documentEmail->getInfoFromKey($key);
        if (! $mailsec_info) {
            return false;
        }
        $documentEmailReponseSQL = $this->getObjectInstancier()->getInstance(DocumentEmailReponseSQL::class);
        $id_d_reponse = $documentEmailReponseSQL->getDocumentReponseId($mailsec_info['id_de']);
        if ($this->getPostOrGetInfo()->get('id_d') != $id_d_reponse) {
            return false;
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public function chunkUploadAction()
    {

        $id_e = $this->getPostOrGetInfo()->getInt('id_e');
        $id_d = $this->getPostOrGetInfo()->get('id_d');
        $page = $this->getPostOrGetInfo()->getInt('page');
        $field = $this->getPostOrGetInfo()->get('field');
        $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);


        $info = $this->getDocumentSQL()->getInfo($id_d);

        if (! $this->getDroitService()->hasDroit($this->getId_u(), $this->getDroitService()->getDroitEdition($info['type']), $id_e)) {
            if (! $this->isDocumentEmailChunkUpload()) {
                echo "KO";
                exit_wrapper();
            }
        }

        $config = new Config();
        $config->setTempDir(UPLOAD_CHUNK_DIRECTORY);

        $request = new Request();

        $upload_filepath = UPLOAD_CHUNK_DIRECTORY . "/{$id_e}_{$id_d}_{$field}" . time() . "_" . mt_rand(0, mt_getrandmax());

        $this->getLogger()->debug("Chargement partiel du fichier : $upload_filepath dans (id_e={$id_e},id_d={$id_d},field={$field}");

        if (Basic::save($upload_filepath, $config, $request)) {
            $documentModificationService =
                $this->getObjectInstancier()->getInstance(DocumentModificationService::class);

            if ($donneesFormulaire->getFormulaire()->getField($field)->isMultiple()) {
                $nb_file = $donneesFormulaire->get($field) ? count($donneesFormulaire->get($field)) : 0;
                $this->getLogger()->debug("ajout fichier $nb_file");
                $documentModificationService->addFile($id_e, $this->getId_u(), $id_d, $field, $nb_file, $upload_filepath);
            } else {
                $documentModificationService->addFile($id_e, $this->getId_u(), $id_d, $field, 0, $upload_filepath);
            }
            $this->getLogger()->debug("chargement terminé");
            unlink($upload_filepath);
        }

        if (1 == mt_rand(1, 100)) {
            Uploader::pruneChunks(UPLOAD_CHUNK_DIRECTORY);
        }
        echo "OK";
        exit_wrapper();
    }
}
