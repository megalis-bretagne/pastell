<?php

/**
 * Permet de créer un objet de type DonneesFormulaire
 * @author eric
 */
class DonneesFormulaireFactory
{
    private $documentTypeFactory;
    private $workspacePath;
    private $connecteurEntiteSQL;
    private $documentSQL;
    private $documentIndexSQL;
    /** @var  YMLLoader */
    private $ymlLoader;
    private $documentAction;

    public function __construct(
        DocumentTypeFactory $documentTypeFactory,
        $workspacePath,
        ConnecteurEntiteSQL $connecteurEntiteSQL,
        DocumentSQL $documentSQL,
        DocumentIndexSQL $documentIndexSQL,
        YMLLoader $ymlLoader,
        DocumentActionSQL $documentAction
    ) {
        $this->documentTypeFactory = $documentTypeFactory;
        $this->workspacePath = $workspacePath;
        $this->connecteurEntiteSQL = $connecteurEntiteSQL;
        $this->documentSQL = $documentSQL;
        $this->documentIndexSQL = $documentIndexSQL;
        $this->ymlLoader = $ymlLoader;
        $this->documentAction = $documentAction;
    }
    /**
     *
     * @param string $id_d
     * @param string|bool $document_type
     * @throws NotFoundException
     * @return DonneesFormulaire
     */
    public function get($id_d, $document_type = false)
    {
        $info = $this->documentSQL->getInfo($id_d);
        if (! $document_type) {
            $document_type = $info['type'];
        }

        if (!$document_type) {
            throw new NotFoundException("Document inexistant");
        }

        $documentType = $this->documentTypeFactory->getFluxDocumentType($document_type);
        return $this->getDocumentFromCache($id_d, $documentType);
    }

    /**
     * @param $id_ce
     * @return DonneesFormulaire
     * @throws Exception
     */
    public function getConnecteurEntiteFormulaire($id_ce)
    {
        $connecteur_entite_info = $this->connecteurEntiteSQL->getInfo($id_ce);
        if ($connecteur_entite_info['id_e']) {
            $documentType = $this->documentTypeFactory->getEntiteDocumentType($connecteur_entite_info['id_connecteur']);
        } else {
            $documentType = $this->documentTypeFactory->getGlobalDocumentType($connecteur_entite_info['id_connecteur']);
        }
        $id_document = "connecteur_$id_ce";
        return $this->getConnecteurFromCache($id_document, $documentType);
    }

    /**
     * @param $id_d
     * @param $id_ce
     * @return DonneesFormulaire
     * @throws Exception
     */
    public function getFromDocumentOrConnecteur($id_d, $id_ce)
    {
        if ($id_d) {
            return $this->get($id_d);
        } else {
            return $this->getConnecteurEntiteFormulaire($id_ce);
        }
    }

    /**
     * @param $id_document
     * @param DocumentType $documentType
     * @return DonneesFormulaire
     */
    private function getConnecteurFromCache($id_document, DocumentType $documentType): DonneesFormulaire
    {
        $doc = new DonneesFormulaire(
            $this->workspacePath  . "/$id_document.yml",
            $documentType,
            $this->ymlLoader
        );
        $doc->{'id_d'} = $id_document;
        $documentIndexor = new DocumentIndexor($this->documentIndexSQL, $id_document);
        $doc->setDocumentIndexor($documentIndexor);
        return $doc;
    }

    /**
     * @param $id_document
     * @param DocumentType $documentType
     * @return DonneesFormulaire
     */
    private function getDocumentFromCache($id_document, DocumentType $documentType): DonneesFormulaire
    {
        $dir = $this->getNewDirectoryPath($id_document);
        if (! file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $doc = new DonneesFormulaire("$dir/$id_document.yml", $documentType, $this->ymlLoader);
        $doc->{'id_d'} = $id_document;
        $doc = $this->setEditableContent($documentType, $doc);
        $documentIndexor = new DocumentIndexor($this->documentIndexSQL, $id_document);
        $doc->setDocumentIndexor($documentIndexor);
        return $doc;
    }

    public function clearCache()
    {
        unset($this->cache);
    }

    private function getNewDirectoryPath($id_document)
    {
        if (mb_strlen($id_document) < 2) {
            return $this->workspacePath;
        }
        if (! is_string($id_document)) {
            return $this->workspacePath;
        }
        $a = $id_document[0];
        $b = $id_document[1];
        return $this->workspacePath . "/$a/$b/";
    }

    public function getNonPersistingDonneesFormulaire()
    {
        $filename = sys_get_temp_dir() . "/pastell_phpunit_non_persinting_donnees_formulaire";
        $documentType = new DocumentType("empty", array());
        if (file_exists($filename)) {
            unlink($filename);
        }
        return new DonneesFormulaire($filename, $documentType);
    }

    /**
     * @param DocumentType $documentType
     * @param DonneesFormulaire $doc
     * @return DonneesFormulaire
     */
    private function setEditableContent(DocumentType $documentType, DonneesFormulaire $doc): DonneesFormulaire
    {
        $last_action = $this->documentAction->getLastActionNotModif($doc->id_d);
        $editable_content = $documentType->getAction()->getEditableContent($last_action);
        if (
            (!in_array($last_action, ['creation', 'modification', false]))
            || $editable_content
        ) {
            $doc->setEditableContent($editable_content ?: []);
        }
        return $doc;
    }
}
