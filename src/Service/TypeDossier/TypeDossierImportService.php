<?php

namespace Pastell\Service\TypeDossier;

use TypeDossierProperties;
use TypeDossierFormulaireElementManager;
use TypeDossierEtapeManager;
use TypeDossierSQL;
use TypeDossierException;
use Exception;
use FluxDefinitionFiles;

class TypeDossierImportService
{
    /**
     * @var TypeDossierSQL
     */
    private $typeDossierSQL;

    /**
     * @var TypeDossierEditionService
     */
    private $typeDossierEditionService;

    /**
     * @var TypeDossierEtapeManager
     */
    private $typeDossierEtapeManager;

    /**
     * @var FluxDefinitionFiles
     */
    private $fluxDefinitionFiles;

    public function __construct(
        TypeDossierEditionService $typeDossierEditionService,
        TypeDossierSQL $typeDossierSQL,
        TypeDossierEtapeManager $typeDossierEtapeManager,
        FluxDefinitionFiles $fluxDefinitionFiles
    ) {
        $this->typeDossierEditionService = $typeDossierEditionService;
        $this->typeDossierSQL = $typeDossierSQL;
        $this->typeDossierEtapeManager = $typeDossierEtapeManager;
        $this->fluxDefinitionFiles = $fluxDefinitionFiles;
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
     * @param $file_content
     * @return array
     * @throws TypeDossierException
     */
    public function import($file_content): array
    {
        if (! $file_content) {
            throw new TypeDossierException("Aucun fichier n'a été présenté ou le fichier est vide");
        }
        $json_content = json_decode($file_content, true);
        $this->checkJsonContent($json_content);

        $typeDossierProperties = $this->getTypeDossierFromArray($json_content[TypeDossierUtilService::RAW_DATA]);
        $id_type_dossier = $json_content[TypeDossierUtilService::ID_TYPE_DOSSIER];

        $id_t = $this->typeDossierSQL->getByIdTypeDossier($id_type_dossier);
        $orig_id_type_dossier = $id_type_dossier;
        if ($id_t) {
            $i = 1;
            do {
                $id_type_dossier = $orig_id_type_dossier . "-" . $i++;
            } while ($this->typeDossierSQL->getByIdTypeDossier($id_type_dossier));
        }

        //TODO Issue 1069. Il faut ajouter cette condition et modifier les tests
        /*
        if ($this->fluxDefinitionFiles->getInfo($id_type_dossier)) {
            throw new TypeDossierException(
                "Le type de dossier $id_type_dossier existe déjà sur ce Pastell"
            );
        }
        */

        $typeDossierProperties->id_type_dossier = $id_type_dossier;

        try {
            $id_t = $this->typeDossierEditionService->edit(0, $typeDossierProperties);
        } catch (Exception $e) {
            throw new TypeDossierException("Impossible de créer de type de dossier : " . $e->getMessage());
        }

        return [
            TypeDossierUtilService::ID_T => $id_t,
            TypeDossierUtilService::ID_TYPE_DOSSIER => $id_type_dossier,
            TypeDossierUtilService::ORIG_ID_TYPE_DOSSIER => $orig_id_type_dossier,
            TypeDossierUtilService::TIMESTAMP => $json_content[TypeDossierUtilService::TIMESTAMP]
        ];
    }

    /**
     * @param $json_content
     * @throws TypeDossierException
     */
    private function checkJsonContent($json_content)
    {
        if (! $json_content) {
            throw new TypeDossierException("Le fichier présenté ne contient pas de json");
        }

        if (empty($json_content[TypeDossierUtilService::RAW_DATA]) || empty($json_content[TypeDossierUtilService::ID_TYPE_DOSSIER])) {
            throw new TypeDossierException("Le fichier présenté ne semble pas contenir de données utilisables");
        }
    }


    /**
     * @param array $info
     * @return TypeDossierProperties
     */
    public function getTypeDossierFromArray(array $info): TypeDossierProperties
    {
        $result = new TypeDossierProperties();

        foreach (array('id_type_dossier', 'nom', 'type', 'description', 'nom_onglet') as $key) {
            if (isset($info[$key])) {
                $result->$key = $info[$key];
            }
        }
        if (empty($info['formulaireElement'])) {
            $info['formulaireElement'] = [];
        }
        if (empty($info['etape'])) {
            $info['etape'] = [];
        }

        $result->formulaireElement = [];
        $typeDossierFormulaireElementManager = new TypeDossierFormulaireElementManager();

        foreach ($info['formulaireElement'] as $formulaire_element) {
            $newFormElement = $typeDossierFormulaireElementManager->getElementFromArray($formulaire_element);
            $result->formulaireElement[] = $newFormElement;
        }

        $result->etape = [];

        foreach ($info['etape'] as $etape) {
            $fomulaire_configuration = $this->typeDossierEtapeManager->getFormulaireConfigurationEtape($etape['type']);
            $newFormEtape = $this->typeDossierEtapeManager->getEtapeFromArray($etape, $fomulaire_configuration);
            $result->etape[$newFormEtape->num_etape ?: 0] = $newFormEtape;
        }
        $sum_type_etape = [];
        foreach ($result->etape as $etape) {
            if (!isset($sum_type_etape[$etape->type])) {
                $sum_type_etape[$etape->type] = 0;
            } else {
                $sum_type_etape[$etape->type]++;
            }
            $etape->num_etape_same_type = $sum_type_etape[$etape->type];
        }
        foreach ($result->etape as $etape) {
            if ($sum_type_etape[$etape->type] > 0) {
                $etape->etape_with_same_type_exists = true;
            }
        }
        return $result;
    }
}
