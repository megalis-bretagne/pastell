<?php

class FluxDataSedaFactureCPP extends FluxDataSedaDefault
{
    private $metadata;

    public function setMetadata(array $metadata)
    {
        $this->metadata = $metadata;
    }

    public function getData($key)
    {
        $method = "get_$key";
        if (method_exists($this, $method)) {
            return $this->$method($key);
        }

        if (isset($this->metadata[$key])) {
            return $this->metadata[$key];
        }

        return parent::getData($key);
    }


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

    public function getContentType($key)
    {
        $method = "getContentType_$key";
        if (method_exists($this, $method)) {
            return $this->$method($key);
        }
        return parent::getContentType($key);
    }

    public function getFileSHA256($key)
    {
        $method = "getFilesha256_$key";
        if (method_exists($this, $method)) {
            return $this->$method($key);
        }
        return parent::getFileSHA256($key);
    }

    /** On utilise deux fois la fonction sha256 sur fichier_facture, du coup, ca marche plus avec FluxDataSedaDefault... */
    public function getFileSHA256_fichier_facture()
    {
        return hash_file("sha256", $this->donneesFormulaire->getFilePath('fichier_facture'));
    }

    public function get_facture_type()
    {
        $facture_type = $this->metadata['facture_type'];
        if ($facture_type == '381') {
            return 'Avoir';
        }
        return 'Facture';
    }

    public function get_facture_cadre()
    {
        $valeur_cadre =  [
        "A1" => "A1- Dépôt par un fournisseur d'une facture",
                "A2" => "A2- Dépôt par un fournisseur d'une facture déjà payée",
                "A3" => "A3- Dépôt par un fournisseur d'un mémoire de frais de justice",
                "A4" => "A4- Dépôt par un fournisseur d'un projet de décompte mensuel",
                "A5" => "A5- Dépôt par un fournisseur d'un état d'acompte",
                "A6" => "A6- Dépôt par un fournisseur d'un état d'acompte validé",
                "A7" => "A7- Dépôt par un fournisseur d'un projet de décompte final",
                "A8" => "A8- Dépôt par un fournisseur d'un décompte général et définitif",
                "A9" => "A9- Dépôt par un sous-traitant d'une facture",
                "A10" => "A10- Dépôt par un sous-traitant d'un projet de décompte mensuel",
                "A12" => "A12- Dépôt par un cotraitant d'une facture",
                "A13" => "A13- Dépôt par un cotraitant d'un projet de décompte mensuel",
                "A14" => "A14- Dépôt par un cotraitant d'un projet de décompte final",
                "A15" => "A15- Dépôt par une MOE d'un état d'acompte",
                "A16" => "A16- Dépôt par une MOE d'un état d'acompte validé",
                "A17" => "A17- Dépôt par une MOE d'un projet de décompte général",
                "A18" => "A18- Dépôt par une MOE d'un décompte général",
                "A19" => "A19- Dépôt par une MOE d'un état d'acompte validé",
                "A20" => "A20- Dépôt par une MOE d'un décompte général",
                "A21" => "A21- Dépôt par un bénéficiaire d'une demande de remboursement de la TIC",
                "A22" => "A22- Projet de décompte général déposé par le fournisseur ou le mandataire",
                "A23" => "A23- Décompte général et définitif tacite déposé par le fournisseur ou le mandataire",
                "A24" => "A24- Dépôt d'un décompte gérénral et définitif par la maitrise d oeuvrage",
                "A25" => "A25- Décompte général et définitif déposé par la maitrise d ouvrage dans le cadre de la facturation d un marché de travaux"
        ];

        return $valeur_cadre[$this->metadata['facture_cadre']];
    }

    public function get_date_facture_iso_8601()
    {
        return date("c", strtotime($this->metadata['date_facture']));
    }

    public function get_date_facture()
    {
        return date('Y-m-d', strtotime($this->metadata['date_facture']));
    }

    public function get_pour_destinataire_nom()
    {
        if ($this->metadata['destinataire_nom']) {
            return ' pour ' . $this->metadata['destinataire_nom'];
        }
        return '';
    }

    public function get_fichier_facture_size_in_bytes()
    {
        return filesize($this->donneesFormulaire->getFilePath('fichier_facture'));
    }

    public function get_facture_pj_01_size_in_bytes()
    {
        return filesize($this->donneesFormulaire->getFilePath('facture_pj_01'));
    }

    public function get_facture_pj_02()
    {
        return $this->donneesFormulaire->get('facture_pj_02');
    }

    public function get_facture_pj_02_size_in_bytes()
    {

        $result = [];

        foreach ($this->donneesFormulaire->get('facture_pj_02') as $i => $title) {
            $result[] = filesize($this->donneesFormulaire->getFilePath('facture_pj_02', $i));
        }
        return $result;
    }
    public function getContentType_facture_pj_02()
    {
        static $i = 0;
        return $this->donneesFormulaire->getContentType('facture_pj_02', $i++);
    }

    public function getFilepath_facture_pj_02()
    {
        static $i = 0;
        return $this->donneesFormulaire->getFilePath('facture_pj_02', $i++);
    }

    public function getFilename_facture_pj_02()
    {
        static $i = 0;
        return $this->donneesFormulaire->getFileName('facture_pj_02', $i++);
    }

    public function getFilesha256_facture_pj_02()
    {
        static $i = 0;
        return hash_file("sha256", $this->donneesFormulaire->getFilePath('facture_pj_02', $i++));
    }
}
