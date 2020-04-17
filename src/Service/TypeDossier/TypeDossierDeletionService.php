<?php

namespace Pastell\Service\TypeDossier;

use TypeDossierSQL;
use TypeDossierPersonnaliseDirectoryManager;
use EntiteSQL;
use Journal;
use TypeDossierException;

class TypeDossierDeletionService
{
    /**
     * @var TypeDossierSQL
     */
    private $typeDossierSQL;

    /**
     * @var TypeDossierPersonnaliseDirectoryManager
     */
    private $typeDossierPersonnaliseDirectoryManager;

    /**
     * @var TypeDossierExportService
     */
    private $typeDossierExportService;

    /**
     * @var Journal
     */
    private $journal;

    public function __construct(
        TypeDossierSQL $typeDossierSQL,
        TypeDossierPersonnaliseDirectoryManager $typeDossierPersonnaliseDirectoryManager,
        TypeDossierExportService $typeDossierExportService,
        Journal $journal
    ) {
        $this->typeDossierSQL = $typeDossierSQL;
        $this->typeDossierPersonnaliseDirectoryManager = $typeDossierPersonnaliseDirectoryManager;
        $this->typeDossierExportService = $typeDossierExportService;
        $this->journal = $journal;
    }

    /**
     * @param int $id_t
     * @throws TypeDossierException
     */
    public function delete(int $id_t): void
    {
        $export = $this->typeDossierExportService->export($id_t);

        $this->typeDossierPersonnaliseDirectoryManager->delete($id_t);
        $this->typeDossierSQL->delete($id_t);

        $this->journal->add(
            Journal::TYPE_DOSSIER_EDITION,
            EntiteSQL::ID_E_ENTITE_RACINE,
            Journal::NO_ID_D,
            Journal::ACTION_SUPPRIME,
            "Suppression du type de dossier id_t=$id_t.\nJSON contenant l'export de la definition du type de dossier : " . $export
        );
    }
}
