<?php

namespace Pastell\System\Check;

use Pastell\System\CheckInterface;
use Pastell\System\HealthCheckItem;

class ConstantsCheck implements CheckInterface
{
    public function check(): array
    {
        return [
            new HealthCheckItem('OPENSSL_PATH', OPENSSL_PATH),
            new HealthCheckItem('WORKSPACE_PATH', WORKSPACE_PATH),
            new HealthCheckItem('HTTP_PROXY_URL', HTTP_PROXY_URL),
            new HealthCheckItem('NO_PROXY', NO_PROXY)
        ];
    }
}
