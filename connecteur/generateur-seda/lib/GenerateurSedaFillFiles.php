<?php

class GenerateurSedaFillFiles
{
    /** @var SimpleXMLElement  */
    private $xml;

    /**
     * GenerateurSedaFillFiles constructor.
     * @param $xml_content
     * @throws SimpleXMLWrapperException
     */
    public function __construct(string $xml_content)
    {
        if (! $xml_content) {
            $xml_content = "<Root></Root>";
        }
        $simpleXMLWrapper = new SimpleXMLWrapper();
        $this->xml = $simpleXMLWrapper->loadString($xml_content);
    }

    private function createUUID(): string
    {
        return uuid_create(UUID_TYPE_RANDOM);
    }

    /**
     * @param string $node_id
     * @return SimpleXMLElement
     * @throws UnrecoverableException
     */
    private function findNode(string $node_id): SimpleXMLElement
    {
        $element = $this->xml->xpath("//*[@id='$node_id']");
        if (count($element) !== 1) {
            throw new UnrecoverableException("Node $node_id not found !?");
        }
        return $element[0];
    }

    /**
     * @param string $parent_id
     * @return SimpleXMLElement
     * @throws UnrecoverableException
     */
    private function findNodeOrRoot(string $parent_id): SimpleXMLElement
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
     * @param bool $do_not_put_mime_type
     * @throws UnrecoverableException
     */
    private function addNode(string $nodeName, string $parent_id, string $description, string $field_expression, bool $do_not_put_mime_type = false): void
    {
        $element = $this->findNodeOrRoot($parent_id);
        $archiveUnit = $element->addChild($nodeName);
        $archiveUnit->addAttribute('id', $this->createUUID());
        $archiveUnit->addAttribute('description', $description);
        $archiveUnit->addAttribute('field_expression', $field_expression);
        $archiveUnit->addAttribute('do_not_put_mime_type', $do_not_put_mime_type);
    }

    /**
     * @param string $parent_id
     * @param string $description
     * @param string $field_expression
     * @throws UnrecoverableException
     */
    public function addArchiveUnit(string $parent_id = "", string $description = "", string $field_expression = ""): void
    {
        $this->addNode("ArchiveUnit", $parent_id, $description, $field_expression);
    }

    /**
     * @param string $node_id
     * @throws UnrecoverableException
     */
    public function deleteNode(string $node_id): void
    {
        $element = $this->findNode($node_id);
        $element = dom_import_simplexml($element);
        $element->parentNode->removeChild($element);
    }

    /**
     * @param string $parent_id
     * @param string $description
     * @param string $field_expression
     * @param bool $do_not_put_mime_type
     * @throws UnrecoverableException
     */
    public function addFile(string $parent_id = "", string $description = "", string $field_expression = "", bool $do_not_put_mime_type = false): void
    {
        $this->addNode("File", $parent_id, $description, $field_expression, $do_not_put_mime_type);
    }

    /**
     * @param string $node_id
     * @param string $description
     * @param string $field_expression
     * @param bool $do_not_put_mime_type
     * @throws UnrecoverableException
     */
    public function setNodeInfo(string $node_id, string $description, string $field_expression, bool $do_not_put_mime_type = false): void
    {
        $element = $this->findNode($node_id);
        $element->attributes()->{'description'} = $description;
        $element->attributes()->{'field_expression'} = $field_expression;
        $element->attributes()->{'do_not_put_mime_type'} = $do_not_put_mime_type;
    }

    /**
     * @param string $node_id
     * @param string $description
     * @throws UnrecoverableException
     */
    public function setNodeDescription(string $node_id, string $description): void
    {
        $element = $this->findNode($node_id);
        $element->attributes()->{'description'} = $description;
    }

    /**
     * @param string $node_id
     * @param string $expression
     * @throws UnrecoverableException
     */
    public function setNodeExpression(string $node_id, string $expression): void
    {
        $element = $this->findNode($node_id);
        $element->attributes()->{'field_expression'} = $expression;
    }

    /**
     * @param string $node_id
     * @param bool $do_not_put_mime_type
     * @throws UnrecoverableException
     */
    public function setNodeDoNotPutMineType(string $node_id, bool $do_not_put_mime_type): void
    {
        $element = $this->findNode($node_id);
        if (isset($element->attributes()->{'do_not_put_mime_type'})) {
            $element->attributes()->{'do_not_put_mime_type'} = $do_not_put_mime_type;
        } else {
            $element->addAttribute('do_not_put_mime_type', $do_not_put_mime_type);
        }
    }

    public function getXML(): string
    {
        return $this->xml->asXML();
    }

    public function getFiles(string $parent_id = ""): array
    {
        if ($parent_id) {
            return $this->xml->xpath("//ArchiveUnit[@id='$parent_id']/File");
        } else {
            return $this->xml->xpath("/Root/File");
        }
    }

    public function getArchiveUnit(string $parent_id = ""): array
    {
        if ($parent_id) {
            return $this->xml->xpath("//ArchiveUnit[@id='$parent_id']/ArchiveUnit");
        } else {
            return $this->xml->xpath("/Root/ArchiveUnit");
        }
    }

