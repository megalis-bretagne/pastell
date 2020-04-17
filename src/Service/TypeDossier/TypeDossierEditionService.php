<?php

namespace Pastell\Service\TypeDossier;

use Exception;
use TypeDossierException;
use TypeDossierSQL;
use TypeDossierPersonnaliseDirectoryManager;
use TypeDossierProperties;
use EntiteSQL;
use Journal;

class TypeDossierEditionService
{
    public const TYPE_DOSSIER_ID_MAX_LENGTH = 32;
    public const TYPE_DOSSIER_ID_REGEXP = "^[0-9a-z-]+$";
    public const TYPE_DOSSIER_ID_PASTELL = "pastell-";

    /**
     * @var TypeDossierSQL
     */
    private $typeDossierSQL;

    /**
     * @var TypeDossierPersonnaliseDirectoryManager
     */
    private $typeDossierPersonnaliseDirectoryManager;

    /**
     * @var TypeDossierExportService
     */
    private $typeDossierExportService;

    /**
     * @var Journal
     */
    private $journal;

    public function __construct(
        TypeDossierSQL $typeDossierSQL,
        TypeDossierPersonnaliseDirectoryManager $typeDossierPersonnaliseDirectoryManager,
        TypeDossierExportService $typeDossierExportService,
        Journal $journal
    ) {
        $this->typeDossierSQL = $typeDossierSQL;
        $this->typeDossierPersonnaliseDirectoryManager = $typeDossierPersonnaliseDirectoryManager;
        $this->typeDossierExportService = $typeDossierExportService;
        $this->journal = $journal;
    }

    /**
     * @param int $id_t
     * @param TypeDossierProperties $typeDossierProperties
     * @return int
     * @throws TypeDossierException
     * @throws Exception
     */
    public function edit(int $id_t, TypeDossierProperties $typeDossierProperties): int
    {
        if (! $id_t) {
            $journal_action = Journal::ACTION_AJOUTE;
            $message_action = 'Ajout';
        } else {
            $journal_action = Journal::ACTION_MODIFFIE;
            $message_action = 'Modification';
        }

        $this->checkTypeDossierId($typeDossierProperties->id_type_dossier);
        $typeDossierProperties = $this->fixSameStepsType($typeDossierProperties);
        $id_t = $this->typeDossierSQL->edit($id_t, $typeDossierProperties);
        $this->typeDossierPersonnaliseDirectoryManager->save($id_t, $typeDossierProperties);

        $export = $this->typeDossierExportService->export($id_t);
        $this->journal->add(
            Journal::TYPE_DOSSIER_EDITION,
            EntiteSQL::ID_E_ENTITE_RACINE,
            Journal::NO_ID_D,
            $journal_action,
            $message_action . " du type de dossier id_t=$id_t.\nJSON contenant l'export de la definition du type de dossier : " . $export
        );
        return $id_t;
    }

    /**
     * @param $id_type_dossier
     * @throws TypeDossierException
     */
    public function checkTypeDossierId($id_type_dossier)
    {
        if (! $id_type_dossier) {
            throw new TypeDossierException(
                "Aucun identifiant de type de dossier fourni"
            );
        }

        if (substr($id_type_dossier, 0, 8) === self::TYPE_DOSSIER_ID_PASTELL) {
            throw new TypeDossierException(
                "L'identifiant du type de dossier ne doit pas commencer par : " . self::TYPE_DOSSIER_ID_PASTELL
            );
        }

        if (!preg_match("#" . self::TYPE_DOSSIER_ID_REGEXP . "#", $id_type_dossier)) {
            throw new TypeDossierException(
                "L'identifiant du type de dossier « " . get_hecho($id_type_dossier) . " » ne respecte pas l'expression rationnelle : " . self::TYPE_DOSSIER_ID_REGEXP
            );
        }

        if (strlen($id_type_dossier) > self::TYPE_DOSSIER_ID_MAX_LENGTH) {
            throw new TypeDossierException(
                "L'identifiant du type de dossier « " . get_hecho($id_type_dossier) . " » ne doit pas dépasser " . self::TYPE_DOSSIER_ID_MAX_LENGTH . " caractères"
            );
        }
    }

    /**
     * @param TypeDossierProperties $typeDossierProperties
     * @return TypeDossierProperties
     */
    public function fixSameStepsType(TypeDossierProperties $typeDossierProperties): TypeDossierProperties
    {
        $numberOfStepsPerType = [];
        $numSameStep = [];

        foreach ($typeDossierProperties->etape as $step) {
            if (empty($numberOfStepsPerType[$step->type])) {
                $numberOfStepsPerType[$step->type] = 0;
                $numSameStep[$step->type] = 0;
            }
            ++$numberOfStepsPerType[$step->type];
        }

        foreach ($typeDossierProperties->etape as $step) {
            $step->etape_with_same_type_exists = $numberOfStepsPerType[$step->type] > 1;
            $step->num_etape_same_type = $numSameStep[$step->type];
            ++$numSameStep[$step->type];
        }

        return $typeDossierProperties;
    }
}
