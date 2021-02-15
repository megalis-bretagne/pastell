<?php

namespace Pastell\System\Check;

use Pastell\System\CheckInterface;
use Pastell\System\HealthCheckItem;

class PhpConfigurationCheck implements CheckInterface
{
    public function check(): array
    {
        $expectedData = [
            'memory_limit' => "512M",
            'post_max_size' => "200M",
            'upload_max_filesize' => "200M",
            'max_execution_time' => 600,
            'session.cookie_httponly' => 1,
            'session.cookie_secure' => 1,
            'session.use_only_cookies' => 1
        ];
        $ini = [];

        foreach ($expectedData as $key => $expectedValue) {
            $ini[] = (new HealthCheckItem(
                $key,
                ini_get($key),
                $expectedValue
            ))->setSuccess((int)ini_get($key) >= (int)$expectedValue);
        }

        return $ini;
    }
}
