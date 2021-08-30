<?php

namespace Pastell\Service\TypeDossier;

use Pastell\Service\TypeDossier\TypeDossierActionService;
use TypeDossierSQL;
use TypeDossierException;
use Exception;

class TypeDossierImportService
{
    /**
     * @var TypeDossierManager
     */
    private $typeDossierManager;

    /**
     * @var TypeDossierSQL
     */
    private $typeDossierSQL;

    /**
     * @var TypeDossierEditionService
     */
    private $typeDossierEditionService;

    /**
     * @var TypeDossierActionService
     */
    private $typeDossierActionService;


    public function __construct(
        TypeDossierManager $typeDossierManager,
        TypeDossierEditionService $typeDossierEditionService,
        TypeDossierSQL $typeDossierSQL,
        TypeDossierActionService $typeDossierActionService
    ) {
        $this->typeDossierManager = $typeDossierManager;
        $this->typeDossierEditionService = $typeDossierEditionService;
        $this->typeDossierSQL = $typeDossierSQL;
        $this->typeDossierActionService = $typeDossierActionService;
    }

    /**
     * @param string $filepath
     * @return array
     * @throws TypeDossierException
     */
    public function importFromFilePath(string $filepath): array
    {
        $this->typeDossierActionService->setId_u(0);
        return $this->import(file_get_contents($filepath));
    }

    /**
     * @param $file_content
     * @return array
     * @throws TypeDossierException
     */
    public function import(string $file_content): array
    {
        $json_content = $this->checkFileContent($file_content);
        $typeDossierProperties = $this->typeDossierManager->getTypeDossierFromArray($json_content[TypeDossierUtilService::RAW_DATA]);
        $id_type_dossier = $json_content[TypeDossierUtilService::ID_TYPE_DOSSIER];

        $id_t = $this->typeDossierSQL->getByIdTypeDossier($id_type_dossier);
        $orig_id_type_dossier = $id_type_dossier;
        if ($id_t) {
            $i = 1;
            do {
                $id_type_dossier = $orig_id_type_dossier . "-" . $i++;
            } while ($this->typeDossierSQL->getByIdTypeDossier($id_type_dossier));
        }

        $typeDossierProperties->id_type_dossier = $id_type_dossier;
        try {
            $id_t = $this->typeDossierEditionService->create($typeDossierProperties);
        } catch (Exception $e) {
            throw new TypeDossierException("Impossible de créer le type de dossier : " . $e->getMessage());
        }

        return [
            TypeDossierUtilService::ID_T => $id_t,
            TypeDossierUtilService::ID_TYPE_DOSSIER => $id_type_dossier,
            TypeDossierUtilService::ORIG_ID_TYPE_DOSSIER => $orig_id_type_dossier,
            TypeDossierUtilService::TIMESTAMP => $json_content[TypeDossierUtilService::TIMESTAMP]
        ];
    }

    /**
     * @param $file_content
     * @return array
     * @throws TypeDossierException
     */
    private function checkFileContent($file_content): array
    {
        if (! $file_content) {
            throw new TypeDossierException("Aucun fichier n'a été présenté ou le fichier est vide");
        }
        $json_content = json_decode($file_content, true);
        if (! $json_content) {
            throw new TypeDossierException("Le fichier présenté ne contient pas de json");
        }
        if (empty($json_content[TypeDossierUtilService::RAW_DATA]) || empty($json_content[TypeDossierUtilService::ID_TYPE_DOSSIER])) {
            throw new TypeDossierException("Le fichier présenté ne semble pas contenir de données utilisables");
        }
        return $json_content;
    }
}
