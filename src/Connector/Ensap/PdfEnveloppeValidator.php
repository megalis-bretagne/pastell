<?php

namespace Pastell\Connector\Ensap;

use Exception;
use InvalidArgumentException;
use SimpleXMLElement;

use function in_array;

class PdfEnveloppeValidator
{
    /**
     * @throws Exception
     */
    public function validatePdfFiles(array $files, string $xml): bool
    {
        $xmlElement = new SimpleXMLElement($xml);
        $xmlFiles = [];
        foreach ($xmlElement->assure as $assure) {
            $fileName = (string)$assure->gestionnaire->document->nom_fichier;
            if (in_array($fileName, $xmlFiles, true)) {
                throw new InvalidArgumentException("Le fichier {$fileName} est déjà référencé dans le XML");
            }
            $xmlFiles[] = $fileName;
        }

        foreach ($files as $file) {
            if (!in_array($file, $xmlFiles, true)) {
                throw new InvalidArgumentException("Le fichier {$file} n'est pas référencé dans le XML");
            }
        }

        foreach ($xmlFiles as $xmlFile) {
            if (!in_array($xmlFile, $files, true)) {
                throw new InvalidArgumentException("Le fichier {$xmlFile} n'est pas dans la liste de fichiers fournie");
            }
        }
        return true;
    }
}
