<?php

use Pastell\Connector\Ensap\PdfEnveloppeValidator;
use Pastell\Connector\Ensap\XmlEnveloppeValidator;

class Ensap extends CFNConnecteur
{
    private const CHAMP_RETOUR_CFN = 'retour_cfn';
    public function __construct(
        private readonly PdfEnveloppeValidator $pdfEnveloppeValidator,
        private readonly XmlEnveloppeValidator $xmlEnveloppeValidator,
        private readonly TmpFolder $tmpFolder,
    ) {
    }

    /**
     * @throws Exception
     */
    public function send(array $bp_files, string $xml, DonneesFormulaire $donneesFormulaire): void
    {
        $this->xmlEnveloppeValidator->validateXsd($xml);
        $this->pdfEnveloppeValidator->validatePdfFiles(array_keys($bp_files), $xml);
        $this->xmlEnveloppeValidator->validateContent($xml);

        try {
            $tmpFolder = $this->tmpFolder->create();
            $archiveName = $this->getArchiveName($xml);
            $donneesFormulaire->addFileFromData(
                self::CHAMP_RETOUR_CFN,
                $archiveName . '.tar.gz',
                $this->createTarGzArchive($archiveName, $bp_files, $xml, $tmpFolder)
            );
        } catch (Exception $e) {
            throw new RuntimeException('Cannot create temporary directory : ' . $e->getMessage());
        }
    }

    private function createTarGzArchive(
        string $archiveName,
        array $documentContent,
        string $xmlContent,
        string $tmpFolder
    ): bool|string {
        $tar = new PharData("$tmpFolder/$archiveName.tar");
        foreach ($documentContent as $filename => $filecontent) {
            $tar->addFromString($filename, $filecontent);
        }
        $tar->addFromString('index.xml', $xmlContent);
        $tar->compress(Phar::GZ);
        unlink("$tmpFolder/$archiveName.tar");

        $archivePath = "$tmpFolder/$archiveName.tar.gz";
        return file_get_contents($archivePath);
    }

    /**
     * @throws Exception
     */
    private function getArchiveName(string $xml): string
    {
        $xmlElement = new SimpleXMLElement($xml);
        return (string)$xmlElement->message->nom_fichier;
    }
}
