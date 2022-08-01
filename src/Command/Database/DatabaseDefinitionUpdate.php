<?php

namespace Pastell\Command\Database;

use Exception;
use Pastell\Command\BaseCommand;
use Pastell\Database\DatabaseUpdater;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DatabaseDefinitionUpdate extends BaseCommand
{
    public function __construct(
        private readonly DatabaseUpdater $databaseUpdater,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('dev:database:update-definition')
            ->setDescription('Mets à jour les fichiers de définition de la bases de données');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->getIO()->title('Mise à jour des fichiers de définition de la base de données');
        $this->databaseUpdater->updateDefinitionFromDatabase();
        $this->getIO()->success('Les fichiers sont à jour');
        return self::SUCCESS;
    }
}
