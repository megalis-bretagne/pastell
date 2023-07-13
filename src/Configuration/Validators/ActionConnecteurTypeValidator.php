<?php

declare(strict_types=1);

namespace Pastell\Configuration\Validators;

use ActionExecutor;
use ConnecteurDefinitionFiles;
use Pastell\Configuration\ActionElement;
use Pastell\Configuration\DocumentTypeValidation;
use Pastell\Configuration\ModuleElement;

class ActionConnecteurTypeValidator implements ValidatorInterface
{
    private array $errors = [];

    public function __construct(
        private readonly DocumentTypeValidation $documentTypeValidation,
        private readonly ConnecteurDefinitionFiles $connecteurDefinitionFiles,
    ) {
    }

    public function validate(array $typeDefinition): bool
    {
        $this->errors = [];
        if (!empty($typeDefinition[ModuleElement::ACTION->value])) {
            $allActionKeys = array_keys($typeDefinition[ModuleElement::ACTION->value]);

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
                    $this->errors[] = "action:<b>$actionName</b>:connecteur-type:" .
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
                    $this->errors[] = "action:<b>$actionName</b>:connecteur-type-action:" .
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
                        !in_array($elementName, $this->documentTypeValidation->getFormulaireElements())
                        && !in_array($elementName, $allActionKeys)
                    ) {
                        $this->errors[] =  "action:<b>$actionName</b>:connecteur-type-mapping:$key:" .
                            "<b>$elementName</b> n'est pas un élément du formulaire";
                    }
                }
            }
        }
        if (count($this->errors) > 0) {
            return false;
        }
        return true;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
