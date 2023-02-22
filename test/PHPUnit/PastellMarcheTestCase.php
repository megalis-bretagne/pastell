<?php

class PastellMarcheTestCase extends PastellTestCase
{
    public function reinitDatabase()
    {
        parent::reinitDatabase();

        /** @var RoleSQL $roleSQL */
        $roleSQL = $this->getObjectInstancier()->getInstance(RoleSQL::class);

        $flux_id_list = [
            'pes-marche',
            'piece-marche',
            'piece-marche-par-etape',
            'dossier-marche'
        ];

        foreach ($flux_id_list as $id_flux) {
            $roleSQL->addDroit('admin', "$id_flux:lecture");
            $roleSQL->addDroit('admin', "$id_flux:edition");
        }
    }
}
