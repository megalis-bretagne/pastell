<?php

declare(strict_types=1);

namespace Pastell\Command\Daemon;

use Monolog\Logger;
use Pastell\Command\BaseCommand;
use PastellDaemon;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:daemon:start',
    description: 'Starts the daemon'
)]
final class StartDaemon extends BaseCommand
{
    private readonly PastellDaemon $daemon;

    public function __construct(
        private readonly \ObjectInstancier $objectInstancier,
    ) {
        /**
         * TODO: When everything is loaded by symfony autowiring, the correct logging channel should be injected
         */
        $this->objectInstancier->setInstance(
            Logger::class,
            $this->objectInstancier->getInstance(Logger::class)->withName('DAEMON')
        );
        $this->daemon = $this->objectInstancier->getInstance(PastellDaemon::class);
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        pcntl_signal(SIGTERM, fn() => $this->daemon->stop());

        $this->daemon->jobMaster();
    }
}
