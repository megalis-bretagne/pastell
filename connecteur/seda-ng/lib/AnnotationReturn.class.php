<?php

class AnnotationReturn
{
    const EMPTY_RETURN = "empty";
    const STRING = "string";
    const XML_REPLACE = "xml_replace";
    const ATTACHMENT_INFO = "attachment";

    public $type;
    public $string;
    public $data;
    public $node_attributes;
}
