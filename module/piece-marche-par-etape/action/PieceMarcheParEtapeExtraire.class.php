<?php

require_once(PASTELL_PATH . "/action/CommonExtractionAction.class.php");
require_once __DIR__ . "/../lib/PieceMarcheParEtapeFichierArchiveExtraire.class.php";

class PieceMarcheParEtapeExtraire extends CommonExtractionAction
{
    /**
     * @param string $tmp_folder
     * @throws Exception
     * @throws UnrecoverableException
     */
    public function extract(string $tmp_folder)
    {
        $fichier_zip = $this->getDonneesFormulaire()->getFilePath('zip_etape');

        $this->getDonneesFormulaire()->deleteField('piece');
        $this->getDonneesFormulaire()->deleteField('type_piece');
        $this->getDonneesFormulaire()->deleteField('type_pj');

        if (!$this->getDonneesFormulaire()->getFileContent('zip_etape')) {
            throw new Exception("Il n'y a pas de fichier zip");
        }
        $FichierArchiveExtraire = new PieceMarcheParEtapeFichierArchiveExtraire();

        copy($fichier_zip, $tmp_folder . "/archive.zip");

        $tmp_folder = $FichierArchiveExtraire->extract($tmp_folder . "/archive.zip");

        $file_num = 0;
        $file_list = scandir($tmp_folder);
        foreach ($file_list as $file_result) {
            $file_result_path = $tmp_folder . "/" . $file_result;
            if (is_file($file_result_path)) {
                $this->getDonneesFormulaire()->addFileFromCopy("piece", $file_result, $file_result_path, $file_num);
                $file_num++;
            }
        }
    }
}
