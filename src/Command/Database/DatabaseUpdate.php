<?php

namespace Pastell\Command\Database;

use Exception;
use Pastell\Command\BaseCommand;
use Pastell\Database\DatabaseUpdater;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DatabaseUpdate extends BaseCommand
{
    public function __construct(
        private readonly DatabaseUpdater $databaseUpdater,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:database:update')
            ->setDescription('Mets à jour la base de données en fonction du fichier de définition')
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Truncate the table journal_historique without asking'
            );
        ;
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $force = $input->getOption('force') ?? false;
        $this->getIO()->title('Mise à jour de la base de données');
        $queries = $this->databaseUpdater->getQueries();
        if (! $queries) {
            $this->getIO()->text('La base de données est déjà à jour');
            $this->getIO()->success('Done');
            return self::SUCCESS;
        }
        if (! $force) {
            $this->getIO()->text('Requêtes à passer pour mettre à jour la base de données: ');
            foreach ($queries as $query) {
                $this->getIO()->text($query);
            }
            $confirm =  $this->getIO()->confirm(
                'Êtes-vous certain de vouloir exécuter ces requêtes ?',
                false
            );
        } else {
            $confirm = true;
        }
        if (! $confirm) {
            $this->getIO()->note('Abandon de la modification de la base de données');
            return self::FAILURE;
        }

        $this->databaseUpdater->update();
        $this->getIO()->success('La base de données a été modifiée');
        return self::SUCCESS;
    }
}
