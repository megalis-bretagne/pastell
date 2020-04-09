<?php

namespace Pastell\Service\TypeDossier;

use TypeDossierSQL;
use TypeDossierPersonnaliseDirectoryManager;
use TypeDossierImportExport;
use EntiteSQL;
use Journal;
use TypeDossierException;
use UnrecoverableException;

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
     * @var TypeDossierImportExport
     */
    private $typeDossierImportExport;

    /**
     * @var Journal
     */
    private $journal;

    public function __construct(
        TypeDossierSQL $typeDossierSQL,
        TypeDossierPersonnaliseDirectoryManager $typeDossierPersonnaliseDirectoryManager,
        TypeDossierImportExport $typeDossierImportExport,
        Journal $journal
    ) {
        $this->typeDossierSQL = $typeDossierSQL;
        $this->typeDossierPersonnaliseDirectoryManager = $typeDossierPersonnaliseDirectoryManager;
        $this->typeDossierImportExport = $typeDossierImportExport;
        $this->journal = $journal;
    }


    /**
     * @param int $id_t
     * @throws TypeDossierException
     * @throws UnrecoverableException
     */
    public function delete(int $id_t): void
    {
        $export = $this->typeDossierImportExport->export($id_t);

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
