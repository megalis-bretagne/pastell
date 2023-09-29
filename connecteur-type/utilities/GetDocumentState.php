<?php

declare(strict_types=1);

class GetDocumentState extends ConnecteurTypeChoiceActionExecutor
{
    private const DOCUMENT_TYPE_FIELD = 'document_type';
    private const DOCUMENT_STATE_FIELD = 'document_state';
    private const DOCUMENT_STATE_LABEL = 'documentS_sate_label';
    private const PAGE_TITLE = 'page_title';

    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {

        $document_etat = $this->getRecuperateur()->get($this->getMappingValue(self::DOCUMENT_STATE_FIELD));
        $list_etat = $this->displayAPI();
        if (empty($list_etat[$document_etat])) {
            throw new Exception("Cet état n'existe pas");
        }

        $this->getConnecteurProperties()->setData(
            $this->getMappingValue(self::DOCUMENT_STATE_FIELD),
            $document_etat
        );
        $this->getConnecteurProperties()->setData(
            $this->getMappingValue(self::DOCUMENT_STATE_LABEL),
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
        $document_type = $this->getConnecteurProperties()->get($this->getMappingValue(self::DOCUMENT_TYPE_FIELD));
        if (!$document_type) {
            throw new Exception("Il faut d'abord choisir un type de dossier");
        }
        return $documentType = $this->objectInstancier->getInstance(DocumentTypeFactory::class)->getFluxDocumentType(
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
            $this->getMappingValue(self::DOCUMENT_STATE_FIELD),
            $this->getConnecteurProperties()->get($this->getMappingValue(self::DOCUMENT_STATE_FIELD))
        );

        $this->setViewParameter('list_etat', $this->displayAPI());
        $this->renderPage(
            $this->getMappingValue(self::PAGE_TITLE),
            'connector/purge/PurgeDocumentEtat'
        );
        return true;
    }
}
