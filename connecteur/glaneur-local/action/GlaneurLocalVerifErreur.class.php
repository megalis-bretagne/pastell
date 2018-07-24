<?php


class GlaneurLocalVerifErreur extends ActionExecutor {

	/**
	 * @throws Exception
	 */
	public function go(){

		$all_connecteur = $this->objectInstancier
			->getInstance(ConnecteurEntiteSQL::class)
			->getAllById('glaneur-local');

		$data = "Nombre de documents par connecteurs:\n";

		$nb_ok = 0;
		$nb_ko = 0;



		foreach($all_connecteur as $connecteur){
			if ($connecteur['id_e'] == 0){
				continue;
			}
			$data .= $connecteur['denomination']." - ".$connecteur['libelle']." : ";
			/** @var GlaneurLocal $glaneurLocal */
			$glaneurLocal = $this->getConnecteurFactory()->getConnecteurById($connecteur['id_ce']);
			$error_file = $glaneurLocal->listErrorDirectories();
			$data.= count($error_file)."\n";
			if (count($error_file)){
				$nb_ko++;
			} else {
				$nb_ok++;
			}
		}

		$data= "Nombre de connecteur ok : $nb_ok\nNombre de connecteur avec des erreurs : $nb_ko\n\n".$data;

		if ($nb_ko){
			mail(
				ADMIN_EMAIL,
				"[Pastell] Des glaneurs ont glanés des fichiers en erreurs",
				$data
			);
			$data.="\n\n mail envoyé à ".ADMIN_EMAIL;
		}


		$this->setLastMessage(nl2br($data));
		return ($nb_ko==0);

	}

}