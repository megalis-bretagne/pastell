<?php

declare(strict_types=1);

namespace Pastell\Configuration\Validators;

use Pastell\Configuration\FormulaireElement;
use Pastell\Configuration\ModuleElement;

class OneTitleValidator implements ValidatorInterface
{
    private array $errors = [];
    public function validate(array $typeDefinition): bool
    {
        $this->errors = [];
        if (!empty($typeDefinition[ModuleElement::FORMULAIRE->value])) {
            $title = [];
            foreach ($typeDefinition[ModuleElement::FORMULAIRE->value] as $onglet => $formulaireProperties) {
                foreach ($formulaireProperties as $elementName => $elementProperties) {
                    if (isset($elementProperties[FormulaireElement::TITLE->value])) {
                        $title[] = $elementName;
                    }
                }
            }
            if (count($title) > 1) {
                $this->errors[] = 'Plusieurs éléments trouvés avec la propriété « <b>title</b> » : '
                    . implode(',', $title);
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
