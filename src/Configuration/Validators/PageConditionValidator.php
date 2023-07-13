<?php

declare(strict_types=1);

namespace Pastell\Configuration\Validators;

use Pastell\Configuration\DocumentTypeValidation;
use Pastell\Configuration\ModuleElement;

class PageConditionValidator implements ValidatorInterface
{
    private array $errors = [];

    public function __construct(
        private readonly DocumentTypeValidation $documentTypeValidation,
    ) {
    }

    public function validate(array $typeDefinition): bool
    {
        $this->errors = [];
        $allPageConditionKeys = array_keys($typeDefinition[ModuleElement::PAGE_CONDITION->value]);
        $allFormulaireKeys = array_keys($typeDefinition[ModuleElement::FORMULAIRE->value]);
        foreach ($allPageConditionKeys as $pageCondition) {
            if (!in_array($pageCondition, $allFormulaireKeys)) {
                $this->errors[] = "page-condition:<b>$pageCondition</b> n'est pas une clé de <b>formulaire</b>";
                continue;
            }
            foreach ($typeDefinition[ModuleElement::PAGE_CONDITION->value][$pageCondition] as $element => $test) {
                if (!in_array($element, $this->documentTypeValidation->getFormulaireElements())) {
                    $this->errors[] = "page-condition:<b>$pageCondition:$element</b> n'est pas "
                        . 'un élément du <b>formulaire</b>';
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
