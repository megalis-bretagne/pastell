<?php

class TdtTeletransmettre extends ActionExecutor
{
    /**
     * @throws Exception
     */
    public function go()
    {

        $stringMapper = $this->getDocumentType()->getAction()->getConnecteurMapper($this->action);

        /** @var TdtConnecteur $tdt */
        $tdt = $this->getConnecteur("TdT");


        $nounce_param = $tdt->getNounce();

        $redirect_url = $tdt->getRedirectURLForTeletransimission();
        $tedetis_transaction_id = $this->getDonneesFormulaire()->get($stringMapper->get('tedetis_transaction_id'));

        $return_teletransmission_tdt = $stringMapper->get('return-teletransmission-tdt');

        $this->changeAction("teletransmission-tdt", "La télétransmission a été ordonnée depuis Pastell");

        $url_retour = SITE_BASE . "/Document/action?id_d={$this->id_d}&id_e={$this->id_e}&action={$return_teletransmission_tdt}&error=%%ERROR%%&message=%%MESSAGE%%";

        $to = $redirect_url . "?id={$tedetis_transaction_id}" ;
        if ($nounce_param) {
            $to .= "&" . $nounce_param;
        }
        $to .= "&url_return=" . urlencode($url_retour);
        header_wrapper("Location: $to");
        exit_wrapper();
    }

    public function goLot(array $all_id_d)
    {

        $stringMapper = $this->getDocumentType()->getAction()->getConnecteurMapper($this->action);
        $return_teletransmission_tdt = $stringMapper->get('return-teletransmission-tdt');

        $lst_id_d = "";
        $lst_id_transaction = "";

        /** @var TdtConnecteur $tdt */
        $tdt = $this->getConnecteur("TdT");

        $nounce_param = $tdt->getNounce();

        $redirect_url = $tdt->getRedirectURLForTeletransimissionMulti();

        foreach ($all_id_d as $id_d) {
            $lst_id_d .= "id_d[]=" . $id_d . "&";
            $tedetis_transaction_id = $this->objectInstancier->DonneesFormulaireFactory->get($id_d)->get($stringMapper->get('tedetis_transaction_id'));
            $lst_id_transaction .= "id[]=$tedetis_transaction_id&";
            $this->changeAction($this->action, "La télétransmission par lot a été ordonnée depuis Pastell");
        }

        $this->setJobManagerForLot($all_id_d);

        $url_retour = SITE_BASE . "/Document/retourTeletransmission?{$lst_id_d}type={$this->type}&id_e={$this->id_e}&id_u={$this->id_u}&action={$return_teletransmission_tdt}";
        $to = $redirect_url . "?{$lst_id_transaction}";
        if ($nounce_param) {
            $to .= "&" . $nounce_param;
        }
        $to .= "&url_return=" . urlencode($url_retour);

        header("Location: $to");
        exit;
    }
}
