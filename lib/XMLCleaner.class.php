<?php

class XMLCleaner
{
    public function cleanXML($xml_string)
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml_string, LIBXML_PARSEHUGE);
        $this->cleanDOM($dom);
        return $dom->saveXML();
    }

    public function cleanDOM(DOMDocument $dom)
    {
        $this->cleanElement($dom->documentElement);
    }

    private function cleanElement(DOMElement $dom)
    {
        $this->removeEmptyAttributes($dom);
        $this->removeEmptyChilds($dom);
    }

    private function removeEmptyAttributes(DOMElement $dom)
    {
        foreach ($this->getAttributesList($dom) as $attr) {
            if (trim($attr->nodeValue) === '') {
                $dom->removeAttribute($attr->nodeName);
            }
        }
    }

    private function removeEmptyChilds(DOMElement $dom)
    {
        foreach ($this->getChildsNode($dom) as $child) {
            if ($child->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            $this->cleanElement($child);
            if ($this->needCleaning($child)) {
                $dom->removeChild($child);
            }
        }
    }

    private function needCleaning(DOMElement $dom)
    {
        if ($dom->attributes->length > 0) {
            return false;
        }
        foreach ($dom->childNodes as $child) {
            if ($child->nodeType == XML_ELEMENT_NODE) {
                return false;
            }
        }
        return trim($dom->nodeValue) === '';
    }

    private function getAttributesList(DOMElement $dom)
    {
        $domAttributesList = [];
        foreach ($dom->attributes as $attr) {
            $domAttributesList[] = $attr;
        }
        return $domAttributesList;
    }

    private function getChildsNode(DOMElement $dom)
    {
        //Attention : $dom->childNodes est vidé si on supprime un des noeuds !
        $domNodeList = [];
        foreach ($dom->childNodes as $child) {
            $domNodeList[] = $child;
        }
        return $domNodeList;
    }
}
