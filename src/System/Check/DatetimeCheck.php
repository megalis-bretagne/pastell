<?php

namespace Pastell\System\Check;

use Date;
use Pastell\System\CheckInterface;
use Pastell\System\HealthCheckItem;

class DatetimeCheck implements CheckInterface
{
    public function check(): array
    {
        return [
            new HealthCheckItem('TIMEZONE', TIMEZONE),
            new HealthCheckItem('LOCAL_DATETIME', date('c')),
            new HealthCheckItem('UTC_DATETIME', gmdate('c')),
        ];
    }
}
