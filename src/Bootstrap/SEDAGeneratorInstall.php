<?php

declare(strict_types=1);

namespace Pastell\Bootstrap;

use Exception;
use Pastell\Service\Connecteur\ConnecteurCreationService;
use SedaGenerique;

class SEDAGeneratorInstall implements InstallableBootstrap
{
    public function __construct(
        private readonly ConnecteurCreationService $connecteurCreationService,
    ) {
    }

    public function getName(): string
    {
        return 'Connecteur global « Générateur SEDA »';
    }

    /**
     * @throws Exception
     */
    public function install(): InstallResult
    {
        if ($this->connecteurCreationService->hasConnecteurGlobal(SedaGenerique::CONNECTEUR_GLOBAL_TYPE)) {
            return InstallResult::NothingToDo;
        }

        $this->connecteurCreationService->createAndAssociateGlobalConnecteur(
            SedaGenerique::CONNECTEUR_TYPE_ID,
            SedaGenerique::CONNECTEUR_GLOBAL_TYPE,
            'Générateur SEDA',
            [
                'seda_generator_url' => 'http://seda-generator',
            ]
        );

        return InstallResult::InstallOk;
    }
}
