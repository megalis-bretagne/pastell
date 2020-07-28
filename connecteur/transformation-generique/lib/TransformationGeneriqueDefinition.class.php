<?php

class TransformationGeneriqueDefinition
{
    private const ELEMENT_ID = 'definition';

    /**
     * @param DonneesFormulaire $donneesFormulaire
     * @return array
     */
    public function getData(DonneesFormulaire $donneesFormulaire): array
    {
        $file_content = $donneesFormulaire->getFileContent(self::ELEMENT_ID);
        if (! $file_content) {
            return [];
        }
        return json_decode($file_content, true);
    }

    /**
     * @param DonneesFormulaire $donneesFormulaire
     * @param array $data_definition
     * @throws Exception
     */
    public function setTransformation(DonneesFormulaire $donneesFormulaire, array $data_definition): void
    {
        $file_content = json_encode($data_definition);
        $donneesFormulaire->addFileFromData(self::ELEMENT_ID, "definition.json", $file_content);
    }
}
