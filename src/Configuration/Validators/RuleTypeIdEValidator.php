<?php

declare(strict_types=1);

namespace Pastell\Configuration\Validators;

use EntiteSQL;
use Pastell\Configuration\ActionElement;
use Pastell\Configuration\DocumentTypeValidation;

class RuleTypeIdEValidator implements ValidatorInterface
{
    private array $errors = [];

    public function __construct(
        private readonly DocumentTypeValidation $documentTypeValidation,
    ) {
    }

    public function validate(array $typeDefinition): bool
    {
        $this->errors = [];
        $allRule = $this->documentTypeValidation->getRuleList($typeDefinition);
        $allType = [];
        foreach ($allRule as $rule) {
            $allType = array_merge(
                $allType,
                $this->documentTypeValidation->getElementRuleValue($rule, ActionElement::TYPE_ID_E->value)
            );
        }
        foreach ($allType as $type) {
            if (! in_array($type, array_keys(EntiteSQL::getAllType()))) {
                $this->errors[] = "action:*:rule:type_id_e:<b>$type</b></b> n'est pas un type d'entité du système";
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
