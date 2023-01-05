<?php

declare(strict_types=1);

namespace Pastell\Command\Storage;

use Pastell\Storage\S3Wrapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateBucket extends Command
{
    public function __construct(
        private readonly string $S3url,
        private readonly string $S3key,
        private readonly string $S3secret,
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
        $S3wrapper = new S3Wrapper($this->S3url, $this->S3key, $this->S3secret, $input->getArgument('bucket'));
        if (!$S3wrapper->isBucketSet()) {
            $S3wrapper->createBucket();
        }
        return Command::SUCCESS;
    }
}
