<?php

declare(strict_types=1);

namespace Pastell\Viewer;

use Connecteur;

abstract class ConnectorViewer implements Viewer
{
    private Connecteur $connector;

    public function setConnector(Connecteur $connector): void
    {
        $this->connector = $connector;
    }

    public function getConnector(): ?Connecteur
    {
        return $this->connector;
    }
}
