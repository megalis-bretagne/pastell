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
        $json_content = $this->connecteurFactory->getConnecteurConfig($id_ce)->jsonExport();
        //xdebug_var_dump($json_content);
        return hash("sha256", $json_content);
    }
}
