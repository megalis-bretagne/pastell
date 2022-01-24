<?php

class PESAcquitFile extends PESV2XMLFile
{
    /**
     * @param $pes_acquit_path
     * @return bool
     * @throws Exception
     */
    public function getEtatAck($pes_acquit_path)
    {
        $xml = $this->getSimpleXMLFromFile($pes_acquit_path);
        $result = $this->getValueFromXPath($xml, "//ACQUIT/ElementACQUIT/EtatAck/@V");
        return ($result == "1");
    }
}
