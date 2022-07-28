<?php

namespace Pastell\Service\TypeDossier;

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

    public function __construct(
        TypeDossierManager $typeDossierManager,
        TypeDossierEditionService $typeDossierEditionService,
        TypeDossierSQL $typeDossierSQL
    ) {
        $this->typeDossierManager = $typeDossierManager;
        $this->typeDossierEditionService = $typeDossierEditionService;
        $this->typeDossierSQL = $typeDossierSQL;
    }

    /**
     * @param string $filepath
     * @return array
     * @throws TypeDossierException
     */
    public function importFromFilePath(string $filepath): array
    {
        return $this->import(file_get_contents($filepath));
    }

    /**
     * @param string $file_content
     * @return array
     * @throws TypeDossierException
     */
    public function import(string $file_content): array
    {
        list($json_content,$id_type_dossier,$typeDossierProperties) = $this->getInfoFromFileContent($file_content);

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
     * @throws TypeDossierException
     */
    public function getInfoFromFileContent(?string $file_content): array
    {
        $json_content = $this->checkFileContent($file_content);
        $typeDossierProperties = $this->typeDossierManager->getTypeDossierFromArray($json_content[TypeDossierUtilService::RAW_DATA]);
        $id_type_dossier = $json_content[TypeDossierUtilService::ID_TYPE_DOSSIER];
        return [$json_content, $id_type_dossier, $typeDossierProperties ];
    }



    /**
     * @throws TypeDossierException
     */
    private function checkFileContent(?string $file_content): array
    {
        if ($file_content === null || $file_content === '') {
            throw new TypeDossierException("La définition du type de dossier est vide");
        }
        $json_content = json_decode($file_content, true);
        if (! $json_content) {
            throw new TypeDossierException("La définition json du type de dossier n'est pas valide");
        }
        if (empty($json_content[TypeDossierUtilService::RAW_DATA]) || empty($json_content[TypeDossierUtilService::ID_TYPE_DOSSIER])) {
            throw new TypeDossierException("La définition json du type de dossier ne semble pas contenir de données utilisables");
        }
        return $json_content;
    }
}
