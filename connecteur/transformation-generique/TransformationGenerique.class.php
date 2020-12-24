<?php

use Pastell\Service\SimpleTwigRenderer;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;

require_once __DIR__ . "/lib/TransformationGeneriqueDefinition.class.php";

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
     * @throws LoaderError
     * @throws SyntaxError
     */
    public function transform(DonneesFormulaire $donneesFormulaire, array $utilisateur_info): void
    {
        $result = $this->getNewValue($donneesFormulaire);
        foreach ($result as $id => $value) {
            $donneesFormulaire->setData($id, $value);
        }
    }

    /**
     * @param DonneesFormulaire $donneesFormulaire
     * @return string
     * @throws LoaderError
     * @throws SyntaxError
     */
    public function testTransform(DonneesFormulaire $donneesFormulaire): string
    {
        $result = $this->getNewValue($donneesFormulaire);
        return json_encode($result);
    }

    /**
     * @param DonneesFormulaire $donneesFormulaire
     * @return array
     * @throws LoaderError
     * @throws SyntaxError
     */
    private function getNewValue(DonneesFormulaire $donneesFormulaire): array
    {
        $transformation_data = $this->transformationGeneriqueDefinition->getData($this->connecteurConfig);

        foreach ($transformation_data as $element_id => $expression) {
            try {
                $transformation_data[$element_id] = $this->simpleTwigRenderer->render(
                    $expression,
                    $donneesFormulaire
                );
            } catch (Exception $e) {
                throw new UnrecoverableException("Erreur lors de la génération de $element_id : " . $e->getMessage());
            }
        }
        return $transformation_data;
    }
}
