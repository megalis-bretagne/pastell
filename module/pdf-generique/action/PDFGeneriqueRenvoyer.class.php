<?php

class PDFGeneriqueRenvoyer extends ActionExecutor
{
    /**
     * @return MailSec
     */
    private function getMailSecConnecteur()
    {
        return $this->getConnecteur('mailsec');
    }

    public function go()
    {
        $recuperateur = new Recuperateur($_POST);
        $id_de = $recuperateur->getInt('id_de');

        if ($id_de) {
            $this->setLastMessage("Un email a été renvoyé à l'utilisateur");
            $this->getMailSecConnecteur()->sendOneMail($this->id_e, $this->id_d, $id_de);
        } else {
            $this->getMailSecConnecteur()->sendAllMail($this->id_e, $this->id_d);
            $this->setLastMessage("Un email a été renvoyé à tous les utilisateurs");
            $this->addActionOK("Email renvoyé");
        }

        return true;
    }
}
