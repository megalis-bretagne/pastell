<?php

class VersionAPIControllerTest extends PastellTestCase
{
    public function testGet(): void
    {
        $info = $this->getInternalAPI()->get('/version');
        static::assertSame(
            [
                'version' => '1.4-fixtures',
                'revision' => '1352',
                'last_changed_date' => '$LastChangedDate: 2015-05-07 17:52:26 +0200 (jeu., 07 mai 2015) $',
                'extensions_versions_accepted' => [
                    '1.1.4',
                    '1.1.5',
                    '1.1.6',
                    1.2,
                    '1.3-dev',
                    1.3,
                    '1.3.04',
                    '1.4-dev',
                ],
                'version_complete' => 'Version 1.4-fixtures - Révision  1352',
            ],
            $info
        );
    }

    public function testV1()
    {
        $this->expectOutputRegex("#1.4-fixtures#");
        $this->getV1("version.php");
    }
}
