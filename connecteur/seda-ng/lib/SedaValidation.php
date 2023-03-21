<?php

class SedaValidation
{
    /**
     * @deprecated 4.0.0 Le support SEDA 0.2 est déprécié
     */
    public const SEDA_V_0_2_NS = "fr:gouv:ae:archive:draft:standard_echange_v0.2";
    public const SEDA_V_1_0_NS = "fr:gouv:culture:archivesdefrance:seda:v1.0";

    private $last_errors;

    public function getLastErrors()
    {
        return $this->last_errors;
    }

    public function getSchemaPath($xmlns)
    {
        $all_schema =  [
            self::SEDA_V_0_2_NS => __DIR__ . "/../xsd/seda_v0-2/seda_v0-2.xsd",
            self::SEDA_V_1_0_NS => __DIR__ . "/../xsd/seda_v1-0/seda_v1-0.xsd",
        ];

        if (empty($all_schema[$xmlns])) {
            throw new SchemaNotFoundException("Impossible de trouver le schéma correspondant à l'espace de nom $xmlns");
        }

        return $all_schema[$xmlns];
    }

    public function validateSEDA($xml_content)
    {
        $this->last_errors = [];
        $previous = libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadXML($xml_content);


        $xmlns = $dom->documentElement->getAttribute("xmlns");
        $schema_path = $this->getSchemaPath($xmlns);

        $result = $dom->schemaValidate($schema_path);
        $this->last_errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        return $result;
    }

    public function validateRelaxNG($xml_content, $relax_ng_path)
    {
        $this->last_errors = [];
        $previous = libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadXML($xml_content);
        $result = $dom->relaxNGValidate($relax_ng_path);
        $this->last_errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        return $result;
    }
}
