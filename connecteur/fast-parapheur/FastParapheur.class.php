<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'DocapostParapheurSoapClient.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'DocapostParapheurSoapClientException.php';
require_once PASTELL_PATH . DIRECTORY_SEPARATOR . 'pastell-core' . DIRECTORY_SEPARATOR . 'FileToSign.php';

class FastParapheur extends SignatureConnecteur
{
    public const PARAPHEUR_NB_JOUR_MAX_DEFAULT = 30;
    public const WSDL_URI = '/parapheur-soap/soap/v1/Documents?wsdl';
    public const REST_URI = '/parapheur-ws/rest/v1/';
    public const CIRCUIT_ON_THE_FLY_URI = self::REST_URI . '/documents/ondemand/%s/upload';

    private $url;
    private $subscriberNumber;
    private $circuits;

    private $connectionCertificatePath;
    private $connectionCertificatePassword;

    private $connectionCertificateCertOnly;
    private $connectionCertificateKeyOnly;
    private $connectionCertificateKeyCert;

    private $maxNumberOfDaysInParapheur;

    /**
     * @var SoapClientFactory
     */
    private $soapClientFactory;

    /**
     * @var TmpFolder
     */
    private $tmpFolder;

    /**
     * @var ZipArchive
     */
    private $zipArchive;

    /**
     * @var CurlWrapperFactory
     */
    private $curlWrapperFactory;

    /**
     * @var CurlWrapper
     */
    private $curlWrapper;

