<?php

namespace Pastell\Service\TypeDossier;

use Exception;
use TypeDossierEtapeManager;
use TypeDossierEtapeProperties;
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
     * @var TypeDossierEtapeManager
     */
    private $typeDossierEtapeManager;

    /**
     * @var TypeDossierExportService
     */
    private $typeDossierExportService;

    /**
     * @var TypeDossierManager
     */
    private $typeDossierManager;
    /**
     * @var Journal
     */
    private $journal;

    public function __construct(
        TypeDossierSQL $typeDossierSQL,
        TypeDossierPersonnaliseDirectoryManager $typeDossierPersonnaliseDirectoryManager,
        TypeDossierEtapeManager $typeDossierEtapeManager,
        TypeDossierExportService $typeDossierExportService,
        Journal $journal,
        TypeDossierManager $typeDossierManager
    ) {
        $this->typeDossierSQL = $typeDossierSQL;
        $this->typeDossierPersonnaliseDirectoryManager = $typeDossierPersonnaliseDirectoryManager;
        $this->typeDossierEtapeManager = $typeDossierEtapeManager;
        $this->typeDossierExportService = $typeDossierExportService;
        $this->journal = $journal;
        $this->typeDossierManager = $typeDossierManager;
    }

    /**
     * @param TypeDossierProperties $typeDossierProperties
     * @return int
     * @throws TypeDossierException
     * @throws Exception
     */
    public function create(TypeDossierProperties $typeDossierProperties): int
    {
        $this->checkTypeDossierId($typeDossierProperties->id_type_dossier);
        $this->checkNomOnglet($typeDossierProperties->nom_onglet);
        return $this->edit(0, $typeDossierProperties);
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

        $typeDossierProperties = $this->fixSameStepsType($typeDossierProperties);
        $id_t = $this->typeDossierSQL->edit($id_t, $typeDossierProperties);
        $this->typeDossierPersonnaliseDirectoryManager->save($id_t, $typeDossierProperties);

        $export = $this->typeDossierExportService->export($id_t);
        $this->journal->add(
            Journal::TYPE_DOSSIER_EDITION,
            EntiteSQL::ID_E_ENTITE_RACINE,
            Journal::NO_ID_D,
            $journal_action,
            $message_action . " du type de dossier id_t=$id_t. JSON contenant l'export de la definition du type de dossier : " . $export
        );
        return $id_t;
    }

    /**
     * @param string $source_type_dossier_id
     * @param string $target_type_dossier_id
     * @throws TypeDossierException
     */
    public function renameTypeDossierId(string $source_type_dossier_id, string $target_type_dossier_id)
    {
        $this->typeDossierPersonnaliseDirectoryManager->rename($source_type_dossier_id, $target_type_dossier_id);
    }

    /**
     * @param $id_t
     * @param $nom
     * @param $type
     * @param $description
     * @param $nom_onglet
     * @throws TypeDossierException
     */
    public function editLibelleInfo($id_t, $nom, $type, $description, $nom_onglet)
    {
        $this->checkNomOnglet($nom_onglet);
        $typeDossierProporties = $this->typeDossierManager->getTypeDossierProperties($id_t);
        $typeDossierProporties->nom = $nom;
        $typeDossierProporties->type = $type;
        $typeDossierProporties->description = $description;
        $typeDossierProporties->nom_onglet = $nom_onglet;
        $this->edit($id_t, $typeDossierProporties);
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
     * @param $nom_onglet
     * @throws TypeDossierException
     */
    public function checkNomOnglet($nom_onglet)
    {
        if (stristr($nom_onglet, '#')) {
            throw new TypeDossierException(
                "Le libellé de l'onglet principal ne doit pas contenir « # »"
            );
        }

        $typeDossierEtape = new TypeDossierEtapeProperties();
        $liste_onglet_name = [];
        $all_etape_type = $this->typeDossierEtapeManager->getAllType();

        foreach ($all_etape_type as $etape_type => $libelle) {
            $typeDossierEtape->type = $etape_type;
            foreach ($this->typeDossierEtapeManager->getFormulaireForEtape($typeDossierEtape) as $etape_onglet_name => $onglet_content) {
                $liste_onglet_name[] = $etape_onglet_name;
            }
        }
        $liste_onglet_name = array_unique($liste_onglet_name);
        sort($liste_onglet_name);

        foreach ($liste_onglet_name as $etape_onglet_name) {
            if (strcasecmp($nom_onglet, $etape_onglet_name) == 0) {
                throw new TypeDossierException(
                    "Le libellé de l'onglet principal ne doit pas faire partie de la liste: " . implode(", ", $liste_onglet_name)
                );
            }
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
