<?php

declare(strict_types=1);

namespace Pastell\Command\Daemon;

use ObjectInstancier;
use Pastell\Command\BaseCommand;
use Pastell\Mailer\Mailer;
use Pastell\System\Check\DaemonCheck;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

#[AsCommand(
    name: 'app:daemon:notify-check',
    description: 'Notify ADMIN_EMAIL when daemon check is KO'
)]
final class NotifyCheck extends BaseCommand
{
    public function __construct(
        private readonly ObjectInstancier $objectInstancier,
        private readonly DaemonCheck $daemonCheck,
        private readonly Mailer $pastellMailer
    ) {
        parent::__construct();
    }

    /**
     * @throws TransportExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $daemonHealth = $this->daemonCheck->check()[0];
        if ($daemonHealth->isSuccess()) {
            if ($this->getIO()->isVerbose()) {
                $this->getIO()->writeln(sprintf(
                    '[OK] %s: %s',
                    $daemonHealth->label,
                    $daemonHealth->result
                ));
            }
            return 0;
        } else {
            $message = sprintf(
                '[KO] %s du site %s: %s',
                $daemonHealth->label,
                $this->objectInstancier->getInstance('site_base'),
                $daemonHealth->result
            );
            $templatedEmail = (new TemplatedEmail())
                ->to(...$this->objectInstancier->getInstance('admin_email'))
                ->subject('[PASTELL] Alerte tâches automatiques')
                ->text($message);
            $this->pastellMailer->send($templatedEmail);
            if ($this->getIO()->isVerbose()) {
                $this->getIO()->writeln($message);
            }
            return 2;
        }
    }
}
