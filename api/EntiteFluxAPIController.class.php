<?php

use Pastell\Service\Connecteur\ConnecteurAssociationService;
use Pastell\Service\Droit\DroitService;

class EntiteFluxAPIController extends BaseAPIController
{

    private $entiteSQL;
    private $actionPossible;
    private $fluxEntiteSQL;
    private $actionExecutorFactory;
    private $droitService;
    private $connecteurAssociationService;

    public function __construct(
        EntiteSQL $entiteSQL,
        ActionPossible $actionPossible,
        FluxEntiteSQL $fluxEntiteSQL,
        ActionExecutorFactory $actionExecutorFactory,
        DroitService $droitService,
        ConnecteurAssociationService $connecteurAssociationService
    ) {

        $this->entiteSQL = $entiteSQL;
        $this->actionPossible = $actionPossible;
        $this->fluxEntiteSQL = $fluxEntiteSQL;
        $this->actionExecutorFactory = $actionExecutorFactory;
        $this->droitService = $droitService;
        $this->connecteurAssociationService = $connecteurAssociationService;
    }

    private function checkedEntite()
    {
        $id_e = $this->getFromQueryArgs(0) ?: 0;
        if ($id_e && ! $this->entiteSQL->getInfo($id_e)) {
            throw new NotFoundException("L'entité $id_e n'existe pas");
        }
        $this->checkDroit($id_e, "entite:lecture");
        return $id_e;
    }

    /**
     * @param $id_e
     * @throws ForbiddenException
     */
    private function checkConnecteurLecture(int $id_e): void
    {
        $part = $this->droitService->getPartForConnecteurDroit();
        $this->checkDroit($id_e, DroitService::getDroitLecture($part));
    }

    /**
     * @param $id_e
     * @throws ForbiddenException
     */
    private function checkConnecteurEdition(int $id_e): void
    {
        $part = $this->droitService->getPartForConnecteurDroit();
        $this->checkDroit($id_e, DroitService::getDroitEdition($part));
    }

    /**
     * @api {get}  /Connecteur/recherche /Connecteur/recherche
     * @apiDescription Recherche des association flux/connecteur (was: /list-flux-connecteur.php)
     * @apiGroup Connecteur
     * @apiVersion 1.0.0
     *
     * @apiParam {int} id_e Identifiant de l'entité
     * @apiParam {string} type Famille de connecteur
     * @apiParam {string} flux Flux
     *
     * @apiSuccess {Object[]} flux_entite liste d'association
     */
    public function get()
    {
        $id_e = $this->checkedEntite();
        $this->checkConnecteurLecture($id_e);
        $flux = $this->getFromRequest('flux', null);
        $type = $this->getFromRequest('type', null);

        $this->checkDroit($id_e, "entite:lecture");

        $result = $this->fluxEntiteSQL->getAllFluxEntite($id_e, $flux, $type);
        return $result;
    }


    public function post()
    {
        if ($this->getFromQueryArgs(3) == 'action') {
            return $this->postAction();
        }
        if ($this->getFromQueryArgs(3) == 'connecteur') {
            return $this->postConnecteur();
        }
        return false;
    }


    public function postConnecteur()
    {
        $id_e = (int)$this->checkedEntite();
        $this->checkConnecteurEdition($id_e);
        $flux = $this->getFromQueryArgs(2);
        $id_ce = (int)$this->getFromQueryArgs(4);
        $type = $this->getFromRequest('type');
        $num_same_type = (int)$this->getFromRequest('num_same_type', 0);

        $this->checkDroit($id_e, "entite:edition");
        $id_fe = $this->connecteurAssociationService->addConnecteurAssociation(
            $id_e,
            $id_ce,
            $type,
            $this->getUtilisateurId(),
            $flux,
            $num_same_type
        );
        $result['id_fe'] = $id_fe;
        return $result;
    }

    //Ca c'est vraiment pas bo... mais c'est pour assurer la compatibilité avec la V1
    public function postAction()
    {
        $id_e = $this->checkedEntite();
        $this->checkConnecteurEdition($id_e);
        $flux = $this->getFromQueryArgs(2);


        $type_connecteur = $this->getFromRequest('type');
        //WTF ! Il faut que le connecteur soit associé à un flux ??

        $action = $this->getFromRequest('action');
        $action_params = $this->getFromRequest('action_params', array());


        // La vérification des droits est déléguée au niveau du test sur l'action est-elle possible.
        //$this->verifDroit($id_e, "entite:edition");

        $connecteur_info = $this->fluxEntiteSQL->getConnecteur($id_e, $flux, $type_connecteur);

        if (!$connecteur_info) {
            throw new Exception("Le connecteur de type $type_connecteur n'existe pas pour le flux $flux.");
        }

        $id_ce = $connecteur_info['id_ce'];

        $actionPossible = $this->actionPossible;

        if (! $actionPossible->isActionPossibleOnConnecteur($id_ce, $this->getUtilisateurId(), $action)) {
            throw new Exception("L'action « $action »  n'est pas permise : " . $actionPossible->getLastBadRule());
        }


        $result = $this->actionExecutorFactory->executeOnConnecteur($id_ce, $this->getUtilisateurId(), $action, true, $action_params);
        $message = $this->actionExecutorFactory->getLastMessage();

        if (! $result) {
            throw new Exception($message);
        }

        return array("result" => $result, "message" => $message);
    }


    public function delete()
    {
        $id_e = $this->checkedEntite();
        $id_fe = $this->getFromRequest('id_fe');
        $this->checkConnecteurEdition($id_e);
        $this->checkDroit($id_e, "entite:edition");

        $fluxEntiteSQL = $this->fluxEntiteSQL;
        $infoFluxConnecteur = $fluxEntiteSQL->getConnecteurById($id_fe);

        if (!$infoFluxConnecteur) {
            throw new Exception("Le connecteur-flux n'existe pas : {id_fe=$id_fe}");
        }

        if ($id_e != $infoFluxConnecteur['id_e']) {
            throw new Exception("Le connecteur-flux n'existe pas sur l'entité spécifié : {id_fe=$id_fe, id_e=$id_e}");
        }

        $fluxEntiteSQL->removeConnecteur($id_fe);

        $result['result'] = self::RESULT_OK;
        return $result;
    }
}
