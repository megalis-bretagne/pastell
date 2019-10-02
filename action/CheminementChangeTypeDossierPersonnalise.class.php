<?php

class CheminementChangeTypeDossierPersonnalise extends ActionExecutor
{

    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        if ($this->getDonneesFormulaire()->get('envoi_signature')) {
            /** @var SignatureConnecteur $signatureConnector */
            $signatureConnector = $this->getConnecteur('signature');

            if ($signatureConnector->isFastSignature()) {
                $this->getDonneesFormulaire()->setData('envoi_signature_fast', true);
            } else {
                $this->getDonneesFormulaire()->setData('envoi_signature_iparapheur', true);
            }
        } else {
            $this->getDonneesFormulaire()->setData('envoi_signature_iparapheur', false);
            $this->getDonneesFormulaire()->setData('envoi_signature_fast', false);
        }

        return true;
    }
}