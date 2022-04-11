<?php

use Pastell\Client\Crypto\CryptoClientException;
use Psr\Http\Client\ClientExceptionInterface;

class LibersignTestV2 extends ChoiceActionExecutor
{
    /**
     * @throws RecoverableException
     * @throws UnrecoverableException
     * @throws CryptoClientException
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    public function go()
    {
        $recuperateur = $this->getRecuperateur();

        /** @var Libersign $connector */
        $connector = $this->getMyConnecteur();
        $publicCertificate = $recuperateur->get('publicCertificate');
        $dataToSignList = $recuperateur->get('dataToSignList');
        $filePath = $this->getConnecteurProperties()->getFilePath('libersign_test_document');

        if ($publicCertificate && !$dataToSignList) {
            echo $connector->generateDataToSign($filePath, $publicCertificate, 'Signature de test');
            return true;
        }

        if (!$dataToSignList) {
            throw new UnrecoverableException("Aucune donnée de signature n'a été trouvée.");
        }
        $dataToSignListDecoded = json_decode($dataToSignList, true);
        $generatedDataToSign = json_decode($recuperateur->get('generatedDataToSign'), true);
        $dataToSign = $generatedDataToSign['dataToSignList'];
        foreach ($dataToSignListDecoded as $index => $signature) {
            $dataToSign[$index]['signatureValue'] = $signature;
        }

        $signedFile = $connector->generateSignature(
            $filePath,
            $publicCertificate,
            $dataToSign,
            $generatedDataToSign['signatureDateTime'],
            'Signature de test'
        );

        $this->getConnecteurProperties()->addFileFromData(
            'libersign_test_document_result',
            $this
                ->getConnecteurProperties()
                ->getFileNameWithoutExtension('libersign_test_document') . '.' . $signedFile->extension,
            $signedFile->signature
        );
        $this->redirect("/Connecteur/edition?id_ce=" . $this->id_ce);
    }

    public function displayAPI()
    {
        throw new Exception("Nothing to display");
    }

    /**
     * @throws Exception
     */
    public function display()
    {
        $this->setViewParameter('libersignConnecteur', $this->getMyConnecteur());
        $this->renderPage('Test de Libersign', __DIR__ . '/../template/LibersignTest.php');
        return true;
    }
}
