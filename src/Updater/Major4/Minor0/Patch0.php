<?php

namespace Pastell\Updater\Major4\Minor0;

use Exception;
use Pastell\Service\Droit\DroitService;
use Pastell\Service\UpdateFieldService;
use Pastell\Updater\Version;
use Monolog\Logger;
use RoleDroit;
use RoleSQL;
use SQLQuery;

class Patch0 implements Version
{
    public function __construct(
        private readonly SQLQuery $sqlQuery,
        private readonly RoleSQL $roleSQL,
        private readonly RoleDroit $roleDroit,
        private readonly UpdateFieldService $updateFieldService,
        private readonly ?Logger $logger = null,
    ) {
    }

    /**
     * @throws Exception
     */
    public function update(): void
    {
        $this->updateEntiteServiceByCollectivite();
        $this->forceUpdateFieldMailsec();
        $this->addConnectorPermission();
    }

    /**
     * @throws Exception
     */
    /*
        Suppression du modèle `Entite`, remplacé par `EntiteSQL` (et suppression du type d'entité "service") #1589
    */
    private function updateEntiteServiceByCollectivite(): void
    {
        $sql = "UPDATE entite " .
            " SET type = 'collectivite' " .
            " WHERE type = 'service' ";
        $this->sqlQuery->query($sql);
        $this->logger?->info("[UPDATER]-updateEntiteServiceByCollectivite: $sql");
    }

    /**
     * @throws Exception
     */
    /*
        Suppression de la constante MODE_MUTUALISE.
        Lors de l'envoi d'un mail sécurisé, mailsec_from prend la valeur de PLATEFORME_MAIL.
        Il faut lancer la commande:
        `app:force-update-field connector mailsec mailsec_reply_to
        "{% if mailsec_reply_to == '' %}{{mailsec_from}}{% else %}{{mailsec_reply_to}}{% endif %}"`
        pour reporter l'ancien mailsec_from à mailsec_reply_to (s'il n'est pas déjà renseigné) #1465
    */
    private function forceUpdateFieldMailsec(): void
    {
        $scope = UpdateFieldService::SCOPE_CONNECTOR;
        $scopeType = 'configuration';
        $type = "mailsec";
        $field = "mailsec_reply_to";
        $twigExpression = "{% if mailsec_reply_to == '' %}{{mailsec_from}}{% else %}{{mailsec_reply_to}}{% endif %}";

        $documents = $this->updateFieldService->getAllDocuments($scope, $type);
        $documentsNumber = count($documents);
        $this->logger?->info(
            sprintf(
                "[UPDATER]-forceUpdateFieldMailsec: " .
                "%d %s %s `%s` to update",
                $documentsNumber,
                $scopeType,
                $scope,
                $type
            )
        );
        if ($documentsNumber === 0) {
            return;
        }

        foreach ($documents as $document) {
            $message = $this->updateFieldService->updateField($document, $scope, $field, $twigExpression);
            $this->logger?->info("[UPDATER]-forceUpdateFieldMailsec: $message");
        }
    }

    /**
     * @throws Exception
     */
    /*
        Suppression de la constante CONNECTEUR_DROIT.
        Il faut maintenant ajouter les droits 'connecteur:lecture' et 'connecteur:edition'
        afin de gérer les connecteurs et les associations de types de documents.
        Il faut reporter les droits (entite:) existants aux nouveaux droits (connecteur:) #1136
    */
    private function addConnectorPermission(): void
    {
        $roleDroitConnecteur = [];
        $roleDroitEntite = [];
        foreach ($this->roleSQL->getAllRole() as $role) {
            $droit = $this->roleSQL->getDroit($this->roleDroit->getAllDroit(), $role['role']);
            $this->RoleDroitfilter($role['role'], $droit, DroitService::DROIT_CONNECTEUR, $roleDroitConnecteur);
            $this->RoleDroitfilter($role['role'], $droit, DroitService::DROIT_ENTITE, $roleDroitEntite);
        }

        if (! empty($roleDroitConnecteur)) {
            $this->logger?->info(
                sprintf(
                    "[UPDATER]-addConnectorPermission: " .
                    "Nothing to do. There are already connector permission for role: `%s`",
                    json_encode($roleDroitConnecteur)
                )
            );
            return;
        }

        $numberOfRole = count($roleDroitEntite);
        $this->logger?->info(
            sprintf(
                "[UPDATER]-addConnectorPermission: " .
                "There are %s role whith entite permission to copy to connector",
                $numberOfRole
            )
        );

        foreach ($roleDroitEntite as $role => $droitEntite) {
            foreach ($droitEntite as $droit) {
                list(, $type) = explode(":", $droit);
                $this->roleSQL->addDroit($role, DroitService::DROIT_CONNECTEUR . ":" . $type);
                $this->logger?->info(
                    sprintf(
                        "[UPDATER]-addConnectorPermission: " .
                        "Add  %s:%s for %s",
                        DroitService::DROIT_CONNECTEUR,
                        $type,
                        $role
                    )
                );
            }
        }
    }

    /**
     * @param string $role
     * @param array $droit
     * @param string $familleDroit
     * @param array $roleDroit
     * @return void
     */
    private function roleDroitFilter(string $role, array $droit, string $familleDroit, array &$roleDroit): void
    {
        $droitFilter = array_filter($droit, static function ($value, $key) use ($familleDroit) {
            list($part) = explode(":", $key);
            return $value == 1 && $part === $familleDroit;
        }, ARRAY_FILTER_USE_BOTH);
        if (! empty($droitFilter)) {
            $roleDroit[$role] = array_keys($droitFilter);
        }
    }
}
