<?php

declare(strict_types=1);

namespace Pastell\Command\Storage;

use Pastell\Storage\S3adapter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateBucket extends Command
{
    public function __construct(
        private readonly string $s3Url,
        private readonly string $s3Key,
        private readonly string $s3Secret,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:storage:minio:create-bucket')
            ->setDescription('Create a bucket for object storage with MinIO')
            ->addArgument('bucket', InputArgument::REQUIRED, 'Bucket name')
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $S3wrapper = new S3adapter($this->s3Url, $this->s3Key, $this->s3Secret, $input->getArgument('bucket'));
        if (!$S3wrapper->isBucketSet()) {
            $S3wrapper->createBucket();
        }
        return Command::SUCCESS;
    }
}
