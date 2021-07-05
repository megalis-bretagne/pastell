<?php

use Pastell\Service\Document\DocumentTitre;

class TransformationTransform extends ConnecteurTypeActionExecutor
{

    /**
     * @var string
     */
    public const TRANSFORMATION_ERROR_STATE = 'transformation-error';

    /**
     * @return bool
     * @throws NotFoundException
     * @throws UnrecoverableException
     */
    public function go(): bool
    {
        $donneesFormulaire = $this->getDonneesFormulaire();
        /** @var TransformationConnecteur $transformationConnecteur */
        $transformationConnecteur = $this->getConnecteur("transformation");

        $modified_fields = $transformationConnecteur->transform($donneesFormulaire);

        try {
            $this->addOnChange($modified_fields);
        } catch (Exception $e) {
            $transformationError = $this->getMappingValue(self::TRANSFORMATION_ERROR_STATE);
            $this->changeAction($transformationError, $e->getMessage());
            $this->notify(
                $transformationError,
                $this->type,
                "Erreur lors de la transformation: " . $e->getMessage()
            );
            return false;
        }

        $documentTitre = $this->objectInstancier->getInstance(DocumentTitre::class);
        $documentTitre->update($this->id_d);

        $message = "Transformation terminée";
        $this->addActionOK($message);
        $this->notify($this->action, $this->type, $message);
        $this->setLastMessage($message);
        return true;
    }

    /**
     * @param array $modified_fields
     * @throws NotFoundException
     * @throws UnrecoverableException
     */
    private function addOnChange(array $modified_fields = []): void
    {
        $donneesFormulaire = $this->objectInstancier->getInstance(DonneesFormulaireFactory::class)->get($this->id_d);
        foreach ($modified_fields as $id => $value) {
            $field = $donneesFormulaire->getFieldData($id)->getField();
            if ($field->getOnChange()) {
                $actionExecutorFactory = $this->objectInstancier->getInstance(ActionExecutorFactory::class);
                $actionExecutorFactory->executeOnDocumentCritical($this->id_e, $this->id_u, $this->id_d, $field->getOnChange());
            }
        }

        $donneesFormulaire = $this->objectInstancier->getInstance(DonneesFormulaireFactory::class)->get($this->id_d);
        if (! $donneesFormulaire->isValidable()) {
            throw new UnrecoverableException(
                "[transformation] Le dossier n'est pas valide : " . $donneesFormulaire->getLastError()
            );
        }
    }
}
