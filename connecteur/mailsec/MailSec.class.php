<?php

class MailSec extends MailsecConnecteur
{
    public const CONNECTEUR_ID = 'mailsec';

    public const TITRE_REPLACEMENT_REGEXP = "#%TITRE%#";
    public const ENTITE_REPLACEMENT_REGEXP = "#%ENTITE%#";
    public const LINK_REPLACEMENT_REGEXP = "#%LINK%#";

    /**
     * @var ZenMail
     */
    private $zenMail;

    /**
     * @var DocumentEmail
     */
    private $documentEmail;

    /**
     * @var Journal
     */
    private $journal;

    /**
     * @var DonneesFormulaire
     */
    private $connecteurConfig;

    private $sujet;
    private $mailsec_content;
    private $content_html;
    private $embeded_image;

    /**
     * @var EntiteSQL
     */
    private $entiteSQL;

    private $connecteurFactory;

    public function __construct(
        ZenMail $zenMail,
        DocumentEmail $documentEmail,
        Journal $journal,
        EntiteSQL $entiteSQL,
        ConnecteurFactory $connecteurFactory
    ) {
        $this->zenMail = $zenMail;
        $this->documentEmail = $documentEmail;
        $this->journal = $journal;
        $this->entiteSQL = $entiteSQL;
        $this->connecteurFactory = $connecteurFactory;
    }

    public function setConnecteurConfig(DonneesFormulaire $connecteurConfig)
    {
        $this->connecteurConfig = $connecteurConfig;
    }

    /**
     * @param $id_e
     * @param $id_d
     * @throws Exception
     */
    public function sendAllMail($id_e, $id_d)
    {
        foreach ($this->documentEmail->getInfo($id_d) as $email_info) {
            $this->configZenMail();
            $this->sendEmail($id_e, $id_d, $email_info);
        }
    }

    /**
     * @param $id_e
     * @param $id_d
     * @param $id_de
     * @throws Exception
     */
    public function sendOneMail($id_e, $id_d, $id_de)
    {
        $this->configZenMail();
        $email_info = $this->documentEmail->getInfoFromPK($id_de);
        $this->sendEmail($id_e, $id_d, $email_info);
    }

    /**
     * @throws Exception
     */
    private function configZenMail()
    {
        $this->setEmetteur();

        /** @var UndeliveredMail $undeliveredMail */
        $undeliveredMail = $this->connecteurFactory->getGlobalConnecteur('UndeliveredMail');

        if ($undeliveredMail) {
            $this->zenMail->setReturnPath($undeliveredMail->getReturnPath());
        }

        $this->sujet = $this->connecteurConfig->getWithDefault('mailsec_subject');
        $this->mailsec_content = $this->connecteurConfig->getWithDefault('mailsec_content');
        $this->content_html = $this->connecteurConfig->getFileContent("content_html");


        $docDonneesFormulaire = $this->getDocDonneesFormulaire();
        if ($docDonneesFormulaire) {
            $titre = $docDonneesFormulaire->getTitre();
            $this->replaceElement(self::TITRE_REPLACEMENT_REGEXP, $titre);
            $this->replaceFluxElement();
        }

        $connecteur_info = $this->getConnecteurInfo();
        $entite_info = $this->entiteSQL->getInfo($connecteur_info['id_e'] ?? 0);

        $this->replaceElement(self::ENTITE_REPLACEMENT_REGEXP, $entite_info['denomination'] ?? '');

        $this->zenMail->setSujet($this->sujet);
        $this->embeded_image = array();
        if ($this->connecteurConfig->get('embeded_image')) {
            foreach ($this->connecteurConfig->get('embeded_image') as $i => $filename) {
                $this->embeded_image[$filename] = $this->connecteurConfig->getFilePath("embeded_image", $i);
            }
            foreach ($this->embeded_image as $filename => $file_path) {
                $this->zenMail->addRelatedImage($filename, $file_path);
            }
        }
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
        for ($i = 1; $i < count($fields); $i++) {
            if (!key_exists($fields[$i], $v)) {
                throw new Exception("La clé ${fields[$i]} de $data n'existe pas, vérifier la syntaxe.");
            }
            $v = $v[$fields[$i]];
        }
        if (!is_numeric($v) && !is_string($v)) {
            throw new Exception("La valeur de $data n'est pas un type simple, vérifier la syntaxe.");
        }
        return $v;
    }

    /**
     * @throws Exception
     */
    private function replaceFluxElement()
    {
        preg_match_all(
            "#%FLUX:([^%]*)%#",
            $this->content_html . "\n" . $this->mailsec_content . "\n" . $this->sujet,
            $matches
        );
        foreach ($matches[1] as $data) {
            if (substr($data, 0, 1) === '@') {
                $replacement = $this->replaceFluxElementFromFile($data);
            } else {
                $replacement = $this->getDocDonneesFormulaire()->get($data);
            }
            $this->replaceElement("#%FLUX:$data%#", $replacement);
        }
    }

    private function replaceElement($pattern, $replacement)
    {
        $this->sujet = preg_replace($pattern, $replacement, $this->sujet);
        $this->mailsec_content = preg_replace($pattern, $replacement, $this->mailsec_content);
        $this->content_html = preg_replace($pattern, $replacement, $this->content_html);
    }

    /**
     * @throws Exception
     */
    private function sendEmail($id_e, $id_d, $email_info)
    {
        $link = WEBSEC_BASE . "index.php?key={$email_info['key']}";

        $this->zenMail->setDestinataire($email_info['email']);
        $this->zenMail->resetExtraHeaders();
        $this->zenMail->addExtraHeaders(UndeliveredMail::PASTELL_RETURN_INFO_HEADER . ": {$email_info['key']}");

        if ($this->content_html) {
            $this->replaceElement(self::LINK_REPLACEMENT_REGEXP, $link);
            $this->zenMail->sendHTMLContent($this->content_html);
        } else {
            if (preg_match(self::LINK_REPLACEMENT_REGEXP, $this->mailsec_content)) {
                $this->replaceElement(self::LINK_REPLACEMENT_REGEXP, $link);
                $message = $this->mailsec_content;
            } else {
                $message = "{$this->mailsec_content}\n$link";
            }

            $this->zenMail->setContenuText($message);
            $this->zenMail->send();
        }

        $this->documentEmail->updateRenvoi($email_info['id_de']);
        $this->journal->addActionAutomatique(
            Journal::MAIL_SECURISE,
            $id_e,
            $id_d,
            'envoi',
            "Mail sécurisé envoyé à {$email_info['email']}"
        );
    }

    private function setEmetteur()
    {
        $this->zenMail->setEmetteur(
            $this->connecteurConfig->getWithDefault('mailsec_from_description'),
            PLATEFORME_MAIL,
            $this->connecteurConfig->get('mailsec_reply_to')
        );
    }

    /**
     * @throws Exception
     */
    public function test(): string
    {
        $this->setEmetteur();
        $sujet = $this->connecteurConfig->getWithDefault('mailsec_subject');
        $this->zenMail->setSujet($sujet);
        $message = $this->connecteurConfig->getWithDefault('mailsec_content');
        $mailsec_reply_to = $this->connecteurConfig->get('mailsec_reply_to');

        $destinataire = ($mailsec_reply_to && ($mailsec_reply_to !== '')) ?
            $mailsec_reply_to : PLATEFORME_MAIL;
        $this->zenMail->setDestinataire($destinataire);
        $this->zenMail->setContenuText($message);
        $this->zenMail->send();
        return $destinataire;
    }
}
