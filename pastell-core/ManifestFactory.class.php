<?php

class ManifestFactory
{
    public const MANIFEST_FILENAME = "manifest.yml";

    private $pastell_path;
    private $ymlLoader;

    public function __construct($pastell_path, YMLLoader $ymlLoader)
    {
        $this->pastell_path = $pastell_path;
        $this->ymlLoader = $ymlLoader;
    }

    public function getManifest($extension_path)
    {
        $manifest_file_path  = $extension_path . "/" . self::MANIFEST_FILENAME;
        if (! $manifest_file_path) {
            throw new Exception("Le fichier $manifest_file_path n'existe pas");
        }
        $manifest_info = $this->ymlLoader->getArray($manifest_file_path);
        if (!$manifest_info) {
            throw new Exception("Le fichier $manifest_file_path est vide");
        }
        return new ManifestReader($manifest_info);
    }

    public function getPastellManifest()
    {
        return $this->getManifest($this->pastell_path);
    }
}
