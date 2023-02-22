<?php

class XMLFormattage
{
    private function getDomDocument(): DOMDocument
    {
        $domDocument = new DOMDocument();
        //libxml_use_internal_errors(true);
        $domDocument->preserveWhiteSpace = false;
        $domDocument->formatOutput = true;
        return $domDocument;
    }

    /**
     * @param string $filepath
     * @throws UnrecoverableException
     */
    public function changeFileOutputFormat(string $filepath): void
    {
        $domDocument = $this->getDomDocument();
        if (! $domDocument->load($filepath)) {
            throw new UnrecoverableException("Impossible de charger le XML depuis $filepath");
        }

        if (! $domDocument->save($filepath)) {
            throw new UnrecoverableException("Impossible de sauvegarder le fichier $filepath");
        }
    }

    /**
     * @param string $filepath
     * @return string
     * @throws UnrecoverableException
     */
    public function getString(string $filepath): string
    {
        $domDocument = $this->getDomDocument();

        if (! $domDocument->load($filepath)) {
            throw new UnrecoverableException("Impossible de charger le XML depuis $filepath");
        }
        return $domDocument->saveXML();
    }
}
