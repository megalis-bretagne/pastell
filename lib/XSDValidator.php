<?php

declare(strict_types=1);

class XSDValidator
{
    /**
     * @param $schemaPath
     * @param DOMDocument $dom
     * @return bool
     */
    private function schemaValidateCommon($schemaPath, DOMDocument $dom): bool
    {
        $previous = libxml_use_internal_errors(true);
        $err = $dom->schemaValidate($schemaPath);
        if (!$err) {
            $last_error = libxml_get_errors();
            $msg = ' ';
            foreach ($last_error as $err) {
                $msg .= "[Erreur #{$err->code}] " . $err->message . "\n";
            }
            libxml_use_internal_errors($previous);
            throw new RuntimeException($msg);
        }
        libxml_use_internal_errors($previous);
        return true;
    }

    /**
     * @throws Exception
     */
    public function schemaValidateFromPath($schemaPath, $filePath): bool
    {
        $dom = new DOMDocument();
        $dom->load($filePath);
        return $this->schemaValidateCommon($schemaPath, $dom);
    }

    /**
     * @throws Exception
     */
    public function schemaValidateFromContent($schemaPath, $xmlContent): bool
    {
        $dom = new DOMDocument();
        $dom->loadXML($xmlContent);
        return $this->schemaValidateCommon($schemaPath, $dom);
    }
}
