<?php

declare(strict_types=1);

namespace Pastell\Configuration\Validators;

use Pastell\Configuration\DocumentTypeValidation;
use Pastell\Configuration\FormulaireElement;
use Pastell\Configuration\ModuleElement;

class DependValidator implements ValidatorInterface
{
    private array $errors;

    public function __construct(
        private readonly DocumentTypeValidation $documentTypeValidation,
    ) {
    }

    public function validate(array $typeDefinition): bool
    {
        $this->errors = [];
        $allFormulaire = $typeDefinition[ModuleElement::FORMULAIRE->value];
        foreach ($allFormulaire as $onglet => $elementList) {
            foreach ($elementList as $name => $property) {
                if (empty($property[FormulaireElement::DEPEND->value])) {
                    continue;
                }
                if (! in_array($property['depend'], $this->documentTypeValidation->getFormulaireElements())) {
                    $this->errors[] =
                        "<b>formulaire:$onglet:$name:depend:{$property[FormulaireElement::DEPEND->value]}</b> "
                        . "n'est pas un élément du formulaire";
                }
            }
        }
        return count($this->errors) === 0;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
