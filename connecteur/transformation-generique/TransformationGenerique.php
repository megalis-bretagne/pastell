<?php

use Pastell\Service\Document\DocumentTransformService;

class TransformationGenerique extends TransformationConnecteur
{
    private DonneesFormulaire $connecteurConfig;

    public function __construct(
        private readonly TransformationGeneriqueDefinition $transformationGeneriqueDefinition,
        private readonly DocumentTransformService $documentTransformService
    ) {
    }

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire): void
    {
        $this->connecteurConfig = $donneesFormulaire;
    }

    /**
     * @throws UnrecoverableException
     * @throws JsonException
     */
    public function transform(DonneesFormulaire $donneesFormulaire): array
    {
        return $this->documentTransformService->transform(
            $donneesFormulaire,
            $this->transformationGeneriqueDefinition->getData($this->connecteurConfig)
        );
    }

    /**
     * @throws UnrecoverableException
     * @throws JsonException
     */
    public function testTransform(DonneesFormulaire $donneesFormulaire): string
    {
        $result = $this->documentTransformService->getNewValue(
            $donneesFormulaire,
            $this->transformationGeneriqueDefinition->getData($this->connecteurConfig)
        );
        return json_encode($result, JSON_THROW_ON_ERROR);
    }
}
