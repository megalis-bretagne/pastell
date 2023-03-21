<?php

class Siren
{
    //http://xml.insee.fr/schema/siret.html#controles
    public function isValid($siren)
    {
        if (!is_numeric($siren)) {
            return false;
        }

        if (strlen($siren) != 9) {
            return false;
        }

        $sum = $this->getKey($siren);
        return ($sum % 10 == 0);
    }

    private function getKey($siren)
    {
        $sum = 0;
        $siren_array = str_split($siren);
        foreach ($siren_array as $i => $chiffre) {
            if ($i % 2 == 1) {
                $chiffre2 = str_split((string)((int)$chiffre * 2));
                foreach ($chiffre2 as $c) {
                    $sum += $c;
                }
            } else {
                $sum += $chiffre;
            }
        }
        return $sum;
    }

    public function generate()
    {
        $siren = "";
        for ($i = 0; $i < 8; $i++) {
            $siren = $siren . rand(0, 9);
        }
        $key = $this->getKey($siren);
        return $siren . ((10 - $key % 10) % 10);
    }
}
