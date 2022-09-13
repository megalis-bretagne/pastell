<?php

namespace Pastell\Updater\Major4\Minor0;

use Exception;
use Pastell\Updater\Version;
use Monolog\Logger;
use SQLQuery;

class Patch0 implements Version
{
    public function __construct(
        private readonly SQLQuery $sqlQuery,
        private readonly ?Logger $logger = null,
    ) {
    }

    public function update(): void
    {
        $this->updateEntiteServiceByCollectivite();
    }

    /**
     * @throws Exception
     */
    private function updateEntiteServiceByCollectivite(): void
    {
        $sql = "UPDATE entite " .
            " SET type = 'collectivite' " .
            " WHERE type = 'service' ";
        $this->sqlQuery->query($sql);
        $this->logger?->info("[UPDATE DATABASE] $sql");
    }
}
