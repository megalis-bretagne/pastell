<?php

declare(strict_types=1);

namespace Pastell;

function areNullOrEmptyStrings(?string ...$strings): bool
{
    foreach ($strings as $string) {
        if ($string !== null && $string !== '') {
            return false;
        }
    }
    return true;
}
