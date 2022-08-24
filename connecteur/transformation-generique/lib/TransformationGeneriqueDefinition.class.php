<?php

class TransformationGeneriqueDefinition
{
    private const FILE_FIELD_ID = 'definition';
    public const ELEMENT_ID_MAX_LENGTH = 64;
    public const ELEMENT_ID_REGEXP = "^[0-9a-z_]+$";

    /**
     * @param DonneesFormulaire $donneesFormulaire
     * @return array
     */
    public function getData(DonneesFormulaire $donneesFormulaire): array
    {
        $file_content = $donneesFormulaire->getFileContent(self::FILE_FIELD_ID);
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
        $donneesFormulaire->addFileFromData(self::FILE_FIELD_ID, "definition.json", $file_content);
    }

    /**
     * @param $element_id
     * @throws Exception
     */
    public function checkElementId($element_id)
    {
        if (!preg_match("#" . self::ELEMENT_ID_REGEXP . "#", $element_id)) {
            throw new Exception(
                "L'identifiant de l'élément « " . get_hecho(
                    $element_id
                ) . " » ne respecte pas l'expression rationnelle : " . self::ELEMENT_ID_REGEXP
            );
        }
        if (strlen($element_id) > self::ELEMENT_ID_MAX_LENGTH) {
            throw new Exception(
                "L'identifiant de l'élément « " . get_hecho(
                    $element_id
                ) . " » ne doit pas dépasser " . self::ELEMENT_ID_MAX_LENGTH . " caractères"
            );
        }
    }
}
