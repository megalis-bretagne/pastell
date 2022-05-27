<?php

use Pastell\Mailer\Mailer;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Part\DataPart;

class MailSec extends MailsecConnecteur
{
    public const CONNECTEUR_ID = 'mailsec';

    public const TITRE_REPLACEMENT_REGEXP = "#%TITRE%#";
    public const ENTITE_REPLACEMENT_REGEXP = "#%ENTITE%#";
    public const LINK_REPLACEMENT_REGEXP = "#%LINK%#";

    private DonneesFormulaire $connecteurConfig;

    public function __construct(
        private readonly DocumentEmail $documentEmail,
        private readonly Journal $journal,
        private readonly EntiteSQL $entiteSQL,
        private readonly Mailer $mailer,
        private readonly string $websec_base,
    ) {
    }

    public function setConnecteurConfig(DonneesFormulaire $connecteurConfig)
    {
        $this->connecteurConfig = $connecteurConfig;
    }

    /**
     * @throws Exception
     */
    public function sendAllMail(int $id_e, string $id_d): void
    {
        foreach ($this->documentEmail->getInfo($id_d) as $email_info) {
            $this->sendEmail($id_e, $id_d, $email_info);
        }
    }

    /**
     * @throws Exception
     */
    public function sendOneMail(int $id_e, string $id_d, int $id_de): void
    {
        $email_info = $this->documentEmail->getInfoFromPK($id_de);
        $this->sendEmail($id_e, $id_d, $email_info);
    }

    /**
     * @throws Exception
     */
    public function processMessageItem(string $message, string $link): string
    {
        $docDonneesFormulaire = $this->getDocDonneesFormulaire();
        if ($docDonneesFormulaire) {
            $titre = $docDonneesFormulaire->getTitre();
            $message = $this->replace(self::TITRE_REPLACEMENT_REGEXP, $titre, $message);
            $message = $this->replace(self::LINK_REPLACEMENT_REGEXP, $link, $message);
            $message = $this->replaceFluxElement($message);
        }
        $connecteur_info = $this->getConnecteurInfo();
        $entite_info = $this->entiteSQL->getInfo($connecteur_info['id_e'] ?? 0);

        $message = $this->replace(self::ENTITE_REPLACEMENT_REGEXP, $entite_info['denomination'] ?? '', $message);

        return $message;
    }

    private function replaceFluxElement(string $message): string
    {
        preg_match_all(
            "#%FLUX:([^%]*)%#",
            $message,
            $matches
        );
        foreach ($matches[1] as $data) {
            if (substr($data, 0, 1) === '@') {
                $replacement = $this->replaceFluxElementFromFile($data);
            } else {
                $replacement = $this->getDocDonneesFormulaire()->get($data);
            }
            $message = $this->replace("#%FLUX:$data%#", $replacement, $message);
        }
        return $message;
    }

    /**
     * @param $data
     * @return bool|mixed|string
     * @throws Exception
     */
    private function replaceFluxElementFromFile($data)
    {
        // data => @mail_metadata:factur-x:data:bt-27%

        $srcForm = $this->getDocDonneesFormulaire();

        $fields = explode(':', $data);
        $v = substr($fields[0], 1);
        $metadata = $srcForm->getFileContent($v);
        $metadata = json_decode($metadata, true);
        if ($metadata === null) {
            throw new Exception("Erreur de lecture du contenu de $v");
        }

        $v = $metadata;
        for ($i = 1, $iMax = count($fields); $i < $iMax; $i++) {
            if (!array_key_exists($fields[$i], $v)) {
                throw new Exception("La clé ${fields[$i]} de $data n'existe pas, vérifier la syntaxe.");
            }
            $v = $v[$fields[$i]];
        }
        if (!is_numeric($v) && !is_string($v)) {
            throw new Exception("La valeur de $data n'est pas un type simple, vérifier la syntaxe.");
        }
        return $v;
    }

    private function replace(string $pattern, string $replacement, string $message): string
    {
        return preg_replace($pattern, $replacement, $message);
    }

    /**
     * @throws Exception
     */
    private function sendEmail($id_e, $id_d, $email_info)
    {
        $this->send($email_info['email'], $email_info['key']);
        $this->documentEmail->updateRenvoi($email_info['id_de']);
        $this->journal->addActionAutomatique(
            Journal::MAIL_SECURISE,
            $id_e,
            $id_d,
            'envoi',
            "Mail sécurisé envoyé à {$email_info['email']}"
        );
    }

    private function send(string $to, $mailPastellId = ''): void
    {
        $link = sprintf('%sindex.php?key=%s', $this->websec_base, $mailPastellId);
        $sujet = $this->processMessageItem($this->connecteurConfig->getWithDefault('mailsec_subject'), $link);
        $message = $this->processMessageItem($this->connecteurConfig->getWithDefault('mailsec_content'), $link);
        $content_html = $this->processMessageItem($this->connecteurConfig->getFileContent("content_html"), $link);

        $mailsec_from_description = $this->connecteurConfig->getWithDefault('mailsec_from_description');
        $mailsec_reply_to = $this->connecteurConfig->get('mailsec_reply_to', PLATEFORME_MAIL);

        $templatedEmail = (new TemplatedEmail())
            ->from(new Address(PLATEFORME_MAIL, $mailsec_from_description))
            ->to($to)
            ->subject($sujet)
            ->replyTo($mailsec_reply_to);

        if ($mailPastellId) {
            $templatedEmail
                ->getHeaders()
                ->addTextHeader(UndeliveredMail::PASTELL_RETURN_INFO_HEADER, $mailPastellId);
        }

        if ($content_html) {
            if ($this->connecteurConfig->get('embeded_image')) {
                foreach ($this->connecteurConfig->get('embeded_image') as $i => $filename) {
                    $datePart = DataPart::fromPath(
                        $this->connecteurConfig->getFilePath("embeded_image", $i),
                        "image$i"
                    );
                    $contentId = $datePart->getContentId();
                    $templatedEmail->attachPart($datePart);
                    $content_html = preg_replace(
                        "#cid:image$i#",
                        "cid:$contentId",
                        $content_html
                    );
                }
            }
            $templatedEmail->html($content_html)
                ->context([]);
        } else {
            // Hugly hack
            if (! str_contains($message, $link)) {
                $message .= "\n$link";
            }
            $templatedEmail->text($message);
        }
        $this->mailer->send($templatedEmail);
    }

    /**
     * @throws Exception
     */
    public function test(): string
    {
        $mailsec_reply_to = $this->connecteurConfig->get('mailsec_reply_to', PLATEFORME_MAIL);
        $this->send($mailsec_reply_to);
        return $mailsec_reply_to;
    }
}
