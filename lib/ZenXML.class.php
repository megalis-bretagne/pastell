<?php

class ZenXML implements ArrayAccess
{
    private $tag_name;
    private $child;
    private $cdata;
    private $attributs;

    private $isMultivalued;
    private $multipleValue;

    /*
        Normalement, il faut bien échapper les caractères, mais pour des raisons de compatibilité ascendante,
        ZenXML n'échappe pas les caractères des chaînes CDATA !
    */
    private $escape_cdata;

    public function __construct(string $tag_name, string $cdata = "", bool $escape_cdata = false)
    {
        $this->tag_name = $tag_name;
        $this->cdata = $cdata;
        $this->child = array();
        $this->attributs = array();
        $this->multipleValue = [];
        $this->escape_cdata = $escape_cdata;
    }

    public function set(string $tag_name, $cdata = false): void
    {
        if (is_object($cdata)) {
            $this->child[$tag_name] = $cdata;
        } else {
            $this->child[$tag_name] = new ZenXML($tag_name, $cdata, $this->escape_cdata);
        }
    }

    public function __set(string $tag_name, $cdata)
    {
        $this->set($tag_name, $cdata);
    }

    public function get(string $tag_name)
    {
        if (empty($this->child[$tag_name])) {
            $this->child[$tag_name] = new ZenXML($tag_name, "", $this->escape_cdata);
        }
        return $this->child[$tag_name];
    }

    public function __get(string $tag_name)
    {
        return $this->get($tag_name);
    }

    private function getCDATA(string $data, bool $escape_special_char = true): string
    {
        if (! $escape_special_char) {
            return $data;
        }
        return htmlspecialchars($data, ENT_QUOTES);
    }

    public function getAttrData(string $data)
    {
        if ($this->escape_cdata) {
            return $this->getCDATA($data, $this->escape_cdata);
        } else {
            return str_replace('"', '&quot;', $data);
        }
    }

    private function getAttr(): string
    {
        $attr = "";
        foreach ($this->attributs as $name => $value) {
            $value = $this->getAttrData($value);
            $attr .= " $name=\"$value\"";
        }
        return $attr;
    }

    public function asXML(): string
    {
        $xml = "";
        if ($this->isMultivalued) {
            foreach ($this->multipleValue as $node) {
                $xml .= $node->asXML();
            }
            return $xml;
        }
        $attr = $this->getAttr();
        $xml = "<$this->tag_name$attr>";
        if ($this->cdata) {
            $xml .=  $this->getCDATA($this->cdata, $this->escape_cdata);
        }
        foreach ($this->child as $child) {
            $xml .= $child->asXML();
        }

        $xml .= "</$this->tag_name>\n";
        return $xml;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->attributs[$offset]);
    }

    /*
     * TODO Pastell 4.0.0 => Le type mixed n'existe pas en PHP 7.2 et génère un warning en PHP 8.1
     */
    public function offsetGet($offset)
    {

        if (is_int($offset)) {
            if (empty($this->multipleValue[$offset])) {
                $this->offsetSet($offset, "");
            }
            return $this->multipleValue[$offset];
        }
        return $this->attributs[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        if ($offset === null) {
            if (! $this->multipleValue) {
                $offset = 0;
            } else {
                $offset = count($this->multipleValue) + 1;
            }
        }
        if (is_int($offset)) {
            $this->isMultivalued = true;
            if (is_object($value)) {
                $node = $value;
            } else {
                $node = new ZenXML($this->tag_name, $value, $this->escape_cdata);
            }
            $this->multipleValue[$offset] = $node;
        } else {
            $this->attributs[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->attributs[$offset]);
    }
}
