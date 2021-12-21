<?php

class JournalControler extends PastellControler
{
    public function _beforeAction()
    {
        parent::_beforeAction();
        $id_e = $this->getPostOrGetInfo()->getInt('id_e');
        $id_d = $this->getPostOrGetInfo()->get('id_d');
        $type = $this->getPostOrGetInfo()->get('type');


        if ($id_d) {
            $document_info = $this->getDocument()->getInfo($id_d);
            $type = $document_info['type'];
            $this->{'type_e_menu'} = $type;
        }


        $this->setNavigationInfo($id_e, "Journal/index?type=$type");
        $this->{'menu_gauche_link'} = "Journal/index?id_e={$id_e}";

        if (! $id_d && ! $type) {
            $this->{'pages_without_left_menu'} = true;
        }
    }

    public function exportAction()
    {

        $recuperateur = new Recuperateur($_REQUEST);
        $this->{'id_e'} = $recuperateur->getInt('id_e', 0);
        $this->{'type'} = $recuperateur->get('type');
        $this->{'id_d'} = $recuperateur->get('id_d');
        $this->{'id_u'} = $recuperateur->get('id_u');

        $this->verifDroit($this->{'id_e'}, 'journal:lecture');

        $this->{'entite_info'} = $this->getEntiteSQL()->getInfo($this->{'id_e'});
        $this->{'utilisateur_info'} = $this->getUtilisateur()->getInfo($this->{'id_u'});
        $this->{'document_info'} = $this->getDocument()->getInfo($this->{'id_d'});


        $this->{'recherche'} = $recuperateur->get('recherche');
        $this->{'date_debut'} = $recuperateur->get('date_debut', date("Y-m-d"));
        $this->{'date_fin'} = $recuperateur->get('date_fin', date("Y-m-d"));

        $this->{'page_title'} = "Journal des événements - Export";
        $this->{'template_milieu'} = "JournalExport";
        $this->renderDefault();
    }

    public function detailAction()
    {
        $recuperateur = new Recuperateur($_GET);
        $this->{'id_j'} = $recuperateur->getInt('id_j', 0);
        $this->{'offset'} = $recuperateur->getInt('offset', 0);
        $this->{'id_e'} = $recuperateur->getInt('id_e', 0);
        $this->{'type'} = $recuperateur->get('type');
        $this->{'id_d'} = $recuperateur->get('id_d');

        $this->{'info'} = $this->getJournal()->getAllInfo($this->{'id_j'});
        $this->verifDroit($this->{'info'}['id_e'], "journal:lecture");

        /** @var OpensslTSWrapper $opensslTSWrapper */
        $opensslTSWrapper = $this->getInstance("OpensslTSWrapper");

        $this->{'preuve_txt'} = $opensslTSWrapper->getTimestampReplyString($this->{'info'}['preuve']);

        /** @var HorodateurPastell $horodateur */
        $horodateur = $this->getConnecteurFactory()->getGlobalConnecteur('horodateur');
        if ($horodateur) {
            try {
                    $horodateur->verify($this->{'info'}['message_horodate'], $this->{'info'}['preuve']);
                    $this->{'preuve_is_ok'} = true;
            } catch (Exception $e) {
                $this->{'preuve_is_ok'} = false;
                $this->{'preuve_error'} = $e->getMessage();
            }
            if ($this->{'preuve_is_ok'} == false) {
                try {
                    //OK, c'est pas terrible, mais ca permet d'éviter la gestiond d'une constante supplémentaire
                    //pour noter la position du journal au moment de la bascule iso-8859-1 => utf-8
                    $horodateur->verify(utf8_decode($this->{'info'}['message_horodate']), $this->{'info'}['preuve']);
                    $this->{'preuve_is_ok'} = true;
                } catch (Exception $e) {
                    $this->{'preuve_is_ok'} = false;
                    $this->{'preuve_error'} = $e->getMessage();
                }
            }
        } else {
            $this->{'preuve_is_ok'} = false;
            $this->{'preuve_error'} = "Aucun horodateur n'est configuré";
        }

        $this->{'page_title'} = "Événement numéro {$this->{'id_j'}}";
        $this->{'template_milieu'} = "JournalDetail";
        $this->renderDefault();
    }

