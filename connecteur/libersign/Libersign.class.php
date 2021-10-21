<?php

use Pastell\Client\Crypto\CryptoClient;
use Pastell\Client\Crypto\CryptoClientException;
use Pastell\Client\Crypto\CryptoClientFactory;
use Psr\Http\Client\ClientExceptionInterface;

class Libersign extends SignatureConnecteur
{
    public const LIBERSIGN_SIGNATURE_CADES = 'CADES';
    public const LIBERSIGN_SIGNATURE_XADES = 'XADES';

    /** @var DonneesFormulaire */
    private $collectiviteProperties;

    /**
     * @var string
     */
    private $cryptoUrl;

    /**
     * @var string
     */
    private $signatureType;

    /**
     * @var ConnecteurFactory
     */
    private $connecteurFactory;
    /**
     * @var ConnecteurEntiteSQL
     */
    private $connecteurEntiteSql;
    /**
     * @var CryptoClientFactory
     */
    private $cryptoClientFactory;
    /**
     * @var CryptoClient
     */
    private $cryptoClient;

    public function __construct(
        ConnecteurFactory $connecteurFactory,
        ConnecteurEntiteSQL $connecteurEntiteSql,
        CryptoClientFactory $cryptoClientFactory
    ) {
        $this->connecteurFactory = $connecteurFactory;
        $this->connecteurEntiteSql = $connecteurEntiteSql;
        $this->cryptoClientFactory = $cryptoClientFactory;
    }

    public function setConnecteurConfig(DonneesFormulaire $collectiviteProperties)
    {
        $this->collectiviteProperties = $collectiviteProperties;

        $url = $collectiviteProperties->get('libersign_crypto_url');
        if (!$url) {
            $connectorId = $this->connecteurEntiteSql->getGlobal('libersign');
            if ($connectorId) {
                $connectorConfig = $this->connecteurFactory->getConnecteurConfig($connectorId);
                $url = $connectorConfig->get('libersign_crypto_url') ?: '';
            }
        }
        $this->cryptoUrl = $url;
        $this->signatureType = $collectiviteProperties->get(
            'libersign_signature_type'
        ) ?: self::LIBERSIGN_SIGNATURE_CADES;
        $this->cryptoClient = $this->cryptoClientFactory->getClient($this->cryptoUrl);
    }

    public function getNbJourMaxInConnecteur()
    {
        throw new Exception("Not implemented");
    }

    public function getSousType()
    {
        throw new Exception("Not implemented");
    }

    public function getDossierID($id, $name)
    {
        return "n/a";
    }

    public function sendDossier(FileToSign $dossier)
    {
        throw new BadMethodCallException("Not implemented");
    }

    /**
     * @deprecated 3.0
     */
    public function sendDocument(
        $typeTechnique,
        $sousType,
        $dossierID,
        $document_content,
        $content_type,
        array $all_annexes = array(),
        $date_limite = false,
        $visuel_pdf = ''
    ) {
        throw new Exception("Not implemented --");
    }

    public function getHistorique($dossierID)
    {
        throw new Exception("Not implemented");
    }

    public function getSignature($dossierID, $archive = true)
    {
        throw new Exception("Not implemented");
    }

    public function sendHeliosDocument(
        $typeTechnique,
        $sousType,
        $dossierID,
        $document_content,
        $content_type,
        $visuel_pdf,
        array $metadata = array()
    ) {
        throw new Exception("Not implemented");
    }

    public function getAllHistoriqueInfo($dossierID)
    {
        throw new Exception("Not implemented");
    }

    public function getLastHistorique($dossierID)
    {
        throw new Exception("Not implemented");
    }

    public function effacerDossierRejete($dossierID)
    {
        throw new Exception("Not implemented");
    }

    public function isLocalSignature()
    {
        return true;
    }

    public function displayLibersignJS(): void
    {
        // Variables included in template
        $libersign_applet_url = $this->collectiviteProperties->get('libersign_applet_url');
        $libersign_extension_update_url = $this->collectiviteProperties->get('libersign_extension_update_url');
        include_once __DIR__ . '/template/LibersignJS.php';
    }

