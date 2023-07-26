<?php

declare(strict_types=1);

namespace Pastell\Configuration\Validators;

use ActionExecutor;
use Pastell\Configuration\ActionElement;
use Pastell\Configuration\ModuleElement;

class ActionClassValidator implements ValidatorInterface
{
    private array $errors;

    public function validate(array $typeDefinition): bool
    {
        $this->errors = [];
        $allAction = $typeDefinition[ModuleElement::ACTION->value];
        foreach ($allAction as $actionName => $action) {
            if (empty($action[ActionElement::ACTION_CLASS->value])) {
                continue;
            }
            if (!class_exists($action[ActionElement::ACTION_CLASS->value])) {
                $this->errors[] = sprintf(
                    "action:%s:action-class:<b>%s</b> n'est pas disponible sur le système",
                    $actionName,
                    $action['action-class']
                );
            } elseif (!is_subclass_of($action[ActionElement::ACTION_CLASS->value], ActionExecutor::class)) {
                $this->errors[] = sprintf(
                    "action:%s:action-class:<b>%s</b> n'étend pas %s",
                    $actionName,
                    $action[ActionElement::ACTION_CLASS->value],
                    ActionExecutor::class,
                );
            }
        }
        return count($this->errors) === 0;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
