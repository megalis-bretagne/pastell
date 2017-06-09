<?php

require_once __DIR__."/HeliosGeneriqueXMLFile.class.php";

class HeliosGeneriquePESAcquit extends HeliosGeneriqueXMLFile {

    public function getEtatAck($pes_acquit_path){
        $xml = $this->getPESAllerAsSimpleXML($pes_acquit_path);
        $result = $this->getValueFromXPath($xml,"//ACQUIT/ElementACQUIT/EtatAck/@V");
        return ($result == "1");
    }

}
