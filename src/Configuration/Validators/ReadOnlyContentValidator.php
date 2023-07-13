<?php

namespace Pastell\Configuration\Validators;

use Pastell\Configuration\DocumentTypeValidation;
use Pastell\Configuration\FormulaireElement;

class ReadOnlyContentValidator implements ValidatorInterface
{
    private array $errors = [];

    public function __construct(
        private readonly DocumentTypeValidation $documentTypeValidation,
    ) {
    }

    public function validate(array $typeDefinition): bool
    {
        $this->errors = [];
        $readOnlyContentList = $this->documentTypeValidation->getFormulairePropertiesValue(
            $typeDefinition,
            FormulaireElement::READ_ONLY_CONTENT->value,
        );
        foreach ($readOnlyContentList as $readOnlyContentElement) {
            foreach ($readOnlyContentElement as $name => $prop) {
                if (! in_array($name, $this->documentTypeValidation->getFormulaireElements())) {
                    $this->errors[] = "formulaire:xx:yy:read-only-content:<b>$name</b> "
                        . "n'est pas défini dans le formulaire";
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
