<?php

declare(strict_types=1);

namespace Pastell\Configuration\Validators;

use EntiteSQL;
use Pastell\Configuration\ActionElement;
use Pastell\Configuration\ModuleElement;

class ActionSelectionValidator implements ValidatorInterface
{
    private array $errors;

    public function validate(array $typeDefinition): bool
    {
        $this->errors = [];
        $allAction = $typeDefinition[ModuleElement::ACTION->value];
        foreach ($allAction as $actionName => $action) {
            if (empty($action[ActionElement::ACTION_SELECTION->value])) {
                continue;
            }
            if (!array_key_exists($action[ActionElement::ACTION_SELECTION->value], EntiteSQL::getAllType())) {
                $this->errors[] = "action:$actionName:action-selection:<b>{$action['action-selection']}</b> "
                    . "n'est pas un type d'entité du système";
            }
        }
        return count($this->errors) === 0;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
