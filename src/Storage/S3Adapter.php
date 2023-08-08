<?php

declare(strict_types=1);

namespace Pastell\Storage;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

class S3Adapter implements StorageInterface
{
    private S3Client $aws;
    private string $bucket;

    public function __construct(string $s3Url, string $s3Key, string $s3Secret, string $s3Bucket)
    {
        $this->bucket = $s3Bucket;
        $this->aws = new S3Client([
            'version' => 'latest',
            'region'  => 'fr-par',
            'endpoint' => $s3Url,
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key'    => $s3Key,
                'secret' => $s3Secret,
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
        try {
            $object = $this->aws->getObject([
                'Bucket' => $this->bucket,
                'Key'    => $id,
            ]);
        } catch (S3Exception $e) {
            if ($e->getAwsErrorCode() === 'NoSuchKey') {
                return '';
            } else {
                throw $e;
            }
        }
        return $object->get('Body')->__toString();
    }
}
