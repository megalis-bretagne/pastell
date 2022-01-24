<?php

abstract class GlaneurConnecteur extends Connecteur
{
    public const NB_MAX_FILE_DISPLAY = 20;
    public const TRAITEMENT_ACTIF = 'traitement_actif';

    public const DIRECTORY = 'directory';
    public const DIRECTORY_SEND = 'directory_send';
    public const DIRECTORY_ERROR = 'directory_error';

    public const TYPE_DEPOT = 'type_depot';
    public const TYPE_DEPOT_ZIP = 'ZIP';
    public const TYPE_DEPOT_FOLDER = 'FOLDER';
    public const TYPE_DEPOT_VRAC = 'VRAC';

    /* Pour le mode manifest */
    public const MANIFEST_TYPE = 'manifest_type';
    public const MANIFEST_FILENAME = 'manifest_filename';
    public const MANIFEST_FILENAME_DEFAULT = 'manifest.xml';
    public const MANIFEST_TYPE_NONE = 'no';
    public const MANIFEST_TYPE_XML = 'xml';

    /* Pour le mode filename_matcher */
    public const FLUX_NAME = 'flux_name';
    public const FILE_PREG_MATCH = 'file_preg_match';
    public const METADATA_STATIC = 'metadata_static';

    public const FORCE_ACTION_OK = 'force_action_ok';
    public const ACTION_OK = 'action_ok';
    public const ACTION_KO = 'action_ko';

    /* Pour tester */
    public const FICHER_EXEMPLE = 'fichier_exemple';


    /** @var  DonneesFormulaire */
    protected $connecteurConfig;

    private $last_message;

    /** @var GlaneurDocumentCreator  */
    private $glaneurLocalDocumentCreator;

    /** @var DocumentTypeFactory */
    private $documentTypeFactory;

    /**
     * Permet de lister les fichiers d'un repertoire pour le test
     * @param string $directory
     * @return array count->nombre de fichier iterator->un itérateur sur les NB_MAX_FILE_DISPLAY premier fichier
     */
    abstract protected function listFile(string $directory): array;

    /**
     * Retourne la liste de tous les fichiers.
     * @param string $directory
     * @return array
     */
    abstract protected function listAllFile(string $directory): array;

    /**
     * Permet de scanner le prochain objet d'un repertoire
     * @param string $directory
     * @return string $file_or_directory (chemin relatif à partir de $directory)
     */
    abstract protected function getNextItem(string $directory): string;

    /**
     * Indique si un élement est ou non un repertoire
     * @param string $directory_or_file
     * @return bool
     */
    abstract protected function isDir(string $directory_or_file): bool;


    /**
     * Permet de copier le repertoire distant vers le repertoire temporaire local
     * @param string $directory
     * @param string $tmp_folder
     */
    abstract protected function mirror(string $directory, string $tmp_folder);

    /**
     * Supprime une liste de fichier
     * @param array $item_list
     * @return mixed
     */
    abstract protected function remove(array $item_list);

    /**
     * Indique si un fichier ou un repertoire existe sur le système distant
     * @param string $file_or_directory
     * @return bool
     */
    abstract protected function exists(string $file_or_directory): bool;

    /**
     * Déplace un fichier sur le système distant
     * @param string $item
     * @param string $file_deplacement
     */
    abstract protected function rename(string $item, string $file_deplacement);

    /**
     * Copie un fichier depuis le système distant vers le système local
     * @param string $originFile
     * @param string $targetFile
     * @return mixed
     */
    abstract protected function copy(string $originFile, string $targetFileOnLocal);

