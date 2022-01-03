<?php

require_once __DIR__ . "/pastell-core/type-dossier/TypeDossierLoader.class.php";

trait TypeDossierLoaderTestTrait
{
    /**
     * @param $type_dossier_file_path
     * @throws TypeDossierException
     */
    protected function loadTypeDossier($type_dossier_file_path)
    {
        $this->getObjectInstancier()
            ->getInstance(TypeDossierLoader::class)
            ->createTypeDossierFromFilepath($type_dossier_file_path);
    }

    protected function unloadTypeDossier()
    {
        $this->getObjectInstancier()
            ->getInstance(TypeDossierLoader::class)
            ->unload();
    }

    /**
     * @return ObjectInstancier
     */
    abstract public function getObjectInstancier();
}
