<?php

declare(strict_types=1);

namespace Pastell\Configuration\Validators;

use Pastell\Configuration\ActionElement;
use Pastell\Configuration\DocumentTypeValidation;
use Pastell\Configuration\ModuleElement;

class EditableContentValidator implements ValidatorInterface
{
    private array $errors;

    public function __construct(
        private readonly DocumentTypeValidation $documentTypeValidation,
    ) {
    }

    public function validate(array $typeDefinition): bool
    {
        $this->errors = [];
        $editableContentList = [];
        $allAction = $typeDefinition[ModuleElement::ACTION->value];
        foreach ($allAction as $action) {
            if (empty($action[ActionElement::EDITABLE_CONTENT->value])) {
                continue;
            }
            $editableContentList = array_merge(
                $editableContentList,
                $action[ActionElement::EDITABLE_CONTENT->value]
            );
        }
        foreach ($editableContentList as $editableContent) {
            if (! in_array($editableContent, $this->documentTypeValidation->getFormulaireElements())) {
                $this->errors[] = "formulaire:xx:yy:editable-content:<b>$editableContent</b> "
                    . "n'est pas défini dans le formulaire";
            }
        }
        return count($this->errors) === 0;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
