<?php

/**
 * Class GlaneurDoc
 * @deprecated PA 3.0
 */
class GlaneurDoc extends Connecteur {
	
	private $objectInstancier;	
	
	/**
	 * @var RecuperationFichier
	 */
	private $connecteurRecuperation;
	private $mode_auto;
    private $fic_exemple_name;
    private $fic_exemple_path;
    private $annexe_regexp;


    public function __construct(ObjectInstancier $objectInstancier){
        $this->objectInstancier = $objectInstancier;
    }

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire){
        $id_ce = $donneesFormulaire->get("connecteur_recup_id");
        $this->connecteurRecuperation = $this->objectInstancier->{'ConnecteurFactory'}->getConnecteurById($id_ce);
        $this->mode_auto = $donneesFormulaire->get('connecteur_auto');
        $this->fic_exemple_name = $donneesFormulaire->getFileName('fic_exemple');
        $this->fic_exemple_path = $donneesFormulaire->getFilePath('fic_exemple');
        $this->setAnnexeRegexp($donneesFormulaire->get("annexe_regexp"));
    }

    public function setAnnexeRegexp($annexe_regexp = "#annexe#i"){
        if (! $annexe_regexp) { $annexe_regexp = "#annexe#i"; }
        $this->annexe_regexp = $annexe_regexp;
    }

    public function recupAllAuto($id_e){
        if (!$this->mode_auto){
            return array("Le mode automatique est désactivé");
        }
        return $this->recupAll($id_e);
    }

    public function recupAll($id_e){
        $result = array();
        foreach($this->connecteurRecuperation->listFile() as $file){
            if (in_array($file, array('.','..'))){
                continue;
            }
            $result[] = $this->recupFile($file,$id_e);
        }
        return $result;
    }

    public function recupFileExemple($id_e){

        $tableau_doc = array();
        if (! $this->fic_exemple_name){
            return "Il n'y a pas de fichier exemple";
        }
        $tmpFolder = $this->objectInstancier->{'TmpFolder'}->create();
        if (! copy($this->fic_exemple_path, $tmpFolder.'/'.$this->fic_exemple_name)) {
            return $this->fic_exemple_name." n'a pas été récupéré";
        }
        try{
            if (substr($this->fic_exemple_name, -4) == ".zip"){
                $result = $this->recupFileThrow($this->fic_exemple_name, $tmpFolder,$id_e);
            }
            else {
                $tableau_doc['doc'] = $this->fic_exemple_name;
                $result = $this->creerDoc($tableau_doc,$tmpFolder,$id_e);
            }
        } catch (Exception $e){
            $this->objectInstancier->{'TmpFolder'}->delete($tmpFolder);
            return "Erreur lors de l'importation : ".$e->getMessage();
        }
        $this->objectInstancier->{'TmpFolder'}->delete($tmpFolder);

        return $result;

    }

	private function recupFile($filename,$id_e){

        $tableau_doc = array('doc'=>array(),'annexe'=>array());
		$tmpFolder = $this->objectInstancier->{'TmpFolder'}->create();
		$this->connecteurRecuperation->retrieveFile($filename, $tmpFolder);
		try{
            if (substr($filename, -4) == ".zip"){
                $result = $this->recupFileThrow($filename, $tmpFolder,$id_e);
            }
            else {
                $tableau_doc['doc'] = $filename;
                $result = $this->creerDoc($tableau_doc,$tmpFolder,$id_e);
            }
		} catch (Exception $e){
			$this->objectInstancier->{'TmpFolder'}->delete($tmpFolder);
			return "Erreur lors de l'importation : ".$e->getMessage();
		}
		$this->connecteurRecuperation->sendFile($tmpFolder,$filename);
		$this->connecteurRecuperation->deleteFile($filename);
		$this->objectInstancier->{'TmpFolder'}->delete($tmpFolder);

		return $result;
	}

	public function isAnnexe($filename){
        return (bool) preg_match($this->annexe_regexp,$filename);
    }

    private function recupTableauDoc($tmpFolder){
        $tableau_doc = array('doc'=>array(),'annexe'=>array());
        foreach(scandir($tmpFolder) as $file){
            if ((substr($file, -4) !== ".zip") && (is_file($tmpFolder."/".$file))) {
                if ($file == "metadata-iparapheur.json") {
                    $tableau_doc['json_metadata'] = $file;
                } elseif ($file == "metadata-sae.json") {
                    $tableau_doc['sae_config'] = $file;
                } elseif ($this->isAnnexe($file)) {
                    $tableau_doc['annexe'][] = $file;
                } else {
                    $tableau_doc['doc'] = $file;
                }
            }
        }

        return $tableau_doc;
    }

	private function recupFileThrow($filename,$tmpFolder,$id_e){
		
		$zip = new ZipArchive();
		$handle = $zip->open($tmpFolder."/".$filename);
		if (!$handle){
			throw new Exception("Impossible d'ouvrir le fichier zip");
		}
		$zip->extractTo($tmpFolder);
		$zip->close();

		$tableau_doc = $this->recupTableauDoc($tmpFolder);
        $doc_principal = $tmpFolder."/".$tableau_doc['doc'];
        if (! is_file($doc_principal)){
            throw new Exception("Le document principal ".$doc_principal." n'a pas été trouvé");
        }

        return $this->creerDoc($tableau_doc,$tmpFolder,$id_e);

	}

    public function creerDoc($tableau_doc,$tmpFolder,$id_e){

        $erreur = "";

        $pastell_type = $this->getFluxName();

        if (!$this->objectInstancier->{'DocumentTypeFactory'}->isTypePresent($pastell_type)){
            throw new Exception("Le type $pastell_type n'existe pas sur cette plateforme Pastell");
        }

        $new_id_d = $this->objectInstancier->{'Document'}->getNewId();
        $this->objectInstancier->{'Document'}->save($new_id_d,$pastell_type);
        $this->objectInstancier->{'DocumentEntite'}->addRole($new_id_d, $id_e, "editeur");

        $actionCreator = new ActionCreator($this->objectInstancier->{'SQLQuery'},$this->objectInstancier->{'Journal'},$new_id_d);

        /** @var DonneesFormulaire $donneesFormulaire */
        $donneesFormulaire = $this->objectInstancier->getInstance('DonneesFormulaireFactory')->get($new_id_d);

        $donneesFormulaire->setData('libelle',$tableau_doc['doc']);

        // Affectation du titre au document
        $titre_fieldname = $donneesFormulaire->getFormulaire()->getTitreField();
        $titre = $donneesFormulaire->get($titre_fieldname);
        $this->objectInstancier->{'Document'}->setTitre($new_id_d,$titre);

        $donneesFormulaire->addFileFromCopy('document',$tableau_doc['doc'],$tmpFolder."/".$tableau_doc['doc']);

        $file_num = 0;
        foreach($tableau_doc['annexe'] as $filename){
            if (! file_exists($tmpFolder."/".$filename)){
                $erreur .= "Le fichier $filename n'a pas été trouvé.";
                continue;
            }
            $donneesFormulaire->addFileFromCopy('autre_document_attache',$filename,$tmpFolder."/".$filename,$file_num);
            $donneesFormulaire->addFileFromCopy('annexe',$filename,$tmpFolder."/".$filename,$file_num);
            $file_num++;
        }

        foreach(array('json_metadata','ged_config_1','ged_config_2','sae_config') as $type_fichier) {
            if (isset($tableau_doc[$type_fichier])) {
                $donneesFormulaire->addFileFromCopy(
                    $type_fichier,
                    $tableau_doc[$type_fichier],
                    $tmpFolder . "/" . $tableau_doc[$type_fichier]
                );
            }
        }


        // Valorisation du cheminement d'après les valeurs par défaut définies dans le connecteur de parametrage associé au flux
        /** @var ParametrageFluxDoc $parametrageFluxDoc */
        $parametrageFluxDoc = $this->objectInstancier->{'ConnecteurFactory'}->getConnecteurByType($id_e, $pastell_type,'ParametrageFlux');
        if ($parametrageFluxDoc) {
            $tabParam = $parametrageFluxDoc->getParametres();
            foreach ($tabParam as $key => $value) {
                $donneesFormulaire->setData($key, $value);
            }
        }

        $erreur = false;
        if (! $donneesFormulaire->isValidable()){
            $erreur = $donneesFormulaire->getLastError();
        }

        if ($erreur) { // création avec erreur
            $message = "Création du document avec erreur: #ID $new_id_d - type : $pastell_type - $titre - Erreur: $erreur";
            $actionCreator->addAction($id_e,0,Action::CREATION,$message);
        }
        else { // création succcès
            $message = "Création du document avec succès #ID $new_id_d - type : $pastell_type - $titre";
            $actionCreator->addAction($id_e,0, Action::MODIFICATION, $message);

            // Valorisation de l'état suivant
            $actionCreator->addAction($id_e, 0,'importation',"Traitement du document");
            $this->objectInstancier->getInstance('ActionExecutorFactory')->executeOnDocument($id_e, 0,$new_id_d,'orientation');
        }
        return $message;
    }

    public function getFluxName(){
        /** @var FluxEntiteSQL $fluxEntiteSQL */
        $fluxEntiteSQL = $this->objectInstancier->{'FluxEntiteSQL'};
        $connecteurInfo = $this->getConnecteurInfo();
        $all = $fluxEntiteSQL->getFluxByConnecteur($connecteurInfo['id_ce']);

        if (empty($all)){
            throw new Exception("Le connecteur n'est associé à aucun flux...");
        }
        if (count($all)> 1){
            throw new Exception("Le connecteur est associé à plusieurs flux...");
        }
        return $all[0];
    }

}