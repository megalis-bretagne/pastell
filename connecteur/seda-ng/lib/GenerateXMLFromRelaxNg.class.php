<?php

class GenerateXMLFromRelaxNg
{
    /** @var  DOMDocument */
    protected $domDocument;

    private $domResultNamespace;

    /** @var XMLFile  */
    private $relaxNG;

    public function __construct(RelaxNG $relaxNG)
    {
        $this->relaxNG = $relaxNG;
    }

    public function generateFromRelaxNG($relax_ng_path)
    {
        return $this->generateFromRelaxNGString(file_get_contents($relax_ng_path));
    }

    public function generateFromRelaxNGString($relax_ng_string)
    {
        $relax_ng = $this->relaxNG->getFromString($relax_ng_string);
        $this->domResultNamespace = (string) $relax_ng->attributes()->{'ns'};

        $children = $relax_ng->children(RelaxNG::RELAX_NG_NS);

        /** @var SimpleXMLElement $start_ref */
        $start_ref = $children->{'start'}->{'ref'};

        $domDocument = new DOMDocument();

        $this->generate($start_ref, $domDocument);

        $domDocument->preserveWhiteSpace = false;
        $domDocument->formatOutput = true;
        return $domDocument->saveXML();
    }



    public function generate(SimpleXMLElement $element, DOMDocument $DOMDocument)
    {
        $this->domDocument = $DOMDocument;
        $this->walk($element, $DOMDocument);
    }

    protected function walk(SimpleXMLElement $element, DOMNode $domNodeResult)
    {
        $element_name = $element->getName();
        $function = "get" . ucfirst($element_name) . "Node";

        if (! method_exists($this, $function)) {
            throw new Exception("Unkown « $element_name » tag in RelaxNG");
        }

        $this->$function($element, $domNodeResult);
    }

    protected function walkChildren(SimpleXMLElement $element, DOMNode $domNodeResult)
    {
        foreach ($element->children(RelaxNG::RELAX_NG_NS) as $child) {
            $this->walk($child, $domNodeResult);
        }
    }

    protected function getElementNode(SimpleXMLElement $element, DOMNode $domNodeResult)
    {
        $element_name = $element->attributes()->{'name'};
        if (! $element_name) {
            return;
        }
        $domNode = $this->domDocument->createElementNS($this->domResultNamespace, $element_name);
        $domNodeResult->appendChild($domNode);
        $this->walkChildren($element, $domNode);
    }

    protected function getValueNode(SimpleXMLElement $element, DOMNode $domNodeResult)
    {
        $domNode = $this->domDocument->createTextNode((string) $element);
        $domNodeResult->appendChild($domNode);
    }

    protected function getEmptyNode()
    {
        //Nothing to do
    }

    protected function getRefNode(SimpleXMLElement $element, DOMNode $domNodeResult)
    {
        $defineRef = (string)$element->attributes()->{'name'};
        $refNode = $element->xpath("//rng:define[@name='$defineRef']")[0];
        $this->walk($refNode, $domNodeResult);
    }

    protected function getDefineNode(SimpleXMLElement $element, DOMNode $domNodeResult)
    {
        $this->walkChildren($element, $domNodeResult);
    }

    protected function getDataNode(SimpleXMLElement $element, DOMNode $domNodeResult)
    {
        /*$domNode = $this->domDocument->createTextNode(self::STRING_MOCK);
        $domNodeResult->appendChild($domNode);*/
    }

    protected function getAttributeNode(SimpleXMLElement $element, DOMNode $domNodeResult)
    {
        $attribute_name = (string)$element->attributes()->{'name'};
        $domNode = $this->domDocument->createAttribute($attribute_name);
        $domNodeResult->appendChild($domNode);
        $this->walkChildren($element, $domNode);
    }

    protected function getOneOrMoreNode(SimpleXMLElement $element, DOMNode $domNodeResult)
    {
        $this->walkChildren($element, $domNodeResult);
    }

    protected function getZeroOrMoreNode(SimpleXMLElement $element, DOMNode $domNodeResult)
    {
        $this->walkChildren($element, $domNodeResult);
    }

    protected function getOptionalNode(SimpleXMLElement $element, DOMNode $domNodeResult)
    {
        //on ne traite pas le cas des élements "optionnel" => ils sont soit interdit soit obligatoire
        $this->walkChildren($element, $domNodeResult);
    }
}
