<?php

class XMLCleaningEmptyNode
{
    private $nodeToRemove;

    public function clean(DOMDocument $DOMDocument)
    {
        do {
            $this->onePass($DOMDocument);
        } while ($this->nodeToRemove);
    }

    private function onePass(DOMDocument $DOMDocument)
    {
        $this->nodeToRemove = array();
        $this->cleanElement($DOMDocument->documentElement);

        /**
         * @var DOMElement $domElement
         * @var DOMElement $node
         */
        foreach ($this->nodeToRemove as list($domElement,$node)) {
            $domElement->removeChild($node);
        }
    }

    private function cleanElement(DOMElement $domElement)
    {
        /** @var DOMElement $child */
        foreach ($domElement->childNodes as $child) {
            if ($child->nodeType != XML_ELEMENT_NODE) {
                continue;
            }
            /** @var DOMAttr $attribute */
            foreach ($child->attributes as $attribute) {
                if ($attribute->value == "") {
                    $child->removeAttribute($attribute->name);
                }
            }
            if ($child->childNodes->length === 0 && $child->attributes->length === 0) {
                $this->nodeToRemove[] = array($domElement,$child);
            } else {
                $this->cleanElement($child);
            }
        }
    }
}
