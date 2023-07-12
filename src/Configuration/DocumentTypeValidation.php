<?php

declare(strict_types=1);

namespace Pastell\Configuration;

use ActionExecutor;
use ActionPossible;
use ConnecteurDefinitionFiles;
use EntiteSQL;
use Exception;
use Pastell\Service\Pack\PackService;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;
use UnrecoverableException;

use function class_exists;
use function is_subclass_of;
use function sprintf;

class DocumentTypeValidation
{
    private array $errorList = [];
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

    /**
     * @throws UnrecoverableException
     */
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
        $this->validateRuleAction($typeDefinition, RuleElement::LAST_ACTION->value);
        $this->validateRuleAction($typeDefinition, RuleElement::NO_ACTION->value);
        $this->validateRuleAction($typeDefinition, RuleElement::HAS_ACTION->value);
        $this->validateActionProperties($typeDefinition, ActionElement::ACTION_AUTOMATIQUE->value);
        $this->validateActionProperties($typeDefinition, ActionElement::ACCUSE_DE_RECEPTION_ACTION->value);
        $this->validateEditableContent($typeDefinition);
        $this->validateDepend($typeDefinition);
        $this->validateRuleContent($typeDefinition);
        $this->validateActionSelection($typeDefinition);
        $this->validateRuleTypeIdE($typeDefinition);
        $this->validateActionClass($typeDefinition);
        $this->validateChamps($typeDefinition, ModuleElement::CHAMPS_RECHERCHE_AVANCEE->value);
        $this->validateChamps($typeDefinition, ModuleElement::CHAMPS_AFFICHES->value);
        $this->validateActionConnecteurType($typeDefinition);
        $this->validateValueWithType($typeDefinition);
        $this->validateRuleElement($typeDefinition);

