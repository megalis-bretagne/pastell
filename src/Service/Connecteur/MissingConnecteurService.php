<?php

namespace Pastell\Service\Connecteur;

use ConnecteurFactory;
use ConnecteurEntiteSQL;
use ZipArchive;

class MissingConnecteurService
{
    private $connecteurFactory;
    private $connecteurEntiteSQL;
    private $workspace_path;

    public function __construct(
        ConnecteurFactory $connecteurFactory,
        ConnecteurEntiteSQL $connecteurEntiteSQL,
        string $workspacePath
    ) {
        $this->connecteurFactory = $connecteurFactory;
        $this->connecteurEntiteSQL = $connecteurEntiteSQL;
        $this->workspace_path = $workspacePath;
    }

    public function exportAll($zip_filepath)
    {
        $zip = new ZipArchive();
        $zip->open($zip_filepath, ZipArchive::CREATE);
        $connecteur_manquant_list = $this->connecteurFactory->getManquant();

        foreach ($connecteur_manquant_list as $id_connecteur) {
            $id_ce_list = $this->connecteurEntiteSQL->getAllById($id_connecteur);
            foreach ($id_ce_list as $connecteur_info) {
                $id_ce = $connecteur_info['id_ce'];
                $json_content = $this->connecteurFactory->getConnecteurConfig($id_ce)->jsonExport();
                $zip->addFromString("connecteur_{$id_ce}.json", $json_content);
                $all_file = glob($this->workspace_path . "/connecteur_{$id_ce}.yml_*");
                foreach ($all_file as $connecteur_file) {
                    $zip->addFile($connecteur_file, basename($connecteur_file));
                }
            }
        }
        $zip->close();
    }
}
