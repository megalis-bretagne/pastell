<?php

class FluxDataSedaActes extends FluxDataStandard  {

    public function getData($key) {
        $method = "get_$key";
        if (method_exists($this, $method)){
            return $this->$method($key);
        }
        return parent::getData($key);
    }

	public function getdonneesFormulaire() {
		return $this->donneesFormulaire;
	}

    public function getFilename($key) {
        $method = "getFilename_$key";
        if (method_exists($this, $method)){
            return $this->$method($key);
        }
        return parent::getFilename($key);
    }

	public function getFilepath($key) {
		$method = "getFilepath_$key";
		if (method_exists($this, $method)){
			return $this->$method($key);
		}
		return parent::getFilepath($key);
	}

	public function getContentType($key) {
		$method = "getContentType_$key";
		if (method_exists($this, $method)){
			return $this->$method($key);
		}
		return parent::getContentType($key);
	}

    public function getFileSHA256($key) {
        $method = "getFilesha256_$key";
        if (method_exists($this, $method)){
            return $this->$method($key);
        }
        return parent::getFileSHA256($key);
    }

    public function get_fichier_actes_sha1(){
        return $this->getFileSHA256('arrete');
    }

    public function get_date_aractes(){
        $xml = simplexml_load_file($this->getFilePath('aractes'));
        $xml->registerXPathNamespace("actes","http://www.interieur.gouv.fr/ACTES#v1.1-20040216");
		$result = $xml->attributes("actes",true);
		if (empty($result->DateReception)){
			throw new Exception("Impossible de récupérer la date de l'AR Acte");
		}
		return strval($result->DateReception);
    }

    public function get_acte_nature(){
        $actes_nature = $this->donneesFormulaire->getFormulaire()->getField("acte_nature")->getSelect();
        return $actes_nature[$this->donneesFormulaire->get('acte_nature')];
    }

    /**
     * « AR048 » pour les actes codifiés 4 (fonction publique) et dont la nature=arrêtés individuels
     * ou Contrats
     * et conventions, 8.2 et dont la nature=arrêtés individuels ;
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
    public function get_restriction_acces(){
        $classification = $this->donneesFormulaire->get('classification');
        $nature = $this->donneesFormulaire->get('acte_nature');
        if ($nature != 3){
            return "AR038";
        }
        if (preg_match("#^4.#",$classification) || preg_match("#^8.2#",$classification)){
            return "AR048";
        }
        return "AR038";
    }

    public function get_arrete_size_in_byte(){
        return filesize($this->getFilePath('arrete'));
    }

    public function get_size_ar_in_bytes(){
        return filesize($this->getFilePath('aractes'));
    }

    public function get_annexe(){

        $annexe = $this->donneesFormulaire->get('autre_document_attache');
        return $annexe;
    }

    public function getContentType_autre_document_attache(){
        static $i = 0;
        $content_type = $this->donneesFormulaire->getContentType('autre_document_attache',$i++);
        if ($content_type == "application/vnd.openxmlformats-officedocument.wordprocessingml.document") {
            return "";
        }
        return $content_type;
    }

	public function getFilepath_autre_document_attache(){
		static $i = 0;
		return $this->donneesFormulaire->getFilePath('autre_document_attache',$i++);
	}

    public function getFilename_autre_document_attache(){
        static $i = 0;
        return $this->donneesFormulaire->getFileName('autre_document_attache',$i++);
    }

    public function getFilesha256_autre_document_attache(){
        static $i = 0;
        return hash_file("sha256",$this->donneesFormulaire->getFilePath('autre_document_attache',$i++));
    }

    public function get_size_autre_document_attache(){
        static $i = 0;
        return filesize($this->donneesFormulaire->getFilePath('autre_document_attache',$i++));
    }

    public function get_langue_annexe(){
        return "fra";
    }

    public function get_signature_size(){
        return filesize($this->donneesFormulaire->getFilePath('signature'));
    }

    public function get_signature_language(){
        return "fra";
    }


}
