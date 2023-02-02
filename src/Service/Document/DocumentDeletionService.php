<?php

namespace Pastell\Service\Document;

use DocumentEntite;
use DocumentSQL;
use DonneesFormulaireFactory;
use JobManager;
use Journal;
use NotFoundException;

class DocumentDeletionService
{
    public function __construct(
        private readonly DocumentSQL $documentSQL,
        private readonly DonneesFormulaireFactory $donneesFormulaireFactory,
        private readonly DocumentEntite $documentEntite,
        private readonly JobManager $jobManager,
        private readonly Journal $journal,
    ) {
    }

    /**
     * @param string $id_d
     * @param string|null $message
     * @return string
     * @throws NotFoundException
     */
    public function delete(string $id_d, ?string $message = null): string
    {
        $id_e = $this->documentEntite->getEntite($id_d)[0]['id_e'];
        $info = $this->documentSQL->getInfo($id_d);

        $this->donneesFormulaireFactory->get($id_d)->delete();
        $this->documentSQL->delete($id_d);
        $this->jobManager->deleteDocumentForAllEntities($id_d);

        $message = sprintf(
            'Le document « %s » (%s) a été supprimé %s',
            $info['titre'],
            $id_d,
            ($message) ? ' - ' . $message : '',
        );
        $this->journal->add(
            Journal::DOCUMENT_ACTION,
            $id_e,
            $id_d,
            'suppression',
            $message
        );
        return $message;
    }
}
