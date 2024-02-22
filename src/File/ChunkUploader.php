<?php

namespace Pastell\File;

use Exception;
use Flow\Basic;
use Flow\Config;
use Flow\Request;
use Flow\Uploader;

class ChunkUploader
{
    public function __construct(
        private readonly string $uploadChunkDirectory,
        private readonly Config $config,
        private readonly Request $request,
    ) {
    }

    public function getUploadChunkDirectory(): string
    {
        return $this->uploadChunkDirectory;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function uploadChunk($upload_filepath): bool
    {
        $this->config->setTempDir($this->uploadChunkDirectory);
        return Basic::save($upload_filepath, $this->config, $this->request);
    }

    public function continueChunk(): array
    {
        header_wrapper('HTTP/1.1 200 Ok');
        return ['result' => 'success', 'message' => 'Chunk uploaded'];
    }

    public function createdChunk($upload_filepath): array
    {
        unlink($upload_filepath);
        header_wrapper('HTTP/1.1 201 Created');
        return ['result' => 'success', 'message' => 'File uploaded'];
    }

    /**
     * @throws Exception
     */
    public function pruneChunks(): void
    {
        if (random_int(1, 100) === 1) {
            Uploader::pruneChunks($this->uploadChunkDirectory);
        }
    }
}
