<?php

class GlaneurLocal extends Connecteur {

    const FLUX_NAME = 'flux_name';

    const DIRECTORY = 'directory';

    const TYPE_DEPOT = 'type_depot';
    const TYPE_DEPOT_ZIP = 'ZIP';
    const TYPE_DEPOT_FOLDER = 'FOLDER';
    const TYPE_DEPOT_VRAC = 'VRAC';

    /** @var  DonneesFormulaire */
    private $connecteurConfig;

    private $last_message;

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire) {
        $this->connecteurConfig = $donneesFormulaire;
    }

    public function getLastMessage(){
        return $this->last_message;
    }

    public function glaner(){
        $tmpFolder = new TmpFolder();
        $tmp_folder = $tmpFolder->create();
        try {
            $this->glanerThrow($tmp_folder);
        } catch(Exception $e){
            $tmpFolder->delete($tmp_folder);
            throw $e;
        }
        $tmpFolder->delete($tmp_folder);
        return true;
    }

    private function glanerThrow(string $tmp_folder){
        $repertoire = $this->getNextItem();
        if (!$repertoire){
            $this->last_message = "Le répertoire est vide";
            return true;
        }

        $type_depot = $this->connecteurConfig->get(self::TYPE_DEPOT);
        if ($type_depot == self::TYPE_DEPOT_VRAC) {
            //TODO constuire le répertoire avec les données issu des regexp
            throw new Exception("Not implemented");
        } else if ($type_depot == self::TYPE_DEPOT_ZIP){
            $zip = new ZipArchive();
            $handle = $zip->open($repertoire);
            if (!$handle){
                throw new Exception("Impossible d'ouvrir le fichier zip");
            }
            $zip->extractTo($tmp_folder);
            $zip->close();
            $repertoire = $tmp_folder;
        } else if($type_depot == self::TYPE_DEPOT_FOLDER){
           //Nothing to do
        } else {
            throw new UnrecoverableException("Le type de dépot est inconnu");
        }

        return $this->glanerRepertoire($repertoire);
    }

    private function getNextItem(){
        $directory = $this->connecteurConfig->get('directory');

        $directoryIterator = new DirectoryIterator($directory);
        do {
            $current = $directoryIterator->current()->getFilename();
            $directoryIterator->next();
        } while (in_array($current,array('.','..')));
        if (!$current) {
            return false;
        }
        return $this->connecteurConfig->get('directory'). "/".$current;
    }


    private function glanerRepertoire(string $repertoire){
        //TODO : il y a un manifeste ?



        echo $repertoire;
    }


}