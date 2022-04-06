<?php

class ActesGeneriqueArreteChange extends ActionExecutor
{
    public function go()
    {
        if (! $this->getDonneesFormulaire()->get('arrete')) {
            return true;
        }

        $content_type = $this->getDonneesFormulaire()->getContentType('arrete');
        //Vérifier que le doc est en xml ou en pdf
        if (in_array($content_type, ["application/pdf","application/xml"])) {
            return true;
        }

        $filename = $this->getDonneesFormulaire()->getFileName('arrete');

        if (
            ! in_array($content_type, ["application/vnd.oasis.opendocument.text",
                                            "application/vnd.ms-office",
                                            "application/msword",
                                            "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
            ])
        ) {
                throw new Exception("Le document $filename est au format $content_type ! Or, il doit être au format PDF ou XML. Il sera bloqué par le tiers de télétransmission");
        }

        $pdfConverter = $this->getGlobalConnecteur('convertisseur-office-pdf');
        if (! $pdfConverter) {
            throw new Exception("Le document « $filename » n'est pas dans le bon format et aucun convertisseur PDF n'est configuré.");
        }

        $pdfConverter->convertField($this->getDonneesFormulaire(), 'arrete', 'arrete');
        $this->setLastMessage("Le document « $filename » a été converti au format PDF pour respecter la norme ACTES");
        return true;
    }
}
