<?php

class RawVisionneuse extends Visionneuse
{
    public function display($filename, $filepath)
    {
        if (! file_exists($filepath)) {
            echo "Aucun fichier présent";
            return;
        }
        hecho(file_get_contents($filepath));
    }
}
