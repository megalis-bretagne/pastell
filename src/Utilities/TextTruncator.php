<?php

declare(strict_types=1);

namespace Pastell\Utilities;

final class TextTruncator
{
    public static function truncate(string $text, int $expectedBytes): string
    {
        $bytesInText = \strlen($text);
        if ($expectedBytes > $bytesInText) {
            return $text;
        }
        $half = (int)\floor($expectedBytes / 2);

        $firstPart = \mb_strcut($text, 0, $half);
        $lastPart = \mb_strcut($text, $bytesInText - $half, $bytesInText);
        return $firstPart . '…' . $lastPart;
    }
}
