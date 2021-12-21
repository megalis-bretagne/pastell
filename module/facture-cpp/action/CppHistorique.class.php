<?php

require_once(__DIR__ . "/../lib/AttrFactureCPP.class.php");

class CppHistorique extends ActionExecutor
{
    /**
     * @return array
     * @throws NotFoundException
     * @throws UnrecoverableException
     * @throws Exception
     */
    protected function metier()
    {
        /** @var PortailFactureConnecteur $connPortailFacture */
        $connPortailFacture = $this->getConnecteur('PortailFacture');
        /** @var DonneesFormulaire $donneesFormulaire */
        $doc  = $this->objectInstancier->getInstance(DonneesFormulaireFactory::class)->get($this->id_d);

        return $connPortailFacture->getHistoStatutFacture($doc->get(AttrFactureCPP::ATTR_ID_FACTURE_CPP));
    }

    /**
     * @return bool
     * @throws NotFoundException
     * @throws UnrecoverableException
     */
    public function go()
    {
        $this->metier();
        // Traitement du tableau
        $this->setLastMessage('Tableau traduit');
        return true;
    }
}
