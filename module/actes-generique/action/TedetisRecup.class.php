<?php

class TedetisRecup extends ActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        /** @var TdtConnecteur $tdT */
        $tdT = $this->getConnecteur("TdT");

        if (!$tdT) {
            throw new Exception("Aucun Tdt disponible");
        }

        $tedetis_transaction_id = $this->getDonneesFormulaire()->get('tedetis_transaction_id');

        $actionCreator = $this->getActionCreator();
        if (! $tedetis_transaction_id) {
            $message = "Une erreur est survenue lors de l'envoi à " . $tdT->getLogicielName() . " (tedetis_transaction_id non disponible)";
            $this->setLastMessage($message);
            $actionCreator->addAction($this->id_e, 0, 'tdt-error', $message);
            $this->notify('tdt-error', $this->type, $message);
            return false;
        }

        try {
            $status = $tdT->getStatus($tedetis_transaction_id);
        } catch (Exception $e) {
            $message = "Echec de la récupération des informations : " .  $e->getMessage();
            $this->setLastMessage($message);
            return false;
        }

        if ($status == TdtConnecteur::STATUS_ERREUR) {
            $message = "Transaction en erreur sur le TdT : " . $tdT->getLastError();
            $this->setLastMessage($message);
            $this->getActionCreator()->addAction($this->id_e, $this->id_u, 'erreur-verif-tdt', $message);
            $this->notify('erreur-verif-tdt', $this->type, $message);
            return false;
        }

        if ($status != TdtConnecteur::STATUS_ACQUITTEMENT_RECU) {
            $this->setLastMessage("La transaction a comme statut : " . TdtConnecteur::getStatusString($status));
            return true;
        }

        $aractes = $tdT->getARActes();
        $bordereau_data = $tdT->getBordereau($tedetis_transaction_id);
        $actes_tamponne = $tdT->getActeTamponne($tedetis_transaction_id);
        $annexes_tamponnees_list = $tdT->getAnnexesTamponnees($tedetis_transaction_id);



        $actionCreator->addAction($this->id_e, 0, 'acquiter-tdt', "L'acte a été acquitté par le contrôle de légalité");

        $infoDocument = $this->getDocument()->getInfo($this->id_d);
        $documentActionEntite = $this->getDocumentActionEntite();
        $infoUser = $documentActionEntite->getUserFromAction($this->id_e, $this->id_d, 'send-tdt');
        $message = "L'acte « {$infoDocument['titre']} » télétransmis par {$infoUser['prenom']} {$infoUser['nom']} a été acquitté par le contrôle de légalité";

        $message .= "\n\nConsulter le détail de l'acte : " . SITE_BASE . "Document/detail?id_d={$this->id_d}&id_e={$this->id_e}";

        $donneesFormulaire = $this->getDonneesFormulaire();
        $numero_de_lacte = $donneesFormulaire->get('numero_de_lacte');

        if ($bordereau_data) {
            $donneesFormulaire->setData('has_bordereau', true);
            $donneesFormulaire->addFileFromData('bordereau', $numero_de_lacte . "-bordereau-tdt.pdf", $bordereau_data);
        }
        if ($aractes) {
            $donneesFormulaire->addFileFromData('aractes', "$numero_de_lacte-ar-actes.xml", $aractes);

            $simpleXMLWrapper = new SimpleXMLWrapper();
            $xmlDocument = $simpleXMLWrapper->loadString($aractes);
            $idActe = (string)$xmlDocument->xpath('////actes:ARActe/@actes:IDActe')[0];
            $donneesFormulaire->setData('acte_unique_id', $idActe);
        }
        if ($actes_tamponne) {
            $actes_original_filename = $donneesFormulaire->getFileNameWithoutExtension('arrete');
            $donneesFormulaire->addFileFromData('acte_tamponne', $actes_original_filename . "-tampon.pdf", $actes_tamponne);
        }
        if ($annexes_tamponnees_list) {
            $file_number = 0;
            foreach ($annexes_tamponnees_list as $i => $annexe_tamponnee) {
                if (empty($annexe_tamponnee)) {
                    continue;
                }
                $annexe_filename_send = $tdT->getFilenameTransformation($this->getDonneesFormulaire()->getFileName('autre_document_attache', $i));
                if (strcmp($annexe_filename_send, $annexe_tamponnee['filename']) !== 0) {
                    $message = "Une erreur est survenue lors de la récupération des annexes tamponnées de " . $tdT->getLogicielName() . " L'annexe tamponée " . $annexe_tamponnee['filename'] . " ne correspond pas avec " . $annexe_filename_send;
                    $this->setLastMessage($message);
                    $actionCreator->addAction($this->id_e, 0, 'tdt_error', $message);
                    $this->notify('tdt_error', $this->type, $message);
                    return false;
                }
                $annexe_filename = $donneesFormulaire->getFileNameWithoutExtension('autre_document_attache', $i);
                $donneesFormulaire->addFileFromData(
                    'annexes_tamponnees',
                    $annexe_filename . "-tampon.pdf",
                    $annexe_tamponnee['content'],
                    $file_number++
                );
            }
        }

        $donneesFormulaire->setData('date_ar', $tdT->getDateAR($tedetis_transaction_id));

        $this->notify('acquiter-tdt', $this->type, $message);

        $this->setLastMessage("L'acquittement du contrôle de légalité a été reçu.");
        return true;
    }
}
