<?php

class XMLVisionneuse extends Visionneuse
{
    public function display($filename, $filepath)
    {
        $xmlFormattage = new XMLFormattage();
        echo "<pre>";
        hecho($xmlFormattage->getString($filepath));
        echo "</pre>";
    }
}
