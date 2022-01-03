<?php

class FastTdtClassification
{
    /**
     * @var FastTdt $tdt
     */
    private $tdt;

    public function __construct(FastTdt $tdt)
    {
        $this->tdt = $tdt;
    }


    /**
     * @return resource|string
     * @throws Exception
     */
    public function getClassificationFile()
    {
        return $this->tdt->getClassification();
    }

    /**
     * @param $classification
     * @return string
     * @throws Exception
     */
    public function getClassificationDate($classification)
    {
        $simpleXMLWrapper = new SimpleXMLWrapper();
        $xmlDocument = $simpleXMLWrapper->loadString($classification);
        return (string)$xmlDocument->xpath('//actes:DateClassification')[0];
    }
}
