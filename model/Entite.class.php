<?php

/** @deprecated 4.0.0 */
//Voir EntiteSQL.class.php

class Entite extends SQL
{
    private $id_e;

    private $info;

    public function __construct(SQLQuery $sqlQuery, $id_e)
    {
        parent::__construct($sqlQuery);
        $this->id_e = $id_e;
    }

    public function exists()
    {
        return $this->getInfo();
    }

    public function getMere()
    {
        $info = $this->getInfo();
        return $info['entite_mere'];
    }

    public function getInfo()
    {
        if (! $this->info) {
            $this->info = $this->getInfoWithId($this->id_e) ;
        }
        return $this->info;
    }


    private function getInfoWithId($id_e)
    {
        $sql = "SELECT * FROM entite WHERE id_e=?";
        return $this->queryOne($sql, $id_e);
    }


    public function getExtendedInfo()
    {
        $result = $this->getInfo();
        $cdg_id_e = $this->getCDG();
        $result['cdg'] = [];
        if ($cdg_id_e) {
            $result['cdg'] = $this->getInfoWithId($cdg_id_e) ;
        }
        if ($result['entite_mere']) {
            $result['entite_mere'] = $this->getInfoWithId($result['entite_mere']) ;
        }
        $result['filles'] = $this->getFille();

        return $result;
    }

    public function getSiren()
    {
        return $this->getHeritedInfo('siren');
    }

    public function getCDG()
    {
        return $this->getHeritedInfo('centre_de_gestion');
    }

    private function getHeritedInfo($colname)
    {
        $info = $this->getInfo();
        if ($info[$colname]) {
            return $info[$colname];
        }

        $ancetre = $this->getAncetre();
        foreach ($ancetre as $id => $info) {
            if ($info[$colname]) {
                return $info[$colname];
            }
        }
        return false;
    }

    public function getFille()
    {
        $sql = "SELECT * FROM entite WHERE entite_mere=? ORDER BY denomination";
        return $this->query($sql, $this->id_e);
    }

    public function getFilleWithType(array $type)
    {
        foreach ($type as $i => $t) {
            $type[$i] = "'$t'";
        }
        $sql = "SELECT * FROM entite " .
                " WHERE entite_mere=? " .
                " AND type IN (" . implode(",", $type) . ")" .
                " ORDER BY denomination";
        return $this->query($sql, $this->id_e);
    }

    public function getDescendance($id_e)
    {
        $sql = "SELECT id_e FROM entite_ancetre WHERE id_e_ancetre=?";
        $r = $this->query($sql, $id_e);
        $result = [];
        foreach ($r as $entite) {
            $result[] = $entite['id_e'];
        }
        return $result;
    }

    public function getAncetre()
    {
        static $ancetre;
        if (! $ancetre) {
            $sql = "SELECT * FROM entite_ancetre " .
                    " JOIN entite ON entite_ancetre.id_e_ancetre=entite.id_e " .
                    " WHERE entite_ancetre.id_e=? ORDER BY niveau DESC";
            $ancetre = $this->query($sql, $this->id_e);
        }
        return $ancetre;
    }

    public function getAncetreId()
    {
        $ancetre = $this->getAncetre();
        array_pop($ancetre);
        $result = [0];
        foreach ($ancetre as $entite) {
            $result[] = $entite['id_e'];
        }
        return $result;
    }
}
