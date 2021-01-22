<?php

namespace Pastell\System;

interface CheckInterface
{
    /**
     * @return HealthCheckItem[]
     */
    public function check(): array;
}
