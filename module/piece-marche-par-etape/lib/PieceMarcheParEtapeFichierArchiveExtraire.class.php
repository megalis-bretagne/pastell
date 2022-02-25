<?php

class PieceMarcheParEtapeFichierArchiveExtraire
{
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

    public function __destruct()
    {
        $tmpFolder = new TmpFolder();
        $tmpFolder->delete($this->tmp_folder);
    }

    /**
     * @param $zip_file
     * @return string
     * @throws Exception
     * @throws UnrecoverableException
     */
    public function extract($zip_file)
    {

        $zip = new ZipArchive();
        $handle = $zip->open($zip_file);

        if ($handle !== true) {
            throw new UnrecoverableException("Impossible d'ouvrir le fichier zip - erreur $handle");
        }

        $zip->extractTo($this->tmp_folder);
        $zip->close();

        return $this->tmp_folder;
    }
}
