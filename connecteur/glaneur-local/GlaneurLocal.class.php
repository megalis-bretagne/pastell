<?php

require_once __DIR__."/lib/GlaneurLocalDocumentInfo.class.php";
require_once __DIR__."/lib/GlaneurLocalDocumentCreator.class.php";
require_once __DIR__."/lib/GlaneurLocalFilenameMatcher.class.php";

class GlaneurLocal extends Connecteur {

    const TRAITEMENT_ACTIF = 'traitement_actif';

    const DIRECTORY = 'directory';
    const DIRECTORY_SEND = 'directory_send';
    const DIRECTORY_ERROR = 'directory_error';

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

    /* Pour tester */
    const FICHER_EXEMPLE = 'fichier_exemple';


    /** @var  DonneesFormulaire */
    private $connecteurConfig;

    private $last_message;

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

    public function getDirectory() {
        return $this->connecteurConfig->get(self::DIRECTORY);
    }

    public function getDirectorySend() {
        return $this->connecteurConfig->get(self::DIRECTORY_SEND);
    }

    public function getDirectoryError(){
    	return $this->connecteurConfig->get(self::DIRECTORY_ERROR);
	}

	/**
	 * @param $directory
	 * @return array
	 * @throws Exception
	 */
    public function listFile($directory) {
        if (! $directory){
            throw new Exception("Le nom du répertoire est vide");
        }
        $scan = @ scandir($directory);
        if (! $scan) {
            throw new Exception($directory." n'a pas été scanné");
        }
        return $scan;
    }

	/**
	 * @return int $id_d : identifiant du document créé
	 * @throws Exception
	 */
    public function glaner(){
        if (!$this->connecteurConfig->get(self::TRAITEMENT_ACTIF)){
            $this->last_message[] = "Le traitement du glaneur est désactivé";
            return false;
        }
        $tmpFolder = new TmpFolder();
        $tmp_folder = $tmpFolder->create();
        try {
            $id_d = $this->glanerThrow($this->getDirectory(),$this->getDirectorySend(),$tmp_folder);
        } catch(Exception $e){
            $tmpFolder->delete($tmp_folder);
            throw $e;
        }
        $tmpFolder->delete($tmp_folder);
        return $id_d;
    }

	/**
	 * @return bool
	 * @throws Exception
	 */
    public function glanerFicExemple(){
        $tmpFolder = new TmpFolder();
        $tmp_folder = $tmpFolder->create();

        $directory = $tmpFolder->create();
        $directory_send = "";
        $fichier_exemple_path = $this->connecteurConfig->getFilePath(self::FICHER_EXEMPLE);
        $fichier_exemple_name = $this->connecteurConfig->getFileName(self::FICHER_EXEMPLE);

        if (! $fichier_exemple_name){
            $this->last_message[] = "Il n'y a pas de fichier exemple";
            return false;
        }
        if (! copy($fichier_exemple_path, $directory.'/'.$fichier_exemple_name)) {
            $this->last_message[] = $fichier_exemple_name." n'a pas été récupéré";
            return false;
        }

        try {
            $id_d = $this->glanerThrow($directory,$directory_send,$tmp_folder);
        } catch(Exception $e){
            $tmpFolder->delete($tmp_folder);
            throw $e;
        }
        $tmpFolder->delete($tmp_folder);
        return $id_d;
    }

	/**
	 * @param $directory
	 * @param $directory_send
	 * @param string $tmp_folder
	 * @return bool
	 * @throws UnrecoverableException
	 * @throws Exception
	 */
    private function glanerThrow($directory,$directory_send,string $tmp_folder){
        $type_depot = $this->connecteurConfig->get(self::TYPE_DEPOT);

        if ($type_depot == self::TYPE_DEPOT_VRAC){
            return $this->glanerVrac($directory,$directory_send,$tmp_folder);
        }

        if ($type_depot == self::TYPE_DEPOT_FOLDER){
            return $this->glanerFolder($directory,$directory_send,$tmp_folder);
        }

        if ($type_depot == self::TYPE_DEPOT_ZIP){
            return $this->glanerZip($directory,$directory_send,$tmp_folder);
        }
        throw new UnrecoverableException("Le type de dépot est inconnu");
    }

	/**
	 * @param $directory
	 * @param $directory_send
	 * @param $tmp_folder
	 * @return bool
	 * @throws UnrecoverableException
	 * @throws Exception
	 */
    private function glanerFolder($directory,$directory_send,$tmp_folder){

        $current = $this->getNextItem($directory);

        if (!$current){
            $this->last_message[] = "Le répertoire est vide";
            return true;
        }

		$this->getLogger()->debug("Glanage de $current");
        $directory = $directory.'/'.$current;
        if (!is_dir($directory)) {
            $this->last_message[] = $directory." n'est pas un répertoire";
            $this->moveToErrorDirectory($directory);
            return false;
        }
        if ($directory_send) {
            $directory_send = $directory_send.'/'.$current;
        }

        $repertoire = $directory;
        if (! $this->getNextItem($directory)){
            $this->last_message[] = "Le répertoire est vide";
            return false;
        }
        $directory_listing = $this->listFile($repertoire);
        $menage = array();
        foreach($directory_listing as $filename){
            if (in_array($filename, array('.','..'))){
                continue;
            }
            if (! copy($repertoire."/$filename",$tmp_folder."/$filename")){
                throw new UnrecoverableException(
                	"La copie de ".$repertoire."/$filename"." vers ".$tmp_folder."/$filename"." n'a pas été possible"
				);
            }
            $menage[] = $repertoire."/$filename";
        }
        $id_d = $this->glanerRepertoire($tmp_folder);
        //TODO : si erreur déplacer le repertoire dans erreur
        if ($id_d) {
            $this->menage($directory_send,$menage);
            rmdir($directory);
        }
        return $id_d;
    }

