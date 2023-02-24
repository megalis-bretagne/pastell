<?php

class EnvoieSignatureChangeAPI extends ActionExecutor
{
    public function go()
    {
        $this->getDonneesFormulaire()->setData(
            'envoi_signature_check',
            $this->getDonneesFormulaire()->get('envoi_signature')
            || $this->getDonneesFormulaire()->get('envoi_signature_fast')
        );
    }
}
