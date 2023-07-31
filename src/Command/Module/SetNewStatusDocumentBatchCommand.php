<?php

declare(strict_types=1);

namespace Pastell\Command\Module;

use DocumentActionEntite;
use Pastell\Configuration\DocumentTypeValidation;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

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
        private readonly \FluxDefinitionFiles $fluxDefinitionFiles,
        private readonly DocumentTypeValidation $documentTypeValidation,
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

        if (count($documents) === 0) {
            $io->note(sprintf(
                "Il n'existe aucun document de type : %s pour l'entité : %s ayant pour statut : %s.",
                $type,
                $id_e,
                $oldStatus
            ));
            return Command::SUCCESS;
        }

        $this->checkExistingAction($type, $newStatus, $io);

        $io->note(sprintf(
            '%d documents, type : %s et entité : %s, vont passer du statut %s au statut %s.',
            count($documents),
            $type,
            $id_e,
            $oldStatus,
            $newStatus
        ));

        $answer = $io->ask('Etes-vous sûr (o/N) ?');
        if ($answer != 'o') {
            $io->note("Aucun document n'a été modifié");
            return Command::SUCCESS;
        }

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

    /**
     * @param mixed $type
     * @param mixed $newStatus
     * @param SymfonyStyle $io
     * @return void
     */
    public function checkExistingAction(mixed $type, mixed $newStatus, SymfonyStyle $io): void
    {
        $path = $this->fluxDefinitionFiles->getDefinitionPath($type);
        $file = Yaml::parseFile($path);
        $actions = $this->documentTypeValidation->getAllPossibleAction($file);

        if (!in_array($newStatus, $actions)) {
            $io->warning(sprintf("Le statut %s n'existe pas.", $newStatus));
        }
    }
}
