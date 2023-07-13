<?php

declare(strict_types=1);

namespace Pastell\Configuration\Validators;

use Pastell\Configuration\DocumentTypeValidation;
use Pastell\Configuration\FormulaireElement;

class ChoiceActionValidator implements ValidatorInterface
{
    private array $errors = [];

    public function __construct(
        private readonly DocumentTypeValidation $documentTypeValidation,
    ) {
    }

    public function validate(array $typeDefinition): bool
    {
        $this->errors = [];
        $choiceActionList = $this->documentTypeValidation->getFormulairePropertiesValue(
            $typeDefinition,
            FormulaireElement::CHOICE_ACTION->value,
        );
        foreach ($choiceActionList as $action) {
            if (!in_array($action, $this->documentTypeValidation->getAllPossibleAction($typeDefinition))) {
                $this->errors[] = "formulaire:xx:<b>$action</b> n'est pas une clé de <b>action</b>";
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
