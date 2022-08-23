<?php

use Pastell\Helpers\SedaHelper;

class SAEVerifier extends ConnecteurTypeActionExecutor
{
    public const SAE_TRANSFERT_ID = 'sae_transfert_id';
    public const AR_SAE = 'ar_sae';
    public const ACTION_NAME_RECU = 'ar-recu-sae';
    public const ACTION_NAME_ERROR = 'verif-sae-erreur';
    public const SAE_ACK_COMMENT = 'sae_ack_comment';
    public const SAE_BORDEREAU = 'sae_bordereau';
    public const ACK_NOT_PROVIDED = 'ack_not_provided';

    public const COMMENT = 'Comment';

    /**
     * @return bool
     * @throws Exception
     */
    public function go(): bool
    {
        /** @var SAEConnecteur $sae */
        $sae = $this->getConnecteur('SAE');
        $donneesFormulaire = $this->getDonneesFormulaire();

        $sae_transfert_id_element = $this->getMappingValue(self::SAE_TRANSFERT_ID);
        $ar_sae = $this->getMappingValue(self::AR_SAE);
        $action_name_error = $this->getMappingValue(self::ACTION_NAME_ERROR);
        $action_name_recu = $this->getMappingValue(self::ACTION_NAME_RECU);
        $sae_ack_comment_element = $this->getMappingValue(self::SAE_ACK_COMMENT);
        $sae_bordereau = $this->getMappingValue(self::SAE_BORDEREAU);
        $ackNotProvided = $this->getMappingValue(self::ACK_NOT_PROVIDED);

        if (!$sae->provideAcknowledgment()) {
            $this->changeAction($ackNotProvided, "L'ACK n'est pas fourni par ce SAE");
            return true;
        }

        $sedaHelper = new SedaHelper();

        $simpleXMLWrapper = new SimpleXMLWrapper();
        $bordereau_content = $donneesFormulaire->getFileContent($sae_bordereau);

        $xml = $simpleXMLWrapper->loadString($bordereau_content);
        $originating_agency_id = $sedaHelper->getOriginatingAgency($xml);

        $id_transfert = $donneesFormulaire->get($sae_transfert_id_element);

        try {
            $aknowledgement_content = $sae->getAck($id_transfert, $originating_agency_id);
        } catch (UnrecoverableException $e) {
            $this->changeAction($action_name_error, 'Erreur irrécupérable : ' . $e->getMessage());
            throw $e;
        }

        $donneesFormulaire->addFileFromData($ar_sae, 'ACK_unknow.xml', $aknowledgement_content);

        $simpleXMLWrapper = new SimpleXMLWrapper();
        $xml = $simpleXMLWrapper->loadString($aknowledgement_content);


        $transfert_id_from_message = $sedaHelper->getTransfertIdFromAck($xml);

        if ($transfert_id_from_message != $id_transfert) {
            throw new UnrecoverableException(
                sprintf(
                    "L'identifiant du transfert (%s) ne correspond pas à l'identifiant de l'accusé de réception (%s)",
                    $id_transfert,
                    $transfert_id_from_message
                )
            );
        }

        $ack_id = $sedaHelper->getAckID($xml);

        $ack_name = sprintf('%s.xml', $ack_id);
        $donneesFormulaire->addFileFromData($ar_sae, $ack_name, $aknowledgement_content);

        if ($xml->{self::COMMENT}) {
            $donneesFormulaire->setData($sae_ack_comment_element, $xml->{self::COMMENT});
        }

        $message = "Récupération de l'accusé de réception : " .
            $xml->getName() .
            ' - ' .
            $xml->{'Comment'};

        $this->changeAction($action_name_recu, $message);
        $this->notify($action_name_recu, $this->type, $message);
        return true;
    }
}
