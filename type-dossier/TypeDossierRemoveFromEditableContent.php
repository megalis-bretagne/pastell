<?php

trait TypeDossierRemoveFromEditableContent
{
    private function removeFromEditableContent(array $elementIdToRemove, array &$result): void
    {
        foreach ($result[DocumentType::ACTION] as $id_element => $properties) {
            if (isset($properties['editable-content'])) {
                $result[DocumentType::ACTION][$id_element]['editable-content'] =
                    array_values(array_diff($properties['editable-content'], $elementIdToRemove));
            }
        }
    }
}
