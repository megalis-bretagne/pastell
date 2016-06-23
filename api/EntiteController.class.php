<?php


class EntiteController extends BaseAPIController {

	/**
	 * @api {get} /list-entite.php /Entite/list
	 * @apiDescription Liste l'ensemble des entités sur lesquelles l'utilisateur a des droits.
	 * 					Liste également les entités filles.
	 * @apiGroup Entite
	 * @apiVersion 1.0.0
	 * @apiSuccess {Object[]} entite Liste d'entité
	 * @apiSuccess {string} id_e Identifiant numérique de l'entité
	 * @apiSuccess {string} denomination Libellé de l'entité (ex : Saint-Amand-les-Eaux)
	 * @apiSuccess {string} siren Numéro SIREN de l'entité (si c'est une collectivité ou un centre de gestion)
	 * @apiSuccess {string} centre_de_gestion Identifiant numérique (id_e) du CDG de la collectivité
	 * @apiSuccess {string} entite_mere Identifiant numérique (id_e) de l'entité mère de l'entité (par exemple pour un service)
	 */
	public function listAction(){
		return $this->getRoleUtilisateur()->getAllEntiteWithFille($this->getUtilisateurId(),'entite:lecture');
	}


}
