<?php

class PieceMarcheDocumentChange extends ActionExecutor
{
    public function go()
    {
        $content_type = $this->getDonneesFormulaire()->getContentType('document');
        //Vérifier que le doc est en xml ou en pdf
        if (in_array($content_type, array("application/pdf"))) {
            return true;
        }

        $filename = $this->getDonneesFormulaire()->getFileName('document');

        if (
            ! in_array($content_type, array("application/vnd.oasis.opendocument.text",
                "application/vnd.ms-office",
                "application/msword",
                "application/vnd.openxmlformats-officedocument.wordprocessingml.document"))
        ) {
            throw new Exception("Le document $filename est au format $content_type ! Or, il doit être au format PDF");
        }

        $pdfConverter = $this->getGlobalConnecteur('convertisseur-office-pdf');
        if (! $pdfConverter) {
            throw new Exception("Le document « $filename » n'est pas dans le bon format et aucun convertisseur PDF n'est configuré.");
        }

        $pdfConverter->convertField($this->getDonneesFormulaire(), 'document', 'document');
        $this->setLastMessage("Le document « $filename » a été converti au format PDF");
        return true;
    }
}
