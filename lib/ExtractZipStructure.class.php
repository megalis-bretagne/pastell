<?php

/**
 * Class ExtractZipStructure
 *
 * Extrait une archive zip et extrait les informations relative au fichier et répertoire afin d'être utilisé directement
 * par une  classe FluxDataSedaDefault
 *
 * L'archive est extraite dans un répertoire temporaire qui est détruit avec l'objet
 * Le tableau résultat :
 *
 * - root_directory : le répertoire à la racine du zip
 * - tmp_folder : le répertoire temporaire contenant le dézippage de l'archive
 * - folder : la liste des contenu de répertoire (répertoire) en profondeur d'abord
 * - folder_name : liste des noms de répertoire en profondeur d'abord
 * - document : la liste des contenu de répertoire (document) en largeur d'abord
 * - file_list : la liste des fichier en largeur d'abord
 *
 * La complexité est du au fait que dans le SEDA : on liste d'abord tous les repertoire, puis on liste les documents
 *
 */
class ExtractZipStructure
{
    public const MAX_RECURSION_LEVEL = 20;

    /**
     * Fichier qui seront exclus des répertoires à archiver
     * @return array
     */
    private function exludeFileList(): array
    {
        return ['.','..','__MACOSX','.DS_Store','.gitkeep'];
    }


    private $nb_recursion_level_stop;

    private $tmp_folder;


    /**
     * CD31FileArchiveContent constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $tmpFolder = new TmpFolder();
        $this->tmp_folder = $tmpFolder->create();
    }

    public function setNbRecusionLevelStop($nb_recusion_level_stop)
    {
        $this->nb_recursion_level_stop = $nb_recusion_level_stop;
    }

    public function __destruct()
    {
        $tmpFolder = new TmpFolder();
        $tmpFolder->delete($this->tmp_folder);
    }

    /**
     * @param $zip_file
     * @return array
     * @throws Exception
     * @throws UnrecoverableException
     */
    public function extract($zip_file)
    {
        $this->unzipArchive($zip_file, $this->tmp_folder);
        $result['tmp_folder'] = $this->tmp_folder;
        $result['root_directory'] = $this->getRootDirectory($this->tmp_folder);
        $this->extractInfoFromFolder(
            $this->tmp_folder . "/" . $result['root_directory'],
            "",
            $result
        );
        return $result;
    }

    /**
     * @param $zip_file
     * @param $target_folder
     * @throws UnrecoverableException
     */
    private function unzipArchive($zip_file, $target_folder)
    {
        $zip = new ZipArchive();
        $handle = $zip->open($zip_file);
        if (!$handle) {
            throw new UnrecoverableException("Impossible d'ouvrir le fichier zip");
        }
        $zip->extractTo($target_folder);
        $zip->close();
    }

    /**
     * @param $folder
     * @return mixed
     * @throws UnrecoverableException
     */
    private function getRootDirectory($folder)
    {
        $archive_list = $this->getDirectoryList($folder);
        if (count($archive_list) != 1) {
            throw new UnrecoverableException(
                "Le fichier zip ne doit comporter qu'un seul répertoire racine" .
                implode(",", $archive_list)
            );
        }
        return $archive_list[0];
    }


    /**
     * La fonction un peu tricky...
     * @param $directory_path
     * @param $relative_dir_path
     * @param $result
     * @param $recursion_level
     * @throws UnrecoverableException
     */
    private function extractInfoFromFolder($directory_path, $relative_dir_path, &$result, $recursion_level = 0)
    {

        if ($recursion_level >= self::MAX_RECURSION_LEVEL) {
            throw new UnrecoverableException("Il y a plus de " . self::MAX_RECURSION_LEVEL . " sous-niveaux de répertoire, impossible de générer le bordereau");
        }

        if ($this->nb_recursion_level_stop && $recursion_level >= $this->nb_recursion_level_stop) {
            $result['folder'][] = [];
            $result['file'][] = [];
            //Ajouter à document et file_list
            $this->extractFileList($directory_path, $relative_dir_path, $result);
            return;
        }

        $directory_list = $this->getDirectoryList($directory_path);
        $folder_to_analyse = [];
        $file_list = [];
        foreach ($directory_list as $element) {
            if (is_file($directory_path . "/" . $element)) {
                $file_list[] = $relative_dir_path . "/" . $element;
            } elseif (is_dir($directory_path . "/" . $element)) {
                $folder_to_analyse[] = $element;
            }
        }
        $result['folder'][] = $folder_to_analyse;

        $recursion_level++;
        foreach ($folder_to_analyse as $element) {
            $result['folder_name'][] = $element;
            $this->extractInfoFromFolder(
                $directory_path . "/" . $element,
                $relative_dir_path . "/" . $element,
                $result,
                $recursion_level
            );
        }

        $result['file'][] = $file_list;

        foreach ($file_list as $element) {
            $result['file_list'][] = trim($element, "/");
        }
    }

    /**
     * @param $directory_path
     * @return array
     * @throws UnrecoverableException
     */
    private function getDirectoryList($directory_path)
    {
        $directory_listing = scandir($directory_path);
        if (! $directory_listing) {
            throw new UnrecoverableException("Impossible de lister le contenu de $directory_path");
        }
        $result = array_diff($directory_listing, $this->exludeFileList());
        return array_values($result);
    }


    /**
     * @param $directory_path
     * @param $relative_dir_path
     * @param $result
     * @throws UnrecoverableException
     */
    private function extractFileList($directory_path, $relative_dir_path, &$result)
    {

        $directory_list = $this->getDirectoryList($directory_path);
        $folder_to_analyse = [];
        $file_list = [];
        foreach ($directory_list as $element) {
            if (is_file($directory_path . "/" . $element)) {
                $file_list[] = $relative_dir_path . "/" . $element;
            } elseif (is_dir($directory_path . "/" . $element)) {
                $folder_to_analyse[] = $element;
            }
        }

        foreach ($folder_to_analyse as $element) {
            $this->extractFileList($directory_path . "/" . $element, $relative_dir_path . "/" . $element, $result);
        }

        $result['file'][count($result['file']) - 1] = array_merge($result['file'][count($result['file']) - 1], $file_list);

        foreach ($file_list as $element) {
            $result['file_list'][] = trim($element, "/");
        }
    }
}
