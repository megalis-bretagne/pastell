<?php

class DossierMarcheFileArchiveContent
{
    private $tmp_folder;

    /**
     * DossierMarcheFileArchiveContent constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $tmpFolder = new TmpFolder();
        $this->tmp_folder = $tmpFolder->create();
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

        $zip = new ZipArchive();
        $handle = $zip->open($zip_file);
        if (!$handle) {
            throw new UnrecoverableException("Impossible d'ouvrir le fichier zip");
        }

        $zip->extractTo($this->tmp_folder);
        $zip->close();

        $archive_list = $this->getDirectoryList($this->tmp_folder);


        $folder_name = array_pop($archive_list);

        if ($archive_list) {
            throw new UnrecoverableException("Le fichier zip contient plusieurs éléments racine");
        }

        if (! $folder_name || ! is_dir($this->tmp_folder . "/" . $folder_name)) {
            throw new UnrecoverableException("Le fichier zip ne contient pas de sous-répertoire");
        }

        if (! preg_match("#^([^_]*)_#", $folder_name, $matches)) {
            throw new UnrecoverableException("Le nom du répertoire $folder_name ne correspond pas à un ce qui est attendu");
        }

        $result['folder_name'] = $folder_name;
        $result['numero_marche'] = $matches[1];


        return $result;
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
        $result = array_diff($directory_listing, ['.','..','__MACOSX','.DS_Store']);
        return array_values($result);
    }
}
