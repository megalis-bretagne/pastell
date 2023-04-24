<?php

declare(strict_types=1);

namespace Pastell\Command\Storage;

use Journal;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MoveProofToMinio extends Command
{
    private Journal $journal;

    public function __construct(Journal $journal)
    {
        parent::__construct();
        $this->journal = $journal;
    }
    protected function configure()
    {
        $this
            ->setName('app:storage:minio:move-proof-to-minio')
            ->setDescription('Move all existing proof from DB to MinIO (object storage)')
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sql = "SELECT id_j FROM journal WHERE preuve != ''";
        $proofIdList = $this->journal->query($sql);

        foreach ($proofIdList as $proofId) {
            $sql = "SELECT preuve FROM journal WHERE id_j = ?";
            $proof = $this->journal->query($sql, $proofId['id_j'])[0];
            $this->journal->saveProof($proofId['id_j'], $proof['preuve']);
            $sql = "UPDATE journal SET preuve = '' WHERE id_j = ?";
            $this->journal->query($sql, $proofId['id_j']);
        }
        return Command::SUCCESS;
    }
}
