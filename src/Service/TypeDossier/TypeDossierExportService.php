<?php

namespace Pastell\Service\TypeDossier;

use TypeDossierSQL;
use ManifestFactory;

class TypeDossierExportService
{
    /**
     * @var TypeDossierSQL
     */
    private $typeDossierSQL;

    /**
     * @var ManifestFactory
     */
    private $manifestFactory;

    private $time_function;

    public function __construct(
        TypeDossierSQL $typeDossierSQL,
        ManifestFactory $manifestFactory
    ) {
        $this->typeDossierSQL = $typeDossierSQL;
        $this->manifestFactory = $manifestFactory;
        $this->setTimeFunction(function () {
            return time();
        });
    }

    /**
     * @param callable $time_function
     */
    public function setTimeFunction(callable $time_function)
    {
        $this->time_function = $time_function;
    }

    /**
     * @param int $id_t
     * @return string
     */
    public function export(int $id_t): string
    {
        $raw_data = $this->typeDossierSQL->getTypeDossierArray($id_t);

        $type_dossier_info = $this->typeDossierSQL->getInfo($id_t);

        $result[TypeDossierUtilService::ID_TYPE_DOSSIER] = $type_dossier_info[TypeDossierUtilService::ID_TYPE_DOSSIER];

        $result[TypeDossierUtilService::PASTELL_VERSION] = $this->manifestFactory->getPastellManifest()->getVersion();
        $t_function = $this->time_function;
        $result[TypeDossierUtilService::TIMESTAMP] = $t_function();
        $result[TypeDossierUtilService::RAW_DATA] = $raw_data;

        return json_encode($result);
    }
}
