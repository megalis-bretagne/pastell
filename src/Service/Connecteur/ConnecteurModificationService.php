<?php

namespace Pastell\Service\Connecteur;

use ConnecteurEntiteSQL;
use Exception;
use Pastell\Service\Connecteur\ConnecteurActionService;

class ConnecteurModificationService
{
    private $connecteurEntiteSQL;
    private $connecteurActionService;

    public function __construct(
        ConnecteurEntiteSQL $connecteurEntiteSQL,
        ConnecteurActionService $connecteurActionService
    ) {
        $this->connecteurEntiteSQL = $connecteurEntiteSQL;
        $this->connecteurActionService = $connecteurActionService;
    }

    /**
     * @throws Exception
     */
    public function editConnecteurLibelle(int $id_ce, string $libelle, int $frequence_en_minute = 1, string $id_verrou = '', int $id_e = 0, int $id_u = 0, string $message = ''): void
    {
        $this->connecteurEntiteSQL->edit($id_ce, $libelle, $frequence_en_minute, $id_verrou);

        $this->connecteurActionService->add(
            $id_e,
            $id_u,
            $id_ce,
            '',
            ConnecteurActionService::ACTION_MODIFFIE,
            $message
        );
    }
}
