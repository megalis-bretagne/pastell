<?php

declare(strict_types=1);

namespace Pastell\Service\Document;

use DonneesFormulaire;
use DonneesFormulaireFactory;
use Exception;
use Pastell\Service\SimpleTwigRenderer;
use Pastell\Validator\ElementIdValidator;
use UnrecoverableException;

class DocumentTransformService
{
    public function __construct(
        private readonly SimpleTwigRenderer $simpleTwigRenderer,
        private readonly DocumentPastellMetadataService $documentPastellMetadataService,
        private readonly DonneesFormulaireFactory $donneesFormulaireFactory,
        private readonly ElementIdValidator $elementIdValidator,
    ) {
    }

    /**
     * @throws UnrecoverableException
     */
    public function transform(
        DonneesFormulaire $donneesFormulaire,
        array $transformationData = []
    ): array {
        $result = $this->getNewValue($donneesFormulaire, $transformationData);
        foreach ($result as $id => $value) {
            $donneesFormulaire->setData($id, $value);
        }
        return $result;
    }

    /**
     * @throws UnrecoverableException
     */
    public function getNewValue(DonneesFormulaire $donneesFormulaire, array $transformationData = []): array
    {
        $otherMetadata = $this->documentPastellMetadataService->getMetadataPastellByDocument($donneesFormulaire->id_d);
        foreach ($transformationData as $elementId => $expression) {
            try {
                $transformationData[$elementId] = $this->simpleTwigRenderer->render(
                    (string)$expression,
                    $donneesFormulaire,
                    $otherMetadata
                );
            } catch (Exception $e) {
                throw new UnrecoverableException(
                    "Erreur lors de la transformation pour générer l'élement <b>$elementId</b> :
                        <br/><br/> " . $e->getMessage()
                );
            }
        }
        return $transformationData;
    }

    /**
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function validateTransformationData(array $transformationData): bool
    {
        foreach ($transformationData as $elementId => $expressionTwig) {
            $this->elementIdValidator->validate($elementId);
        }
        $this->getNewValue(
            $this->donneesFormulaireFactory->getNonPersistingDonneesFormulaire(),
            $transformationData
        );
        return true;
    }
}
