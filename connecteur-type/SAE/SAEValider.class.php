<?php

use Pastell\Helpers\SedaHelper;

class SAEValider extends ConnecteurTypeActionExecutor
{
    public const ARCHIVE_TRANSFER_REPLY = 'ArchiveTransferReply';
    public const COMMENT = 'Comment';

    /**
     * @return bool
     * @throws Exception
     */
    public function go(): bool
    {
        $sae_transfert_id_element = $this->getMappingValue('sae_transfert_id');
        $reply_sae_element = $this->getMappingValue('reply_sae');
        $action_name_error_envoi = $this->getMappingValue('erreur-envoie-sae');
        $action_name_error_validation = $this->getMappingValue('validation-sae-erreur');
        $action_name_accepter = $this->getMappingValue('accepter-sae');
        $action_name_rejet = $this->getMappingValue('rejet-sae');
        $sae_atr_comment_element = $this->getMappingValue('sae_atr_comment');
        $sae_archival_identifier_element = $this->getMappingValue('sae_archival_identifier');
        $sae_bordereau = $this->getMappingValue('sae_bordereau');

        /** @var SAEConnecteur $sae */
        $sae = $this->getConnecteur('SAE');

        $donneesFormulaire = $this->getDonneesFormulaire();

        $sedaHelper = new SedaHelper();

        $simpleXMLWrapper = new SimpleXMLWrapper();
        $xml = $simpleXMLWrapper->loadString($donneesFormulaire->getFileContent($sae_bordereau));
        $originating_agency_id = $sedaHelper->getOriginatingAgency($xml);

        $id_transfert = $donneesFormulaire->get($sae_transfert_id_element);

        if (!$id_transfert) {
            $message = "Impossible de trouver l'identifiant du transfert";
            $this->setLastMessage($message);
            $this->getActionCreator()->addAction($this->id_e, $this->id_u, $action_name_error_envoi, $message);
            $this->notify($this->action, $this->type, $message);
            return false;
        }

        try {
            $atr_content = $sae->getAtr($id_transfert, $originating_agency_id);
        } catch (UnrecoverableException $e) {
            $this->changeAction($action_name_error_validation, 'Erreur irrécupérable : ' . $e->getMessage());
            throw $e;
        }

        $donneesFormulaire->addFileFromData($reply_sae_element, 'ATR_unknown.xml', $atr_content);

        $simpleXMLWrapper = new SimpleXMLWrapper();
        $xml = $simpleXMLWrapper->loadString($atr_content);

        $sedaHelper = new SedaHelper();

        $atr_id = $sedaHelper->getAtrID($xml);

        $atr_name = sprintf('%s.xml', $atr_id);
        $donneesFormulaire->addFileFromData($reply_sae_element, $atr_name, $atr_content);

        if ($xml->{self::COMMENT}) {
            $donneesFormulaire->setData($sae_atr_comment_element, $xml->{self::COMMENT});
        }
        if (! $this->isArchiveAccepted($xml)) {
            $replyCode = (string)($xml->ReplyCode);
            $commentaire = $donneesFormulaire->get($sae_atr_comment_element);

            if ($replyCode) {
                $commentaire .= " (Archive refusée - code de retour : $replyCode)";
            } else {
                $commentaire .= ' (Archive refusée)';
            }
            $donneesFormulaire->setData($sae_atr_comment_element, $commentaire);
            $this->addActionAndNotify($action_name_rejet, "La transaction a été refusée par le SAE. $commentaire");
            return false;
        }

        $sae_archival_identifier = $sedaHelper->getSAEArchivalIdentifierFromAtr($xml);
        $donneesFormulaire->setData($sae_archival_identifier_element, $sae_archival_identifier);

        $this->addActionAndNotify($action_name_accepter, 'La transaction a été acceptée par le SAE');
        return true;
    }

    private function addActionAndNotify(string $nextAction, string $message): void
    {
        $this->getActionCreator()->addAction($this->id_e, $this->id_u, $nextAction, $message);
        $this->notify($nextAction, $this->type, $message);
        $this->setLastMessage($message);
    }

    private function isArchiveAccepted(SimpleXMLElement $xml): bool
    {
        $nodeName = $xml->getName();

        if ($nodeName === 'ArchiveTransferAcceptance') {
            //For SEDA V1
            return true;
        }

        if ($nodeName === self::ARCHIVE_TRANSFER_REPLY) {
            $replyCode  = (string)($xml->ReplyCode);
            if ($replyCode === '000') {
                return true;
            }
        }
        return false;
    }
}
