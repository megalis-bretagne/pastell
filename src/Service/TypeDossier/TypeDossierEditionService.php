<?php

namespace Pastell\Service\TypeDossier;

use Exception;
use TypeDossierEtapeManager;
use TypeDossierEtapeProperties;
use TypeDossierException;
use TypeDossierSQL;
use TypeDossierPersonnaliseDirectoryManager;
use TypeDossierProperties;
use FluxDefinitionFiles;

class TypeDossierEditionService
{
    public const TYPE_DOSSIER_ID_MAX_LENGTH = 45;
    public const TYPE_DOSSIER_ID_REGEXP = "^[0-9a-z-]{1,32}(-destinataire|-reponse)?$";
    public const TYPE_DOSSIER_ID_PASTELL = 'pastell-';
    public const TYPE_DOSSIER_ID_LS = 'ls-';

    public function __construct(
        private readonly TypeDossierSQL $typeDossierSQL,
        private readonly TypeDossierPersonnaliseDirectoryManager $typeDossierPersonnaliseDirectoryManager,
        private readonly TypeDossierEtapeManager $typeDossierEtapeManager,
        private readonly TypeDossierManager $typeDossierManager,
        private readonly FluxDefinitionFiles $fluxDefinitionFiles
    ) {
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
     * @throws Exception
     */
    public function edit(int $id_t, TypeDossierProperties $typeDossierProperties): int
    {

        $typeDossierProperties = $this->fixSameStepsType($typeDossierProperties);
        $id_t = $this->typeDossierSQL->edit($id_t, $typeDossierProperties);
        $this->typeDossierPersonnaliseDirectoryManager->save($id_t, $typeDossierProperties);
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
     * @throws Exception
     */
    public function editLibelleInfo($id_t, $nom, $type, $description, $nom_onglet, $affiche_one)
    {
        $this->checkNomOnglet($nom_onglet);
        $typeDossierProporties = $this->typeDossierManager->getTypeDossierProperties($id_t);
        $typeDossierProporties->nom = $nom;
        $typeDossierProporties->type = $type;
        $typeDossierProporties->description = $description;
        $typeDossierProporties->nom_onglet = $nom_onglet;
        $typeDossierProporties->affiche_one = $affiche_one === 'on';
        $this->edit($id_t, $typeDossierProporties);
    }

    /**
     * @throws TypeDossierException
     */
    public function checkTypeDossierId(string $id_type_dossier): void
    {
        if ($id_type_dossier === '') {
            throw new TypeDossierException(
                'Aucun identifiant de type de dossier fourni'
            );
        }
        if ($this->fluxDefinitionFiles->getInfo($id_type_dossier)) {
            throw new TypeDossierException(
                "Le type de dossier $id_type_dossier existe déjà sur ce Pastell"
            );
        }
        if (
            \str_starts_with($id_type_dossier, self::TYPE_DOSSIER_ID_PASTELL) ||
            \str_starts_with($id_type_dossier, self::TYPE_DOSSIER_ID_LS)
        ) {
            throw new TypeDossierException(
                \sprintf(
                    "L'identifiant du type de dossier ne doit pas commencer par : %s ou %s",
                    self::TYPE_DOSSIER_ID_PASTELL,
                    self::TYPE_DOSSIER_ID_LS
                )
            );
        }
        if (!preg_match('#' . self::TYPE_DOSSIER_ID_REGEXP . '#', $id_type_dossier)) {
            throw new TypeDossierException(
                \sprintf(
                    "L'identifiant du type de dossier « %s » ne respecte pas l'expression rationnelle : %s",
                    \get_hecho($id_type_dossier),
                    self::TYPE_DOSSIER_ID_REGEXP
                )
            );
        }
        if (\strlen($id_type_dossier) > self::TYPE_DOSSIER_ID_MAX_LENGTH) {
            throw new TypeDossierException(
                \sprintf(
                    "L'identifiant du type de dossier « %s » ne doit pas dépasser %d caractères",
                    \get_hecho($id_type_dossier),
                    self::TYPE_DOSSIER_ID_MAX_LENGTH
                )
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
