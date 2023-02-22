<?php

class FluxDataSedaDossierMarche extends FluxDataSedaDefault
{
    private const NAME_ZIP = 'fichier_zip';

    public function getFilename($key)
    {
        $method = "getFilename_$key";
        if (method_exists($this, $method)) {
            return $this->$method($key);
        }
        return parent::getFilename($key);
    }

    public function getFilepath($key)
    {
        $method = "getFilepath_$key";
        if (method_exists($this, $method)) {
            return $this->$method($key);
        }
        return parent::getFilepath($key);
    }

    public function getFileSHA256($key)
    {
        $method = "getFilesha256_$key";
        if (method_exists($this, $method)) {
            return $this->$method($key);
        }
        return parent::getFileSHA256($key);
    }

    public function get_transfert_id()
    {
        return sha1_file($this->donneesFormulaire->getFilePath('fichier_zip')) . "_" . time();
    }

    private $num_folder = 0;

    /**
     * @return mixed
     * @throws Exception
     */
    public function get_folder()
    {
        $archive_content = $this->getArchiveContent(self::NAME_ZIP);
        $folder = $archive_content['folder'][$this->num_folder];
        $this->num_folder++;
        return $folder;
    }

    private $num_folder_name = 0;

    /**
     * @return mixed
     * @throws Exception
     */
    public function get_folder_name()
    {
        $archive_content = $this->getArchiveContent(self::NAME_ZIP);
        $result =  $archive_content['folder_name'][$this->num_folder_name];
        $this->num_folder_name++;
        return $result;
    }

    private $num_document = 0;

    /**
     * @return mixed
     * @throws Exception
     */
    public function get_document()
    {
        $archive_content = $this->getArchiveContent(self::NAME_ZIP);
        $document = $archive_content['file'][$this->num_document];
        $this->num_document++;
        return $document;
    }

    private $num_document_path = 0;

    /**
     * @return string
     * @throws Exception
     */
    public function getFilepath_document_file()
    {
        $archive_content = $this->getArchiveContent(self::NAME_ZIP);
        return \sprintf(
            '%s/%s/%s',
            $archive_content['tmp_folder'],
            $archive_content['root_directory'],
            $archive_content['file_list'][$this->num_document_path++]
        );
    }

    private $num_documentname = 0;

    /**
     * @return string
     * @throws Exception
     */
    public function getFilename_document_file()
    {

        $archive_content = $this->getArchiveContent(self::NAME_ZIP);

        $result =  $archive_content['file_list'][$this->num_documentname];
        $result = ltrim($result, "/");
        $this->num_documentname++;
        return $result;
    }

    private $num_documentsize = 0;

    /**
     * @return float
     * @throws Exception
     */
    public function get_document_size_in_ko()
    {
        $archive_content = $this->getArchiveContent(self::NAME_ZIP);
        $filename = \sprintf(
            '%s/%s/%s',
            $archive_content['tmp_folder'],
            $archive_content['root_directory'],
            $archive_content['file_list'][$this->num_documentsize++]
        );
        return round(filesize($filename) / 1024);
    }


    private $num_document_sha256 = 0;

    /**
     * @return string
     * @throws Exception
     */
    public function getFilesha256_document()
    {
        $archive_content = $this->getArchiveContent(self::NAME_ZIP);
        return hash_file(
            'sha256',
            \sprintf(
                '%s/%s/%s',
                $archive_content['tmp_folder'],
                $archive_content['root_directory'],
                $archive_content['file_list'][$this->num_document_sha256++]
            )
        );
    }

    /**
     * num_consultation - Marché num_marché –
     * intitulé_marché, notifié le date_notification AAAA-MM-JJ [si case récurrent cochée écrire « (récurrent) -
     * si case infructueux cochée écrire (infructueux) -
     * si case sans_suite cochée écrire (sans suite)] -
     * contenu_versement
     */
    public function get_nom_archive()
    {
        $numero_consultation = $this->donneesFormulaire->get('numero_consultation');
        $numero_marche = $this->donneesFormulaire->get('numero_marche');
        $libelle = $this->donneesFormulaire->get('libelle');
        $date_notification = $this->donneesFormulaire->get('date_notification');
        $nom_archive =  "Consultation $numero_consultation ";

        if ($numero_marche) {
            $nom_archive .= "- Marché $numero_marche ";
        }

        $nom_archive .= "- $libelle, ";

        if ($date_notification) {
            $nom_archive .= "notifié le $date_notification";
        }
        if ($this->donneesFormulaire->get('recurrent')) {
            $nom_archive .= " récurrent";
        }
        if ($this->donneesFormulaire->get('infructueux')) {
            $nom_archive .= " infructueux";
        }
        if ($this->donneesFormulaire->get('sans_suite')) {
            $nom_archive .= " sans suite";
        }

        $contenu_versement = $this->donneesFormulaire->get('contenu_versement');
        if ($contenu_versement) {
            $nom_archive .= " - $contenu_versement";
        }

        return $nom_archive;
    }

    public function get_type_marche()
    {
        $type_marche = $this->donneesFormulaire->get('type_marche');
        if ($type_marche) {
            $lst_type_marche = $this->donneesFormulaire->getFormulaire()->getField("type_marche")->getSelect();
            $type_marche = $lst_type_marche[$this->donneesFormulaire->get('type_marche')];
        }
        return $type_marche;
    }

    public function get_type_consultation()
    {
        $type_consultation = $this->donneesFormulaire->get('type_consultation');
        if ($type_consultation) {
            $lst_type_consultation = $this->donneesFormulaire
                ->getFormulaire()
                ->getField('type_consultation')
                ->getSelect();
            $type_consultation = $lst_type_consultation[$this->donneesFormulaire->get('type_consultation')];
        }
        return $type_consultation;
    }

    public function get_infructueux()
    {
        return $this->donneesFormulaire->get('infructueux') ? "Infructueux" : "";
    }

    public function get_recurrent()
    {
        return $this->donneesFormulaire->get('recurrent') ? "Récurrent" : "";
    }

    public function get_sans_suite()
    {
        return $this->donneesFormulaire->get('sans_suite') ? "Sans suite" : "";
    }

    public function get_attributaire()
    {
        if ($this->donneesFormulaire->get('attributaire')) {
            $result = explode("\n", $this->donneesFormulaire->get('attributaire'));
            array_walk($result, function (&$a) {
                $a = $a ? "Attributaire: $a" : "";
            });
            return $result;
        }
    }

    public function get_mot_cle()
    {
        if ($this->donneesFormulaire->get('mot_cle')) {
            $result = explode("\n", $this->donneesFormulaire->get('mot_cle'));
            array_walk($result, function (&$a) {
                $a = $a ? "$a" : "";
            });
            return $result;
        }
    }

    public function get_description_du_contenu()
    {
        $result = "Dossier de marché public";
        if ($this->getData('contenu_versement')) {
            $result .= " - " . $this->getData('contenu_versement');
        }
        return $result;
    }

    public function get_date_notification_or_date_de_fin()
    {
        $date_notification = $this->donneesFormulaire->get('date_notification');
        if ($date_notification && $date_notification != '1970-01-01') {
            return $date_notification;
        }
        return $this->donneesFormulaire->get('date_fin');
    }
}
