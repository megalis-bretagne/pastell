<?php

require_once __DIR__."/lib/GlaneurLocalDocumentInfo.class.php";
require_once __DIR__."/lib/GlaneurLocalDocumentCreator.class.php";
require_once __DIR__."/lib/GlaneurLocalFilenameMatcher.class.php";

class GlaneurLocal extends Connecteur {

    const DIRECTORY = 'directory';
    const DIRECTORY_SEND = 'directory_send';

    const TYPE_DEPOT = 'type_depot';
    const TYPE_DEPOT_ZIP = 'ZIP';
    const TYPE_DEPOT_FOLDER = 'FOLDER';
    const TYPE_DEPOT_VRAC = 'VRAC';

    /* Pour le mode manifest */
    const MANIFEST_TYPE = 'manifest_type';
    const MANIFEST_FILENAME = 'manifest_filename';

    /* Pour le mode filename_matcher */
    const FLUX_NAME = 'flux_name';
    const FILE_PREG_MATCH = 'file_preg_match';
    const METADATA_STATIC = 'metadata_static';

    const ACTION_OK = 'action_ok';
    const ACTION_KO = 'action_ko';


    /** @var  DonneesFormulaire */
    private $connecteurConfig;


    private $last_message;
    private $created_id_d;

    /** @var GlaneurLocalDocumentCreator  */
    private $glaneurLocalDocumentCreator;

    /** @var DocumentTypeFactory */
    private $documentTypeFactory;

    public function __construct(
        DocumentTypeFactory $documentTypeFactory,
        GlaneurLocalDocumentCreator $glaneurLocalDocumentCreator
    ) {
        $this->documentTypeFactory = $documentTypeFactory;
        $this->glaneurLocalDocumentCreator = $glaneurLocalDocumentCreator;
    }

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire) {
        $this->connecteurConfig = $donneesFormulaire;
    }

    public function getLastMessage(){
        return $this->last_message;
    }

    public function getCreatedId_d(){
        return $this->created_id_d;
    }

    /** @throws Exception */
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

    /**
     * @param $tmp_folder
     * @throws Exception
     * @return bool
     */
    private function glanerThrow(string $tmp_folder){
        $repertoire = $this->getNextItem();
        if (!$repertoire){
            $this->last_message[] = "Le répertoire est vide";
            return true;
        }

        $type_depot = $this->connecteurConfig->get(self::TYPE_DEPOT);
        if ($type_depot == self::TYPE_DEPOT_VRAC) {
            //TODO constuire le répertoire avec les données issu des regexp
            throw new Exception("Dépot vrac  : Not implemented");
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

        $result =  $this->glanerRepertoire($repertoire);
        $directory_send = $this->connecteurConfig->get(self::DIRECTORY_SEND);
        //marche pour TYPE_FOLDER et TYPE_ZIP
        //TODO pour VRAC
        if ($directory_send){
            $file_deplacement =  $directory_send."/".basename($repertoire);
            $i = 0;
            while(file_exists($file_deplacement)){
                $file_deplacement = $directory_send."/".basename($repertoire)."-$i";
                $i++;
            }

            rename($repertoire,$file_deplacement);
        } else {
            $tmpFolder = new TmpFolder();
            $tmpFolder->delete($repertoire);
        }

        return $result;
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


    /**
     * @param $repertoire
     * @throws Exception
     */
    private function glanerRepertoire(string $repertoire){
        // Le mode manifeste à précédence sur le mode filename_matcher
        if ($this->connecteurConfig->get(self::MANIFEST_FILENAME)) {
            $glaneurLocalDocumentInfo = $this->glanerModeManifest($repertoire);
        } else {
            $glaneurLocalDocumentInfo = $this->glanerModeFilematcher($repertoire);
        }

        $this->createDocument($glaneurLocalDocumentInfo,$repertoire);

    }

    /**
     * @param GlaneurLocalDocumentInfo $glaneurLocalDocumentInfo
     * @param string $repertoire
     * @return string
     * @throws Exception
     */
    private function createDocument(GlaneurLocalDocumentInfo $glaneurLocalDocumentInfo,string $repertoire){
        $id_d = $this->glaneurLocalDocumentCreator->create($glaneurLocalDocumentInfo,$repertoire);
        $this->last_message[] = "Création du document $id_d";
        $this->created_id_d[] = $id_d;
        return $id_d;
    }

    /**
     * @param $repertoire
     * @return GlaneurLocalDocumentInfo
     * @throws Exception
     */
    private function glanerModeFilematcher($repertoire){
        $nom_flux = $this->connecteurConfig->get(self::FLUX_NAME);
        if (!$nom_flux){
            throw new UnrecoverableException("Impossible de trouver le nom du flux à créer");
        }

        $glaneurLocalFilenameMatcher = new GlaneurLocalFilenameMatcher();

        $file_match = $glaneurLocalFilenameMatcher->getFilenameMatching(
            $this->connecteurConfig->get(self::FILE_PREG_MATCH),
            $this->getCardinalite($nom_flux),
            $this->getFileList($repertoire)
        );

        $glaneurLocalDocumentInfo = new GlaneurLocalDocumentInfo($this->getConnecteurInfo()['id_e']);
        $glaneurLocalDocumentInfo->nom_flux = $nom_flux;
        $glaneurLocalDocumentInfo->element_files_association = $file_match;
        $glaneurLocalDocumentInfo->metadata = $this->getMetadataStatic($file_match);
        $glaneurLocalDocumentInfo->action_ok = $this->connecteurConfig->get(self::ACTION_OK);
        $glaneurLocalDocumentInfo->action_ko = $this->connecteurConfig->get(self::ACTION_KO);
        return $glaneurLocalDocumentInfo;
    }

    /**
     * @param $file_match
     * @return array
     * @throws Exception
     */
    private function getMetadataStatic(array $file_match){
        $metadata_static = $this->connecteurConfig->get(self::METADATA_STATIC);
        $metadata = array();
        foreach(explode("\n",$metadata_static) as $line){
            $r = explode(':',$line);
            if (count($r)<2){
                continue;
            }
            $key = trim($r[0]);
            $value = trim($r[1]);

            if (preg_match("#^%(.*)%$#",$value,$matches)){
                if (empty($file_match[$matches[1]][0])){
                    throw new Exception("$matches[1] n'a pas été trouvé dans la correspondance des fichiers");
                }
                $value = $file_match[$matches[1]][0];
            }
            $metadata[$key] = $value;
        }
        return $metadata;
    }

    private $cardinalite = null;

    /**
     * @param $type_document
     * @return array
     * @throws Exception
     */
    private function getCardinalite($type_document){
        if ($this->cardinalite === null){
            $documentType = $this->documentTypeFactory->getFluxDocumentType($type_document);

            if (! $documentType->exists()){
                throw new UnrecoverableException("Impossible de trouver le type $type_document sur ce pastell");
            }
            $cardinalite = array();
            foreach($documentType->getFormulaire()->getAllFields() as $field){
                if ($field->getType() == 'file'){
                    $cardinalite[$field->getName()] = $field->getProperties('is_multiple')?'n':'1';
                }
            }
            $this->cardinalite = $cardinalite;
        }
        return $this->cardinalite;
    }



    private function getFileList(string $directory){
        $result = array();
        foreach(new DirectoryIterator($directory) as $file){
            if ($file->isFile()) {
                $result[] = $file->getFilename();
            }
        };
        return $result;
    }

    /**
     * @param $repertoire
     * @throws Exception
     */
    private function glanerModeManifest($repertoire) {
        print_r($repertoire);
        //TODO traiter le manifeste
        throw new Exception("Not Implemented");
    }


}