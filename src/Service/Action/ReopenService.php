<?php

declare(strict_types=1);

namespace Pastell\Service\Action;

use DocumentActionEntite;
use DocumentActionSQL;
use DocumentEntite;
use Journal;
use UnrecoverableException;

class ReopenService
{
    public function __construct(
        private readonly DocumentActionSQL $documentActionSQL,
        private readonly DocumentActionEntite $documentActionEntite,
        private readonly DocumentEntite $documentEntite,
        private readonly Journal $journal,
    ) {
    }

    /**
     * @throws UnrecoverableException
     */
    public function reopen(int $id_e, string $id_d, int $id_u): void
    {
        $lastActionInfo = $this->documentActionSQL->getLastActionInfo($id_d, $id_e);
        if ($lastActionInfo['action'] !== 'termine') {
            throw new UnrecoverableException(
                "La réouverture du document n'est possible que sur un document dans l'état terminé"
            );
        }
        $id_a = $this->documentActionSQL->removeLastAction($id_e, $id_d);
        $this->documentActionEntite->remove($id_a);

        $lastAction = $this->documentActionSQL->getLastActionInfo($id_d, $id_e);
        $this->documentEntite->changeAction($id_e, $id_d, $lastAction['action'], $lastAction['date']);

         $this->journal->addSQL(
             Journal::DOCUMENT_ACTION,
             $id_e,
             $id_u,
             $id_d,
             'reopen',
             'Le dossier a été rouvert.'
         );
    }
}
