<?php

namespace Pastell\System\Check;

use Pastell\System\CheckInterface;
use Pastell\System\HealthCheckItem;
use VerifEnvironnement;

class RedisCheck implements CheckInterface
{
    /**
     * @var VerifEnvironnement
     */
    private $verifEnvironnement;

    public function __construct(VerifEnvironnement $verifEnvironnement)
    {
        $this->verifEnvironnement = $verifEnvironnement;
    }

    public function check(): array
    {
        $status = $this->verifEnvironnement->checkRedis();
        if ($status) {
            $result = 'OK';
        } else {
            $result = 'KO ' . $this->verifEnvironnement->getLastError();
        }
        return [
            (new HealthCheckItem('Statut de redis', $result))->setSuccess($status),
            new HealthCheckItem(
                'Temps de mise en cache (définition des flux, des connecteurs, ...)',
                CACHE_TTL_IN_SECONDS . ' seconde(s)'
            )
        ];
    }
}
