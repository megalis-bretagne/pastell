<?php

class TransformationTransform extends ConnecteurTypeActionExecutor
{

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

        $utilisateur_info = $this->objectInstancier
            ->getInstance(DocumentActionEntite::class)
            ->getCreatorOfDocument(
                $this->id_e,
                $this->id_d
            );

        $transformationConnecteur->transform($donneesFormulaire, $utilisateur_info);

        $this->addOnChange();

        $message = "Transformation terminée";
        $this->addActionOK($message);
        $this->notify($this->action, $this->type, $message);
        $this->setLastMessage($message);
        return true;
    }

    /**
     * @throws NotFoundException
     * @throws UnrecoverableException
     */
    private function addOnChange()
    {
        foreach ($this->getDonneesFormulaire()->getFormulaire()->getAllFields() as $field) {
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
