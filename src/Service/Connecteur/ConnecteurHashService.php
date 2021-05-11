<?php

namespace Pastell\Service\Connecteur;

use ConnecteurFactory;
use Exception;

class ConnecteurHashService
{
    private $connecteurFactory;

    public function __construct(
        ConnecteurFactory $connecteurFactory
    ) {
        $this->connecteurFactory = $connecteurFactory;
    }

    /**
     * @param int $id_ce
     * @return string
     * @throws Exception
     */
    public function getHash(int $id_ce): string
    {
        return hash("sha256", $this->connecteurFactory->getConnecteurConfig($id_ce)->jsonExport());
    }
}
