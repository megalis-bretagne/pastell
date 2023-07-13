<?php

declare(strict_types=1);

namespace Pastell\Configuration\Validators;

use Pastell\Configuration\DisplayedField;
use Pastell\Configuration\DocumentTypeValidation;
use Pastell\Configuration\ModuleElement;
use Pastell\Configuration\SearchField;

class ChampsValidator implements ValidatorInterface
{
    private array $errors = [];
    private array $fieldKey;

    public function __construct(
        private readonly DocumentTypeValidation $documentTypeValidation,
    ) {
        $this->fieldKey = [
            ModuleElement::CHAMPS_RECHERCHE_AVANCEE->value,
            ModuleElement::CHAMPS_AFFICHES->value,
        ];
    }
    public function validate(array $typeDefinition): bool
    {
        $this->errors = [];
        foreach ($this->fieldKey as $key) {
            $allChamps = $typeDefinition[$key];
            foreach ($allChamps as $champs) {
                if (
                    in_array($champs, array_column(SearchField::cases(), 'value'))
                    && $key === ModuleElement::CHAMPS_RECHERCHE_AVANCEE->value
                    || in_array($champs, array_column(DisplayedField::cases(), 'value'))
                    && $key === ModuleElement::CHAMPS_AFFICHES->value
                ) {
                    continue;
                }
                if (in_array($champs, $this->documentTypeValidation->getFormulaireElements())) {
                    continue;
                }
                $this->errors[] = "$key:<b>$champs</b> n'est pas une valeur par défaut "
                    . 'ou un élément indexé du formulaire';
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
