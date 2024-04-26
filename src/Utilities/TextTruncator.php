<?php

declare(strict_types=1);

namespace Pastell\Utilities;

final class TextTruncator
{
    private const MIDDLE_DELIMITER = '…';

    public static function truncate(string $text, int $expectedBytes): string
    {
        $bytesInText = \strlen($text);
        if ($expectedBytes >= $bytesInText) {
            return $text;
        }
        $middleDelimiterHalfLength = (int)\ceil(\strlen(self::MIDDLE_DELIMITER) / 2);
        $halfLength = (int)\floor($expectedBytes / 2) - $middleDelimiterHalfLength;

        $firstPart = '';
        $length = 0;
        while (\strlen(\mb_strcut($text, 0, $length)) <= ($halfLength)) {
            $firstPart = \mb_strcut($text, 0, $length);
            ++$length;
        }

        $lastPart = '';
        $length = 0;
        while (\strlen(\mb_strcut($text, $bytesInText - $length, $bytesInText)) <= ($halfLength)) {
            $lastPart = \mb_strcut($text, $bytesInText - $length, $bytesInText);
            ++$length;
        }

        return $firstPart . self::MIDDLE_DELIMITER . $lastPart;
    }
}
