<?php

class FluxDataTestSedaGenerique extends FluxDataTest
{
    private $file_list = [];
    private $date_list = [];

    public function __construct()
    {
        parent::__construct([]);
    }

    public function addFileList($file_list): void
    {
        $this->file_list = $file_list;
    }

    public function addDateList($date_list): void
    {
        $this->date_list = $date_list;
    }

    public function getData($key)
    {
        if (in_array($key, $this->date_list)) {
            return date("Y-m-d");
        }
        if (in_array($key, $this->file_list)) {
            return ["sera_remplace_par_le_nom_du_fichier_correspondant_a_lelement_{$key}_du_dossier.pdf"];
        }
        return "%%Sera remplacé par le contenu de l'élement $key du dossier%%";
    }

    public function getContentType($key)
    {
        return "application/pdf";
    }

    public function getFileSHA256($key)
    {
        return "1ac8ab33f5a60b4c27c6900abc61ed14fdca17ffb4e4b4bed42060016b73a0f2";
    }
}
