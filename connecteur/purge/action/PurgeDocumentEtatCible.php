<?php

class PurgeDocumentEtatCible extends ChoiceActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        $document_etat = $this->getRecuperateur()->get('document_etat_cible');
        $list_etat = $this->displayAPI();
        if (empty($list_etat[$document_etat])) {
            throw new Exception("Cette action n'existe pas");
        }
        $this->getConnecteurProperties()->setData('document_etat_cible', $document_etat);
        $this->getConnecteurProperties()->setData(
            'document_etat_cible_libelle',
            isset($list_etat[$document_etat]['name-action']) ? $list_etat[$document_etat]['name-action'] : $document_etat
        );
        return true;
    }

    /**
     * @return array|mixed
     * @throws Exception
     */
    public function displayAPI()
    {
        $document_type = $this->getConnecteurProperties()->get('document_type');
        if (! $document_type) {
            throw new Exception("Il faut d'abord choisir un type de dossier");
        }
        $document_etat = $this->getConnecteurProperties()->get('document_etat');
        if (! $document_etat) {
            throw new Exception("Il faut d'abord choisir un état source");
        }

        $documentType = $this->objectInstancier->getInstance(DocumentTypeFactory::class)->getFluxDocumentType($document_type)->getTabAction();

        return $documentType;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function display()
    {
        $this->setViewParameter('document_etat', $this->getConnecteurProperties()->get('document_etat_cible'));

        $this->setViewParameter('list_etat', $this->displayAPI());
        $this->renderPage(
            "Choix de l'action sur le dossier",
            'connector/purge/PurgeDocumentEtatCible'
        );
        return true;
    }
}