    private function moveToErrorDirectory($file_or_folder){
		//TODO
	}


	/**
	 * @param $directory_send
	 * @param array $item_list
	 * @throws UnrecoverableException
	 */
    private function menage($directory_send,array $item_list){
        foreach($item_list as $item ) {
            if ($directory_send) {
                if (!file_exists($directory_send)) {
                    mkdir($directory_send);
                }
                $file_deplacement = $directory_send . "/" . basename($item);
                $i = 0;
                while (file_exists($file_deplacement)) {
                    $file_deplacement = $directory_send . "/" . basename($item) . "-$i";
                    $i++;
                }
                if (! rename($item, $file_deplacement)){
                    throw new UnrecoverableException("Le déplacement de ".$item." vers ".$file_deplacement." n'a pas été possible");
                }
            } else {
                if (! unlink($item)) {
                    throw new UnrecoverableException("La suppression de ".$item." n'a pas été possible");
                }
            }
        }
    }

	/**
	 * @param $directory
	 * @param $directory_send
	 * @param $tmp_folder
	 * @return bool
	 * @throws UnrecoverableException
	 * @throws Exception
	 */
    private function glanerVrac($directory,$directory_send,$tmp_folder){
        $repertoire = $directory;
        if (! $this->getNextItem($directory)){
            $this->last_message[] = "Le répertoire est vide";
            return true;
        }
        $file_match = $this->getFileMatch($repertoire);
        $menage = array();
        foreach($file_match['file_match'] as $id => $file_list){
            foreach($file_list as $i => $filename){
                if (! copy($repertoire."/$filename",$tmp_folder."/$filename")){
                    throw new UnrecoverableException("La copie de ".$repertoire."/$filename"." vers ".$tmp_folder."/$filename"." n'a pas été possible");
                }
                $menage[] = $repertoire."/$filename";
            }
        }
        $id_d = $this->glanerRepertoire($tmp_folder);
        //TODO Si erreur déplacer dans erreur...
        $this->menage($directory_send,$menage);
        return $id_d;
    }

    /**
	 * @param $directory
	 * @param $directory_send
     * @param $tmp_folder
     * @return bool
     * @throws Exception
     */
    public function glanerZip($directory,$directory_send,$tmp_folder){
        $current = $this->getNextItem($directory);
        if (!$current){
            $this->last_message[] = "Le répertoire est vide";
            return true;
        }
        $zip_file = $directory.'/'.$current;
        $zip = new ZipArchive();
        $handle = $zip->open($zip_file);
        if ($handle !== true){
			//TODO : si erreur, alors copier dans error
            throw new Exception("Impossible d'ouvrir le fichier zip");
        }
        $zip->extractTo($tmp_folder);
        $zip->close();

        $id_d = $this->glanerRepertoire($tmp_folder);

        //TODO : si erreur, alors copier dans error

        $this->menage($directory_send,[$zip_file]);
        return $id_d;
    }


    private function getNextItem($directory){
        $directoryIterator = new DirectoryIterator($directory);
        do {
            $current = $directoryIterator->current()->getFilename();
            $directoryIterator->next();
        } while (in_array($current,array('.','..')));
        if (!$current) {
            return false;
        }
        return $current;
    }


	/**
	 * @param string $repertoire
	 * @return int $id_d
	 * @throws Exception
	 */
    private function glanerRepertoire(string $repertoire){
        if (!$repertoire){
            $this->last_message[] = "Le répertoire ".$repertoire." est vide";
            return false;
        }
        // Le mode manifeste à précédence sur le mode filename_matcher
        if ($this->connecteurConfig->get(self::MANIFEST_TYPE) == self::MANIFEST_TYPE_XML) {
            $glaneurLocalDocumentInfo = $this->glanerModeManifest($repertoire);
        } else {
            $glaneurLocalDocumentInfo = $this->glanerModeFilematcher($repertoire);
        }
        return $this->createDocument($glaneurLocalDocumentInfo,$repertoire);
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
        $glaneurLocalDocumentInfo->element_files_association = $file_match['file_match'];
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
                if (empty($file_match['file_match'][$matches[1]][0])){
                    throw new Exception("$matches[1] n'a pas été trouvé dans la correspondance des fichiers");
                }
                $value = $file_match['file_match'][$matches[1]][0];
            }
            else {
                $matches = $file_match['matches'];
                $value = preg_replace_callback(
                    '#\$matches\[(\d+)\]\[(\d+)\]#',
                    function ($m) use ($matches){
                        if (empty($matches[$m[1]][$m[2]])){
                            return false;
                        }
                        return $matches[$m[1]][$m[2]];
                    },
                    $value
                );
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
                    $cardinalite[$field->getName()] = $field->getProperties('multiple')?'n':'1';
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
        sort($result);
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

        if (empty($xml->attributes()->{'type'})) {
            throw new Exception("Le type de flux n'a pas été trouvé dans le manifest");
        }

        $glaneurLocalDocumentInfo->nom_flux = strval($xml->attributes()->{'type'});
        foreach($xml->{'data'} as $data){
            $name = strval($data['name']);
            $value = strval($data['value']);
            $glaneurLocalDocumentInfo->metadata[$name] = $value;
        }

        foreach($xml->{'files'} as $files){
            $name = strval($files['name']);
            foreach($files->{'file'} as  $file){
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