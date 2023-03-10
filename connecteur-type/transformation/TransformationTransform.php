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
     * @throws JsonException
     */
    public function go(): bool
    {
        $donneesFormulaire = $this->getDonneesFormulaire();
        /** @var TransformationConnecteur $transformationConnecteur */
        $transformationConnecteur = $this->getConnecteur("transformation");

        $transformation_file_element = $this->getMappingValue('transformation_file');
        $has_transformation_element = $this->getMappingValue('has_transformation');

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

        if (!empty($modified_fields)) {
            $donneesFormulaire->setData($has_transformation_element, true);
            $donneesFormulaire->addFileFromData(
                $transformation_file_element,
                'transformation_file.json',
                json_encode($modified_fields, JSON_THROW_ON_ERROR)
            );
        }

        $message = "Transformation terminée";
        $this->addActionOK($message);
        $this->notify($this->action, $this->type, $message);
        $this->setLastMessage($message);
        return true;
    }

    /**
     * @throws NotFoundException
     * @throws JsonException
     * @throws UnrecoverableException
     */
    private function addOnChange(array $modified_fields = []): void
    {
        $donneesFormulaire = $this->objectInstancier->getInstance(DonneesFormulaireFactory::class)->get($this->id_d);
        $actionExecutorFactory = $this->objectInstancier->getInstance(ActionExecutorFactory::class);

        foreach ($modified_fields as $id => $value) {
            $field = $donneesFormulaire->getFieldData($id)->getField();
            if ($field->getOnChange()) {
                $actionExecutorFactory->executeOnDocumentCritical(
                    $this->id_e,
                    $this->id_u,
                    $this->id_d,
                    $field->getOnChange(),
                    $this->id_destinataire,
                    $this->from_api,
                    $this->action_params,
                    $this->id_worker,
                    false
                );
            }
        }

        //FIXME: it's trash
        $actionExecutorFactory->setLastClassAction($this);
        $donneesFormulaire = $this->objectInstancier->getInstance(DonneesFormulaireFactory::class)->get($this->id_d);
        if (! $donneesFormulaire->isValidable()) {
            throw new UnrecoverableException(
                "[transformation] Le dossier n'est pas valide : " . $donneesFormulaire->getLastError()
            );
        }
    }
}
