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
                // Display iparapheur tab by default
                $this->getDonneesFormulaire()->setData('envoi_iparapheur', true);
                $this->getDonneesFormulaire()->setData('envoi_fast', false);
                /** @var SignatureConnecteur $signatureConnector */
                $signatureConnector = $this->getConnecteur('signature');

                if ($signatureConnector->isFastSignature()) {
                    $this->getDonneesFormulaire()->setData('envoi_fast', true);
                    $this->getDonneesFormulaire()->setData('envoi_iparapheur', false);
                }
            } else {
                $this->getDonneesFormulaire()->setData('envoi_iparapheur', false);
                $this->getDonneesFormulaire()->setData('envoi_fast', false);
            }
        } elseif (count($signatureFields) > 1) {
            for ($i = 1; $i <= count($signatureFields); ++$i) {
                if ($this->getDonneesFormulaire()->get("envoi_signature_$i")) {
                    // Display iparapheur tab by default
                    $this->getDonneesFormulaire()->setData("envoi_iparapheur_$i", true);
                    $this->getDonneesFormulaire()->setData("envoi_fast_$i", false);

                    try {
                        /** @var SignatureConnecteur $signatureConnector */
                        $signatureConnector = $this->getConnecteur('signature', $i - 1);
                    } catch (UnrecoverableException $e) {
                        // If the first signature connector is not associated, we still need to continue
                        // to initialize the next ones
                        continue;
                    }

                    if ($signatureConnector->isFastSignature()) {
                        $this->getDonneesFormulaire()->setData("envoi_fast_$i", true);
                        $this->getDonneesFormulaire()->setData("envoi_iparapheur_$i", false);
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
