<?php

class StatutFactureCppSend extends ActionExecutor
{
    /**
     * @throws Exception
     */
    public function go()
    {
        $donneesFormulaire = $this->getDonneesFormulaire();

        /** @var CPP $portailFature */
        $portailFature = $this->getConnecteur("PortailFacture");

        $invoiceCppId = $donneesFormulaire->get('identifiant_facture_cpp');
        $targetStatus = $donneesFormulaire->get('statut_cible');
        $reason = $donneesFormulaire->get('commentaire');

        $portailFature->setStatutFacture($invoiceCppId, $targetStatus, $reason);
        $this->addActionOK("Changement du statut effectué avec succès");

        return true;
    }
}
