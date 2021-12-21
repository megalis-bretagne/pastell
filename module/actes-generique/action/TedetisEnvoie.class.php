<?php

class TedetisEnvoie extends ActionExecutor
{
    public function go()
    {
        /** @var TdtConnecteur $tdT */
        $tdT = $this->getConnecteur("TdT");
        try {
            $tdT->postActes($this->getDonneesFormulaire());
        } catch (Exception $e) {
            if ($this->id_worker) {
                $message = "Erreur lors de l'envoi au Tdt : " . $e->getMessage();
                $this->changeAction("send-tdt-erreur", $message);
                $this->notify("send-tdt-erreur", $this->type, $message);
            }
            throw $e;
        }

        $tdtConfig = $this->getConnecteurConfigByType("TdT");
        if ($tdtConfig->get('authentication_for_teletransmisson')) {
            $message = "Le document a été envoyé au TdT, en attente du certificat RGS**";
            $this->changeAction("document-transmis-tdt", $message);
            $this->notify("document-transmis-tdt", $this->type, $message);
        } else {
            $this->addActionOK("Le document a été envoyé au contrôle de légalité");
            $this->notify($this->action, $this->type, "Le document a été envoyé au contrôle de légalité");
        }

        return true;
    }

    public function goLot(array $all_id_d)
    {
        $tdt_config = $this->getConnecteurConfigByType("TdT");
        if (! $tdt_config->get("forward_x509_certificate")) {
            parent::goLot($all_id_d);
            return;
        }

        foreach ($all_id_d as $id_d) {
            try {
                $this->clearCache();
                $this->setDocumentId($this->type, $id_d);
                $this->go();
            } catch (Exception $e) {
                $this->getJournal()->add(Journal::DOCUMENT_TRAITEMENT_LOT, $this->id_e, $id_d, $this->action, "Erreur lors du traitement par lot de $id_d : " . $e->getMessage());
            }
        }
        $this->setJobManagerForLot($all_id_d);
    }
}
