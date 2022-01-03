<?php

class DonneesFormulaireTarBall
{
    private $tmpFolder;

    public function __construct(TmpFolder $tmpFolder)
    {
        $this->tmpFolder = $tmpFolder;
    }

    /**
     * Extrait le contenu d'un fichier tgz dans un autre élements de type file multiple
     */
    public function extract(DonneesFormulaire $donneesFormulaire, $element_in, $element_out)
    {
        $file_path = $donneesFormulaire->getFilePath($element_in);
        $folder_name = $this->tmpFolder->create();

        $temp_file_path = $folder_name . "/fichier.tar.gz";
        copy($file_path, $temp_file_path);

        $result_folder = $folder_name . "/result/";
        mkdir($result_folder);

        $this->extractTarBall($temp_file_path, $result_folder);

        $file_list = scandir($result_folder);
        $num_file = 0;
        foreach ($file_list as $file_result) {
            $file_result_path = $result_folder . "/" . $file_result;
            if (is_file($file_result_path)) {
                $donneesFormulaire->addFileFromCopy($element_out, $file_result, $file_result_path, $num_file++);
            }
        }
        $this->tmpFolder->delete($folder_name);
    }


    private function extractTarBall($file_path, $directory)
    {
        $zipArchive = new PharData($file_path);
        $zipArchive->extractTo($directory);
    }
}
