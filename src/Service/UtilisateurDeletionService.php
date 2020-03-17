<?php

namespace Pastell\Service;

use RoleUtilisateur;
use UtilisateurSQL;
use Exception;
use Journal;

class UtilisateurDeletionService
{
    /**
     * @var UtilisateurSQL
     */
    private $utilisateurSQL;

    /**
     * @var Journal
     */
    private $journal;

    /**
     * @var RoleUtilisateur
     */
    private $roleUtilisateur;

    public function __construct(
        UtilisateurSQL $utilisateurSQL,
        RoleUtilisateur $roleUtilisateur,
        Journal $journal
    ) {
        $this->utilisateurSQL = $utilisateurSQL;
        $this->journal = $journal;
        $this->roleUtilisateur = $roleUtilisateur;
    }

    /**
     * Suppression de l'utilisateur
     * Attention, on enregistre pas les données nominatives dans le journal.
     * @param int $id_u
     * @throws Exception
     */
    public function delete(int $id_u): void
    {
        $this->roleUtilisateur->removeAllRole($id_u);
        $this->utilisateurSQL->desinscription($id_u);
        $this->journal->add(
            Journal::MODIFICATION_UTILISATEUR,
            0,
            0,
            "Supprimé",
            "Suppression de l'utilisateur id_u=$id_u"
        );
    }
}
