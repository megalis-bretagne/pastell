<?php

namespace Pastell\System\Check;

use JobQueueSQL;
use Pastell\System\CheckInterface;
use Pastell\System\HealthCheckItem;

class DaemonCheck implements CheckInterface
{
    public function __construct(
        private readonly JobQueueSQL $jobQueueSQL
    ) {
    }

    public function check(): array
    {
        return [$this->checkDaemon()];
    }

    private function checkDaemon(): HealthCheckItem
    {
        $daemonResult = "Le gestionnaire des tâches ne présente pas de travaux suspendus depuis plus d'une heure.";
        $success = true;

        $lastTry = $this->jobQueueSQL->getMaxLastTryOneHourLate();
        if (($lastTry) && ((time() - strtotime($lastTry)) > 3600)) {
            $daemonResult = "Le gestionnaire des tâches semble arrêté depuis plus d'une heure.";
            $success = false;
        } else {
            $nbLock = $this->jobQueueSQL->getNbLockSinceOneHour();
            if ($nbLock) {
                $daemonResult = sprintf(
                    "%d %s depuis plus d'une heure.",
                    $nbLock,
                    $nbLock > 1 ? 'travaux semblent suspendus' : 'travail semble suspendu'
                );
                $success = false;
            }
        }
        return (new HealthCheckItem('Tâches automatiques', $daemonResult))
            ->setSuccess($success);
    }
}
