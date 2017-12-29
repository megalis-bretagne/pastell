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
    const MANIFEST_FILENAME_DEFAULT = 'manifest.xml';
    const MANIFEST_TYPE_NONE = 'no';
    const MANIFEST_TYPE_XML = 'xml';

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
        $type_depot = $this->connecteurConfig->get(self::TYPE_DEPOT);

        if ($type_depot == self::TYPE_DEPOT_VRAC){
            return $this->glanerVrac($tmp_folder);
        }

        if ($type_depot == self::TYPE_DEPOT_FOLDER){
            return $this->glanerFolder();
        }

        if ($type_depot == self::TYPE_DEPOT_ZIP){
            return $this->glanerZip($tmp_folder);
        }
        throw new UnrecoverableException("Le type de dépot est inconnu");
    }


    /**
     * @return bool
     * @throws Exception
     */
    private function glanerFolder(){
        $repertoire = $this->getNextItem();
        if (!$repertoire){
            $this->last_message[] = "Le répertoire est vide";
            return true;
        }
        $result =  $this->glanerRepertoire($repertoire);
        $this->menage([$repertoire]);
        return $result;
    }

    private function menage(array $item_list){
        $directory_send = $this->connecteurConfig->get(self::DIRECTORY_SEND);
        foreach($item_list as $item ) {
            if ($directory_send) {
                $file_deplacement = $directory_send . "/" . basename($item);
                $i = 0;
                while (file_exists($file_deplacement)) {
                    $file_deplacement = $directory_send . "/" . basename($item) . "-$i";
                    $i++;
                }

                rename($item, $file_deplacement);
            } else {
                $tmpFolder = new TmpFolder();
                $tmpFolder->delete($item);
            }
        }
    }

    /**
     * @param $tmp_folder
     * @return bool
     * @throws Exception
     * @throws UnrecoverableException
     */
    private function glanerVrac($tmp_folder){
        $repertoire = $this->connecteurConfig->get(self::DIRECTORY);

        if (! $this->getNextItem()){
            $this->last_message[] = "Le répertoire est vide";
            return true;
        }
        $file_match = $this->getFileMatch($repertoire);
        $menage = array();
        foreach($file_match as $id => $file_list){
            foreach($file_list as $i => $filename){
                copy($repertoire."/$filename",$tmp_folder."/$filename");
                $menage[] = $repertoire."/$filename";
            }
        }
        $result = $this->glanerRepertoire($tmp_folder);
        $this->menage($menage);
        return $result;
    }

    /**
     * @param $tmp_folder
     * @return bool
     * @throws Exception
     */
    public function glanerZip($tmp_folder){
        $zip_file = $this->getNextItem();
        if (!$zip_file){
            $this->last_message[] = "Le répertoire est vide";
            return true;
        }

        $zip = new ZipArchive();
        $handle = $zip->open($zip_file);
        if ($handle !== true){
            throw new Exception("Impossible d'ouvrir le fichier zip");
        }
        $zip->extractTo($tmp_folder);
        $zip->close();

        $result = $this->glanerRepertoire($tmp_folder);

        $this->menage([$zip_file]);
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
        if ($this->connecteurConfig->get(self::MANIFEST_TYPE) == self::MANIFEST_TYPE_XML) {
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
        $file_match = $this->getFileMatch($repertoire);
        $glaneurLocalDocumentInfo = new GlaneurLocalDocumentInfo($this->getConnecteurInfo()['id_e']);
        $glaneurLocalDocumentInfo->nom_flux = $this->connecteurConfig->get(self::FLUX_NAME);
        $glaneurLocalDocumentInfo->element_files_association = $file_match;
        $glaneurLocalDocumentInfo->metadata = $this->getMetadataStatic($file_match);
        $glaneurLocalDocumentInfo->action_ok = $this->connecteurConfig->get(self::ACTION_OK);
        $glaneurLocalDocumentInfo->action_ko = $this->connecteurConfig->get(self::ACTION_KO);
        return $glaneurLocalDocumentInfo;
    }

    /**
     * @param $repertoire
     * @return array
     * @throws Exception
     * @throws UnrecoverableException
     */
    private function getFileMatch($repertoire){
        $nom_flux = $this->connecteurConfig->get(self::FLUX_NAME);
        if (!$nom_flux){
            throw new UnrecoverableException("Impossible de trouver le nom du flux à créer");
        }

        $glaneurLocalFilenameMatcher = new GlaneurLocalFilenameMatcher();

        return $glaneurLocalFilenameMatcher->getFilenameMatching(
            $this->connecteurConfig->get(self::FILE_PREG_MATCH),
            $this->getCardinalite($nom_flux),
            $this->getFileList($repertoire)
        );
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
     * @return  GlaneurLocalDocumentInfo
     * @param $repertoire
     * @throws Exception
     */
    private function glanerModeManifest($repertoire) {

        $glaneurLocalDocumentInfo = new GlaneurLocalDocumentInfo($this->getConnecteurInfo()['id_e']);


        $glaneurLocalDocumentInfo->action_ok = $this->connecteurConfig->get(self::ACTION_OK);
        $glaneurLocalDocumentInfo->action_ko = $this->connecteurConfig->get(self::ACTION_KO);

        $manifest_filename = $this->connecteurConfig->get(self::MANIFEST_FILENAME)?:self::MANIFEST_FILENAME_DEFAULT;

        if (! file_exists($repertoire."/".$manifest_filename)){
            throw new Exception("Le fichier $manifest_filename n'existe pas");
        }

        $simpleXMLWrapper = new SimpleXMLWrapper();

        $xml = $simpleXMLWrapper->loadFile($repertoire."/".$manifest_filename);

        if (empty($xml->attributes()->type)) {
            throw new Exception("Le type de flux n'a pas été trouvé dans le manifest");
        }

        $glaneurLocalDocumentInfo->nom_flux = strval($xml->attributes()->type);
        foreach($xml->data as $data){
            $name = strval($data['name']);
            $value = strval($data['value']);
            $glaneurLocalDocumentInfo->metadata[$name] = $value;
        }

        foreach($xml->files as $files){
            $name = strval($files['name']);
            foreach($files->file as  $file){
                $filename = strval($file['content']);
                if (! file_exists($repertoire."/".$filename)){
                    throw new Exception("Le fichier $filename n'a pas été trouvé.");
                }
                $glaneurLocalDocumentInfo->element_files_association[$name][] = $filename;
            }
        }

        return $glaneurLocalDocumentInfo;
    }


}