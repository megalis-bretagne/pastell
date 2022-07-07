<?php

declare(strict_types=1);

/**
 * @deprecated
 */
class TdtRetriever
{
    private string $lastMessage = '';

    public function __construct(
        private readonly ActionCreatorSQL $actionCreatorSQL,
        private readonly ConnecteurFactory $connecteurFactory,
        private readonly DonneesFormulaireFactory $donneesFormulaireFactory,
        private readonly NotificationMail $notificationMail,
        private readonly DocumentSQL $documentSQL,
        private readonly DocumentActionEntite $documentActionEntite,
    ) {
    }

    private function setLastMessage(string $message): void
    {
        $this->lastMessage = $message;
    }

    public function getLastMessage(): string
    {
        return $this->lastMessage;
    }

    private function getTdtConnecteur(string $type_flux, int $id_e): TdtConnecteur
    {
        /** @var TdtConnecteur $tdtConnecteur */
        $tdtConnecteur = $this->connecteurFactory->getConnecteurByType($id_e, $type_flux, TdtConnecteur::FAMILLE_CONNECTEUR);

        if (!$tdtConnecteur) {
            throw new Exception('Aucun Tdt disponible');
        }
        return $tdtConnecteur;
    }

    private function getTransactionId(TdtConnecteur $tdtConnecteur, string $type_flux, string $id_d, int $id_e): string|bool
    {

        $donneesFormulaire = $this->donneesFormulaireFactory->get($id_d);

        $tedetis_transaction_id = $donneesFormulaire->get('tedetis_transaction_id');

        if (! $tedetis_transaction_id) {
            $message = sprintf(
                "Une erreur est survenue lors de l'envoi à %s (tedetis_transaction_id non disponible)",
                $tdtConnecteur->getLogicielName()
            );
            $this->setLastMessage($message);
            $this->actionCreatorSQL->addAction($id_e, 0, 'tdt-error', $message, $id_d);
            $this->notificationMail->notify($id_e, $id_d, 'tdt-error', $type_flux, $message);
            return false;
        }
        return $tedetis_transaction_id;
    }

    private function retrieveStatus(TdtConnecteur $tdtConnecteur, string $type_flux, int $id_e, string $id_d, int $id_u): bool
    {
        $tedetis_transaction_id = $this->getTransactionId($tdtConnecteur, $type_flux, $id_d, $id_e);

        try {
            $status = $tdtConnecteur->getStatus($tedetis_transaction_id);
        } catch (Exception $e) {
            $message = 'Echec de la récupération des informations : ' .  $e->getMessage();
            $this->setLastMessage($message);
            return false;
        }

        if ($status == TdtConnecteur::STATUS_ERREUR) {
            $message = 'Transaction en erreur sur le TdT : ' . $tdtConnecteur->getLastError();
            $this->setLastMessage($message);
            $this->actionCreatorSQL->addAction($id_e, $id_u, 'erreur-verif-tdt', $message, $id_d);
            $this->notificationMail->notify($id_e, $id_d, 'erreur-verif-tdt', $type_flux, $message);
            return false;
        }

        if ($status == TdtConnecteur::STATUS_ACQUITTEMENT_RECU) {
            $this->setLastMessage("La transaction a comme statut : " . TdtConnecteur::getStatusString($status));
            return true;
        }
        return false;
    }

    public function stampAgain(string $type_flux, int $id_e, string $id_d): bool
    {
        $donneesFormulaire = $this->donneesFormulaireFactory->get($id_d);

        $date_publication = null;
        if ($donneesFormulaire->get('acte_use_publication_date')) {
            $date_publication = $donneesFormulaire->get('acte_publication_date');
        }

        $tdtConnecteur = $this->getTdtConnecteur($type_flux, $id_e);
        $tedetis_transaction_id = $this->getTransactionId($tdtConnecteur, $type_flux, $id_d, $id_e);

        $actes_tamponne = $tdtConnecteur->getActeTamponne($tedetis_transaction_id, $date_publication);
        $annexes_tamponnees_list = $tdtConnecteur->getAnnexesTamponnees($tedetis_transaction_id, $date_publication);
        $this->retrieveDocumentTamponne($actes_tamponne, $annexes_tamponnees_list, $donneesFormulaire, $tdtConnecteur, $type_flux, $id_e, $id_d);
        return true;
    }

