<?php

use Aws\S3\S3Client;

class S3Wrapper implements ProofBackend
{
    private string $type;
    private string $extension;
    private string $bucket;

    public function __construct($type, $extension, $bucket)
    {
        $this->type = $type;
        $this->extension = $extension;
        $this->bucket = $bucket;
        // mettre tous les parametres spécifiques à MINIO bucket extension etc
    }

    public static function getAccess(): S3Client
    {
        return new S3Client([
            'version' => 'latest',
            'region'  => 'fr-par',
            'endpoint' => 'http://minio:9000/',
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key'    => 'minioadmin',
                'secret' => 'minioadmin'
            ],
        ]);
    }
    public function write($id, $content): void
    {
        $connexion = self::getAccess();
        $connexion->putObject([
            'Bucket' => $this->bucket,
            'Key'    => $id . $this->type . '.' . $this->extension,
            'Body'   => $content,
        ]);
    }

    public function read($id)
    {
        $connexion = self::getAccess();
        $object = $connexion->getObject([
            'Bucket' => $this->bucket,
            'Key'    => $id . $this->type . '.' . $this->extension,
        ]);
        return $object['Body'];
    }
}