    public function __construct(
        SoapClientFactory $soapClientFactory,
        CurlWrapperFactory $curlWrapperFactory,
        TmpFolder $tmpFolder = null,
        ZipArchive $zipArchive = null
    ) {
        $this->soapClientFactory = $soapClientFactory;
        $this->curlWrapperFactory = $curlWrapperFactory;
        $this->setTmpFolder($tmpFolder ?? new TmpFolder());
        $this->setZipArchive($zipArchive ?? new ZipArchive());
    }

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire)
    {
        $this->url = $donneesFormulaire->get('wsdl');
        $this->subscriberNumber = $donneesFormulaire->get('numero_abonnement');
        $this->circuits = $donneesFormulaire->get('circuits');
        $this->maxNumberOfDaysInParapheur = $donneesFormulaire->get("parapheur_nb_jour_max");

        $this->connectionCertificatePath = $donneesFormulaire->getFilePath('certificat_connexion');
        $this->connectionCertificatePassword = $donneesFormulaire->get('certificat_password');

        $this->connectionCertificateCertOnly = $donneesFormulaire->getFilePath('certificat_connexion_cert_pem');
        $this->connectionCertificateKeyOnly = $donneesFormulaire->getFilePath('certificat_connexion_key_pem');
        $this->connectionCertificateKeyCert = $donneesFormulaire->getFilePath('certificat_connexion_key_cert_pem');

        $this->curlWrapper = $this->curlWrapperFactory->getInstance();
        $this->curlWrapper->setClientCertificate(
            $this->connectionCertificateCertOnly,
            $this->connectionCertificateKeyOnly,
            $this->connectionCertificatePassword
        );
    }

    /**
     * @param ZipArchive $zipArchive
     */
    public function setZipArchive(ZipArchive $zipArchive)
    {
        $this->zipArchive = $zipArchive;
    }

    /**
     * @param TmpFolder $tmpFolder
     */
    public function setTmpFolder(TmpFolder $tmpFolder)
    {
        $this->tmpFolder = $tmpFolder;
    }

    /**
     * @return DocapostParapheurSoapClient
     * @throws Exception
     */
    protected function getClient()
    {
        $stream_context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);

        $client = $this->soapClientFactory->getInstance(
            $this->url . self::WSDL_URI,
            [
                'login' => '',
                'local_cert' => $this->connectionCertificateKeyCert,
                'passphrase' => $this->connectionCertificatePassword,
                'trace' => 1,
                'exceptions' => 1,
                'use_curl' => 1,
                'userKeyOnly' => $this->connectionCertificateKeyOnly,
                'userCertOnly' => $this->connectionCertificateCertOnly,
                'stream_context' => $stream_context
            ],
            true
        );

        return new DocapostParapheurSoapClient($client);
    }

    /**
     * @throws Exception
     */
    public function testConnection()
    {
        return $this->getClient()->listRemainingAcknowledgements($this->subscriberNumber);
    }

    public function getNbJourMaxInConnecteur()
    {
        return $this->maxNumberOfDaysInParapheur ?: self::PARAPHEUR_NB_JOUR_MAX_DEFAULT;
    }

    public function getSousType()
    {
        return explode(';', $this->circuits);
    }

    public function getDossierID($id, $name)
    {
        // TODO: Implement getDossierID() method.
    }

    /**
     * @param FileToSign $file
     * @return bool
     * @throws Exception
     */
    public function sendDossier(FileToSign $file)
    {
        $temporaryDirectory = $this->tmpFolder->create();

        if ($file->annexes) {
            try {
                $archive = $this->generateArchive(
                    $temporaryDirectory,
                    $file->document->filename,
                    $file->document->filepath,
                    $file->annexes
                );
            } catch (Exception $e) {
                $this->lastError = $e->getMessage();
                return false;
            }
            $file->document->filepath = $archive;
            $file->document->filename = basename($archive);
            $file->document->content = file_get_contents($archive);
            $file->document->contentType = mime_content_type($archive);
        }

        $this->curlWrapper->addPostFile(
            'doc',
            $file->document->filepath,
            $file->document->filename,
            $file->document->contentType
        );

        if (!empty($file->circuit_configuration->content)) {
            if ($file->emailRecipients) {
                $this->curlWrapper->addPostData('email_destinataire', $file->emailRecipients);
            }
            if ($file->emailCc) {
                $this->curlWrapper->addPostData('email_cc', $file->emailCc);
            }
            if ($file->agents) {
                $this->curlWrapper->addPostData('agents', $file->agents);
            }
            $this->curlWrapper->addPostData('circuit', $file->circuit_configuration->content);
            $result_from_curl = $this->curlWrapper->get(
                $this->url . sprintf(self::CIRCUIT_ON_THE_FLY_URI, $this->subscriberNumber)
            );

            if ($this->curlWrapper->getLastError()) {
                $this->lastError = $this->curlWrapper->getLastError();
                return false;
            }
            $result = json_decode($result_from_curl, true);
            if ($result === null) {
                $this->lastError = "unable to decode json : $result_from_curl";
                return false;
            }

            if (isset($result['errorCode'])) {
                throw new SignatureException(
                    sprintf(
                        "Erreur %s : %s (%s)",
                        $result['errorCode'],
                        $result['userFriendlyMessage'],
                        $result['developerMessage']
                    )
                );
            }
            return $result;
        }

        try {
            return $this->getClient()->upload(
                $this->subscriberNumber,
                $file->circuit,
                $file->document->filename,
                $file->document->content
            );
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        } finally {
            $this->tmpFolder->delete($temporaryDirectory);
        }
    }

    /**
     * @param $filename
     * @param $sousType
     * @param $file_path
     * @param $document_content
     * @param $content_type
     * @param array $all_annexes
     * @param bool $date_limite
     * @param string $visuel_pdf
     * @return bool
     * @throws Exception
     * @deprecated 3.0. Use sendDossier() instead.
     */
    public function sendDocument(
        $filename,
        $sousType,
        $file_path,
        $document_content,
        $content_type,
        array $all_annexes = [],
        $date_limite = false,
        $visuel_pdf = ''
    ) {
        $file = new FileToSign();
        $file->circuit = $sousType;
        $file->document = new Fichier();
        $file->document->filename = $filename;
        $file->document->filepath = $file_path;
        $file->document->content = $document_content;

        foreach ($all_annexes as $annexe) {
            $currentAnnexe = new Fichier();
            $currentAnnexe->filepath = $annexe['file_path'] ?? null;
            $currentAnnexe->filename = $annexe['name'] ?? null;
            $file->annexes[] = $currentAnnexe;
        }

        return $this->sendDossier($file);
    }

    public function getHistorique($dossierID)
    {
        // TODO: Implement getHistorique() method.
    }

    public function getSignature($documentId, $archive = true)
    {
        try {
            return $this->getClient()->download($documentId);
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * @param $filename
     * @param $sousType
     * @param $dossierID
     * @param $document_content
     * @param $content_type
     * @param $visuel_pdf
     * @param array $metadata
     * @return bool
     */
    public function sendHeliosDocument(
        $filename,
        $sousType,
        $dossierID,
        $document_content,
        $content_type,
        $visuel_pdf,
        array $metadata = []
    ) {

        try {
            return $this->getClient()->upload($this->subscriberNumber, $sousType, $filename, $document_content);
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function getAllHistoriqueInfo($documentId)
    {
        try {
            return $this->getClient()->history($documentId);
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    public function getLastHistorique($history)
    {
        $lastLog = end($history);
        $date = date("d/m/Y H:i:s", strtotime($lastLog->date));
        return $date . " : [" . $lastLog->stateName . "]";
    }

    public function effacerDossierRejete($documentId)
    {
        try {
            $this->getLogger()->debug("Effacement du dossier $documentId rejeté");
            $result = $this->getClient()->delete($documentId);
            $this->getLogger()->debug("Résultat de l'effacement du dossier $documentId: " . json_encode($result));
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            $this->getLogger()->error("Impossible d'effacer le dossier $documentId: " . $e->getMessage());
            return false;
        }
        return $result;
    }

    /**
     * @codeCoverageIgnore
     * @return bool
     */
    public function isFastSignature()
    {
        return true;
    }

    /**
     * Generates an archive containing the document and its annexes
     * The name of the archive will be the name of the document (with its extension).zip
     * Example : main_document.pdf.zip
     *
     * @param $temporaryDirectory
     * @param $filename
     * @param $document
     * @param Fichier[] $annexes
     * @return string
     * @throws Exception
     */
    private function generateArchive(string $temporaryDirectory, $filename, $document, array $annexes)
    {
        $zipPath = $temporaryDirectory . DIRECTORY_SEPARATOR . $filename . '.zip';
        if (!$this->zipArchive->open($zipPath, ZipArchive::CREATE)) {
            throw new Exception("Impossible de créer le fichier d'archive : $zipPath");
        }
        $this->zipArchive->addFile($document, $filename);

        foreach ($annexes as $annexe) {
            $this->zipArchive->addFile($annexe->filepath, $annexe->filename);
        }
        $this->zipArchive->close();

        return $zipPath;
    }

    public function isFinalState(string $lastState): bool
    {
        return strstr($lastState, '[Classé]');
    }

    public function isRejected(string $lastState): bool
    {
        return strstr($lastState, '[Refusé]');
    }

    public function isDetached($signature): bool
    {
        return false;
    }

    /**
     * Workaround because IParapheur::getSignature() does not return only the signature
     *
     * @param $file
     * @return mixed
     */
    public function getDetachedSignature($file)
    {
        return $file;
    }

    /**
     * Workaround because IParapheur::getSignature() does not return only the signature
     *
     * @param $file
     * @return mixed
     */
    public function getSignedFile($file)
    {
        return $file;
    }


    /**
     * Workaround because it is embedded in IParapheur::getSignature()
     *
     * @param $signature
     * @return Fichier
     */
    public function getBordereauFromSignature($signature): ?Fichier
    {
        return null;
    }

    public function hasBordereau()
    {
        return false;
    }

    /**
     * @param $dossierID
     */
    public function exercerDroitRemordDossier($dossierID)
    {
        throw new BadMethodCallException('Not implemented');
    }
}
