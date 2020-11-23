<?php

namespace Pastell\Service\Connecteur;

use ConnecteurFactory;
use ZipArchive;

class MissingConnecteurService
{
    private $connecteurFactory;
    private $workspace_path;

    public function __construct(
        ConnecteurFactory $connecteurFactory,
        string $workspacePath
    ) {
        $this->connecteurFactory = $connecteurFactory;
        $this->workspace_path = $workspacePath;
    }

    public function exportAll($zip_filepath)
    {
        $zip = new ZipArchive();
        $zip->open($zip_filepath, ZipArchive::CREATE);
        $all = $this->connecteurFactory->getManquant();

        foreach ($all as $connecteur_manquant_list) {
            foreach ($connecteur_manquant_list as $connecteur_info) {
                $id_ce = $connecteur_info['id_ce'];
                $json_content = $this->connecteurFactory->getConnecteurConfigManquant($id_ce)->jsonExport();
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
