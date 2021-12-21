<?php

class XSDValidator
{
    /**
     * @param string $schema The path to the schema file
     * @param string $file The path to the file to validate against the schema
     * @return bool
     * @throws Exception
     */
    public function schemaValidate($schema, $file)
    {
        $previous = libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->load($file);
        $err = $dom->schemaValidate($schema);
        if (!$err) {
            $last_error = libxml_get_errors();
            $msg = ' ';
            foreach ($last_error as $err) {
                $msg .= "[Erreur #{$err->code}] " . $err->message . "\n";
            }
            libxml_use_internal_errors($previous);
            throw new Exception($msg);
        }
        libxml_use_internal_errors($previous);
        return true;
    }
}
