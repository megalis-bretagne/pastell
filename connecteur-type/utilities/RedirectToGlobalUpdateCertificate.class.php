<?php

class RedirectToGlobalUpdateCertificate extends ConnecteurTypeActionExecutor
{
    public function go()
    {
        $field = $this->getMappingValue('changement_certificat');
        $this->redirect(
            sprintf(
                "/Connecteur/externalData?id_ce=%s&field=%s",
                $this->id_ce,
                $field
            )
        );
    }
}
