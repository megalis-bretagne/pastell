<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Pastell\Client\IparapheurV5\ClientFactory;
use Psr\Http\Client\ClientInterface;

class RecupFinParapheurTest extends PastellTestCase
{
    private TmpFolder $tmpFolder;
    private string $workspace_path;

    /** @throws Exception */
    protected function setUp(): void
    {
        parent::setUp();
        $this->tmpFolder = new TmpFolder();
        $this->workspace_path = $this->tmpFolder->create();
        $this->getObjectInstancier()->setInstance('workspacePath', $this->workspace_path);
    }

    protected function tearDown(): void
    {
        $this->tmpFolder->delete($this->workspace_path);
    }
}
