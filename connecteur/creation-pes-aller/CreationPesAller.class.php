<?php

/**
 * Class CreationPesAller
 * @deprecated PA 3.0
 */
class CreationPesAller extends Connecteur {

    const MANIFEST_FILENAME = 'manifest.xml';

    private $objectInstancier;

    /**
     * @var RecuperationFichier
     */
    private $connecteurRecuperation;
    private $mode_auto;

    /** @var  DonneesFormulaire */
    private $connecteurConfiguration;

    public function __construct(ObjectInstancier $objectInstancier){
        $this->objectInstancier = $objectInstancier;
    }

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire){
        $id_ce = $donneesFormulaire->get("connecteur_recup_id");
        $this->connecteurRecuperation = $this->objectInstancier->{'ConnecteurFactory'}->getConnecteurById($id_ce);
        $this->mode_auto = $donneesFormulaire->get('connecteur_auto');
        $this->connecteurConfiguration = $donneesFormulaire;
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

    private function recupFile($filename,$id_e){
        if (substr($filename, -4) !== ".xml"){
            return "$filename n'est pas un fichier xml";
        }
        $tmpFolder = $this->objectInstancier->{'TmpFolder'}->create();
        $this->connecteurRecuperation->retrieveFile($filename, $tmpFolder);
        try{
            $result = $this->recupFileThrow($filename, $tmpFolder,$id_e);
        } catch (Exception $e){
            $this->objectInstancier->{'TmpFolder'}->delete($tmpFolder);
            return "Erreur lors de l'importation : ".$e->getMessage();
        }
        $this->connecteurRecuperation->sendFile($tmpFolder,$filename);
        $this->connecteurRecuperation->deleteFile($filename);
        $this->objectInstancier->{'TmpFolder'}->delete($tmpFolder);

        return $result;
    }


    private function recupFileThrow($filename,$tmpFolder,$id_e){
        $erreur = "";
        $isEnvoiAuto = true;

        $pastell_type =  $this->connecteurConfiguration->get('nom_flux');
        if (! $pastell_type) {
            $pastell_type = 'helios-automatique';
        }

        if (!$this->objectInstancier->{'DocumentTypeFactory'}->isTypePresent($pastell_type)){
            throw new Exception("Le type $pastell_type n'existe pas sur cette plateforme Pastell");
        }

        $new_id_d = $this->objectInstancier->{'Document'}->getNewId();
        $this->objectInstancier->{'Document'}->save($new_id_d,$pastell_type);
        $this->objectInstancier->{'DocumentEntite'}->addRole($new_id_d, $id_e, "editeur");

        $actionCreator = new ActionCreator($this->objectInstancier->{'SQLQuery'},$this->objectInstancier->{'Journal'},$new_id_d);

        /** @var DonneesFormulaire $donneesFormulaire */
        $donneesFormulaire = $this->objectInstancier->{'DonneesFormulaireFactory'}->get($new_id_d);

        $donneesFormulaire->setData('objet',$filename);
        $this->objectInstancier->{'Document'}->setTitre($new_id_d,$filename);

        $donneesFormulaire->addFileFromCopy('fichier_pes',$filename,$tmpFolder."/".$filename);


        $donneesFormulaire->setData('envoi_signature_check',$this->connecteurConfiguration->get('envoi_signature'));
        $donneesFormulaire->setData('envoi_signature',$this->connecteurConfiguration->get('envoi_signature'));
        $donneesFormulaire->setData('envoi_tdt',$this->connecteurConfiguration->get('envoi_tdt'));
        $donneesFormulaire->setData('envoi_ged',$this->connecteurConfiguration->get('envoi_ged'));
        $donneesFormulaire->setData('envoi_sae',$this->connecteurConfiguration->get('envoi_sae'));

        $donneesFormulaire->setData('iparapheur_type',$this->connecteurConfiguration->get('iparapheur_type'));
        $donneesFormulaire->setData('iparapheur_sous_type',$this->connecteurConfiguration->get('iparapheur_sous_type'));

        if (! $donneesFormulaire->isValidable()){
            $erreur .= $donneesFormulaire->getLastError();
        }

        if ($erreur) { // création avec erreur
            $actionCreator->addAction($id_e,0,Action::CREATION,"Importation du document (récupération) avec erreur: $erreur");
            return "Création du document avec erreur: #ID $new_id_d - type : $pastell_type - $filename - Erreur: $erreur";
        }
        else { // création succcès
            $actionCreator->addAction($id_e,0,Action::MODIFICATION,"Importation du document (récupération) succès");
            if ($isEnvoiAuto) {
                $actionCreator->addAction($id_e,0,'importation',"Traitement du document");
                $this->objectInstancier->{'ActionExecutorFactory'}->executeOnDocument($id_e,0,$new_id_d,'orientation');
            }
            return "Création du document #ID $new_id_d - type : $pastell_type - $filename";
        }
    }




}