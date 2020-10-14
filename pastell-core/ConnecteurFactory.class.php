<?php

class ConnecteurFactory
{

    private $objectInstancier;

    public function __construct(ObjectInstancier $objectInstancier)
    {
        $this->objectInstancier = $objectInstancier;
    }

    /**
     *
     * @param int $id_ce
     * @return Connecteur
     */
    public function getConnecteurById($id_ce)
    {
        $connecteur_info = $this->objectInstancier->getInstance(ConnecteurEntiteSQL::class)->getInfo($id_ce);
        return $this->getConnecteurObjet($connecteur_info);
    }

    /**
     * @param $id_ce
     * @return DonneesFormulaire
     * @throws Exception
     */
    public function getConnecteurConfig($id_ce)
    {
        return $this->objectInstancier->getInstance(DonneesFormulaireFactory::class)->getConnecteurEntiteFormulaire($id_ce);
    }

    public function getConnecteurId($id_e, $id_flux, $type_connecteur, $num_same_type = 0)
    {
        return $this->objectInstancier
            ->getInstance(FluxEntiteHeritageSQL::class)
            ->getConnecteurId($id_e, $id_flux, $type_connecteur, $num_same_type);
    }

    public function getConnecteurByType($id_e, $id_flux, $type_connecteur, $num_same_type = 0)
    {
        $id_ce = $this->getConnecteurId($id_e, $id_flux, $type_connecteur, $num_same_type);
        if (! $id_ce) {
            return false;
        }
        return $this->getConnecteurById($id_ce);
    }

    public function getConnecteurConfigByType($id_e, $id_flux, $type_connecteur, $num_same_type = 0)
    {
        $id_ce = $this->getConnecteurId($id_e, $id_flux, $type_connecteur, $num_same_type);
        if (! $id_ce) {
            return false;
        }
        return $this->getConnecteurConfig($id_ce);
    }

    /**
     * @param $connecteur_info
     * @return bool|Connecteur
     * @throws Exception
     */
    private function getConnecteurObjet($connecteur_info)
    {
        if (!$connecteur_info) {
            return false;
        }
        $class_name = $this->objectInstancier->getInstance(ConnecteurDefinitionFiles::class)->getConnecteurClass($connecteur_info['id_connecteur']);
        /** @var Connecteur $connecteurObject */
        $connecteurObject = $this->objectInstancier->newInstance($class_name);
        $connecteurObject->setConnecteurInfo($connecteur_info);
        $connecteurObject->setLogger($this->objectInstancier->getInstance('Monolog\Logger'));
        $connecteurObject->setConnecteurConfig($this->getConnecteurConfig($connecteur_info['id_ce']));
        return $connecteurObject;
    }

    public function getGlobalConnecteur($type)
    {
        return $this->getConnecteurByType(0, 'global', $type);
    }

    public function getGlobalConnecteurConfig($type)
    {
        return $this->getConnecteurConfigByType(0, 'global', $type);
    }

    public function getManquant()
    {
        $result = array();
        $all_connecteur_extension = $this->objectInstancier->getInstance(Extensions::class)->getAllConnecteur();
        $all_connecteur_extension = $this->clearRestrictedConnecteur($all_connecteur_extension);
        $all_connecteur_used = $this->objectInstancier->getInstance(ConnecteurEntiteSQL::class)->getAllUsed();
        foreach ($all_connecteur_used as $connecteur_id) {
            if (empty($all_connecteur_extension[$connecteur_id])) {
                $result[] = $connecteur_id;
            }
        }
        sort($result);
        return $result;
    }

    /**
     * @param string $id_connecteur
     * @return bool
     */
    public function isRestrictedConnecteur(string $id_connecteur): bool
    {
        //TODO Global
        if (array_key_exists($id_connecteur, $this->objectInstancier->getInstance(ConnecteurDefinitionFiles::class)->getAllRestricted())) {
            return true;
        }
        return false;
    }

    /**
     * @param array $list_connecteur
     * @return array
     */
    public function clearRestrictedConnecteur(array $list_connecteur): array
    {
        foreach ($list_connecteur as $id_connecteur => $values) {
            if ($this->isRestrictedConnecteur($id_connecteur)) {
                unset($list_connecteur[$id_connecteur]);
            }
        }
        return $list_connecteur;
    }

}
