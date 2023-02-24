<?php

class TdTAnnulation extends ConnecteurTypeActionExecutor
{
    /**
     * @return bool
     * @throws NotFoundException
     * @throws UnrecoverableException
     */
    public function go()
    {
        $tedetis_transaction_id = $this->getMappingValue('tedetis_transaction_id');
        $tedetis_annulation_id =  $this->getMappingValue('tedetis_annulation_id');
        $has_annulation =  $this->getMappingValue('has_annulation');
        $annulation_tdt =  $this->getMappingValue('annulation-tdt');

        /** @var TdtConnecteur $tdT */
        $tdT = $this->getConnecteur('TdT');

        $tedetis_transaction_id_element = $this->getDonneesFormulaire()->get($tedetis_transaction_id);

        $id_annulation_transaction = $tdT->annulationActes($tedetis_transaction_id_element);
        if (!$id_annulation_transaction) {
            $this->setLastMessage($tdT->getLastError());
            return false;
        }
        $this->getDonneesFormulaire()->setData($tedetis_annulation_id, $id_annulation_transaction);
        $this->getDonneesFormulaire()->setData($has_annulation, true);

        $this->addActionOK("Une notification d'annulation a été envoyée au contrôle de légalité");
        $this->notify(
            $annulation_tdt,
            $this->type,
            "Une notification d'annulation a été envoyée au contrôle de légalité"
        );

        return true;
    }
}
