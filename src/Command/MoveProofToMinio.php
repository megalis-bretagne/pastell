<?php

declare(strict_types=1);

namespace Pastell\Command;

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
            ->setName('dev:move-proof-to-minio')
            ->setDescription('Move all existing proof from DB to MinIO (object storage)')
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $proofList = $this->journal->getAllProof();
        foreach ($proofList as $proof) {
            $this->journal->saveProof($proof['id_j'], $proof['preuve']);
        }
        $sql = "UPDATE journal SET preuve = ''";
        $this->journal->query($sql);
        return Command::SUCCESS;
    }
}
