<?php

declare(strict_types=1);

namespace Utils\Rector\Tests\UseGetViewParametersInsteadMagicMethodTest;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Iterator;
use Symplify\SmartFileSystem\SmartFileInfo;

final class UseGetViewParametersInsteadMagicMethodTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }
    /**Test-driven Rule Development
    81
     * @return Iterator<SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }
    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config.php';
    }
}
