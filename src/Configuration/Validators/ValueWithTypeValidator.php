<?php

declare(strict_types=1);

namespace Pastell\Configuration\Validators;

use Pastell\Configuration\ElementType;
use Pastell\Configuration\FormulaireElement;
use Pastell\Configuration\ModuleElement;
use Pastell\Configuration\SearchField;

class ValueWithTypeValidator implements ValidatorInterface
{
    private array $errors;

    public function validate(array $typeDefinition): bool
    {
        $this->errors = [];
        $formulaireElements = $typeDefinition[ModuleElement::FORMULAIRE->value];
        foreach ($formulaireElements as $onglet => $element) {
            foreach ($element as $name => $champs) {
                if (
                    count($champs[FormulaireElement::VALUE->value]) > 0
                    && $champs[SearchField::TYPE->value] !== ElementType::SELECT->value
                ) {
                    $this->errors[] =
                        "La propriété <b>value</b> pour <b>$onglet:$name</b> "
                        . 'est réservé pour les éléments de type <b>select</b>';
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
