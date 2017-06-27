<?php
class TdtTeletransmettre extends ActionExecutor {

	public function go(){
		/** @var TdtConnecteur $tdt */
		$tdt = $this->getConnecteur("TdT");


		$nounce_param = $tdt->getNounce();

		$redirect_url = $tdt->getRedirectURLForTeletransimission();
		$tedetis_transaction_id = $this->getDonneesFormulaire()->get('tedetis_transaction_id');

		$this->changeAction("teletransmission-tdt", "La télétransmission a été ordonné depuis Pastell");

		$url_retour = SITE_BASE."/Document/action?id_d={$this->id_d}&id_e={$this->id_e}&action=return-teletransmission-tdt&error=%%ERROR%%&message=%%MESSAGE%%";

		$to = $redirect_url."?id={$tedetis_transaction_id}" ;
		if ($nounce_param){
			$to .= "&".$nounce_param;
		}
		$to .="&url_return=".urlencode($url_retour);
		header("Location: $to");
		exit;
	}

	public function goLot(array $all_id_d){
		$lst_id_d="";
		$lst_id_transaction="";

		/** @var TdtConnecteur $tdt */
		$tdt = $this->getConnecteur("TdT");

		$nounce_param = $tdt->getNounce();

		$redirect_url = $tdt->getRedirectURLForTeletransimissionMulti();

		foreach($all_id_d as $id_d){
			$lst_id_d.="id_d[]=".$id_d."&";
			$tedetis_transaction_id = $this->objectInstancier->DonneesFormulaireFactory->get($id_d)->get('tedetis_transaction_id');
			$lst_id_transaction.="id[]=$tedetis_transaction_id&";
			$this->changeAction($this->action,"La télétransmission par lot a été ordonné depuis Pastell");
		}

		$this->setJobManagerForLot($all_id_d);

		$url_retour = SITE_BASE."/Document/retour-teletransmission?{$lst_id_d}type={$this->type}&id_e={$this->id_e}&id_u={$this->id_u}";
		$to = $redirect_url."?{$lst_id_transaction}";
		if ($nounce_param){
			$to .= "&".$nounce_param;
		}
		$to .= "&url_return=".urlencode($url_retour);

		header("Location: $to");
		exit;
	}
}