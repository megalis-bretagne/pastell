<?php

namespace Pastell\System\Check;

use ConnecteurFactory;
use Pastell\System\CheckInterface;
use Pastell\System\HealthCheckItem;

class MissingConnectorsCheck implements CheckInterface
{
    /**
     * @var ConnecteurFactory
     */
    private $connecteurFactory;

    public function __construct(ConnecteurFactory $connecteurFactory)
    {
        $this->connecteurFactory = $connecteurFactory;
    }

    public function check(): array
    {
        return [$this->checkMissingConnectors()];
    }

    private function checkMissingConnectors(): HealthCheckItem
    {
        $missingConnectors = $this->connecteurFactory->getManquant();
        $result = empty($missingConnectors) ? 'Aucun' : implode(', ', $missingConnectors);
        return (new HealthCheckItem('Connecteur(s) manquant(s)', $result))
            ->setSuccess(empty($missingConnectors));
    }
}