    public function isFinalState(string $lastState): bool
    {
        throw new BadMethodCallException('Not implemented');
    }

    public function isRejected(string $lastState): bool
    {
        throw new BadMethodCallException('Not implemented');
    }

    public function isDetached($signature): bool
    {
        throw new BadMethodCallException('Not implemented');
    }

    /**
     * Workaround because IParapheur::getSignature() does not return only the signature
     *
     * @param $file
     * @return mixed
     */
    public function getDetachedSignature($file)
    {
        throw new BadMethodCallException('Not implemented');
    }

    /**
     * Workaround because IParapheur::getSignature() does not return only the signature
     *
     * @param $file
     * @return mixed
     */
    public function getSignedFile($file)
    {
        throw new BadMethodCallException('Not implemented');
    }

    /**
     * Workaround because it is embedded in IParapheur::getSignature()
     *
     * @param $signature
     * @return Fichier
     */
    public function getBordereauFromSignature($signature): ?Fichier
    {
        throw new BadMethodCallException('Not implemented');
    }

    /**
     * @param $dossierID
     */
    public function exercerDroitRemordDossier($dossierID)
    {
        throw new BadMethodCallException('Not implemented');
    }

    /**
     * @throws CryptoClientException
     * @throws ClientExceptionInterface
     */
    public function xadesGenerateDataToSign(string $filepath, string $certificate): string
    {
        return $this->cryptoClient->xades()->generateDataToSign($filepath, $certificate);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws CryptoClientException
     */
    public function xadesGenerateSignature(
        string $filepath,
        string $certificate,
        array $dataToSignList,
        string $signatureDateTime
    ): string {
        $payload = [
            'city' => $this->collectiviteProperties->get('libersign_city'),
            'zipCode' => $this->collectiviteProperties->get('libersign_cp'),
            'country' => 'France',
            'claimedRoles' => ['Ordonnateur'],
        ];
        return $this->cryptoClient->xades()->generateSignature(
            $filepath,
            $certificate,
            $dataToSignList,
            $signatureDateTime,
            $payload
        );
    }

    /**
     * @throws CryptoClientException
     * @throws ClientExceptionInterface
     */
    public function cadesGenerateDataToSign(string $filepath, string $certificate): string
    {
        return $this->cryptoClient->cades()->generateDataToSign($filepath, $certificate);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws CryptoClientException
     */
    public function cadesGenerateSignature(
        string $filepath,
        string $certificate,
        array $dataToSignList,
        string $signatureDateTime
    ): string {
        return $this->cryptoClient->cades()->generateSignature(
            $filepath,
            $certificate,
            $dataToSignList,
            $signatureDateTime
        );
    }

    /**
     * @throws CryptoClientException
     * @throws ClientExceptionInterface
     * @throws RecoverableException
     */
    public function generateDataToSign(
        string $filepath,
        string $certificate
    ): string {
        if ($this->signatureType === self::LIBERSIGN_SIGNATURE_CADES) {
            return $this->cadesGenerateDataToSign($filepath, $certificate);
        }

        if ($this->signatureType === self::LIBERSIGN_SIGNATURE_XADES) {
            return $this->xadesGenerateDataToSign($filepath, $certificate);
        }
        throw new \RecoverableException("Unknown signature type : " . $this->signatureType);
    }

    /**
     * @throws CryptoClientException
     * @throws ClientExceptionInterface
     * @throws RecoverableException
     */
    public function generateSignature(
        string $filepath,
        string $certificate,
        array $dataToSignList,
        string $signatureDateTime
    ): string {
        if ($this->signatureType === self::LIBERSIGN_SIGNATURE_CADES) {
            return $this->cadesGenerateSignature($filepath, $certificate, $dataToSignList, $signatureDateTime);
        }

        if ($this->signatureType === self::LIBERSIGN_SIGNATURE_XADES) {
            return $this->xadesGenerateSignature($filepath, $certificate, $dataToSignList, $signatureDateTime);
        }
        throw new \RecoverableException("Unknown signature type : " . $this->signatureType);
    }
}
