<?php

class FluxDataSedaPieceMarche extends FluxDataSedaDefault
{
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

    /** On utilise deux fois la fonction sha256 sur document, du coup, ca marche plus... */
    public function getFileSHA256_document()
    {
        return hash_file("sha256", $this->donneesFormulaire->getFilePath('document'));
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
            $lst_type_consultation = $this->donneesFormulaire->getFormulaire()->getField("type_consultation")->getSelect();
            $type_consultation = $lst_type_consultation[$this->donneesFormulaire->get('type_consultation')];
        }
        return $type_consultation;
    }

    public function get_etape()
    {
        $etape = $this->donneesFormulaire->get('etape');
        if ($etape) {
            $lst_etape = $this->donneesFormulaire->getFormulaire()->getField("etape")->getSelect();
            $etape = $lst_etape[$this->donneesFormulaire->get('etape')];
        }
        return $etape;
    }

    public function get_type_piece_marche()
    {
        $type_piece_marche = $this->donneesFormulaire->get('type_piece_marche');
        if ($type_piece_marche) {
            $lst_type_piece_marche = $this->donneesFormulaire->getFormulaire()->getField("type_piece_marche")->getSelect();
            $type_piece_marche = $lst_type_piece_marche[$this->donneesFormulaire->get('type_piece_marche')];
        }
        return $type_piece_marche;
    }

    public function get_date_document_iso_8601()
    {
        return date('c', strtotime($this->donneesFormulaire->get('date_document')));
    }

    public function get_document_size_in_bytes()
    {
        return filesize($this->donneesFormulaire->getFilePath('document'));
    }

    public function get_primo_signature_detachee()
    {
        return $this->donneesFormulaire->get('primo_signature_detachee');
    }

    public function get_primo_signature_detachee_size_in_bytes()
    {
        $result = [];

        foreach ($this->donneesFormulaire->get('primo_signature_detachee') as $i => $title) {
            $result[] = filesize($this->donneesFormulaire->getFilePath('primo_signature_detachee', $i));
        }
        return $result;
    }

    public function getContentType_primo_signature_detachee()
    {
        static $i = 0;
        return $this->donneesFormulaire->getContentType('primo_signature_detachee', $i++);
    }

    public function getFilepath_primo_signature_detachee()
    {
        static $i = 0;
        return $this->donneesFormulaire->getFilePath('primo_signature_detachee', $i++);
    }

    public function getFilename_primo_signature_detachee()
    {
        static $i = 0;
        return $this->donneesFormulaire->getFileName('primo_signature_detachee', $i++);
    }

    public function getFilesha256_primo_signature_detachee()
    {
        static $i = 0;
        return hash_file("sha256", $this->donneesFormulaire->getFilePath('primo_signature_detachee', $i++));
    }

    public function get_co_signature_detachee_size_in_bytes()
    {
        return filesize($this->donneesFormulaire->getFilePath('co_signature_detachee'));
    }

    public function get_annexe()
    {
        return $this->donneesFormulaire->get('annexe');
    }

    public function get_annexe_size_in_bytes()
    {

        $result = [];

        foreach ($this->donneesFormulaire->get('annexe') as $i => $title) {
            $result[] = filesize($this->donneesFormulaire->getFilePath('annexe', $i));
        }
        return $result;
    }
    public function getContentType_annexe()
    {
        static $i = 0;
        return $this->donneesFormulaire->getContentType('annexe', $i++);
    }

    public function getFilepath_annexe()
    {
        static $i = 0;
        return $this->donneesFormulaire->getFilePath('annexe', $i++);
    }

    public function getFilename_annexe()
    {
        static $i = 0;
        return $this->donneesFormulaire->getFileName('annexe', $i++);
    }

    public function getFilesha256_annexe()
    {
        static $i = 0;
        return hash_file("sha256", $this->donneesFormulaire->getFilePath('annexe', $i++));
    }

    public function get_journal_size_in_bytes()
    {
        return filesize($this->donneesFormulaire->getFilePath('journal'));
    }

    public function get_is_recupere($key)
    {
        return parent::getData($key) ? 'MAIL_RECUPERE_OUI' : 'MAIL_RECUPERE_NON';
    }

    public function get_AccessRestrictionRule()
    {

        if ($this->donneesFormulaire->get('etape') == 'DCE') {
            return 'AR038';
        }
        $type_piece_marche = $this->donneesFormulaire->get('type_piece_marche');
        if (in_array($type_piece_marche, ['LC', 'RC', 'CCAP', 'CCTP', 'ARN'])) {
            return 'AR038';
        }

        return 'AR039';
    }

    public function get_AppraisalRule_Code()
    {

        $etape = $this->donneesFormulaire->get('etape');
        if (in_array($etape, ['DCE', 'ONR'])) {
            return 'detruire';
        }
        if (($etape == 'OR') && ($this->donneesFormulaire->get('recurrent') == true)) {
            return 'detruire';
        }
        $type_piece_marche = $this->donneesFormulaire->get('type_piece_marche');
        if (in_array($type_piece_marche, ['RP','RDP'])) {
            return "detruire";
        }

        return 'conserver';
    }

    public function get_AppraisalRule_Duration()
    {

        if ($this->donneesFormulaire->get('etape') == 'DCE') {
            $type_piece_marche = $this->donneesFormulaire->get('type_piece_marche');
            if (!(in_array($type_piece_marche, ['AC', 'AL']))) {
                return 'P5Y';
            }
        }

        return 'P10Y';
    }
}
