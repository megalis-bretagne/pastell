<?php

namespace Pastell\Command;

use PastellBootstrap;
use PastellLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreatePesViewerConnecteur extends Command
{
    /**
     * @var PastellBootstrap
     */
    private $pastellBootstrap;
    private $pastellLogger;

    public function __construct(PastellBootstrap $pastellBootstrap, PastellLogger $pastellLogger)
    {
        parent::__construct();
        $this->pastellBootstrap = $pastellBootstrap;
        $this->pastellLogger = $pastellLogger;
    }

    protected function configure()
    {
        $this
            ->setName('app:create-pes-viewer-connecteur')
            ->setDescription('Create and associate a global PES Viewer connector if not exists')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->pastellLogger->enableStdOut(true);
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());
        $this->pastellBootstrap->installPESViewerConnecteur();
        $io->success('Done');
        return 0;
    }
}
