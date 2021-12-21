<?php

namespace Pastell\Service\ChorusPro;

class ChorusProXSDStatutPivot
{
    /**
     * The file can be found here : https://communaute.chorus-pro.gouv.fr/cppstatutpivot_v1_19-xsd-2/?lang=en
     *
     * @return string
     */
    public function getSchemaPath()
    {
        return __DIR__ . "/xsd-pivot/CPPStatutPivot_V1_19.xsd";
    }
}
