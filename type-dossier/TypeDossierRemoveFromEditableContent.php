<?php

trait TypeDossierRemoveFromEditableContent
{
    private function removeFromEditableContent(array $elementIdToRemove, array &$result): void
    {
        foreach ($result[DocumentType::ACTION] as $id_element => $properties) {
            if (isset($properties[Action::EDITABLE_CONTENT])) {
                $result[DocumentType::ACTION][$id_element][Action::EDITABLE_CONTENT] =
                    array_values(array_diff($properties[Action::EDITABLE_CONTENT], $elementIdToRemove));
            }
        }
    }
}