    public function indexAction()
    {

        $recuperateur = new Recuperateur($_GET);
        $id_e = $recuperateur->getInt('id_e', 0);
        $this->{'offset'} = $recuperateur->getInt('offset', 0);
        $this->{'type'} = $recuperateur->get('type');
        $this->{'id_d'} = $recuperateur->get('id_d');
        $this->{'id_u'} = $recuperateur->get('id_u');
        $this->{'recherche'} = $recuperateur->get('recherche');
        $this->{'date_debut'} = $recuperateur->get('date_debut');
        $this->{'date_fin'} = $recuperateur->get('date_fin');

        $liste_collectivite = $this->getRoleUtilisateur()->getEntite($this->getId_u(), 'journal:lecture');

        if (! $liste_collectivite) {
            header("Location: " . SITE_BASE);
            exit;
        }

        if (! $id_e && (count($liste_collectivite) == 1)) {
            $id_e = $liste_collectivite[0];
        }
        $this->verifDroit($id_e, "journal:lecture");
        $this->{'id_e'} = $id_e;

        $infoEntite = $this->getEntiteSQL()->getInfo($this->{'id_e'});


        $this->{'count'} = $this->getJournal()->countAll(
            $this->{'id_e'},
            $this->{'type'},
            $this->{'id_d'},
            $this->{'id_u'},
            $this->{'recherche'},
            $this->{'date_debut'},
            $this->{'date_fin'}
        );

        $page_title = "Journal des événements";
        if ($this->{'id_e'}) {
            $page_title .= " - " . $infoEntite['denomination'];
        }
        if ($this->{'type'}) {
            $page_title .= " - " . $this->{'type'};
        }
        if ($this->{'id_d'}) {
            $documentInfo = $this->getDocument()->getInfo($this->{'id_d'});
            $page_title .= " - " . $documentInfo['titre'];
        }
        if ($this->{'id_u'}) {
            $infoUtilisateur = $this->getUtilisateur()->getInfo($this->{'id_u'});
            $page_title .= " - " . $infoUtilisateur['prenom'] . " " . $infoUtilisateur['nom'];
        }

        $this->{'limit'} = 20;
        $this->{'all'} = $this->getJournal()->getAll($this->{'id_e'}, $this->{'type'}, $this->{'id_d'}, $this->{'id_u'}, $this->{'offset'}, $this->{'limit'}, $this->{'recherche'}, $this->{'date_debut'}, $this->{'date_fin'}) ;
        $this->{'liste_collectivite'} = $liste_collectivite;

        $this->setNavigationInfo($id_e, "Journal/index?a=a");

        $this->{'infoEntite'} = $infoEntite;
        $this->{'page_title'} = $page_title;
        $this->{'template_milieu'} = "JournalIndex";
        $this->renderDefault();
    }

    public function doExportAction()
    {
        $recuperateur = new Recuperateur($_REQUEST);
        $id_e = $recuperateur->getInt('id_e', 0);
        $type = $recuperateur->get('type');
        $id_d = $recuperateur->get('id_d');
        $id_u = $recuperateur->getInt('id_u');
        $recherche = $recuperateur->get('recherche');
        $date_debut = $recuperateur->get('date_debut');
        $date_fin = $recuperateur->get('date_fin');
        $en_tete_colonne = $recuperateur->get('en_tete_colonne');

        $this->verifDroit($id_e, "journal:lecture");

        $date_debut = date_fr_to_iso($date_debut);
        $date_fin = date_fr_to_iso($date_fin);

        list($sql,$value) = $this->getJournal()->getQueryAll($id_e, $type, $id_d, $id_u, 0, -1, $recherche, $date_debut, $date_fin) ;


        $this->getSQLQuery()->useUnberfferedQuery();

        $this->getSQLQuery()->prepareAndExecute($sql, $value);
        $CSVoutput = new CSVoutput();
        $CSVoutput->displayHTTPHeader("pastell-export-journal-$id_e-$id_u-$type-$id_d.csv");

        $CSVoutput->begin();
        if ($en_tete_colonne) {
            $headers = array(
                'id_journal', 'type', 'id_entite', 'id_utilisateur', 'id_document', 'action', 'message', 'date',
                'horodatage', 'message_horodate', 'type_document', 'numero_acte', 'collectivite_libelle',
                'utilisateur_nom', 'utilisateur_prenom', 'collectivite_siren');
            $CSVoutput->displayLine($headers);
        }
        while ($this->getSQLQuery()->hasMoreResult()) {
            $data = $this->getSQLQuery()->fetch();
            unset($data['preuve']);
            $CSVoutput->displayLine($data);
        }
        $CSVoutput->end();
    }

    public function messageAction()
    {
        $recuperateur = new Recuperateur($_GET);

        $id_j = $recuperateur->get('id_j');

        $info  = $this->getJournal()->getInfo($id_j);

        $this->verifDroit($info['id_e'], "journal:lecture");


        header("Content-Type: text/plain; charset=utf-8");
        header("Content-disposition: attachment; filename=preuve.txt");
        header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
        header("Pragma: public");

        echo $info['message_horodate'];
    }

    public function preuveAction()
    {

        $recuperateur = new Recuperateur($_GET);

        $id_j = $recuperateur->get('id_j');

        $info  = $this->getJournal()->getInfo($id_j);

        $this->verifDroit($info['id_e'], "journal:lecture");

        header("Content-Type: application/timestamp-reply");
        header("Content-Transfer-Encoding: base64");
        header("Content-disposition: attachment; filename=preuve.tsa");

        header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
        header("Pragma: public");


        echo $info['preuve'];
    }
}