    private function retrieveDocumentTamponne(
        string $actes_tamponne,
        array $annexes_tamponnees_list,
        DonneesFormulaire $donneesFormulaire,
        TdtConnecteur $tdtConnecteur,
        string $type_flux,
        int $id_e,
        string $id_d
    ): bool {
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
                $annexe_filename_send = $tdtConnecteur->getFilenameTransformation($donneesFormulaire->getFileName('autre_document_attache', $i));
                if (strcmp($annexe_filename_send, $annexe_tamponnee['filename']) !== 0) {
                    $message = "Une erreur est survenue lors de la récupération des annexes tamponnées de " . $tdtConnecteur->getLogicielName() . " L'annexe tamponée " . $annexe_tamponnee['filename'] . " ne correspond pas avec " . $annexe_filename_send;
                    $this->setLastMessage($message);
                    $this->actionCreatorSQL->addAction($id_e, 0, 'tdt_error', $message, $id_d);
                    $this->notificationMail->notify($id_e, $id_d, 'tdt_error', $type_flux, $message);
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
        return true;
    }

    public function retrieve(string $type_flux, int $id_e, string $id_d, int $id_u): bool
    {
        $donneesFormulaire = $this->donneesFormulaireFactory->get($id_d);

        $tdtConnecteur = $this->getTdtConnecteur($type_flux, $id_e);

        $tedetis_transaction_id = $this->getTransactionId($tdtConnecteur, $type_flux, $id_d, $id_e);

        if (! $this->retrieveStatus($tdtConnecteur, $type_flux, $id_e, $id_d, $id_u)) {
            return false;
        }

        $date_publication = null;
        if ($donneesFormulaire->get('acte_use_publication_date')) {
            $date_publication = $donneesFormulaire->get('acte_publication_date');
        }

        $aractes = $tdtConnecteur->getARActes();
        $bordereau_data = $tdtConnecteur->getBordereau($tedetis_transaction_id);
        $actes_tamponne = $tdtConnecteur->getActeTamponne($tedetis_transaction_id, $date_publication);
        $annexes_tamponnees_list = $tdtConnecteur->getAnnexesTamponnees($tedetis_transaction_id, $date_publication);

        $this->actionCreatorSQL->addAction($id_e, 0, 'acquiter-tdt', "L'acte a été acquitté par le contrôle de légalité", $id_d);

        $infoDocument = $this->documentSQL->getInfo($id_d);
        $infoUser = $this->documentActionEntite->getUserFromAction($id_e, $id_d, 'send-tdt');
        $message = "L'acte « {$infoDocument['titre']} » télétransmis par {$infoUser['prenom']} {$infoUser['nom']} a été acquitté par le contrôle de légalité";

        $message .= "\n\nConsulter le détail de l'acte : " . SITE_BASE . "Document/detail?id_d={$id_d}&id_e={$id_e}";
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

        if (
            ! $this->retrieveDocumentTamponne(
                $actes_tamponne,
                $annexes_tamponnees_list,
                $donneesFormulaire,
                $tdtConnecteur,
                $type_flux,
                $id_e,
                $id_d
            )
        ) {
            return false;
        }

        $donneesFormulaire->setData('date_ar', $tdtConnecteur->getDateAR($tedetis_transaction_id));

        $this->notificationMail->notify($id_e, $id_d, 'acquiter-tdt', $type_flux, $message);

        $this->setLastMessage("L'acquittement du contrôle de légalité a été reçu.");
        return true;
    }
}
