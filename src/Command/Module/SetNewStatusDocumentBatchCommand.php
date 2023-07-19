<?php

declare(strict_types=1);

namespace Pastell\Command\Module;

use DocumentActionEntite;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:module:set-new-status-document-batch',
    description: 'Set a new status to a document batch having a specific type and status from one entity',
)]
class SetNewStatusDocumentBatchCommand extends Command
{
    public const ID_E = 'id_e';
    public const TYPE = 'type';
    public const OLD_STATUS = 'old_status';
    public const NEW_STATUS = 'new_status';

    public function __construct(
        private readonly \JobManager $jobManager,
        private readonly DocumentActionEntite $documentActionEntite,
        private readonly \ActionChange $actionChange,
    ) {
        parent::__construct();
    }


    protected function configure(): void
    {
        $this
            ->addArgument(self::ID_E, InputArgument::REQUIRED, 'entity id')
            ->addArgument(self::TYPE, InputArgument::REQUIRED, 'document type')
            ->addArgument(self::OLD_STATUS, InputArgument::REQUIRED, 'old status')
            ->addArgument(self::NEW_STATUS, InputArgument::REQUIRED, 'new status')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $id_e = $input->getArgument(self::ID_E);
        $type = $input->getArgument(self::TYPE);
        $oldStatus = $input->getArgument(self::OLD_STATUS);
        $newStatus = $input->getArgument(self::NEW_STATUS);

        $documents = $this->documentActionEntite->getDocument($id_e, $type, $oldStatus);
        $io->progressStart(count($documents));

        foreach ($documents as $document) {
            $io->write(sprintf("Modification de : %s\n", $document['id_d']));
            $this->actionChange->addAction(
                $document['id_d'],
                $id_e,
                0,
                $newStatus,
                'Modification via la commande set-new-status-document-batch'
            );
            $this->jobManager->setJobForDocument(
                $id_e,
                $document['id_d'],
                'Lancement du job via set-new-status-document-batch'
            );
            $io->progressAdvance();
            $io->newLine();
        }

        $io->progressFinish();
        $io->note(sprintf("%s documents ont été modifiés\n", count($documents)));

        $io->success('Done.');

        return Command::SUCCESS;
    }
}
