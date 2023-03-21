<?php

class ActesMessageMetier
{
    public const ACTES_NAMESPACE = 'http://www.interieur.gouv.fr/ACTES#v1.1-20040216';
    public const INSEE_NAMESPACE = 'http://xml.insee.fr/schema';
    public const XSI_NAMESPACE = 'http://www.w3.org/2001/XMLSchema-instance';
    public const XSI_SCHEMA_LOCATION = 'http://www.interieur.gouv.fr/ACTES#v1.1-20040216 actesv1_1.xsd';

    public const XMLNS_ACTES = 'xmlns:actes';
    public const XMLNS_INSEE = 'xmlns:insee';
    public const XMLNS_XSI = 'xmlns:xsi';
    public const XSI_SCHEMA_LOCATION1 = 'xsi:schemaLocation';

    /**
     * @var DOMDocument $domDocument
     */
    private $domDocument;

    /**
     * @var Acte $acte
     */
    private $acte;

    public function __construct(Acte $acte)
    {
        $this->acte = $acte;
        $this->setDomDocument(new DOMDocument('1.0', 'ISO-8859-1'));
    }

    /**
     * @param DOMDocument $domDocument
     */
    public function setDomDocument(DOMDocument $domDocument)
    {
        $this->domDocument = $domDocument;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function generateXmlTransmissionFile(): string
    {
        $actesElement = $this->createActesElement();

        $this->addCodeMatiereElements($actesElement);

        $objectElement = $this->domDocument->createElement('actes:Objet', $this->acte->object);
        $actesElement->appendChild($objectElement);

        $classificationDateElement = $this->domDocument->createElement(
            'actes:ClassificationDateVersion',
            $this->acte->classificationDate
        );
        $actesElement->appendChild($classificationDateElement);

        $documentElement = $this->createDocumentElement();
        $actesElement->appendChild($documentElement);

        $annexesElement = $this->domDocument->createElement('actes:Annexes');
        $annexesElement->setAttribute('actes:Nombre', (string)count($this->acte->annexes));
        if ($this->acte->annexes) {
            $this->addAnnexes($annexesElement, $this->acte->annexes);
        }
        $actesElement->appendChild($annexesElement);

        $documentPapier = $this->domDocument->createElement('actes:DocumentPapier', $this->acte->documentPapier);
        $actesElement->appendChild($documentPapier);

        $this->domDocument->appendChild($actesElement);

        return $this->domDocument->saveXML();
    }

    /**
     * @return DOMElement
     */
    public function createActesElement(): DOMElement
    {
        $actesElement = $this->domDocument->createElement('actes:Acte');
        $actesElement->setAttribute(self::XMLNS_ACTES, self::ACTES_NAMESPACE);
        $actesElement->setAttribute(self::XMLNS_INSEE, self::INSEE_NAMESPACE);
        $actesElement->setAttribute(self::XMLNS_XSI, self::XSI_NAMESPACE);
        $actesElement->setAttribute(self::XSI_SCHEMA_LOCATION1, self::XSI_SCHEMA_LOCATION);
        $actesElement->setAttribute('actes:Date', $this->acte->date);
        $actesElement->setAttribute('actes:NumeroInterne', $this->acte->numero);
        $actesElement->setAttribute('actes:CodeNatureActe', $this->acte->codeNature);

        return $actesElement;
    }

    /**
     * @param DOMElement $actesElement
     * @throws Exception
     */
    public function addCodeMatiereElements(DOMElement $actesElement)
    {
        $regex = '/^\d+(\.\d+)*/';
        $matches = [];
        preg_match($regex, $this->acte->classification, $matches);
        $classification = explode('.', $matches[0]);
        if (count($classification) > 5) {
            throw new ActesMessageMetierException(
                "The classification has more than 5 levels : " . $this->acte->classification
            );
        }
        foreach ($classification as $key => $value) {
            $codeMatiereElement = $this->domDocument->createElement("actes:CodeMatiere" . (string)($key + 1));
            $codeMatiereElement->setAttribute('actes:CodeMatiere', $value);
            $actesElement->appendChild($codeMatiereElement);
        }
    }

    /**
     * @return DOMElement
     */
    public function createDocumentElement(): DOMElement
    {
        $documentElement = $this->domDocument->createElement('actes:Document');
        $documentFileElement = $this->createFileNameElement($this->acte->acte);
        $documentElement->appendChild($documentFileElement);
        return $documentElement;
    }

    private function addAnnexes(DOMElement $annexesElement, array $annexes)
    {
        foreach ($annexes as $annexe) {
            $annexeElement = $this->domDocument->createElement('actes:Annexe');
            $annexeFileElement = $this->createFileNameElement($annexe);
            $annexeElement->appendChild($annexeFileElement);
            $annexesElement->appendChild($annexeElement);
        }
    }

    /**
     * @param $annexe
     * @return DOMElement
     */
    private function createFileNameElement($annexe): DOMElement
    {
        return $this->domDocument->createElement('actes:NomFichier', $annexe);
    }

    /**
     * @return string
     */
    public function generateXmlCancellationFile(): string
    {
        $cancellationElement = $this->domDocument->createElement('actes:Annulation');
        $cancellationElement->setAttribute(self::XMLNS_ACTES, self::ACTES_NAMESPACE);
        $cancellationElement->setAttribute(self::XMLNS_INSEE, self::INSEE_NAMESPACE);
        $cancellationElement->setAttribute(self::XMLNS_XSI, self::XSI_NAMESPACE);
        $cancellationElement->setAttribute(self::XSI_SCHEMA_LOCATION1, self::XSI_SCHEMA_LOCATION);
        $cancellationElement->setAttribute('actes:IDActe', $this->acte->idActe);

        $this->domDocument->appendChild($cancellationElement);
        return $this->domDocument->saveXML();
    }
}
