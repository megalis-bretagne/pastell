<?php

require_once(__DIR__ . "/GenerateXMLFromRelaxNg.class.php");


class GenerateXMLFromAnnotedRelaxNG extends GenerateXMLFromRelaxNg
{
    /**
     * @param SimpleXMLElement $element
     * @param DOMNode $domNodeResult
     * @throws Exception
     */
    protected function walkChildren(SimpleXMLElement $element, DOMNode $domNodeResult)
    {
        parent::walkChildren($element, $domNodeResult);
        foreach ($element->children(RelaxNgImportAgapeAnnotation::PASTELL_ANNOTATION_NS) as $child) {
            $this->walk($child, $domNodeResult);
        }
    }

    protected function getAnnotationNode(SimpleXMLElement $element, DOMNode $domNodeResult)
    {
        $element_name = (string) $element->getName();
        $element_value = (string) $element;
        $domNode = $this->domDocument->createElementNS(
            RelaxNgImportAgapeAnnotation::PASTELL_ANNOTATION_NS,
            $element_name,
            $element_value
        );
        $domNodeResult->appendChild($domNode);
    }

    public function generateFromRelaxNGString($relax_ng_string)
    {
        $xml = parent::generateFromRelaxNGString($relax_ng_string);
        $domDocument = new DOMDocument();
        $domDocument->loadXML($xml);
        $this->ascendAnnotationNode($domDocument);

        $domDocument->preserveWhiteSpace = false;
        $domDocument->formatOutput = true;
        return $domDocument->saveXML();
    }

    /**
     * Il est nécessaire de remonté les noeuds Annotation comme premier fils afin de pouvoir traiter les repeat dans les repeat
     * Normalement, on aurait du le faire dans la classe RelaxNGImportAgapeAnnotation, mais celle-ci est basé sur SimpleXML...
     *
     * @param DOMDocument $domDocument
     */
    private function ascendAnnotationNode(DOMDocument $domDocument)
    {
        $xpath = new DOMXPath($domDocument);
        $xpath->registerNamespace("pastell", RelaxNgImportAgapeAnnotation::PASTELL_ANNOTATION_NS);
        $annotation_list = $xpath->query("//pastell:annotation");

        /** @var DOMNode $annotation */
        foreach ($annotation_list as $annotation) {
            if ($annotation !== $annotation->parentNode->firstChild) {
                $annotation->parentNode->insertBefore($annotation, $annotation->parentNode->firstChild);
            }
        }
    }
}
