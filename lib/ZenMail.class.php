<?php

use Monolog\Logger;

class ZenMail
{
    public const DEFAULT_CHARSET = 'UTF-8';

    private $fileContentType;

    /** @var string */
    private $destinataire;
    private $sujet;
    private $contenu;
    private $image;

    private $emetteur;
    private $reply_to;

    private $charset;

    private $attachment;

    private $disable_mail_sending;
    private $all_info;

    private $return_path;
    private $extra_headers = array();

    private $logger;

    public function __construct(FileContentType $fileContentType, Logger $logger)
    {
        $this->setCharset(self::DEFAULT_CHARSET);
        $this->image = array();
        $this->fileContentType = $fileContentType;
        $this->disable_mail_sending = false;
        $this->logger = $logger;
    }

    public function setCharset($charset)
    {
        $this->charset = $charset;
    }

    public function disableMailSending()
    {
        $this->disable_mail_sending = true;
    }

    public function getAllInfo()
    {
        return $this->all_info;
    }

    /**
     * @param string $nom
     * @param string $mail
     * @param string $reply_to
     */
    public function setEmetteur(string $nom, string $mail, string $reply_to = ''): void
    {
        $this->emetteur = $this->getDisplayNamedMail($nom, $mail);
        $this->reply_to = ($reply_to && ($reply_to !== '')) ? $reply_to : $mail;
    }

    public function getDestinataire(): string
    {
        return $this->destinataire;
    }

    public function setDestinataire($destinataire): void
    {
        $regex = '/^"(.*)"\W*<(.*)>$/';
        $matches = [];
        if (preg_match($regex, $destinataire, $matches)) {
            $this->destinataire = $this->getDisplayNamedMail($matches[1], $matches[2]);
        } else {
            $this->destinataire = $destinataire;
        }
    }

    private function getDisplayNamedMail(string $displayName, string $mail): string
    {
        return '=?utf-8?B?' . base64_encode($displayName) . '?=' . "<$mail>";
    }

    public function setReturnPath($return_path)
    {
        $this->return_path = $return_path;
    }

    public function setSujet($sujet)
    {
        $this->sujet = $this->getFormatedMimeHeadder($sujet);
    }

    public function getSujet()
    {
        return $this->sujet;
    }

    public function getContenu()
    {
        return $this->contenu;
    }

    private function getFormatedMimeHeadder($value)
    {
        $preferences = array(
                "input-charset" => $this->charset,
                "output-charset" => "UTF-8",
                "line-break-chars" => PHP_EOL,
                "scheme" => 'Q',
        );
        $formated_header = mb_substr(iconv_mime_encode("", $value, $preferences), 2);
        return $formated_header;
    }

    public function setContenu($script, $info)
    {
        ob_start();
            include($script);
            $this->contenu = ob_get_contents();
        ob_end_clean();
    }

    public function resetExtraHeaders()
    {
        $this->extra_headers = array();
    }

    public function addExtraHeaders($header_line)
    {
        $this->extra_headers[] = $header_line;
    }

    public function setContenuText($content)
    {
        $this->contenu = $content;
    }

    public function resetAttachment()
    {
        $this->attachment = array();
    }

    public function addAttachment($filename, $filepath)
    {
        $this->attachment[$filename] = $filepath;
    }


    /**
     * @throws Exception
     */
    public function send()
    {
        foreach (['emetteur','reply_to','destinataire','sujet','contenu'] as $key) {
            if (!isset($this->$key)) {
                throw new Exception("ZenMail - $key non défini");
            }
        }

        if ($this->attachment) {
            $this->sendTxtMailWithAttachment();
        } else {
            $entete =   "From: " . $this->emetteur . PHP_EOL .
                        "Reply-To: " . $this->reply_to . PHP_EOL .
                        "Content-Type: text/plain; charset=\"" . $this->charset . "\"";

            if ($this->return_path) {
                $entete .= PHP_EOL . "Return-Path: {$this->return_path}";
            }
            foreach ($this->extra_headers as $header_line) {
                $entete .= PHP_EOL . $header_line;
            }

            $this->mail($this->destinataire, $this->sujet, $this->contenu, $entete, $this->getReturnPathCommand());
        }
    }

    private function mail($destinataire, $sujet, $contenu, $entete, $return_path)
    {
        $log_message = "Envoi d'un mail vers $destinataire (sujet = $sujet)";

        $mail_info = [
            'destinataire' => $destinataire,
            'sujet' => $sujet,
            'contenu' => $contenu,
            'entete' => $entete,
            'return_path' => $return_path
        ];

        if (! $this->disable_mail_sending) {
            mail($destinataire, $sujet, $contenu, $entete, $return_path);
        } else {
            $this->all_info[] = $mail_info;
            $log_message = "[TEST MESSAGE NON ENVOYE] $log_message";
        }
        $this->logger->info($log_message);
        $this->logger->debug("Envoi d'un mail", $mail_info);
    }


