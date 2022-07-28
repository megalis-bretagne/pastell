<?php

class FournisseurCommandeRenvoyerMail extends ActionExecutor
{
    private function getMailSecConnecteur(): MailSec
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getConnecteur('mailsec');
    }

    public function go()
    {
        $recuperateur = new Recuperateur($_POST);
        $id_de = $recuperateur->getInt('id_de');

        if ($id_de) {
            $this->getMailSecConnecteur()->sendOneMail($this->id_e, $this->id_d, $id_de);
        } else {
            $this->getMailSecConnecteur()->sendAllMail($this->id_e, $this->id_d);
        }
        $this->setLastMessage("Un email a été renvoyé au founisseur");

        return true;
    }
}
