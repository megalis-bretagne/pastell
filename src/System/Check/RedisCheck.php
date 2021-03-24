<?php

namespace Pastell\System\Check;

use Pastell\System\CheckInterface;
use Pastell\System\HealthCheckItem;
use RedisWrapper;
use VerifEnvironnement;

class RedisCheck implements CheckInterface
{
    /**
     * @var VerifEnvironnement
     */
    private $verifEnvironnement;
    private $redisWrapper;

    public function __construct(VerifEnvironnement $verifEnvironnement, RedisWrapper $redisWrapper)
    {
        $this->verifEnvironnement = $verifEnvironnement;
        $this->redisWrapper = $redisWrapper;
    }

    public function check(): array
    {
        $status = $this->verifEnvironnement->checkRedis();
        if ($status) {
            $result = 'OK';
        } else {
            $result = 'KO ' . $this->verifEnvironnement->getLastError();
        }

        $number_of_key = 0;
        if ($status) {
            $number_of_key = $this->redisWrapper->getNumberOfKeys();
        }

        return [
            (new HealthCheckItem('Statut de redis', $result))->setSuccess($status),
            new HealthCheckItem(
                'Temps de mise en cache (définition des flux, des connecteurs, ...)',
                CACHE_TTL_IN_SECONDS . ' seconde(s)'
            ),
            new HealthCheckItem(
                'Nombre de clés',
                $number_of_key
            )
        ];
    }
}
