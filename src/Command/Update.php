<?php

namespace Pastell\Command;

use Exception;
use JournalHistoriqueSQL;
use Pastell\Service\Journal\JournalHistoriqueService;
use Pastell\Updater;
use PastellLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Update extends Command
{
    public function __construct(
        private readonly Updater $updater,
        private readonly PastellLogger $pastellLogger,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:update')
            ->setDescription('Lance les scripts de mise à jour de version et de patch');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->pastellLogger->enableConsoleHandler($output);
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());
        $this->updater->update();
        $io->success("Updated");
        return 0;
    }
}
