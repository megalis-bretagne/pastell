<?php

require_once __DIR__ . "/../lib/FluxDataSedaDossierMarche.class.php";
require_once __DIR__ . "/../lib/DossierMarcheCommonEnvoieSAE.class.php";


class DossierMarcheEnvoieSAE extends DossierMarcheCommonEnvoieSAE
{

    public function getFluxDataClassName()
    {
        return FluxDataSedaDossierMarche::class;
    }
}