        if (count($this->errorList) > 0) {
            throw new UnrecoverableException();
        }
    }

    private function getAllFormulaireElements(array $typeDefinition): array
    {
        $result = [];
        foreach ($this->getValues($typeDefinition, ModuleElement::FORMULAIRE->value) as $onglet => $elementList) {
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
        if (!empty($typeDefinition[ModuleElement::FORMULAIRE->value])) {
            $title = [];
            foreach ($typeDefinition[ModuleElement::FORMULAIRE->value] as $onglet => $formulaireProperties) {
                foreach ($formulaireProperties as $elementName => $elementProperties) {
                    if (isset($elementProperties[FormulaireElement::TITLE->value])) {
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
            ModuleElement::PAGE_CONDITION->value
        ));
        $allPageKeys = array_keys($this->getValues($typeDefinition, ModuleElement::FORMULAIRE->value));
        foreach ($allPageConditionKeys as $pageCondition) {
            if (! in_array($pageCondition, $allPageKeys)) {
                $this->errorList[] = "page-condition:<b>$pageCondition</b> n'est pas une clé de <b>formulaire</b>";
                continue;
            }
            foreach ($typeDefinition[ModuleElement::PAGE_CONDITION->value][$pageCondition] as $element => $test) {
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
            FormulaireElement::CHOICE_ACTION->value,
            ModuleElement::FORMULAIRE->value
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
            if ($element === ModuleElement::ACTION->value && !empty($properties[$property])) {
                $propertiesList[] = $properties[$property];
            }
            if ($element === ModuleElement::FORMULAIRE->value) {
                foreach ($properties as $elementName => $elementProperties) {
                    if (isset($elementProperties[$property])) {
                        $propertiesList[] = $elementProperties[$property];
                    }
                }
            }
        }
        return $propertiesList;
    }

    private function checkIsAction(array $typeDefinition, array $actionListToCheck): void
    {
        $allPossibleActionKeys = array_keys($this->getValues($typeDefinition, ModuleElement::ACTION->value));
        if (! in_array(ActionPossible::FATAL_ERROR_ACTION, $allPossibleActionKeys)) {
            $allPossibleActionKeys[] = ActionPossible::FATAL_ERROR_ACTION;
        }
        foreach ($actionListToCheck as $action) {
            if (! in_array($action, $allPossibleActionKeys)) {
                $this->errorList[] = "formulaire:xx:<b>$action</b> n'est pas une clé de <b>action</b>";
            }
        }
    }

    private function validateRestrictionPack(array $typeDefinition): void
    {
        $allRestrictionPack = $this->getValues($typeDefinition, ModuleElement::RESTRICTION_PACK->value);
        foreach ($allRestrictionPack as $restrictionPack) {
            if (!array_key_exists($restrictionPack, $this->packService->getListPack())) {
                $this->errorList[] = "restriction_pack:<b>$restrictionPack</b> "
                    . "n'est pas défini dans la liste des packs";
            }
        }
    }

    private function validateConnecteur(array $typeDefinition): void
    {
        $allConnecteur = $this->getValues($typeDefinition, ModuleElement::CONNECTEUR->value);
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
            FormulaireElement::ONCHANGE->value,
            ModuleElement::FORMULAIRE->value
        );
        $this->checkIsAction($typeDefinition, $onChangeList);
    }

    private function validateIsEqual(array $typeDefinition): void
    {
        $isEqualList = $this->getPropertiesValue(
            $typeDefinition,
            FormulaireElement::IS_EQUAL->value,
            ModuleElement::FORMULAIRE->value
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
            FormulaireElement::READ_ONLY_CONTENT->value,
            ModuleElement::FORMULAIRE->value,
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

    private function validateRuleAction($typeDefinition, string $ruleName): void
    {
        $allActionRule = [];
        if (!empty($typeDefinition['action'])) {
            $allRule = [];
            foreach ($typeDefinition[ModuleElement::ACTION->value] as $key => $action) {
                if (is_array($action) && isset($action[ActionElement::RULE->value])) {
                    $allRule[] = $action[ActionElement::RULE->value];
                }
            }
            foreach ($allRule as $rule) {
                $allActionRule = array_merge($allActionRule, $this->getElementRuleValue($rule, $ruleName));
            }
        }
        $this->checkIsAction($typeDefinition, $allActionRule);
    }

    private function getElementRuleValue(array $ruleList, string $ruleName): array
    {
        $array = [];
        foreach ($ruleList as $rulekey => $rulevalue) {
            if ($rulekey === $ruleName) {
                if (!is_array($rulevalue)) {
                    $rulevalue = [$rulevalue];
                }
                $array = array_merge($array, $rulevalue);
            }
            if (
                str_contains($rulekey, RuleElement::NO->value)
                || str_contains($rulekey, RuleElement::AND->value)
                || str_contains($rulekey, RuleElement::OR->value)
            ) {
                $array = array_merge($array, $this->getElementRuleValue($rulevalue, $ruleName));
            }
        }
        return $array;
    }

    private function validateActionProperties(array $typeDefinition, string $properties): void
    {
        $actionList = $this->getPropertiesValue($typeDefinition, $properties, ModuleElement::ACTION->value);
        $this->checkIsAction($typeDefinition, $actionList);
    }

    private function validateEditableContent(array $typeDefinition): void
    {
        $editableContentList = [];
        $allAction = $this->getValues($typeDefinition, ModuleElement::ACTION->value);
        foreach ($allAction as $action) {
            if (empty($action[ActionElement::EDITABLE_CONTENT->value])) {
                continue;
            }
            $editableContentList = array_merge(
                $editableContentList,
                $action[ActionElement::EDITABLE_CONTENT->value]
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
        $allFormulaire = $this->getValues($typeDefinition, ModuleElement::FORMULAIRE->value);
        foreach ($allFormulaire as $onglet => $elementList) {
            foreach ($elementList as $name => $property) {
                if (empty($property[FormulaireElement::DEPEND->value])) {
                    continue;
                }
                if (! in_array($property['depend'], $this->allFormulaireElements)) {
                    $this->errorList[] =
                        "<b>formulaire:$onglet:$name:depend:{$property[FormulaireElement::DEPEND->value]}</b> "
                        . "n'est pas un élément du formulaire";
                }
            }
        }
    }

    private function validateRuleContent($typeDefinition): void
    {
        $contentList = $this->getElementRuleValue($typeDefinition, RuleElement::CONTENT->value);
        foreach ($contentList as $key => $content) {
            if (! in_array($key, $this->allFormulaireElements)) {
                $this->errorList[] = "action:xx:rule:content:<b>$key</b> n'est pas défini dans le formulaire";
            }
        }
    }

    private function validateActionSelection(array $typeDefinition): void
    {
        $allAction = $this->getValues($typeDefinition, ModuleElement::ACTION->value);
        foreach ($allAction as $actionName => $action) {
            if (empty($action[ActionElement::ACTION_SELECTION->value])) {
                continue;
            }
            if (!in_array($action[ActionElement::ACTION_SELECTION->value], array_keys(EntiteSQL::getAllType()))) {
                $this->errorList[] = "action:$actionName:action-selection:<b>{$action['action-selection']}</b> "
                    . "n'est pas un type d'entité du système";
            }
        }
    }

    private function validateRuleTypeIdE($typeDefinition): void
    {
        $allType = $this->getElementRuleValue($typeDefinition, ActionElement::TYPE_ID_E->value);
        foreach ($allType as $type) {
            if (! in_array($type, array_keys(EntiteSQL::getAllType()))) {
                $this->errorList[] = "action:*:rule:type_id_e:<b>$type</b></b> n'est pas un type d'entité du système";
            }
        }
    }

    private function validateActionClass(array $typeDefinition): void
    {
        $allAction = $this->getValues($typeDefinition, ModuleElement::ACTION->value);
        foreach ($allAction as $actionName => $action) {
            if (empty($action[ActionElement::ACTION_CLASS->value])) {
                continue;
            }
            if (!class_exists($action[ActionElement::ACTION_CLASS->value])) {
                $this->errorList[] = sprintf(
                    "action:%s:action-class:<b>%s</b> n'est pas disponible sur le système",
                    $actionName,
                    $action['action-class']
                );
            } elseif (!is_subclass_of($action[ActionElement::ACTION_CLASS->value], ActionExecutor::class)) {
                $this->errorList[] = sprintf(
                    "action:%s:action-class:<b>%s</b> n'étend pas %s",
                    $actionName,
                    $action[ActionElement::ACTION_CLASS->value],
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
                && $keyName === ModuleElement::CHAMPS_RECHERCHE_AVANCEE->value
                || in_array($champs, array_column(DisplayedField::cases(), 'value'))
                && $keyName === ModuleElement::CHAMPS_AFFICHES->value
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
        if (!empty($typeDefinition[ModuleElement::ACTION->value])) {
            $allActionKeys = array_keys($this->getValues($typeDefinition, ModuleElement::ACTION->value));

            foreach ($typeDefinition[ModuleElement::ACTION->value] as $actionName => $actionProperties) {
                if (empty($actionProperties[ActionElement::CONNECTEUR_TYPE->value])) {
                    continue;
                }
                if (
                    !in_array(
                        $actionProperties[ActionElement::CONNECTEUR_TYPE->value],
                        $this->connecteurDefinitionFiles->getAllType()
                    )
                ) {
                    $this->errorList[] = "action:<b>$actionName</b>:connecteur-type:" .
                        "<b>{$actionProperties[ActionElement::CONNECTEUR_TYPE->value]}</b> "
                        . "n'est pas un connecteur du système";
                }
                if (empty($actionProperties[ActionElement::CONNECTEUR_TYPE_ACTION->value])) {
                    continue;
                }
                if (
                    !is_subclass_of(
                        $actionProperties[ActionElement::CONNECTEUR_TYPE_ACTION->value],
                        ActionExecutor::class
                    )
                ) {
                    $this->errorList[] = "action:<b>$actionName</b>:connecteur-type-action:" .
                        "<b>{$actionProperties[ActionElement::CONNECTEUR_TYPE_ACTION->value]}</b> "
                        . "n'est pas une classe d'action du système";
                }

                if (empty($actionProperties[ActionElement::CONNECTEUR_TYPE_MAPPING->value])) {
                    continue;
                }

                foreach (
                    $actionProperties[ActionElement::CONNECTEUR_TYPE_MAPPING->value] as $key => $elementName
                ) {
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

    private function validateValueWithType(array $typeDefinition): void
    {
        $formulaireElements = $this->getValues($typeDefinition, ModuleElement::FORMULAIRE->value);
        foreach ($formulaireElements as $element) {
            foreach ($element as $champs) {
                if (
                    count($champs[FormulaireElement::VALUE->value]) > 0
                    && $champs[SearchField::TYPE->value] !== ElementType::SELECT->value
                ) {
                    $this->errorList[] =
                        'La propriété <b>value</b> est réservé pour les éléments de type <b>select</b>';
                }
            }
        }
    }

    private function validateRuleElement(array $typeDefinition): void
    {
        $allAction = $typeDefinition[ModuleElement::ACTION->value];
        foreach ($allAction as $key => $action) {
            if (is_array($action) && isset($action[ActionElement::RULE->value])) {
                $this->getAllRule($action[ActionElement::RULE->value], $key . ActionElement::RULE->value);
            }
        }
    }

    private function getAllRule(array $ruleList, $path): void
    {
        foreach ($ruleList as $rulekey => $rulevalue) {
            if (
                str_contains($rulekey, RuleElement::NO->value)
                || str_contains($rulekey, RuleElement::AND->value)
                || str_contains($rulekey, RuleElement::OR->value)
            ) {
                $this->getAllRule($rulevalue, $path . ':' . $rulekey);
            }
            if (
                !in_array($rulekey, array_column(RuleElement::cases(), 'value'))
                && !(
                    str_contains($rulekey, RuleElement::NO->value)
                    || str_contains($rulekey, RuleElement::AND->value)
                    || str_contains($rulekey, RuleElement::OR->value)
                    )
            ) {
                $this->errorList[] = "<b>$path</b>: la clé <b>$rulekey</b> n'est pas attendu";
            }
        }
    }
}
