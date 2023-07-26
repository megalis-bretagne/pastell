<?php

declare(strict_types=1);

namespace Pastell\Configuration\Validators;

use Pastell\Configuration\ActionElement;
use Pastell\Configuration\DocumentTypeValidation;
use Pastell\Configuration\ModuleElement;

class ActionPropertiesValidator implements ValidatorInterface
{
    private array $errors;
    private array $actionType;

    public function __construct(
        private readonly DocumentTypeValidation $documentTypeValidation,
    ) {
        $this->actionType = [
            ActionElement::ACTION_AUTOMATIQUE->value,
            ActionElement::ACCUSE_DE_RECEPTION_ACTION->value,
        ];
    }

    public function validate(array $typeDefinition): bool
    {
        $this->errors = [];
        foreach ($this->actionType as $actionType) {
            $actionList = $this->getActionPropertiesValue($typeDefinition, $actionType);
            foreach ($actionList as $action) {
                if (! in_array($action, $this->documentTypeValidation->getAllPossibleAction($typeDefinition))) {
                    $this->errors[] = "formulaire:xx:<b>$action</b> n'est pas une clé de <b>action</b>";
                }
            }
        }
        return count($this->errors) === 0;
    }

    public function getActionPropertiesValue(array $typeDefinition, string $property): array
    {
        if (empty($typeDefinition[ModuleElement::ACTION->value])) {
            return [];
        }
        $propertiesList = [];
        foreach ($typeDefinition[ModuleElement::ACTION->value] as $properties) {
            if (!empty($properties[$property])) {
                $propertiesList[] = $properties[$property];
            }
        }
        return $propertiesList;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
