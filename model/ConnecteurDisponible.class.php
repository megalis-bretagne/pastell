<?php

class ConnecteurDisponible
{

    public const DROIT_NEDEED = 'entite:edition';

    private $entiteSQL;
    private $roleUtilisateur;
    private $connecteurEntiteSQL;

    public function __construct(EntiteSQL $entiteSQL, RoleUtilisateur $roleUtilisateur, ConnecteurEntiteSQL $connecteurEntiteSQL)
    {
        $this->entiteSQL = $entiteSQL;
        $this->roleUtilisateur = $roleUtilisateur;
        $this->connecteurEntiteSQL = $connecteurEntiteSQL;
    }

    /**
     *
     * @param int $id_u
     * @param int $id_e
     * @param string $type
     * @return array liste des connecteurs disponible pour id_e avec les droits de id_u
     */
    public function getList($id_u, $id_e, $type)
    {
        $ancetre = $this->entiteSQL->getAncetreId($id_e);
        array_shift($ancetre);
        $ancetre[] = $id_e;
        $ancetre = array_reverse($ancetre);
        $result = array();

        foreach ($ancetre as $entite_id_e) {
            if (! $this->roleUtilisateur->hasDroit($id_u, self::DROIT_NEDEED, $entite_id_e)) {
                continue;
            }

            $result = array_merge($result, $this->connecteurEntiteSQL->getDisponible($entite_id_e, $type));
        }
        return $result;
    }
}
