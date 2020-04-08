<?php

namespace Pastell\Service\Connecteur;

use ConnecteurEntiteSQL;
use ConnecteurFactory;
use ZipArchive;

class MissingConnecteurService
{
    private $connecteurFactory;
    private $connecteurEntiteSQL;

    public function __construct(ConnecteurFactory $connecteurFactory, ConnecteurEntiteSQL $connecteurEntiteSQL)
    {
        $this->connecteurFactory = $connecteurFactory;
        $this->connecteurEntiteSQL = $connecteurEntiteSQL;
    }

    public function listAll(): array
    {
        $connecteur_manquant = $this->connecteurFactory->getManquant();

        $connecteur_manquant_list = [];
        foreach ($connecteur_manquant as $connecteur_id) {
            $connecteur_manquant_list[$connecteur_id] = $this->connecteurEntiteSQL->getAllById($connecteur_id);
        }
        return $connecteur_manquant_list;
    }

    public function exportAll($zip_filepath)
    {
        $zip = new ZipArchive();
        $zip->open($zip_filepath, ZipArchive::CREATE);
        $all = $this->listAll();
        foreach ($all as $connecteur_manquant_list) {
            foreach ($connecteur_manquant_list as $connecteur_info) {
                $json_content = $this->connecteurFactory->getConnecteurConfig($connecteur_info['id_ce'])->jsonExport();
                $zip->addFromString("connecteur_{$connecteur_info['id_ce']}.json", $json_content);
            }
        }
        $zip->close();
    }
}
