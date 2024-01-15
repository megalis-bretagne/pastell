<?php

use Pastell\Service\SimpleTwigRenderer;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;

class TransformationGenerique extends TransformationConnecteur
{
    /**
     * @var DonneesFormulaire
     */
    private $connecteurConfig;

    private $transformationGeneriqueDefinition;
    private $simpleTwigRenderer;
    private $entiteSQL;
    private $documentActionSQL;

    public static function getPastellMetadata(): array
    {
        return [
            'pa_entity_id_e' => "Identifiant numérique de l'entité Pastell dans laquelle le document a été créé",
            'pa_entity_name' => "Nom de l'entité Pastell dans laquelle le document a été créé",
            'pa_entity_siren' => "SIREN de l'entité Pastell dans laquelle le document a été créé",
            'pa_creator_lastname' => "Nom de l'utilisateur ayant créé le document",
            'pa_creator_firstname' => "Prénom de l'utilisateur ayant créé le document",
            'pa_creator_email' => "Email de l'utilisateur ayant créé le document",
            'pa_creator_login' => "Login de l'utilisateur ayant créé le document",
            'pa_creator_id_u' => "Identifiant de l'utilisateur ayant créé le document",
            'pa_document_creation_date' => "Date de création du dossier",
            'pa_document_id_d' => "Identifiant du dossier",
        ];
    }

    public function __construct(
        TransformationGeneriqueDefinition $transformationGeneriqueDefinition,
        SimpleTwigRenderer $simpleTwigRenderer,
        EntiteSQL $entiteSQL,
        DocumentActionSQL $documentActionSQL
    ) {
        $this->transformationGeneriqueDefinition = $transformationGeneriqueDefinition;
        $this->simpleTwigRenderer  = $simpleTwigRenderer;
        $this->entiteSQL = $entiteSQL;
        $this->documentActionSQL = $documentActionSQL;
    }

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire)
    {
        $this->connecteurConfig = $donneesFormulaire;
    }

    /**
     * @param DonneesFormulaire $donneesFormulaire
     * @return array
     * @throws LoaderError
     * @throws SyntaxError
     */
    public function transform(DonneesFormulaire $donneesFormulaire): array
    {
        $result = $this->getNewValue($donneesFormulaire);
        foreach ($result as $id => $value) {
            $donneesFormulaire->setData($id, $value);
        }
        return $result;
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
     * @throws UnrecoverableException
     */
    private function getNewValue(DonneesFormulaire $donneesFormulaire): array
    {
        $connecteur_info = $this->getConnecteurInfo();
        $other_metadata = [
            'pa_entity_id_e' => $connecteur_info['id_e'],
        ];
        $entite_info = $this->entiteSQL->getInfo($connecteur_info['id_e']);

        $other_metadata['pa_entity_name'] = $entite_info['denomination'];
        $other_metadata['pa_entity_siren'] = $entite_info['siren'];

        if ($donneesFormulaire->id_d) {
            $user_info = $this->documentActionSQL->getCreator($donneesFormulaire->id_d);
            $other_metadata['pa_creator_lastname'] = $user_info['nom'] ?? '';
            $other_metadata['pa_creator_firstname'] = $user_info['prenom'] ?? '';
            $other_metadata['pa_creator_email'] = $user_info['email'] ?? '';
            $other_metadata['pa_creator_login'] = $user_info['login'] ?? '';
            $other_metadata['pa_creator_id_u'] = $user_info['id_u'] ?? '';
            $other_metadata['pa_document_creation_date'] = $user_info['date'] ?? '';
            $other_metadata['pa_document_id_d'] = $user_info['id_d'] ?? '';
        }

        $transformation_data = $this->transformationGeneriqueDefinition->getData($this->connecteurConfig);

        foreach ($transformation_data as $element_id => $expression) {
            try {
                $transformation_data[$element_id] = trim($this->simpleTwigRenderer->render(
                    $expression,
                    $donneesFormulaire,
                    $other_metadata
                ));
            } catch (Exception $e) {
                throw new UnrecoverableException(
                    "Erreur lors de la transformation pour générer l'élement <b>$element_id</b> :
                        <br/><br/> " . $e->getMessage()
                );
            }
        }
        return $transformation_data;
    }
}
