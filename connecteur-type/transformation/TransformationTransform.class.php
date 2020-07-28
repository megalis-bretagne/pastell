<?php

class TransformationTransform extends ConnecteurTypeActionExecutor
{

    /**
     * @return bool
     * @throws NotFoundException
     * @throws UnrecoverableException
     */
    public function go()
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
        $message = "Transformation terminée";
        $this->addActionOK($message);
        $this->notify($this->action, $this->type, $message);
        $this->setLastMessage($message);
        return true;
    }
}
