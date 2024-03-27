<?php

declare(strict_types=1);

namespace Pastell\Tests\Utilities;

use Pastell\Utilities\TextTruncator;
use PHPUnit\Framework\TestCase;

class TextTruncatorTest extends TestCase
{
    public function truncateProvider(): \Generator
    {
        yield ['abcdefgh', 4, 'ab…gh'];
        yield ['aébècêdë', 5, 'a…ë'];
        yield ['👋aébècêdë', 8, '👋…êdë'];
    }

    /**
     * @dataProvider truncateProvider
     */
    public function testTruncate(string $text, int $bytes, string $expectedResult): void
    {
        self::assertSame($expectedResult, TextTruncator::truncate($text, $bytes));
    }
}