    public function __construct(
        DocumentTypeFactory $documentTypeFactory,
        GlaneurDocumentCreator $glaneurLocalDocumentCreator
    ) {
        $this->documentTypeFactory = $documentTypeFactory;
        $this->glaneurLocalDocumentCreator = $glaneurLocalDocumentCreator;
    }

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire)
    {
        $this->connecteurConfig = $donneesFormulaire;
    }

    public function getLastMessage()
    {
        return $this->last_message;
    }

    public function getDirectory()
    {
        return $this->connecteurConfig->get(self::DIRECTORY);
    }

    public function getDirectorySend()
    {
        return $this->connecteurConfig->get(self::DIRECTORY_SEND);
    }

    public function getDirectoryError()
    {
        return $this->connecteurConfig->get(self::DIRECTORY_ERROR);
    }


    /**
     * @return string
     * @throws Exception
     */
    public function listDirectories()
    {

        $directory_to_scan = [
            'directory' => $this->getDirectory(),
            'directory_send' => $this->getDirectorySend(),
            'directory_error' => $this->getDirectoryError(),
        ];

        $result = "";

        foreach ($directory_to_scan as $libelle => $directory) {
            $info = $this->listFile($directory);

            $result .= "*****\n" . $libelle . " - {$info['count']} fichier(s)/répertoire(s) : \n\n";

            $result .=  $info['detail'];

            $result .= "\n*********\n\n";
        }

        $result .= "Affichage limité au 20 premiers fichiers";

        return nl2br($result);
    }

    /**
     * @return int
     * @throws Exception
     */
    public function countErrorDirectories()
    {
        $directory_error = $this->getDirectoryError();
        if ($directory_error) {
            $info = $this->listFile($this->getDirectoryError());
            return $info['count'];
        }
        return 0;
    }



    /**
     * @return int $id_d : identifiant du document créé
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function glaner()
    {
        if (!$this->connecteurConfig->get(self::TRAITEMENT_ACTIF)) {
            $this->last_message[] = "Le traitement du glaneur est désactivé";
            return false;
        }
        $tmpFolder = new TmpFolder();
        $tmp_folder = $tmpFolder->create();
        try {
            $id_d = $this->glanerThrow($this->getDirectory(), $this->getDirectorySend(), $tmp_folder);
        } catch (Exception $e) {
            //S'il y a une exception qu'on n'a pas prévu, alors, on est obligé de verrouiller le connecteur
            throw new UnrecoverableException($e->getMessage(), $e->getCode(), $e);
        } finally {
            $tmpFolder->delete($tmp_folder);
        }

        return $id_d;
    }

    /**
     * @return bool|string
     * @throws Exception
     */
    public function glanerFicExemple()
    {
        $tmpFolder = new TmpFolder();
        $directory = $tmpFolder->create();
        $fichier_exemple_path = $this->connecteurConfig->getFilePath(self::FICHER_EXEMPLE);
        $fichier_exemple_name = $this->connecteurConfig->getFileName(self::FICHER_EXEMPLE);

        if (! $fichier_exemple_name) {
            $this->last_message[] = "Il n'y a pas de fichier exemple";
            return false;
        }
        if ($this->connecteurConfig->getContentType(self::FICHER_EXEMPLE) !== 'application/zip') {
            $this->last_message[] = "Le fichier d'exemple n'est pas un fichier ZIP";
            return false;
        }

        $zip = new ZipArchive();
        $handle = $zip->open($fichier_exemple_path);
        if ($handle !== true) {
            throw new Exception("Impossible d'ouvrir le fichier zip");
        }
        $zip->extractTo($directory);
        $zip->close();

        try {
            $id_d = $this->glanerRepertoire($directory);
        } finally {
            $tmpFolder->delete($directory);
        }

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
    private function glanerThrow($directory, $directory_send, string $tmp_folder)
    {
        $type_depot = $this->connecteurConfig->get(self::TYPE_DEPOT);

        if ($type_depot == self::TYPE_DEPOT_VRAC) {
            return $this->glanerVrac($directory, $directory_send, $tmp_folder);
        }

        if ($type_depot == self::TYPE_DEPOT_FOLDER) {
            return $this->glanerFolder($directory, $directory_send, $tmp_folder);
        }

        if ($type_depot == self::TYPE_DEPOT_ZIP) {
            return $this->glanerZip($directory, $directory_send, $tmp_folder);
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
    private function glanerFolder($directory, $directory_send, $tmp_folder)
    {

        $current = $this->getNextItem($directory);

        if (!$current) {
            $this->last_message[] = "Le répertoire est vide";
            return true;
        }

        $this->getLogger()->debug("Glanage de $current");
        $directory = $directory . '/' . $current;
        if (!$this->isDir($directory)) {
            $this->last_message[] = $directory . " n'est pas un répertoire";
            $this->moveToErrorDirectory([$directory]);
            return false;
        }

        if (! $this->getNextItem($directory)) {
            $this->moveToErrorDirectory([$directory]);
            $this->last_message[] = "Le répertoire est vide";
            return false;
        }

        $this->mirror($directory, $tmp_folder);
        $id_d = $this->glanerRepertoire($tmp_folder);

        if ($id_d) {
            $this->moveToOutputDirectory($directory_send, [$directory]);
        } else {
            $this->moveToErrorDirectory([$directory]);
        }
        return $id_d;
    }

    /**
     * @param $file_or_folder
     * @throws UnrecoverableException
     */
    private function moveToErrorDirectory($file_or_folder)
    {
        if (! $this->getDirectoryError()) {
            throw new UnrecoverableException("Le répertoire d'erreur n'existe pas !");
        }
        $this->moveToOutputDirectory($this->getDirectoryError(), $file_or_folder);
    }


    /**
     * @param $directory_send
     * @param array $item_list
     */
    private function moveToOutputDirectory($directory_send, array $item_list)
    {
        if (! $directory_send) {
            $this->remove($item_list);
            return;
        }

        foreach ($item_list as $item) {
            $file_deplacement = $directory_send . "/" . basename($item);
            $i = 0;
            while ($this->exists($file_deplacement)) {
                $file_deplacement = $directory_send . "/" . basename($item) . "-$i";
                $i++;
            }

            $this->rename($item, $file_deplacement);
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
    private function glanerVrac($directory, $directory_send, $tmp_folder)
    {

        $repertoire = $directory;
        if (! $this->getNextItem($directory)) {
            $this->last_message[] = "Le répertoire est vide";
            return true;
        }
        $glaneurLocalGlanerRepertoire = $this->getGlaneurGlanerRepertoire();

        $file_match = $glaneurLocalGlanerRepertoire->getFileMatch($repertoire, $this->listAllFile($repertoire));
        $menage = array();
        foreach ($file_match['file_match'] as $id => $file_list) {
            foreach ($file_list as $i => $filename) {
                $this->copy($repertoire . "/$filename", $tmp_folder . "/$filename");
                $menage[] = $repertoire . "/$filename";
            }
        }
        $id_d = $this->glanerRepertoire($tmp_folder);
        if ($id_d) {
            $this->moveToOutputDirectory($directory_send, $menage);
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
    public function glanerZip($directory, $directory_send, $tmp_folder)
    {
        $current = $this->getNextItem($directory);
        if (!$current) {
            $this->last_message[] = "Le répertoire est vide";
            return true;
        }
        $this->copy($directory . "/" . $current, $tmp_folder . "/" . $current);
        $zip_to_remove = $directory . "/" . $current;
        $zip_file = $tmp_folder . '/' . $current;
        $zip = new ZipArchive();
        $handle = $zip->open($zip_file);
        if ($handle !== true) {
            $this->moveToErrorDirectory([$zip_file]);

            throw new Exception("Impossible d'ouvrir le fichier zip");
        }
        $zip->extractTo($tmp_folder);
        $zip->close();

        $filesystem = new \Symfony\Component\Filesystem\Filesystem();
        $filesystem->remove($tmp_folder . "/" . $current);

        $id_d = $this->glanerRepertoire($tmp_folder);

        if ($id_d) {
            $this->moveToOutputDirectory($directory_send, [$zip_to_remove]);
        } else {
            $this->moveToErrorDirectory([$zip_to_remove]);
        }
        return $id_d;
    }


    /**
     * @param $tmp_folder
     * @return bool|string
     * @throws Exception
     */
    private function glanerRepertoire($tmp_folder)
    {
        $glaneurLocalGlanerRepertoire = $this->getGlaneurGlanerRepertoire();
        $result = $glaneurLocalGlanerRepertoire->glanerRepertoire($tmp_folder);
        $this->last_message = $glaneurLocalGlanerRepertoire->getLastMessage();
        return $result;
    }


    private function getGlaneurGlanerRepertoire()
    {
        return new GlaneurGlanerRepertoire(
            $this->glaneurLocalDocumentCreator,
            $this->connecteurConfig,
            $this->getConnecteurInfo()['id_e'],
            $this->documentTypeFactory
        );
    }
}
