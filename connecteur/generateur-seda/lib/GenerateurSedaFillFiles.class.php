<?php

class GenerateurSedaFillFiles
{

    private $xml;

    /**
     * GenerateurSedaFillFiles constructor.
     * @param $xml_content
     * @throws SimpleXMLWrapperException
     */
    public function __construct($xml_content)
    {
        if (! $xml_content) {
            $xml_content = "<Root></Root>";
        }
        $simpleXMLWrapper = new SimpleXMLWrapper();
        $this->xml = $simpleXMLWrapper->loadString($xml_content);
    }

    private function createUUID()
    {
        return uuid_create(UUID_TYPE_RANDOM);
    }

    /**
     * @param string $node_id
     * @return SimpleXMLElement
     * @throws UnrecoverableException
     */
    private function findNode(string $node_id)
    {
        $element = $this->xml->xpath("//*[@id='$node_id']");
        if (count($element) != 1) {
            throw new UnrecoverableException("Node $node_id not found !?");
        }
        return $element[0];
    }

    /**
     * @param string $parent_id
     * @return SimpleXMLElement
     * @throws UnrecoverableException
     */
    private function findNodeOrRoot(string $parent_id)
    {
        if ($parent_id) {
            return $this->findNode($parent_id);
        }
        return $this->xml;
    }

    /**
     * @param string $nodeName
     * @param string $parent_id
     * @param string $description
     * @param string $field_expression
     * @throws UnrecoverableException
     */
    private function addNode(string $nodeName, string $parent_id, string $description, string $field_expression)
    {
        $element = $this->findNodeOrRoot($parent_id);
        $archiveUnit = $element->addChild($nodeName);
        $archiveUnit->addAttribute('id', $this->createUUID());
        $archiveUnit->addAttribute('description', $description);
        $archiveUnit->addAttribute('field_expression', $field_expression);
    }

    /**
     * @param string $parent_id
     * @param string $description
     * @param string $field_expression
     * @throws UnrecoverableException
     */
    public function addArchiveUnit(string $parent_id = "", string $description = "", string $field_expression = "")
    {
        $this->addNode("ArchiveUnit", $parent_id, $description, $field_expression);
    }

    /**
     * @param string $node_id
     * @throws UnrecoverableException
     */
    public function deleteNode(string $node_id)
    {
        $element = $this->findNode($node_id);
        $element = dom_import_simplexml($element);
        $element->parentNode->removeChild($element);
    }

    /**
     * @param string $parent_id
     * @param string $description
     * @param string $field_expression
     * @throws UnrecoverableException
     */
    public function addFile(string $parent_id = "", $description = "", $field_expression = "")
    {
        $this->addNode("File", $parent_id, $description, $field_expression);
    }

    /**
     * @param string $node_id
     * @param string $description
     * @param string $field_expression
     * @throws UnrecoverableException
     */
    public function setNodeInfo(string $node_id, string $description, string $field_expression)
    {
        $element = $this->findNode($node_id);
        $element->attributes()->{'description'} = $description;
        $element->attributes()->{'field_expression'} = $field_expression;
    }

    public function setNodeDescription(string $node_id, string $description)
    {
        $element = $this->findNode($node_id);
        $element->attributes()->description = $description;
    }

    public function setNodeExpression(string $node_id, string $expression)
    {
        $element = $this->findNode($node_id);
        $element->attributes()->field_expression = $expression;
    }

    public function getXML()
    {
        return $this->xml->asXML();
    }

    public function getFiles(string $parent_id = "")
    {
        if ($parent_id) {
            return $this->xml->xpath("//ArchiveUnit[@id='$parent_id']/File");
        } else {
            return $this->xml->xpath("/Root/File");
        }
    }

    public function getArchiveUnit(string $parent_id = "")
    {
        if ($parent_id) {
            return $this->xml->xpath("//ArchiveUnit[@id='$parent_id']/ArchiveUnit");
        } else {
            return $this->xml->xpath("/Root/ArchiveUnit");
        }
    }

    public function countChildNode(string $node_id)
    {
        return $this->findNode($node_id)->count();
    }

    public function getDescription(string $node_id)
    {
        return strval($this->findNode($node_id)['description']);
    }

    public function getParent(string $node_id): array
    {
        if (! $node_id) {
            return [];
        }
        $element = $this->findNode($node_id);
        $ancestor = $element->xpath("ancestor::*");
        array_shift($ancestor);
        return $ancestor;
    }

    public function upNode(string $node_id)
    {
        $element = $this->findNode($node_id);
        $dom = dom_import_simplexml($element);
        $node_name = $element->getName();
        $previousSibling = $element->xpath("preceding-sibling::{$node_name}[1]");
        if (! $previousSibling) {
            return;
        }
        $previousSibling = $previousSibling[0];
        $newNode = $dom->cloneNode();
        $dom->parentNode->insertBefore($newNode, dom_import_simplexml($previousSibling));
        $dom->parentNode->removeChild($dom);
    }

    public function downNode(string $node_id)
    {
        $element = $this->findNode($node_id);
        $dom = dom_import_simplexml($element);
        $node_name = $element->getName();
        $followingSibling = $element->xpath("following-sibling::{$node_name}[1]");
        if (! $followingSibling) {
            return;
        }
        $followingSibling = dom_import_simplexml($followingSibling[0]);
        $newNode = $followingSibling->cloneNode();
        $dom->parentNode->insertBefore($newNode, $dom);
        $dom->parentNode->removeChild($followingSibling);
    }
}
