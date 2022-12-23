<?php

declare(strict_types=1);

namespace Pastell\Bootstrap;

class CreateBucket implements InstallableBootstrap
{
    public function __construct(
        private readonly \S3Wrapper $S3wrapper,
    ) {
    }

    public function install(): InstallResult
    {
        $isset = $this->S3wrapper->isBucketSet();
        if ($isset) {
            return InstallResult::NothingToDo;
        } else {
            $this->S3wrapper->createBucket();
            return InstallResult::InstallOk;
        }
    }

    public function getName(): string
    {
        return 'Bucket pour stockage des preuves du journal';
    }
}
