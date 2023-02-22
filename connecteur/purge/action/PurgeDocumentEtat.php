<?php

class PurgeDocumentEtat extends ChoiceActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        $document_etat = $this->getRecuperateur()->get('document_etat');
        $list_etat = $this->displayAPI();
        if (empty($list_etat[$document_etat])) {
            throw new Exception("Cet état n'existe pas");
        }
        $this->getConnecteurProperties()->setData('document_etat', $document_etat);
        $this->getConnecteurProperties()->setData(
            'document_etat_libelle',
            isset($list_etat[$document_etat]['name']) ? $list_etat[$document_etat]['name'] : $document_etat
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
        return $documentType = $this->objectInstancier->getInstance(DocumentTypeFactory::class)->getFluxDocumentType($document_type)->getTabAction();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function display()
    {
        $this->setViewParameter('document_etat', $this->getConnecteurProperties()->get('document_etat'));

        $this->setViewParameter('list_etat', $this->displayAPI());
        $this->renderPage(
            "Choix de l'état du dossier",
            __DIR__ . "/../template/PurgeDocumentEtat.php"
        );
        return true;
    }
}
