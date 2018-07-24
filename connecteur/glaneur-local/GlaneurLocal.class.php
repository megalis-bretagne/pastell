<?php

require_once __DIR__."/lib/GlaneurLocalDocumentInfo.class.php";
require_once __DIR__."/lib/GlaneurLocalDocumentCreator.class.php";
require_once __DIR__."/lib/GlaneurLocalFilenameMatcher.class.php";
require_once __DIR__."/lib/GlaneurLocalGlanerRepertoire.class.php";

class GlaneurLocal extends Connecteur {

	const NB_MAX_FILE_DISPLAY = 20;
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
	 * @return string
	 * @throws Exception
	 */
	public function listDirectories(){

		$directory_to_scan = [
			'directory' => $this->getDirectory(),
			'directory_send' => $this->getDirectoryError(),
			'directory_error' => $this->getDirectorySend(),
		];

		$result = "";

		foreach($directory_to_scan as $libelle => $directory){
			$info = $this->listFile($directory);

			$result .= "*****\n".$libelle." - {$info['count']} fichier(s)/répertoire(s) : \n\n";


			/** @var \Symfony\Component\Finder\SplFileInfo $file */
			foreach($info['iterator'] as $file){
				$result .= $file->getBasename() . " - " . $file->getSize() ." octets  - ".date("Y-m-d H:i:s",$file->getCTime())."\n";
			}
			$result.="\n*********\n\n";

		}

		$result.="Affichage limité au 20 premiers fichiers";

    	return nl2br($result);
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	public function listErrorDirectories(){
		$directory_error = $this->getDirectoryError();
		if ($directory_error) {
			return $this->listFile($this->getDirectoryError());
		}
		return [];
	}

	/**
	 * @param $directory
	 * @return array
	 * @throws Exception
	 */
    private function listFile($directory) {
		$finder = new \Symfony\Component\Finder\Finder();
		$iter = $finder->in($directory);
		$result['count'] = $iter->count();
		$result['iterator'] = new LimitIterator($iter->getIterator(),0,self::NB_MAX_FILE_DISPLAY);
		return $result;
    }

	/**
	 * @return int $id_d : identifiant du document créé
	 * @throws UnrecoverableException
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
			$id_d = $this->glanerThrow($this->getDirectory(), $this->getDirectorySend(), $tmp_folder);
        } catch(Exception $e){
        	//S'il y a une exception qu'on n'a pas prévu, alors, on est obligé de verrouiller le connecteur
            $tmpFolder->delete($tmp_folder);
			throw new UnrecoverableException($e->getMessage(),$e->getCode(),$e);
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
		$filesystem = new \Symfony\Component\Filesystem\Filesystem();
		$filesystem->copy($fichier_exemple_path,$directory.'/'.$fichier_exemple_name);

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
            $this->moveToErrorDirectory([$directory]);
            return false;
        }

        if (! $this->getNextItem($directory)){
			$this->moveToErrorDirectory([$directory]);
            $this->last_message[] = "Le répertoire est vide";
            return false;
        }

		$filesystem = new \Symfony\Component\Filesystem\Filesystem();
        $filesystem->mirror($directory,$tmp_folder);
        $id_d = $this->glanerRepertoire($tmp_folder);

        if ($id_d) {
        	$this->moveToOutputDirectory($directory_send,[$directory]);
        } else {
        	$this->moveToErrorDirectory([$directory]);
		}
        return $id_d;
    }

	/**
	 * @param $file_or_folder
	 * @throws UnrecoverableException
	 */
    private function moveToErrorDirectory($file_or_folder){
		if (! $this->getDirectoryError()){
			throw new UnrecoverableException("Le répertoire d'erreur n'existe pas !");
		}
    	$this->moveToOutputDirectory($this->getDirectoryError(),$file_or_folder);
	}


	/**
	 * @param $directory_send
	 * @param array $item_list
	 */
    private function moveToOutputDirectory($directory_send, array $item_list){
		$filesystem = new \Symfony\Component\Filesystem\Filesystem();
		if (! $directory_send){
			$filesystem->remove($item_list);
			return;
		}

		foreach($item_list as $item) {
			$file_deplacement = $directory_send . "/" . basename($item);
			$i = 0;
			while ($filesystem->exists($file_deplacement)) {
				$file_deplacement = $directory_send . "/" . basename($item) . "-$i";
				$i++;
			}
			$filesystem->rename($item, $file_deplacement);
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
		$filesystem = new \Symfony\Component\Filesystem\Filesystem();

		$repertoire = $directory;
        if (! $this->getNextItem($directory)){
            $this->last_message[] = "Le répertoire est vide";
            return true;
        }
		$glaneurLocalGlanerRepertoire =$this->getGlaneurLocalGlanerRepertoire();
        $file_match = $glaneurLocalGlanerRepertoire->getFileMatch($repertoire);
        $menage = array();
        foreach($file_match['file_match'] as $id => $file_list){
            foreach($file_list as $i => $filename){
				$filesystem->copy($repertoire."/$filename",$tmp_folder."/$filename");
                $menage[] = $repertoire."/$filename";
            }
        }
        $id_d = $this->glanerRepertoire($tmp_folder);
        if ($id_d){
			$this->moveToOutputDirectory($directory_send,$menage);
		} else {
        	$this->moveToErrorDirectory($menage);
		}

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
			$this->moveToErrorDirectory([$zip_file]);

            throw new Exception("Impossible d'ouvrir le fichier zip");
        }
        $zip->extractTo($tmp_folder);
        $zip->close();

        $id_d = $this->glanerRepertoire($tmp_folder);

		if ($id_d) {
			$this->moveToOutputDirectory($directory_send, [$zip_file]);
		} else {
			$this->moveToErrorDirectory([$zip_file]);
		}
        return $id_d;
    }


    private function getNextItem($directory){
		$finder = new \Symfony\Component\Finder\Finder();
		$found = $finder->in($directory);

		/** @var SplFileInfo $file */
		foreach($found as $file){
			return $file->getBasename();
		}
		return false;
    }

	/**
	 * @param $tmp_folder
	 * @return int
	 * @throws Exception
	 */
	private function glanerRepertoire($tmp_folder){
    	$glaneurLocalGlanerRepertoire =$this->getGlaneurLocalGlanerRepertoire();
    	$result = $glaneurLocalGlanerRepertoire->glanerRepertoire($tmp_folder);
    	$this->last_message = $glaneurLocalGlanerRepertoire->getLastMessage();
    	return $result;
	}


	private function getGlaneurLocalGlanerRepertoire(){
		return new GlaneurLocalGlanerRepertoire(
			$this->glaneurLocalDocumentCreator,$this->connecteurConfig,$this->getConnecteurInfo()['id_e'],$this->documentTypeFactory
		);
	}

}