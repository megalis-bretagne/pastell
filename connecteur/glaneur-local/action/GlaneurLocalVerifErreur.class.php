<?php


class GlaneurLocalVerifErreur extends ActionExecutor {

	/**
	 * @throws Exception
	 */
	public function go(){

		$all_connecteur = $this->objectInstancier
			->getInstance(ConnecteurEntiteSQL::class)
			->getAllById('glaneur-local');

		$data = "";

		$nb_ok = 0;
		$nb_ko = 0;

		foreach($all_connecteur as $connecteur){
			$data .= $connecteur['denomination']." ".$connecteur['libelle']." : ";
			/** @var GlaneurLocal $glaneurLocal */
			$glaneurLocal = $this->getConnecteurFactory()->getConnecteurById($connecteur['id_ce']);
			$error_file = $glaneurLocal->listErrorDirectories();
			$data.= count($error_file)."<br/>";
			if (count($error_file)){
				$nb_ko++;
			} else {
				$nb_ok++;
			}
		}

		if ($nb_ko){
			mail(
				ADMIN_EMAIL,
				"[Pastell] Des glaneurs ont glanés des fichiers en erreurs",
				$data
			);
			$data.="<br/><br/> mail envoyé à ".ADMIN_EMAIL;
		}

		$data= "Nombre de connecteur ok : $nb_ok<br/>Nombre de connecteur avec des erreurs : $nb_ko</br><br/>".$data;
		$this->setLastMessage($data);
		return ($nb_ko==0);

	}

}