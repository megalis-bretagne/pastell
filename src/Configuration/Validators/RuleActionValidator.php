<?php

declare(strict_types=1);

namespace Pastell\Configuration\Validators;

use Pastell\Configuration\DocumentTypeValidation;
use Pastell\Configuration\RuleElement;

class RuleActionValidator implements ValidatorInterface
{
    private array $errors = [];
    private array $actionType;

    public function __construct(
        private readonly DocumentTypeValidation $documentTypeValidation,
    ) {
        $this->actionType = [
            RuleElement::LAST_ACTION->value,
            RuleElement::HAS_ACTION->value,
            RuleElement::NO_ACTION->value
        ];
    }

    public function validate(array $typeDefinition): bool
    {
        $this->errors = [];
        foreach ($this->actionType as $actionType) {
            $allActionRule = [];
            if (!empty($typeDefinition['action'])) {
                $allRule = $this->documentTypeValidation->getRuleList($typeDefinition);
                foreach ($allRule as $rule) {
                    $allActionRule = array_merge(
                        $allActionRule,
                        $this->documentTypeValidation->getElementRuleValue($rule, $actionType)
                    );
                }
            }

            foreach ($allActionRule as $action) {
                if (! in_array($action, $this->documentTypeValidation->getAllPossibleAction($typeDefinition))) {
                    $this->errors[] = "formulaire:xx:<b>$action</b> n'est pas une clé de <b>action</b>";
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
