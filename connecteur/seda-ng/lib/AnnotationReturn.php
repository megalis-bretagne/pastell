<?php

class AnnotationReturn
{
    public const EMPTY_RETURN = "empty";
    public const STRING = "string";
    public const XML_REPLACE = "xml_replace";
    public const ATTACHMENT_INFO = "attachment";

    public $type;
    public $string;
    public $data;
    public $node_attributes;
}