    /**
     * @param string $node_id
     * @return int
     * @throws UnrecoverableException
     */
    public function countChildNode(string $node_id): int
    {
        $element = $this->findNode($node_id);
        return count($element->{'ArchiveUnit'}) + count($element->{'File'});
    }

    /**
     * @param string $node_id
     * @return string
     * @throws UnrecoverableException
     */
    public function getDescription(string $node_id): string
    {
        return strval($this->findNode($node_id)['description']);
    }

    /**
     * @param string $node_id
     * @return array
     * @throws UnrecoverableException
     */
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

    /**
     * @param string $node_id
     * @throws UnrecoverableException
     */
    public function upNode(string $node_id): void
    {
        $element = $this->findNode($node_id);
        $dom = dom_import_simplexml($element);
        $node_name = $element->getName();
        $previousSibling = $element->xpath("preceding-sibling::{$node_name}[1]");
        if (! $previousSibling) {
            return;
        }
        $previousSibling = $previousSibling[0];
        $newNode = $dom->cloneNode(true);
        $dom->parentNode->insertBefore($newNode, dom_import_simplexml($previousSibling));
        $dom->parentNode->removeChild($dom);
    }

    /**
     * @param string $node_id
     * @throws UnrecoverableException
     */
    public function downNode(string $node_id): void
    {
        $element = $this->findNode($node_id);
        $dom = dom_import_simplexml($element);
        $node_name = $element->getName();
        $followingSibling = $element->xpath("following-sibling::{$node_name}[1]");
        if (! $followingSibling) {
            return;
        }
        $followingSibling = dom_import_simplexml($followingSibling[0]);
        $newNode = $followingSibling->cloneNode(true);
        $dom->parentNode->insertBefore($newNode, $dom);
        $dom->parentNode->removeChild($followingSibling);
    }

    /**
     * @param string $node_id
     * @param array $info
     * @throws UnrecoverableException
     */
    public function setArchiveUnitInfo(string $node_id, array $info): void
    {
        $element = $this->findNode($node_id);

        foreach (array_keys($this->getArchiveUnitSpecificInfoDefinition()) as $specificInfoId) {
            if ($element->{$specificInfoId}) {
                $element->{$specificInfoId}[0] = $info[$specificInfoId];
            } else {
                $element->addChild($specificInfoId, $info[$specificInfoId] ?? '');
            }
        }
    }

    /**
     * @param string $node_id
     * @return array
     * @throws UnrecoverableException
     */
    public function getArchiveUnitSpecificInfo(string $node_id): array
    {
        $element = $this->findNode($node_id);
        $result = [];
        foreach (array_keys($this->getArchiveUnitSpecificInfoDefinition()) as $key) {
            $result[$key] = strval($element->{$key} ?? '');
        }
        return $result;
    }

    public function getArchiveUnitSpecificInfoDefinition(): array
    {
        return [
            'Description' => [
                'libelle' => 'Description',
                'commentaire' => '',
            ],
            'DescriptionLevel' => [
                'libelle' => 'Niveau de description',
                'commentaire' => 'forme attendue : class, collection, file, fonds, item, recordgrp, series, subfonds, subgrp, subseries',
            ],
            'Language' => [
                'libelle' => 'Langue de la description',
                'commentaire' => 'forme attendue : fra',
            ],
            'CustodialHistory' => [
                'libelle' => 'Historique de conservation',
                'commentaire' => '',
            ],
            'Keywords' => [
                'libelle' => 'Mots-clés',
                'commentaire' =>
                    "Un mot clé par ligne de la forme : 'Contenu du mot-clé','KeywordReference','KeywordType'
                  <br/><br/>Attention, si un élément contient une virgule, il est nécessaire d'entourer l'expression par des 'guillemets'
                  <br/><br/>L'ensemble du champ est analysé avec Twig, puis les lignes sont lues comme des lignes CSV
                  ( , comme séparateur de champs, \" comme clôture de champs et \ comme caractère d'échappement)
                  <br/><br/>Les mots clés sont mis dans le bordereau au niveau ArchiveUnit - Keyword",
            ],
            'AccessRestrictionRule_AccessRule' => [
                'libelle' => 'Délai de communicabilité',
                'commentaire' => 'forme attendue : AR038 à AR062',
            ],
            'AccessRestrictionRule_StartDate' => [
                'libelle' => 'Date de départ du délai de communicabilité',
                'commentaire' => 'forme attendue : Y-m-d',
            ],
            'ArchiveUnit_AppraisalRule_Rule' => [
                'libelle' => "Durée d'utilité administrative (DUA)",
                'commentaire' => 'ex : P10Y',
            ],
            'ArchiveUnit_AppraisalRule_StartDate' => [
                'libelle' => 'Date de départ de la DUA',
                'commentaire' => 'forme attendue : Y-m-d',
            ],
            'ArchiveUnit_AppraisalRule_FinalAction' => [
                'libelle' => 'Sort final',
                'commentaire' => 'forme attendue : detruire ou conserver',
            ],
        ];
    }
}
