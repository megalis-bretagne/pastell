<?php

namespace Pastell\Command;

use PastellBootstrap;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AddDefaultFrequencies extends Command
{
    /**
     * @var PastellBootstrap
     */
    private $pastellBootstrap;

    public function __construct(PastellBootstrap $pastellBootstrap)
    {
        $this->pastellBootstrap = $pastellBootstrap;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:add-default-frequencies')
            ->setDescription('Adds default frequencies if no frequency currently exists.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());
        $this->pastellBootstrap->installConnecteurFrequenceDefault();
        $io->success('Done');
        return 0;
    }
}
