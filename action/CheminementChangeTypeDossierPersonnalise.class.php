<?php

class CheminementChangeTypeDossierPersonnalise extends ActionExecutor
{

    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        $allFields = $this->getDonneesFormulaire()->getFormulaire()->getFieldsList();
        $signatureFields = preg_grep('/envoi_signature/', array_keys($allFields));

        if (count($signatureFields) === 1) {
            if ($this->getDonneesFormulaire()->get('envoi_signature')) {
                /** @var SignatureConnecteur $signatureConnector */
                $signatureConnector = $this->getConnecteur('signature');

                if ($signatureConnector->isFastSignature()) {
                    $this->getDonneesFormulaire()->setData('envoi_fast', true);
                } else {
                    $this->getDonneesFormulaire()->setData('envoi_iparapheur', true);
                }
            } else {
                $this->getDonneesFormulaire()->setData('envoi_iparapheur', false);
                $this->getDonneesFormulaire()->setData('envoi_fast', false);
            }
        } elseif (count($signatureFields) > 1) {
            for ($i = 1; $i <= count($signatureFields); ++$i) {
                if ($this->getDonneesFormulaire()->get("envoi_signature_$i")) {
                    /** @var SignatureConnecteur $signatureConnector */
                    $signatureConnector = $this->getConnecteur('signature', $i - 1);

                    if ($signatureConnector->isFastSignature()) {
                        $this->getDonneesFormulaire()->setData("envoi_fast_$i", true);
                    } else {
                        $this->getDonneesFormulaire()->setData("envoi_iparapheur_$i", true);
                    }
                } else {
                    $this->getDonneesFormulaire()->setData("envoi_iparapheur_$i", false);
                    $this->getDonneesFormulaire()->setData("envoi_fast_$i", false);
                }
            }
        }

        return true;
    }
}