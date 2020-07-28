<?php

require_once __DIR__ . "/lib/TransformationGeneriqueDefinition.class.php";

class TransformationGenerique extends TransformationConnecteur
{
    /**
     * @var DonneesFormulaire
     */
    private $connecteurConfig;

    private $transformationGeneriqueDefinition;

    public function __construct(TransformationGeneriqueDefinition $transformationGeneriqueDefinition)
    {
        $this->transformationGeneriqueDefinition = $transformationGeneriqueDefinition;
    }

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire)
    {
        $this->connecteurConfig = $donneesFormulaire;
    }

    /**
     * @param DonneesFormulaire $donneesFormulaire
     * @param array $utilisateur_info
     */
    public function transform(DonneesFormulaire $donneesFormulaire, array $utilisateur_info): void
    {
        $result = $this->getNewValue();
        foreach ($result as $id => $value) {
            $donneesFormulaire->setData($id, $value);
        }
    }

    public function testTransform(): string
    {
        $result = $this->getNewValue();
        return json_encode($result);
    }

    /**
     * @return array
     */
    private function getNewValue(): array
    {
        $transformation_data = $this->transformationGeneriqueDefinition->getData($this->connecteurConfig);

        foreach ($transformation_data as $element_id => $expression) {
            $transformation_data[$element_id] = $expression;
        }
        return $transformation_data;
    }
}
