<?php

namespace Pastell\System\Check;

use FreeSpace;
use Pastell\System\CheckInterface;
use Pastell\System\HealthCheckItem;
use VerifEnvironnement;

class WorkspaceCheck implements CheckInterface
{
    /**
     * @var VerifEnvironnement
     */
    private $verifEnvironnement;
    /**
     * @var FreeSpace
     */
    private $freeSpace;

    public function __construct(VerifEnvironnement $verifEnvironnement, FreeSpace $freeSpace)
    {
        $this->verifEnvironnement = $verifEnvironnement;
        $this->freeSpace = $freeSpace;
    }

    public function check(): array
    {
        $spaceUsed = $this->freeSpace->getFreeSpace(WORKSPACE_PATH);
        return [
            (new HealthCheckItem(
                WORKSPACE_PATH . ' accessible en lecture/écriture ?',
                $this->verifEnvironnement->checkWorkspace() ? 'OK' : 'KO'
            ))->setSuccess($this->verifEnvironnement->checkWorkspace()),
            new HealthCheckItem(
                'Taille totale de la partition',
                $spaceUsed['disk_total_space']
            ),
            new HealthCheckItem(
                'Taille des données',
                $spaceUsed['disk_use_space']
            ),
            (new HealthCheckItem(
                "Taux d'occupation",
                $spaceUsed['disk_use_percent']
            ))->setSuccess(!$spaceUsed['disk_use_too_big'])
        ];
    }
}
