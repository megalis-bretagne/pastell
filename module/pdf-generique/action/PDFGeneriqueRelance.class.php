<?php

class PDFGeneriqueRelance extends ActionExecutor
{
    public function go()
    {
        /** @var PdfGeneriqueRelanceConnecteur $pdfGeneriqueRelanceConnecteur */
        $pdfGeneriqueRelanceConnecteur = $this->getConnecteur('pdf-relance');
        if (! $pdfGeneriqueRelanceConnecteur) {
            throw new Exception("Aucun connecteur pdf-relance");
        }

        $last_action = $this->getDocumentActionEntite()->getLastAction($this->id_e, $this->id_d);
        $action_list = $this->getDocumentActionEntite()->getAction($this->id_e, $this->id_d);
        $date_send_mailsec = false;
        foreach ($action_list as $action_info) {
            if ($action_info['action'] == 'send-mailsec') {
                $date_send_mailsec = $action_info['date'];
            }
        }
        if (! $date_send_mailsec) {
            throw new Exception("Impossible de trouver la date du passage à send-mailsec");
        }

        if ($last_action == 'send-mailsec' && $pdfGeneriqueRelanceConnecteur->mustRelance($date_send_mailsec)) {
            $message = "Préparation du renvoi du document";
            $this->setLastMessage($message);
            $this->getActionCreator()->addAction($this->id_e, $this->id_u, "prepare-renvoi", $message);
            return true;
        }

        if ($pdfGeneriqueRelanceConnecteur->mustGoToNextState($date_send_mailsec)) {
            $this->setLastMessage("Le document passe en non reçu !");
            $this->getActionCreator()->addAction($this->id_e, $this->id_u, "non-recu", "Le temps de récupération du document est écoulé");
            return true;
        }
        $message = "";
        if ($last_action == 'send-mailsec') {
            $date_relance = $pdfGeneriqueRelanceConnecteur->getDateRelance($date_send_mailsec);
            $message .= "Relance programmée le $date_relance<br/>";
        }
        $date_non_recu = $pdfGeneriqueRelanceConnecteur->getDateNextState($date_send_mailsec);
        $message .= "Mail défini comme non-reçu le $date_non_recu<br/>";


        $this->setLastMessage($message);
        return true;
    }
}
