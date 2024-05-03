<?php

class TransformationGeneriqueDefinition
{
    private const FILE_FIELD_ID = 'definition';

    /**
     * @throws JsonException
     */
    public function getData(DonneesFormulaire $donneesFormulaire): array
    {
        $file_content = $donneesFormulaire->getFileContent(self::FILE_FIELD_ID);
        if (! $file_content) {
            return [];
        }
        return json_decode($file_content, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws Exception
     */
    public function setTransformation(DonneesFormulaire $donneesFormulaire, array $data_definition): void
    {
        $file_content = json_encode($data_definition, JSON_THROW_ON_ERROR);
        $donneesFormulaire->addFileFromData(self::FILE_FIELD_ID, 'definition.json', $file_content);
    }
}
