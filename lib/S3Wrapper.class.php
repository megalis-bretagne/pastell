<?php

declare(strict_types=1);

use Aws\S3\S3Client;

class S3Wrapper implements ProofBackend
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

    public function write($id, $content): void
    {
        $this->aws->putObject([
            'Bucket' => $this->bucket,
            'Key'    => $id . 'preuve.tsa',
            'Body'   => $content,
        ]);
    }

    public function read($id)
    {
        $object = $this->aws->getObject([
            'Bucket' => $this->bucket,
            'Key'    => $id . 'preuve.tsa',
        ]);
        return $object['Body'];
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
