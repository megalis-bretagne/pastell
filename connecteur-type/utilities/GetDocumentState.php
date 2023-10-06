<?php

declare(strict_types=1);

class GetDocumentState extends ConnecteurTypeChoiceActionExecutor
{
    private const DOCUMENT_TYPE_FIELD = 'document_type';
    private const DOCUMENT_STATE_FIELD = 'document_state';
    private const DOCUMENT_STATE_LABEL = 'document_state_label';
    private const PAGE_TITLE = 'page_title';

    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {

        $document_etat = $this->getRecuperateur()->get('document_state');
        $list_etat = $this->displayAPI();
        $this->getConnecteurProperties()->setData(
            $this->getMappingValue(self::DOCUMENT_STATE_FIELD),
            $document_etat
        );
        $this->getConnecteurProperties()->setData(
            $this->getMappingValue(self::DOCUMENT_STATE_LABEL),
            $list_etat[$document_etat]['name'] ?? $document_etat
        );

        return true;
    }

    /**
     * @return array|mixed
     * @throws Exception
     */
    public function displayAPI()
    {
        $document_type = $this->getConnecteurProperties()->get($this->getMappingValue(self::DOCUMENT_TYPE_FIELD));
        if (!$document_type) {
            throw new Exception("Il faut d'abord choisir un type de dossier");
        }
        return $this->objectInstancier->getInstance(DocumentTypeFactory::class)->getFluxDocumentType(
            $document_type
        )->getTabAction();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function display()
    {
        $this->setViewParameter(
            self::DOCUMENT_STATE_FIELD,
            $this->getConnecteurProperties()->get($this->getMappingValue(self::DOCUMENT_STATE_FIELD))
        );

        $this->setViewParameter('list_etat', $this->displayAPI());
        $this->renderPage(
            $this->getMappingValue(self::PAGE_TITLE),
            'connectorType/utilities/GetDocumentState'
        );
        return true;
    }
}
