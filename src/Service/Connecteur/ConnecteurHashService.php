<?php

namespace Pastell\Service\Connecteur;

use DonneesFormulaireFactory;
use Pastell\Helpers\StringHelper;

class ConnecteurHashService
{
    private $workspacePath;
    private $donneesFormulaireFactory;

    public function __construct(
        string $workspacePath,
        DonneesFormulaireFactory $donneesFormulaireFactory
    ) {
        $this->workspacePath = $workspacePath;
        $this->donneesFormulaireFactory = $donneesFormulaireFactory;
    }

    /**
     * @param int $id_ce
     * @return string
     * @throws \Exception
     */
    public function getHash(int $id_ce): string
    {

        $hash_connecteur = hash_file("sha256", $this->workspacePath . "/connecteur_$id_ce.yml");
        $all_file = glob($this->workspacePath . "/connecteur_$id_ce.yml_*");
        foreach ($all_file as $connecteur_file) {
            $hash_connecteur .= hash_file("sha256", $connecteur_file);
        }

        return StringHelper::chopString(
            hash("sha256", $hash_connecteur),
            8
        );
    }
}
