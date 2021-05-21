<?php

namespace Pastell\Helpers;

class StringHelper
{

    /**
     * @param string $string
     * @param int $lenght
     * @param string $space
     * @return string
     */
    public static function chopString(string $string, int $lenght = 1, string $space = " "): string
    {
        return implode($space, str_split($string, $lenght));
    }
}
