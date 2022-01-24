<?php

class HeliosEnvoieSignatureChangeAPI extends HeliosEnvoieSignatureChange
{
    public function go()
    {

        $this->getDonneesFormulaire()->setData(
            'envoi_signature_check',
            $this->getDonneesFormulaire()->get('envoi_signature')
            || $this->getDonneesFormulaire()->get('envoi_signature_fast')
        );

        parent::go();
    }
}
