<?php

namespace Pastell\Service;

use EntiteSQL;
use Exception;
use Journal;

class EntiteDeletionService
{

    /**
     * @var EntiteSQL
     */
    private $entiteSQL;

    /**
     * @var Journal
     */
    private $journal;

    public function __construct(EntiteSQL $entiteSQL, Journal $journal)
    {
        $this->entiteSQL = $entiteSQL;
        $this->journal = $journal;
    }

    /**
     * @param int $id_e
     * @throws Exception
     */
    public function delete(int $id_e): void
    {
        $info = $this->entiteSQL->getInfo($id_e);
        $this->entiteSQL->removeEntite($id_e);
        $this->journal->add(
            Journal::MODIFICATION_ENTITE,
            $id_e,
            0,
            "Supprimé",
            "Suppression de l'entité id_e=$id_e\nInformation : " . json_encode($info)
        );
    }
}
