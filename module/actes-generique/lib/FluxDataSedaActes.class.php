<?php

class FluxDataSedaActes extends FluxDataSedaDefault
{
    /* Clé à mettre sur une annotation connecteur_info */
    public const ID_PRODUCTEUR_HORS_RH = 'id_producteur_hors_rh';
    public const ID_PRODUCTEUR_RH = 'id_producteur_rh';

    public const LIBELLE_PRODUCTEUR_HORS_RH = 'libelle_producteur_hors_rh';
    public const LIBELLE_PRODUCTEUR_RH = 'libelle_producteur_rh';

    public function getdonneesFormulaire()
    {
        return $this->donneesFormulaire;
    }

    public function getContentType($key)
    {
        $method = "getContentType_$key";
        if (method_exists($this, $method)) {
            return $this->$method($key);
        }
        return parent::getContentType($key);
    }

    public function get_fichier_actes_sha1()
    {
        return hash('sha256', $this->getdonneesFormulaire()->getFilePath('arrete'));
    }

    /**
     * @return string
     * @throws Exception
     */
    public function get_date_aractes()
    {
        $simpleXMLWrapper = new SimpleXMLWrapper();
        $xml = $simpleXMLWrapper->loadFile($this->getdonneesFormulaire()->getFilePath('aractes'));

        $xml->registerXPathNamespace("actes", "http://www.interieur.gouv.fr/ACTES#v1.1-20040216");
        $result = $xml->attributes("actes", true);
        if (empty($result->DateReception)) {
            throw new UnrecoverableException("Impossible de récupérer la date de l'AR Acte");
        }
        return strval($result->DateReception);
    }

    public function get_acte_nature()
    {
        $acte_nature = $this->donneesFormulaire->getFormulaire()->getField("acte_nature")->getSelect();
        return $acte_nature[$this->donneesFormulaire->get('acte_nature')];
    }

    /**
     * @throws UnrecoverableException
     */
    public function get_id_producteur()
    {
        $id_producteur_key = $this->hasDonneesACaracterePersonnel() ?
            self::ID_PRODUCTEUR_RH :
            self::ID_PRODUCTEUR_HORS_RH;
        return $this->getConnecteurContent($id_producteur_key);
    }

    /**
     * @throws UnrecoverableException
     */
    public function get_libelle_producteur()
    {
        $id_producteur_key = $this->hasDonneesACaracterePersonnel() ?
            self::LIBELLE_PRODUCTEUR_RH :
            self::LIBELLE_PRODUCTEUR_HORS_RH;
        return $this->getConnecteurContent($id_producteur_key);
    }

    /**
     * « AR048 » pour les actes codifiés 4 (fonction publique) et dont la nature=arrêtés individuels ou Contrats et conventions,
     *  8.2 et dont la nature=arrêtés individuels ;
     *
     * « AR038 » pour tous les autres actes (méta donnée).
     *
     * La règle AR048 pour les arrêtés individuels codifiés 4 est restrictive,
     * la plupart des actes étant librement communicables.
     * Néanmoins, il serait difficile d'identifier automatiquement les actes contenant
     * des données à caractère personnel (DCP) : le délai de communicabilité de 50 ans
     * est donc appliqué à l'ensemble des actes codifiés comme tel, afin de s'assurer du respect des
     * prescriptions du Code du patrimoine
     * . Les actes librement communicables seront alors consultables via une demande de
     * communication au service producteur ou au service Archives. {{pastell:flux:restriction_acces}}
     */
    public function get_restriction_acces()
    {
        return $this->hasDonneesACaracterePersonnel() ? "AR048" : "AR038";
    }

    private function hasDonneesACaracterePersonnel()
    {
        $classification = $this->donneesFormulaire->get('classification');
        $nature = $this->donneesFormulaire->get('acte_nature');
        if (in_array($nature, array(3,4)) && preg_match("#^4.#", $classification)) {
            return true;
        }
        if ($nature == 3 && preg_match("#^8.2#", $classification)) {
            return true;
        }
        return false;
    }

    /**
     * @return false|int
     * @throws DonneesFormulaireException
     */
    public function get_arrete_size_in_byte()
    {
        return parent::getFilesize('arrete');
    }


    /**
     * @return false|int
     * @throws DonneesFormulaireException
     */
    public function get_size_ar_in_bytes()
    {
        return parent::getFilesize('aractes');
    }

    /**
     * @deprecated PA 3.0 revoir le profil afin de remplacer {{annexe}} par {{autre_document_attache}}
     * @return array|string
     */
    public function get_annexe()
    {
        return $this->donneesFormulaire->get('autre_document_attache');
    }


    private $content_type_autre_document_attache = 0;
    public function getContentType_autre_document_attache()
    {
        $content_type = $this->donneesFormulaire->getContentType('autre_document_attache', $this->content_type_autre_document_attache++);
        if ($content_type == "application/vnd.openxmlformats-officedocument.wordprocessingml.document") {
            return "";
        }
        return $content_type;
    }

    private $size_autre_document_attache = 0;
    public function get_size_autre_document_attache()
    {
        return filesize($this->donneesFormulaire->getFilePath('autre_document_attache', $this->size_autre_document_attache++));
    }

    public function get_langue_annexe()
    {
        return "fra";
    }

    public function get_signature_size()
    {
        return filesize($this->donneesFormulaire->getFilePath('signature'));
    }

    public function get_signature_language()
    {
        return "fra";
    }


    /** WD envoi une date trop précise... */
    public function get_date_de_lacte()
    {
        $date_de_lacte = $this->donneesFormulaire->get('date_de_lacte');
        return date("Y-m-d", strtotime($date_de_lacte));
    }
}
