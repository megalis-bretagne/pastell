<?php

require_once __DIR__ . "/lib/TransformationGeneriqueDefinition.class.php";
require_once __DIR__ . "/lib/SimpleTwigRenderer.class.php";

class TransformationGenerique extends TransformationConnecteur
{
    /**
     * @var DonneesFormulaire
     */
    private $connecteurConfig;

    private $transformationGeneriqueDefinition;
    private $simpleTwigRenderer;

    public function __construct(
        TransformationGeneriqueDefinition $transformationGeneriqueDefinition,
        SimpleTwigRenderer $simpleTwigRenderer
    ) {
        $this->transformationGeneriqueDefinition = $transformationGeneriqueDefinition;
        $this->simpleTwigRenderer  = $simpleTwigRenderer;
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
        $result = $this->getNewValue($donneesFormulaire);
        foreach ($result as $id => $value) {
            $donneesFormulaire->setData($id, $value);
        }
    }

    public function testTransform(DonneesFormulaire $donneesFormulaire): string
    {
        $result = $this->getNewValue($donneesFormulaire);
        return json_encode($result);
    }

    /**
     * @return array
     */
    private function getNewValue(DonneesFormulaire $donneesFormulaire): array
    {
        $transformation_data = $this->transformationGeneriqueDefinition->getData($this->connecteurConfig);

        foreach ($transformation_data as $element_id => $expression) {
            $transformation_data[$element_id] = $this->simpleTwigRenderer->render(
                $expression,
                $donneesFormulaire
            );
        }
        return $transformation_data;
    }
}