    private function getReturnPathCommand()
    {
        if (! $this->return_path) {
            return "";
        }
        return "-f {$this->return_path}";
    }

    private function sendTxtMailWithAttachment()
    {
        $boundary = $this->getBoundary();
        $entete =   "From: " . $this->emetteur . PHP_EOL .
                "Reply-To: " . $this->reply_to . PHP_EOL .
                "MIME-Version: 1.0" . PHP_EOL .
                "Content-Type: multipart/mixed; boundary=\"$boundary\"";

        if ($this->return_path) {
            $entete .= PHP_EOL . "Return-Path: {$this->return_path}";
        }

        $message = "This is a multi-part message in MIME format" . PHP_EOL . PHP_EOL;

        $message .= "--" . $boundary . PHP_EOL .
        "Content-Type: text/plain; charset=\"" . $this->charset . "\"" . PHP_EOL .
        "Content-Transfer-Encoding: 8bit" . PHP_EOL .
        PHP_EOL .
        $this->contenu . PHP_EOL . PHP_EOL;

        foreach ($this->attachment as $filename => $filepath) {
            $content_type = $this->fileContentType->getContentType($filepath);
            $message .= "--" . $boundary . PHP_EOL;
            $message .=
            "Content-Type: $content_type; name=\"$filename\"" . PHP_EOL .
            "Content-Transfer-Encoding: base64" . PHP_EOL .
            "Content-Disposition: attachment, filename=\"$filename\"" . PHP_EOL . PHP_EOL;

            $attachment = chunk_split(base64_encode(file_get_contents($filepath)), 76, PHP_EOL);
            $message .= $attachment;
        }
        $message .= "--" . $boundary . "--" . PHP_EOL;

        $this->mail($this->destinataire, $this->sujet, $message, $entete, $this->getReturnPathCommand());
    }

    private function getBoundary()
    {
        return '_pastell_zen_mail_' .
                substr(sha1('ZenMail' . microtime()), 0, 12);
    }

    private function getTxtAlternative($html_content)
    {
            $search = array('@<script[^>]*?>.*?</script>@si',  // Strip out javascript
                    '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
                    '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
                    '@<![\s\S]*?--[ \t\n\r]*>@'        // Strip multi-line comments i
            );
            return preg_replace($search, '', $html_content);
    }


    public function addRelatedImage($filename, $file_path)
    {
        $this->image[$filename] = $file_path;
    }

    public function sendHTMLContent($html_content, $charset = "iso-8859-15", $txt_alternative = false)
    {

        if (! $txt_alternative) {
            $txt_alternative = $this->getTxtAlternative($html_content);
        }

        $boundary = $this->getBoundary();
        $boundary_related = $this->getBoundary();

        $entete =   "From: " . $this->emetteur . PHP_EOL .
                    "Reply-To: " . $this->reply_to . PHP_EOL .
                    "MIME-Version: 1.0" . PHP_EOL .
                    "Content-Type: multipart/alternative; boundary=\"$boundary\"";

        foreach ($this->extra_headers as $header_line) {
            $entete .= PHP_EOL . $header_line;
        }

        $message = "--" . $boundary . PHP_EOL .
                    "Content-Type: text/plain; charset=\"" . $this->charset . "\"" . PHP_EOL .
                    "Content-Transfer-Encoding: 8bit" . PHP_EOL .
                    PHP_EOL .
                    $txt_alternative . PHP_EOL .
                    PHP_EOL .
                    "--" . $boundary . PHP_EOL .
                    "Content-Type: multipart/related; boundary=\"$boundary_related\"" . PHP_EOL .
                    PHP_EOL .
                    "--" . $boundary_related . PHP_EOL .
                    "Content-Type: text/html; charset=\"" . $this->charset . "\"" . PHP_EOL .
                    "Content-Transfer-Encoding: 8bit" . PHP_EOL .
                    PHP_EOL .
                    $html_content . PHP_EOL .
                    PHP_EOL;
                    $i = 0;
        foreach ($this->image as $filename => $filepath) {
            $content_type = $this->fileContentType->getContentType($filepath);

            $message .= "--" . $boundary_related . PHP_EOL .
                         "Content-type: $content_type; filename=\"$filename\"" . PHP_EOL .
                         "Content-ID: <image$i>" . PHP_EOL .
                         "Content-transfer-encoding: base64" . PHP_EOL .
                         "Content-Disposition: inline, filename=\"$filename\"" . PHP_EOL .
                         PHP_EOL .
                         chunk_split(base64_encode(file_get_contents($filepath)));
            $i++;
        }
        $message .=
            "--" . $boundary_related . "--" . PHP_EOL . PHP_EOL .
            "--" . $boundary . "--";

        $this->mail($this->destinataire, $this->sujet, $message, $entete, $this->getReturnPathCommand());
    }
}
