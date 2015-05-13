<?php

class MailTo {

    private $objectInstancier;

    public function __construct(ObjectInstancier $objectInstancier) {
        $this->objectInstancier = $objectInstancier;
    }

    /**
     * Retourne les emails des utilisateurs de r�le admin associ�s � l'entit� racine.
     * @return array
     */
    public function getRacineAdminsEmails() {
        $mails = array();
        $allAdminUsers = $this->objectInstancier->RoleUtilisateur->getAllUtilisateur(0, 'admin');
        foreach ($allAdminUsers as $user) {
            $mail = $user['email'];
            if ($mail && !in_array($mail, $mails)) {
                array_push($mails, $mail);
            }
        }
        return $mails;
    }

    /**
     * Envoi un mail au destinataire
     * @param string $mailTo email des destinataires, s�par�s par des ', '. <br>
     *      Il ne sont pas n�cessairement abonn� aux notifications.<br>
     * @param string $sujet sujet du mail.
     * @param string $contenu contenu du message<br>
     *      Si mentionne un script (se termine par .php), le contenu du message
     *      sera le r�sultat de l'ex�cution de ce script
     * @param string $action action enregistr�e dans le journal des �v�nements
     * @param array $contenuScriptInfo informations transmises au script de contenu <br>
     *      Utilis� si le contenu se construit par script. C'est le script qui d�finit
     *      les informations dont il a besoin.
     *      Le tableau est associatif : array('nomAttribut' => 'information', ...)
     * @param string $emetteurName Nom de l'�metteur du mail. Si null ou vide, seul l'email de la plateforme �mettrice appara�tra.
     * @param string $id_e id de l'entit� journalis�e. Si null ou vide, pas de journalisation.
     * @param string $id_u id de l'utilisateur journalis�.
     * @param string $id_d id du document journalis�.
     */
    public function mail($mailTo, $sujet, $contenu, $action, array $contenuScriptInfo = array(), $emetteurName = null, $id_e = 0, $id_u = 0, $id_d = 0) {
        $zenMail = $this->objectInstancier->ZenMail;
        $zenMail->setEmetteur($emetteurName, PLATEFORME_MAIL);
        $zenMail->setDestinataire($mailTo);
        $zenMail->setSujet($sujet);
        if (substr($contenu, -4) == '.php') {
            $zenMail->setContenu($contenu, $contenuScriptInfo);
        } else {
            $zenMail->setContenuText($contenu);
        }
        $zenMail->send();
        if ($id_e) {
            $this->objectInstancier->Journal->addSQL(Journal::NOTIFICATION, $id_e, $id_u, $id_d, $action, 'Notification envoy�e � ' . $mailTo);
        }
    }

    /**
     * Envoi un mail aux administrateurs racine (@link MailTo::getRacineAdminsEmails())
     * @param * Voir @link MailTo::mail
     */
    public function mailRacineAdmins($sujet, $contenu, $action, array $contenuScriptInfo = array(), $emetteurName = null, $id_e = 0, $id_u = 0) {
        $emails = $this->getRacineAdminsEmails();
        if (!$emails) {
            return;
        }
        $mailTo = implode(', ', $emails);
        $this->mail($mailTo, $sujet, $contenu, $action, $contenuScriptInfo, $emetteurName, $id_e, $id_u);
    }

}
