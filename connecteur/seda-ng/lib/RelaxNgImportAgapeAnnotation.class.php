<?php

class RelaxNgImportAgapeAnnotation
{
    public const RELAX_NG_NS = "http://relaxng.org/ns/structure/1.0";
    public const PASTELL_ANNOTATION_NS = "http://pastell.adullact-projet.coop/seda-ng/annotation";


    public function importAnnotation($relaxNG_path, $agape_file_path)
    {
        $agapeFile = new AgapeFile();
        $agape = $agapeFile->getFromFilePath($agape_file_path);

        /** @var SimpleXMLElement $first_agape_children */
        $first_agape_children = $agape->children(AgapeFile::XSD_SHEMA)->{'element'};

        $relaxNG = new RelaxNG();

        $relaxng = $relaxNG->getFromFilePath($relaxNG_path);

        /** @var SimpleXMLElement $first_relax_ng_chilren */
        $first_relax_ng_chilren = $relaxng->children(self::RELAX_NG_NS)->{'start'}->{'ref'};

        $refNode = $this->getRefNode($first_relax_ng_chilren);
        /** @var SimpleXMLElement $firstRelaxNgNode */
        $firstRelaxNgNode = $refNode->children(self::RELAX_NG_NS)->{'element'};

        $this->walk($first_agape_children, $firstRelaxNgNode);

        $dom = dom_import_simplexml($relaxng)->ownerDocument;
        $dom->formatOutput = true;
        return $dom->saveXML();
    }

    private function walk(SimpleXMLElement $agapeNode, SimpleXMLElement $relaxNGNode)
    {
        $annotation = (string) $agapeNode->children(AgapeFile::XSD_SHEMA)->{'annotation'}[0];
        $relaxNGNode->addChild("annotation", $annotation, self::PASTELL_ANNOTATION_NS);

        $agapeChildren = $this->getAgapeChildElement($agapeNode);
        $relaxNGChild = $this->getRelaxNGElementChildren($relaxNGNode);

        foreach ($agapeChildren as $childName => $agapeChild_list) {
            foreach ($agapeChild_list as $nb_child => $agapeChild) {
                /*if (empty($relaxNGChild[$childName][$nb_child])){
                    continue;
                }*/
                $this->walk($agapeChild, $relaxNGChild[$childName][$nb_child]);
            }
        }
    }

    private function getAgapeChildElement(SimpleXMLElement $agapeNode)
    {
        $result = [];
        foreach ($agapeNode->xpath("./xsd:element") as $element) {
            $name = (string) $element->attributes()->{'name'};
            $result[$name][] = $element;
        }

        return $result;
    }

    public function getRelaxNGElementChildren(SimpleXMLElement $relaxNgNode)
    {

        $result = [];
        /** @var SimpleXMLElement $child */
        foreach ($relaxNgNode->children(self::RELAX_NG_NS) as $child) {
            if ($child->getName() == 'ref') {
                $refNode = $this->getRefNode($child);
                /*Factoriser la méthode de merge...*/
                foreach ($this->getRelaxNGElementChildren($refNode) as $name => $element) {
                    foreach ($element as $i => $new_element) {
                        $result[$name][] = $new_element;
                    }
                }
            }
            if (in_array($child->getName(), ['optional','oneOrMore','zeroOrMore'])) {
                foreach ($this->getRelaxNGElementChildren($child) as $name => $element) {
                    foreach ($element as $i => $new_element) {
                        $result[$name][] = $new_element;
                    }
                }
            }
            if ($child->getName() == 'element') {
                $name = (string) $child->attributes()->{'name'};
                $result[$name][] = $child;
            }
        }

        return $result;
    }

    /**
     * @param SimpleXMLElement $element
     * @return SimpleXMLElement
     */
    protected function getRefNode(SimpleXMLElement $element)
    {
        $defineRef = (string)$element->attributes()->{'name'};
        return $element->xpath("//rng:define[@name='$defineRef']")[0];
    }
}
