<?php

namespace Pastell\Helpers;

class StringHelper
{
    /**
     * @param string $string
     * @param int $length
     * @param string $space
     * @return string
     */
    public static function chopString(string $string, int $length = 1, string $space = " "): string
    {
        return implode($space, str_split($string, $length));
    }
}
