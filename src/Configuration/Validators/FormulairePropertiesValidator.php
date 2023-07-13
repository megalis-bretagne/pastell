<?php

declare(strict_types=1);

namespace Pastell\Configuration\Validators;

use Pastell\Configuration\DocumentTypeValidation;
use Pastell\Configuration\FormulaireElement;

class FormulairePropertiesValidator implements ValidatorInterface
{
    private array $errors = [];
    private array $properties = [];

    public function __construct(
        private readonly DocumentTypeValidation $documentTypeValidation,
    ) {
        $properties = [
            FormulaireElement::CHOICE_ACTION->value,
            FormulaireElement::ONCHANGE->value,
        ];
    }

    public function validate(array $typeDefinition): bool
    {
        $this->errors = [];
        foreach ($this->properties as $property) {
            $propertyList = $this->documentTypeValidation->getFormulairePropertiesValue(
                $typeDefinition,
                $property,
            );
            foreach ($propertyList as $action) {
                if (!in_array($action, $this->documentTypeValidation->getAllPossibleAction($typeDefinition))) {
                    $this->errors[] = "formulaire:xx:<b>$action</b> n'est pas une clé de <b>action</b>";
                }
            }
            if (count($this->errors) > 0) {
                return false;
            }
        }
        return true;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
