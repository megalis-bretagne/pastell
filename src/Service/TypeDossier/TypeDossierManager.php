<?php

namespace Pastell\Service\TypeDossier;

use Pastell\Helpers\StringHelper;
use TypeDossierProperties;
use TypeDossierFormulaireElementManager;
use TypeDossierEtapeManager;
use TypeDossierSQL;

class TypeDossierManager
{
    /**
     * @var TypeDossierSQL
     */
    private $typeDossierSQL;

    /**
     * @var TypeDossierEtapeManager
     */
    private $typeDossierEtapeManager;

    public function __construct(
        TypeDossierSQL $typeDossierSQL,
        TypeDossierEtapeManager $typeDossierEtapeManager
    ) {
        $this->typeDossierSQL = $typeDossierSQL;
        $this->typeDossierEtapeManager = $typeDossierEtapeManager;
    }

    /**
     * @param $id_t
     * @return mixed
     */
    public function getRawData($id_t)
    {
        return $this->typeDossierSQL->getTypeDossierArray($id_t);
    }

    /**
     * @param $id_t
     * @return TypeDossierProperties
     */
    public function getTypeDossierProperties($id_t): TypeDossierProperties
    {
        $info = $this->getRawData($id_t) ?: [];
        return $this->getTypeDossierFromArray($info);
    }

    /**
     * @param array $info
     * @return TypeDossierProperties
     */
    public function getTypeDossierFromArray(array $info): TypeDossierProperties
    {
        $result = new TypeDossierProperties();

        foreach (['id_type_dossier', 'nom', 'type', 'description', 'affiche_one', 'nom_onglet'] as $key) {
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

    /**
     * @param int $id_t
     * @return string
     */
    public function getHash(int $id_t): string
    {
        $raw_data = $this->getRawData($id_t);
        $this->sortNestedArrayAssoc($raw_data);
        return StringHelper::chopString(
            hash("sha256", json_encode($raw_data)),
            8
        );
    }

    //thanks https://stackoverflow.com/a/37730011
    public function sortNestedArrayAssoc(&$a)
    {
        if (!is_array($a)) {
            return false;
        }
        ksort($a);
        foreach ($a as $k => $v) {
            $this->sortNestedArrayAssoc($a[$k]);
        }
        return true;
    }
}
