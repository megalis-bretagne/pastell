<?php

declare(strict_types=1);

namespace Pastell\Seda;

enum SedaVersion: string
{
    case VERSION_1_0 = '1.0';
    case VERSION_2_1 = '2.1';
    case VERSION_2_1_VITAM = '2.1-vitam';
}
