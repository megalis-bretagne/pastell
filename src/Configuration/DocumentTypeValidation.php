<?php

declare(strict_types=1);

namespace Pastell\Configuration;

use ActionExecutor;
use ActionPossible;
use ConnecteurDefinitionFiles;
use EntiteSQL;
use Exception;
use Field;
use Pastell\Service\Pack\PackService;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;
use UnrecoverableException;

use function class_exists;
use function is_subclass_of;
use function sprintf;

class DocumentTypeValidation
{
    private array $errorList;
    private array $allFormulaireElements;

    public function __construct(
        private readonly DocumentTypeConfiguration $documentTypeConfiguration,
        private readonly PackService $packService,
        private readonly ConnecteurDefinitionFiles $connecteurDefinitionFiles,
    ) {
    }

    public function isDefinitionFileValid(string $filePath): bool
    {
        try {
            $this->getConfiguration($filePath);
            return true;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * @throws UnrecoverableException
     */
    private function getConfiguration(string $filePath): array
    {
        $ymlData = Yaml::parseFile($filePath);
        $dataProcessed = (new Processor())->processConfiguration(
            $this->documentTypeConfiguration,
            [$ymlData]
        );
        $this->validate($dataProcessed);

        return $dataProcessed;
    }

    public function getErrorList(string $filePath): array
    {
        try {
            $this->getConfiguration($filePath);
        } catch (Exception $e) {
            if ($e->getMessage() !== '') {
                $this->errorList[] = $e->getMessage();
            }
        }
        return $this->errorList;
    }

    private function validate(array $typeDefinition): void
    {
        $this->errorList = [];
        $this->allFormulaireElements = $this->getAllFormulaireElements($typeDefinition);

        $this->validateOneTitle($typeDefinition);
        $this->validatePageCondition($typeDefinition);
        $this->validateChoiceAction($typeDefinition);
        $this->validateRestrictionPack($typeDefinition);
        $this->validateConnecteur($typeDefinition);
        $this->validateOnChange($typeDefinition);
        $this->validateIsEqual($typeDefinition);
        $this->validateReadOnlyContent($typeDefinition);
        $this->validateRuleAction($typeDefinition, DocumentTypeConfiguration::LAST_ACTION);
        $this->validateRuleAction($typeDefinition, DocumentTypeConfiguration::NO_ACTION);
        $this->validateRuleAction($typeDefinition, DocumentTypeConfiguration::HAS_ACTION);
        $this->validateActionProperties($typeDefinition, DocumentTypeConfiguration::ACTION_AUTOMATIQUE);
        $this->validateActionProperties(
            $typeDefinition,
            DocumentTypeConfiguration::ACCUSE_DE_RECEPTION_ACTION,
        );
        $this->validateEditableContent($typeDefinition);
        $this->validateDepend($typeDefinition);
        $this->validateRuleContent($typeDefinition);
        $this->validateActionSelection($typeDefinition);
        $this->validateRuleTypeIdE($typeDefinition);
        $this->validateActionClass($typeDefinition);
        $this->validateChamps($typeDefinition, DocumentTypeConfiguration::CHAMPS_RECHERCHE_AVANCEE);
        $this->validateChamps($typeDefinition, DocumentTypeConfiguration::CHAMPS_AFFICHES);
        $this->validateActionConnecteurType($typeDefinition);

        if (count($this->errorList) > 0) {
            throw new UnrecoverableException();
        }
    }

    private function getAllFormulaireElements(array $typeDefinition): array
    {
        $result = [];
        foreach ($this->getValues($typeDefinition, DocumentTypeConfiguration::FORMULAIRE) as $onglet => $elementList) {
            if (! $elementList) {
                $this->errorList[] = 'formulaire:onglet: est vide';
                continue;
            }
            foreach ($elementList as $name => $prop) {
                $result[] = $name;
            }
        }
        return $result;
    }

    private function validateOneTitle(array $typeDefinition): void
    {
        if (!empty($typeDefinition[DocumentTypeConfiguration::FORMULAIRE])) {
            $title = [];
            foreach ($typeDefinition[DocumentTypeConfiguration::FORMULAIRE] as $onglet => $formulaireProperties) {
                foreach ($formulaireProperties as $elementName => $elementProperties) {
                    if (isset($elementProperties[DocumentTypeConfiguration::TITLE])) {
                        $title[] = $elementName;
                    }
                }
            }
            if (count($title) > 1) {
                $this->errorList[] = 'Plusieurs éléments trouvés avec la propriété « <b>title</b> » : '
                    . implode(',', $title);
            }
        }
    }

    private function validatePageCondition(array $typeDefinition): void
    {
        $allPageConditionKeys = array_keys($this->getValues(
            $typeDefinition,
            DocumentTypeConfiguration::PAGE_CONDITION
        ));
        $allPageKeys = array_keys($this->getValues($typeDefinition, DocumentTypeConfiguration::FORMULAIRE));
        foreach ($allPageConditionKeys as $pageCondition) {
            if (! in_array($pageCondition, $allPageKeys)) {
                $this->errorList[] = "page-condition:<b>$pageCondition</b> n'est pas une clé de <b>formulaire</b>";
                continue;
            }
            foreach ($typeDefinition[DocumentTypeConfiguration::PAGE_CONDITION][$pageCondition] as $element => $test) {
                if (!in_array($element, $this->allFormulaireElements)) {
                    $this->errorList[] = "page-condition:<b>$pageCondition:$element</b> n'est pas "
                        . 'un élément du <b>formulaire</b>';
                }
            }
        }
    }

    private function getValues(array $definition, string $keyName): array
    {
        if (empty($definition[$keyName])) {
            return [];
        }
        return $definition[$keyName];
    }

    private function validateChoiceAction(array $typeDefinition): void
    {
        $choiceActionList = $this->getPropertiesValue(
            $typeDefinition,
            DocumentTypeConfiguration::CHOICE_ACTION,
            DocumentTypeConfiguration::FORMULAIRE
        );
        $this->checkIsAction($typeDefinition, $choiceActionList);
    }

    private function getPropertiesValue(array $typeDefinition, string $property, string $element): array
    {
        if (empty($typeDefinition[$element])) {
            return [];
        }
        $propertiesList = [];
        foreach ($typeDefinition[$element] as $onglet => $properties) {
            if ($element === DocumentTypeConfiguration::ACTION && !empty($properties[$property])) {
                $propertiesList[] = $this->canonicalizeValue($properties[$property]);
            }
            if ($element === DocumentTypeConfiguration::FORMULAIRE) {
                foreach ($properties as $elementName => $elementProperties) {
                    if (isset($elementProperties[$property])) {
                        $propertiesList[] = $this->canonicalizeValue($elementProperties[$property]);
                    }
                }
            }
        }
        return $propertiesList;
    }

    private function canonicalizeValue($value): string
    {
        if (!str_contains($value, '_')) {
            $value = Field::Canonicalize($value);
        }
        return $value;
    }

    private function checkIsAction(array $typeDefinition, array $actionListToCheck): void
    {
        $allPossibleActionKeys = array_keys($this->getValues($typeDefinition, DocumentTypeConfiguration::ACTION));
        $fatalErrorKey = $this->canonicalizeValue(ActionPossible::FATAL_ERROR_ACTION);
        if (! array_key_exists($fatalErrorKey, $allPossibleActionKeys)) {
            $allPossibleActionKeys[] = $fatalErrorKey;
        }
        foreach ($actionListToCheck as $action) {
            if (! in_array($action, $allPossibleActionKeys)) {
                $this->errorList[] = "formulaire:xx:<b>$action</b> n'est pas une clé de <b>action</b>";
            }
        }
    }

    private function validateRestrictionPack(array $typeDefinition): void
    {
        $allRestrictionPack = $this->getValues($typeDefinition, DocumentTypeConfiguration::RESTRICTION_PACK);
        foreach ($allRestrictionPack as $restrictionPack) {
            if (!array_key_exists($restrictionPack, $this->packService->getListPack())) {
                $this->errorList[] = "restriction_pack:<b>$restrictionPack</b> "
                    . "n'est pas défini dans la liste des packs";
            }
        }
    }

    private function validateConnecteur(array $typeDefinition): void
    {
        $allConnecteur = $this->getValues($typeDefinition, DocumentTypeConfiguration::CONNECTEUR);
        foreach ($allConnecteur as $connecteur) {
            if (!in_array($connecteur, $this->connecteurDefinitionFiles->getAllType())) {
                $this->errorList[] = "connecteur:<b>$connecteur</b> n'est défini dans aucun connecteur du système";
            }
        }
    }

    private function validateOnChange(array $typeDefinition): void
    {
        $onChangeList = $this->getPropertiesValue(
            $typeDefinition,
            DocumentTypeConfiguration::ONCHANGE,
            DocumentTypeConfiguration::FORMULAIRE
        );
        $this->checkIsAction($typeDefinition, $onChangeList);
    }

    private function validateIsEqual(array $typeDefinition): void
    {
        $isEqualList = $this->getPropertiesValue(
            $typeDefinition,
            DocumentTypeConfiguration::IS_EQUAL,
            DocumentTypeConfiguration::FORMULAIRE
        );
        foreach ($isEqualList as $isEqual) {
            if (! in_array($isEqual, $this->allFormulaireElements)) {
                $this->errorList[] = "formulaire:xx:yy:is_equal:<b>$isEqual</b> n'est pas défini dans le formulaire";
            }
        }
    }

    private function validateReadOnlyContent(array $typeDefinition): void
    {
        $readOnlyContentList = $this->getPropertiesValue(
            $typeDefinition,
            DocumentTypeConfiguration::READ_ONLY_CONTENT,
            DocumentTypeConfiguration::FORMULAIRE,
        );
        foreach ($readOnlyContentList as $readOnlyContentElement) {
            foreach ($readOnlyContentElement as $name => $prop) {
                if (! in_array($name, $this->allFormulaireElements)) {
                    $this->errorList[] = "formulaire:xx:yy:read-only-content:<b>$name</b> "
                        . "n'est pas défini dans le formulaire";
                }
            }
        }
    }

    private function validateRuleAction($typeDefinition, $ruleName): void
    {
        $allAction = $this->getElementRuleValue($typeDefinition, $ruleName);
        $this->checkIsAction($typeDefinition, $allAction);
    }

    private function getElementRuleValue($typeDefinition, $ruleName): array
    {
        if (empty($typeDefinition[DocumentTypeConfiguration::ACTION])) {
            return [];
        }
        $propertiesList = [];

        foreach ($typeDefinition[DocumentTypeConfiguration::ACTION] as $actionName => $actionProperties) {
            if (!empty($actionProperties[DocumentTypeConfiguration::RULE])) {
                $ruleProperty = $this->findRule($actionProperties[DocumentTypeConfiguration::RULE], $ruleName);
                if ($ruleProperty) {
                    $propertiesList = array_merge($propertiesList, $ruleProperty);
                }
            }
        }
        if ($ruleName !== DocumentTypeConfiguration::CONTENT) {
            foreach ($propertiesList as $key => $value) {
                $propertiesList[$key] = $this->canonicalizeValue($value);
            }
        }
        return $propertiesList;
    }

    private function findRule(array $ruleArray, $ruleName): array
    {
        $result = [];
        if (isset($ruleArray[$ruleName])) {
            if (! is_array($ruleArray[$ruleName])) {
                $result = [$ruleArray[$ruleName]];
            } else {
                $result = $ruleArray[$ruleName];
            }
        }
        foreach ($ruleArray as $r_name => $r_properties) {
            if (mb_substr($r_name, 0, 3) == 'or_' || mb_substr($r_name, 0, 4) == 'and_') {
                $result = array_merge($result, $this->findRule($r_properties, $ruleName));
            }
        }
        return $result;
    }

    private function validateActionProperties(array $typeDefinition, string $properties): void
    {
        $actionList = $this->getPropertiesValue($typeDefinition, $properties, DocumentTypeConfiguration::ACTION);
        $this->checkIsAction($typeDefinition, $actionList);
    }

    private function validateEditableContent(array $typeDefinition): void
    {
        $editableContentList = [];
        $allAction = $this->getValues($typeDefinition, DocumentTypeConfiguration::ACTION);
        foreach ($allAction as $action) {
            if (empty($action[DocumentTypeConfiguration::EDITABLE_CONTENT])) {
                continue;
            }
            $editableContentList = array_merge(
                $editableContentList,
                $action[DocumentTypeConfiguration::EDITABLE_CONTENT]
            );
        }
        foreach ($editableContentList as $editableContent) {
            if (! in_array($editableContent, $this->allFormulaireElements)) {
                $this->errorList[] = "formulaire:xx:yy:editable-content:<b>$editableContent</b> "
                    . "n'est pas défini dans le formulaire";
            }
        }
    }

    private function validateDepend(array $typeDefinition): void
    {
        $allFormulaire = $this->getValues($typeDefinition, DocumentTypeConfiguration::FORMULAIRE);
        foreach ($allFormulaire as $onglet => $elementList) {
            foreach ($elementList as $name => $property) {
                if (empty($property[DocumentTypeConfiguration::DEPEND])) {
                    continue;
                }
                if (! in_array($property['depend'], $this->allFormulaireElements)) {
                    $this->errorList[] =
                        "<b>formulaire:$onglet:$name:depend:{$property[DocumentTypeConfiguration::DEPEND]}</b> "
                        . "n'est pas un élément du formulaire";
                }
            }
        }
    }

    private function validateRuleContent($typeDefinition): void
    {
        $contentList = $this->getElementRuleValue($typeDefinition, DocumentTypeConfiguration::CONTENT);
        foreach ($contentList as $key => $content) {
            if (! in_array($key, $this->allFormulaireElements)) {
                $this->errorList[] = "action:xx:rule:content:<b>$key</b> n'est pas défini dans le formulaire";
            }
        }
    }

    private function validateActionSelection(array $typeDefinition): void
    {
        $allAction = $this->getValues($typeDefinition, DocumentTypeConfiguration::ACTION);
        foreach ($allAction as $actionName => $action) {
            if (empty($action[DocumentTypeConfiguration::ACTION_SELECTION])) {
                continue;
            }
            if (!in_array($action[DocumentTypeConfiguration::ACTION_SELECTION], array_keys(EntiteSQL::getAllType()))) {
                $this->errorList[] = "action:$actionName:action-selection:<b>{$action['action-selection']}</b> "
                    . "n'est pas un type d'entité du système";
            }
        }
    }

    private function validateRuleTypeIdE($typeDefinition): void
    {
        $allType = $this->getElementRuleValue($typeDefinition, DocumentTypeConfiguration::TYPE_ID_E);
        foreach ($allType as $type) {
            if (! in_array($type, array_keys(EntiteSQL::getAllType()))) {
                $this->errorList[] = "action:*:rule:type_id_e:<b>$type</b></b> n'est pas un type d'entité du système";
            }
        }
    }

    private function validateActionClass(array $typeDefinition): void
    {
        $allAction = $this->getValues($typeDefinition, DocumentTypeConfiguration::ACTION);
        foreach ($allAction as $actionName => $action) {
            if (empty($action[DocumentTypeConfiguration::ACTION_CLASS])) {
                continue;
            }
            if (!class_exists($action[DocumentTypeConfiguration::ACTION_CLASS])) {
                $this->errorList[] = sprintf(
                    "action:%s:action-class:<b>%s</b> n'est pas disponible sur le système",
                    $actionName,
                    $action['action-class']
                );
            } elseif (!is_subclass_of($action[DocumentTypeConfiguration::ACTION_CLASS], ActionExecutor::class)) {
                $this->errorList[] = sprintf(
                    "action:%s:action-class:<b>%s</b> n'étend pas %s",
                    $actionName,
                    $action[DocumentTypeConfiguration::ACTION_CLASS],
                    ActionExecutor::class,
                );
            }
        }
    }

    private function validateChamps(array $typeDefinition, $keyName): void
    {
        $allChamps = $this->getValues($typeDefinition, $keyName);
        foreach ($allChamps as $champs) {
            if (
                in_array($champs, array_column(SearchField::cases(), 'value'))
                && $keyName === DocumentTypeConfiguration::CHAMPS_RECHERCHE_AVANCEE
                || in_array($champs, array_column(DisplayedField::cases(), 'value'))
                && $keyName === DocumentTypeConfiguration::CHAMPS_AFFICHES
            ) {
                continue;
            }
            if (in_array($champs, $this->allFormulaireElements)) {
                continue;
            }
            $this->errorList[] = "$keyName:<b>$champs</b> n'est pas une valeur par défaut "
                . 'ou un élément indexé du formulaire';
        }
    }

    private function validateActionConnecteurType(array $typeDefinition): void
    {
        if (!empty($typeDefinition[DocumentTypeConfiguration::ACTION])) {
            $allActionKeys = array_keys($this->getValues($typeDefinition, DocumentTypeConfiguration::ACTION));

            foreach ($typeDefinition[DocumentTypeConfiguration::ACTION] as $actionName => $actionProperties) {
                if (empty($actionProperties[DocumentTypeConfiguration::CONNECTEUR_TYPE])) {
                    continue;
                }
                if (
                    !in_array(
                        $actionProperties[DocumentTypeConfiguration::CONNECTEUR_TYPE],
                        $this->connecteurDefinitionFiles->getAllType()
                    )
                ) {
                    $this->errorList[] = "action:<b>$actionName</b>:connecteur-type:" .
                        "<b>{$actionProperties[DocumentTypeConfiguration::CONNECTEUR_TYPE]}</b> "
                        . "n'est pas un connecteur du système";
                }
                if (empty($actionProperties[DocumentTypeConfiguration::CONNECTEUR_TYPE_ACTION])) {
                    continue;
                }
                if (
                    !is_subclass_of(
                        $actionProperties[DocumentTypeConfiguration::CONNECTEUR_TYPE_ACTION],
                        ActionExecutor::class
                    )
                ) {
                    $this->errorList[] = "action:<b>$actionName</b>:connecteur-type-action:" .
                        "<b>{$actionProperties[DocumentTypeConfiguration::CONNECTEUR_TYPE_ACTION]}</b> "
                        . "n'est pas une classe d'action du système";
                }

                if (empty($actionProperties[DocumentTypeConfiguration::CONNECTEUR_TYPE_MAPPING])) {
                    continue;
                }

                foreach (
                    $actionProperties[DocumentTypeConfiguration::CONNECTEUR_TYPE_MAPPING] as $key => $elementName
                ) {
                    $elementName = $this->canonicalizeValue($elementName);
                    if (
                        !in_array($elementName, $this->allFormulaireElements)
                        && !in_array($elementName, $allActionKeys)
                    ) {
                        $this->errorList[] =  "action:<b>$actionName</b>:connecteur-type-mapping:$key:" .
                            "<b>$elementName</b> n'est pas un élément du formulaire";
                    }
                }
            }
        }
    }
}
