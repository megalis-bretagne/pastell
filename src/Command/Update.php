<?php

namespace Pastell\Command;

use Exception;
use Pastell\Updater;
use PastellLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Update extends BaseCommand
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
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->pastellLogger->enableConsoleHandler($output);
        $this->getIO()->title($this->getDescription());
        $this->updater->update();
        $this->getIO()->success("Updated");
        return Command::SUCCESS;
    }
}
