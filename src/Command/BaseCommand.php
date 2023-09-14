<?php

namespace Pastell\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class BaseCommand extends Command
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
        parent::initialize($input, $output);
    }

    public function getIO(): SymfonyStyle
    {
        return $this->io;
    }
}
