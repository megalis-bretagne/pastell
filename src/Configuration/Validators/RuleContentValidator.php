<?php

declare(strict_types=1);

namespace Pastell\Configuration\Validators;

use Pastell\Configuration\DocumentTypeValidation;
use Pastell\Configuration\RuleElement;

class RuleContentValidator implements ValidatorInterface
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
        $allContent = [];
        foreach ($allRule as $rule) {
            $allContent = array_merge(
                $allContent,
                $this->documentTypeValidation->getElementRuleValue($rule, RuleElement::CONTENT->value)
            );
        }
        foreach ($allContent as $key => $content) {
            if (! in_array($key, $this->documentTypeValidation->getFormulaireElements())) {
                $this->errors[] = "action:xx:rule:content:<b>$key</b> n'est pas défini dans le formulaire";
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
