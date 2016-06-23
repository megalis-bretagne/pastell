<?php

class ApiAuthentication {

	/** @var SQLQuery */
	private $sqlQuery;

	/** @var  ConnexionControler */
	private $connexionControler;

	public function __construct(
		ConnexionControler $connexionControler,
		SQLQuery $sqlQuery
	) {
		$this->connexionControler = $connexionControler;
		$this->sqlQuery = $sqlQuery;
	}

	public function getUtilisateurId(){
		try {
			$id_u = $this->getUtilisateurIdThrow();
		} catch (Exception $e){
			throw new ApiAuthenticationException($e->getMessage());
		}
		return $id_u;
	}

	private function getUtilisateurIdThrow(){
		$recuperateur = new Recuperateur($_REQUEST);
		$auth = $recuperateur->get("auth");

		$id_u = false;

		if ($auth=='cas') {
			$id_u = $this->connexionControler->apiCasConnexion();
		}

		$certificatConnexion = new CertificatConnexion($this->sqlQuery);
		$utilisateur = new Utilisateur($this->sqlQuery);
		$utilisateurListe = new UtilisateurListe($this->sqlQuery);

		if (!$id_u){
			$id_u = $certificatConnexion->autoConnect();
		}


		if ( ! $id_u && ! empty($_SERVER['PHP_AUTH_USER'])){
			$id_u = $utilisateurListe->getUtilisateurByLogin($_SERVER['PHP_AUTH_USER']);
			if ( ! $utilisateur->verifPassword($id_u,$_SERVER['PHP_AUTH_PW']) ){
				$id_u = false;
			}
			if (! $certificatConnexion->connexionGranted($id_u)){
				$id_u = false;
			}
		}

		if (! $id_u){
			throw new Exception("Accès interdit");
		}

		return $id_u;
	}
}

class ApiAuthenticationException extends Exception {}