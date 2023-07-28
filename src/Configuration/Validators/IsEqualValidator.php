<?php

declare(strict_types=1);

namespace Pastell\Configuration\Validators;

use Pastell\Configuration\DocumentTypeValidation;
use Pastell\Configuration\FormulaireElement;

class IsEqualValidator implements ValidatorInterface
{
    private array $errors;

    public function __construct(
        private readonly DocumentTypeValidation $documentTypeValidation,
    ) {
    }

    public function validate(array $typeDefinition): bool
    {
        $this->errors = [];
        $isEqualList = $this->documentTypeValidation->getFormulairePropertiesValue(
            $typeDefinition,
            FormulaireElement::IS_EQUAL->value,
        );
        foreach ($isEqualList as $isEqual) {
            if (! in_array($isEqual, $this->documentTypeValidation->getFormulaireElements())) {
                $this->errors[] = "formulaire:xx:yy:is_equal:<b>$isEqual</b> n'est pas défini dans le formulaire";
            }
        }
        return count($this->errors) === 0;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
