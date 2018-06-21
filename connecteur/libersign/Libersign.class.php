<?php
class Libersign extends SignatureConnecteur {

	/** @var DonneesFormulaire */
	private $collectiviteProperties;
	
	public function setConnecteurConfig(DonneesFormulaire $collectiviteProperties){
		$this->collectiviteProperties = $collectiviteProperties;
	}

	public function getLibersignURL(){
		return SITE_BASE."/Extension/web/pastell-extension-adullact-projet/libersign/";
	}

    public function getSha1($xml_content){
        $tmp_file = sys_get_temp_dir(). "/pastell_xml_c14n_".mt_rand(0,mt_getrandmax());
        file_put_contents($tmp_file, $xml_content);


        $xml_starlet_path = $this->collectiviteProperties->get('libersign_xmlstarlet_path')?:'/usr/bin/xmlstarlet';
        if (! is_executable($xml_starlet_path)){
            throw new Exception("Impossible d'executer le programme xmlstarlet ($xml_starlet_path)");
        }

        $c14n_file = sys_get_temp_dir(). "/pastell_xml_c14n_".mt_rand(0,mt_getrandmax());

        $command = "$xml_starlet_path c14n --exc-without-comments {$tmp_file} > $c14n_file";

        if (PHP_OS == "Darwin"){
            //Steeve sucks
            $command = "DYLD_LIBRARY_PATH=''; $command";
        }

        exec($command,$ret,$out);

        if (! file_exists($c14n_file)){
            throw new Exception("Impossible de créer le fichier XML canonique $c14n_file");
        }

        if (filesize($c14n_file) == 0){

            /*
             * Si un noeud fait plus de 10Mo, alors xmlstarlet est incapable d'obtenir la canonicalisation
             */

            throw new Exception("Impossible de signer le fichier : un noeud est trop gros (>10Mo)");

        }

        //echo $c14n_file;exit;

        $result = hash_file("sha256",$c14n_file);
        //$result = sha1_file($c14n_file);

        unlink($tmp_file);
        unlink($c14n_file);

        return $result;
    }

	
	// DEBUT Helios signature locale
	private function checkRecetteOrDepensePES($xml){
		if ($xml->PES_DepenseAller){
			return;
		}
		if($xml->PES_RecetteAller) {
			return;
		}
		throw new Exception("Le bordereau ne contient ni Depense ni Recette");
	}
	
	private function hasIdOnAllBordereauPES($xml){
		foreach(array('PES_DepenseAller','PES_RecetteAller') as $tag){
			if (! $xml->$tag){
				continue;
			}
			foreach($xml->$tag->Bordereau as $bordereau){
				if (empty($bordereau['Id'])){
					return false;
				}
			}
		}
		return true;
	}
	
	public function getInfoForSignaturePES($xml_file_path){
		$xml = simplexml_load_file($xml_file_path,'SimpleXMLElement', LIBXML_PARSEHUGE);
	
		$this->checkRecetteOrDepensePES($xml);
	
		$id = array();
		$hash = array();
		$isBordereau = false;

		if( $this->hasIdOnAllBordereauPES($xml) ){
			foreach(array('PES_DepenseAller','PES_RecetteAller') as $tag){
				if (! $xml->$tag){
					continue;
				}
				/** @var SimpleXMLElement $bordereau */
				foreach($xml->$tag->Bordereau as $bordereau){
					$isBordereau = true;
					$id[]= strval($bordereau['Id']);
					$hash[] = $this->getSha1($bordereau->asXML());
				}
			}
		} else if( isset( $xml['Id'] ) && !empty($xml['Id'] ) ) {
			$id[]  = strval($xml['Id']);
			$hash[] = $this->getSha1($xml->asXML());
			$isBordereau = false;
		} else {
			throw new Exception("Le bordereau du fichier PES ne contient pas d'identifiant valide, ni la balise PESAller : signature impossible");
		}
	
		$info = array();
		if($isBordereau) {
			$info['isbordereau'] = true;
			$info['bordereau_hash'] = implode(",",$hash);
			$info['bordereau_id'] = implode(",",$id);
		}  else {
			$info['isbordereau'] = false;
			$info['flux_hash'] = implode(",",$hash);
			$info['flux_id'] = implode(",",$id);
		}
		return $info;
	}
	
	public function injectSignaturePES($original_file_path,$signature, $isBordereau){
		$all_signature = explode(",",$signature);
	
		$domDocument = new DOMDocument();
		$domDocument->load($original_file_path,LIBXML_PARSEHUGE);
	
		if( $isBordereau ) {
			$all_bordereau = $domDocument->getElementsByTagName('Bordereau');
	
			foreach($all_signature as $num_bordereau => $signature) {
				$signature_1 = base64_decode($signature);
				$signatureDOM = new DOMDocument();
				$signatureDOM->loadXML($signature_1,LIBXML_PARSEHUGE);
				$signature = $signatureDOM->firstChild->firstChild;
				$cloned = $signature->cloneNode(TRUE);
	
				$bordereauNode = $all_bordereau->item($num_bordereau);
	
				$bordereauNode->appendChild($domDocument->importNode($cloned,true));
			}
		}
		else {
			$signature_1 = base64_decode($signature);
			$signatureDOM = new DOMDocument();
			$signatureDOM->loadXML($signature_1,LIBXML_PARSEHUGE);
			$signature = $signatureDOM->firstChild->firstChild;
				
			$rootNode = $domDocument->documentElement;
			$rootNode->appendChild($domDocument->importNode($signature,true));
		}
	
		return $domDocument->saveXML();
	}
	// FIN Helios signature locale
	
	public function getNbJourMaxInConnecteur(){
		throw new Exception("Not implemented");
	}
	
	public function getSousType(){
		throw new Exception("Not implemented");
	}
	
	public function getDossierID($id,$name){
		return "n/a";
	}
	
	public function sendDocument($typeTechnique,$sousType,$dossierID,$document_content,$content_type,array $all_annexes = array(),$date_limite=false,$visuel_pdf=''){
		throw new Exception("Not implemented --");
	}
	
	public function getHistorique($dossierID){
		throw new Exception("Not implemented");
	}
	
	public function getSignature($dossierID){
		throw new Exception("Not implemented");
	}

    public function sendHeliosDocument($typeTechnique,$sousType,$dossierID,$document_content,$content_type,$visuel_pdf,	array $metadata = array()){
        throw new Exception("Not implemented");
    }
	
	public function getAllHistoriqueInfo($dossierID){
		throw new Exception("Not implemented");
	}
	
	public function getLastHistorique($dossierID){
		throw new Exception("Not implemented");		
	}
	
	public function effacerDossierRejete($dossierID){
		throw new Exception("Not implemented");
	}
	
	public function isLocalSignature(){
		return true;
	}

    public function displayLibersignJS(){
        $libersign_applet_url = $this->collectiviteProperties->get('libersign_applet_url');
        $libersign_extension_update_url = $this->collectiviteProperties->get('libersign_extension_update_url');
        $libersign_help_url = $this->collectiviteProperties->get('libersign_help_url');
        include(__DIR__."/template/LibersignJS.php");
    }

}