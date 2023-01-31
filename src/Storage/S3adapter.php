<?php

declare(strict_types=1);

namespace Pastell\Storage;

use Aws\S3\S3Client;

class S3adapter implements StorageInterface
{
    private S3Client $aws;
    private string $bucket;

    /**
     * @param string $S3url
     * @param string $S3key
     * @param string $S3secret
     * @param string $S3bucket
     */
    public function __construct(string $S3url, string $S3key, string $S3secret, string $S3bucket)
    {
        $this->bucket = $S3bucket;
        $this->aws = new S3Client([
            'version' => 'latest',
            'region'  => 'fr-par',
            'endpoint' => $S3url,
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key'    => $S3key,
                'secret' => $S3secret,
            ],
        ]);
    }

    public function write(string $id, string $content): string
    {
        $this->aws->putObject([
            'Bucket' => $this->bucket,
            'Key'    => $id,
            'Body'   => $content,
        ]);
        return $id;
    }

    public function read(string $id): string
    {
        $object = $this->aws->getObject([
            'Bucket' => $this->bucket,
            'Key'    => $id,
        ]);
        return $object->get('Body')->__toString();
    }

    public function createBucket(): void
    {
        $this->aws->createBucket(['Bucket' => $this->bucket]);
    }

    public function isBucketSet(): bool
    {
        return $this->aws->doesBucketExist($this->bucket);
    }
}
