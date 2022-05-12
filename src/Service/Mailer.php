<?php

declare(strict_types=1);

namespace Pastell\Service;

use Symfony\Bridge\Twig\Mime\BodyRenderer;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Mailer
{
    private \Symfony\Component\Mailer\Mailer $mailer;
    private BodyRenderer $twigBodyRenderer;

    public function __construct(
        private readonly string $mailer_dsn,
        private readonly string $email_template_path,
        private readonly string $plateforme_mail,
    ) {
    }

    public function setMailer(\Symfony\Component\Mailer\Mailer $mailer): void
    {
        $this->mailer = $mailer;
    }

    private function getMailer(): \Symfony\Component\Mailer\Mailer
    {
        if (empty($this->mailer)) {
            $transport = Transport::fromDsn(
                $this->mailer_dsn
            );
            $this->mailer = new \Symfony\Component\Mailer\Mailer($transport);
        }
        return $this->mailer;
    }

    private function render(TemplatedEmail $templatedEmail): void
    {
        if (empty($this->twigBodyRenderer)) {
            $loader = new FilesystemLoader($this->email_template_path);
            $twigEnv = new Environment($loader);
            $this->twigBodyRenderer = new BodyRenderer($twigEnv);
        }
        $this->twigBodyRenderer->render($templatedEmail);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function send(TemplatedEmail $templatedEmail): void
    {
        if ($templatedEmail->getFrom() === []) {
            $templatedEmail->from($this->plateforme_mail);
        }
        $this->render($templatedEmail);
        $this->getMailer()->send($templatedEmail);
    }
}
