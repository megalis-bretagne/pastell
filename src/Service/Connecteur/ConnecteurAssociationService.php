<?php

namespace Pastell\Service\Connecteur;

use ConnecteurEntiteSQL;
use FluxDefinitionFiles;
use FluxEntiteSQL;
use Exception;
use Pastell\Service\Connecteur\ConnecteurActionService;
use Pastell\Service\Droit\DroitService;
use UnrecoverableException;

class ConnecteurAssociationService
{
    private $connecteurEntiteSQL;
    private $fluxEntiteSQL;
    private $droitService;
    private $fluxDefinitionFiles;
    private $connecteurActionService;

    private $lastMessage = '';

    public function __construct(
        ConnecteurEntiteSQL $connecteurEntiteSQL,
        FluxEntiteSQL $fluxEntiteSQL,
        DroitService $droitService,
        FluxDefinitionFiles $fluxDefinitionFiles,
        ConnecteurActionService $connecteurActionService
    ) {
        $this->connecteurEntiteSQL = $connecteurEntiteSQL;
        $this->fluxEntiteSQL = $fluxEntiteSQL;
        $this->droitService = $droitService;
        $this->fluxDefinitionFiles = $fluxDefinitionFiles;
        $this->connecteurActionService = $connecteurActionService;
    }

    /**
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function addConnecteurAssociation(
        int $id_e,
        int $id_ce,
        string $type_connecteur,
        int $id_u = 0,
        string $type_dossier = '',
        int $num_same_type = 0
    ): int {

        $info = $this->connecteurEntiteSQL->getInfo($id_ce);

        if ($info['type'] != $type_connecteur) {
            throw new UnrecoverableException("Le connecteur n'est pas du bon type :  {$info['type']} présenté, $type_connecteur requis");
        }
        if (! $this->droitService->hasDroitConnecteurEdition($id_e, $id_u)) {
            throw new UnrecoverableException("Vous n'avez pas le droit d'édition pour les connecteurs");
        }
        if ($type_dossier != null) {
            $info = $this->fluxDefinitionFiles->getInfo($type_dossier);
            if (!$info) {
                throw new UnrecoverableException("Le type de dossier « $type_dossier » n'existe pas.");
            }
        }

        $id_fe = $this->fluxEntiteSQL->addConnecteur($id_e, $type_dossier, $type_connecteur, $id_ce, $num_same_type);

        $message =  ($type_dossier != null) ?
            "Association au type de dossier $type_dossier en position " . ++$num_same_type . " du type de connecteur $type_connecteur pour l'entité id_e = $id_e"
            : "Association au type de connecteur $type_connecteur";

        $this->connecteurActionService->add(
            $id_e,
            $id_u,
            $id_ce,
            $type_dossier,
            ConnecteurActionService::ACTION_ASSOCIE,
            $message
        );

        return $id_fe;
    }
}
