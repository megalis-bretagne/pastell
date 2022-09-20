<?php

declare(strict_types=1);

namespace Pastell\Command;

use PastellLogger;
use ReflectionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Bootstrap extends BaseCommand
{
    public function __construct(
        private readonly \Pastell\Bootstrap\Bootstrap $bootstrap,
        private readonly PastellLogger $pastellLogger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:bootstrap')
            ->setDescription('Bootstrap Pastell (create default globals connectors, ...)')
        ;
    }

    /**
     * @throws ReflectionException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->pastellLogger->enableConsoleHandler($output);
        $this->getIO()->title($this->getDescription());
        $this->bootstrap->bootstrap();
        $this->getIO()->success('Done');
        return 0;
    }
}
